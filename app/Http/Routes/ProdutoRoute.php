<?php

namespace App\Http\Routes;

use App\Http\Controllers\Multban\Produto\ProdutoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class ProdutoRoute
{
    public static function rotas()
    {
        Route::get('produto', [ProdutoController::class, 'index'])->middleware('permission:produto.index')->name('produto.index');
        Route::get('produto/{id}/alterar', [ProdutoController::class, 'edit'])->middleware('permission:produto.edit')->name('produto.edit');
        Route::patch('produto/{id}/alterar', [ProdutoController::class, 'update'])->middleware('permission:produto.update')->name('produto.update');
        Route::get('produto/inserir', [ProdutoController::class, 'create'])->middleware('permission:produto.create')->name('produto.create');
        Route::post('produto/inserir', [ProdutoController::class, 'store'])->middleware('permission:produto.create')->name('produto.store');
        Route::get('produto/{id}/copiar', [ProdutoController::class, 'copy'])->middleware('permission:produto.copy')->name('produto.copy');
        Route::get('produto/{id}/visualizar', [ProdutoController::class, 'show'])->middleware('permission:produto.show')->name('produto.show');
        Route::delete('produto/{id}', [ProdutoController::class, 'destroy'])->middleware('permission:produto.destroy')->name('produto.destroy');

        Route::post('produto/obtergridpesquisa', [ProdutoController::class, 'getObterGridPesquisa']);

        Route::get('produto/obtergrid', [ProdutoController::class, 'getGrid'])->name('produto.getGrid');
        Route::get('produto/obter-empresas', [ProdutoController::class, 'getObterEmpresas']);
        Route::get('produto/obter-descricao-produto', [ProdutoController::class, 'getObterDescricaoProduto']);

    }
}
