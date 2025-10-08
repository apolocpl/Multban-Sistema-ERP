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
        Schema::create('tbsy_log_auditoria', function (Blueprint $table) {
            // PRIMARY KEY
            $table->id();
            // FIELDS
            $table->dateTime('auddat')->nullable();
            $table->string('audusu', 50)->nullable();
            $table->string('audtar', 100)->nullable();
            $table->string('audarq', 50)->nullable();
            $table->integer('audlan')->nullable();
            $table->string('audant', 100)->nullable();
            $table->string('auddep', 100)->nullable();
            $table->string('audnip', 20)->nullable();
            // KEYS
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbsy_log_auditoria');
    }
};
