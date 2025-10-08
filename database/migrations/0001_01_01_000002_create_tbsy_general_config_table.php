<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbsyGeneralConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbsy_tab_alias', function (Blueprint $table) {
            // PRIMARY KEY
            $table->foreignId('emp_id');
            $table->char('emp_tab_name', 100);
            // FIELDS
            $table->char('emp_tab_alias', 100)->nullable();
            // KEYS
            $table->primary(['emp_id', 'emp_tab_name']);
            // FOREIGN KEY
            // $table->foreign('emp_id')->references('emp_id')->on('db_sys_client.tbdm_empresa_geral');
        });

        Schema::create('tbsy_conexoes_bc_emp', function (Blueprint $table) {
            // PRIMARY KEY
            $table->foreignid('emp_id');
            // FIELDS
            $table->string('bc_fornec', 100)->nullable();
            $table->string('bc_emp_ident', 100)->nullable();
            $table->string('bc_emp_host', 255)->nullable();
            $table->string('bc_emp_porta', 255)->nullable();
            $table->string('bc_emp_nome', 255)->nullable();
            $table->string('bc_emp_user', 255)->nullable();
            $table->string('bc_emp_pass', 255)->nullable();
            $table->string('bc_emp_token', 100)->nullable();
            $table->string('bc_emp_sslmo', 100)->nullable();
            $table->string('bc_emp_sslce', 100)->nullable();
            $table->string('bc_emp_sslky', 100)->nullable();
            $table->string('bc_emp_sslca', 100)->nullable();
            $table->string('bc_emp_toconex', 100)->nullable();
            $table->string('bc_emp_tocons', 100)->nullable();
            $table->string('bc_emp_pooling', 100)->nullable();
            $table->string('bc_emp_charset', 100)->nullable();
            $table->string('bc_emp_tzone', 100)->nullable();
            $table->string('bc_emp_appname', 100)->nullable();
            $table->string('bc_emp_keepalv', 100)->nullable();
            $table->string('bc_emp_compress', 100)->nullable();
            $table->string('bc_emp_readonly', 100)->nullable();
            // KEYS
            $table->primary(['emp_id']);
            // FOREIGN KEY
            // $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbsy_tab_alias');
        Schema::dropIfExists('tbsy_conexoes_bc_emp');
    }
}
