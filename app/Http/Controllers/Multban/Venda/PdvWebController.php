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

class PdvWebController extends Controller
{
    /**
     * Processa cobrança DN e grava nas tabelas tbtr_h_titulos, tbtr_i_titulos, tbtr_p_titulos_cp, tbtr_s_titulos
     */
    public function cobrarDN(Request $request)
    {
        try {
            // Dados do usuário logado
            $user = Auth::user();
            $emp_id = $user->emp_id;
            $user_id = $user->user_id;

            // Dados do PDV
            $cliente_id = $request->input('cliente_id');
            $valortotalacobrar = $request->input('valortotalacobrar');
            $checkout_total = $request->input('checkout_total');
            $checkout_desconto = $request->input('checkout_desconto');
            $checkout_cashback = $request->input('checkout_cashback');
            $carrinho = $request->input('carrinho'); // array de itens
            $proporcao_acobrar = 1;

            // Cálculo do desconto proporcional
            if ($valortotalacobrar >= $checkout_total) {
                $vlr_dec = $checkout_desconto;
                $proporcao_acobrar = 1;
            } else {
                $vlr_dec = ($checkout_desconto * $valortotalacobrar) / $checkout_total;
                $proporcao_acobrar = $valortotalacobrar / $checkout_total;
            }

            $qtd_pts_utlz = $checkout_cashback * $proporcao_acobrar;
            $perc_pts_utlz = $valortotalacobrar > 0 ? ($qtd_pts_utlz / $valortotalacobrar) * 100 : 0;
            $vlr_btot = $valortotalacobrar - $qtd_pts_utlz;
            $perc_desc = $valortotalacobrar > 0 ? ($vlr_dec / $valortotalacobrar) * 100 : 0;
            $vlr_dec_mn = 0;
            $vlr_btot_split = $vlr_btot - $vlr_dec - $vlr_dec_mn;
            $perc_juros = 0;
            $vlr_juros = 0;
            $vlr_btot_cj = $vlr_btot_split + $vlr_juros;
            $vlr_atr_m = 0;
            $vlr_atr_j = 0;
            $vlr_acr_mn = 0;

            // Gerar UUIDs
            $nid_titulo = Str::uuid();

            // Gravar tbtr_h_titulos
            $hTitulo = new TbtrHTitulos([
                'emp_id' => $emp_id,
                'user_id' => $user_id,
                // 'titulo' será preenchido após insert (auto-incremento)
                'nid_titulo' => $nid_titulo,
                'qtd_parc' => 1,
                'primeira_para' => 1,
                'cnd_pag' => 1,
                'cliente_id' => $cliente_id,
                'meio_pag' => 'DN',
                'card_uuid' => null,
                'data_mov' => now(),
                'check_reemb' => null,
                'vlr_brt' => $valortotalacobrar,
                'tax_adm' => 0,
                'tax_rebate' => 0,
                'tax_royalties' => 0,
                'tax_comissao' => 0,
                'qtd_pts_utlz' => $qtd_pts_utlz,
                'perc_pts_utlz' => $perc_pts_utlz,
                'vlr_btot' => $vlr_btot,
                'perc_desc' => $perc_desc,
                'vlr_dec' => $vlr_dec,
                'vlr_btot_split' => $vlr_btot_split,
                'perc_juros' => $perc_juros,
                'vlr_juros' => $vlr_juros,
                'vlr_btot_cj' => $vlr_btot_cj,
                'vlr_atr_m' => $vlr_atr_m,
                'vlr_atr_j' => $vlr_atr_j,
                'vlr_acr_mn' => $vlr_acr_mn,
            ]);
            $hTitulo->save();
            $titulo = $hTitulo->titulo;

            // Gravar tbtr_i_titulos para cada item do carrinho
            $item_seq = 1;
            foreach ($carrinho as $item) {
                $produto_tipo = $item['produto_tipo'];
                $produto_id = $item['produto_id'];
                $qtd_item = $item['qtd_item'];
                $vlr_unit_item = $item['vlr_unit_item'];
                $vlr_brt_item = $item['vlr_brt_item'];
                $perc_toti = $valortotalacobrar > 0 ? ($vlr_brt_item / $valortotalacobrar) * 100 : 0;
                $qtd_pts_utlz_item = $perc_toti * $qtd_pts_utlz / 100;
                $vlr_base_item = $vlr_brt_item - $qtd_pts_utlz_item;
                $vlr_dec_item = $vlr_dec * ($vlr_brt_item / $valortotalacobrar);
                $vlr_dec_mn = 0;
                $vlr_bpar_split_item = $vlr_base_item - $vlr_dec_item - $vlr_dec_mn;
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

                TbtrITitulos::create([
                    'emp_id' => $emp_id,
                    'user_id' => $user_id,
                    'titulo' => $titulo,
                    'nid_titulo' => $nid_titulo,
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
                $item_seq++;
            }

            // Gravar tbtr_p_titulos_cp (parcela única)
            $nid_parcela = Str::uuid();
            TbtrPTitulosCp::create([
                'emp_id' => $emp_id,
                'user_id' => $user_id,
                'titulo' => $titulo,
                'nid_titulo' => $nid_titulo,
                'qtd_parc' => 1,
                'primeira_para' => 1,
                'cnd_pag' => 1,
                'cliente_id' => $cliente_id,
                'meio_pag_v' => 'DN',
                'card_uuid' => null,
                'data_mov' => now(),
                'parcela' => 1,
                'nid_parcela' => $nid_parcela,
                'id_fatura' => null,
                'integ_bc' => null,
                'data_venc' => now(),
                'data_pgto' => now(),
                'meio_pag_t' => 'DN',
                'parcela_sts' => 'BDI',
                'destvlr' => null,
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

            // Gravar tbtr_s_titulos para cada item do carrinho
            $parcela = 1;
            foreach ($carrinho as $item) {
                $produto_id = $item['produto_id'];
                TbtrSTitulos::create([
                    'emp_id' => $emp_id,
                    'user_id' => $user_id,
                    'titulo' => $titulo,
                    'parcela' => $parcela,
                    'produto_id' => $produto_id,
                    'lanc_tp' => 'DN',
                    'recebedor' => $emp_id,
                    'tax_adm' => 0,
                    'vlr_plan' => $vlr_btot_split,
                    'perc_real' => 100,
                    'vlr_real' => $vlr_btot_split,
                ]);
            }

            return response()->json(['success' => true, 'titulo' => $titulo, 'nid_titulo' => $nid_titulo]);

        } catch (\Throwable $e) {

            return response()->json(['success' => false, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
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
