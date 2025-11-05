@extends('layouts.app-master')
@section('page.title', 'Cliente')
@push('script-head')

<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-select/css/select.bootstrap4.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-fixedheader/css/fixedHeader.bootstrap4.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}" />
<link href="{{ asset('assets/plugins/summernote/summernote-bs4.min.css') }}" rel="stylesheet">
  <!-- Ekko Lightbox -->
  <link rel="stylesheet" href="{{ asset('assets/plugins/ekko-lightbox/ekko-lightbox.css') }}">

@endpush

@section('content')
<!-- Main content -->
<section class="content">

    @if($routeAction)

    <form class="form-horizontal" id="formPrincipal" role="form" method="POST"
        action="{{ route('cliente.update', $cliente->cliente_id) }}">
        @method('PATCH')
        @else
        <form class="form-horizontal" id="formPrincipal" role="form" method="POST"
            action="{{ route('cliente.store') }}">
            @method('POST')
            @endif
            @include('Multban.template.updatetemplate')

            <input type="hidden" id="cliente_id" name="cliente_id" value="{{$cliente->cliente_id}}" />
            <input type="hidden" id="empresa_id" name="empresa_id" value="{{$empresa->emp_id}}" />
            <input type="hidden" id="is_edit" value="1" />

            <div class="card card-primary card-outline card-outline-tabs">
                <!-- MENU ABAS/TABS -->
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">

                        <li class="nav-item">
                            <a class="nav-link {{!request()->has('pront') ? 'active' : ''}}" id="tabs-dados-tab"
                                data-toggle="pill" href="#tabs-dados" role="tab" aria-controls="tabs-dados"
                                aria-selected="true">Dados Gerais</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="tabs-endereco-tab" data-toggle="pill" href="#tabs-endereco"
                                role="tab" aria-controls="tabs-endereco" aria-selected="false">Endereço</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="tabs-compras-tab" data-toggle="pill" href="#tabs-compras" role="tab"
                                aria-controls="tabs-compras" aria-selected="false">Compras Realizadas</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="tabs-score-tab" data-toggle="pill" href="#tabs-score" role="tab"
                                aria-controls="tabs-score" aria-selected="false">SCORE</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="tabs-cartoes-tab" data-toggle="pill" href="#tabs-cartoes" role="tab"
                                aria-controls="tabs-cartoes" aria-selected="false">Cartões</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{request()->has('pront') ? 'active' : ''}}" id="tabs-prontuario-tab"
                                data-toggle="pill" href="#tabs-prontuario" role="tab" aria-controls="tabs-prontuario"
                                aria-selected="false">Prontuário</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="tabs-atendimento-tab" data-toggle="pill" href="#tabs-atendimento"
                                role="tab" aria-controls="tabs-atendimento" aria-selected="false">Atendimento</a>
                        </li>

                    </ul>
                </div>

                <!-- CONTEÚDO ABAS/TABS -->
                <div class="card-body">
                    <div class="tab-content" id="custom-tabs-two-tabContent">

                        <!--ABA DADOS GERAIS-->
                        <div class="tab-pane fade {{!request()->has('pront') ? 'active show' : ''}}" id="tabs-dados"
                            role="tabpanel" aria-labelledby="tabs-dados-tab">

                            <div class="card card-primary">

                                <div class="card-body">

                                    <div class="form-row">

                                        <div class="form-group col-md-2">
                                            <label for="cliente_tipo">Tipo de cliente:*</label>
                                            <select class="form-control select2" id="cliente_tipo" name="cliente_tipo"
                                                data-placeholder="Selecione o tipo" style="width: 100%;">
                                                <option></option>
                                                @foreach($tipos as $key => $tipo)
                                                <option value="{{$tipo->cliente_tipo}}" {{$cliente->cliente_tipo ==
                                                    $tipo->cliente_tipo ? 'selected': ''}}>{{$tipo->cliente_tipo_desc}}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label for="cliente_sts">Status:*</label>
                                            <select class="form-control select2" id="cliente_sts" name="cliente_sts"
                                                data-placeholder="Selecione o Status" style="width: 100%;">
                                                <option></option>
                                                @if ($canChangeStatus)
                                                @foreach($status as $key => $sta)
                                                <option value="{{$sta->cliente_sts}}" {{$cliente->cliente_sts ==
                                                    $sta->cliente_sts ? 'selected': ''}}>{{$sta->cliente_sts_desc}}
                                                </option>
                                                @endforeach
                                                @else
                                                <option value="NA" selected>Em análise</option>
                                                @endif
                                            </select>
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label for="cliente_doc" id="labelcliente_doc">CPF/CNPJ:*</label>
                                            <div class="input-group input-group-sm">
                                                <input type="text" id="cliente_doc" name="cliente_doc"
                                                    class="form-control  form-control-sm"
                                                    placeholder="Digite o CPF ou CNPJ"
                                                    value="{{$cliente->cliente_doc}}">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="cliente_rg">RG:</label>
                                            <div class="input-group input-group-sm">
                                                <input type="text" maxlength="15" id="cliente_rg" name="cliente_rg"
                                                    class="form-control  form-control-sm" placeholder="Digite o RG"
                                                    value="{{$cliente->cliente_rg}}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row">

                                        <div class="form-group col-md-4">
                                            <label for="cliente_nome">Nome:*</label>
                                            <input autocomplete="off" maxlength="255"
                                                class="form-control  form-control-sm" placeholder="Digite o nome"
                                                name="cliente_nome" type="text" id="cliente_nome"
                                                value="{{$cliente->cliente_nome}}">
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label for="cliente_nm_alt">Nome Alternativo:</label>
                                            <input autocomplete="off" maxlength="255"
                                                class="form-control  form-control-sm"
                                                placeholder="Digite o nome alternativo" name="cliente_nm_alt"
                                                type="text" id="cliente_nm_alt" value="{{$cliente->cliente_nm_alt}}">
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label for="cliente_dt_nasc">Data de Nascimento:*</label>
                                            <input class="form-control  form-control-sm"
                                                placeholder="Digite a data de nascimento" name="cliente_dt_nasc"
                                                type="date" id="cliente_dt_nasc" value="{{$cliente->cliente_dt_nasc}}"
                                                required>
                                        </div>

                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label for="cliente_email">E-mail:*</label>
                                            <div class="input-group input-group-sm">
                                                <input autocomplete="off" type="email"
                                                    class="form-control  form-control-sm" id="cliente_email"
                                                    name="cliente_email" value="{{$cliente->cliente_email}}"
                                                    placeholder="Digite o E-mail">
                                            </div>
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label for="cliente_nm_card">Nome Impresso no Cartão:</label>
                                            <input autocomplete="off" class="form-control  form-control-sm"
                                                placeholder="Digite o nome impresso no cartão" name="cliente_nm_card"
                                                type="text" id="cliente_nm_card" value="{{$cliente->cliente_nm_card}}">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-2">
                                            <label for="cliente_cel">Celular:*</label>
                                            <input autocomplete="off" type="text"
                                                class="form-control cell_with_ddd form-control-sm" id="cliente_cel"
                                                name="cliente_cel" value="{{$cliente->cliente_cel}}"
                                                placeholder="Digite o Celular">
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label for="cliente_telfixo">Telefone Fixo:</label>
                                            <input autocomplete="off" type="text"
                                                class="form-control phone_with_ddd form-control-sm" id="cliente_telfixo"
                                                name="cliente_telfixo" value="{{$cliente->cliente_telfixo}}"
                                                placeholder="Digite o Telefone">
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label for="cliente_rendam">Renda Mensal Aprox.:*</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">R$</span>
                                                </div>
                                                <input autocomplete="off" class="form-control money form-control-sm"
                                                    placeholder="Digite a renda mensal aproximada" name="cliente_rendam"
                                                    type="text" id="cliente_rendam" value="{{$cliente->cliente_rendam}}"
                                                    required>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label for="cliente_dt_fech">Dia para Fech.:*</label>
                                            <input class="form-control  form-control-sm"
                                                placeholder="Digite o melhor dia para fechamento" name="cliente_dt_fech"
                                                type="number" id="cliente_dt_fech" value="{{$cliente->cliente_dt_fech}}"
                                                required>
                                        </div>

                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-2">
                                            <label for="convenio_id">Convênio:*</label>
                                            <select class="form-control select2" style="width: 100%;"
                                                data-placeholder="Selecione o Convênio" id="convenio_id"
                                                name="convenio_id">
                                                <option></option>
                                                @foreach($convenios as $key => $convenio)
                                                <option value="{{$convenio->convenio_id}}" {{$convenio->convenio_id ==
                                                    $cliente->convenio_id ? 'selected' :
                                                    ''}}>{{$convenio->convenio_desc}}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label for="carteirinha">Carteirinha:</label>
                                            <input autocomplete="off" type="text" class="form-control form-control-sm"
                                                id="carteirinha" name="carteirinha" value="{{$cliente->carteirinha}}"
                                                placeholder="Digite a Carteirinha">
                                        </div>
                                    </div>
                                </div>
                                <!-- /.card-body -->
                            </div>

                            <!--Campo para os Dados Saneados-->
                            <div class="card card-default">

                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-pie mr-1"></i>
                                        Dados Saneados
                                    </h3>
                                </div>

                                <div class="card-body">

                                    <div class="form-row">
                                        <div class="form-group col-md-2">
                                            <label for="cliente_cel_s">Telefone Celular Saneado:</label>
                                            <input type="text" class="form-control cell_with_ddd form-control-sm"
                                                value="{{$cliente->cliente_cel_s}}" id="cliente_cel_s"
                                                name="cliente_cel_s" disabled>
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label for="cliente_rdam_s">Renda Mensal Saneada:</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">R$</span>
                                                </div>
                                                <input type="text" class="form-control money form-control-sm"
                                                    value="{{$cliente->cliente_rdam_s}}" id="cliente_rdam_s"
                                                    name="cliente_rdam_s" disabled>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label for="cliente_email_s">E-mail Saneado:</label>
                                            <input type="text" class="form-control  form-control-sm"
                                                value="{{$cliente->cliente_email_s}}" id="cliente_email_s"
                                                name="cliente_email_s" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--ABA ENDEREÇO-->
                        <div class="tab-pane fade" id="tabs-endereco" role="tabpanel"
                            aria-labelledby="tabs-endereco-tab">

                            <div class="card card-primary">

                                <div class="card-body">

                                    <div class="form-row">

                                        <div class="form-group col-md-2">
                                            <label for="cliente_cep">CEP:*</label>
                                            <a href="#" data-toggle="modal" data-target="#cep-info-modal">
                                                <i class="far fa-question-circle"></i>
                                            </a>

                                            <div class="input-group input-group-sm">
                                                <input autocomplete="off" type="text" autofocus="autofocus"
                                                    class="form-control cep form-control-sm" id="cliente_cep"
                                                    name="cliente_cep" value="{{$cliente->cliente_cep}}"
                                                    placeholder="CEP">
                                                <span class="input-group-append">
                                                    <button type="button" id="btnPesquisarCep"
                                                        class="btn btn-default"><i class="fa fa-search"></i></button>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label for="cliente_end">Endereço (Logradouro):*</label>
                                            <input autocomplete="off" class="form-control  form-control-sm"
                                                placeholder="Endereço" value="{{$cliente->cliente_end}}"
                                                name="cliente_end" type="text" id="cliente_end">
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label for="cliente_endnum">Número:*</label>
                                            <input autocomplete="off" class="form-control  form-control-sm"
                                                placeholder="Número" value="{{$cliente->cliente_endnum}}"
                                                name="cliente_endnum" type="text" id="cliente_endnum">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="cliente_endcmp">Complemento:</label>
                                            <input autocomplete="off" class="form-control  form-control-sm"
                                                placeholder="Complemento" value="{{$cliente->cliente_endcmp}}"
                                                name="cliente_endcmp" type="text" id="cliente_endcmp">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <label for="cliente_endbair">Bairro:*</label>
                                            <input autocomplete="off" class="form-control  form-control-sm"
                                                placeholder="Bairro" value="{{$cliente->cliente_endbair}}"
                                                name="cliente_endbair" type="text" id="cliente_endbair">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="cliente_endcid">Cidade:*</label>
                                            <select id="cliente_endcid" name="cliente_endcid"
                                                class="form-control select2 select2-hidden-accessible"
                                                data-placeholder="Pesquise a cidade" style="width: 100%;"
                                                aria-hidden="true">
                                                <option value="" data-estado="">Selecione</option>
                                                @foreach($cidades as $cidadeOption)
                                                    <option value="{{ $cidadeOption->cidade }}"
                                                        data-estado="{{ $cidadeOption->cidade_est }}"
                                                        @selected((string) old('cliente_endcid', $cliente->cliente_endcid) === (string) $cidadeOption->cidade)>
                                                        {{ $cidadeOption->cidade }} - {{ $cidadeOption->cidade_desc }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="cliente_endest">Estado:*</label>
                                            <select id="cliente_endest" name="cliente_endest"
                                                class="form-control select2 select2-hidden-accessible"
                                                data-placeholder="Pesquise o estado" style="width: 100%;"
                                                aria-hidden="true">
                                                <option value="">Selecione</option>
                                                @foreach($estados as $estadoOption)
                                                    <option value="{{ $estadoOption->estado }}"
                                                        @selected(old('cliente_endest', $cliente->cliente_endest) === $estadoOption->estado)>
                                                        {{ $estadoOption->estado }} - {{ $estadoOption->estado_desc }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label for="cliente_endpais">Pais:*</label>
                                            <select id="cliente_endpais" name="cliente_endpais"
                                                class="form-control select2 select2-hidden-accessible"
                                                data-placeholder="Pesquise o País" style="width: 100%;"
                                                aria-hidden="true">
                                                @if($cliente->pais)
                                                <option value="{{$cliente->pais->pais}}">{{$cliente->pais->pais}} -
                                                    {{$cliente->pais->pais_desc}}</option>
                                                @else
                                                <option value="BR">BR - BRASIL</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- card Endereço -->

                            <!--Campo para os Dados Saneados-->
                            <div class="card card-default">

                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-pie mr-1"></i>
                                        Dados Saneados
                                    </h3>
                                </div>
                                <div class="card-body">

                                    <div class="form-row">
                                        <div class="form-group col-md-2">
                                            <label for="cliente_cep_s">CEP:*</label>
                                            <input type="text" class="form-control  form-control-sm"
                                                value="{{$cliente->cliente_cep_s}}" id="cliente_cep_s"
                                                name="cliente_cep_s" disabled>
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label for="cliente_end_s">Endereço:</label>
                                            <input type="text" class="form-control  form-control-sm"
                                                value="{{$cliente->cliente_end_s}}" id="cliente_end_s"
                                                name="cliente_end_s" disabled>
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label for="cliente_endnum_s">Número:</label>
                                            <input type="text" class="form-control  form-control-sm"
                                                value="{{$cliente->cliente_endnum_s}}" id="cliente_endnum_s"
                                                name="cliente_endnum_s" disabled>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="cliente_endcmp_s">Complemento:</label>
                                            <input type="text" class="form-control  form-control-sm"
                                                value="{{$cliente->cliente_endcmp_s}}" id="cliente_endcmp_s"
                                                name="cliente_endcmp_s" disabled>
                                        </div>
                                    </div>

                                    <div class="form-row">

                                        <div class="form-group col-md-3">
                                            <label for="cliente_endbair_s">Bairro:</label>
                                            <input type="text" class="form-control  form-control-sm"
                                                value="{{$cliente->cliente_endbair_s}}" id="cliente_endbair_s"
                                                name="cliente_endbair_s" disabled>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="cliente_endcid_s">Cidade:</label>
                                            <input type="text" class="form-control  form-control-sm"
                                                value="{{$cliente->cliente_endcid_s}}" id="cliente_endcid_s"
                                                name="cliente_endcid_s" disabled>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label for="cliente_endest_s">Estado:</label>
                                            <input type="text" class="form-control  form-control-sm"
                                                value="{{$cliente->cliente_endest_s}}" id="cliente_endest_s"
                                                name="cliente_endest_s" disabled>
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label for="cliente_endpais_s">Pais:</label>
                                            <input type="text" class="form-control  form-control-sm"
                                                value="{{$cliente->cliente_endpais_s}}" id="cliente_endpais_s"
                                                name="cliente_endpais_s" disabled>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- ABA COMPRAS REALIZADAS -->
                        <div class="tab-pane fade" id="tabs-compras" role="tabpanel" aria-labelledby="tabs-compras-tab">

                            <div class="card card-primary">

                                <div class="card-body">

                                    <!-- FILTROS -->
                                    <div class="form-row">
                                        <div class="form-group col-md-2">

                                            <label for="dt_vencimento_mod">Data de Vencimento da Mensalidade:*</label>
                                            <input type="date" class="form-control  form-control-sm"
                                                id="dt_vencimento_mod" name="dt_vencimento_mod">
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label for="dt_vencimento">Data de Vencimento:</label> <input type="date"
                                                class="form-control  form-control-sm" id="dt_vencimento"
                                                name="dt_vencimento">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-2">
                                            <label for="dt_pagamento">Data de Pagamento:</label>
                                            <input type="date" class="form-control  form-control-sm" id="dt_pagamento"
                                                name="dt_pagamento">
                                        </div>

                                        <div class="form-group col-md-2">
                                            <label for="titulo_sts">Status do Título</label>
                                            <select id="titulo_sts" name="titulo_sts" class="form-control select2"
                                                data-placeholder="Selecione um Status" style="width: 100%;">
                                                <option value="OP">As opções devem ser gerar a partir dos dados mestres
                                                    deste campoY</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3 align-self-end ">
                                            <button type="button" id="btnPesquisar" class="btn btn-primary btn-sm"
                                                style=""><i class="fa fa-search"></i> Pesquisar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CORPO DO QUADRO DO GRID DE TÍTULOS -->
                            <div class="card card-outline card-primary">

                                <!-- CORPO DO QUADRO DO GRID DE TÍTULOS -->
                                <div class="card-body">

                                    <!-- AÇÕES GERAIS -->
                                    <div class="form-row">
                                        <div class="form-group col-md-2 align-self-end input-group-sm">
                                            <button type="button" class="form-control form-control-sm btn btn-primary"
                                                id="btnImprimirTudo">
                                                <i class="fas fa-shredder"></i> Imprimir Boletos</button>
                                        </div>

                                        <div class="form-group col-md-2 align-self-end input-group-sm">
                                            <button type="button" class="form-control form-control-sm btn btn-primary"
                                                id="btnAlterarTudo">
                                                <i class="fas fa-sync-alt"></i> Alteração em Massa</button>
                                        </div>
                                        <div class="form-group col-md-2 align-self-end input-group-sm">
                                            <button type="button" class="form-control form-control-sm btn btn-primary"
                                                id="btnEnviarLinkCompra">
                                                <i class="fas fa-money-check-edit-alt"></i> Enviar Link de
                                                Pagto</button>
                                        </div>
                                        <div class="form-group col-md-2 align-self-end input-group-sm">
                                            <button type="button" class="form-control form-control-sm btn btn-primary"
                                                id="btnEnviarLinkFatura">
                                                <i class="fas fa-credit-card"></i> Enviar Link da Fatura</button>
                                        </div>
                                    </div>

                                    <!-- TABELA DE DADOS -->
                                    <div class="table-responsive table-sm">
                                        <table id="gridtemplate"
                                            class="table table-striped table-bordered nowrap table-sm">
                                            <thead>
                                                <tr>
                                                    <th style="width: 20px;">
                                                        <input type="checkbox" id="selectAll" class="mr-2"
                                                            title="Selecionar Todos" />
                                                    </th>
                                                    <th style="width: 230px;">Ações</th>
                                                    <th>ID Emp.</th>
                                                    <th>Título</th>
                                                    <th>Cliente</th>
                                                    <th>Parcela</th>
                                                    <th>Vlr. Init.</th>
                                                    <th>Vlr. Jrs.</th>
                                                    <th>Vlr. Tot.</th>
                                                    <th>Meio Pgto</th>
                                                    <th>Data Venda</th>
                                                    <th>Data Venc.</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($compras as $compra)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="mr-2"
                                                            value="{{ $compra['identificador'] }}">
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-primary mt-1"
                                                            title="Imprimir Comprovante">
                                                            <i class="fas fa-print"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-primary mt-1"
                                                            title="Manutenção de Título">
                                                            <i class="fas fa-wrench"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-primary mt-1"
                                                            title="Pagar">
                                                            <i class="fas fa-usd-square"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-primary mt-1"
                                                            title="Cancelar">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-primary mt-1"
                                                            title="Baixa Manual">
                                                            <i class="fas fa-hands-usd"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-primary mt-1"
                                                            title="Cobrança">
                                                            <i class="far fa-file-invoice-dollar"></i>
                                                        </button>
                                                    </td>
                                                    <td>{{ $compra['emp_id'] }}</td>
                                                    <td>{{ $compra['titulo'] }}</td>
                                                    <td>{{ $compra['cliente'] }}</td>
                                                    <td>{{ $compra['parcela'] }}</td>
                                                    <td>{{ $compra['valor_inicial'] }}</td>
                                                    <td>{{ $compra['valor_juros'] }}</td>
                                                    <td>{{ $compra['valor_total'] }}</td>
                                                    <td>{{ $compra['meio_pagamento'] ?? '-' }}</td>
                                                    <td>{{ $compra['data_venda'] }}</td>
                                                    <td>{{ $compra['data_vencimento'] }}</td>
                                                    <td>
                                                        <span
                                                            class="badge badge-{{ data_get($compra, 'status.classe', 'secondary') }}">
                                                            {{ data_get($compra, 'status.descricao', '-') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="13" class="text-center text-muted">
                                                        Nenhuma compra encontrada para este cliente.
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- ABA SCORE -->
                        <div class="tab-pane fade" id="tabs-score" role="tabpanel" aria-labelledby="tabs-score-tab">
                            <div class="card card-primary">
                                <div class="card-body">

                                    <!-- AÇÕES -->
                                    <div class="form-row">
                                        <div class="form-group col-md-2">
                                            <button type="button" class="btn btn-primary btn-sm"
                                                id="btnConsultarScore">Consultar SCORE</button>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <input class="font-weight-bold" style="font-size: 1.5rem; color: #a702d8;"
                                                id="cliente_socre" name="cliente_socre" placeholder="SCORE"
                                                value="{{ $cliente->cliente_score ?? '' }}">
                                        </div>
                                    </div>

                                    <!-- TABELA -->
                                    <div class="table-responsive">
                                        <table id="gridtemplate-score"
                                            class="table table-striped table-bordered nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Número do Processo/Protesto</th>
                                                    <th>Tipo do Processo/Protesto</th>
                                                    <th>Descrição do Processo/Protesto</th>
                                                    <th>Status do Processo/Protesto</th>
                                                    <th>Data da Pesquisa</th>
                                                    <th>Data Início do Processo/Protesto</th>
                                                    <th>Data Fim do Processo/Protesto</th>
                                                    <th>Valor do Processo/Protesto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($scoreEntries as $score)
                                                <tr>
                                                    <td>{{ $score['numero'] }}</td>
                                                    <td>{{ $score['tipo'] }}</td>
                                                    <td>{{ $score['descricao'] }}</td>
                                                    <td>{{ $score['status'] }}</td>
                                                    <td>{{ $score['data_consulta'] }}</td>
                                                    <td>{{ $score['data_inicio'] }}</td>
                                                    <td>{{ $score['data_fim'] }}</td>
                                                    <td>{{ $score['valor'] }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted">
                                                        Nenhum registro de score encontrado para este cliente.
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        Ajuste o zoom do navegador se necessário para visualizar todas as colunas em
                                        telas menores.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ABA CARTÕES -->
                        <div class="tab-pane fade" id="tabs-cartoes" role="tabpanel" aria-labelledby="tabs-cartoes-tab">
                            <div class="card card-primary">
                                <div class="card-body">

                                    <!-- AÇÕES -->
                                    <div class="form-row">
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-primary btn-sm"
                                                data-modal="modalCriarCartao" id="btnCriarCartao">Criar Novo
                                                Cartão</button>
                                        </div>

                                        <div class="col-md-8">
                                            <div class="form-group d-inline-block">
                                                <label for="cliente_socre" class="mr-2">SCORE do Cliente:</label>
                                                <input type="text" id="cliente_socre"
                                                    class="form-control form-control-sm" readonly
                                                    style="width: 150px; display: inline-block;"
                                                    value="{{ $cliente->cliente_score ?? '' }}">
                                            </div>

                                            <div class="form-group d-inline-block ml-2">
                                                <label for="cliente_lmt_sg" class="mr-2">Limite Sugerido:</label>
                                                <input type="text" id="cliente_lmt_sg"
                                                    class="form-control form-control-sm" placeholder="Limite Sugerido"
                                                    readonly style="width: 150px; display: inline-block;">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- TABELA -->
                                    <table id="gridtemplate-cards" class="table table-striped table-bordered nowrap">
                                        <thead>
                                            <tr>
                                                <th>Ações</th>
                                                <th>Empresa</th>
                                                <th>Número do Cartão</th>
                                                <th>CV</th>
                                                <th>Status</th>
                                                <th>Tipo do Cartão</th>
                                                <th>Modalidade do Cartão</th>
                                                <th>Categoria</th>
                                                <th>Descrição do Cartão</th>
                                                <th>Saldo do Cartão</th>
                                                <th>Limite do Cartão</th>
                                                <th>Saldo de Pontos</th>
                                            </tr>
                                        </thead>
                                    </table>


                                    AQUI PRECISAMOS FAZER COM QUE ESTES DADOS DIMINUAM A FONTE CONFORME O TAMANHO DA
                                    TELA</br>
                                    PARA CABER NA TELA INDEPENDENTE DO TAMANHO DO DISPOSITIVO

                                </div>
                            </div>
                        </div>

                        <!--ABA PROTUÁRIO-->
                        <div class="tab-pane fade {{request()->has('pront') ? 'active show' : ''}}" id="tabs-prontuario"
                            role="tabpanel" aria-labelledby="tabs-prontuario-tab">

                            <!-- Seção Inicial: Linha do tempo -->
                            <div class="row">
                                AQUI DEVEMOS COLOCAR A LINHA DO TEMPO
                            </div>

                            <div class="row">

                                <!-- Seção Esquerda: Filtro e Lista -->
                                <div class="col-md-3 border-right" id="filtro-prontuario">
                                    <form class="mb-4">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="data_de">De:</label>
                                                <input type="date" class="form-control  form-control-sm" id="data_de"
                                                    name="data_de">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="data_ate">Até:</label>
                                                <input type="date" class="form-control  form-control-sm" id="data_ate"
                                                    name="data_ate">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="protocolo">Protocolo:</label>
                                                <input type="text" class="form-control  form-control-sm" id="protocolo"
                                                    name="protocolo" placeholder="Digite o Protocolo">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="user_id">Médico:</label>
                                                <select id="user_id" name="user_id"
                                                    class="form-control select2" data-placeholder="Selecione" data-allow-clear="true" style="width: 100%;">
                                                    <option></option>
                                                    @foreach ($users as $user)
                                                    <option value="{{$user->user_id}}">{{$user->user_name}} @if ($user->cargo) -
                                                        {{$user->cargo->user_func_desc}}
                                                        @endif </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6 align-self-end">
                                                <button type="button" id="btnPesquisarProtocolo"
                                                    class="btn btn-primary btn-sm" style=""><i class="fa fa-search"></i>
                                                    Pesquisar</button>
                                            </div>
                                        </div>
                                    </form>
                                    <table id="gridprotocolo" class="table table-striped table-bordered nowrap">
                                        <thead>
                                            <tr>
                                                <th>Protocolo</th>
                                                <th>Tipo</th>
                                                <th>Médico</th>
                                                <th>Anexo</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>

                                <!-- Seção Direita: Abas de Atendimento -->

                                <div class="card card-primary card-outline card-outline-tabs d-flex flex-column flex-grow-1" id="formProntuario"
                                    style="min-height: 0; margin-left: 1rem; height: 100%;">

                                    <!-- CABEÇALHO DAS ABAS DE PRONTUÁRIO-->
                                    <div class="card-header p-0 pt-1 border-bottom-0">

                                        <!-- ABAS -->
                                        <ul class="nav nav-tabs" id="custom-tabs-prt-tab" role="tablist">

                                            <li class="nav-item">
                                                <a class="nav-link" id="tabs-anamnese-tab" data-toggle="pill"
                                                    href="#tabs-anamnese" role="tab" aria-controls="tabs-anamnese"
                                                    aria-selected="true">Anamnese</a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link active" id="tabs-anotacao-tab" data-toggle="pill"
                                                    href="#tabs-anotacao" role="tab" aria-controls="tabs-anotacao"
                                                    aria-selected="true">Anotações</a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link" id="tabs-anotacao-priv-tab" data-toggle="pill"
                                                    href="#tabs-anotacao-priv" role="tab"
                                                    aria-controls="tabs-anotacao-priv" aria-selected="false">Anotações
                                                    Privadas</a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link" id="tabs-receituario-tab" data-toggle="pill"
                                                    href="#tabs-receituario" role="tab" aria-controls="tabs-receituario"
                                                    aria-selected="false">Receituário</a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link" id="tabs-exames-tab" data-toggle="pill"
                                                    href="#tabs-exames" role="tab" aria-controls="tabs-exames"
                                                    aria-selected="false">Solic. de Exames</a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link" id="tabs-atestado-tab" data-toggle="pill"
                                                    href="#tabs-atestado" role="tab" aria-controls="tabs-atestado"
                                                    aria-selected="false">Atestado</a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link" id="tabs-fotos-tab" data-toggle="pill"
                                                    href="#tabs-fotos" role="tab" aria-controls="tabs-fotos"
                                                    aria-selected="false">Fotos</a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link" id="tabs-documentos-tab" data-toggle="pill"
                                                    href="#tabs-documentos" role="tab" aria-controls="tabs-documentos"
                                                    aria-selected="false">Documentos</a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link" id="tabs-orcamento-tab" data-toggle="pill"
                                                    href="#tabs-orcamento" role="tab" aria-controls="tabs-orcamento"
                                                    aria-selected="false">Orçamentos</a>
                                            </li>

                                        </ul>
                                    </div>

                                    <!--CONTEÚDO DAS ABAS-->
                                    <div class="card-body">
                                        <div class="tab-content" id="custom-tabs-prt-tabContent">

                                            <!--CONTEÚDO DA ABA ANAMNESE-->
                                            <div class="tab-pane fade show" id="tabs-anamnese" role="tabpanel"
                                                aria-labelledby="tabs-anamnese-tab">

                                                <div class="card-body">
                                                    <textarea id="texto_anm" name="texto_anm"
                                                        class="form-control summernote"
                                                        rows="8"></textarea>
                                                </div>

                                            </div>

                                            <!--CONTEÚDO DA ABA ANOTAÇÕES-->
                                            <div class="tab-pane fade show active" id="tabs-anotacao" role="tabpanel"
                                                aria-labelledby="tabs-anotacao-tab">

                                                <div class="card-body">
                                                    <textarea id="texto_prt" name="texto_prt"
                                                        class="form-control summernote"
                                                        rows="8"></textarea>
                                                </div>

                                            </div>

                                            <!--CONTEÚDO DA ABA ANOTAÇÕES PRIVADAS-->
                                            <div class="tab-pane fade" id="tabs-anotacao-priv" role="tabpanel"
                                                aria-labelledby="tabs-anotacao-priv-tab">

                                                <div class="card-body">
                                                    <textarea id="texto_prv" name="texto_prv"
                                                        class="form-control summernote"
                                                        rows="8"></textarea>
                                                </div>

                                            </div>

                                            <!--CONTEÚDO DA ABA RECEITUÁRIO-->
                                            <div class="tab-pane fade" id="tabs-receituario" role="tabpanel"
                                                aria-labelledby="tabs-receituario-tab">

                                                <div class="container-fluid">
                                                    <!-- Linha 1: Logo + Dados Empresa/Médico -->
                                                    <div class="row align-items-center mb-3">
                                                        <div class="col-md-2 text-center">
                                                            <img src="{{ asset('storage/logos/empresa_' . $empresa->emp_id . '/logo.jpg') }}"
                                                                alt="Logo" style="max-width: 80px;">
                                                        </div>
                                                        <div class="col-md-10">
                                                            <div>
                                                                <span class="font-weight-bold"
                                                                    style="font-size: 1.1rem;">{{ $empresa->emp_nmult ??
                                                                    'Nome da Empresa' }}</span>
                                                                <span class="ml-3">CNPJ: {{ formatarCNPJ($empresa->emp_cnpj) ??
                                                                    '00.000.000/0000-00' }}</span>
                                                            </div>
                                                            <div>
                                                                <span>Médico: <span id="rec_medico_nome"></span></span>
                                                                <span class="ml-3">CRM: <span id="rec_crm_medico"></span></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Linha 2: Paciente -->
                                                    <div class="row mb-3">
                                                        <div class="col">
                                                            <label class="font-weight-bold">Paciente:</label>
                                                            <span>{{ $cliente->cliente_nome ?? 'Nome do Paciente' }}</span>
                                                            <span class="ml-3">CPF: {{ formatarCPF($cliente->cliente_doc) ??
                                                                '000.000.000-00' }}</span>
                                                        </div>
                                                    </div>
                                                    <!-- Linha 3: Produto + Posologia -->
                                                    <div class="form-row mb-3">
                                                        <div class="form-group col-md-4">
                                                            <label for="rec_produto_id">Medicamento:</label>
                                                            <select id="rec_produto_id" name="rec_produto_id"
                                                                class="form-control select2"
                                                                data-placeholder="Pesquise o Medicamento"
                                                                style="width: 100%;">
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="rec_detalhes_posologia">Detalhes da
                                                                Posologia:</label>
                                                            <input autocomplete="off" maxlength="255"
                                                                class="form-control form-control-sm"
                                                                placeholder="Digite os detalhes da posologia"
                                                                name="rec_detalhes_posologia" type="text"
                                                                id="rec_detalhes_posologia">
                                                        </div>
                                                        <div class="form-group col-md-3 d-flex align-items-end">
                                                            <button id="btnAdicionarMed" type="button"
                                                                class="btn btn-primary btn-sm w-100">
                                                                <i class="icon fas fa-plus-square"></i> Adicionar
                                                                Medicamento
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <!-- Linha 4: Campo texto tipo anotações -->
                                                    <div class="row mb-3">
                                                        <div class="col">
                                                            <textarea id="texto_rec" name="texto_rec"
                                                                class="form-control summernote"
                                                                placeholder="Adicione os medicamentos ou digite manualmente..."
                                                                rows="8"></textarea>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <!--CONTEÚDO DA ABA SOLICITAÇÃO DE EXAMES-->
                                            <div class="tab-pane fade" id="tabs-exames" role="tabpanel"
                                                aria-labelledby="tabs-exames-tab">

                                                <div class="container-fluid">
                                                    <!-- Linha 1: Logo + Dados Empresa/Médico -->
                                                    <div class="row align-items-center mb-3">
                                                        <div class="col-md-2 text-center">
                                                            <img src="{{ asset('storage/logos/empresa_'. $empresa->emp_id . '/logo.jpg') }}"
                                                                alt="Logo" style="max-width: 80px;">
                                                        </div>
                                                        <div class="col-md-10">
                                                            <div>
                                                                <span class="font-weight-bold"
                                                                    style="font-size: 1.1rem;">{{ $empresa->emp_nmult ??
                                                                    'Nome da Empresa' }}</span>
                                                                <span class="ml-3">CNPJ: {{ formatarCNPJ($empresa->emp_cnpj) ??
                                                                    '00.000.000/0000-00' }}</span>
                                                            </div>
                                                            <div>
                                                                <span>Médico: <span id="exa_medico_nome"></span></span>
                                                                <span class="ml-3">CRM: <span id="exa_crm_medico"></span></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Linha 2: Paciente -->
                                                    <div class="row mb-3">
                                                        <div class="col">
                                                            <label class="font-weight-bold">Paciente:</label>
                                                            <span>{{ $cliente->cliente_nome ?? 'Nome do Paciente' }}</span>
                                                            <span class="ml-3">CPF: {{ formatarCPF($cliente->cliente_doc) ?? '000.000.000-00' }}</span>
                                                        </div>
                                                    </div>
                                                    <!-- Linha 3: Produto + Posologia -->
                                                    <div class="form-row mb-3">
                                                        <div class="form-group col-md-4">
                                                            <label for="produto_id">Exame:</label>
                                                            <select id="produto_id" name="produto_id"
                                                                class="form-control select2"
                                                                data-placeholder="Pesquise o Medicamento"
                                                                style="width: 100%;">
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="detalhes_posologia">Detalhes do Exame:</label>
                                                            <input autocomplete="off" maxlength="255"
                                                                class="form-control form-control-sm"
                                                                placeholder="Digite os detalhes da posologia"
                                                                name="detalhes_posologia" type="text"
                                                                id="detalhes_posologia">
                                                        </div>
                                                        <div class="form-group col-md-3 d-flex align-items-end">
                                                            <button id="btnAdicionar" type="button"
                                                                class="btn btn-primary btn-sm w-100">
                                                                <i class="icon fas fa-plus-square"></i> Adicionar Exame
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <!-- Linha 4: Campo texto tipo anotações -->
                                                    <div class="row mb-3">
                                                        <div class="col">
                                                            <label for="texto_exm">Exames:</label>
                                                            <textarea id="texto_exm" class="form-control summernote"
                                                                placeholder="Adicione os exames ou digite manualmente..."
                                                                rows="8"></textarea>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <!--CONTEÚDO DA ABA ATESTADO-->
                                            <div class="tab-pane fade" id="tabs-atestado" role="tabpanel"
                                                aria-labelledby="tabs-atestado-tab">

                                                <div class="container-fluid">
                                                    <!-- Linha 1: Logo + Dados Empresa/Médico -->
                                                    <div class="row align-items-center mb-3">
                                                        <div class="col-md-2 text-center">
                                                            <img src="{{ asset('storage/logos/empresa_' . $empresa->emp_id . '/logo.jpg') }}"
                                                                alt="Logo" style="max-width: 80px;">
                                                        </div>
                                                        <div class="col-md-10">
                                                            <div>
                                                                <span class="font-weight-bold"
                                                                    style="font-size: 1.1rem;">{{ $empresa->emp_nmult ??
                                                                    'Nome da Empresa' }}</span>
                                                                <span class="ml-3">CNPJ: {{ formatarCNPJ($empresa->emp_cnpj) ??
                                                                    '00.000.000/0000-00' }}</span>
                                                            </div>
                                                            <div>
                                                                <span>Médico: <span id="atd_medico_nome"></span></span>
                                                                <span class="ml-3">CRM: <span id="atd_crm_medico"></span></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Linha 2: Paciente -->
                                                    <div class="row mb-3">
                                                        <div class="col">
                                                            <label class="font-weight-bold">Paciente:</label>

                                                            <span>{{ $cliente->cliente_nome ?? 'Nome do Paciente' }}</span>
                                                            <span class="ml-3">CPF: {{ formatarCPF($cliente->cliente_doc) ?? '000.000.000-00' }}</span>
                                                        </div>
                                                    </div>
                                                    <!-- Linha 3: Campo texto tipo anotações -->
                                                    <div class="row mb-3">
                                                        <div class="col">
                                                            <label for="texto_atd">Atestado:</label>
                                                            <textarea id="texto_atd" class="form-control summernote"
                                                                rows="8"></textarea>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <!--CONTEÚDO DA ABA FOTOS-->
                                            <div class="tab-pane fade" id="tabs-fotos" role="tabpanel"
                                                aria-labelledby="tabs-fotos-tab">
                                                <div class="row">
                                                    <div class="col-md-12" id="dropzone-fotos">
                                                        <div class="card card-default">
                                                            <div class="card-header">
                                                                <h3 class="card-title">Adicionar fotos <small><em>Clique para pesquisar</em> ou arraste e solte</small></h3>
                                                            </div>

                                                            <div class="card-body">
                                                                <div id="actions" class="row">
                                                                    <div class="col-lg-6">
                                                                        <div class="btn-group w-100">
                                                                            <span class="btn btn-success col fileinput-button-fotos">
                                                                                <i class="fas fa-plus"></i>
                                                                                <span>Adicionar fotos</span>
                                                                            </span>
                                                                            <!--button type="submit" class="btn btn-primary col start">
                                                                                <i class="fas fa-upload"></i>
                                                                                <span>Start upload</span-->
                                                                            </button>
                                                                            <button type="reset" class="btn btn-warning col cancel">
                                                                                <i class="fas fa-times-circle"></i>
                                                                                <span>Remover fotos</span>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6 d-flex align-items-center">
                                                                        <div class="fileupload-process w-100">
                                                                            <div id="total-progress" class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                                                                <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="table table-striped files" id="previews">
                                                                    <div id="template" class="row mt-2">
                                                                        <div class="col-auto">
                                                                            <span class="preview"><img src="data:," alt="" data-dz-thumbnail /></span>
                                                                        </div>
                                                                        <div class="col d-flex align-items-center">
                                                                            <p class="mb-0">
                                                                            <span class="lead" data-dz-name></span>
                                                                            (<span data-dz-size></span>)
                                                                            </p>
                                                                            <strong class="error text-danger" data-dz-errormessage></strong>
                                                                        </div>

                                                                        <div class="col-auto d-flex align-items-center">
                                                                            <div class="btn-group">
                                                                                <!--button class="btn btn-primary start">
                                                                                <i class="fas fa-upload"></i>
                                                                                <span>Start</span>
                                                                                </button>
                                                                                <button data-dz-remove class="btn btn-warning cancel">
                                                                                <i class="fas fa-times-circle"></i>
                                                                                <span>Cancelar</span>
                                                                                </button-->
                                                                                <button data-dz-remove class="btn btn-danger delete">
                                                                                <i class="fas fa-trash"></i>
                                                                                <span>Excluir</span>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Exemplo de exibição de fotos anexadas -->
                                                <div class="mt-4">
                                                    <label>Fotos Anexadas:</label>
                                                    <div id="listaFotosAnexadas">
                                                    </div>

                                                 </div>
                                            </div>

                                            <!--CONTEÚDO DA ABA DOCUMENTOS-->
                                            <div class="tab-pane fade" id="tabs-documentos" role="tabpanel"
                                                aria-labelledby="tabs-documentos-tab">

                                                <div class="row" id="dropzone-documentos">
                                                    <div class="col-md-12">
                                                        <div class="card card-default">
                                                            <div class="card-header">
                                                                <h3 class="card-title">Adicionar documentos <small><em>Clique para pesquisar</em> ou arraste e solte</small></h3>
                                                            </div>
                                                            <div class="card-body">
                                                                <div id="actions-documentos" class="row">
                                                                    <div class="col-lg-6">
                                                                        <div class="btn-group w-100">
                                                                            <span class="btn btn-success col fileinput-button-documentos">
                                                                                <i class="fas fa-plus"></i>
                                                                                <span>Adicionar Documentos</span>
                                                                            </span>
                                                                            <!--button type="submit" class="btn btn-primary col start">
                                                                                <i class="fas fa-upload"></i>
                                                                                <span>Start upload</span-->
                                                                            </button>
                                                                            <button type="reset" class="btn btn-warning col cancel">
                                                                                <i class="fas fa-times-circle"></i>
                                                                                <span>Remover documentos</span>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-6 d-flex align-items-center">
                                                                        <div class="fileupload-process w-100">
                                                                            <div id="total-progress-documentos" class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                                                                <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="table table-striped files" id="previews-documentos">
                                                                    <div id="template-documentos" class="row mt-2">
                                                                        <div class="col-auto">
                                                                            <span class="preview"><img src="data:," alt="" data-dz-thumbnail /></span>
                                                                        </div>
                                                                        <div class="col d-flex align-items-center">
                                                                            <p class="mb-0">
                                                                            <span class="lead" data-dz-name></span>
                                                                            (<span data-dz-size></span>)
                                                                            </p>
                                                                            <strong class="error text-danger" data-dz-errormessage></strong>
                                                                        </div>
                                                                        <div class="col-auto d-flex align-items-center">
                                                                            <div class="btn-group">
                                                                                <!--button class="btn btn-primary start">
                                                                                <i class="fas fa-upload"></i>
                                                                                <span>Start</span>
                                                                                </button>
                                                                                <button data-dz-remove class="btn btn-warning cancel">
                                                                                <i class="fas fa-times-circle"></i>
                                                                                <span>Cancelar</span>
                                                                                </button-->
                                                                                <button data-dz-remove class="btn btn-danger delete-documentos">
                                                                                <i class="fas fa-trash"></i>
                                                                                <span>Excluir</span>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                    <h6>Documentos Anexados:</h6>
                                                    <div id="listaDocsAnexados"  class="mt-4">
                                                    </div>

                                            </div>

                                            <!--CONTEÚDO DA ABA DOCUMENTOS-->
                                            <div class="tab-pane fade" id="tabs-orcamento" role="tabpanel"
                                                aria-labelledby="tabs-orcamento-tab">

                                                ACRESCENTAR AQUI A LISTA DOS ORÇAMENTOS CRIADOS.<br>
                                                AQUI, PRECISAMOS CRIAR UM MODAL ONDE O ATENDENTE POSSA SELECIONAR O
                                                PRODUTOS, DIGITAR VALOR E QUANTIDADE,<br>
                                                E CRIAR UMA LISTA DE VENDA QUE, POSTERIORMENTE, SERÁ UTILIZADA PELO PDV
                                                WEB PARA FECHAR A VENDA OFICIAL<br>
                                                <br>
                                                NESTA PARTE, TEREMOS UMA LINHA PARA CADA ORÇAMENTO CRIADO, ESTES
                                                ORÇAMENTOS SERÃO GRAVADOS NA TABELA<br>
                                                TBTR_CLIENTES_ORC
                                                <br>
                                                NESTA RELA, EM CADA REGISTRO, DEVE TER UM BOTÃO, "FINALIZAR VENDA", AO
                                                CLICAR NESTE BOTÃO, DEVE DIRECIONAR<br>
                                                PARA O PDV WEB JÁ COM OS DADOS DO CLIENTE PREENCHIDO, E OS ITENS
                                                INCLUÍDOS NO CARRINHO DE COMPRA

                                            </div>

                                        </div>
                                    </div>

                                    <div class="card-footer">
                                        <button id="btnSalvarPrt" type="button" class="btn btn-primary btn-sm">
                                            <i class="icon fas fa-save"></i> Salvar
                                        </button>
                                        <button id="btnImprimir" type="button" class="btn btn-primary btn-sm d-none"
                                            onclick="printCorpoReceita()">
                                            <i class="fas fa-print"></i> Imprimir
                                        </button>
                                    </div>

                                </div>

                            </div>

                            A TABELA A SER UTILIZADA É A TBDM_CLIENTES_PRT</br>
                            SOMENTE USUÁRIO DO TIPO MÉDICO TERÃO ACESSO A ESTA ABA</br>
                            TUDO O QUE FOR DIGITADO NO CAMPO TEXTO DEVERÁ SER GRAVADO EM UMA TABELA DO BANCO DE
                            DADOS</br>
                            AS IMAGENS CARREGADAS TAMBÉM PRECISAM SER GRAVADAS NO BANCO DE DADOS</br>
                            DO LADO ESQUERDO, TEMOS O HISTÓRICO DE TUDO O QUE FOI GRAVADO, SE CLICAR EM ALGUM HISTÓRICO,
                            DEVEMOS CARREGAR O TEXTO E AS IMAGENS</br>
                            OS FILTROS DE DATA DE/ATÉ DEVEM UTILIZAR O CAMPO DTHR_CR DA TABELA TBDM_CLIENTES_REC</br>
                            ATENTAR PARA A NAVEGAÇÃO, POIS AO CLICAR EM RECEITUÁRIO, DEVEMOS LEVAR O PROTOCOLO QUE
                            ESTIVER SELECIONADO

                        </div>

                        <!--ABA ATENDIMENTO-->
                        <div class="tab-pane fade" id="tabs-atendimento" role="tabpanel"
                            aria-labelledby="tabs-atendimento-tab">

                            EXEMPLO</br>
                            AQUI PRECISAMOS EMULAR A TELA DO WHATS APP PARA QUE O USUÁRIO POSSA ENTRAR EM CONTATO COM O
                            CLIENTE</br>

                            <div class="card"
                                style="max-width: 500px; margin: 0 auto; height: 600px; display: flex; flex-direction: column;">
                                <!-- Cabeçalho do chat -->
                                <div class="card-header bg-success text-white d-flex align-items-center">
                                    <img src="{{ asset('assets/dist/img/user-avatar.png') }}" alt="Avatar"
                                        class="rounded-circle mr-2" style="width: 40px; height: 40px;">
                                    <span>Atendimento WhatsApp</span>
                                </div>
                                <!-- Corpo do chat -->
                                <div id="chat-body" class="card-body"
                                    style="flex: 1 1 auto; overflow-y: auto; background: #e5ddd5;">
                                    <!-- Mensagens exemplo -->
                                    <div class="d-flex flex-column">
                                        <div class="align-self-start bg-white rounded p-2 mb-2" style="max-width: 70%;">
                                            <small class="text-muted">Cliente</small><br>
                                            Olá, gostaria de informações sobre meu atendimento.
                                        </div>
                                        <div class="align-self-end bg-success text-white rounded p-2 mb-2"
                                            style="max-width: 70%;">
                                            <small class="text-white">Atendente</small><br>
                                            Olá! Como posso ajudar?
                                        </div>
                                    </div>
                                </div>
                                <!-- Campo de digitação -->
                                <div class="card-footer bg-light">
                                    <form id="form-chat" class="d-flex">
                                        <input type="text" id="chat-input" class="form-control mr-2"
                                            placeholder="Digite sua mensagem..." autocomplete="off">
                                        <button type="button" class="btn btn-success"><i class="fab fa-whatsapp"></i>
                                            Enviar</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </form>
</section>




<div class="modal fade" id="pesquisa-cliente-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Informação</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Digite o CNPJ ou o nome do cliente.</p>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <select id="clicod" name="clicod" autofocus="autofocus"
                            class="form-control select2 select2-hidden-accessible"
                            data-placeholder="Digite o CNPJ ou o nome do cliente" style="width: 100%;"
                            aria-hidden="true">
                            <option></option>

                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success pull-right" id="btnSearchClient" data-dismiss="modal"><i
                        class="fa fa-check"></i> OK</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal cnpj-->

<div class="modal fade" id="cnpj-info-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Informação</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Digite o CNPJ e clique no botão <button type="button" class="btn btn-default" disabled><i
                            class="fa fa-search"></i></button>, para preencher as informações da empresa
                    automaticamente&hellip;</p>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal cnpj-->

<!-- Modal para Criar Novo Cartão -->
<div class="modal fade" id="modalCriarCartao" role="dialog" aria-labelledby="modalCriarCartaoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCriarCartaoLabel">Criar Novo Cartão
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="formCriarCartao">
                @method('POST')
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="emp_id">Empresa:</label>
                        <select id="emp_id" name="emp_id" class="form-control select2" style="width: 100%;" disabled>
                            <option selected value="{{$empresa->emp_id}}">{{$empresa->emp_nmult}}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="card_tp">Tipo do Cartão:</label>
                        <select class="form-control select2" style="width: 100%;" data-placeholder="Selecione o Tipo"
                            id="card_tp" name="card_tp">
                            <option></option>
                            @foreach($cardTipos as $key => $card)
                            <option value="{{$card->card_tp}}">{{$card->card_tp_desc}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="card_mod">Modalidade do Cartão:</label>
                        <select class="form-control select2" style="width: 100%;"
                            data-placeholder="Selecione a Modalidade" id="card_mod" name="card_mod">
                            <option></option>
                            @foreach($cardMod as $key => $card)
                            <option value="{{$card->card_mod}}">{{$card->card_mod_desc}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="card_categ">Categoria:</label>
                        <select class="form-control select2" style="width: 100%;"
                            data-placeholder="Selecione a Categoria" id="card_categ" name="card_categ">
                            <option></option>
                            @foreach($cardCateg as $key => $card)
                            <option value="{{$card->card_categ}}">{{$card->card_categ_desc}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="card_desc">Descrição do Cartão:</label>
                        <input type="text" class="form-control  form-control-sm" id="card_desc" name="card_desc"
                            placeholder="Descrição do Cartão">
                    </div>
                    <div class="form-group">
                        <label for="card_limite">Limite do Cartão:</label>
                        <input type="text" class="form-control money form-control-sm" id="card_limite"
                            name="card_limite" placeholder="Limite do Cartão">
                    </div>
                </div>

            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-secundary-multban btn-sm" data-dismiss="modal">
                    <i class="fas fa-times"></i> Fechar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSalvarCartao"><i class="fas fa-save"></i>
                    Salvar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cep-info-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Informação</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Digite o CEP e clique no botão <button type="button" class="btn btn-default" disabled><i
                            class="fa fa-search"></i></button>, para preencher o Endereço automaticamente&hellip;</p>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal cep-->

<!-- Modal de Alteração em Massa -->
<div class="modal fade" id="modalAlteracaoMassa" tabindex="-1" role="dialog" aria-labelledby="modalAlteracaoMassaLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAlteracaoMassaLabel">
                    <i class="fas fa-sync-alt"></i> Alteração em Massa
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formAlteracaoMassa">
                    <div class="form-group">
                        <label for="nova_data_venc">Data de Vencimento:</label>
                        <input type="date" id="nova_data_venc" name="nova_data_venc"
                            class="form-control form-control-sm" placeholder="Selecione a nova data de vencimento">
                        <small class="form-text text-muted">Deixe em branco para não alterar a data de
                            vencimento</small>
                    </div>

                    <div class="form-group" id="tipoDataVencimentoGroup" style="display: none;">
                        <label>Tipo de Aplicação da Data de Vencimento:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_data_vencimento" id="data_mesma"
                                value="mesma_data" checked>
                            <label class="form-check-label" for="data_mesma">
                                Utilizar a mesma data de vencimento para todos os títulos
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_data_vencimento" id="data_base"
                                value="data_base">
                            <label class="form-check-label" for="data_base">
                                Utilizar a data selecionada como base para a primeira parcela e calcular as demais
                            </label>
                        </div>
                        <small class="form-text text-muted">Selecione como a data de vencimento será aplicada nos
                            títulos selecionados</small>
                    </div>

                    <div class="form-group">
                        <label for="vlr_desc">Desconto:</label>
                        <input type="text" id="vlr_desc" name="vlr_desc" class="form-control form-control-sm money"
                            placeholder="R$ 0,00">
                        <small class="form-text text-muted">Deixe em branco para não aplicar desconto</small>
                    </div>

                    <div class="form-group" id="tipoDescontoGroup" style="display: none;">
                        <label>Tipo de Aplicação do Desconto:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_desconto" id="desconto_mesmo_valor"
                                value="mesmo_valor" checked>
                            <label class="form-check-label" for="desconto_mesmo_valor">
                                Aplicar o mesmo valor de desconto em todos os títulos
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_desconto" id="desconto_dividir"
                                value="dividir">
                            <label class="form-check-label" for="desconto_dividir">
                                Dividir o valor igualmente para todos os títulos
                            </label>
                        </div>
                        <small class="form-text text-muted">Selecione como o desconto será aplicado nos títulos
                            selecionados</small>
                    </div>

                    <div class="form-group">
                        <label for="negociacao_obs">Detalhes da Negociação:</label>
                        <textarea id="negociacao_obs" name="negociacao_obs" class="form-control form-control-sm"
                            rows="8" placeholder="Descreva os detalhes das negociações e atendimento..."></textarea>
                        <small class="form-text text-muted">Informe todos os detalhes sobre as negociações
                            realizadas</small>
                    </div>
                    <div class="alert alert-info"
                        style="background-color: #ecba41; border-color: #ecba41; color: #000;">
                        <i class="fas fa-info-circle"></i>
                        <strong>Atenção:</strong> As alterações serão aplicadas apenas aos títulos selecionados na
                        tabela.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm" data-dismiss="modal"
                    style="background-color: #a702d8; color: white; border-color: #a702d8;">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnExecutarMudancas">
                    <i class="fas fa-sync-alt"></i> Executar Mudanças
                </button>
            </div>
        </div>
    </div>
</div>

<!-- /.content -->
@endsection

@push('scripts')



<!-- Ekko Lightbox -->
<script src="{{ asset('assets/plugins/ekko-lightbox/ekko-lightbox.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jquery-validation/localization/messages_pt_BR.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jquery-validation/additional-methods.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/i18n/pt-BR.js') }}"></script>

<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>

<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-select/js/dataTables.select.min.js')}}"></script>
<script src="{{ asset('assets/plugins/datatables-fixedheader/js/dataTables.fixedHeader.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>

<!-- dropzonejs -->
<script src="{{ asset('assets/plugins/dropzone/min/dropzone.min.js') }}"></script>
<!-- Moment -->
<script src="{{ asset('assets/plugins/moment/moment-with-locales.js') }}"></script>
<script src="{{ asset('assets/plugins/inputmask/min/jquery.inputmask.bundle.min.js') }}"></script>

<!-- Summernote -->
<script src="{{ asset('assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
<link rel="stylesheet" href="{{asset('assets/dist/css/app.css') }}" />
<script src="{{asset('assets/dist/js/app.js') }}"></script>
<script src="{{asset('assets/dist/js/pages/cliente/cliente.js') }}"></script>

<script type="text/javascript">
 $(function () {
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
      event.preventDefault();
      $(this).ekkoLightbox({
        alwaysShowClose: true
      });
    });
  })

    $(document).ready(function () {

        @if ( request()->has('pront') )
            $('#btnCancelar').attr('onclick', 'location.href="{{ route('agendamento.edit', request('pront')) }}"');

        @endif

         // Verifica o status do usuário e ajusta o texto do botão de ativação/inativação
        if ("{{$cliente->cliente_sts}}" == "EX" ) {
            $("#btnInativar").text("Ativar");
            $("#btnInativar").prepend('<i class="fa fa-check"></i> ');
            $("#btnExcluir").prop('disabled', true);


        } else if ("{{$cliente->cliente_sts}}" == "AT") {
            $("#btnInativar").text("Inativar");
            $("#btnInativar").prepend('<i class="fa fa-ban"></i> ');
            $("#btnExcluir").prop('disabled', false);}
         else {
            $("#btnInativar").text("Inativar");
            $("#btnInativar").prepend('<i class="fa fa-ban"></i> ');
        }
        // Exibição de mensagens de alerta
        $(".alert-dismissible")
            .fadeTo(10000, 500)
            .slideUp(500, function() {
                $(".alert-dismissible").alert("close");
            });

        // Inicialização do Summernote - Editor de Texto
        $('.summernote').summernote({
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear', 'fontsize', 'fontname', 'color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'video', 'table']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });

        // Botão Receituário
        $('#btnReceituario').on('click', function() {
            // Copia o valor do protocolo atual para o campo da aba Receituário
            $('#protocolo_receita').val($('#protocolo_atual').val());
            // Mostra a aba Receituário
            $('#tabs-receituario-tab').tab('show');
        });

        // Quando clicar em uma linha de protocolo, atualiza o campo do protocolo atual
        $('.linha-protocolo').on('click', function() {
            var protocolo = $(this).data('protocolo');
            $('#protocolo_atual').val(protocolo);
        });

        // Quando clicar em uma linha de protocolo, atualiza o campo do protocolo receita
        $('.linha-protocolo-rec').on('click', function() {
            var protocolo = $(this).data('protocolo');
            $('#protocolo_receita').val(protocolo);
        });

        function toggleBtnImprimir() {
            var activeTab = $('#custom-tabs-prt-tab .nav-link.active').attr('id');
            if (
                activeTab === 'tabs-receituario-tab' ||
                activeTab === 'tabs-exames-tab' ||
                activeTab === 'tabs-atestado-tab'
            ) {
                $('#btnImprimir').removeClass('d-none');
            } else {
                $('#btnImprimir').addClass('d-none');
            }
        }

        // Chama ao carregar
        toggleBtnImprimir();

        // Chama ao trocar de aba
        $('#custom-tabs-prt-tab .nav-link').on('shown.bs.tab', function () {
            toggleBtnImprimir();
        });

        // Funcionalidade do checkbox "Selecionar Todos"
        $(document).on('click', '#selectAll', function() {
            var isChecked = $(this).is(':checked');
            $('#gridtemplate tbody input[type="checkbox"]').prop('checked', isChecked);
        });

        // Se algum checkbox individual for desmarcado, desmarcar o "Selecionar Todos"
        $(document).on('change', '#gridtemplate tbody input[type="checkbox"]', function() {
            var totalCheckboxes = $('#gridtemplate tbody input[type="checkbox"]').length;
            var checkedCheckboxes = $('#gridtemplate tbody input[type="checkbox"]:checked').length;

            if (totalCheckboxes > 0) {
                if (checkedCheckboxes === totalCheckboxes) {
                    $('#selectAll').prop('checked', true);
                } else {
                    $('#selectAll').prop('checked', false);
                }
            }
        });

        // Botão Imprimir Todos
        $('#btnImprimirTudo').on('click', function() {
            var checkedCheckboxes = $('#gridtemplate tbody input[type="checkbox"]:checked').length;

            if (checkedCheckboxes === 0) {
                Swal.fire({
                    title: 'Atenção!',
                    text: 'Selecione pelo menos um título para realizar a impressão.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Aqui você implementaria a lógica de impressão
            console.log(`Imprimindo ${checkedCheckboxes} título(s) selecionado(s)`);

            // Exemplo de confirmação
            Swal.fire({
                title: 'Imprimir Títulos',
                text: `Deseja imprimir ${checkedCheckboxes} título(s) selecionado(s)?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, imprimir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // TODO: Implementar lógica de impressão
                    Swal.fire({
                        title: 'Sucesso!',
                        text: `${checkedCheckboxes} título(s) enviado(s) para impressão.`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        // Botão Enviar Link de Pagamento da Compra
        $('#btnEnviarLinkCompra').on('click', function() {
            var checkedCheckboxes = $('#gridtemplate tbody input[type="checkbox"]:checked').length;

            if (checkedCheckboxes === 0) {
                Swal.fire({
                    title: 'Atenção!',
                    text: 'Selecione pelo menos um título para enviar o link de pagamento da compra.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Confirmação antes de enviar
            Swal.fire({
                title: 'Enviar Link de Pagamento da Compra',
                text: `Deseja enviar o link de pagamento da compra para ${checkedCheckboxes} título(s) selecionado(s)?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // TODO: Implementar lógica de envio do link da compra
                    console.log(`Enviando link de pagamento da compra para ${checkedCheckboxes} título(s)`);

                    Swal.fire({
                        title: 'Sucesso!',
                        text: `Link de pagamento da compra enviado para ${checkedCheckboxes} título(s).`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        // Botão Enviar Link de Pagamento da Fatura
        $('#btnEnviarLinkFatura').on('click', function() {
            var checkedCheckboxes = $('#gridtemplate tbody input[type="checkbox"]:checked').length;

            if (checkedCheckboxes === 0) {
                Swal.fire({
                    title: 'Atenção!',
                    text: 'Selecione pelo menos um título para enviar o link de pagamento da fatura.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Confirmação antes de enviar
            Swal.fire({
                title: 'Enviar Link de Pagamento da Fatura',
                text: `Deseja enviar o link de pagamento da fatura para ${checkedCheckboxes} título(s) selecionado(s)?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // TODO: Implementar lógica de envio do link da fatura
                    console.log(`Enviando link de pagamento da fatura para ${checkedCheckboxes} título(s)`);

                    Swal.fire({
                        title: 'Sucesso!',
                        text: `Link de pagamento da fatura enviado para ${checkedCheckboxes} título(s).`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        // Botão de Alteração em Massa
        $('#btnAlterarTudo').on('click', function() {
            var checkedCheckboxes = $('#gridtemplate tbody input[type="checkbox"]:checked').length;

            if (checkedCheckboxes === 0) {
                Swal.fire({
                    title: 'Atenção!',
                    text: 'Selecione pelo menos um título para realizar a alteração em massa.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Limpa os campos do modal
            $('#nova_data_venc').val('');
            $('#desconto').val('');
            $('#tipoDataVencimentoGroup').hide();

            // Abre o modal
            $('#modalAlteracaoMassa').modal('show');
        });

        // Controlar visibilidade dos radio buttons de tipo de desconto
        $('#vlr_desc').on('input keyup', function() {
            var valor = $(this).val().trim();
            if (valor && valor !== '' && valor !== 'R$ 0,00') {
                $('#tipoDescontoGroup').slideDown(300);
            } else {
                $('#tipoDescontoGroup').slideUp(300);
            }
        });

        // Controlar visibilidade dos radio buttons de tipo de data de vencimento
        $('#nova_data_venc').on('input change', function() {
            var data = $(this).val().trim();
            if (data && data !== '') {
                $('#tipoDataVencimentoGroup').slideDown(300);
            } else {
                $('#tipoDataVencimentoGroup').slideUp(300);
            }
        });

        // Botão Executar Mudanças
        $('#btnExecutarMudancas').on('click', function() {
            var novaDataVencimento = $('#nova_data_venc').val();
            var desconto = $('#vlr_desc').val();
            var tipoDataVencimento = $('input[name="tipo_data_vencimento"]:checked').val();
            var checkedTitulos = [];

            // Coleta os títulos selecionados
            $('#gridtemplate tbody input[type="checkbox"]:checked').each(function() {
                var row = $(this).closest('tr');
                var tituloId = row.find('td:eq(3)').text(); // Título está na 4ª coluna
                checkedTitulos.push(tituloId);
            });

            // Validação
            if (!novaDataVencimento && !desconto) {
                Swal.fire({
                    title: 'Atenção!',
                    text: 'Preencha pelo menos um campo para realizar a alteração.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Validação de data futura
            if (novaDataVencimento) {
                var hoje = new Date().toISOString().split('T')[0];
                if (novaDataVencimento <= hoje) {
                    Swal.fire({
                        title: 'Data Inválida!',
                        text: 'A data de vencimento deve ser uma data futura.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
            }

            // Confirmação antes de executar
            Swal.fire({
                title: 'Confirmar Alteração',
                text: `Deseja realmente alterar ${checkedTitulos.length} título(s) selecionado(s)?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, alterar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aqui você faria a chamada AJAX para o backend
                    // Exemplo de estrutura de dados para enviar:
                    var dadosAlteracao = {
                        titulos: checkedTitulos,
                        nova_data_vencimento: novaDataVencimento,
                        tipo_data_vencimento: tipoDataVencimento,
                        desconto: desconto,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    };

                    // TODO: Implementar chamada AJAX
                    console.log('Dados para alteração:', dadosAlteracao);

                    // Simula sucesso - remover quando implementar AJAX real
                    $('#modalAlteracaoMassa').modal('hide');
                    Swal.fire({
                        title: 'Sucesso!',
                        text: `${checkedTitulos.length} título(s) alterado(s) com sucesso.`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });

                    // Desmarcar todos os checkboxes
                    $('#gridtemplate tbody input[type="checkbox"]').prop('checked', false);
                    $('#selectAll').prop('checked', false);
                }
            });
        });

        // Formatação de moeda para o campo desconto
        $('#desconto').mask('#.##0,00', {
            reverse: true,
            translation: {
                '#': {pattern: /[0-9]/}
            }
        });

    });

    // Impressão do conteúdo do editor de receita
    function printCorpoReceita() {
        var conteudo = $('#corpo_receita').summernote('code');
        var janela = window.open('', '', 'height=600,width=800');
        janela.document.write('<html><head><title>Receita</title>');
        janela.document.write('<link rel="stylesheet" href="{{ asset('assets/plugins/summernote/summernote-bs4.min.css') }}">');
        janela.document.write('<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">');
        janela.document.write('</head><body>');
        janela.document.write(conteudo);
        janela.document.write('</body></html>');
        janela.document.close();
        janela.focus();

        // Aguarda o carregamento das imagens antes de imprimir
        janela.onload = function() {
            setTimeout(function() {
                janela.print();
                janela.close();
            }, 500); // 0.5 segundo de delay para garantir o carregamento
        };
    }

    $(function () {
        "use strict";

        @if(!empty($clienteProntuarios))
            var dataSet = {{Js::from($clienteProntuarios)}};
            console.log(dataSet.original.data);
            clientejs.loadDatatablePrt(dataSet.original.data);
        @endif
    });

</script>

@endpush
