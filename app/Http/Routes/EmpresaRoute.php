<?php

namespace App\Http\Routes;

use App\Http\Controllers\Multban\Empresa\EmpresaController;
use Illuminate\Support\Facades\Route;

class EmpresaRoute
{
    public static function rotas()
    {
        // Pedido de venda
        Route::get('empresa', [EmpresaController::class, 'index'])->middleware('permission:empresa.index')->name('empresa.index');
        Route::get('empresa/{id}/alterar', [EmpresaController::class, 'edit'])->middleware('permission:empresa.edit')->name('empresa.edit');
        Route::patch('empresa/{id}/alterar', [EmpresaController::class, 'update'])->middleware('permission:empresa.update')->name('empresa.update');
        Route::get('empresa/inserir', [EmpresaController::class, 'create'])->middleware('permission:empresa.create')->name('empresa.create');
        Route::post('empresa/inserir', [EmpresaController::class, 'store'])->middleware('permission:empresa.store')->name('empresa.store');
        Route::get('empresa/{id}/visualizar', [EmpresaController::class, 'show'])->middleware('permission:empresa.show')->name('empresa.show');
        Route::delete('empresa/{id}', [EmpresaController::class, 'destroy'])->middleware('permission:empresa.destroy')->name('empresa.destroy');
        Route::post('empresa/active/{id}', [EmpresaController::class, 'active']); // ->middleware('permission:empresa.active')->name('empresa.active');
        Route::post('empresa/inactive/{id}', [EmpresaController::class, 'inactive']); // ->middleware('permission:empresa.inactive')->name('empresa.inactive');

        Route::post('empresa/obtergridpesquisa', [EmpresaController::class, 'getObterGridPesquisa']);

        Route::get('empresa/cidade-estado-pais', [EmpresaController::class, 'getCityEstPais']);
        Route::get('empresa/obter-cidade', [EmpresaController::class, 'getObterCidade']);
        Route::get('empresa/obter-estado', [EmpresaController::class, 'getObterEstado']);
        Route::get('empresa/obter-pais', [EmpresaController::class, 'getObterPais']);
        Route::get('empresa/obter-empresas-franqueadoras', [EmpresaController::class, 'getObterEmpresasFranqueadoras']);
        Route::get('empresa/obter-empresas', [EmpresaController::class, 'getObterEmpresas']);
        Route::get('empresa/obter-empresas-nmult', [EmpresaController::class, 'getObterEmpresasNmult']);
        Route::get('empresa/obter-users', [EmpresaController::class, 'getObterUsers']);
    }
}
