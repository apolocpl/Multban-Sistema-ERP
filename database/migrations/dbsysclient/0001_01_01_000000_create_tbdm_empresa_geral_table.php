<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbdmEmpresaGeralTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       // DB::statement('SET SESSION sql_require_primary_key=0');
        Schema::create('tbdm_empresa_geral', function (Blueprint $table) {
            $table->id('emp_id');
            $table->string('emp_cnpj', 14)->unique();
            $table->string('emp_sts', 2);
            //FIELDS
            $table->string('emp_wl', 1)->nullable();
            $table->integer('emp_wlde')->length(10)->nullable();
            $table->decimal('emp_comwl', 10, 2)->nullable();
            $table->string('emp_privlbl', 1)->nullable();
            $table->string('emp_ie', 14)->nullable();
            $table->string('emp_im', 14)->nullable();
            $table->text('emp_rzsoc')->nullable();
            $table->text('emp_nfant')->nullable();
            $table->string('emp_nmult', 15)->nullable();
            $table->integer('emp_ratv')->length(3)->nullable();
            $table->integer('emp_frqmst')->length(10)->nullable();
            $table->string('emp_frq', 1)->nullable();
            $table->string('emp_frqcmp', 1)->nullable();
            $table->string('emp_altlmt', 1)->nullable();
            $table->string('emp_integra', 1)->nullable();
            $table->string('emp_reemb', 1)->nullable();
            $table->string('emp_checkb', 1)->nullable();
            $table->string('emp_tpbolet', 3)->nullable();
            $table->string('emp_checkm', 1)->nullable();
            $table->string('tp_plano', 6)->nullable();
            $table->string('emp_checkc', 1)->nullable();
            $table->string('emp_adqrnt', 10)->nullable();
            $table->string('emp_meiocom', 2)->nullable();
            $table->decimal('vlr_imp', 10, 2)->nullable();
            $table->date('dtvenc_imp')->nullable();
            $table->integer('cond_pgto')->length(2)->nullable();
            $table->decimal('vlr_mens', 10, 2)->nullable();
            $table->date('dtvenc_mens')->nullable();
            $table->string('emp_cep', 8)->nullable();
            $table->text('emp_end')->nullable();
            $table->string('emp_endnum', 15)->nullable();
            $table->text('emp_endcmp')->nullable();
            $table->string('emp_endbair', 100)->nullable();
            $table->integer('emp_endcid')->length(4)->nullable();
            $table->string('emp_endest', 2)->nullable();
            $table->string('emp_endpais', 4)->nullable();
            $table->text('emp_resplg')->nullable();
            $table->text('emp_emailrp')->nullable();
            $table->string('emp_celrp', 25)->nullable();
            $table->text('emp_respcm')->nullable();
            $table->text('emp_emailcm')->nullable();
            $table->string('emp_celcm', 25)->nullable();
            $table->text('emp_respfi')->nullable();
            $table->text('emp_emailfi')->nullable();
            $table->string('emp_celfi', 25)->nullable();
            $table->text('emp_pagweb')->nullable();
            $table->text('emp_rdsoc')->nullable();
            $table->text('logo_path')->nullable();
            $table->integer('criador')->nullable();
            $table->timestamp('dthr_cr')->nullable();
            $table->integer('modificador')->nullable();
            $table->timestamp('dthr_ch')->useCurrent();
            //KEYS
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbdm_empresa_geral');
    }
}
