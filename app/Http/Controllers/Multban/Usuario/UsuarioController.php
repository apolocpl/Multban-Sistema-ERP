<?php

namespace App\Http\Controllers\Multban\Usuario;

use App\Enums\EmpresaStatusEnum;
use App\Enums\FiltrosEnum;
use App\Http\Controllers\Controller;
use App\Models\Multban\Auditoria\LogAuditoria;
use App\Models\Multban\DadosMestre\TbDmBncCode;
use App\Models\Multban\DadosMestre\TbDmLangu;
use App\Models\Multban\DadosMestre\TbDmUserFunc;
use App\Models\Multban\DadosMestre\TbDmUserStatus;
use App\Models\Multban\Empresa\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class UsuarioController extends Controller
{
    private $permissions;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filtros = [FiltrosEnum::ID => 'CÓDIGO', FiltrosEnum::NAME => 'NOME', FiltrosEnum::EMAIL => 'E-MAIL', FiltrosEnum::USERNAME => 'USERNAME'];

        return response(view('Multban.usuario.index', compact('filtros')));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $usuario = new User;
        $usuario->user_pcomis = formatarMoneyToDecimal($usuario->user_pcomis);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = [];

        $tbDmBncCode = TbDmBncCode::all();
        $tbDmUserFunc = TbDmUserFunc::all();
        $status = TbDmUserStatus::all();
        $users = User::all();

        $langu = TbDmLangu::all();
        $telas = Permission::where('name', 'LIKE', '%index')->get();

        return response(view('Multban.usuario.edit', compact('users', 'usuario', 'roles', 'userRole', 'tbDmBncCode', 'tbDmUserFunc', 'status', 'langu', 'telas')));
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

            $user = new User;

            $input = $request->all();
            $input['user_cpf'] = removerCNPJ($request->user_cpf);

            $validator = Validator::make($input, $user->rulesCreate(), $user->messages(), $user->attributes());

            if ($validator->fails()) {
                return response()->json([
                    'message'   => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user->emp_id = $request->emp_id;
            $user->user_logon = $request->user_logon;
            $user->user_sts = $request->user_sts;
            $user->user_name = $request->user_name;
            $user->user_cpf = removerCNPJ($request->user_cpf);
            $user->user_crm = $request->user_crm;
            $user->user_email = $request->user_email;
            $user->user_func = $request->user_func;
            $user->user_cel = removerMascaraTelefone($request->user_cel);
            $user->user_tfixo = removerMascaraTelefone($request->user_tfixo);
            $user->user_screen = $request->user_screen;
            $user->langu = $request->langu;
            $user->user_resp = $request->user_resp;
            $user->user_pcomis = formatarTextoParaDecimal($request->user_pcomis);
            $user->user_comis = $request->user_comis == 'sim' ? 'x' : '';
            $user->user_seller = $request->user_seller;
            $user->user_cdgbc = $request->user_cdgbc;
            $user->user_ccbc = $request->user_ccbc;
            $user->user_pix = $request->user_pix;
            $user->user_agbc = $request->user_agbc;

            if (! empty($input['user_pass'])) {
                $user->user_pass = Hash::make($input['user_pass']);
            } else {
                $input = Arr::except($input, ['user_pass']);
            }

            $user->save();
            $user->syncRoles($request->input('user_role'));

            $logAuditoria = new LogAuditoria;
            $logAuditoria->auddat = date('Y-m-d H:i:s');
            $logAuditoria->audusu = \Illuminate\Support\Facades\Auth::user()->user_name;
            $logAuditoria->audtar = 'Adicionou a empresa ';
            $logAuditoria->audarq = $user->getTable();
            $logAuditoria->audlan = $user->user_id;
            $logAuditoria->audant = '';
            $logAuditoria->auddep = '';
            $logAuditoria->audnip = request()->ip();

            $logAuditoria->save();

            // Session::flash("idModeloInserido", $id);
            // Session::flash('success', "Usuário atualizado com sucesso.");
            DB::commit();

            return response()->json([
                'message'   => 'Usuário adicionado com sucesso...',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message'   => $e->getMessage(),
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

        $usuario = User::find($id);
        $usuario->user_pcomis = formatarMoneyToDecimal($usuario->user_pcomis);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $usuario->roles->pluck('name', 'name')->all();

        $tbDmBncCode = TbDmBncCode::all();
        $tbDmUserFunc = TbDmUserFunc::all();
        $status = TbDmUserStatus::all();
        $users = User::all();

        $langu = TbDmLangu::all();
        $telas = Permission::where('name', 'LIKE', '%index')->get();

        return response(view('Multban.usuario.edit', compact('users', 'usuario', 'roles', 'userRole', 'tbDmBncCode', 'tbDmUserFunc', 'status', 'langu', 'telas')));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $usuario = User::find($id);
        $usuario->user_pcomis = formatarMoneyToDecimal($usuario->user_pcomis);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $usuario->roles->pluck('name', 'name')->all();

        $tbDmBncCode = TbDmBncCode::all();
        $tbDmUserFunc = TbDmUserFunc::all();
        $status = TbDmUserStatus::all();
        $users = User::all();

        $langu = TbDmLangu::all();
        $telas = Permission::where('name', 'LIKE', '%index')->get();

        return response(view('Multban.usuario.edit', compact('users', 'usuario', 'roles', 'userRole', 'tbDmBncCode', 'tbDmUserFunc', 'status', 'langu', 'telas')));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function copy($id)
    {
        $usuario = User::find($id);
        $usuario->user_pcomis = str_replace('.', ',', $usuario->user_pcomis);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $usuario->roles->pluck('name', 'name')->all();

        $tbDmBncCode = TbDmBncCode::all();
        $tbDmUserFunc = TbDmUserFunc::all();
        $status = TbDmUserStatus::all();
        $users = User::all();

        $langu = TbDmLangu::all();
        $telas = Permission::where('name', 'LIKE', '%index')->get();

        return response(view('Multban.usuario.edit', compact('users', 'usuario', 'roles', 'userRole', 'tbDmBncCode', 'tbDmUserFunc', 'status', 'langu', 'telas')));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // adicione o DB transiction no codigo abaixo
        DB::beginTransaction();
        try {

            $user = User::find($id);

            $input = $request->all();

            $validator = Validator::make($input, $user->rules($id), $user->messages(), $user->attributes());

            if ($validator->fails()) {
                return response()->json([
                    'message'   => $validator->errors(),

                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Verifica se ouve mudanças nos campos, se sim grava na auditoria
            foreach ($input as $key => $value) {
                if (Arr::exists($user->toArray(), $key)) {
                    if ($value != $user->$key) {
                        $logAuditoria = new LogAuditoria;
                        $logAuditoria->auddat = date('Y-m-d H:i:s');
                        $logAuditoria->audusu = Auth::user()->username;
                        $nomeValue = $value;
                        if ($request->file('image') && $key == 'image') {

                            $image = $request->file('image');
                            $string = tirarAcentos($user->name);

                            $string = strtolower(str_replace(' ', '-', $string));

                            $string = str_replace(["'", '&#039;', '.'], '', $string);
                            $nomeValue = date('Ymdhms') . '-' . $string . '.' . $image->getClientOriginalExtension();
                        }
                        $logAuditoria->audtar = 'Alterou o campo ' . $key;
                        $logAuditoria->audarq = $user->getTable();
                        $logAuditoria->audlan = $user->id;
                        $logAuditoria->audant = $user->$key;
                        $logAuditoria->auddep = $nomeValue;
                        $logAuditoria->audnip = request()->ip();
                        $logAuditoria->save();
                    }
                }
            }

            // Verifica se ouve mudanças nos campos, se sim grava na auditoria

            if ($user->roles->pluck('name', 'name')->first() != $request->input('user_role')[0]) {
                $logAuditoria = new LogAuditoria;
                $logAuditoria->auddat = date('Y-m-d H:i:s');
                $logAuditoria->audusu = Auth::user()->user_logon;
                $logAuditoria->audtar = 'Alterou o campo ' . $key;
                $logAuditoria->audarq = $user->getTable();
                $logAuditoria->audlan = $user->roles->pluck('id', 'id')->first();
                $logAuditoria->audant = $user->roles->pluck('name', 'name')->first();
                $logAuditoria->auddep = $request->input('user_role')[0];
                $logAuditoria->audnip = request()->ip();
                $logAuditoria->save();
            }

            $user->emp_id = $request->emp_id;
            $user->user_logon = $request->user_logon;
            $user->user_sts = $request->user_sts;
            $user->user_name = $request->user_name;
            $user->user_cpf = removerCNPJ($request->user_cpf);
            $user->user_crm = $request->user_crm;
            $user->user_email = $request->user_email;
            $user->user_func = $request->user_func;
            $user->user_cel = removerMascaraTelefone($request->user_cel);
            $user->user_tfixo = removerMascaraTelefone($request->user_tfixo);
            $user->user_screen = $request->user_screen;
            $user->langu = $request->langu;
            $user->user_resp = $request->user_resp;
            $user->user_pcomis = formatarTextoParaDecimal($request->user_pcomis);
            $user->user_comis = $request->user_comis == 'sim' ? 'x' : '';
            $user->user_seller = $request->user_seller;
            $user->user_cdgbc = $request->user_cdgbc;
            $user->user_ccbc = $request->user_ccbc;
            $user->user_pix = $request->user_pix;
            $user->user_agbc = $request->user_agbc;

            if (! empty($input['user_pass'])) {
                $user->user_pass = Hash::make($input['user_pass']);
            } else {
                $input = Arr::except($input, ['user_pass']);
            }

            $user->save();
            $user->syncRoles($request->input('user_role'));

            // Session::flash("idModeloInserido", $id);
            // Session::flash('success', "Usuário atualizado com sucesso.");
            DB::commit();

            return response()->json([
                'message'   => 'Usuário atualizado com sucesso...',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $user = User::find($id);

            if ($user->user_id == 1) {
                return response()->json([
                    'message' => 'Não é possível Excluir o usuário administrador.',
                    'data'    => [],
                ], Response::HTTP_BAD_REQUEST);
            }

            $user->user_sts = EmpresaStatusEnum::EXCLUIDO;
            $user->save();

            // auditoria
            $logAuditoria = new LogAuditoria;
            $logAuditoria->auddat = date('Y-m-d H:i:s');
            $logAuditoria->audusu = Auth::user()->user_name;
            $logAuditoria->audtar = 'Deletou o usuário ' . $user->user_name;
            $logAuditoria->audarq = $user->getTable();
            $logAuditoria->audlan = $user->user_id;
            $logAuditoria->audant = '';
            $logAuditoria->auddep = '';
            $logAuditoria->audnip = request()->ip();
            $logAuditoria->save();

            // Deletar logs de auditoria do usuário
            return response()->json([
                'title' => 'Sucesso',
                'text'  => 'Usuário Excluido com sucesso!',
                'type'  => 'success',
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

            $user = User::find($id);
            if ($user) {
                $user->user_sts = EmpresaStatusEnum::INATIVO;
                $user->save();

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

            $user = User::find($id);
            if ($user) {
                $user->user_sts = EmpresaStatusEnum::ATIVO;
                $user->save();

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

    public function sendResetLinkEmail(Request $request)
    {
        try {

            $request->validate(['user_email' => 'required|email']);

            $status = Password::sendResetLink(
                $request->only('user_email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message'   => __($status),
                ], Response::HTTP_OK);
            }

            return response()->json([
                'message'   => __($status),
            ]);
        } catch (\Throwable $th) {

            return response()->json([
                'message'   => $th->getMessage(),
            ], 500);
        }
    }

    public function getUsersFromRspresa($emp_id)
    {
        if (! Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, 'Usuário não autenticado...');
        }

        $query = '';

        if (is_numeric($emp_id)) {

            $query .= 'emp_id = ' . $emp_id;
        } else {

            $empresasGeral = Empresa::where('emp_nmult', 'like', '%' . $emp_id . '%')->get(['emp_id'])->pluck('emp_id')->toArray();

            if ($empresasGeral) {
                $query .= selectItens($empresasGeral, 'emp_id');
            }
        }

        $query . " and user_sts = 'AT'";

        $users = User::whereRaw(DB::raw($query))
            ->get(['user_id as id', 'user_name as text'])
            ->toArray();

        return response()->json($users);
    }

    public function postObterGridPesquisa(Request $request)
    {
        if (! Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, 'Usuário não autenticado...');
        }

        $data = new Collection;

        $query = '';

        if (! empty($request->usuario)) {
            if (is_numeric($request->usuario)) {

                $query .= 'user_id = ' . $request->usuario . ' AND ';
            } else {

                $query .= "user_name LIKE '%" . $request->usuario . "%' AND ";
            }
        }

        if (! empty($request->cpf)) {
            $query .= 'user_cpf = ' . quotedstr(removerCNPJ($request->cpf)) . ' AND ';
        }

        if (! empty($request->empresa_id)) {
            if (is_numeric($request->empresa_id)) {

                $query .= 'emp_id = ' . $request->empresa_id;
            } else {

                $empresasGeral = Empresa::where('emp_nmult', 'like', '%' . $request->empresa_id . '%')->get(['emp_id'])->pluck('emp_id')->toArray();

                if ($empresasGeral) {
                    $query .= selectItens($empresasGeral, 'emp_id');
                }
            }
        }

        $query = rtrim($query, 'AND ');

        if (! empty($query)) {
            $data = User::whereRaw(DB::raw($query))->get();
        }

        $this->permissions = Auth::user()->getAllPermissions()->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group">';
                if (in_array('usuario.show', $this->permissions)) {
                    $btn .= '<a href="usuario/' . $row->user_id . '/visualizar" class="btn btn-sm btn-primary mr-1" data-placement="top" data-trigger="hover" data-toggle="tooltip" title="Visualizar"><i class="fas fa-eye"></i></a>';
                }

                if (in_array('usuario.edit', $this->permissions)) {
                    $btn .= '<a href="usuario/' . $row->user_id . '/alterar" class="btn btn-sm btn-primary mr-1" data-placement="top" data-trigger="hover" data-toggle="tooltip" title="Editar"><i class="fas fa-edit"></i></a>';
                }

                if (in_array('usuario.copy', $this->permissions)) {
                    $btn .= '<a href="usuario/' . $row->user_id . '/copiar" class="btn btn-sm btn-primary mr-1" data-placement="top" data-trigger="hover" data-toggle="tooltip" title="Copiar"><i class="fas fa-copy"></i></a>';
                }

                if (in_array('usuario.destroy', $this->permissions)) {
                    $btn .= '<a href="#" class="btn btn-sm btn-primary mr-1" id="delete_grid_id" data-id="' . $row->user_id . '" data-placement="top" data-trigger="hover" data-toggle="tooltip" title="Excluir"><i class="far fa-trash-alt"></i></a>';
                }

                $btn .= '</div>';

                return $btn;
            })
            ->editColumn('role', function ($row) {
                $badge = '';
                foreach ($row->getRoleNames() as $key => $value) {
                    $badge .= '<span class="badge badge-success">' . $value . '</span>';
                }

                return $badge;
            })
            ->editColumn('status', function ($row) {
                $badge = '';
                if (! empty($row->status)) {

                    switch ($row->status->user_sts) {

                        case 'AT':
                            $badge = '<span class="badge badge-success">' . $row->status->user_sts_desc . '</span>';
                            break;
                        case 'IN':
                        case 'EX':
                        case 'BL':
                            $badge = '<span class="badge badge-danger">' . $row->status->user_sts_desc . '</span>';
                            break;
                    }
                }

                return $badge;
            })
            ->editColumn('empresa', function ($row) {
                $emp_nfant = '';
                $empresa = Empresa::find($row->emp_id);
                if ($empresa) {
                    $emp_nfant = $empresa->emp_nfant;
                }

                return $emp_nfant;
            })
            ->editColumn('user_cpf', function ($row) {
                return formatarCPF($row->user_cpf);
            })
            ->rawColumns(['action', 'role', 'status'])
            ->make(true);
    }
}
