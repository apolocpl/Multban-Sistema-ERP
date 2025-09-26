<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbdmClientesCardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbdm_clientes_card', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignId('emp_id');
            $table->foreignId('cliente_id');
            $table->string('cliente_doc', 14);
            $table->uuid('card_uuid');
            $table->string('cliente_cardn', 16);
            $table->string('cliente_cardcv', 3);
            //FIELDS
            $table->string('cliente_pasprt', 15)->nullable();
            $table->string('card_sts', 2);
            $table->string('card_tp', 4);
            $table->string('card_mod', 4);
            $table->string('card_categ', 4)->nullable();
            $table->string('card_desc', 100)->nullable();
            $table->decimal('card_saldo_vlr', 10, 2);
            $table->decimal('card_limite', 10, 2);
            $table->decimal('card_saldo_pts', 10, 2);
            $table->text('card_pass')->nullable();
            $table->integer('criador');
            $table->timestamp('dthr_cr');
            $table->integer('modificador');
            $table->timestamp('dthr_ch')->useCurrent();
            //KEYS
            $table->primary(['emp_id', 'cliente_id', 'cliente_doc', 'card_uuid', 'cliente_cardn', 'cliente_cardcv']);
            //INDICES
            $table->index('card_uuid');
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
            $table->foreign('cliente_id')->references('cliente_id')->on('tbdm_clientes_geral');
        });

        Schema::create('tbdm_clientes_prt', function (Blueprint $table) {
            //PRIMARY KEY
            $table->id('protocolo');
            $table->string('protocolo_tp', 2);
            $table->date('protocolo_dt');
            $table->primary(['protocolo', 'protocolo_tp', 'protocolo_dt']);

            //FOREIGN KEYS
            $table->foreignId('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
            $table->foreignId('cliente_id')->references('cliente_id')->on('tbdm_clientes_geral');
            $table->integer('cliente_doc')->length(14);

            //FIELDS
            $table->integer('user_id');
            $table->string('cliente_pasprt', 15)->nullable();
            $table->string('anexo', 1)->nullable();
            $table->string('doc_anexo_path', 255)->nullable();
            $table->string('foto_anexo_path', 255)->nullable();
            $table->longText('texto_prt')->nullable();
            $table->longText('texto_rec')->nullable();
            $table->longText('texto_anm')->nullable();
            $table->longText('texto_prv')->nullable();
            $table->longText('texto_exm')->nullable();
            $table->longText('texto_atd')->nullable();
            $table->integer('criador');
            $table->timestamp('dthr_cr');
            $table->integer('modificador');
            $table->timestamp('dthr_ch');

            //INDEXES
            $table->index('emp_id');
            $table->index('cliente_id');
            $table->index('cliente_doc');
            $table->index('user_id');
        });

        Schema::create('tbtr_clientes_orc', function (Blueprint $table) {
            //PRIMARY KEY
            $table->id('orcamento');
            $table->date('orcamento_dt');
            $table->integer('orcamento_sts');
            $table->primary(['orcamento', 'orcamento_dt', 'orcamento_sts']);

            //FOREIGN KEYS
            $table->foreignid('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
            $table->foreignid('cliente_id')->references('cliente_id')->on('tbdm_clientes_geral');
            $table->string('cliente_doc', 14);
            $table->foreignid('produto_id')->references('produto_id')->on('tbdm_produtos_geral');

            //FIELDS
            $table->integer('user_id');
            $table->integer('qtde_orc')->nullable();
            $table->decimal('vlr_orc', 10, 2)->nullable();
            $table->integer('criador');
            $table->timestamp('dthr_cr');
            $table->integer('modificador');
            $table->timestamp('dthr_ch');

            //INDEXES
            $table->index('emp_id');
            $table->index('cliente_id');
            $table->index('cliente_doc');
            $table->index('user_id');
            $table->index('produto_id');
        });

        Schema::create('tbdm_cartoes_pre', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignid('emp_id');
            $table->id('prg_id');
            $table->string('card_mod', 4);
            $table->string('card_sts', 30);
            //FIELDS
            $table->string('prg_nome', 100)->nullable();
            $table->decimal('prg_valor', 10, 2)->nullable();
            $table->integer('criador')->length()->nullable();
            $table->timestamp('dthr_cr')->nullable();
            $table->integer('modificador')->length()->nullable();
            $table->timestamp('dthr_ch')->nullable();
            //KEYS
            $table->primary(['prg_id', 'emp_id', 'card_mod', 'card_sts']);
            //FOREIGN KEY
            $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
        });

        Schema::create('tbdm_programa_pts', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignid('emp_id');
            $table->id('prgpts_id');
            $table->string('card_categ', 4);
            $table->string('prgpts_sts', 2);
            //FIELDS
            $table->decimal('prgpts_valor', 10, 2)->nullable();
            $table->decimal('prgpts_eq', 10, 2)->nullable();
            $table->string('prgpts_sc', 1)->nullable();
            $table->integer('criador')->length()->nullable();
            $table->timestamp('dthr_cr')->nullable();
            $table->integer('modificador')->length()->nullable();
            $table->timestamp('dthr_ch')->nullable();
            // PRIMARY KEY
            $table->primary('prgpts_id');
            // UNIQUE KEY para emp_id + card_categ
            $table->unique(['emp_id', 'card_categ']);
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
        Schema::dropIfExists('tbdm_clientes_card');
        Schema::dropIfExists('tbtr_clientes_prt');
        Schema::dropIfExists('tbdm_cartoes_pre');
        Schema::dropIfExists('tbdm_programa_pts');
    }
}
