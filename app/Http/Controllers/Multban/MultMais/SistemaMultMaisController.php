<?php

namespace App\Http\Controllers\Multban\MultMais;

use App\Http\Controllers\Controller;
use App\Models\Multban\DadosMestre\TbDmAPIGrupo;
use App\Models\Multban\DadosMestre\TbDmAPISubGrupo;
use App\Models\Multban\DadosMestre\TbDmCanalCm;
use App\Models\Multban\DadosMestre\TbdmFornecedor;
use App\Models\Multban\DadosMestre\TbDmMsgCateg;
use App\Models\Multban\Empresa\DestinoDosValores;
use Illuminate\Http\Request;
use App\Models\Multban\Empresa\EmpresaTiposDePlanoVendido;
use App\Models\Multban\Empresa\Empresa;
use App\Models\Multban\Empresa\EmpresaTipoDeAdquirentes;
use App\Models\Multban\Empresa\EmpresaTipoDeBoletagem;
use App\Models\Multban\TbCf\ConexoesAPI;
use App\Models\Multban\TbCf\ConexoesBcEmp;
use App\Models\Multban\TbCf\TbCfMsgComp;
use App\Models\Multban\TbCf\TbCfWorkFlow;
use App\Models\Multban\TbSy\TbSyTabAlias;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Intervention\Image\Laravel\Facades\Image;

class SistemaMultMaisController extends Controller
{
    private $permissions;
    private $req;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tiposDePlanoVendido = EmpresaTiposDePlanoVendido::all();
        $empresaGeral = new Empresa();

        $tbdmFornecedor = TbdmFornecedor::all();
        $tables = DB::connection('dbsysclient')->select('SHOW TABLES');
        $tbdmApiGrupo = TbDmAPIGrupo::all();
        $tbdmApiSubGrupo = TbDmAPISubGrupo::all();
        $destinoDosValores = DestinoDosValores::all();
        $tipoDeBoletagem = EmpresaTipoDeBoletagem::all();
        $tipoDeAdquirentes = EmpresaTipoDeAdquirentes::all();
        $tbDmMsgCateg = TbDmMsgCateg::all();
        $tbDmCanalCm = TbDmCanalCm::all();
        $users = User::all();

        return view('Multban.sistema-multmais.index', compact(
            'tiposDePlanoVendido',
            'empresaGeral',
            'tbdmFornecedor',
            'tables',
            'tbdmApiGrupo',
            'tbdmApiSubGrupo',
            'destinoDosValores',
            'tipoDeBoletagem',
            'tipoDeAdquirentes',
            'tbDmMsgCateg',
            'tbDmCanalCm',
            'users'
        ));
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
    public function store(Request $request) {}

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
    public function edit(string $id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function storeConexoesBcEmp(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'bc_fornec' => 'required',
                'bc_emp_host' => 'required',
                'bc_emp_porta' => 'required',
                'bc_emp_nome' => 'required',
                'bc_emp_user' => 'required',
                'bc_emp_pass' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $conexao = ConexoesBcEmp::where('emp_id', '=', $request->emp_id)->first();

            if ($conexao) {
                return response()->json([
                    'title' => 'Erro',
                    'text' => 'Já existe uma conexão para essa empresa.',
                    'type' => 'error',

                ], Response::HTTP_NOT_ACCEPTABLE);
            }

            DB::beginTransaction();
            $conexao = DB::table('tbsy_conexoes_bc_emp')->insert([
                "bc_emp_ident" => $request->bc_emp_ident,
                "bc_emp_host" => Crypt::encryptString($request->bc_emp_host),
                "bc_emp_porta" => Crypt::encryptString($request->bc_emp_porta),
                "bc_emp_nome" => Crypt::encryptString($request->bc_emp_nome),
                "bc_emp_user" => Crypt::encryptString($request->bc_emp_user),
                "bc_emp_pass" => Crypt::encryptString($request->bc_emp_pass),
                "bc_emp_token" => $request->bc_emp_token,
                "bc_emp_sslmo" => $request->bc_emp_sslmo,
                "bc_emp_sslce" => $request->bc_emp_sslce,
                "bc_emp_sslky" => $request->bc_emp_sslky,
                "bc_emp_sslca" => $request->bc_emp_sslca,
                "bc_emp_toconex" => $request->bc_emp_toconex,
                "bc_emp_tocons" => $request->bc_emp_tocons,
                "bc_emp_pooling" => $request->bc_emp_pooling,
                "bc_emp_charset" => $request->bc_emp_charset,
                "bc_emp_tzone" => $request->bc_emp_tzone,
                "bc_emp_appname" => $request->bc_emp_appname,
                "bc_emp_keepalv" => $request->bc_emp_keepalv,
                "bc_emp_compress" => $request->bc_emp_compress,
                "bc_emp_readonly" => $request->bc_emp_readonly,
                "bc_fornec" => $request->bc_fornec,
                "emp_id" => $request->emp_id,
            ]);

            if ($conexao) {
                DB::commit();
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro criado com sucesso!',
                    'type' => 'success',
                    'data' => $conexao
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text' => 'Registro não encontrado!',
                'type' => 'error'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'title' => 'Erro',
                'text' => $th->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    public function updateConexoesBcEmp(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'bc_fornec' => 'required',
                'bc_emp_host' => 'required',
                'bc_emp_porta' => 'required',
                'bc_emp_nome' => 'required',
                'bc_emp_user' => 'required',
                'bc_emp_pass' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $conexao = DB::table('tbsy_conexoes_bc_emp')->where('emp_id', '=', $request->emp_id)->update([
                "bc_emp_ident" => $request->bc_emp_ident,
                "bc_emp_host" => Crypt::encryptString($request->bc_emp_host),
                "bc_emp_porta" => Crypt::encryptString($request->bc_emp_porta),
                "bc_emp_nome" => Crypt::encryptString($request->bc_emp_nome),
                "bc_emp_user" => Crypt::encryptString($request->bc_emp_user),
                "bc_emp_pass" => Crypt::encryptString($request->bc_emp_pass),
                "bc_emp_token" => $request->bc_emp_token,
                "bc_emp_sslmo" => $request->bc_emp_sslmo,
                "bc_emp_sslce" => $request->bc_emp_sslce,
                "bc_emp_sslky" => $request->bc_emp_sslky,
                "bc_emp_sslca" => $request->bc_emp_sslca,
                "bc_emp_toconex" => $request->bc_emp_toconex,
                "bc_emp_tocons" => $request->bc_emp_tocons,
                "bc_emp_pooling" => $request->bc_emp_pooling,
                "bc_emp_charset" => $request->bc_emp_charset,
                "bc_emp_tzone" => $request->bc_emp_tzone,
                "bc_emp_appname" => $request->bc_emp_appname,
                "bc_emp_keepalv" => $request->bc_emp_keepalv,
                "bc_emp_compress" => $request->bc_emp_compress,
                "bc_emp_readonly" => $request->bc_emp_readonly,
                "bc_fornec" => $request->bc_fornec,
            ]);

            if ($conexao) {
                DB::commit();
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro alterado com sucesso!',
                    'type' => 'success',
                    'data' => $conexao
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text' => 'Registro não encontrado!',
                'type' => 'error'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'title' => 'Erro',
                'text' => $th->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    public function editConexoesBcEmp($emp_id)
    {
        try {
            $conexao = ConexoesBcEmp::where('emp_id', '=', $emp_id)->first();
            $conexao->bc_emp_host = Crypt::decryptString($conexao->bc_emp_host);
            $conexao->bc_emp_porta = Crypt::decryptString($conexao->bc_emp_porta);
            $conexao->bc_emp_nome = Crypt::decryptString($conexao->bc_emp_nome);
            $conexao->bc_emp_user = Crypt::decryptString($conexao->bc_emp_user);
            $conexao->bc_emp_pass = Crypt::decryptString($conexao->bc_emp_pass);
            if ($conexao) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Resposta obtida com sucesso!',
                    'type' => 'success',
                    'data' => $conexao
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

    public function getObterEmpresas(Request $request)
    {
        $parametro = $request != null ? $request->all()['parametro'] : '';

        if (empty($parametro)) {
            return [];
        }

        return Empresa::select(DB::raw('emp_id as id, emp_id, emp_cnpj, UPPER(emp_nmult) text'))
            ->where("emp_nmult", "LIKE", '%' . $parametro . '%')
            ->get()
            ->toArray();
    }

    public function getObterGridPesquisa(Request $request)
    {
        if (!Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, "Usuário não autenticado...");
        }

        $data = new Collection();

        if (!empty($request->emp_id)) {

            if (is_numeric($request->emp_id)) {
                if (!empty($request->fornec_bd)) {
                    $data = ConexoesBcEmp::where('emp_id', '=', $request->emp_id)->where('bc_fornec', '=', $request->fornec_bd)->get();
                } else {
                    $data = ConexoesBcEmp::where('emp_id', '=', $request->emp_id)->get();
                }
            } else {
                $empresasGeral = Empresa::where('emp_nmult', 'like', '%' . $request->emp_id . '%')->get(['emp_id'])->pluck('emp_id')->toArray();
                if ($empresasGeral) {
                    if (!empty($request->fornec_bd)) {
                        $data = ConexoesBcEmp::whereIn('emp_id', $empresasGeral)->where('bc_fornec', '=', $request->fornec_bd)->get();
                    } else {
                        $data = ConexoesBcEmp::whereIn('emp_id', $empresasGeral)->get();
                    }
                }
            }
        }

        $this->permissions = Auth::user()->getAllPermissions()->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';

                if (in_array('config-sistema-multmais.edit', $this->permissions)) {

                    $btn .= '<button type="button" data-emp-id="' . $row->emp_id . '" class="btn btn-primary btn-sm mr-1 btn-conexao-db" title="Editar"><i class="fas fa-edit"></i></a>';
                }

                $btn .= '';

                return $btn;
            })->editColumn('fornecedor', function ($row) {
                return $row->fornecedor->fornec_desc;
            })->editColumn('empresa', function ($row) {

                return $row->empresa->emp_nfant;
            })->editColumn('empresa_sts', function ($row) {
                $badge = '';
                if (!empty($row->empresa->status)) {

                    switch ($row->empresa->status->emp_sts) {

                        case 'AT':
                            $badge = '<span class="badge badge-success">' . $row->empresa->status->emp_sts_desc . '</span>';
                            break;
                        case 'NA':
                        case 'IN':
                        case 'ON':
                            $badge = '<span class="badge badge-warning">' . $row->empresa->status->emp_sts_desc . '</span>';
                            break;
                        case 'EX':
                        case 'BL':
                            $badge = '<span class="badge badge-danger">' . $row->empresa->status->emp_sts_desc . '</span>';
                            break;
                    }
                }

                return $badge;
            })
            ->rawColumns(['action', 'empresa_sts'])
            ->make(true);
    }

    public function storeAlias(Request $request)
    {
        try {


            $validator = Validator::make($request->all(), [
                'emp_tab_name' => 'required',
                'emp_tab_alias' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $aliasEx = TbSyTabAlias::where('emp_tab_name', '=', $request->emp_tab_name)
                ->where('emp_tab_alias', '=', $request->emp_tab_alias)->where('emp_id', '=', $request->emp_id)->first();
            if ($aliasEx) {
                return response()->json([
                    'title' => 'Erro',
                    'text' => 'Já existe um Alias cadastrado.',
                    'type' => 'error',
                ], Response::HTTP_NOT_FOUND);
            }

            DB::beginTransaction();
            $alias = DB::table('tbsy_tab_alias')->insert([
                "emp_tab_name" => $request->emp_tab_name,
                "emp_tab_alias" => $request->emp_tab_alias,
                "emp_id" => $request->emp_id,
            ]);

            if ($alias) {
                DB::commit();
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro criado com sucesso!',
                    'type' => 'success',
                    'data' => $alias
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

    public function updateAlias(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'emp_tab_name' => 'required',
                'emp_tab_alias' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $aliasEx = TbSyTabAlias::where('emp_tab_alias', '=', $request->emp_tab_alias)
                ->where('emp_id', '=', $request->emp_id)->first();
            if ($aliasEx) {
                return response()->json([
                    'title' => 'Erro',
                    'text' => 'Já existe um Alias cadastrado!',
                    'type' => 'error',
                ]);
            }

            $alias = DB::table('tbsy_tab_alias')->where('emp_tab_name', '=', $request->emp_tab_name)->where('emp_id', '=', $request->emp_id)
                ->update([
                    'emp_tab_alias' => $request->emp_tab_alias,
                ]);

            if ($alias) {

                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Alias atualizado com sucesso!',
                    'type' => 'success',
                    'data' => $alias
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text' => 'Alias não encontrado!',
                'type' => 'error',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text' => $th->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    public function editAlias(Request $request, $emp_id)
    {
        try {
            $alias = TbSyTabAlias::where('emp_tab_name', '=', $request->tab_name)->where('emp_id', '=', $emp_id)->first();
            if ($alias) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Resposta obtida com sucesso!',
                    'type' => 'success',
                    'data' => $alias
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

    public function destroyAlias(Request $request, $emp_id)
    {
        try {

            $alias = DB::table('tbsy_tab_alias')->where('emp_tab_name', '=', $request->tab_name)->where('emp_id', '=', $request->emp_id)->delete();

            if ($alias) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro deletado com sucesso!',
                    'type' => 'success',
                    'btnPesquisar' => 'btnPesquisarAlias'
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

    public function getObterGridPesquisaAlias(Request $request)
    {
        if (!Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, "Usuário não autenticado...");
        }

        $data = new Collection();

        if (!empty($request->emp_id)) {

            if (is_numeric($request->emp_id)) {
                $data = TbSyTabAlias::where('emp_id', '=', $request->emp_id)->get();
            } else {
                $empresasGeral = Empresa::where('emp_nmult', 'like', '%' . $request->emp_id . '%')->get(['emp_id'])->pluck('emp_id')->toArray();
                if ($empresasGeral) {
                    $data = TbSyTabAlias::whereIn('emp_id', $empresasGeral)->get();
                }
            }
        }

        $this->permissions = Auth::user()->getAllPermissions()->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';

                if (in_array('config-sistema-multmais.edit', $this->permissions)) {

                    $btn .= '<button type="button" data-emp-id="' . $row->emp_id . '" data-tab-name="' . $row->emp_tab_name . '"  class="btn btn-primary btn-sm mr-1 btn-alias-db" title="Editar"><i class="fas fa-edit"></i></a>';
                }

                $btn .= '<button href="#" class="btn btn-sm btn-primary mr-1 delete_id" data-url="destroy-alias" data-tab-name="' . $row->emp_tab_name . '" data-emp-id="' . $row->emp_id . '" title="Excluir"><i class="far fa-trash-alt"></i></button>';

                $btn .= '';

                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    //APIs
    public function storeApis(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'emp_id' => 'required',
                'bc_fornec_api' => 'required',
                'api_grupo_api' => 'required',
                'api_subgrp_api' => 'required',
                'api_emp_endpoint' => 'required',
                'api_emp_mtdo' => 'required',
                'api_emp_tpde' => 'required',
                'api_emp_tpda' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $apiInsert = DB::connection('dbsysclient')
                ->table('tbcf_conexoes_api_emp')
                ->where('emp_id', '=', $request->emp_id)
                ->where('bc_fornec', '=', $request->bc_fornec_api)
                ->where('api_grupo', '=', $request->api_grupo_api)
                ->where('api_subgrp', '=', $request->api_subgrp_api)
                ->first();

            if ($apiInsert) {
                return response()->json([
                    'title' => 'Erro',
                    'text' => 'Já existe uma API para essa empresa com o mesmo fornecedor, grupo e subgrupo.',
                    'type' => 'error'
                ], Response::HTTP_BAD_REQUEST);
            }

            $input = $request->all();
            $input['bc_fornec'] = $request->bc_fornec_api;
            $input['api_grupo'] = $request->api_grupo_api;
            $input['api_subgrp'] = $request->api_subgrp_api;
            unset($input['_method']);
            unset($input['_token']);
            unset($input['bc_fornec_api']);
            unset($input['api_grupo_api']);
            unset($input['api_subgrp_api']);
            unset($input['t_name']);
            unset($input['v_id']);
            unset($input['tabela_bdm']);
            $apiInsert = DB::connection('dbsysclient')->table('tbcf_conexoes_api_emp')->insert(
                $input
            );
            if ($apiInsert) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'API cadastrada com sucesso!',
                    'type' => 'success',
                    'data' => $apiInsert
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text' => $th->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    public function updateApis(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'emp_id' => 'required',
                'bc_fornec_api' => 'required',
                'api_grupo_api' => 'required',
                'api_subgrp_api' => 'required',
                'api_emp_endpoint' => 'required',
                'api_emp_mtdo' => 'required',
                'api_emp_tpde' => 'required',
                'api_emp_tpda' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $input = $request->all();
            unset($input['_method']);
            unset($input['_token']);
            unset($input['bc_fornec_api']);
            unset($input['api_grupo_api']);
            unset($input['api_subgrp_api']);
            unset($input['emp_id']);
            unset($input['t_name']);
            unset($input['v_id']);
            unset($input['tabela_bdm']);

            $apiUpdate = DB::connection('dbsysclient')->table('tbcf_conexoes_api_emp')
                ->where('emp_id', '=', $request->emp_id)
                ->where('bc_fornec', '=', $request->bc_fornec_api)
                ->where('api_grupo', '=', $request->api_grupo_api)
                ->where('api_subgrp', '=', $request->api_subgrp_api)
                ->update($input);

            if ($apiUpdate) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'API atualizada com sucesso!',
                    'type' => 'success',
                    'data' => $apiUpdate
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text' => 'API não encontrado!',
                'type' => 'error',
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text' => $th->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    public function editApis(Request $request, $id)
    {
        try {

            $apis = DB::connection('dbsysclient')
                ->table('tbcf_conexoes_api_emp')
                ->where('emp_id', '=', $request->emp_id)
                ->where('bc_fornec', '=', $request->bc_fornec)
                ->where('api_grupo', '=', $request->api_grupo)
                ->where('api_subgrp', '=', $request->api_subgrp)
                ->first();

            if ($apis) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Resposta obtida com sucesso!',
                    'type' => 'success',
                    'data' => $apis
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

    public function destroyApis(Request $request, $id)
    {
        try {

            $alias = DB::connection('dbsysclient')->table('tbcf_conexoes_api_emp')
                ->where('bc_fornec', '=', $request->fornec)
                ->where('api_grupo', '=', $request->grupo)
                ->where('api_subgrp', '=', $request->subgrp)
                ->where('emp_id', '=', $request->emp_id)->delete();

            if ($alias) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro deletado com sucesso!',
                    'type' => 'success',
                    'btnPesquisar' => 'btnPesquisarFapi'
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

    public function getObterGridPesquisaApis(Request $request)
    {

        if (!Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, "Usuário não autenticado...");
        }

        $data = new Collection();

        $query = "";

        if (!empty($request->bc_fornec_apis)) {

            $query .= "bc_fornec = " . quotedstr($request->bc_fornec_apis) . " AND ";
        }

        if (!empty($request->api_grupo)) {

            $query .= "api_grupo = " . quotedstr($request->api_grupo) . " AND ";
        }

        if (!empty($request->api_subgrp)) {
            $query .= "api_subgrp = " . quotedstr($request->api_subgrp) . " AND ";
        }

        if (!empty($request->emp_id)) {

            if (is_numeric($request->emp_id)) {

                $query .= "emp_id = " . $request->emp_id;
                $data = ConexoesAPI::whereRaw(DB::raw($query))->get();
            } else {

                $empresasGeral = Empresa::where('emp_nmult', 'like', '%' . $request->emp_id . '%')->get(['emp_id'])->pluck('emp_id')->toArray();
                if ($empresasGeral) {
                    $query .= selectItens($empresasGeral, 'emp_id');
                    $data = ConexoesAPI::whereRaw(DB::raw($query))->get();
                }
            }
        }

        $this->permissions = Auth::user()->getAllPermissions()->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';

                if (in_array('config-sistema-multmais.edit', $this->permissions)) {

                    $btn .= '<button type="button" data-emp-id="' . $row->emp_id . '" ';
                    $btn .= 'data-fornec="' . $row->bc_fornec . '" ';
                    $btn .= 'data-grupo="' . $row->api_grupo . '" ';
                    $btn .= 'data-subgrp="' . $row->api_subgrp . '" ';
                    $btn .= 'class="btn btn-primary btn-sm mr-1 btn-apis-db" title="Editar"><i class="fas fa-edit"></i></a>';
                }

                if (in_array('config-sistema-multmais.destroy', $this->permissions)) {

                    $btn .= '<button href="#" class="btn btn-sm btn-primary mr-1 delete_id " ';
                    $btn .= 'data-emp-id="' . $row->emp_id . '" ';
                    $btn .= 'data-fornec="' . $row->bc_fornec . '" ';
                    $btn .= 'data-grupo="' . $row->api_grupo . '" ';
                    $btn .= 'data-subgrp="' . $row->api_subgrp . '" data-url="destroy-apis" ';
                    $btn .= 'title="Excluir"><i class="far fa-trash-alt"></i></button>';
                }

                return $btn;
            })->editColumn('bc_fornec', function ($row) {
                return $row->fornecedor->fornec_desc;
            })->editColumn('api_grupo', function ($row) {
                return $row->grupo->api_grupo_desc;
            })->editColumn('api_subgrp', function ($row) {
                return $row->subgrupo->api_subgrp_desc;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    //Padrões de planos
    public function storePdPlan(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'tp_plano' => 'required',
                'emp_destvlr' => 'required',
                'emp_tpbolet' => 'required',
                'emp_adqrnt' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $padroes = DB::connection('dbsysclient')->table('tbcf_padroes_planos')
                ->where('emp_id', '=', $request->emp_id)
                ->where('tp_plano', '=', $request->tp_plano)
                ->first();

            if ($padroes) {
                return response()->json([
                    'title' => 'Erro',
                    'text' => 'Já existe uma conexão para essa empresa.',
                    'type' => 'error',

                ], Response::HTTP_NOT_ACCEPTABLE);
            }

            DB::beginTransaction();
            $input = $request->all();
            unset($input['_method']);
            unset($input['_token']);
            unset($input['t_name']);
            unset($input['v_id']);
            unset($input['tabela_bdm']);
            $padroes = DB::connection('dbsysclient')->table('tbcf_padroes_planos')->insert($input);

            if ($padroes) {
                DB::commit();
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro criado com sucesso!',
                    'type' => 'success',
                    'data' => $padroes
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text' => 'Registro não encontrado!',
                'type' => 'error'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'title' => 'Erro',
                'text' => $th->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    public function editPdPlan(Request $request, $id)
    {
        try {

            $padrao = DB::connection('dbsysclient')
                ->table('tbcf_padroes_planos')
                ->where('emp_id', '=', $id)
                ->where('tp_plano', '=', $request->tp_plano)
                ->first();

            if ($padrao) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Resposta obtida com sucesso!',
                    'type' => 'success',
                    'data' => $padrao
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

    public function updatePdPlan(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'tp_plano' => 'required',
                'emp_destvlr' => 'required',
                'emp_tpbolet' => 'required',
                'emp_adqrnt' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            DB::beginTransaction();
            $input = $request->all();
            unset($input['_method']);
            unset($input['_token']);
            unset($input['emp_id']);
            unset($input['tp_plano']);
            unset($input['t_name']);
            unset($input['v_id']);
            unset($input['tabela_bdm']);

            $padroes = DB::connection('dbsysclient')->table('tbcf_padroes_planos')
                ->where('emp_id', '=', $request->emp_id)
                ->where('tp_plano', '=', $request->tp_plano)->update($input);

            if ($padroes) {
                DB::commit();
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro atualizado com sucesso!',
                    'type' => 'success',
                    'data' => $padroes
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

    public function destroyPdPlan(Request $request, $id)
    {
        try {

            $padroes = DB::connection('dbsysclient')->table('tbcf_padroes_planos')
                ->where('emp_id', '=', $request->emp_id)
                ->where('tp_plano', '=', $request->tp_plano)->delete();

            if ($padroes) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro deletado com sucesso!',
                    'type' => 'success',
                    'btnPesquisar' => 'btnPesquisarTpPlano'
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

    public function getObterGridPesquisaPdPlan(Request $request)
    {

        if (!Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, "Usuário não autenticado...");
        }

        $data = new Collection();

        $query = "";

        if (!empty($request->tp_plano_pesquisa)) {

            $query .= "tp_plano = " . quotedstr($request->tp_plano_pesquisa) . " AND ";
        }

        if (!empty($request->emp_id)) {

            if (is_numeric($request->emp_id)) {

                $query .= "emp_id = " . $request->emp_id;
                $data = DB::connection('dbsysclient')->table('tbcf_padroes_planos')->whereRaw(DB::raw($query))->get();
            } else {

                $empresasGeral = Empresa::where('emp_nmult', 'like', '%' . $request->emp_id . '%')->get(['emp_id'])->pluck('emp_id')->toArray();
                if ($empresasGeral) {
                    $query .= selectItens($empresasGeral, 'emp_id');
                    $data = DB::connection('dbsysclient')->table('tbcf_padroes_planos')->whereRaw(DB::raw($query))->get();
                }
            }
        }

        $this->permissions = Auth::user()->getAllPermissions()->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';

                if (in_array('config-sistema-multmais.edit', $this->permissions)) {

                    $btn .= '<button type="button" data-emp-id="' . $row->emp_id . '" ';
                    $btn .= 'data-tp-plano="' . $row->tp_plano . '" ';
                    $btn .= 'class="btn btn-primary btn-sm mr-1 btn-tp-plano" title="Editar"><i class="fas fa-edit"></i></a>';
                }

                if (in_array('config-sistema-multmais.destroy', $this->permissions)) {

                    $btn .= '<button href="#" class="btn btn-sm btn-primary mr-1 delete_id " ';
                    $btn .= 'data-emp-id="' . $row->emp_id . '" ';
                    $btn .= 'data-tp-plano="' . $row->tp_plano . '" ';
                    $btn .= 'data-url="destroy-padroes-de-planos" ';
                    $btn .= 'title="Excluir"><i class="far fa-trash-alt"></i></button>';
                }

                return $btn;
            })->editColumn('tp_plano', function ($row) {
                return DB::connection('dbsysclient')->table('tbdm_tpplanovd')->where('tp_plano', '=', $row->tp_plano)->first()->tp_plano_desc;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    //White Label
    public function storeWhiteLabel(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [

                'text_color_df' => 'required',
                'fd_color' => 'required',
                'fdsel_color' => 'required',
                'ft_color' => 'required',
                'ftsel_color' => 'required',
                'bg_menu_ac_color' => 'required',
                'bg_item_menu_ac_color' => 'required',
                'menu_ac_color' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $padroes = DB::connection('dbsysclient')->table('tbcf_config_wl')
                ->where('emp_id', '=', $request->emp_id)
                ->first();

            if ($padroes) {
                return response()->json([
                    'title' => 'Erro',
                    'text' => 'Já existe um White Label para essa empresa.',
                    'type' => 'error',

                ], Response::HTTP_NOT_ACCEPTABLE);
            }

            $destinationPath = storage_path() . '/app/public/white-label/empresa-' . $request->emp_id . '/';
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file = public_path('assets/dist/css/multban.min.css');
            File::copy($file, $destinationPath . 'multban.min.css');

            $filename = $destinationPath . 'multban.min.css';

            $search = [
                '--bg-menu-active-multban: #D6C5FF;',
                '--bg-item-menu-active-multban: #FFFFFF;',
                '--text-menu-active-multban: #1c0065;',
                '--text-color: #212529;',
                '--primary-multban: #1c0065;',
                '--primary-hover-multban: #371885;',
                '--secondary-multban: #a702d8;',
                '--secondary-hover-multban: #ae22d8;',
                '--secondary-hover-bd-multban: #951db9;',
                '--secondary-disabled-multban: #d635dc;',
                '--secondary-disabled-bd-multban: #c035dc;',
            ];

            $insert = [
                '--bg-menu-active-multban: ' . $request->bg_menu_ac_color . ';',
                '--bg-item-menu-active-multban: ' . $request->bg_item_menu_ac_color . ';',
                '--text-menu-active-multban: ' . $request->menu_ac_color . ';',
                '--text-color: ' . $request->text_color_df . ';',
                '--primary-multban: ' . $request->fd_color . ';',
                '--primary-hover-multban: ' . $request->fdsel_color . ';',
                '--secondary-multban: ' . $request->ft_color . ';',
                '--secondary-hover-multban: ' . $request->ftsel_color . ';',
                '--secondary-hover-bd-multban: ' . $request->ftsel_color . ';',
                '--secondary-disabled-multban: ' . $request->ft_color . ';',
                '--secondary-disabled-bd-multban: ' . $request->ft_color . ';',
            ];

            $replace = $insert;

            file_put_contents($filename, str_replace($search, $replace, file_get_contents($filename)));

            $image_name = '';
            $image_name_logo_h = '';

            if ($request->hasFile('mini_logo')) {

                $image = $request->file('mini_logo');

                $image_name = 'mini-logo.' . $image->getClientOriginalExtension();

                $destinationPath = storage_path() . '/app/public/white-label/empresa-' . $request->emp_id . '/';

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $resize_image = Image::read($image);

                $resize_image->resize(110, 110, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $resize_image->save($destinationPath . $image_name);
            }

            if ($request->hasFile('logo_h')) {

                $image = $request->file('logo_h');

                $image_name_logo_h = 'logo-h.' . $image->getClientOriginalExtension();

                $destinationPath = storage_path() . '/app/public/white-label/empresa-' . $request->emp_id . '/';

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $resize_image = Image::read($image);

                $resize_image->resize(110, 55, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $resize_image->save($destinationPath . $image_name_logo_h);
            }

            DB::beginTransaction();
            $input = $request->all();
            unset($input['_method']);
            unset($input['_token']);
            unset($input['t_name']);
            unset($input['v_id']);
            unset($input['tabela_bdm']);
            unset($input['emp_tab_alias']);
            $input['mini_logo'] = $image_name;
            $input['logo_h'] = $image_name_logo_h;

            $padroes = DB::connection('dbsysclient')->table('tbcf_config_wl')->insert($input);

            if ($padroes) {
                DB::commit();

                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro criado com sucesso!',
                    'type' => 'success',
                    'data' => $padroes
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text' => 'Registro não encontrado!',
                'type' => 'error'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'title' => 'Erro',
                'text' => $th->getMessage() . ', Line: ' .  $th->getLine(),
                'type' => 'error'
            ], 500);
        }
    }

    public function editWhiteLabel(Request $request, $id)
    {
        try {

            $padrao = DB::connection('dbsysclient')
                ->table('tbcf_config_wl')
                ->where('emp_id', '=', $request->emp_id)
                ->first();

            if ($padrao) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Resposta obtida com sucesso!',
                    'type' => 'success',
                    'data' => $padrao
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

    public function updateWhiteLabel(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [

                'text_color_df' => 'required',
                'fd_color' => 'required',
                'fdsel_color' => 'required',
                'ft_color' => 'required',
                'ftsel_color' => 'required',
                'bg_menu_ac_color' => 'required',
                'bg_item_menu_ac_color' => 'required',
                'menu_ac_color' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $destinationPath = storage_path() . '/app/public/white-label/empresa-' . $request->emp_id . '/';
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file = public_path('assets/dist/css/multban.min.css');
            File::copy($file, $destinationPath . 'multban.min.css');

            $filename = $destinationPath . 'multban.min.css';

            $search = [
                '--bg-menu-active-multban: #D6C5FF;',
                '--bg-item-menu-active-multban: #FFFFFF;',
                '--text-menu-active-multban: #1c0065;',
                '--text-color: #212529;',
                '--primary-multban: #1c0065;',
                '--primary-hover-multban: #371885;',
                '--secondary-multban: #a702d8;',
                '--secondary-hover-multban: #ae22d8;',
                '--secondary-hover-bd-multban: #951db9;',
                '--secondary-disabled-multban: #d635dc;',
                '--secondary-disabled-bd-multban: #c035dc;',
            ];

            $insert = [
                '--bg-menu-active-multban: ' . $request->bg_menu_ac_color . ';',
                '--bg-item-menu-active-multban: ' . $request->bg_item_menu_ac_color . ';',
                '--text-menu-active-multban: ' . $request->menu_ac_color . ';',
                '--text-color: ' . $request->text_color_df . ';',
                '--primary-multban: ' . $request->fd_color . ';',
                '--primary-hover-multban: ' . $request->fdsel_color . ';',
                '--secondary-multban: ' . $request->ft_color . ';',
                '--secondary-hover-multban: ' . $request->ftsel_color . ';',
                '--secondary-hover-bd-multban: ' . $request->ftsel_color . ';',
                '--secondary-disabled-multban: ' . $request->ft_color . ';',
                '--secondary-disabled-bd-multban: ' . $request->ft_color . ';',
            ];

            $replace = $insert;

            file_put_contents($filename, str_replace($search, $replace, file_get_contents($filename)));

            $image_name = '';
            $image_name_logo_h = '';

            if ($request->hasFile('mini_logo')) {

                $image = $request->file('mini_logo');

                $image_name = 'mini-logo.' . $image->getClientOriginalExtension();

                $destinationPath = storage_path() . '/app/public/white-label/empresa-' . $request->emp_id . '/';

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $resize_image = Image::read($image);

                $resize_image->resize(110, 110, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $resize_image->save($destinationPath . $image_name);
            }

            if ($request->hasFile('logo_h')) {

                $image = $request->file('logo_h');

                $image_name_logo_h = 'logo-h.' . $image->getClientOriginalExtension();

                $destinationPath = storage_path() . '/app/public/white-label/empresa-' . $request->emp_id . '/';

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $resize_image = Image::read($image);

                $resize_image->resize(110, 55, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $resize_image->save($destinationPath . $image_name_logo_h);
            }

            DB::beginTransaction();
            $input = $request->all();
            unset($input['_method']);
            unset($input['_token']);
            unset($input['t_name']);
            unset($input['v_id']);
            unset($input['tabela_bdm']);
            unset($input['emp_id']);
            $input['mini_logo'] = $image_name;
            $input['logo_h'] = $image_name_logo_h;

            $whiteLabel = DB::connection('dbsysclient')->table('tbcf_config_wl')->where('emp_id', $request->emp_id)->update($input);

            if ($whiteLabel) {
                DB::commit();

                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro criado com sucesso!',
                    'type' => 'success',
                    'data' => $whiteLabel
                ]);
            }

            return response()->json([
                'title' => 'Erro',
                'text' => 'Registro não encontrado!',
                'type' => 'error'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'title' => 'Erro',
                'text' => $th->getMessage() . ', Line: ' .  $th->getLine(),
                'type' => 'error'
            ], 500);
        }
    }

    public function destroyWhiteLabel(Request $request, $id)
    {
        try {

            $padroes = DB::connection('dbsysclient')->table('tbcf_config_wl')
                ->where('emp_id', '=', $request->emp_id)->delete();


            if (Storage::disk('public')->exists('white-label/empresa-' . $request->emp_id . '/multban.min.css')) {
                Storage::disk('public')->delete('white-label/empresa-' . $request->emp_id . '/multban.min.css');
            }

            if (Storage::disk('public')->exists('white-label/empresa-' . $request->emp_id . '/logo-h.png')) {
                Storage::disk('public')->delete('white-label/empresa-' . $request->emp_id . '/logo-h.png');
            }

            if (Storage::disk('public')->exists('white-label/empresa-' . $request->emp_id . '/mini-logo.png')) {
                Storage::disk('public')->delete('white-label/empresa-' . $request->emp_id . '/mini-logo.png');
            }

            if ($padroes) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro deletado com sucesso!',
                    'type' => 'success',
                    'btnPesquisar' => 'btnPesquisarWl'
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

    public function getObterGridPesquisaWhiteLabel(Request $request)
    {

        if (!Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, "Usuário não autenticado...");
        }

        $data = new Collection();

        $query = "";

        if (!empty($request->emp_id)) {

            if (is_numeric($request->emp_id)) {

                $query .= "emp_id = " . $request->emp_id;
                $data = DB::connection('dbsysclient')->table('tbcf_config_wl')->whereRaw(DB::raw($query))->get();
            } else {

                $empresasGeral = Empresa::where('emp_nmult', 'like', '%' . $request->emp_id . '%')->get(['emp_id'])->pluck('emp_id')->toArray();
                if ($empresasGeral) {
                    $query .= selectItens($empresasGeral, 'emp_id');
                    $data = DB::connection('dbsysclient')->table('tbcf_config_wl')->whereRaw(DB::raw($query))->get();
                }
            }
        }

        $this->permissions = Auth::user()->getAllPermissions()->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';

                if (in_array('config-sistema-multmais.edit', $this->permissions)) {

                    $btn .= '<button type="button" data-emp-id="' . $row->emp_id . '" ';
                    $btn .= 'class="btn btn-primary btn-sm mr-1 btn-white-label" title="Editar"><i class="fas fa-edit"></i></a>';
                }

                if (in_array('config-sistema-multmais.destroy', $this->permissions)) {

                    $btn .= '<button href="#" class="btn btn-sm btn-primary mr-1 delete_id " ';
                    $btn .= 'data-emp-id="' . $row->emp_id . '" ';
                    $btn .= 'data-url="destroy-white-label" ';
                    $btn .= 'title="Excluir"><i class="far fa-trash-alt"></i></button>';
                }

                return $btn;
            })->editColumn('empresa', function ($row) {
                return Empresa::find($row->emp_id)->emp_nfant;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function createDm(Request $request)
    {
        $columnDefinitions = Schema::connection('dbsysclient')->getColumns($request->tabela_bdm);

        $count = 1;
        $form = '<div class="form-row">';

        foreach ($columnDefinitions as $key => $value) {

            $form .= '<div class="form-group col-md-12"><label for="' . $value['name'] . '">' . $value['name'] . '</label>';
            $form .= '<input ';
            $form .= ' maxlength="' . str_replace(["varchar(", ")"], ["", ""], $value['type']) . '"';
            $form .= ' class="form-control  form-control-sm" type="text" id="' . $value['name'] . '" name="' . $value['name'] . '" value=""/></div>';

            if ($count % 3 == 0) {

                $form .= '</div><div class="form-row">';
            }

            $count++;
        }

        $form .= '</div>';

        return response()->json([
            'title' => 'Sucesso',
            'text' => 'Resposta obtida com sucesso!',
            'type' => 'success',
            'data' => $columnDefinitions,
            'form' => $form
        ]);
    }

    public function editDm(Request $request)
    {
        try {

            $tbdm = DB::connection('dbsysclient')
                ->table(Crypt::decryptString($request->name))
                ->whereRaw(DB::raw(Crypt::decryptString($request->id)))
                ->first();

            $count = 1;

            $form = '<div class="form-row">';

            foreach ($tbdm as $key => $value) {
                $form .= '<div class="form-group col-md-12"><label for="' . $key . '">' . $key . '</label><input class="form-control  form-control-sm" type="text" id="' . $key . '" name="' . $key . '" value="' . $value . '"/></div>';

                if ($count % 3 == 0) {

                    $form .= '</div><div class="form-row">';
                }

                $count++;
            }

            $form .= '</div>';

            if ($tbdm) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Resposta obtida com sucesso!',
                    'type' => 'success',
                    'data' => $tbdm,
                    'form' => $form,
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

    public function storeDm(Request $request)
    {
        try {

            DB::beginTransaction();

            $columnDefinitions = Schema::connection('dbsysclient')->getColumns($request->tabela_bdm);

            $validatorFields = [];

            foreach ($columnDefinitions as $key => $value) {
                $valor = $value['nullable'] == true ? '' : 'required';
                if ($value['type_name'] == "varchar") {
                    $valor .= '|max:' . str_replace(["varchar(", ")"], ["", ""], $value['type']);
                }

                $validatorFields[$value['name']] = $valor;
            }

            $validator = Validator::make($request->all(), $validatorFields);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $input = $request->all();
            unset($input['_method']);
            unset($input['_token']);
            unset($input['emp_id']);
            unset($input['t_name']);
            unset($input['v_id']);
            unset($input['tabela_bdm']);

            $tbdm = DB::connection('dbsysclient')
                ->table($request->tabela_bdm)
                ->insert($input);

            if ($tbdm) {
                DB::commit();
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Dados atualizados com sucesso!',
                    'type' => 'success',
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
    public function updateDm(Request $request)
    {
        try {

            DB::beginTransaction();
            $columnDefinitions = Schema::connection('dbsysclient')->getColumns($request->tabela_bdm);

            $validatorFields = [];

            foreach ($columnDefinitions as $key => $value) {
                $valor = $value['nullable'] == true ? '' : 'required';
                if ($value['type_name'] == "varchar") {
                    $valor .= '|max:' . str_replace(["varchar(", ")"], ["", ""], $value['type']);
                }

                $validatorFields[$value['name']] = $valor;
            }

            $validator = Validator::make($request->all(), $validatorFields);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $input = $request->all();
            unset($input['_method']);
            unset($input['_token']);
            unset($input['emp_id']);
            unset($input['t_name']);
            unset($input['v_id']);
            unset($input['tabela_bdm']);

            $tbdm = DB::connection('dbsysclient')
                ->table(Crypt::decryptString($request->t_name))
                ->whereRaw(DB::raw(Crypt::decryptString($request->v_id)))
                ->first();

            if (!$tbdm) {
                return response()->json([
                    'title' => 'Erro',
                    'text' => 'Registro não encontrado!',
                    'type' => 'error'
                ], Response::HTTP_NOT_FOUND);
            }

            $tbdm = DB::connection('dbsysclient')
                ->table(Crypt::decryptString($request->t_name))
                ->whereRaw(DB::raw(Crypt::decryptString($request->v_id)))
                ->update($input);

            return response()->json([
                'title' => 'Sucesso',
                'text' => 'Dados atualizados com sucesso!',
                'type' => 'success',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text' => $th->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    public function destroyDm(Request $request)
    {
        try {
            $tbdm = DB::connection('dbsysclient')
                ->table(Crypt::decryptString($request->name))
                ->whereRaw(DB::raw(Crypt::decryptString($request->id)))
                ->delete();

            if ($tbdm) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro deletado com sucesso!',
                    'type' => 'success',
                    'btnPesquisar' => 'btnPesquisarTbdm'
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

    public function getObterGridPesquisaDm(Request $request)
    {
        $this->req = $request;

        if (!Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, "Usuário não autenticado...");
        }

        $data = new Collection();

        $query = "";
        $columnDefinitions = new Collection();

        if (!empty($request->emp_id)) {


            $columnDefinitions = DB::connection('dbsysclient')->getSchemaBuilder()->getColumnListing($this->req->tabela_bdm);

            $data = DB::connection('dbsysclient')->table($request->tabela_bdm)->get();
        }

        $this->permissions = Auth::user()->getAllPermissions()->pluck('name')->toArray();

        $dataTables = DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';
                $attrData = '';
                $count = 0;

                foreach ($row as $key => $value) {

                    if ($count < 5) {
                        if (!empty($value)) {
                            $attrData .= $key . ' = ' . quotedstr($value) . ' AND ';
                        }
                    }
                    $count++;
                }

                $attrData = substr($attrData, 0, -4);

                if (in_array('config-sistema-multmais.edit', $this->permissions)) {

                    $btn .= '<button type="button" data-id="' . Crypt::encryptString($attrData)  . '" ';
                    $btn .= 'data-name="' . Crypt::encryptString($this->req->tabela_bdm) . '" ';
                    $btn .= 'class="btn btn-primary btn-sm mr-1 btn-dados-mestre" title="Editar"><i class="fas fa-edit"></i></a>';
                }

                if (in_array('config-sistema-multmais.destroy', $this->permissions)) {

                    $btn .= '<button href="#" class="btn btn-sm btn-primary mr-1 delete_id " ';
                    $btn .= 'data-id="' . Crypt::encryptString($attrData) . '" ';
                    $btn .= 'data-name="' . Crypt::encryptString($this->req->tabela_bdm) . '" ';
                    $btn .= 'data-url="destroy-dados-mestre" ';
                    $btn .= 'title="Excluir"><i class="far fa-trash-alt"></i></button>';
                }

                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);

        return response()->json(['dataTables' => $dataTables, 'columnDefinitions' => $columnDefinitions]);
    }

    public function storePdMsg(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'canal_id' => 'required',
                'msg_categ' => 'required',
                'msg_text' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $msg = DB::connection('dbsysclient')->table('tbcf_msg_comp')
                ->where('canal_id', '=', $request->canal_id)
                ->where('msg_categ', '=', $request->msg_categ)
                ->where('emp_id', '=', $request->emp_id)->first();

            if ($msg) {
                return response()->json([
                    'title' => 'Erro',
                    'text' => 'Já existe uma Mensagem cadastrada.',
                    'type' => 'error',
                ], Response::HTTP_NOT_FOUND);
            }

            DB::beginTransaction();
            $msg = DB::connection('dbsysclient')->table('tbcf_msg_comp')->insert([
                "canal_id" => $request->canal_id,
                "msg_categ" => $request->msg_categ,
                "msg_text" => $request->msg_text,
                "emp_id" => $request->emp_id,
            ]);

            if ($msg) {
                DB::commit();
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro criado com sucesso!',
                    'type' => 'success',
                    'data' => $msg
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

    public function updatePdMsg(Request $request)
    {
        try {

            $msg = DB::connection('dbsysclient')->table('tbcf_msg_comp')
                ->where('canal_id', '=', $request->canal_id)
                ->where('msg_categ', '=', $request->msg_categ)
                ->where('emp_id', '=', $request->emp_id)->update([
                    'msg_text' => $request->msg_text,
                ]);

            return response()->json([
                'title' => 'Sucesso',
                'text' => 'Mensagem atualizado com sucesso!',
                'type' => 'success',
                'data' => $msg
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Erro',
                'text' => $th->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    public function editPdMsg(Request $request, $emp_id)
    {
        try {

            $msg = DB::connection('dbsysclient')->table('tbcf_msg_comp')
                ->where('canal_id', '=', $request->canal_id)
                ->where('msg_categ', '=', $request->msg_categ)
                ->where('emp_id', '=', $request->emp_id)->first();

            if ($msg) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Resposta obtida com sucesso!',
                    'type' => 'success',
                    'data' => $msg
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

    public function destroyPdMsg(Request $request, $emp_id)
    {
        try {

            $msg = DB::connection('dbsysclient')->table('tbcf_msg_comp')
                ->where('canal_id', '=', $request->canal_id)
                ->where('msg_categ', '=', $request->msg_categ)
                ->where('emp_id', '=', $request->emp_id)->delete();

            if ($msg) {
                return response()->json([
                    'title' => 'Sucesso',
                    'text' => 'Registro deletado com sucesso!',
                    'type' => 'success',
                    'btnPesquisar' => 'btnPesquisarPdMsg'
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

    public function getObterGridPesquisaPdMsg(Request $request)
    {
        if (!Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, "Usuário não autenticado...");
        }

        $data = new Collection();

        $query = "";

        if (!empty($request->canal_id_filtro)) {

            $query .= "canal_id = " . quotedstr($request->canal_id_filtro) . " AND ";
        }

        if (!empty($request->msg_categ_filtro)) {

            $query .= "msg_categ = " . quotedstr($request->msg_categ_filtro) . " AND ";
        }

        if (!empty($request->emp_id)) {

            if (is_numeric($request->emp_id)) {

                $query .= "emp_id = " . $request->emp_id;
                $data = TbCfMsgComp::whereRaw(DB::raw($query))->get();
            } else {

                $empresasGeral = Empresa::where('emp_nmult', 'like', '%' . $request->emp_id . '%')->get(['emp_id'])->pluck('emp_id')->toArray();
                if ($empresasGeral) {
                    $query .= selectItens($empresasGeral, 'emp_id');
                    $data = TbCfMsgComp::whereRaw(DB::raw($query))->get();
                }
            }
        }

        $this->permissions = Auth::user()->getAllPermissions()->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';

                if (in_array('config-sistema-multmais.edit', $this->permissions)) {

                    $btn .= '<button type="button" ';
                    $btn .= 'data-emp-id="' . $row->emp_id . '" ';
                    $btn .= 'data-canal-id="' . $row->canal_id . '" ';
                    $btn .= 'data-msg-categ="' . $row->msg_categ . '" ';
                    $btn .= 'class="btn btn-primary btn-sm mr-1 btn-padroes-de-mensagens" title="Editar"><i class="fas fa-edit"></i></a>';
                }

                if (in_array('config-sistema-multmais.destroy', $this->permissions)) {

                    $btn .= '<button href="#" class="btn btn-sm btn-primary mr-1 delete_id " ';
                    $btn .= 'data-emp-id="' . $row->emp_id . '" ';
                    $btn .= 'data-canal-id="' . $row->canal_id . '" ';
                    $btn .= 'data-msg-categ="' . $row->msg_categ . '" ';
                    $btn .= 'data-url="destroy-padroes-de-mensagens" ';
                    $btn .= 'title="Excluir"><i class="far fa-trash-alt"></i></button>';
                }

                return $btn;
            })->editColumn('canal', function ($row) {
                return $row->canal->canal_desc;
            })->editColumn('categoria', function ($row) {
                return $row->categoria->msg_categ_desc;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    //Work Flow
    public function storeWf(Request $request)
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
                ->where('emp_id', '=', $request->emp_id)->first();

            if ($data) {
                return response()->json([
                    'title' => 'Erro',
                    'text' => 'Já existe um Work Flow cadastrada.',
                    'type' => 'error',
                ], Response::HTTP_NOT_FOUND);
            }

            DB::beginTransaction();
            $data = DB::connection('dbsysclient')->table('tbcf_config_wf')->insert([
                "tabela" => $request->tabela,
                "campo" => $request->campo,
                "user_id" => $request->user_id,
                "emp_id" => $request->emp_id,
            ]);

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

    public function updateWf(Request $request)
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

    public function editWf(Request $request, $emp_id)
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

    public function destroyWf(Request $request, $emp_id)
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

    public function getObterTabelas(Request $request)
    {
        $parametro = $request != null ? $request->all()['parametro'] : '';

        if (empty($parametro)) {
            return [];
        }

        $dataArray = [];

        $tables = DB::connection('dbsysclient')->select("SHOW TABLES where Tables_in_db_sys_client like('%" . $parametro . "%')");

        foreach ($tables as $key => $value) {
            $dataArray[] = ['id' => $value->Tables_in_db_sys_client, 'text' => strtoupper($value->Tables_in_db_sys_client)];
        }
        return $dataArray;
    }

    public function getColumnsFromTable($table)
    {

        if (empty($table)) {
            return [];
        }

        $columnsList = DB::connection('dbsysclient')->getSchemaBuilder()->getColumnListing($table);
        $columns = [];

        foreach ($columnsList as $key => $col) {
            $columns[] = ['id' => $col, 'text' => $col];
        }

        return $columns;
    }

    public function getObterGridPesquisaWf(Request $request)
    {

        if (!Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, "Usuário não autenticado...");
        }

        $data = new Collection();

        $query = "";

        if (!empty($request->tabela_filtro)) {

            $query .= "tabela = " . quotedstr($request->tabela_filtro) . " AND ";
        }

        if (!empty($request->emp_id)) {

            if (is_numeric($request->emp_id)) {

                $query .= "emp_id = " . $request->emp_id;
                $data = TbCfWorkFlow::whereRaw(DB::raw($query))->get();
            } else {

                $empresasGeral = Empresa::where('emp_nmult', 'like', '%' . $request->emp_id . '%')->get(['emp_id'])->pluck('emp_id')->toArray();
                if ($empresasGeral) {
                    $query .= selectItens($empresasGeral, 'emp_id');
                    $data = TbCfWorkFlow::whereRaw(DB::raw($query))->get();
                }
            }
        }

        $this->permissions = Auth::user()->getAllPermissions()->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '';

                if (in_array('config-sistema-multmais.edit', $this->permissions)) {

                    $btn .= '<button type="button" ';
                    $btn .= 'data-emp-id="' . $row->emp_id . '" ';
                    $btn .= 'data-tabela="' . $row->tabela . '" ';
                    $btn .= 'data-campo="' . $row->campo . '" ';
                    $btn .= 'class="btn btn-primary btn-sm mr-1 btn-work-flow" title="Editar"><i class="fas fa-edit"></i></a>';
                }

                if (in_array('config-sistema-multmais.destroy', $this->permissions)) {

                    $btn .= '<button href="#" class="btn btn-sm btn-primary mr-1 delete_id " ';
                    $btn .= 'data-emp-id="' . $row->emp_id . '" ';
                    $btn .= 'data-tabela="' . $row->tabela . '" ';
                    $btn .= 'data-campo="' . $row->campo . '" ';
                    $btn .= 'data-url="destroy-work-flow" ';
                    $btn .= 'title="Excluir"><i class="far fa-trash-alt"></i></button>';
                }

                return $btn;
            })->editColumn('user', function ($row) {
                $user = User::find($row->user_id);
                $userName = "";
                if ($user) {
                    $userName = $user->user_name;
                }
                return $userName;
            })->editColumn('empresa', function ($row) {
                $emp_rzsoc = "";
                if ($row->empresa) {
                    $emp_rzsoc = $row->empresa->emp_rzsoc;
                }
                return $emp_rzsoc;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
