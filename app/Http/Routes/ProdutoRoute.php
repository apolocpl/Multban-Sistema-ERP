<?php

namespace App\Http\Routes;

use App\Http\Controllers\Multban\Produto\ProdutoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class ProdutoRoute
{
    public static function rotas()
    {
        Route::get('produtos', [ProdutoController::class, 'index'])->middleware('permission:produtos.index')->name('produtos.index');
        Route::get('produtos/{id}/alterar', [ProdutoController::class, 'edit'])->middleware('permission:produtos.edit')->name('produtos.edit');
        Route::patch('produtos/{id}/alterar', [ProdutoController::class, 'update'])->middleware('permission:produtos.update')->name('produtos.update');
        Route::get('produtos/inserir', [ProdutoController::class, 'create'])->middleware('permission:produtos.create')->name('produtos.create');
        Route::post('produtos/inserir', [ProdutoController::class, 'store'])->middleware('permission:produtos.create')->name('produtos.store');
        Route::get('produtos/{id}/copiar', [ProdutoController::class, 'copy'])->middleware('permission:produtos.copy')->name('produtos.copy');
        Route::get('produtos/{id}/visualizar', [ProdutoController::class, 'show'])->middleware('permission:produtos.show')->name('produtos.show');
        Route::delete('produtos/{id}', [ProdutoController::class, 'destroy'])->middleware('permission:produtos.destroy')->name('produtos.destroy');

        Route::post('produtos/obtergridpesquisa', [ProdutoController::class, 'getObterGridPesquisa']);

        Route::get('produtos/obtergrid', [ProdutoController::class, 'getGrid'])->name('produtos.getGrid');
        Route::get('produtos/obter-empresas', [ProdutoController::class, 'getObterEmpresas']);
        Route::get('produtos/obter-descricao-produto', [ProdutoController::class, 'getObterDescricaoProduto']);

    }
}
