<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbdmClientesGeralTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbdm_clientes_geral', function (Blueprint $table) {
            //PRIMARY KEY
            $table->id('cliente_id');
            $table->uuid('cliente_uuid');
            $table->integer('cliente_tipo')->length(2);
            $table->string('cliente_doc', 14);
            $table->string('cliente_pasprt', 15)->nullable();
            $table->string('cliente_sts', 2);
            //FIELDS
            $table->string('cliente_nome', 255);
            $table->string('cliente_rg', 12)->nullable();
            $table->date('cliente_dt_nasc')->nullable();
            $table->integer('convenio_id')->nullable();
            $table->string('carteirinha', 50)->nullable();
            $table->string('cliente_nm_alt', 255)->nullable();
            $table->string('cliente_nm_card', 255)->nullable();
            $table->string('cliente_email', 255);
            $table->string('cliente_email_s', 255)->nullable();
            $table->string('cliente_cel', 25);
            $table->string('cliente_cel_s', 25)->nullable();
            $table->string('cliente_telfixo', 25)->nullable();
            $table->decimal('cliente_rendam', 10, 2);
            $table->decimal('cliente_rdam_s', 10, 2)->nullable();
            $table->integer('cliente_dt_fech');
            $table->string('cliente_cep', 8);
            $table->text('cliente_end');
            $table->string('cliente_endnum', 15);
            $table->text('cliente_endcmp')->nullable();
            $table->string('cliente_endbair', 100);
            $table->integer('cliente_endcid');
            $table->string('cliente_endest', 2);
            $table->string('cliente_endpais', 4)->nullable();
            $table->string('cliente_cep_s', 8)->nullable();
            $table->string('cliente_end_s', 255)->nullable();
            $table->string('cliente_endnum_s', 15)->nullable();
            $table->string('cliente_endcmp_s', 255)->nullable();
            $table->string('cliente_endbair_s', 100)->nullable();
            $table->integer('cliente_endcid_s')->nullable();
            $table->string('cliente_endest_s', 2)->nullable();
            $table->string('cliente_endpais_s', 4)->nullable();
            $table->integer('cliente_score')->length(5)->nullable();
            $table->decimal('cliente_lmt_sg', 10, 2)->nullable();
            $table->string('cliente_pass', 255)->nullable();
            $table->integer('criador');
            $table->timestamp('dthr_cr');
            $table->integer('modificador');
            $table->timestamp('dthr_ch')->useCurrent();
            //KEYS
            $table->primary(['cliente_id', 'cliente_uuid', 'cliente_tipo', 'cliente_doc', 'cliente_sts']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbdm_clientes_geral');
    }
}
