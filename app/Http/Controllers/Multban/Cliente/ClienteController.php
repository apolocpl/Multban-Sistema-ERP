<?php

namespace App\Http\Controllers\Multban\Cliente;

use App\Enums\EmpresaStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Multban\Auditoria\LogAuditoria;
use App\Models\Multban\Cliente\CardCateg;
use App\Models\Multban\Cliente\CardMod;
use App\Models\Multban\Cliente\CardStatus;
use App\Models\Multban\Cliente\CardTipo;
use App\Models\Multban\Cliente\Cliente;
use App\Models\Multban\Cliente\ClienteCard;
use App\Models\Multban\Cliente\ClienteProntuario;
use App\Models\Multban\Cliente\ClienteScore;
use App\Models\Multban\Cliente\ClienteStatus;
use App\Models\Multban\Cliente\ClienteTipo;
use App\Models\Multban\DadosMestre\TbDmConvenios;
use App\Models\Multban\Empresa\Empresa;
use App\Models\Multban\Endereco\Cidade;
use App\Models\Multban\Endereco\Estados;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;
use App\Support\Tenancy\TenantManager;

class ClienteController extends Controller
{
    private ?array $permissions = null;

    public function __construct(private readonly TenantManager $tenantManager) {}

    /**
     * Resolve the authenticated user's empresa id and ensure incoming identifiers belong to it.
     */
    private function authenticatedEmpresaId(?int $empresaId = null): int
    {
        return $this->tenantManager->ensure($empresaId);
    }

    /**
     * Fetch a cliente ensuring it belongs to the authenticated empresa and authorize the provided ability.
     */
    private function getClienteForUserOrFail(int $clienteId, string $ability = 'view'): Cliente
    {
        $empresaId = $this->tenantManager->ensure();

        $cliente = Cliente::where('cliente_id', $clienteId)
            ->whereHas('empresa', function ($query) use ($empresaId) {
                $query->where('tbdm_clientes_emp.emp_id', $empresaId);
            })
            ->firstOrFail();

        $this->authorize($ability, $cliente);

        return $cliente;
    }

    /**
     * Normalize incoming date values to the database default format.
     */
    private function normalizeDate(?string $value, string $targetFormat = 'Y-m-d'): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        $knownFormats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'];
        foreach ($knownFormats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format($targetFormat);
            } catch (\Throwable $e) {
                // keep trying with the next format
            }
        }

        try {
            return Carbon::parse($value)->format($targetFormat);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Resolve and cache the authenticated user's permissions.
     */
    private function resolvePermissions(): array
    {
        if ($this->permissions !== null) {
            return $this->permissions;
        }

        $user = Auth::user();

        if (! $user) {
            return $this->permissions = [];
        }

        if (method_exists($user, 'getAllPermissions')) {
            return $this->permissions = $user->getAllPermissions()->pluck('name')->toArray();
        }

        $rolePerms = collect();
        foreach ($user->roles as $role) {
            if (method_exists($role, 'permissions') || isset($role->permissions)) {
                $rolePerms = $rolePerms->merge($role->permissions->pluck('name'));
            }
        }

        $directPerms = isset($user->permissions) ? $user->permissions->pluck('name') : collect();

        return $this->permissions = $rolePerms->merge($directPerms)->unique()->toArray();
    }

    /**
     * Build the action buttons for cliente listings.
     */
    private function buildClienteActions(Cliente $cliente, array $permissions): string
    {
        $buttons = [];

        if (in_array('cliente.edit', $permissions, true)) {
            $buttons[] = '<a href="cliente/' . $cliente->cliente_id . '/alterar" class="btn btn-primary btn-sm mr-1" title="Editar"><i class="fas fa-edit"></i></a>';
        }

        $statusCode = optional($cliente->status)->cliente_sts;

        $disabled = '';
        if ($statusCode === EmpresaStatusEnum::ATIVO) {
            $disabled = 'disabled';
        }

        $buttons[] = '<button href="#" class="btn btn-primary btn-sm mr-1" ' . $disabled . ' id="active_grid_id" data-url="cliente" data-id="' . $cliente->cliente_id . '" title="Ativar"><i class="far fa-check-circle"></i></button>';

        $disabled = '';
        if ($statusCode === EmpresaStatusEnum::INATIVO) {
            $disabled = 'disabled';
        }

        $buttons[] = '<button href="#" class="btn btn-primary btn-sm mr-1" ' . $disabled . ' id="inactive_grid_id" data-url="cliente" data-id="' . $cliente->cliente_id . '" title="Inativar"><i class="fas fa-ban"></i></button>';

        if (in_array('cliente.destroy', $permissions, true)) {
            $disabled = '';
            if ($statusCode === EmpresaStatusEnum::EXCLUIDO) {
                $disabled = 'disabled';
            }

            $buttons[] = '<button href="#" class="btn btn-sm btn-primary mr-1" ' . $disabled . ' id="delete_grid_id" data-url="cliente" data-id="' . $cliente->cliente_id . '" title="Excluir"><i class="far fa-trash-alt"></i></button>';
        }

        return implode('', $buttons);
    }

    /**
     * Format cliente status badge.
     */
    private function buildClienteStatusBadge(Cliente $cliente): string
    {
        $status = $cliente->status;

        if (! $status) {
            return '<span class="badge badge-secondary">Sem status</span>';
        }

        switch ($status->cliente_sts) {
            case EmpresaStatusEnum::ATIVO:
                $class = 'success';
                break;
            case EmpresaStatusEnum::EMANALISE:
                $class = 'warning';
                break;
            case EmpresaStatusEnum::INATIVO:
            case EmpresaStatusEnum::EXCLUIDO:
            case EmpresaStatusEnum::BLOQUEADO:
                $class = 'danger';
                break;
            default:
                $class = 'secondary';
        }

        return '<span class="badge badge-' . $class . '">' . e($status->cliente_sts_desc ?? $status->cliente_sts) . '</span>';
    }

    /**
     * Format cliente tipo badge.
     */
    private function buildClienteTipoBadge(Cliente $cliente): string
    {
        $tipo = $cliente->tipo;

        if (! $tipo) {
            return '<span class="badge badge-secondary">Sem tipo</span>';
        }

        switch ($tipo->cliente_tipo) {
            case 1:
                $class = 'info';
                break;
            case 2:
            case 3:
            case 4:
                $class = 'success';
                break;
            default:
                $class = 'secondary';
        }

        return '<span class="badge badge-' . $class . '">' . e($tipo->cliente_tipo_desc ?? (string) $tipo->cliente_tipo) . '</span>';
    }

    /**
     * Format CPF/CNPJ.
     */
    private function formatClienteDocumento(?string $documento): string
    {
        if (empty($documento)) {
            return '-';
        }

        $limpo = preg_replace('/\D/', '', $documento);
        if (strlen($limpo) > 11) {
            return formatarCNPJ($limpo);
        }

        return formatarCPF($limpo);
    }

    private function formatCurrency($valor): string
    {
        if ($valor === null || $valor === '') {
            return '-';
        }

        return formatarDecimalParaTexto((float) $valor);
    }

    private function formatDate(?string $value, string $format = 'd/m/Y'): string
    {
        if (empty($value)) {
            return '-';
        }

        try {
            return Carbon::parse($value)->format($format);
        } catch (\Throwable $throwable) {
            return (string) $value;
        }
    }

    private function mapParcelaStatusBadge(?string $codigo): string
    {
        switch ($codigo) {
            case 'BXD':
            case 'BXI':
                return 'success';
            case 'REG':
                return 'warning';
            case 'IND':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * Load compras realizadas for the cliente/empresa context.
     */
    private function loadComprasForCliente(Cliente $cliente, int $empresaId)
    {
        $rows = DB::connection('dbsysclient')
            ->table('tbtr_p_titulos_cp as parcelas')
            ->leftJoin('tbdm_parcela_sts as status', function ($join) {
                $join->on('parcelas.parcela_sts', '=', 'status.parcela_sts')
                    ->where('status.langu', '=', 'PORT');
            })
            ->select([
                'parcelas.emp_id as emp_id',
                'parcelas.titulo as titulo',
                'parcelas.parcela as parcela',
                'parcelas.vlr_bpar_split as valor_inicial',
                'parcelas.vlr_jurosp as valor_juros',
                'parcelas.vlr_bpar_cj as valor_total',
                'parcelas.meio_pag_v as meio_pagamento',
                'parcelas.data_mov as data_venda',
                'parcelas.data_venc as data_vencimento',
                'parcelas.parcela_sts as status_codigo',
                'parcelas.nid_parcela as identificador',
                'status.parcela_sts_desc as status_descricao',
            ])
            ->where('parcelas.emp_id', $empresaId)
            ->where('parcelas.cliente_id', $cliente->cliente_id)
            ->orderByDesc('parcelas.data_mov')
            ->orderByDesc('parcelas.parcela')
            ->get();

        return $rows->map(function ($row) use ($cliente) {
            $statusClass = $this->mapParcelaStatusBadge($row->status_codigo);
            $statusDesc = $row->status_descricao ?? $row->status_codigo;

            return [
                'identificador'    => $row->identificador,
                'emp_id'           => $row->emp_id,
                'titulo'           => $row->titulo,
                'cliente'          => $cliente->cliente_nome,
                'parcela'          => $row->parcela,
                'valor_inicial'    => $this->formatCurrency($row->valor_inicial),
                'valor_juros'      => $this->formatCurrency($row->valor_juros),
                'valor_total'      => $this->formatCurrency($row->valor_total),
                'meio_pagamento'   => $row->meio_pagamento,
                'data_venda'       => $this->formatDate($row->data_venda),
                'data_vencimento'  => $this->formatDate($row->data_vencimento),
                'status'           => [
                    'descricao' => $statusDesc,
                    'classe'    => $statusClass,
                ],
            ];
        });
    }

    /**
     * Load cartões vinculados ao cliente no contexto da empresa.
     */
    private function loadCartoesForCliente(Cliente $cliente, int $empresaId, bool $canManageRelatedData)
    {
        if (! $canManageRelatedData) {
            return collect();
        }

        $cards = ClienteCard::where('emp_id', $empresaId)
            ->where('cliente_id', $cliente->cliente_id)
            ->get();

        if ($cards->isEmpty()) {
            return collect();
        }

        $empresaNome = Empresa::select('emp_nmult')->find($empresaId)?->emp_nmult ?? 'Empresa não encontrada';
        $permissions = $this->resolvePermissions();
        $canResetPassword = in_array('cliente.edit', $permissions, true);

        $statusLookup = CardStatus::all()->keyBy('card_sts');
        $categoriaLookup = CardCateg::all()->keyBy('card_categ');

        return $cards->map(function (ClienteCard $card) use ($empresaNome, $statusLookup, $categoriaLookup, $canManageRelatedData, $canResetPassword) {
            $statusModel = $statusLookup->get($card->card_sts);
            $statusDesc = $statusModel?->card_sts_desc ?? ($card->card_sts ?? 'Sem status');
            $statusBadge = $this->formatCardStatusBadge($card->card_sts, $statusDesc);

            $categoriaBadge = '';
            if ($categoriaLookup->has($card->card_categ)) {
                $categoriaBadge = '<span class="badge badge-info">' . e($categoriaLookup->get($card->card_categ)->card_categ_desc) . '</span>';
            }

            return [
                'actions'         => $canManageRelatedData ? $this->buildCartaoActions($card, $canResetPassword) : '',
                'empresa'         => $empresaNome,
                'numero'          => formatarCartaoCredito(Str::mask($card->cliente_cardn, '*', 0, -4)),
                'cv'              => $card->cliente_cardcv,
                'status_badge'    => $statusBadge,
                'tipo_label'      => $card->card_tp === 'PRE' ? 'Pré-pago' : 'Pós-pago',
                'modalidade'      => $this->translateCardModalidade($card->card_mod),
                'categoria_badge' => $categoriaBadge,
                'descricao'       => mb_strtoupper(rtrim($card->card_desc ?? ''), 'UTF-8'),
                'saldo_valor'     => formatarDecimalParaTexto($card->card_saldo_vlr ?? 0),
                'limite_valor'    => formatarDecimalParaTexto($card->card_limite ?? 0),
                'saldo_pontos'    => formatarDecimalParaTexto(
                    ($card->card_pts_part ?? 0)
                    + ($card->card_pts_fraq ?? 0)
                    + ($card->card_pts_mult ?? 0)
                    + ($card->card_pts_cash ?? 0)
                ),
            ];
        })->values();
    }

    /**
     * Build action buttons for cartão rows.
     */
    private function buildCartaoActions(ClienteCard $card, bool $canResetPassword): string
    {
        $buttons = [];

        $isDeleted = $card->card_sts === 'EX';

        if ($canResetPassword) {
            $maskedCardNumber = formatarCartaoCredito(Str::mask($card->cliente_cardn, '*', 0, -4));
            $resetDisabled = $isDeleted ? 'disabled' : '';
            $buttons[] = '<button type="button" class="btn btn-sm btn-primary mr-1 btn-reset-card-password" data-emp-id="' . $card->emp_id . '" data-uuid="' . e($card->card_uuid) . '" data-card-label="' . e($maskedCardNumber) . '" title="Resetar Senha" ' . $resetDisabled . '><i class="fas fa-key"></i></button>';
        }

        $maskedCardNumber = formatarCartaoCredito(Str::mask($card->cliente_cardn, '*', 0, -4));
        $isActive = $card->card_sts === 'AT';
        $activateDisabled = $isActive ? 'disabled' : '';
        if ($isDeleted) {
            $activateDisabled = 'disabled';
        }
        $isBlocked = $card->card_sts === 'BL';
        $blockDisabled = $isBlocked ? 'disabled' : '';
        if ($isDeleted) {
            $blockDisabled = 'disabled';
        }
        $editDisabled = $isDeleted ? 'disabled' : '';
        $buttons[] = '<button type="button" class="btn btn-sm btn-primary mr-1 btn-edit-card" data-emp-id="' . $card->emp_id . '" data-uuid="' . e($card->card_uuid) . '" data-card-label="' . e($maskedCardNumber) . '" data-current-status="' . e($card->card_sts ?? '') . '" title="Editar" ' . $editDisabled . '><i class="fas fa-edit"></i></button>';
        $buttons[] = '<button type="button" class="btn btn-sm btn-primary mr-1 btn-activate-card" data-emp-id="' . $card->emp_id . '" data-uuid="' . e($card->card_uuid) . '" data-card-label="' . e($maskedCardNumber) . '" data-current-status="' . e($card->card_sts ?? '') . '" title="Ativar" ' . $activateDisabled . '><i class="far fa-check-circle"></i></button>';
        $buttons[] = '<button type="button" class="btn btn-sm btn-primary mr-1 btn-block-card" data-emp-id="' . $card->emp_id . '" data-uuid="' . e($card->card_uuid) . '" data-card-label="' . e($maskedCardNumber) . '" data-current-status="' . e($card->card_sts ?? '') . '" title="Bloquear" ' . $blockDisabled . '><i class="fas fa-ban"></i></button>';
        $deleteDisabled = ($card->card_sts === 'EX') ? 'disabled' : '';
        $buttons[] = '<button type="button" class="btn btn-sm btn-primary mr-1 btn-delete-card" data-emp-id="' . $card->emp_id . '" data-uuid="' . e($card->card_uuid) . '" data-card-label="' . e($maskedCardNumber) . '" data-current-status="' . e($card->card_sts ?? '') . '" title="Excluir" ' . $deleteDisabled . '><i class="far fa-trash-alt"></i></button>';

        return implode('', $buttons);
    }

    /**
     * Determine whether a 4-digit card password is considered weak (sequential or repeated digits).
     */
    private function isWeakCardPassword(string $password): bool
    {
        if (! preg_match('/^\d{4}$/', $password)) {
            return true;
        }

        $digits = str_split($password);

        if (count(array_unique($digits)) === 1) {
            return true;
        }

        $ascending = true;
        $descending = true;

        for ($index = 1; $index < count($digits); $index++) {
            $previous = (int) $digits[$index - 1];
            $current = (int) $digits[$index];

            if ($current !== (($previous + 1) % 10)) {
                $ascending = false;
            }

            if ($current !== (($previous + 9) % 10)) {
                $descending = false;
            }
        }

        return $ascending || $descending;
    }

    /**
     * Build the cache key used to store temporary private keys for card password encryption.
     */
    private function cardPasswordTokenCacheKey(string $token): string
    {
        return 'card-password-token:' . $token;
    }

    /**
     * Decrypt an encrypted password value using the provided private key.
     */
    private function decryptCardPasswordValue(string $privateKey, string $cipherText): ?string
    {
        $decodedCipher = base64_decode($cipherText, true);

        if ($decodedCipher === false) {
            return null;
        }

        $decrypted = '';
        $success = openssl_private_decrypt($decodedCipher, $decrypted, $privateKey, OPENSSL_PKCS1_OAEP_PADDING);

        if (! $success) {
            return null;
        }

        return $decrypted;
    }

    /**
     * Format the cartão status badge.
     */
    private function formatCardStatusBadge(?string $statusCode, string $statusDesc): string
    {
        if (! $statusCode) {
            return '<span class="badge badge-secondary">Sem status</span>';
        }

        switch ($statusCode) {
            case 'AT':
                $class = 'success';
                break;
            case 'IN':
            case 'EX':
            case 'BL':
                $class = 'danger';
                break;
            default:
                $class = 'secondary';
                break;
        }

        return '<span class="badge badge-' . $class . '">' . e($statusDesc) . '</span>';
    }

    /**
     * Translate the cartão modalidade code into a human readable label.
     */
    private function translateCardModalidade(?string $codigo): string
    {
        switch ($codigo) {
            case 'CRDT':
                return 'Crédito';
            case 'DEBT':
                return 'Débito';
            case 'GIFT':
                return 'Gift Card';
            case 'FID':
                return 'Fidelidade';
            default:
                return $codigo ?? '-';
        }
    }

    /**
     * Load prontuários vinculados ao cliente.
     */
    private function loadProntuariosForCliente(Cliente $cliente, int $empresaId, bool $canManageRelatedData)
    {
        if (! $canManageRelatedData) {
            return collect();
        }

        $prontuarios = ClienteProntuario::with(['user', 'tipo'])
            ->where('emp_id', $empresaId)
            ->where('cliente_id', $cliente->cliente_id)
            ->orderByDesc('protocolo')
            ->get();

        return $prontuarios->map(function (ClienteProntuario $row) {
            $badge = '';
            if (! empty($row->tipo)) {
                switch ($row->tipo->protocolo_tp) {
                    case 1:
                        $badge = '<span class="badge badge-info">' . e($row->tipo->prt_tp_desc) . '</span>';
                        break;
                    case 2:
                    case 3:
                    case 4:
                        $badge = '<span class="badge badge-success">' . e($row->tipo->prt_tp_desc) . '</span>';
                        break;
                    default:
                        $badge = '<span class="badge badge-secondary">' . e($row->tipo->prt_tp_desc ?? $row->protocolo_tp) . '</span>';
                        break;
                }
            }

            $anexo = $row->anexo === 'x'
                ? '<span class="text-center ml-3"><i class="fas fa-paperclip"></i></span>'
                : '<span class="badge badge-secondary">Sem Anexo</span>';

            return [
                'id'             => $row->id,
                'protocolo'      => str_pad((string) $row->protocolo, 12, '0', STR_PAD_LEFT),
                'protocolo_raw'  => $row->protocolo,
                'protocolo_tp'   => $badge,
                'medico'         => $row->user->user_name ?? 'Sem Médico',
                'crm_medico'     => $row->user->user_crm ?? 'Sem CRM',
                'anexo'          => $anexo,
                'texto_anm'      => $row->texto_anm ?? '',
                'texto_prt'      => $row->texto_prt ?? '',
                'texto_prv'      => $row->texto_prv ?? '',
                'texto_rec'      => $row->texto_rec ?? '',
                'texto_exm'      => $row->texto_exm ?? '',
                'texto_atd'      => $row->texto_atd ?? '',
                'images'         => $this->listPublicFiles('prontuarios/empresa_' . $row->emp_id . '/client_' . $row->cliente_id . '/fotos/protocolo_' . $row->protocolo . '/thumbnails'),
                'docs'           => $this->listPublicFiles('prontuarios/empresa_' . $row->emp_id . '/client_' . $row->cliente_id . '/docs/protocolo_' . $row->protocolo),
            ];
        })->values();
    }

    /**
     * Helper to list files from the public storage disk safely.
     */
    private function listPublicFiles(string $path): array
    {
        if (! Storage::disk('public')->exists($path)) {
            return [];
        }

        return Storage::disk('public')->allFiles($path);
    }

    /**
     * Load score entries for the cliente.
     */
    private function loadScoreEntriesForCliente(Cliente $cliente)
    {
        return ClienteScore::where('cliente_id', $cliente->cliente_id)
            ->orderByDesc('dthr_cr')
            ->get()
            ->map(function ($row) {
                return [
                    'numero'        => $row->cliente_proc_num,
                    'tipo'          => $row->cliente_proc_tipo,
                    'descricao'     => $row->cliente_proc_desc,
                    'status'        => $row->cliente_proc_sts,
                    'data_consulta' => $this->formatDate($row->cliente_proc_dtc),
                    'data_inicio'   => $this->formatDate($row->cliente_proc_dti),
                    'data_fim'      => $this->formatDate($row->cliente_proc_dtf),
                    'valor'         => $this->formatScoreValor($row->cliente_proc_vlr),
                ];
            });
    }

    private function formatScoreValor(?string $valor): string
    {
        if ($valor === null || $valor === '') {
            return '-';
        }

        $normalizado = preg_replace('/[^\d,.-]/', '', $valor);
        if ($normalizado === '' || $normalizado === null) {
            return $valor;
        }

        $normalizado = str_replace('.', '', $normalizado);
        $normalizado = str_replace(',', '.', $normalizado);

        if (! is_numeric($normalizado)) {
            return $valor;
        }

        return formatarDecimalParaTexto((float) $normalizado);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $status = ClienteStatus::all();
        $tipos = ClienteTipo::all();
        $filters = session('cliente_filters', []);
        $nomeMultbanOptions = [];
        $clientesRecentes = collect();
        $empresa = null;

        try {
            $empresaId = $this->tenantManager->ensure();
            $empresa = Empresa::select('emp_id', 'emp_nmult', 'emp_cnpj')->find($empresaId);

            if ($empresa && ! empty($empresa->emp_nmult)) {
                $nomeMultbanOptions[] = $empresa->emp_nmult;
            }

            $recentes = Cliente::with(['status', 'tipo'])
                ->whereHas('clienteEmp', function ($query) use ($empresaId) {
                    $query->where('emp_id', $empresaId);
                })
                ->orderByDesc('cliente_id')
                ->limit(10)
                ->get();

            $permissions = $this->resolvePermissions();

            $clientesRecentes = $recentes->map(function (Cliente $cliente) use ($permissions) {
                return [
                    'action'               => $this->buildClienteActions($cliente, $permissions),
                    'cliente_id'           => $cliente->cliente_id,
                    'cliente_nome'         => $cliente->cliente_nome,
                    'cliente_doc'          => $this->formatClienteDocumento($cliente->cliente_doc),
                    'cliente_tipo_badge'   => $this->buildClienteTipoBadge($cliente),
                    'cliente_email'        => $cliente->cliente_email ?? '-',
                    'cliente_cel'          => $cliente->cliente_cel ? formatarTelefone($cliente->cliente_cel) : '-',
                    'cliente_status_badge' => $this->buildClienteStatusBadge($cliente),
                ];
            });
        } catch (\Throwable $e) {
            // Caso o tenant não esteja disponível ainda, apenas ignore e siga sem opções pré-carregadas.
        }

        if (! empty($filters['nome_multban']) && ! in_array($filters['nome_multban'], $nomeMultbanOptions, true)) {
            $nomeMultbanOptions[] = $filters['nome_multban'];
        }

        $nomeMultbanOptions = array_values(array_filter(array_unique($nomeMultbanOptions), function ($value) {
            return $value !== null && $value !== '';
        }));

        return response()->view('Multban.cliente.index', compact(
            'status',
            'tipos',
            'filters',
            'nomeMultbanOptions',
            'clientesRecentes',
            'empresa'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $emp_id = $this->tenantManager->ensure();
        $userRole = Auth::user()->roles->pluck('name', 'name')->all();

        $status = ClienteStatus::all();
        $tipos = ClienteTipo::all();
        $cardTipos = CardTipo::all();
        $cardMod = CardMod::all();
        $cardCateg = CardCateg::all();
        $cliente = new Cliente;
        $convenios = TbDmConvenios::all();
       $estados = Estados::orderBy('estado_desc')->get();
       $cidades = Cidade::orderBy('cidade_desc')->get();
       $users = User::with('cargo')->get(); // ou sua query customizada
        $compras = collect();
        $scoreEntries = collect();
        $cartoes = collect();
        $prontuarios = collect();
        $canManageRelatedData = true;

        $canChangeStatus = false;
        foreach ($userRole as $key => $value) {

            if ($value == 'admin') {
                $canChangeStatus = true; // Se for usuário Admin, pode mudar o Status
            }
        }

        return response()->view('Multban.cliente.edit', compact(
            'cliente',
            'status',
            'tipos',
            'cardTipos',
            'cardMod',
            'cardCateg',
            'canChangeStatus',
            'convenios',
            'estados',
            'cidades',
            'users',
            'compras',
            'scoreEntries',
            'cartoes',
            'prontuarios',
            'canManageRelatedData'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        DB::beginTransaction();
        try {
            $emp_id = $this->tenantManager->ensure();

            $userRole = Auth::user()->roles->pluck('name', 'name')->all();
            $canChangeStatus = false;
            foreach ($userRole as $key => $value) {

                if ($value == 'admin') {
                    $canChangeStatus = true; // Se for usuário Admin, pode mudar o Status
                }
            }

            $cliente = new Cliente;
            $input = $request->all();

            $input['cliente_nome'] = rtrim($request->cliente_nome);
            $input['cliente_doc'] = removerCNPJ($request->cliente_doc);
            $input['cliente_rendam'] = formatarTextoParaDecimal($request->cliente_rendam);

            $clienteChk = Cliente::where('cliente_doc', removerCNPJ($request->cliente_doc))->first();
            if ($clienteChk) {
                return response()->json([
                    'message_type' => 'Já existe um cliente cadastrado com esse CPF/CNPJ.',
                    'message'      => ['cliente_doc' => ['Já existe um cliente cadastrado com esse CPF/CNPJ.']],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validator = Validator::make($input, $cliente->rules(), $cliente->messages(), $cliente->attributes());

            if ($validator->fails()) {
                return response()->json([
                    'message'   => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $cliente->cliente_tipo = $request->cliente_tipo;
            $cliente->convenio_id = $request->convenio_id;
            $cliente->carteirinha = $request->carteirinha;
            $cliente->cliente_dt_nasc = $this->normalizeDate($request->cliente_dt_nasc);
            $cliente->cliente_doc = removerCNPJ($request->cliente_doc);
            $cliente->cliente_rg = removerCNPJ($request->cliente_rg);
            $cliente->cliente_pasprt = $request->cliente_pasprt;
            $cliente->cliente_sts = ! $canChangeStatus ? 'NA' : $request->cliente_sts; /* Cliente nasce com o status "Em Análise" */
            $cliente->cliente_uuid = Str::uuid()->toString();
            $cliente->cliente_nome = mb_strtoupper(rtrim($request->cliente_nome), 'UTF-8');
            $cliente->cliente_nm_alt = mb_strtoupper(rtrim($request->cliente_nm_alt), 'UTF-8');
            $cliente->cliente_nm_card = $request->cliente_nm_card;
            $cliente->cliente_email = $request->cliente_email;
            $cliente->cliente_email_s = $request->cliente_email_s;
            $cliente->cliente_cel = removerMascaraTelefone($request->cliente_cel);
            $cliente->cliente_cel_s = removerMascaraTelefone($request->cliente_cel_s);
            $cliente->cliente_telfixo = removerMascaraTelefone($request->cliente_telfixo);
            $cliente->cliente_rendam = $input['cliente_rendam'];
            $cliente->cliente_rdam_s = $request->cliente_rdam_s;
            $cliente->cliente_dt_fech = $request->cliente_dt_fech;
            $cliente->cliente_cep = removerMascaraCEP($request->cliente_cep);
            $cliente->cliente_end = mb_strtoupper(rtrim($request->cliente_end), 'UTF-8');
            $cliente->cliente_endnum = $request->cliente_endnum;
            $cliente->cliente_endcmp = mb_strtoupper(rtrim($request->cliente_endcmp), 'UTF-8');
            $cliente->cliente_endbair = mb_strtoupper(rtrim($request->cliente_endbair), 'UTF-8');
            $cliente->cliente_endcid = $request->cliente_endcid;
            $cliente->cliente_endest = $request->cliente_endest;
            $cliente->cliente_endpais = $request->cliente_endpais;
            $cliente->cliente_cep_s = removerMascaraCEP($request->cliente_cep_s);
            $cliente->cliente_end_s = mb_strtoupper(rtrim($request->cliente_end_s), 'UTF-8');
            $cliente->cliente_endnum_s = $request->cliente_endnum_s;
            $cliente->cliente_endcmp_s = mb_strtoupper(rtrim($request->cliente_endcmp_s), 'UTF-8');
            $cliente->cliente_endbair_s = mb_strtoupper(rtrim($request->cliente_endbair_s), 'UTF-8');
            $cliente->cliente_endcid_s = $request->cliente_endcid_s;
            $cliente->cliente_endest_s = $request->cliente_endest_s;
            $cliente->cliente_endpais_s = $request->cliente_endpais_s;
            $cliente->cliente_score = $request->cliente_score;
            $cliente->cliente_lmt_sg = $request->cliente_lmt_sg;
            $cliente->criador = Auth::user()->user_id;
            $cliente->modificador = Auth::user()->user_id;
            $cliente->dthr_cr = Carbon::now();
            $cliente->dthr_ch = Carbon::now();

            $cliente->save();

            $tbdm_clientes_emp = DB::connection('dbsysclient')->table('tbdm_clientes_emp')->insert([
                'emp_id'         => $emp_id,
                'cliente_id'     => $cliente->cliente_id,
                'cliente_uuid'   => $cliente->cliente_uuid,
                'cliente_doc'    => removerCNPJ($cliente->cliente_doc),
                'cliente_pasprt' => $cliente->cliente_pasprt,
                'cad_liberado'   => '',
                'criador'        => Auth::user()->user_id,
                'dthr_cr'        => Carbon::now(),
                'modificador'    => Auth::user()->user_id,
                'dthr_ch'        => Carbon::now(),
            ]);

            // Auditoria

            $logAuditoria = new LogAuditoria;
            $logAuditoria->auddat = date('Y-m-d H:i:s');
            $logAuditoria->audusu = Auth::user()->user_id;
            $logAuditoria->audtar = 'Adicionou o cliente';
            $logAuditoria->audarq = $cliente->getTable();
            $logAuditoria->audlan = $cliente->id;
            $logAuditoria->audant = '';
            $logAuditoria->auddep = $cliente->cliente_nome;
            $logAuditoria->audnip = request()->ip();
            $logAuditoria->save();

            DB::commit();
            // Session::flash("idModeloInserido", $cliente->cliente_id);

            // Session::flash('success', "Cliente " . str_pad($cliente->cliente_id, 5, "0", STR_PAD_LEFT) . " adicionado com sucesso.");

            return response()->json([
                'message'   => 'Cliente ' . str_pad($cliente->cliente_id, 5, '0', STR_PAD_LEFT) . ' adicionado com sucesso.',
                'redirect'  => route('cliente.edit', ['id' => $cliente->cliente_id]),
            ]);
        } catch (Exception|\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message'   => $e->getMessage(),
            ], 500);
        }
    }

    public function createCardPasswordToken(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        if ($user->cannot('cliente.edit')) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $keyResource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if (! $keyResource) {
            throw new Exception('Não foi possível gerar a chave de criptografia temporária.');
        }

        $privateKey = '';
        $exported = openssl_pkey_export($keyResource, $privateKey);
        $details = openssl_pkey_get_details($keyResource);

        openssl_pkey_free($keyResource);

        if (! $exported || ! $details || empty($details['key'])) {
            throw new Exception('Falha ao preparar a chave pública para criptografia.');
        }

        $token = (string) Str::uuid();
        Cache::put($this->cardPasswordTokenCacheKey($token), $privateKey, now()->addMinutes(5));

        return response()->json([
            'token'      => $token,
            'public_key' => $details['key'],
        ]);
    }

    public function resetCardPassword(Request $request, string $cardUuid)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'emp_id'                         => ['required', 'integer'],
                    'password_token'                 => ['required', 'string'],
                    'password_cipher'                => ['required', 'string'],
                    'password_confirmation_cipher'   => ['required', 'string'],
                ],
                [
                    'password_token.required'               => 'Não foi possível preparar a criptografia da senha. Recarregue a página e tente novamente.',
                    'password_cipher.required'              => 'Informe a nova senha do cartão.',
                    'password_confirmation_cipher.required' => 'Confirme a nova senha do cartão.',
                ],
                [
                    'password_cipher'              => 'nova senha',
                    'password_confirmation_cipher' => 'confirmação da senha',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validator->validated();

            $cacheKey = $this->cardPasswordTokenCacheKey($validated['password_token']);
            $privateKey = Cache::pull($cacheKey);

            if (! $privateKey) {
                return response()->json([
                    'message' => [
                        'password_cipher' => ['Sessão expirada para redefinição de senha. Gere uma nova solicitação.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $password = $this->decryptCardPasswordValue($privateKey, $validated['password_cipher']);
            $passwordConfirmation = $this->decryptCardPasswordValue($privateKey, $validated['password_confirmation_cipher']);

            unset($privateKey);

            if ($password === null || $passwordConfirmation === null) {
                return response()->json([
                    'message' => [
                        'password_cipher' => ['Não foi possível processar a nova senha do cartão. Tente novamente.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($password !== $passwordConfirmation) {
                return response()->json([
                    'message' => [
                        'password_confirmation_cipher' => ['As senhas informadas não coincidem.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (! preg_match('/^\d{4}$/', $password)) {
                return response()->json([
                    'message' => [
                        'password_cipher' => ['A nova senha deve conter exatamente 4 dígitos numéricos.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($this->isWeakCardPassword($password)) {
                return response()->json([
                    'message' => [
                        'password_cipher' => ['Utilize uma combinação menos previsível (evite sequências e dígitos repetidos).'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $empresaId = $this->authenticatedEmpresaId((int) $validated['emp_id']);

            $card = ClienteCard::query()
                ->where('card_uuid', $cardUuid)
                ->where('emp_id', $empresaId)
                ->firstOrFail();

            $this->getClienteForUserOrFail((int) $card->cliente_id, 'manageRelatedData');

            DB::connection('dbsysclient')
                ->table('tbdm_clientes_card')
                ->where('card_uuid', $cardUuid)
                ->where('emp_id', $empresaId)
                ->update([
                    'card_pass'   => Hash::make($password),
                    'modificador' => Auth::user()->user_id,
                    'dthr_ch'     => Carbon::now(),
                ]);

            return response()->json([
                'title' => 'Sucesso',
                'text'  => 'Senha do cartão atualizada com sucesso.',
                'type'  => 'success',
            ]);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'title' => 'Registro não encontrado',
                'text'  => 'Cartão não localizado ou não pertence à empresa autenticada.',
                'type'  => 'error',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text'  => $th->getMessage(),
                'type'  => 'error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function activateCard(Request $request, string $cardUuid)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'emp_id' => ['required', 'integer'],
                ],
                [
                    'emp_id.required' => 'Empresa obrigatória para ativar o cartão.',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $empresaId = $this->authenticatedEmpresaId((int) $validator->validated()['emp_id']);

            $card = ClienteCard::query()
                ->where('card_uuid', $cardUuid)
                ->where('emp_id', $empresaId)
                ->firstOrFail();

            $this->getClienteForUserOrFail((int) $card->cliente_id, 'manageRelatedData');

            if ($card->card_sts === 'AT') {
                return response()->json([
                    'title' => 'Aviso',
                    'text'  => 'Este cartão já encontra-se ativo.',
                    'type'  => 'info',
                ]);
            }

            if ($card->card_sts === 'EX') {
                return response()->json([
                    'title' => 'Aviso',
                    'text'  => 'Este cartão foi excluído e não pode ser reativado.',
                    'type'  => 'info',
                ], Response::HTTP_CONFLICT);
            }

            DB::connection('dbsysclient')
                ->table('tbdm_clientes_card')
                ->where('card_uuid', $cardUuid)
                ->where('emp_id', $empresaId)
                ->update([
                    'card_sts'     => 'AT',
                    'modificador'  => Auth::user()->user_id,
                    'dthr_ch'      => Carbon::now(),
                ]);

            return response()->json([
                'title' => 'Sucesso',
                'text'  => 'Cartão ativado com sucesso.',
                'type'  => 'success',
            ]);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'title' => 'Registro não encontrado',
                'text'  => 'Cartão não localizado ou não pertence à empresa autenticada.',
                'type'  => 'error',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text'  => $th->getMessage(),
                'type'  => 'error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function blockCard(Request $request, string $cardUuid)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'emp_id' => ['required', 'integer'],
                ],
                [
                    'emp_id.required' => 'Empresa obrigatória para bloquear o cartão.',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $empresaId = $this->authenticatedEmpresaId((int) $validator->validated()['emp_id']);

            $card = ClienteCard::query()
                ->where('card_uuid', $cardUuid)
                ->where('emp_id', $empresaId)
                ->firstOrFail();

            $this->getClienteForUserOrFail((int) $card->cliente_id, 'manageRelatedData');

            if ($card->card_sts === 'BL') {
                return response()->json([
                    'title' => 'Aviso',
                    'text'  => 'Este cartão já está bloqueado.',
                    'type'  => 'info',
                ]);
            }

            if ($card->card_sts === 'EX') {
                return response()->json([
                    'title' => 'Aviso',
                    'text'  => 'Este cartão foi excluído e não pode ser bloqueado.',
                    'type'  => 'info',
                ], Response::HTTP_CONFLICT);
            }

            DB::connection('dbsysclient')
                ->table('tbdm_clientes_card')
                ->where('card_uuid', $cardUuid)
                ->where('emp_id', $empresaId)
                ->update([
                    'card_sts'     => 'BL',
                    'modificador'  => Auth::user()->user_id,
                    'dthr_ch'      => Carbon::now(),
                ]);

            return response()->json([
                'title' => 'Sucesso',
                'text'  => 'Cartão bloqueado com sucesso.',
                'type'  => 'success',
            ]);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'title' => 'Registro não encontrado',
                'text'  => 'Cartão não localizado ou não pertence à empresa autenticada.',
                'type'  => 'error',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text'  => $th->getMessage(),
                'type'  => 'error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteCard(Request $request, string $cardUuid)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'emp_id' => ['required', 'integer'],
                ],
                [
                    'emp_id.required' => 'Empresa obrigatória para excluir o cartão.',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $empresaId = $this->authenticatedEmpresaId((int) $validator->validated()['emp_id']);

            $card = ClienteCard::query()
                ->where('card_uuid', $cardUuid)
                ->where('emp_id', $empresaId)
                ->firstOrFail();

            $this->getClienteForUserOrFail((int) $card->cliente_id, 'manageRelatedData');

            if ($card->card_sts === 'EX') {
                return response()->json([
                    'title' => 'Aviso',
                    'text'  => 'Este cartão já está marcado como excluído.',
                    'type'  => 'info',
                ]);
            }

            DB::connection('dbsysclient')
                ->table('tbdm_clientes_card')
                ->where('card_uuid', $cardUuid)
                ->where('emp_id', $empresaId)
                ->update([
                    'card_sts'     => 'EX',
                    'modificador'  => Auth::user()->user_id,
                    'dthr_ch'      => Carbon::now(),
                ]);

            return response()->json([
                'title' => 'Sucesso',
                'text'  => 'Cartão excluído com sucesso.',
                'type'  => 'success',
            ]);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'title' => 'Registro não encontrado',
                'text'  => 'Cartão não localizado ou não pertence à empresa autenticada.',
                'type'  => 'error',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text'  => $th->getMessage(),
                'type'  => 'error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function showCardDetails(Request $request, string $cardUuid)
    {
        try {
            $empresaId = $this->authenticatedEmpresaId((int) $request->input('emp_id'));

            $card = ClienteCard::query()
                ->where('card_uuid', $cardUuid)
                ->where('emp_id', $empresaId)
                ->firstOrFail();

            $this->getClienteForUserOrFail((int) $card->cliente_id, 'manageRelatedData');

            return response()->json([
                'title' => 'Sucesso',
                'text'  => 'Dados obtidos com sucesso.',
                'type'  => 'success',
                'data'  => [
                    'card_desc'   => $card->card_desc ?? '',
                    'card_limite' => formatarDecimalParaTexto($card->card_limite ?? 0),
                ],
            ]);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'title' => 'Registro não encontrado',
                'text'  => 'Cartão não localizado ou não pertence à empresa autenticada.',
                'type'  => 'error',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text'  => $th->getMessage(),
                'type'  => 'error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateCardDetails(Request $request, string $cardUuid)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'emp_id'      => ['required', 'integer'],
                    'card_desc'   => ['required', 'string', 'max:255'],
                    'card_limite' => ['required', 'string'],
                ],
                [
                    'card_desc.required'   => 'Informe a descrição do cartão.',
                    'card_limite.required' => 'Informe o limite do cartão.',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validator->validated();

            $empresaId = $this->authenticatedEmpresaId((int) $validated['emp_id']);

            $card = ClienteCard::query()
                ->where('card_uuid', $cardUuid)
                ->where('emp_id', $empresaId)
                ->firstOrFail();

            $this->getClienteForUserOrFail((int) $card->cliente_id, 'manageRelatedData');

            $descricao = mb_strtoupper(rtrim($validated['card_desc']), 'UTF-8');
            $limite = formatarTextoParaDecimal($validated['card_limite']);

            DB::connection('dbsysclient')
                ->table('tbdm_clientes_card')
                ->where('card_uuid', $cardUuid)
                ->where('emp_id', $empresaId)
                ->update([
                    'card_desc'    => $descricao,
                    'card_limite'  => $limite,
                    'modificador'  => Auth::user()->user_id,
                    'dthr_ch'      => Carbon::now(),
                ]);

            return response()->json([
                'title' => 'Sucesso',
                'text'  => 'Cartão atualizado com sucesso.',
                'type'  => 'success',
            ]);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'title' => 'Registro não encontrado',
                'text'  => 'Cartão não localizado ou não pertence à empresa autenticada.',
                'type'  => 'error',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text'  => $th->getMessage(),
                'type'  => 'error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cliente = $this->getClienteForUserOrFail((int) $id, 'view');

        return response()->view('Multban.cliente.edit', compact(
            'cliente',
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $emp_id = $this->tenantManager->ensure();
        $userRole = Auth::user()->roles->pluck('name', 'name')->all();

        $status = ClienteStatus::all();
        $tipos = ClienteTipo::all();
        $cardTipos = CardTipo::all();
        $cardMod = CardMod::all();
        $cardCateg = CardCateg::all();
        $cliente = $this->getClienteForUserOrFail((int) $id, 'view');
        $canManageRelatedData = Auth::user()?->can('manageRelatedData', $cliente) ?? false;
        $compras = $this->loadComprasForCliente($cliente, $emp_id);
        $scoreEntries = $this->loadScoreEntriesForCliente($cliente);
        $cartoes = $this->loadCartoesForCliente($cliente, $emp_id, $canManageRelatedData);
        $prontuarios = $this->loadProntuariosForCliente($cliente, $emp_id, $canManageRelatedData);

        $convenios = TbDmConvenios::all();
        $estados = Estados::orderBy('estado_desc')->get();
        $cidades = Cidade::orderBy('cidade_desc')->get();
        $dbsysclient = DB::connection('dbsysclient');
        $users = User::join($dbsysclient->getDatabaseName() . '.tbdm_userfunc', 'tbsy_user.user_func', '=', 'tbdm_userfunc.user_func')
            ->where('user_func_grp', '=', 'consulta')->get();

        $canChangeStatus = false;
        foreach ($userRole as $key => $value) {

            if ($value == 'admin') {
                $canChangeStatus = true; // Se for usuário Admin, pode mudar o Status
            }
        }

        return response()->view('Multban.cliente.edit', compact(
            'cliente',
            'status',
            'tipos',
            'cardTipos',
            'cardMod',
            'cardCateg',
            'canChangeStatus',
            'convenios',
            'estados',
            'cidades',
            'users',
            'compras',
            'scoreEntries',
            'cartoes',
            'prontuarios',
            'canManageRelatedData',
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            $cliente = $this->getClienteForUserOrFail((int) $id, 'update');
            $input = $request->all();

            $input['cliente_doc'] = removerCNPJ($request->cliente_doc);
            $input['cliente_rendam'] = formatarTextoParaDecimal($request->cliente_rendam);
            $input['cliente_cel'] = removerMascaraTelefone($request->cliente_cel);
            $input['cliente_telfixo'] = removerMascaraTelefone($request->cliente_telfixo);
            $input['cliente_cel_s'] = $request->filled('cliente_cel_s')
                ? removerMascaraTelefone($request->cliente_cel_s)
                : $cliente->cliente_cel_s;
            $input['cliente_cep'] = removerMascaraCEP($request->cliente_cep);
            $input['cliente_cep_s'] = removerMascaraCEP($request->cliente_cep_s);

            $validator = Validator::make($input, $cliente->rules($id), $cliente->messages(), $cliente->attributes());

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $originalData = $cliente->getOriginal();
            $userRoles = Auth::user()->roles->pluck('name')->all();
            $canChangeStatus = in_array('admin', $userRoles, true);

            $cliente->cliente_tipo = $request->cliente_tipo;
            $cliente->convenio_id = $request->convenio_id;
            $cliente->carteirinha = $request->carteirinha;
            $cliente->cliente_dt_nasc = $this->normalizeDate($request->cliente_dt_nasc);
            $cliente->cliente_doc = $input['cliente_doc'];
            $cliente->cliente_rg = removerCNPJ($request->cliente_rg);
            $cliente->cliente_pasprt = $request->cliente_pasprt;
            if ($canChangeStatus) {
                $cliente->cliente_sts = $request->cliente_sts;
            }
            $cliente->cliente_nome = mb_strtoupper(rtrim($request->cliente_nome), 'UTF-8');
            $cliente->cliente_nm_alt = mb_strtoupper(rtrim($request->cliente_nm_alt), 'UTF-8');
            $cliente->cliente_nm_card = $request->cliente_nm_card;
            $cliente->cliente_email = $request->cliente_email;
            $cliente->cliente_email_s = $request->cliente_email_s;
            $cliente->cliente_cel = $input['cliente_cel'];
            $cliente->cliente_cel_s = $input['cliente_cel_s'];
            $cliente->cliente_telfixo = $input['cliente_telfixo'];
            $cliente->cliente_rendam = $input['cliente_rendam'];
            $cliente->cliente_rdam_s = $request->cliente_rdam_s;
            $cliente->cliente_dt_fech = $request->cliente_dt_fech;
            $cliente->cliente_cep = $input['cliente_cep'];
            $cliente->cliente_end = mb_strtoupper(rtrim($request->cliente_end), 'UTF-8');
            $cliente->cliente_endnum = $request->cliente_endnum;
            $cliente->cliente_endcmp = mb_strtoupper(rtrim($request->cliente_endcmp), 'UTF-8');
            $cliente->cliente_endbair = mb_strtoupper(rtrim($request->cliente_endbair), 'UTF-8');
            $cliente->cliente_endcid = $request->cliente_endcid;
            $cliente->cliente_endest = $request->cliente_endest;
            $cliente->cliente_endpais = $request->cliente_endpais;
            $cliente->cliente_cep_s = $input['cliente_cep_s'];
            $cliente->cliente_end_s = mb_strtoupper(rtrim($request->cliente_end_s), 'UTF-8');
            $cliente->cliente_endnum_s = $request->cliente_endnum_s;
            $cliente->cliente_endcmp_s = mb_strtoupper(rtrim($request->cliente_endcmp_s), 'UTF-8');
            $cliente->cliente_endbair_s = mb_strtoupper(rtrim($request->cliente_endbair_s), 'UTF-8');
            $cliente->cliente_endcid_s = $request->cliente_endcid_s;
            $cliente->cliente_endest_s = $request->cliente_endest_s;
            $cliente->cliente_endpais_s = $request->cliente_endpais_s;
            $cliente->cliente_score = $request->cliente_score;
            $cliente->cliente_lmt_sg = $request->cliente_lmt_sg;
            $cliente->modificador = Auth::user()->user_id;
            $cliente->dthr_ch = Carbon::now();

            $updatedAttributes = $cliente->getAttributes();
            foreach ($originalData as $key => $oldValue) {
                if (!array_key_exists($key, $updatedAttributes)) {
                    continue;
                }

                $newValue = $updatedAttributes[$key];
                if ($newValue == $oldValue || in_array($key, ['updated_at', 'created_at'], true)) {
                    continue;
                }

                $logAuditoria = new LogAuditoria;
                $logAuditoria->auddat = date('Y-m-d H:i:s');
                $logAuditoria->audusu = Auth::user()->username;
                $logAuditoria->audtar = 'Alterou o campo ' . $key;
                $logAuditoria->audarq = $cliente->getTable();
                $logAuditoria->audlan = $cliente->id;
                $logAuditoria->audant = $oldValue;
                $logAuditoria->auddep = $newValue;
                $logAuditoria->audnip = request()->ip();
                $logAuditoria->save();
            }

            $cliente->save();

            return response()->json([
                'message' => 'Cliente atualizado com sucesso.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            $cliente = $this->getClienteForUserOrFail((int) $id, 'delete');
            if ($cliente) {
                $cliente->cliente_sts = EmpresaStatusEnum::EXCLUIDO;
                $cliente->save();

                return response()->json([
                    'title' => 'Sucesso',
                    'text'  => 'Registro Excluído com sucesso!',
                    'type'  => 'success',
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text'  => 'Registro não encontrado!',
                'type'  => 'error',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message'   => $e->getMessage(),
            ], 500);
        }
    }

    public function inactive($id)
    {
        try {

            $cliente = $this->getClienteForUserOrFail((int) $id, 'updateStatus');
            if ($cliente) {
                $cliente->cliente_sts = EmpresaStatusEnum::INATIVO;
                $cliente->save();

                return response()->json([
                    'title' => 'Sucesso',
                    'text'  => 'Registro Inativado com sucesso!',
                    'type'  => 'success',
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text'  => 'Registro não encontrado!',
                'type'  => 'error',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'title' => 'Erro',
                'text'  => $e->getMessage(),
                'type'  => 'error',
            ], 500);
        }
    }

    public function active($id)
    {
        try {

            $cliente = $this->getClienteForUserOrFail((int) $id, 'updateStatus');

            if ($cliente) {
                $cliente->cliente_sts = EmpresaStatusEnum::ATIVO;
                $cliente->save();

                return response()->json([
                    'title' => 'Sucesso',
                    'text'  => 'Registro Ativado com sucesso!',
                    'type'  => 'success',
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text'  => 'Registro não encontrado!',
                'type'  => 'error',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'title' => 'Erro',
                'text'  => $e->getMessage(),
                'type'  => 'error',
            ], 500);
        }
    }

    // BUSCA DOS CLIENTES TANTO PELO NOME COMO PELO DOCUMENTO, SEMPRE RESPEITANDO A EMPRESA LOAGADA
    public function getClient(Request $request)
    {
        $parametro = $request->query('parametro', '');
        $parametro = str_replace(['.', '/', '-'], '', $parametro);
        $emp_id = $this->tenantManager->ensure();

        if (empty($parametro)) {
            return [
                'clientes' => [],
                'cartoes'  => [],
            ];
        }

        $query = Cliente::whereHas('clienteEmp', function ($q) use ($emp_id) {
            $q->where('emp_id', $emp_id);
        });

        if (is_numeric($parametro)) {
            $query->where('cliente_doc', 'LIKE', '%' . $parametro . '%');
        } else {
            $query->where('cliente_nome', 'LIKE', '%' . $parametro . '%');
        }

        $clientes = $query->select(
            DB::raw('cliente_id as id, cliente_id, cliente_doc, cliente_sts, cliente_dt_fech, UPPER(cliente_nome) as text')
        )->get();

        foreach ($clientes as $cliente) {
            $cliente->cartoes = ClienteCard::where('cliente_id', $cliente->cliente_id)
                ->where('emp_id', $emp_id)
                ->select(
                    'card_tp', 'card_mod', 'card_categ',
                    'card_desc', 'card_uuid', 'cliente_cardn',
                    'cliente_cardcv', 'card_saldo_vlr',
                    'card_limite', 'card_pts_part', 'card_pts_fraq',
                    'card_pts_mult', 'card_pts_cash', 'card_sts'
                )
                ->get()
                ->map(function ($cartao) {
                    // Busca os textos descritivos usando os models
                    $tp = CardTipo::where('card_tp', $cartao->card_tp)->first();
                    $mod = CardMod::where('card_mod', $cartao->card_mod)->first();
                    $sts = CardStatus::where('card_sts', $cartao->card_sts)->first();
                    $cartao->card_tp_desc = $tp ? $tp->card_tp_desc : $cartao->card_tp;
                    $cartao->card_mod_desc = $mod ? $mod->card_mod_desc : $cartao->card_mod;
                    $cartao->card_sts_desc = $sts ? $sts->card_sts_desc : $cartao->card_sts;

                    return $cartao;
                })
                ->toArray();

            // Soma dos pontos do cliente com base nos campos de pontuação
            $cliente->cliente_pts = ClienteCard::where('cliente_id', $cliente->cliente_id)
                ->where('emp_id', $emp_id)
                ->where('cliente_doc', $cliente->cliente_doc)
                ->get()
                ->sum(function ($cartao) {
                    return
                        floatval($cartao->card_pts_part) +
                        floatval($cartao->card_pts_fraq) +
                        floatval($cartao->card_pts_mult) +
                        floatval($cartao->card_pts_cash);
                });

        }

        return [
            'clientes' => $clientes->toArray(),
        ];
    }

    public function storeProntuario(Request $request)
    {
        try {

            $cliente = $this->getClienteForUserOrFail((int) $request->input('cliente_id'), 'manageRelatedData');
            $empresaId = $this->authenticatedEmpresaId((int) $request->input('emp_id'));

            $prontuario = new ClienteProntuario;
            $prontuario->cliente_id = $cliente->cliente_id;
            $prontuario->protocolo_tp = 1;
            $prontuario->protocolo_dt = Carbon::now();
            $prontuario->cliente_doc = $cliente->cliente_doc;
            $prontuario->emp_id = $empresaId;
            $prontuario->user_id = Auth::user()->user_id;
            $prontuario->criador = Auth::user()->user_id;
            $prontuario->dthr_cr = Carbon::now();
            $prontuario->modificador = Auth::user()->user_id;
            $prontuario->dthr_ch = Carbon::now();
            $prontuario->texto_prt = rtrim($request->input('texto_prt'));
            $prontuario->texto_rec = rtrim($request->input('texto_rec'));
            $prontuario->texto_anm = rtrim($request->input('texto_anm'));
            $prontuario->texto_prv = rtrim($request->input('texto_prv'));
            $prontuario->texto_exm = rtrim($request->input('texto_exm'));
            $prontuario->texto_atd = rtrim($request->input('texto_atd'));

            $prontuario->save();

            if ($request->hasFile('fotoUpload')) {
                $images = $request->file('fotoUpload');

                foreach ($images as $image) {
                    $directory = Storage::disk('public')->path('prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/fotos/protocolo_' . $prontuario->protocolo . '/images');
                    if (! file_exists($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    $directoryThumbs = Storage::disk('public')->path('prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/fotos/protocolo_' . $prontuario->protocolo . '/thumbnails');
                    if (! file_exists($directoryThumbs)) {
                        mkdir($directoryThumbs, 0777, true);
                    }

                    $image_name = time() . '_' . uniqid() . '.webp';

                    $resize_image = Image::read($image);

                    $resize_image->toWebp();
                    $resize_image->scale(height: 500);

                    $resize_image->save(Storage::disk('public')->path('prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/fotos/protocolo_' . $prontuario->protocolo) . '/images/' . $image_name);

                    $thumbnails = Image::read($image);
                    $thumbnails->toWebp();
                    $thumbnails->crop(width: 100, height: 100, position: 'center')
                        ->scale(width: 100, height: 100);
                    $thumbnails->save(Storage::disk('public')->path('prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/fotos/protocolo_' . $prontuario->protocolo) . '/thumbnails/' . $image_name);

                    // $destinationPath = public_path('/storage/images/logos/');

                    // $image->move($destinationPath, $image_name);
                }
                $prontuario = ClienteProntuario::find($prontuario->protocolo);
                $prontuario->anexo = 'x';
                $prontuario->foto_anexo_path = 'prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/fotos/protocolo_' . $prontuario->protocolo . '/thumbnails/';
                $prontuario->save();
            }

            if ($request->hasFile('fileDoc')) {
                $files = $request->file('fileDoc');
                foreach ($files as $file) {

                    $directory = Storage::disk('public')->path('prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/docs/protocolo_' . $prontuario->protocolo);
                    if (! file_exists($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    $file->move($directory, $filename);
                }

                $prontuario = ClienteProntuario::find($prontuario->protocolo);
                $prontuario->anexo = 'x';
                $prontuario->doc_anexo_path = 'prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/docs/protocolo_' . $prontuario->protocolo . '/';

                $prontuario->save();
            }

            return response()->json([
                'title' => 'Sucesso',
                'type'  => 'success',
                'text'  => 'Prontuário salvo com sucesso.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title'   => 'Erro',
                'message' => $th->getMessage(),
                'type'    => 'error',
            ], 500);
        }
    }

    public function updateProntuario(Request $request, $id)
    {
        try {

            $cliente = $this->getClienteForUserOrFail((int) $request->input('cliente_id'), 'manageRelatedData');
            $empresaId = $this->authenticatedEmpresaId();

            $prontuario = ClienteProntuario::where('id', $id)
                ->where('cliente_id', $cliente->cliente_id)
                ->where('emp_id', $empresaId)
                ->firstOrFail();

            $prontuario->cliente_id = $cliente->cliente_id;
            $prontuario->modificador = Auth::user()->user_id;
            $prontuario->dthr_ch = Carbon::now();
            $prontuario->texto_prt = rtrim($request->input('texto_prt'));
            $prontuario->texto_rec = rtrim($request->input('texto_rec'));
            $prontuario->texto_anm = rtrim($request->input('texto_anm'));
            $prontuario->texto_prv = rtrim($request->input('texto_prv'));
            $prontuario->texto_exm = rtrim($request->input('texto_exm'));
            $prontuario->texto_atd = rtrim($request->input('texto_atd'));

            if ($request->hasFile('fotoUpload')) {
                $images = $request->file('fotoUpload');

                foreach ($images as $image) {
                    $directory = Storage::disk('public')->path('prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/fotos/protocolo_' . $prontuario->protocolo . '/images');
                    if (! file_exists($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    $directoryThumbs = Storage::disk('public')->path('prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/fotos/protocolo_' . $prontuario->protocolo . '/thumbnails');
                    if (! file_exists($directoryThumbs)) {
                        mkdir($directoryThumbs, 0777, true);
                    }

                    $image_name = time() . '_' . uniqid() . '.webp';

                    $resize_image = Image::read($image);

                    $resize_image->toWebp();
                    $resize_image->scale(height: 500);

                    $resize_image->save(Storage::disk('public')->path('prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/fotos/protocolo_' . $prontuario->protocolo) . '/images/' . $image_name);

                    $thumbnails = Image::read($image);
                    $thumbnails->toWebp();
                    $thumbnails->crop(width: 100, height: 100, position: 'center')
                        ->scale(width: 100, height: 100);
                    $thumbnails->save(Storage::disk('public')->path('prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/fotos/protocolo_' . $prontuario->protocolo) . '/thumbnails/' . $image_name);

                    // $destinationPath = public_path('/storage/images/logos/');

                    // $image->move($destinationPath, $image_name);
                }

                $prontuario = ClienteProntuario::find($prontuario->protocolo);
                $prontuario->anexo = 'x';
                $prontuario->foto_anexo_path = 'prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/fotos/protocolo_' . $prontuario->protocolo . '/thumbnails/';
            } else {
                $prontuario->foto_anexo_path = null;
            }

            if ($request->hasFile('fileDoc')) {
                $files = $request->file('fileDoc');
                foreach ($files as $file) {

                    $directory = Storage::disk('public')->path('prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/docs/protocolo_' . $prontuario->protocolo);
                    if (! file_exists($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    $file->move($directory, $filename);
                }

                $prontuario = ClienteProntuario::find($prontuario->protocolo);
                $prontuario->anexo = 'x';
                $prontuario->doc_anexo_path = 'prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/docs/protocolo_' . $prontuario->protocolo . '/';
            } else {
                $prontuario->doc_anexo_path = null;
            }

            if (! $request->hasFile('fotoUpload') && ! $request->hasFile('fileDoc')) {
                $prontuario->anexo = null;
            }

            $prontuario->save();

            return response()->json([
                'title' => 'OK',
                'type'  => 'success',
                'text'  => 'Prontuário atualizado com sucesso.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title'   => 'Erro',
                'message' => $th->getMessage(),
                'type'    => 'error',
            ], 500);
        }
    }

    public function postObterGridPesquisaProtocolo(Request $request)
    {
        try {

            $empresaId = $this->authenticatedEmpresaId();
            $prontuariosQuery = ClienteProntuario::where('emp_id', $empresaId);

            if ($request->filled('cliente_id')) {
                $cliente = $this->getClienteForUserOrFail((int) $request->cliente_id, 'manageRelatedData');
                $prontuariosQuery->where('cliente_id', $cliente->cliente_id);
            }

            if (! empty($request->data_de) && ! empty($request->data_ate)) {
                $dataDe = Carbon::createFromFormat('Y-m-d', $request->data_de);
                $dataAte = Carbon::createFromFormat('Y-m-d', $request->data_ate);

                if ($dataDe->greaterThan($dataAte)) {
                    return response()->json([
                        'title'   => 'Erro',
                        'type'    => 'error',
                        'message' => "Data 'De:' não pode ser maior que a data 'Até:'.",
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $prontuariosQuery
                    ->whereDate('protocolo_dt', '>=', $dataDe->toDateString())
                    ->whereDate('protocolo_dt', '<=', $dataAte->toDateString());
            }

            if ($request->filled('protocolo')) {
                $prontuariosQuery->where('protocolo', $request->protocolo);
            }

            if ($request->filled('user_id')) {
                $prontuariosQuery->where('user_id', $request->user_id);
            }

            $prontuarios = $prontuariosQuery->get();

            return DataTables::of($prontuarios)
                ->addIndexColumn()
                ->editColumn('anexo', function ($row) {
                    if ($row->anexo == 'x') {
                        return '<span class="text-center ml-3"><i class="fas fa-paperclip"></i></span>';
                    }

                    return '<span class="badge badge-secondary">Sem Anexo</span>';
                })
                ->addColumn('medico', function ($row) {
                    if (! empty($row->user)) {
                        return $row->user->user_name;
                    }

                    return 'Sem Médico';
                })
                ->addColumn('crm_medico', function ($row) {
                    if (! empty($row->user)) {
                        return $row->user->user_crm;
                    }

                    return 'Sem CRM';
                })
                ->addColumn('images', function ($row) {
                    return Storage::disk('public')->allFiles('prontuarios/empresa_' . $row->emp_id . '/client_' . $row->cliente_id . '/fotos/protocolo_' . $row->protocolo . '/thumbnails');
                })
                ->addColumn('docs', function ($row) {
                    return Storage::disk('public')->allFiles('prontuarios/empresa_' . $row->emp_id . '/client_' . $row->cliente_id . '/docs/protocolo_' . $row->protocolo);
                })
                ->editColumn('protocolo', function ($row) {
                    return str_pad($row->protocolo, 12, '0', STR_PAD_LEFT);
                })
                ->editColumn('protocolo_tp', function ($row) {
                    $badge = '';
                    if (! empty($row->tipo)) {

                        switch ($row->tipo->protocolo_tp) {
                            case 1:
                                $badge = '<span class="badge badge-info">' . $row->tipo->prt_tp_desc . '</span>';
                                break;
                            case 2:
                            case 3:
                            case 4:
                                $badge = '<span class="badge badge-success">' . $row->tipo->prt_tp_desc . '</span>';
                                break;
                        }
                    }

                    return $badge;
                })
                ->rawColumns(['anexo', 'medico', 'action', 'protocolo_tp'])
                ->make(true);
        } catch (\Throwable $th) {
            return response()->json([
                'title'   => 'Erro',
                'type'    => 'error',
                'message' => $th->getMessage() . ' ' . $th->getLine() . ' ' . $th->getFile(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function postObterGridPesquisa(Request $request)
    {
        if (! Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, 'Usuário não autenticado...');
        }

        $empresaId = $this->tenantManager->ensure();

        $clientesQuery = Cliente::query()
            ->join('tbdm_clientes_emp', 'tbdm_clientes_geral.cliente_id', '=', 'tbdm_clientes_emp.cliente_id')
            ->select('tbdm_clientes_geral.*', 'tbdm_clientes_emp.emp_id')
            ->where('tbdm_clientes_emp.emp_id', $empresaId);

        if ($request->filled('cliente_sts')) {
            $clientesQuery->where('tbdm_clientes_geral.cliente_sts', $request->cliente_sts);
        }

        if ($request->filled('cliente_tipo')) {
            $clientesQuery->where('tbdm_clientes_geral.cliente_tipo', $request->cliente_tipo);
        }

        if ($request->filled('cliente_id')) {
            if (is_numeric($request->cliente_id)) {
                $clientesQuery->where('tbdm_clientes_geral.cliente_id', $request->cliente_id);
            } else {
                $clientesQuery->where('tbdm_clientes_geral.cliente_nome', 'like', '%' . $request->cliente_id . '%');
            }
        }

        if ($request->filled('cliente_doc')) {
            $clientesQuery->where('tbdm_clientes_geral.cliente_doc', removerCNPJ($request->cliente_doc));
        }

        if ($request->filled('nome_multban')) {
            $empresaMatches = Empresa::where('emp_id', $empresaId)
                ->where('emp_nmult', 'like', '%' . $request->nome_multban . '%')
                ->exists();

            if (! $empresaMatches) {
                $clientesQuery->whereRaw('1 = 0');
            }
        }

        $data = $clientesQuery->get();

        $this->permissions = $this->resolvePermissions();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';
                if (in_array('cliente.edit', $this->permissions)) {
                    $btn .= '<a href="cliente/' . $row->cliente_id . '/alterar" class="btn btn-primary btn-sm mr-1" title="Editar"><i class="fas fa-edit"></i></a>';
                }

                $disabled = '';
                if ($row->status->cliente_sts == EmpresaStatusEnum::ATIVO) {
                    $disabled = 'disabled';
                }

                $btn .= '<button href="#" class="btn btn-primary btn-sm mr-1" ' . $disabled . ' id="active_grid_id" data-url="cliente" data-id="' . $row->cliente_id . '" title="Ativar"><i class="far fa-check-circle"></i></button>';

                $disabled = '';
                if ($row->status->cliente_sts == EmpresaStatusEnum::INATIVO) {
                    $disabled = 'disabled';
                }

                $btn .= '<button href="#" class="btn btn-primary btn-sm mr-1" ' . $disabled . ' id="inactive_grid_id" data-url="cliente" data-id="' . $row->cliente_id . '" title="Inativar"><i class="fas fa-ban"></i></button>';

                if (in_array('cliente.destroy', $this->permissions)) {
                    $disabled = '';
                    if ($row->status->cliente_sts == EmpresaStatusEnum::EXCLUIDO) {
                        $disabled = 'disabled';
                    }
                    $btn .= '<button href="#" class="btn btn-sm btn-primary mr-1" ' . $disabled . ' id="delete_grid_id" data-url="cliente" data-id="' . $row->cliente_id . '" title="Excluir"><i class="far fa-trash-alt"></i></button>';
                }
                $btn .= '';

                return $btn;
            })->editColumn('cliente_cel', function ($row) {
                $badge = formatarTelefone($row->cliente_cel);

                return $badge;
            })->editColumn('cliente_doc', function ($row) {
                $badge = strlen($row->cliente_doc) == 18 ? formatarCNPJ($row->cliente_doc) : formatarCPF($row->cliente_doc);

                return $badge;
            })->editColumn('cliente_tipo', function ($row) {
                $badge = '';
                if (! empty($row->tipo)) {

                    switch ($row->tipo->cliente_tipo) {
                        case 1:
                            $badge = '<span class="badge badge-info">' . $row->tipo->cliente_tipo_desc . '</span>';
                            break;
                        case 2:
                        case 3:
                        case 4:
                            $badge = '<span class="badge badge-success">' . $row->tipo->cliente_tipo_desc . '</span>';
                            break;
                    }
                }

                return $badge;
            })->editColumn('cliente_sts', function ($row) {
                $badge = '';
                if (! empty($row->status)) {

                    switch ($row->status->cliente_sts) {

                        case 'AT':
                            $badge = '<span class="badge badge-success">' . $row->status->cliente_sts_desc . '</span>';
                            break;
                        case 'NA':
                            $badge = '<span class="badge badge-warning">' . $row->status->cliente_sts_desc . '</span>';
                            break;
                        case 'IN':
                        case 'EX':
                        case 'BL':
                            $badge = '<span class="badge badge-danger">' . $row->status->cliente_sts_desc . '</span>';
                            break;
                    }
                }

                return $badge;
            })->editColumn('cliente_id', function ($row) {
                // $id = str_pad($row->cliente_id, 5, "0", STR_PAD_LEFT);
                return $row->cliente_id;
            })
            ->rawColumns(['action', 'cliente_doc', 'cliente_sts', 'cliente_tipo'])
            ->make(true);
    }

    // Cartão
    public function storeCard(Request $request)
    {
        try {

            $empresaId = $this->authenticatedEmpresaId((int) $request->input('emp_id'));

            if (empty($request->cliente_id)) {
                return response()->json([
                    'message_type' => 'Cliente ainda não cadatrado, favor cadastrar o cliente antes de criar o cartão.',
                    'message'      => [],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validator = Validator::make($request->all(), [
                'emp_id'      => 'required',
                'cliente_id'  => 'required',
                'card_tp'     => 'required',
                'card_mod'    => 'required',
                'card_categ'  => 'required',
                'card_desc'   => 'required',
                'card_limite' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $cardPassword = $request->card_pass;
            $cardPassword = $cardPassword === null ? null : trim((string) $cardPassword);

            if ($cardPassword !== null && $cardPassword !== '') {
                if (! preg_match('/^\d{4}$/', $cardPassword)) {
                    return response()->json([
                        'message' => [
                            'card_pass' => ['A senha do cartão deve conter exatamente 4 dígitos numéricos.'],
                        ],
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                if ($this->isWeakCardPassword($cardPassword)) {
                    return response()->json([
                        'message' => [
                            'card_pass' => ['Utilize uma combinação menos previsível (evite sequências e dígitos repetidos).'],
                        ],
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

            DB::beginTransaction();

            $empresa = Empresa::find($empresaId);
            $cnpj = '';
            if ($empresa) {
                $cnpj = substr(removerCNPJ($empresa->emp_cnpj), 0, 7);
            }

            $cliente = $this->getClienteForUserOrFail((int) $request->cliente_id, 'manageRelatedData');
            $cpf = '';
            if ($cliente) {
                $cpf = substr(removerCNPJ($cliente->cliente_doc), 0, 7);
            }

            // Exemplo: '4' para Visa, '5' para Mastercard, '3' para American Express
            // Você pode ajustar o prefixo conforme necessário para outros tipos de cartões
            // Gera um número de cartão de crédito com 16 dígitos
            $numeroCartao = $this->gerarNumeroCartaoCredito($cnpj, $cpf, $request->card_mod, $request->card_tp);

            if ($cardPassword === null || $cardPassword === '') {
                $cardPassword = (string) random_int(100000, 999999);
            }
            $cardPasswordHash = Hash::make($cardPassword);

            $dadosCartao = [
                'emp_id'           => $empresaId,
                'cliente_id'       => $cliente->cliente_id,
                'cliente_doc'      => $cliente->cliente_doc,
                'cliente_pasprt'   => $cliente->cliente_pasprt,
                'card_uuid'        => Str::uuid()->toString(),
                'cliente_cardn'    => $numeroCartao,
                'cliente_cardcv'   => random_int(100, 999),
                'card_sts'         => 'AT',
                'card_tp'          => $request->card_tp,
                'card_mod'         => $request->card_mod,
                'card_categ'       => $request->card_categ,
                'card_desc'        => mb_strtoupper(rtrim($request->card_desc), 'UTF-8'),
                'card_saldo_vlr'   => formatarTextoParaDecimal($request->card_limite),
                'card_limite'      => formatarTextoParaDecimal($request->card_limite),
                'card_pts_part'    => 0,
                'card_pts_fraq'    => 0,
                'card_pts_mult'    => 0,
                'card_pts_cash'    => 0,
                'card_pass'        => $cardPasswordHash,
                'criador'          => Auth::user()->user_id,
                'dthr_cr'          => Carbon::now(),
                'modificador'      => Auth::user()->user_id,
                'dthr_ch'          => Carbon::now(),
            ];

            $data = DB::connection('dbsysclient')->table('tbdm_clientes_card')->insert($dadosCartao);

            if ($data) {
                DB::commit();

                return response()->json([
                    'title' => 'Sucesso',
                    'text'  => 'Registro criado com sucesso!',
                    'type'  => 'success',
                    'data'  => $data,
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'title' => 'Erro',
                'text'  => $th->getMessage(),
                'type'  => 'error',
            ], 500);
        }
    }

    /**
     * Gera um número de cartão de crédito com base no CNPJ da empresa, CPF do cliente e tipo de cartão,
     * completando com dígitos aleatórios e finalizando com o dígito de Luhn.
     *
     * @param  string  $cnpj
     * @param  string  $cpf
     * @param  string  $card_mod
     * @param  int  $length
     * @return string
     */
    private function gerarNumeroCartaoCredito($cnpj, $cpf, $card_mod, $card_tp, $length = 16)
    {
        // Remove caracteres não numéricos
        $cnpj = preg_replace('/\D/', '', $cnpj);
        $cpf = preg_replace('/\D/', '', $cpf);

        // Identifica o tipo de cartão pelo card_mod e card_tp
        $tipo = '1'; // Padrão para POS

        if ($card_tp == 'PRE') {

            $tipo = '2';
        }

        if ($card_tp == 'POS' && $card_mod == 'CRDT') {

            $tipo = '1';
        }

        if ($card_mod == 'GIFT') {

            $tipo = '3';
        }

        if ($card_mod == 'FIDL') {

            $tipo = '4';
        }

        // Usa o prefixo: 61 + 7 primeiros do CNPJ + 7 primeiros do CPF + tipo
        $prefix = '61' . substr($cnpj, 0, 6) . substr($cpf, 0, 6) . $tipo;

        // Completa até o penúltimo dígito com números aleatórios
        while (strlen($prefix) < ($length - 1)) {
            $prefix .= mt_rand(0, 9);
        }

        // Calcula o dígito verificador (Luhn)
        $soma = 0;
        $invertido = strrev($prefix);
        for ($i = 0; $i < strlen($invertido); $i++) {
            $digito = intval($invertido[$i]);
            if ($i % 2 == 0) {
                $digito *= 2;
                if ($digito > 9) {
                    $digito -= 9;
                }
            }
            $soma += $digito;
        }
        $digitoVerificador = (10 - ($soma % 10)) % 10;

        return $prefix . $digitoVerificador;
    }

    public function updateCard(Request $request)
    {
        try {

            $empresaId = $this->authenticatedEmpresaId((int) $request->input('emp_id'));

            $validator = Validator::make($request->all(), [
                'tabela'  => 'required',
                'campo'   => 'required',
                'emp_id'  => 'required',
                'user_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $data = DB::connection('dbsysclient')->table('tbcf_config_wf')
                ->where('tabela', '=', $request->tabela)
                ->where('campo', '=', $request->campo)
                ->where('user_id', '=', $request->user_id)
                ->where('emp_id', '=', $empresaId)->update([
                    'tabela'  => $request->tabela,
                    'campo'   => $request->campo,
                    'user_id' => $request->user_id,
                    'emp_id'  => $empresaId,
                ]);

            return response()->json([
                'title' => 'Sucesso',
                'text'  => 'Work Flow atualizado com sucesso!',
                'type'  => 'success',
                'data'  => $data,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text'  => $th->getMessage(),
                'type'  => 'error',
            ], 500);
        }
    }

    public function editCard(Request $request, $emp_id)
    {
        try {

            $empresaId = $this->authenticatedEmpresaId((int) ($request->input('emp_id') ?? $emp_id));

            $data = DB::connection('dbsysclient')->table('tbcf_config_wf')
                ->where('tabela', '=', $request->tabela)
                ->where('campo', '=', $request->campo)
                ->where('emp_id', '=', $empresaId)->first();

            $columnsList = DB::connection('dbsysclient')->getSchemaBuilder()->getColumnListing($request->tabela);
            $columns = [];

            foreach ($columnsList as $key => $col) {
                $columns[] = ['id' => $col, 'text' => $col];
            }

            if ($data) {
                return response()->json([
                    'title'   => 'Sucesso',
                    'text'    => 'Resposta obtida com sucesso!',
                    'type'    => 'success',
                    'data'    => $data,
                    'columns' => $columns,
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text'  => 'Registro não encontrado!',
                'type'  => 'error',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text'  => $th->getMessage(),
                'type'  => 'error',
            ], 500);
        }
    }

    public function destroyCard(Request $request, $emp_id)
    {
        try {

            $empresaId = $this->authenticatedEmpresaId((int) ($request->input('emp_id') ?? $emp_id));

            $data = DB::connection('dbsysclient')->table('tbcf_config_wf')
                ->where('tabela', '=', $request->tabela)
                ->where('campo', '=', $request->campo)
                ->where('emp_id', '=', $empresaId)->delete();

            if ($data) {
                return response()->json([
                    'title'        => 'Sucesso',
                    'text'         => 'Registro deletado com sucesso!',
                    'type'         => 'success',
                    'btnPesquisar' => 'btnPesquisarWf',
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text'  => 'Registro não encontrado!',
                'type'  => 'error',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text'  => $th->getMessage(),
                'type'  => 'error',
            ], 500);
        }
    }

    public function getObterGridPesquisaCard(Request $request)
    {

        if (! Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, 'Usuário não autenticado...');
        }

        $empresaId = $this->authenticatedEmpresaId(
            $request->filled('emp_id') ? (int) $request->input('emp_id') : null
        );

        $clienteId = (int) $request->input('cliente_id');

        if ($clienteId <= 0) {
            return DataTables::of(collect())->make(true);
        }

        // Garantir que o cliente pertence à empresa autenticada antes de carregar os cartões
        $this->getClienteForUserOrFail($clienteId, 'view');

        $data = DB::connection('dbsysclient')
            ->table('tbdm_clientes_card')
            ->where('emp_id', $empresaId)
            ->where('cliente_id', $clienteId)
            ->get();

        $this->permissions = $this->resolvePermissions();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';

                if (in_array('cliente.edit', $this->permissions)) {

                    $maskedCardNumber = formatarCartaoCredito(Str::mask($row->cliente_cardn, '*', 0, -4));
                    $resetDisabled = ($row->card_sts === 'EX') ? 'disabled' : '';
                    $btn .= '<button type="button" class="btn btn-sm btn-primary mr-1 btn-reset-card-password" ';
                    $btn .= 'data-emp-id="' . $row->emp_id . '" ';
                    $btn .= 'data-uuid="' . e($row->card_uuid) . '" ';
                    $btn .= 'data-card-label="' . e($maskedCardNumber) . '" ';
                    $btn .= $resetDisabled . ' title="Resetar Senha"><i class="fas fa-key"></i></button>';
                }

                $editDisabled = ($row->card_sts === 'EX') ? 'disabled' : '';
                $btn .= '<button type="button" class="btn btn-sm btn-primary mr-1 btn-edit-card" ';
                $btn .= 'data-emp-id="' . $row->emp_id . '" ';
                $btn .= 'data-uuid="' . e($row->card_uuid) . '" ';
                $btn .= 'data-card-label="' . e($maskedCardNumber) . '" ';
                $btn .= 'data-current-status="' . e($row->card_sts ?? '') . '" ';
                $btn .= $editDisabled . ' title="Editar"><i class="fas fa-edit"></i></button>';

                $maskedCardNumber = formatarCartaoCredito(Str::mask($row->cliente_cardn, '*', 0, -4));
                $activateDisabled = ($row->card_sts === 'AT' || $row->card_sts === 'EX') ? 'disabled' : '';
                $btn .= '<button type="button" class="btn btn-sm btn-primary mr-1 btn-activate-card" ';
                $btn .= 'data-emp-id="' . $row->emp_id . '" ';
                $btn .= 'data-uuid="' . e($row->card_uuid) . '" ';
                $btn .= 'data-card-label="' . e($maskedCardNumber) . '" ';
                $btn .= 'data-current-status="' . e($row->card_sts ?? '') . '" ';
                $btn .= $activateDisabled . ' title="Ativar"><i class="far fa-check-circle"></i></button>';

                $blockDisabled = ($row->card_sts === 'BL' || $row->card_sts === 'EX') ? 'disabled' : '';
                $btn .= '<button type="button" class="btn btn-sm btn-primary mr-1 btn-block-card" ';
                $btn .= 'data-emp-id="' . $row->emp_id . '" ';
                $btn .= 'data-uuid="' . e($row->card_uuid) . '" ';
                $btn .= 'data-card-label="' . e($maskedCardNumber) . '" ';
                $btn .= 'data-current-status="' . e($row->card_sts ?? '') . '" ';
                $btn .= $blockDisabled . ' title="Bloquear"><i class="fas fa-ban"></i></button>';

                $deleteDisabled = ($row->card_sts === 'EX') ? 'disabled' : '';
                $btn .= '<button type="button" class="btn btn-sm btn-primary mr-1 btn-delete-card" ';
                $btn .= 'data-emp-id="' . $row->emp_id . '" ';
                $btn .= 'data-uuid="' . e($row->card_uuid) . '" ';
                $btn .= 'data-card-label="' . e($maskedCardNumber) . '" ';
                $btn .= 'data-current-status="' . e($row->card_sts ?? '') . '" ';
                $btn .= $deleteDisabled . ' title="Excluir"><i class="far fa-trash-alt"></i></button>';

                return $btn;
            })->editColumn('card_sts', function ($row) {
                $badge = '';
                if (! empty($row->card_sts)) {
                    $statusStr = '';
                    $status = CardStatus::where('card_sts', $row->card_sts)->first();
                    if ($status) {
                        $statusStr = $status->card_sts_desc;
                    } else {
                        $statusStr = 'Desconhecido';
                    }

                    switch ($row->card_sts) {
                        case 'AT':
                            $badge = '<span class="badge badge-success">' . $statusStr . '</span>';
                            break;
                        case 'IN':
                        case 'EX':
                        case 'BL':
                            $badge = '<span class="badge badge-danger">' . $statusStr . '</span>';
                            break;
                    }
                }

                return $badge;
            })->editColumn('card_tp', function ($row) {
                return $row->card_tp == 'PRE' ? 'Pré-pago' : 'Pós-pago';
            })->editColumn('card_mod', function ($row) {
                return $row->card_mod == 'CRDT' ? 'Crédito' : ($row->card_mod == 'DEBT' ? 'Débito' : ($row->card_mod == 'GIFT' ? 'Gift Card' : 'Fidelidade'));
            })->editColumn('cliente_cardn', function ($row) {
                return formatarCartaoCredito(Str::mask($row->cliente_cardn, '*', 0, -4));
            })->addColumn('empresa', function ($row) {
                $empresa = Empresa::find($row->emp_id);

                return $empresa ? $empresa->emp_nmult : 'Empresa não encontrada';
            })->editColumn('card_limite', function ($row) {
                return formatarDecimalParaTexto($row->card_limite);
            })->editColumn('card_saldo_vlr', function ($row) {
                return formatarDecimalParaTexto($row->card_saldo_vlr);
            })->editColumn('card_saldo_pts', function ($row) {
                $total = ($row->card_pts_part ?? 0)
                    + ($row->card_pts_fraq ?? 0)
                    + ($row->card_pts_mult ?? 0)
                    + ($row->card_pts_cash ?? 0);

                return formatarDecimalParaTexto($total);
            })->editColumn('card_desc', function ($row) {
                return mb_strtoupper(rtrim($row->card_desc), 'UTF-8');
            })->editColumn('card_categ', function ($row) {
                $badge = '';
                if (! empty($row->card_categ)) {
                    $badge = CardCateg::where('card_categ', $row->card_categ)->first();
                    if ($badge) {
                        return '<span class="badge badge-info">' . $badge->card_categ_desc . '</span>';
                    }
                }

                return $badge;
            })
            ->rawColumns(['action', 'card_sts', 'card_categ'])
            ->make(true);
    }
}
