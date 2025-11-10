<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbdmClientesEmpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbdm_clientes_emp', function (Blueprint $table) {
            //PRIMARY KEY
            $table->unsignedBigInteger('emp_id');
            $table->unsignedBigInteger('cliente_id');
            $table->uuid('cliente_uuid');
            $table->string('cliente_doc', 14);
            $table->string('cliente_pasprt', 15)->nullable();
            $table->string('cliente_sts', 2)->default('NA');
            $table->string('cad_liberado', 1);
            //FIELDS
            $table->integer('criador');
            $table->timestamp('dthr_cr');
            $table->integer('modificador');
            $table->timestamp('dthr_ch')->useCurrent();;
            //KEYS
            $table->primary(['emp_id', 'cliente_id', 'cliente_uuid', 'cliente_doc', 'cad_liberado']);
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbdm_clientes_emp');
    }
}
