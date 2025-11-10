<?php

namespace App\Http\Controllers\Multban\Venda;

use App\Http\Controllers\Controller;
use App\Models\Multban\DadosMestre\MeioDePagamento;
use App\Models\Multban\Empresa\Empresa;
use App\Models\Multban\Empresa\EmpresaParam;
use App\Models\Multban\Empresa\EmpresaTaxpos;
use App\Models\Multban\Produto\Produto;
use App\Models\Multban\Produto\ProdutoStatus;
use App\Models\Multban\Produto\ProdutoTipo;
use App\Models\Multban\Cliente\Cliente;
use App\Models\Multban\Cliente\ClienteCard;
use App\Models\Multban\TbTr\TbtrHTitulos;
use App\Models\Multban\TbTr\TbtrITitulos;
use App\Models\Multban\TbTr\TbtrPTitulosCp;
use App\Models\Multban\TbTr\TbtrPTitulosAb;
use App\Models\Multban\TbTr\TbtrSTitulos;
use App\Models\Multban\TbTr\TbtrFTitulos;
use App\Models\Multban\Venda\RegrasParc;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Laravel\Pail\ValueObjects\Origin\Console;

class PdvWebController extends Controller
{
    /**
     * Processa cobrança DN e grava nas tabelas tbtr_h_titulos, tbtr_i_titulos, tbtr_p_titulos_cp, tbtr_s_titulos
     */
    public function realizarVenda(Request $request)
    {

        try {
            DB::connection('dbsysclient')->beginTransaction();

            /////////////////////////////////////////////////////////////////////////////
            // DADOS DO USUÁRIO LOGADO
            $user = Auth::user();
            $user_param = User::find($user->user_id);
            $user_comis = $user_param->user_comis;
            $user_pcomis = $user_param->user_pcomis;

            /////////////////////////////////////////////////////////////////////////////
            // DADOS RECEBIDOS DO REQUEST - PDV WEB / API / MANUTENÇÃO DE TÍTULOS
            $cliente_id = $request->input('cliente_id');
            $checkout_subtotal = $request->input('checkout_subtotal');            // Valor total do carrinho antes de descontos
            $checkout_cashback = $request->input('checkout_cashback');            // Valor total de cashback aplicado
            $checkout_desconto = $request->input('checkout_desconto');            // Valor total de desconto aplicado
            $proporcao_cobrado = $request->input('proporcao_cobrado');            // Proporção do valor total a ser cobrado
            $tipoPagto = $request->input('tipoPagto');                            // Tipo de pagamento (ex: 'DN', 'CARTAO', etc.)
            $carrinho_venda = $request->input('carrinho');                        // array de itens
            $check_reembolso = $request->input('check_reembolso', null); // Identifica se o processo é para reembolso
            $dt_primeira_parc = $request->input('dt_primeira_parc');              // Data da primeira parcela
            $parcelas = $request->input('parcelas');                              // Número de parcelas
            $jurosTotal = $request->input('jurosTotal');                          // Juros total da venda
            $tax_categ = $request->input('tax_categ');                            // Categoria da taxa (À Vista / 30 / 60 / 90)
            $regra_parc = $request->input('regra_parc');                          // Regra de parcelamento
            $card_uuid = $request->input('card_uuid');                            // UUID do cartão
            $card_mod = $request->input('card_mod');                              // Modelo do cartão
            $card_tp = $request->input('card_tp');                                // Tipo do cartão

            $vlr_dec_mn = 0;                                                           // Valor de desconto manual
            $vlr_atr_m = 0;                                                            // Valor multa por atraso
            $vlr_atr_j = 0;                                                            // Valor juros por atraso
            $isent_mj = null;                                                          // Isentar Multa e Juros
            $pts_disp_part = 0;                                                        // Pontos disponíveis Programa Particular
            $pts_disp_fraq = 0;                                                        // Pontos disponíveis Programa da Franquia
            $pts_disp_mult = 0;                                                        // Pontos disponíveis Programa Multban
            $pts_disp_cash = 0;                                                        // Pontos disponíveis Programa de Cashback
            $protestado = null;                                                        // Check de Protestado
            $negociacao = null;                                                        // Check de Negociação
            $vlr_acr_mn = 0;                                                           // Valor acréscimo manual
            $vlr_cst_cob = 0;                                                          // Valor custo de cobrança
            $negociacao_obs = null;                                                    // Observação da negociação
            $negociacao_file = null;                                                   // Arquivos da negociação
            $follow_dt = null;                                                         // Data do Follow Up
            $integ_bc = null;                                                          // Código da Integração Bancária
            $data_pgto = null;                                                         // Data do Pagamento do Título
            $meio_pag_t = null;                                                        // Meio de Pagamento do Título
            $nid_parcela_org = null;                                                   // ID da Parcela Original
            $parcela_obs = null;                                                       // Observação da Parcela
            $parcela_ins_pg = null;                                                    // Instrução de Pagamento da Parcela
            $check_ant = null;                                                         // Check Valor Antecipado
            $perct_ant = 0;                                                            // Taxa de Antecipação
            $ant_desc = 0;                                                             // Valor Descontado pela Antecipação
            $pgt_vlr = 0;                                                              // Valor Pago
            $pgt_desc = 0;                                                             // Valor Descontado no Pagamento
            $pgt_mtjr = 0;                                                             // Valor de Multa e Juros do Pagamento
            $vlr_rec = 0;                                                              // Valor Recebido

            $checkout_pago = $request->input('checkout_pago');                    // Valor total pago
            $checkout_troco = $request->input('valortroco');                      // Valor total de troco
            $checkout_descontado = $request->input('checkout_descontado');        // Valor total descontado
            $checkout_resgatado = $request->input('checkout_resgatado');          // Pontos de cashback resgatados
            $checkout_total = $request->input('checkout_total');                  // Valor total a cobrar (subtotal - desconto - cashback)
            $valortotalacobrar = $request->input('valortotalacobrar');            // Valor que o usuário quer cobrar agora
            $vendaSemJuros = $request->input('vendaSemJuros', 0);        // Check de Vendas sem juros
            $valorTotalComJuros = $request->input('valorTotalComJuros');          // Valor total com juros
            $valorParcelaComJuros = $request->input('valorParcelaComJuros');      // Valor da parcela com juros
            $valorParcelaSemJuros = $request->input('valorParcelaSemJuros');      // Valor da parcela sem juros
            $jurosTotalParcela = $request->input('jurosTotalParcela');            // Juros total da parcela
            $card_categ = $request->input('card_categ');                          // Categoria do cartão

            /////////////////////////////////////////////////////////////////////////////
            // CARREGA DADOS DA EMPRESA DO USUÁRIO LOGADO
            $emp_id = $user->emp_id;
            $empresa = Empresa::find($emp_id);

            /////////////////////////////////////////////////////////////////////////////
            // DADOS DOS PARÂMETROS DA EMPRESA
            $empresaParam = EmpresaParam::find($emp_id);
            $emp_destvlr = $empresaParam->emp_destvlr ? $empresaParam->emp_destvlr : 0;                // Data da primeira parcela
            $vlr_pix = $empresaParam->vlr_pix ? $empresaParam->vlr_pix : 0;                            // Valor Pix
            $vlr_boleto = $empresaParam->vlr_boleto ? $empresaParam->vlr_boleto : 0;                   // Valor Boleto
            $tax_blt = $empresaParam->tax_blt ? $empresaParam->tax_blt : 0;                            // Taxa Boleto
            $tax_pre = $empresaParam->tax_pre ? $empresaParam->tax_pre : 0;                            // Taxa Pré
            $tax_gift = $empresaParam->tax_gift ? $empresaParam->tax_gift : 0;                         // Taxa Gift
            $tax_fid = $empresaParam->tax_fid ? $empresaParam->tax_fid : 0;                            // Taxa Fidelidade
            $tax_rebate = $empresaParam->tax_rebate ? $empresaParam->tax_rebate : 0;                   // Taxa Rebate
            $rebate_emp = $empresaParam->rebate_emp ? $empresaParam->rebate_emp : 0;                   // Rebate Empresa
            $tax_royalties = $empresaParam->tax_royalties ? $empresaParam->tax_royalties : 0;          // Taxa Royalties
            $royalties_emp = $empresaParam->royalties_emp ? $empresaParam->royalties_emp : 0;          // Royalties Empresa
            $tax_comiss = $empresaParam->tax_comiss ? $empresaParam->tax_comiss : 0;                   // Taxa de Comissão
            $comiss_emp = $empresaParam->comiss_emp ? $empresaParam->comiss_emp : 0;                   // Empresa Comissionada
            $isnt_pixblt = $empresaParam->isnt_pixblt ? $empresaParam->isnt_pixblt : 0;                // Isenção Pix e Boleto
            $parc_com_jrs = $empresaParam->parc_com_jrs ? $empresaParam->parc_com_jrs : 0;             // Comissão Parcelamento com Juros

            $vlr_bolepix = $empresaParam->vlr_bolepix ? $empresaParam->vlr_bolepix : 0;                // Valor Boleto + Pix
            $pp_particular = $empresaParam->pp_particular ? $empresaParam->pp_particular : 0;          // Pagamento Particular
            $pp_franquia = $empresaParam->pp_franquia ? $empresaParam->pp_franquia : 0;                // Pagamento Franquia
            $pp_mult = $empresaParam->pp_mult ? $empresaParam->pp_mult : 0;                            // Pagamento Multi Cartão
            $pp_cashback = $empresaParam->pp_cashback ? $empresaParam->pp_cashback : 0;                // Pagamento Cashback
            $tax_antmult = $empresaParam->tax_antmult ? $empresaParam->tax_antmult : 0;                // Taxa Antecipação Multi
            $tax_antfundo = $empresaParam->tax_antfundo ? $empresaParam->tax_antfundo : 0;             // Taxa Antecipação Fundo
            $perc_rec_ant = $empresaParam->perc_rec_ant ? $empresaParam->perc_rec_ant : 0;             // Percentual Recebido Antecipação

            /////////////////////////////////////////////////////////////////////////////
            // CALCULA DIAS DE PRAZO, SE HOUVER
            $diasPrazo = null;
            $data_venc = Carbon::today();
            if ($dt_primeira_parc) {
                $dataPrimeira = Carbon::parse($dt_primeira_parc);
                $hoje = Carbon::today();
                $diasPrazo = $hoje->diffInDays($dataPrimeira, false);
                $data_venc = Carbon::parse($dt_primeira_parc);
            }

            /////////////////////////////////////////////////////////////////////////////
            // BUSCA A TAXA DE ACORDO COM A CATEGORIA E PARCELAS
            $taxpos = null;
            if ($tax_categ && $parcelas) {
                $taxpos = EmpresaTaxpos::where('emp_id', $emp_id)
                    ->where('tax_categ', $tax_categ)
                    ->where('parc_de', '<=', $parcelas)
                    ->where('parc_ate', '>=', $parcelas)
                    ->first();
            }
            if ($taxpos) {
                $tax_pos = $taxpos->tax;
            }

            /////////////////////////////////////////////////////////////////////////////
            // VERIFICA LIMITES DO CARTÃO E LIMITE SUGERIDO PARA LIBERAÇÃO DA ANTECIPAÇÃO
            $lib_ant = null;
            if ($card_uuid && $emp_id) {
                $card = ClienteCard::where('emp_id', $emp_id)
                    ->where('card_uuid', $card_uuid)
                    ->where('cliente_id', $cliente_id)
                    ->first();

                if ($card) {
                    $cliente = Cliente::where('cliente_id', $card->cliente_id)->first();
                    if ($cliente) {
                        $card_limite = floatval(str_replace(',', '.', str_replace('.', '', $card->card_limite ?? '0')));
                        $cliente_lmt_sg = floatval(str_replace(',', '.', str_replace('.', '', $cliente->cliente_lmt_sg ?? '0')));
                        if ($card_limite <= $cliente_lmt_sg) {
                            $lib_ant = 'X';
                        }
                    }
                }
            }

            /////////////////////////////////////////////////////////////////////////////
            // SE A EMPRESA FOR WHITE LABEL, BUSCA A COMISSÃO PARA A MULTBAN
            $emp_wlde = $empresa->emp_wlde;
            $emp_comwl = null;
            if ($emp_wlde) {
                $empresaWhiteLabel = Empresa::find($emp_wlde);
                if ($empresaWhiteLabel) {
                    $emp_comwl = $empresaWhiteLabel->emp_comwl;
                }
            }

            /////////////////////////////////////////////////////////////////////////////
            // VARIÁVEIS
            $cdg_multban = 2;

            /////////////////////////////////////////////////////////////////////////////
            // FÓRMULAS
            $user_id = $user->user_id;
            $vlr_brt = $checkout_subtotal * $proporcao_cobrado;
            $qtd_pts_utlz = $checkout_cashback * $proporcao_cobrado;
            $qtd_pts_utlz_parcela = $qtd_pts_utlz / $parcelas;
            $perc_pts_utlz = $vlr_brt > 0 ? ($qtd_pts_utlz / $vlr_brt) * 100 : 0;
            $vlr_btot = $vlr_brt - $qtd_pts_utlz;
            $vlr_btot_parcela = $vlr_btot / $parcelas;
            $vlr_dec = $checkout_desconto * $proporcao_cobrado;
            $vlr_dec_parcela = $vlr_dec / $parcelas;
            $perc_desc = $vlr_brt > 0 ? ($vlr_dec / $vlr_brt) * 100 : 0;
            $vlr_btot_split = $vlr_btot - $vlr_dec - $vlr_dec_mn;
            $vlr_btot_split_parcela = $vlr_btot_split / $parcelas;
            $taxa_bacen = ($vlr_btot_split_parcela <= $isnt_pixblt) ? ($vlr_pix + $vlr_boleto) : 0;

            $perc_juros = $vlr_brt > 0 ? ($jurosTotal / $vlr_brt) * 100 : 0;
            $vlr_juros = $jurosTotal;
            $vlr_juros_parcela = $vlr_juros / $parcelas;
            $vlr_btot_cj = $vlr_btot_split + $vlr_juros;
            $vlr_btot_cj_parcela = $vlr_btot_cj / $parcelas;

            /////////////////////////////////////////////////////////////////////////////
            // TAXA ADMINISTRATIVA
            $tax_adm = 0;
            if ($tipoPagto === 'CM') {
                if ($card_tp === 'PRE') {
                    $tax_adm = $tax_pre;
                } elseif ($card_mod === 'CRDT') {
                    $tax_adm = $tax_pos;
                } elseif ($card_mod === 'GIFT') {
                    $tax_adm = $tax_gift;
                } elseif ($card_mod === 'FIDL') {
                    $tax_adm = $tax_fid;
                }
            } elseif ($tipoPagto === 'BL') {
                $tax_adm = $tax_blt;
            } elseif (in_array($tipoPagto, ['DN', 'PX', 'OT'], true)) {
                $tax_adm = 0;
            }

            /////////////////////////////////////////////////////////////////////////////
            // STATUS DOS LANÇAMENTOS DOS TÍTULOS ABERTOS E COMPENSADOS
            if (in_array($tipoPagto, ['CM', 'BL'], true)) {
                $parcela_sts = 'REG';
            } else {
                $parcela_sts = 'BXD';
            }

            /////////////////////////////////////////////////////////////////////////////
            ///////////////// INÍCIO DO PROCESSO DE GRAVAÇÃO DAS VENDAS /////////////////
            /////////////////////////////////////////////////////////////////////////////

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

            // ////////////////////////////////////////
            // GRAVA OS DADOS NA TABELA TBTR_H_TITULOS
            // ////////////////////////////////////////
            $data = [
                'emp_id'  => $emp_id,
                'user_id' => $user_id,
                // 'titulo' => $titulo, --- IGNORE ---
                'nsu_titulo'     => $nsu_titulo,
                'qtd_parc'       => $parcelas ? $parcelas : 1,
                'primeira_para'  => $regra_parc ? $regra_parc : 1,
                'cnd_pag'        => ($parcelas > 1) ? 2 : 1,
                'cliente_id'     => $cliente_id,
                'meio_pag_v'     => $tipoPagto,
                'card_uuid'      => $card_uuid,
                'data_mov'       => now(),
                'nsu_autoriz'    => $nsu_autoriz,
                'check_reemb'    => $check_reembolso,
                'lib_ant'        => $lib_ant,
                'vlr_brt'        => $vlr_brt,
                'tax_adm'        => $tax_adm,
                'tax_rebate'     => $tax_rebate,
                'tax_royalties'  => $tax_royalties,
                'tax_comissao'   => $tax_comiss,
                'qtd_pts_utlz'   => $qtd_pts_utlz,
                'perc_pts_utlz'  => $perc_pts_utlz,
                'vlr_btot'       => $vlr_btot,
                'perc_desc'      => $perc_desc,
                'vlr_dec'        => $vlr_dec,
                'vlr_dec_mn'     => $vlr_dec_mn,
                'vlr_btot_split' => $vlr_btot_split,
                'perc_juros'     => $perc_juros,
                'vlr_juros'      => $vlr_juros,
                'vlr_btot_cj'    => $vlr_btot_cj,
                'vlr_atr_m'      => $vlr_atr_m,
                'vlr_atr_j'      => $vlr_atr_j,
                'vlr_acr_mn'     => $vlr_acr_mn,
                'criador'        => $user_id,
                'dthr_cr'        => now(),
                'modificador'    => $user_id,
                'dthr_ch'        => now(),
            ];

            if ($tituloRequest) {
                $data['titulo'] = $tituloRequest;
            }

            $hTitulo = new TbtrHTitulos($data);
            $hTitulo->save();
            $titulo = $hTitulo->titulo;

            // //////////////////////////////////////////////////////////////////////////////////////
            // GRAVA OS DADOS NAS TABELAS DE FATURA E PARCELAS
            // //////////////////////////////////////////////////////////////////////////////////////
            for ($parcela = 1; $parcela <= $parcelas; $parcela++) {

                // as demais parcelas são incrementadas de 1 mês
                if ($parcela > 1) {
                    $data_venc = $data_venc->copy()->addMonthNoOverflow();
                }

                // TBTR_F_TITULOS - Somente para cartão de crédito
                // Verifica se já existe fatura para o cliente, cartão e data de vencimento

                $id_fatura = null;
                if (in_array($tipoPagto, ['CM'], true)) {
                    if ($emp_id && $cliente_id && $card_uuid && $data_venc) {

                        $fatura = TbtrFTitulos::where('emp_id', $emp_id)
                            ->where('cliente_id', $cliente_id)
                            ->where('card_uuid', $card_uuid)
                            ->where('fatura_sts', 1)
                            ->where('data_venc', $data_venc)
                            ->first();

                        // se já existir, usa o id_fatura existente e alterar o valor total
                        if ($fatura) {
                            $id_fatura = $fatura->id_fatura;

                            $vlr_fatura_gravada = floatval($fatura->vlr_tot ?? 0);
                            $vlr_fatura_novo = floatval($vlr_btot_cj_parcela ?? 0);

                            // Atualiza o valor da fatura existente
                            $novo_vlr_tot = $vlr_fatura_gravada + $vlr_fatura_novo;

                            $fatura->vlr_tot = $novo_vlr_tot;
                            if (property_exists($fatura, 'modificador')) {
                                $fatura->modificador = $user_id;
                            }
                            if (property_exists($fatura, 'dthr_ch')) {
                                $fatura->dthr_ch = now();
                            }
                            $fatura->save();

                        // se não existir, cria uma nova fatura
                        } else {
                            // cria nova fatura
                            $id_fatura = (string) Str::uuid();
                            $fatura = new TbtrFTitulos([
                                'emp_id'      => $emp_id,
                                'id_fatura'   => $id_fatura,
                                'cliente_id'  => $cliente_id,
                                'card_uuid'   => $card_uuid,
                                'integ_bc'    => null,
                                'fatura_sts'  => 1,
                                'data_fech'   => ($data_venc instanceof Carbon)
                                                ? $data_venc->copy()->subDays(10)
                                                : Carbon::parse($data_venc)->subDays(10),
                                'data_venc'   => $data_venc,
                                'data_pgto'   => null,
                                'vlr_tot'     => $vlr_btot_cj_parcela ?? 0,
                                'vlr_pgto'    => 0,
                                'criador'     => $user_id,
                                'dthr_cr'     => now(),
                                'modificador' => $user_id,
                                'dthr_ch'     => now(),
                            ]);
                            $fatura->save();

                        }
                    }
                }

                // TBTR_P_TITULOS_AB
                // TBTR_P_TITULOS_CP
                $nid_parcela = Str::uuid();

                $dataParcela = [
                    'emp_id'      => $emp_id,
                    'user_id'     => $user_id,
                    'titulo'      => $titulo,
                    'nsu_titulo'  => $nsu_titulo,
                    'nsu_autoriz' => $nsu_autoriz,
                    'qtd_parc'    => $parcelas,
                    'primeira_para' => $regra_parc ? $regra_parc : 1,
                    'cnd_pag'      => ($parcelas > 1) ? 2 : 1,
                    'cliente_id'   => $cliente_id,
                    'meio_pag_v'   => $tipoPagto,
                    'data_mov'    => now(),
                    'parcela'     => $parcela,
                    'nid_parcela' => $nid_parcela,
                    'data_venc'   => $data_venc,
                    'parcela_sts' => $parcela_sts,
                    'destvlr'     => $emp_destvlr,
                    'card_uuid'   => $card_uuid,
                    'id_fatura'   => $id_fatura,
                    'integ_bc'    => $integ_bc,
                    'data_pgto'   => $data_pgto,
                    'meio_pag_t'  => $meio_pag_t,
                    'nid_parcela_org' => $nid_parcela_org,
                    'parcela_obs' => $parcela_obs,
                    'parcela_ins_pg' => $parcela_ins_pg,
                    'qtd_pts_utlz' => $qtd_pts_utlz_parcela,
                    'tax_bacen'   => $taxa_bacen,
                    'vlr_dec'     => $vlr_dec_parcela,
                    'vlr_dec_mn'  => $vlr_dec_mn,
                    'vlr_bpar_split' => $vlr_btot_split_parcela,
                    'vlr_jurosp'  => $vlr_juros_parcela,
                    'vlr_bpar_cj' => $vlr_btot_cj_parcela,
                    'vlr_atr_m'   => $vlr_atr_m,
                    'vlr_atr_j'   => $vlr_atr_j,
                    'isent_mj'    => $isent_mj,
                    'protestado'  => $protestado,
                    'negociacao'  => $negociacao,
                    'vlr_acr_mn'  => $vlr_acr_mn,
                    'vlr_cst_cob' => $vlr_cst_cob,
                    'negociacao_obs' => $negociacao_obs,
                    'negociacao_file' => $negociacao_file,
                    'follow_dt' => $follow_dt,
                    'check_ant' => $check_ant,
                    'perct_ant' => $perct_ant,
                    'ant_desc' => $ant_desc,
                    'pgt_vlr' => $pgt_vlr,
                    'pgt_desc' => $pgt_desc,
                    'pgt_mtjr' => $pgt_mtjr,
                    'vlr_rec' => $vlr_rec,
                    'pts_disp_part' => $pts_disp_part,
                    'pts_disp_fraq' => $pts_disp_fraq,
                    'pts_disp_mult' => $pts_disp_mult,
                    'pts_disp_cash' => $pts_disp_cash,
                    'criador'        => $user_id,
                    'dthr_cr'        => now(),
                    'modificador'    => $user_id,
                    'dthr_ch'        => now(),
                ];

                if (in_array($tipoPagto, ['CM', 'BL'], true)) {
                    $hParcela = new TbtrPTitulosAb($dataParcela);
                    $hParcela->save();

                } else {
                    $hParcela = new TbtrPTitulosCp($dataParcela);
                    $hParcela->save();
                }

                // //////////////////////////////////////////////////////////////////////////////////////
                // GRAVA OS DADOS NA TABELA TBTR_I_TITULOS
                //          Para cada item no carrinho
                //          Apenas para a primeira parcela
                // GRAVA OS DADOS NA TABELA TBTR_S_TITULOS
                //          Para cada item no carrinho
                // //////////////////////////////////////////////////////////////////////////////////////
                $item_seq = 1;

                foreach ($carrinho_venda as $item) {
                    $data = [];

                    $produto_tipo = $item['produto_tipo'];
                    $produto_id = $item['produto_id'];
                    $qtd_item = $item['qtd_item'];
                    $vlr_unit_item = $item['vlr_unit_item'];
                    $perc_toti = $item['proporcao_item'];
                    $vlr_brt_item = $item['vlr_brut_item'] * $proporcao_cobrado;
                    $vlr_dec_item = $item['vlr_desc_item'] * $proporcao_cobrado;
                    $vlr_dec_mn = 0;

                    $vlr_base_item = $vlr_brt_item - $vlr_dec_item - $vlr_dec_mn;
                    $qtd_pts_utlz_item = $qtd_pts_utlz * $perc_toti;

                    $vlr_split_item = ($vlr_base_item - $qtd_pts_utlz_item);
                    $vlr_bpar_split_item = $vlr_split_item / $parcelas;

                    $vlr_j_item = ($jurosTotal * $perc_toti);
                    $vlr_jpar_item = ($vlr_j_item / $parcelas);

                    $vlr_split_cj_item = $vlr_split_item + $vlr_j_item;
                    $vlr_bpar_cj_item = $vlr_bpar_split_item + $vlr_jpar_item;

                    // VALOR RECEBIDO
                    if ($tipoPagto === 'CM') {
                        $vlr_rec = 0;
                    } elseif ($tipoPagto === 'BL') {
                        $vlr_rec = 0;
                    } elseif ($tipoPagto === 'DN') {
                        $vlr_rec = $vlr_bpar_cj_item;
                    } elseif ($tipoPagto === 'PX') {
                        $vlr_rec = $vlr_bpar_cj_item;
                    } elseif ($tipoPagto === 'OT') {
                        $vlr_rec = $vlr_bpar_cj_item;
                    }

                    $vlr_atrm_item = $vlr_atr_m * $perc_toti;
                    $vlr_atrj_item = $vlr_atr_j * $perc_toti;
                    $vlr_acr_mn = 0;
                    $ant_desc_item = $ant_desc * $perc_toti;
                    $pgt_vlr_item = $pgt_vlr * $perc_toti;
                    $pgt_desc_item = $pgt_desc *  $perc_toti;
                    $pgt_mtjr_item = $pgt_mtjr * $perc_toti;
                    $vlr_rec_item = $vlr_rec * $perc_toti;
                    $pts_disp_part_item = $pts_disp_part * $perc_toti;
                    $pts_disp_fraq_item = $pts_disp_fraq * $perc_toti;
                    $pts_disp_mult_item = $pts_disp_mult * $perc_toti;
                    $pts_disp_cash_item = $pts_disp_cash * $perc_toti;

                    // ///////////////
                    // TBTR_I_TITULOS
                    if ($parcela == 1) {

                        $data = [
                            'emp_id'              => $emp_id,
                            'user_id'             => $user_id,
                            'titulo'              => $titulo,
                            'nsu_titulo'          => $nsu_titulo,
                            'nsu_autoriz'         => $nsu_autoriz,
                            'item'                => $item_seq,
                            'produto_tipo'        => $produto_tipo,
                            'produto_id'          => $produto_id,

                            'qtd_item'            => $qtd_item,
                            'vlr_unit_item'       => $vlr_unit_item,
                            'vlr_brt_item'        => $vlr_brt_item,
                            'perc_toti'           => $perc_toti,
                            'qtd_pts_utlz_item'   => $qtd_pts_utlz_item,
                            'vlr_base_item'       => $vlr_base_item,
                            'vlr_dec_item'        => $vlr_dec_item,
                            'vlr_dec_mn'          => $vlr_dec_mn,
                            'vlr_bsplit_item'     => $vlr_split_item,
                            'vlr_bjrs_item'       => $vlr_j_item,
                            'vlr_bsplit_cj_item'  => $vlr_split_cj_item,
                            'vlr_atrm_item'       => $vlr_atrm_item,
                            'vlr_atrj_item'       => $vlr_atrj_item,
                            'vlr_acr_mn'          => $vlr_acr_mn,
                            'ant_desc'            => $ant_desc_item,
                            'pgt_vlr'             => $pgt_vlr_item,
                            'pgt_desc'            => $pgt_desc_item,
                            'pgt_mtjr'            => $pgt_mtjr_item,
                            'vlr_rec'             => $vlr_rec_item,
                            'card_pts_part'       => $pts_disp_part_item,
                            'card_pts_fraq'       => $pts_disp_fraq_item,
                            'card_pts_mult'       => $pts_disp_mult_item,
                            'card_pts_cash'       => $pts_disp_cash_item,
                            'criador'             => $user_id,
                            'dthr_cr'             => now(),
                            'modificador'         => $user_id,
                            'dthr_ch'             => now(),
                        ];

                        $iTitulo = new TbtrITitulos($data);
                        $iTitulo->save();
                    }

                    ///////////////////////////////////
                    // TBTR_S_TITULOS - CARTÃO E BOLETO
                    if (in_array($tipoPagto, ['CM', 'BL'], true)) {

                        // TAXA BACEN
                        $vlr_bacen = 0;
                        if ($taxa_bacen > 0) {

                                // Desconta Taxa Bacen da empresa
                                $data = [
                                    'emp_id' => $emp_id,
                                    'user_id' => $user_id,
                                    'titulo' => $titulo,
                                    'nsu_titulo' => $nsu_titulo,
                                    'nsu_autoriz' => $nsu_autoriz,
                                    'parcela' => $parcela,
                                    'produto_id' => $produto_id,
                                    'lanc_tp' => 'TAXA_BAC',
                                    'recebedor' => $emp_id,

                                    'tax_adm' => 0,
                                    'vlr_plan' => -abs(floatval($taxa_bacen)),
                                    'perc_real' => 0,
                                    'vlr_real' => 0,
                                    'criador' => $user_id,
                                    'dthr_cr' => now(),
                                    'modificador' => $user_id,
                                    'dthr_ch' => now(),
                                ];

                                $sTitulo = new TbtrSTitulos($data);
                                $sTitulo->save();

                                // Acrescenta Taxa Bacen para Multban
                                $data = [
                                    'emp_id' => $emp_id,
                                    'user_id' => $user_id,
                                    'titulo' => $titulo,
                                    'nsu_titulo' => $nsu_titulo,
                                    'nsu_autoriz' => $nsu_autoriz,
                                    'parcela' => $parcela,
                                    'produto_id' => $produto_id,
                                    'lanc_tp' => 'TAXA_BAC',
                                    'recebedor' => $cdg_multban,

                                    'tax_adm' => 0,
                                    'vlr_plan' => $taxa_bacen,
                                    'perc_real' => 0,
                                    'vlr_real' => 0,
                                    'criador' => $user_id,
                                    'dthr_cr' => now(),
                                    'modificador' => $user_id,
                                    'dthr_ch' => now(),
                                ];

                                $sTitulo = new TbtrSTitulos($data);
                                $sTitulo->save();

                            }

                        // CALCULO DE TAXA ADMINISTRATIVA
                        $vlr_tax = 0;
                        if ($tax_adm > 0) {
                            $vlr_tax = $vlr_bpar_split_item * $tax_adm / 100;

                            // CALCULO DE REBATE
                            $vlr_rebate = 0;
                            if ($rebate_emp && $tax_rebate && $tax_adm) {
                                $tax_adm_rebate = $tax_adm - ($tax_adm * $tax_rebate / 100);
                                $vlr_rebate = $vlr_tax * $tax_rebate / 100;
                                $tax_adm = $tax_adm - $tax_adm_rebate;
                                $vlr_tax = $vlr_tax - $vlr_rebate;

                                // Acrescenta Rebate para a Empresa
                                $data = [
                                    'emp_id' => $emp_id,
                                    'user_id' => $user_id,
                                    'titulo' => $titulo,
                                    'nsu_titulo' => $nsu_titulo,
                                    'nsu_autoriz' => $nsu_autoriz,
                                    'parcela' => $parcela,
                                    'produto_id' => $produto_id,
                                    'lanc_tp' => 'VLR_REBT',
                                    'recebedor' => $rebate_emp,

                                    'tax_adm' => $tax_adm_rebate,
                                    'vlr_plan' => $vlr_rebate,
                                    'perc_real' => 0,
                                    'vlr_real' => 0,
                                    'criador' => $user_id,
                                    'dthr_cr' => now(),
                                    'modificador' => $user_id,
                                    'dthr_ch' => now(),
                                ];

                                $sTitulo = new TbtrSTitulos($data);
                                $sTitulo->save();

                            }

                            // Acrescenta Taxa Adm para Multban
                            $data = [
                                'emp_id' => $emp_id,
                                'user_id' => $user_id,
                                'titulo' => $titulo,
                                'nsu_titulo' => $nsu_titulo,
                                'nsu_autoriz' => $nsu_autoriz,
                                'parcela' => $parcela,
                                'produto_id' => $produto_id,
                                'lanc_tp' => 'TAXA_ADM',
                                'recebedor' => $cdg_multban,

                                'tax_adm' => $tax_adm,
                                'vlr_plan' => $vlr_tax,
                                'perc_real' => 0,
                                'vlr_real' => 0,
                                'criador' => $user_id,
                                'dthr_cr' => now(),
                                'modificador' => $user_id,
                                'dthr_ch' => now(),
                            ];

                            $sTitulo = new TbtrSTitulos($data);
                            $sTitulo->save();
                        }

                        // CALCULO DE ROYALTIES
                        $vlr_royalties = 0;
                        if ($royalties_emp && $tax_royalties) {
                            $vlr_royalties = ($vlr_bpar_split_item - $vlr_tax - $vlr_rebate) * $tax_royalties / 100;

                            // Acrescenta Royalties para a Empresa
                            $data = [
                                'emp_id' => $emp_id,
                                'user_id' => $user_id,
                                'titulo' => $titulo,
                                'nsu_titulo' => $nsu_titulo,
                                'nsu_autoriz' => $nsu_autoriz,
                                'parcela' => $parcela,
                                'produto_id' => $produto_id,
                                'lanc_tp' => 'VLR_ROTS',
                                'recebedor' => $royalties_emp,

                                'tax_adm' => $tax_royalties,
                                'vlr_plan' => $vlr_royalties,
                                'perc_real' => 0,
                                'vlr_real' => 0,
                                'criador' => $user_id,
                                'dthr_cr' => now(),
                                'modificador' => $user_id,
                                'dthr_ch' => now(),
                            ];

                            $sTitulo = new TbtrSTitulos($data);
                            $sTitulo->save();
                        }

                        // CALCULO DE COMISSÃO DE VENDAS
                        $vlr_comissao = 0;
                        if ($comiss_emp && $tax_comiss) {
                            $vlr_comissao = ($vlr_bpar_split_item - $vlr_tax - $vlr_rebate) * $tax_comiss / 100;

                            // Acrescenta Comissão para a Empresa
                            $data = [
                                'emp_id' => $emp_id,
                                'user_id' => $user_id,
                                'titulo' => $titulo,
                                'nsu_titulo' => $nsu_titulo,
                                'nsu_autoriz' => $nsu_autoriz,
                                'parcela' => $parcela,
                                'produto_id' => $produto_id,
                                'lanc_tp' => 'VLR_CMIS',
                                'recebedor' => $comiss_emp,

                                'tax_adm' => $tax_comiss,
                                'vlr_plan' => $vlr_comissao,
                                'perc_real' => 0,
                                'vlr_real' => 0,
                                'criador' => $user_id,
                                'dthr_cr' => now(),
                                'modificador' => $user_id,
                                'dthr_ch' => now(),
                            ];

                            $sTitulo = new TbtrSTitulos($data);
                            $sTitulo->save();
                        }

                        // CALCULO DE COMISSÃO DE FUNCIONARIO
                        $vlr_comissao_func = 0;
                        if ( $user_comis && $user_pcomis) {
                            $vlr_comissao_func = ($vlr_bpar_split_item - $vlr_tax - $vlr_rebate) * $user_pcomis / 100;

                            // Acrescenta Comissão para a Funcionário
                            $data = [
                                'emp_id' => $emp_id,
                                'user_id' => $user_id,
                                'titulo' => $titulo,
                                'nsu_titulo' => $nsu_titulo,
                                'nsu_autoriz' => $nsu_autoriz,
                                'parcela' => $parcela,
                                'produto_id' => $produto_id,
                                'lanc_tp' => 'VLR_CMIS',
                                'recebedor' => $user_comis,

                                'tax_adm' => $user_pcomis,
                                'vlr_plan' => $vlr_comissao_func,
                                'perc_real' => 0,
                                'vlr_real' => 0,
                                'criador' => $user_id,
                                'dthr_cr' => now(),
                                'modificador' => $user_id,
                                'dthr_ch' => now(),
                            ];

                            $sTitulo = new TbtrSTitulos($data);
                            $sTitulo->save();
                        }

                        // REPASSE PARA A EMPRESA
                        $vlr_plan_repasse = $vlr_bpar_split_item - $vlr_tax - $vlr_rebate - $vlr_royalties - $vlr_comissao - $vlr_comissao_func;
                        if ($vlr_plan_repasse > 0) {

                            // Acrescenta Comissão para a Funcionário
                            $data = [
                                'emp_id' => $emp_id,
                                'user_id' => $user_id,
                                'titulo' => $titulo,
                                'nsu_titulo' => $nsu_titulo,
                                'nsu_autoriz' => $nsu_autoriz,
                                'parcela' => $parcela,
                                'produto_id' => $produto_id,
                                'lanc_tp' => 'VLR_VNDA',
                                'recebedor' => $emp_id,

                                'tax_adm' => 0,
                                'vlr_plan' => $vlr_plan_repasse,
                                'perc_real' => 0,
                                'vlr_real' => 0,
                                'criador' => $user_id,
                                'dthr_cr' => now(),
                                'modificador' => $user_id,
                                'dthr_ch' => now(),
                            ];

                            $sTitulo = new TbtrSTitulos($data);
                            $sTitulo->save();
                        }

                        // JUROS DA VENDA
                        if ($vlr_jpar_item > 0) {

                            $vlr_juros_empresa = $vlr_jpar_item * $parc_com_jrs / 100;
                            $vlr_juros_multban = $vlr_jpar_item - $vlr_juros_empresa;

                            // Acrescenta Valor dos Juros para a Empresa
                            $data = [
                                'emp_id' => $emp_id,
                                'user_id' => $user_id,
                                'titulo' => $titulo,
                                'nsu_titulo' => $nsu_titulo,
                                'nsu_autoriz' => $nsu_autoriz,
                                'parcela' => $parcela,
                                'produto_id' => $produto_id,
                                'lanc_tp' => 'DIV_JURS',
                                'recebedor' => $emp_id,

                                'tax_adm' => 0,
                                'vlr_plan' => $vlr_juros_empresa,
                                'perc_real' => 0,
                                'vlr_real' => 0,
                                'criador' => $user_id,
                                'dthr_cr' => now(),
                                'modificador' => $user_id,
                                'dthr_ch' => now(),
                            ];

                            $sTitulo = new TbtrSTitulos($data);
                            $sTitulo->save();

                            // Acrescenta Valor dos Juros para a Multban
                            $data = [
                                'emp_id' => $emp_id,
                                'user_id' => $user_id,
                                'titulo' => $titulo,
                                'nsu_titulo' => $nsu_titulo,
                                'nsu_autoriz' => $nsu_autoriz,
                                'parcela' => $parcela,
                                'produto_id' => $produto_id,
                                'lanc_tp' => 'DIV_JURS',
                                'recebedor' => $cdg_multban,

                                'tax_adm' => 0,
                                'vlr_plan' => $vlr_juros_multban,
                                'perc_real' => 0,
                                'vlr_real' => 0,
                                'criador' => $user_id,
                                'dthr_cr' => now(),
                                'modificador' => $user_id,
                                'dthr_ch' => now(),
                            ];

                            $sTitulo = new TbtrSTitulos($data);
                            $sTitulo->save();
                        }

                    ///////////////////////////////////
                    // TBTR_S_TITULOS - DINHEIRO
                    } else if ($tipoPagto === 'DN') {
                        $data = [
                            'emp_id' => $emp_id,
                            'user_id' => $user_id,
                            'titulo' => $titulo,
                            'nsu_titulo' => $nsu_titulo,
                            'nsu_autoriz' => $nsu_autoriz,
                            'parcela' => $parcela,
                            'produto_id' => $produto_id,
                            'lanc_tp' => 'DINHEIRO',
                            'recebedor' => $emp_id,

                            'tax_adm' => 0,
                            'vlr_plan' => $vlr_bpar_split_item,
                            'perc_real' => 100,
                            'vlr_real' => $vlr_rec,
                            'criador' => $user_id,
                            'dthr_cr' => now(),
                            'modificador' => $user_id,
                            'dthr_ch' => now(),
                        ];

                        $sTitulo = new TbtrSTitulos($data);
                        $sTitulo->save();

                    ///////////////////////////////////
                    // TBTR_S_TITULOS - PIX
                    } else if ($tipoPagto === 'PX') {
                        $vlr_plan_repasse = $vlr_bpar_split_item - $vlr_pix;
                        $vlr_real_repasse = $vlr_rec - $vlr_pix;

                        // SE A EMPRESA LOGADA FOR WHITE LABEL, PRECISAMOS PROPORCIONAR OS VALORES
                        if ($emp_wlde && $emp_comwl) {
                            $vlr_pix = $vlr_pix * $emp_comwl / 100;
                            $vlr_pix_wl = $vlr_pix * (100 - $emp_comwl);

                            // VALORES DA EMPRESA WHITE LABEL
                            $data = [
                                'emp_id' => $emp_id,
                                'user_id' => $user_id,
                                'titulo' => $titulo,
                                'nsu_titulo' => (string) $nsu_titulo,
                                'nsu_autoriz' => (string) $nsu_autoriz,
                                'parcela' => $parcela,
                                'produto_id' => $produto_id,
                                'lanc_tp' => 'TAX_BAC',
                                'recebedor' => $emp_wlde,

                                'tax_adm' => 0,
                                'vlr_plan' => $vlr_pix_wl,
                                'perc_real' => 100,
                                'vlr_real' => $vlr_pix_wl,
                                'criador' => $user_id,
                                'dthr_cr' => now(),
                                'modificador' => $user_id,
                                'dthr_ch' => now(),
                            ];

                            $sTitulo = new TbtrSTitulos($data);
                            $sTitulo->save();
                        }

                        // VALOR DE REPASSE PARA A EMPRESA
                        $data = [
                            'emp_id' => $emp_id,
                            'user_id' => $user_id,
                            'titulo' => $titulo,
                            'nsu_titulo' => (string) $nsu_titulo,
                            'nsu_autoriz' => (string) $nsu_autoriz,
                            'parcela' => $parcela,
                            'produto_id' => $produto_id,
                            'lanc_tp' => 'VLR_VNDA',
                            'recebedor' => $emp_id,

                            'tax_adm' => 0,
                            'vlr_plan' => $vlr_plan_repasse,
                            'perc_real' => 100,
                            'vlr_real' => $vlr_real_repasse,
                            'criador' => $user_id,
                            'dthr_cr' => now(),
                            'modificador' => $user_id,
                            'dthr_ch' => now(),
                        ];

                        $sTitulo = new TbtrSTitulos($data);
                        $sTitulo->save();

                        // VALOR DA MULTBAN
                        $data = [
                            'emp_id' => $emp_id,
                            'user_id' => $user_id,
                            'titulo' => $titulo,
                            'nsu_titulo' => (string) $nsu_titulo,
                            'nsu_autoriz' => (string) $nsu_autoriz,
                            'parcela' => $parcela,
                            'produto_id' => $produto_id,
                            'lanc_tp' => 'TAX_BAC',
                            'recebedor' => $cdg_multban,

                            'tax_adm' => 0,
                            'vlr_plan' => $vlr_pix,
                            'perc_real' => 100,
                            'vlr_real' => $vlr_pix,
                            'criador' => $user_id,
                            'dthr_cr' => now(),
                            'modificador' => $user_id,
                            'dthr_ch' => now(),
                        ];

                        $sTitulo = new TbtrSTitulos($data);
                        $sTitulo->save();

                    ///////////////////////////////////
                    // TBTR_S_TITULOS - OUTROS
                    } else if ($tipoPagto === 'OT') {
                        $data = [
                            'emp_id' => $emp_id,
                            'user_id' => $user_id,
                            'titulo' => $titulo,
                            'nsu_titulo' => (string) $nsu_titulo,
                            'nsu_autoriz' => (string) $nsu_autoriz,
                            'parcela' => $parcela,
                            'produto_id' => $produto_id,
                            'lanc_tp' => 'OUTROS',
                            'recebedor' => $emp_id,

                            'tax_adm' => 0,
                            'vlr_plan' => $vlr_bpar_split_item,
                            'perc_real' => 100,
                            'vlr_real' => $vlr_rec,
                            'criador' => $user_id,
                            'dthr_cr' => now(),
                            'modificador' => $user_id,
                            'dthr_ch' => now(),
                        ];

                        $sTitulo = new TbtrSTitulos($data);
                        $sTitulo->save();

                    }

                    // INCREMENTA O SEQUENCIAL DOS ITENS
                    $item_seq++;
                }

            // FIM DO LOOP DAS PARCELAS
            }

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

        if (! $titulo) {
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

    /**
     * Remove the specified resource from storage.
     */
    public function pdv()
    {
        //
    }

    //////////////////////////////////////////////////////////////////////////////////////////////
    // CALCULA AS PARCELAS DE UMA VENDA
    public function calcularParcelas(Request $request)
    {
        // loga a chegada da requisição (ajuda a depurar)
        try {
            $user = Auth::user();
        } catch (\Throwable $e) {
            // se logging falhar não interrompe o fluxo
            Log::warning('calcularParcelas: falha ao logar params', ['err' => $e->getMessage()]);
        }

        if (! $user) {
            return response()->json(['success' => false, 'error' => 'Usuário não autenticado'], 401);
        }

        $emp_id = $user->emp_id;
        $empresaParam = EmpresaParam::find($emp_id);

        $tipo = $request->input('tipo', 'card'); // 'card' or 'boleto'
        $valorText = $request->input('valortotalacobrar') ?? $request->input('total') ?? '0';
        $totalVenda = $this->parseBRLPhp($valorText);

        // limite de parcelas (prioriza empresaParam)
        $parclib = 1;
        if ($empresaParam && $tipo === 'card' && $empresaParam->card_posparc) {
            $parclib = intval($empresaParam->card_posparc);
        } elseif ($empresaParam && $tipo === 'boleto' && $empresaParam->blt_parclib) {
            $parclib = intval($empresaParam->blt_parclib);
        } else {
            $parclib = intval($request->input('parclib', 1));
        }
        if ($parclib <= 0) $parclib = 1;

        // parâmetros de juros (apenas usados para cartão)
        $parc_cjuros_flag = false;
        $parc_jr_deprc_val = 0;
        $tax_jrsparc_val = 0;
        if ($empresaParam) {
            $parc_cjuros_flag = !! $empresaParam->parc_cjuros;
            $parc_jr_deprc_val = intval(preg_replace('/\D/', '', (string) ($empresaParam->parc_jr_deprc ?? '0'))) ?: 0;
            $tax_raw = $empresaParam->tax_jrsparc ?? 0;
            if (is_string($tax_raw)) $tax_raw = str_replace(',', '.', $tax_raw);
            $tax_jrsparc_val = floatval($tax_raw) ?: 0;
        }

        $vendaSemJuros = $request->input('vendaSemJuros', 0);
        $options = [];

        for ($i = 1; $i <= $parclib; $i++) {
            $parcelaValor = 0.0;
            $descricaoJuros = '';
            $totalVendaComJuros = 0.0;
            $vlr_tot_juros = 0.0;
            $vlr_tot_parc = 0.0;

            if ($tipo === 'card' && $parc_cjuros_flag && $parc_jr_deprc_val > 0 && $i >= $parc_jr_deprc_val && ! (bool) $vendaSemJuros) {
                // calcula juros
                $perc_tot_juros = ($i * $tax_jrsparc_val) / 100.0;
                $vlr_tot_juros = $totalVenda * $perc_tot_juros;
                $totalVendaComJuros = $totalVenda + $vlr_tot_juros;
                $vlr_tot_juros_parc = $vlr_tot_juros / max(1, $i);
                $vlr_tot_parc = $totalVenda / max(1, $i);
                $parcelaValor = $totalVendaComJuros / max(1, $i);
                $descricaoJuros = ' - com juros';
            } else {
                $parcelaValor = $i > 0 ? ($totalVenda / $i) : $totalVenda;
                $totalVendaComJuros = 0.0;
                $vlr_tot_juros = 0.0;
                $vlr_tot_parc = $totalVenda / max(1, $i);
            }

            $parcelaValorFormatado = $this->formatBRLPhp($parcelaValor);
            $numParcela = str_pad((string) $i, 2, '0', STR_PAD_LEFT);

            $options[] = [
                'value' => $i,
                'data' => [
                    'parcelas' => $numParcela,
                    'total_venda_com_juros' => number_format($totalVendaComJuros, 2, '.', ''),
                    'total_juros' => number_format($vlr_tot_juros, 2, '.', ''),
                    'valor_parcela' => number_format($vlr_tot_parc, 2, '.', ''),
                    'valor_parcela_com_juros' => number_format($parcelaValor, 2, '.', ''),
                ],
                'label' => "{$numParcela} X R$ {$parcelaValorFormatado}{$descricaoJuros}",
            ];
        }

        return response()->json(['success' => true, 'options' => $options]);
    }

    /**
     * Converte string BRL ('1.234,56' ou '1234,56' ou 'R$ 1.234,56') para float
     */
    private function parseBRLPhp($str)
    {
        if ($str === null || $str === '') return 0.0;
        // remove R$ e espaços
        $s = preg_replace('/R\$|\s+/', '', (string) $str);
        // se houver vírgula, normaliza: remove pontos de milhar e troca vírgula por ponto
        if (strpos($s, ',') !== false) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            // nenhum separador decimal - remove pontos (milhar) apenas
            $s = str_replace('.', '', $s);
        }
        return floatval($s);
    }

    /**
     * Formata número para padrão BRL (ex: 1234.5 => '1.234,50')
     */
    private function formatBRLPhp($valor)
    {
        return number_format(floatval($valor), 2, ',', '.');
    }

    //////////////////////////////////////////////////////////////////////////////////////////////
    // RENDERIZA O HTML DA TABELA DE RESGATE DE PONTOS
    protected function renderResgateTableHtml(array $cartoes): string
    {
        $html = '';

        foreach ($cartoes as $cartao) {
            $id = isset($cartao['id']) ? e($cartao['id']) : '';
            $numero = isset($cartao['numero']) ? e($cartao['numero']) : '';

            $html .= '<tr class="bg-light">';
            $html .= '<td colspan="4" style="text-align:left; font-size:1em;">';
            $html .= '<span style="font-weight:bold;">CARTÃO:</span> ' . $numero;
            $html .= '</td></tr>';

            $programas = [
                ['nome' => 'Programa Particular', 'campo' => 'pontos_part', 'valor' => $cartao['pontos_part'] ?? 0],
                ['nome' => 'Programa Franquia',   'campo' => 'pontos_fraq', 'valor' => $cartao['pontos_fraq'] ?? 0],
                ['nome' => 'Programa Multban',    'campo' => 'pontos_mult', 'valor' => $cartao['pontos_mult'] ?? 0],
                ['nome' => 'Cash Back',           'campo' => 'pontos_cash', 'valor' => $cartao['pontos_cash'] ?? 0],
            ];

            foreach ($programas as $prog) {
                $valor = floatval($prog['valor']);
                // Formatação BRL (2 decimais, vírgula)
                $valorFmt = number_format($valor, 2, ',', '.');
                $valorZeroFmt = number_format(0, 2, ',', '.');

                $html .= '<tr data-cartao="' . e($id) . '" data-programa="' . e($prog['campo']) . '">';
                $html .= '<td>' . e($prog['nome']) . '</td>';
                $html .= '<td class="pontos-disponiveis">' . $valorFmt . '</td>';
                $html .= '<td style="text-align:center; vertical-align:middle;">';
                $html .= '<input type="text" class="form-control form-control-sm pontos-utilizar" style="width:90px; text-align:right;" data-max="' . $valorFmt . '" value="' . $valorZeroFmt . '">';
                $html .= '</td>';
                $html .= '<td><input type="checkbox" class="utilizar-tudo"><span style="margin-left:8px;">Selecionar Tudo</span></td>';
                $html .= '</tr>';
            }
        }

        return $html;
    }

    //////////////////////////////////////////////////////////////////////////////////////////////
    // PREENCHE A TABELA DE RESGATE DE PONTOS VIA AJAX
    public function preencherTabelaResgateHtml(Request $request)
    {
        $cartoes = $request->input('cartoes', []);
        $html = $this->renderResgateTableHtml($cartoes);

        return response()->json(['success' => true, 'html' => $html]);
    }

}


// // REGRA DO PROGRAMA DE PONTOS - COMENTADO NESTE MOMENTO
            // // VÁLIDO APENAS PARA COBRANÇA, MANTEMOS AQUI APENAS PARA CONHECIMENTO
            // if ($tipoPagto === 'CM') {

            //     $emp_pp = null;
            //     // se programa de pontos particular ativo
            //     if ($pp_particular) {
            //         $emp_pp = $empresa->emp_id;
            //         if ($emp_pp) {
            //             // BUSCA A REGRA DO PROGRAMA DE PONTOS
            //             $taxpos = ProgramaPts::where('emp_id', $emp_pp)
            //                 ->where('card_categ', $card_categ)
            //                 ->where('prgpts_sts', '=', 'AT')
            //                 ->first();

            //             $prgpts_valor = $taxpos->prgpts_valor ?? 0;
            //             $prgpts_eq = $taxpos->prgpts_eq ?? 0;

            //             if ($prgpts_valor > 0 && $prgpts_eq > 0) {
            //                 // Calcula o valor do cashback com base na regra
            //                 $pts_disp_part = floor($vlr_btot_split / $prgpts_valor) * $prgpts_eq;
            //             }
            //         }
            //     }
            //     // se programa de pontos da franquia ativo
            //     if ($pp_franquia) {
            //         $emp_pp = $empresa->emp_frqmst ?? null;
            //         if ($emp_pp) {
            //             // BUSCA A REGRA DO PROGRAMA DE PONTOS
            //             $taxpos = ProgramaPts::where('emp_id', $emp_pp)
            //                 ->where('card_categ', $card_categ)
            //                 ->where('prgpts_sts', '=', 'AT')
            //                 ->first();

            //             $prgpts_valor = $taxpos->prgpts_valor ?? 0;
            //             $prgpts_eq = $taxpos->prgpts_eq ?? 0;

            //             if ($prgpts_valor > 0 && $prgpts_eq > 0) {
            //                 // Calcula o valor do cashback com base na regra
            //                 $pts_disp_fraq = floor($vlr_btot_split / $prgpts_valor) * $prgpts_eq;
            //             }
            //         }
            //     }
            //     // se programa de pontos multban ativo
            //     if ($pp_mult) {
            //         $emp_pp = 1;
            //         if ($emp_pp) {
            //             // BUSCA A REGRA DO PROGRAMA DE PONTOS
            //             $taxpos = ProgramaPts::where('emp_id', $emp_pp)
            //                 ->where('card_categ', $card_categ)
            //                 ->where('prgpts_sts', '=', 'AT')
            //                 ->first();

            //             $prgpts_valor = $taxpos->prgpts_valor ?? 0;
            //             $prgpts_eq = $taxpos->prgpts_eq ?? 0;

            //             if ($prgpts_valor > 0 && $prgpts_eq > 0) {
            //                 // Calcula o valor do cashback com base na regra
            //                 $pts_disp_mult = floor($vlr_btot_split / $prgpts_valor) * $prgpts_eq;
            //             }
            //         }
            //     }
            //     // se programa de cashback multban ativo
            //     if ($pp_cashback) {
            //         $emp_pp = 1;
            //         if ($emp_pp) {
            //             // BUSCA A REGRA DO PROGRAMA DE PONTOS
            //             $taxpos = ProgramaPts::where('emp_id', $emp_pp)
            //                 ->where('card_categ', $card_categ)
            //                 ->where('prgpts_sts', '=', 'AT')
            //                 ->first();

            //             $prgpts_valor = $taxpos->prgpts_valor ?? 0;
            //             $prgpts_eq = $taxpos->prgpts_eq ?? 0;

            //             if ($prgpts_valor > 0 && $prgpts_eq > 0) {
            //                 // Calcula o valor do cashback com base na regra
            //                 $pts_disp_cash = floor($vlr_btot_split / $prgpts_valor) * $prgpts_eq;
            //             }
            //         }
            //     }

            // } else if ($tipoPagto === 'BL') {
            //     // Se houver regras, coloque aqui
            // } else if ($tipoPagto === 'DN') {
            //     // Se houver regras, coloque aqui
            // } else if ($tipoPagto === 'PX') {
            //     // Se houver regras, coloque aqui
            // } else if ($tipoPagto === 'OT') {
            //     // Se houver regras, coloque aqui
            // }
