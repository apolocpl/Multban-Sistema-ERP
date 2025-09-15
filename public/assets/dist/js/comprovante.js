/**
 * Gera o HTML do comprovante de venda (filipeta) para exibição e impressão.
 * @param {Object} params - Parâmetros necessários para o comprovante.
 * @returns {string} HTML do comprovante.
 */
function gerarComprovanteVenda(params) {
    // Parâmetros esperados:
    // params = {
    //   empresa: { nome, cnpj, im, ie, emp_id },
    //   cliente: { nome, doc, pontos: cliente_pts },
    //   comprovante: {
    //     titulo, nsu_titulo, nsu_autoriz, data_hora, cartao_numero, meio_pagamentos, tipo_pagamento, parcelas,
    //     checkout_subtotal, checkout_desconto, checkout_cashback, checkout_total, pontos_concedidos
    //   },
    //   mensagens: { cabecalho, multban, rodape },
    //   autorizacao: "COMPRA PRESENCIAL" | "AUTORIZADO MEDIANTE SENHA PESSOAL" | "AUTORIZADO POR APROVAÇÃO ELETRÔNICA"
    // }

    // Função para mascarar CNPJ
    function mascaraCNPJ(cnpj) {
        cnpj = cnpj.replace(/\D/g, '');
        return cnpj.length === 14 ? cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5") : cnpj;
    }
    // Função para mascarar CPF
    function mascaraCPF(cpf) {
        cpf = cpf.replace(/\D/g, '');
        return cpf.length === 11 ? cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4") : cpf;
    }
    // Função para mascarar cartão (4 primeiros + 5 últimos)
    function mascaraCartao(num) {
        if (!num) return '';
        num = String(num).replace(/\D/g, '');
        if (num.length < 9) return num;
        return num.substr(0, 4) + ' **** **** ' + num.substr(-5);
    }
    // Função para formatar moeda
    function formatBRL(valor) {
        return Number(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    // Função para linha horizontal
    function linha() {
        return '<hr style="border:0;border-top:1px dashed #bfa76a;margin:4px 0;">';
    }

    // Função para espaço
    function espaco() {
        return '<div style="height:10px"></div>';
    }

    // Função para bloco de texto (bege/filipeta)
    function bloco(content) {
        return `<div style="background:#fdf6e3;
                     border-radius:6px;
                     padding:12px 16px;
                     margin-bottom:8px;
                     font-family:monospace;
                     font-size:14px;
                     color:#5a4d2b;">${content}</div>`;
    }

    let nsuAutorizHtml = '';
    if (Array.isArray(params.comprovante.nsu_autoriz)) {
        params.comprovante.nsu_autoriz.forEach(function(nsu, idx) {
            nsuAutorizHtml += `
            <div style="display:flex;align-items:center;font-size:12px;">
                <span style="flex-shrink:0;">Autorização ${params.comprovante.nsu_autoriz.length > 1 ? (idx + 1) : ''}</span>
                <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
                <b style="flex-shrink:0;">${nsu}</b>
            </div>`;
        });
    } else {
        nsuAutorizHtml = `
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">Autorização</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${params.comprovante.nsu_autoriz || ''}</b>
        </div>`;
    }

    // Monta o HTML do comprovante
    let html = `<div id="comprovante-venda" style="max-width:400px;
                                                   min-width:400px;
                                                   margin:0 auto;
                                                   background:#fdf6e3;
                                                   border:1px solid #bfa76a;
                                                   border-radius:8px;
                                                   padding:16px;
                                                   box-shadow:0 2px 8px #bfa76a33;
                                                   font-size:14px;
                                                   font-family:monospace;">

        <div style="text-align:center;font-size:13px;">
            ${(params.mensagens.cabecalho || '').replace(/\\n/g, '<br>').replace(/\n/g, '<br>')}
        </div>
        ${espaco()}
        ${espaco()}
        <div style="font-weight:bold;text-align:center;font-size:13px;">ESTABELECIMENTO</div>
        ${linha()}
        <div style="display:flex;justify-content:space-between;">
            <div style="text-align:left;font-size:12px;">
                <div>${params.empresa.nome || ''}</div>
                <div>CNPJ: ${mascaraCNPJ(params.empresa.cnpj || '')}</div>
                <div>IM: ${params.empresa.im || 'ISENTO'}</div>
                <div>IE: ${params.empresa.ie || 'ISENTO'}</div>
            </div>
            <div style="text-align:right;font-size:12px;">
                <div>Estabelecimento: ${params.empresa.emp_id || ''}</div>
            </div>
        </div>
        ${espaco()}
        ${espaco()}
        <div style="font-weight:bold;text-align:center;font-size:13px;">OPERAÇÃO</div>
        ${linha()}
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">Cdg do Documento Vinculado</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${params.comprovante.titulo || ''}</b>
        </div>
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">Valor da Compra</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${formatBRL(params.comprovante.checkout_subtotal)}</b>
        </div>
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">Desconto Concedido</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${formatBRL(params.comprovante.checkout_desconto)}</b>
        </div>
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">Pontos Utilizados</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${params.comprovante.checkout_cashback || 0}</b>
        </div>
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">Valor do Pagamento</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${formatBRL(params.comprovante.checkout_total)}</b>
        </div>
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">Troco</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${formatBRL(params.comprovante.checkout_troco)}</b>
        </div>
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">Meio de Pagamento</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${params.comprovante.meio_pagamentos || ''}</b>
        </div>
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">Tipo de Pagamento</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${params.comprovante.tipo_pagamento || ''}</b>
        </div>
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">Parcela</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${params.comprovante.parcelas || '1'}</b>
        </div>
        ${espaco()}
        ${espaco()}
        <div style="font-size:12px;">
            ${(params.mensagens.multban || '').replace(/\\n/g, '<br>').replace(/\n/g, '<br>')}
        </div>
        ${espaco()}
        ${espaco()}
        <div style="font-weight:bold;text-align:center;font-size:13px;">COMPROVANTE DE OPERAÇÃO</div>
        ${linha()}
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">Nº Cartão</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${mascaraCartao(params.comprovante.cartao_numero || '')}</b>
        </div>
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">NSU</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${params.comprovante.nsu_titulo || ''}</b>
        </div>
        ${nsuAutorizHtml}
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">Autorizado em</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${params.comprovante.data_hora || ''}</b>
        </div>
        ${espaco()}
        <div style="font-weight:bold;text-align:center;font-size:13px;">PROGRAMA DE PONTOS</div>
        ${espaco()}
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">Saldo de Pontos</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${params.cliente.pontos || 0}</b>
        </div>
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">Pontos Utilizados</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${params.comprovante.checkout_cashback || 0}</b>
        </div>
        <div style="display:flex;align-items:center;font-size:12px;">
            <span style="flex-shrink:0;">Pontos Adquiridos</span>
            <span style="flex-grow:1;border-bottom:1px dotted #bfa76a;margin:0 6px;"></span>
            <b style="flex-shrink:0;">${params.comprovante.pontos_concedidos || 0}</b>
        </div>
        ${espaco()}
        ${espaco()}
        <div style="text-align:center;font-size:12px;">
            ${params.autorizacao || ''}
        </div>
        ${espaco()}
        ${espaco()}
        <div style="font-weight:bold;text-align:center;font-size:13px;">RECONHECIMENTO</div>
        ${linha()}
        <div style="font-size:12px;">${params.mensagens.rodape || ''} - EM ESPÉCIE</div>
        <div style="font-size:12px;">${params.cliente.nome || ''} - ${mascaraCPF(params.cliente.doc || '')}</div>
        ${espaco()}
    </div>`;
    return html;
}
