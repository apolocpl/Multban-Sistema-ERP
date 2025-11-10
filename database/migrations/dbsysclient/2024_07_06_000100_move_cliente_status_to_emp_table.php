<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tbdm_clientes_emp', function (Blueprint $table) {
            if (! Schema::hasColumn('tbdm_clientes_emp', 'cliente_sts')) {
                $table->string('cliente_sts', 2)->default('NA')->after('cliente_pasprt');
            }
        });

        if (Schema::hasColumn('tbdm_clientes_geral', 'cliente_sts')) {
            DB::statement('
                UPDATE tbdm_clientes_emp AS ce
                INNER JOIN tbdm_clientes_geral AS cg ON ce.cliente_id = cg.cliente_id
                SET ce.cliente_sts = cg.cliente_sts
            ');

            Schema::table('tbdm_clientes_geral', function (Blueprint $table) {
                $table->dropColumn('cliente_sts');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbdm_clientes_geral', function (Blueprint $table) {
            if (! Schema::hasColumn('tbdm_clientes_geral', 'cliente_sts')) {
                $table->string('cliente_sts', 2)->default('NA')->after('cliente_pasprt');
            }
        });

        DB::statement('
            UPDATE tbdm_clientes_geral AS cg
            INNER JOIN (
                SELECT cliente_id, MIN(cliente_sts) AS cliente_sts
                FROM tbdm_clientes_emp
                GROUP BY cliente_id
            ) AS ce ON ce.cliente_id = cg.cliente_id
            SET cg.cliente_sts = ce.cliente_sts
        ');

        Schema::table('tbdm_clientes_emp', function (Blueprint $table) {
            if (Schema::hasColumn('tbdm_clientes_emp', 'cliente_sts')) {
                $table->dropColumn('cliente_sts');
            }
        });
    }
};
