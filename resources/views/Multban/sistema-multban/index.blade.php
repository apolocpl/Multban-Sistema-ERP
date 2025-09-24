@extends('layouts.app-master')
@section('page.title', 'Sistema multban')
@push('script-head')
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-select/css/select.bootstrap4.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-fixedheader/css/fixedHeader.bootstrap4.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}" />
<!-- Bootstrap Color Picker -->
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css') }}">
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    @if (session()->get('success'))
    <div class="col-sm-12">
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Sucesso!</h5>
            {{ session()->get('success') }}
        </div>
    </div>
    @endif

    @if (session()->get('warning'))
    <div class="col-sm-12">
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Alerta!</h5>
            {{ session()->get('warning') }}
        </div>
    </div>
    @endif

    <input type="hidden" id="is_edit" value="1" />
    <input type="hidden" id="t_name" value="" />
    <input type="hidden" id="v_id" value="" />
    <!-- QUADRO DO FORMULÁRIO DE PESQUISA -->
    <div class="card card-outline card-primary">

        <div class="card-body">

            <!-- PRIMEIRA LINHA DO FORMULÁRIO DE PESQUISA -->
            <div class="form-row">

                <!-- FILTRO DO NOME DA EMPRESA -->
                <div class="form-group col-md-3">
                    <label for="Empresa">Empresa:*</label>
                    <input type="hidden" value="" id="emp_id" />
                    <select id="empresa_id" name="empresa_id" class="form-control select2 select2-hidden-accessible"
                        data-placeholder="Pesquise a Empresa" style="width: 100%;" aria-hidden="true">
                    </select>
                    <!--br>
                        ESTE FILTRO DE EMPRESA DEVE SER REALIZADO SOBRE O NOME MULTBAN DA EMPRESA<br>
                        CAMPO EMP_NMULT DA TABELA TBDM_EMPRESA_GERAL<br>
                        <br>
                        AO PREENCHER O CAMPO PARA PESQUISAR AS EMPRESAS NA BASE DE DADOS, A TAG TAMBÉM DEVER SER UMA OPÇÃO DE SELEÇÃO<br>
                        E OS RESULTADOS QUE APARECEM NAS OPÇÕES, DEVEM RESPEITAR A TAG DIGITADA<br-->
                </div>

            </div>

        </div>

    </div>

    <!-- QUADRO DOS RESULTADOS DE PESQUISA -->
    <div class="card card-primary card-outline card-outline-tabs">

        <!-- CABEÇALHO DA ABA -->
        <div class="card-header p-0 pt-1 border-bottom-0">

            <!-- ABAS -->
            <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tabs-conexao-tab" data-toggle="pill" href="#tabs-conexao" role="tab"
                        aria-controls="tabs-conexao" aria-selected="true">Conexão BD</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="tabs-alias-tab" data-toggle="pill" href="#tabs-alias" role="tab"
                        aria-controls="tabs-alias" aria-selected="false">Alias de Tabelas</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="tabs-apis-tab" data-toggle="pill" href="#tabs-apis" role="tab"
                        aria-controls="tabs-apis" aria-selected="false">APIs</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="tabs-padroes-planos-tab" data-toggle="pill" href="#tabs-padroes-planos"
                        role="tab" aria-controls="tabs-padroes-planos" aria-selected="false">Padrões dos Planos</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="tabs-white-label-tab" data-toggle="pill" href="#tabs-white-label" role="tab"
                        aria-controls="tabs-white-label" aria-selected="false">White Label</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="tabs-work-flow-tab" data-toggle="pill" href="#tabs-work-flow" role="tab"
                        aria-controls="tabs-work-flow" aria-selected="false">Work Flow</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="tabs-padrao-msg-tab" data-toggle="pill" href="#tabs-padrao-msg" role="tab"
                        aria-controls="tabs-padrao-msg" aria-selected="false">Padrão de Mensagens</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" id="tabs-dados-mestres-tab" data-toggle="pill" href="#tabs-dados-mestres"
                        role="tab" aria-controls="tabs-dados-mestres" aria-selected="false">Dados Mestres</a>
                </li>

            </ul>
        </div>

        <!-- CORPO DAS ABAS -->
        <div class="card-body">
            <div class="tab-content" id="custom-tabs-two-tabContent">

                <!---------------------------->
                <!---- ABA CONEXÃO COM BD ---->
                <!---------------------------->
                <div class="tab-pane fade active show" id="tabs-conexao" role="tabpanel"
                    aria-labelledby="tabs-conexao-tab">

                    <!-- PRIMEIRA LINHA DO FORMULÁRIO DE PESQUISA -->
                    <div class="form-row">

                        <!-- FILTRO DO FORNECEDOR -->
                        <div class="form-group col-md-3">
                            <label for="fornec_bd">Fornecedor:</label>
                            <select class="form-control select2" name="fornec_bd" id="fornec_bd"
                                data-placeholder="Selecione o Fornecedor" style="width: 100%;" data-allow-clear="true">
                                <option value=""></option>
                                @foreach($tbdmFornecedor as $key => $fornec)

                                <option value="{{$fornec->fornec}}">{{$fornec->fornec_desc}}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- BOTÃO PESQUISAR -->
                        <div class="form-group col-md-3 mt-4">
                            <button type="button" id="btnPesquisarFbd" class="btn btn-primary mt-2" style=""><i
                                    class="fa fa-search"></i> Pesquisar</button>
                        </div>

                    </div>

                    <div class="form-row">
                        <!-- BOTÃO PARA CRIAR CONEXÃO DB -->
                        <div class="form-group col-md-3 mt-4">
                            <button type="button" id="btnCriarConexaoDB" data-modal="modalConexaoDB" data-toggle="modal"
                                data-target="#modalConexaoDB" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Criar novo</button>
                        </div>
                    </div>


                    <div class="table-responsive">
                        <table id="gridconexao" class="table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th>Ações</th>
                                    <th>Forcecedor</th>
                                    <th>Empresa</th>
                                    <th>Empresa Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <!---------------------------->
                <!--- ABA ALIAS DE TABELAS --->
                <!---------------------------->
                <div class="tab-pane fade" id="tabs-alias" role="tabpanel" aria-labelledby="tabs-alias-tab">

                    <div class="form-row d-flex align-items-end mb-3">
                        <!-- BOTÃO PESQUISAR -->
                        <div class="mr-2">
                            <button type="button" id="btnPesquisarAlias" class="btn btn-primary btn-sm" style=""><i
                                    class="fa fa-search"></i> Carregar Dados</button>
                        </div>

                        <!-- BOTÃO PARA CRIAR ALIAS -->
                        <div>

                            <button type="button" id="btnCriarAlias" data-modal="modalAlias" class="btn btn-primary btn-sm"
                                data-toggle="modal" data-target="#modalAlias">
                                <i class="fa fa-plus"></i> Criar novo</button>
                        </div>
                    </div>

                    <!-- CORPO DO QUADRO DO GRID DE AlIAS -->
                    <div class="table-responsive">
                        <table id="gridtemplate-alias" class="table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th>Ações</th>
                                    <th>Tabela de Sistema</th>
                                    <th>Alias</th>
                                </tr>
                            </thead>

                        </table>
                    </div>

                </div>

                <!---------------------------->
                <!--------- ABA APIS --------->
                <!---------------------------->
                <div class="tab-pane fade" id="tabs-apis" role="tabpanel" aria-labelledby="tabs-apis-tab">

                    <!-- PRIMEIRA LINHA DO FORMULÁRIO DE PESQUISA -->
                    <div class="form-row">

                        <!-- FILTRO DO FORNECEDOR -->
                        <div class="form-group col-md-3">
                            <label for="bc_fornec_apis">Fornecedor:</label>
                            <select id="bc_fornec_apis" name="bc_fornec_apis"
                                class="form-control select2 select2-hidden-accessible"
                                data-placeholder="Selecione o Fornecedor" style="width: 100%;" aria-hidden="true"
                                data-allow-clear="true">
                                <option></option>
                                @foreach($tbdmFornecedor as $key => $fornec)
                                <option value="{{$fornec->fornec}}">{{$fornec->fornec_desc}}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- FILTRO DO GRUPO DE API -->
                        <div class="form-group col-md-3">
                            <label for="api_grupo">Grupo de APIs:</label>
                            <select id="api_grupo" name="api_grupo" class="form-control select2"
                                data-placeholder="Selecione o Grupo de APIs" style="width: 100%;" aria-hidden="true"
                                data-allow-clear="true">
                                <option></option>
                                @foreach($tbdmApiGrupo as $key => $grupo)
                                <option value="{{$grupo->api_grupo}}">{{$grupo->api_grupo_desc}}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="form-row">
                        <!-- FILTRO DO SUB GRUPO DE API -->
                        <div class="form-group col-md-3">
                            <label for="api_subgrp">Sub Grupo de APIs:</label>
                            <select id="api_subgrp" name="api_subgrp" class="form-control select2"
                                data-placeholder="Pesquise o Sub Grupo de APIs" style="width: 100%;" aria-hidden="true"
                                data-allow-clear="true">
                                <option></option>
                                @foreach($tbdmApiSubGrupo as $key => $subGrupo)
                                <option value="{{$subGrupo->api_subgrp}}">{{$subGrupo->api_subgrp_desc}}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- BOTÃO PESQUISAR -->
                        <div class="form-group col-md-3 mt-4">
                            <button type="button" id="btnPesquisarFapi" class="btn btn-primary mt-2" style=""><i
                                    class="fa fa-search"></i> Pesquisar</button>
                        </div>

                    </div>

                    <div class="form-row">
                        <!-- BOTÃO PARA CRIAR CONEXÃO API -->
                        <div class="form-group col-md-3 mt-4">
                            <button type="button" id="btnCriarConexaoAPI" data-modal="modalCriarConexaoAPI"
                                class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalCriarConexaoAPI">
                                <i class="fa fa-plus"></i> Criar novo</button>
                        </div>
                    </div>

                    <!-- CORPO DO QUADRO DO GRID DE CONEXÕES APIS-->
                    <div class="table-responsive">
                        <table id="gridapis" class="table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th>Ações</th>
                                    <th>Fornecedor</th>
                                    <th>Grupo de API</th>
                                    <th>Sub Grupo de API</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <!---------------------------->
                <!-- ABA PADRÕES DOS PLANOS -->
                <!---------------------------->
                <div class="tab-pane fade" id="tabs-padroes-planos" role="tabpanel"
                    aria-labelledby="tabs-padroes-planos-tab">

                    <!-- PRIMEIRA LINHA DO FORMULÁRIO DE PESQUISA -->
                    <div class="form-row">

                        <!-- FILTRO DO NOME DO TIPO DE PLANO -->
                        <div class="form-group col-md-3">
                            <label for="tp_plano_pesquisa">Tipo de Plano:</label>
                            <select class="form-control select2" name="tp_plano_pesquisa" data-allow-clear="true"
                                id="tp_plano_pesquisa" data-placeholder="Plano Contratado:" style="width: 100%;">
                                <option></option>
                                @foreach($tiposDePlanoVendido as $key => $tpplano)

                                <option value="{{$tpplano->tp_plano}}">{{$tpplano->tp_plano_desc}}</option>
                                @endforeach
                            </select>
                            <span id="tp_plano_pesquisaError" class="text-danger text-sm"></span>
                        </div>

                        <!-- BOTÃO PESQUISAR -->
                        <div class="form-group col-md-3 mt-4">
                            <button type="button" id="btnPesquisarTpPlano" class="btn btn-primary mt-2" style=""><i
                                    class="fa fa-search"></i> Pesquisar</button>
                        </div>
                    </div>

                    <div class="form-row">
                        <!-- BOTÃO PARA CRIAR CONEXÃO API -->
                        <div class="form-group col-md-3 mt-4">
                            <button type="button" id="btnCriarPadroesPlano" data-modal="modalPadroesPlano"
                                class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalPadroesPlano">
                                <i class="fa fa-plus"></i> Criar novo</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="gridtemplate-pdplan" class="table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th>Ações</th>
                                    <th>Tipo de Plano</th>
                                </tr>
                            </thead>

                        </table>
                    </div>
                </div>

                <!---------------------------->
                <!------- WHITE LABEL -------->
                <!---------------------------->
                <div class="tab-pane fade" id="tabs-white-label" role="tabpanel" aria-labelledby="tabs-white-label-tab">
                    <!-- BOTÃO PESQUISAR -->
                    <div class="form-row d-flex align-items-start mb-3">

                        <div class="mr-2">
                            <button type="button" id="btnPesquisarWl" class="btn btn-primary btn-sm" style=""><i
                                    class="fa fa-search"></i> Carregar Dados</button>

                        </div>
                        <div>
                            <button type="button" id="btnCriarWl" data-modal="modalWl" class="btn btn-primary btn-sm"
                                data-toggle="modal" data-target="#modalWl">
                                <i class="fa fa-plus"></i> Criar novo</button>
                        </div>
                    </div>


                    <div class="table-responsive">
                        <table id="gridtemplate-wl" class="table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th>Ações</th>
                                    <th>Empresa</th>
                                </tr>
                            </thead>

                        </table>
                    </div>
                </div>

                <!---------------------------->
                <!-------- WORK FLOW --------->
                <!---------------------------->
                <div class="tab-pane fade" id="tabs-work-flow" role="tabpanel" aria-labelledby="tabs-work-flow-tab">

                    <!-- PRIMEIRA LINHA DO FORMULÁRIO DE PESQUISA -->
                    <div class="form-row">

                        <!-- FILTRO DAS TABELAS SENSIBILIZADAS -->
                        <div class="form-group col-md-3">
                            <label for="tabela_filtro">Tabela Sensibilizada:</label>
                            <select id="tabela_filtro" name="tabela_filtro"
                                class="form-control select2 select2-hidden-accessible"
                                data-placeholder="Pesquise a Empresa" style="width: 100%;" aria-hidden="true">
                            </select>
                        </div>

                        <!-- BOTÃO PESQUISAR -->
                        <div class="form-group col-md-3 mt-4">
                            <button type="button" id="btnPesquisarWf" class="btn btn-primary mt-2" style=""><i
                                    class="fa fa-search"></i> Pesquisar</button>
                        </div>

                    </div>

                    <div class="form-row">
                        <!-- BOTÃO PARA CRIAR NOVO WORKFLOW -->
                        <div class="form-group col-md-3 mt-4">
                            <button type="button" id="btnCriarWorkFlow" class="btn btn-primary btn-sm" data-toggle="modal"
                                data-target="#modalWorkFlow" data-modal="modalWorkFlow">
                                <i class="fa fa-plus"></i> Criar novo</button>
                        </div>
                    </div>

                    <!-- CORPO DO QUADRO DO GRID DE MENSAGENS -->
                    <div class="table-responsive">
                        <table id="gridtemplate-wf" class="table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th>Ações</th>
                                    <th>Tabela</th>
                                    <th>Campo</th>
                                    <th>Usuário para Notificação</th>
                                    <th>Empresa</th>
                                </tr>
                            </thead>

                        </table>
                    </div>

                </div>

                <!---------------------------->
                <!--- PADRÃO DE MENSAGENS ---->
                <!---------------------------->
                <div class="tab-pane fade" id="tabs-padrao-msg" role="tabpanel" aria-labelledby="tabs-padrao-msg-tab">

                    <!-- PRIMEIRA LINHA DO FORMULÁRIO DE PESQUISA -->
                    <div class="form-row">

                        <!-- FILTRO DE CANAL DE COMUNICAÇÃO -->
                        <div class="form-group col-md-3">
                            <label for="canal_id_filtro">Canal de Comunicação:</label>
                            <select id="canal_id_filtro" name="canal_id_filtro"
                                class="form-control select2 select2-hidden-accessible" data-placeholder="Canal"
                                style="width: 100%;" aria-hidden="true">
                                <option></option>
                                @foreach($tbDmCanalCm as $key => $canal)
                                <option value="{{$canal->canal_id}}">{{$canal->canal_desc}}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- FILTRO DE CATEGORIA DE MENSAGEM -->
                        <div class="form-group col-md-3">
                            <label for="msg_categ_filtro">Categoria da Mensagem:</label>
                            <select id="msg_categ_filtro" name="msg_categ_filtro"
                                class="form-control select2 select2-hidden-accessible" data-placeholder="Categoria"
                                style="width: 100%;" aria-hidden="true">
                                <option></option>
                                @foreach($tbDmMsgCateg as $key => $categ)
                                <option value="{{$categ->msg_categ}}">{{$categ->msg_categ_desc}}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- BOTÃO PESQUISAR -->
                        <div class="form-group col-md-3 mt-4">
                            <button type="button" id="btnPesquisarPdMsg" class="btn btn-primary mt-2"><i
                                    class="fa fa-search"></i> Pesquisar</button>
                        </div>

                    </div>

                    <div class="form-row">
                        <!-- BOTÃO PARA CRIAR NOVA MENSAGEM -->
                        <div class="form-group col-md-3 mt-4">
                            <button type="button" id="btnCriarMsg" class="btn btn-primary btn-sm" data-toggle="modal"
                                data-modal="modalMensagem" data-target="#modalMensagem">
                                <i class="fa fa-plus"></i> Criar novo</button>
                        </div>
                    </div>

                    <!-- CORPO DO QUADRO DO GRID DE MENSAGENS -->
                    <div class="form-row">
                        <div class="table-responsive">
                            <table id="gridtemplate-pdmsg" class="table table-striped table-bordered nowrap">
                                <thead>
                                    <tr>
                                        <th>Ações</th>
                                        <th>Canal de Comunicação</th>
                                        <th>Categoria da Mensagem</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                </div>

                <!---------------------------->
                <!------- DADOS MESTRES ------>
                <!---------------------------->
                <div class="tab-pane fade" id="tabs-dados-mestres" role="tabpanel"
                    aria-labelledby="tabs-dados-mestres-tab">

                    <!-- PRIMEIRA LINHA DO FORMULÁRIO DE PESQUISA -->
                    <div class="form-row">

                        <!-- FILTRO DE TABELA DE DADOS MESTRES -->
                        <div class="form-group col-md-3">
                            <label for="tabela_bdm">Tabela de Dados Mestre:</label>
                            <select id="tabela_bdm" name="tabela_bdm"
                                class="form-control select2 select2-hidden-accessible"
                                data-placeholder="Pesquise a Tabela de Dados Mestre" style="width: 100%;"
                                aria-hidden="true">
                                @foreach($tables as $key => $table)
                                @if(Str::contains($table->Tables_in_db_sys_client, "tbdm") &&
                                !Str::contains($table->Tables_in_db_sys_client,
                                ["tbdm_empresa_geral","tbdm_empresa_param","tbdm_empresa_param","tbdm_produtos_geral","tbdm_clientes_geral"]))
                                <option value="{{$table->Tables_in_db_sys_client}}">{{$table->Tables_in_db_sys_client}}
                                </option>
                                @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- BOTÃO PESQUISAR -->
                        <div class="form-group col-md-3 mt-4">
                            <button type="button" id="btnPesquisarTbdm" class="btn btn-primary mt-2" style=""><i
                                    class="fa fa-search"></i> Pesquisar</button>
                        </div>

                    </div>

                    <div class="form-row">
                        <!-- BOTÃO PARA CRIAR NOVA TBDM -->
                        <div class="form-group col-md-3 mt-4">
                            <button type="button" id="btnCriarTbDm" class="btn btn-primary btn-sm" data-toggle="modal"
                                data-target="#modalTbDm">
                                <i class="fa fa-plus"></i> Criar novo</button>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="table-responsive">
                            <table id="gridtemplate-dm" class="table table-striped table-bordered nowrap">
                                <thead>
                                    <tr id="header-dt">
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</section>

<!-- MODAL DE CRIAÇÃO DE PADRÕES DE PLANOS -->
<div class="modal fade" id="modalPadroesPlano" role="dialog" aria-labelledby="modalPadroesPlanoLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalPadroesPlanoLabel">Padrões de Planos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="formPdPlan">
                @method('POST')
                @csrf
                <div class="modal-body">

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="tp_plano">Tipo de Plano:</label>
                            <select class="form-control select2" name="tp_plano" data-allow-clear="true" id="tp_plano"
                                data-placeholder="Selecione o Tipo de Plano" style="width: 100%;">
                                <option></option>
                                @foreach($tiposDePlanoVendido as $key => $tpplano)
                                <option value="{{$tpplano->tp_plano}}">{{$tpplano->tp_plano_desc}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for='emp_cdgbc'>Destino dos valores :*</label>
                            <select class="form-control select2" data-allow-clear="true" name="emp_destvlr"
                                id="emp_destvlr" data-placeholder="Selecione" style="width: 100%;">
                                <option></option>
                                @foreach($destinoDosValores as $key => $dest)
                                <option value="{{$dest->destvlr}}">{{$dest->destvlr}} -
                                    {{$dest->destvlr_desc}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="emp_wl" id="emp_wl">
                                <label for="emp_wl" class="custom-control-label">Contrato White Label:</label>
                            </div>
                        </div>

                        <div class="form-group col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="emp_privlbl" id="emp_privlbl">
                                <label for="emp_privlbl" class="custom-control-label">Contrato Private Label:</label>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="emp_reemb" id="emp_reemb">
                                <label for="emp_reemb" class="custom-control-label">Aceita Reembolso:</label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="emp_checkb" id="emp_checkb">
                                <label for="emp_checkb" class="custom-control-label">Check Out Boletagem:</label>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="emp_checkc" id="emp_checkc">
                                <label for="emp_checkc" class="custom-control-label">Check Out Convencional:</label>
                                <span id="emp_checkcError" class="text-danger text-sm"></span>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="emp_checkm" id="emp_checkm">
                                <label for="emp_checkm" class="custom-control-label">Check Out multban:</label>
                                <span id="emp_checkmError" class="text-danger text-sm"></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for='emp_cdgbc'>Tipo Boletagem :*</label>
                            <select class="form-control select2" name="emp_tpbolet" data-allow-clear="true"
                                id="emp_tpbolet" data-placeholder="Selecione o Tipo:" style="width: 100%;">
                                <option></option>
                                @foreach($tipoDeBoletagem as $key => $tpbolet)
                                <option value="{{$tpbolet->emp_tpbolet}}">{{$tpbolet->tpbolet_desc}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label for='emp_cdgbc'>Adquirente :*</label>
                            <select class="form-control select2" data-allow-clear="true" name="emp_adqrnt"
                                id="emp_adqrnt" data-placeholder="Selecione o Adquirente:" style="width: 100%;">
                                <option></option>
                                @foreach($tipoDeAdquirentes as $key => $tpadqrnt)
                                <option value="{{$tpadqrnt->emp_adqrnt}}">{{$tpadqrnt->adqrnt_desc}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="lib_cnscore" id="lib_cnscore">
                                <label for="lib_cnscore" class="custom-control-label">Liberar Consulta de SCORE:</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for='intervalo_mes'>Intervalo de meses entre Consultas:*</label>
                            <input type="number" id='intervalo_mes' name='intervalo_mes' value="" class="form-control form-control-sm"
                                placeholder='0'>
                        </div>
                        <div class="form-group col-md-4">
                            <label for='qtde_cns_freem'>Qtde de Consultas Mensal Gratuítas:*</label>
                            <input type="number" id='qtde_cns_freem' name='qtde_cns_freem' value="" class="form-control form-control-sm"
                                placeholder='0'>
                        </div>
                        <div class="form-group col-md-4">
                            <label for='qtde_cns_cntrm'>Qtde de Consultas Mensal Contratadas:*</label>
                            <input type="number" id='qtde_cns_cntrm' name='qtde_cns_cntrm' value="" class="form-control form-control-sm"
                                placeholder='0'>
                        </div>
                    </div>

                    <hr>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="card_posctr" id="card_posctr">
                                <label for="card_posctr" class="custom-control-label">Cartão Pós Pago Contratado:</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for='card_posparc'>Quantidade de Parcelas liberadas:*</label>
                            <input type="number" id='card_posparc' name='card_posparc' value="" class="form-control form-control-sm"
                                placeholder='0'>
                        </div>
                        <div class="form-group col-md-2">
                            <label for='vlr_pix'>Valor PIX:*</label>
                            <input type="text" id='vlr_pix' name='vlr_pix' value="" class="form-control money form-control-sm"
                                placeholder='0,00'>
                        </div>
                        <div class="form-group col-md-2">
                            <label for='vlr_boleto'>Valor Boleto:*</label>
                            <input type="text" id='vlr_boleto' name='vlr_boleto' value="" class="form-control money form-control-sm"
                                placeholder='0,00'>
                        </div>
                        <div class="form-group col-md-2">
                            <label for='vlr_bolepix'>Valor BolePIX:*</label>
                            <input type="text" id='vlr_bolepix' name='vlr_bolepix' value="" class="form-control money form-control-sm"
                                placeholder='0,00'>
                        </div>
                    </div>

                    <hr>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="card_prectr" id="card_prectr">
                                <label for="card_prectr" class="custom-control-label">Cartão Pré Pago Contratado:</label>
                            </div>
                        </div>

                        <div class="form-group col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="card_giftctr" id="card_giftctr">
                                <label for="card_giftctr" class="custom-control-label">Gift Card Contratado:</label>
                            </div>
                        </div>

                        <div class="form-group col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="card_fidctr" id="card_fidctr">
                                <label for="card_fidctr" class="custom-control-label">Cartão Fidelidade Contratado:</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for='tax_pre'>Taxa Pré:*</label>
                            <input type="text" id='tax_pre' name='tax_pre' value=""
                                class="form-control porcentagem form-control-sm" placeholder='0,00'>
                        </div>
                        <div class="form-group col-md-4">
                            <label for='tax_gift'>Taxa Gift:*</label>
                            <input type="text" id='tax_gift' name='tax_gift' value=""
                                class="form-control porcentagem form-control-sm" placeholder='0,00'>
                        </div>
                        <div class="form-group col-md-4">
                            <label for='tax_fid'>Taxa Fidelidade:*</label>
                            <input type="text" id='tax_fid' name='tax_fid' value=""
                                class="form-control porcentagem form-control-sm" placeholder='0,00'>
                        </div>
                    </div>

                    <hr>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="cob_mltjr_atr"
                                    id="cob_mltjr_atr">
                                <label for="cob_mltjr_atr" class="custom-control-label">Cobrar Multa e Juros por Atraso:</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for='perc_mlt_atr'>Percentual de Multa:*</label>
                            <input type="text" id='perc_mlt_atr' name='perc_mlt_atr' value=""
                                class="form-control porcentagem form-control-sm" placeholder='0,00'>
                        </div>
                        <div class="form-group col-md-4">
                            <label for='perc_jrs_atr'>Percentual de Juros:*</label>
                            <input type="text" id='perc_jrs_atr' name='perc_jrs_atr' value=""
                                class="form-control porcentagem form-control-sm" placeholder='0,00'>
                        </div>
                        <div class="form-group col-md-4">
                            <label for='perc_com_mltjr'>Comissão:*</label>
                            <input type="text" id='perc_com_mltjr' name='perc_com_mltjr' value=""
                                class="form-control porcentagem form-control-sm" placeholder='0,00'>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for='dias_inat_card'>Dias de atraso para inativação do cartão:*</label>
                            <input type="number" id='dias_inat_card' name='dias_inat_card' value="" class="form-control form-control-sm"
                                placeholder='0'>
                        </div>
                        <div class="form-group col-md-4">
                            <label for='isnt_pixblt'>Isentar PIX / Boleto para parcelas acima de:*</label>
                            <input type="text" id='isnt_pixblt' name='isnt_pixblt' value="" class="form-control money form-control-sm"
                                placeholder='0,00'>
                        </div>
                    </div>

                    <hr>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="parc_cjuros" id="parc_cjuros">
                                <label for="parc_cjuros" class="custom-control-label">Parcelamento com Juros:</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for='parc_jr_deprc'>A Partir da Parcela:*</label>
                            <input type="number" id='parc_jr_deprc' name='parc_jr_deprc' value="" class="form-control form-control-sm"
                                placeholder='0'>
                        </div>
                        <div class="form-group col-md-4">
                            <label for='tax_jrsparc'>Taxa de Juros:*</label>
                            <input type="text" id='tax_jrsparc' name='tax_jrsparc' value=""
                                class="form-control porcentagem form-control-sm" placeholder='0,00'>
                        </div>
                        <div class="form-group col-md-4">
                            <label for='parc_com_jrs'>Comissão:*</label>
                            <input type="text" id='parc_com_jrs' name='parc_com_jrs' value=""
                                class="form-control porcentagem form-control-sm" placeholder='0,00'>
                        </div>
                    </div>

                    <hr>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="pp_particular" id="pp_particular">
                                <label for="pp_particular" class="custom-control-label">PP - Particular:</label>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="pp_franquia" id="pp_franquia">
                                <label for="pp_franquia" class="custom-control-label">PP - Franquia:</label>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="pp_multcard" id="pp_multcard">
                                <label for="pp_multcard" class="custom-control-label">PP - Multcard Pontos:</label>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="pp_cashback" id="pp_cashback">
                                <label for="pp_cashback" class="custom-control-label">PP - Multcard Valor:</label>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secundary-multban btn-sm" data-dismiss="modal">
                        <i class="fas fa-times"></i> Fechar</button>
                    <button type="submit" class="btn btn-primary btn-sm" data-emp-id="" id="btnSalvarPadroesPlano"><i
                            class="fas fa-save"></i>
                        Salvar</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- MODAL DE CRIAÇÃO DE ALIAS -->
<div class="modal fade" id="modalAlias" role="dialog" aria-labelledby="modalAliasLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAliasLabel">Criar Novo Alias</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAlias">
                <div class="modal-body">
                    @method('POST')
                    @csrf
                    <div class="form-group">
                        <label for="emp_tab_name">Tabela de Sistema</label>
                        <select id="emp_tab_name" name="emp_tab_name" class="form-control select2"
                            data-placeholder="Selecione uma Tabela" style="width: 100%;">
                            @foreach($tables as $key => $table)
                            @if(!Str::contains($table->Tables_in_db_sys_client, "tbsy"))
                            <option value="{{$table->Tables_in_db_sys_client}}">{{$table->Tables_in_db_sys_client}}
                            </option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="emp_tab_alias">Alias</label>
                        <input type="text" id="emp_tab_alias" name="emp_tab_alias" placeholder="Digite o End Point"
                            class="form-control  form-control-sm" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secundary-multban btn-sm" data-dismiss="modal">
                        <i class="icon fas fa-times"></i> Fechar</button>
                    <button type="submit" class="btn btn-primary btn-sm" data-emp-id="" id="btnSalvarAlias"><i
                            class="fas fa-save"></i>
                        Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DE CRIAÇÃO DE CONEXÃO DB -->
<div class="modal fade" id="modalConexaoDB" role="dialog" aria-labelledby="modalConexaoDBLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalConexaoDBLabel">Conexão de banco de dados</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="formConexaoDB">
                @method('POST')
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="bc_fornec">Fornecedor:</label>
                        <select id="bc_fornec" name="bc_fornec" class="form-control select2"
                            data-placeholder="Selecione um Fornecedor" style="width: 100%;">
                            @foreach($tbdmFornecedor as $key => $fornecedor)
                            <option value="{{ $fornecedor->fornec}}">{{ $fornecedor->fornec_desc}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="bc_emp_ident">Identificação da Conexão:</label>
                            <input type="text" class="form-control  form-control-sm" id="bc_emp_ident" name="bc_emp_ident"
                                placeholder="Identificação da Conexão" />
                        </div>

                        <div class="form-group col-md-4">
                            <label for="bc_emp_host">Host</label>
                            <input type="text" class="form-control  form-control-sm" id="bc_emp_host" name="bc_emp_host"
                                placeholder="Host" />
                        </div>

                        <div class="form-group col-md-4">
                            <label for="bc_emp_porta">Porta</label>
                            <input type="text" id="bc_emp_porta" name="bc_emp_porta" placeholder="Porta"
                                class="form-control  form-control-sm" />
                        </div>
                    </div>

                    <div class="form-row">

                        <div class="form-group col-md-4">
                            <label for="bc_emp_nome">Nome do Bando de Dados</label>
                            <input type="text" id="bc_emp_nome" name="bc_emp_nome" placeholder="Nome do Bando de Dados"
                                class="form-control  form-control-sm" />
                        </div>

                        <div class="form-group col-md-4">
                            <label for="bc_emp_user">Usuário</label>
                            <input type="text" id="bc_emp_user" name="bc_emp_user" placeholder="Usuário"
                                class="form-control  form-control-sm" />
                        </div>

                        <div class="form-group col-md-4">
                            <label for="bc_emp_pass">Senha</label>
                            <input type="text" id="bc_emp_pass" name="bc_emp_pass" placeholder="Senha"
                                class="form-control  form-control-sm" />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="bc_emp_token">Token - Autenticação</label>
                            <input type="text" id="bc_emp_token" name="bc_emp_token" placeholder="Token - Autenticação"
                                class="form-control  form-control-sm" />
                        </div>

                        <div class="form-group col-md-4">
                            <label for="bc_emp_sslmo">SSL Mode</label>
                            <input type="text" id="bc_emp_sslmo" name="bc_emp_sslmo" placeholder="SSL Mode"
                                class="form-control  form-control-sm" />
                        </div>

                        <div class="form-group col-md-4">
                            <label for="bc_emp_sslce">SSL Certificate</label>
                            <input type="text" id="bc_emp_sslce" name="bc_emp_sslce" placeholder="SSL Certificate"
                                class="form-control  form-control-sm" />
                        </div>
                    </div>

                    <div class="form-row">

                        <div class="form-group col-md-4">
                            <label for="bc_emp_sslky">SSL Key</label>
                            <input type="text" id="bc_emp_sslky" name="bc_emp_sslky" placeholder="SSL Key"
                                class="form-control  form-control-sm" />
                        </div>

                        <div class="form-group col-md-4">
                            <label for="bc_emp_sslca">SSL CA</label>
                            <input type="text" id="bc_emp_sslca" name="bc_emp_sslca" placeholder="SSL CA"
                                class="form-control  form-control-sm" />
                        </div>

                        <div class="form-group col-md-4">
                            <label for="bc_emp_toconex">Timeout de conexão</label>
                            <input type="text" id="bc_emp_toconex" name="bc_emp_toconex"
                                placeholder="Timeout de conexão" class="form-control  form-control-sm" />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="bc_emp_tocons">Timeout de consulta</label>
                            <input type="text" id="bc_emp_tocons" name="bc_emp_tocons" placeholder="Timeout de consulta"
                                class="form-control  form-control-sm" />
                        </div>

                        <div class="form-group col-md-4">
                            <label for="bc_emp_pooling">Pooling de conexão</label>
                            <input type="text" id="bc_emp_pooling" name="bc_emp_pooling"
                                placeholder="Pooling de conexão" class="form-control  form-control-sm" />
                        </div>

                        <div class="form-group col-md-4">
                            <label for="bc_emp_charset">Charset</label>
                            <input type="text" id="bc_emp_charset" name="bc_emp_charset" placeholder="Charset"
                                class="form-control  form-control-sm" />
                        </div>
                    </div>

                    <div class="form-row">

                        <div class="form-group col-md-4">
                            <label for="bc_emp_tzone">Time Zone</label>
                            <input type="text" id="bc_emp_tzone" name="bc_emp_tzone" placeholder="Time Zone"
                                class="form-control  form-control-sm" />
                        </div>

                        <div class="form-group col-md-4">
                            <label for="bc_emp_appname">Application Name</label>
                            <input type="text" id="bc_emp_appname" name="bc_emp_appname" placeholder="Application Name"
                                class="form-control  form-control-sm" />
                        </div>

                        <div class="form-group col-md-4">
                            <label for="bc_emp_keepalv">Keep Alive</label>
                            <input type="text" id="bc_emp_keepalv" name="bc_emp_keepalv" placeholder="Keep Alive"
                                class="form-control  form-control-sm" />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="bc_emp_compress">Compression</label>
                            <input type="text" id="bc_emp_compress" name="bc_emp_compress" placeholder="Compression"
                                class="form-control  form-control-sm" />
                        </div>

                        <div class="form-group col-md-4">
                            <label for="bc_emp_readonly">Read Only</label>
                            <input type="text" id="bc_emp_readonly" name="bc_emp_readonly" placeholder="Read Only"
                                class="form-control  form-control-sm" />
                        </div>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secundary-multban btn-sm" data-dismiss="modal">
                        <i class="fas fa-times"></i> Fechar</button>
                    <button type="submit" class="btn btn-primary btn-sm" data-emp-id="" id="btnSalvarConexaoDB"><i
                            class="fas fa-save"></i>
                        Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DE CRIAÇÃO DE CONEXÃO API-->
<div class="modal fade" id="modalCriarConexaoAPI" role="dialog" aria-labelledby="modalCriarConexaoAPILabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalCriarConexaoAPILabel">Criar Parâmetros de Conexão</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>


            <div class="modal-body">
                <div id="formAPI">
                    @method('POST')
                    @csrf
                    <div class="form-group">
                        <label for="bc_fornec_api">Fornecedor</label>
                        <select id="bc_fornec_api" name="bc_fornec_api" class="form-control select2"
                            data-placeholder="Selecione um Fornecedor" style="width: 100%;">
                            <option></option>
                            @foreach($tbdmFornecedor as $key => $fornec)
                            <option value="{{$fornec->fornec}}">{{$fornec->fornec_desc}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="api_grupo_api">Grupo de API</label>
                            <select id="api_grupo_api" name="api_grupo_api" class="form-control select2"
                                data-placeholder="Selecione um Grupo de API" style="width: 100%;">
                                <option></option>
                                @foreach($tbdmApiGrupo as $key => $grupo)
                                <option value="{{$grupo->api_grupo}}">{{$grupo->api_grupo_desc}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="api_subgrp_api">Sub Grupo de API</label>
                            <select id="api_subgrp_api" name="api_subgrp_api" class="form-control select2"
                                data-placeholder="Selecione um Sub Grupo de API" style="width: 100%;">
                                <option></option>
                                @foreach($tbdmApiSubGrupo as $key => $grupo)
                                <option value="{{$grupo->api_subgrp}}">{{$grupo->api_subgrp_desc}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="api_emp_endpoint">End Point</label>
                            <input type="text" id="api_emp_endpoint" name="api_emp_endpoint"
                                placeholder="Digite o End Point" class="form-control  form-control-sm" />
                        </div>

                        <div class="form-group col-md-6">
                            <label for="api_emp_mtdo">Método HTTP</label>
                            <select id="api_emp_mtdo" name="api_emp_mtdo" class="form-control select2"
                                data-placeholder="Digite o Método HTTP" style="width: 100%;">
                                <option value="get">get</option>
                                <option value="post">post</option>
                                <option value="put">put</option>
                                <option value="patch">patch</option>
                                <option value="delete">delete</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="api_emp_token">Token de Autenticação</label>
                            <input type="text" id="api_emp_token" name="api_emp_token"
                                placeholder="Digite o Token de Autenticação" class="form-control  form-control-sm" />
                        </div>

                        <div class="form-group col-md-6">
                            <label for="api_emp_tpde">Tipo de Dados Enviados</label>
                            <input type="text" id="api_emp_tpde" name="api_emp_tpde"
                                placeholder="Tipo de Dados Enviados" class="form-control  form-control-sm" />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="api_emp_tpda">Tipo de Dados Aceitos</label>
                            <input type="text" id="api_emp_tpda" name="api_emp_tpda" placeholder="Tipo de Dados Aceitos"
                                class="form-control  form-control-sm" />
                        </div>

                        <div class="form-group col-md-6">
                            <label for="api_emp_user">Usuário</label>
                            <input type="text" id="api_emp_user" name="api_emp_user" placeholder="Usuário"
                                class="form-control  form-control-sm" />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="api_emp_pass">Senha</label>
                            <input type="text" id="api_emp_pass" name="api_emp_pass" placeholder="Senha"
                                class="form-control  form-control-sm" />
                        </div>

                        <div class="form-group col-md-6">
                            <label for="api_emp_key">API Key</label>
                            <input type="text" id="api_emp_key" name="api_emp_key" placeholder="API Key"
                                class="form-control  form-control-sm" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secundary-multban btn-sm" data-dismiss="modal">
                    <i class="icon fas fa-times"></i> Fechar</button>
                <button type="button" class="btn btn-primary btn-sm" data-emp-id="" id="btnSalvarAPI">
                    <i calss="icon fas fa-save"></i> Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE CRIAÇÃO DE White Label -->
<div class="modal fade" id="modalWl" role="dialog" aria-labelledby="modalWlLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalWlLabel">Criar White Label</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formWl">
                <div class="modal-body">
                    @method('POST')
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="text_color_df">Mini Logo:</label>


                            <div class="custom-file">
                                <input autocomplete="off" type="file" class="custom-file-input"
                                    accept=".jpeg,.png,.jpg,.gif,.webp" name="mini_logo" id="mini_logo">
                                <label class="custom-file-label" for="mini_logo" id="mini_logo_label"
                                    data-browse="Alterar Imagem">Adicionar Imagem</label>
                            </div>
                            <!-- /.input group -->
                        </div>
                        <div class="form-group col-md-6">
                            <label for="text_color_df">Logo Horizontal:</label>


                            <div class="custom-file">
                                <input autocomplete="off" type="file" class="custom-file-input"
                                    accept=".jpeg,.png,.jpg,.gif,.webp" name="logo_h" id="logo_h">
                                <label class="custom-file-label" for="logo_h " id="logo_h_label"
                                    data-browse="Alterar Imagem">Adicionar Imagem</label>
                            </div>
                            <!-- /.input group -->
                        </div>
                    </div>
                    <div class="form-row">

                        <div class="form-group col-md-3">
                            <label for="text_color_df">Cor Padrão do Texto:</label>

                            <div class="input-group text_color_df">
                                <input type="text" class="form-control  form-control-sm" id="text_color_df" name="text_color_df">

                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-square fa-lg"></i></span>
                                </div>
                            </div>
                            <!-- /.input group -->
                        </div>
                        <div class="form-group col-md-3">
                            <label for="fd_color">Cor Primária:</label>

                            <div class="input-group fd_color">
                                <input type="text" class="form-control  form-control-sm" id="fd_color" name="fd_color">

                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-square fa-lg"></i></span>
                                </div>
                            </div>
                            <!-- /.input group -->
                        </div>
                        <div class="form-group col-md-6">
                            <label for="fdsel_color">Hover/Active Botão Cor Primária:</label>

                            <div class="input-group fdsel_color">
                                <input type="text" class="form-control  form-control-sm" id="fdsel_color" name="fdsel_color">

                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-square fa-lg"></i></span>
                                </div>
                            </div>
                            <!-- /.input group -->
                        </div>
                    </div>

                    <div class="form-row">

                        <div class="form-group col-md-5">
                            <label for="ft_color">Cor Secundária:</label>

                            <div class="input-group ft_color">
                                <input type="text" class="form-control  form-control-sm" id="ft_color" name="ft_color">

                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-square fa-lg"></i></span>
                                </div>
                            </div>
                            <!-- /.input group -->
                        </div>
                        <div class="form-group col-md-5">
                            <label for="ftsel_color">Hover/Active Botão Cor Secundária:</label>

                            <div class="input-group ftsel_color">
                                <input type="text" class="form-control  form-control-sm" id="ftsel_color" name="ftsel_color">

                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-square fa-lg"></i></span>
                                </div>
                            </div>
                            <!-- /.input group -->
                        </div>
                    </div>

                    <div class="form-row">

                        <div class="form-group col-md-5">
                            <label for="bg_menu_ac_color">Background Menu active:</label>

                            <div class="input-group bg_menu_ac_color">
                                <input type="text" class="form-control  form-control-sm" id="bg_menu_ac_color" name="bg_menu_ac_color">

                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-square fa-lg"></i></span>
                                </div>
                            </div>
                            <!-- /.input group -->
                        </div>
                        <div class="form-group col-md-4">
                            <label for="bg_item_menu_ac_color">Bg Item Menu active:</label>

                            <div class="input-group bg_item_menu_ac_color">
                                <input type="text" class="form-control  form-control-sm" id="bg_item_menu_ac_color"
                                    name="bg_item_menu_ac_color">

                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-square fa-lg"></i></span>
                                </div>
                            </div>
                            <!-- /.input group -->
                        </div>
                        <div class="form-group col-md-3">
                            <label for="menu_ac_color">Texto Menu active:</label>

                            <div class="input-group menu_ac_color">
                                <input type="text" class="form-control  form-control-sm" id="menu_ac_color" name="menu_ac_color">

                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-square fa-lg"></i></span>
                                </div>
                            </div>
                            <!-- /.input group -->
                        </div>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secundary-multban btn-sm" data-dismiss="modal">
                        <i class="icon fas fa-times"></i> Fechar</button>
                    <button type="submit" class="btn btn-primary btn-sm" data-emp-id="" id="btnSalvarWl"><i
                            class="fas fa-save"></i>
                        Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DE CRIAÇÃO DE WORKFLOW -->
<div class="modal fade" id="modalWorkFlow" role="dialog" aria-labelledby="modalWorkFlowLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalWorkFlowLabel">Criar WorkFlow</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="formWorkFlow">

                    <div class="form-group">
                        <label for="tabela">Tabela Sensibilizada:</label>
                        <select id="tabela" name="tabela" class="form-control select2"
                            data-placeholder="Selecione uma Tabela do Bano de Dados" style="width: 100%;">
                            @foreach($tables as $key => $table)
                            <option value="{{$table->Tables_in_db_sys_client}}">{{$table->Tables_in_db_sys_client}}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="campo">Campo da Tabela:</label>
                        <select id="campo" name="campo" class="form-control select2"
                            data-placeholder="Selecione um campo da Tabela" style="width: 100%;">
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="user_id">Usuário para Notificação:</label>
                        <select id="user_id" name="user_id" class="form-control select2"
                            data-placeholder="Selecione um usuário" style="width: 100%;">
                            @foreach($users as $key => $user)
                            <option value="{{$user->user_id}}">{{$user->user_name}}</option>
                            @endforeach
                        </select>
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secundary-multban btn-sm" data-dismiss="modal">
                    <i class="fas fa-times"></i> Fechar</button>
                <button type="button" class="btn btn-primary btn-sm" data-emp-id="" id="btnSalvarWorkFlow">
                    <i class="fas fa-save"></i> Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE CRIAÇÃO DE MENSAGEM -->
<div class="modal fade" id="modalMensagem" role="dialog" aria-labelledby="modalMensagemLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalMensagemLabel">Criar Mensagem</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="formMensagem">

                    <div class="form-group">
                        <label for="canal_id">Canal de Comunicação:</label>
                        <select id="canal_id" name="canal_id" class="form-control select2 select2-hidden-accessible"
                            data-placeholder="Canal" style="width: 100%;" aria-hidden="true">
                            <option></option>
                            @foreach($tbDmCanalCm as $key => $canal)
                            <option value="{{$canal->canal_id}}">{{$canal->canal_desc}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="msg_categ">Categoria da Mensagem:</label>
                        <select id="msg_categ" name="msg_categ" class="form-control select2 select2-hidden-accessible"
                            data-placeholder="Categoria" style="width: 100%;" aria-hidden="true">
                            <option></option>
                            @foreach($tbDmMsgCateg as $key => $categ)
                            <option value="{{$categ->msg_categ}}">{{$categ->msg_categ_desc}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="msg_text">Mensagem</label>
                        <textarea id="msg_text" name="msg_text" class="form-control  form-control-sm"></textarea>
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secundary-multban btn-sm" data-dismiss="modal">
                    <i class="fas fa-times"></i> Fechar</button>
                <button type="button" class="btn btn-primary btn-sm" data-emp-id="" id="btnSalvarMensagem">
                    <i class="fas fa-save"></i> Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE TABELAS DADOS MESTRE -->
<div class="modal fade" id="modalTbDm" role="dialog" aria-labelledby="modalTbDmLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTbDmLabel">Criar Novo Alias</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formTbDm">
                @method('POST')
                @csrf
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secundary-multban btn-sm" data-dismiss="modal">
                        <i class="icon fas fa-times"></i> Fechar</button>
                    <button type="submit" class="btn btn-primary btn-sm" data-emp-id="" id="btnSalvarTbDm"><i
                            class="fas fa-save"></i>
                        Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Select2 -->
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/i18n/pt-BR.js') }}"></script>
<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-select/js/dataTables.select.min.js')}}"></script>
<script src="{{ asset('assets/plugins/datatables-fixedheader/js/dataTables.fixedHeader.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>

<!-- bootstrap color picker -->
<script src="{{ asset('assets/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js') }}"></script>
<script src="{{ asset('assets/dist/js/app.js') }}"></script>
<script src="{{ asset('assets/dist/js/pages/sistema-multban/gridsistema-multban.js') }}"></script>

<script type="text/javascript">
    function empresaObrigatoria(isNew = false) {
            var empresa = $('#empresa_id').select2('data')[0];
            var hasError = false;
            if (empresa) {
                if (!empresa.emp_id) {
                    hasError = true;
                }
            } else {
                hasError = true;
            }

            if (hasError && isNew) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Selecione uma empresa antes de continuar.'
                });

                $("#empresa_id").addClass("is-invalid");

                $("#empresa_id")
                    .closest(".form-group")
                    .find(".select2-selection")
                    .css("border-color", "#dc3545")
                    .addClass("text-danger");

                return false;
            }

            if (!empresa) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Selecione uma empresa antes de continuar.'
                });
                $("#empresa_id").addClass("is-invalid");

                $("#empresa_id")
                    .closest(".form-group")
                    .find(".select2-selection")
                    .css("border-color", "#dc3545")
                    .addClass("text-danger");
                return false;
            }
            return true;
        };

        // Intercepta todos os botões de pesquisa/carregar
        $('#btnPesquisarFbd, #btnPesquisarAlias, #btnPesquisarFapi, #btnPesquisarTpPlano, #btnPesquisarWl, #btnPesquisarWf, #btnPesquisarPdMsg, #btnPesquisarTbdm').on('click', function(e) {
            if (!empresaObrigatoria(false)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
        });

        // Intercepta todos os botões de criar
        $('#btnCriarConexaoDB, #btnCriarAlias, #btnCriarConexaoAPI, #btnCriarWl, #btnCriarWorkFlow, #btnCriarMsg, #btnCriarPadroesPlano').on('click', function(e) {
            if (!empresaObrigatoria(true)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
        });


        $(document).ready(function () {
            @if ($message = Session::get('success'))
                $("#empresa_id").val({{ Session::get('idModeloInserido') }})
                toastr.success("{{ $message }}", "Sucesso");
            @endif
            @if ($message = Session::get('error'))
                toastr.error("{{ $message }}", "Erro");
            @endif
            @if (count($errors) > 0)
                @foreach ($errors->all() as $error)
                    toastr.error("{{ $error }}", "Erro");
                @endforeach
            @endif


        });
</script>
@endpush
