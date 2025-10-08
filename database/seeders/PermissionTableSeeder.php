<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $Cadastros = 1;
        $Vendas = 2;
        $Administracao = 3;
        $Configuracoes = 4;

        $permissions = [
            ['id' => $Cadastros, 'parent_id' => 0, 'description' => 'Cadastros', 'name' => 'cadastro', 'icon' => 'fa-plus-square'],
            ['id' => $Vendas, 'parent_id' => 0, 'description' => 'Vendas', 'name' => 'venda', 'icon' => 'fa-plus-square'],
            ['id' => $Administracao, 'parent_id' => 0, 'description' => 'Administração', 'name' => 'administracao', 'icon' => 'fa-plus-square'],
            ['id' => $Configuracoes, 'parent_id' => 0, 'description' => 'Configuracões', 'name' => 'configuracoes', 'icon' => 'fa-plus-square'],
            // CADASTRO / Agendamento
            ['description' => 'Agendamento', 'icon' => 'far fa-calendar-alt', 'name' => 'agendamento.store', 'parent_id' => $Cadastros],
            ['description' => 'Agendamento', 'icon' => 'far fa-calendar-alt', 'name' => 'agendamento.create', 'parent_id' => $Cadastros],
            ['description' => 'Agendamento', 'icon' => 'far fa-calendar-alt', 'name' => 'agendamento.edit', 'parent_id' => $Cadastros],
            ['description' => 'Agendamento', 'icon' => 'far fa-calendar-alt', 'name' => 'agendamento.update', 'parent_id' => $Cadastros],
            ['description' => 'Agendamento', 'icon' => 'far fa-calendar-alt', 'name' => 'agendamento.copy', 'parent_id' => $Cadastros],
            ['description' => 'Agendamento', 'icon' => 'far fa-calendar-alt', 'name' => 'agendamento.index', 'parent_id' => $Cadastros],
            ['description' => 'Agendamento', 'icon' => 'far fa-calendar-alt', 'name' => 'agendamento.show', 'parent_id' => $Cadastros],
            ['description' => 'Agendamento', 'icon' => 'far fa-calendar-alt', 'name' => 'agendamento.destroy', 'parent_id' => $Cadastros],

            // CADASTRO / Usuário
            ['description' => 'Usuário', 'icon' => 'fa fa-user', 'name' => 'usuario.store', 'parent_id' => $Cadastros],
            ['description' => 'Usuário', 'icon' => 'fa fa-user', 'name' => 'usuario.create', 'parent_id' => $Cadastros],
            ['description' => 'Usuário', 'icon' => 'fa fa-user', 'name' => 'usuario.edit', 'parent_id' => $Cadastros],
            ['description' => 'Usuário', 'icon' => 'fa fa-user', 'name' => 'usuario.update', 'parent_id' => $Cadastros],
            ['description' => 'Usuário', 'icon' => 'fa fa-user', 'name' => 'usuario.copy', 'parent_id' => $Cadastros],
            ['description' => 'Usuário', 'icon' => 'fa fa-user', 'name' => 'usuario.index', 'parent_id' => $Cadastros],
            ['description' => 'Usuário', 'icon' => 'fa fa-user', 'name' => 'usuario.show', 'parent_id' => $Cadastros],
            ['description' => 'Usuário', 'icon' => 'fa fa-user', 'name' => 'usuario.destroy', 'parent_id' => $Cadastros],

            // CADASTRO / Cliente
            ['description' => 'Cliente', 'icon' => 'fa fa-address-card', 'name' => 'cliente.store', 'parent_id' => $Cadastros],
            ['description' => 'Cliente', 'icon' => 'fa fa-address-card', 'name' => 'cliente.create', 'parent_id' => $Cadastros],
            ['description' => 'Cliente', 'icon' => 'fa fa-address-card', 'name' => 'cliente.edit', 'parent_id' => $Cadastros],
            ['description' => 'Cliente', 'icon' => 'fa fa-address-card', 'name' => 'cliente.update', 'parent_id' => $Cadastros],
            ['description' => 'Cliente', 'icon' => 'fa fa-address-card', 'name' => 'cliente.copy', 'parent_id' => $Cadastros],
            ['description' => 'Cliente', 'icon' => 'fa fa-address-card', 'name' => 'cliente.index', 'parent_id' => $Cadastros],
            ['description' => 'Cliente', 'icon' => 'fa fa-address-card', 'name' => 'cliente.show', 'parent_id' => $Cadastros],
            ['description' => 'Cliente', 'icon' => 'fa fa-address-card', 'name' => 'cliente.destroy', 'parent_id' => $Cadastros],

            // CADASTRO / Empresa
            ['description' => 'Empresa', 'icon' => 'fas fa-store', 'name' => 'empresa.store', 'parent_id' => $Cadastros],
            ['description' => 'Empresa', 'icon' => 'fas fa-store', 'name' => 'empresa.create', 'parent_id' => $Cadastros],
            ['description' => 'Empresa', 'icon' => 'fas fa-store', 'name' => 'empresa.edit', 'parent_id' => $Cadastros],
            ['description' => 'Empresa', 'icon' => 'fas fa-store', 'name' => 'empresa.update', 'parent_id' => $Cadastros],
            ['description' => 'Empresa', 'icon' => 'fas fa-store', 'name' => 'empresa.copy', 'parent_id' => $Cadastros],
            ['description' => 'Empresa', 'icon' => 'fas fa-store', 'name' => 'empresa.index', 'parent_id' => $Cadastros],
            ['description' => 'Empresa', 'icon' => 'fas fa-store', 'name' => 'empresa.show', 'parent_id' => $Cadastros],
            ['description' => 'Empresa', 'icon' => 'fas fa-store', 'name' => 'empresa.destroy', 'parent_id' => $Cadastros],

            // CADASTRO / Cartão Fidelidade / Gift
            ['description' => 'Cartão Fidelidade / Gift', 'icon' => 'fas fa-credit-card-front', 'name' => 'cartao-fidelidade-gift.store', 'parent_id' => $Cadastros],
            ['description' => 'Cartão Fidelidade / Gift', 'icon' => 'fas fa-credit-card-front', 'name' => 'cartao-fidelidade-gift.create', 'parent_id' => $Cadastros],
            ['description' => 'Cartão Fidelidade / Gift', 'icon' => 'fas fa-credit-card-front', 'name' => 'cartao-fidelidade-gift.edit', 'parent_id' => $Cadastros],
            ['description' => 'Cartão Fidelidade / Gift', 'icon' => 'fas fa-credit-card-front', 'name' => 'cartao-fidelidade-gift.update', 'parent_id' => $Cadastros],
            ['description' => 'Cartão Fidelidade / Gift', 'icon' => 'fas fa-credit-card-front', 'name' => 'cartao-fidelidade-gift.copy', 'parent_id' => $Cadastros],
            ['description' => 'Cartão Fidelidade / Gift', 'icon' => 'fas fa-credit-card-front', 'name' => 'cartao-fidelidade-gift.index', 'parent_id' => $Cadastros],
            ['description' => 'Cartão Fidelidade / Gift', 'icon' => 'fas fa-credit-card-front', 'name' => 'cartao-fidelidade-gift.show', 'parent_id' => $Cadastros],
            ['description' => 'Cartão Fidelidade / Gift', 'icon' => 'fas fa-credit-card-front', 'name' => 'cartao-fidelidade-gift.destroy', 'parent_id' => $Cadastros],

            // CADASTRO / Programa de Pontos
            ['description' => 'Programa de Pontos', 'icon' => 'fas fa-credit-card', 'name' => 'programa-de-pontos.store', 'parent_id' => $Cadastros],
            ['description' => 'Programa de Pontos', 'icon' => 'fas fa-credit-card', 'name' => 'programa-de-pontos.create', 'parent_id' => $Cadastros],
            ['description' => 'Programa de Pontos', 'icon' => 'fas fa-credit-card', 'name' => 'programa-de-pontos.edit', 'parent_id' => $Cadastros],
            ['description' => 'Programa de Pontos', 'icon' => 'fas fa-credit-card', 'name' => 'programa-de-pontos.update', 'parent_id' => $Cadastros],
            ['description' => 'Programa de Pontos', 'icon' => 'fas fa-credit-card', 'name' => 'programa-de-pontos.copy', 'parent_id' => $Cadastros],
            ['description' => 'Programa de Pontos', 'icon' => 'fas fa-credit-card', 'name' => 'programa-de-pontos.index', 'parent_id' => $Cadastros],
            ['description' => 'Programa de Pontos', 'icon' => 'fas fa-credit-card', 'name' => 'programa-de-pontos.show', 'parent_id' => $Cadastros],
            ['description' => 'Programa de Pontos', 'icon' => 'fas fa-credit-card', 'name' => 'programa-de-pontos.destroy', 'parent_id' => $Cadastros],

            // CADASTRO / Produtos
            ['description' => 'Produto', 'icon' => 'fas fa-box-check', 'name' => 'produto.store', 'parent_id' => $Cadastros],
            ['description' => 'Produto', 'icon' => 'fas fa-box-check', 'name' => 'produto.create', 'parent_id' => $Cadastros],
            ['description' => 'Produto', 'icon' => 'fas fa-box-check', 'name' => 'produto.edit', 'parent_id' => $Cadastros],
            ['description' => 'Produto', 'icon' => 'fas fa-box-check', 'name' => 'produto.update', 'parent_id' => $Cadastros],
            ['description' => 'Produto', 'icon' => 'fas fa-box-check', 'name' => 'produto.copy', 'parent_id' => $Cadastros],
            ['description' => 'Produto', 'icon' => 'fas fa-box-check', 'name' => 'produto.index', 'parent_id' => $Cadastros],
            ['description' => 'Produto', 'icon' => 'fas fa-box-check', 'name' => 'produto.show', 'parent_id' => $Cadastros],
            ['description' => 'Produto', 'icon' => 'fas fa-box-check', 'name' => 'produto.destroy', 'parent_id' => $Cadastros],

            // VENDAS / Comprovantes Pgto.
            // ['description' => 'Comprovantes Pgto.', 'icon' => 'far fa-receipt', 'name' => 'comprovantes-pgto.store', 'parent_id' => $Vendas],
            // ['description' => 'Comprovantes Pgto.', 'icon' => 'far fa-receipt', 'name' => 'comprovantes-pgto.create', 'parent_id' => $Vendas],
            // ['description' => 'Comprovantes Pgto.', 'icon' => 'far fa-receipt', 'name' => 'comprovantes-pgto.edit', 'parent_id' => $Vendas],
            // ['description' => 'Comprovantes Pgto.', 'icon' => 'far fa-receipt', 'name' => 'comprovantes-pgto.update', 'parent_id' => $Vendas],
            // ['description' => 'Comprovantes Pgto.', 'icon' => 'far fa-receipt', 'name' => 'comprovantes-pgto.copy', 'parent_id' => $Vendas],
            // ['description' => 'Comprovantes Pgto.', 'icon' => 'far fa-receipt', 'name' => 'comprovantes-pgto.index', 'parent_id' => $Vendas],
            // ['description' => 'Comprovantes Pgto.', 'icon' => 'far fa-receipt', 'name' => 'comprovantes-pgto.show', 'parent_id' => $Vendas],
            // ['description' => 'Comprovantes Pgto.', 'icon' => 'far fa-receipt', 'name' => 'comprovantes-pgto.destroy', 'parent_id' => $Vendas],

            // VENDAS / Faturamento Serviços
            ['description' => 'Faturamento Serviços', 'icon' => 'fas fa-hand-holding-usd', 'name' => 'faturamento-servico.store', 'parent_id' => $Vendas],
            ['description' => 'Faturamento Serviços', 'icon' => 'fas fa-hand-holding-usd', 'name' => 'faturamento-servico.create', 'parent_id' => $Vendas],
            ['description' => 'Faturamento Serviços', 'icon' => 'fas fa-hand-holding-usd', 'name' => 'faturamento-servico.edit', 'parent_id' => $Vendas],
            ['description' => 'Faturamento Serviços', 'icon' => 'fas fa-hand-holding-usd', 'name' => 'faturamento-servico.update', 'parent_id' => $Vendas],
            ['description' => 'Faturamento Serviços', 'icon' => 'fas fa-hand-holding-usd', 'name' => 'faturamento-servico.copy', 'parent_id' => $Vendas],
            ['description' => 'Faturamento Serviços', 'icon' => 'fas fa-hand-holding-usd', 'name' => 'faturamento-servico.index', 'parent_id' => $Vendas],
            ['description' => 'Faturamento Serviços', 'icon' => 'fas fa-hand-holding-usd', 'name' => 'faturamento-servico.show', 'parent_id' => $Vendas],
            ['description' => 'Faturamento Serviços', 'icon' => 'fas fa-hand-holding-usd', 'name' => 'faturamento-servico.destroy', 'parent_id' => $Vendas],

            // VENDAS / PDV WEB
            ['description' => 'PDV WEB', 'icon' => 'fas fa-cash-register', 'name' => 'pdv-web.store', 'parent_id' => $Vendas],
            ['description' => 'PDV WEB', 'icon' => 'fas fa-cash-register', 'name' => 'pdv-web.create', 'parent_id' => $Vendas],
            ['description' => 'PDV WEB', 'icon' => 'fas fa-cash-register', 'name' => 'pdv-web.edit', 'parent_id' => $Vendas],
            ['description' => 'PDV WEB', 'icon' => 'fas fa-cash-register', 'name' => 'pdv-web.update', 'parent_id' => $Vendas],
            ['description' => 'PDV WEB', 'icon' => 'fas fa-cash-register', 'name' => 'pdv-web.copy', 'parent_id' => $Vendas],
            ['description' => 'PDV WEB', 'icon' => 'fas fa-cash-register', 'name' => 'pdv-web.index', 'parent_id' => $Vendas],
            ['description' => 'PDV WEB', 'icon' => 'fas fa-cash-register', 'name' => 'pdv-web.show', 'parent_id' => $Vendas],
            ['description' => 'PDV WEB', 'icon' => 'fas fa-cash-register', 'name' => 'pdv-web.destroy', 'parent_id' => $Vendas],

            // VENDAS / Recarga Cartões
            ['description' => 'Recarga Cartões', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-cartoes.store', 'parent_id' => $Vendas],
            ['description' => 'Recarga Cartões', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-cartoes.create', 'parent_id' => $Vendas],
            ['description' => 'Recarga Cartões', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-cartoes.edit', 'parent_id' => $Vendas],
            ['description' => 'Recarga Cartões', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-cartoes.update', 'parent_id' => $Vendas],
            ['description' => 'Recarga Cartões', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-cartoes.copy', 'parent_id' => $Vendas],
            ['description' => 'Recarga Cartões', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-cartoes.index', 'parent_id' => $Vendas],
            ['description' => 'Recarga Cartões', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-cartoes.show', 'parent_id' => $Vendas],
            ['description' => 'Recarga Cartões', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-cartoes.destroy', 'parent_id' => $Vendas],

            // VENDAS / Recarga Gift Card
            // ['description' => 'Recarga Gift Card', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-gift-card.store', 'parent_id' => $Vendas],
            // ['description' => 'Recarga Gift Card', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-gift-card.create', 'parent_id' => $Vendas],
            // ['description' => 'Recarga Gift Card', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-gift-card.edit', 'parent_id' => $Vendas],
            // ['description' => 'Recarga Gift Card', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-gift-card.update', 'parent_id' => $Vendas],
            // ['description' => 'Recarga Gift Card', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-gift-card.copy', 'parent_id' => $Vendas],
            // ['description' => 'Recarga Gift Card', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-gift-card.index', 'parent_id' => $Vendas],
            // ['description' => 'Recarga Gift Card', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-gift-card.show', 'parent_id' => $Vendas],
            // ['description' => 'Recarga Gift Card', 'icon' => 'fas fa-credit-card', 'name' => 'recarga-gift-card.destroy', 'parent_id' => $Vendas],

            // ADMINISTRAÇÃO / Manutenção de Títulos
            ['description' => 'Manutenção de Títulos', 'icon' => 'fas fa-file-invoice', 'name' => 'manutencao-titulo.store', 'parent_id' => $Administracao],
            ['description' => 'Manutenção de Títulos', 'icon' => 'fas fa-file-invoice', 'name' => 'manutencao-titulo.create', 'parent_id' => $Administracao],
            ['description' => 'Manutenção de Títulos', 'icon' => 'fas fa-file-invoice', 'name' => 'manutencao-titulo.edit', 'parent_id' => $Administracao],
            ['description' => 'Manutenção de Títulos', 'icon' => 'fas fa-file-invoice', 'name' => 'manutencao-titulo.update', 'parent_id' => $Administracao],
            ['description' => 'Manutenção de Títulos', 'icon' => 'fas fa-file-invoice', 'name' => 'manutencao-titulo.copy', 'parent_id' => $Administracao],
            ['description' => 'Manutenção de Títulos', 'icon' => 'fas fa-file-invoice', 'name' => 'manutencao-titulo.index', 'parent_id' => $Administracao],
            ['description' => 'Manutenção de Títulos', 'icon' => 'fas fa-file-invoice', 'name' => 'manutencao-titulo.show', 'parent_id' => $Administracao],
            ['description' => 'Manutenção de Títulos', 'icon' => 'fas fa-file-invoice', 'name' => 'manutencao-titulo.destroy', 'parent_id' => $Administracao],

            // ADMINISTRAÇÃO / Painel de Cobranças
            ['description' => 'Painel de Cobranças', 'icon' => 'fas fa-desktop', 'name' => 'painel-cobranca.store', 'parent_id' => $Administracao],
            ['description' => 'Painel de Cobranças', 'icon' => 'fas fa-desktop', 'name' => 'painel-cobranca.create', 'parent_id' => $Administracao],
            ['description' => 'Painel de Cobranças', 'icon' => 'fas fa-desktop', 'name' => 'painel-cobranca.edit', 'parent_id' => $Administracao],
            ['description' => 'Painel de Cobranças', 'icon' => 'fas fa-desktop', 'name' => 'painel-cobranca.update', 'parent_id' => $Administracao],
            ['description' => 'Painel de Cobranças', 'icon' => 'fas fa-desktop', 'name' => 'painel-cobranca.copy', 'parent_id' => $Administracao],
            ['description' => 'Painel de Cobranças', 'icon' => 'fas fa-desktop', 'name' => 'painel-cobranca.index', 'parent_id' => $Administracao],
            ['description' => 'Painel de Cobranças', 'icon' => 'fas fa-desktop', 'name' => 'painel-cobranca.show', 'parent_id' => $Administracao],
            ['description' => 'Painel de Cobranças', 'icon' => 'fas fa-desktop', 'name' => 'painel-cobranca.destroy', 'parent_id' => $Administracao],

            // ADMINISTRAÇÃO / BI - Relatórios
            ['description' => 'BI - Relatórios', 'icon' => 'fas fa-chart-line', 'name' => 'bi-relatorios.store', 'parent_id' => $Administracao],
            ['description' => 'BI - Relatórios', 'icon' => 'fas fa-chart-line', 'name' => 'bi-relatorios.create', 'parent_id' => $Administracao],
            ['description' => 'BI - Relatórios', 'icon' => 'fas fa-chart-line', 'name' => 'bi-relatorios.edit', 'parent_id' => $Administracao],
            ['description' => 'BI - Relatórios', 'icon' => 'fas fa-chart-line', 'name' => 'bi-relatorios.update', 'parent_id' => $Administracao],
            ['description' => 'BI - Relatórios', 'icon' => 'fas fa-chart-line', 'name' => 'bi-relatorios.copy', 'parent_id' => $Administracao],
            ['description' => 'BI - Relatórios', 'icon' => 'fas fa-chart-line', 'name' => 'bi-relatorios.index', 'parent_id' => $Administracao],
            ['description' => 'BI - Relatórios', 'icon' => 'fas fa-chart-line', 'name' => 'bi-relatorios.show', 'parent_id' => $Administracao],
            ['description' => 'BI - Relatórios', 'icon' => 'fas fa-chart-line', 'name' => 'bi-relatorios.destroy', 'parent_id' => $Administracao],

            // CONFIGURAÇÕES / Carga de Dados
            ['description' => 'Carga de Dados', 'icon' => 'fas fa-arrow-circle-up', 'name' => 'carga-dados.store', 'parent_id' => $Configuracoes],
            ['description' => 'Carga de Dados', 'icon' => 'fas fa-arrow-circle-up', 'name' => 'carga-dados.create', 'parent_id' => $Configuracoes],
            ['description' => 'Carga de Dados', 'icon' => 'fas fa-arrow-circle-up', 'name' => 'carga-dados.edit', 'parent_id' => $Configuracoes],
            ['description' => 'Carga de Dados', 'icon' => 'fas fa-arrow-circle-up', 'name' => 'carga-dados.update', 'parent_id' => $Configuracoes],
            ['description' => 'Carga de Dados', 'icon' => 'fas fa-arrow-circle-up', 'name' => 'carga-dados.copy', 'parent_id' => $Configuracoes],
            ['description' => 'Carga de Dados', 'icon' => 'fas fa-arrow-circle-up', 'name' => 'carga-dados.index', 'parent_id' => $Configuracoes],
            ['description' => 'Carga de Dados', 'icon' => 'fas fa-arrow-circle-up', 'name' => 'carga-dados.show', 'parent_id' => $Configuracoes],
            ['description' => 'Carga de Dados', 'icon' => 'fas fa-arrow-circle-up', 'name' => 'carga-dados.destroy', 'parent_id' => $Configuracoes],

            // CONFIGURAÇÕES / Work Flow -fas fa-money-check-edit-alt
            ['description' => 'Work Flow', 'icon' => 'fas fa-project-diagram', 'name' => 'work-flow.store', 'parent_id' => $Configuracoes],
            ['description' => 'Work Flow', 'icon' => 'fas fa-project-diagram', 'name' => 'work-flow.create', 'parent_id' => $Configuracoes],
            ['description' => 'Work Flow', 'icon' => 'fas fa-project-diagram', 'name' => 'work-flow.edit', 'parent_id' => $Configuracoes],
            ['description' => 'Work Flow', 'icon' => 'fas fa-project-diagram', 'name' => 'work-flow.update', 'parent_id' => $Configuracoes],
            ['description' => 'Work Flow', 'icon' => 'fas fa-project-diagram', 'name' => 'work-flow.copy', 'parent_id' => $Configuracoes],
            ['description' => 'Work Flow', 'icon' => 'fas fa-project-diagram', 'name' => 'work-flow.index', 'parent_id' => $Configuracoes],
            ['description' => 'Work Flow', 'icon' => 'fas fa-project-diagram', 'name' => 'work-flow.show', 'parent_id' => $Configuracoes],
            ['description' => 'Work Flow', 'icon' => 'fas fa-project-diagram', 'name' => 'work-flow.destroy', 'parent_id' => $Configuracoes],

            // CONFIGURAÇÕES / Sistema multban
            ['description' => 'Sistema Mult+', 'icon' => 'fas fa-cog', 'name' => 'config-sistema-multban.store', 'parent_id' => $Configuracoes],
            ['description' => 'Sistema Mult+', 'icon' => 'fas fa-cog', 'name' => 'config-sistema-multban.create', 'parent_id' => $Configuracoes],
            ['description' => 'Sistema Mult+', 'icon' => 'fas fa-cog', 'name' => 'config-sistema-multban.edit', 'parent_id' => $Configuracoes],
            ['description' => 'Sistema Mult+', 'icon' => 'fas fa-cog', 'name' => 'config-sistema-multban.update', 'parent_id' => $Configuracoes],
            ['description' => 'Sistema Mult+', 'icon' => 'fas fa-cog', 'name' => 'config-sistema-multban.copy', 'parent_id' => $Configuracoes],
            ['description' => 'Sistema Mult+', 'icon' => 'fas fa-cog', 'name' => 'config-sistema-multban.index', 'parent_id' => $Configuracoes],
            ['description' => 'Sistema Mult+', 'icon' => 'fas fa-cog', 'name' => 'config-sistema-multban.show', 'parent_id' => $Configuracoes],
            ['description' => 'Sistema Mult+', 'icon' => 'fas fa-cog', 'name' => 'config-sistema-multban.destroy', 'parent_id' => $Configuracoes],

            // CONFIGURAÇÕES / OnBoarding Empresas
            // ['description' => 'OnBoarding Empresas', 'icon' => 'fas fa-layer-plus', 'name' => 'onboarding-empresas.store', 'parent_id' => $Configuracoes],
            // ['description' => 'OnBoarding Empresas', 'icon' => 'fas fa-layer-plus', 'name' => 'onboarding-empresas.create', 'parent_id' => $Configuracoes],
            // ['description' => 'OnBoarding Empresas', 'icon' => 'fas fa-layer-plus', 'name' => 'onboarding-empresas.edit', 'parent_id' => $Configuracoes],
            // ['description' => 'OnBoarding Empresas', 'icon' => 'fas fa-layer-plus', 'name' => 'onboarding-empresas.update', 'parent_id' => $Configuracoes],
            // ['description' => 'OnBoarding Empresas', 'icon' => 'fas fa-layer-plus', 'name' => 'onboarding-empresas.copy', 'parent_id' => $Configuracoes],
            // ['description' => 'OnBoarding Empresas', 'icon' => 'fas fa-layer-plus', 'name' => 'onboarding-empresas.index', 'parent_id' => $Configuracoes],
            // ['description' => 'OnBoarding Empresas', 'icon' => 'fas fa-layer-plus', 'name' => 'onboarding-empresas.show', 'parent_id' => $Configuracoes],
            // ['description' => 'OnBoarding Empresas', 'icon' => 'fas fa-layer-plus', 'name' => 'onboarding-empresas.destroy', 'parent_id' => $Configuracoes],

            // CONFIGURAÇÕES / Perfis de Acesso
            ['description' => 'Perfis de Acesso', 'icon' => 'fas fa-user-tag', 'name' => 'perfil-de-acesso.store', 'parent_id' => $Configuracoes],
            ['description' => 'Perfis de Acesso', 'icon' => 'fas fa-user-tag', 'name' => 'perfil-de-acesso.create', 'parent_id' => $Configuracoes],
            ['description' => 'Perfis de Acesso', 'icon' => 'fas fa-user-tag', 'name' => 'perfil-de-acesso.edit', 'parent_id' => $Configuracoes],
            ['description' => 'Perfis de Acesso', 'icon' => 'fas fa-user-tag', 'name' => 'perfil-de-acesso.update', 'parent_id' => $Configuracoes],
            ['description' => 'Perfis de Acesso', 'icon' => 'fas fa-user-tag', 'name' => 'perfil-de-acesso.copy', 'parent_id' => $Configuracoes],
            ['description' => 'Perfis de Acesso', 'icon' => 'fas fa-user-tag', 'name' => 'perfil-de-acesso.index', 'parent_id' => $Configuracoes],
            ['description' => 'Perfis de Acesso', 'icon' => 'fas fa-user-tag', 'name' => 'perfil-de-acesso.show', 'parent_id' => $Configuracoes],
            ['description' => 'Perfis de Acesso', 'icon' => 'fas fa-user-tag', 'name' => 'perfil-de-acesso.destroy', 'parent_id' => $Configuracoes],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
