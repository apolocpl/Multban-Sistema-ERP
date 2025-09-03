////////////
// VARIÁVEIS

// Armazena o valor original do produto selecionado
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
var empresaParam = window.empresaParam || null;
var lastToastr = {};
var venda_subtotal = $("#p_subtotal").text().replace("R$", "").trim();
var venda_desconto = $("#p_discount").text().replace("R$", "").trim();
var venda_total = $("#valorTotal").text().replace("R$", "").trim();

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
    var totalCarrinho = parseBRL($('.valorTotal').text());
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
    var total = $(".valorTotal").text();
    $("#checkout_subtotal").text(subtotal);
    $("#checkout_desconto").text(desconto);
    $("#checkout_total").text(total);
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
        // Abre o dropdown logo em seguida para que o primeiro clique já mostre o Select2 corretamente
        setTimeout(function(){
            try { $('#parcelasBoleto').select2('open'); } catch(err) { /* noop */ }
        }, 60);
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

        // Se a empresa permite juros e a parcela atual está na faixa que cobra juros (>= parc_jr_deprc_val)
        if (parc_cjuros_flag && parc_jr_deprc_val > 0 && i >= parc_jr_deprc_val && !$('#vendaSemJurosCartao').is(':checked')) {
            // calcula percentual total de juros: i * tax_jrsparc_val (em %)
            var jurosPercentTotal = (i * tax_jrsparc_val) / 100; // ex: 4 parcelas * 2% = 8% => 0.08
            // valor absoluto de juros sobre o total da venda
            var jurosAmount = totalVenda * jurosPercentTotal;
            // soma os juros ao total e divide pela quantidade de parcelas
            var adjustedTotal = totalVenda + jurosAmount;
            parcelaValor = adjustedTotal / i;
            descricaoJuros = ' - com juros';
        } else {
            parcelaValor = i > 0 ? (totalVenda / i) : totalVenda;
        }

        var parcelaValorFormatado = (typeof formatBRL === 'function') ? formatBRL(parcelaValor) : parcelaValor.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        var numParcela = i.toString().padStart(2, '0');
        select.append(`<option value="${i}">${numParcela} X R$ ${parcelaValorFormatado}${descricaoJuros}</option>`);
    }
    // Inicializa Select2 de forma segura (apenas se estiver disponível) para limitar altura do dropdown
    try {
        initParcelSelect2IfAvailable('#parcelasCartao');
        // Abre o dropdown logo em seguida para que o primeiro clique já mostre o Select2 corretamente
        setTimeout(function(){
            try { $('#parcelasCartao').select2('open'); } catch(err) { /* noop */ }
        }, 60);
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
    console.log("numero", numero);
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
        // Não atualiza o contador de itens distintos
    }
    console.log('adicionarAoCarrinho', item)
}

function deleteItemFromCart(item) {
    var index = _.findIndex(cart, item);
    cart.splice(index, 1);
    show_cart();
}

function gravarPedido(status, e){
    if(status == vendaStatus.CONCLUIDA){
        var valorpago = $("#valortotalpago").val().replace('.','').replace(',', '.');
        var valorAPagar = $("#valorAPagar").val().replace('.','').replace(',', '.');
        if(valorpago <= 0){
            $("#valortotalpago").focus();
            swal.fire('', 'Digite o valor pago', 'error');
            return;
        }
        if((valorpago - valorAPagar) < 0){
            $("#valortotalpago").focus();
            swal.fire('', 'O valor pago é menor que o valor Total', 'error');
            return;
        }
    }

    if (cart.length < 1) {
        $("#checkout-modal").modal("hide");
        swal.fire("", "Pedido sem itens", "error");
        return false;
    }

    var vendasituacao = status;

    var form_data = {
        id: $("#pedidoID").val(),
        idempresa: $("#idempresa").val(),
        orcamento: $("#orcamento").val(),
        idclientevendedor: $("#idclientevendedor").val(),
        faturar: $("#faturar").val(),
        pdv: 1,
        observacao: $("#observacao").val(),
        idcliente: $("#idcliente").isNullOrEmpty() ? 1 : $("#idcliente").val(),
        id_tipo_pagto: $("#id_forma_pagto").val(),
        idvendatipo: $("#tipoDeVenda").val(),
        valorsubtotal: $("#p_subtotal").html().replace("R$", "").replace('.','').replace(',', '.'),
        valortotalpago: $("#valortotalpago").val().replace('.','').replace(',', '.'),
        valortotal: $(".valorTotal").html().replace("R$", "").replace('.','').replace(',', '.'),
        valortroco: $("#valortroco").val().replace('.','').replace(',', '.'),
        descontovalor : $("#valorDesconto").val().replace('.','').replace(',', '.'),
        descontoporcento : $("#valorDescCento").val().replace('.','').replace(',', '.'),
        vendaitens: _.map(cart, function(cart) {
            return {
                idproduto: cart.product_id,
                idcart: cart.id,
                quantidade: cart.quantity,
                discount: cart.discountValue,
                valorunitario: cart.price,
                name: cart.name,
                valortotal: (parseInt(cart.quantity) * cart.price),
            }
        })
    };

    var total_amount = Number(localStorage.getItem("total_amount"));
    _.map(cart, function(cart) {
        localStorage.setItem("total_amount", total_amount + (cart.quantity * cart.price));
    });

    $(e).html('<i class="fa fa-spinner fa-spin" style="font-size:18px"></i> Processando...');
    $(e).prop("disabled", true);

    var url = '';

    if($("#pedidoID").isNullOrEmpty()){
        url = '/pdv/inserir';
    }else{
        var id = $("#pedidoID").val();
        url = '/pdv/alterar/' + id;
    }

    Pace.restart();
    Pace.track(function () {
    $.ajax({
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: url,
        data: form_data,
        success: function(data) {
            $(".emEspera").html(data.emespera);
            $("#checkout-modal").modal("hide");
            cart = [];
            $("#TableNo").text("");
            $("#total_pago").val("");
            $("#valortroco").val("");
            $("#observacao").val("");
            $("#total_amount_modal").html("R$0,00");
            $("#finalizarPedido").html('Finalizar');
            $("#finalizarPedido").prop("disabled", false);
            $("#id").val("");

            var title = 'Pedido Finalizado';

            if(status == vendaStatus.ABERTA){
                title = 'Pedido em espera'
            }

            toastr.success(title);
            $('#idsearchphone').val('');
            $('#idsearchphone').trigger('change');
            if(status == vendaStatus.CONCLUIDA){
                $("#pedidoID").val(data.msg);
                $('#impressaoModal').modal('show');
            }

            $("#p_subtotal").html("R$0,00");
            $("#p_discount").html("R$0,00");
            $("#idcliente").val("");

            show_cart();

            if(status == vendaStatus.CONCLUIDA)
                $(e).html('Finalizar');
            else
                $(e).html('Pedido em espera');

            $(e).prop("disabled", false);
        },
        error: function(xhr, type, exception) {
            console.log(xhr);
            if(xhr.status == 401 || xhr.status == 419){
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
            }else{
                toastr.error(xhr.responseJSON.message);
                if(status == vendaStatus.CONCLUIDA)
                    $(e).html('Finalizar');
                else
                    $(e).html('Pedido em espera');
                $(e).prop("disabled", false);
            }
        }
    });
    });
}

function show_cart() {
    if (cart.length > 0) {
        var qty = 0;
        var total = 0;
        var discount = 0;
        var cart_html = "";
        var obj = cart;
        $.each(obj, function(key, value) {
            console.log('show_cart', value)
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
        console.log($.toMoney(discount))
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
    var item = {
        id: item_uid,
        product_id: parseInt(id),
        price: price,
        name: descricao,
        quantity: quantity,
        discount: discount,
        discountType: discountType,
        discountValue: discountValue,
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

$("body").on("click", "#checkout", function() {
    finalizarClick = true;

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

    // Validação de produtos selecionado
    var total_pedido = $(".valorTotal").html().replace("R$", "");
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

    // Atualiza os valores do modal de checkout
    $("#checkout_subtotal").text($("#p_subtotal").text());
    $("#checkout_desconto").text($("#p_discount").text());
    $("#checkout_total").text($(".valorTotal").text());
    $("#valortotalacobrar").text($(".valorTotal").text());

    var tipoDeVenda = $("#tipoDeVenda").val();
    var data = $.isNotNullAndNotEmpty($("#idcliente").val());

    $("#checkout-modal").modal("show");

    if (tipoDeVenda == vendaTipo.ENTREGAR) {
        if(!data){
            $("#salvarCliente").html("Próximo");
            $("#modalCliente").modal("show");
        }else {
            $("#checkout-modal").modal("show");
        }
        $("#motoboy_row").show();
    }
    if (tipoDeVenda == vendaTipo.RETIRAR) {
        $("#motoboy_row").hide();
        $("#checkout-modal").modal("show");
    }
    if (tipoDeVenda == vendaTipo.NOLOCAL) {
        $("#motoboy_row").hide();
        $("#checkout-modal").modal("show");
    }
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
    //$("#valorDescCento").val("0");
    //$("#valorDesconto").val("0,00");
    var valorTotal = $("#total_amount_modal").html().replace("R$", "");
    //$("#valorAPagar").val(valorTotal);
    $("#Dinheiro").addClass("payment-box-active");
    $("#valortroco").val("0,00");
    $("#valortotalpago").habilitar();
    $("#valortotalpago").focus();
    $("#valortotalpago").select();
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

    e.preventDefault();
    var texto = $(this).val();
    // Remove apenas '.', '/', '-' para manter compatibilidade com backend
    texto = texto.replace(/[\.\/-]/g, '');
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
                // Espera que o backend retorne: id, nome, documento, etc.
                if (!response || response.length === 0) {
                    Swal.fire(
                        "Oops...",
                        "Cliente não encontrado!",
                        "error"
                    );
                    return;
                }
                var cliente = response[0];
                // Preenche campos do cliente
                $('#desCli').text(cliente.text || cliente.nome || cliente.razaosocial || '');
                $('#cliente_cadastro_id').val(cliente.id);
                $('#getCliente').val(cliente.cliente_doc);
                    // Aplica máscara se for CPF/CNPJ
                    if (cliente.cliente_doc) {
                        var doc = cliente.cliente_doc.replace(/\D/g, '');
                        if (doc.length === 11) {
                            $('#getCliente').val(doc.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4'));
                        } else if (doc.length === 14) {
                            $('#getCliente').val(doc.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5'));
                        } else {
                            $('#getCliente').val(cliente.cliente_doc);
                        }
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

                    // // Adiciona ao carrinho
                    // var item = {
                    //     id: parseInt(response.id),
                    //     product_id: parseInt(response.id),
                    //     price: response.farvre,
                    //     name: response.fardes,
                    //     quantity: quantity,
                    //     discount: 0,
                    //     discountType: discountType,
                    //     discountValue: 0,
                    // };
                    // itens = item;
                    // adicionarAoCarrinho(item);
                    // show_cart();

                    // // Limpa campos do produto
                    // $('#getProduto').val("");
                    // $('#getProduto').focus();
                    // $('#item-quantity').val("1");
                    // $('#item-discount').val('0,00');
                    // $('#item-price').val('0,00');
                    // $('#item-subtotal').val('0,00');
                    // $('#find-product').select2('data', null);
                    // $('#find-product').val(null);
                    // $('#find-product').trigger('change');
                    // $('#desProd').html('');

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
$(document).ready(function () {

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
        // Preencher campos na tela principal
        $('#getProduto').val(prodId);
        $('#desProd').html(prodDesc);
        $('#item-price').val(prodPrice);
        $('#item-price').trigger('keyup');
        // Atualizar campo oculto com o id do produto selecionado
        $('#produto_dmf_id').val(prodId);
        // Atualizar o atributo data-id do botão inserir
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
        var totalCarrinho = parseBRL($('.valorTotal').text());
        $('#valortotalacobrar').val(formatBRL(totalCarrinho));
        $('#valorsaldo').val(formatBRL(0));
        $('#valortroco').val(formatBRL(0));
        // Atualiza variável global CobrarValor com o total atual do carrinho
    });

    // Máscara tipo calculadora de dinheiro: transforma dígitos em centavos
    $('#valortotalacobrar').on('input', function(e) {
        var v = $(this).val().replace(/\D/g, ''); // só dígitos
        if (v.length === 0) v = '0';
        var valor = (parseInt(v, 10) / 100).toFixed(2);
        var totalCarrinho = parseBRL($('.valorTotal').text());
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
        $(this).val(formatBRL(valor));
    });

    // Atualiza os valores visíveis do checkout imediatamente
    atualizarCheckoutValores();

    // Controla o comportamento do DropDown para Parcelas de Boleto
    $("body").on('mousedown', '#parcelasBoleto', function(e){
        var $sel = $(this);
        if (!$sel.hasClass('select2-hidden-accessible')) {
            e.preventDefault(); // avoid native dropdown
            atualizarCheckoutValores();
            atualizarParcelasBoleto();
        }
    });

    $("body").on('focus', '#parcelasBoleto', function(){
        var $sel = $(this);
        if (!$sel.hasClass('select2-hidden-accessible')) {
            atualizarCheckoutValores();
            atualizarParcelasBoleto();
        }
    });

    // Ao fechar o select2 ou perder foco, limpa as opções para forçar recálculo na próxima abertura
    $("body").on('select2:close', '#parcelasBoleto', function(e){
        try {
            var $el = $(this);
            $el.empty().append('<option value="">Selecione...</option>').val("").trigger('change');
            try { $el.select2('destroy'); } catch(ex) {}
        } catch(err) {}
    });

    $("body").on('blur', '#parcelasBoleto', function(e){
        try { $(this).empty().append('<option value="">Selecione...</option>').val("").trigger('change'); } catch(err) {}
    });

    // Controla o comportamento do DropDown para Parcelas de Cartão
    $("body").on('mousedown', '#parcelasCartao', function(e){
        var $sel = $(this);
        if (!$sel.hasClass('select2-hidden-accessible')) {
            e.preventDefault(); // avoid native dropdown
            atualizarCheckoutValores();
            atualizarParcelasCartao();
        }
    });

    $("body").on('focus', '#parcelasCartao', function(){
        var $sel = $(this);
        if (!$sel.hasClass('select2-hidden-accessible')) {
            atualizarCheckoutValores();
            atualizarParcelasCartao();
        }
    });

    $("body").on('select2:close', '#parcelasCartao', function(e){
        try {
            var $el = $(this);
            $el.empty().append('<option value="">Selecione...</option>').val("").trigger('change');
            try { $el.select2('destroy'); } catch(ex) {}
        } catch(err) {}
    });

    $("body").on('blur', '#parcelasCartao', function(e){
        try { $(this).empty().append('<option value="">Selecione...</option>').val("").trigger('change'); } catch(err) {}
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
        }
    });

    // Limpa o campo hidden quando o select2 é limpo
    $('#cliente_id').on('change', function () {
        var val = $(this).val();
        if (!val || val === '' || val.length === 0) {
            $('#cliente_cadastro_id').val('');
        }
    });

    // Ao abrir o modal de checkout, resetar campos de pagamento parcelado
    $('#checkout-modal').on('show.bs.modal', function() {
        resetarCamposPagamentoParcelado();
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

        var clientId = data.id;
        var clientName = data.text;
        var clientDoc = data.cliente_doc;

        $('#cliente_cadastro_id').val(clientId);
        // Aplica máscara ao CPF/CNPJ
        if (clientDoc) {
            var doc = clientDoc.replace(/\D/g, '');
            if (doc.length === 11) {
                $('#getCliente').val(doc.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4'));
            } else if (doc.length === 14) {
                $('#getCliente').val(doc.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5'));
            } else {
                $('#getCliente').val(clientDoc);
            }
        } else {
            $('#getCliente').val('');
        }
        $('#desCli').text(clientName);        // Preenche o nome do cliente

        // Aplica máscara
        if (clientDoc && clientDoc.length === 11) {
            $('#getCliente').mask('000.000.000-00');
        } else if (clientDoc && clientDoc.length === 14) {
            $('#getCliente').mask('00.000.000/0000-00');
        }
    });

    // // Ao clicar em OK no modal de produto, preenche os campos com o produto selecionado
    // $('#btn-find-product').on('click', function() {

    //     var data = $('#produto_dmf').select2('data')[0];
    //     console.log('Produto selecionado:', data);

    //     if (data) {
    //         $('#getProduto').val(data.id || data.produto_id);
    //         // Sempre prioriza produto_dm
    //         var descricao = data.text || data.produto_dm || '';
    //         $('#desProd').html(descricao);
    //         var valor = data.produto_vlr || data.valor_venda || data.farvre || '';
    //         $('#item-price').val(valor);
    //         $('#item-price').trigger('keyup');
    //         // Atualiza o data-id do botão 'Inserir' com o id do produto selecionado
    //         $('#btn-adicionar-item').data('id', data.id || data.produto_id);
    //         // Limpa o modal após transferir os dados
    //         $('#produto_dmf').val(null).trigger('change');
    //         $('#produto_dmf_id').val('');
    //         // Zera a variável data
    //         data = null;
    //     }
    // });

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
        } else if (tipoPagamentoSelecionado === "CM") {
            $("#div-cartao").show();
            $("#div-boleto").hide();
            $("#payment-instructions").show();
        } else {
            $("#div-boleto, #div-cartao").hide();
            $("#payment-instructions").hide();
        }
    });

});
