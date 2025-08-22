<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbdmEmpresaParamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbdm_empresa_param', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignId('emp_id');
            //FIELDS
            $table->string('emp_destvlr', 4)->nullable();
            $table->string('emp_dbaut', 1)->nullable();
            $table->string('emp_cdgbc', 6)->nullable();
            $table->string('emp_agbc', 20)->nullable();
            $table->string('emp_ccbc', 20)->nullable();
            $table->char('emp_pix', 100)->nullable();
            $table->string('emp_seller', 100)->nullable();
            $table->string('emp_cdgbcs', 6)->nullable();
            $table->string('emp_agbcs', 20)->nullable();
            $table->string('emp_ccbcs', 20)->nullable();
            $table->char('emp_pixs', 100)->nullable();
            $table->string('emp_sellers', 100)->nullable();
            $table->string('blt_ctr', 1)->nullable();
            $table->float('tax_blt')->nullable();
            $table->integer('blt_parclib')->length(2)->nullable();
            $table->string('lib_cnscore', 1)->nullable();
            $table->integer('intervalo_mes')->nullable()->nullable();
            $table->integer('qtde_cns_freem')->length(10)->nullable();
            $table->integer('qtde_cns_cntrm')->length(10)->nullable();
            $table->integer('qtde_cns_prem')->length(10)->nullable();
            $table->integer('qtde_cns_totm')->length(10)->nullable();
            $table->integer('qtde_cns_utlxm')->length(10)->nullable();
            $table->integer('qtde_cns_dispm')->length(10)->nullable();
            $table->string('card_posctr', 1)->nullable();
            $table->integer('card_posparc')->length(2)->nullable();
            $table->decimal('vlr_pix', 10, 2)->nullable();
            $table->decimal('vlr_boleto', 10, 2)->nullable();
            $table->decimal('vlr_bolepix', 10, 2)->nullable();
            $table->string('cob_mltjr_atr', 1)->nullable();
            $table->float('perc_mlt_atr')->nullable();
            $table->float('perc_jrs_atr')->nullable();
            $table->float('perc_com_mltjr')->nullable();
            $table->integer('dias_inat_card')->length(3)->nullable();
            $table->decimal('isnt_pixblt', 10, 2)->nullable();
            $table->string('parc_cjuros', 1)->nullable();
            $table->integer('parc_jr_deprc')->length(3)->nullable();
            $table->float('tax_jrsparc')->nullable();
            $table->float('parc_com_jrs')->nullable();
            $table->string('card_prectr', 1)->nullable();
            $table->float('tax_pre')->nullable();
            $table->string('card_giftctr', 1)->nullable();
            $table->float('tax_gift')->nullable();
            $table->string('card_fidctr', 1)->nullable();
            $table->float('tax_fid')->nullable();
            $table->string('pp_particular', 1)->nullable();
            $table->string('pp_franquia', 1)->nullable();
            $table->string('pp_multmais', 1)->nullable();
            $table->string('pp_cashback', 1)->nullable();
            $table->string('antecip_ctr', 1)->nullable();
            $table->float('tax_antmult')->nullable();
            $table->float('tax_antfundo')->nullable();
            $table->float('perc_rec_ant')->nullable();
            $table->string('inad_descprox', 1)->nullable();
            $table->string('inad_semrisco', 1)->nullable();
            $table->string('fndant_cdgbc', 6)->nullable();
            $table->string('fndant_agbc', 20)->nullable();
            $table->string('fndant_ccbc', 20)->nullable();
            $table->char('fndant_pix', 100)->nullable();
            $table->string('fndant_seller', 100)->nullable();
            $table->string('antecip_auto', 1)->nullable();
            $table->integer('ant_auto_srvd')->length(3)->nullable();
            $table->integer('ant_auto_prdvo')->length(3)->nullable();
            $table->integer('ant_auto_prdvd')->length(3)->nullable();
            $table->string('ant_blktit', 1)->nullable();
            $table->string('ant_titpdv', 1)->nullable();
            $table->integer('rebate_emp')->length(10)->nullable();
            $table->float('tax_rebate')->nullable();
            $table->string('rebate_split', 1)->nullable();
            $table->string('rebate_transf', 1)->nullable();
            $table->integer('royalties_emp')->length(10)->nullable();
            $table->float('tax_royalties')->nullable();
            $table->string('royalties_split', 1)->nullable();
            $table->string('royalties_transf', 1)->nullable();
            $table->integer('comiss_emp')->length(10)->nullable();
            $table->float('tax_comiss')->nullable();
            $table->string('comiss_split', 1)->nullable();
            $table->string('comiss_transf', 1)->nullable();
            $table->string('cobsrv_atv', 1)->nullable();
            $table->integer('cobsrv_diasatr')->length(3)->nullable();
            $table->float('cobsrv_multa')->nullable();
            $table->float('cobsrv_juros')->nullable();
            $table->float('tax_cobsrv_adm')->nullable();
            $table->float('tax_cobsrv_juss')->nullable();
            $table->integer('criador')->nullable();
            $table->timestamp('dthr_cr')->nullable();
            $table->integer('modificador')->nullable();
            $table->timestamp('dthr_ch')->useCurrent();
            //KEYS
            $table->primary('emp_id');
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
        });

        Schema::create('tbdm_empresa_taxpos', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignId('emp_id');
            $table->string('tax_categ', 10);
            //FIELDS
            $table->integer('parc_de')->length(5)->nullable();
            $table->integer('parc_ate')->length(5)->nullable();
            $table->float('tax')->nullable();
            $table->integer('criador')->nullable();
            $table->timestamp('dthr_cr')->nullable();
            $table->integer('modificador')->nullable();
            $table->timestamp('dthr_ch')->useCurrent();
            //KEYS
            $table->primary(['emp_id', 'tax_categ']);
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
        });

        Schema::create('tbcf_conexoes_api_emp', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignid('emp_id');
            $table->string('bc_fornec', 100);
            $table->string('api_grupo', 5);
            $table->string('api_subgrp', 5);
            //FIELDS
            $table->string('api_emp_endpoint', 100)->nullable();
            $table->string('api_emp_mtdo', 100)->nullable();
            $table->string('api_emp_token', 100)->nullable();
            $table->string('api_emp_tpde', 100)->nullable();
            $table->string('api_emp_tpda', 100)->nullable();
            $table->string('api_emp_user', 100)->nullable();
            $table->string('api_emp_pass', 100)->nullable();
            $table->string('api_emp_key', 100)->nullable();
            //KEYS
            $table->primary(['emp_id', 'bc_fornec', 'api_grupo', 'api_subgrp']);
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
        });

        Schema::create('tbcf_msg_comp', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignid('emp_id');
            $table->integer('canal_id')->length(2);
            $table->string('msg_categ', 5);
            //FIELDS
            $table->string('langu', 4)->nullable();
            $table->longtext('msg_text')->nullable();
            //KEYS
            $table->primary(['emp_id', 'canal_id', 'msg_categ']);
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
        });

        Schema::create('tbcf_config_wf', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignid('emp_id');
            $table->string('tabela', 50);
            $table->string('campo', 50);
            $table->foreignid('user_id');
            //KEYS
            $table->primary(['emp_id', 'tabela', 'campo', 'user_id']);
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
        });

        Schema::create('tbcf_padroes_planos', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignid('emp_id');
            $table->string('tp_plano', 6);
            //FIELDS
            $table->string('emp_destvlr', 4)->nullable();
            $table->string('emp_wl', 1)->nullable();
            $table->string('emp_privlbl', 1)->nullable();
            $table->string('emp_reemb', 1)->nullable();
            $table->string('emp_checkb', 1)->nullable();
            $table->string('emp_tpbolet', 3)->nullable();
            $table->string('emp_checkm', 1)->nullable();
            $table->string('emp_checkc', 1)->nullable();
            $table->string('emp_adqrnt', 10)->nullable();
            $table->string('lib_cnscore', 1)->nullable();
            $table->integer('intervalo_mes')->length(2)->nullable();
            $table->integer('qtde_cns_freem')->length(10)->nullable();
            $table->integer('qtde_cns_cntrm')->length(10)->nullable();
            $table->string('card_posctr', 1)->nullable();
            $table->integer('card_posparc')->length(2)->nullable();
            $table->decimal('vlr_pix', 10, 2)->nullable();
            $table->decimal('vlr_boleto', 10, 2)->nullable();
            $table->decimal('vlr_bolepix', 10, 2)->nullable();
            $table->string('cob_mltjr_atr', 1)->nullable();
            $table->float('perc_mlt_atr', 10, 2)->nullable();
            $table->float('perc_jrs_atr', 10, 2)->nullable();
            $table->float('perc_com_mltjr', 10, 2)->nullable();
            $table->integer('dias_inat_card')->length(3)->nullable();
            $table->decimal('isnt_pixblt', 10, 2)->nullable();
            $table->string('parc_cjuros', 1)->nullable();
            $table->integer('parc_jr_deprc')->length(3)->nullable();
            $table->float('tax_jrsparc', 10, 2)->nullable();
            $table->float('parc_com_jrs', 10, 2)->nullable();
            $table->string('card_prectr', 1)->nullable();
            $table->float('tax_pre', 10, 2)->nullable();
            $table->string('card_giftctr', 1)->nullable();
            $table->float('tax_gift', 10, 2)->nullable();
            $table->string('card_fidctr', 1)->nullable();
            $table->float('tax_fid', 10, 2)->nullable();
            $table->string('pp_particular', 1)->nullable();
            $table->string('pp_franquia', 1)->nullable();
            $table->string('pp_multcard', 1)->nullable();
            $table->string('pp_cashback', 1)->nullable();
            //KEYS
            $table->primary(['emp_id', 'tp_plano']);
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
        });

        Schema::create('tbcf_config_wl', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignid('emp_id');
            //FIELDS

            $table->string('mini_logo', 255)->nullable();
            $table->string('logo_h', 255)->nullable();
            $table->string('text_color_df', 50)->nullable();
            $table->string('fd_color', 50)->nullable();
            $table->string('fdsel_color', 50)->nullable();
            $table->string('ft_color', 50)->nullable();
            $table->string('ftsel_color', 50)->nullable();
            $table->string('bg_menu_ac_color', 50)->nullable();
            $table->string('bg_item_menu_ac_color', 50)->nullable();
            $table->string('menu_ac_color', 50)->nullable();

            //KEYS
            $table->primary(['emp_id']);
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
        });

        Schema::create('tbtr_implt_empresas', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignid('emp_id');
            $table->integer('emp_cnpj', 14);
            $table->string('emp_sts', 2);
            //FIELDS
            $table->string('emp_nfant', 255)->nullable();
            $table->string('emp_nmult', 15)->nullable();
            $table->date('fc_envio')->nullable();
            $table->date('fc_receb')->nullable();
            $table->integer('fc_delay')->length()->nullable();
            $table->date('pc_inicio')->nullable();
            $table->date('pc_fim')->nullable();
            $table->integer('pc_delay')->length()->nullable();
            $table->date('ct_envio')->nullable();
            $table->date('ct_receb')->nullable();
            $table->integer('ct_delay')->length()->nullable();
            $table->date('dc_solic')->nullable();
            $table->date('dc_receb')->nullable();
            $table->integer('dc_delay')->length()->nullable();
            $table->date('bc_inicad')->nullable();
            $table->date('bc_inianl')->nullable();
            $table->date('bc_fimanl')->nullable();
            $table->integer('bc_delaycad')->length()->nullable();
            $table->date('bc_envprv')->nullable();
            $table->date('bc_relprv')->nullable();
            $table->integer('bc_delayprv')->length()->nullable();
            $table->date('bc_envctr')->nullable();
            $table->date('bc_assctr')->nullable();
            $table->integer('bc_delayctr')->length()->nullable();
            $table->date('bc_ccopen')->nullable();
            $table->date('bc_sellercad')->nullable();
            $table->date('ft_inicio')->nullable();
            $table->date('ft_fim')->nullable();
            $table->integer('ft_delay')->length()->nullable();
            $table->date('pr_envform')->nullable();
            $table->date('pr_preform')->nullable();
            $table->integer('pr_delayfrom')->length()->nullable();
            $table->date('pr_inicr')->nullable();
            $table->date('pr_fimcr')->nullable();
            $table->integer('pr_delayprd')->length()->nullable();
            $table->date('pr_usercad')->nullable();
            $table->date('pr_agndtrn')->nullable();
            $table->date('pr_reltrn')->nullable();
            $table->integer('pr_delaytrn')->length()->nullable();
            $table->date('op_inicio')->nullable();
            $table->date('op_fim')->nullable();
            //KEYS
            $table->primary(['emp_cnpj', 'emp_id', 'emp_sts']);
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbdm_empresa_param');
        Schema::dropIfExists('tbdm_empresa_taxpos');
        Schema::dropIfExists('tbcf_conexoes_api_emp');
        Schema::dropIfExists('tbcf_padroes_planos');
        Schema::dropIfExists('tbcf_msg_comp');
        Schema::dropIfExists('tbcf_config_wl');
        Schema::dropIfExists('tbcf_config_wf');
    }
}
