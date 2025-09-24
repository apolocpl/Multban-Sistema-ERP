<?php

namespace App\Http\Routes;

use App\Http\Controllers\Multban\multban\SistemaMultbanController;
use Illuminate\Support\Facades\Route;

class SistemaMultbanRoute
{
    public static function rotas()
    {
        Route::get('config-sistema-multban', [SistemaMultbanController::class, 'index'])->name('config-sistema-multban.index');
        Route::get('config-sistema-multban/{id}/alterar', [SistemaMultbanController::class, 'edit'])->name('config-sistema-multban.edit');
        Route::patch('config-sistema-multban/{id}/alterar', [SistemaMultbanController::class, 'update'])->name('config-sistema-multban.update');
        Route::get('config-sistema-multban/inserir', [SistemaMultbanController::class, 'create'])->name('config-sistema-multban.create');
        Route::post('config-sistema-multban/inserir', [SistemaMultbanController::class, 'store'])->name('config-sistema-multban.store');
        Route::get('config-sistema-multban/{id}/copiar', [SistemaMultbanController::class, 'copy'])->name('config-sistema-multban.copy');
        Route::get('config-sistema-multban/{id}/visualizar', [SistemaMultbanController::class, 'show'])->name('config-sistema-multban.show');
        Route::delete('config-sistema-multban/{id}', [SistemaMultbanController::class, 'destroy'])->name('config-sistema-multban.destroy');

        Route::get('config-sistema-multban/obter-empresas', [SistemaMultbanController::class, 'getObterEmpresas']);
        Route::get('config-sistema-multban/obter-tabelas', [SistemaMultbanController::class, 'getObterTabelas']);
        Route::post('config-sistema-multban/obtergridpesquisa', [SistemaMultbanController::class, 'getObterGridPesquisa']);
        Route::get('config-sistema-multban/edit-conexoes-bc-emp/{emp_id}', [SistemaMultbanController::class, 'editConexoesBcEmp']);
        Route::post('config-sistema-multban/update-conexoes-bc-emp', [SistemaMultbanController::class, 'updateConexoesBcEmp']);
        Route::post('config-sistema-multban/store-conexoes-bc-emp', [SistemaMultbanController::class, 'storeConexoesBcEmp']);

        //Alias
        Route::post('config-sistema-multban/obtergridpesquisa-alias', [SistemaMultbanController::class, 'getObterGridPesquisaAlias']);
        Route::get('config-sistema-multban/edit-alias/{emp_id}', [SistemaMultbanController::class, 'editAlias']);
        Route::post('config-sistema-multban/store-alias', [SistemaMultbanController::class, 'storeAlias']);
        Route::post('config-sistema-multban/update-alias', [SistemaMultbanController::class, 'updateAlias']);
        Route::delete('config-sistema-multban/destroy-alias/{emp_id}', [SistemaMultbanController::class, 'destroyAlias']);

        //API
        Route::post('config-sistema-multban/obtergridpesquisa-apis', [SistemaMultbanController::class, 'getObterGridPesquisaApis']);
        Route::get('config-sistema-multban/edit-apis/{emp_id}', [SistemaMultbanController::class, 'editApis']);
        Route::post('config-sistema-multban/store-apis', [SistemaMultbanController::class, 'storeApis']);
        Route::post('config-sistema-multban/update-apis', [SistemaMultbanController::class, 'updateApis']);
        Route::delete('config-sistema-multban/destroy-apis/{emp_id}', [SistemaMultbanController::class, 'destroyApis']);

        //Padrões dos Planos
        Route::post('config-sistema-multban/obtergridpesquisa-padroes-de-planos', [SistemaMultbanController::class, 'getObterGridPesquisaPdPlan']);
        Route::get('config-sistema-multban/edit-padroes-de-planos/{emp_id}', [SistemaMultbanController::class, 'editPdPlan']);
        Route::post('config-sistema-multban/store-padroes-de-planos', [SistemaMultbanController::class, 'storePdPlan']);
        Route::post('config-sistema-multban/update-padroes-de-planos', [SistemaMultbanController::class, 'updatePdPlan']);
        Route::delete('config-sistema-multban/destroy-padroes-de-planos/{emp_id}', [SistemaMultbanController::class, 'destroyPdPlan']);

        //White Label
        Route::post('config-sistema-multban/obtergridpesquisa-white-label', [SistemaMultbanController::class, 'getObterGridPesquisaWhiteLabel']);
        Route::get('config-sistema-multban/edit-white-label/{emp_id}', [SistemaMultbanController::class, 'editWhiteLabel']);
        Route::post('config-sistema-multban/store-white-label', [SistemaMultbanController::class, 'storeWhiteLabel']);
        Route::post('config-sistema-multban/update-white-label', [SistemaMultbanController::class, 'updateWhiteLabel']);
        Route::delete('config-sistema-multban/destroy-white-label/{emp_id}', [SistemaMultbanController::class, 'destroyWhiteLabel']);

        //Padrões de Mensagens
        Route::post('config-sistema-multban/obtergridpesquisa-padroes-de-mensagens', [SistemaMultbanController::class, 'getObterGridPesquisaPdMsg']);
        Route::get('config-sistema-multban/edit-padroes-de-mensagens/{emp_id}', [SistemaMultbanController::class, 'editPdMsg']);
        Route::post('config-sistema-multban/store-padroes-de-mensagens', [SistemaMultbanController::class, 'storePdMsg']);
        Route::post('config-sistema-multban/update-padroes-de-mensagens', [SistemaMultbanController::class, 'updatePdMsg']);
        Route::delete('config-sistema-multban/destroy-padroes-de-mensagens/{emp_id}', [SistemaMultbanController::class, 'destroyPdMsg']);

        //Work Flow
        Route::post('config-sistema-multban/obtergridpesquisa-work-flow', [SistemaMultbanController::class, 'getObterGridPesquisaWf']);
        Route::get('config-sistema-multban/get-columns-from-table/{table}', [SistemaMultbanController::class, 'getColumnsFromTable']);
        Route::get('config-sistema-multban/edit-work-flow/{emp_id}', [SistemaMultbanController::class, 'editWf']);
        Route::post('config-sistema-multban/store-work-flow', [SistemaMultbanController::class, 'storeWf']);
        Route::post('config-sistema-multban/update-work-flow', [SistemaMultbanController::class, 'updateWf']);
        Route::delete('config-sistema-multban/destroy-work-flow/{emp_id}', [SistemaMultbanController::class, 'destroyWf']);

        //Dados Mestre
        Route::post('config-sistema-multban/obtergridpesquisa-dados-mestre', [SistemaMultbanController::class, 'getObterGridPesquisaDm']);
        Route::get('config-sistema-multban/edit-dados-mestre', [SistemaMultbanController::class, 'editDm']);
        Route::get('config-sistema-multban/create-dados-mestre', [SistemaMultbanController::class, 'createDm']);
        Route::post('config-sistema-multban/store-dados-mestre', [SistemaMultbanController::class, 'storeDm']);
        Route::post('config-sistema-multban/update-dados-mestre', [SistemaMultbanController::class, 'updateDm']);
        Route::delete('config-sistema-multban/destroy-dados-mestre/{tabela}', [SistemaMultbanController::class, 'destroyDm']);
    }
}
