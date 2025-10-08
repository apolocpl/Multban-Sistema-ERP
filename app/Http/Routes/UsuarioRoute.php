<?php

namespace App\Http\Routes;

use App\Http\Controllers\Multban\Usuario\UsuarioController;
use Illuminate\Support\Facades\Route;

class UsuarioRoute
{
    public static function rotas()
    {

        Route::get('usuario', [UsuarioController::class, 'index'])->name('usuario.index');
        Route::get('usuario/{id}/alterar', [UsuarioController::class, 'edit'])->name('usuario.edit');
        Route::patch('usuario/{id}/alterar', [UsuarioController::class, 'update'])->name('usuario.update');
        Route::get('usuario/inserir', [UsuarioController::class, 'create'])->name('usuario.create');
        Route::post('usuario/inserir', [UsuarioController::class, 'store'])->name('usuario.store');
        Route::get('usuario/{id}/copiar', [UsuarioController::class, 'copy'])->name('usuario.copy');
        Route::get('usuario/{id}/visualizar', [UsuarioController::class, 'show'])->name('usuario.show');
        Route::get('usuario/get-users-from-espresa/{emp_id}', [UsuarioController::class, 'getUsersFromRspresa']);
        Route::delete('usuario/{id}', [UsuarioController::class, 'destroy'])->name('usuario.destroy');
        Route::post('usuario/send-reset-link-email', [UsuarioController::class, 'sendResetLinkEmail']);
        Route::post('usuario/active/{id}', [UsuarioController::class, 'active']); // ->middleware('permission:empresa.active')->name('empresa.active');
        Route::post('usuario/inactive/{id}', [UsuarioController::class, 'inactive']); // ->middleware('permission:empresa.inactive')->name('empresa.inactive');

        Route::post('usuario/obtergridpesquisa', [UsuarioController::class, 'postObterGridPesquisa']);
    }
}
