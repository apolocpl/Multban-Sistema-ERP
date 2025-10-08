<?php

namespace App\Http\Controllers\Multban\PerfilDeAcesso;

use App\Enums\FiltrosEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class PerfilDeAcessoController extends Controller
{
    private $permissions;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filtros = [FiltrosEnum::ID => 'CÓDIGO', FiltrosEnum::NAME => 'NOME'];

        return response(view('Multban.perfil-de-acesso.index', compact('filtros')));

        $roles = Role::orderBy('id', 'DESC')->paginate(5);

        return view('Multban.perfil-de-acesso.index', compact('roles'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $role = new Role;

        $rolePermissions = [];
        $permissionsa = Permission::where('parent_id', 0)->get();

        // dd($role, $rolePermissions, $permissions);
        return response(view('Multban.perfil-de-acesso.edit', compact('role', 'rolePermissions', 'permissionsa')));

        $permissions = Permission::where('parent_id', 0)->get();

        return view('Multban.perfil-de-acesso.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $role = new Role;
            $input = $request->all();
            $validator = Validator::make($input, $role->rules(), $role->messages(), $role->attributes());
            dd($input);
            if ($validator->fails()) {
                return response()->json([
                    'message'        => $validator->errors(),
                    'uploaded_image' => '',
                    'class_name'     => 'alert-danger',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $permissionArray = $request->get('permission');
            foreach ($permissionArray as $key => $permission) {

                if (Str::contains($permission, ['.create'])) {
                    $permissionArray = array_merge($permissionArray, [Str::replace('.create', '.store', $permission) => Str::replace('.create', '.store', $permission)]);
                }

                if (Str::contains($permission, ['.edit'])) {
                    $permissionArray = array_merge($permissionArray, [Str::replace('.edit', '.update', $permission) => Str::replace('.edit', '.update', $permission)]);
                }

                $per = Str::replace(['.show', '.edit', '.update', '.create', '.copy', '.destroy'], [], $permission);

                if (! in_array($per . '.index', $permissionArray)) {
                    if (! Str::contains($permission, ['.index']) && Str::contains($permission, ['.show', '.edit', '.update', '.create', '.copy', '.destroy'])) {
                        $per = Str::replace(['.show', '.edit', '.update', '.create', '.copy', '.destroy'], [], $permission);
                        $permissionArray = array_merge($permissionArray, [$per . '.index' => $per . '.index']);
                    }
                }
            }

            $role = Role::create(['name' => $request->get('name')]);
            $role->syncPermissions($permissionArray);
            Artisan::call('optimize:clear');

            return response()->json([
                'message'        => 'Processando...',
                'uploaded_image' => '',
                'class_name'     => 'alert-danger',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message'        => $e->getMessage() . ', ' . $e->getLine(),
                'uploaded_image' => '',
                'class_name'     => 'alert-danger',
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

        $role = Role::where('id', $id)->first();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $permissionsa = Permission::where('parent_id', 0)->get();

        // dd($role, $rolePermissions, $permissions);
        return view('Multban.perfil-de-acesso.edit', compact('role', 'rolePermissions', 'permissionsa'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $role = Role::where('id', $id)->first();
        // $role = Role::findOrFail($id);

        // if ($role->id == 1)
        //     return redirect()->route('perfil-de-acesso.index')
        //         ->with('error', 'Não é possível alterar a permissão administrador.');

        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $permissionsa = Permission::where('parent_id', 0)->get();

        // dd($role, $rolePermissions, $permissions);
        return view('Multban.perfil-de-acesso.edit', compact('role', 'rolePermissions', 'permissionsa'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function copy($id)
    {

        $role = Role::where('id', $id)->first();
        // $role = Role::findOrFail($id);

        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $permissionsa = Permission::where('parent_id', 0)->get();

        // dd($role, $rolePermissions, $permissions);
        return view('Multban.perfil-de-acesso.edit', compact('role', 'rolePermissions', 'permissionsa'));
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
            $permissionArray = $request->get('permission');
            foreach ($permissionArray as $key => $permission) {

                if (Str::contains($permission, ['.create'])) {
                    $permissionArray = array_merge($permissionArray, [Str::replace('.create', '.store', $permission) => Str::replace('.create', '.store', $permission)]);
                }

                if (Str::contains($permission, ['.edit'])) {
                    $permissionArray = array_merge($permissionArray, [Str::replace('.edit', '.update', $permission) => Str::replace('.edit', '.update', $permission)]);
                }

                $per = Str::replace(['.show', '.edit', '.update', '.create', '.copy', '.destroy'], [], $permission);

                if (! in_array($per . '.index', $permissionArray)) {
                    if (! Str::contains($permission, ['.index']) && Str::contains($permission, ['.show', '.edit', '.update', '.create', '.copy', '.destroy'])) {
                        $per = Str::replace(['.show', '.edit', '.update', '.create', '.copy', '.destroy'], [], $permission);
                        $permissionArray = array_merge($permissionArray, [$per . '.index' => $per . '.index']);
                    }
                }
            }

            $this->validate(
                $request,
                [
                    'name'       => 'required',
                    'permission' => 'required',
                ],
                [
                    'name.required'       => 'O campo Nome é obrigatótio',
                    'permission.required' => 'Selecione ao menos uma permissão',
                ]
            );

            $role = Role::where('id', $id)->first();
            // $role = Role::find($id);

            $role->update($request->only('name'));

            $role->syncPermissions($permissionArray);

            Session::flash('idModeloInserido', $role->id);
            Session::flash('success', 'Usuário atualizado com sucesso.');
            Artisan::call('optimize:clear');

            return response()->json([
                'message'        => 'Processando...',
                'uploaded_image' => '',
                'class_name'     => 'alert-danger',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message'        => $e->getMessage(),
                'uploaded_image' => '',
                'class_name'     => 'alert-danger',
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

            $role = Role::find($id);
            $modelhas = DB::table('tbsy_model_has_roles')->where('role_id', $id)->get();
            if ($modelhas) {
                return response()->json([
                    'message' => 'Não é possível deletar esta permissão, existem usuários que a utilizam.',
                    'data'    => [],
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($role->id == 1) {
                return response()->json([
                    'message' => 'Não é possível deletar a permissão de administrador.',
                    'data'    => [],
                ], Response::HTTP_BAD_REQUEST);
            }

            $role->delete();

            return response()->json([
                'title' => 'Sucesso',
                'text'  => 'Registro deletado com sucesso!',
                'type'  => 'success',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message'        => $th->getMessage(),
                'uploaded_image' => '',
                'class_name'     => 'alert-danger',
            ], 500);
        }
    }

    public function getObterGridPesquisa(Request $request)
    {
        if (! Auth::check()) {
            abort(Response::HTTP_UNAUTHORIZED, 'Usuário não autenticado...');
        }

        $parametro = $request->parametro;

        $data = '';
        switch ($request->idFiltro) {
            case FiltrosEnum::ID:
                if (! empty($parametro)) {
                    $data = Role::where('id', $parametro)->limit(100)->get();
                } else {
                    $data = Role::get();
                }
                break;
            case FiltrosEnum::NAME:
                if (! empty($parametro)) {
                    $data = Role::where('name', 'LIKE', '%' . $parametro . '%')
                        ->get();
                } else {
                    $data = Role::get();
                }
                break;

            default:
                break;
        }

        $this->permissions = Auth::user()->getAllPermissions()->pluck('name')->toArray();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group">';
                if (in_array('perfil-de-acesso.show', $this->permissions)) {
                    $btn .= '<a href="perfil-de-acesso/' . $row->id . '/visualizar" class="btn btn-default btn-sm" data-placement="top" data-trigger="hover" data-toggle="tooltip" title="Visualizar"><i class="fas fa-eye text-info"></i></a>';
                }

                if (in_array('perfil-de-acesso.edit', $this->permissions)) {
                    $btn .= '<a href="perfil-de-acesso/' . $row->id . '/alterar" class="btn btn-sm btn-default" data-placement="top" data-trigger="hover" data-toggle="tooltip" title="Editar"><i class="fas fa-edit text-success"></i></a>';
                }

                if (in_array('perfil-de-acesso.copy', $this->permissions)) {
                    $btn .= '<a href="perfil-de-acesso/' . $row->id . '/copiar" class="btn btn-default btn-sm" data-placement="top" data-trigger="hover" data-toggle="tooltip" title="Copiar"><i class="fas fa-copy text-warning"></i></a>';
                }

                if (in_array('perfil-de-acesso.destroy', $this->permissions)) {
                    $btn .= '<a href="#" class="btn btn-sm btn-default" id="delete_grid_id" data-id="' . $row->id . '" data-placement="top" data-trigger="hover" data-toggle="tooltip" title="Excluir"><i class="far fa-trash-alt text-danger"></i></a>';
                }

                $btn .= '</div>';

                return $btn;
            })
            ->editColumn('userRoles', function ($row) {
                $badge = '';

                $roles = DB::table('tbsy_model_has_roles')->where('role_id', $row->id)->get();

                foreach ($roles as $key => $value) {
                    $user = User::find($value->model_id);
                    if ($user) {
                        if ($user->name != 'Administrator') {
                            $badge .= '<span class="badge badge-success mr-2">' . $user->name . '</span>';
                        }
                    }
                }

                return $badge;
            })->editColumn('id', function ($row) {
                return $row->id;
            })
            ->rawColumns(['action', 'userRoles'])
            ->make(true);
    }
}
