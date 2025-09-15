<?php

namespace App\Http\Controllers\Multban\Venda;

use App\Http\Controllers\Multban\Produto\ProdutoController;
use App\Http\Controllers\Controller;
use App\Models\Multban\DadosMestre\MeioDePagamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Multban\Venda\RegrasParc;
use App\Models\Multban\Empresa\EmpresaParam;
use App\Models\Multban\Empresa\Empresa;
use App\Models\Multban\Produto\Produto;
use App\Models\Multban\Produto\ProdutoStatus;
use App\Models\Multban\Produto\ProdutoTipo;
use App\Models\Multban\TbTr\TbtrHTitulos;
use App\Models\Multban\TbTr\TbtrITitulos;
use App\Models\Multban\TbTr\TbtrPTitulosCp;
use App\Models\Multban\TbTr\TbtrSTitulos;
use Illuminate\Support\Str;
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

                // Dados do usuário logado
                $user = Auth::user();

                // Dados do PDV
                $cliente_id = $request->input('cliente_id');
                $checkout_subtotal = $request->input('checkout_subtotal');      // Valor total do carrinho antes de descontos
                $checkout_cashback = $request->input('checkout_cashback');      // Valor total de cashback aplicado
                $checkout_desconto = $request->input('checkout_desconto');      // Valor total de desconto aplicado

                $checkout_pago = $request->input('checkout_pago');              // Valor total pago
                $checkout_descontado = $request->input('checkout_descontado');  // Valor total descontado
                $checkout_resgatado = $request->input('checkout_resgatado');    // Pontos de cashback resgatados

                $checkout_total = $request->input('checkout_total');            // Valor total a cobrar (subtotal - desconto - cashback)
                $valortotalacobrar = $request->input('valortotalacobrar');      // Valor que o usuário quer cobrar agora
                $proporcao_cobrado = $request->input('proporcao_cobrado');      // Proporção do valor total a ser cobrado
                $tipoPagto = $request->input('tipoPagto');                      // Tipo de pagamento (ex: 'DN', 'CARTAO', etc.)

                $carrinho = $request->input('carrinho');                   // array de itens

                $emp_id = $user->emp_id;
                // Carrega dados da empresa e parâmetros da empresa do usuário logado
                $empresaParam = EmpresaParam::find($emp_id);

                $user_id = $user->user_id;
                $vlr_brt = $checkout_subtotal * $proporcao_cobrado;
                $tax_adm = 0;
                $tax_rebate = $empresaParam->tax_rebate ? $empresaParam->tax_rebate : 0;
                $tax_royalties = $empresaParam->tax_royalties ? $empresaParam->tax_royalties : 0;
                $tax_comissao = $empresaParam->tax_comiss ? $empresaParam->tax_comiss : 0;
                $qtd_pts_utlz = $checkout_cashback * $proporcao_cobrado;
                $perc_pts_utlz = $vlr_brt > 0 ? ($qtd_pts_utlz / $vlr_brt) * 100 : 0;
                $vlr_btot = $vlr_brt - $qtd_pts_utlz;
                $vlr_dec = $checkout_desconto * $proporcao_cobrado;
                $perc_desc = $vlr_brt > 0 ? ($vlr_dec / $vlr_brt) * 100 : 0;
                $vlr_dec_mn = 0;
                $vlr_btot_split = $vlr_btot - $vlr_dec - $vlr_dec_mn;
                $perc_juros = 0;
                $vlr_juros = 0;
                $vlr_btot_cj = $vlr_btot_split + $vlr_juros;
                $vlr_atr_m = 0;
                $vlr_atr_j = 0;
                $vlr_acr_mn = 0;

                // Recebe o título do request (pode ser null na primeira cobrança)
                // Log::info('Request recebido em cobrarDN:', $request->all());
                $tituloRequest = $request->input('titulo');
                $nsu_tituloRequest = $request->input('nsu_titulo');

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
                Log::info('Empresa:', ['emp_id' => $emp_id]);
                Log::info('Usuário:', ['user_id' => $user_id]);

                $data = [
                    'emp_id' => $emp_id,
                    'user_id' => $user_id,
                    // 'titulo' => $titulo, --- IGNORE ---
                    'nsu_titulo' => $nsu_titulo,
                    'cliente_id' => $cliente_id,
                    'meio_pag_v' => $tipoPagto,
                    'data_mov' => now(),
                    'nsu_autoriz' => $nsu_autoriz,
                    'vlr_brt' => $vlr_brt,
                    'tax_adm' => $tax_adm,
                    'tax_rebate' => $tax_rebate,
                    'tax_royalties' => $tax_royalties,
                    'tax_comissao' => $tax_comissao,
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
                ];

                if ($tituloRequest) {
                    $data['titulo'] = $tituloRequest;
                }

                if ($tipoPagto === 'CM') {

                } else if ($tipoPagto === 'BL') {

                } else if ($tipoPagto === 'DN') {
                    $data['qtd_parc'] = 1;
                    $data['primeira_para'] = 1;
                    $data['cnd_pag'] = 1;
                    $data['card_uuid'] = null;
                    $data['check_reemb'] = null;
                    $data['lib_ant'] = null;

                } else if ($tipoPagto === 'PX') {

                } else if ($tipoPagto === 'OT') {

                }

                $hTitulo = new TbtrHTitulos($data);
                $hTitulo->save();
                $titulo = $hTitulo->titulo;

                ////////////////////////////////////////////////////////////////////////////////////////
                // GRAVA OS DADOS NA TABELA TBTR_I_TITULOS E TBTR_S_TITULOS - PARA CADA ITEM DO CARRINHO
                ////////////////////////////////////////////////////////////////////////////////////////
                $item_seq = 1;
                $parcela = 1;

                foreach ($carrinho as $item) {
                    // Log::info('Carrinho Item:', $item);

                    $produto_tipo = $item['produto_tipo'];
                    $proporcao_item = $item['proporcao_item'];
                    $produto_id = $item['produto_id'];
                    $qtd_item = $item['qtd_item'];
                    $vlr_unit_item = $item['vlr_unit_item'];
                    $vlr_brt_item = $item['vlr_brut_item'] * $proporcao_cobrado;
                    $vlr_desc_item = $item['vlr_desc_item'] * $proporcao_cobrado;
                    $vlr_liqu_item = $item['vlr_liqu_item'] * $proporcao_cobrado;

                    $vlr_dec_item = $vlr_desc_item;
                    $vlr_dec_mn = 0;
                    $vlr_base_item = $vlr_brt_item - $vlr_dec_item - $vlr_dec_mn;
                    $perc_toti = $proporcao_item;
                    $qtd_pts_utlz_item = $perc_toti * $qtd_pts_utlz / 100;
                    $vlr_bpar_split_item = $vlr_base_item - $qtd_pts_utlz_item;
                    $vlr_jpar_item = 0;
                    $vlr_bpar_cj_item = $vlr_bpar_split_item + $vlr_jpar_item;
                    $vlr_atrm_item = 0;
                    $vlr_atrj_item = 0;
                    $vlr_acr_mn = 0;
                    $ant_desc = 0;
                    $pgt_vlr = 0;
                    $pgt_desc = 0;
                    $pgt_mtjr = 0;
                    $vlr_rec = $vlr_bpar_split_item + $vlr_jpar_item;
                    $pts_disp = 0;

                    // TBTR_I_TITULOS
                    TbtrITitulos::create([

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
                    ]);

                    // TBTR_S_TITULOS
                    TbtrSTitulos::create([
                        'emp_id' => $emp_id,
                        'user_id' => $user_id,
                        'titulo' => $titulo,
                        'nsu_titulo' => (string) $nsu_titulo,
                        'nsu_autoriz' => (string) $nsu_autoriz,
                        'parcela' => $parcela,
                        'produto_id' => $produto_id,
                        'lanc_tp' => 'DINHEIRO',
                        'recebedor' => $emp_id,

                        'tax_adm' => 0,
                        'vlr_plan' => $vlr_bpar_split_item,
                        'perc_real' => 100,
                        'vlr_real' => $vlr_rec,
                    ]);

                    $item_seq++;
                }

                /////////////////////////////////////////////////////////////
                // GRAVA OS DADOS NA TABELA TBTR_P_TITULOS_CP - PARCELA ÚNICA
                /////////////////////////////////////////////////////////////
                $nid_parcela = Str::uuid();
                TbtrPTitulosCp::create([
                    'emp_id' => $emp_id,
                    'user_id' => $user_id,
                    'titulo' => $titulo,
                    'nsu_titulo' => (string) $nsu_titulo,
                    'nsu_autoriz' => (string) $nsu_autoriz,
                    'qtd_parc' => 1,
                    'primeira_para' => 1,
                    'cnd_pag' => 1,
                    'cliente_id' => $cliente_id,
                    'meio_pag_v' => 'DN',
                    'data_mov' => now(),
                    'parcela' => 1,
                    'nid_parcela' => (string) $nid_parcela,
                    'data_venc' => now(),
                    'parcela_sts' => 'BDI',
                    'destvlr' => $empresaParam->emp_destvlr,

                    'id_fatura' => null,
                    'card_uuid' => null,
                    'integ_bc' => null,
                    'data_pgto' => now(),
                    'meio_pag_t' => 'DN',
                    'nid_parcela_org' => null,
                    'parcela_obs' => null,
                    'parcela_ins_pg' => null,
                    'qtd_pts_utlz' => $qtd_pts_utlz,
                    'tax_bacen' => 0,
                    'vlr_dec' => $vlr_dec,
                    'vlr_dec_mn' => 0,
                    'vlr_bpar_split' => $vlr_btot_split,
                    'vlr_jurosp' => 0,
                    'vlr_bpar_cj' => $vlr_btot_split,
                    'vlr_atr_m' => 0,
                    'vlr_atr_j' => 0,
                    'isent_mj' => null,
                    'negociacao' => null,
                    'vlr_acr_mn' => 0,
                    'negociacao_obs' => null,
                    'follow_dt' => null,
                    'perct_ant' => 0,
                    'ant_desc' => 0,
                    'pgt_vlr' => $vlr_btot_split,
                    'pgt_desc' => 0,
                    'pgt_mtjr' => 0,
                    'vlr_rec' => $vlr_btot_split,
                    'pts_disp_item' => 0,
                ]);

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
            return response()->json(['success' => false, 'error' => 'Título não informado.']);
        }

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
