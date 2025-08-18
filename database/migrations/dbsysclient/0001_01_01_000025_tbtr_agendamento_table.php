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
        Schema::create('tbtr_agendamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agendamento_tipo');
            $table->foreignId('cliente_id');
            $table->foreignId('user_id');
            $table->foreignId('prontuario_id');
            $table->string('title');
            $table->string('description')->nullable();
            $table->date('date');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->text('observacao')->nullable();
            $table->string('class_name')->nullable();
            $table->string('status', 2)->default('AG'); // AT - Atendido, CA - Cancelado, AG - Agendado
            $table->integer('criador')->nullable();
            $table->timestamp('dthr_cr')->nullable();
            $table->integer('modificador')->nullable();
            $table->timestamp('dthr_ch')->useCurrent();
            //KEYS
            $table->primary(['id','agendamento_tipo', 'cliente_id', 'user_id', 'prontuario_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbtr_agendamento');
    }


};
