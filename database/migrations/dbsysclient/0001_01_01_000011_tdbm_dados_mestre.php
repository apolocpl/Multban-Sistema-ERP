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
        Schema::create('tbdm_langu', function (Blueprint $table) {
            $table->string('langu', 4);
            $table->string('langu_desc', 30);
            //KEYS
            $table->primary('langu');
        });

        Schema::create('tbdm_userstatus', function (Blueprint $table) {
            $table->string('user_sts', 2);
            $table->string('langu', 4);
            $table->string('user_sts_desc', 30);
            //KEYS
            $table->primary(['user_sts', 'langu']);
        });

        Schema::create('tbdm_userfunc', function (Blueprint $table) {
            $table->integer('user_func');
            $table->string('langu', 4);
            $table->string('user_func_desc', 30);
            //KEYS
            $table->primary(['user_func', 'langu']);
        });

        Schema::create('tbdm_empstatus', function (Blueprint $table) {
            $table->string('emp_sts', 2);
            $table->string('langu', 4);
            $table->string('emp_sts_desc', 30);
            //KEYS
            $table->primary(['emp_sts', 'langu']);
        });

        Schema::create('tbdm_emp_ratv', function (Blueprint $table) {
            $table->integer('emp_ratv');
            $table->string('langu', 4);
            $table->string('emp_ratv_desc', 50);
            //KEYS
            $table->primary(['emp_ratv', 'langu']);
        });

        Schema::create('tbdm_bnccode', function (Blueprint $table) {
            $table->string('cdgbc', 6);
            $table->string('langu', 4);
            $table->text('cdgbc_desc');
            //KEYS
            $table->primary(['cdgbc', 'langu']);
        });

        Schema::create('tbdm_tpplanovd', function (Blueprint $table) {
            $table->string('tp_plano', 6);
            $table->string('langu', 4);
            $table->string('tp_plano_desc', 30);
            //KEYS
            $table->primary(['tp_plano', 'langu']);
        });

        Schema::create('tbdm_estados', function (Blueprint $table) {
            $table->string('estado_pais', 4);
            $table->string('estado', 2);
            $table->string('langu', 4);
            $table->string('estado_desc', 40);
            //KEYS
            $table->primary(['estado_pais', 'estado', 'langu']);
        });

        Schema::create('tbdm_cidade', function (Blueprint $table) {
            $table->id('cidade');
            $table->string('cidade_est', 2);
            $table->string('cidade_ibge', 10);
            $table->string('langu', 4);
            $table->string('cidade_desc', 50);
            //KEYS
            $table->primary(['cidade', 'cidade_est', 'cidade_ibge', 'langu']);
        });

        Schema::create('tbdm_pais', function (Blueprint $table) {
            $table->string('pais', 4);
            $table->string('langu', 4);
            $table->string('pais_desc', 50);
            //KEYS
            $table->primary(['pais', 'langu']);
        });

        Schema::create('tbdm_cliente_tp', function (Blueprint $table) {
            $table->integer('cliente_tipo');
            $table->string('langu', 4);
            $table->string('cliente_tipo_desc', 50);
            //KEYS
            $table->primary(['cliente_tipo', 'langu']);
        });

        Schema::create('tbdm_agendamento_tp', function (Blueprint $table) {
            $table->integer('agendamento_tipo');
            $table->string('langu', 4);
            $table->string('agendamento_tipo_desc', 50);
            //KEYS
            $table->primary(['agendamento_tipo', 'langu']);
        });

         Schema::create('tbdm_agendamento_sts', function (Blueprint $table) {
            $table->string('agendamento_sts', 2);
            $table->string('langu', 4);
            $table->string('agendamento_sts_desc', 50);
            //KEYS
            $table->primary(['agendamento_sts', 'langu']);
        });

        Schema::create('tbdm_cliente_sts', function (Blueprint $table) {
            $table->string('cliente_sts', 2);
            $table->string('langu', 4);
            $table->string('cliente_sts_desc', 50);
            //KEYS
            $table->primary(['cliente_sts', 'langu']);
        });

        Schema::create('tbdm_card_tp', function (Blueprint $table) {
            $table->string('card_tp', 4);
            $table->string('langu', 4);
            $table->string('card_tp_desc', 50);
            //KEYS
            $table->primary(['card_tp', 'langu']);
        });

        Schema::create('tbdm_card_mod', function (Blueprint $table) {
            $table->string('card_mod', 4);
            $table->string('langu', 4);
            $table->string('card_mod_desc', 50);
            //KEYS
            $table->primary(['card_mod', 'langu']);
        });

        Schema::create('tbdm_card_categ', function (Blueprint $table) {
            $table->string('card_categ', 4);
            $table->string('langu', 4);
            $table->string('card_categ_desc', 50);
            //KEYS
            $table->primary(['card_categ', 'langu']);
        });

        Schema::create('tbdm_card_sts', function (Blueprint $table) {
            $table->string('card_sts', 2);
            $table->string('langu', 4);
            $table->string('card_sts_desc', 30);
            //KEYS
            $table->primary(['card_sts', 'langu']);
        });

        Schema::create('tbdm_prgpts_sts', function (Blueprint $table) {
            $table->string('prgpts_sts', 2);
            $table->string('langu', 4);
            $table->string('prgpts_sts_desc', 30);
            //KEYS
            $table->primary(['prgpts_sts', 'langu']);
        });

        Schema::create('tbdm_meiocom', function (Blueprint $table) {
            $table->string('emp_meiocom', 2);
            $table->string('langu', 4);
            $table->string('meio_com_desc', 30);
            //KEYS
            $table->primary(['emp_meiocom', 'langu']);
        });

        Schema::create('tbdm_produto_tp', function (Blueprint $table) {
            $table->integer('produto_tipo', 2);
            $table->string('langu', 4);
            $table->string('produto_tipo_desc', 50);
            //KEYS
            $table->primary(['produto_tipo', 'langu']);
        });

        Schema::create('tbdm_produto_sts', function (Blueprint $table) {
            $table->string('produto_sts', 2);
            $table->string('langu', 4);
            $table->string('produto_sts_desc', 50);
            //KEYS
            $table->primary(['produto_sts', 'langu']);
        });

        Schema::create('tbdm_adquirentes', function (Blueprint $table) {
            $table->string('emp_adqrnt', 10);
            $table->string('adqrnt_desc', 100);
            //KEYS
            $table->primary('emp_adqrnt');
        });

        Schema::create('tbdm_tpbolet', function (Blueprint $table) {
            $table->string('emp_tpbolet', 3);
            $table->string('langu', 4);
            $table->string('tpbolet_desc', 50);
            //KEYS
            $table->primary(['emp_tpbolet', 'langu']);
        });

        Schema::create('tbdm_fornec', function (Blueprint $table) {
            $table->string('fornec', 5);
            $table->string('langu', 4);
            $table->string('fornec_desc', 50);
            //KEYS
            $table->primary(['fornec', 'langu']);
        });

        Schema::create('tbdm_destvlr', function (Blueprint $table) {
            $table->string('destvlr', 4);
            $table->string('langu', 4);
            $table->string('destvlr_desc', 50);
            //KEYS
            $table->primary(['destvlr', 'langu']);
        });

        Schema::create('tbdm_tax_categ', function (Blueprint $table) {
            $table->string('tax_categ', 4);
            $table->string('langu', 4);
            $table->string('tax_categ_desc', 50);
            //KEYS
            $table->primary(['tax_categ', 'langu']);
        });

        Schema::create('tbdm_convenios', function (Blueprint $table) {
            $table->id('convenio_id');
            $table->string('convenio_desc', 50);
        });

        Schema::create('tbdm_msg_categ', function (Blueprint $table) {
            $table->string('msg_categ', 5);
            $table->string('langu', 4);
            $table->string('msg_categ_desc', 50);
            //KEYS
            $table->primary(['msg_categ', 'langu']);
        });

        Schema::create('tbdm_canal_cm', function (Blueprint $table) {
            $table->integer('canal_id', 2);
            $table->string('langu', 4);
            $table->string('canal_desc', 50);
            //KEYS
            $table->primary(['canal_id', 'langu']);
        });

        Schema::create('tbdm_api_grupo', function (Blueprint $table) {
            $table->string('api_grupo', 5);
            $table->string('langu', 4);
            $table->string('api_grupo_desc', 50);
            //KEYS
            $table->primary(['api_grupo', 'langu']);
        });

        Schema::create('tbdm_api_subgrp', function (Blueprint $table) {
            $table->string('api_subgrp', 5);
            $table->string('langu', 4);
            $table->string('api_subgrp_desc', 50);
            //KEYS
            $table->primary(['api_subgrp', 'langu']);
        });

        Schema::create('tbdm_meio_pag', function (Blueprint $table) {
            $table->string('meio_pag', 2);
            $table->string('langu', 4);
            $table->string('meio_pag_desc', 50);
            $table->string('meio_pag_icon', 50);
            $table->integer('meio_order');
            //KEYS
            $table->primary(['meio_pag', 'langu']);
        });

        Schema::create('tbdm_prt_tp', function (Blueprint $table) {
            $table->string('protocolo_tp', 2);
            $table->string('langu', 4);
            $table->string('prt_tp_desc', 50);
            //KEYS
            $table->primary(['protocolo_tp', 'langu']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbdm_langu');
        Schema::dropIfExists('tbdm_userstatus');
        Schema::dropIfExists('tbdm_userfunc');
        Schema::dropIfExists('tbdm_empstatus');
        Schema::dropIfExists('tbdm_emp_ratv');
        Schema::dropIfExists('tbdm_bnc_code');
        Schema::dropIfExists('tbdm_tpplanovd');
        Schema::dropIfExists('tbdm_estados');
        Schema::dropIfExists('tbdm_cidades');
        Schema::dropIfExists('tbdm_pais');
        Schema::dropIfExists('tbdm_cliente_tp');
        Schema::dropIfExists('tbdm_cliente_sts');
        Schema::dropIfExists('tbdm_card_tp');
        Schema::dropIfExists('tbdm_card_mod');
        Schema::dropIfExists('tbdm_card_categ');
        Schema::dropIfExists('tbdm_card_sts');
        Schema::dropIfExists('tbdm_prgpts_sts');
        Schema::dropIfExists('tbdm_meiocom');
        Schema::dropIfExists('tbdm_produto_tp');
        Schema::dropIfExists('tbdm_produto_sts');
        Schema::dropIfExists('tbdm_adquirentes');
        Schema::dropIfExists('tbdm_tpbolet');
        Schema::dropIfExists('tbdm_fornec');
        Schema::dropIfExists('tbdm_destvlr');
        Schema::dropIfExists('tbdm_tax_categ');
        Schema::dropIfExists('tbdm_msg_categ');
        Schema::dropIfExists('tbdm_canal_cm');
        Schema::dropIfExists('tbdm_api_grupo');
        Schema::dropIfExists('tbdm_api_subgrp');
        Schema::dropIfExists('tbdm_meio_pag');
        Schema::dropIfExists('tbdm_prt_tp');
        Schema::dropIfExists('tbdm_agendamento_tp');
        Schema::dropIfExists('tbdm_convenios');
    }
};
