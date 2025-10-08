<?php

use App\Http\Controllers\HomeController;
use App\Http\Routes\AgendamentoRoute;
use App\Http\Routes\AuditoriaRoute;
use App\Http\Routes\CargaDadosRoute;
use App\Http\Routes\ClienteRoute;
use App\Http\Routes\ConfiguracoesRoute;
use App\Http\Routes\EmpresaRoute;
use App\Http\Routes\FaturamentoServicoRoute;
use App\Http\Routes\GiftCardRoute;
use App\Http\Routes\ManutencaoTituloRoute;
use App\Http\Routes\PainelCobrancaRoute;
use App\Http\Routes\PerfilDeAcessoRoute;
use App\Http\Routes\PerfilRoute;
use App\Http\Routes\ProdutoRoute;
use App\Http\Routes\ProgramaPTSRoute;
use App\Http\Routes\RecargaCartoesRoute;
use App\Http\Routes\RelatoriosRoute;
use App\Http\Routes\SistemamultbanRoute;
use App\Http\Routes\UsuarioRoute;
use App\Http\Routes\VendasRoute;
use App\Http\Routes\WorkFlowRoute;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/cache/clear', function () {
    try {
        Artisan::call('optimize:clear');
    } catch (\Throwable $th) {
        return redirect('login');
    }

    return 'chace limpo <a href="/">rotornar</a>';
});

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/maisvendidos', [HomeController::class, 'maisvendidos'])->name('home.maisvendidos');
    /**
     * Clientes Routes
     */
    ClienteRoute::rotas();

    /**
     * Usuarios Routes
     */
    UsuarioRoute::rotas();

    /**
     * Agenda Routes
     */
    AgendamentoRoute::rotas();

    /**
     * PerfilRoute Routes
     */
    PerfilRoute::rotas();

    /**
     * AuditoriaRoute Routes
     */
    AuditoriaRoute::rotas();

    /**
     * Perfil De Acesso Routes
     */
    PerfilDeAcessoRoute::rotas();

    /**
     * Empresa Route
     */
    EmpresaRoute::rotas();

    /**
     * GiftCard Route
     */
    GiftCardRoute::rotas();

    /**
     * Produto Route
     */
    ProdutoRoute::rotas();

    /**
     * Programa de Pontos Route
     */
    ProgramaPTSRoute::rotas();

    /**
     * Programa multban
     */
    SistemamultbanRoute::rotas();

    /**
     * WorkFlow
     */
    WorkFlowRoute::rotas();

    /**
     * Painel de Cobrança
     */
    PainelCobrancaRoute::rotas();

    /**
     * Manutenção Título
     */
    ManutencaoTituloRoute::rotas();

    /**
     * Faturamento de serviços
     */
    FaturamentoServicoRoute::rotas();

    /**
     * Recarga Cartões
     */
    RecargaCartoesRoute::rotas();

    /**
     * Carga de Dados
     */
    CargaDadosRoute::rotas();

    /**
     * Relatórios
     */
    RelatoriosRoute::rotas();

    /**
     * Vendas
     */
    VendasRoute::rotas();

    /**
     * Configurações
     */
    ConfiguracoesRoute::rotas();
});

require __DIR__ . '/auth.php';

Auth::routes();
