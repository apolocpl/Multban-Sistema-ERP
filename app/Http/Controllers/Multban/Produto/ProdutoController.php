<?php

namespace App\Http\Controllers\Multban\Produto;

use App\Http\Controllers\Controller;
use App\Models\Multban\Produto\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Multban\Empresa\Empresa;
use App\Models\Multban\Produto\ProdutoTipo;
use App\Models\Multban\Produto\ProdutoStatus;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\Collection;
use Yajra\DataTables\Facades\DataTables;


class ProdutoController extends Controller
{
    protected $permissions = [];
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tipos = ProdutoTipo::all();
        $status = ProdutoStatus::all();
        return view('Multban.produto.index', compact('tipos', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $produto = new Produto();
        $tipos = ProdutoTipo::all();
        $status = ProdutoStatus::all();
        return view('Multban.produto.create', compact('produto', 'tipos', 'status'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        // Regras de validação
        $rules = [
            'produto_dc' => 'required',
            'produto_dm' => 'required',
            'produto_dl' => 'required',
            'produto_vlr' => 'required',
            'produto_tipo' => 'required',
            'produto_sts' => 'required',
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
                'message' => 'Produto cadastrado com sucesso!',
                'redirect' => route('produtos.create')
            ]);
        }
        return redirect()->route('produtos.create')
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
        $tipos = ProdutoTipo::all();
        $status = ProdutoStatus::all();
        return view('Multban.produto.edit', compact('produto', 'tipos', 'status'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        // Regras de validação
        $rules = [
            'produto_dc' => 'required',
            'produto_dm' => 'required',
            'produto_dl' => 'required',
            'produto_vlr' => 'required',
            'produto_tipo' => 'required',
            'produto_sts' => 'required',
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
                'message' => 'Produto atualizado com sucesso!',
                'redirect' => route('produtos.edit', $produto->produto_id)
            ]);
        }
        return redirect()->route('produtos.edit', $produto->produto_id)
            ->with('success', 'Produto atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // FUNÇÃO QUE BUSCA DADOS DO FILTRO EMPRESA
    public function getObterEmpresas(Request $request)
    {
        $parametro = $request != null ? $request->all()['parametro'] : '';
        $campo = 'emp_nfant';

        if (empty($parametro)) {
            return [];
        }

        $query = Empresa::select(DB::raw('emp_id as id, emp_id, emp_cnpj, UPPER(' . $campo . ') text'))
            ->where(function($q) use ($campo, $parametro) {
                $q->where($campo, 'like', "%$parametro%")
                  ->orWhere('emp_cnpj', 'like', "%$parametro%")
                  ->orWhere('emp_id', 'like', "%$parametro%");
            });

        return $query->get()->toArray();
    }

    // FUNÇÃO QUE BUSCA DADOS DO FILTRO DESCRIÇÃO DO PRODUTO
    public function getObterDescricaoProduto(Request $request)
    {
        $parametro = $request != null ? $request->all()['parametro'] : '';
        $campo = 'produto_dm';

        if (empty($parametro)) {
            return [];
        }

        $query = Produto::select(DB::raw('produto_id as id, produto_id, produto_dm, UPPER(' . $campo . ') text'))
            ->where(function($q) use ($campo, $parametro) {
                $q->where($campo, 'like', "%$parametro%")
                  ->orWhere('produto_id', 'like', "%$parametro%");
            });

        return $query->get()->toArray();
    }

    // FUNÇÃO QUE RETORNA OS PRODUTOS AO CLICAR EM PESQUISAR
    public function getObterGridPesquisa(Request $request)
    {
        if (!Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, "Usuário não autenticado...");
        }

        $emp_id = "";
        $produto_id = "";
        $produto_tipo = "";
        $produto_dm = "";
        $produto_sts = "";

        $data = new Collection();

        if (!empty($request->empresa_id)) {
            $emp_id = $request->empresa_id;
        }

        if (!empty($request->produto_id)) {
            $produto_id = $request->produto_id;
        }

        if (!empty($request->produto_tipo)) {
            $produto_tipo = $request->produto_tipo;
        }

        if (!empty($request->produto_dm)) {
            $produto_dm = $request->produto_dm;
        }

        if (!empty($request->produto_sts)) {
            $produto_sts = $request->produto_sts;
        }

        // PESQUISA A EMPRESA PELOS FILTROS SELECIONADOS
        $query = Produto::query();

        if (!empty($emp_id)) {
            $query->where('emp_id', '=', $emp_id);
        }

        if (!empty($produto_id)) {
            if (is_numeric($produto_id) && intval($produto_id) > 0) {
                $query->where('produto_id', '=', $produto_id);
            } else {
                $query->where('produto_dm', 'like', '%' . $produto_dm . '%');
            }
        }

        if (!empty($produto_tipo)) {
            $query->where('produto_tipo', '=', $produto_tipo);
        }

        if (!empty($produto_dm)) {
            $query->where('produto_dm', '=', $produto_dm);
        }

        if (!empty($produto_sts)) {
            $query->where('produto_sts', '=', $produto_sts);
        }

        // RESULTADO FINAL DA PESQUISA
        $data = $query->get();

        $this->permissions = Auth::user()->getAllPermissions()->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';
                if (in_array('produtos.edit', $this->permissions)) {
                    $btn .= '<a href="produtos/' . $row->produto_id . '/alterar" class="btn btn-primary btn-sm mr-1" title="Editar"><i class="fas fa-edit"></i></a>';
                }
                if (in_array('produtos.destroy', $this->permissions)) {
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
