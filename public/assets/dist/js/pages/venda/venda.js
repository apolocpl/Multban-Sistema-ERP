////////////
// VARIÁVEIS

// Armazena o valor original do produto selecionado
var tituloAtual = null;
var nsu_tituloAtual = null;
var cartaoSelecionado = null;
var precoOriginalProduto = 0;
var data = [];
var tipoPagamentoSelecionado = null;
var regrasParcMap = {};
var products = new Array();
var count_items = 0;
var cart = new Array();
var itens = {};
var finalizarClick = false;
var searchProduct = false;
var discountType = '%';
var empresa = window.empresa || null;
var empresaParam = window.empresaParam || null;
var podeFecharCheckoutModal = false;
var cobrar = false;
var lastToastr = {};
var venda_subtotal = $("#p_subtotal").text().replace("R$", "").trim();
var venda_desconto = $("#p_discount").text().replace("R$", "").trim();
var venda_total = $("#valorTotal").text().replace("R$", "").trim();
var arr_nsu_autoriz = [];
var arr_checkout_desconto = [];
var arr_checkout_cashback = [];
var arr_checkout_total = [];
var arr_meiosPagtoUtilizados = [];
var arr_checkout_juros = [];
var arr_checkout_total = [];
var arr_checkout_troco = [];
var arr_checkout_desconto = [];
var carrinho = [];

//status das mesas
var mesaStatus = {}
mesaStatus.OCUPADA = 0
mesaStatus.LIVRE = 1

//Venda Situação
var vendaStatus = {}
vendaStatus.ABERTA = 1;
vendaStatus.EMPREPARACAO = 2;
vendaStatus.EMENTREGA = 3;
vendaStatus.CONCLUIDA = 4;

//tipo de venda
var vendaTipo = {}
vendaTipo.NOLOCAL = 1
vendaTipo.RETIRAR = 2
vendaTipo.ENTREGAR = 3

var html_pedidos_by_cli = '';
var table = null;

var carregaCliente = function(msg){
    var url = "/cliente/searchphone";
    var parametro = {
        parametro: msg.celular == null ? msg.telefone : msg.celular
    };
    $.get(url, parametro, function(item) {
        $("#idsearchphone").select2("trigger", "select", {
            data: item[0],
        });
    });
}

var calcDiscount = function(id, index) {
    var quantity = $.tratarValor($('#item-quantity-'+id).val());
    var discount = $.tratarValor($('#item-discount-'+id).val());
    var discountTotal = 0;
    if(quantity > 0){
        var price = $.tratarValor($("#item-price-"+id).val());
        if(discountType === "%"){
            discountTotal = quantity * ((discount * price) / 100);
        }else{
            discountTotal = quantity * discount;
        }
    }

    return discountTotal
}




///////////////////////////////////////////////////////////////////////
// FUNÇOES EXECUTADAS SOMENTE ANTES QUE TODA A PÁGINA ESTIVER CARREGADA
///////////////////////////////////////////////////////////////////////

//////////
// FUNÇÕES
//////////

// Realiza a cobrança (total ou parcial)
function realizarCobranca(cobrancaDados) {

    $.ajax({
        url: '/pdv-web/realizar-venda',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': cobrancaDados.token },
        data: {
            token: cobrancaDados.token,
            cliente_id: cobrancaDados.cliente_id,
            tipoPagto: cobrancaDados.tipoPagto,
            checkout_subtotal: cobrancaDados.checkout_subtotal,
            checkout_cashback: cobrancaDados.checkout_cashback,
            checkout_desconto: cobrancaDados.checkout_desconto,
            checkout_pago: cobrancaDados.checkout_pago,
            checkout_descontado: cobrancaDados.checkout_descontado,
            checkout_troco: cobrancaDados.checkout_troco,
            checkout_resgatado: cobrancaDados.checkout_resgatado,
            checkout_total: cobrancaDados.checkout_total,
            valortotalacobrar: cobrancaDados.valortotalacobrar,
            vendaSemJuros: cobrancaDados.vendaSemJuros,
            check_reembolso: cobrancaDados.check_reembolso,
            tax_categ: cobrancaDados.tax_categ,
            regra_parc: cobrancaDados.regra_parc,
            valorTotalComJuros: cobrancaDados.valorTotalComJuros,
            valorParcelaComJuros: cobrancaDados.valorParcelaComJuros,
            valorParcelaSemJuros: cobrancaDados.valorParcelaSemJuros,
            jurosTotal: cobrancaDados.jurosTotal,
            jurosTotalParcela: cobrancaDados.jurosTotalParcela,
            parcelas: cobrancaDados.parcelas,
            dataPrimeiraParcela: cobrancaDados.dataPrimeiraParcela,
            proporcao_cobrado: cobrancaDados.proporcao_cobrado,
            checkout_desconto_proporcional: cobrancaDados.checkout_desconto_proporcional,
            checkout_cashback_proporcional: cobrancaDados.checkout_cashback_proporcional,
            carrinho: cobrancaDados.carrinho,
            card_tp: cobrancaDados.card_tp,
            card_mod: cobrancaDados.card_mod,
            card_categ: cobrancaDados.card_categ,
            card_desc: cobrancaDados.card_desc,
            card_uuid: cobrancaDados.card_uuid,
            cliente_cardn: cobrancaDados.cliente_cardn,
            cliente_cardcv: cobrancaDados.cliente_cardcv,
            card_saldo_vlr: cobrancaDados.card_saldo_vlr,
            card_limite: cobrancaDados.card_limite,
            card_saldo_pts: cobrancaDados.card_saldo_pts,
            card_sts: cobrancaDados.card_sts,
            tituloAtual: tituloAtual,
            nsu_tituloAtual: nsu_tituloAtual,
        },

        success: function(resp) {
            if (resp.success) {

                // Se for a primeira cobrança, salva o título e o nsu_título
                if (!tituloAtual && resp.titulo) {
                    tituloAtual = resp.titulo;
                }
                if (!nsu_tituloAtual && resp.nsu_titulo) {
                    nsu_tituloAtual = resp.nsu_titulo;
                }

                // Atualiza arrays de controle
                arr_nsu_autoriz.push(resp.nsu_autoriz || '');
                arr_checkout_desconto.push(cobrancaDados.checkout_desconto_proporcional);
                arr_checkout_cashback.push(cobrancaDados.checkout_cashback_proporcional);
                arr_checkout_troco.push(cobrancaDados.checkout_troco);
                arr_checkout_total.push(cobrancaDados.valortotalacobrar);
                arr_checkout_juros.push(cobrancaDados.jurosTotal);

                // Calcula saldo remanescente
                var totaljuros = arr_checkout_juros.reduce((a, b) => Number(a) + Number(b), 0);
                var totalCobrado = arr_checkout_total.reduce((a, b) => Number(a) + Number(b), 0);
                var totalTroco = arr_checkout_troco.reduce((a, b) => Number(a) + Number(b), 0);
                var totalDesconto = arr_checkout_desconto.reduce((a, b) => Number(a) + Number(b), 0);
                var saldo = cobrancaDados.checkout_subtotal - totalCobrado - cobrancaDados.checkout_desconto;

                if (saldo > 0.01) {

                    // Mensagem de cobrança parcial
                    Swal.fire('Parcial', 'Cobrança parcial registrada! Ainda há saldo a receber.', 'info');

                    // Atualiza campos para o saldo remanescente
                    $("#valortotalacobrar").val(saldo.toLocaleString('pt-BR', {minimumFractionDigits: 2}));
                    $("#checkout_total").text(saldo.toLocaleString('pt-BR', {minimumFractionDigits: 2}));

                    $("#checkout_pago").text((cobrancaDados.checkout_pago + cobrancaDados.valortotalacobrar).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }));
                    $("#checkout_descontado").text((cobrancaDados.checkout_descontado + cobrancaDados.checkout_desconto_proporcional).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }));
                    $("#checkout_resgatado").text((cobrancaDados.checkout_resgatado + cobrancaDados.checkout_cashback_proporcional).toLocaleString('pt-BR', { maximumFractionDigits: 0 }));

                    // Limpa apenas campos de pagamento/modal
                    $('.payment-box-active').removeClass('payment-box-active');
                    $('#id_forma_pagto').val('').trigger('change');
                    $('#valortroco').val('0,00');
                    $("#valorsaldo").val('0,00');

                    // Resetar campos de pagamento parcelado
                    $("#parcelasCartao").val("").trigger("change");
                    $("#parcelasBoleto").val("").trigger("change");
                    $("#PrimeiraParaCartao").val("").trigger("change");
                    $("#PrimeiraParaBoleto").val("").trigger("change");
                    $("#dataPrimeiraParcelaCartao").val("");
                    $("#dataPrimeiraParcelaBoleto").val("");

                } else {
                    Swal.fire('Sucesso', 'Cobrança registrada!', 'success').then(function() {

                        // Limpar carrinho, atualizar tela, etc.
                        $("#totalItens").html("0");
                        $("#TableNo").text("");
                        $("#TableNoCart").text("");
                        $(".valorTotal").html("R$ 0,00");
                        $("#CartHTML").html("");
                        $("#p_subtotal").html("R$ 0,00");
                        $("#p_discount").html("R$ 0,00");
                        $(".totalPagar").html("R$ 0,00");
                        $("#pedidoID").val("");
                        // Produto
                        $('#find-product').val(null).trigger('change');
                        $('#find-product').select2('data', null);
                        $('#getProduto').val('');
                        $('#desProd').html('');
                        $('#item-quantity').val('1');
                        $('#item-discount').val('0,00');
                        $('#item-price').val('0,00');
                        $('#item-subtotal').val('0,00');
                        $('#produto_dmf').val(null).trigger('change');
                        $('#produto_dmf_id').val('');
                        $('#produto_tipo_id').val('');
                        // Pagamento
                        $('#valortotalacobrar').val('0,00');
                        $('#valorsaldo').val('0,00');
                        $('#valortroco').val('0,00');
                        $('#checkout_subtotal').text('R$ 0,00');
                        $('#checkout_desconto').text('R$ 0,00');
                        $('#checkout_pago').text('R$ 0,00');
                        $('#checkout_descontado').text('R$ 0,00');
                        $('#checkout_resgatado').text('0');
                        $('#checkout_total').text('R$ 0,00');
                        if ($("#checkout_cashback").length) {
                            $("#checkout_cashback").text('R$ 0,00');
                        }
                        $('.payment-box-active').removeClass('payment-box-active');
                        $('#id_forma_pagto').val('').trigger('change');
                        $('#checkout-modal').modal('hide');

                        ///////////////////////////////////////////////
                        // MONTA OS PARÂMETROS PARA GERAR O COMPROVANTE
                        var tipo_pagamento = '';
                        if (cobrancaDados.tipoPagto === 'CM') {
                            if (cobrancaDados.parcelas > 1) {
                                tipo_pagamento = 'Parcelado';
                            } else  {
                                tipo_pagamento = 'À Vista';
                            }
                        } else if (cobrancaDados.tipoPagto === 'BL') {
                            if (cobrancaDados.parcelas > 1) {
                                tipo_pagamento = 'Parcelado';
                            } else  {
                                tipo_pagamento = 'À Vista';
                            }
                        } else if (cobrancaDados.tipoPagto === 'DN') {
                            tipo_pagamento = 'À Vista';
                        } else if (cobrancaDados.tipoPagto === 'PX') {
                            tipo_pagamento = 'À Vista';
                        } else if (cobrancaDados.tipoPagto === 'OT') {
                            tipo_pagamento = 'À Vista';
                        }

                        $.get('/api/mensagens-comp', {
                            canal_id: 4,
                            categorias: ['CBCPR','MULTB','RPCPR', 'AUTHO']

                        }, function(mensagensComp) {

                            var params = {
                                empresa: {
                                    nome: empresa.emp_nfant,
                                    cnpj: empresa.emp_cnpj,
                                    im: empresa.emp_im,
                                    ie: empresa.emp_ie,
                                    emp_id: empresa.emp_id
                                },

                                cliente: {
                                    nome: window.clientName,
                                    doc: window.clientDoc,
                                    pontos: window.clientPontos
                                },

                                comprovante: {
                                    titulo: resp.titulo,
                                    nsu_titulo: resp.nsu_titulo,
                                    nsu_autoriz: arr_nsu_autoriz,
                                    data_hora: (new Date()).toLocaleString('pt-BR'),
                                    cartao_numero: (cobrancaDados.cliente_cardn || ''),

                                    tipo_pagamento: tipo_pagamento,
                                    parcelas: cobrancaDados.parcelas,
                                    pontos_concedidos: 0,

                                    meio_pagamentos: arr_meiosPagtoUtilizados.join(', '),
                                    jurosTotal: totaljuros,
                                    checkout_subtotal: cobrancaDados.checkout_subtotal,
                                    checkout_troco: totalTroco,
                                    checkout_desconto: totalDesconto,
                                    checkout_cashback: cobrancaDados.checkout_cashback,
                                    checkout_total: totalCobrado + totaljuros
                                },

                                mensagens: {
                                    cabecalho: mensagensComp.CBCPR,
                                    multban: mensagensComp.MULTB,
                                    rodape: mensagensComp.RPCPR
                                },

                                autorizacao: mensagensComp.AUTHO
                            };

                            var htmlComprovante = gerarComprovanteVenda(params);

                            Swal.fire({
                                html: htmlComprovante,
                                showConfirmButton: false,
                                width: 500,
                                customClass: { popup: 'swal2-comprovante-popup' },
                                allowOutsideClick: false,
                                footer: `
                                    <div style="display:flex;justify-content:space-between;gap:8px;">
                                        <button class="btn btn-primary btn-sm" id="btnEnviarEmailComprovante">Enviar por Email</button>
                                        <button class="btn btn-success btn-sm" id="btnEnviarWhatsAppComprovante">Enviar por WhatsApp</button>
                                        <button class="btn btn-secundary-multban btn-sm" id="btnFecharComprovante">Fechar</button>
                                        <button class="btn btn-primary btn-sm" id="btnImprimirComprovante">Imprimir</button>
                                    </div>
                                `
                            });

                            // Fechar o modal ao clicar em fechar
                            $(document).on('click', '#btnFecharComprovante', function() {
                                 // Resetar arrays de controle
                                cart = [];
                                carrinho = [];
                                arr_nsu_autoriz = [];
                                arr_meiosPagtoUtilizados = [];
                                arr_checkout_desconto = [];
                                arr_checkout_cashback = [];
                                arr_checkout_total = [];
                                arr_checkout_juros = [];
                                arr_checkout_troco = [];
                                cartaoSelecionado = null;
                                //window.responseCartoesCliente = [];
                                window.cobrancaDados = [];
                                tituloAtual = null;
                                nsu_tituloAtual = null;
                                Swal.close();
                                show_cart();

                            });

                            // Imprimir ao clicar em imprimir
                            $(document).on('click', '#btnImprimirComprovante', function() {
                                // Pega o HTML do comprovante
                                var comprovanteHtml = $('#comprovante-venda').prop('outerHTML');
                                // Abre nova janela
                                var printWindow = window.open('', '', 'width=800,height=600');
                                printWindow.document.write(`
                                    <html>
                                    <head>
                                        <title>Imprimir Comprovante</title>
                                        <style>
                                            body { background: #fdf6e3; font-family: monospace; }
                                            #comprovante-venda { margin: 0 auto; }
                                        </style>
                                    </head>
                                    <body>${comprovanteHtml}</body>
                                    </html>
                                `);
                                printWindow.document.close();
                                printWindow.focus();
                                // Aguarda o carregamento e imprime
                                printWindow.onload = function() {
                                    printWindow.print();
                                    // printWindow.close(); // Descomente se quiser fechar automaticamente após imprimir
                                };
                            });

                            // Envio por email - IMPLEMENTAR
                            $(document).on('click', '#btnEnviarEmailComprovante', function() {
                                Swal.fire('Atenção', 'Funcionalidade de envio por email ainda não implementada.', 'info');
                            });

                            // Envio por whatsapp - IMPLEMENTAR
                            $(document).on('click', '#btnEnviarWhatsAppComprovante', function() {
                                Swal.fire('Atenção', 'Funcionalidade de envio por WhatsApp ainda não implementada.', 'info');
                            });

                        });

                    });

                }

            } else {
                var msg = resp.error || resp.message || 'Não foi possível registrar a cobrança.';
                Swal.fire('Erro', msg, 'error');
            }

        },

        error: function(xhr) {
            var msg = 'Falha na comunicação com o servidor.';
            if (xhr && xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) {
                msg = xhr.responseJSON.error || xhr.responseJSON.message;
            } else if (xhr && xhr.responseText) {
                try {
                    var json = JSON.parse(xhr.responseText);
                    msg = json.error || json.message || msg;
                } catch(e) {
                    msg = xhr.responseText;
                }
            }
            Swal.fire('Erro', msg, 'error');
        }
    });

}

// Função para abrir um select2 de forma segura (destrói se já estiver inicializado)
function safeOpenSelect2(selector) {
    var $el = $(selector);
    // Destroi se já estiver inicializado
    if ($el.hasClass('select2-hidden-accessible')) {
        try { $el.select2('destroy'); } catch(e) {}
    }
    // Inicializa
    $el.select2({
        width: 'resolve',
        dropdownParent: $('#checkout-modal').length ? $('#checkout-modal') : $(document.body),
        dropdownCssClass: 'parc-limit'
    });
    // Aguarda o próximo tick do JS para abrir
    setTimeout(function() {
        $el.select2('open');
    }, 0);
}

// Função para mascarar número do cartão (formato: 1234.****.****.5678)
function maskCardNumberCustom(cardNumber) {
    if (!cardNumber) return '';
    var n = String(cardNumber).replace(/\D/g, '');
    if (n.length < 8) return n; // Não mascara se não tiver pelo menos 8 dígitos
    var first4 = n.substr(0, 4);
    var last4 = n.substr(-4);
    return `${first4}.****.****.${last4}`;
}

// Função para formatar valor para padrão brasileiro
function formatBRL(valor) {
    return valor.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Função para converter string para float (aceita 1.234,56 ou 1234,56 ou 1234)
function parseBRL(str) {
    if (!str) return 0;
    str = str.replace(/R\$|\s/g, '').trim();
    // Se não tem vírgula, é inteiro (centavos = 00)
    if (str.indexOf(',') === -1) {
        str = str.replace(/\./g, '');
        return parseFloat(str) || 0;
    }
    // Se tem vírgula, trata milhar e decimal
    str = str.replace(/\./g, '').replace(',', '.');
    return parseFloat(str) || 0;
}

// Regra para limitar subtotal
function validarSubtotal() {
    var quantity = parseInt($('#item-quantity').val()) || 1;
    var price = $.tratarValor($('#item-price').val());
    var subtotal = quantity * price;
    return true;
}

// Atualiza o campo #valortotalacobrar a partir de um número (evita disparar o handler de input
// que aplica a máscara calculadora e pode converter vírgula para ponto). Também recalcula saldo e troco.
function atualizarCampoValortotalacobrar(valorNumero) {
    var totalCarrinho = parseBRL($('#checkout_total').text());
    var tipoPagamento = $('.payment-box-active').data('identificacao') || $('#id_forma_pagto').val();
    var valorCobrar = Number(valorNumero) || 0;
    // Para tipos diferentes de Dinheiro, não permitir cobrar mais que o total
    if (tipoPagamento !== 'DN' && valorCobrar > totalCarrinho) {
        valorCobrar = totalCarrinho;
    }
    // Atualiza campo com formatação brasileira
    $('#valortotalacobrar').val((typeof formatBRL === 'function') ? formatBRL(valorCobrar) : valorCobrar.toFixed(2).replace('.', ','));
    // Recalcula saldo e troco
    var saldo = totalCarrinho - valorCobrar;
    if (valorCobrar > totalCarrinho) saldo = 0;
    $('#valorsaldo').val(formatBRL(saldo));
    var troco = 0;
    if (tipoPagamento === 'DN' && valorCobrar > totalCarrinho) {
        troco = valorCobrar - totalCarrinho;
    }
    $('#valortroco').val(formatBRL(troco));
}

// ATUALIZA OS VALORES DO CHECKOUT
function atualizarCheckoutValores() {
    var subtotal = $("#p_subtotal").text();
    var desconto = $("#p_discount").text();
    var totalCobrar = $("#valortotalacobrar").val();
    $("#checkout_subtotal").text(subtotal);
    $("#checkout_desconto").text(desconto);
    $("#checkout_total").text(totalCobrar);
}

// ATUALIZA AS PARCELAS QUE APARECEM COMO OPÇÕES NA VENDA POR BOLETO
function atualizarParcelasBoleto() {

    // Prioriza o valor informado manualmente em #valortotalacobrar, depois #checkout_total e por fim .valorTotal
    var totalVenda = 0;
    var valorCobrarText = $("#valortotalacobrar").val() || "";

    // #valortotalacobrar contém texto no formato '123,45' (sem R$)
    if (typeof parseBRL === 'function') {
        totalVenda = parseBRL(valorCobrarText);
    } else {
        var totalStr0 = valorCobrarText.replace(/R\$|\s/g, '').trim();
        totalStr0 = totalStr0.replace(/\./g, '').replace(',', '.');
        totalVenda = parseFloat(totalStr0) || 0;
    }

    // Pega limite de parcelas da empresa (blt_parclib). Se não existir, cai para #card_posparc
    var parclib = 1;
    if (empresaParam && empresaParam.blt_parclib) {
        parclib = parseInt(empresaParam.blt_parclib) || 1;
    } else {
        parclib = parseInt($("#card_posparc").val()) || 1;
    }

    // O requisito pede: criar exatamente blt_parclib entradas com o resultado da divisão total/blt_parclib
    var select = $("#parcelasBoleto");
    select.empty();
    select.append('<option value="">Selecione...</option>');

    if (parclib <= 0) parclib = 1;

    for (var i = 1; i <= parclib; i++) {
        var parcelaValor = i > 0 ? (totalVenda / i) : totalVenda;
        var parcelaValorFormatado = (typeof formatBRL === 'function') ? formatBRL(parcelaValor) : parcelaValor.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        var numParcela = i.toString().padStart(2, '0');
        select.append(`<option value="${i}">${numParcela} X R$ ${parcelaValorFormatado}</option>`);
    }
    // Inicializa Select2 de forma segura (apenas se estiver disponível) para limitar altura do dropdown
    try {
        initParcelSelect2IfAvailable('#parcelasBoleto');

    } catch(e) { console.warn('initParcelSelect2IfAvailable erro:', e); }
}

// ATUALIZA AS PARCELAS QUE APARECEM COMO OPÇÕES NA VENDA POR CARTÃO
function atualizarParcelasCartao() {

    // Prioriza o valor informado manualmente em #valortotalacobrar, depois #checkout_total e por fim .valorTotal
    var totalVenda = 0;
    var valorCobrarTextC = $("#valortotalacobrar").val() || "";

    if (typeof parseBRL === 'function') {
        totalVenda = parseBRL(valorCobrarTextC);
    } else {
        var totalStr0c = valorCobrarTextC.replace(/R\$|\s/g, '').trim();
        totalStr0c = totalStr0c.replace(/\./g, '').replace(',', '.');
        totalVenda = parseFloat(totalStr0c) || 0;
    }

    // Pega limite de parcelas da empresa (card_posparc). Se não existir, cai para #card_posparc
    var parclib = 1;
    if (empresaParam && empresaParam.card_posparc) {
        parclib = parseInt(empresaParam.card_posparc) || 1;
    } else {
        parclib = parseInt($("#card_posparc").val()) || 1;
    }

    // Leitura das configurações de juros (se existirem) na empresa
    var parc_cjuros_flag = false; // indica se a empresa permite vendas com juros
    var parc_jr_deprc_val = 0; // número mínimo de parcelas para começar a cobrar juros
    var tax_jrsparc_val = 0; // taxa de juros (porcentagem) por parcela
    if (empresaParam) {
        // parc_cjuros pode vir como string/boolean/numero
        parc_cjuros_flag = !!empresaParam.parc_cjuros;
        // parc_jr_deprc deve ser inteiro (ex: a partir de qual parcela começa juros)
        parc_jr_deprc_val = parseInt(String(empresaParam.parc_jr_deprc || '').replace(/\D/g, ''), 10) || 0;
        // tax_jrsparc pode conter vírgula como decimal -> normaliza para ponto
        var _tax = empresaParam.tax_jrsparc;
        if (typeof _tax === 'string') {
            _tax = _tax.replace(',', '.');
        }
        tax_jrsparc_val = parseFloat(_tax) || 0;
    }

    var select = $("#parcelasCartao");
    select.empty();
    select.append('<option value="">Selecione...</option>');

    if (parclib <= 0) parclib = 1;

    for (var i = 1; i <= parclib; i++) {
        var parcelaValor = 0;
        var descricaoJuros = '';
        var totalComJuros = 0;

        // Se a empresa permite juros e a parcela atual está na faixa que cobra juros (>= parc_jr_deprc_val)
        if (parc_cjuros_flag && parc_jr_deprc_val > 0 && i >= parc_jr_deprc_val && !$('#vendaSemJurosCartao').is(':checked')) {
            // calcula percentual total de juros: i * tax_jrsparc_val (em %)
            var jurosPercentTotal = (i * tax_jrsparc_val) / 100; // ex: 4 parcelas * 2% = 8% => 0.08
            // valor absoluto de juros sobre o total da venda
            var jurosAmount = totalVenda * jurosPercentTotal;
            // soma os juros ao total e divide pela quantidade de parcelas
            var totalComJuros = totalVenda + jurosAmount;

            parcelaValor = totalComJuros / i;
            descricaoJuros = ' - com juros';

        } else {
            parcelaValor = i > 0 ? (totalVenda / i) : totalVenda;

        }

        var parcelaValorFormatado = (typeof formatBRL === 'function') ? formatBRL(parcelaValor) : parcelaValor.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        var numParcela = i.toString().padStart(2, '0');
        select.append(`<option value="${i}" data-total-com-juros="${totalComJuros.toFixed(2)}">${numParcela} X R$ ${parcelaValorFormatado}${descricaoJuros}</option>`);
    }

    // Inicializa Select2 de forma segura (apenas se estiver disponível) para limitar altura do dropdown
    try {
        initParcelSelect2IfAvailable('#parcelasCartao');

    } catch(e) { console.warn('initParcelSelect2IfAvailable erro:', e); }
}

function showToastrOnce(type, message, key, cooldownMs) {
    cooldownMs = cooldownMs || 1000; // 1s default
    var now = Date.now();
    key = key || message;
    if (!lastToastr[key] || (now - lastToastr[key]) > cooldownMs) {
        lastToastr[key] = now;
        if (type === 'error') toastr.error(message);
        else if (type === 'success') toastr.success(message);
        else if (type === 'info') toastr.info(message);
        else toastr.warning(message);
    }
}

// Inicializa Select2 em uma select (se a biblioteca estiver carregada) sem alterar a largura/estilo original.
function initParcelSelect2IfAvailable(selector) {
    if (typeof $ === 'undefined' || typeof $.fn === 'undefined' || !$.fn.select2) return;
    var $el = $(selector);
    if ($el.length === 0) return;
    // se já inicializado, destroy antes para re-inicializar com as opções corretas
    try {
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
    } catch (e) {
        // ignore
    }
    $el.select2({
        width: 'resolve',
        dropdownParent: $('#checkout-modal').length ? $('#checkout-modal') : $(document.body),
        dropdownCssClass: 'parc-limit'
    });
}

function formataNumeroTelefone(numero) {
    numero = numero.replace(" ", "").replace("-", "").replace("(", "").replace(")", "");

    if(numero.length == 0)
    return "Sem número";
    var length = numero.length;
    var telefoneFormatado;

    if (length === 10) {
    telefoneFormatado = '(' + numero.substring(0, 2) + ') ' + numero.substring(2, 6) + '-' + numero.substring(6, 10);
    } else if (length === 11) {
    telefoneFormatado = '(' + numero.substring(0, 2) + ') ' + numero.substring(2, 7) + '-' + numero.substring(7,11);
    }
    return telefoneFormatado;
}

function setarCidade(id) {
    var url = "/cliente/obtercidadeid";
    var parametro = {
        parametro: id
    };
    $.get(url, parametro, function(item) {
        $("#idcidade").select2("trigger", "select", {
            data: item[0],
        });
    });
};

function setarEstado(id) {
    var url = "/cliente/obterestadoid";
    var parametro = {
        parametro: id
    };
    $.get(url, parametro, function(item) {
        $("#idestado").select2("trigger", "select", {
            data: item[0],
        });
    });
};

function calculaDescontoPorcentagem(){
    $("#valortotalpago").val("0,00");
    $("#valortroco").val("0,00");
    var valorDescCento = $.tratarValor($("#valorDescCento").val());
    var valortotal =  $.tratarValor($("#total_amount_modal").html());
    var valorAPagar =  $.tratarValor($("#valorAPagar").val());

    var valorDesconto = valortotal * (valorDescCento/100);

    $("#valorDesconto").val(valorDesconto.toFixed(2).replace('.', ','));
    $("#valorAPagar").val((valortotal - valorDesconto).toFixed(2).replace('.', ','));

    const tipo_pagto = document.querySelector("#cartao");
    if(tipo_pagto.classList.contains("text-success")){
        $("#valortotalpago").val($("#valorAPagar").val());
    }
    else{
        $("#valortotalpago").val("0,00");
    }
}

function calculaDescontoValor(){
    $("#valortroco").val("0,00");
    $("#valorDescCento").val("0");

    var valortotal = $("#total_amount_modal").html().replace("R$", "").replace(".", "").replace(",", ".");
    var valorDesconto = $("#valorDesconto").val().replace(".", "").replace(",", ".");
    var valorAPagar = (valortotal - valorDesconto).toFixed(2).replace('.', ',');
    $("#valorAPagar").val(valorAPagar);
    const tipo_pagto = document.querySelector("#cartao");
    if(tipo_pagto.classList.contains("text-success")){
        $("#valortotalpago").val(valorAPagar);

    }
    else{
        $("#valortotalpago").val("0,00");
    }
}

function adicionarAoCarrinho(item){
    // Busca item igual por product_id, price, discount e discountType
    var index = _.findIndex(cart, function(carrinhoItem) {
        return carrinhoItem.product_id === item.product_id && carrinhoItem.price === item.price && carrinhoItem.discount === item.discount && carrinhoItem.discountType === item.discountType;
    });
    if (index === -1) {
        cart.push(item);
        // Atualiza contador de itens distintos
        $('#totalItens').text(cart.length);
    } else {
        cart[index].quantity += item.quantity;
        // Recalcula desconto e subtotal do item existente
        var quantity = cart[index].quantity;
        var price = cart[index].price;
        var discount = cart[index].discount;
        var discountTypeItem = cart[index].discountType;
        var discountTotal = 0;
        if(discountTypeItem === "%"){
            discountTotal = quantity * ((discount * price) / 100);
        }else{
            discountTotal = quantity * discount;
        }
        cart[index].discountValue = discountTotal;
        cart[index].subtotal = Number((price * quantity) - discountTotal).toFixed(2);
    }

}

function deleteItemFromCart(item) {
    var index = _.findIndex(cart, item);
    cart.splice(index, 1);
    show_cart();
}

// function gravarPedido(status, e){
//     if(status == vendaStatus.CONCLUIDA){
//         var valorpago = $("#valortotalpago").val().replace('.','').replace(',', '.');
//         var valorAPagar = $("#valorAPagar").val().replace('.','').replace(',', '.');
//         if(valorpago <= 0){
//             $("#valortotalpago").focus();
//             swal.fire('', 'Digite o valor pago', 'error');
//             return;
//         }
//         if((valorpago - valorAPagar) < 0){
//             $("#valortotalpago").focus();
//             swal.fire('', 'O valor pago é menor que o valor Total', 'error');
//             return;
//         }
//     }

//     if (cart.length < 1) {
//         $("#checkout-modal").modal("hide");
//         swal.fire("", "Pedido sem itens", "error");
//         return false;
//     }

//     var vendasituacao = status;

//     var form_data = {
//         id: $("#pedidoID").val(),
//         idempresa: $("#idempresa").val(),
//         orcamento: $("#orcamento").val(),
//         idclientevendedor: $("#idclientevendedor").val(),
//         faturar: $("#faturar").val(),
//         pdv: 1,
//         observacao: $("#observacao").val(),
//         idcliente: $("#idcliente").isNullOrEmpty() ? 1 : $("#idcliente").val(),
//         id_tipo_pagto: $("#id_forma_pagto").val(),
//         idvendatipo: $("#tipoDeVenda").val(),
//         valorsubtotal: $("#p_subtotal").html().replace("R$", "").replace('.','').replace(',', '.'),
//         valortotalpago: $("#valortotalpago").val().replace('.','').replace(',', '.'),
//         valortotal: $(".valorTotal").html().replace("R$", "").replace('.','').replace(',', '.'),
//         valortroco: $("#valortroco").val().replace('.','').replace(',', '.'),
//         descontovalor : $("#valorDesconto").val().replace('.','').replace(',', '.'),
//         descontoporcento : $("#valorDescCento").val().replace('.','').replace(',', '.'),
//         vendaitens: _.map(cart, function(cart) {
//             return {
//                 idproduto: cart.product_id,
//                 idcart: cart.id,
//                 quantidade: cart.quantity,
//                 discount: cart.discountValue,
//                 valorunitario: cart.price,
//                 name: cart.name,
//                 valortotal: (parseInt(cart.quantity) * cart.price),
//             }
//         })
//     };

//     var total_amount = Number(localStorage.getItem("total_amount"));
//     _.map(cart, function(cart) {
//         localStorage.setItem("total_amount", total_amount + (cart.quantity * cart.price));
//     });

//     $(e).html('<i class="fa fa-spinner fa-spin" style="font-size:18px"></i> Processando...');
//     $(e).prop("disabled", true);

//     var url = '';

//     if($("#pedidoID").isNullOrEmpty()){
//         url = '/pdv/inserir';
//     }else{
//         var id = $("#pedidoID").val();
//         url = '/pdv/alterar/' + id;
//     }

//     Pace.restart();
//     Pace.track(function () {
//     $.ajax({
//         type: 'POST',
//         headers: {
//             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//         },
//         url: url,
//         data: form_data,
//         success: function(data) {
//             $(".emEspera").html(data.emespera);
//             $("#checkout-modal").modal("hide");
//             cart = [];
//             $("#TableNo").text("");
//             $("#total_pago").val("");
//             $("#valortroco").val("");
//             $("#observacao").val("");
//             $("#total_amount_modal").html("R$0,00");
//             $("#finalizarPedido").html('Finalizar');
//             $("#finalizarPedido").prop("disabled", false);
//             $("#id").val("");

//             var title = 'Pedido Finalizado';

//             if(status == vendaStatus.ABERTA){
//                 title = 'Pedido em espera'
//             }

//             toastr.success(title);
//             $('#idsearchphone').val('');
//             $('#idsearchphone').trigger('change');
//             if(status == vendaStatus.CONCLUIDA){
//                 $("#pedidoID").val(data.msg);
//                 $('#impressaoModal').modal('show');
//             }

//             $("#p_subtotal").html("R$0,00");
//             $("#p_discount").html("R$0,00");
//             $("#idcliente").val("");

//             show_cart();

//             if(status == vendaStatus.CONCLUIDA)
//                 $(e).html('Finalizar');
//             else
//                 $(e).html('Pedido em espera');

//             $(e).prop("disabled", false);
//         },
//         error: function(xhr, type, exception) {
//             console.log(xhr);
//             if(xhr.status == 401 || xhr.status == 419){
//                 Swal.fire({
//                     title: "Erro",
//                     text: "Sua sessão expirou, é preciso fazer o login novamente.",
//                     type: "error",
//                     showCancelButton: false,
//                     allowOutsideClick: false,
//                 }).then(function (result) {
//                     $.limparBloqueioSairDaTela();
//                     location.reload();
//                 });
//             }else{
//                 toastr.error(xhr.responseJSON.message);
//                 if(status == vendaStatus.CONCLUIDA)
//                     $(e).html('Finalizar');
//                 else
//                     $(e).html('Pedido em espera');
//                 $(e).prop("disabled", false);
//             }
//         }
//     });
//     });
// }

function show_cart() {
    if (cart.length > 0) {
        var qty = 0;
        var total = 0;
        var discount = 0;
        var cart_html = "";
        var obj = cart;
        $.each(obj, function(key, value) {
            // console.log('show_cart', value)
            qty = Number(value.quantity);
            var itemTotal = Number(value.price * qty);

            // Regra: desconto não pode ser maior que o total do item
            if (value.discountValue > itemTotal) {
                Swal.fire('', 'O desconto não pode ser maior que o valor do item!', 'error');
                // Zera o desconto do item no carrinho (estado) para não afetar os totais
                var idx = _.findIndex(cart, { id: value.id });
                if (idx !== -1) {
                    cart[idx].discount = 0;
                    cart[idx].discountValue = 0;
                    cart[idx].subtotal = Number((cart[idx].price * cart[idx].quantity) - 0).toFixed(2);
                    // atualiza o objeto corrente também
                    value.discount = 0;
                    value.discountValue = 0;
                    value.subtotal = cart[idx].subtotal;
                }
                // Restaura o tipo de desconto do botão do item para '%'
                var $btn = $("button[data-id='" + value.id + "'].btn-change-discount");
                if($btn.length){
                    $btn.html('%');
                    cart[idx] && (cart[idx].discountType = '%');
                }
                // Não adiciona desconto inválido aos acumuladores
                discount += 0;
            } else {
                discount += value.discountValue;
            }

            // Agora gera o HTML do item usando o valor (possivelmente) ajustado acima
            cart_html += '<tr>';
            cart_html += '<td><h5 style="margin:0px;">' + value.name + '</h5></td>';
            cart_html += '<td width="15%"><input type="number" value="' + parseInt(value.quantity) + '" id="item-quantity-'+value.id+'" class="form-control form-control-sm IncOrDecToCart" data-id=' + value.id + ' min="1" step="1" pattern="[0-9]*" inputmode="numeric"></td>';
            cart_html += '<td width="15%"><input type="text" value="' + $.toMoneySimples(value.price) + '" id="item-price-'+value.id+'" class="form-control form-control-sm money priceToCart" data-id=' + value.id + '></td>';
            cart_html += '<td width="15%"><div class="input-group input-group-sm">';
            cart_html += '<input type="text" value="' + $.toMoneySimples(value.discount) + '" id="item-discount-'+value.id+'" class="form-control form-control-sm money discountToCart" data-id=' + value.id + '>';
            cart_html += '<span class="input-group-append"><button type="button" data-id=' + value.id + ' class="btn btn-primary btn-sm btn-change-discount">'+value.discountType+'</button>';
            cart_html += '</span></div></td>';
            cart_html += '<td width="15%" class="text-center"><h5 style="margin:0px;">' + $.toMoney((value.price * value.quantity) - value.discountValue) + '</h5> </td>';
            cart_html += '<td width="10%" class="text-center"><a href="javascript:void(0)"';
            cart_html += 'class="btn btn-sm btn-danger DeleteItem" data-id=' + value.id + '><i class="fa fa-trash"></i></a></td>';
            cart_html += '</tr>';

            total = Number(total) + itemTotal;

        });

        var taxa = 0;

        $("#p_subtotal").html($.toMoney(total));
        $("#p_discount").html($.toMoney(discount));
        $("#valorDesconto").val($.toMoneyVendaSimples(String(discount), false));

        var total_amount = Number(total) - discount;
        $("#total_amount").val(total_amount);
        $("#total_amount_modal").html($.toMoney(total));
        $("#taxa").val(taxa);
        $("#valorAPagar").val($.toMoneyVendaSimples(String(total_amount), false))

        $(".valorTotal").html($.toMoney(total_amount));
        $("#CartHTML").html("");
        $("#CartHTML").html(cart_html);
        count_items = 0;
        cart.forEach(function(conta){
            count_items++;
        });
        $("#totalItens").html(count_items);
        $(".countcart").html(count_items);
    } else {
        count_items = 0;
        $("#totalItens").html(count_items);
        $(".countcart").html(count_items);
        $(".valorTotal").html("R$0,00");
        $("#p_subtotal").html("R$0,00");
        $("#p_discount").html("R$0,00");
        $("#valorDesconto").val("0,00");
        $("#total_amount_modal").html("R$0,00");
        $("#CartHTML").html("");
    }

}




/////////////////////
// AÇÕES DE NAVEGAÇÂO
/////////////////////
$('#modalCartaoMult').on('show.bs.modal', function () {
    var nomeCliente = $('#desCli').text() || '';
    $('#modalCartaoMultLabel').text('Cartões Registrados Para: ' + nomeCliente);
});

$("body").on("change", "#find-product", function(e) {
    var data = $(this).select2('data')[0];
    if (data) {
        if(!data.id){
            return;
        }
        var quantity = parseInt($('#item-quantity').val()) == 0 ? 1 : parseInt($('#item-quantity').val());

        $("#desProd").html(data.fardes);
        $("#item-price").val(data.farvre);
        $("#item-price").trigger('keyup');
        $('#btn-adicionar-item').data('id', data.id);

        $("#item-subtotal").val(Number(data.farvre * quantity).toFixed(2));
        $("#item-subtotal").trigger('keyup');

        $('#pesquisar-produto-modal').modal('hide');

        $("#item-quantity").val(1);
    }
});

$("body").on("keyup blur", "#item-quantity, #item-discount, #item-price", function(e) {
    // Sempre trata o preço para float, removendo todos os pontos e trocando vírgula por ponto
    var rawPrice = $('#item-price').val();
    var price = 0;
    if (typeof rawPrice === 'string') {
        price = parseFloat(rawPrice.replace(/\./g, '').replace(',', '.')) || 0;
    } else {
        price = Number(rawPrice) || 0;
    }
    var quantity = parseInt($('#item-quantity').val()) || 1;
    var discount = $.tratarValor($('#item-discount').val());
    var discountTotal = 0;
    if(quantity > 0){
        if(discountType === "%"){
            discountTotal = quantity * ((discount * price) / 100);
        }else{
            discountTotal = quantity * discount;
        }
        var subtotal = Number((price * quantity) - discountTotal);
        $("#item-subtotal").val( $.toMoneySimples(subtotal.toFixed(2)) );
    }
});

$("body").on("keyup", "#item-quantity", function(e) {
    if(e.keyCode == 13){
        //$(this).blur();
        $('#item-discount').focus();
    }
});

$("body").on("keyup", "#item-discount", function(e) {
    if(e.keyCode == 13){
        //$(this).blur();
        $('#item-price').focus();
    }
});

$("body").on("keyup", "#item-price", function(e) {
    if(e.keyCode == 13){
        //$(this).blur();
        $('#btn-adicionar-item').trigger('click');
    }
});

$("body").on("click", "#btn-adicionar-item", async function(){

    var quantity = $.tratarValor($('#item-quantity').val());
    // Corrige o tratamento do preço para aceitar valores grandes
    var rawPrice = $('#item-price').val();
    var price = 0;
    if (typeof rawPrice === 'string') {
        price = parseFloat(rawPrice.replace(/\./g, '').replace(',', '.')) || 0;
    } else {
        price = Number(rawPrice) || 0;
    }
    var discount = $.tratarValor($('#item-discount').val());
    var discountValue = 0;

    var id = $(this).data('id');
    var descricao = $("#desProd").html();

    if(discountType === "%"){
        discountValue = ((discount * price) / 100) * quantity;
        if(((discount * price) / 100) * quantity > price){
            Swal.fire('', 'O desconto não pode ser maior que o valor do item!', 'error');
            return;
        }
    }else{
        discountValue = discount;
        if(discount > price){
            Swal.fire('', 'O desconto não pode ser maior que o valor do item!', 'error');
            return;
        }
    }

    if(descricao.length === 0 || descricao === ""){
        toastr.error('Selecione um produto.');
        return;
    }

    if(quantity == 0){
        toastr.error('A Quantidade não pode ficar zerada.');
        return;
    }

    if(price == 0){
        toastr.error('O Preço não pode ficar zerado.');
        return;
    }

    // Gera um id único para o item
    var item_uid = 'item_' + Date.now() + '_' + Math.floor(Math.random() * 10000);
    var prodTipoId = $('#produto_tipo_id').val();

    var item = {
        id: item_uid,
        product_id: parseInt(id),
        price: price,
        name: descricao,
        quantity: quantity,
        discount: discount,
        discountType: discountType,
        discountValue: discountValue,
        produto_tipo: prodTipoId,
    };

    adicionarAoCarrinho(item);
    show_cart();

    $('#item-quantity').val('1');
    $('#item-discount').val('0,00');
    $('#item-price').val('0,00');
    $('#item-subtotal').val('0,00');
    $('#find-product').select2('data', null);
    $('#find-product').val(null);
    $('#find-product').trigger('change');
    $("#desProd").html('')
    $('#getProduto').val('');
    // Limpa os campos do modal de produto
    $('#produto_dmf').val(null).trigger('change');
    $('#produto_dmf_id').val('');
    $('#produto_tipo_id').val('');

});

$("body").on("click", "#btn-change-discount", function(){
        if($(this).html() === "%"){
            $(this).html("R$");
            discountType = "R$";
        }else{
            $(this).html("%");
            discountType = "%";
        }
        // Recalcula o subtotal ao trocar o tipo de desconto
        var quantity = $.tratarValor($('#item-quantity').val());
        var discount = $.tratarValor($('#item-discount').val());
        var price = $.tratarValor($('#item-price').val());
        var discountTotal = 0;
        if(quantity > 0){
            if(discountType === "%"){
                discountTotal = quantity * ((discount * price) / 100);
            }else{
                discountTotal = quantity * discount;
            }
            $("#item-subtotal").val( $.toMoneySimples(Number((price * quantity) - discountTotal).toFixed(2)) );
        }
});

$("body").on("click", ".btn-change-discount", function(){
        var id = $(this).data('id');
        var index = _.findIndex(cart, { id : id});
        if($(this).html() === "%"){
            $(this).html("R$");
            cart[index].discountType = "R$";
        }else{
            $(this).html("%");
            cart[index].discountType = "%";
        }
        // Recalcula o desconto e total do item
        var quantity = cart[index].quantity;
        var price = cart[index].price;
        var discount = cart[index].discount;
        var discountTotal = 0;
        if(cart[index].discountType === "%"){
            discountTotal = quantity * ((discount * price) / 100);
        }else{
            discountTotal = quantity * discount;
        }
        cart[index].discountValue = discountTotal;
        cart[index].subtotal = Number((price * quantity) - discountTotal).toFixed(2);
        show_cart();
});

$("body").on("click", "#showModalCliente", function(){
    finalizarClick = false;
    $("#salvarCliente").html("OK")
    $("#modalCliente").modal('show');
});

$("body").on("click", "#btnPesquisarProduto", function(){
    $("#pesquisar-produto-modal").modal('show');
});

$("body").on("click", "#btnPesquisarCliente", function(){
    $("#pesquisar-cliente-modal").modal('show');
});

///////////////////////////
// AÇÃO DE FINALIZAR PEDIDO
$("body").on("click", "#checkout", function() {
    finalizarClick = true;

    ///////////////////////////////////
    // Validação de cliente selecionado
    var clienteSelecionado = $.isNotNullAndNotEmpty($("#cliente_cadastro_id").val()) || $.isNotNullAndNotEmpty($("#idcliente").val());
    if (!clienteSelecionado) {
        Swal.fire({
            icon: 'error',
            title: 'Cliente não selecionado',
            text: 'Selecione um cliente antes de finalizar a venda.',
            confirmButtonText: 'OK',
            allowOutsideClick: false
        });
        return;
    }

    ////////////////////////////////////
    // Validação de produtos selecionado
    // var total_pedido = $(".valorTotal").html().replace("R$", "");
    if (cart.length == 0) {
        Swal.fire({
            icon: 'error',
            title: 'Nenhum produto adicionado',
            text: 'Adicione ao menos um item no pedido.',
            confirmButtonText: 'OK',
            allowOutsideClick: false
        });
        return;
    }

    ///////////////////////////////////////////
    // Atualiza os valores do modal de checkout
    $("#checkout_subtotal").text($("#p_subtotal").text());
    $("#checkout_desconto").text($("#p_discount").text());
    $("#checkout_total").text($(".valorTotal").text());
    $("#valortotalacobrar").text($(".valorTotal").text());

    //////////////////////////////////////////
    // Monta o carrinho para enviar ao backend
    carrinho = cart.map(function(item) {
        var vlr_total = parseBRL($("#checkout_total").text()) || 0;
        var vlr_brt_item = (item.price * item.quantity);
        var vlr_discount_item = (item.discountValue && parseFloat(item.discountValue) > 0) ? parseFloat(item.discountValue) : 0;
        var vlr_liqu_item = vlr_brt_item - vlr_discount_item;

        if (vlr_total > 0) {
            proporcao_item_total = vlr_liqu_item / vlr_total;
        }

        return {
            produto_tipo: item.produto_tipo || '',
            proporcao_item: proporcao_item_total,
            produto_id: item.product_id,
            qtd_item: item.quantity,
            vlr_unit_item: item.price,
            vlr_brut_item: vlr_brt_item,
            vlr_desc_item: vlr_discount_item,
            vlr_liqu_item: vlr_brt_item - vlr_discount_item
        };
    });

    ////////////////////////////
    // Exibe o modal de checkout
    $("#checkout-modal").modal("show");

});

$("body").on("change", "#idsearchphone", function(e) {
    var data = $(this).select2('data')[0];
    if (data) {
        $("#nomefantasia").val(data['nomefantasia']);
        $("#razaosocial").val(data['razaosocial']);
        $("#telefone").val(data['telefone']);
        $("#celular").val(data['celular']);
        $("#cep").val(data['cep']);
        $("#endereco").val(data['endereco']);
        $("#numero").val(data['numero']);
        $("#complemento").val(data['complemento']);
        $("#bairro").val(data['bairro']);
        $("#pontoreferencia").val(data['pontoreferencia']);
        $("#idenderecotipo").val(data['idenderecotipo']);
        $('#idenderecotipo').trigger('change');
        $("#id").val(data['id']);
        $("#idcliente").val(data['id']);
        var dataCidade = {
            "id": data['idcidade'],
            "text": data['cidadeDescricao'],
        }
        $("#idcidade").select2("trigger", "select", {
            data: dataCidade,
        });

        var dataEstado = {
            "id": data['idestado'],
            "text": data['estadoDescricao'],
        }
        $("#idestado").select2("trigger", "select", {
            data: dataEstado,
        });

    } else {
        $('#idcidade').val(''); // Select the option with a value of ''
        $('#idcidade').trigger('change'); // Notify any JS components that the value changed
        $('#idestado').val('');
        $('#idestado').trigger('change');
        $("#nomefantasia").val("");
        $("#razaosocial").val("");
        $("#telefone").val("");
        $("#celular").val("");
        $("#cep").val("");
        $("#endereco").val("");
        $("#numero").val("");
        $("#complemento").val("");
        $("#bairro").val("");
        $("#pontoreferencia").val("");
        $("#idenderecotipo").val("");
        $('#idenderecotipo').trigger('change');
        $("#id").val("");
        $("#idcliente").val("");
    }
});

$("body").on("click", ".deleteHoldOrder", function(e) {

    var id = $(this).data('id');
    var token = $('meta[name="csrf-token"]').attr("content");
    var url = "/pdv/" + id;
    Pace.restart();
    Pace.track(function () {
        $.ajax({
            header: {
                "X-CSRF-TOKEN": token,
            },
            url: url,
            type: "post",
            data: { id: id, _method: "delete", _token: token },
        }).done(function (response) {
            $(".emEspera").html(response.emespera);
            Swal.fire({
                title: response.title,
                text: response.text,
                type: response.type,
                showCancelButton: false,
                allowOutsideClick: false,
            }).then(function (result) {
                if (response.type === "error") return;
                if (result.value) {
                    $(".deleteHoldOrder").parents('tr').first().remove();
                    if($(".deleteHoldOrder").parents('tr').first().length <= 0)
                        $("#listaDePedidosModal").modal('hide');
                }
            });
        }).fail(function () {
            Swal.fire(
                "Oops...",
                "Algo deu errado ao tentar delatar!",
                "error"
            );
        });
    });

    e.preventDefault();
});

$(".form-control").on("keyup", function(){
    $(this).removeClass("is-invalid");
});

$("body").on("click", "#ClearForm", function() {
    $('#idsearchphone').val('');
    $('#idsearchphone').trigger('change');
    $("#modalCliente").modal("hide");
});

$("body").on("input", "#valorDescCento", function(){
    calculaDescontoPorcentagem();
});

$("body").on("keyup", "#valorDesconto", function(){
    calculaDescontoValor();
});

$("body").on("click", "#salvarCliente", function() {

    var form_data = {
        razaosocial: $("#razaosocial").val(),
        nomefantasia: $("#nomefantasia").val(),
        telefone: $("#telefone").val(),
        celular: $("#celular").val(),
        cep: $("#cep").val(),
        endereco: $("#endereco").val(),
        numero: $("#numero").val(),
        complemento: $("#complemento").val(),
        bairro: $("#bairro").val(),
        pontoreferencia: $("#pontoreferencia").val(),
        idcidade: $('#idcidade').val(),
        idestado: $('#idestado').val(),
        idenderecotipo: $("#idenderecotipo").val(),
        id: $("#id").val()
    }

    if ($("#razaosocial").val() == "") {
        toastr.error('O campo Nome deve ser preenchido')
        $("#razaosocial").addClass("is-invalid");
        return false;
    }

    var cel = $("#celular").val().replace(/[() -]/g, '');
    var tel = $("#telefone").val().replace(/[() -]/g, '');

    if ($("#celular").val() == "" && $("#telefone").val() == "")  {
        toastr.error('O campo Celular ou Telefone deve ser preenchido')
        $("#celular").addClass("is-invalid");
        return false;
    }

    if (cel.length < 11 && tel.length < 10) {
        toastr.error('O campo Celular ou Telefone contém um valor inválido')
        $("#celular").addClass("is-invalid");
        return false;
    }

    if ($("#endereco").val() == "") {
        toastr.error('O campo Endereço deve ser preenchido')
        $("#endereco").addClass("is-invalid");
        return false;
    }

    if ($("#idcidade").val() == "") {
        toastr.error('O campo Cidade deve ser preenchido')
        $("#idcidade").select2('open');
        $("#idcidade").select2('focus');
        $("#idcidade").addClass("is-invalid");
        $("#idcidade")
        .closest(".form-group")
        .find(".select2-selection")
        .css("border-color", "#dc3545")
        .addClass("text-danger");
        return false;
    }

    if ($("#idestado").val() == "") {
        toastr.error('O campo Estado deve ser preenchido')
        $("#idestado").select2('open');
        $("#idestado").select2('focus');
        $("#idestado").addClass("is-invalid");
        $("#idestado")
        .closest(".form-group")
        .find(".select2-selection")
        .css("border-color", "#dc3545")
        .addClass("text-danger");
        return false;
    }

    $(this).html('<i class="fa fa-spinner fa-spin" style="font-size:18px"></i> Processando...');
    $(this).prop("disabled", true);
    Pace.restart();
    Pace.track(function () {
        $.ajax({
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/pdv/storeclient',
            data: form_data,
            success: function(msg) {
                $("#salvarCliente").html('Próximo');
                $("#salvarCliente").prop("disabled", false);
                var obj = msg;
                if (obj['message'] == "OK") {
                    $("#modalCliente").modal("hide");

                    if(finalizarClick){
                        $("#checkout-modal").modal("show");
                    }

                    $("#idcliente").val(obj['id']);
                    $("#TableNo").html("(" +$("#razaosocial").val()+ ")");
                    $("#TableNoCart").html("(" + $("#razaosocial").val() + ")");
                } else {
                    toastr.error(msg.message,msg.title);
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {

                if(XMLHttpRequest.status == 401){
                    Swal.fire({
                        title: "Erro",
                        text: "Sua sessão expirou, é preciso fazer o login novamente.",
                        type: "error",
                        showCancelButton: false,
                        allowOutsideClick: false,
                    }).then(function (result) {
                        $.limparBloqueioSairDaTela();
                        location.reload();
                    });
                    }
                    else if( XMLHttpRequest.status === 400 ) {
                        var errors = $.parseJSON(XMLHttpRequest.responseText);
                        //console.log(errors);
                        $.each(errors.message, function (key, val) {
                            toastr.error(val);
                        });
                    }else{
                        toastr.error("Opos, algo deu errado.");
                    }
                    $("#salvarCliente").html('Próximo');
                    $("#salvarCliente").prop("disabled", false);
                }
        });
    });
});

$('#impressaoModal').on('hidden.bs.modal', function() {
    $("#pedidoID").val("");
});

$('#checkout-modal').on('shown.bs.modal', function() {
    $(".payment-box-active").removeClass("payment-box-active");
    $("#valortotalpago").val("0,00");
    // $("#valorDescCento").val("0");
    // $("#valorDesconto").val("0,00");
    var valorTotal = $("#total_amount_modal").html().replace("R$", "");
    //$ ("#valorAPagar").val(valorTotal);
    // $("#Dinheiro").addClass("payment-box-active");
    $("#valortroco").val("0,00");
    $("#valortotalpago").habilitar();
    $("#valortotalpago").focus();
    $("#valortotalpago").select();
    // Não seleciona nenhuma forma de pagamento por padrão
    $("#valortroco").val("0,00");
    $("#valortotalpago").habilitar();
    $("#valortotalpago").focus();
    $("#valortotalpago").select();
});

$('#checkout-modal').on('hidden.bs.modal', function() {
    // Ao fechar o modal, remove seleção de pagamento
    $(".payment-box-active").removeClass("payment-box-active");
});

$('#modalCliente').on('shown.bs.modal', function() {
    if($("#idsearchphone").isNullOrEmpty()){
        setTimeout(function(){
            $("#idsearchphone").select2('open');
            $("#idsearchphone").select2('focus');
        },950);
    }
});

$("body").on("click", ".payment-box", function() {
    $(".payment-box-active").removeClass("payment-box-active");
    $("#id_forma_pagto").val($(this).attr("data-identificacao"));
    $("#id_forma_pagto").trigger('change');

    $(this).addClass("payment-box-active");
    if ($(this).attr("data-id") == "Dinheiro") {
        $("#valortotalpago").val("0,00");
        $("#valortroco").val("0,00");
        $("#valortotalpago").habilitar();
        $("#valortotalpago").focus();
        $("#valortotalpago").select();
    } else {
        $("#valortotalpago").desabilitar();
        $("#valortotalpago").val($("#valorAPagar").val());
        $("#valortroco").val("0,00");
    }
});

$(function() {
    $(".navbar-minimalize").click();
});

$("body").on("keyup", "#valortotalpago", function() {
    var total_amount = $("#total_amount").val();
    var valorAPagar = $("#valorAPagar").val().replace('.', '').replace(',', '.');
    var desconto = $("#valorDescCento").val().replace('.', '').replace(',', '.');
    var descontoValor = $("#valorDesconto").val().replace('.', '').replace(',', '.');
    var valortotalpago = $(this).val().replace('.', '').replace(',', '.');
    var valortroco = 0;
    if(desconto > 0 || descontoValor > 0)
        valortroco = Number(valortotalpago) - Number(valorAPagar);
    else
        valortroco = Number(valortotalpago) - Number(total_amount);

        $("#valortroco").val(valortroco.toFixed(2).replace('.', ','));

});

$("body").on("keyup", "#getCliente", function (e) {
    // Funções de validação de CPF/CNPJ
    function validaCPF(cpf) {
        cpf = cpf.replace(/\D/g, '');
        if (cpf.length !== 11 || /^([0-9])\1+$/.test(cpf)) return false;
        var soma = 0, resto;
        for (var i = 1; i <= 9; i++) soma += parseInt(cpf[i-1]) * (11 - i);
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf[9])) return false;
        soma = 0;
        for (var i = 1; i <= 10; i++) soma += parseInt(cpf[i-1]) * (12 - i);
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf[10])) return false;
        return true;
    }

    function validaCNPJ(cnpj) {
        cnpj = cnpj.replace(/\D/g, '');
        if (cnpj.length !== 14) return false;
        if (/^([0-9])\1+$/.test(cnpj)) return false;
        var tamanho = cnpj.length - 2;
        var numeros = cnpj.substring(0, tamanho);
        var digitos = cnpj.substring(tamanho);
        var soma = 0;
        var pos = tamanho - 7;
        for (var i = tamanho; i >= 1; i--) {
            soma += numeros[tamanho - i] * pos--;
            if (pos < 2) pos = 9;
        }
        var resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos[0]) return false;
        tamanho = tamanho + 1;
        numeros = cnpj.substring(0, tamanho);
        soma = 0;
        pos = tamanho - 7;
        for (var i = tamanho; i >= 1; i--) {
            soma += numeros[tamanho - i] * pos--;
            if (pos < 2) pos = 9;
        }
        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos[1]) return false;
        return true;
    }

    e.preventDefault();
    var texto = $(this).val();
    // Remove apenas '.', '/', '-' para manter compatibilidade com backend
    texto = texto.replace(/[\.\/-]/g, '');

    // Validação de documento ao pressionar Enter
    if (e.key == 'Enter') {
        var doc = texto.replace(/\D/g, '');
        if (!(validaCPF(doc) || validaCNPJ(doc))) {
            Swal.fire({
                icon: 'error',
                title: 'Documento inválido',
                text: 'O número de documento informado não é um CPF ou CNPJ válido.',
                confirmButtonText: 'OK',
                allowOutsideClick: false
            });
            return;
        }
    }
    var url = "/cliente/get-client?parametro=" + texto;

    if (e.key == 'Enter' && texto ) {
        Pace.restart();
        Pace.track(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: url,
                type: "GET",
            })
            .done(function (response) {
                    // Espera que o backend retorne: {clientes: [...], cartoes: [...]}
                    if (!response || !response.clientes || response.clientes.length === 0) {
                        Swal.fire(
                            "Oops...",
                            "Cliente não encontrado!",
                            "error"
                        );
                        // Limpa campos se não encontrar cliente, mas só ao pressionar Enter
                        $('#desCli').text('');
                        $('#cliente_cadastro_id').val('');
                        $('#getCliente').val('');
                        return;
                    }

                    window.responseCliente = cliente;
                    window.clientId = response.clientes[0].id;
                    window.clientName = response.clientes[0].text;
                    window.clientDoc = response.clientes[0].cliente_doc;
                    window.clienteDataFech = response.clientes[0].cliente_dt_fech;
                    window.clientPontos = response.clientes[0].cliente_pts;
                    window.responseCartoesCliente = response.clientes[0].cartoes || [];

                    // Verifica status de inadimplência
                    if (response.clientes[0].cliente_sts && response.clientes[0].cliente_sts === "MN") {
                        Swal.fire({
                            icon: 'warning',
                            title: 'ATENÇÃO',
                            html: '<div>Identificamos que este cliente, nos últimos 6 meses,<br>' +
                                  'tem histórico de atraso nos pagamentos.<br><br>' +
                                  'Sugestões:<br>' +
                                  '- Solicite um valor de entrada.<br>' +
                                  '- De preferência por um meio de pagamento à vista.<br>' +
                                  '- Coloque juros nas parcelas para cobrir um possível atraso.<br><br>' +
                                  '<strong>Deseja realmente continuar?</strong></div>',
                            showCancelButton: true,
                            confirmButtonText: 'OK',
                            cancelButtonText: 'Cancelar',
                            allowOutsideClick: false
                        }).then(function(result) {
                            if (!result.isConfirmed) {
                                // Limpa seleção se cancelar
                                $('#desCli').text('');
                                $('#cliente_cadastro_id').val('');
                                $('#getCliente').val('');
                            }
                        });
                    }

                    // Preenche campos do cliente
                    $('#desCli').text(window.clientName || cliente.nome || cliente.razaosocial || '');
                    $('#cliente_cadastro_id').val(window.clientId);
                    // Só preenche o campo se vier documento válido
                    if (window.clientDoc) {
                        var doc = window.clientDoc.replace(/\D/g, '');
                        if (doc.length === 11) {
                            $('#getCliente').val(doc.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4'));
                        } else if (doc.length === 14) {
                            $('#getCliente').val(doc.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5'));
                        } else {
                            $('#getCliente').val(window.clientDoc);
                        }
                    } else {
                        $('#getCliente').val('');
                    }


            })
            .fail(function (xhr, status, error) {
                $('#desCli').text('');
                $('#cliente_cadastro_id').val('');
                $('#getCliente').val(texto);
                if (xhr.status == 403) {
                    Swal.fire(
                        "Oops...",
                        "Você não tem permissão, contate o administrador!",
                        "error"
                    );
                } else if (xhr.status == 404){
                    Swal.fire(
                        "Oops...",
                        "Cliente não encontrado!",
                        "error"
                    );
                }else if(xhr.status == 401 || xhr.status == 419){
                    Swal.fire({
                        title: "Erro",
                        text: "Sua sessão expirou, é preciso fazer o login novamente.",
                        type: "error",
                        showCancelButton: false,
                        allowOutsideClick: false,
                    }).then(function (result) {
                        $.limparBloqueioSairDaTela();
                        location.reload();
                    });
                }else {
                    Swal.fire(
                        "Oops...",
                        "Algo deu errado!",
                        "error"
                    );
                }
            });
        });
    }
});

$("body").on("keyup", "#getProduto", function (e) {

    e.preventDefault();
    var texto = $(this).val();
    var url = "/produto/obter-descricao-produto?pdv='sim'&parametro=" + texto;
    var quantity = $.tratarValor($('#item-quantity').val());

    if (e.key == 'Enter' && texto ) {
        Pace.restart();
        Pace.track(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: url,
                type: "GET",
            })
            .done(function (response) {
                // Espera que o backend retorne um array de produtos
                var produto = Array.isArray(response) ? response[0] : response;
                if (!produto || !produto.produto_id || !produto.produto_dm || !produto.produto_vlr) {
                    Swal.fire(
                        "Oops...",
                        "Produto não encontrado!",
                        "error"
                    );
                    return;
                }
                if(parseFloat(produto.produto_vlr) == 0){
                    Swal.fire(
                        "Oops...",
                        "Produto sem preço, contate o administrador.",
                        "error"
                    );
                    return;
                }

                // Preenche campos conforme solicitado
                $('#getProduto').val(texto); // Mantém o valor digitado
                $('#desProd').html(produto.produto_dm); // Descrição do produto
                $('#item-price').val($.toMoneySimples(produto.produto_vlr)); // Valor do produto

            })
            .fail(function (xhr, status, error) {
                $('#desProd').html('');
                $('#item-price').val('0,00');
                $('#item-quantity').val('1');
                $('#item-discount').val('0,00');
                $('#item-subtotal').val('0,00');
                $('#find-product').select2('data', null);
                $('#find-product').val(null);
                $('#find-product').trigger('change');
                discountType = "%";

                if (xhr.status == 403) {
                    Swal.fire(
                        "Oops...",
                        "Você não tem permissão, contate o administrador!",
                        "error"
                    );
                } else if (xhr.status == 404){
                    Swal.fire(
                        "Oops...",
                        "Produto não encontrado!",
                        "error"
                    );
                }else if(xhr.status == 401 || xhr.status == 419){
                    Swal.fire({
                        title: "Erro",
                        text: "Sua sessão expirou, é preciso fazer o login novamente.",
                        type: "error",
                        showCancelButton: false,
                        allowOutsideClick: false,
                    }).then(function (result) {
                        $.limparBloqueioSairDaTela();
                        location.reload();
                    });
                }else {
                    Swal.fire(
                        "Oops...",
                        "Algo deu errado!",
                        "error"
                    );
                }
            });
        });
    }
});

$("body").on("click", "#limparCarrinho", function() {

    if(cart.length > 0){
        Swal.fire({
                title: "Cancelar?",
                text: "Deseja realmente Cancelar?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#1c0065",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, Cancelar!",
                preConfirm: function () {
                    return new Promise(function (resolve) {
                        cart = new Array();
                        $("#totalItens").html("0");
                        $("#TableNo").text("");
                        $("#TableNoCart").text("");
                        $(".valorTotal").html("R$0,00");
                        $("#CartHTML").html("");
                        $("#p_subtotal").html("R$0,00");
                        $(".totalPagar").html("R$0,00");
                        $("#pedidoID").val("");
                        $('#idsearchphone').val('');
                        $('#idsearchphone').trigger('change');
                        $('#tipoDeVenda').val(vendaTipo.ENTREGAR);
                        $('#tipoDeVenda').trigger('change');
                        Swal.fire({
                            title: "Sucesso",
                            text: "Sucesso",
                            icon: "success",
                        });
                    });
                },
                allowOutsideClick: false,
            });
        }
});

$("body").on("blur change", ".IncOrDecToCart", function(e) {
    //debugger;

    var item_id = $(this).attr("data-id");
    var index = _.findIndex(cart, { id: item_id });
    // Força a quantidade a ser inteiro
    var rawQuantity = String($(this).val()).replace(/\D/g, '');
    var quantity = parseInt(rawQuantity) || 1;
    $(this).val(quantity); // Atualiza o campo na tabela para inteiro
    if (quantity <= 0) {
        deleteItemFromCart({ id: item_id });
    } else {
        cart[index].quantity = quantity;
        var price = cart[index].price;
        var discount = cart[index].discount;
        var discountTypeItem = cart[index].discountType;
        var discountTotal = 0;
        if(discountTypeItem === "%"){
            discountTotal = quantity * ((discount * price) / 100);
        }else{
            discountTotal = quantity * discount;
        }
        cart[index].discountValue = discountTotal;
        cart[index].subtotal = Number((price * quantity) - discountTotal).toFixed(2);
    }
    show_cart();
});

$("body").on("keyup", ".IncOrDecToCart", function(e) {
    if(e.keyCode == 13){
    $(this).blur();
    $(this).trigger('blur');
    }
});

$("body").on("blur change", ".priceToCart", function(e) {
    var item_id = $(this).attr("data-id");
    var index = _.findIndex(cart, { id: item_id });
    // Trata o valor para float correto
    var rawPrice = String($(this).val());
    var price = parseFloat(rawPrice.replace(/\./g, '').replace(',', '.')) || 0;
    cart[index].price = price;
    var quantity = cart[index].quantity;
    var discount = cart[index].discount;
    var discountTypeItem = cart[index].discountType;
    var discountTotal = 0;
    if(discountTypeItem === "%"){
        discountTotal = quantity * ((discount * price) / 100);
    }else{
        discountTotal = quantity * discount;
    }
    cart[index].discountValue = discountTotal;
    cart[index].subtotal = Number((price * quantity) - discountTotal).toFixed(2);
    show_cart();
});

$("body").on("keyup", ".priceToCart", function(e) {
    if(e.keyCode == 13){
    $(this).blur();
    $(this).trigger('blur');
    }
});

$("body").on("blur change", ".discountToCart", function(e) {
    var item_id = $(this).attr("data-id");
    var index = _.findIndex(cart, { id: item_id });
    cart[index].discount = $.tratarValor($(this).val());
    var quantity = cart[index].quantity;
    var price = cart[index].price;
    var discount = cart[index].discount;
    var discountTypeItem = cart[index].discountType;
    var discountTotal = 0;
    if(discountTypeItem === "%"){
        discountTotal = quantity * ((discount * price) / 100);
    }else{
        discountTotal = quantity * discount;
    }
    cart[index].discountValue = discountTotal;
    cart[index].subtotal = Number((price * quantity) - discountTotal).toFixed(2);
    show_cart();
});

$("body").on("keyup", ".discountToCart", function(e) {

    if(e.keyCode == 13){
    $(this).blur();
    $(this).trigger('blur');
    }
});

$("body").on("click", ".DeleteItem", function() {
    var item = {
        id: $(this).attr("data-id")
    };
    deleteItemFromCart(item);
});

$("body").on("click", "#imprimirCozinha", function() {
    $('body').find('iframe[id=iframe_impressao]').attr('src', '/pdv/cozinha/' + $("#pedidoID").val());
});

$("body").on("click", "#imprimirCupom", function() {
    $('body').find('iframe[id=iframe_impressao]').attr('src', '/pdv/cupom/' + $("#pedidoID").val() );
});

$("body").on("click", "#printPedidosBtn", function() {
    var id = $(this).attr('data-id')
    if($.isNotNullAndNotEmpty(id)){
        Pace.restart()
        Pace.track(function(){
            $('body').find('iframe[id=iframe_impressao]').attr('src', '/pdv/cupom/' + id)
        })
    }else{
        Swal.fire(
            "Oops...",
            "Selecione um pedido..",
            "error"
        );
    }
});

$("body").on("click", "#finalizarPedido", function() {
    //socket.emit("guiche history");
    gravarPedido(vendaStatus.CONCLUIDA, this);
});

$('#pesquisar-produto-modal').on('hidden.bs.modal', function() {

    setTimeout(() => {
        $('#item-quantity').focus();
    }, 500);
});

$('#pesquisar-produto-modal').on('shown.bs.modal', function() {

    $("#find-product").select2('focus');
    $("#find-product").select2('open');
});

$('#pesquisar-cliente-modal').on('shown.bs.modal', function() {

    $("#find-client").select2('focus');
    $("#find-client").select2('open');
});

shortcut.add("F1", function (e) {
    e.preventDefault();
    $('#pesquisar-produto-modal').modal('show');
});

shortcut.add("F2", function (e) {
    e.preventDefault();
    $('#item-quantity').focus();
    $('#item-quantity').select();

});

shortcut.add("F3", function (e) {
    e.preventDefault();
    $('#item-discount').focus();
    $('#item-discount').select();

});
shortcut.add("F4", function (e) {
    e.preventDefault();
    $('#item-price').focus();
    $('#item-price').select();
});

shortcut.add("F5", function (e) {
    e.preventDefault();
});

shortcut.add("F6", function (e) {
    e.preventDefault();
    $('#pesquisar-cliente-modal').modal('show');
});

shortcut.add("F9", function (e) {
    e.preventDefault();
    $("#checkout").trigger('click');
});

shortcut.add("F7", function (e) {
    e.preventDefault();
    $("#limparCarrinho").trigger('click');
});

shortcut.add("ESC", function (e) {
    e.preventDefault();
    $("#pesquisar-produto-modal").modal('hide');
});




////////////////////////////////////////////////////////////////////////
// FUNÇOES EXECUTADAS SOMENTE DEPOIS QUE TODO A PÁGINA ESTIVER CARREGADA
////////////////////////////////////////////////////////////////////////

// Recalcula parcelas do cartão ao alterar o checkbox "vendaSemJurosCartao"
$(document).on('change', '#vendaSemJurosCartao', function() {
    atualizarParcelasCartao();
});

$(document).ready(function () {

    // Atualiza os valores visíveis do checkout ao mudar as parcelas
    atualizarCheckoutValores();

    $('#parcelasCartao').on('change', function () {
        // Pegue o valor total original (exemplo: do campo #checkout_total)
        var valorTotal = parseFloat($('#valortotalacobrar').val().replace(/[^\d,]/g, '').replace(',', '.')) || 0;

        // Pegue o valor total com juros da opção selecionada (exemplo: pode estar em um data-attribute ou calculado via JS)
        // Exemplo: <option value="2" data-total-com-juros="120.00">2x</option>
        var totalComJuros = parseFloat($('#parcelasCartao option:selected').data('total-com-juros')) || valorTotal;

        // Só calcula se o checkbox NÃO estiver marcado
        if (!$('#vendaSemJurosCartao').is(':checked')) {
            var juros = totalComJuros - valorTotal;
            // Verifica se o juros é maior que zero
            if (juros > 0) {
                $('#checkout_juros').text('R$ ' + juros.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#checkout_total').text('R$ ' + totalComJuros.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            } else {
                // Se não houver juros, zera os campos
                $('#checkout_juros').text('R$ 0,00');
                $('#checkout_total').text($("#valortotalacobrar").val());
            }

        } else {
            $('#checkout_juros').text(' R$ 0,00');
            $('#checkout_total').text($("#valortotalacobrar").val());
        }

    });

    // Também atualize ao marcar/desmarcar o checkbox
    $('#vendaSemJurosCartao').on('change', function () {
        $('#parcelasCartao').trigger('change');
    });

    // Função para atualizar os textos das opções do select PrimeiraParaCartao
    function atualizarOpcoesPrimeiraParaCartao() {
        var $select = $('#PrimeiraParaCartao');
        var hoje = new Date();
        $select.find('option').each(function() {
            var val = $(this).val();
            if (val == '6') {
                $(this).text('Rotativo');
            } else if (val == '7') {
                var mes = hoje.getMonth() + 2;
                var ano = hoje.getFullYear();
                if (mes > 12) { mes = 1; ano++; }
                var mesNome = new Date(ano, mes-1, 1).toLocaleString('pt-BR', { month: 'long' });
                $(this).text(mesNome.charAt(0).toUpperCase() + mesNome.slice(1) + ' ' + ano);
            } else if (val == '8') {
                var mes = hoje.getMonth() + 3;
                var ano = hoje.getFullYear();
                if (mes > 12) { mes -= 12; ano++; }
                var mesNome = new Date(ano, mes-1, 1).toLocaleString('pt-BR', { month: 'long' });
                $(this).text(mesNome.charAt(0).toUpperCase() + mesNome.slice(1) + ' ' + ano);
            } else if (val == '9') {
                var mes = hoje.getMonth() + 4;
                var ano = hoje.getFullYear();
                if (mes > 12) { mes -= 12; ano++; }
                var mesNome = new Date(ano, mes-1, 1).toLocaleString('pt-BR', { month: 'long' });
                $(this).text(mesNome.charAt(0).toUpperCase() + mesNome.slice(1) + ' ' + ano);
            }
        });
    }

    $('#btnPesquisarCliente').on('click', function() {
        $('#getCliente').val('');
        $('#desCli').text('');
    });

    $(document).on('change', '#cliente_cadastro_id', function() {
        atualizarOpcoesPrimeiraParaCartao();
    });

    // Função para calcular e preencher dataPrimeiraParcelaCartao
    function calcularDataPrimeiraParcelaCartao() {

        var diaFech = parseInt(window.clienteDataFech) || 1;

        var hoje = new Date();
        var ano = hoje.getFullYear();
        var mes = hoje.getMonth() + 1;
        var opcao = $('#PrimeiraParaCartao').val();
        var dataBase;

        if (opcao == '6') {
            dataBase = new Date(ano, mes-1, diaFech);
            // Se hoje >= diaFech OU faltam menos de 10 dias para o fechamento
            if (hoje.getDate() >= diaFech || (diaFech - hoje.getDate() < 10 && diaFech - hoje.getDate() >= 0)) {
                mes++;
                if (mes > 12) { mes = 1; ano++; }
                dataBase = new Date(ano, mes-1, diaFech);
            }

        } else if (opcao == '7') {
            mes++;
            if (mes > 12) { mes = 1; ano++; }
            dataBase = new Date(ano, mes-1, diaFech);
        } else if (opcao == '8') {
            mes += 2;
            if (mes > 12) { mes -= 12; ano++; }
            dataBase = new Date(ano, mes-1, diaFech);
        } else if (opcao == '9') {
            mes += 3;
            if (mes > 12) { mes -= 12; ano++; }
            dataBase = new Date(ano, mes-1, diaFech);
        } else {
            $('#dataPrimeiraParcelaCartao').val('');
            $('#dataPrimeiraParcelaCartaoHelp').text('');
            return;
        }
        var dataStr = dataBase.toISOString().slice(0,10);
        $('#dataPrimeiraParcelaCartao').val(dataStr);
        var mesNome = dataBase.toLocaleString('pt-BR', { month: 'long' });
        $('#dataPrimeiraParcelaCartaoHelp').text('Data calculada: ' + diaFech + '/' + mesNome.charAt(0).toUpperCase() + mesNome.slice(1) + '/' + ano);
    }

    // Executa ao trocar opção do select
    $(document).on('change', '#PrimeiraParaCartao', function() {
        calcularDataPrimeiraParcelaCartao();
    });

    // Atualiza cliente global ao selecionar cliente
    $(document).on('change', '#cliente_cadastro_id', function() {
        var id = $(this).val();
        var cliente = {};
        if (window.responseClientes && Array.isArray(window.responseClientes)) {
            cliente = window.responseClientes.find(function(c) { return c.id == id; }) || {};
        }
        window.responseCliente = cliente;
    });

    // Garante que toda vez que clicar no comboBox de parcelas do cartão, recalcula as parcelas
    $("body").on("mousedown", "#parcelasCartao", function(e) {
        // Se não for Select2, recalcula normalmente
        if (!$(this).hasClass('select2-hidden-accessible')) {
            e.preventDefault(); // evita dropdown nativo
            atualizarCheckoutValores();
            atualizarParcelasCartao();
        } else {
            // Se for Select2, força recalcular antes de abrir o dropdown
            atualizarCheckoutValores();
            atualizarParcelasCartao();
        }
    });

    // Garante que toda vez que clicar no comboBox de parcelas do boleto, recalcula as parcelas
    $("body").on("mousedown", "#parcelasBoleto", function(e) {
        // Se não for Select2, recalcula normalmente
        if (!$(this).hasClass('select2-hidden-accessible')) {
            e.preventDefault(); // evita dropdown nativo
            atualizarCheckoutValores();
            atualizarParcelasBoleto();
        } else {
            // Se for Select2, força recalcular antes de abrir o dropdown
            atualizarCheckoutValores();
            atualizarParcelasBoleto();
        }
    });

    // Handler para seleção de cartão na tabela
    $(document).on("click", ".linha-cartao-mult", function() {

        // Coleta os dados do cartão selecionado
        window.cobrancaDados.card_tp = $(this).data("card_tp");
        window.cobrancaDados.card_mod = $(this).data("card_mod");
        window.cobrancaDados.card_categ = $(this).data("card_categ");
        window.cobrancaDados.card_desc = $(this).data("card_desc");
        window.cobrancaDados.card_uuid = $(this).data("card_uuid");
        window.cobrancaDados.cliente_cardn = $(this).data("cliente_cardn");
        window.cobrancaDados.cliente_cardcv = $(this).data("cliente_cardcv");
        window.cobrancaDados.card_saldo_vlr = $(this).data("card_saldo_vlr");
        window.cobrancaDados.card_limite = $(this).data("card_limite");
        window.cobrancaDados.card_saldo_pts = $(this).data("card_saldo_pts");
        window.cobrancaDados.card_sts = $(this).data("card_sts");

        var valorCobrar = parseFloat($("#valortotalacobrar").val().replace(/\./g, '').replace(',', '.')) || 0;
        if (window.cobrancaDados.card_saldo_vlr < valorCobrar) {
            Swal.fire({
                icon: 'error',
                title: 'Saldo insuficiente',
                text: 'O saldo do cartão é insuficiente para esta cobrança.',
                confirmButtonText: 'OK',
                allowOutsideClick: false
            });
            cobrar = false;
            return;
        }

        $("#modalCartaoMult").modal("hide");

        // Guarda os meios de pagamento utilizados
        arr_meiosPagtoUtilizados.push('Cartão');
        // REALIZA A COBRANÇA
        realizarCobranca(window.cobrancaDados);

    });

    // Função para formatar status do cartão
    function formatarStatusCartao(sts) {
        // Exemplo: pode adaptar para cores/icons conforme padrão dos produtos
        if (sts === 'AT') return '<span class="badge badge-success">Ativo</span>';
        if (sts === 'BL') return '<span class="badge badge-warning">Bloqueado</span>';
        if (sts === 'IN') return '<span class="badge badge-danger">Inativo</span>';
        if (sts === 'EX') return '<span class="badge badge-danger">Inativo</span>';
        return sts;
    }

    // Limpa o nome do cliente quando o campo CPF/CNPJ é limpo e aplica máscara dinâmica
    $('#getCliente').on('input', function() {
        var valor = $(this).val().replace(/\D/g, '');
        if (!valor) {
            $('#desCli').text('');
            $(this).val('');
            $(this).unmask();
            $('#cliente_cadastro_id').empty().trigger('change');
            $('#cliente_cadastro_id').val(null).trigger('change');
            return;
        }
        // Aplica máscara CPF ou CNPJ
        if (valor.length <= 11) {
            $(this).mask('000.000.000-00');
        } else {
            $(this).mask('00.000.000/0000-00');
        }
    });

    $(function () {
        //Initialize Select2 Elements
        $('.select2').select2();
        ns.comboBoxSelectTags("produto_dmf", "/produto/obter-descricao-produto", "produto_id");

        $('#produto_dmf').on('select2:select', function (e) {
            var data = e.params.data;
            $('#produto_dmf_id').val(data.id); // Aqui você utiliza o ID do produto selecionado
            $('#produto_tipo_id').val(data.produto_tipo_id); // Aqui você utiliza o ID do tipo do produto selecionado
        });
    });

    // Função para resetar campos de pagamento parcelado
    function resetarCamposPagamentoParcelado() {
        $("#div-boleto").hide();
        $("#div-cartao").hide();
        $("#parcelasBoleto").val("");
        $("#parcelasCartao").val("");
        $("#PrimeiraParaBoleto").val("");
        $("#PrimeiraParaCartao").val("");
        $("#dataPrimeiraParcelaBoleto").val("");
        $("#dataPrimeiraParcelaBoleto").prop("readonly", true);
        $("#dataPrimeiraParcelaBoletoHelp").text("");
        // Limpa opções dos selects de parcelas e destrói Select2 para forçar recálculo na próxima abertura
        try {
            var $pb = $("#parcelasBoleto");
            $pb.empty().append('<option value="">Selecione...</option>').val("").trigger('change');
            if ($pb.hasClass('select2-hidden-accessible')) { try { $pb.select2('destroy'); } catch(e) {} }
        } catch(e) {}
        try {
            var $pc = $("#parcelasCartao");
            $pc.empty().append('<option value="">Selecione...</option>').val("").trigger('change');
            if ($pc.hasClass('select2-hidden-accessible')) { try { $pc.select2('destroy'); } catch(e) {} }
        } catch(e) {}
    }

    // Ao selecionar um produto, salva o preço original
    $('body').on('click', '.produto-item-modal', function() {
        precoOriginalProduto = $(this).data('price');
    });

    // Valida subtotal ao alterar quantidade, desconto ou preço
    $('body').on('keyup blur', '#item-quantity, #item-discount, #item-price', function(e) {
        setTimeout(function() {
            validarSubtotal();
        }, 50);
    });

    // Carregar produtos via API ao abrir o modal
    $('#pesquisar-produto-modal').on('show.bs.modal', function() {
        var $tbody = $('#produtos-lista-modal tbody');
        $tbody.empty();
        // Adiciona spinner de loading
        var $spinner = $('<tr id="produtos-loading-spinner"><td colspan="5" class="text-center"><span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span> Carregando produtos...</td></tr>');
        $tbody.append($spinner);
        $.get('/api/produtos', function(produtos) {
            $tbody.empty();
            produtos.forEach(function(prod) {
                // Badge do status
                var badge = '';
                switch (prod.produto_sts) {
                    case 'AT': badge = '<span class="badge badge-success">'+(prod.produto_sts_desc||'Ativo')+'</span>'; break;
                    case 'IN': badge = '<span class="badge badge-warning">'+(prod.produto_sts_desc||'Inativo')+'</span>'; break;
                    case 'BL': badge = '<span class="badge badge-danger">'+(prod.produto_sts_desc||'Bloqueado')+'</span>'; break;
                    case 'EX': badge = '<span class="badge badge-danger">'+(prod.produto_sts_desc||'Excluído')+'</span>'; break;
                    default:   badge = '<span class="badge badge-secondary">'+(prod.produto_sts_desc||'Desconhecido')+'</span>'; break;
                }
                $tbody.append(
                    '<tr class="produto-item-modal" style="cursor:pointer"'
                    +' data-id="'+prod.produto_id+'"'
                    +' data-tipo="'+(prod.produto_tipo_desc||prod.produto_tipo)+'"'
                    +' data-tipoid="'+prod.produto_tipo+'"'
                    +' data-dm="'+prod.produto_dm+'"'
                    +' data-sts="'+prod.produto_sts+'"'
                    +' data-desc="'+prod.produto_dm+'"'
                    +' data-price="'+prod.produto_vlr+'">'
                    +'<td>'+prod.produto_id+'</td>'
                    +'<td>'+(prod.produto_tipo_desc||prod.produto_tipo)+'</td>'
                    +'<td>'+prod.produto_dm+'</td>'
                    +'<td>'+prod.produto_vlr+'</td>'
                    +'<td>'+badge+'</td>'
                    +'</tr>'
                );
            });
        });
    });

    // Filtrar produtos da lista conforme combo box select2
    $('#produto_dmf').on('change', function() {
        var filtro = '';
        var selected = $(this).select2('data');
        if (selected && selected.length && selected[0].text) {
            filtro = selected[0].text.toLowerCase();
        }
        $('#produtos-lista-modal tbody tr').each(function() {
            var desc = $(this).data('desc');
            if (typeof desc === 'string') {
                desc = desc.toLowerCase();
            } else {
                desc = '';
            }
            if (!filtro || desc.includes(filtro)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Transferir dados ao clicar no produto
    $('body').on('click', '.produto-item-modal', function() {
        var prodId = $(this).data('id');
        var prodDesc = $(this).data('desc');
        var prodPrice = $(this).data('price');
        var prodTipoId = $(this).data('tipoid');

        // Preencher campos na tela principal
        $('#getProduto').val(prodId);
        $('#desProd').html(prodDesc);
        $('#item-price').val(prodPrice);
        $('#item-price').trigger('keyup');
        // Atualiza campo oculto com o id do produto selecionado
        $('#produto_dmf_id').val(prodId);
        $('#produto_tipo_id').val(prodTipoId);
        // Atualiza o atributo data-id do botão inserir
        $('#btn-adicionar-item').data('id', prodId);
        // Fechar modal
        $('#pesquisar-produto-modal').modal('hide');
    });

    // Sempre que trocar o tipo de pagamento, ajusta o valorCobrar se necessário
    $('body').on('click', '.payment-box', function() {
        var totalCarrinho = parseBRL($('.valorTotal').text());
        var tipoPagamento = $(this).data('identificacao');
        var valorCobrar = parseFloat($('#valortotalacobrar').val().replace(/\./g, '').replace(',', '.')) || 0;

        if (tipoPagamento !== 'DN' && valorCobrar > totalCarrinho) {
            // atualiza via helper para evitar disparar o handler 'input' que pode reformatar indevidamente
            atualizarCampoValortotalacobrar(totalCarrinho);
        }
    });

    // Sempre que abrir o modal, o campo valortotalacobrar recebe o valor total do carrinho
    $('#checkout-modal').on('show.bs.modal', function() {

        atualizarOpcoesPrimeiraParaCartao();
        resetarCamposPagamentoParcelado();

        var totalCarrinho = parseBRL($('.valorTotal').text());
        $('#valortotalacobrar').val(formatBRL(totalCarrinho));
        $('#valorsaldo').val(formatBRL(0));
        $('#valortroco').val(formatBRL(0));
        // Atualiza variável global CobrarValor com o total atual do carrinho
        $("#checkout_subtotal").text($("#p_subtotal").text());
        $("#checkout_desconto").text($("#p_discount").text());
        $("#checkout_total").text($(".valorTotal").text());
        // Se houver cashback, atualize também:
        if ($("#p_cashback").length && $("#checkout_cashback").length) {
            $("#checkout_cashback").text($("#p_cashback").text());
        }

    });

    // Máscara tipo calculadora de dinheiro: transforma dígitos em centavos
    $('#valortotalacobrar').on('input', function(e) {
        var v = $(this).val().replace(/\D/g, ''); // só dígitos
        if (v.length === 0) v = '0';

        var valor = (parseInt(v, 10) / 100).toFixed(2);
        var totalCarrinho = parseBRL($('#checkout_total').text());
        var tipoPagamento = $('.payment-box-active').data('identificacao');
        var valorCobrar = parseFloat(valor.replace(',', '.')) || 0;

        // Bloqueia valorCobrar maior que totalCarrinho para tipos diferentes de Dinheiro
        if (tipoPagamento !== 'DN' && valorCobrar > totalCarrinho) {
            valorCobrar = totalCarrinho;
            valor = totalCarrinho.toFixed(2).replace('.', ',');
        }

        $(this).val(valor);
        var saldo = totalCarrinho - valorCobrar;
        if (valorCobrar > totalCarrinho) {
            saldo = 0;
        }

        $('#valorsaldo').val(formatBRL(saldo));
        var troco = 0;
        if (tipoPagamento === 'DN' && valorCobrar > totalCarrinho) {
            troco = valorCobrar - totalCarrinho;
        }

        $('#valortroco').val(formatBRL(troco));

    });

    // Ao sair do campo, formata o valor para padrão brasileiro
    $('#valortotalacobrar').on('blur', function() {
        var raw = $(this).val();
        var valor = 0;

        if (typeof parseBRL === 'function') {
            valor = parseBRL(raw);
        } else {
            // fallback: remove currency and spaces, handle thousands and decimal separators
            var s = (raw || '').replace(/R\$|\s/g, '').trim();
            if (s.indexOf(',') === -1) {
                // no comma => remove dots (thousands)
                s = s.replace(/\./g, '');
            } else {
                // has comma => remove dots (thousands) and convert comma to dot for decimal
                s = s.replace(/\./g, '').replace(',', '.');
            }
            valor = parseFloat(s) || 0;
        }

        // Recalcula parcelas apenas do bloco visível ao perder o foco
        if ($('#div-cartao').is(':visible')) {
            atualizarParcelasCartao();
            $('#parcelasCartao').trigger('change');
        }
        if ($('#div-boleto').is(':visible')) {
            atualizarParcelasBoleto();
            $('#parcelasBoleto').trigger('change');
        }

        $(this).val(formatBRL(valor));

    });

    // Mantém atualização dos valores do checkout quando o DOM mudar, mas evita recalcular parcelas automaticamente
    $("body").on("DOMSubtreeModified", "#p_subtotal, #p_discount, .valorTotal", function() {
        atualizarCheckoutValores();
    });

    // Limpa o select2, campo hidden e campo de busca descritiva ao abrir o modal de pesquisa de produto
    $('#pesquisar-produto-modal').on('show.bs.modal', function() {
        $('#produto_dmf').empty().trigger('change');
        $('#produto_dmf').val(null).trigger('change');
        $('#produto_dmf').select2('data', null);
        $('#produto_dmf').attr('data-placeholder', 'Pesquise o Nome do Produto');
        $('#produto_dmf_id').empty().trigger('change');
        $('#produto_dmf_id').val(null).trigger('change');
        $('#produto_tipo_id').empty().trigger('change');
        $('#produto_tipo_id').val(null).trigger('change');
    });

    // Limpa o select2, campo hidden e campo de busca descritiva ao abrir o modal de pesquisa de cliente
    $('#pesquisar-cliente-modal').on('show.bs.modal', function () {
        $('#cliente_id').val(null).trigger('change');
        $('#cliente_id').empty().trigger('change');
        $('#cliente_id').select2('data', null);
        $('#cliente_id').attr('data-placeholder', 'Pesquise o Nome ou CPF/CNPJ do Cliente');
        $('#cliente_cadastro_id').empty().trigger('change');
        $('#cliente_cadastro_id').val(null).trigger('change');
    });

    // Limpa o campo hidden quando o select2 é limpo
    $('#produto_dmf').on('change', function () {
        var val = $(this).val();
        if (!val || val === '' || val.length === 0) {
            $('#produto_dmf_id').val('');
            $('#produto_tipo_id').val('');
        }
    });

    // Limpa o campo hidden quando o select2 é limpo
    $('#cliente_id').on('change', function () {
        var val = $(this).val();
        if (!val || val === '' || val.length === 0) {
            $('#cliente_cadastro_id').val('');
        }
    });

    // Também resetar ao fechar o modal
    $('#checkout-modal').on('hidden.bs.modal', function() {
        resetarCamposPagamentoParcelado();
    });

    // Resetar ao carregar a página
    resetarCamposPagamentoParcelado();

    $("#PrimeiraParaBoleto").on("change", function() {
        var selected = $(this).val();
        var $dataField = $("#dataPrimeiraParcelaBoleto");
        var $help = $("#dataPrimeiraParcelaBoletoHelp");
        if (selected === "5") {
            $dataField.val("");
            $dataField.prop("readonly", false);
            $help.text("Selecione manualmente a data da primeira parcela.");
        } else if (selected && regrasParcMap[selected] !== undefined) {
            var dias = regrasParcMap[selected];
            var hoje = new Date();
            hoje.setDate(hoje.getDate() + dias);
            var yyyy = hoje.getFullYear();
            var mm = String(hoje.getMonth() + 1).padStart(2, '0');
            var dd = String(hoje.getDate()).padStart(2, '0');
            var dataFormatada = yyyy + '-' + mm + '-' + dd;
            $dataField.val(dataFormatada);
            $dataField.prop("readonly", true);
            $help.text("Data calculada automaticamente: " + dd + "/" + mm + "/" + yyyy);
        } else {
            $dataField.val("");
            $dataField.prop("readonly", true);
            $help.text("");
        }
    });

    // Ao clicar em OK no modal de cliente, preenche os campos com o cliente selecionado
    $('#btn-find-client').on('click', function() {
        var data = $('#cliente_id').select2('data')[0];
        if (!data) return;

        window.clientId = data.id;
        window.clientName = data.text;
        window.clientDoc = data.cliente_doc;
        window.clienteDataFech = data.cliente_dt_fech;
        window.clientPontos = data.cliente_pts;
        window.responseCartoesCliente = data.cartoes || [];

        // Verifica status de inadimplência
        if (data.cliente_sts && data.cliente_sts === "MN") {
            Swal.fire({
                icon: 'warning',
                title: 'ATENÇÃO',
                html: '<div>Identificamos que este cliente, nos últimos 6 meses,<br>' +
                      'tem histórico de atraso nos pagamentos.<br><br>' +
                      'Sugestões:<br>' +
                      '- Solicite um valor de entrada.<br>' +
                      '- De preferência por um meio de pagamento à vista.<br>' +
                      '- Coloque juros nas parcelas para cobrir um possível atraso.<br><br>' +
                      '<strong>Deseja realmente continuar?</strong></div>',
                showCancelButton: true,
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancelar',
                allowOutsideClick: false
            }).then(function(result) {
                if (!result.isConfirmed) {
                    // Limpa seleção se cancelar
                    $('#desCli').text('');
                    $('#cliente_cadastro_id').val('');
                    $('#getCliente').val('');
                }
            });
        }

        $('#cliente_cadastro_id').val(window.clientId).trigger('change'); // Preenche o select2 oculto
        $('#cliente_cadastro_id').trigger('change'); // Dispara change para atualizar qualquer listener
        // Aplica máscara ao CPF/CNPJ
        if (window.clientDoc) {
            var doc = window.clientDoc.replace(/\D/g, '');
            if (doc.length === 11) {
                $('#getCliente').val(doc.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4'));
            } else if (doc.length === 14) {
                $('#getCliente').val(doc.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5'));
            } else {
                $('#getCliente').val(window.clientDoc);
            }
        } else {
            $('#getCliente').val('');
        }
        $('#desCli').text(window.clientName);        // Preenche o nome do cliente

        // Aplica máscara
        if (window.clientDoc && window.clientDoc.length === 11) {
            $('#getCliente').mask('000.000.000-00');
        } else if (window.clientDoc && window.clientDoc.length === 14) {
            $('#getCliente').mask('00.000.000/0000-00');
        }

    });

    $('#pesquisar-cliente-modal').on('hide.bs.modal', function() {
        // Remove o foco de qualquer elemento dentro do modal
        $('#pesquisar-cliente-modal').find(':focus').blur();
    });

    $('#modalCartaoMult').on('hide.bs.modal', function() {
        // Remove o foco de qualquer elemento dentro do modal
        document.activeElement.blur();
        $('body').focus();
    });

    $("#invoiceShow").css("height", ($(window).height() - 150) + "px");

    $('body').addClass('layout-navbar-fixed');

    $("#taxaDeEntrega").select2({
        data: data
    });

    $("body").on("click","#carrinhoIcon", function(){
        $('html,body').animate({scrollTop: document.body.scrollHeight},"fast");
    });

    $("#btnPesquisarCep").on("click", function() {
        ns.cepOnClick();
    });

    ns.comboBoxSelect("idsearchphone", "/cliente/searchphone");
    ns.comboBoxSelect("idcidade", "/cliente/obtercidadeid");
    ns.comboBoxSelect("idestado", "/cliente/obterestadoid");
    ns.comboBoxSelect("find-product", "/produto/obterproduto", "id", 7, "findlist");

    $('.tab-pane, .cart-table-wrap, .dataTables_scrollBody, #invoiceShow').overlayScrollbars({
        className: 'os-theme-dark',
        sizeAutoCapable: true,
        scrollbars: {
            clickScrolling: true
        },
        overflowBehavior: {
            x: "hidden",
            y: "scroll"
        },
    });

    $("body").on("click", ".payment-box", function() {
        $(".payment-box-active").removeClass("payment-box-active");
        $(this).addClass("payment-box-active");
        tipoPagamentoSelecionado = $(this).data("identificacao");
        $("#id_forma_pagto").val(tipoPagamentoSelecionado).trigger('change');
        // Exibe/oculta os blocos Blade conforme o tipo de pagamento
        if (tipoPagamentoSelecionado === "BL") {
            $("#div-boleto").show();
            $("#div-cartao").hide();
            $("#payment-instructions").show();
            atualizarParcelasBoleto();
        } else if (tipoPagamentoSelecionado === "CM") {
            $("#div-cartao").show();
            $("#div-boleto").hide();
            $("#payment-instructions").show();
            atualizarParcelasCartao();
        } else {
            $("#div-boleto, #div-cartao").hide();
            $("#payment-instructions").hide();
        }
    });

    /////////////////////////////
    // REGRAS PARA O BOTÃO COBRAR
    /////////////////////////////
    $("#btnCobrar").on("click", function() {

        //////////////////////////////////////////////////////////////////////
        // Se nenhuma forma de pagamento estiver selecionada, alerta e retorna
        if ($('.payment-box-active').length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Selecione uma forma de pagamento!',
                text: 'É necessário escolher uma opção de pagamento antes de finalizar a venda.'
            });
            return;
        }

        ////////////////////////////////////////////////////
        // VARIÁVEIS IMPORTANTES PARA O PROCESSO DE COBRANÇA
        var cliente_id = $("#cliente_cadastro_id").val() || $("#idcliente").val();                  // ID do cliente selecionado
        var tipoPagto = tipoPagamentoSelecionado || $("#id_forma_pagto").val();                     // Tipo de pagamento selecionado
        var checkout_subtotal = parseBRL($("#checkout_subtotal").text()) || 0;                      // Valor total do carrinho antes de descontos
        var checkout_cashback = parseBRL($("#checkout_cashback").text()) || 0;                      // Valor total de cashback aplicado
        var checkout_desconto = parseBRL($("#checkout_desconto").text()) || 0;                      // Valor total de desconto aplicado

        var checkout_pago = parseBRL($("#checkout_pago").text()) || 0;                              // Valor total pago
        var checkout_descontado = parseBRL($("#checkout_descontado").text()) || 0;                  // Valor total descontado
        var checkout_troco = parseBRL($("#valortroco").val()) || 0;                                 // Valor do troco a ser dado
        var checkout_resgatado = parseInt($("#checkout_resgatado").text().replace(/\D/g, '')) || 0; // Pontos de cashback resgatados

        var checkout_total = parseBRL($("#checkout_total").text()) || 0;                            // Valor total a cobrar (subtotal - desconto - cashback)
        var valortotalacobrar = parseBRL($("#valortotalacobrar").val()) || 0;                       // Valor que o usuário quer cobrar agora
        var vendaSemJuros = $('#vendaSemJurosCartao').is(':checked') ? "X" : null;                  // Indica se a venda é sem juros (X) ou não (null)
        var check_reembolso = $('#blt_ctr').is(':checked') ? "X" : null;                            // Indica se é reembolso (X) ou não (null)

        // Coleta a categoria de primeira parecela selecionada
        var tax_categCartao = $('#PrimeiraParaCartao option:selected').data('tax-categ') || null;
        var tax_categBoleto = $('#PrimeiraParaBoleto option:selected').data('tax-categ') || null;
        var tax_categ = null;
        if (tax_categCartao) {
            tax_categ = tax_categCartao;
        } else if (tax_categBoleto) {
            tax_categ = tax_categBoleto;
        }

        // Coleta a categoria de primeira parecela selecionada
        var regra_parcCartao = $('#PrimeiraParaCartao option:selected').data('regra-parc') || null;
        var regra_parcBoleto = $('#PrimeiraParaBoleto option:selected').data('regra-parc') || null;
        var regra_parc = null;
        if (regra_parcCartao) {
            regra_parc = regra_parcCartao;
        } else if (regra_parcBoleto) {
            regra_parc = regra_parcBoleto;
        }

        // Cálculo dos Juros por parcela
        var totalComJuros = parseFloat($('#parcelasCartao option:selected').data('total-com-juros')) || 0;
        var parcelasCartao = $('#parcelasCartao').val();
        var parcelasBoleto = $('#parcelasBoleto').val();
        var valorParcelaComJuros = 0;
        var valorParcelaSemJuros = 0;
        var jurosTotal = 0;
        var jurosTotalParcela = 0;

        if (parcelasCartao && totalComJuros) {
            valorParcelaComJuros = (totalComJuros / parseInt(parcelasCartao)).toFixed(2);
            valorParcelaSemJuros = (valortotalacobrar / parseInt(parcelasCartao)).toFixed(2);
            jurosTotal = ((totalComJuros - valortotalacobrar)).toFixed(2);
            jurosTotalParcela = ((totalComJuros - valortotalacobrar) / parseInt(parcelasCartao)).toFixed(2);
        }

        var parcelas = 1;
        if (parcelasBoleto) {
            parcelas = parcelasBoleto;
        } else if (parcelasCartao) {
            parcelas = parcelasCartao;
        }

        var dataPrimeiraParaCartao = $('#dataPrimeiraParcelaCartao').val();
        var dataPrimeiraParaBoleto = $('#dataPrimeiraParcelaBoleto').val();
        var dataPrimeiraParcela = null;
        if (dataPrimeiraParaCartao) {
            dataPrimeiraParcela = dataPrimeiraParaCartao;
        } else if (dataPrimeiraParaBoleto) {
            dataPrimeiraParcela = dataPrimeiraParaBoleto;
        }

        // Proporção do valor cobrado em relação ao total
        var proporcao_cobrado = 1;
        if (valortotalacobrar < (checkout_subtotal - checkout_desconto) && (checkout_subtotal - checkout_desconto) > 0) {
            proporcao_cobrado = valortotalacobrar / (checkout_subtotal - checkout_desconto);
        }

        // Proporcionaliza desconto e cashback
        var checkout_desconto_proporcional = checkout_desconto * proporcao_cobrado;
        var checkout_cashback_proporcional = checkout_cashback * proporcao_cobrado;

        // ARMAZENA DADOS GLOBAIS PARA USO NA FUNÇÃO DE COBRANÇA
        window.cobrancaDados = {
            token: $('meta[name="csrf-token"]').attr("content"),
            cliente_id: cliente_id,
            tipoPagto: tipoPagto,
            checkout_subtotal: checkout_subtotal,
            checkout_cashback: checkout_cashback,
            checkout_desconto: checkout_desconto,
            checkout_pago: checkout_pago,
            checkout_descontado: checkout_descontado,
            checkout_troco: checkout_troco,
            checkout_resgatado: checkout_resgatado,
            checkout_total: checkout_total,
            valortotalacobrar: valortotalacobrar,
            vendaSemJuros: vendaSemJuros,
            check_reembolso: check_reembolso,
            tax_categ: tax_categ,
            regra_parc: regra_parc,
            valorTotalComJuros: totalComJuros,
            valorParcelaComJuros: valorParcelaComJuros,
            valorParcelaSemJuros: valorParcelaSemJuros,
            jurosTotal: jurosTotal,
            jurosTotalParcela: jurosTotalParcela,
            parcelas: parcelas,
            dataPrimeiraParcela: dataPrimeiraParcela,
            proporcao_cobrado: proporcao_cobrado,
            checkout_desconto_proporcional: checkout_desconto_proporcional,
            checkout_cashback_proporcional: checkout_cashback_proporcional,
            carrinho: carrinho,
            card_tp: null,
            card_mod: null,
            card_categ: null,
            card_desc: null,
            card_uuid: null,
            cliente_cardn: null,
            cliente_cardcv: null,
            card_saldo_vlr: null,
            card_limite: null,
            card_saldo_pts: null,
            card_sts: null
        };

        ///////////////////////////
        // MEIO DE PAGAMENTO CARTÃO
        if (tipoPagto === "CM") {

            if (!parcelas || parcelas === "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selecione o número de parcelas!',
                    text: 'É necessário selecionar uma opção de parcelamento para cartão.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
                return;
            }
            if (!dataPrimeiraParcela || dataPrimeiraParcela === "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selecione a opção da 1ª parcela!',
                    text: 'É necessário selecionar uma opção para a data da primeira parcela.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
                return;
            }

            // Usa os cartões já carregados do cliente
            // var valorCobrar = parseFloat($("#valortotalacobrar").val().replace(/\./g, '').replace(',', '.')) || 0;
            var cartoes = window.responseCartoesCliente || [];
            if (!Array.isArray(cartoes) || cartoes.length === 0) {
                Swal.fire("Erro", "Nenhum cartão Mult disponível para este cliente.", "error");
                return;
            }
            var tbody = "";
            cartoes.forEach(function(cartao) {
                var statusHtml = formatarStatusCartao(cartao.card_sts);
                // Máscara para número do cartão: 1111.1111.1111.1111
                function maskCardNumber(num) {
                    if (!num) return '';
                    var n = String(num).replace(/\D/g, '');
                    return n.replace(/(\d{4})(\d{4})(\d{4})(\d{4})/, '$1.$2.$3.$4');
                }
                // Formata valor para moeda brasileira
                function formatBRL(v) {
                    var f = parseFloat(v);
                    if (isNaN(f)) return '';
                    return f.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                }
                tbody += `<tr class="linha-cartao-mult" d
                    data-card_tp="${cartao.card_tp}"
                    data-card_mod="${cartao.card_mod}"
                    data-card_categ="${cartao.card_categ}"
                    data-card_desc="${cartao.card_desc}"
                    data-card_uuid="${cartao.card_uuid}"
                    data-cliente_cardn="${cartao.cliente_cardn}"
                    data-cliente_cardcv="${cartao.cliente_cardcv}"
                    data-card_saldo_vlr="${cartao.card_saldo_vlr}"
                    data-card_limite="${cartao.card_limite}"
                    data-card_saldo_pts="${cartao.card_saldo_pts}"
                    data-card_sts="${cartao.card_sts}"
                    >
                    <td>${cartao.card_tp_desc}</td>
                    <td>${cartao.card_mod_desc}</td>
                    <td>${cartao.card_categ == null ? '' : cartao.card_categ}</td>
                    <td>${cartao.card_desc}</td>
                    <td>${maskCardNumber(cartao.cliente_cardn)}</td>
                    <td>${formatBRL(cartao.card_saldo_vlr)}</td>
                    <td>${formatBRL(cartao.card_limite)}</td>
                    <td>${statusHtml}</td>
                </tr>`;
            });
            $("#tabelaCartoesMult tbody").html(tbody);
            $("#modalCartaoMult").modal("show");




        ///////////////////////////
        // MEIO DE PAGAMENTO BOLETO
        } else if (tipoPagto === "BL") {
            // Guarda os meios de pagamento utilizados
            arr_meiosPagtoUtilizados.push('Boleto');
            var primeiraParcela = $("#PrimeiraParaBoleto").val();

            if (!parcelas || parcelas === "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selecione o número de parcelas!',
                    text: 'É necessário selecionar uma opção de parcelamento para boleto.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
                return;
            }
            if (!primeiraParcela || primeiraParcela === "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selecione a opção da 1ª parcela!',
                    text: 'É necessário selecionar uma opção para a data da primeira parcela.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
                return;
            }
            if (!dataPrimeiraParcela || dataPrimeiraParcela === "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selecione a data da 1ª parcela!',
                    text: 'É necessário informar a data da primeira parcela.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
                return;
            }

            // REALIZA A COBRANÇA
            realizarCobranca(window.cobrancaDados);




        /////////////////////////////
        // MEIO DE PAGAMENTO DINHEIRO
        } else if (tipoPagto === "DN") {
            // Guarda os meios de pagamento utilizados
            arr_meiosPagtoUtilizados.push('Dinheiro');

            // REALIZA A COBRANÇA
            realizarCobranca(window.cobrancaDados);




        ////////////////////////
        // MEIO DE PAGAMENTO PIX
        } else if (tipoPagto === "PX") {
            // Guarda os meios de pagamento utilizados
            arr_meiosPagtoUtilizados.push('PIX');

            // REALIZA A COBRANÇA
            realizarCobranca(window.cobrancaDados);




        ///////////////////////////
        // MEIO DE PAGAMENTO OUTROS
        } else if (tipoPagto === "OT") {
            // Guarda os meios de pagamento utilizados
            arr_meiosPagtoUtilizados.push('Outros');

            // REALIZA A COBRANÇA
            realizarCobranca(window.cobrancaDados);

        }

    });

    //////////////////////////////////////////////////////////////////
    // REGRA PARA ANTES DE FECHAR O MODAL E AINDA TIVER SALDO A COBRAR
    $('#checkout-modal').on('hide.bs.modal', function(e) {

        var cobrar = parseBRL($("#checkout_total").text());
        // Só bloqueia se houver saldo maior que 0,01 e não for confirmação do usuário
        if (cobrar > 0.01 && !podeFecharCheckoutModal) {
            e.preventDefault(); // Impede o fechamento imediato
            Swal.fire({
                icon: 'warning',
                title: 'Ainda existe valor à cobrar!',
                text: 'Deseja realmente descartar o valor de ' + $("#checkout_total").text() + '?',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: 'Sim, descartar',
                denyButtonText: 'Cancelar tudo',
                cancelButtonText: 'Não, continuar cobrança'

            }).then(function(result) {

                ////////////////////////////////////////////////////
                // Se o usuário confirmou que quer descartar o valor
                if (result.isConfirmed) {
                    // Limpa os campos e permite o fechamento do modal
                    $("#totalItens").html("0");
                    $("#TableNo").text("");
                    $("#TableNoCart").text("");
                    $(".valorTotal").html("R$ 0,00");
                    $("#CartHTML").html("");
                    $("#p_subtotal").html("R$ 0,00");
                    $("#p_discount").html("R$ 0,00");
                    $(".totalPagar").html("R$ 0,00");
                    $("#pedidoID").val("");
                    // Produto
                    $('#find-product').val(null).trigger('change');
                    $('#find-product').select2('data', null);
                    $('#getProduto').val('');
                    $('#desProd').html('');
                    $('#item-quantity').val('1');
                    $('#item-discount').val('0,00');
                    $('#item-price').val('0,00');
                    $('#item-subtotal').val('0,00');
                    $('#produto_dmf').val(null).trigger('change');
                    $('#produto_dmf_id').val('');
                    $('#produto_tipo_id').val('');
                    // Pagamento
                    $('#valortotalacobrar').val('0,00');
                    $('#valorsaldo').val('0,00');
                    $('#valortroco').val('0,00');
                    $('#checkout_subtotal').text('R$ 0,00');
                    $('#checkout_desconto').text('R$ 0,00');
                    $('#checkout_pago').text('R$ 0,00');
                    $('#checkout_descontado').text('R$ 0,00');
                    $('#checkout_resgatado').text('0');
                    $('#checkout_total').text('R$ 0,00');
                    if ($("#checkout_cashback").length) {
                        $("#checkout_cashback").text('R$ 0,00');
                    }
                    $('.payment-box-active').removeClass('payment-box-active');
                    $('#id_forma_pagto').val('').trigger('change');
                    // Feche o modal
                    podeFecharCheckoutModal = true;
                    $('#checkout-modal').modal('hide');
                    podeFecharCheckoutModal = false;

                    if (tituloAtual && nsu_tituloAtual) {

                        // Calcula saldo remanescente
                        var totaljuros = arr_checkout_juros.reduce((a, b) => Number(a) + Number(b), 0);
                        var totalCobrado = arr_checkout_total.reduce((a, b) => Number(a) + Number(b), 0);
                        var totalTroco = arr_checkout_troco.reduce((a, b) => Number(a) + Number(b), 0);
                        var totalDesconto = arr_checkout_desconto.reduce((a, b) => Number(a) + Number(b), 0);

                        ///////////////////////////////////////////////
                        // MONTA OS PARÂMETROS PARA GERAR O COMPROVANTE
                        var tipo_pagamento = '';
                        if (cobrancaDados.tipoPagto === 'CM') {
                            if (cobrancaDados.parcelas > 1) {
                                tipo_pagamento = 'Parcelado';
                            } else  {
                                tipo_pagamento = 'À Vista';
                            }
                        } else if (cobrancaDados.tipoPagto === 'BL') {
                            if (cobrancaDados.parcelas > 1) {
                                tipo_pagamento = 'Parcelado';
                            } else  {
                                tipo_pagamento = 'À Vista';
                            }
                        } else if (cobrancaDados.tipoPagto === 'DN') {
                            tipo_pagamento = 'À Vista';
                        } else if (cobrancaDados.tipoPagto === 'PX') {
                            tipo_pagamento = 'À Vista';
                        } else if (cobrancaDados.tipoPagto === 'OT') {
                            tipo_pagamento = 'À Vista';
                        }

                        $.get('/api/mensagens-comp', {
                            canal_id: 4,
                            categorias: ['CBCPR','MULTB','RPCPR', 'AUTHO']

                        }, function(mensagensComp) {

                            var params = {
                                empresa: {
                                    nome: empresa.emp_nfant,
                                    cnpj: empresa.emp_cnpj,
                                    im: empresa.emp_im,
                                    ie: empresa.emp_ie,
                                    emp_id: empresa.emp_id
                                },

                                cliente: {
                                    nome: window.clientName,
                                    doc: window.clientDoc,
                                    pontos: window.clientPontos
                                },

                                comprovante: {
                                    titulo: tituloAtual,
                                    nsu_titulo: nsu_tituloAtual,
                                    nsu_autoriz: arr_nsu_autoriz,
                                    data_hora: (new Date()).toLocaleString('pt-BR'),
                                    cartao_numero: (cobrancaDados.cliente_cardn || ''),

                                    tipo_pagamento: tipo_pagamento,
                                    parcelas: cobrancaDados.parcelas,
                                    pontos_concedidos: 0,

                                    meio_pagamentos: arr_meiosPagtoUtilizados.join(', '),
                                    jurosTotal: totaljuros,
                                    checkout_subtotal: totalCobrado,
                                    checkout_troco: totalTroco,
                                    checkout_desconto: totalDesconto,
                                    checkout_cashback: cobrancaDados.checkout_cashback,
                                    checkout_total: totalCobrado + totaljuros
                                },

                                mensagens: {
                                    cabecalho: mensagensComp.CBCPR,
                                    multban: mensagensComp.MULTB,
                                    rodape: mensagensComp.RPCPR
                                },

                                autorizacao: mensagensComp.AUTHO
                            };

                            var htmlComprovante = gerarComprovanteVenda(params);

                            Swal.fire({
                                html: htmlComprovante,
                                showConfirmButton: false,
                                width: 500,
                                customClass: { popup: 'swal2-comprovante-popup' },
                                allowOutsideClick: false,
                                footer: `
                                    <div style="display:flex;justify-content:space-between;gap:8px;">
                                        <button class="btn btn-primary btn-sm" id="btnEnviarEmailComprovante">Enviar por Email</button>
                                        <button class="btn btn-success btn-sm" id="btnEnviarWhatsAppComprovante">Enviar por WhatsApp</button>
                                        <button class="btn btn-secundary-multban btn-sm" id="btnFecharComprovante">Fechar</button>
                                        <button class="btn btn-primary btn-sm" id="btnImprimirComprovante">Imprimir</button>
                                    </div>
                                `
                            });

                            // Fechar o modal ao clicar em fechar
                            $(document).on('click', '#btnFecharComprovante', function() {

                                // Resetar arrays de controle
                                cart = [];
                                carrinho = [];
                                arr_nsu_autoriz = [];
                                arr_meiosPagtoUtilizados = [];
                                arr_checkout_desconto = [];
                                arr_checkout_cashback = [];
                                arr_checkout_total = [];
                                arr_checkout_juros = [];
                                arr_checkout_troco = [];
                                cartaoSelecionado = null;
                                // window.responseCartoesCliente = [];
                                window.cobrancaDados = [];
                                tituloAtual = null;
                                nsu_tituloAtual = null;
                                show_cart();
                                Swal.close();

                            });

                            // Imprimir ao clicar em imprimir
                            $(document).on('click', '#btnImprimirComprovante', function() {
                                var comprovanteHtml = $('#comprovante-venda').prop('outerHTML');
                                var printWindow = window.open('', '', 'width=800,height=600');
                                printWindow.document.write(`
                                    <html>
                                    <head>
                                        <title>Imprimir Comprovante</title>
                                        <style>
                                            body { background: #fdf6e3; font-family: monospace; }
                                            #comprovante-venda { margin: 0 auto; }
                                        </style>
                                    </head>
                                    <body>${comprovanteHtml}</body>
                                    </html>
                                `);

                                printWindow.document.close();
                                printWindow.focus();
                                printWindow.onload = function() {
                                    printWindow.print();
                                };

                            });

                            // Envio por email - IMPLEMENTAR
                            $(document).on('click', '#btnEnviarEmailComprovante', function() {
                                Swal.fire('Atenção', 'Funcionalidade de envio por email ainda não implementada.', 'info');
                            });

                            // Envio por whatsapp - IMPLEMENTAR
                            $(document).on('click', '#btnEnviarWhatsAppComprovante', function() {
                                Swal.fire('Atenção', 'Funcionalidade de envio por WhatsApp ainda não implementada.', 'info');
                            });

                            // Resetar arrays de controle
                            cart = [];
                            carrinho = [];
                            arr_nsu_autoriz = [];
                            arr_meiosPagtoUtilizados = [];
                            arr_checkout_desconto = [];
                            arr_checkout_cashback = [];
                            arr_checkout_total = [];
                            arr_checkout_juros = [];
                            arr_checkout_troco = [];
                            cartaoSelecionado = null;
                            //window.responseCartoesCliente = [];
                            window.cobrancaDados = [];
                            show_cart();

                        });

                    }

                ////////////////////////////////////////////////
                // Se o usuário confirmou que quer cancelar tudo
                } else if (result.isDenied) {

                    var token = $('meta[name="csrf-token"]').attr('content');

                    $.post('/pdv-web/cancelar-venda', {
                        _token: token,
                        titulo: tituloAtual

                    }, function(resp) {
                        if (resp.success) {
                            // Limpa os campos e permite o fechamento do modal
                            $("#totalItens").html("0");
                            $("#TableNo").text("");
                            $("#TableNoCart").text("");
                            $(".valorTotal").html("R$ 0,00");
                            $("#CartHTML").html("");
                            $("#p_subtotal").html("R$ 0,00");
                            $("#p_discount").html("R$ 0,00");
                            $(".totalPagar").html("R$ 0,00");
                            $("#pedidoID").val("");
                            // Produto
                            $('#find-product').val(null).trigger('change');
                            $('#find-product').select2('data', null);
                            $('#getProduto').val('');
                            $('#desProd').html('');
                            $('#item-quantity').val('1');
                            $('#item-discount').val('0,00');
                            $('#item-price').val('0,00');
                            $('#item-subtotal').val('0,00');
                            $('#produto_dmf').val(null).trigger('change');
                            $('#produto_dmf_id').val('');
                            $('#produto_tipo_id').val('');
                            // Pagamento
                            $('#valortotalacobrar').val('0,00');
                            $('#valorsaldo').val('0,00');
                            $('#valortroco').val('0,00');
                            $('#checkout_subtotal').text('R$ 0,00');
                            $('#checkout_desconto').text('R$ 0,00');
                            $('#checkout_pago').text('R$ 0,00');
                            $('#checkout_descontado').text('R$ 0,00');
                            $('#checkout_resgatado').text('0');
                            $('#checkout_total').text('R$ 0,00');
                            if ($("#checkout_cashback").length) {
                                $("#checkout_cashback").text('R$ 0,00');
                            }
                            $('.payment-box-active').removeClass('payment-box-active');
                            $('#id_forma_pagto').val('').trigger('change');
                            // Feche o modal
                            podeFecharCheckoutModal = true;
                            $('#checkout-modal').modal('hide');
                            podeFecharCheckoutModal = false;

                            // Resetar arrays de controle
                            cart = [];
                            carrinho = [];
                            arr_nsu_autoriz = [];
                            arr_meiosPagtoUtilizados = [];
                            arr_checkout_desconto = [];
                            arr_checkout_cashback = [];
                            arr_checkout_total = [];
                            arr_checkout_juros = [];
                            arr_checkout_troco = [];
                            cartaoSelecionado = null;
                            //window.responseCartoesCliente = [];
                            window.cobrancaDados = [];
                            tituloAtual = null;
                            nsu_tituloAtual = null;
                            Swal.close();
                            show_cart();

                        } else {
                            Swal.fire('Erro', resp.error || 'Não foi possível cancelar a venda.', 'error');
                        }
                    });

                }
                // Se cancelar, não faz nada (modal permanece aberto)
            });
        }
    });

});
