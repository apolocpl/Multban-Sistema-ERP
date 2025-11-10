// //////////////////////////////////
// // VARIÁVEIS GOBAIS COMPARTILHADAS
let regrasParcMap = {};
let cart = [];
let arr_nsu_autoriz = [];
let arr_checkout_desconto = [];
let arr_checkout_cashback = [];
let arr_checkout_total = [];
let arr_meiosPagtoUtilizados = [];
let arr_checkout_juros = [];
let arr_checkout_troco = [];
let tituloAtual = null;
let nsu_tituloAtual = null;
// var cartaoSelecionado = null;
// var precoOriginalProduto = 0;
// var data = [];
// var tipoPagamentoSelecionado = null;
// var products = new Array();
// var count_items = 0;
// var itens = {};
// var finalizarClick = false;
// var searchProduct = false;
// var empresa = window.empresa || null;
// var empresaParam = window.empresaParam || null;
// var podeFecharCheckoutModal = false;
// var cobrar = false;
// var lastToastr = {};
// var venda_subtotal = $("#p_subtotal").text().replace("R$", "").trim();
// var venda_desconto = $("#p_discount").text().replace("R$", "").trim();
// var venda_total = $("#valorTotal").text().replace("R$", "").trim();
// var carrinho = [];
// var checkout_juros_total = 0;
// var totalVendaComJuros = 0;
// var valorParcela = 0;
// var valorParcelaComJuros = 0;
// var valorParcelaSemJuros = 0;
// var jurosTotal = 0;
// var jurosTotalParcela = 0;

// var html_pedidos_by_cli = '';
// var table = null;

// let ultimaAcaoResgate = null;

//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// FUNÇOES UTILIZADAS PELO FRONT END /////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////
// REGRA PARA LIMITAR SUBTOTAL
function validarSubtotal() {
    let quantity = parseInt($('#item-quantity').val()) || 1;
    let price = $.tratarValor($('#item-price').val());
    let subtotal = quantity * price;
    return true;
}

//////////////////////////////////////////////////////////////////////////////////////////////
// ADICIONA ITEM AO CARRINHO
function adicionarAoCarrinho(item){
    // Busca item igual por product_id, price, discount e discountType
    let index = _.findIndex(cart, function(carrinhoItem) {
        return carrinhoItem.product_id   === item.product_id
            && carrinhoItem.price        === item.price
            && carrinhoItem.discount     === item.discount
            && carrinhoItem.discountType === item.discountType;
    });
    if (index === -1) {
        cart.push(item);
        // Atualiza contador de itens distintos
        $('#totalItens').text(cart.length);
    } else {
        cart[index].quantity += item.quantity;
        // Recalcula desconto e subtotal do item existente
        let quantity = cart[index].quantity;
        let price = cart[index].price;
        let discount = cart[index].discount;
        let discountTypeItem = cart[index].discountType;
        let discountTotal = 0;
        if(discountTypeItem === "%"){
            discountTotal = quantity * ((discount * price) / 100);
        }else{
            discountTotal = quantity * discount;
        }
        cart[index].discountValue = discountTotal;
        cart[index].subtotal = Number((price * quantity) - discountTotal).toFixed(2);
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////
// MOSTRA ITENS DO CARRINHO
function show_cart() {

    if (cart.length > 0) {
        let qty = 0;
        let total = 0;
        let discount = 0;
        let cart_html = "";
        let obj = cart;
        $.each(obj, function(key, value) {
            qty = Number(value.quantity);
            let itemTotal = Number(value.price * qty);

            // Regra: desconto não pode ser maior que o total do item
            if (value.discountValue > itemTotal) {
                Swal.fire('', 'O desconto não pode ser maior que o valor do item!', 'error');
                // Zera o desconto do item no carrinho (estado) para não afetar os totais
                let idx = _.findIndex(cart, { id: value.id });
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
                let $btn = $("button[data-id='" + value.id + "'].btn-change-discount");
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
            cart_html += '<td width="15%"><input type="number" value="' + parseInt(value.quantity) +
                         '" id="item-quantity-' + value.id +
                         '" class="form-control form-control-sm quantityToCart" data-id=' + value.id +
                         ' min="1" step="1" pattern="[0-9]*" inputmode="numeric"></td>';
            cart_html += '<td width="15%"><input type="text" value="' + $.toMoneySimples(value.price) +
                         '" id="item-price-' + value.id +
                         '" class="form-control form-control-sm money priceToCart" data-id=' + value.id + '></td>';
            cart_html += '<td width="15%"><div class="input-group input-group-sm">';
            cart_html += '<input type="text" value="' + $.toMoneySimples(value.discount) +
                         '" id="item-discount-' + value.id +
                         '" class="form-control form-control-sm money discountToCart" data-id=' + value.id + '>';
            cart_html += '<span class="input-group-append"><button type="button" data-id=' + value.id +
                         ' class="btn btn-primary btn-sm btn-change-discount">'+value.discountType+'</button>';
            cart_html += '</span></div></td>';
            cart_html += '<td width="15%" class="text-center"><h5 style="margin:0px;">' +
                         $.toMoney((value.price * value.quantity) - value.discountValue) + '</h5> </td>';
            cart_html += '<td width="10%" class="text-center"><a href="javascript:void(0)"';
            cart_html += 'class="btn btn-sm btn-danger DeleteItem" data-id=' + value.id + '><i class="fa fa-trash"></i></a></td>';
            cart_html += '</tr>';

            total = Number(total) + itemTotal;

        });

        let taxa = 0;

        $("#p_subtotal").html($.toMoney(total));
        $("#p_discount").html($.toMoney(discount));
        $("#valorDesconto").val($.toMoneyVendaSimples(String(discount), false));

        let total_amount = Number(total) - discount;
        $("#total_amount").val(total_amount);
        $("#total_amount_modal").html($.toMoney(total));
        $("#taxa").val(taxa);
        $("#valorAPagar").val($.toMoneyVendaSimples(String(total_amount), false))

        $("#p_valorTotal").html($.toMoney(total_amount));
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
        $("#p_valorTotal").html("R$0,00");
        $("#p_subtotal").html("R$0,00");
        $("#p_discount").html("R$0,00");
        $("#valorDesconto").val("0,00");
        $("#total_amount_modal").html("R$0,00");
        $("#CartHTML").html("");
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////
// DELETA ITENS DO CARRINHO
function deleteItemFromCart(item) {
    let index = _.findIndex(cart, item);
    cart.splice(index, 1);
    show_cart();
}

//////////////////////////////////////////////////////////////////////////////////////////////
// FUNÇÃO PARA FORMATAR VALOR BRL - STRING PARA FLOAT (ACEITA 1.234,56 OU 1234,56 OU 1234)
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

//////////////////////////////////////////////////////////////////////////////////////////////
// FUNÇÃO PARA FORMATAR VALOR PARA PADRÃO BRASILEIRO
function formatBRL(valor) {
    return valor.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

//////////////////////////////////////////////////////////////////////////////////////////////
// FUNÇÃO PARA ATUALIZAR O CAMPO #valortotalacobrar
function atualizarCampoValortotalacobrar(checkout_total) {
    let totalCarrinho = parseBRL($('#checkout_total').text());
    let tipoPagamento = $('.payment-box-active').data('identificacao') || $('#id_forma_pagto').val();
    let valorCobrar = Number(checkout_total) || 0;

    // Para tipos diferentes de Dinheiro, não permitir cobrar mais que o total
    if (tipoPagamento !== 'DN' && valorCobrar > totalCarrinho) {
        valorCobrar = totalCarrinho;
    }

    // Atualiza campo com formatação brasileira
    $('#valortotalacobrar').val((typeof formatBRL === 'function') ? formatBRL(valorCobrar) : valorCobrar.toFixed(2).replace('.', ','));

    // Recalcula saldo e troco
    let saldo = totalCarrinho - valorCobrar;
    if (valorCobrar > totalCarrinho)
        saldo = 0;

    $('#valorsaldo').val(formatBRL(saldo));

    let troco = 0;
    if (tipoPagamento === 'DN' && valorCobrar > totalCarrinho) {
        troco = valorCobrar - totalCarrinho;
    }

    $('#valortroco').val(formatBRL(troco));
}

//////////////////////////////////////////////////////////////////////////////////////////////
// FUNÇÃO PARA RESETAR CAMPOS DE PAGAMENTO PARCELADO
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
        let $pb = $("#parcelasBoleto");
        $pb.empty().append('<option value="">Selecione...</option>').val("").trigger('change.select2');
    } catch(e) { console.warn(e); }
    try {
        let $pc = $("#parcelasCartao");
        $pc.empty().append('<option value="">Selecione...</option>').val("").trigger('change.select2');
    } catch(e) { console.warn(e); }
}

//////////////////////////////////////////////////////////////////////////////////////////////
// FUNÇÃO PARA ATUALIZAR OS TEXTOS DAS OPÇÕES DO SELECT PRIMEIRAPARACARTAO
function atualizarOpcoesPrimeiraParaCartao() {
    let $select = $('#PrimeiraParaCartao');
    let hoje = new Date();

    $select.find('option').each(function() {
        let val = $(this).val();
        if (val == '6') {
            $(this).text('Rotativo');
        } else if (val == '7') {
            let mes = hoje.getMonth() + 2;
            let ano = hoje.getFullYear();
            if (mes > 12) { mes = 1; ano++; }
            let mesNome = new Date(ano, mes-1, 1).toLocaleString('pt-BR', { month: 'long' });
            $(this).text(mesNome.charAt(0).toUpperCase() + mesNome.slice(1) + ' ' + ano);
        } else if (val == '8') {
            let mes = hoje.getMonth() + 3;
            let ano = hoje.getFullYear();
            if (mes > 12) { mes -= 12; ano++; }
            let mesNome = new Date(ano, mes-1, 1).toLocaleString('pt-BR', { month: 'long' });
            $(this).text(mesNome.charAt(0).toUpperCase() + mesNome.slice(1) + ' ' + ano);
        } else if (val == '9') {
            let mes = hoje.getMonth() + 4;
            let ano = hoje.getFullYear();
            if (mes > 12) { mes -= 12; ano++; }
            let mesNome = new Date(ano, mes-1, 1).toLocaleString('pt-BR', { month: 'long' });
            $(this).text(mesNome.charAt(0).toUpperCase() + mesNome.slice(1) + ' ' + ano);
        }
    });
}

//////////////////////////////////////////////////////////////////////////////////////////////
// FUNÇÃO PARA CALCULAR E PREENCHER dataPrimeiraParcelaCartao
function calcularDataPrimeiraParcelaCartao() {

    let diaFech = parseInt(window.clienteDataFech) || 1;

    let hoje = new Date();
    let ano = hoje.getFullYear();
    let mes = hoje.getMonth() + 1;
    let opcao = $('#PrimeiraParaCartao').val();
    let dataBase;

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
    let dataStr = dataBase.toISOString().slice(0,10);
    $('#dataPrimeiraParcelaCartao').val(dataStr);
    let mesNome = dataBase.toLocaleString('pt-BR', { month: 'long' });
    $('#dataPrimeiraParcelaCartaoHelp').text('Data calculada automaticamente: ' + diaFech + '/' + mesNome.charAt(0).toUpperCase() + mesNome.slice(1) + '/' + ano);
}

//////////////////////////////////////////////////////////////////////////////////////////////
// FUNÇÃO PARA CALCULAR E PREENCHER dataPrimeiraParcelaBoleto
function calcularDataPrimeiraParcelaBoleto() {
    let selected = $(this).val();
    let $dataField = $("#dataPrimeiraParcelaBoleto");
    let $help = $("#dataPrimeiraParcelaBoletoHelp");

    if (selected === "5") {
        $dataField.val("");
        $dataField.prop("readonly", false);
        $help.text("Selecione manualmente a data da primeira parcela.");
    } else if (selected && regrasParcMap[selected] !== undefined) {
        let dias = regrasParcMap[selected];
        let hoje = new Date();
        hoje.setDate(hoje.getDate() + dias);
        let yyyy = hoje.getFullYear();
        let mm = String(hoje.getMonth() + 1).padStart(2, '0');
        let dd = String(hoje.getDate()).padStart(2, '0');
        let dataFormatada = yyyy + '-' + mm + '-' + dd;
        $dataField.val(dataFormatada);
        $dataField.prop("readonly", true);
        $help.text("Data calculada automaticamente: " + dd + "/" + mm + "/" + yyyy);
    } else {
        $dataField.val("");
        $dataField.prop("readonly", true);
        $help.text("");
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////
// FUNÇÃO PARA BUSCAR AS PARCELAS DISPONÍVEIS
function fetchParcelas(tipo, selectSelector, extraParams) {
    extraParams = extraParams || {};

    let payload = Object.assign({
        tipo: tipo,
        valortotalacobrar: $('#valortotalacobrar').val() || '',
        parclib: ($('#card_posparc').length ? $('#card_posparc').val() : undefined),
        vendaSemJuros: (tipo === 'card' ? ($('#vendaSemJurosCartao').is(':checked') ? 1 : 0) : ($('#vendaSemJurosBoleto').is(':checked') ? 1 : 0))
    }, extraParams);

    // Retorna os valora do AJAX
    return $.getJSON('/pdv-web/calcular-parcelas', payload)
        .done(function(resp) {
            if (!resp || !resp.success || !Array.isArray(resp.options)) {
                console.warn('calcular-parcelas: resposta inválida', resp);
                return;
            }
            let $select = $(selectSelector);
            if ($select.length === 0) {
                console.warn('select não encontrado:', selectSelector);
                return;
            }
            $select.empty();
            $select.append('<option value="">Selecione...</option>');
            resp.options.forEach(function(opt) {
                let attrs = '';
                if (opt.data) {
                    for (let k in opt.data) {
                        if (!opt.data.hasOwnProperty(k)) continue;
                        attrs += ' data-' + k + '="' + opt.data[k] + '"';
                    }
                }
                $select.append('<option value="' + opt.value + '"' + attrs + '>' + opt.label + '</option>');
            });

        })
        .fail(function(jqxhr, textStatus, error) {
            let msg = 'Falha ao obter parcelas: ' + textStatus + (error ? (' - ' + error) : '');
            console.error(msg, jqxhr && jqxhr.responseText);
        });
}

//////////////////////////////////////////////////////////////////////////////////////////////
// ATUALIZA AS PARCELAS QUE APARECEM COMO OPÇÕES NA VENDA POR BOLETO
function atualizarParcelasBoleto() {
    fetchParcelas('boleto', '#parcelasBoleto');
}

//////////////////////////////////////////////////////////////////////////////////////////////
// ATUALIZA AS PARCELAS QUE APARECEM COMO OPÇÕES NA VENDA POR CARTÃO
function atualizarParcelasCartao() {
    fetchParcelas('card', '#parcelasCartao');
}

//////////////////////////////////////////////////////////////////////////////////////////////
// ATUALIZA O TOTAL DE PONTOS SELECIONADOS
function atualizarTotalPontosSelecionados() {
    let total = 0;
    $('.pontos-utilizar').each(function() {
        let val = parseFloat($(this).val().replace(/\./g, '').replace(',', '.')) || 0;
        total += val;
    });

    let valorTotalAPagar = getValorTotalAPagar();

    // Se o total selecionado ultrapassar o valor a pagar
    if (total > valorTotalAPagar) {
        // GUARDA O TOTAL SELECIONADO ANTES DE DESFAZER
        let totalSelecionadoParaResgatar = total;

        // Desfaz a última ação
        if (ultimaAcaoResgate) {
            desfazerUltimaAcao();
            // Recalcula o total após desfazer
            total = 0;
            $('.pontos-utilizar').each(function() {
                total += parseBRL($(this).val()) || 0;
            });
        }

        // Exibe mensagem de aviso
        Swal.fire({
            icon: 'warning',
            title: 'Atenção!',
            text: `O total de pontos selecionados (${formatBRL(totalSelecionadoParaResgatar)}) não pode ser maior que o valor a pagar (${formatBRL(valorTotalAPagar)}).`,
            confirmButtonText: 'OK',
            showConfirmButton: true,
            allowOutsideClick: false
        });
    }

    $('#totalPontosSelecionados').text(total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
}

//////////////////////////////////////////////////////////////////////////////////////////////
// ATUALIZA A TABELA DE RESGATE DE PONTOS
function preencherTabelaResgate(cartoes) {
    $.ajax({
        url: '/pdv-web/resgatar-tabela',
        method: 'POST',
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ cartoes: cartoes }),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(resp) {
            if (resp && resp.success) {
                $('#tabelaResgatePontos tbody').html(resp.html);
                // chama a função cliente que recalcula total/reativa handlers
                if (typeof atualizarTotalPontosSelecionados === 'function') {
                    atualizarTotalPontosSelecionados();
                }
            } else {
                console.warn('Resposta inválida ao carregar tabela de resgate', resp);
            }
        },
        error: function(xhr) {
            console.error('Falha ao carregar tabela de resgate', xhr);
        }
    });
}

//////////////////////////////////////////////////////////////////////////////////////////////
// DESFAZ A ÚLTIMA AÇÃO DE RESGATE DE PONTOS
function desfazerUltimaAcao() {
    if (!ultimaAcaoResgate) return;

    let elemento = $(ultimaAcaoResgate.elemento);
    let tr = elemento.closest('tr');

    if (ultimaAcaoResgate.tipo === 'checkbox') {
        // Desmarca o checkbox e zera o campo
        elemento.prop('checked', false);
        tr.find('.pontos-utilizar').val('0,00');
    } else if (ultimaAcaoResgate.tipo === 'input') {
        // Restaura o valor anterior do input
        elemento.val(formatBRL(ultimaAcaoResgate.valorAnterior));
        // Ajusta o checkbox conforme o valor
        let max = parseBRL(tr.find('.pontos-disponiveis').text());
        let val = ultimaAcaoResgate.valorAnterior;
        tr.find('.utilizar-tudo').prop('checked', val === max);
    }

    ultimaAcaoResgate = null;
}

//////////////////////////////////////////////////////////////////////////////////////////////
// FUNÇÃO PARA APLICAR MÁSCARA SIMPLES EM NÚMERO DE CARTÃO
function maskCardNumberSimple(cardNumber) {
    if (!cardNumber) return '';
    let n = String(cardNumber).replace(/\D/g, '');
    return n.replace(/(.{4})/g, '$1.').replace(/\.$/, '');
}

//////////////////////////////////////////////////////////////////////////////////////////////
// FUNÇÃO PARA OBTER O VALOR TOTAL A PAGAR
function getValorTotalAPagar() {
    let totalCobrar = parseBRL($("#checkout_subtotal").text());
    let checkout_cashback = parseBRL($("#checkout_cashback").text());
    let totalDesconto = parseBRL($("#checkout_desconto").text());
    let totalPago = parseBRL($("#checkout_pago").text());

    let totalAPagar = totalCobrar - checkout_cashback - totalDesconto - totalPago;
    return totalAPagar > 0 ? totalAPagar : 0;
}

//////////////////////////////////////////////////////////////////////////////////////////////
// FUNÇÃO PARA FORMATAR O STATUS DO CARTÃO
function formatarStatusCartao(sts) {
    // Exemplo: pode adaptar para cores/icons conforme padrão dos produtos
    if (sts === 'AT') return '<span class="badge badge-success">Ativo</span>';
    if (sts === 'BL') return '<span class="badge badge-warning">Bloqueado</span>';
    if (sts === 'IN') return '<span class="badge badge-danger">Inativo</span>';
    if (sts === 'EX') return '<span class="badge badge-danger">Inativo</span>';
    return sts;
}

//////////////////////////////////////////////////////////////////////////////////////////////
// FUNÇÃO PARA MASCARAR NÚMERO DO CARTÃO (formato: 1234 **** **** 5678)
function maskCardNumber(cardNumber) {
    if (!cardNumber) return '';
    let n = String(cardNumber).replace(/\D/g, '');
    if (n.length < 8) return n; // Não mascara se não tiver pelo menos 8 dígitos
    let first4 = n.substr(0, 4);
    let last4 = n.substr(-4);
    return `${first4}.****.****.${last4}`;
}

//////////////////////////////////////////////////////////////////////////////////////////////
// FUNÇÃO PARA VALIDAR CPF
function validaCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    if (cpf.length !== 11 || /^([0-9])\1+$/.test(cpf)) return false;
    let soma = 0, resto;
    for (let i = 1; i <= 9; i++) soma += parseInt(cpf[i-1]) * (11 - i);
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf[9])) return false;
    soma = 0;
    for (let i = 1; i <= 10; i++) soma += parseInt(cpf[i-1]) * (12 - i);
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf[10])) return false;
    return true;
}

//////////////////////////////////////////////////////////////////////////////////////////////
// FUNÇÃO PARA VALIDAR CNPJ
function validaCNPJ(cnpj) {
    cnpj = cnpj.replace(/\D/g, '');
    if (cnpj.length !== 14) return false;
    if (/^([0-9])\1+$/.test(cnpj)) return false;
    let tamanho = cnpj.length - 2;
    let numeros = cnpj.substring(0, tamanho);
    let digitos = cnpj.substring(tamanho);
    let soma = 0;
    let pos = tamanho - 7;
    for (let i = tamanho; i >= 1; i--) {
        soma += numeros[tamanho - i] * pos--;
        if (pos < 2) pos = 9;
    }
    let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos[0]) return false;
    tamanho = tamanho + 1;
    numeros = cnpj.substring(0, tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (let i = tamanho; i >= 1; i--) {
        soma += numeros[tamanho - i] * pos--;
        if (pos < 2) pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos[1]) return false;
    return true;
}

//////////////////////////////////////////////////////////////////////////////////////////////
// REALIZAR COBRANÇA TOTAL OU PARCIAL
function realizarCobranca(cobrancaDados) {

    $.ajax({
        url: '/pdv-web/realizar-venda',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': cobrancaDados.token },
        data: {
            token:                          cobrancaDados.token,
            cliente_id:                     cobrancaDados.cliente_id,
            tipoPagto:                      cobrancaDados.tipoPagto,
            checkout_subtotal:              cobrancaDados.checkout_subtotal,
            checkout_cashback:              cobrancaDados.checkout_cashback,
            checkout_desconto:              cobrancaDados.checkout_desconto,
            checkout_pago:                  cobrancaDados.checkout_pago,
            checkout_descontado:            cobrancaDados.checkout_descontado,
            checkout_troco:                 cobrancaDados.checkout_troco,
            checkout_resgatado:             cobrancaDados.checkout_resgatado,
            checkout_total:                 cobrancaDados.checkout_total,
            valortotalacobrar:              cobrancaDados.valorTotalacobrar,
            check_semjuros:                 cobrancaDados.vendaSemJuros,
            check_reembolso:                cobrancaDados.check_reembolso,
            tax_categ:                      cobrancaDados.tax_categ,
            regra_parc:                     cobrancaDados.regra_parc,
            valorTotalComJuros:             cobrancaDados.valorTotalComJuros,
            valorParcelaComJuros:           cobrancaDados.valorParcelaComJuros,
            valorParcelaSemJuros:           cobrancaDados.valorParcelaSemJuros,
            jurosTotal:                     cobrancaDados.jurosTotal,
            jurosTotalParcela:              cobrancaDados.jurosTotalParcela,
            parcelas:                       cobrancaDados.parcelas,
            dt_primeira_parc:               cobrancaDados.dt_primeira_parc,
            proporcao_cobrado:              cobrancaDados.proporcao_cobrado,
            checkout_desconto_proporcional: cobrancaDados.checkout_desconto_proporcional,
            checkout_cashback_proporcional: cobrancaDados.checkout_cashback_proporcional,
            carrinho:                       cobrancaDados.carrinho,
            vlr_dec_mn:                     cobrancaDados.vlr_dec_mn,
            vlr_dec_mn_item:                cobrancaDados.vlr_dec_mn_item,
            vlr_atr_m:                      cobrancaDados.vlr_atr_m,
            vlr_atr_j:                      cobrancaDados.vlr_atr_j,
            isent_mj:                       cobrancaDados.isent_mj,
            protestado:                     cobrancaDados.protestado,
            negociacao:                     cobrancaDados.negociacao,
            vlr_acr_mn:                     cobrancaDados.vlr_acr_mn,
            vlr_acr_mn_item:                cobrancaDados.vlr_acr_mn_item,
            vlr_cst_cob:                    cobrancaDados.vlr_cst_cob,
            negociacao_obs:                 cobrancaDados.negociacao_obs,
            negociacao_file:                cobrancaDados.negociacao_file,
            check_ant:                      cobrancaDados.check_ant,
            perct_ant:                      cobrancaDados.perct_ant,
            ant_desc:                       cobrancaDados.ant_desc,
            pgt_vlr:                        cobrancaDados.pgt_vlr,
            pgt_desc:                       cobrancaDados.pgt_desc,
            pgt_mtjr:                       cobrancaDados.pgt_mtjr,
            vlr_rec:                        cobrancaDados.vlr_rec,
            pts_disp_part:                  cobrancaDados.pts_disp_part,
            pts_disp_fraq:                  cobrancaDados.pts_disp_fraq,
            pts_disp_mult:                  cobrancaDados.pts_disp_mult,
            pts_disp_cash:                  cobrancaDados.pts_disp_cash,
            card_tp:                        cobrancaDados.card_tp,
            card_mod:                       cobrancaDados.card_mod,
            card_categ:                     cobrancaDados.card_categ,
            card_desc:                      cobrancaDados.card_desc,
            card_uuid:                      cobrancaDados.card_uuid,
            cliente_cardn:                  cobrancaDados.cliente_cardn,
            cliente_cardcv:                 cobrancaDados.cliente_cardcv,
            card_saldo_vlr:                 cobrancaDados.card_saldo_vlr,
            card_limite:                    cobrancaDados.card_limite,
            card_saldo_pts:                 cobrancaDados.card_saldo_pts,
            card_sts:                       cobrancaDados.card_sts,
            card_tp:                        cobrancaDados.card_tp,
            card_mod:                       cobrancaDados.card_mod,
            card_categ:                     cobrancaDados.card_categ,
            card_desc:                      cobrancaDados.card_desc,
            card_uuid:                      cobrancaDados.card_uuid,
            cliente_cardn:                  cobrancaDados.cliente_cardn,
            cliente_cardcv:                 cobrancaDados.cliente_cardcv,
            card_saldo_vlr:                 cobrancaDados.card_saldo_vlr,
            card_limite:                    cobrancaDados.card_limite,
            card_saldo_pts:                 cobrancaDados.card_saldo_pts,
            card_sts:                       cobrancaDados.card_sts,
            tituloAtual:                    tituloAtual,
            nsu_tituloAtual:                nsu_tituloAtual,
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
                let totaljuros = arr_checkout_juros.reduce((a, b) => Number(a) + Number(b), 0);
                let totalCobrado = arr_checkout_total.reduce((a, b) => Number(a) + Number(b), 0);
                let totalTroco = arr_checkout_troco.reduce((a, b) => Number(a) + Number(b), 0);
                let totalDesconto = arr_checkout_desconto.reduce((a, b) => Number(a) + Number(b), 0);
                let saldo = cobrancaDados.checkout_subtotal - cobrancaDados.checkout_cashback - cobrancaDados.checkout_desconto - totalCobrado;

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
                        $("#p_valorTotal").html("R$ 0,00");
                        $("#CartHTML").html("");
                        $("#p_subtotal").html("R$ 0,00");
                        $("#p_discount").html("R$ 0,00");
                        $(".totalPagar").html("R$ 0,00");
                        $("#pedidoID").val("");
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
                        let tipo_pagamento = '';
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

                            let params = {
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

                            let htmlComprovante = gerarComprovanteVenda(params);

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
                                let comprovanteHtml = $('#comprovante-venda').prop('outerHTML');
                                // Abre nova janela
                                let printWindow = window.open('', '', 'width=800,height=600');
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
                let msg = resp.error || resp.message || 'Não foi possível registrar a cobrança.';
                Swal.fire('Erro', msg, 'error');
            }

        },

        error: function(xhr) {
            let msg = 'Falha na comunicação com o servidor.';
            if (xhr && xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) {
                msg = xhr.responseJSON.error || xhr.responseJSON.message;
            } else if (xhr && xhr.responseText) {
                try {
                    let json = JSON.parse(xhr.responseText);
                    msg = json.error || json.message || msg;
                } catch(e) {
                    msg = xhr.responseText;
                }
            }
            Swal.fire('Erro', msg, 'error');
        }
    });

}










//////////////////////////////////////////////////////////////////////////////////////////////
// AÇÕES EXECUTADAS SOMENTE DEPOIS QUE TODO A PÁGINA ESTIVER CARREGADA ///////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
$(document).ready(function () {

//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// TECLAS DE ATALHO //////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
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

    shortcut.add("F7", function (e) {
        e.preventDefault();
        $("#limparCarrinho").trigger('click');
    });

    shortcut.add("ESC", function (e) {
        e.preventDefault();
        $("#pesquisar-cliente-modal").modal('hide');
    });

    shortcut.add("F9", function (e) {
        e.preventDefault();
        $("#checkout").trigger('click');
    });









//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// AÇÕES EXECUTADAS QUANDO O DOM ESTIVER PRONTO //////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////

    // FIXA A NAVBAR NO TOPO DA PÁGINA
    $('body').addClass('layout-navbar-fixed');

    $(function () {

        // INICIALIZA SELECT2 DO PRODUTO NO MODAL DE PESQUISA
        $('.select2').select2();
        ns.comboBoxSelectTags("produto_dmf", "/produto/obter-descricao-produto", "produto_id");

        $('#produto_dmf').on('select2:select', function (e) {
            let data = e.params.data;
            $('#produto_dmf_id').val(data.id);
            $('#produto_tipo_id').val(data.produto_tipo_id);
        });

        // MINIMIZA A BARRA LATERAL AO CARREGAR A PÁGINA
        const $btn = $(".navbar-minimalize");
        if ($btn.length && !$("body").hasClass("navbar-minimized")) {
            $btn.trigger('click');
        }

    });










//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// AÇÕES DE PESQUISA DO CLIENTE //////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////

    $("body").on("click", "#btnPesquisarCliente", function(){
        $("#pesquisar-cliente-modal").modal('show');
    });

    $('#btnPesquisarCliente').on('click', function() {
        $('#getCliente').val('');
        $('#desCli').text('');
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO CLICAR EM OK NO MODAL DE CLIENTE, PREENCHE OS CAMPOS COM O CLIENTE SELECIONADO
    $('#btn-find-client').on('click', function() {
        let data = $('#cliente_id').select2('data')[0];
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
            let doc = window.clientDoc.replace(/\D/g, '');
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

    //////////////////////////////////////////////////////////////////////////////////////////////
    // LIMPA O NOME DO CLIENTE QUANDO O CAMPO CPF/CNPJ É LIMPO E APLICA MÁSCARA DINÂMICA
    $('#getCliente').on('input', function() {
        let valor = $(this).val().replace(/\D/g, '');
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

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO DIGITAR O CPF DO CLIENTE E CLICAR A TECLA "ENTER", O SISTEMA PESQUISA O CLIENTE PELO CPF
    $("body").on("keyup", "#getCliente", function (e) {

        e.preventDefault();
        let texto = $(this).val();
        // Remove apenas '.', '/', '-' para manter compatibilidade com backend
        texto = texto.replace(/[\.\/-]/g, '');

        // Validação de documento ao pressionar Enter
        if (e.key == 'Enter') {
            let doc = texto.replace(/\D/g, '');
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
        let url = "/cliente/get-client?parametro=" + texto;

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
                            let doc = window.clientDoc.replace(/\D/g, '');
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

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO SAIR DO CAMPO DE PESQUISA DO CLIENTE, REMOVE O FOCO DE QUALQUER ELEMENTO DENTRO DO MODAL
    $('#pesquisar-cliente-modal').on('hide.bs.modal', function() {
        $('#pesquisar-cliente-modal').find(':focus').blur();
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // LIMPA O CAMPO HIDDEN DO CLIENTE QUANDO O SELECT2 É LIMPO
    $('#cliente_id').on('change', function () {
        let val = $(this).val();
        if (!val || val === '' || val.length === 0) {
            $('#cliente_cadastro_id').val('');
        }
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // ATUALIZA CLIENTE GLOBAL AO SELECIONAR CLIENTE
    $("body").on('change', '#cliente_cadastro_id', function() {
        let id = $(this).val();
        let cliente = {};
        if (window.responseClientes && Array.isArray(window.responseClientes)) {
            cliente = window.responseClientes.find(function(c) { return c.id == id; }) || {};
        }
        window.responseCliente = cliente;
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // DEPOIS DE ABRIR O MODAL DE PESQUISA DO CLIENTE, FOCA E ABRE O SELECT2
    $('#pesquisar-cliente-modal').on('shown.bs.modal', function() {
        $("#find-client").select2('focus');
        $("#find-client").select2('open');
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO ABRIR O MODAL DE PESQUISA DO CLIENTE, LIMPA OS CAMPOS
    $('#pesquisar-cliente-modal').on('show.bs.modal', function () {
        $('#cliente_id').val(null).trigger('change');
        $('#cliente_id').empty().trigger('change');
        $('#cliente_id').select2('data', null);
        $('#cliente_id').attr('data-placeholder', 'Pesquise o Nome ou CPF/CNPJ do Cliente');
        $('#cliente_cadastro_id').empty().trigger('change');
        $('#cliente_cadastro_id').val(null).trigger('change');
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO CLICAR NO BOTÃO DE ADICIONAR CLIENTE, ABRE O MODAL DE CADASTRO DE CLIENTE
    $("body").on("click", "#showModalCliente", function(){
        finalizarClick = false;
        $("#salvarCliente").html("OK")
        $("#modalCliente").modal('show');
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO ABRIR O MODAL DE CARTÕES DO CLIENTE, ATUALIZA O TÍTULO COM O NOME DO CLIENTE
    $('#modalCartaoMult').on('show.bs.modal', function () {
        let nomeCliente = $('#desCli').text() || '';
        $('#modalCartaoMultLabel').text('Cartões Registrados Para: ' + nomeCliente);
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO FECHAR O MODAL DE CARTÕES DO CLIENTE, REMOVE O FOCO DE QUALQUER ELEMENTO DENTRO DO MODAL
    $('#modalCartaoMult').on('hide.bs.modal', function() {
        if (document.activeElement && document.activeElement !== document.body) {
            try { document.activeElement.blur(); } catch (e) { /* silent */ }
        }
    });










//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// AÇÕES DE PESQUISA DO PRODUTO //////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
    $("body").on("click", "#btnPesquisarProduto", function(){
        $("#pesquisar-produto-modal").modal('show');
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO ABRIR O MODAL DE PESQUISA DO PRODUTO, LIMPA OS CAMPOS
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

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO SELECIONAR UM PRODUTO, SALVA O PREÇO ORIGINAL
    $('body').on('click', '.produto-item-modal', function() {
        precoOriginalProduto = $(this).data('price');
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO FECHAR O MODAL DE PESQUISA DO PRODUTO, FOCA O CAMPO DE QUANTIDADE
    $('#pesquisar-produto-modal').on('hidden.bs.modal', function() {
        setTimeout(() => { $('#item-quantity').focus();}, 500);
    });

    // //////////////////////////////////////////////////////////////////////////////////////////////
    // // AO ABRIR O MODAL DE PESQUISA DO PRODUTO, FOCA E ABRE O SELECT2
    // $('#pesquisar-produto-modal').on('shown.bs.modal', function() {
    //     $("#find-product").select2('focus');
    //     $("#find-product").select2('open');
    // });

    // //////////////////////////////////////////////////////////////////////////////////////////////
    // // AO SELECIONAR UM PRODUTO NO MODAL DE PESQUISA, PREENCHA OS CAMPOS CORRESPONDENTES
    // $("body").on("change", "#find-product", function(e) {
    //     let data = $(this).select2('data')[0];
    //     if (data) {
    //         if(!data.id){
    //             return;
    //         }
    //         let quantity = parseInt($('#item-quantity').val()) == 0 ? 1 : parseInt($('#item-quantity').val());

    //         console.log(data);

    //         $("#desProd").html(data.fardes);
    //         $("#item-price").val(data.farvre);
    //         $("#item-price").trigger('keyup');
    //         $('#btn-adicionar-item').data('id', data.id);
    //         $("#item-subtotal").val(Number(data.farvre * quantity).toFixed(2));
    //         $("#item-subtotal").trigger('keyup');
    //         $('#pesquisar-produto-modal').modal('hide');
    //         $("#item-quantity").val(1);
    //     }
    // });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO CLICAR EM UM PRODUTO NA LISTA DO MODAL, TRANSFERE OS DADOS PARA A TELA PRINCIPAL
    $('body').on('click', '.produto-item-modal', function() {
        let prodId = $(this).data('id');
        let prodDesc = $(this).data('desc');
        let prodPrice = $(this).data('price');
        let prodTipoId = $(this).data('tipoid');
        let quantity = parseInt($('#item-quantity').val()) == 0 ? 1 : parseInt($('#item-quantity').val());

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
        // Atualiza o subtotal
        $("#item-subtotal").val(Number(prodPrice * quantity).toFixed(2));
        // Fechar modal
        $('#pesquisar-produto-modal').modal('hide');
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // LIMPA OS DADOS DO PRODUTO QUANDO LIMPA O CAMPO DE PESQUISA
    $('#getProduto').on('input', function() {
        let valor = $(this).val().replace(/\D/g, '');
        if (!valor) {
            $('#desProd').text('');
            $(this).val('');
            $('#item-price').empty().trigger('change');
            $('#item-price').val('0,00');
            $('#item-quantity').val('1');
            $('#item-discount').val('0,00');
            return;
        }
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO DIGITAR O CÓDIGO DO PRODUTO E CLICAR A TECLA "ENTER", O SISTEMA PESQUISA PELO CÓDIGO
    $("body").on("keyup", "#getProduto", function (e) {

        e.preventDefault();
        let texto = $(this).val();
        let url = "/produto/obter-descricao-produto?pdv='sim'&parametro=" + texto;
        let quantity = $.tratarValor($('#item-quantity').val());

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
                    let produto = Array.isArray(response) ? response[0] : response;
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
                    $("#item-subtotal").val(Number(produto.produto_vlr * quantity).toFixed(2));

                })
                .fail(function (xhr, status, error) {
                    $('#desProd').html('');
                    $('#item-price').val('0,00');
                    $('#item-quantity').val('1');
                    $('#item-discount').val('0,00');
                    $('#item-subtotal').val('0,00');
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

    //////////////////////////////////////////////////////////////////////////////////////////////
    // CARREGAR LISTA DE PRODUTOS NO MODAL DE PESQUISA
    $('#pesquisar-produto-modal').on('show.bs.modal', function() {
        let $tbody = $('#produtos-lista-modal tbody');
        $tbody.empty();
        // Adiciona spinner de loading
        let $spinner = $('<tr id="produtos-loading-spinner"><td colspan="5" class="text-center"><span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span> Carregando produtos...</td></tr>');
        $tbody.append($spinner);
        $.get('/api/produtos', function(produtos) {
            $tbody.empty();
            produtos.forEach(function(prod) {
                // Badge do status
                let badge = '';
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

    //////////////////////////////////////////////////////////////////////////////////////////////
    // FILTRAR PRODUTOS DA LISTA CONFORME COMBO BOX SELECT2
    $('#produto_dmf').on('change', function() {
        let filtro = '';
        let selected = $(this).select2('data');
        if (selected && selected.length && selected[0].text) {
            filtro = selected[0].text.toLowerCase();
        }
        $('#produtos-lista-modal tbody tr').each(function() {
            let desc = $(this).data('desc');
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

    //////////////////////////////////////////////////////////////////////////////////////////////
    // LIMPAR O CAMPO HIDDEN DO PRODUTO QUANDO O SELECT2 É LIMPO
    $('#produto_dmf').on('change', function () {
        let val = $(this).val();
        if (!val || val === '' || val.length === 0) {
            $('#produto_dmf_id').val('');
            $('#produto_tipo_id').val('');
        }
    });










//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// CÁLCULOS DE DESCONTO OU TOTAIS DOS ITENS ANTES DE ADCIONAR AO CARRINHO ////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////

    //////////////////////////////////////////////////////////////////////////////////////////////
    // NA TELA PRINCIPAL, AO CLICAR NO BOTÃO TIPO DE DESCONTO, ALTERA O TIPO E RECALC. O SUBTOTAL
    $("body").on("click", "#btn-change-discount", function(){

        let discountType = '';
        if($(this).html() === "%"){
            $(this).html("R$");
            discountType = "R$";
        }else{
            $(this).html("%");
            discountType = "%";
        }
        // Recalcula o subtotal ao trocar o tipo de desconto
        let quantity = $.tratarValor($('#item-quantity').val());
        let discount = $.tratarValor($('#item-discount').val());
        let price = $.tratarValor($('#item-price').val());
        let discountTotal = 0;
        let subtotal = Number(price * quantity);

        // Valida os limites do desconto
        if (discountType === "%") {
            // Não deixar desconto > 100%
            if (discount > 100) {
                discount = 100;
                Swal.fire({
                    icon: 'warning',
                    title: 'Desconto Inválido',
                    text: 'O desconto percentual não pode ser maior que 100%.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
                $('#item-discount').val(String(discount).replace('.', ','));
            }
        } else {
            // Não deixar desconto em R$ maior que subtotal
            if ( (discount) > subtotal) {
                discount = subtotal;
                Swal.fire({
                    icon: 'warning',
                    title: 'Desconto Inválido',
                    text: 'O desconto em R$ não pode ser maior que o subtotal.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
                if (typeof $ !== 'undefined' && $.toMoneySimples) {
                    $('#item-discount').val($.toMoneySimples(discount.toFixed(2)));
                } else {
                    $('#item-discount').val(discount.toFixed(2).replace('.', ','));
                }
            }
        }

        if(quantity > 0){
            if(discountType === "%"){
                // discountTotal = quantity * ((discount * price) / 100);
                discountTotal = (discount * subtotal / 100);
            }else{
                // discountTotal = quantity * discount;
                discountTotal = discount;
            }

            subtotal = Number((price * quantity) - discountTotal);

            $("#item-subtotal").val( $.toMoneySimples(subtotal.toFixed(2)) );
        }
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO ALTERAR A QUANTIDADE, DESCONTO OU PREÇO, RECALCULA O SUBTOTAL
    $("body").on("keyup blur", "#item-quantity, #item-discount, #item-price", function(e) {
        // Sempre trata o preço para float, removendo todos os pontos e trocando vírgula por ponto
        let rawPrice = $('#item-price').val();
        let price = 0;
        let quantity = parseInt($('#item-quantity').val()) || 1;
        let discount = $.tratarValor($('#item-discount').val());
        let discountType = $('#btn-change-discount').html();

        if (typeof rawPrice === 'string') {
            price = parseFloat(rawPrice.replace(/\./g, '').replace(',', '.')) || 0;
        } else {
            price = Number(rawPrice) || 0;
        }

        let discountTotal = 0;
        let subtotal = Number(price * quantity);

        // Valida os limites do desconto
        if (discountType === "%") {
            // Não deixar desconto > 100%
            if (discount > 100) {
                discount = 100;
                Swal.fire({
                    icon: 'warning',
                    title: 'Desconto Inválido',
                    text: 'O desconto percentual não pode ser maior que 100%.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
                $('#item-discount').val(String(discount).replace('.', ','));
            }
        } else {
            // Não deixar desconto em R$ maior que subtotal
            if ( (discount) > subtotal) {
                discount = subtotal;
                Swal.fire({
                    icon: 'warning',
                    title: 'Desconto Inválido',
                    text: 'O desconto em R$ não pode ser maior que o subtotal.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
                if (typeof $ !== 'undefined' && $.toMoneySimples) {
                    $('#item-discount').val($.toMoneySimples(discount.toFixed(2)));
                } else {
                    $('#item-discount').val(discount.toFixed(2).replace('.', ','));
                }
            }
        }

        if(quantity > 0){
            if(discountType === "%"){
                // discountTotal = quantity * ((discount * price) / 100);
                discountTotal = (discount * subtotal / 100);
            }else{
                // discountTotal = quantity * discount;
                discountTotal = discount;
            }
            subtotal = Number((price * quantity) - discountTotal);
            $("#item-subtotal").val( $.toMoneySimples(subtotal.toFixed(2)) );
        }
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // FUNÇÃO PARA VALIDAR O SUBTOTAL
    $('body').on('keyup blur', '#item-quantity, #item-discount, #item-price', function(e) {
        setTimeout(function() {validarSubtotal();}, 50);
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO PRESSIONAR ENTER NA QUANTIDADE, MOVE O FOCO PARA O PRÓXIMO CAMPO
    $("body").on("keyup", "#item-quantity", function(e) {
        if(e.keyCode === 'Enter'){
            $('#item-discount').focus();
        }
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO PRESSIONAR ENTER NO DESCONTO, MOVE O FOCO PARA O PRÓXIMO CAMPO
    $("body").on("keyup", "#item-discount", function(e) {
        if(e.keyCode === 'Enter'){
            $('#item-price').focus();
        }
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO PRESSIONAR ENTER NA QUANTIDADE, DESCONTO OU PREÇO, MOVE O FOCO PARA O PRÓXIMO CAMPO
    $("body").on("keyup", "#item-price", function(e) {
        if(e.keyCode === 'Enter'){
            $('#item-quantity').focus();
        }
    });










//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// AÇÕES DO CARRINHO /////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO CLICAR NO BOTÃO DE ADICIONAR ITEM, ADICIONA O ITEM AO CARRINHO
    $("body").on("click", "#btn-adicionar-item", async function(){
        let quantity = $.tratarValor($('#item-quantity').val());
        let rawPrice = $('#item-price').val();
        let price = 0;
        let discountType = $('#btn-change-discount').html();
        if (typeof rawPrice === 'string') {
            price = parseFloat(rawPrice.replace(/\./g, '').replace(',', '.')) || 0;
        } else {
            price = Number(rawPrice) || 0;
        }
        let discount = $.tratarValor($('#item-discount').val());
        let discountValue = 0;
        let id = $(this).data('id');
        let descricao_prod = $("#desProd").html();
        let descricao_cli = $("#desCli").html();

        if(descricao_cli.length === 0 || descricao_cli === ""){
            Swal.fire({
            icon: 'error',
                title: 'Nenhum cliente selecionado',
                text: 'Selecione ao menos um cliente.',
                confirmButtonText: 'OK',
                allowOutsideClick: false
            });
            return;
        }

        if(descricao_prod.length === 0 || descricao_prod === ""){
            Swal.fire({
                icon: 'error',
                title: 'Nenhum produto selecionado',
                text: 'Selecione ao menos um produto.',
                confirmButtonText: 'OK',
                allowOutsideClick: false
            });
            return;
        }

        if(quantity == 0){
            Swal.fire({
                icon: 'error',
                title: 'Quantidade inválida',
                text: 'A Quantidade não pode ficar zerada.',
                confirmButtonText: 'OK',
                allowOutsideClick: false
            });
            return;
        }

        if(price == 0){
            Swal.fire({
                icon: 'error',
                title: 'Preço inválido',
                text: 'O Preço não pode ficar zerado.',
                confirmButtonText: 'OK',
                allowOutsideClick: false
            });
            return;
        }

        // Gera um id único para o item
        let item_uid = 'item_' + Date.now() + '_' + Math.floor(Math.random() * 10000);
        let prodTipoId = $('#produto_tipo_id').val();

        let item = {
            id: item_uid,
            product_id: parseInt(id),
            price: price,
            name: descricao_prod,
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
        $("#desProd").html('')
        $('#getProduto').val('');
        // Limpa os campos do modal de produto
        $('#produto_dmf').val(null).trigger('change');
        $('#produto_dmf_id').val('');
        $('#produto_tipo_id').val('');

    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO CLICAR NO BOTÃO DE DELETAR ITEM, REMOVE O ITEM DO CARRINHO
    $("body").on("click", ".DeleteItem", function() {
        let item = {
            id: $(this).attr("data-id")
        };
        deleteItemFromCart(item);
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO CLICAR NO BOTÃO EM LIMPAR CARRINHO, REMOVE TODOS OS ITENS DO CARRINHO
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
                        $("#p_valorTotal").html("R$0,00");
                        $("#CartHTML").html("");
                        $("#p_subtotal").html("R$0,00");
                        $(".totalPagar").html("R$0,00");
                        $("#pedidoID").val("");
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

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO ALTERAR O TIPO DE DESCONTO NO ITEM DO CARRINHO, VALIDA E RECALCULA O SUBTOTAL
    $("body").on("click", ".btn-change-discount", function(){
        let id = $(this).data('id');
        let index = _.findIndex(cart, { id : id});
        if (index === -1) return;

        // --- alterna visual e tipo
        if($(this).html() === "%"){
            $(this).html("R$");
            cart[index].discountType = "R$";
        } else {
            $(this).html("%");
            cart[index].discountType = "%";
        }

        // pega a linha do item para ler o campo de desconto visível
        const $row = $(this).closest('tr');

        // --- PREÇO (normaliza tanto strings "1.234,56" quanto numbers)
        let rawPrice = cart[index].price;
        let price = 0;
        if (typeof rawPrice === 'string') {
            price = parseFloat(rawPrice.replace(/\./g, '').replace(',', '.')) || 0;
        } else {
            price = Number(rawPrice) || 0;
        }

        // --- QUANTIDADE (garante inteiro)
        let quantity = parseInt(cart[index].quantity) || 1;

        // --- DESCONTO: lê do input da linha quando disponível, caso contrário usa o modelo
        let rawDiscountInput = $row.find('.discountToCart').val();
        if (rawDiscountInput === undefined || rawDiscountInput === null) {
            rawDiscountInput = cart[index].discount;
        }

        let discount = 0;
        if (typeof $ !== 'undefined' && typeof $.tratarValor === 'function') {
            // usa utilitário do projeto quando disponível (retorna número já tratado)
            try { discount = Number($.tratarValor(rawDiscountInput)) || 0; } catch (err) { discount = 0; }
        } else if (typeof rawDiscountInput === 'string') {
            discount = parseFloat(rawDiscountInput.replace(/\./g, '').replace(',', '.')) || 0;
        } else {
            discount = Number(rawDiscountInput) || 0;
        }

        // grava no modelo (valor por unidade no formato atual do tipo)
        cart[index].discount = discount;

        // recalcula limites e total do desconto
        let discountTypeItem = cart[index].discountType || "%";
        let discountTotal = 0;
        let subtotal = Number(price * quantity);

        // Valida os limites do desconto
        if (discountTypeItem === "%") {
            // não permitir > 100%
            if (discount > 100) {
                discount = 100;
                Swal.fire({
                    icon: 'warning',
                    title: 'Desconto Inválido',
                    text: 'O desconto percentual não pode ser maior que 100%.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
                cart[index].discount = discount;
            }
            // discountTotal = quantity * ((discount * price) / 100);
            discountTotal = (discount * subtotal / 100);

        } else {
            // desconto em R$ por unidade não pode exceder subtotal por unidade
            if ((discount) > subtotal) {
                discount = subtotal;
                Swal.fire({
                    icon: 'warning',
                    title: 'Desconto Inválido',
                    text: 'O desconto em R$ não pode ser maior que o subtotal.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
                cart[index].discount = discount;
            }
            // discountTotal = quantity * discount;
            discountTotal = discount;
        }

        cart[index].discountValue = discountTotal;
        cart[index].subtotal = Number((price * quantity) - discountTotal).toFixed(2);

        show_cart();
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO ALTERAR A QUANTIDADE, RECALCULA O SUBTOTAL DO ITEM NO CARRINHO
    $("body").on("blur change", ".quantityToCart", function(e) {

        let item_id = $(this).attr("data-id");
        let index = _.findIndex(cart, { id: item_id });

        // Força a quantidade a ser inteiro
        let rawQuantity = String($(this).val()).replace(/\D/g, '');
        let quantity = parseInt(rawQuantity) || 1;

        $(this).val(quantity); // Atualiza o campo na tabela para inteiro
        if (quantity <= 0) {
            deleteItemFromCart({ id: item_id });
        } else {
            cart[index].quantity = quantity;
            let price = cart[index].price;
            let discount = cart[index].discount;
            let discountTypeItem = cart[index].discountType;
            let discountTotal = 0;
            let subtotal = Number(price * quantity);

            if(discountTypeItem === "%"){
                // discountTotal = quantity * ((discount * price) / 100);
                discountTotal = (discount * subtotal / 100);
            }else{
                // discountTotal = quantity * discount;
                discountTotal = discount;
            }
            cart[index].discountValue = discountTotal;
            cart[index].subtotal = Number((price * quantity) - discountTotal).toFixed(2);
        }
        show_cart();
    });

    $("body").on("keyup", ".quantityToCart", function(e) {
        if(e.keyCode == 13){
        $(this).blur();
        $(this).trigger('blur');
        }
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO ALTERAR O PREÇO, RECALCULA O SUBTOTAL DO ITEM NO CARRINHO
    $("body").on("blur change", ".priceToCart",    function(e) {

        let item_id = $(this).attr("data-id");
        let index = _.findIndex(cart, { id: item_id });

        // Trata o valor para float correto
        let rawPrice = String($(this).val());
        let price = parseFloat(rawPrice.replace(/\./g, '').replace(',', '.')) || 0;

        cart[index].price = price;
        let quantity = cart[index].quantity;
        let discount = cart[index].discount;
        let discountTypeItem = cart[index].discountType;
        let discountTotal = 0;
        let subtotal = Number(price * quantity);

        if(discountTypeItem === "%"){
            // discountTotal = quantity * ((discount * price) / 100);
            discountTotal = (discount * subtotal / 100);
        }else{
            // discountTotal = quantity * discount;
            discountTotal = discount;
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

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO ALTERAR O DISCONTO, RECALCULA O SUBTOTAL DO ITEM NO CARRINHO
    $("body").on("blur change", ".discountToCart", function(e) {

        let item_id = $(this).attr("data-id");
        let index = _.findIndex(cart, { id: item_id });

        cart[index].discount = $.tratarValor($(this).val());
        let quantity = cart[index].quantity;
        let price = cart[index].price;
        let discount = cart[index].discount;
        let discountTypeItem = cart[index].discountType;
        let discountTotal = 0;
        let subtotal = Number(price * quantity);

        // Valida os limites do desconto
        if (discountTypeItem === "%") {
            // Não deixar desconto > 100%
            if (discount > 100) {
                discount = 100;
                Swal.fire({
                    icon: 'warning',
                    title: 'Desconto Inválido',
                    text: 'O desconto percentual não pode ser maior que 100%.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
                cart[index].discount = discount;
            }
        } else {
            // Não deixar desconto em R$ maior que subtotal
            if ( discount > subtotal) {
                discount = subtotal;
                Swal.fire({
                    icon: 'warning',
                    title: 'Desconto Inválido',
                    text: 'O desconto em R$ não pode ser maior que o subtotal.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
                cart[index].discount = discount;
            }
        }

        if(discountTypeItem === "%"){
            // discountTotal = quantity * ((discount * price) / 100);
            discountTotal = (discount * subtotal / 100);
        }else{
            // discountTotal = quantity * discount;
            discountTotal = discount;
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










//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// AÇÕES NA TELA DE CHECKOUT /////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////

    //////////////////////////////////////////////////////////////////////////////////////////////
    // REMOVER A CLASSE DE ERRO AO DIGITAR NOS CAMPOS DE FORMULÁRIO
    $("body").on('input', '.form-control', function () {
        $(this).removeClass('is-invalid');
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO FECHAR O MODAL DE IMPRESSÃO, LIMPA O CAMPO DO PEDIDO
    $('#impressaoModal').on('hidden.bs.modal', function() {
        $("#pedidoID").val("");
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AÇÕES EXECUTADAS AO ABRIR O MODAL DE CHECKOUT

    // INÍCIO DO PROCESSO
    // $('#checkout-modal').on('show.bs.modal', function() {
    //     atualizarOpcoesPrimeiraParaCartao();
    //     resetarCamposPagamentoParcelado();
    //     var totalCarrinho = parseBRL($('#p_valorTotal').text());
    //     $('#valortotalacobrar').val(formatBRL(totalCarrinho));
    //     $('#valorsaldo').val(formatBRL(0));
    //     $('#valortroco').val(formatBRL(0));
    //     // Atualiza variável global CobrarValor com o total atual do carrinho
    //     $("#checkout_subtotal").text($("#p_subtotal").text());
    //     $("#checkout_desconto").text($("#p_discount").text());
    //     $("#checkout_total").text($("#p_valorTotal").text());
    //     // Se houver cashback, atualize também:
    //     if ($("#p_cashback").length && $("#checkout_cashback").length) {
    //         $("#checkout_cashback").text($("#p_cashback").text());
    //     }
    // });

    // FIM DO PROCESSO
    $('#checkout-modal').on('shown.bs.modal', function() {
        // Habilita e foca no campo de valor a cobrar
        $("#valortotalacobrar").habilitar();
        $("#valortotalacobrar").focus();
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AÇÕES EXECUTADAS AO FECHAR O MODAL DE CHECKOUT

    // INÍCIO DO PROCESSO
    // DESFOKA O ELEMENTO ATIVO
    $('#checkout-modal').on('hide.bs.modal', function () {
        if (document.activeElement && $(this).has(document.activeElement).length) {
            try { document.activeElement.blur(); } catch (e) { /* silent */ }
        }
    });

    // FIM DO PROCESSO
    // REMOVE A SELEÇÃO DE PAGAMENTO
    $('#checkout-modal').on('hidden.bs.modal', function() {
        $(".payment-box-active").removeClass("payment-box-active");
        resetarCamposPagamentoParcelado();
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO CLICAR EM FINALIZAR VENDA, ABRE O MODAL DE CHECKOUT
    $("body").on("click", "#checkout", function() {
        finalizarClick = true;

        ///////////////////////////////////
        // Validação de cliente selecionado
        let clienteSelecionado = $.isNotNullAndNotEmpty($("#cliente_cadastro_id").val()) || $.isNotNullAndNotEmpty($("#idcliente").val());
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

        ////////////////////////////////////
        // Monta a lista de cartões do cliente
        let cartoesCliente = (window.responseCartoesCliente || []).map(cartao => {
            let numeroCartao = maskCardNumberSimple(cartao.cliente_cardn);
            return {
                id: cartao.card_uuid || cartao.id,
                numero: numeroCartao,
                pontos_part: parseFloat(cartao.card_pts_part) || 0,
                pontos_fraq: parseFloat(cartao.card_pts_fraq) || 0,
                pontos_mult: parseFloat(cartao.card_pts_mult) || 0,
                pontos_cash: parseFloat(cartao.card_pts_cash) || 0,
            };
        });

        preencherTabelaResgate(cartoesCliente);

        ///////////////////////////////////////////
        // Atualiza os valores do modal de checkout
        $("#nome_cliente").text($("#desCli").text());
        $("#cliente_pts").text(formatBRL(parseFloat(window.clientPontos) || 0));
        $("#checkout_subtotal").text($("#p_subtotal").text());
        $("#checkout_desconto").text($("#p_discount").text());
        $("#checkout_total").text($("#p_valorTotal").text());
        $(".payment-box-active").removeClass("payment-box-active");
        $("#valortotalacobrar").val(($("#p_valorTotal").text()).replace(/[^\d\.,-]/g, '').trim());
        $("#valorsaldo").val(formatBRL(0));
        $("#valortroco").val(formatBRL(0));

        atualizarOpcoesPrimeiraParaCartao();
        resetarCamposPagamentoParcelado();
        atualizarParcelasCartao();
        atualizarParcelasBoleto();

        //////////////////////////////////////////
        // Monta o carrinho para enviar ao backend
        carrinho = cart.map(function(item) {
            let vlr_total         = parseBRL($("#checkout_total").text()) || 0;
            let vlr_brt_item      = (item.price * item.quantity);
            let vlr_discount_item = (item.discountValue && parseFloat(item.discountValue) > 0) ? parseFloat(item.discountValue) : 0;
            let vlr_liqu_item     = vlr_brt_item - vlr_discount_item;

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

        ///////////////////////////////////////////
        // Desfoca qualquer campo que esteja focado
        if (document.activeElement && document.activeElement !== document.body) {
            try { document.activeElement.blur(); } catch (e) { /* silent */ }
        }

        ////////////////////////////
        // Exibe o modal de checkout
        $("#checkout-modal").modal("show");

    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO DIGITAR NO CAMPO DE VALOR A COBRAR NO MODAL DE CHECKOUT
    $('#valortotalacobrar').on('input', function(e) {
        let v = $(this).val().replace(/\D/g, '');
        if (v.length === 0) v = '0';
        let valor = (parseInt(v, 10) / 100).toFixed(2);
        let totalCarrinho = parseBRL($('#checkout_total').text());
        let tipoPagamento = $('.payment-box-active').data('identificacao');
        let valorCobrar = parseFloat(valor.replace(',', '.')) || 0;

        if (!tipoPagamento) {
            Swal.fire({
                icon: 'warning',
                title: 'Meio de pagamento não selecionado',
                text: 'Selecione um meio de pagamento antes de continuar.',
                confirmButtonText: 'OK',
                allowOutsideClick: false
            });
        }

        // Bloqueia valorCobrar maior que totalCarrinho para tipos diferentes de Dinheiro
        if (tipoPagamento !== 'DN' && valorCobrar > totalCarrinho) {
            valorCobrar = totalCarrinho;
            valor = totalCarrinho.toFixed(2).replace('.', ',');
        }

        $(this).val(valor);
        let saldo = totalCarrinho - valorCobrar;
        if (valorCobrar > totalCarrinho) {
            saldo = 0;
        }

        $('#valorsaldo').val(formatBRL(saldo));
        let troco = 0;
        if (tipoPagamento === 'DN' && valorCobrar > totalCarrinho) {
            troco = valorCobrar - totalCarrinho;
        }

        $('#valortroco').val(formatBRL(troco));
        atualizarParcelasCartao();
        atualizarParcelasBoleto();

    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO ALTERAR OU SAIR DO CAMPO DE VALOR A COBRAR NO MODAL DE CHECKOUT
    $('#valortotalacobrar').on('change blur', function() {
        atualizarParcelasCartao();
        atualizarParcelasBoleto();
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO CLICAR EM UMA OPÇÃO DE PAGAMENTO NO MODAL DE CHECKOUT
    $('body').on('click', '.payment-box', function() {

        $('#PrimeiraParaCartao').val("").trigger('change');
        $('#PrimeiraParaBoleto').val("").trigger('change');
        $('#dataPrimeiraParcelaCartao').val("");
        $('#dataPrimeiraParcelaBoleto').val("");
        $('#parcelasCartao').val("").trigger('change');
        $('#parcelasBoleto').val("").trigger('change');
        $('.payment-box-active').removeClass('payment-box-active');

        $(this).addClass("payment-box-active");

        let checkout_subtotal = parseBRL($('#checkout_subtotal').text());
        let checkout_cashback = parseBRL($('#checkout_cashback').text());
        let checkout_desconto = parseBRL($('#checkout_desconto').text());
        let checkout_pago = parseBRL($('#checkout_pago').text());
        let checkout_total = checkout_subtotal - checkout_cashback - checkout_desconto - checkout_pago;
        let tipoPagamento = $(this).data('identificacao');
        $('#id_forma_pagto').val(tipoPagamento).trigger('change');

        if (tipoPagamento === "BL") {
            $("#div-boleto").show();
            $("#div-cartao").hide();
            $("#payment-instructions").show();
            // atualizarParcelasBoleto();

        } else if (tipoPagamento === "CM") {
            $("#div-cartao").show();
            $("#div-boleto").hide();
            $("#payment-instructions").show();
            // atualizarParcelasCartao();
        } else {
            $("#div-boleto, #div-cartao").hide();
            $("#payment-instructions").hide();
        }

        // if (tipoPagamento !== 'DN' && valorCobrar > checkout_total) {
        if (tipoPagamento !== 'DN') {
            atualizarCampoValortotalacobrar(checkout_total);
        }

        // Habilita e foca no campo de valor a cobrar
        $("#valortotalacobrar").habilitar();
        $("#valortotalacobrar").focus();

    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO ALTERAR O CAMPO DE PRIMEIRA PARCELA DO CARTAO, CALCULA A DATA DA PRIMEIRA PARCELA
    $('body').on('change', '#PrimeiraParaCartao', calcularDataPrimeiraParcelaCartao);

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO ALTERAR O CAMPO DE PRIMEIRA PARCELA DO BOLETO, CALCULA A DATA DA PRIMEIRA PARCELA
    $('body').on('change', '#PrimeiraParaBoleto', calcularDataPrimeiraParcelaBoleto);

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO CLICAR NO COMBOBOX DE PARCELAS DO CARTÃO, RECALCULA AS PARCELAS
    $("body").on("mousedown", "#parcelasCartao", atualizarParcelasCartao);

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO CLICAR NO COMBOBOX DE PARCELAS DO BOLETO, RECALCULA AS PARCELAS
    $("body").on("mousedown", "#parcelasBoleto", atualizarParcelasBoleto);

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO ALTERAR A OPÇÃO DE VENDA SEM JUROS NO CARTÃO, RECALCULA AS PARCELAS
    $("body").on('change', '#vendaSemJurosCartao', atualizarParcelasCartao);

    //////////////////////////////////////////////////////////////////////////////////////////////
    // EXIBE O MODAL DE RESGATE DE PONTOS
    $('#btn_resgatar_pts').on('click', function() {
        $('#modalResgatarPontos').modal('show');;
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO FECHAR O MODAL DE RESGATE DE PONTOS
    $('#modalResgatarPontos').on('hide.bs.modal', function () {
        // Desfoca qualquer campo que esteja focado
        if (document.activeElement && document.activeElement !== document.body) {
            try { document.activeElement.blur(); } catch (e) { /* silent */ }
        }
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO CLICAR NA OPÇÃO DE "UTILIZAR TUDO" NO RESGATE DE PONTOS
    $("body").on('change', '.utilizar-tudo', function() {
        let tr = $(this).closest('tr');
        let max = parseBRL(tr.find('.pontos-disponiveis').text());
        let input = tr.find('.pontos-utilizar');
        let valorAnterior = parseBRL(input.val()) || 0;

        // Armazena a ação atual
        ultimaAcaoResgate = {
            tipo: 'checkbox',
            elemento: this,
            valorAnterior: valorAnterior,
            checked: !$(this).is(':checked') // valor anterior do checkbox
        };

        if ($(this).is(':checked')) {
            input.val(formatBRL(max));
        } else {
            input.val('0,00');
        }

        atualizarTotalPontosSelecionados();
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO ALTERAR O VALOR DE PONTOS A UTILIZAR NO RESGATE DE PONTOS
    $("body").on('input', '.pontos-utilizar', function() {
        let tr = $(this).closest('tr');
        let max = parseFloat(tr.find('.pontos-disponiveis').text().replace(/\./g, '').replace(',', '.')) || 0;
        let valorAnterior = parseBRL($(this).data('valor-anterior')) || 0;

        // Armazena a ação atual para desfazer se necessário
        if (!ultimaAcaoResgate || ultimaAcaoResgate.elemento !== this) {
            ultimaAcaoResgate = {
                tipo: 'input',
                elemento: this,
                valorAnterior: valorAnterior
            };
        }

        // Aplicar formatação de calculadora de dinheiro
        let v = $(this).val().replace(/\D/g, '');
        if (v.length === 0) v = '0';
        while (v.length < 3) v = '0' + v;
        let intPart = v.slice(0, -2);
        let decPart = v.slice(-2);
        let formatted = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ',' + decPart;
        // Remove zeros à esquerda
        formatted = formatted.replace(/^0+(?=\d)/, '');

        // Validação: não pode ser maior que o máximo
        let val = parseFloat(formatted.replace(/\./g, '').replace(',', '.')) || 0;
        if (val > max) {
            val = max;
            formatted = val.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        $(this).val(formatted);

        // Atualiza checkbox conforme o valor
        tr.find('.utilizar-tudo').prop('checked', val === max);

        // Atualiza o total selecionado
        atualizarTotalPontosSelecionados();
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO SAIR DO CAMPO DE PONTOS A UTILIZAR NO RESGATE DE PONTOS
    $("body").on('keyup blur', '.pontos-utilizar', function() {
        let tr = $(this).closest('tr');
        let max = parseFloat(tr.find('.pontos-disponiveis').text().replace(/\./g, '').replace(',', '.')) || 0;
        let val = parseFloat($(this).val().replace(/\./g, '').replace(',', '.')) || 0;
        if (val > max) {
            $(this).val(max.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            val = max;
        }
        tr.find('.utilizar-tudo').prop('checked', val === max);
        atualizarTotalPontosSelecionados();
    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO CONFIRMAR O RESGATE DE PONTOS
    $('#confirmarResgatePontos').on('click', function() {
        // Pega o valor total selecionado (já formatado)
        let totalResgatado = parseBRL($('#totalPontosSelecionados').text());

        // Atualiza o campo de cashback e o campo de resgatado no modal checkout
        $('#checkout_cashback').text('R$ ' + formatBRL(totalResgatado));

        // Recalcula o total do checkout
        let totalCobrar = parseBRL($("#checkout_subtotal").text());
        let checkout_cashback = parseBRL($("#checkout_cashback").text());
        let totalDesconto = parseBRL($("#checkout_desconto").text());
        let totalPago = parseBRL($("#checkout_pago").text());

        $("#checkout_total").text(formatBRL(totalCobrar - checkout_cashback - totalDesconto - totalPago));
        $("#valortotalacobrar").val(formatBRL(totalCobrar - checkout_cashback - totalDesconto - totalPago));

        // Recalcula o total a pagar
        atualizarParcelasCartao();
        atualizarParcelasBoleto();

        ///////////////////////////////////////////
        // Desfoca qualquer campo que esteja focado
        if (document.activeElement && document.activeElement !== document.body) {
            try { document.activeElement.blur(); } catch (e) { /* silent */ }
        }

        // Fecha o modal de resgate
        $('#modalResgatarPontos').modal('hide');

    });

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO ALTERAR O NÚMERO DE PARCELAS DO CARTÃO, RECALCULA O TOTAL COM JUROS
    $('#parcelasCartao').on('change', function () {

        let valorTotal = parseFloat($('#valortotalacobrar').val().replace(/[^\d,]/g, '').replace(',', '.')) || 0;
        let totalVendaComJuros = parseFloat($('#parcelasCartao option:selected').data('total_venda_com_juros')) || valorTotal;
        let juros = parseFloat($('#parcelasCartao option:selected').data('total_juros')) || 0;

        let checkout_subtotal = parseFloat($("#checkout_subtotal").text().replace(/[^\d,]/g, '').replace(',', '.')) || 0;
        let checkout_cashback = parseFloat($("#checkout_cashback").text().replace(/[^\d,]/g, '').replace(',', '.')) || 0;
        let checkout_desconto = parseFloat($("#checkout_desconto").text().replace(/[^\d,]/g, '').replace(',', '.')) || 0;
        let checkout_pago = parseFloat($("#checkout_pago").text().replace(/[^\d,]/g, '').replace(',', '.')) || 0;

        let checkout_total_check = checkout_subtotal - checkout_cashback - checkout_desconto - checkout_pago;

        // Só calcula se o checkbox NÃO estiver marcado
        if (!$('#vendaSemJurosCartao').is(':checked')) {

            // Verifica se o juros é maior que zero
            if (juros > 0) {
                if (valorTotal < checkout_total_check) {
                    $('#checkout_total').text('R$ ' + (checkout_total_check + juros).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#checkout_juros').text('R$ ' + juros.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                } else {
                    $('#checkout_total').text('R$ ' + totalVendaComJuros.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#checkout_juros').text('R$ ' + juros.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                }

            } else {
                $('#checkout_total').text('R$ ' + checkout_total_check.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#checkout_juros').text(' R$ 0,00');
            }

        } else {
            $('#checkout_total').text('R$ ' + checkout_total_check.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#checkout_juros').text(' R$ 0,00');
        }

    });










//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
// REGRAS PARA O BOTÃO COBRAR ////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO CLICAR NO BOTÃO DE COBRAR NO MODAL DE CHECKOUT
    $("#btnCobrar").on("click", function() {

        //////////////////////////////////////////////////////////////////////////////////////////////
        // SE NENHUMA FORMA DE PAGAMENTO ESTIVER SELECIONADA, EXIBE ALERTA E PARA A EXECUÇÃO
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
        let cliente_id           = $("#cliente_cadastro_id").val() || $("#idcliente").val();          // ID do cliente selecionado
        let tipoPagto            = $("#id_forma_pagto").val();                                        // Tipo de pagamento selecionado
        let totalVendaComJuros   = 0;
        let valorParcela         = 0;
        let valorParcelaComJuros = 0;
        let jurosTotal           = 0;
        let jurosTotalParcela    = 0;
        let parcelas             = 1;

        // Tela de resumo - Lado Esquerdo
        let checkout_subtotal    = parseBRL($("#checkout_subtotal").text()) || 0;                     // Valor total do carrinho antes de descontos
        let checkout_cashback    = parseBRL($("#checkout_cashback").text()) || 0;                     // Valor total de cashback aplicado
        let checkout_desconto    = parseBRL($("#checkout_desconto").text()) || 0;                     // Valor total de desconto aplicado
        let checkout_pago        = parseBRL($("#checkout_pago").text()) || 0;                         // Valor total pago
        let checkout_descontado  = parseBRL($("#checkout_descontado").text()) || 0;                   // Valor total descontado
        let checkout_resgatado   = parseInt($("#checkout_resgatado").text().replace(/\D/g, '')) || 0; // Pontos de cashback resgatados

        // Tela de resumo - Lado Direito
        let prevJuros = Array.isArray(arr_checkout_juros) && arr_checkout_juros.length
            ? arr_checkout_juros.reduce(function(a, b){ return Number(a) + Number(b); }, 0)
            : 0;
        let currentJuros         = parseBRL($("#checkout_juros").text()) || 0;
        let checkout_total       = parseBRL($("#checkout_total").text()) || 0;                        // Valor total a cobrar (subtotal - desconto - cashback)
        let checkout_juros_total = prevJuros + currentJuros;                                          // Valor total dos juros aplicados na venda

        // Tela dos valores da cobrança - abaixo das opções de pagamento
        let valortotalacobrar    = parseBRL($("#valortotalacobrar").val()) || 0;                      // Valor que o usuário quer cobrar agora
        let checkout_troco       = parseBRL($("#valortroco").val()) || 0;                             // Valor do troco a ser dado

        // Demais parâmetros
        let check_semjuros       = $('#vendaSemJurosCartao').is(':checked') ? "X" : null;             // Indica se a venda é sem juros (X) ou não (null)
        let gerar_comp_sep       = $('#gerarComprovanteSeparados').is(':checked') ? "X" : null;       // Indica se deve gerar comprovantes separados (X) ou não (null)
        let check_reembolso      = $('#blt_ctr').is(':checked') ? "X" : null;                         // Indica se é reembolso (X) ou não (null)

        let dt_primeira_parc  = null;                                                                 // Armazena a data da primeira parcela selecionada
        let dt_primeira_parcC = $('#dataPrimeiraParcelaCartao').val();
        let dt_primeira_parcB = $('#dataPrimeiraParcelaBoleto').val();
        if (dt_primeira_parcC) {
            dt_primeira_parc = dt_primeira_parcC;
        } else if (dt_primeira_parcB) {
            dt_primeira_parc = dt_primeira_parcB;
        }

        let regra_parc           = null;                                                              // Armazena a regra de parcelamento
        let tax_categ            = null;                                                              // Armazena a categoria de parcelamento
        let $opt_primeira_cartao = $();
        let $opt_primeira_boleto = $();

        if ($('#PrimeiraParaCartao').val()) {
            $opt_primeira_cartao = $('#PrimeiraParaCartao option:selected');
            regra_parc = $opt_primeira_cartao.data('regra') || $opt_primeira_cartao.attr('data-regra') || null;
            tax_categ  = $opt_primeira_cartao.data('taxCateg') || $opt_primeira_cartao.attr('data-tax-categ') || null;
        }

        if ($('#PrimeiraParaBoleto').val()) {
            $opt_primeira_boleto = $('#PrimeiraParaBoleto option:selected');
            // boleto só define regra/tax se não houver vindo do cartão
            regra_parc = regra_parc || $opt_primeira_boleto.data('regra') || $opt_primeira_boleto.attr('data-regra') || null;
            tax_categ  = tax_categ  || $opt_primeira_boleto.data('taxCateg') || $opt_primeira_boleto.attr('data-tax-categ') || null;
        }

        // Cálculo dos Juros e Parcelas
        if ($('#parcelasCartao').val()) {
            $opt_parcelas_cartao = $('#parcelasCartao option:selected');
            totalVendaComJuros   = parseFloat(($opt_parcelas_cartao).data('total_venda_com_juros')) || 0;
            valorParcela         = parseFloat(($opt_parcelas_cartao).data('valor_parcela')) || 0;
            valorParcelaComJuros = parseFloat(($opt_parcelas_cartao).data('valor_parcela_com_juros')) || 0;
            parcelas             = parseInt(($opt_parcelas_cartao).data('parcelas'), 10) || 1;
            jurosTotal           = ((totalVendaComJuros - valortotalacobrar)).toFixed(2);
            jurosTotalParcela    = ((totalVendaComJuros - valortotalacobrar) / parseInt(parcelas)).toFixed(2);
        }

        if ($('#parcelasBoleto').val()) {
            $opt_parcelas_boleto = $('#parcelasBoleto option:selected');
            totalVendaComJuros   = parseFloat(($opt_parcelas_boleto).data('total_venda_com_juros')) || 0;
            valorParcela         = parseFloat(($opt_parcelas_boleto).data('valor_parcela')) || 0;
            valorParcelaComJuros = parseFloat(($opt_parcelas_boleto).data('valor_parcela_com_juros')) || 0;
            parcelas             = parseInt(($opt_parcelas_boleto).data('parcelas'), 10) || 1;
        }

        // Proporção do valor cobrado em relação ao total
        let proporcao_cobrado = 1;
        if (valortotalacobrar < (checkout_subtotal - checkout_desconto - checkout_cashback) && (checkout_subtotal - checkout_desconto - checkout_cashback) > 0) {
            proporcao_cobrado = valortotalacobrar / (checkout_subtotal - checkout_desconto - checkout_cashback);
        }

        // Proporcionaliza desconto e cashback
        let checkout_desconto_proporcional = checkout_desconto * proporcao_cobrado;
        let checkout_cashback_proporcional = checkout_cashback * proporcao_cobrado;

        // ARMAZENA DADOS GLOBAIS PARA USO NA FUNÇÃO DE COBRANÇA
        window.cobrancaDados = {
            token: $('meta[name="csrf-token"]').attr("content"),
            cliente_id:                     cliente_id,
            tipoPagto:                      tipoPagto,
            checkout_subtotal:              checkout_subtotal,
            checkout_cashback:              checkout_cashback,
            checkout_desconto:              checkout_desconto,
            checkout_pago:                  checkout_pago,
            checkout_descontado:            checkout_descontado,
            checkout_troco:                 checkout_troco,
            checkout_resgatado:             checkout_resgatado,
            checkout_total:                 checkout_total,
            valortotalacobrar:              valortotalacobrar,
            check_semjuros:                 check_semjuros,
            check_reembolso:                check_reembolso,
            tax_categ:                      tax_categ,
            regra_parc:                     regra_parc,
            valorTotalComJuros:             totalVendaComJuros,
            valorParcelaComJuros:           valorParcelaComJuros,
            valorParcelaSemJuros:           valorParcela,
            jurosTotal:                     jurosTotal,
            jurosTotalParcela:              jurosTotalParcela,
            parcelas:                       parcelas,
            dt_primeira_parc:               dt_primeira_parc,
            proporcao_cobrado:              proporcao_cobrado,
            checkout_desconto_proporcional: checkout_desconto_proporcional,
            checkout_cashback_proporcional: checkout_cashback_proporcional,
            carrinho:                       carrinho,
            vlr_dec_mn:                     null,
            vlr_dec_mn_item:                null,
            vlr_atr_m:                      null,
            vlr_atr_j:                      null,
            isent_mj:                       null,
            protestado:                     null,
            negociacao:                     null,
            vlr_acr_mn:                     null,
            vlr_acr_mn_item:                null,
            vlr_cst_cob:                    null,
            negociacao_obs:                 null,
            negociacao_file:                null,
            check_ant:                      null,
            perct_ant:                      null,
            ant_desc:                       null,
            pgt_vlr:                        null,
            pgt_desc:                       null,
            pgt_mtjr:                       null,
            vlr_rec:                        null,
            pts_disp_part:                  null,
            pts_disp_fraq:                  null,
            pts_disp_mult:                  null,
            pts_disp_cash:                  null,
            card_tp:                        null,
            card_mod:                       null,
            card_categ:                     null,
            card_desc:                      null,
            card_uuid:                      null,
            cliente_cardn:                  null,
            cliente_cardcv:                 null,
            card_saldo_vlr:                 null,
            card_limite:                    null,
            card_saldo_pts:                 null,
            card_sts:                       null
        };

        ///////////////////////////
        // MEIO DE PAGAMENTO CARTÃO
        if (tipoPagto === "CM") {

            console.log(parcelas);
            console.log(totalVendaComJuros);
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
            if (!dt_primeira_parc || dt_primeira_parc === "") {
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
            let cartoes = window.responseCartoesCliente || [];
            if (!Array.isArray(cartoes) || cartoes.length === 0) {
                Swal.fire("Erro", "Nenhum cartão Mult disponível para este cliente.", "error");
                return;
            }
            let tbody = "";
            cartoes.forEach(function(cartao) {
                let statusHtml = formatarStatusCartao(cartao.card_sts);

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
            let primeiraParcela = $("#PrimeiraParaBoleto").val();

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
            if (!dt_primeira_parc || dt_primeira_parc === "") {
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

    //////////////////////////////////////////////////////////////////////////////////////////////
    // AO CLICAR EM UMA LINHA DE CARTÃO MULT NO MODAL DE SELEÇÃO DE CARTÃO
    $("body").on("click", ".linha-cartao-mult", function() {

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

        let valorCobrar = parseFloat($("#valortotalacobrar").val().replace(/\./g, '').replace(',', '.')) || 0;

        if (window.cobrancaDados.card_saldo_vlr < valorCobrar) {
            $("#modalCartaoMult").modal("hide");

            $('#modalCartaoMult').one('hidden.bs.modal', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Saldo insuficiente',
                    text: 'O saldo do cartão é insuficiente para esta cobrança.',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                }).then(() => {
                    // Aguarda a animação do Swal desaparecer por completo
                    setTimeout(() => {
                        $("#modalCartaoMult").modal("show");
                    }, 300); // <- 300ms é o tempo exato do fade do SweetAlert
                });
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













});


























// var calcDiscount = function(id, index) {
//     var quantity = $.tratarValor($('#item-quantity-'+id).val());
//     var discount = $.tratarValor($('#item-discount-'+id).val());
//     var discountTotal = 0;
//     if(quantity > 0){
//         var price = $.tratarValor($("#item-price-"+id).val());
//         if(discountType === "%"){
//             discountTotal = quantity * ((discount * price) / 100);
//         }else{
//             discountTotal = quantity * discount;
//         }
//     }

//     return discountTotal
// }

// ///////////////////////////////////////////////////////////////////////
// // FUNÇOES EXECUTADAS SOMENTE ANTES QUE TODA A PÁGINA ESTIVER CARREGADA
// ///////////////////////////////////////////////////////////////////////

// //////////
// // FUNÇÕES
// //////////





// /////////////////////
// // AÇÕES DE NAVEGAÇÂO
// /////////////////////



// ////////////////////////////////////////////////////////////////////////
// // FUNÇOES EXECUTADAS SOMENTE DEPOIS QUE TODO A PÁGINA ESTIVER CARREGADA
// ////////////////////////////////////////////////////////////////////////
// $(document).ready(function () {









//     //////////////////////////////////////////////////////////////////
//     // REGRA PARA ANTES DE FECHAR O MODAL E AINDA TIVER SALDO A COBRAR
//     //////////////////////////////////////////////////////////////////
//     $('#checkout-modal').on('hide.bs.modal', function(e) {

//         var cobrar = parseBRL($("#checkout_total").text());
//         // Só bloqueia se houver saldo maior que 0,01 e não for confirmação do usuário
//         if (cobrar > 0.01 && !podeFecharCheckoutModal) {
//             e.preventDefault(); // Impede o fechamento imediato
//             Swal.fire({
//                 icon: 'warning',
//                 title: 'Ainda existe valor à cobrar!',
//                 text: 'Deseja realmente descartar o valor de ' + $("#checkout_total").text() + '?',
//                 showCancelButton: true,
//                 showDenyButton: true,
//                 confirmButtonText: 'Sim, descartar',
//                 denyButtonText: 'Cancelar tudo',
//                 cancelButtonText: 'Não, continuar cobrança'

//             }).then(function(result) {

//                 ////////////////////////////////////////////////////
//                 // Se o usuário confirmou que quer descartar o valor
//                 if (result.isConfirmed) {
//                     // Limpa os campos e permite o fechamento do modal
//                     $("#totalItens").html("0");
//                     $("#TableNo").text("");
//                     $("#TableNoCart").text("");
//                     $("#p_valorTotal").html("R$ 0,00");
//                     $("#CartHTML").html("");
//                     $("#p_subtotal").html("R$ 0,00");
//                     $("#p_discount").html("R$ 0,00");
//                     $(".totalPagar").html("R$ 0,00");
//                     $("#pedidoID").val("");
//                     // Produto
////                     $('#find-product').val(null).trigger('change');
////                     $('#find-product').select2('data', null);
//                     $('#getProduto').val('');
//                     $('#desProd').html('');
//                     $('#item-quantity').val('1');
//                     $('#item-discount').val('0,00');
//                     $('#item-price').val('0,00');
//                     $('#item-subtotal').val('0,00');
//                     $('#produto_dmf').val(null).trigger('change');
//                     $('#produto_dmf_id').val('');
//                     $('#produto_tipo_id').val('');
//                     // Pagamento
//                     $('#valortotalacobrar').val('0,00');
//                     $('#valorsaldo').val('0,00');
//                     $('#valortroco').val('0,00');
//                     $('#checkout_subtotal').text('R$ 0,00');
//                     $('#checkout_desconto').text('R$ 0,00');
//                     $('#checkout_pago').text('R$ 0,00');
//                     $('#checkout_descontado').text('R$ 0,00');
//                     $('#checkout_resgatado').text('0');
//                     $('#checkout_total').text('R$ 0,00');
//                     if ($("#checkout_cashback").length) {
//                         $("#checkout_cashback").text('R$ 0,00');
//                     }
//                     $('.payment-box-active').removeClass('payment-box-active');
//                     $('#id_forma_pagto').val('').trigger('change');
//                     // Feche o modal
//                     podeFecharCheckoutModal = true;
//                     $('#checkout-modal').modal('hide');
//                     podeFecharCheckoutModal = false;

//                     if (tituloAtual && nsu_tituloAtual) {

//                         // Calcula saldo remanescente
//                         var totaljuros = arr_checkout_juros.reduce((a, b) => Number(a) + Number(b), 0);
//                         var totalCobrado = arr_checkout_total.reduce((a, b) => Number(a) + Number(b), 0);
//                         var totalTroco = arr_checkout_troco.reduce((a, b) => Number(a) + Number(b), 0);
//                         var totalDesconto = arr_checkout_desconto.reduce((a, b) => Number(a) + Number(b), 0);

//                         ///////////////////////////////////////////////
//                         // MONTA OS PARÂMETROS PARA GERAR O COMPROVANTE
//                         var tipo_pagamento = '';
//                         if (cobrancaDados.tipoPagto === 'CM') {
//                             if (cobrancaDados.parcelas > 1) {
//                                 tipo_pagamento = 'Parcelado';
//                             } else  {
//                                 tipo_pagamento = 'À Vista';
//                             }
//                         } else if (cobrancaDados.tipoPagto === 'BL') {
//                             if (cobrancaDados.parcelas > 1) {
//                                 tipo_pagamento = 'Parcelado';
//                             } else  {
//                                 tipo_pagamento = 'À Vista';
//                             }
//                         } else if (cobrancaDados.tipoPagto === 'DN') {
//                             tipo_pagamento = 'À Vista';
//                         } else if (cobrancaDados.tipoPagto === 'PX') {
//                             tipo_pagamento = 'À Vista';
//                         } else if (cobrancaDados.tipoPagto === 'OT') {
//                             tipo_pagamento = 'À Vista';
//                         }

//                         $.get('/api/mensagens-comp', {
//                             canal_id: 4,
//                             categorias: ['CBCPR','MULTB','RPCPR', 'AUTHO']

//                         }, function(mensagensComp) {

//                             var params = {
//                                 empresa: {
//                                     nome: empresa.emp_nfant,
//                                     cnpj: empresa.emp_cnpj,
//                                     im: empresa.emp_im,
//                                     ie: empresa.emp_ie,
//                                     emp_id: empresa.emp_id
//                                 },

//                                 cliente: {
//                                     nome: window.clientName,
//                                     doc: window.clientDoc,
//                                     pontos: window.clientPontos
//                                 },

//                                 comprovante: {
//                                     titulo: tituloAtual,
//                                     nsu_titulo: nsu_tituloAtual,
//                                     nsu_autoriz: arr_nsu_autoriz,
//                                     data_hora: (new Date()).toLocaleString('pt-BR'),
//                                     cartao_numero: (cobrancaDados.cliente_cardn || ''),

//                                     tipo_pagamento: tipo_pagamento,
//                                     parcelas: cobrancaDados.parcelas,
//                                     pontos_concedidos: 0,

//                                     meio_pagamentos: arr_meiosPagtoUtilizados.join(', '),
//                                     jurosTotal: totaljuros,
//                                     checkout_subtotal: totalCobrado,
//                                     checkout_troco: totalTroco,
//                                     checkout_desconto: totalDesconto,
//                                     checkout_cashback: cobrancaDados.checkout_cashback,
//                                     checkout_total: totalCobrado + totaljuros
//                                 },

//                                 mensagens: {
//                                     cabecalho: mensagensComp.CBCPR,
//                                     multban: mensagensComp.MULTB,
//                                     rodape: mensagensComp.RPCPR
//                                 },

//                                 autorizacao: mensagensComp.AUTHO
//                             };

//                             var htmlComprovante = gerarComprovanteVenda(params);

//                             Swal.fire({
//                                 html: htmlComprovante,
//                                 showConfirmButton: false,
//                                 width: 500,
//                                 customClass: { popup: 'swal2-comprovante-popup' },
//                                 allowOutsideClick: false,
//                                 footer: `
//                                     <div style="display:flex;justify-content:space-between;gap:8px;">
//                                         <button class="btn btn-primary btn-sm" id="btnEnviarEmailComprovante">Enviar por Email</button>
//                                         <button class="btn btn-success btn-sm" id="btnEnviarWhatsAppComprovante">Enviar por WhatsApp</button>
//                                         <button class="btn btn-secundary-multban btn-sm" id="btnFecharComprovante">Fechar</button>
//                                         <button class="btn btn-primary btn-sm" id="btnImprimirComprovante">Imprimir</button>
//                                     </div>
//                                 `
//                             });

//                             // Fechar o modal ao clicar em fechar
//                             $(document).on('click', '#btnFecharComprovante', function() {

//                                 // Resetar arrays de controle
//                                 cart = [];
//                                 carrinho = [];
//                                 arr_nsu_autoriz = [];
//                                 arr_meiosPagtoUtilizados = [];
//                                 arr_checkout_desconto = [];
//                                 arr_checkout_cashback = [];
//                                 arr_checkout_total = [];
//                                 arr_checkout_juros = [];
//                                 arr_checkout_troco = [];
//                                 cartaoSelecionado = null;
//                                 // window.responseCartoesCliente = [];
//                                 window.cobrancaDados = [];
//                                 tituloAtual = null;
//                                 nsu_tituloAtual = null;
//                                 show_cart();
//                                 Swal.close();

//                             });

//                             // Imprimir ao clicar em imprimir
//                             $(document).on('click', '#btnImprimirComprovante', function() {
//                                 var comprovanteHtml = $('#comprovante-venda').prop('outerHTML');
//                                 var printWindow = window.open('', '', 'width=800,height=600');
//                                 printWindow.document.write(`
//                                     <html>
//                                     <head>
//                                         <title>Imprimir Comprovante</title>
//                                         <style>
//                                             body { background: #fdf6e3; font-family: monospace; }
//                                             #comprovante-venda { margin: 0 auto; }
//                                         </style>
//                                     </head>
//                                     <body>${comprovanteHtml}</body>
//                                     </html>
//                                 `);

//                                 printWindow.document.close();
//                                 printWindow.focus();
//                                 printWindow.onload = function() {
//                                     printWindow.print();
//                                 };

//                             });

//                             // Envio por email - IMPLEMENTAR
//                             $(document).on('click', '#btnEnviarEmailComprovante', function() {
//                                 Swal.fire('Atenção', 'Funcionalidade de envio por email ainda não implementada.', 'info');
//                             });

//                             // Envio por whatsapp - IMPLEMENTAR
//                             $(document).on('click', '#btnEnviarWhatsAppComprovante', function() {
//                                 Swal.fire('Atenção', 'Funcionalidade de envio por WhatsApp ainda não implementada.', 'info');
//                             });

//                             // Resetar arrays de controle
//                             cart = [];
//                             carrinho = [];
//                             arr_nsu_autoriz = [];
//                             arr_meiosPagtoUtilizados = [];
//                             arr_checkout_desconto = [];
//                             arr_checkout_cashback = [];
//                             arr_checkout_total = [];
//                             arr_checkout_juros = [];
//                             arr_checkout_troco = [];
//                             cartaoSelecionado = null;
//                             //window.responseCartoesCliente = [];
//                             window.cobrancaDados = [];
//                             show_cart();

//                         });

//                     }

//                 ////////////////////////////////////////////////
//                 // Se o usuário confirmou que quer cancelar tudo
//                 } else if (result.isDenied) {

//                     var token = $('meta[name="csrf-token"]').attr('content');

//                     $.post('/pdv-web/cancelar-venda', {
//                         _token: token,
//                         titulo: tituloAtual

//                     }, function(resp) {
//                         if (resp.success) {
//                             // Limpa os campos e permite o fechamento do modal
//                             $("#totalItens").html("0");
//                             $("#TableNo").text("");
//                             $("#TableNoCart").text("");
//                             $("#p_valorTotal").html("R$ 0,00");
//                             $("#CartHTML").html("");
//                             $("#p_subtotal").html("R$ 0,00");
//                             $("#p_discount").html("R$ 0,00");
//                             $(".totalPagar").html("R$ 0,00");
//                             $("#pedidoID").val("");
//                             // Produto
////                             $('#find-product').val(null).trigger('change');
////                             $('#find-product').select2('data', null);
//                             $('#getProduto').val('');
//                             $('#desProd').html('');
//                             $('#item-quantity').val('1');
//                             $('#item-discount').val('0,00');
//                             $('#item-price').val('0,00');
//                             $('#item-subtotal').val('0,00');
//                             $('#produto_dmf').val(null).trigger('change');
//                             $('#produto_dmf_id').val('');
//                             $('#produto_tipo_id').val('');
//                             // Pagamento
//                             $('#valortotalacobrar').val('0,00');
//                             $('#valorsaldo').val('0,00');
//                             $('#valortroco').val('0,00');
//                             $('#checkout_subtotal').text('R$ 0,00');
//                             $('#checkout_desconto').text('R$ 0,00');
//                             $('#checkout_pago').text('R$ 0,00');
//                             $('#checkout_descontado').text('R$ 0,00');
//                             $('#checkout_resgatado').text('0');
//                             $('#checkout_total').text('R$ 0,00');
//                             if ($("#checkout_cashback").length) {
//                                 $("#checkout_cashback").text('R$ 0,00');
//                             }
//                             $('.payment-box-active').removeClass('payment-box-active');
//                             $('#id_forma_pagto').val('').trigger('change');
//                             // Feche o modal
//                             podeFecharCheckoutModal = true;
//                             $('#checkout-modal').modal('hide');
//                             podeFecharCheckoutModal = false;

//                             // Resetar arrays de controle
//                             cart = [];
//                             carrinho = [];
//                             arr_nsu_autoriz = [];
//                             arr_meiosPagtoUtilizados = [];
//                             arr_checkout_desconto = [];
//                             arr_checkout_cashback = [];
//                             arr_checkout_total = [];
//                             arr_checkout_juros = [];
//                             arr_checkout_troco = [];
//                             cartaoSelecionado = null;
//                             //window.responseCartoesCliente = [];
//                             window.cobrancaDados = [];
//                             tituloAtual = null;
//                             nsu_tituloAtual = null;
//                             Swal.close();
//                             show_cart();

//                         } else {
//                             Swal.fire('Erro', resp.error || 'Não foi possível cancelar a venda.', 'error');
//                         }
//                     });

//                 }
//                 /////////////////////////////////////////////////////
//                 // Se cancelar, não faz nada (modal permanece aberto)
//             });
//         }
//     });

// });
































































// function showToastrOnce(type, message, key, cooldownMs) {
//     cooldownMs = cooldownMs || 1000; // 1s default
//     var now = Date.now();
//     key = key || message;
//     if (!lastToastr[key] || (now - lastToastr[key]) > cooldownMs) {
//         lastToastr[key] = now;
//         if (type === 'error') toastr.error(message);
//         else if (type === 'success') toastr.success(message);
//         else if (type === 'info') toastr.info(message);
//         else toastr.warning(message);
//     }
// }

// function safeOpenSelect2(selector) {
//     var $el = $(selector);
//     if ($el.length === 0) return;
//     if (typeof $ === 'undefined' || typeof $.fn === 'undefined' || !$.fn.select2) return;

//     // Se não inicializado, inicializa com opções seguras
//     try {
//         if (!$el.hasClass('select2-hidden-accessible')) {
//             $el.select2({
//                 width: 'resolve',
//                 dropdownParent: $('#checkout-modal').length ? $('#checkout-modal') : $(document.body),
//                 dropdownCssClass: 'parc-limit'
//             });
//         }
//     } catch (e) {
//         // fallback silencioso
//         console.warn('safeOpenSelect2: init failed for', selector, e);
//     }

//     // Abre de forma segura no próximo tick e com try/catch
//     setTimeout(function() {
//         try {
//             // checa novamente se foi inicializado antes de abrir
//             if ($el.hasClass('select2-hidden-accessible')) {
//                 $el.select2('open');
//             }
//         } catch (err) {
//             console.warn('safeOpenSelect2: open failed for', selector, err);
//         }
//     }, 0);
// }



// // ATUALIZA OS VALORES DO CHECKOUT
// function atualizarCheckoutValores() {
//     // var subtotal = $("#p_subtotal").text();
//     // var desconto = $("#p_discount").text();
//     // var totalCobrar = $("#valortotalacobrar").val();
//     // $("#checkout_subtotal").text(subtotal);
//     // $("#checkout_desconto").text(desconto);
//     // $("#checkout_total").text(totalCobrar);
// }

//     // Mantém atualização dos valores do checkout quando o DOM mudar, mas evita recalcular parcelas automaticamente
//     $("body").on("DOMSubtreeModified", "#p_subtotal, #p_discount, #p_valorTotal", function() {
//         atualizarCheckoutValores();
//     });

//     // Atualiza os valores visíveis do checkout ao mudar as parcelas
//     atualizarCheckoutValores();

// $("body").on("input", "#valorDescCento", function(){
//     calculaDescontoPorcentagem();
// });

// $("body").on("keyup", "#valortotalpago", function() {
//     var total_amount = $("#total_amount").val();
//     var valorAPagar = $("#valorAPagar").val().replace('.', '').replace(',', '.');
//     var desconto = $("#valorDescCento").val().replace('.', '').replace(',', '.');
//     var descontoValor = $("#valorDesconto").val().replace('.', '').replace(',', '.');
//     var valortotalpago = $(this).val().replace('.', '').replace(',', '.');
//     var valortroco = 0;
//     if(desconto > 0 || descontoValor > 0)
//         valortroco = Number(valortotalpago) - Number(valorAPagar);
//     else
//         valortroco = Number(valortotalpago) - Number(total_amount);

//         $("#valortroco").val(valortroco.toFixed(2).replace('.', ','));

// });

// function calculaDescontoValor(){
//     $("#valortroco").val("0,00");
//     $("#valorDescCento").val("0");

//     var valortotal = $("#total_amount_modal").html().replace("R$", "").replace(".", "").replace(",", ".");
//     var valorDesconto = $("#valorDesconto").val().replace(".", "").replace(",", ".");
//     var valorAPagar = (valortotal - valorDesconto).toFixed(2).replace('.', ',');
//     $("#valorAPagar").val(valorAPagar);
//     const tipo_pagto = document.querySelector("#cartao");
//     if(tipo_pagto.classList.contains("text-success")){
//         $("#valortotalpago").val(valorAPagar);

//     }
//     else{
//         $("#valortotalpago").val("0,00");
//     }
// }

// $("body").on("keyup", "#valorDesconto", function(){
//     calculaDescontoValor();
// });

// function calculaDescontoPorcentagem(){
//     $("#valortotalpago").val("0,00");
//     $("#valortroco").val("0,00");
//     var valorDescCento = $.tratarValor($("#valorDescCento").val());
//     var valortotal =  $.tratarValor($("#total_amount_modal").html());
//     var valorAPagar =  $.tratarValor($("#valorAPagar").val());

//     var valorDesconto = valortotal * (valorDescCento/100);

//     $("#valorDesconto").val(valorDesconto.toFixed(2).replace('.', ','));
//     $("#valorAPagar").val((valortotal - valorDesconto).toFixed(2).replace('.', ','));

//     const tipo_pagto = document.querySelector("#cartao");
//     if(tipo_pagto.classList.contains("text-success")){
//         $("#valortotalpago").val($("#valorAPagar").val());
//     }
//     else{
//         $("#valortotalpago").val("0,00");
//     }
// }

// $("body").on("click", ".deleteHoldOrder", function(e) {

//     var id = $(this).data('id');
//     var token = $('meta[name="csrf-token"]').attr("content");
//     var url = "/pdv/" + id;
//     Pace.restart();
//     Pace.track(function () {
//         $.ajax({
//             header: {
//                 "X-CSRF-TOKEN": token,
//             },
//             url: url,
//             type: "post",
//             data: { id: id, _method: "delete", _token: token },
//         }).done(function (response) {
//             $(".emEspera").html(response.emespera);
//             Swal.fire({
//                 title: response.title,
//                 text: response.text,
//                 type: response.type,
//                 showCancelButton: false,
//                 allowOutsideClick: false,
//             }).then(function (result) {
//                 if (response.type === "error") return;
//                 if (result.value) {
//                     $(".deleteHoldOrder").parents('tr').first().remove();
//                     if($(".deleteHoldOrder").parents('tr').first().length <= 0)
//                         $("#listaDePedidosModal").modal('hide');
//                 }
//             });
//         }).fail(function () {
//             Swal.fire(
//                 "Oops...",
//                 "Algo deu errado ao tentar delatar!",
//                 "error"
//             );
//         });
//     });

//     e.preventDefault();
// });

//     // Ao focar no input, armazena o valor atual como valor anterior
//     $(document).on('focus', '.pontos-utilizar', function() {
//         let valorAtual = parseBRL($(this).val()) || 0;
//         $(this).data('valor-anterior', valorAtual);
//     });

//     $('#modalResgatarPontos').on('shown.bs.modal', function() {
//         $('.pontos-utilizar').mask('000.000.000.000.000,00', {reverse: true});
//     });

//     $(document).on('change', '#cliente_cadastro_id', function() {
//         atualizarOpcoesPrimeiraParaCartao();
//     });

//     $("#taxaDeEntrega").select2({
//         data: data
//     });

//     $("#btnPesquisarCep").on("click", function() {
//         ns.cepOnClick();
//     });

//     $("body").on("click","#carrinhoIcon", function(){
//         $('html,body').animate({scrollTop: document.body.scrollHeight},"fast");
//     });

//     $('.tab-pane, .cart-table-wrap, .dataTables_scrollBody, #invoiceShow').overlayScrollbars({
//         className: 'os-theme-dark',
//         sizeAutoCapable: true,
//         scrollbars: {
//             clickScrolling: true
//         },
//         overflowBehavior: {
//             x: "hidden",
//             y: "scroll"
//         },
//     });

//     $("#invoiceShow").css("height", ($(window).height() - 150) + "px");

//     ;
