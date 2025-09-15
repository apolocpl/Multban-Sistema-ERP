<?php

namespace App\Http\Routes;

use App\Http\Controllers\Multban\Venda\PdvWebController;
use App\Http\Controllers\Multban\Produto\ProdutoController;
use Illuminate\Support\Facades\Route;

class VendasRoute
{
    public static function rotas()
    {
    //Pedido de venda
    Route::get('pdv-web', [PdvWebController::class, 'index'])->middleware('permission:pdv-web.index')->name('pdv-web.index');

    // Rota para API de produtos
    Route::get('api/produtos', [ProdutoController::class, 'apiProdutos']);

    // Rota para cobrança DN
    Route::post('pdv-web/realizar-venda', [PdvWebController::class, 'realizarVenda'])->name('pdv-web.realizar-venda');

    // Rota para cancelar venda
    Route::post('/pdv-web/cancelar-venda', [PdvWebController::class, 'cancelarVenda']);

    }
}
