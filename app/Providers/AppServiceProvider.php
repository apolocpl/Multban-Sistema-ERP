<?php

namespace App\Providers;

use App\Models\Multban\Cliente\Cliente;
use App\Models\Multban\Empresa\Empresa;
use App\Policies\ClientePolicy;
use App\Support\Tenancy\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Spatie\Menu\Laravel\Link;
use Spatie\Menu\Laravel\Menu;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantManager::class, function () {
            return new TenantManager;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(190);

        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        Gate::policy(Cliente::class, ClientePolicy::class);

        Blade::if('canView', function (string $key) {
            $permissions = request()->attributes->get('frontendAcl', View::shared('frontendAcl') ?? []);

            return (bool) data_get($permissions, $key, false);
        });

        View::composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $empresa = Empresa::where('emp_id', $user->emp_id)->first();
                if (! $empresa) {
                    $empresa = new Empresa;
                }

                $permissionsForEach = Auth::user()->getAllPermissions();
                $tes = $permissionsForEach->pluck('id')->toArray();

                // Monta o menu SEM usar Session
                $menus = Menu::new()
                    ->setAttribute('role', 'menu')
                    ->setAttribute('data-widget', 'treeview')
                    ->setAttribute('data-accordion', 'false')
                    ->addClass('nav nav-pills nav-sidebar flex-column nav-compact nav-flat ')
                    ->add(Link::to('/', '<i class="nav-icon fas fa-tachometer-alt"></i> <p>Home</p>')
                        ->addClass('nav-link')
                        ->addParentClass('nav-item'));

                foreach ($permissionsForEach as $permission) {
                    $menu = Menu::new();
                    $subs = DB::table('tbsy_permissions')->whereIn('id', $tes)->where('parent_id', '=', $permission->id)->where('name', 'LIKE', '%.index')->orderBy('id')->get();
                    foreach ($subs as $item) {
                        $menu
                            ->add(Link::to(str_replace('.index', '', '/' . $item->name), '<i class="' . $item->icon . ' nav-icon"></i> <p>' . ucfirst(str_replace('.index', '', $item->description)) . '</p>')
                                ->addClass(Str::startsWith(Route::currentRouteName(), str_replace('.index', '', $item->name)) ? 'nav-link active' : 'nav-link')
                                ->addParentClass('nav-item'));
                    }

                    $esta_ativo = false;
                    foreach ($subs as $item) {
                        if (Str::startsWith(Route::currentRouteName(), str_replace('.index', '', $item->name))) {
                            $esta_ativo = true;
                            break;
                        }
                    }

                    $menus->submenuIf(
                        $permission->parent_id == 0,
                        Link::to('#', '<i class="nav-icon fas ' . $permission->icon . '"></i> <p>' . ucfirst($permission->description) . ' <i class="right fas fa-angle-left"></i></p>')
                            ->addClass($esta_ativo ? 'nav-link active' : 'nav-link')
                            ->addParentClass('nav-item'),
                        $menu->addParentClass($esta_ativo ? 'nav-item menu-open' : 'nav-item')
                            ->addClass('nav nav-treeview')
                    );
                }

                $permissions = $user->getAllPermissions()->pluck('name')->toArray();

                $view->with(
                    [
                        'permissions'         => in_array('auditoria.index', $permissions),
                        'create_config'       => in_array('config.store', $permissions),
                        'dataInicial'         => '',
                        'dataFinal'           => '',
                        'empresa'             => $empresa,
                        'minimizarMenu'       => false,
                        'notificacaoContador' => 2,
                        'menus'               => $menus,
                        'route'               => request()->segment(1),
                        'routeAction'         => Str::contains(Route::currentRouteName(), 'edit'),
                    ]
                );
            }
        });
    }
}
