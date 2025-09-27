<?php

namespace App\Http\Controllers\Multban\Venda;

use App\Http\Controllers\Multban\Produto\ProdutoController;
use App\Http\Controllers\Controller;
use App\Models\Multban\DadosMestre\MeioDePagamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Multban\Venda\RegrasParc;
use App\Models\Multban\Empresa\Empresa;
use App\Models\Multban\Empresa\EmpresaParam;
use App\Models\Multban\Empresa\EmpresaTaxpos;
use App\Models\Multban\Produto\Produto;
use App\Models\Multban\Produto\ProdutoStatus;
use App\Models\Multban\Produto\ProdutoTipo;
use App\Models\Multban\TbTr\TbtrHTitulos;
use App\Models\Multban\TbTr\TbtrITitulos;
use App\Models\Multban\TbTr\TbtrPTitulosCp;
use App\Models\Multban\TbTr\TbtrSTitulos;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PdvWebController extends Controller
{
    /**
     * Processa cobrança DN e grava nas tabelas tbtr_h_titulos, tbtr_i_titulos, tbtr_p_titulos_cp, tbtr_s_titulos
     */
    public function realizarVenda(Request $request)
    {
        try {
            DB::connection('dbsysclient')->beginTransaction();

                // DADOS DO USUÁRIO LOGADO
                $user = Auth::user();

                // DADOS RECEBIDOS DO REQUEST - PDV WEB
                $cliente_id = $request->input('cliente_id');
                $checkout_subtotal = $request->input('checkout_subtotal');      // Valor total do carrinho antes de descontos
                $checkout_cashback = $request->input('checkout_cashback');      // Valor total de cashback aplicado
                $checkout_desconto = $request->input('checkout_desconto');      // Valor total de desconto aplicado
                $checkout_pago = $request->input('checkout_pago');              // Valor total pago
                $checkout_troco = $request->input('valortroco');                // Valor total de troco
                $checkout_descontado = $request->input('checkout_descontado');  // Valor total descontado
                $checkout_resgatado = $request->input('checkout_resgatado');    // Pontos de cashback resgatados
                $checkout_total = $request->input('checkout_total');            // Valor total a cobrar (subtotal - desconto - cashback)
                $valortotalacobrar = $request->input('valortotalacobrar');      // Valor que o usuário quer cobrar agora
                $proporcao_cobrado = $request->input('proporcao_cobrado');      // Proporção do valor total a ser cobrado
                $tipoPagto = $request->input('tipoPagto');                      // Tipo de pagamento (ex: 'DN', 'CARTAO', etc.)
                $carrinho_venda = $request->input('carrinho');                  // array de itens

                $vendaSemJuros = $request->input('vendaSemJuros', 0);
                $check_reembolso = $request->input('check_reembolso', null);
                $dataPrimeiraParcela = $request->input('dataPrimeiraParcela');
                $parcelas = $request->input('parcelas');
                $valorTotalComJuros = $request->input('valorTotalComJuros');
                $valorParcelaComJuros = $request->input('valorParcelaComJuros');
                $valorParcelaSemJuros = $request->input('valorParcelaSemJuros');
                $jurosTotal = $request->input('jurosTotal');
                $jurosTotalParcela = $request->input('jurosTotalParcela');
                $tax_categ = $request->input('tax_categ');
                $regra_parc = $request->input('regra_parc');
                $card_categ = $request->input('card_categ');
                $card_uuid = $request->input('card_uuid');
                $card_mod = $request->input('card_mod');
                $card_tp = $request->input('card_tp');

                // CARREGA DADOS DA EMPRESA E PARÂMETROS DA EMPRESA DO USUÁRIO LOGADO
                $emp_id = $user->emp_id;
                $empresa = Empresa::find($emp_id);
                $empresaParam = EmpresaParam::find($emp_id);
                $empresaTaxpos = EmpresaTaxpos::find($emp_id);

                // CALCULA DIAS DE PRAZO, SE HOUVER
                $diasPrazo = null;
                if ($dataPrimeiraParcela) {
                    $dataPrimeira = Carbon::parse($dataPrimeiraParcela);
                    $hoje = Carbon::today();
                    $diasPrazo = $hoje->diffInDays($dataPrimeira, false);
                }

                // BUSCA A TAXA DE ACORDO COM A CATEGORIA E PARCELAS
                $taxpos = EmpresaTaxpos::where('emp_id', $emp_id)
                    ->where('tax_categ', $tax_categ)
                    ->where('parc_de', '<=', $parcelas)
                    ->where('parc_ate', '>=', $parcelas)
                    ->first();

                $tax = null;
                if ($taxpos) {
                    $tax = $taxpos->tax;
                }

                // DESENHAR A REGRA AQUI PARA A LIBERAÇÃO DA ANTECIPAÇÃO
                $lib_ant = null;

                // PARÂMETROS
                $emp_wlde = $empresa->emp_wlde;
                $emp_comwl = null;
                if ($emp_wlde) {
                    $empresaWhiteLabel = Empresa::find($emp_wlde);
                    if ($empresaWhiteLabel) {
                        $emp_comwl = $empresaWhiteLabel->emp_comwl;
                    }
                }

                // DADOS DOS PARÂMETROS DA EMPRESA
                $vlr_pix = $empresaParam->vlr_pix ? $empresaParam->vlr_pix : 0;                            // Valor Pix
                $vlr_boleto = $empresaParam->vlr_boleto ? $empresaParam->vlr_boleto : 0;                   // Valor Boleto
                $taxa_bacen = $vlr_pix + $vlr_boleto;                                                      // Taxa Bacen (Pix + Boleto)
                $vlr_bolepix = $empresaParam->vlr_bolepix ? $empresaParam->vlr_bolepix : 0;                // Valor Boleto + Pix
                $tax_blt = $empresaParam->tax_blt ? $empresaParam->tax_blt : 0;                            // Taxa Boleto
                $parc_com_jrs = $empresaParam->parc_com_jrs ? $empresaParam->parc_com_jrs : 0;             // Comissão Parcelamento com Juros
                $tax_pre = $empresaParam->tax_pre ? $empresaParam->tax_pre : 0;                            // Taxa Pré
                $tax_gift = $empresaParam->tax_gift ? $empresaParam->tax_gift : 0;                         // Taxa Gift
                $tax_fid = $empresaParam->tax_fid ? $empresaParam->tax_fid : 0;                            // Taxa Fidelidade
                $pp_particular = $empresaParam->pp_particular ? $empresaParam->pp_particular : 0;          // Pagamento Particular
                $pp_franquia = $empresaParam->pp_franquia ? $empresaParam->pp_franquia : 0;                // Pagamento Franquia
                $pp_mult = $empresaParam->pp_mult ? $empresaParam->pp_mult : 0;                            // Pagamento Multi Cartão
                $pp_cashback = $empresaParam->pp_cashback ? $empresaParam->pp_cashback : 0;                // Pagamento Cashback
                $tax_antmult = $empresaParam->tax_antmult ? $empresaParam->tax_antmult : 0;                // Taxa Antecipação Multi
                $tax_antfundo = $empresaParam->tax_antfundo ? $empresaParam->tax_antfundo : 0;             // Taxa Antecipação Fundo
                $perc_rec_ant = $empresaParam->perc_rec_ant ? $empresaParam->perc_rec_ant : 0;             // Percentual Recebido Antecipação
                $rebate_emp = $empresaParam->rebate_emp ? $empresaParam->rebate_emp : 0;                   // Rebate Empresa
                $tax_rebate = $empresaParam->tax_rebate ? $empresaParam->tax_rebate : 0;                   // Taxa Rebate
                $royalties_emp = $empresaParam->royalties_emp ? $empresaParam->royalties_emp : 0;          // Royalties Empresa
                $tax_royalties = $empresaParam->tax_royalties ? $empresaParam->tax_royalties : 0;          // Taxa Royalties
                $comiss_emp = $empresaParam->comiss_emp ? $empresaParam->comiss_emp : 0;                   // Comissão Empresa
                $tax_comiss = $empresaParam->tax_comiss ? $empresaParam->tax_comiss : 0;

                // FÓRMULAS
                $user_id = $user->user_id;
                $vlr_brt = $checkout_subtotal * $proporcao_cobrado;
                $qtd_pts_utlz = $checkout_cashback * $proporcao_cobrado;
                $perc_pts_utlz = $vlr_brt > 0 ? ($qtd_pts_utlz / $vlr_brt) * 100 : 0;
                $vlr_btot = $vlr_brt - $qtd_pts_utlz;
                $vlr_dec = $checkout_desconto * $proporcao_cobrado;
                $perc_desc = $vlr_brt > 0 ? ($vlr_dec / $vlr_brt) * 100 : 0;
                $vlr_dec_mn = 0;
                $vlr_btot_split = $vlr_btot - $vlr_dec - $vlr_dec_mn;
                $perc_juros = $vlr_brt > 0 ? ($jurosTotal / $vlr_brt) * 100 : 0;;
                $vlr_juros = $jurosTotal;
                $vlr_btot_cj = $vlr_btot_split + $vlr_juros;
                $vlr_atr_m = 0;
                $vlr_atr_j = 0;
                $vlr_acr_mn = 0;

                // REGRA DO PROGRAMA DE PONTOS
                // se programa de pontos particular ativo
                $emp_pp_particular = null;
                if ($pp_particular) {
                    $emp_pp_particular = $empresa->emp_id;
                }
                // se programa de pontos da franquia ativo
                $emp_pp_franquia = null;
                if ($pp_franquia) {
                    $emp_pp_franquia = $empresa->emp_frqmst ?? null;
                }
                // se programa de pontos multban ativo
                $emp_pp_mult = null;
                if ($pp_mult) {
                    $emp_pp_mult = 1;
                }
                // se programa de cashback multban ativo
                $emp_pp_cashback = null;
                if ($pp_cashback) {
                    $emp_pp_cashback = 1;
                }

                // TAXA ADMINISTRATIVA
                $tax_adm = 0;
                if ($tipoPagto === 'CM') {
                    if ($card_tp === 'PRE') {
                        $tax_adm = $tax_pre;
                    } else if ($card_mod === 'CRDT') {
                        $tax_adm = $tax;
                    } else if ($card_mod === 'GIFT') {
                        $tax_adm = $tax_gift;
                    } else if ($card_mod === 'FIDL') {
                        $tax_adm = $tax_fid;
                    }
                } else if ($tipoPagto === 'BL') {
                    $tax_adm = $tax_blt;
                } else if ($tipoPagto === 'DN') {
                    $tax_adm = 0;
                } else if ($tipoPagto === 'PX') {
                    $tax_adm = 0;
                } else if ($tipoPagto === 'OT') {
                    $tax_adm = 0;
                }

                // RECEBE O TÍTULO DO REQUEST (PODE SER NULL NA PRIMEIRA COBRANÇA)
                $tituloRequest = $request->input('tituloAtual');
                $nsu_tituloRequest = $request->input('nsu_tituloAtual');
                $nsu_autoriz = Str::uuid();

                if ($tituloRequest && $nsu_tituloRequest) {
                    // Cobrança parcial: use os mesmos titulo e nsu_titulo da primeira cobrança
                    $titulo = $tituloRequest;
                    $nsu_titulo = $nsu_tituloRequest;
                } else {
                    // Primeira cobrança: gere um novo nsu_titulo
                    $nsu_titulo = Str::uuid();
                    // O $titulo será preenchido após o insert (auto-incremento)
                }

                //////////////////////////////////////////
                // GRAVA OS DADOS NA TABELA TBTR_H_TITULOS
                //////////////////////////////////////////
                $data = [
                    'emp_id' => $emp_id,
                    'user_id' => $user_id,
                    // 'titulo' => $titulo, --- IGNORE ---
                    'nsu_titulo' => $nsu_titulo,
                    'qtd_parc' => $parcelas ? $parcelas : 1,
                    'primeira_para' => $regra_parc ? $regra_parc : 1,
                    'cnd_pag' => ($parcelas > 1) ? 2 : 1,
                    'cliente_id' => $cliente_id,
                    'meio_pag_v' => $tipoPagto,
                    'card_uuid' => $card_uuid,
                    'data_mov' => now(),
                    'nsu_autoriz' => $nsu_autoriz,
                    'check_reemb' => $check_reembolso,
                    'lib_ant' => $lib_ant,
                    'vlr_brt' => $vlr_brt,
                    'tax_adm' => $tax_adm,
                    'tax_rebate' => $tax_rebate,
                    'tax_royalties' => $tax_royalties,
                    'tax_comissao' => $tax_comiss,
                    'qtd_pts_utlz' => $qtd_pts_utlz,
                    'perc_pts_utlz' => $perc_pts_utlz,
                    'vlr_btot' => $vlr_btot,
                    'perc_desc' => $perc_desc,
                    'vlr_dec' => $vlr_dec,
                    'vlr_dec_mn' => $vlr_dec_mn,
                    'vlr_btot_split' => $vlr_btot_split,
                    'perc_juros' => $perc_juros,
                    'vlr_juros' => $vlr_juros,
                    'vlr_btot_cj' => $vlr_btot_cj,
                    'vlr_atr_m' => $vlr_atr_m,
                    'vlr_atr_j' => $vlr_atr_j,
                    'vlr_acr_mn' => $vlr_acr_mn,
                    'criador' => $user_id,
                    'dthr_cr' => now(),
                    'modificador' => $user_id,
                    'dthr_ch' => now(),
                ];

                if ($tituloRequest) {
                    $data['titulo'] = $tituloRequest;
                }

                $hTitulo = new TbtrHTitulos($data);
                $hTitulo->save();
                $titulo = $hTitulo->titulo;













                ////////////////////////////////////////////////////////////////////////////////////////
                // GRAVA OS DADOS NA TABELA TBTR_I_TITULOS E TBTR_S_TITULOS - PARA CADA ITEM DO CARRINHO
                ////////////////////////////////////////////////////////////////////////////////////////
                $item_seq = 1;

                foreach ($carrinho_venda as $item) {
                    $data = [];

                    $produto_tipo = $item['produto_tipo'];
                    $proporcao_item = $item['proporcao_item'];
                    $produto_id = $item['produto_id'];
                    $qtd_item = $item['qtd_item'];
                    $vlr_brt_item = $item['vlr_brut_item'] * $proporcao_cobrado;
                    $vlr_unit_item = $item['vlr_unit_item'];
                    $vlr_dec_item = $item['vlr_desc_item'] * $proporcao_cobrado;
                    $vlr_dec_mn = 0;
                    $vlr_base_item = $vlr_brt_item - $vlr_dec_item - $vlr_dec_mn;
                    $perc_toti = $proporcao_item;
                    $qtd_pts_utlz_item = $perc_toti * $qtd_pts_utlz / 100;
                    $vlr_bpar_split_item = $vlr_base_item - $qtd_pts_utlz_item;
                    $vlr_jpar_item = $perc_toti * $jurosTotal / 100;
                    $vlr_bpar_cj_item = $vlr_bpar_split_item + $vlr_jpar_item;
                    $vlr_atrm_item = 0;
                    $vlr_atrj_item = 0;
                    $vlr_acr_mn = 0;
                    $ant_desc = 0;
                    $pgt_vlr = 0;
                    $pgt_desc = 0;
                    $pgt_mtjr = 0;
                    $pts_disp = 0;

                    // VALOR RECEBIDO
                    if ($tipoPagto === 'CM') {
                        $vlr_rec = 0;
                    } else if ($tipoPagto === 'BL') {
                        $vlr_rec = 0;
                    } else if ($tipoPagto === 'DN') {
                        $vlr_rec = $vlr_bpar_cj_item;
                    } else if ($tipoPagto === 'PX') {
                        $vlr_rec = $vlr_bpar_cj_item;
                    } else if ($tipoPagto === 'OT') {
                        $vlr_rec = $vlr_bpar_cj_item;
                    }

                    /////////////////
                    // TBTR_I_TITULOS
                    $data = [
                        'emp_id' => $emp_id,
                        'user_id' => $user_id,
                        'titulo' => $titulo,
                        'nsu_titulo' => (string) $nsu_titulo,
                        'nsu_autoriz' => (string) $nsu_autoriz,
                        'item' => $item_seq,
                        'produto_tipo' => $produto_tipo,
                        'produto_id' => $produto_id,

                        'qtd_item' => $qtd_item,
                        'vlr_unit_item' => $vlr_unit_item,
                        'vlr_brt_item' => $vlr_brt_item,
                        'perc_toti' => $perc_toti,
                        'qtd_pts_utlz_item' => $qtd_pts_utlz_item,
                        'vlr_base_item' => $vlr_base_item,
                        'vlr_dec_item' => $vlr_dec_item,
                        'vlr_dec_mn' => $vlr_dec_mn,
                        'vlr_bpar_split_item' => $vlr_bpar_split_item,
                        'vlr_jpar_item' => $vlr_jpar_item,
                        'vlr_bpar_cj_item' => $vlr_bpar_cj_item,
                        'vlr_atrm_item' => $vlr_atrm_item,
                        'vlr_atrj_item' => $vlr_atrj_item,
                        'vlr_acr_mn' => $vlr_acr_mn,
                        'ant_desc' => $ant_desc,
                        'pgt_vlr' => $pgt_vlr,
                        'pgt_desc' => $pgt_desc,
                        'pgt_mtjr' => $pgt_mtjr,
                        'vlr_rec' => $vlr_rec,
                        'pts_disp' => $pts_disp,
                        'criador' => $user_id,
                        'dthr_cr' => now(),
                        'modificador' => $user_id,
                        'dthr_ch' => now(),
                    ];

                    $iTitulo = new TbtrITitulos($data);
                    $iTitulo->save();
                    $item_seq++;








                //     /////////////////
                //     // TBTR_S_TITULOS

                //     // CALCULO DE TAXA ADMINISTRATIVA
                //     $vlr_tax = 0;
                //     if ($tax_adm > 0) {
                //         $vlr_tax = $vlr_bpar_split_item * $tax_adm / 100;
                //     }

                //     // CALCULO DE REBATE
                //     $vlr_rebate = 0;
                //     if ($rebate_emp && $tax_rebate && $tax_adm) {
                //         $vlr_rebate = $vlr_tax * $tax_rebate / 100;
                //         $vlr_tax = $vlr_tax - $vlr_rebate;
                //     }

                //     // CALCULO DE ROYALTIES
                //     $vlr_royalties = 0;
                //     if ($royalties_emp && $tax_royalties) {
                //         $vlr_royalties = $vlr_bpar_split_item * $tax_royalties / 100;
                //     }

                //     // CALCULO DE COMISSÃO DE VENDAS
                //     $vlr_comissao = 0;
                //     if ($comiss_emp && $tax_comiss) {
                //         $vlr_comissao = $vlr_bpar_split_item * $tax_comiss / 100;
                //     }

                //     if ($tipoPagto === 'CM') {

                //     } else if ($tipoPagto === 'BL') {

                //     } else if ($tipoPagto === 'DN') {
                //         $data = [
                //             'emp_id' => $emp_id,
                //             'user_id' => $user_id,
                //             'titulo' => $titulo,
                //             'nsu_titulo' => (string) $nsu_titulo,
                //             'nsu_autoriz' => (string) $nsu_autoriz,
                //             'parcela' => $parcela,
                //             'produto_id' => $produto_id,
                //             'lanc_tp' => 'DINHEIRO',
                //             'recebedor' => $emp_id,

                //             'tax_adm' => 0,
                //             'vlr_plan' => $vlr_bpar_split_item,
                //             'perc_real' => 100,
                //             'vlr_real' => $vlr_rec,
                //             'criador' => $user_id,
                //             'dthr_cr' => now(),
                //             'modificador' => $user_id,
                //             'dthr_ch' => now(),
                //         ];

                //         $sTitulo = new TbtrSTitulos($data);
                //         $sTitulo->save();

                //     } else if ($tipoPagto === 'PX') {
                //         $vlr_plan_repasse = $vlr_bpar_split_item * (1 - $vlr_pix / 100);
                //         $vlr_real_repasse = $vlr_rec * (1 - $vlr_pix / 100);
                //         $vlr_plan_vlr_pix = $vlr_bpar_split_item - $vlr_plan_repasse;
                //         $vlr_real_vlr_pix = $vlr_rec - $vlr_real_repasse;

                //         $data = [
                //             'emp_id' => $emp_id,
                //             'user_id' => $user_id,
                //             'titulo' => $titulo,
                //             'nsu_titulo' => (string) $nsu_titulo,
                //             'nsu_autoriz' => (string) $nsu_autoriz,
                //             'parcela' => $parcela,
                //             'produto_id' => $produto_id,
                //             'lanc_tp' => 'REPASSE',
                //             'recebedor' => $emp_id,

                //             'tax_adm' => 0,
                //             'vlr_plan' => $vlr_plan_repasse,
                //             'perc_real' => 100,
                //             'vlr_real' => $vlr_real_repasse,
                //             'criador' => $user_id,
                //             'dthr_cr' => now(),
                //             'modificador' => $user_id,
                //             'dthr_ch' => now(),
                //         ];

                //         $sTitulo = new TbtrSTitulos($data);
                //         $sTitulo->save();

                //         // SE A EMPRESA FOR WHITE LABEL, PRECISAMOS PROPORCIONAR OS VALORES
                //         // DE ACORDO COM A COMISSÃO DA MULTBAN
                //         if ($emp_wlde && $emp_comwl) {
                //             $vlr_plan_vlr_pix_wl = $vlr_plan_vlr_pix * (1 - $emp_comwl / 100);
                //             $vlr_plan_vlr_pix = $vlr_plan_vlr_pix * ($emp_comwl / 100);
                //             $vlr_real_vlr_pix_wl = $vlr_real_vlr_pix * (1 - $emp_comwl / 100);
                //             $vlr_real_vlr_pix = $vlr_real_vlr_pix * ($emp_comwl / 100);

                //             // MULTBAN
                //             $data = [
                //                 'emp_id' => $emp_id,
                //                 'user_id' => $user_id,
                //                 'titulo' => $titulo,
                //                 'nsu_titulo' => (string) $nsu_titulo,
                //                 'nsu_autoriz' => (string) $nsu_autoriz,
                //                 'parcela' => $parcela,
                //                 'produto_id' => $produto_id,
                //                 'lanc_tp' => 'TAX_BAC',
                //                 'recebedor' => 1,

                //                 'tax_adm' => 0,
                //                 'vlr_plan' => $vlr_plan_vlr_pix,
                //                 'perc_real' => 100,
                //                 'vlr_real' => $vlr_real_vlr_pix,
                //                 'criador' => $user_id,
                //                 'dthr_cr' => now(),
                //                 'modificador' => $user_id,
                //                 'dthr_ch' => now(),
                //             ];

                //             $sTitulo = new TbtrSTitulos($data);
                //             $sTitulo->save();

                //             // EMPRESA WHITE LABEL
                //             $data = [
                //                 'emp_id' => $emp_id,
                //                 'user_id' => $user_id,
                //                 'titulo' => $titulo,
                //                 'nsu_titulo' => (string) $nsu_titulo,
                //                 'nsu_autoriz' => (string) $nsu_autoriz,
                //                 'parcela' => $parcela,
                //                 'produto_id' => $produto_id,
                //                 'lanc_tp' => 'TAX_BAC',
                //                 'recebedor' => 1,

                //                 'tax_adm' => 0,
                //                 'vlr_plan' => $vlr_plan_vlr_pix_wl,
                //                 'perc_real' => 100,
                //                 'vlr_real' => $vlr_real_vlr_pix_wl,
                //                 'criador' => $user_id,
                //                 'dthr_cr' => now(),
                //                 'modificador' => $user_id,
                //                 'dthr_ch' => now(),
                //             ];

                //             $sTitulo = new TbtrSTitulos($data);
                //             $sTitulo->save();

                //         } else {
                //             $data = [
                //                 'emp_id' => $emp_id,
                //                 'user_id' => $user_id,
                //                 'titulo' => $titulo,
                //                 'nsu_titulo' => (string) $nsu_titulo,
                //                 'nsu_autoriz' => (string) $nsu_autoriz,
                //                 'parcela' => $parcela,
                //                 'produto_id' => $produto_id,
                //                 'lanc_tp' => 'TAX_BAC',
                //                 'recebedor' => 1,

                //                 'tax_adm' => 0,
                //                 'vlr_plan' => $vlr_plan_vlr_pix,
                //                 'perc_real' => 100,
                //                 'vlr_real' => $vlr_real_vlr_pix,
                //                 'criador' => $user_id,
                //                 'dthr_cr' => now(),
                //                 'modificador' => $user_id,
                //                 'dthr_ch' => now(),
                //             ];
                //         }

                //         $sTitulo = new TbtrSTitulos($data);
                //         $sTitulo->save();

                //     } else if ($tipoPagto === 'OT') {
                //         $data = [
                //             'emp_id' => $emp_id,
                //             'user_id' => $user_id,
                //             'titulo' => $titulo,
                //             'nsu_titulo' => (string) $nsu_titulo,
                //             'nsu_autoriz' => (string) $nsu_autoriz,
                //             'parcela' => $parcela,
                //             'produto_id' => $produto_id,
                //             'lanc_tp' => 'OUTROS',
                //             'recebedor' => $emp_id,

                //             'tax_adm' => 0,
                //             'vlr_plan' => $vlr_bpar_split_item,
                //             'perc_real' => 100,
                //             'vlr_real' => $vlr_rec,
                //             'criador' => $user_id,
                //             'dthr_cr' => now(),
                //             'modificador' => $user_id,
                //             'dthr_ch' => now(),
                //         ];

                //         $sTitulo = new TbtrSTitulos($data);
                //         $sTitulo->save();

                //     }


                }

















                // /////////////////////////////////////////////////////////////
                // // GRAVA OS DADOS NA TABELA TBTR_P_TITULOS_CP - PARCELA ÚNICA
                // /////////////////////////////////////////////////////////////
                // $nid_parcela = Str::uuid();

                // $data = [
                //     'emp_id' => $emp_id,
                //     'user_id' => $user_id,
                //     'titulo' => $titulo,
                //     'nsu_titulo' => (string) $nsu_titulo,
                //     'nsu_autoriz' => (string) $nsu_autoriz,
                //     'cliente_id' => $cliente_id,
                //     'meio_pag_v' => $tipoPagto,
                //     'data_mov' => now(),
                //     'nid_parcela' => (string) $nid_parcela,
                //     'data_venc' => now(),
                //     'destvlr' => $empresaParam->emp_destvlr,
                //     'data_pgto' => now(),
                //     'meio_pag_t' => $tipoPagto,
                //     'nid_parcela_org' => null,
                //     'parcela_obs' => null,
                //     'parcela_ins_pg' => null,
                //     'qtd_pts_utlz' => $qtd_pts_utlz,
                //     'vlr_dec' => $vlr_dec,
                //     'vlr_dec_mn' => 0,
                //     'vlr_bpar_split' => $vlr_btot_split,
                //     'vlr_bpar_cj' => $vlr_btot_split,
                //     'vlr_atr_m' => 0,
                //     'vlr_atr_j' => 0,
                //     'isent_mj' => null,
                //     'negociacao' => null,
                //     'vlr_acr_mn' => 0,
                //     'negociacao_obs' => null,
                //     'follow_dt' => null,
                //     'perct_ant' => 0,
                //     'ant_desc' => 0,
                //     'pgt_vlr' => $vlr_btot_split,
                //     'pgt_desc' => 0,
                //     'pgt_mtjr' => 0,
                //     'vlr_rec' => $vlr_btot_split,
                //     'pts_disp_item' => 0,
                //     'criador' => $user_id,
                //     'dthr_cr' => now(),
                //     'modificador' => $user_id,
                //     'dthr_ch' => now(),
                // ];

                // if ($tipoPagto === 'CM') {

                // } else if ($tipoPagto === 'BL') {

                // } else if ($tipoPagto === 'DN') {
                //     $data['qtd_parc'] = 1;
                //     $data['primeira_para'] = 1;
                //     $data['cnd_pag'] = 1;
                //     $data['parcela'] = 1;
                //     $data['parcela_sts'] = 'BDI';
                //     $data['id_fatura'] = null;
                //     $data['card_uuid'] = null;
                //     $data['integ_bc'] = null;
                //     $data['tax_bacen'] = 0;
                //     $data['vlr_jurosp'] = 0;

                // } else if ($tipoPagto === 'PX') {
                //     $data['qtd_parc'] = 1;
                //     $data['primeira_para'] = 1;
                //     $data['cnd_pag'] = 1;
                //     $data['parcela'] = 1;
                //     $data['parcela_sts'] = 'BPX';
                //     $data['id_fatura'] = null;
                //     $data['card_uuid'] = null;
                //     $data['integ_bc'] = null;
                //     $data['tax_bacen'] = $taxa_bacen;
                //     $data['vlr_jurosp'] = 0;

                // } else if ($tipoPagto === 'OT') {
                //     $data['qtd_parc'] = 1;
                //     $data['primeira_para'] = 1;
                //     $data['cnd_pag'] = 1;
                //     $data['parcela'] = 1;
                //     $data['parcela_sts'] = 'BOT';
                //     $data['id_fatura'] = null;
                //     $data['card_uuid'] = null;
                //     $data['integ_bc'] = null;
                //     $data['tax_bacen'] = 0;
                //     $data['vlr_jurosp'] = 0;
                // }

                // $pTituloCp = new TbtrPTitulosCp($data);
                // $pTituloCp->save();

            DB::connection('dbsysclient')->commit();
            return response()->json(['success' => true, 'titulo' => $titulo, 'nsu_titulo' => $nsu_titulo, 'nsu_autoriz' => $nsu_autoriz]);

        } catch (\Throwable $e) {
            DB::connection('dbsysclient')->rollBack();
            $msg = $e->getMessage();
            if (preg_match("/Column '([^']+)' cannot be null/", $msg, $matches)) {
                $campo = $matches[1];
                $msg = "O campo obrigatório \"$campo\" não está preenchido no cadastro da empresa. Por favor, revise o cadastro antes de finalizar a venda.";
            }
            return response()->json(['success' => false, 'error' => $msg, 'trace' => $e->getTraceAsString()], 500);
        }
    }

    public function cancelarVenda(Request $request)
    {
        $titulo = $request->input('titulo');

        if (!$titulo) {
            return response()->json(['success' => true]); // Como não há título, nada a cancelar
        }

        if ($titulo) {
            try {
                DB::connection('dbsysclient')->beginTransaction();

                // Exclua os registros relacionados ao título
                TbtrITitulos::where('titulo', $titulo)->delete();
                TbtrSTitulos::where('titulo', $titulo)->delete();
                TbtrPTitulosCp::where('titulo', $titulo)->delete();
                TbtrHTitulos::where('titulo', $titulo)->delete();

                DB::connection('dbsysclient')->commit();
                return response()->json(['success' => true]);
            } catch (\Throwable $e) {
                DB::connection('dbsysclient')->rollBack();
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
        }

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $RegrasParc = RegrasParc::all();
        $meioDePagamento = MeioDePagamento::orderBy('meio_order')->get();

        // Obtém o emp_id do usuário autenticado
        $empId = Auth::user()->emp_id;

        // Filtra produtos pelo emp_id do usuário logado
        $produtos = Produto::where('emp_id', $empId)->get();

        // Carrega parâmetros da empresa para este emp_id
        $empresa = Empresa::find($empId);

        // Carrega parâmetros da empresa para este emp_id
        $empresaParam = EmpresaParam::find($empId);

        // Indexa o array pelo código do status para facilitar busca no Blade
        $produtosStatus = ProdutoStatus::all()->keyBy('produto_sts');

        // Monta array de código => descrição
        $produtosTipo = ProdutoTipo::all()->pluck('produto_tipo_desc', 'produto_tipo');
        return view('Multban.venda.pdv.index', compact('meioDePagamento', 'RegrasParc', 'produtos', 'produtosStatus', 'produtosTipo', 'empresaParam'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function pdv()
    {
        // Busca todas as regras de parcelamento
        // $RegrasParc = RegrasParc::all();
        // return view('Multban.venda.pdv.index', compact('RegrasParc'));
    }
}
