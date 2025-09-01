@extends('layouts.app-master-pdv')
@section('page.title', 'PDV - Venda')
@push('script-head')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}"  />
    <link rel="stylesheet" href="{{ asset('assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-select/css/select.bootstrap4.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-fixedheader/css/fixedHeader.bootstrap4.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}" />
@endpush

@section('content')
    <iframe id="iframe_impressao" src="" style="display:none"></iframe>

    <!-- Main content -->
    <section class="content">

        <div class="container-fluid">

            <input id="idempresa" name="idempresa" value="{{auth()->user()->idempresafilial}}" type="hidden" />
            <input id="orcamento" name="orcamento" value="0" type="hidden" />
            <input id="pedidoID" name="pedidoID" value="" type="hidden" />
            <input id="idclientevendedor" name="idclientevendedor" value="{{auth()->user()->id}}" type="hidden" />
            <input id="faturar" name="faturar" value="0" type="hidden" />
            @method('POST')
            @csrf

            <!-- CONTEÚDO PRINCIPAL -->
            <div class="row align-items-start pt-3 d-flex" id="listaDeProdutos">

                <!-- COLUNA ESQUERDA -->
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6 h-100" style="height: 100%; min-height: 100%; overflow: auto;">
                    <!-- HEADER DA SESSÃO -->
                    <div class="card card-widget widget-user-2 shadow">
                        <div class="widget-user-header bg-primary">
                            <div class="widget-user-image">
                            <img class="img-circle elevation-2"
                            src="{{url('/assets/dist/img/') . '/' . 'logo-amarela-min.png'}}"
                            alt="">
                        </div>
                            <h2 class="widget-user-username">PDV</h2>
                            <h5 class="widget-user-desc">{{$empresa->emp_rzsoc}}</h5>
                        </div>
                    </div>

                    <!-- INFORMAÇÕES DO CLIENTE E SELEÇÃO DE ITENS -->
                    <div class="card card-outline card-primary">
                        <div class="card-body p-2">

                            <!-- QUADRO DE SELEÇÃO DE CLIENTE -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header p-2">
                                            <label for="getCliente">CPF / CNPJ do Cliente: (F6)</label>
                                            <div class="input-group mb-3 input-group-sm">
                                                <span class="input-group-append">
                                                    <button type="button" id="btnPesquisarCliente"
                                                        class="btn btn-default btn-lg"><i class="fa fa-search"></i></button>
                                                </span>
                                                <input autocomplete="off" type="text" autofocus="autofocus"
                                                    class="form-control form-control-lg" id="getCliente" name="getCliente"
                                                    value="" placeholder="CPF / CNPJ do Cliente">
                                            </div>
                                        </div>
                                        <div class="card-footer p-2">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <span class="text-bold">Cliente:</span> <span id="desCli"></span>
                                                </div>
                                            </div>
                                            @if(isset($empresa) && !empty($empresa->emp_reemb))
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" type="checkbox" name="blt_ctr" id="blt_ctr">
                                                        <label for="blt_ctr" class="custom-control-label">Procedimento de Reembolso:</label>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- QUADRO DE SELEÇÃO DE PRODUTOS -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card">

                                        <div class="card-header p-2">
                                            <label for="getProduto">Código do produto: (F1)</label>
                                            <div class="input-group mb-3 input-group-sm">
                                                <span class="input-group-append">
                                                    <button type="button" id="btnPesquisarProduto"
                                                        class="btn btn-default btn-lg"><i class="fa fa-search"></i></button>
                                                </span>
                                                <input autocomplete="off" type="text" autofocus="autofocus"
                                                    class="form-control form-control-lg" id="getProduto" name="getProduto"
                                                    value="" placeholder="Código do produto">
                                            </div>
                                        </div>

                                        <div class="card-footer p-2">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <span class="text-bold">Descrição:</span> <span id="desProd"></span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="form-row p-2">
                                <div class="form-group col">
                                    <label>Quantidade: (F2)</label>
                                    <input autocomplete="off" type="number" class="form-control form-control-sm"
                                        id="item-quantity" value="1" placeholder="1">
                                </div>
                                <div class="form-group col">
                                    <label>Desconto: (F3)</label>
                                    <div class="input-group mb-3 input-group-sm">
                                        <input autocomplete="off" type="text" class="form-control form-control-sm money"
                                                id="item-discount" value="0,00" placeholder="1">
                                        <span class="input-group-append">
                                            <button type="button" id="btn-change-discount"
                                                class="btn btn-primary btn-sm">%</button>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group col">
                                    <label>Preço: (F4)</label>
                                    <input autocomplete="off" type="text" class="form-control form-control-sm money"
                                        id="item-price" value="0,00" placeholder="1">
                                </div>
                                <div class="form-group col">
                                    <label>Subtotal:</label>
                                    <input autocomplete="off" type="text" readonly disabled class="form-control form-control-sm money"
                                        id="item-subtotal" value="0,00" placeholder="1">
                                </div>
                                <div class="form-group col mt-3">
                                    <button type="button" id="btn-adicionar-item" class="btn btn-primary btn-sm btn-block mt-3"><i
                                            class="fas fa-plus"></i> Inserir</button>
                                </div>
                            </div>
                            <div class="form">
                                <div class="col">
                                    <label>Observação:</label>
                                    <textarea id="item-observacao" class="form-control form-control-sm" maxlength="500"></textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- COLUNA DIREITA -->
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6 h-100 pull-right" style="height: 100%; min-height: 100%; overflow: auto;">

                    <!-- RESUMO DA VENDA -->
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title mt-2"><span class="badge badge-multban" id="totalItens">0</span> Itens
                                <span id="TableNo"> </span>
                            </h3>
                        </div>
                        <div class="card-body" id="car_items" style="padding: 5px;min-height: calc(100vh - 55.5vh);">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm table-bordered nowrap">
                                    <thead>
                                        <tr>
                                            <th scope="col">Descrição</th>
                                            <th scope="col">Qtde</th>
                                            <th scope="col">Preço</th>
                                            <th scope="col">Desconto</th>
                                            <th scope="col">Total</th>
                                            <th scope="col">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody id="CartHTML"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <table width="100%" style="border: 0" cellspacing="0" cellpadding="0">
                                <tbody>
                                    <tr>
                                        <td>
                                            <h4>Subtotal:</h4>
                                        </td>
                                        <td class="text-right">
                                            <h4 id="p_subtotal">R$ 0,00</h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h4>Desconto:</h4>
                                        </td>
                                        <td class="text-right">
                                            <h4 id="p_discount">R$ 0,00</h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h3><strong>Total:</strong></h3>
                                        </td>
                                        <td class="text-right ">
                                            <h3 class="valorTotal">R$ 0,00</h3>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>

                    <!-- AÇÕES -->
                    <div class="card-body card mt-2 p-3">
                        <div class="row">
                            <div class="col-md-6 mt-3">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" name="gerarComprovanteSeparados" id="gerarComprovanteSeparados">
                                    <label for="gerarComprovanteSeparados" class="custom-control-label">Gerar Comprovante Separados Por Participante</label>
                                </div></div>
                            <div class="col-md-6">
                                <div class="btn-group" role="group" aria-label="Checkout" style="float: right;">
                                    <button type="button" id="limparCarrinho" class="btn btn-secundary-multban btn-md btn-flat mr-3"><i
                                    class="fas fa-ban"></i> Cancelar (F7)</button>
                                    <button type="button" id="checkout" class="btn btn-primary btn-md btn-flat"><i
                                    class="fas fa-cash-register"></i> Finalizar venda (F9)</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- CONTEÚDO DE PESQUISA - NÃO UTILIZADO -->
            <div class="row animated bounceInLeft" id="listaDePedidos" style="display:none;">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4 pull-left">
                    <div class="card p-1">
                        <table class="table table-striped table-bordered table-head-fixed" id="tablePed">
                            <thead>
                                <tr>
                                    <th>ID Pedido</th>
                                    <th>Cliente</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
                    <div class="card p-1" id="invoiceShow">
                        <div class="invoice p-3 mb-3">
                            <!-- title row -->
                            <div class="row">
                                <div class="col-12">
                                    <h4>
                                        empresas razaosocia.
                                        <small class="float-right" id="dataped"></small>
                                    </h4>
                                </div>
                                <!-- /.col -->
                            </div>
                            <!-- info row -->
                            <div class="row invoice-primary">
                            </div>
                            <!-- /.row -->

                            <!-- Table row -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header border-0 bg-light">
                                            <h3 class="card-title title-vendasituacao"></h3>
                                        </div>
                                        <div class="card-body" id="pedidos-by-cli">

                                        </div>
                                    </div>
                                </div>
                                <!-- /.col -->
                            </div>
                            <!-- /.row -->

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

<!---------------------------------------------->
<!-- MODAIS / SCRIPTS / JAVASCRIPT ------------->
<!---------------------------------------------->
@push('scripts')

    <!---------------------------------------------->
    <!-- MODAIS ------------------------------------>
    <!---------------------------------------------->
    <!-- MODAL - IMPRIMIR CUPOM -->
    <div class="modal inmodal" id="impressaoModal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false"
        data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content animated bounceInRight">
                <div class="modal-header">
                    <h4 class="modal-title">Imprimir Cupom</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <button type="button" id="imprimirCupom" class="btn btn-success btn-lg"><i class="fas fa-print"></i> Imprimir Cupom</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL - CHECKOUT -->
    <div class="modal inmodal" id="checkout-modal" role="dialog" aria-hidden="true">

        <div class="modal-dialog modal-lg">
            <div class="modal-content animated bounceInDown">
                <div class="modal-header bg-primary">
                    <h4 style="float:left;">Finalizar venda</h4>
                </div>
                <div class="modal-body clearfix">

                    <input type="hidden" id="taxa" class="form-control  form-control-sm" value="0.00">
                    <input type="hidden" id="delivery_cost" class="form-control  form-control-sm" value="0">
                    <input type="hidden" id="total_amount" class="form-control  form-control-sm" value="0.00">
                    <input type="hidden" id="idcliente" class="form-control  form-control-sm" value="">

                    <div class="row">
                        <div class="col-md-6">
                            <p class="m-0 text-bold">Forma de Pagamento: <span class="font-weight-light">Nome completo do cliente</span></p>
                            <p class="text-bold">Pontos / CashBack: <span class="text-multban-bold-secundary">0</span></p>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" id="" class="btn btn-primary btn-sm">Resgatar Pontos/Cash Back</button>
                        </div>
                    </div>

                    <div class="card card-outline card-primary">
                        <div class="card-body p-3">
                        <div class="row m-0">
                                <div class="col-md-6">
                                    <p class="m-0 text-bold">Total da Compra: <span class="float-right font-weight-light" id="checkout_subtotal">R$ 0,00</span></p>
                                    <p class="m-0 text-bold">Total de Pontos / CashBack Resgatado: <span class="float-right font-weight-light">0</span></p>
                                    <p class="m-0 text-bold">Total de Desconto Concedido: <span class="float-right font-weight-light" id="checkout_desconto">R$ 0,00</span></p>
                                </div>
                                <div class="col-md-6 text-right">
                                    <p class="mr-4 text-bold m-0" >Total a Pagar</p>
                                    <p class="text-bold"><span class="money-multban-bold-secundary" id="checkout_total">R$ 0,00</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <!-- SELEÇÃO DOS MEIOS DE PAGAMENTO -->
                    <div class="form-row" style="display:none">
                        <div class="form-group col-sm-12">
                            <select class="form-control select2" name="id_forma_pagto" id="id_forma_pagto"
                            data-placeholder="Selecione"
                                style="width: 100%;">
                                <option></option>
                                @foreach ($meioDePagamento as $meio)
                                <option value="{{$meio->meio_pag}}">
                                    {{$meio->meio_pag_desc}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        @foreach($meioDePagamento as $meio)
                        <div class="col">
                            <div class="form-group text-center">
                                <div data-identificacao="{{$meio->meio_pag}}" id="{{$meio->meio_pag_desc}}" data-id="{{$meio->meio_pag_desc}}"
                                    class="payment-box @if($meio->meio_pag == "DN") payment-box-active @endif">
                                    <span class="payment-box-icon"><img src="{{ asset('assets/dist/img/payment/'). '/' . $meio->meio_pag_icon}}"/></span>
                                    <div class="payment-box-content">
                                        <span>{{$meio->meio_pag_desc}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- TELA ADICIONAL COM INSTRUÇÕES DE VENDA -->
                    <div id="payment-instructions" style="display:none;" class="mt-3">

                        <!-- OPÇÕES ADICIONAIS - BOLETO -->
                        <div id="div-boleto" class="payment-extra-section" style="display:none;">
                            <h4>Opções Adicionais para Boleto:</h4>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="parcelasBoleto">Selecione o número de parcelas:*</label>
                                    <select id="parcelasBoleto" class="form-control" required data-size="10">
                                        <option value="">Selecione...</option>
                                        <!-- As opções serão preenchidas dinamicamente via JavaScript -->
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="PrimeiraParaBoleto">1º Parcela para:*</label>
                                    <select id="PrimeiraParaBoleto" class="form-control" required>
                                        <option value="">Selecione...</option>
                                        @if(isset($RegrasParc) && count($RegrasParc) > 0)
                                            @foreach($RegrasParc as $regra)
                                                @if(isset($regra->meio_pag) && $regra->meio_pag == 'BL')
                                                    <option value="{{ $regra->opcao_parc }}" data-regra="{{ $regra->regra_parc }}">{{ $regra->opcao_parc_desc }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="dataPrimeiraParcelaBoleto">Data da primeira parcela:*</label>
                                    <input type="date" id="dataPrimeiraParcelaBoleto" class="form-control" readonly required>
                                    <small id="dataPrimeiraParcelaBoletoHelp" class="form-text text-muted" required></small>
                                </div>
                            </div>
                        </div>

                        <!-- OPÇÕES ADICIONAIS - CARTÃO -->
                        <div id="div-cartao" class="payment-extra-section" style="display:none;">
                            <h4>Opções Adicionais para Cartão:</h4>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="parcelasCartao">Selecione o número de parcelas:*</label>
                                    <select id="parcelasCartao" class="form-control" required>
                                        <option value="">Selecione...</option>
                                        <!-- As opções serão preenchidas dinamicamente via JavaScript -->
                                    </select>
                                </div>
                                @if(isset($empresaParam) && !empty($empresaParam->parc_cjuros))
                                <div class="col-md-6 d-flex align-items-end mb-2">
                                    <div class="form-check w-100">
                                        <input class="form-check-input" type="checkbox" id="vendaSemJurosCartao">
                                        <label class="form-check-label" for="vendaSemJurosCartao">Venda sem juros</label>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label for="PrimeiraParaCartao">1º Parcela para:*</label>
                                    <select id="PrimeiraParaCartao" class="form-control" required>
                                        <option value="">Selecione...</option>
                                        @if(isset($RegrasParc) && count($RegrasParc) > 0)
                                            @foreach($RegrasParc as $regra)
                                                @if(isset($regra->meio_pag) && $regra->meio_pag == 'CM')
                                                    <option value="{{ $regra->opcao_parc }}" data-regra="{{ $regra->regra_parc }}">{{ $regra->opcao_parc_desc }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="dataPrimeiraParcelaCartao">Data da primeira parcela:*</label>
                                    <input type="date" id="dataPrimeiraParcelaCartao" class="form-control" readonly required>
                                    <small id="dataPrimeiraParcelaCartaoHelp" class="form-text text-muted" required></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BLOCO DOS VALORES E BOTÃO COBRAR -->
                    <div class="card card-outline card-primary">
                        <div class="card-body p-3">

                        <div class="row m-0 text-center">

                                <div class="col-md-3">
                                    <label for="valortotalacobrar" class="text-bold m-0" style="font-size:16px;">Valor à Cobrar</label>
                                    <input autocomplete="off" type="text" class="form-control form-control-sm money" id="valortotalacobrar"
                                        name="valortotalacobrar" value="0,00">
                                    <span id="valortotalacobrarError" class="text-danger text-sm"></span>
                                </div>

                                <div class="col-md-3">
                                    <label for="valorsaldo" class="text-bold m-0" style="font-size:16px;">Saldo</label>
                                    <input autocomplete="off" type="text" class="form-control money form-control-sm" id="valorsaldo"
                                        name="valorsaldo" value="0,00" readonly>
                                    <span id="valorsaldoError" class="text-danger text-sm"></span>
                                </div>

                                <div class="col-md-3">
                                    <label for="valortroco" class="text-bold m-0" style="font-size:16px;">Troco</label>
                                    <input autocomplete="off" type="text" class="form-control money form-control-sm" id="valortroco"
                                        name="valortroco" value="0,00" readonly>
                                    <span id="valortrocoError" class="text-danger text-sm"></span>
                                </div>

                                <div class="col-md-3 d-flex align-items-center justify-content-center">
                                    <button type="button" class="form-control form-control-sm btn btn-secundary-multban d-flex align-items-center justify-content-center" style="height: 100%;">
                                        <h5 class="text-bold m-0 w-100 text-center">Cobrar</h5>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PESQUISAR CLIENTE -->
    <div class="modal inmodal" id="pesquisar-cliente-modal">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title">Pesquisa de cliente</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
            <p>Digite o nome, CPF ou CNPJ do cliente.</p>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <select id="find-client" name="find-client" autofocus="autofocus"
                                class="form-control select2 select2-hidden-accessible"
                                data-placeholder="Digite o nome, CPF ou CNPJ do cliente" style="width: 100%;"
                                aria-hidden="true">
                                    <option></option>

                            </select>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-primary pull-right" id="btn-find-client" data-dismiss="modal"><i class="fa fa-check"></i> OK</button>
            </div>
        </div>
        </div>
    </div>

    <!-- MODAL PESQUISAR PRODUTO -->
    <div class="modal inmodal" id="pesquisar-produto-modal">
        <div class="modal-dialog" style="max-width:900px; min-width:700px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Pesquisa de produto</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group col-md-12">
                        <label for="produto_dmf">Descrição do Produto:</label>
                        <select id="produto_dmf" name="produto_dmf" class="form-control select2 select2-hidden-accessible"
                            data-placeholder="Pesquise o Nome do Produto" style="width: 100%;" aria-hidden="true">
                        </select>
                        <input type="hidden" id="produto_dmf_id" name="produto_dmf_id" value="">
                    </div>

                    <div class="table-responsive">
                        <table id="produtos-lista-modal" class="table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Tabela será preenchida via JS -->
                                <tr class="produto-item-modal" style="cursor:pointer" data-id="" data-tipo="" data-dm="" data-sts="" data-desc="" data-price="">
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PARA CADASTRO DE NOVOS CLIENTES -->
    <div class="modal inmodal" id="modalCliente" tabindex="-1" role="dialog" aria-hidden="true">

        <div class="modal-dialog modal-lg">
            <div class="modal-content animated bounceInRight">
                <div class="modal-header">
                    <h4 class="modal-title" id="total_amount_modal">Cliente</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body clearfix">
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="celular">Pesquisar Cliente:</label>
                            <select id="idsearchphone" autofocus="autofocus" name="idsearchphone"
                                class="form-control select2 select2-hidden-accessible"
                                data-placeholder="PESQUISE PELO TELEFONE" style="width: 100%;" aria-hidden="true">
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="razaosocial" id="labelrazaosocial">Nome:</label>
                            <input class="form-control  form-control-sm" placeholder="Digite o nome" name="razaosocial" type="text"
                                id="razaosocial" value="">

                            <input class="form-control  form-control-sm" placeholder="Digite o nome" name="nomefantasia" type="hidden"
                                id="nomefantasia" value="">

                        </div>
                        <div class="form-group col-md-4">
                            <label for="celular" id="labelcelular">Celular:</label>
                            <input class="form-control cell_with_ddd form-control-sm" placeholder="Digite o celular" name="celular"
                                type="text" id="celular" value="">

                        </div>
                        <div class="form-group col-md-4">
                            <label for="telefone" id="labeltelefone">Telefone:</label>
                            <input class="form-control phone_with_ddd form-control-sm" placeholder="Digite o telefone" name="telefone"
                                type="text" id="telefone" value="">

                        </div>
                    </div>
                    <hr>
                    <div class="form-row">
                        <div class="form-goup col-md-3>
                            <label for='cep'>CEP</label>
                            <div class="input-group mb-3 input-group-sm">
                                <input type="text" autofocus="autofocus" class="form-control cep form-control-sm" id="cep" name="cep"
                                    value="" placeholder="Digite o CEP">
                                <span class="input-group-append">
                                    <button type="button" id="btnPesquisarCep" class="btn btn-default"><i
                                            class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for='endereco'>Endereço (Logradouro)</label>
                            <input class="form-control  form-control-sm" placeholder="Digite o Endereço" name="endereco" type="text"
                                id="endereco" value="">
                        </div>
                        <div class="form-group col-md-3">
                            <label for='numero'>Número</label>
                            <input class="form-control  form-control-sm" placeholder="Digite o Número" name="numero" type="text" id="numero"
                                value="">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for='complemento'>Complemento</label>
                            <input class="form-control  form-control-sm" placeholder="Digite o Complemento" name="complemento" type="text"
                                id="complemento" value="">
                        </div>
                        <div class="form-group col-md-4">
                            <label for='bairro'>Bairro</label>
                            <input class="form-control  form-control-sm" placeholder="Digite o Bairro" name="bairro" type="text" id="bairro"
                                value="">
                        </div>
                        <div class="form-group col-md-4">
                            <label for='pontoreferencia'>Ponto de referência</label>
                            <input class="form-control  form-control-sm" placeholder="Digite o Ponto de referência" name="pontoreferencia"
                                type="text" id="pontoreferencia" value="">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for='idcidade'>Cidade</label>
                            <select id="idcidade" name="idcidade" class="form-control select2 select2-hidden-accessible"
                                data-placeholder="PESQUISE A CIDADE" style="width: 100%;" aria-hidden="true">
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for='idestado'>Estado</label>
                            <select id="idestado" name="idestado" class="form-control select2 select2-hidden-accessible"
                                data-placeholder="PESQUISE O ESTADO" style="width: 100%;" aria-hidden="true">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for='idenderecotipo'>Tipo</label>
                            <select id="idenderecotipo" name="idenderecotipo"
                                class="form-control select2 select2-hidden-accessible"
                                data-placeholder="SELECIONE O TIPO DE ENDEREÇO" style="width: 100%;" aria-hidden="true">

                            </select>
                        </div>
                    </div>

                    <div class="col-sm-12 text-right">
                        <button type="button" id="ClearForm" class="btn btn-danger">Cancelar</button>
                        <button type="button" id="salvarCliente" class="btn btn-primary btn-sm">OK</button>
                        <span id="errorMessage" style="color:red"> </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!---------------------------------------------->
    <!-- SCRIPTS ----------------------------------->
    <!---------------------------------------------->
    <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}">
    <script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/i18n/pt-BR.js') }}"></script>

    <script src="{{ asset('assets/dist/js/app.js') }}"></script>
    <script src="{{ asset('assets/dist/js/pages/venda/pdv/updatevenda.js') }}"></script>

    <script src="{{ asset('assets/plugins/lodash/lodash.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <!--script src="{{ asset('assets/plugins/jquery-print/jQuery.print.min.js') }}"></script-->
    <link rel="stylesheet" href="{{ asset('assets/plugins/animate/animate.css') }}" />

    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-fixedheader/js/dataTables.fixedHeader.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/dist/js/pages/venda/venda.js') }}"></script>

    <script type="text/javascript">

        window.empresaParam = @json($empresaParam ?? null);

        // Mapeia as opções de regra_parc para uso no JS
        @if(isset($RegrasParc) && count($RegrasParc) > 0)
            @foreach($RegrasParc as $regra)
                regrasParcMap["{{ $regra->opcao_parc }}"] = {{ intval($regra->regra_parc) }};
            @endforeach
        @endif

    </script>

    <!-- ESTILO CSS DOS CAMPOS ESPECÍFICOS DO PDV -->
    <style>

        .text-multban-bold-secundary {
            font-weight: 700 !important;
            font-size: 19px;
            color: #a702d8;
        }

        .money-multban-bold-secundary {
            font-weight: 700 !important;
            font-size: 26px;
            color: #a702d8;
            margin-top: -10px;
            margin-right: 20px;
        }

        .payment-box {
            border-radius: .50rem;
            border-color: #86a1a5 !important;
            padding-top: 1rem !important;
            border: 1.6px solid #cbd1d5 !important;
            display: -ms-flexbox;
            display: flex;
            min-height: 40px;
            padding: .5rem;
            position: relative;
            width: 100%;
            text-align: center;
        }

        .payment-box:hover{
            cursor: pointer;
        }

        .payment-box .payment-box-content {
            margin-top: -9px;
            margin-left: 0px;
            padding: 6px;
            position: relative;
            text-align: center;
        }

        .payment-box .payment-box-icon {
            border-radius: .50rem;
            -ms-flex-align: center;
            align-items: center;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-pack: center;
            height: 20px;
        }

        .payment-box .payment-box-icon img{
            width: 30px;
        }

        .payment-box-active {
            border-radius: .50rem;
            padding-top: 1rem !important;
            border: 1px solid #1c0065 !important;
            background-color: #1c0065;
            color: #ffffff !important;
        }

        .total-box {
                border-radius: .50rem;
            border: 1px solid #1c0065 !important;
            background-color: #e0e0e0;
            min-height: 50px;
            height: 65px;
            padding: 5px;
        }
        .box-finalizar {
            border-radius: .50rem;
            border: 1px solid #1c0065 !important;
            background-color: #a702d8;
            min-height: 50px;
            height: 65px;
            padding: 19px;
            color: #ffffff;
            cursor: pointer;
        }

        /* Limita a altura do dropdown do Select2 para aproximadamente 10 itens (com scrollbar) */
        .select2-dropdown.parc-limit .select2-results__options {
            max-height: 360px; /* ajuste fino: ~10 itens dependente do line-height */
            overflow-y: auto;
        }

        .user-block .description {
            color: #b9b9b9 !important;
        }

        .cart-item {
            max-height: 160px;
            overflow-y: scroll;
        }

        .scale-anm {
            transform: scale(1);
        }

        .tile {
            -web-kit-transform: scale(0);
            transform: scale(0);
            -webkit-transition: all 350ms ease;
            transition: all 350ms ease;
        }

        .product_list {
            min-height: 200px !important;
            margin-top: 0px;
        }

        .product_list h2 {
            padding: 2px 8px;
            margin-bottom: 8px !important;
        }

        .sidebar-mini.sidebar-collapse .content-wrapper,
        .sidebar-mini.sidebar-collapse .main-footer,
        .sidebar-mini.sidebar-collapse .main-header {
            margin-left: 0px !important;
        }

        .content-header,
        .main-sidebar,
        .navbar-nav-pdv {
            display: none !important;
        }

        /* Chrome, Safari, Edge, Opera */
        .IncOrDecToCart::-webkit-outer-spin-button,
        .IncOrDecToCart::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        .IncOrDecToCart[type=number] {
            -moz-appearance: textfield;
        }

    </style>

@endpush
