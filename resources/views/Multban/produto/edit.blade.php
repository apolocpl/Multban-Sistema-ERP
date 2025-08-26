@extends('layouts.app-master')
@section('page.title', 'Produtos')
@push('script-head')
<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<!-- Main content -->
<section class="content">

    @if($routeAction)
    <form class="form-horizontal" id="formPrincipal" role="form" method="POST"
        action="{{ route('produto.update', $produto->produto_id) }}">
        @method('PATCH')

    @else
    <form class="form-horizontal" id="formPrincipal" role="form" method="POST"
        action="{{ route('produto.store') }}">
        @method('POST')
        @endif

        <!-- FAIXA DE OPÇÕES SALVAR / INATIVAR / EXCLUIR / VOLTAR -->
        @include('Multban.template.updatetemplate')

        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <!--ABA/TAB-->
                <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                    <li class="nav-item"><a class="nav-link active" id="tabs-dados-tab" data-toggle="pill" href="#tabs-dados" role="tab" aria-controls="tabs-dados" aria-selected="true">Dados Gerais</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="custom-tabs-two-tabContent">

                    <div class="tab-pane fade active show" id="tabs-dados" role="tabpanel" aria-labelledby="tabs-dados-tab">

                        <div class="card card-primary">

                            <div class="card-body">

                                <!-- PRIMEIRA LINHA DE DADOS -->
                                <div class="form-row">

                                    <div class="form-group col-md-4">
                                        <label for="emp_id">Empresa:</label>
                                        <div class="input-group input-group-sm">
                                            <input autocomplete="off" type="text" class="form-control form-control-sm" id="emp_id" name="emp_id"
                                                   value="{{ $empresaDesc ?? $produto->emp_id }}" placeholder="Empresa" readonly>
                                        </div>
                                        <span id="emp_idError" class="text-danger text-sm"></span>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label for="produto_id">Código do Produto:</label>
                                        <div class="input-group input-group-sm">
                                            <input autocomplete="off" type="text" class="form-control  form-control-sm" id="produto_id" name="produto_id"
                                                   value="{{ $produto->produto_id }}" placeholder="Código do Produto" readonly>
                                        </div>
                                            <span id="produto_idError" class="text-danger text-sm"></span>
                                    </div>

                                </div>

                                <!-- SEGUNDA LINHA DE DADOS -->
                                <div class="form-row">

                                    <div class="form-group col-md-2">
                                        <label for="dthr_cr">Data de Cadastro:</label>
                                        <input autocomplete="off" readonly class="form-control  form-control-sm"
                                               value="{{ $produto->dthr_cr }}" placeholder="Data de cadastro" name="dthr_cr" type="text" id="dthr_cr">
                                            <span id="dthr_crError" class="text-danger text-sm"></span>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label for="dthr_ch">Última atualização:</label>
                                        <input autocomplete="off" readonly class="form-control  form-control-sm"
                                               value="{{ $produto->dthr_ch }}" placeholder="Última atualização" name="dthr_ch" type="text" id="dthr_ch">
                                            <span id="dthr_chError" class="text-danger text-sm"></span>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label for="produto_tipo">Tipo de Produto:</label>
                                        <div class="input-group input-group-sm">
                                            <select class="form-control select2" id="produto_tipo" name="produto_tipo" data-placeholder="Selecione o Tipo" style="width: 100%;">
                                                <option value="">Selecione...</option>
                                                @foreach($tipos as $tipo)
                                                    <option value="{{ $tipo->produto_tipo }}" {{ (isset($produto) && $produto->produto_tipo == $tipo->produto_tipo) ? 'selected' : '' }}>{{ $tipo->produto_tipo_desc ?? $tipo->produto_tipo }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                            <span id="produto_tipoError" class="text-danger text-sm"></span>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label for="produto_sts">Status:</label>
                                        <div class="input-group input-group-sm">
                                            <select class="form-control select2" id="produto_sts" name="produto_sts" data-placeholder="Selecione o Status" style="width: 100%;">
                                                <option value="">Selecione...</option>
                                                @foreach($status as $sts)
                                                    <option value="{{ $sts->produto_sts }}" {{ (isset($produto) && $produto->produto_sts == $sts->produto_sts) ? 'selected' : '' }}>{{ $sts->produto_sts_desc ?? $sts->produto_sts }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                            <span id="produto_stsError" class="text-danger text-sm"></span>
                                    </div>

                                </div>

                                <!-- TERCEIRA LINHA DE DADOS -->
                                <div class="form-row">

                                    <div class="form-group col-md-2">
                                        <label for="produto_dc">Descrição Curta:</label>
                                        <div class="input-group input-group-sm">
                                            <input autocomplete="off" type="text" class="form-control  form-control-sm" id="produto_dc" name="produto_dc"
                                                   value="{{ $produto->produto_dc }}" placeholder="Descrição Curta" required maxlength="15">
                                        </div>
                                            <span id="produto_dcError" class="text-danger text-sm"></span>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="produto_dm">Descrição Média:</label>
                                        <div class="input-group input-group-sm">
                                            <input autocomplete="off" type="text" class="form-control  form-control-sm" id="produto_dm" name="produto_dm"
                                                   value="{{ $produto->produto_dm }}" placeholder="Descrição Média" required maxlength="100">
                                        </div>
                                            <span id="produto_dmError" class="text-danger text-sm"></span>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label for="produto_vlr">Preço de Venda:</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">R$</span>
                                            </div>
                                            <input type="text" class="form-control  form-control-sm" id="produto_vlr" name="produto_vlr"
                                                   value="{{ $produto->produto_vlr }}" placeholder="Valor de Venda" required>
                                        </div>
                                            <span id="produto_vlrError" class="text-danger text-sm"></span>
                                    </div>

                                </div>

                                <!-- QUARTA LINHA DE DADOS -->
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="produto_dl">Descrição Longa:</label>
                                        <div class="input-group input-group-sm">
                                            <input autocomplete="off" type="text" class="form-control  form-control-sm" id="produto_dl" name="produto_dl"
                                                   value="{{ $produto->produto_dl }}" placeholder="Descrição Longa" required maxlength="255">
                                        </div>
                                            <span id="produto_dlError" class="text-danger text-sm"></span>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="produto_dt">Descrição Técnica:</label>
                                        <div class="input-group input-group-sm">
                                            <input autocomplete="off" type="text" class="form-control  form-control-sm" id="produto_dt" name="produto_dt"
                                                   value="{{ $produto->produto_dt }}" placeholder="Descrição Técnica" maxlength="255">
                                        </div>
                                            <span id="produto_dtError" class="text-danger text-sm"></span>
                                    </div>
                                </div>

                                <!-- Campos Adicionais -->
                                <div id="camposAdicionais" style="display: none;">

                                    <!-- Campos para Participante -->
                                    <div id="camposParticipante" style="display: none;">
                                        <div class="row" style="margin-bottom: 1px;">
                                            <div class="form-group col-md-2">
                                                <label for="partcp_pvlaor">% de Participação:</label>
                                                <input type="text" class="form-control form-control-sm" id="partcp_pvlaor" name="partcp_pvlaor"
                                                    value="{{ isset($produto->partcp_pvlaor) ? number_format($produto->partcp_pvlaor, 2, ',', '.') : '' }}" placeholder="00,00" maxlength="6">
                                                <span id="partcp_pvlaorError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for="empresa_id">Empresa do Participante:</label>
                                                <select id="empresa_id" name="empresa_id" class="form-control select2 select2-hidden-accessible"
                                                    value="{{ $produto->partcp_empid }}" data-placeholder="Pesquise a Empresa" style="width: 100%;" aria-hidden="true" required>
                                                </select>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for="partcp_seller">Id de Integração - Seller:</label>
                                                <input type="text" class="form-control  form-control-sm" id="partcp_seller" name="partcp_seller"
                                                    value="{{ $produto->partcp_seller }}" placeholder="Id de Integração">
                                                    <span id="partcp_sellerError" class="text-danger text-sm"></span>
                                            </div>
                                        </div>

                                        <div class="row" style="margin-bottom: 1px;">
                                            <div class="form-group col-md-3">
                                                <label>Pagar Por:</label><br>
                                                <div class="form-check form-check-inline">
                                                    <input type="radio" class="form-check-input" id="partcp_pgsplit" name="pagarPor"
                                                           value="partcp_pgsplit" {{ (!empty($produto->partcp_pgsplit) || (isset($produto->pagarPor) && $produto->pagarPor == 'partcp_pgsplit')) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="partcp_pgsplit">Split</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input type="radio" class="form-check-input" id="partcp_pgtransf" name="pagarPor"
                                                           value="partcp_pgtransf" {{ (!empty($produto->partcp_pgtransf) || (isset($produto->pagarPor) && $produto->pagarPor == 'partcp_pgtransf')) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="partcp_pgtransf">Transferência Bancária</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row" style="margin-bottom: 1px;">
                                            <div class="form-group col-md-3" id="campoBanco" style="display: none;">
                                                <label for="partcp_cdgbc">Cdg Banco:</label>
                                                <select class="form-control select2" id="partcp_cdgbc" name="partcp_cdgbc" data-placeholder="Selecione o Banco" style="width: 100%;">
                                                    <option></option>
                                                        @foreach($bancos as $banco)
                                                            <option value="{{ $banco->cdgbc }}" {{ (isset($produto) && $produto->partcp_cdgbc == $banco->cdgbc) ? 'selected' : '' }}>
                                                                {{ $banco->cdgbc }} - {{ $banco->cdgbc_desc }}
                                                            </option>
                                                        @endforeach
                                                </select>
                                                    <span id="partcp_cdgbcError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-1" id="campoAgencia" style="display: none;">
                                                <label for="partcp_agbc">Agência:</label>
                                                <input type="text" class="form-control  form-control-sm" id="partcp_agbc" name="partcp_agbc"
                                                       value="{{ $produto->partcp_agbc }}" placeholder="Agência">
                                                    <span id="partcp_agbcError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-1" id="campoConta" style="display: none;">
                                                <label for="partcp_ccbc">Conta:</label>
                                                <input type="text" class="form-control  form-control-sm" id="partcp_ccbc" name="partcp_ccbc"
                                                       value="{{ $produto->partcp_ccbc }}" placeholder="Conta">
                                                    <span id="partcp_ccbcError" class="text-danger text-sm"></span>
                                            </div>

                                            <div class="form-group col-md-3" id="campoChavePix" style="display: none;">
                                                <label for="partcp_pix">Chave PIX:</label>
                                                <input type="text" class="form-control  form-control-sm" id="partcp_pix" name="partcp_pix"
                                                       value="{{ $produto->partcp_pix }}" placeholder="Chave PIX" maxlength="100">
                                                    <span id="partcp_pixError" class="text-danger text-sm"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campos para Produto-->
                                    <div id="camposProduto" style="display: none;">
                                        <div class="row">
                                            <div class="form-group col-md-2">
                                                <label for="produto_ncm">NCM:</label>
                                                <input type="text" class="form-control  form-control-sm" id="produto_ncm" name="produto_ncm"
                                                       value="{{ $produto->produto_ncm }}" placeholder="NCM" required maxlength="10">
                                                    <span id="produto_ncmError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="produto_peso">Peso em gr:</label>
                                                <input type="text" class="form-control  form-control-sm" id="produto_peso" name="produto_peso"
                                                       value="{{ $produto->produto_peso }}" placeholder="Peso em gr" required >
                                                    <span id="produto_pesoError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="produto_cdgb">QR Code:</label>
                                                <input type="text" class="form-control  form-control-sm" id="produto_cdgb" name="produto_cdgb"
                                                       value="{{ $produto->produto_cdgb }}" placeholder="Digite o QR Code" required maxlength="255">
                                                    <span id="produto_cdgbError" class="text-danger text-sm"></span>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label>Controlar Estoque:</label><br>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="produto_ctrl" name="produto_ctrl"
                                                           value="{{ $produto->produto_ctrl }}">
                                                    <label class="form-check-label" for="produto_ctrl">Sim</label>
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
        </div>
    </form>

</section>

<!-- /.content -->
@endsection

@push('scripts')

<script type="text/javascript">
    $(document).ready(function () {

        // Preencher empresa do participante ao editar
        var empresaPartId = '{{ $produto->partcp_empid }}';
        var empresaPartOption = @json($empresaPartOption ?? null);
        if (empresaPartId && empresaPartOption) {
            var $empresaSelect = $('#empresa_id');
            if ($empresaSelect.find("option[value='" + empresaPartId + "']").length === 0) {
                $empresaSelect.append(new Option(empresaPartOption.text, empresaPartOption.id, true, true));
            }
            $empresaSelect.val(empresaPartId).trigger('change');
        }

        $(".alert-dismissible")
            .fadeTo(10000, 500)
            .slideUp(500, function() {
                $(".alert-dismissible").alert("close");
            });

        // Máscara valor
        $('#produto_vlr').mask('#.##0,00', {reverse: true});
        // Máscara peso
        $('#produto_peso').mask('#.##0,00', {reverse: true});
        // Máscara percentual participação igual campo valor
        $('#partcp_pvlaor').mask('#.##0,00', {reverse: true});

        // Regras de exibição dos campos adicionais
        $('#produto_tipo').change(function() {

            @if (!$routeAction) // Somente na criação
                $('#produto_ncm').val('');
                $('#produto_peso').val('');
                $('#produto_cdgb').val('');
                $('#produto_ctrl').prop('checked', false);
                $('#partcp_pvlaor').val('');
                $('#partcp_seller').val('');
                $('#partcp_pgsplit').prop('checked', false);
                $('#partcp_pgtransf').prop('checked', false);
                $('#partcp_cdgbc').val('').trigger('change');
                $('#partcp_agbc').val('');
                $('#partcp_ccbc').val('');
                $('#partcp_pix').val('');
            @endif

            $('#camposParticipante').hide();
            $('#camposProduto').hide();
            $('#camposAdicionais').hide();

            var tipoSelecionado = $(this).val();

            if (tipoSelecionado === '3') {

                $('#camposParticipante').show();
                $('#camposAdicionais').show();

            } else if (tipoSelecionado === '1') {

                $('#camposProduto').show();
                $('#camposAdicionais').show();

            } else {
                $('#camposParticipante').hide();
                $('#camposProduto').hide();
                $('#camposAdicionais').hide();
            }

        });

        $('input[name="pagarPor"]').change(function() {
            var valorSelecionado = $(this).val();
            if (valorSelecionado === 'partcp_pgtransf') {
                $('#campoBanco').show();
                $('#campoAgencia').show();
                $('#campoConta').show();
                $('#campoChavePix').show();
            } else {
                $('#campoBanco').hide();
                $('#campoAgencia').hide();
                $('#campoConta').hide();
                $('#campoChavePix').hide();
            }
        });

        // Validação dinâmica dos campos obrigatórios
        $('#formPrincipal').submit(function(e) {
            // Limpa mensagens anteriores
            var campos = [
                'produto_dc', 'produto_dm', 'produto_dl', 'produto_vlr', 'produto_tipo', 'produto_sts',
                'produto_ncm', 'produto_peso', 'partcp_pvlaor', 'partcp_seller', 'partcp_cdgbc', 'partcp_agbc', 'partcp_ccbc', 'partcp_pix'
            ];
            campos.forEach(function(campo) {
                $('#' + campo + 'Error').text('');
            });

            var erros = 0;
            // Sempre obrigatórios
            if (!$('#produto_dc').val()) { $('#produto_dcError').text('Descrição Curta é obrigatória.'); erros++; }
            if (!$('#produto_dm').val()) { $('#produto_dmError').text('Descrição Média é obrigatória.'); erros++; }
            if (!$('#produto_dl').val()) { $('#produto_dlError').text('Descrição Longa é obrigatória.'); erros++; }
            if (!$('#produto_vlr').val()) { $('#produto_vlrError').text('Preço de Venda é obrigatório.'); erros++; }

            var tipo = $('#produto_tipo').val();
            if (!tipo) { $('#produto_tipoError').text('Tipo de Produto é obrigatório.'); erros++; }
            if (!$('#produto_sts').val()) { $('#produto_stsError').text('Status é obrigatório.'); erros++; }

            if (tipo === '1') {
                if (!$('#produto_ncm').val()) { $('#produto_ncmError').text('NCM é obrigatório para Produto.'); erros++; }
                if (!$('#produto_peso').val()) { $('#produto_pesoError').text('Peso é obrigatório para Produto.'); erros++; }
            }
            if (tipo === '3') {
                if (!$('#partcp_pvlaor').val()) { $('#partcp_pvlaorError').text('% de Participação é obrigatório para Participante.'); erros++; }
            }

            var pagarPor = $('input[name="pagarPor"]:checked').val();
            if (pagarPor === 'partcp_pgsplit') {
                if (!$('#partcp_seller').val()) { $('#partcp_sellerError').text('Id de Integração - Seller é obrigatório para Split.'); erros++; }
            }
            if (pagarPor === 'partcp_pgtransf') {
                if (!$('#partcp_cdgbc').val()) { $('#partcp_cdgbcError').text('Cdg Banco é obrigatório para Transferência.'); erros++; }
                if (!$('#partcp_agbc').val()) { $('#partcp_agbcError').text('Agência é obrigatória para Transferência.'); erros++; }
                if (!$('#partcp_ccbc').val()) { $('#partcp_ccbcError').text('Conta é obrigatória para Transferência.'); erros++; }
                if (!$('#partcp_pix').val()) { $('#partcp_pixError').text('Chave PIX é obrigatória para Transferência.'); erros++; }
            }

            if (erros > 0) {
                e.preventDefault();
                return false;
            }
        });

        // Chama função de abertura de tela por tipo de produto selecionado
        @if($routeAction)
            $('#produto_tipo').trigger('change');
            $('input[name="pagarPor"]').trigger('change');
                // Estado inicial dos campos bancários
                var pagarPorInicial = $('input[name="pagarPor"]:checked').val();
                if (pagarPorInicial !== 'partcp_pgtransf') {
                    $('#campoBanco').hide();
                    $('#campoAgencia').hide();
                    $('#campoConta').hide();
                    $('#campoChavePix').hide();
                }
        @endif

    });
</script>
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/i18n/pt-BR.js') }}"></script>
<script src="{{ asset('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<!-- InputMask -->
<script src="{{ asset('assets/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/inputmask/min/jquery.inputmask.bundle.min.js') }}"></script>
<!-- Summernote -->
<script src="{{ asset('assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
<link rel="stylesheet" href="{{asset('assets/dist/css/app.css') }}" />
<script src="{{asset('assets/dist/js/app.js') }}"></script>
<script src="{{ asset('assets/dist/js/pages/produto/gridproduto.js') }}"></script>

@endpush
