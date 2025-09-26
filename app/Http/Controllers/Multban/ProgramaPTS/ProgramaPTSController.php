<?php

namespace App\Http\Controllers\Multban\ProgramaPts;

use App\Http\Controllers\Controller;
use App\Models\Multban\DadosMestre\TbDmCardCateg;
use App\Models\Multban\ProgramaPts\ProgramaPts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Yajra\DataTables\Facades\DataTables;
use App\Enums\ProgramaPtsStatusEnum;
use App\Models\Multban\ProgramaPts\ProgramaPtsStatus;

class ProgramaPtsController extends Controller
{
    protected $permissions = [];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $card_categ = TbDmCardCateg::select('card_categ as categ', 'card_categ_desc as descricao')->get();
        $prgpts_sts = ProgramaPtsStatus::select('prgpts_sts as status', 'prgpts_sts_desc as descricao')->get();
        return view('Multban.programa-pontos.index', compact('card_categ', 'prgpts_sts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
                $programa = new ProgramaPts();
                $programa->emp_id = $request->input('empresa_id');
                $programa->card_categ = $request->input('card_categ');
                $programa->prgpts_sts = $request->input('prgpts_sts');
                $programa->prgpts_valor = str_replace(',', '.', str_replace('.', '', $request->input('prgpts_valor')));
                $programa->prgpts_eq = str_replace(',', '.', str_replace('.', '', $request->input('prgpts_eq')));
                $programa->prgpts_sc = $request->input('prgpts_sc');
                $programa->criador = Auth::user()->user_id;
                $programa->dthr_cr = now();
                $programa->modificador = Auth::user()->user_id;
                $programa->dthr_ch = now();

                $programa->save();

                return response()->json(['success' => true, 'message' => 'Programa criado com sucesso!']);

            } catch (QueryException $e) {
                if ($e->getCode() == 23000) { // Violação de restrição única
                    return response()->json([
                        'success' => false,
                        'message' => 'Já existe um registro criado para a categoria de cartão selecionada nesta empresa.'
                    ], 422);
                }
                // Outros erros
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao criar programa.'
                ], 500);
            }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $programa = ProgramaPts::where('prgpts_id', $id)->firstOrFail();

        return response()->json([
            'card_categ' => $programa->card_categ,
            'prgpts_valor' => $programa->prgpts_valor,
            'prgpts_eq' => $programa->prgpts_eq,
            'prgpts_sts' => $programa->prgpts_sts,
            'prgpts_sc' => $programa->prgpts_sc
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $programa = ProgramaPts::where('prgpts_id', $id)->firstOrFail();
        $programa->card_categ = $request->input('card_categ');
        $programa->prgpts_sts = $request->input('prgpts_sts');
        $programa->prgpts_sc = $request->input('prgpts_sc');
        $programa->prgpts_valor = str_replace(',', '.', str_replace('.', '', $request->input('prgpts_valor')));
        $programa->prgpts_eq = str_replace(',', '.', str_replace('.', '', $request->input('prgpts_eq')));
        $programa->prgpts_sc = $request->input('prgpts_sc');
        $programa->modificador = Auth::user()->user_id;
        $programa->dthr_ch = now();

        $programa->save();

        return response()->json(['success' => true, 'message' => 'Programa atualizado com sucesso!']);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {

            $programa = ProgramaPts::find($id);
            if ($programa) {
                $programa->prgpts_sts = ProgramaPtsStatusEnum::EXCLUIDO;
                $programa->save();
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
                'title' => 'Erro',
                'text' => $e->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    public function inactive($id)
    {
        try {

            $programa = ProgramaPts::find($id);
            if ($programa) {
                $programa->prgpts_sts = ProgramaPtsStatusEnum::INATIVO;
                $programa->save();
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

            $programa = ProgramaPts::find($id);
            if ($programa) {
                $programa->prgpts_sts = ProgramaPtsStatusEnum::ATIVO;
                $programa->save();
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

    // FUNÇÃO QUE RETORNA OS PROGRAMAS CADASTRADOS AO CLICAR EM PESQUISAR
    public function getObterGridPesquisa(Request $request)
    {
        if (!Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, "Usuário não autenticado...");
        }

        $emp_id = "";
        $categ = "";

        $data = new Collection();

        if (!empty($request->empresa_id)) {
            $emp_id = $request->empresa_id;
        }

        if (!empty($request->card_categ)) {
            $categ = $request->card_categ;
        }

        $query = ProgramaPts::query();

        if (!empty($emp_id)) {
            $query->where('emp_id', '=', $emp_id);
        }

        if (!empty($categ)) {
            $query->where('card_categ', '=', $categ);
        }

        // RESULTADO FINAL DA PESQUISA
        $data = $query->get();

        $this->permissions = Auth::user()->getAllPermissions()->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';
                if (in_array('programa-de-pontos.edit', $this->permissions)) {
                    $btn .= '<button type="button" class="btn btn-primary btn-sm mr-1 btn-editar-programa" data-id="' . $row->prgpts_id . '" title="Editar"><i class="fas fa-edit"></i></button>';
                }

                $disabled = "";
                if ($row->prgpts_sts == ProgramaPtsStatusEnum::ATIVO)
                    $disabled = "disabled";

                $btn .= '<button href="#" class="btn btn-primary btn-sm mr-1" ' . $disabled . ' id="active_grid_id" data-url="programa-de-pontos" data-id="' . $row->prgpts_id . '" title="Ativar"><i class="far fa-check-circle"></i></button>';

                $disabled = "";
                if ($row->prgpts_sts == ProgramaPtsStatusEnum::INATIVO)
                    $disabled = "disabled";

                $btn .= '<button href="#" class="btn btn-primary btn-sm mr-1" ' . $disabled . ' id="inactive_grid_id" data-url="programa-de-pontos" data-id="' . $row->prgpts_id . '" title="Inativar"><i class="fas fa-ban"></i></button>';

                if (in_array('programa-de-pontos.destroy', $this->permissions)) {
                    $btn .= '<button href="#" class="btn btn-sm btn-primary mr-1" id="delete_grid_id" data-url="programa-de-pontos" data-id="' . $row->prgpts_id . '" title="Excluir"><i class="far fa-trash-alt"></i></button>';
                }
                return $btn;
            })
            ->editColumn('card_categ', function ($row) {
                $card_categ_desc = TbDmCardCateg::where('card_categ', $row->card_categ)->first();
                return $card_categ_desc ? $card_categ_desc->card_categ_desc : $row->card_categ;
            })
            ->editColumn('prgpts_valor', function ($row) {
                return 'R$ ' . number_format($row->prgpts_valor, 2, ',', '.');
            })
            ->editColumn('prgpts_eq', function ($row) {
                return number_format($row->prgpts_eq, 2, ',', '.');
            })
            ->editColumn('prgpts_sc', function ($row) {
                return $row->prgpts_sc;
            })
            ->editColumn('prgpts_sts', function ($row) {
                $status = ProgramaPtsStatus::where('prgpts_sts', $row->prgpts_sts)->first();
                $badge = $row->prgpts_sts;
                if ($status) {
                    switch ($status->prgpts_sts) {
                        case 'AT':
                            $badge = '<span class="badge badge-success">' . $status->prgpts_sts_desc . '</span>';
                            break;
                        case 'NA':
                        case 'IN':
                        case 'ON':
                            $badge = '<span class="badge badge-warning">' . $status->prgpts_sts_desc . '</span>';
                            break;
                        case 'EX':
                        case 'BL':
                            $badge = '<span class="badge badge-danger">' . $status->prgpts_sts_desc . '</span>';
                            break;
                        default:
                            $badge = '<span class="badge badge-secondary">' . $status->prgpts_sts_desc . '</span>';
                    }
                }
                return $badge;
            })
            ->rawColumns(['action', 'prgpts_sts'])
            ->make(true);
    }

    public function alterarStatus(Request $request, $id)
    {
        $programa = ProgramaPts::where('prgpts_id', $id)->firstOrFail();
        $programa->prgpts_sts = $request->input('prgpts_sts');
        $programa->modificador = Auth::user()->user_id;
        $programa->dthr_ch = now();
        $programa->save();

        return response()->json(['success' => true, 'message' => 'Status alterado com sucesso!']);
    }

}
