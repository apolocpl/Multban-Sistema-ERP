<?php

namespace App\Http\Controllers\Multban\Cliente;

use App\Enums\EmpresaStatusEnum;
use App\Enums\EstoqramEnum;
use App\Enums\FiltrosEnum;
use App\Http\Controllers\Controller;
use App\Models\Multban\Auditoria\LogAuditoria;
use App\Models\Multban\Cliente\CardCateg;
use App\Models\Multban\Cliente\CardMod;
use App\Models\Multban\Cliente\CardStatus;
use App\Models\Multban\Cliente\CardTipo;
use App\Models\Multban\Cliente\Cliente;
use App\Models\Multban\Cliente\ClienteCard;
use App\Models\Multban\Cliente\ClienteProntuario;
use App\Models\Multban\Cliente\ClienteStatus;
use App\Models\Multban\Cliente\ClienteTipo;
use App\Models\Multban\Cliente\Endereco\Cadasest;
use App\Models\Multban\Cliente\Endereco\Cadasmun;
use App\Models\Multban\Cliente\Endereco\CadasPais;
use App\Models\Multban\DadosMestre\TbDmConvenios;
use App\Models\Multban\Empresa\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;
use Intervention\Image\Laravel\Facades\Image;
use Laravel\Pail\ValueObjects\Origin\Console;
use Illuminate\Support\Facades\Log;

class ClienteController extends Controller
{
    private $permissions;
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

        return response()->view('Multban.cliente.index', compact('status', 'tipos', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $emp_id = Auth::user()->emp_id;
        $userRole = Auth::user()->roles->pluck('name', 'name')->all();

        $status = ClienteStatus::all();
        $tipos = ClienteTipo::all();
        $cardTipos = CardTipo::all();
        $cardMod = CardMod::all();
        $cardCateg = CardCateg::all();
        $cliente = new Cliente();
        $convenios = TbDmConvenios::all();

        $canChangeStatus = false;
        foreach ($userRole as $key => $value) {

            if ($value == 'admin') {
                $canChangeStatus = true; //Se for usuário Admin, pode mudar o Status
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
            'convenios'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {


            $emp_id = Auth::user()->emp_id;

            $userRole = Auth::user()->roles->pluck('name', 'name')->all();
            $canChangeStatus = false;
            foreach ($userRole as $key => $value) {

                if ($value == 'admin') {
                    $canChangeStatus = true; //Se for usuário Admin, pode mudar o Status
                }
            }

            $cliente = new Cliente();
            $input = $request->all();

            $input['cliente_nome'] = rtrim($request->cliente_nome);
            $input['cliente_doc'] = removerCNPJ($request->cliente_doc);
            $input['cliente_rendam'] = formatarTextoParaDecimal($request->cliente_rendam);

            $clienteChk = Cliente::where('cliente_doc', removerCNPJ($request->cliente_doc))->first();
            if ($clienteChk) {
                return response()->json([
                    'message_type' => 'Já existe um cliente cadastrado com esse CPF/CNPJ.',
                    'message' => ['cliente_doc' => ['Já existe um cliente cadastrado com esse CPF/CNPJ.']],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validator = Validator::make($input, $cliente->rules(), $cliente->messages(), $cliente->attributes());

            if ($validator->fails()) {
                return response()->json([
                    'message'   => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $cliente->cliente_tipo       = $request->cliente_tipo;
            $cliente->convenio_id        = $request->convenio_id;
            $cliente->carteirinha        = $request->carteirinha;
            $cliente->cliente_dt_nasc    = $request->cliente_dt_nasc ? Carbon::createFromFormat('d/m/Y', $request->cliente_dt_nasc)->format('Y-m-d') : null;
            $cliente->cliente_doc        = removerCNPJ($request->cliente_doc);
            $cliente->cliente_rg         = removerCNPJ($request->cliente_rg);
            $cliente->cliente_pasprt     = $request->cliente_pasprt;
            $cliente->cliente_sts        = !$canChangeStatus ? 'NA' : $request->cliente_sts; /*Cliente nasce com o status "Em Análise"*/
            $cliente->cliente_uuid       = Str::uuid()->toString();
            $cliente->cliente_nome       = mb_strtoupper(rtrim($request->cliente_nome), 'UTF-8');
            $cliente->cliente_nm_alt     = mb_strtoupper(rtrim($request->cliente_nm_alt), 'UTF-8');
            $cliente->cliente_nm_card    = $request->cliente_nm_card;
            $cliente->cliente_email      = $request->cliente_email;
            $cliente->cliente_email_s    = $request->cliente_email_s;
            $cliente->cliente_cel        = removerMascaraTelefone($request->cliente_cel);
            $cliente->cliente_cel_s      = removerMascaraTelefone($request->cliente_cel_s);
            $cliente->cliente_telfixo    = removerMascaraTelefone($request->cliente_telfixo);
            $cliente->cliente_rendam     = $input['cliente_rendam'];
            $cliente->cliente_rdam_s     = $request->cliente_rdam_s;
            $cliente->cliente_dt_fech    = $request->cliente_dt_fech;
            $cliente->cliente_cep        = removerMascaraCEP($request->cliente_cep);
            $cliente->cliente_end        = mb_strtoupper(rtrim($request->cliente_end), 'UTF-8');
            $cliente->cliente_endnum     = $request->cliente_endnum;
            $cliente->cliente_endcmp     = mb_strtoupper(rtrim($request->cliente_endcmp), 'UTF-8');
            $cliente->cliente_endbair    = mb_strtoupper(rtrim($request->cliente_endbair), 'UTF-8');
            $cliente->cliente_endcid     = $request->cliente_endcid;
            $cliente->cliente_endest     = $request->cliente_endest;
            $cliente->cliente_endpais    = $request->cliente_endpais;
            $cliente->cliente_cep_s      = removerMascaraCEP($request->cliente_cep_s);
            $cliente->cliente_end_s      = mb_strtoupper(rtrim($request->cliente_end_s), 'UTF-8');
            $cliente->cliente_endnum_s   = $request->cliente_endnum_s;
            $cliente->cliente_endcmp_s   = mb_strtoupper(rtrim($request->cliente_endcmp_s), 'UTF-8');
            $cliente->cliente_endbair_s  = mb_strtoupper(rtrim($request->cliente_endbair_s), 'UTF-8');
            $cliente->cliente_endcid_s   = $request->cliente_endcid_s;
            $cliente->cliente_endest_s   = $request->cliente_endest_s;
            $cliente->cliente_endpais_s  = $request->cliente_endpais_s;
            $cliente->cliente_score      = $request->cliente_score;
            $cliente->cliente_lmt_sg     = $request->cliente_lmt_sg;
            $cliente->criador            = Auth::user()->user_id;
            $cliente->modificador        = Auth::user()->user_id;
            $cliente->dthr_cr            = Carbon::now();
            $cliente->dthr_ch            = Carbon::now();

            $cliente->save();

            $tbdm_clientes_emp = DB::connection('dbsysclient')->table('tbdm_clientes_emp')->insert([
                'emp_id' => $emp_id,
                'cliente_id' => $cliente->cliente_id,
                'cliente_uuid' => $cliente->cliente_uuid,
                'cliente_doc' => removerCNPJ($cliente->cliente_doc),
                'cliente_pasprt' => $cliente->cliente_pasprt,
                'cad_liberado' => '',
                'criador' => Auth::user()->user_id,
                'dthr_cr' => Carbon::now(),
                'modificador' => Auth::user()->user_id,
                'dthr_ch' => Carbon::now(),
            ]);

            //Auditoria

            $logAuditoria         = new LogAuditoria();
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
                'message'   => "Cliente " . str_pad($cliente->cliente_id, 5, "0", STR_PAD_LEFT) . " adicionado com sucesso.",
            ]);
        } catch (Exception | \Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message'   => $e->getMessage(),
            ], 500);
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
        $cliente = Cliente::findOrFail($id);

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
        $emp_id = Auth::user()->emp_id;
        $userRole = Auth::user()->roles->pluck('name', 'name')->all();

        $status = ClienteStatus::all();
        $tipos = ClienteTipo::all();
        $cardTipos = CardTipo::all();
        $cardMod = CardMod::all();
        $cardCateg = CardCateg::all();
        $cliente = Cliente::findOrFail($id);

        $convenios = TbDmConvenios::all();
        $prontuarios = ClienteProntuario::where('cliente_id', $id)->get();
        $dbsysclient = DB::connection('dbsysclient');
        $users = User::join($dbsysclient->getDatabaseName() . '.tbdm_userfunc', 'tbsy_user.user_func', '=', 'tbdm_userfunc.user_func')
            ->where('user_func_grp', '=', 'consulta')->get();

        $clienteProntuarios = DataTables::of($prontuarios)
            ->addIndexColumn()
            ->editColumn('anexo', function ($row) {
                if ($row->anexo == 'x') {
                    return '<span class="text-center ml-3"><i class="fas fa-paperclip"></i></span>';
                }
                return '<span class="badge badge-secondary">Sem Anexo</span>';
            })
            ->addColumn('medico', function ($row) {
                if (!empty($row->user)) {
                    return $row->user->user_name;
                }
                return '<button class="btn btn-sm btn-primary" onclick="editMedico(' . $row->id . ')">Editar</button>';
            })
            ->addColumn('crm_medico', function ($row) {
                if (!empty($row->user)) {
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
                return str_pad($row->protocolo, 12, "0", STR_PAD_LEFT);
            })
            ->editColumn('protocolo_tp', function ($row) {
                $badge = '';
                if (!empty($row->tipo)) {

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

        $canChangeStatus = false;
        foreach ($userRole as $key => $value) {

            if ($value == 'admin') {
                $canChangeStatus = true; //Se for usuário Admin, pode mudar o Status
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
            'clienteProntuarios',
            'users',
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            $cliente = Cliente::find($id);
            $input = $request->all();

            $input['cliente_doc'] = removerCNPJ($request->clicgc);
            $input['cliente_cel'] = removerMascaraTelefone($request->clicep);
            $input['cliente_telfixo'] = removerMascaraTelefone($request->clicep);
            $input['cliente_cel_s'] = removerMascaraTelefone($request->clicep);
            $input['cliente_cep'] = removerMascaraCEP($request->clicep);

            $validator = Validator::make($input, $cliente->rules($id), $cliente->messages(), $cliente->attributes());

            if ($validator->fails()) {
                return response()->json([
                    'message'   => $validator->errors(),

                ], 422);
            }

            //Verifica se ouve mudanças nos campos, se sim grava na auditoria
            foreach ($input as $key => $value) {
                if (Arr::exists($cliente->toArray(), $key)) {
                    if ($cliente->$key != $value) {
                        if ($key == 'updated_at' || $key == 'created_at') {
                        } else {

                            $logAuditoria = new LogAuditoria();
                            $logAuditoria->auddat = date('Y-m-d H:i:s');
                            $logAuditoria->audusu = Auth::user()->username;
                            $logAuditoria->audtar = 'Alterou o campo ' . $key;
                            $logAuditoria->audarq = $cliente->getTable();
                            $logAuditoria->audlan = $cliente->id;
                            $logAuditoria->audant = $cliente->$key;
                            $logAuditoria->auddep = $value;
                            $logAuditoria->audnip = request()->ip();

                            $logAuditoria->save();
                        }
                    }
                }
            }

            $cliente->save();

            return response()->json([
                'message'   => 'Cliente atualizado com sucesso.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message'   => $e->getMessage(),
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

            $cliente = Cliente::find($id);
            if ($cliente) {
                $cliente->cliente_sts = EmpresaStatusEnum::EXCLUIDO;
                $cliente->save();
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro Excluído com sucesso!',
                    'type' => 'success'
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text' => 'Registro não encontrado!',
                'type' => 'error'
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

            $cliente = Cliente::find($id);
            if ($cliente) {
                $cliente->cliente_sts = EmpresaStatusEnum::INATIVO;
                $cliente->save();
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro Inativado com sucesso!',
                    'type' => 'success'
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text' => 'Registro não encontrado!',
                'type' => 'error'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'title' => 'Erro',
                'text' => $e->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    public function active($id)
    {
        try {

            $cliente = Cliente::find($id);

            if ($cliente) {
                $cliente->cliente_sts = EmpresaStatusEnum::ATIVO;
                $cliente->save();
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro Ativado com sucesso!',
                    'type' => 'success'
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text' => 'Registro não encontrado!',
                'type' => 'error'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'title' => 'Erro',
                'text' => $e->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    // BUSCA DOS CLIENTES TANTO PELO NOME COMO PELO DOCUMENTO, SEMPRE RESPEITANDO A EMPRESA LOAGADA
    public function getClient(Request $request)
    {
        $parametro = $request->query('parametro', '');
        $parametro = str_replace(['.', '/', '-'], '', $parametro);
        $emp_id = Auth::user()->emp_id;

        if (empty($parametro)) {
            return [
                'clientes' => [],
                'cartoes' => []
            ];
        }

        $query = Cliente::whereHas('clienteEmp', function($q) use ($emp_id) {
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
                    'card_desc', 'cliente_cardn', 'card_saldo_vlr',
                    'card_limite', 'card_saldo_pts','card_sts'
                )
                ->get()
                ->map(function($cartao) {
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

            // Soma dos pontos do cliente (card_saldo_pts)
            $cliente->cliente_pts = ClienteCard::where('cliente_id', $cliente->cliente_id)
            ->where('emp_id', $emp_id)
            ->where('cliente_doc', $cliente->cliente_doc)
            ->sum('card_saldo_pts');

        }

        return [
            'clientes' => $clientes->toArray()
        ];
    }

    // public function getClient(Request $request)
    // {
    //     $parametro = $request != null ? $request->all()['parametro'] : '';

    //     if (empty($parametro)) {
    //         return [];
    //     }

    //     return Cliente::select(DB::raw('cliente_id as id, cliente_id, cliente_doc, UPPER(cliente_nome) text'))
    //         ->whereRaw(DB::raw("cliente_nome LIKE '%" . $parametro . "%' OR cliente_id = '%" . $parametro . "%'"))
    //         ->get()
    //         ->toArray();
    // }

    public function storeProntuario(Request $request)
    {
        try {

            $prontuario = new ClienteProntuario();
            $prontuario->cliente_id = $request->input('cliente_id');
            $prontuario->protocolo_tp = 1;
            $prontuario->protocolo_dt = Carbon::now();
            $prontuario->cliente_doc = 1;
            $prontuario->emp_id = $request->input('emp_id');
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
                    if (!file_exists($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    $directoryThumbs = Storage::disk('public')->path('prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/fotos/protocolo_' . $prontuario->protocolo . '/thumbnails');
                    if (!file_exists($directoryThumbs)) {
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
                    if (!file_exists($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    $filename = time()  . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    $file->move($directory, $filename);
                }


                $prontuario = ClienteProntuario::find($prontuario->protocolo);
                $prontuario->anexo = 'x';
                $prontuario->doc_anexo_path = 'prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/docs/protocolo_' . $prontuario->protocolo . '/';

                $prontuario->save();
            }

            return response()->json([
                'title' => 'Sucesso',
                'type' => 'success',
                'text' => 'Prontuário salvo com sucesso.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'message' => $th->getMessage(),
                'type' => 'error',
            ], 500);
        }
    }

    public function updateProntuario(Request $request, $id)
    {
        try {

            $prontuario = ClienteProntuario::find($id);
            $prontuario->cliente_id = $request->input('cliente_id');
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
                    if (!file_exists($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    $directoryThumbs = Storage::disk('public')->path('prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/fotos/protocolo_' . $prontuario->protocolo . '/thumbnails');
                    if (!file_exists($directoryThumbs)) {
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
                    if (!file_exists($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    $filename = time()  . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    $file->move($directory, $filename);
                }


                $prontuario = ClienteProntuario::find($prontuario->protocolo);
                $prontuario->anexo = 'x';
                $prontuario->doc_anexo_path = 'prontuarios/empresa_' . $prontuario->emp_id . '/client_' . $prontuario->cliente_id . '/docs/protocolo_' . $prontuario->protocolo . '/';
            } else {
                $prontuario->doc_anexo_path = null;
            }

            if (!$request->hasFile('fotoUpload') && !$request->hasFile('fileDoc')) {
                $prontuario->anexo = null;
            }


            $prontuario->save();

            return response()->json([
                'title' => 'OK',
                'type' => 'success',
                'text' => 'Prontuário atualizado com sucesso.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'message' => $th->getMessage(),
                'type' => 'error',
            ], 500);
        }
    }

    public function postObterGridPesquisaProtocolo(Request $request)
    {
        try {

            $query = '';

            $hasQuery = false;
            if (!empty($request->data_de) && !empty($request->data_ate)) {
                if (Carbon::createFromFormat('Y-m-d', $request->data_de) > Carbon::createFromFormat('Y-m-d', $request->data_ate)) {
                    return response()->json([
                        "title" => "Erro",
                        "type" => "error",
                        "message" => "Data 'De:' não pode ser maior que a data 'Até:'."
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $query .= " protocolo_dt >= '" . Carbon::createFromFormat('Y-m-d', $request->data_de)->toDateString() . "' AND";
                $query .= " protocolo_dt <= '" . Carbon::createFromFormat('Y-m-d', $request->data_ate)->toDateString() . "' AND";
                $hasQuery = true;
            }



            if (!empty($request->protocolo)) {
                $query .= "protocolo = " . quotedstr($request->protocolo) . " AND";
                $hasQuery = true;
            }

            if (!empty($request->user_id)) {
                $query .= " user_id = " . quotedstr($request->user_id) . "";
                $hasQuery = true;
            }

            if (!empty($request->protocolo_dt)) {

                $query .= " protocolo_dt >= '" . Carbon::createFromFormat('Y-m-d', $request->data_de)->toDateString() . "' AND";
                $hasQuery = true;
            }

            if (!empty($request->protocolo_dt)) {

                $query .= " protocolo_dt <= '" . Carbon::createFromFormat('Y-m-d', $request->data_ate)->toDateString() . "' AND";
                $hasQuery = true;
            }

            $query = rtrim($query, "AND");

            if ($hasQuery) {
                $prontuarios = ClienteProntuario::whereRaw(DB::raw($query))->get();
            } else {
                $prontuarios = ClienteProntuario::where('cliente_id', $request->cliente_id)->get();
            }

            return DataTables::of($prontuarios)
                ->addIndexColumn()
                ->editColumn('anexo', function ($row) {
                    if ($row->anexo == 'x') {
                        return '<span class="text-center ml-3"><i class="fas fa-paperclip"></i></span>';
                    }
                    return '<span class="badge badge-secondary">Sem Anexo</span>';
                })
                ->addColumn('medico', function ($row) {
                    if (!empty($row->user)) {
                        return $row->user->user_name;
                    }
                    return 'Sem Médico';
                })
                ->addColumn('crm_medico', function ($row) {
                    if (!empty($row->user)) {
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
                    return str_pad($row->protocolo, 12, "0", STR_PAD_LEFT);
                })
                ->editColumn('protocolo_tp', function ($row) {
                    $badge = '';
                    if (!empty($row->tipo)) {

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
                "title" => "Erro",
                "type" => "error",
                "message" => $th->getMessage() . ' ' . $th->getLine() . ' ' . $th->getFile()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function postObterGridPesquisa(Request $request)
    {
        if (!Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, "Usuário não autenticado...");
        }

        //$this->applyFilters(request(), ['empresa_id' => $request->empresa_id, 'cliente_sts' => $request->cliente_sts, 'cliente_tipo' => $request->cliente_tipo, 'cliente_id' => $request->cliente_id, 'cliente_doc' => $request->cliente_doc], 'cliente_filters');

        $data = new Collection();

        $query = "";

        if (!empty($request->cliente_sts)) {
            $query .= "cliente_sts = " . quotedstr($request->cliente_sts) . " AND ";
        }
        if (!empty($request->cliente_tipo)) {
            $query .= "cliente_tipo = " . quotedstr($request->cliente_tipo) . " AND ";
        }
        if (!empty($request->cliente_id)) {
            if (is_numeric($request->cliente_id)) {
                $query .= "tbdm_clientes_geral.cliente_id = " . quotedstr($request->cliente_id) . " AND ";
            } else {
                $query .= "tbdm_clientes_geral.cliente_nome like '%" . $request->cliente_id . "%' AND ";
            }
        }

        if (!empty($request->cliente_doc)) {

            $query .= "tbdm_clientes_geral.cliente_doc = " . quotedstr(removerCNPJ($request->cliente_doc)) . " AND ";
        }

        if (!empty($request->empresa_id)) {
            if (is_numeric($request->empresa_id)) {

                $query .= "emp_id = " . $request->empresa_id;
            } else {

                $empresasGeral = Empresa::where('emp_nmult', 'like', '%' . $request->empresa_id . '%')->get(['emp_id'])->pluck('emp_id')->toArray();

                if ($empresasGeral) {
                    $query .= selectItens($empresasGeral, 'tbdm_clientes_emp.emp_id');
                }
            }
        }

        $query = rtrim($query, "AND ");

        if (!empty($query)) {
            $data = Cliente::join('tbdm_clientes_emp', 'tbdm_clientes_geral.cliente_id', '=', 'tbdm_clientes_emp.cliente_id')
                ->select(
                    'tbdm_clientes_geral.*',
                    'tbdm_clientes_emp.emp_id',
                )
                ->whereRaw(DB::raw($query))->get();
        }

        $this->permissions = Auth::user()->getAllPermissions()->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';
                if (in_array('cliente.edit', $this->permissions)) {
                    $btn .= '<a href="cliente/' . $row->cliente_id . '/alterar" class="btn btn-primary btn-sm mr-1" title="Editar"><i class="fas fa-edit"></i></a>';
                }

                $disabled = "";
                if ($row->status->cliente_sts == EmpresaStatusEnum::ATIVO)
                    $disabled = "disabled";

                $btn .= '<button href="#" class="btn btn-primary btn-sm mr-1" ' . $disabled . ' id="active_grid_id" data-url="cliente" data-id="' . $row->cliente_id . '" title="Ativar"><i class="far fa-check-circle"></i></button>';

                $disabled = "";
                if ($row->status->cliente_sts == EmpresaStatusEnum::INATIVO)
                    $disabled = "disabled";

                $btn .= '<button href="#" class="btn btn-primary btn-sm mr-1" ' . $disabled . ' id="inactive_grid_id" data-url="cliente" data-id="' . $row->cliente_id . '" title="Inativar"><i class="fas fa-ban"></i></button>';

                if (in_array('cliente.destroy', $this->permissions)) {
                    $disabled = "";
                    if ($row->status->cliente_sts == EmpresaStatusEnum::EXCLUIDO)
                        $disabled = "disabled";
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
                if (!empty($row->tipo)) {

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
                if (!empty($row->status)) {

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
                //$id = str_pad($row->cliente_id, 5, "0", STR_PAD_LEFT);
                return $row->cliente_id;
            })
            ->rawColumns(['action', 'cliente_doc', 'cliente_sts', 'cliente_tipo'])
            ->make(true);
    }

    //Cartão
    public function storeCard(Request $request)
    {
        try {

            if (empty($request->cliente_id)) {
                return response()->json([
                    'message_type' => 'Cliente ainda não cadatrado, favor cadastrar o cliente antes de criar o cartão.',
                    'message' => []
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validator = Validator::make($request->all(), [
                'emp_id' => 'required',
                'cliente_id' => 'required',
                'card_tp' => 'required',
                'card_mod' => 'required',
                'card_categ' => 'required',
                'card_desc' => 'required',
                'card_limite' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            DB::beginTransaction();

            $empresa = Empresa::find($request->emp_id);
            $cnpj = '';
            if ($empresa) {
                $cnpj = substr(removerCNPJ($empresa->emp_cnpj), 0, 7);
            }

            $cliente = Cliente::find($request->cliente_id);
            $cpf = '';
            if ($cliente) {
                $cpf = substr(removerCNPJ($cliente->cliente_doc), 0, 7);
            }

            // Exemplo: '4' para Visa, '5' para Mastercard, '3' para American Express
            // Você pode ajustar o prefixo conforme necessário para outros tipos de cartões
            // Gera um número de cartão de crédito com 16 dígitos
            $numeroCartao = $this->gerarNumeroCartaoCredito($cnpj, $cpf, $request->card_mod, $request->card_tp);

            $dadosCartao = [
                'emp_id'           => $request->emp_id,
                'cliente_id'       => $request->cliente_id,
                'cliente_doc'      => removerCNPJ($request->cliente_doc),
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
                'card_saldo_pts'   => 0,
                'card_pass'        => $request->card_pass ?? null,
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
                    'text' => 'Registro criado com sucesso!',
                    'type' => 'success',
                    'data' => $data
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'title' => 'Erro',
                'text' => $th->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    /**
     * Gera um número de cartão de crédito com base no CNPJ da empresa, CPF do cliente e tipo de cartão,
     * completando com dígitos aleatórios e finalizando com o dígito de Luhn.
     *
     * @param string $cnpj
     * @param string $cpf
     * @param string $card_mod
     * @param int $length
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

            $validator = Validator::make($request->all(), [
                'tabela' => 'required',
                'campo' => 'required',
                'emp_id' => 'required',
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
                ->where('emp_id', '=', $request->emp_id)->update([
                    "tabela" => $request->tabela,
                    "campo" => $request->campo,
                    "user_id" => $request->user_id,
                    "emp_id" => $request->emp_id,
                ]);

            return response()->json([
                'title' => 'Sucesso',
                'text' => 'Work Flow atualizado com sucesso!',
                'type' => 'success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text' => $th->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    public function editCard(Request $request, $emp_id)
    {
        try {

            $data = DB::connection('dbsysclient')->table('tbcf_config_wf')
                ->where('tabela', '=', $request->tabela)
                ->where('campo', '=', $request->campo)
                ->where('emp_id', '=', $request->emp_id)->first();

            $columnsList = DB::connection('dbsysclient')->getSchemaBuilder()->getColumnListing($request->tabela);
            $columns = [];

            foreach ($columnsList as $key => $col) {
                $columns[] = ['id' => $col, 'text' => $col];
            }

            if ($data) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Resposta obtida com sucesso!',
                    'type' => 'success',
                    'data' => $data,
                    'columns' => $columns,
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text' => 'Registro não encontrado!',
                'type' => 'error'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text' => $th->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    public function destroyCard(Request $request, $emp_id)
    {
        try {

            $data = DB::connection('dbsysclient')->table('tbcf_config_wf')
                ->where('tabela', '=', $request->tabela)
                ->where('campo', '=', $request->campo)
                ->where('emp_id', '=', $request->emp_id)->delete();

            if ($data) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro deletado com sucesso!',
                    'type' => 'success',
                    'btnPesquisar' => 'btnPesquisarWf'
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text' => 'Registro não encontrado!',
                'type' => 'error'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text' => $th->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    public function getObterGridPesquisaCard(Request $request)
    {

        if (!Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, "Usuário não autenticado...");
        }

        $data = DB::connection('dbsysclient')->table('tbdm_clientes_card')->where(
            'emp_id',
            '=',
            1
        )->where(
            'cliente_id',
            '=',
            $request->cliente_id
        )->get();


        $this->permissions = Auth::user()->getAllPermissions()->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';

                if (in_array('cliente.edit', $this->permissions)) {

                    $btn .= '<button class="btn btn-sm btn-primary mr-1"';
                    $btn .= 'data-emp-id="' . $row->emp_id . '" ';
                    $btn .= 'data-uuid="' . $row->card_uuid . '" ';
                    $btn .= ' title="Resetar Senha"><i class="fas fa-key"></i></button>';
                }

                $btn .= '<button class="btn btn-sm btn-primary mr-1" title="Editar"><i class="fas fa-edit"></i></button>';
                $btn .= '<button class="btn btn-sm btn-primary mr-1" title="Ativar"><i class="far fa-check-circle"></i></button>';
                $btn .= '<button class="btn btn-sm btn-primary mr-1" title="Bloquear"><i class="fas fa-ban"></i></button>';
                $btn .= '<button class="btn btn-sm btn-primary mr-1" title="Excluir"><i class="far fa-trash-alt"></i></button>';


                return $btn;
            })->editColumn('card_sts', function ($row) {
                $badge = '';
                if (!empty($row->card_sts)) {
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
                return formatarDecimalParaTexto($row->card_saldo_pts);
            })->editColumn('card_desc', function ($row) {
                return mb_strtoupper(rtrim($row->card_desc), 'UTF-8');
            })->editColumn('card_categ', function ($row) {
                $badge = '';
                if (!empty($row->card_categ)) {
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
