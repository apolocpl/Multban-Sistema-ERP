@extends('layouts.app-master')
@section('page.title', 'Empresa')
@push('script-head')
    <link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/summernote/summernote-bs4.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}" />
@endpush

@section('content')
<!-- Main content -->
<section class="content">

    @if ($routeAction)
        <form class="form-horizontal" id="formPrincipal" role="form" method="POST"
            action="{{ route('empresa.update', $empresaGeral->emp_id) }}">
            @method('PATCH')
    @else
        <form class="form-horizontal" id="formPrincipal" role="form" method="POST"
            action="{{ route('empresa.store') }}">
            @method('POST')
    @endif

        @csrf
        @include('Multban.template.updatetemplate')
        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="dados-gerais-tab" data-toggle="pill" href="#dados-gerais"
                            role="tab" aria-controls="dados-gerais" aria-selected="true">Dados Gerais</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="endereco-tab" data-toggle="pill" href="#endereco" role="tab"
                            aria-controls="endereco" aria-selected="false">Endereço</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="contatos-tab" data-toggle="pill" href="#contatos" role="tab"
                            aria-controls="contatos" aria-selected="false">Contatos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="multmais-tab" data-toggle="pill" href="#multmais" role="tab"
                            aria-controls="multmais" aria-selected="false">Mult+</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="antecipacao-tab" data-toggle="pill" href="#antecipacao" role="tab"
                            aria-controls="antecipacao" aria-selected="false">Antecipação</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="rebate-tab" data-toggle="pill" href="#rebate" role="tab"
                            aria-controls="rebate" aria-selected="false">Rebate / Royalties / Comissão</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="cobrancas-tab" data-toggle="pill" href="#cobrancas" role="tab"
                            aria-controls="cobrancas" aria-selected="false">Cobranças</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="comunicacao-tab" data-toggle="pill" href="#comunicacao" role="tab"
                            aria-controls="comunicacao" aria-selected="false">Comunicação</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- DADOS GERAIS -->
                    <div class="tab-pane fade show active" id="dados-gerais" role="tabpanel"
                        aria-labelledby="dados-gerais-tab">
                        <div class="form-row" id="serachClient">
                            <div class="form-group col-md-2">
                                <label for="emp_id">Código:</label>
                                <div class="input-group mb-3 input-group-sm">
                                    <span class="input-group-prepend">
                                        <a href="#" data-toggle="modal" data-target="#pesquisa-empresa-modal"
                                            class="btn btn-default" data-placement="top"
                                            title="Pesquisar empresa"><i class="fas fa-search text-primary"></i></a>
                                    </span>
                                    <input autocomplete="off" type="text" autofocus="autofocus"
                                        class="form-control cep form-control-sm" id="emp_id" name="emp_id"
                                        value="{{ str_pad($empresaGeral->emp_id, 5, "0", STR_PAD_LEFT) }}"
                                        placeholder="Código da Empresa">
                                    <span class="input-group-append">
                                        <a href="#" class="btn btn-default" data-placement="top"
                                            id="btnCarregaEmpresa" data-toggle="tooltip" title="Carregar empresa"><i
                                                class="fas fa-check text-info"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">

                            <div class="form-group col-md-2">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" {{$empresaGeral->emp_wl == 'x' ? 'checked' : ''}} type="checkbox" name="emp_wl" id="emp_wl">
                                    <label for="emp_wl" class="custom-control-label">Contrato White Label:</label>
                                    <span id="emp_wlError" class="text-danger text-sm"></span>
                                </div>
                            </div>

                            <div class="form-group col-md-2">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" {{$empresaGeral->emp_privlbl == 'x' ? 'checked' : ''}} type="checkbox" name="emp_privlbl" id="emp_privlbl">
                                    <label for="emp_privlbl" class="custom-control-label">Contrato Private
                                        Label:</label>
                                    <span id="emp_privlblError" class="text-danger text-sm"></span>
                                </div>
                            </div>

                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="emp_cnpj">CNPJ:*</label>
                                <input autocomplete="off" type="text" autofocus="autofocus"
                                    class="form-control cnpj form-control-sm" id="emp_cnpj" name="emp_cnpj"
                                    value="{{$empresaGeral->emp_cnpj}}" required placeholder="00.000.000.0000/00">
                                <span id="emp_cnpjError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-2">
                                <label for="emp_sts">Status:*</label>
                                <select class="form-control select2" name="emp_sts" id="emp_sts"
                                    data-placeholder="Selecione" style="width: 100%;">
                                    <option></option>
                                    @foreach($status as $key => $sta)

                                        <option {{$sta->emp_sts == $empresaGeral->emp_sts ? 'selected' : ''}}
                                            value="{{$sta->emp_sts}}">{{$sta->emp_sts_desc}}</option>
                                    @endforeach
                                </select>
                                <span id="emp_stsError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="emp_wlde">White Label da Empresa:</label>
                                <input autocomplete="off" class="form-control  form-control-sm" placeholder="White Label da Empresa"
                                    autofocus="autofocus" maxlength="60" name="emp_wlde" type="text" id="emp_wlde"
                                    value="{{ $empresaGeral->emp_wlde }}" readonly>
                                <span id="emp_wldeError" class="text-danger text-sm"></span>
                            </div>

                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="emp_ie">Inscrição Estadual:</label>
                                <input autocomplete="off" type="text" class="form-control ie form-control-sm" id="emp_ie"
                                    name="emp_ie" value="{{$empresaGeral->emp_ie}}"
                                    placeholder="Inscrição Estadual">
                                <span id="emp_ieError" class="text-danger text-sm"></span>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="emp_im">Inscrição Municipal:</label>
                                <input autocomplete="off" type="text" class="form-control  form-control-sm" id="emp_im" name="emp_im"
                                    value="{{$empresaGeral->emp_im}}" placeholder="Inscrição Municipal">
                                <span id="emp_imError" class="text-danger text-sm"></span>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="emp_rzsoc">Razão Social:*</label>
                                <input autocomplete="off" type="text" maxlength="255" class="form-control  form-control-sm"
                                    id="emp_rzsoc" name="emp_rzsoc" value="{{$empresaGeral->emp_rzsoc}}"
                                    placeholder="Razão Social">
                                <span id="emp_rzsocError" class="text-danger text-sm"></span>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="emp_nfant">Nome Fantasia:*</label>
                                <input autocomplete="off" maxlength="255" type="text" class="form-control  form-control-sm"
                                    id="emp_nfant" name="emp_nfant" value="{{$empresaGeral->emp_nfant}}"
                                    placeholder="Nome Fantasia">
                                <span id="emp_nfantError" class="text-danger text-sm"></span>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="emp_nmult">Nome multmais:*</label>
                                <input autocomplete="off" maxlength="15" type="text" class="form-control  form-control-sm"
                                    id="emp_nmult" name="emp_nmult" value="{{$empresaGeral->emp_nmult}}"
                                    placeholder="Nome multmais">
                                <span id="emp_nmultError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="emp_ratv">Ramo de Atividade: </label>
                                <select id="emp_ratv" name="emp_ratv"
                                    class="form-control select2 select2-hidden-accessible"
                                    data-placeholder="Selecione o ramo de atividade" style="width: 100%;" required>
                                    <option></option>
                                    @foreach($ratvs as $key => $ramo)

                                        <option {{$ramo->emp_ratv == $empresaGeral->emp_ratv ? 'selected' : ''}}
                                            value="{{$ramo->emp_ratv}}">{{$ramo->emp_ratv_desc}}</option>
                                    @endforeach
                                </select>
                                <span id="ramoError" class="text-danger text-sm"></span>
                            </div>
                        </div>

                        <hr>
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label>Empresa é Franqueadora?:</label>
                                <div class="form-group">
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio"
                                            {{$empresaGeral->emp_frq == 'x' ? 'checked' : ''}} value="sim"
                                            id="emp_frq" name="emp_frq">
                                        <label for="emp_frq" class="custom-control-label">SIM</label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" {{$empresaGeral->emp_frq == '' ? 'checked' : ''}} value="nao" type="radio" id="emp_frq_n" name="emp_frq">
                                        <label for="emp_frq_n" class="custom-control-label">NÃO</label>
                                    </div>
                                    <span id="emp_frqError" class="text-danger text-sm"></span>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="emp_frqmst">Franqueador Master:</label>
                                <select class="form-control select2" data-allow-clear="true"
                                    @if($empresaGeral->emp_frq == 'x') disabled="disabled" @endif name="emp_frqmst"
                                    id="emp_frqmst" data-placeholder="Selecione" style="width: 100%;">
                                    <option></option>
                                    @foreach($franqueadorMaster as $key => $master)

                                        <option {{$master->emp_id == $empresaGeral->emp_frqmst ? 'selected' : ''}}
                                            value="{{$master->emp_id}}">{{$master->emp_rzsoc}}</option>
                                    @endforeach
                                </select>
                                <span id="emp_frqmstError" class="text-danger text-sm"></span>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Compartilha Cadastro com Franqueador?:</label>
                                <div class="form-group">
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio"
                                            {{$empresaGeral->emp_frqcmp == 'x' ? 'checked' : ''}} value="sim"
                                            id="emp_frqcmp" name="emp_frqcmp">
                                        <label for="emp_frqcmp" class="custom-control-label">SIM</label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio"
                                            {{$empresaGeral->emp_frqcmp == '' ? 'checked' : ''}} value="nao"
                                            id="emp_frqcmp_n" name="emp_frqcmp">
                                        <label for="emp_frqcmp_n" class="custom-control-label">NÃO</label>
                                    </div>
                                </div>
                                <span id="emp_frqcmp_nError" class="text-danger text-sm"></span>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Aumentar Limite de Crédito:</label>
                                <div class="form-group">
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio"
                                            {{$empresaGeral->emp_altlmt == 'x' ? 'checked' : ''}} value="sim"
                                            id="emp_altlmt" name="emp_altlmt">
                                        <label for="emp_altlmt" class="custom-control-label">SOMENTE GESTOR</label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio"
                                            {{$empresaGeral->emp_altlmt == '' ? 'checked' : ''}} value="nao"
                                            id="emp_altlmt_n" name="emp_altlmt">
                                        <label for="emp_altlmt_n" class="custom-control-label">TODOS</label>
                                    </div>
                                </div>
                                <span id="emp_altlmt_nError" class="text-danger text-sm"></span>
                            </div>
                        </div>
                        <hr>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" {{$empresaGeral->emp_integra == 'x' ? 'checked' : ''}} type="checkbox" name="emp_integra" id="emp_integra">
                                    <label for="emp_integra" class="custom-control-label">Utiliza
                                        Integração:</label>
                                    <span id="emp_integraError" class="text-danger text-sm"></span>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" {{$empresaGeral->emp_checkb == 'x' ? 'checked' : ''}} type="checkbox" name="emp_checkb" id="emp_checkb">
                                    <label for="emp_checkb" class="custom-control-label">Check Out
                                        Boletagem:</label>
                                    <span id="emp_checkbError" class="text-danger text-sm"></span>
                                </div>
                            </div>

                            <div class="form-group col-md-3">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" {{$empresaGeral->emp_checkm == 'x' ? 'checked' : ''}} type="checkbox" name="emp_checkm" id="emp_checkm">
                                    <label for="emp_checkm" class="custom-control-label">Check Out multmais:</label>
                                    <span id="emp_checkmError" class="text-danger text-sm"></span>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" {{$empresaGeral->emp_checkc == 'x' ? 'checked' : ''}} type="checkbox" name="emp_checkc" id="emp_checkc">
                                    <label for="emp_checkc" class="custom-control-label">Check Out Convencional:</label>
                                    <span id="emp_checkcError" class="text-danger text-sm"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" {{$empresaGeral->emp_reemb == 'x' ? 'checked' : ''}} type="checkbox" name="emp_reemb" id="emp_reemb">
                                    <label for="emp_reemb" class="custom-control-label">Aceita Reembolso:</label>
                                    <span id="emp_reembError" class="text-danger text-sm"></span>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <select class="form-control select2" name="emp_tpbolet" data-allow-clear="true"
                                    id="emp_tpbolet" data-placeholder="Tipo Boletagem:" style="width: 100%;">
                                    <option></option>
                                    @foreach($tipoDeBoletagem as $key => $tpbolet)

                                        <option {{$tpbolet->emp_tpbolet == $empresaGeral->emp_tpbolet ? 'selected' : ''}}
                                            value="{{$tpbolet->emp_tpbolet}}">{{$tpbolet->tpbolet_desc}}</option>
                                    @endforeach
                                </select>
                                <span id="emp_tpboletError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-3">
                                <select class="form-control select2" name="tp_plano" data-allow-clear="true"
                                    id="tp_plano" data-placeholder="Plano Contratado:" style="width: 100%;">
                                    <option></option>
                                    @foreach($tiposDePlanoVendido as $key => $tpplano)

                                        <option {{$tpplano->tp_plano == $empresaGeral->tp_plano ? 'selected' : ''}}
                                            value="{{$tpplano->tp_plano}}">{{$tpplano->tp_plano_desc}}</option>
                                    @endforeach
                                </select>
                                <span id="tp_planoError" class="text-danger text-sm"></span>
                            </div>
                            <div class="form-group col-md-3">
                                <select class="form-control select2" data-allow-clear="true" name="emp_adqrnt"
                                    id="emp_adqrnt" data-placeholder="Adquirente:" style="width: 100%;">
                                    <option></option>
                                    @foreach($tipoDeAdquirentes as $key => $tpadqrnt)

                                        <option {{$tpadqrnt->emp_adqrnt == $empresaGeral->emp_adqrnt ? 'selected' : ''}}
                                            value="{{$tpadqrnt->emp_adqrnt}}">{{$tpadqrnt->adqrnt_desc}}</option>
                                    @endforeach
                                </select>
                                <span id="emp_adqrntError" class="text-danger text-sm"></span>
                            </div>
                        </div>
                        <hr>
                        <div class="form-row">

                            <div class="form-group col-md-2">
                                <label for="emp_meiocom">Como conheceu a Multban:*</label>
                                <select class="form-control select2" name="emp_meiocom" id="emp_meiocom"
                                    data-placeholder="Selecione" style="width: 100%;">
                                    <option></option>
                                    @foreach($meioComomunicacao as $key => $meiocom)

                                        <option {{$meiocom->emp_meiocom == $empresaGeral->emp_meiocom ? 'selected' : ''}}
                                            value="{{$meiocom->emp_meiocom}}">{{$meiocom->meio_com_desc}}</option>
                                    @endforeach
                                </select>
                                <span id="emp_meiocomError" class="text-danger text-sm"></span>
                            </div>
                        </div>
                        <hr>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="vlr_imp">Valor da Implantação:*</label>
                                <input autocomplete="off" type="text" class="form-control money form-control-sm" id="vlr_imp"
                                    name="vlr_imp" value="{{$empresaGeral->vlr_imp}}" placeholder="0,00">
                                <span id="vlr_impError" class="text-danger text-sm"></span>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="dtvenc_imp">Data do vencimento da Implantação:*</label>
                                <input autocomplete="off" type="text" class="form-control datetimepicker-input form-control-sm"
                                    id="dtvenc_imp" name="dtvenc_imp" value="{{$empresaGeral->dtvenc_imp}}"
                                    data-toggle="datetimepicker" placeholder="dd/mm/aaaa" data-target="#dtvenc_imp"
                                    placeholder="Data do vencimento da Implantação">
                                <span id="dtvenc_impError" class="text-danger text-sm"></span>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="cond_pgto">Condição de Pgto da Implantação:*</label>
                                <input autocomplete="off" type="number" class="form-control  form-control-sm" id="cond_pgto"
                                    name="cond_pgto" value="{{$empresaGeral->cond_pgto}}" placeholder="0">
                                <span id="cond_pgtoError" class="text-danger text-sm"></span>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="vlr_mens">Valor da Mensalidade:*</label>
                                <input autocomplete="off" type="text" class="form-control money form-control-sm" id="vlr_mens"
                                    name="vlr_mens" value="{{$empresaGeral->vlr_mens}}" placeholder="0,00">
                                <span id="vlr_mensError" class="text-danger text-sm"></span>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="dtvenc_mens">Data de Vencimento da Mensalidade:*</label>
                                <input autocomplete="off" type="text" class="form-control datetimepicker-input form-control-sm"
                                    id="dtvenc_mens" name="dtvenc_mens" value="{{$empresaGeral->dtvenc_mens}}"
                                    data-toggle="datetimepicker" placeholder="dd/mm/aaaa" data-target="#dtvenc_mens"
                                    placeholder="Data de Vencimento da Mensalidade">
                                <span id="dtvenc_mensError" class="text-danger text-sm"></span>
                            </div>
                        </div>

                    </div>

                    <!-- ENDEREÇO -->
                    <div class="tab-pane fade" id="endereco" role="tabpanel" aria-labelledby="endereco-tab">
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for='emp_cep'>CEP:*</label>
                                <a href="#" data-toggle="modal" data-target="#cep-info-modal">
                                    <i class="far fa-question-circle"></i>
                                </a>
                                <div class="input-group mb-3 input-group-sm">
                                    <input autocomplete="off" type="text" autofocus="autofocus"
                                        class="form-control cep form-control-sm" id="emp_cep" name="emp_cep"
                                        value="{{$empresaGeral->emp_cep}}" placeholder="00000-000">
                                    <span class="input-group-append">
                                        <button type="button" id="btnPesquisarCep" class="btn btn-default"><i
                                                class="fa fa-search"></i></button>
                                    </span>
                                </div>
                                <span id="emp_cepError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-4">
                                <label for='emp_end'>Endereço (Logradouro):*</label>
                                <input type="text" id='emp_end' name='emp_end' value="{{$empresaGeral->emp_end}}"
                                    class="form-control form-control-sm" placeholder='Endereço' maxlength='60'>
                                <span id="emp_endError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-2">
                                <label for='emp_endnum'>Número:*</label>
                                <input type="text" id='emp_endnum' name='emp_endnum'
                                    value="{{$empresaGeral->emp_endnum}}" class="form-control form-control-sm" placeholder='Número'>
                                <span id="emp_endnumError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-4">
                                <label for='emp_endcmp'>Complemento:</label>
                                <input type="text" id='emp_endcmp' name='emp_endcmp'
                                    value="{{$empresaGeral->emp_endcmp}}" class="form-control form-control-sm"
                                    placeholder='Complemento'>
                                <span id="emp_endcmpError" class="text-danger text-sm"></span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for='emp_endbair'>Bairro:*</label>
                                <input type="text" id='emp_endbair' name='emp_endbair'
                                    value="{{$empresaGeral->emp_endbair}}" class="form-control form-control-sm"
                                    placeholder='Bairro'>
                                <span id="emp_endbairError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-2">
                                <label for='emp_endpais'>Pais:*</label>
                                <select id="emp_endpais" name="emp_endpais"
                                    class="form-control select2 select2-hidden-accessible"
                                    data-placeholder="Pesquise o Pais" style="width: 100%;" aria-hidden="true">
                                    @if($empresaGeral->pais)
                                        <option value="{{$empresaGeral->pais->pais}}">{{$empresaGeral->pais->pais}} -
                                            {{$empresaGeral->pais->pais_desc}}</option>
                                    @else
                                        <option value="BR">BR - BRASIL</option>
                                    @endif
                                </select>
                                <span id="emp_endpaisError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-3">
                                <label for='emp_endest'>Estado:*</label>
                                <select id="emp_endest" name="emp_endest"
                                    class="form-control select2 select2-hidden-accessible"
                                    data-placeholder="Pesquise o Estado" style="width: 100%;" aria-hidden="true">
                                    @if ($empresaGeral->estado)
                                        <option value="{{$empresaGeral->estado->estado}}">
                                            {{$empresaGeral->estado->estado}} - {{$empresaGeral->estado->estado_desc}}
                                        </option>
                                    @endif
                                </select>
                                <span id="emp_endestError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-4">
                                <label for='emp_endcid'>Cidade:*</label>
                                <select id="emp_endcid" name="emp_endcid"
                                    class="form-control select2 select2-hidden-accessible"
                                    data-placeholder="Pesquise a Cidade" style="width: 100%;" aria-hidden="true">
                                    @if($empresaGeral->cidade)
                                        <option value="{{$empresaGeral->cidade->cidade}}">
                                            {{$empresaGeral->cidade->cidade_ibge}}
                                            - {{$empresaGeral->cidade->cidade_desc}}</option>
                                    @endif
                                </select>
                                <span id="emp_endcidError" class="text-danger text-sm"></span>
                            </div>
                        </div>

                    </div>

                    <!-- CONTATOS -->
                    <div class="tab-pane fade" id="contatos" role="tabpanel" aria-labelledby="contatos-tab">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for='emp_resplg'>Responsável Legal:*</label>
                                <input type="text" id='emp_resplg' name='emp_resplg'
                                    value="{{$empresaGeral->emp_resplg}}" class="form-control form-control-sm"
                                    placeholder='Responsável Legal' maxlength='60'>
                                <span id="emp_resplgError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-3">
                                <label for='emp_emailrp'>E-mail Responsável Legal:*</label>
                                <input type="email" id='emp_emailrp' name='emp_emailrp'
                                    value="{{$empresaGeral->emp_emailrp}}" class="form-control form-control-sm"
                                    placeholder='E-mail Responsável Legal'>
                                <span id="emp_emailrpError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-3">
                                <label for='emp_celrp'>Celular Responsável Legal:*</label>
                                <input type="text" id='emp_celrp' name='emp_celrp'
                                    value="{{$empresaGeral->emp_celrp}}" class='form-control cell_with_ddd'
                                    placeholder='Celular Responsável Legal'>
                                <span id="emp_celrpError" class="text-danger text-sm"></span>
                            </div>
                        </div>

                        <div class="form-row">

                            <div class="form-group col-md-3">
                                <label for='emp_respcm'>Contato Comercial:*</label>
                                <input type="text" id='emp_respcm' name='emp_respcm'
                                    value="{{$empresaGeral->emp_respcm}}" class="form-control form-control-sm"
                                    placeholder='Contato Comercial'>
                                <span id="emp_respcmError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-3">
                                <label for='emp_emailcm'>E-mail Comercial:*</label>
                                <input type="email" id='emp_emailcm' name='emp_emailcm'
                                    value="{{$empresaGeral->emp_emailcm}}" class="form-control form-control-sm"
                                    placeholder='E-mail Comercial'>
                                <span id="emp_emailcmError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-3">
                                <label for='emp_celcm'>Celular Comercial:*</label>
                                <input type="text" id='emp_celcm' name='emp_celcm'
                                    value="{{$empresaGeral->emp_celcm}}" class='form-control cell_with_ddd'
                                    placeholder='Celular Comercial'>
                                <span id="emp_celcmError" class="text-danger text-sm"></span>
                            </div>
                        </div>

                        <div class="form-row">

                            <div class="form-group col-md-3">
                                <label for='emp_respfi'>Contato Financeiro:*</label>
                                <input type="text" id='emp_respfi' name='emp_respfi'
                                    value="{{$empresaGeral->emp_respfi}}" class="form-control form-control-sm"
                                    placeholder='Contato Financeiro'>
                                <span id="emp_respfiError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-3">
                                <label for='emp_emailfi'>E-mail Financeiro:*</label>
                                <input type="email" id='emp_emailfi' name='emp_emailfi'
                                    value="{{$empresaGeral->emp_emailfi}}" class="form-control form-control-sm"
                                    placeholder='E-mail Financeiro'>
                                <span id="emp_emailfiError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-3">
                                <label for='emp_celfi'>Celular Financeiro:*</label>
                                <input type="text" id='emp_celfi' name='emp_celfi'
                                    value="{{$empresaGeral->emp_celfi}}" class='form-control cell_with_ddd'
                                    placeholder='Celular Financeiro'>
                                <span id="emp_celfiError" class="text-danger text-sm"></span>
                            </div>
                        </div>

                        <div class="form-row">

                            <div class="form-group col-md-3">
                                <label for='emp_pagweb'>Página Web:*</label>
                                <input type="text" id='emp_pagweb' name='emp_pagweb'
                                    value="{{$empresaGeral->emp_pagweb}}" class="form-control form-control-sm"
                                    placeholder='Página Web'>
                                <span id="emp_pagwebError" class="text-danger text-sm"></span>
                            </div>

                            <div class="form-group col-md-3">
                                <label for='emp_rdsoc'>Redes Sociais:*</label>
                                <input type="text" id='emp_rdsoc' name='emp_rdsoc'
                                    value="{{$empresaGeral->emp_rdsoc}}" class="form-control form-control-sm"
                                    placeholder='Redes Sociais'>
                                <span id="emp_rdsocError" class="text-danger text-sm"></span>
                            </div>
                        </div>

                    </div>

                    <!-- MULTMAIS -->
                    <div class="tab-pane fade" id="multmais" role="tabpanel" aria-labelledby="multmais-tab">

                        <div class="card card-prinary">
                            <div class="card-body">
                                <div class="form-row align-items-end">
                                    <div class="form-group col-md-3">
                                        <label for='emp_cdgbc'>Destino dos valores :*</label>
                                        <select class="form-control select2" data-allow-clear="true"
                                            name="emp_destvlr" id="emp_destvlr" data-placeholder="Selecione"
                                            style="width: 100%;">
                                            <option></option>
                                            @foreach($destinoDosValores as $key => $dest)
                                                <option {{$dest->destvlr == $empresaParam->emp_destvlr ? 'selected' : ''}}
                                                    value="{{$dest->destvlr}}">{{$dest->destvlr}} -
                                                    {{$dest->destvlr_desc}}</option>
                                            @endforeach
                                        </select>
                                        <span id="emp_cdgbcError" class="text-danger text-sm"></span>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" {{$empresaParam->emp_dbaut == 'x' ? 'checked' : ''}} type="checkbox" name="emp_dbaut" id="emp_dbaut">
                                            <label for="emp_dbaut" class="custom-control-label">Tatifas e Mensalidade em Débito Automático:</label>
                                            <span id="emp_dbautError" class="text-danger text-sm"></span>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-row">

                                    <div class="form-group col-md-3">
                                        <label for='emp_cdgbc'>Cdg Banco Principal:*</label>
                                        <select class="form-control select2" data-allow-clear="true"
                                            name="emp_cdgbc" id="emp_cdgbc" data-placeholder="Selecione"
                                            style="width: 100%;">
                                            <option></option>

                                            @foreach($codigoDosbancos as $key => $emp_cdgbc)

                                                <option {{$emp_cdgbc->cdgbc == $empresaParam->emp_cdgbc ? 'selected' : ''}} value="{{$emp_cdgbc->cdgbc}}">{{$emp_cdgbc->cdgbc}} -
                                                    {{$emp_cdgbc->cdgbc_desc}}</option>
                                            @endforeach
                                        </select>
                                        <span id="emp_cdgbcError" class="text-danger text-sm"></span>
                                    </div>

                                    <div class="col-md-7" id="banco-principal" style="display: none;">
                                        <div class="row">
                                            <div class="form-group col-md-3">
                                                <label for='emp_agbc'>Agência:</label>
                                                <input type="text" maxlength="20" id='emp_agbc' name='emp_agbc'
                                                    value="{{$empresaParam->emp_agbc}}" class="form-control form-control-sm"
                                                    placeholder='Agência'>
                                                <span id="emp_agbcError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for='emp_ccbc'>Conta Corrente:</label>
                                                <input type="text" id='emp_ccbc' name='emp_ccbc'
                                                    value="{{$empresaParam->emp_ccbc}}" class="form-control form-control-sm"
                                                    placeholder='Conta Corrente'>
                                                <span id="emp_ccbcError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for='emp_pix'>Chave PIX:</label>
                                                <input type="text" id='emp_pix' name='emp_pix'
                                                    value="{{$empresaParam->emp_pix}}" class="form-control form-control-sm"
                                                    placeholder='Chave PIX'>
                                                <span id="emp_pixError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for='emp_seller'>Seller:</label>
                                                <input type="text" id='emp_seller' name='emp_seller'
                                                    value="{{$empresaParam->emp_seller}}" class="form-control form-control-sm"
                                                    placeholder='Seller'>
                                                <span id="emp_sellerError" class="text-danger text-sm"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for='emp_cdgbcs'>Cdg Banco Secundário:</label>
                                        <select class="form-control select2" data-allow-clear="true"
                                            name="emp_cdgbcs" id="emp_cdgbcs" data-placeholder="Selecione"
                                            style="width: 100%;">
                                            <option></option>
                                            @foreach($codigoDosbancos as $key => $emp_cdgbcs)

                                                <option {{$emp_cdgbcs->cdgbc == $empresaParam->emp_cdgbcs ? 'selected' : ''}} value="{{$emp_cdgbcs->cdgbc}}">{{$emp_cdgbcs->cdgbc}} -
                                                    {{$emp_cdgbcs->cdgbc_desc}}</option>
                                            @endforeach
                                        </select>
                                        <span id="emp_cdgbcsError" class="text-danger text-sm"></span>
                                    </div>
                                    <div class="col-md-7" id="banco-secundario" style="display: none;">
                                        <div class="row">
                                            <div class="form-group col-md-3">
                                                <label for='emp_agbcs'>Agência:</label>
                                                <input type="text" maxlength="20" id='emp_agbcs' name='emp_agbcs'
                                                    value="{{$empresaParam->emp_agbcs}}" class="form-control form-control-sm"
                                                    placeholder='Agência'>
                                                <span id="emp_agbcsError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for='emp_ccbcs'>Conta Corrente:</label>
                                                <input type="text" id='emp_ccbcs' name='emp_ccbcs'
                                                    value="{{$empresaParam->emp_ccbcs}}" class="form-control form-control-sm"
                                                    placeholder='Conta Corrente'>
                                                <span id="emp_ccbcsError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for='emp_pixs'>Chave PIX:</label>
                                                <input type="text" id='emp_pixs' name='emp_pixs'
                                                    value="{{$empresaParam->emp_pixs}}" class="form-control form-control-sm"
                                                    placeholder='Chave PIX'>
                                                <span id="emp_pixsError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for='emp_sellers'>Seller:</label>
                                                <input type="text" id='emp_sellers' name='emp_sellers'
                                                    value="{{$empresaParam->emp_sellers}}" class="form-control form-control-sm"
                                                    placeholder='Seller'>
                                                <span id="emp_sellersError" class="text-danger text-sm"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="form-row">
                            <div class="col-md-12">
                                <div class="card card-prinary">
                                    <div class="card-body">
                                        <div class="form-row">

                                            <div class="form-group col-md-2">
                                                <label for='vlr_pix'>Valor PIX:*</label>
                                                <input type="text" id='vlr_pix' name='vlr_pix'
                                                    value="{{$empresaParam->vlr_pix}}" class="form-control money form-control-sm"
                                                    placeholder='0,00'>
                                                <span id="vlr_pixError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for='vlr_boleto'>Valor Boleto:*</label>
                                                <input type="text" id='vlr_boleto' name='vlr_boleto'
                                                    value="{{$empresaParam->vlr_boleto}}" class="form-control money form-control-sm"
                                                    placeholder='0,00'>
                                                <span id="vlr_boletoError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for='vlr_bolepix'>Valor BolePIX:*</label>
                                                <input type="text" id='vlr_bolepix' name='vlr_bolepix'
                                                    value="{{$empresaParam->vlr_bolepix}}"
                                                    class="form-control money form-control-sm" placeholder='0,00'>
                                                <span id="vlr_bolepixError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for='dias_inat_card'>Dias de atraso para inativação do
                                                    cartão:*</label>
                                                <input type="number" id='dias_inat_card' name='dias_inat_card'
                                                    value="{{$empresaParam->dias_inat_card}}" class="form-control form-control-sm"
                                                    placeholder='0'>
                                                <span id="dias_inat_cardError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for='isnt_pixblt'>Isentar PIX / Boleto para parcelas acima
                                                    de:*</label>
                                                <input type="text" id='isnt_pixblt' name='isnt_pixblt'
                                                    value="{{$empresaParam->isnt_pixblt}}"
                                                    class="form-control money form-control-sm" placeholder='0,00'>
                                                <span id="isnt_pixbltError" class="text-danger text-sm"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div
                                    class="card card-outline card-primary {{$empresaParam->blt_ctr == 'x' ? '' : 'collapsed-card'}}">
                                    <div class="card-header">
                                        <div class="card-title pt-2">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input" {{$empresaParam->blt_ctr == 'x' ? 'checked' : ''}} type="checkbox" name="blt_ctr" id="blt_ctr">
                                                <label for="blt_ctr" class="custom-control-label">Boletagem
                                                    Contratada:</label>
                                            </div>
                                        </div>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" id="blt_ctr_coll"
                                                data-card-widget="collapse">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">

                                        <div class="form-row">
                                            <div class="form-group col-md-2">
                                                <label for='tax_blt'>Taxa:*</label>
                                                <input type="text" id='tax_blt' name='tax_blt'
                                                    value="{{$empresaParam->tax_blt}}"
                                                    class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                                <span id="tax_bltError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for='blt_parclib'>Quantidade de Parcelas liberadas:*</label>
                                                <input type="number" id='blt_parclib' name='blt_parclib'
                                                    value="{{$empresaParam->blt_parclib}}" class="form-control form-control-sm"
                                                    placeholder='0'>
                                                <span id="blt_parclibError" class="text-danger text-sm"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="card card-outline card-primary {{$empresaParam->lib_cnscore == 'x' ? '' : 'collapsed-card'}}">
                                    <div class="card-header">
                                        <div class="card-title pt-2">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input"
                                                    {{$empresaParam->lib_cnscore == 'x' ? 'checked' : ''}}
                                                    type="checkbox" name="lib_cnscore" id="lib_cnscore">
                                                <label for="lib_cnscore" class="custom-control-label">Liberar
                                                    Consulta de SCORE:</label>
                                            </div>
                                        </div>

                                        <div class="card-tools">

                                            <button type="button" class="btn btn-tool" id="lib_cnscore_coll"
                                                data-card-widget="collapse">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">

                                        <div class="form-row">

                                            <div class="form-group col-md-3">
                                                <label for='intervalo_mes'>Intervalo de meses entre
                                                    Consultas:</label>
                                                <input type="number" id='intervalo_mes' name='intervalo_mes'
                                                    value="{{$empresaParam->intervalo_mes}}" class="form-control form-control-sm"
                                                    placeholder='0'>
                                                <span id="intervalo_mesError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for='qtde_cns_freem'>Qtde de Consultas Mensal
                                                    Gratuítas:*</label>
                                                <input type="number" id='qtde_cns_freem' name='qtde_cns_freem'
                                                    value="{{$empresaParam->qtde_cns_freem}}" class="form-control form-control-sm"
                                                    placeholder='0'>
                                                <span id="qtde_cns_freemError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for='qtde_cns_cntrm'>Qtde de Consultas Mensal
                                                    Contratadas:*</label>
                                                <input type="number" id='qtde_cns_cntrm' name='qtde_cns_cntrm'
                                                    value="{{$empresaParam->qtde_cns_cntrm}}" class="form-control form-control-sm"
                                                    placeholder='0'>
                                                <span id="qtde_cns_cntrmError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for='qtde_cns_prem'>Qtde de Consultas Mensal
                                                    Pré-Pagas:</label>
                                                <input type="number" id='qtde_cns_prem' readonly disabled='disabled'
                                                    name='qtde_cns_prem' value="{{$empresaParam->qtde_cns_prem}}"
                                                    class="form-control form-control-sm" placeholder='0'>
                                                <span id="qtde_cns_premError" class="text-danger text-sm"></span>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <label for='qtde_cns_totm'>Qtde Total de Consultas Mensal:</label>
                                                <input type="number" id='qtde_cns_totm' name='qtde_cns_totm'
                                                    readonly disabled='disabled'
                                                    value="{{$empresaParam->qtde_cns_totm}}" class="form-control form-control-sm"
                                                    placeholder='0'>
                                                <span id="qtde_cns_totmError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for='qtde_cns_utlxm'>Qtde Utilizada no Mês:</label>
                                                <input type="number" id='qtde_cns_utlxm' name='qtde_cns_utlxm'
                                                    readonly disabled='disabled'
                                                    value="{{$empresaParam->qtde_cns_utlxm}}" class="form-control form-control-sm"
                                                    placeholder='0'>
                                                <span id="qtde_cns_utlxmError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for='qtde_cns_dispm'>Qtde Disponível no Mês:</label>
                                                <input type="number" id='qtde_cns_dispm' name='qtde_cns_dispm'
                                                    readonly disabled='disabled'
                                                    value="{{$empresaParam->qtde_cns_dispm}}" class="form-control form-control-sm"
                                                    placeholder='0'>
                                                <span id="qtde_cns_dispmError" class="text-danger text-sm"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-12">
                                <div
                                    class="card card-outline card-primary {{$empresaParam->card_posctr == 'x' ? '' : 'collapsed-card'}}">
                                    <div class="card-header">
                                        <div class="card-title pt-2">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input"
                                                    {{$empresaParam->card_posctr == 'x' ? 'checked' : ''}}
                                                    type="checkbox" name="card_posctr" id="card_posctr">
                                                <label for="card_posctr" class="custom-control-label">Cartão Pós
                                                    Pago Contratado:</label>
                                            </div>
                                        </div>

                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" id="card_posctr_coll"
                                                data-card-widget="collapse">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">

                                        <div class="form-row">
                                            <div class="form-group col-md-2">
                                                <label for='card_posparc'>Quantidade de Parcelas liberadas:*</label>
                                                <input type="number" id='card_posparc' name='card_posparc'
                                                    value="{{$empresaParam->card_posparc}}" class="form-control form-control-sm"
                                                    placeholder='0'>
                                                <span id="card_posparcError" class="text-danger text-sm"></span>
                                            </div>
                                        </div>
                                        <div class="card card-prinary">
                                            <div class="card-header">
                                                <div class="card-title pt-2">
                                                    <h5>Primeira à Vista</h5>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p><span id="VISTAError" class="text-danger text-sm"></span>
                                                        </p>
                                                        <p><span id="VISTA_parc_deError"
                                                                class="text-danger text-sm"></span></p>
                                                        <p><span id="VISTA_parc_ateError"
                                                                class="text-danger text-sm"></span></p>
                                                        <p><span id="VISTA_taxaError"
                                                                class="text-danger text-sm"></span></p>
                                                    </div>
                                                </div>

                                                <div class="row text-bold">
                                                    <div class="col-2">Parcela de:</div>
                                                    <div class="col-2">Parcela até:</div>
                                                    <div class="col-2">Taxa:</div>
                                                </div>
                                                @php
                                                    $countTax = $empresaTaxpos->count();
                                                @endphp
                                                <div id="taxa_avista">
                                                    @foreach($empresaTaxpos as $key => $tax)
                                                        @if($tax->tax_categ == "VISTA")
                                                            <div class="form-row" id="taxa_avista_{{$countTax}}">
                                                                <div class="form-group col-md-2">
                                                                    <input class="form-control form-control-sm"
                                                                        value="{{$tax->tax_categ}}"
                                                                        name="tax_categ_avista[{{$countTax}}][categ]"
                                                                        type="hidden">
                                                                    <input class="form-control form-control-sm"
                                                                        data-categ="{{$tax->tax_categ}}"
                                                                        data-tipo="{{$tax->tax_categ}}_parc_de"
                                                                        value="{{$tax->parc_de}}" placeholder="0"
                                                                        name="tax_categ_avista[{{$countTax}}][parc_de]"
                                                                        type="number">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <input class="form-control form-control-sm"
                                                                        data-categ="{{$tax->tax_categ}}"
                                                                        data-tipo="{{$tax->tax_categ}}_parc_ate"
                                                                        value="{{$tax->parc_ate}}" placeholder="0"
                                                                        name="tax_categ_avista[{{$countTax}}][parc_ate]"
                                                                        type="number">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <input class="form-control form-control-sm porcentagem"
                                                                        data-categ="{{$tax->tax_categ}}"
                                                                        data-tipo="{{$tax->tax_categ}}_taxa"
                                                                        value="{{$tax->tax}}" placeholder="0,00"
                                                                        name="tax_categ_avista[{{$countTax}}][taxa]"
                                                                        type="text">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <button type="button" data-id="{{$countTax}}"
                                                                        data-categ="avista"
                                                                        class="btn btn-danger btn-sm remove_taxa"><i
                                                                            class="icon fas fa-trash"></i></button>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @php
                                                            $countTax++;
                                                        @endphp
                                                    @endforeach
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-md-2">
                                                        <button type="button"
                                                            class="btn btn-primary btn-sm incluir_taxa_btn"
                                                            data-categ="avista" data-tipo="VISTA">
                                                            <i class="fas fa-plus"></i> Incluir Taxa
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card card-prinary">
                                            <div class="card-header">
                                                <div class="card-title pt-2">
                                                    <h5>Primeira para 30 dias</h5>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p><span id="PRM_30Error"
                                                                class="text-danger text-sm"></span> </p>
                                                        <p><span id="PRM_30_parc_deError"
                                                                class="text-danger text-sm"></span></p>
                                                        <p><span id="PRM_30_parc_ateError"
                                                                class="text-danger text-sm"></span></p>
                                                        <p><span id="PRM_30_taxaError"
                                                                class="text-danger text-sm"></span></p>
                                                    </div>
                                                </div>

                                                <div class="row text-bold">
                                                    <div class="col-2">Parcela de:</div>
                                                    <div class="col-2">Parcela até:</div>
                                                    <div class="col-2">Taxa:</div>
                                                </div>
                                                <div id="taxa_30">
                                                    @foreach($empresaTaxpos as $key => $tax)
                                                        @if($tax->tax_categ == "PRM_30")
                                                            <div class="form-row" id="taxa_30_{{$countTax}}">
                                                                <div class="form-group col-md-2">
                                                                    <input class="form-control form-control-sm"
                                                                        value="{{$tax->tax_categ}}"
                                                                        name="tax_categ_30[{{$countTax}}][categ]"
                                                                        type="hidden">
                                                                    <input class="form-control form-control-sm"
                                                                        data-categ="{{$tax->tax_categ}}"
                                                                        data-tipo="{{$tax->tax_categ}}_parc_de"
                                                                        value="{{$tax->parc_de}}" placeholder="0"
                                                                        name="tax_categ_30[{{$countTax}}][parc_de]"
                                                                        type="number">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <input class="form-control form-control-sm"
                                                                        data-categ="{{$tax->tax_categ}}"
                                                                        data-tipo="{{$tax->tax_categ}}_parc_ate"
                                                                        value="{{$tax->parc_ate}}" placeholder="0"
                                                                        name="tax_categ_30[{{$countTax}}][parc_ate]"
                                                                        type="number">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <input class="form-control form-control-sm porcentagem"
                                                                        data-categ="{{$tax->tax_categ}}"
                                                                        data-tipo="{{$tax->tax_categ}}_taxa"
                                                                        value="{{$tax->tax}}" placeholder="0,00"
                                                                        name="tax_categ_30[{{$countTax}}][taxa]"
                                                                        type="text">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <button type="button" data-id="{{$countTax}}"
                                                                        data-categ="30"
                                                                        class="btn btn-danger btn-sm remove_taxa"><i
                                                                            class="icon fas fa-trash"></i></button>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @php
                                                            $countTax++;
                                                        @endphp
                                                    @endforeach

                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-md-2">
                                                        <button type="button"
                                                            class="btn btn-primary btn-sm incluir_taxa_btn" data-categ="30"
                                                            data-tipo="PRM_30">
                                                            <i class="fas fa-plus"></i> Incluir Taxa
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card card-prinary">
                                            <div class="card-header">
                                                <div class="card-title pt-2">
                                                    <h5>Primeira para 60 dias</h5>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p><span id="PRM_60Error"
                                                                class="text-danger text-sm"></span> </p>
                                                        <p><span id="PRM_60_parc_deError"
                                                                class="text-danger text-sm"></span></p>
                                                        <p><span id="PRM_60_parc_ateError"
                                                                class="text-danger text-sm"></span></p>
                                                        <p><span id="PRM_60_taxaError"
                                                                class="text-danger text-sm"></span></p>
                                                    </div>
                                                </div>

                                                <div class="row text-bold">
                                                    <div class="col-2">Parcela de:</div>
                                                    <div class="col-2">Parcela até:</div>
                                                    <div class="col-2">Taxa:</div>
                                                </div>
                                                <div id="taxa_60">
                                                    @foreach($empresaTaxpos as $key => $tax)
                                                        @if($tax->tax_categ == "PRM_60")
                                                            <div class="form-row" id="taxa_60_{{$countTax}}">
                                                                <div class="form-group col-md-2">
                                                                    <input class="form-control form-control-sm"
                                                                        value="{{$tax->tax_categ}}"
                                                                        name="tax_categ_60[{{$countTax}}][categ]"
                                                                        type="hidden">
                                                                    <input class="form-control form-control-sm"
                                                                        data-categ="{{$tax->tax_categ}}"
                                                                        data-tipo="{{$tax->tax_categ}}_parc_de"
                                                                        value="{{$tax->parc_de}}" placeholder="0"
                                                                        name="tax_categ_60[{{$countTax}}][parc_de]"
                                                                        type="number">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <input class="form-control form-control-sm"
                                                                        data-categ="{{$tax->tax_categ}}"
                                                                        data-tipo="{{$tax->tax_categ}}_parc_ate"
                                                                        value="{{$tax->parc_ate}}" placeholder="0"
                                                                        name="tax_categ_60[{{$countTax}}][parc_ate]"
                                                                        type="number">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <input class="form-control form-control-sm porcentagem"
                                                                        data-categ="{{$tax->tax_categ}}"
                                                                        data-tipo="{{$tax->tax_categ}}_taxa"
                                                                        value="{{$tax->tax}}" placeholder="0,00"
                                                                        name="tax_categ_60[{{$countTax}}][taxa]"
                                                                        type="text">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <button type="button" data-id="{{$countTax}}"
                                                                        data-categ="60"
                                                                        class="btn btn-danger btn-sm remove_taxa"><i
                                                                            class="icon fas fa-trash"></i></button>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @php
                                                            $countTax++;
                                                        @endphp
                                                    @endforeach
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-md-2">
                                                        <button type="button"
                                                            class="btn btn-primary btn-sm incluir_taxa_btn" data-categ="60"
                                                            data-tipo="PRM_60">
                                                            <i class="fas fa-plus"></i> Incluir Taxa
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card card-prinary">
                                            <div class="card-header">
                                                <div class="card-title pt-2">
                                                    <h5>Primeira para 90 dias</h5>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p><span id="PRM_90Error"
                                                                class="text-danger text-sm"></span> </p>
                                                        <p><span id="PRM_90_parc_deError"
                                                                class="text-danger text-sm"></span></p>
                                                        <p><span id="PRM_90_parc_ateError"
                                                                class="text-danger text-sm"></span></p>
                                                        <p><span id="PRM_90_taxaError"
                                                                class="text-danger text-sm"></span></p>
                                                    </div>
                                                </div>

                                                <div class="row text-bold">
                                                    <div class="col-2">Parcela de:</div>
                                                    <div class="col-2">Parcela até:</div>
                                                    <div class="col-2">Taxa:</div>
                                                </div>
                                                <div id="taxa_90">
                                                    @foreach($empresaTaxpos as $key => $tax)
                                                        @if($tax->tax_categ == "PRM_90")
                                                            <div class="form-row" id="taxa_90_{{$countTax}}">
                                                                <div class="form-group col-md-2">
                                                                    <input class="form-control form-control-sm"
                                                                        value="{{$tax->tax_categ}}"
                                                                        name="tax_categ_90[{{$countTax}}][categ]"
                                                                        type="hidden">
                                                                    <input class="form-control form-control-sm"
                                                                        data-categ="{{$tax->tax_categ}}"
                                                                        data-tipo="{{$tax->tax_categ}}_parc_de"
                                                                        value="{{$tax->parc_de}}" placeholder="0"
                                                                        name="tax_categ_90[{{$countTax}}][parc_de]"
                                                                        type="number">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <input class="form-control form-control-sm"
                                                                        data-categ="{{$tax->tax_categ}}"
                                                                        data-tipo="{{$tax->tax_categ}}_parc_ate"
                                                                        value="{{$tax->parc_ate}}" placeholder="0"
                                                                        name="tax_categ_90[{{$countTax}}][parc_ate]"
                                                                        type="number">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <input class="form-control form-control-sm porcentagem"
                                                                        data-categ="{{$tax->tax_categ}}"
                                                                        data-tipo="{{$tax->tax_categ}}_taxa"
                                                                        value="{{$tax->tax}}" placeholder="0,00"
                                                                        name="tax_categ_90[{{$countTax}}][taxa]"
                                                                        type="text">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <button type="button" data-id="{{$countTax}}"
                                                                        data-categ="90"
                                                                        class="btn btn-danger btn-sm remove_taxa"><i
                                                                            class="icon fas fa-trash"></i></button>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @php
                                                            $countTax++;
                                                        @endphp
                                                    @endforeach
                                                </div>
                                                <div class="form-row">
                                                    <div class="form-group col-md-2">
                                                        <button type="button"
                                                            class="btn btn-primary btn-sm incluir_taxa_btn" data-categ="90"
                                                            data-tipo="PRM_90">
                                                            <i class="fas fa-plus"></i> Incluir Taxa
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div
                                    class="card card-outline card-primary {{$empresaParam->cob_mltjr_atr == 'x' ? '' : 'collapsed-card'}}">
                                    <div class="card-header">
                                        <div class="card-title pt-2">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input"
                                                    {{$empresaParam->cob_mltjr_atr == 'x' ? 'checked' : ''}}
                                                    type="checkbox" name="cob_mltjr_atr" id="cob_mltjr_atr">
                                                <label for="cob_mltjr_atr" class="custom-control-label">Cobrar Multa
                                                    e Juros por Atraso:</label>
                                            </div>
                                        </div>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" id="cob_mltjr_atr_coll"
                                                data-card-widget="collapse">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <label for='perc_mlt_atr'>Percentual de Multa:*</label>
                                                <input type="text" id='perc_mlt_atr' name='perc_mlt_atr'
                                                    value="{{$empresaParam->perc_mlt_atr}}"
                                                    class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                                <span id="perc_mlt_atrError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for='perc_jrs_atr'>Percentual de Juros:*</label>
                                                <input type="text" id='perc_jrs_atr' name='perc_jrs_atr'
                                                    value="{{$empresaParam->perc_jrs_atr}}"
                                                    class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                                <span id="perc_jrs_atrError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for='perc_com_mltjr'>Comissão:*</label>
                                                <input type="text" id='perc_com_mltjr' name='perc_com_mltjr'
                                                    value="{{$empresaParam->perc_com_mltjr}}"
                                                    class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                                <span id="perc_com_mltjrError" class="text-danger text-sm"></span>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div
                                    class="card card-outline card-primary {{$empresaParam->parc_cjuros == 'x' ? '' : 'collapsed-card'}}">
                                    <div class="card-header">
                                        <div class="card-title pt-2">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input"
                                                    {{$empresaParam->parc_cjuros == 'x' ? 'checked' : ''}}
                                                    type="checkbox" name="parc_cjuros" id="parc_cjuros">
                                                <label for="parc_cjuros" class="custom-control-label">Parcelamento
                                                    com Juros:</label>
                                            </div>
                                        </div>
                                        <div class="card-tools">
                                            <button type="button" id="parc_cjuros_coll" class="btn btn-tool"
                                                data-card-widget="collapse">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <label for='parc_jr_deprc'>A Partir da Parcela:*</label>
                                                <input type="number" id='parc_jr_deprc' name='parc_jr_deprc'
                                                    value="{{$empresaParam->parc_jr_deprc}}" class="form-control form-control-sm"
                                                    placeholder='0'>
                                                <span id="parc_jr_deprcError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for='tax_jrsparc'>Taxa de Juros:*</label>
                                                <input type="text" id='tax_jrsparc' name='tax_jrsparc'
                                                    value="{{$empresaParam->tax_jrsparc}}"
                                                    class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                                <span id="tax_jrsparcError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for='parc_com_jrs'>Comissão:*</label>
                                                <input type="text" id='parc_com_jrs' name='parc_com_jrs'
                                                    value="{{$empresaParam->parc_com_jrs}}"
                                                    class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                                <span id="parc_com_jrsError" class="text-danger text-sm"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-md-12">
                                <div class="card card-outline card-primary {{$empresaParam->card_prectr == 'x' ? '' : 'collapsed-card'}} "
                                    id="card_prectr_coll">
                                    <div class="card-header">

                                        <div class="card-title pt-2">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input"
                                                    {{$empresaParam->card_prectr == 'x' ? 'checked' : ''}}
                                                    type="checkbox" name="card_prectr" id="card_prectr">
                                                <label for="card_prectr" class="custom-control-label">Cartão Pré
                                                    Pago Contratado:</label>
                                            </div>
                                        </div>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <label for='tax_pre'>Taxa Pré-Pago:*</label>
                                                <input type="text" id='tax_pre' name='tax_pre'
                                                    value="{{$empresaParam->tax_pre}}"
                                                    class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                                <span id="tax_preError" class="text-danger text-sm"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card card-outline card-primary {{$empresaParam->card_giftctr == 'x' ? '' : 'collapsed-card'}}"
                                    id="card_giftctr_coll">
                                    <div class="card-header">

                                        <div class="card-title pt-2">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input"
                                                    {{$empresaParam->card_giftctr == 'x' ? 'checked' : ''}}
                                                    type="checkbox" name="card_giftctr" id="card_giftctr">
                                                <label for="card_giftctr" class="custom-control-label">Gift Card
                                                    Contratado:</label>
                                            </div>
                                        </div>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <label for='tax_gift'>Taxa Gift Card:*</label>
                                                <input type="text" id='tax_gift' name='tax_gift'
                                                    value="{{$empresaParam->tax_gift}}"
                                                    class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                                <span id="tax_giftError" class="text-danger text-sm"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card card-outline card-primary {{$empresaParam->card_fidctr == 'x' ? '' : 'collapsed-card'}}"
                                    id="card_fidctr_coll">
                                    <div class="card-header">

                                        <div class="card-title pt-2">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input"
                                                    {{$empresaParam->card_fidctr == 'x' ? 'checked' : ''}}
                                                    type="checkbox" name="card_fidctr" id="card_fidctr">
                                                <label for="card_fidctr" class="custom-control-label">Cartão
                                                    Fidelidade Contratado:</label>
                                            </div>
                                        </div>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <label for='tax_fid'>Taxa Fidelidade:*</label>
                                                <input type="text" id='tax_fid' name='tax_fid'
                                                    value="{{$empresaParam->tax_fid}}"
                                                    class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                                <span id="tax_fidError" class="text-danger text-sm"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h5>Programa de pontos</h5>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input"
                                                        {{$empresaParam->pp_particular == 'x' ? 'checked' : ''}}
                                                        type="checkbox" name="pp_particular" id="pp_particular">
                                                    <label for="pp_particular"
                                                        class="custom-control-label">Particular:</label>
                                                    <span id="pp_particularError"
                                                        class="text-danger text-sm"></span>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-3">

                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input"
                                                        {{$empresaParam->pp_franquia == 'x' ? 'checked' : ''}}
                                                        type="checkbox" name="pp_franquia" id="pp_franquia">
                                                    <label for="pp_franquia"
                                                        class="custom-control-label">Franquia:</label>
                                                </div>
                                                <span id="pp_franquiaError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input"
                                                        {{$empresaParam->pp_multmais == 'x' ? 'checked' : ''}}
                                                        type="checkbox" name="pp_multmais" id="pp_multmais">
                                                    <label for="pp_multmais" class="custom-control-label">Multmais Pontos:</label>
                                                </div>
                                                <span id="pp_multmaisError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input"
                                                        {{$empresaParam->pp_cashback == 'x' ? 'checked' : ''}}
                                                        type="checkbox" name="pp_cashback" id="pp_cashback">
                                                    <label for="pp_cashback" class="custom-control-label">Multmais Valor:</label>
                                                </div>
                                                <span id="pp_cashbackError" class="text-danger text-sm"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ANTECIPAÇÃO -->
                    <div class="tab-pane fade" id="antecipacao" role="tabpanel" aria-labelledby="antecipacao-tab">
                        <div class="row">
                            <div class="col-md-12">

                                <div
                                    class="card card-outline card-primary {{$empresaParam->antecip_ctr == 'x' ? '' : 'collapsed-card'}}">
                                    <div class="card-header">
                                        <div class="card-title pt-2">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input"
                                                    {{$empresaParam->antecip_ctr == 'x' ? 'checked' : ''}}
                                                    type="checkbox" name="antecip_ctr" id="antecip_ctr">
                                                <label for="antecip_ctr" class="custom-control-label">Antecipação
                                                    Contratada:</label>
                                            </div>
                                        </div>

                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" id="antecip_ctr_coll"
                                                data-card-widget="collapse">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-3">

                                                <label for='tax_antmult'>Taxa Multban:*</label>
                                                <input type="text" id='tax_antmult' name='tax_antmult'
                                                    value="{{$empresaParam->tax_antmult}}"
                                                    class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                                <span id="tax_antmultError" class="text-danger text-sm"></span>

                                            </div>
                                            <div class="form-group col-md-3">

                                                <label for='tax_antfundo'>Taxa Fundo:*</label>
                                                <input type="text" id='tax_antfundo' name='tax_antfundo'
                                                    value="{{$empresaParam->tax_antfundo}}"
                                                    class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                                <span id="tax_antfundoError" class="text-danger text-sm"></span>

                                            </div>
                                            <div class="form-group col-md-3">

                                                <label for='perc_rec_ant'>% Dos Recebíveis para Antecipar:*</label>
                                                <input type="text" id='perc_rec_ant' name='perc_rec_ant'
                                                    value="{{$empresaParam->perc_rec_ant}}"
                                                    class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                                <span id="perc_rec_antError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label>Em caso de Inadimplência:</label>
                                                <div class="form-group">
                                                    <div class="custom-control custom-radio">
                                                        <input class="custom-control-input"
                                                            {{$empresaParam->inad_descprox == 'x' ? 'checked' : ''}}
                                                            value="inad_descprox" type="radio" id="inad_descprox"
                                                            name="inadimplencia">
                                                        <label for="inad_descprox"
                                                            class="custom-control-label">Descontar dos Próximos
                                                            Recebíveis</label>
                                                    </div>
                                                    <div class="custom-control custom-radio">
                                                        <input class="custom-control-input"
                                                            {{$empresaParam->inad_semrisco == 'x' ? 'checked' : ''}}
                                                            value="inad_semrisco" type="radio" id="inad_semrisco"
                                                            name="inadimplencia">
                                                        <label for="inad_semrisco" class="custom-control-label">Sem
                                                            Risco para a Empresa</label>
                                                    </div>
                                                    <span id="inad_semriscoError"
                                                        class="text-danger text-sm"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card card-outline card-primary">
                                            <div class="card-header">
                                                <div class="card-title">
                                                    <h5>Fundo Parceiro</h5>
                                                </div>
                                            </div>
                                            <div class="card-body">

                                                <div class="form-row">

                                                    <div class="form-group col-md-4">
                                                        <label for="fndant_cdgbc">Cdg Banco:</label>
                                                        <select class="form-control select2" data-allow-clear="true"
                                                            name="fndant_cdgbc" id="fndant_cdgbc"
                                                            data-placeholder="Selecione" style="width: 100%;">
                                                            <option></option>
                                                            @foreach($codigoDosbancos as $key => $emp_cdgbcs)

                                                                <option
                                                                    {{$emp_cdgbcs->cdgbc == $empresaParam->fndant_cdgbc ? 'selected' : ''}} value="{{$emp_cdgbcs->cdgbc}}">
                                                                    {{$emp_cdgbcs->cdgbc}} - {{$emp_cdgbcs->cdgbc_desc}}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <span id="fndant_cdgbcError"
                                                            class="text-danger text-sm"></span>
                                                    </div>

                                                    <div class="col-md-8" id="banco-fndant" style="display: none;">
                                                        <div class="row">
                                                            <div class="form-group col-md-3">
                                                                <label for="fndant_agbc">Agência:</label>
                                                                <input type="text" maxlength="20" id='fndant_agbc'
                                                                    name='fndant_agbc'
                                                                    value="{{$empresaParam->fndant_agbc}}"
                                                                    class="form-control form-control-sm" placeholder='Agência'>
                                                                <span id="fndant_agbcError"
                                                                    class="text-danger text-sm"></span>
                                                            </div>

                                                            <div class="form-group col-md-3">
                                                                <label for="fndant_ccbc">Conta Corrent:</label>
                                                                <input type="text" id='fndant_ccbc'
                                                                    name='fndant_ccbc'
                                                                    value="{{$empresaParam->fndant_ccbc}}"
                                                                    class="form-control form-control-sm"
                                                                    placeholder='Conta Corrente'>
                                                                <span id="fndant_ccbcError"
                                                                    class="text-danger text-sm"></span>
                                                            </div>

                                                            <div class="form-group col-md-3">
                                                                <label for="fndant_pix">Chave PIX:</label>
                                                                <input type="text" id='fndant_pix' name='fndant_pix'
                                                                    value="{{$empresaParam->fndant_pix}}"
                                                                    class="form-control form-control-sm" placeholder='Chave PIX'>
                                                                <span id="fndant_pixError"
                                                                    class="text-danger text-sm"></span>
                                                            </div>

                                                            <div class="form-group col-md-3">
                                                                <label for="fndant_seller">Seller:</label>
                                                                <input type="text" id='fndant_seller'
                                                                    name='fndant_seller'
                                                                    value="{{$empresaParam->fndant_seller}}"
                                                                    class="form-control form-control-sm" placeholder='Seller'>
                                                                <span id="fndant_sellerError"
                                                                    class="text-danger text-sm"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">

                                <div
                                    class="card card-outline card-primary {{$empresaParam->antecip_auto == 'x' ? '' : 'collapsed-card'}}">
                                    <div class="card-header">
                                        <div class="card-title pt-2">
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input"
                                                    {{$empresaParam->antecip_auto == 'x' ? 'checked' : ''}}
                                                    type="checkbox" name="antecip_auto" id="antecip_auto">
                                                <label for="antecip_auto" class="custom-control-label">Antecipação
                                                    Automática:</label>
                                            </div>
                                        </div>

                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" id="antecip_auto_coll"
                                                data-card-widget="collapse">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="card card-outline card-primary">
                                                    <div class="card-header">
                                                        <div class="card-title">
                                                            <h5>VENDA DE SERVIÇOS</h5>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="form-row">
                                                            <div class="form-group col-md-12">
                                                                <label for="ant_auto_srvd">Prazo em dias para
                                                                    liberação*:</label>
                                                                <input type="number" id='ant_auto_srvd'
                                                                    name='ant_auto_srvd'
                                                                    value="{{$empresaParam->ant_auto_srvd}}"
                                                                    class="form-control col-md-4 form-control-sm" placeholder='0'>
                                                                <span id="ant_auto_srvdError"
                                                                    class="text-danger text-sm"></span>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="card card-outline card-primary">
                                                    <div class="card-header">
                                                        <div class="card-title">
                                                            <h5>VENDA DE PRODUTOS</h5>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="form-row">
                                                            <div class="form-group col-md-6">
                                                                <label for="ant_auto_prdvo">Online: Prazo em dias
                                                                    para liberação*:</label>
                                                                <input type="number" id='ant_auto_prdvo'
                                                                    name='ant_auto_prdvo'
                                                                    value="{{$empresaParam->ant_auto_prdvo}}"
                                                                    class="form-control col-md-4 form-control-sm" placeholder='0'>
                                                                <span id="ant_auto_prdvoError"
                                                                    class="text-danger text-sm"></span>
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label for="ant_auto_prdvd">Presencial: Prazo em
                                                                    dias para liberação*:</label>
                                                                <input type="number" id='ant_auto_prdvd'
                                                                    name='ant_auto_prdvd'
                                                                    value="{{$empresaParam->ant_auto_prdvd}}"
                                                                    class="form-control col-md-4 form-control-sm" placeholder='0'>
                                                                <span id="ant_auto_prdvdError"
                                                                    class="text-danger text-sm"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <div class="custom-control custom-checkbox ml-4 p-3">
                                    <input class="custom-control-input" {{$empresaParam->ant_blktit == 'x' ? 'checked' : ''}} type="checkbox" name="ant_blktit" id="ant_blktit">
                                    <label for="ant_blktit" class="custom-control-label">Bloquear título após
                                        antecipação:</label>
                                    <span id="ant_blktitError" class="text-danger text-sm"></span>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <div class="custom-control custom-checkbox p-3">
                                    <input class="custom-control-input" {{$empresaParam->ant_titpdv == 'x' ? 'checked' : ''}} type="checkbox" name="ant_titpdv" id="ant_titpdv">
                                    <label for="ant_titpdv" class="custom-control-label">Antecipar apenas Títulos
                                        Validados pelo PDV Web:</label>

                                    <span id="ant_titpdvError" class="text-danger text-sm"></span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- REBATE / ROYALTIES / COMISSÕES -->
                    <div class="tab-pane fade" id="rebate" role="tabpanel" aria-labelledby="rebate-tab">
                        <div class="row">

                            <div class="col-md-12">

                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h5>RABATE PARA</h5>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label for="rebate_emp">Loja beneficiada:</label>
                                                <select class="form-control select2" data-allow-clear="true"
                                                    name="rebate_emp" id="rebate_emp"
                                                    class="form-control select2 select2-hidden-accessible"
                                                    data-placeholder="Pesquise a Empresa" style="width: 100%;"
                                                    aria-hidden="true">
                                                    @if ($rebateLoja)
                                                        <option value="{{$rebateLoja->emp_id}}">{{$rebateLoja->emp_id}}
                                                            - {{$rebateLoja->emp_rzsoc}}</option>
                                                    @endif
                                                </select>
                                                <span id="rebate_empError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-4">

                                                <label for='tax_rebate'>% pago sobre as taxas aplicadas:*</label>
                                                <input type="text" id='tax_rebate' name='tax_rebate'
                                                    value="{{$empresaParam->tax_rebate}}"
                                                    class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                                <span id="tax_rebateError" class="text-danger text-sm"></span>

                                            </div>

                                            <div class="form-group col-md-4">
                                                <label>Pagar Por:</label>
                                                <div class="form-group">
                                                    <div class="custom-control custom-radio">
                                                        <input class="custom-control-input"
                                                            {{$empresaParam->rebate_split == 'x' ? 'checked' : ''}}
                                                            type="radio" id="rebate_split" value="rebate_split"
                                                            name="pagar_por">
                                                        <label for="rebate_split"
                                                            class="custom-control-label">Split</label>
                                                    </div>
                                                    <div class="custom-control custom-radio">
                                                        <input class="custom-control-input"
                                                            {{$empresaParam->rebate_transf == 'x' ? 'checked' : ''}}
                                                            type="radio" id="rebate_transf" value="rebate_transf"
                                                            name="pagar_por">
                                                        <label for="rebate_transf"
                                                            class="custom-control-label">Transferência
                                                            Bancária</label>
                                                    </div>
                                                    <span id="rebate_transfError"
                                                        class="text-danger text-sm"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h5>ROYALTIES PARA</h5>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label for="royalties_emp">Loja beneficiada:</label>
                                                <select class="form-control select2" data-allow-clear="true"
                                                    name="royalties_emp" id="royalties_emp"
                                                    class="form-control select2 select2-hidden-accessible"
                                                    data-placeholder="Pesquise a Empresa" style="width: 100%;"
                                                    aria-hidden="true">
                                                    @if ($royaltiesLoja)
                                                        <option value="{{$royaltiesLoja->emp_id}}">
                                                            {{$royaltiesLoja->emp_id}} - {{$royaltiesLoja->emp_rzsoc}}
                                                        </option>
                                                    @endif
                                                </select>
                                                <span id="royalties_empError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-4">

                                                <label for='tax_royalties'>% pago sobre as taxas aplicadas:*</label>
                                                <input type="text" id='tax_royalties' name='tax_royalties'
                                                    value="{{$empresaParam->tax_royalties}}"
                                                    class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                                <span id="tax_royaltiesError" class="text-danger text-sm"></span>

                                            </div>

                                            <div class="form-group col-md-4">
                                                <label>Pagar Por:</label>
                                                <div class="form-group">
                                                    <div class="custom-control custom-radio">
                                                        <input class="custom-control-input"
                                                            {{$empresaParam->royalties_split == 'x' ? 'checked' : ''}}
                                                            value="royalties_split" type="radio"
                                                            id="royalties_split" name="royalties_paghar_por">
                                                        <label for="royalties_split"
                                                            class="custom-control-label">Split</label>
                                                    </div>
                                                    <div class="custom-control custom-radio">
                                                        <input class="custom-control-input"
                                                            {{$empresaParam->royalties_transf == 'x' ? 'checked' : ''}} type="radio" id="royalties_transf"
                                                            value="royalties_transf" name="royalties_paghar_por">
                                                        <label for="royalties_transf"
                                                            class="custom-control-label">Transferência
                                                            Bancária</label>
                                                    </div>
                                                    <span id="royalties_transfError"
                                                        class="text-danger text-sm"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">

                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h5>COMISSÃO PARA</h5>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label for="comiss_emp">Loja beneficiada:</label>
                                                <select class="form-control select2" data-allow-clear="true"
                                                    name="comiss_emp" id="comiss_emp" data-placeholder="Selecione"
                                                    class="form-control select2 select2-hidden-accessible"
                                                    data-placeholder="Pesquise a Empresa" style="width: 100%;"
                                                    aria-hidden="true">
                                                    @if ($comissaoLoja)
                                                        <option value="{{$comissaoLoja->emp_id}}">
                                                            {{$comissaoLoja->emp_id}} - {{$comissaoLoja->emp_rzsoc}}
                                                        </option>
                                                    @endif
                                                </select>
                                                <span id="comiss_empError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for='tax_comiss'>% pago sobre as taxas aplicadas:*</label>
                                                <input type="text" id='tax_comiss' name='tax_comiss'
                                                    value="{{$empresaParam->tax_comiss}}"
                                                    class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                                <span id="tax_comissError" class="text-danger text-sm"></span>

                                            </div>

                                            <div class="form-group col-md-4">
                                                <label>Pagar Por:</label>
                                                <div class="form-group">
                                                    <div class="custom-control custom-radio">
                                                        <input class="custom-control-input"
                                                            {{$empresaParam->comiss_split == 'x' ? 'checked' : ''}}
                                                            type="radio" id="comiss_split" value="comiss_split"
                                                            name="comissao_paghar_por">
                                                        <label for="comiss_split"
                                                            class="custom-control-label">Split</label>
                                                    </div>
                                                    <div class="custom-control custom-radio">
                                                        <input class="custom-control-input" type="radio"
                                                            id="comiss_transf" value="comiss_transf"
                                                            name="comissao_paghar_por">
                                                        <label for="comiss_transf"
                                                            class="custom-control-label">Transferência
                                                            Bancária</label>
                                                    </div>
                                                    <span id="comissao_paghar_porError"
                                                        class="text-danger text-sm"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- COBRANÇA -->
                    <div class="tab-pane fade" id="cobrancas" role="tabpanel" aria-labelledby="cobrancas-tab">

                        <div class="card card-outline card-primary {{$empresaParam->cobsrv_atv == 'x' ? '' : 'collapsed-card'}}">
                            <div class="card-header">
                                <div class="card-title pt-2">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" {{$empresaParam->cobsrv_atv == 'x' ? 'checked' : ''}} type="checkbox" name="cobsrv_atv" id="cobsrv_atv">
                                        <label for="cobsrv_atv" class="custom-control-label">Ativar serviço de
                                            cobrança:</label>
                                    </div>
                                </div>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" id="cobsrv_atv_coll"
                                        data-card-widget="collapse">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for='cobsrv_diasatr'>Dias de atraso para Iniciar fluxo de
                                            cobrança:*</label>
                                        <input type="number" id='cobsrv_diasatr' name='cobsrv_diasatr'
                                            value="{{$empresaParam->cobsrv_diasatr}}" class="form-control form-control-sm"
                                            placeholder='0'>
                                        <span id="cobsrv_diasatrError" class="text-danger text-sm"></span>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for='cobsrv_multa'>Percentual de Multa:*</label>
                                        <input type="text" id='cobsrv_multa' name='cobsrv_multa'
                                            value="{{$empresaParam->cobsrv_multa}}" class="form-control porcentagem form-control-sm"
                                            placeholder='0,00'>
                                        <span id="cobsrv_multaError" class="text-danger text-sm"></span>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for='cobsrv_juros'>Percentual de Juros:*</label>
                                        <input type="text" id='cobsrv_juros' name='cobsrv_juros'
                                            value="{{$empresaParam->cobsrv_juros}}" class="form-control porcentagem form-control-sm"
                                            placeholder='0,00'>
                                        <span id="cobsrv_jurosError" class="text-danger text-sm"></span>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for='tax_cobsrv_adm'>Taxa Administrativa:*</label>
                                        <input type="text" id='tax_cobsrv_adm' name='tax_cobsrv_adm'
                                            value="{{$empresaParam->tax_cobsrv_adm}}"
                                            class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                        <span id="tax_cobsrv_admError" class="text-danger text-sm"></span>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for='tax_cobsrv_juss'>Taxa Jurídica:*</label>
                                        <input type="text" id='tax_cobsrv_juss' name='tax_cobsrv_juss'
                                            value="{{$empresaParam->tax_cobsrv_juss}}"
                                            class="form-control porcentagem form-control-sm" placeholder='0,00'>
                                        <span id="tax_cobsrv_jussError" class="text-danger text-sm"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- COMUNICAÇÃO -->
                    <div class="tab-pane fade" id="comunicacao" role="tabpanel" aria-labelledby="comunicacao-tab">

                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <div class="card-title">
                                    <h5>Carregue sua Logo</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Preview da imagem -->
                                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                                        <div id="logo-preview" style="width: 120px; height: 120px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center;">
                                            @if(isset($empresaGeral->logo_path) && $empresaGeral->logo_path)
                                                <img src="{{ asset('storage/' . $empresaGeral->logo_path) }}" alt="Logo" style="max-width: 100%; max-height: 100%;">
                                            @else
                                                <span style="font-size: 0.9rem;">Pré-visualização</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Seleção do Arquivo -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="logoFile">Selecione o arquivo:</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control-file" id="logoFile" name="logoFile" accept=".jpg,.jpeg,.pdf,.png,.img" onchange="previewLogo(event)">
                                                <div class="input-group-append" style="width:100%">
                                                    <input type="text" class="form-control" id="logoFileName" readonly placeholder="Nenhum arquivo selecionado">
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">Apenas arquivos .jpg, .jpeg, .pdf, .png, .img</small>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h5>Integração: WhatsApp Comercial</h5>
                                        </div>
                                    </div>
                                    <div class="card-body text-center">
                                        <!-- Exemplo de QRCode para WhatsApp -->
                                        <p>Escaneie para configurar seu Canal de Comunicação:</p>
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=https://wa.me/5511999999999" alt="QR Code WhatsApp" style="width:180px;height:180px;">
                                        <small class="form-text text-muted">Exemplo: https://wa.me/5511999999999</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h5>Integração: WhatsApp Cobrança</h5>
                                        </div>
                                    </div>
                                    <div class="card-body text-center">
                                        <!-- Exemplo de QRCode para WhatsApp -->
                                        <p>Escaneie para configurar seu Canal de Cobrança:</p>
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=https://wa.me/5511999999999" alt="QR Code WhatsApp" style="width:180px;height:180px;">
                                        <small class="form-text text-muted">Exemplo: https://wa.me/5511999999999</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
            <!-- /.card -->
        </div>
    </form>

</section>

<div class="modal fade" id="pesquisa-empresa-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Informação</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Digite o CNPJ ou o nome da empresa.</p>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <select id="emp_id" name="emp_id" autofocus="autofocus"
                            class="form-control select2 select2-hidden-accessible"
                            data-placeholder="Digite o CNPJ ou o nome do empresa" style="width: 100%;"
                            aria-hidden="true">
                            <option></option>
                        </select>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success pull-right" id="btnSearchEmpresa" data-dismiss="modal"><i
                        class="fa fa-check"></i>
                    OK</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
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
<!-- /.content -->

@endsection

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function (e) {

            setTimeout(() => {
                listaTaxa = {{$empresaTaxpos->count() + 2}};

            }, 1000);


            @if ($empresaParam->emp_cdgbc)
                $("#banco-principal").show();
            @endif

            @if ($empresaParam->emp_cdgbcs)
                $("#banco-secundario").show();
            @endif

            @if ($empresaParam->fndant_cdgbc)
                $("#banco-fndant").show();
            @endif

            @if ($empresaGeral->emp_checkb != "x")
                $("#emp_tpbolet").prop("disabled", true);
            @endif

            @if ($empresaGeral->emp_checkm != "x")
                $("#tp_plano").prop("disabled", true);

            @endif

            @if ($empresaGeral->emp_checkc != "x")
                $("#emp_adqrnt").prop("disabled", true);
            @endif

            ns.iniciarlizarMascaras();
            bsCustomFileInput.init();
            $('#message').css('display', 'none');

            $('#dtvenc_imp').datetimepicker({
                format: 'DD/MM/YYYY',
                locale: 'pt-br'
            });
            $('#dtvenc_mens').datetimepicker({
                format: 'DD/MM/YYYY',
                locale: 'pt-br'
            });

        });

        function previewLogo(event) {
            const input = event.target;
            const preview = document.getElementById('logo-preview');
            const fileNameField = document.getElementById('logoFileName');
            if (input.files && input.files[0]) {
                fileNameField.value = input.files[0].name;
                const file = input.files[0];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = '<img src="' + e.target.result + '" alt="Logo" style="max-width: 100%; max-height: 100%;">';
                    }
                    reader.readAsDataURL(file);
                } else {
                    preview.innerHTML = '<span>Arquivo selecionado não é uma imagem</span>';
                }
            } else {
                fileNameField.value = '';
                preview.innerHTML = '<span>Pré-visualização</span>';
            }
        }

    </script>

    <script src="{{ asset('assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-validation/localization/messages_pt_BR.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/i18n/pt-BR.js') }}"></script>
    <script src="{{ asset('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <!-- Moment -->
    <script src="{{ asset('assets/plugins/moment/moment-with-locales.js') }}"></script>
    <script src="{{ asset('assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('assets/plugins/inputmask/min/jquery.inputmask.bundle.min.js') }}"></script>
    <!-- Summernote -->
    <script src="{{ asset('assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/dist/css/app.css') }}" />
    <script src="{{ asset('assets/dist/js/app.js') }}"></script>
    <script src="{{ asset('assets/dist/js/pages/empresa/empresa.js') }}"></script>
@endpush
