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
            //PRIMARY KEY
            $table->foreignid('emp_id');
            $table->foreignid('user_id');
            $table->integer('titulo');
            $table->uuid('nid_titulo');
            $table->integer('qtd_parc');
            $table->integer('primeira_para');
            $table->integer('cnd_pag');
            $table->foreignid('cliente_id');
            $table->string('meio_pag', 2);
            $table->foreignuuid('card_uuid');
            $table->date('data_mov');
            $table->string('check_reemb', 1);
            $table->string('lib_ant', 1);
            //FIELDS
            $table->decimal('vlr_brt', 10, 2)->nullable();
            $table->float('tax_adm')->nullable();
            $table->float('tax_rebate')->nullable();
            $table->float('tax_royalties')->nullable();
            $table->float('tax_comissao')->nullable();
            $table->integer('qtd_pts_utlz')->nullable();
            $table->float('perc_pts_utlz')->nullable();
            $table->decimal('vlr_btot', 10, 2)->nullable();
            $table->float('perc_desc')->nullable();
            $table->decimal('vlr_dec', 10, 2)->nullable();
            $table->decimal('vlr_dec_mn', 10, 2)->nullable();
            $table->decimal('vlr_btot_split', 10, 2)->nullable();
            $table->float('perc_juros')->nullable();
            $table->decimal('vlr_juros', 10, 2)->nullable();
            $table->decimal('vlr_btot_cj', 10, 2)->nullable();
            $table->decimal('vlr_atr_m', 10, 2)->nullable();
            $table->decimal('vlr_atr_j', 10, 2)->nullable();
            $table->decimal('vlr_acr_mn', 10, 2)->nullable();
            //KEYS
            $table->primary(['titulo', 'nid_titulo', 'qtd_parc', 'primeira_para', 'cnd_pag', 'meio_pag', 'data_mov', 'check_reemb', 'lib_ant']);
            //INDICES
            $table->index('nid_titulo');
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
            $table->foreign('user_id')->references('user_id')->on('db_sys_app.tbsy_user');
            $table->foreign('cliente_id')->references('cliente_id')->on('tbdm_clientes_geral');
            $table->foreign('card_uuid')->references('card_uuid')->on('tbdm_clientes_card');

        });

        Schema::create('tbtr_i_titulos', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignid('emp_id');
            $table->foreignid('user_id');
            $table->integer('titulo');
            $table->foreignuuid('nid_titulo');
            $table->integer('item');
            $table->integer('produto_tipo');
            $table->foreignid('produto_id');
            //FIELDS
            $table->integer('qtd_item')->nullable();
            $table->decimal('vlr_unit_item', 10, 2)->nullable();
            $table->decimal('vlr_brt_item', 10, 2)->nullable();
            $table->float('perc_toti')->nullable();
            $table->integer('qtd_pts_utlz_item')->nullable();
            $table->decimal('vlr_base_item', 10, 2)->nullable();
            $table->decimal('vlr_dec_item', 10, 2)->nullable();
            $table->decimal('vlr_dec_mn', 10, 2)->nullable();
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
            $table->integer('pts_disp')->nullable();
            //KEYS
            $table->primary(['item', 'produto_tipo']);
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
            $table->foreign('user_id')->references('user_id')->on('db_sys_app.tbsy_user');
            $table->foreign('titulo')->references('titulo')->on('tbtr_h_titulos');
            $table->foreign('nid_titulo')->references('nid_titulo')->on('tbtr_h_titulos');
            $table->foreign('produto_id')->references('produto_id')->on('tbdm_produtos_geral');
        });

        Schema::create('tbtr_f_titulos', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignid('emp_id');
            $table->foreignid('user_id');
            $table->integer('titulo');
            $table->foreignuuid('nid_titulo');
            $table->foreignid('cliente_id');
            $table->foreignuuid('card_uuid');
            $table->uuid('id_fatura');
            //FIELDS
            $table->uuid('integ_bc')->nullable();
            $table->integer('fatura_sts')->nullable();
            $table->date('data_fech')->nullable();
            $table->date('data_venc')->nullable();
            $table->date('data_pgto')->nullable();
            $table->decimal('vlr_tot', 10, 2)->nullable();
            $table->decimal('vlr_pgto', 10, 2)->nullable();
            //KEYS
            $table->primary(['id_fatura']);
            //INDICES
            $table->index('id_fatura');
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
            $table->foreign('user_id')->references('user_id')->on('db_sys_app.tbsy_user');
            $table->foreign('titulo')->references('titulo')->on('tbtr_h_titulos');
            $table->foreign('nid_titulo')->references('nid_titulo')->on('tbtr_h_titulos');
            $table->foreign('cliente_id')->references('cliente_id')->on('tbdm_clientes_geral');
            $table->foreign('card_uuid')->references('card_uuid')->on('tbdm_clientes_card');
        });

        Schema::create('tbtr_p_titulos_ab', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignid('emp_id');
            $table->foreignid('user_id');
            $table->integer('titulo');
            $table->foreignuuid('nid_titulo');
            $table->integer('qtd_parc');
            $table->integer('primeira_para');
            $table->integer('cnd_pag');
            $table->foreignid('cliente_id');
            $table->string('meio_pag_v', 2);
            $table->foreignuuid('card_uuid');
            $table->date('data_mov');
            $table->integer('parcela');
            $table->uuid('nid_parcela');
            $table->foreignuuid('id_fatura');
            $table->date('data_venc');
            $table->string('destvlr', 4);
            //FIELDS
            $table->uuid('integ_bc');
            $table->date('data_pgto');
            $table->string('meio_pag_t', 2);
            $table->string('parcela_sts', 3);
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
            $table->string('negociacao', 1)->nullable();
            $table->decimal('vlr_acr_mn', 10, 2)->nullable();
            $table->longtext('negociacao_obs')->nullable();
            $table->longtext('negociacao_file')->nullable();
            $table->date('follow_dt')->nullable();
            $table->float('perct_ant')->nullable();
            $table->decimal('ant_desc', 10, 2)->nullable();
            $table->decimal('pgt_vlr', 10, 2)->nullable();
            $table->decimal('pgt_desc', 10, 2)->nullable();
            $table->decimal('pgt_mtjr', 10, 2)->nullable();
            $table->decimal('vlr_rec', 10, 2)->nullable();
            $table->integer('pts_disp_item')->nullable();
            //KEYS
            $table->primary(['nid_titulo' ,'qtd_parc', 'primeira_para', 'cnd_pag', 'meio_pag_v', 'data_mov', 'parcela', 'nid_parcela', 'data_venc', 'destvlr']);
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
            $table->foreign('user_id')->references('user_id')->on('db_sys_app.tbsy_user');
            $table->foreign('titulo')->references('titulo')->on('tbtr_h_titulos');
            $table->foreign('cliente_id')->references('cliente_id')->on('tbdm_clientes_geral');
            $table->foreign('card_uuid')->references('card_uuid')->on('tbdm_clientes_card');
            $table->foreign('id_fatura')->references('id_fatura')->on('tbtr_f_titulos');
        });

        Schema::create('tbtr_p_titulos_cp', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignid('emp_id');
            $table->foreignid('user_id');
            $table->integer('titulo');
            $table->foreignuuid('nid_titulo');
            $table->integer('qtd_parc');
            $table->integer('primeira_para');
            $table->integer('cnd_pag');
            $table->foreignid('cliente_id');
            $table->string('meio_pag_v', 2);
            $table->foreignuuid('card_uuid');
            $table->date('data_mov');
            $table->integer('parcela');
            $table->uuid('nid_parcela');
            $table->foreignuuid('id_fatura');
            $table->date('data_venc');
            $table->string('destvlr', 4);
            //FIELDS
            $table->uuid('integ_bc');
            $table->date('data_pgto');
            $table->string('meio_pag_t', 2);
            $table->string('parcela_sts', 3);
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
            $table->string('negociacao', 1)->nullable();
            $table->decimal('vlr_acr_mn', 10, 2)->nullable();
            $table->longtext('negociacao_obs')->nullable();
            $table->longtext('negociacao_file')->nullable();
            $table->date('follow_dt')->nullable();
            $table->float('perct_ant')->nullable();
            $table->decimal('ant_desc', 10, 2)->nullable();
            $table->decimal('pgt_vlr', 10, 2)->nullable();
            $table->decimal('pgt_desc', 10, 2)->nullable();
            $table->decimal('pgt_mtjr', 10, 2)->nullable();
            $table->decimal('vlr_rec', 10, 2)->nullable();
            $table->integer('pts_disp_item')->nullable();
            //KEYS
            $table->primary(['nid_titulo' ,'qtd_parc', 'primeira_para', 'cnd_pag', 'meio_pag_v', 'data_mov', 'parcela', 'nid_parcela', 'data_venc', 'destvlr']);
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
            $table->foreign('user_id')->references('user_id')->on('db_sys_app.tbsy_user');
            $table->foreign('titulo')->references('titulo')->on('tbtr_h_titulos');
            $table->foreign('cliente_id')->references('cliente_id')->on('tbdm_clientes_geral');
            $table->foreign('card_uuid')->references('card_uuid')->on('tbdm_clientes_card');
            $table->foreign('id_fatura')->references('id_fatura')->on('tbtr_f_titulos');
        });

        Schema::create('tbtr_s_titulos', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignid('emp_id');
            $table->foreignid('user_id');
            $table->integer('titulo');
            $table->integer('parcela');
            $table->foreignid('produto_id');
            $table->string('lanc_tp', 10);
            $table->foreignid('recebedor');
            //FIELDS
            $table->float('tax_adm')->nullable();
            $table->decimal('vlr_plan', 10, 2)->nullable();
            $table->float('perc_real')->nullable();
            $table->decimal('vlr_real', 10, 2)->nullable();
            //KEYS
            $table->primary(['parcela', 'lanc_tp']);
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
            $table->foreign('user_id')->references('user_id')->on('db_sys_app.tbsy_user');
            $table->foreign('titulo')->references('titulo')->on('tbtr_h_titulos');
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
