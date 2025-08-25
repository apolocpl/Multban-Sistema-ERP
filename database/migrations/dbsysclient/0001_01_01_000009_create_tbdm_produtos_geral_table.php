<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbdmProdutosGeralTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbdm_produtos_geral', function (Blueprint $table) {
            //PRIMARY KEY
            $table->foreignId('emp_id');
            $table->id('produto_id');
            $table->string('produto_sts', 2);
            $table->integer('produto_tipo')->length(2);
            //FIELDS
            $table->float('partcp_pvlaor')->nullable();
            $table->integer('partcp_empid')->nullable();
            $table->string('partcp_seller', 100)->nullable();
            $table->string('partcp_pgsplit', 1)->nullable();
            $table->string('partcp_pgtransf', 1)->nullable();
            $table->string('partcp_cdgbc', 6)->nullable();
            $table->string('partcp_agbc', 20)->nullable();
            $table->string('partcp_ccbc', 20)->nullable();
            $table->string('partcp_pix', 100)->nullable();
            $table->string('produto_ncm', 10)->nullable();
            $table->string('produto_cdgb', 255)->nullable();
            $table->decimal('produto_peso', 10, 2)->nullable();
            $table->string('produto_ctrl', 1)->nullable();
            $table->string('produto_dc', 15)->nullable();
            $table->string('produto_dm', 100);
            $table->string('produto_dl', 255);
            $table->string('produto_dt', 255);
            $table->decimal('produto_vlr', 10, 2);
            $table->integer('criador');
            $table->timestamp('dthr_cr');
            $table->integer('modificador');
            $table->timestamp('dthr_ch')->useCurrent();;
            //KEYS
            $table->primary(['produto_id', 'emp_id', 'produto_sts', 'produto_tipo']);
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
        Schema::dropIfExists('tbdm_produtos_geral');
    }
}
