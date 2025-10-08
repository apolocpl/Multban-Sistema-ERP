<?php

namespace App\Http\Routes;

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

class HomeRoute
{
    public static function rotas()
    {
        Route::get('home/cupomfidelidade', 'HomeController@cupomFidelidade')->name('home.cupomfidelidade');
        Route::get('home', 'HomeController@index')->name('home');
        Route::resource('home', 'HomeController');

        Route::get('home/maisvendidos', [HomeController::class, 'maisVendidos'])->name('home.maisvendidos');

        Route::get('/notificacao/obtergridpesquisa', 'Sistema\Notificacao\NotificacaoController@getObterGridPesquisa');
        Route::get('/notificacao/alterar/{id}', 'Sistema\Notificacao\NotificacaoController@edit')->name('notificacao.edit');
        Route::post('/notificacao/alterar/{id}', 'Sistema\Notificacao\NotificacaoController@update')->name('notificacao.update');
        Route::get('/notificacao/inserir', 'Sistema\Notificacao\NotificacaoController@create')->name('notificacao.create');
        Route::post('/notificacao/inserir', 'Sistema\Notificacao\NotificacaoController@store')->name('notificacao.store');
        Route::get('/notificacao/visualizar/{id}', 'Sistema\Notificacao\NotificacaoController@show')->name('notificacao.visualizar');
        Route::get('/notificacao/delete/{id}', 'Sistema\Notificacao\NotificacaoController@destroy')->name('notificacao.destroy');
        Route::delete('/notificacao/{id}', 'Sistema\Notificacao\NotificacaoController@destroy')->name('notificacao.destroy');

        Route::resource('notificacao', 'Sistema\Notificacao\NotificacaoController');
        // Route::resource('transportadora', 'Sistema\Transportadora\TransportadoraController');
        // Route::resource('documentacao', 'Sistema\Documentacao\DocumentacaoController');
        // Route::resource('perfil', 'Sistema\Usuario\Perfil\PerfilController');

        Route::get('/logout', 'Auth\LoginController@logout');
    }
}
