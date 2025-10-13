<?php

namespace App\Http\Controllers\Multban\Produto;

use App\Enums\ProdutoStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Multban\DadosMestre\TbDmBncCode;
use App\Models\Multban\Empresa\Empresa;
use App\Models\Multban\Produto\Produto;
use App\Models\Multban\Produto\ProdutoStatus;
use App\Models\Multban\Produto\ProdutoTipo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class ProdutoController extends Controller
{
    protected $permissions = [];

    /**
     * Display a listing of the resource.
     */
    public function apiProdutos()
    {
        // BUSCA SOMENTE PRODUTOS ATIVOS DA EMPRESA DO USUÁRIO LOGADO
        $empresaId = Auth::user()->emp_id;
        $produtos = Produto::where('produto_sts', 'AT')
            ->where('emp_id', $empresaId)
            ->get();

        $result = $produtos->map(function ($prod) {
            // Busca descrição do tipo de produto
            $tipoRow = ProdutoTipo::where('produto_tipo', $prod->produto_tipo)->first();
            $tipoDesc = $tipoRow ? $tipoRow->produto_tipo_desc : 'Desconhecido';

            // Busca descrição do status, se não vier pelo relacionamento, busca direto
            $statusRow = ProdutoStatus::where('produto_sts', $prod->produto_sts)->first();
            $stsDesc = $statusRow ? $statusRow->produto_sts_desc : 'Desconhecido';

            return [
                'produto_id'        => $prod->produto_id,
                'produto_tipo'      => $prod->produto_tipo,
                'produto_tipo_desc' => $tipoDesc,
                'produto_dm'        => $prod->produto_dm,
                'produto_vlr'       => $prod->produto_vlr,
                'produto_sts'       => $prod->produto_sts,
                'produto_sts_desc'  => $stsDesc,
            ];
        });

        return response()->json($result);
    }

    public function index()
    {
        $tipos = ProdutoTipo::all();
        $status = ProdutoStatus::all();
        $empresas = Empresa::all();
        $bancos = TbDmBncCode::all();

        return view('Multban.produto.index', compact('tipos', 'status', 'empresas', 'bancos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $produto = new Produto;
        $tipos = ProdutoTipo::all();
        $status = ProdutoStatus::all();
        $bancos = TbDmBncCode::all();

        return view('Multban.produto.edit', compact('produto', 'tipos', 'status', 'bancos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        // Regras de validação
        $rules = [
            'produto_dc'   => 'required',
            'produto_dm'   => 'required',
            'produto_dl'   => 'required',
            'produto_vlr'  => 'required',
            'produto_tipo' => 'required',
            'produto_sts'  => 'required',
        ];
        if ($request->input('produto_tipo') == '1') {
            $rules['produto_ncm'] = 'required';
            $rules['produto_peso'] = 'required';
        }
        if ($request->input('produto_tipo') == '3') {
            $rules['partcp_pvlaor'] = 'required';
        }
        if ($request->input('pagarPor') == 'partcp_pgsplit') {
            $rules['partcp_seller'] = 'required';
        }
        if ($request->input('pagarPor') == 'partcp_pgtransf') {
            $rules['partcp_cdgbc'] = 'required';
            $rules['partcp_agbc'] = 'required';
            $rules['partcp_ccbc'] = 'required';
            $rules['partcp_pix'] = 'required';
        }
        $validated = $request->validate($rules);

        $data = $request->except(['_token', '_method']);
        // Regra para partcp_pvlaor
        if (isset($data['partcp_pvlaor'])) {
            $partcp_pvlaor = str_replace(',', '.', $data['partcp_pvlaor']);
            if ($partcp_pvlaor === '' || $partcp_pvlaor === null) {
                unset($data['partcp_pvlaor']);
            } else {
                $data['partcp_pvlaor'] = $partcp_pvlaor;
            }
        }
        $data['produto_ctrl'] = isset($data['produto_ctrl']) ? 'X' : '';
        $data['emp_id'] = $user->emp_id;
        if (isset($data['produto_vlr'])) {
            $data['produto_vlr'] = str_replace(['.', ','], ['', '.'], $data['produto_vlr']);
        }
        if (isset($data['produto_peso'])) {
            $data['produto_peso'] = str_replace([','], ['.'], $data['produto_peso']);
        }
        $data['criador'] = $user->user_id;
        $data['modificador'] = $user->user_id;
        $data['dthr_cr'] = now();
        $data['dthr_ch'] = now();
        $produto = Produto::create($data);
        if ($request->ajax()) {
            return response()->json([
                'message'  => 'Produto cadastrado com sucesso!',
                'redirect' => route('produto.edit'),
            ]);
        }

        return redirect()->route('produto.edit')
            ->with('success', 'Produto cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $produto = Produto::find($id);
        $empresaDesc = null;
        if ($produto && $produto->emp_id) {
            $empresa = Empresa::where('emp_id', $produto->emp_id)->first();
            $empresaDesc = $empresa ? $empresa->emp_nmult : null;
        }
        $tipos = ProdutoTipo::all();
        $status = ProdutoStatus::all();
        $bancos = TbDmBncCode::all();
        $empresaPartOption = null;
        if (! empty($produto->partcp_empid)) {
            $empresa = Empresa::where('emp_id', $produto->partcp_empid)->first();
            if ($empresa) {
                $empresaPartOption = [
                    'id'   => $empresa->emp_id,
                    'text' => $empresa->emp_id . ' - ' . $empresa->emp_nmult,
                ];
            }
        }

        return view('Multban.produto.edit', [
            'produto'           => $produto,
            'tipos'             => $tipos,
            'status'            => $status,
            'bancos'            => $bancos,
            'empresaDesc'       => $empresaDesc,
            'empresaPartOption' => $empresaPartOption,
            'routeAction'       => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        // Regras de validação
        $rules = [
            'produto_dc'   => 'required',
            'produto_dm'   => 'required',
            'produto_dl'   => 'required',
            'produto_vlr'  => 'required',
            'produto_tipo' => 'required',
            'produto_sts'  => 'required',
        ];
        if ($request->input('produto_tipo') == '1') {
            $rules['produto_ncm'] = 'required';
            $rules['produto_peso'] = 'required';
        }
        if ($request->input('produto_tipo') == '3') {
            $rules['partcp_pvlaor'] = 'required';
        }
        if ($request->input('pagarPor') == 'partcp_pgsplit') {
            $rules['partcp_seller'] = 'required';
        }
        if ($request->input('pagarPor') == 'partcp_pgtransf') {
            $rules['partcp_cdgbc'] = 'required';
            $rules['partcp_agbc'] = 'required';
            $rules['partcp_ccbc'] = 'required';
            $rules['partcp_pix'] = 'required';
        }
        $validated = $request->validate($rules);

        $data = $request->except(['_token', '_method']);

        // Converte o valor para formato correto antes do update
        $partcp_pvlaor = str_replace(',', '.', $request->input('partcp_pvlaor'));
        if ($partcp_pvlaor === '' || $partcp_pvlaor === null) {
            $request->request->remove('partcp_pvlaor');
        } else {
            $request->merge([
                'partcp_pvlaor' => $partcp_pvlaor,
            ]);
        }

        $data = $request->all();
        $data['produto_ctrl'] = isset($data['produto_ctrl']) ? 'X' : '';
        $data['emp_id'] = $user->emp_id;
        if (isset($data['produto_vlr'])) {
            $data['produto_vlr'] = str_replace(['.', ','], ['', '.'], $data['produto_vlr']);
        }
        if (isset($data['produto_peso'])) {
            $data['produto_peso'] = str_replace([','], ['.'], $data['produto_peso']);
        }
        $data['modificador'] = $user->user_id;
        $data['dthr_ch'] = now();
        $produto = Produto::findOrFail($id);
        $produto->update($data);
        if ($request->ajax()) {
            return response()->json([
                'message'  => 'Produto atualizado com sucesso!',
                'redirect' => route('produto.edit', $produto->produto_id),
            ]);
        }

        return redirect()->route('produto.edit', $produto->produto_id)
            ->with('success', 'Produto atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $produto = Produto::find($id);
            if ($produto) {
                $produto->produto_sts = 'EX'; // Excluído
                $produto->save();

                return response()->json([
                    'title' => 'Sucesso',
                    'text'  => 'Produto excluído com sucesso!',
                    'type'  => 'success',
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text'  => 'Produto não encontrado!',
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

    public function inactive($id)
    {
        try {
            $produto = Produto::find($id);
            if ($produto) {
                $produto->produto_sts = 'IN'; // Inativo
                $produto->save();

                return response()->json([
                    'title' => 'Sucesso',
                    'text'  => 'Produto inativado com sucesso!',
                    'type'  => 'success',
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text'  => 'Produto não encontrado!',
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

    public function active(string $id)
    {
        try {
            $produto = Produto::find($id);
            if ($produto) {
                $produto->produto_sts = 'AT'; // Ativo
                $produto->save();

                return response()->json([
                    'title' => 'Sucesso',
                    'text'  => 'Produto ativado com sucesso!',
                    'type'  => 'success',
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text'  => 'Produto não encontrado!',
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

    // FUNÇÃO QUE BUSCA DADOS DO FILTRO DESCRIÇÃO DO PRODUTO
    public function getObterDescricaoProduto(Request $request)
    {
        $parametro = $request != null ? $request->all()['parametro'] : '';
        $campo = 'produto_dm';

        if (empty($parametro)) {
            return [];
        }

        $query = Produto::select(DB::raw('produto_id as id, produto_id, produto_dm, produto_vlr, UPPER(' . $campo . ') text'))
            ->where(function ($q) use ($campo, $parametro) {
                $q->where($campo, 'like', "%$parametro%")
                    ->orWhere('produto_id', 'like', "%$parametro%");
            });

        return $query->get()->toArray();
    }

    // FUNÇÃO QUE RETORNA OS PRODUTOS AO CLICAR EM PESQUISAR
    public function getObterGridPesquisa(Request $request)
    {
        if (empty($request->empresa_id)) {
            return response()->json([
                'error' => 'Selecione uma Empresa antes de pesquisar.',
            ], 422);
        }

        if (! Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, 'Usuário não autenticado...');
        }

        $emp_id = '';
        $produto_id = '';
        $produto_dm_id = '';
        $produto_tipo = '';
        $produto_sts = '';

        $data = new Collection;

        if (! empty($request->empresa_id)) {
            $emp_id = $request->empresa_id;
        }

        if (! empty($request->produto_id)) {
            $produto_id = $request->produto_id;
        }

        if (! empty($request->produto_dmf_id)) {
            $produto_dm_id = $request->produto_dmf_id;
        }

        if (! empty($request->produto_tipo)) {
            $produto_tipo = $request->produto_tipo;
        }

        if (! empty($request->produto_sts)) {
            $produto_sts = $request->produto_sts;
        }

        // PESQUISA A EMPRESA PELOS FILTROS SELECIONADOS
        $query = Produto::query();

        if (! empty($emp_id)) {
            $query->where('emp_id', '=', $emp_id);
        }

        if (! empty($produto_id) && is_numeric($produto_id) && intval($produto_id) > 0) {
            $query->where('produto_id', '=', $produto_id);
        }

        if (! empty($produto_dm_id)) {
            $query->where('produto_id', '=', $produto_dm_id);
        }

        if (! empty($produto_tipo)) {
            $query->where('produto_tipo', '=', $produto_tipo);
        }

        if (! empty($produto_sts)) {
            $query->where('produto_sts', '=', $produto_sts);
        }

        // RESULTADO FINAL DA PESQUISA
        $data = $query->get();

        $this->permissions = Auth::user()->permissions->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';
                if (in_array('produto.edit', $this->permissions)) {
                    $btn .= '<a href="produto/' . $row->produto_id . '/alterar" class="btn btn-primary btn-sm mr-1" title="Editar"><i class="fas fa-edit"></i></a>';
                }

                $disabled = '';
                if ($row->produto_sts == ProdutoStatusEnum::ATIVO) {
                    $disabled = 'disabled';
                }

                $btn .= '<button href="#" class="btn btn-primary btn-sm mr-1" ' . $disabled . ' id="active_grid_id" data-url="produto" data-id="' . $row->produto_id . '" title="Ativar"><i class="far fa-check-circle"></i></button>';

                $disabled = '';
                if ($row->produto_sts == ProdutoStatusEnum::INATIVO) {
                    $disabled = 'disabled';
                }

                $btn .= '<button href="#" class="btn btn-primary btn-sm mr-1" ' . $disabled . ' id="inactive_grid_id" data-url="produto" data-id="' . $row->produto_id . '" title="Inativar"><i class="fas fa-ban"></i></button>';

                if (in_array('produto.destroy', $this->permissions)) {
                    $btn .= '<button href="#" class="btn btn-sm btn-primary mr-1" id="delete_grid_id" data-url="produtos" data-id="' . $row->produto_id . '" title="Excluir"><i class="far fa-trash-alt"></i></button>';
                }

                return $btn;
            })
            ->editColumn('produto_id', function ($row) {
                return $row->produto_id;
            })
            ->editColumn('produto_tipo', function ($row) {
                $tipo = ProdutoTipo::where('produto_tipo', $row->produto_tipo)->first();

                return $tipo ? $tipo->produto_tipo_desc : $row->produto_tipo;
            })
            ->editColumn('produto_dc', function ($row) {
                return $row->produto_dc;
            })
            ->editColumn('produto_dm', function ($row) {
                return $row->produto_dm;
            })
            ->editColumn('produto_sts', function ($row) {
                $status = ProdutoStatus::where('produto_sts', $row->produto_sts)->first();
                $badge = $row->produto_sts;
                if ($status) {
                    switch ($status->produto_sts) {
                        case 'AT':
                            $badge = '<span class="badge badge-success">' . $status->produto_sts_desc . '</span>';
                            break;
                        case 'NA':
                        case 'IN':
                        case 'ON':
                            $badge = '<span class="badge badge-warning">' . $status->produto_sts_desc . '</span>';
                            break;
                        case 'EX':
                        case 'BL':
                            $badge = '<span class="badge badge-danger">' . $status->produto_sts_desc . '</span>';
                            break;
                        default:
                            $badge = '<span class="badge badge-secondary">' . $status->produto_sts_desc . '</span>';
                    }
                }

                return $badge;
            })
            ->rawColumns(['action', 'produto_sts'])
            ->make(true);
    }
}
