<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbsyUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbsy_user', function (Blueprint $table) {
            //PRIMARY KEY
            $table->id('user_id')->unique();
            $table->string('user_logon', 20)->unique();
            $table->string('user_sts', 2);
            $table->foreignId('emp_id');
            $table->string('user_cpf', 11)->unique();
            $table->string('user_crm', 11)->unique()->nullable();
            //FIELDS
            $table->string('user_name', 255);
            $table->integer('user_func')->nullable()->length(3);
            $table->string('user_email')->unique();
            $table->string('user_cel', 25)->nullable();
            $table->string('user_tfixo', 25)->nullable();
            $table->string('user_role', 100)->nullable();
            $table->string('user_screen', 50)->nullable();
            $table->string('langu', 4)->nullable();
            $table->string('user_pass', 255)->nullable();
            $table->integer('user_resp')->nullable();
            $table->char('user_comis', 1)->nullable();
            $table->decimal('user_pcomis', 15, 2)->nullable();
            $table->string('user_seller', 100)->nullable();
            $table->string('user_cdgbc', 6)->nullable();
            $table->string('user_agbc', 20)->nullable();
            $table->string('user_ccbc', 20)->nullable();
            $table->string('user_pix', 100)->nullable();
            $table->integer('criador')->nullable();
            $table->timestamp('dthr_cr')->nullable();
            $table->integer('modificador')->nullable();
            $table->timestamp('dthr_ch')->useCurrent();
            //REMEMBER TOKEN
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            //KEYS
            $table->primary(['user_id', 'user_logon', 'user_sts', 'emp_id', 'user_cpf']);
            //FOREIGN KEY
            //$table->foreign('emp_id')->references('emp_id')->on('db_sys_client.tbdm_empresa_geral');
        });

        // Schema::table('tbdm_user', function($table) {
		//  $table->foreign('emp_id')->references('emp_id')->on('tbdm_empresa_geral');
        // });

        Schema::create('tbsy_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('tbsy_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbsy_user');
        Schema::dropIfExists('tbsy_password_reset_tokens');
        Schema::dropIfExists('tbsy_sessions');
    }
}
