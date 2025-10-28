<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbtr_h_titulos', function (Blueprint $table) {
            // PRIMARY KEY & FOREIGN KEYS
            $table->foreignId('emp_id');
            $table->foreignId('user_id');
            $table->bigIncrements('titulo');
            $table->uuid('nsu_titulo');
            $table->integer('qtd_parc');
            $table->integer('primeira_para');
            $table->integer('cnd_pag');
            $table->foreignId('cliente_id');
            $table->string('meio_pag_v', 2);
            $table->foreignUuid('card_uuid')->nullable();
            $table->date('data_mov');
            $table->uuid('nsu_autoriz');

            // FIELDS
            $table->string('comp_sep', 1)->nullable();
            $table->string('check_reemb', 1)->nullable();
            $table->string('lib_ant', 1)->nullable();
            $table->decimal('vlr_brt', 10, 2)->nullable();
            $table->decimal('tax_adm', 10, 2)->nullable();
            $table->decimal('tax_rebate', 10, 2)->nullable();
            $table->decimal('tax_royalties', 10, 2)->nullable();
            $table->decimal('tax_comissao', 10, 2)->nullable();
            $table->integer('qtd_pts_utlz')->nullable();
            $table->decimal('perc_pts_utlz', 5, 2)->nullable();
            $table->decimal('vlr_btot', 10, 2)->nullable();
            $table->decimal('perc_desc', 5, 2)->nullable();
            $table->decimal('vlr_dec', 10, 2)->nullable();
            $table->decimal('vlr_dec_mn', 10, 2)->nullable();
            $table->decimal('vlr_btot_split', 10, 2)->nullable();
            $table->decimal('perc_juros', 5, 2)->nullable();
            $table->decimal('vlr_juros', 10, 2)->nullable();
            $table->decimal('vlr_btot_cj', 10, 2)->nullable();
            $table->decimal('vlr_atr_m', 10, 2)->nullable();
            $table->decimal('vlr_atr_j', 10, 2)->nullable();
            $table->decimal('vlr_acr_mn', 10, 2)->nullable();
            $table->decimal('vlr_rec', 10, 2)->nullable();
            $table->decimal('pts_disp_part', 10, 2)->nullable();
            $table->decimal('pts_disp_fraq', 10, 2)->nullable();
            $table->decimal('pts_disp_mult', 10, 2)->nullable();
            $table->decimal('pts_disp_cash', 10, 2)->nullable();
            $table->integer('criador')->nullable();
            $table->timestamp('dthr_cr')->nullable();
            $table->integer('modificador')->nullable();
            $table->timestamp('dthr_ch')->useCurrent();
            // KEYS
            $table->primary(['titulo', 'nsu_titulo', 'nsu_autoriz']);
            // INDICES SIMPLES
            $table->index('qtd_parc');
            $table->index('primeira_para');
            $table->index('cnd_pag');
            $table->index('meio_pag_v');
            $table->index('data_mov');
            $table->index('nsu_titulo');
            $table->index('nsu_autoriz');
            $table->index('check_reemb');
            $table->index('lib_ant');
            // INDICES COMPOSTOS
            $table->index(['emp_id', 'cliente_id', 'cnd_pag', 'meio_pag_v', 'data_mov'], 'idx_emp_cli_cnd_meio_data');
            $table->index(['emp_id', 'cnd_pag', 'meio_pag_v', 'data_mov'], 'idx_emp_cnd_meio_data');
            $table->index(['emp_id', 'data_mov'], 'idx_emp_data');
            // FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
            // $table->foreign('user_id')->references('user_id')->on('u630533599_dmb_db_sys_app.tbsy_user');
            $table->foreign('cliente_id')->references('cliente_id')->on('tbdm_clientes_geral');
            $table->foreign('card_uuid')->references('card_uuid')->on('tbdm_clientes_card');
        });

        Schema::create('tbtr_i_titulos', function (Blueprint $table) {
            // PRIMARY KEY & FOREIGN KEYS
            $table->foreignId('emp_id');
            $table->foreignId('user_id');
            $table->foreignId('titulo');
            $table->foreignUuid('nsu_titulo');
            $table->foreignUuid('nsu_autoriz');
            $table->integer('item');
            $table->integer('produto_tipo');
            $table->foreignId('produto_id');
            // FIELDS
            $table->integer('qtd_item')->nullable();
            $table->decimal('vlr_unit_item', 10, 2)->nullable();
            $table->decimal('vlr_brt_item', 10, 2)->nullable();
            $table->decimal('vlr_dec_item', 10, 2)->nullable();
            $table->decimal('vlr_dec_mn', 10, 2)->nullable();
            $table->decimal('vlr_base_item', 10, 2)->nullable();
            $table->decimal('perc_toti', 5, 2)->nullable();
            $table->integer('qtd_pts_utlz_item')->nullable();
            $table->decimal('vlr_bpar_split_item', 10, 2)->nullable();
            $table->decimal('vlr_jpar_item', 10, 2)->nullable();
            $table->decimal('vlr_bpar_cj_item', 10, 2)->nullable();
            $table->decimal('vlr_atrm_item', 10, 2)->nullable();
            $table->decimal('vlr_atrj_item', 10, 2)->nullable();
            $table->decimal('vlr_acr_mn', 10, 2)->nullable();
            $table->decimal('ant_desc', 10, 2)->nullable();
            $table->decimal('pgt_vlr', 10, 2)->nullable();
            $table->decimal('pgt_desc', 10, 2)->nullable();
            $table->decimal('pgt_mtjr', 10, 2)->nullable();
            $table->decimal('vlr_rec', 10, 2)->nullable();
            $table->decimal('pts_disp_part', 10, 2)->nullable();
            $table->decimal('pts_disp_fraq', 10, 2)->nullable();
            $table->decimal('pts_disp_mult', 10, 2)->nullable();
            $table->decimal('pts_disp_cash', 10, 2)->nullable();
            $table->integer('criador')->nullable();
            $table->timestamp('dthr_cr')->nullable();
            $table->integer('modificador')->nullable();
            $table->timestamp('dthr_ch')->useCurrent();
            // KEYS
            $table->primary(['emp_id', 'user_id', 'titulo', 'nsu_titulo', 'nsu_autoriz', 'produto_tipo', 'produto_id', 'item']);
            // INDICES COMPOSTOS
            $table->index(['emp_id', 'titulo', 'nsu_titulo', 'produto_tipo', 'produto_id'], 'idx_emp_titulo_nid_prod');
            $table->index(['emp_id', 'produto_tipo', 'produto_id'], 'idx_emp_prod');
            // FOREIGN KEYS
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
            // $table->foreign('user_id')->references('user_id')->on('u630533599_dmb_db_sys_app.tbsy_user');
            $table->foreign(['titulo', 'nsu_titulo', 'nsu_autoriz'])->references(['titulo', 'nsu_titulo', 'nsu_autoriz'])->on('tbtr_h_titulos');
            $table->foreign('produto_id')->references('produto_id')->on('tbdm_produtos_geral');
        });

        Schema::create('tbtr_f_titulos', function (Blueprint $table) {
            // PRIMARY KEY
            $table->foreignId('emp_id');
            $table->uuid('id_fatura')->unique();
            $table->foreignId('cliente_id');
            $table->foreignUuid('card_uuid');
            // FIELDS
            $table->uuid('integ_bc')->nullable();
            $table->integer('fatura_sts')->nullable();
            $table->date('data_fech')->nullable();
            $table->date('data_venc')->nullable();
            $table->date('data_pgto')->nullable();
            $table->decimal('vlr_tot', 10, 2)->nullable();
            $table->decimal('vlr_pgto', 10, 2)->nullable();
            $table->integer('criador')->nullable();
            $table->timestamp('dthr_cr')->nullable();
            $table->integer('modificador')->nullable();
            $table->timestamp('dthr_ch')->useCurrent();
            // KEYS
            $table->primary(['id_fatura', 'cliente_id', 'card_uuid']);
            // INDICES SIMPLES
            $table->index('id_fatura');
            $table->index('cliente_id');
            $table->index('card_uuid');
            $table->index('data_fech');
            // INDICES COMPOSTOS
            $table->index(['emp_id', 'id_fatura', 'cliente_id', 'fatura_sts'], 'idx_id_cliente_sts');
            $table->index(['emp_id', 'id_fatura', 'cliente_id', 'fatura_sts', 'data_venc'], 'idx_id_cliente_stsdt1');
            $table->index(['emp_id', 'id_fatura', 'cliente_id', 'fatura_sts', 'data_fech'], 'idx_id_cliente_stsdt2');
            $table->index(['emp_id', 'id_fatura', 'cliente_id', 'fatura_sts', 'data_pgto'], 'idx_id_cliente_stsdt3');
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
            $table->foreign('cliente_id')->references('cliente_id')->on('tbdm_clientes_geral');
            $table->foreign('card_uuid')->references('card_uuid')->on('tbdm_clientes_card');
        });

        Schema::create('tbtr_p_titulos_ab', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignId('emp_id');
            $table->foreignId('user_id');
            $table->foreignId('titulo');
            $table->foreignUuid('nsu_titulo');
            $table->foreignUuid('nsu_autoriz');
            $table->integer('qtd_parc');
            $table->integer('primeira_para');
            $table->integer('cnd_pag');
            $table->foreignId('cliente_id');
            $table->string('meio_pag_v', 2);
            $table->date('data_mov');
            $table->integer('parcela');
            $table->uuid('nid_parcela');
            $table->date('data_venc');
            $table->string('parcela_sts', 3);
            $table->string('destvlr', 4);
            //FIELDS
            $table->foreignuuid('card_uuid')->nullable();
            $table->foreignuuid('id_fatura')->nullable();
            $table->uuid('integ_bc')->nullable();
            $table->date('data_pgto')->nullable();
            $table->string('meio_pag_t', 2)->nullable();
            $table->string('nid_parcela_org', 36)->nullable();
            $table->longtext('parcela_obs')->nullable();
            $table->longtext('parcela_ins_pg')->nullable();
            $table->integer('qtd_pts_utlz')->nullable();
            $table->decimal('tax_bacen', 10, 2)->nullable();
            $table->decimal('vlr_dec', 10, 2)->nullable();
            $table->decimal('vlr_dec_mn', 10, 2)->nullable();
            $table->decimal('vlr_bpar_split', 10, 2)->nullable();
            $table->decimal('vlr_jurosp', 10, 2)->nullable();
            $table->decimal('vlr_bpar_cj', 10, 2)->nullable();
            $table->decimal('vlr_atr_m', 10, 2)->nullable();
            $table->decimal('vlr_atr_j', 10, 2)->nullable();
            $table->string('isent_mj', 1)->nullable();
            $table->string('protestado', 1)->nullable();
            $table->string('negociacao', 1)->nullable();
            $table->decimal('vlr_acr_mn', 10, 2)->nullable();
            $table->decimal('vlr_cst_cob', 10, 2)->nullable();
            $table->longtext('negociacao_obs')->nullable();
            $table->longtext('negociacao_file')->nullable();
            $table->date('follow_dt')->nullable();
            $table->string('check_ant', 1)->nullable();
            $table->decimal('perct_ant', 5, 2)->nullable();
            $table->decimal('ant_desc', 10, 2)->nullable();
            $table->decimal('pgt_vlr', 10, 2)->nullable();
            $table->decimal('pgt_desc', 10, 2)->nullable();
            $table->decimal('pgt_mtjr', 10, 2)->nullable();
            $table->decimal('vlr_rec', 10, 2)->nullable();
            $table->decimal('pts_disp_part', 10, 2)->nullable();
            $table->decimal('pts_disp_fraq', 10, 2)->nullable();
            $table->decimal('pts_disp_mult', 10, 2)->nullable();
            $table->decimal('pts_disp_cash', 10, 2)->nullable();
            $table->integer('criador')->nullable();
            $table->timestamp('dthr_cr')->nullable();
            $table->integer('modificador')->nullable();
            $table->timestamp('dthr_ch')->useCurrent();
            //KEYS
            $table->primary(['emp_id', 'user_id', 'titulo','nsu_titulo' , 'nsu_autoriz', 'qtd_parc', 'primeira_para', 'cnd_pag', 'meio_pag_v', 'data_mov', 'parcela', 'nid_parcela', 'data_venc', 'destvlr']);
            // INDICES COMPOSTOS
            $table->index(['emp_id', 'titulo', 'cnd_pag','cliente_id', 'meio_pag_v', 'data_mov', 'data_venc', 'parcela_sts'], 'idx_emp_tit_cliente_datas');
            $table->index(['emp_id', 'titulo', 'data_mov', 'data_venc','parcela_sts'], 'idx_emp_tit_parcela_sts_data');
            $table->index(['emp_id', 'titulo', 'parcela_sts'], 'idx_emp_tit_parcela_sts');
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
            // $table->foreign('user_id')->references('user_id')->on('u630533599_dmb_db_sys_app.tbsy_user');
            $table->foreign(['titulo', 'nsu_titulo', 'nsu_autoriz'])->references(['titulo', 'nsu_titulo', 'nsu_autoriz'])->on('tbtr_h_titulos');
            $table->foreign('cliente_id')->references('cliente_id')->on('tbdm_clientes_geral');
            $table->foreign('card_uuid')->references('card_uuid')->on('tbdm_clientes_card');
            $table->foreign('id_fatura')->references('id_fatura')->on('tbtr_f_titulos');
        });

        Schema::create('tbtr_p_titulos_cp', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignId('emp_id');
            $table->foreignId('user_id');
            $table->foreignId('titulo');
            $table->foreignUuid('nsu_titulo');
            $table->foreignUuid('nsu_autoriz');
            $table->integer('qtd_parc');
            $table->integer('primeira_para');
            $table->integer('cnd_pag');
            $table->foreignId('cliente_id');
            $table->string('meio_pag_v', 2);
            $table->date('data_mov');
            $table->integer('parcela');
            $table->uuid('nid_parcela');
            $table->date('data_venc');
            $table->string('parcela_sts', 3);
            $table->string('destvlr', 4);
            //FIELDS
            $table->foreignuuid('card_uuid')->nullable();
            $table->foreignuuid('id_fatura')->nullable();
            $table->uuid('integ_bc')->nullable();
            $table->date('data_pgto')->nullable();
            $table->string('meio_pag_t', 2)->nullable();
            $table->string('nid_parcela_org', 36)->nullable();
            $table->longtext('parcela_obs')->nullable();
            $table->longtext('parcela_ins_pg')->nullable();
            $table->integer('qtd_pts_utlz')->nullable();
            $table->decimal('tax_bacen', 10, 2)->nullable();
            $table->decimal('vlr_dec', 10, 2)->nullable();
            $table->decimal('vlr_dec_mn', 10, 2)->nullable();
            $table->decimal('vlr_bpar_split', 10, 2)->nullable();
            $table->decimal('vlr_jurosp', 10, 2)->nullable();
            $table->decimal('vlr_bpar_cj', 10, 2)->nullable();
            $table->decimal('vlr_atr_m', 10, 2)->nullable();
            $table->decimal('vlr_atr_j', 10, 2)->nullable();
            $table->string('isent_mj', 1)->nullable();
            $table->string('protestado', 1)->nullable();
            $table->string('negociacao', 1)->nullable();
            $table->decimal('vlr_acr_mn', 10, 2)->nullable();
            $table->decimal('vlr_cst_cob', 10, 2)->nullable();
            $table->longtext('negociacao_obs')->nullable();
            $table->longtext('negociacao_file')->nullable();
            $table->date('follow_dt')->nullable();
            $table->string('check_ant', 1)->nullable();
            $table->decimal('perct_ant', 5, 2)->nullable();
            $table->decimal('ant_desc', 10, 2)->nullable();
            $table->decimal('pgt_vlr', 10, 2)->nullable();
            $table->decimal('pgt_desc', 10, 2)->nullable();
            $table->decimal('pgt_mtjr', 10, 2)->nullable();
            $table->decimal('vlr_rec', 10, 2)->nullable();
            $table->decimal('pts_disp_part', 10, 2)->nullable();
            $table->decimal('pts_disp_fraq', 10, 2)->nullable();
            $table->decimal('pts_disp_mult', 10, 2)->nullable();
            $table->decimal('pts_disp_cash', 10, 2)->nullable();
            $table->integer('criador')->nullable();
            $table->timestamp('dthr_cr')->nullable();
            $table->integer('modificador')->nullable();
            $table->timestamp('dthr_ch')->useCurrent();
            //KEYS
            $table->primary(['emp_id', 'user_id', 'titulo','nsu_titulo' , 'nsu_autoriz', 'qtd_parc', 'primeira_para', 'cnd_pag', 'meio_pag_v', 'data_mov', 'parcela', 'nid_parcela', 'data_venc', 'destvlr']);
            // INDICES COMPOSTOS
            $table->index(['emp_id', 'titulo', 'cnd_pag','cliente_id', 'meio_pag_v', 'data_mov', 'data_venc', 'parcela_sts'], 'idx_emp_tit_cliente_datas');
            $table->index(['emp_id', 'titulo', 'data_mov', 'data_venc','parcela_sts'], 'idx_emp_tit_parcela_sts_data');
            $table->index(['emp_id', 'titulo', 'parcela_sts'], 'idx_emp_tit_parcela_sts');
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
            // $table->foreign('user_id')->references('user_id')->on('u630533599_dmb_db_sys_app.tbsy_user');
            $table->foreign(['titulo', 'nsu_titulo', 'nsu_autoriz'])->references(['titulo', 'nsu_titulo', 'nsu_autoriz'])->on('tbtr_h_titulos');
            $table->foreign('cliente_id')->references('cliente_id')->on('tbdm_clientes_geral');
            $table->foreign('card_uuid')->references('card_uuid')->on('tbdm_clientes_card');
            $table->foreign('id_fatura')->references('id_fatura')->on('tbtr_f_titulos');
        });

        Schema::create('tbtr_s_titulos', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignId('emp_id');
            $table->foreignId('user_id');
            $table->foreignId('titulo');
            $table->foreignUuid('nsu_titulo');
            $table->foreignUuid('nsu_autoriz');
            $table->integer('parcela');
            $table->foreignId('produto_id');
            $table->string('lanc_tp', 10);
            $table->foreignId('recebedor');
            //FIELDS
            $table->decimal('tax_adm', 10, 2)->nullable();
            $table->decimal('vlr_plan', 10, 2)->nullable();
            $table->decimal('perc_real', 5, 2)->nullable();
            $table->decimal('vlr_real', 10, 2)->nullable();
            $table->integer('criador')->nullable();
            $table->timestamp('dthr_cr')->nullable();
            $table->integer('modificador')->nullable();
            $table->timestamp('dthr_ch')->useCurrent();
            //KEYS
            $table->primary(['emp_id', 'user_id', 'titulo', 'nsu_titulo', 'nsu_autoriz', 'parcela', 'produto_id', 'lanc_tp','recebedor']);
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
            // $table->foreign('user_id')->references('user_id')->on('u630533599_dmb_db_sys_app.tbsy_user');
            $table->foreign(['titulo', 'nsu_titulo', 'nsu_autoriz'])->references(['titulo', 'nsu_titulo', 'nsu_autoriz'])->on('tbtr_h_titulos');
            $table->foreign('produto_id')->references('produto_id')->on('tbdm_produtos_geral');
            $table->foreign('recebedor')->references('emp_id')->on('tbdm_empresa_geral');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbtr_h_titulos');
        Schema::dropIfExists('tbtr_p_titulos_ab');
        Schema::dropIfExists('tbtr_p_titulos_cp');
        Schema::dropIfExists('tbtr_i_titulos');
        Schema::dropIfExists('tbtr_s_titulos');
        Schema::dropIfExists('tbtr_f_titulos');
    }
};
