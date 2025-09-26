<?php

namespace App\Http\Routes;

use App\Http\Controllers\Multban\ProgramaPTS\ProgramaPtsController;
use Illuminate\Support\Facades\Route;

class ProgramaPtsRoute
{
    public static function rotas()
    {
        Route::get('programa-de-pontos', [ProgramaPtsController::class, 'index'])->name('programa-de-pontos.index');
        Route::get('programa-de-pontos/{id}/alterar', [ProgramaPtsController::class, 'edit'])->name('programa-de-pontos.edit');
        Route::patch('programa-de-pontos/{id}/alterar', [ProgramaPtsController::class, 'update'])->name('programa-de-pontos.update');
        Route::get('programa-de-pontos/inserir', [ProgramaPtsController::class, 'create'])->name('programa-de-pontos.create');
        Route::post('programa-de-pontos/inserir', [ProgramaPtsController::class, 'store'])->name('programa-de-pontos.store');
        Route::get('programa-de-pontos/{id}/copiar', [ProgramaPtsController::class, 'copy'])->name('programa-de-pontos.copy');
        Route::get('programa-de-pontos/{id}/visualizar', [ProgramaPtsController::class, 'show'])->name('programa-de-pontos.show');
        Route::delete('programa-de-pontos/{id}', [ProgramaPtsController::class, 'destroy'])->name('programa-de-pontos.destroy');
        Route::post('programa-de-pontos/active/{id}', [ProgramaPtsController::class, 'active']);//->middleware('permission:empresa.active')->name('empresa.active');
        Route::post('programa-de-pontos/inactive/{id}', [ProgramaPtsController::class, 'inactive']);//->middleware('permission:empresa.inactive')->name('empresa.inactive');

        Route::post('programa-de-pontos/obtergridpesquisa', [ProgramaPtsController::class, 'getObterGridPesquisa']);
        Route::post('programa-de-pontos/{id}/alterar-status', [ProgramaPtsController::class, 'alterarStatus']);

    }
}
