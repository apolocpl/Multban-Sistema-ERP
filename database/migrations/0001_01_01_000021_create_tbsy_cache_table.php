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
        Schema::create('tbsy_cache', function (Blueprint $table) {
            // PRIMARY KEY
            $table->string('key');
            // FIELDS
            $table->mediumText('value');
            $table->integer('expiration');
            // KEYS
            $table->primary('key');
        });

        Schema::create('tbsy_cache_locks', function (Blueprint $table) {
            // PRIMARY KEY
            $table->string('key');
            // FIELDS
            $table->string('owner');
            $table->integer('expiration');
            // KEYS
            $table->primary('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbsy_cache');
        Schema::dropIfExists('tbsy_cache_locks');
    }
};
