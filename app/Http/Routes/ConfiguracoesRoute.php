<?php

namespace App\Http\Routes;

use App\Http\Controllers\Multban\Configuracoes\ConfiguracoesController;
use Illuminate\Support\Facades\Route;

class ConfiguracoesRoute
{
    public static function rotas()
    {
        Route::get('/api/mensagens-comp', [ConfiguracoesController::class, 'getMensagensComprovante']);
    }
}
