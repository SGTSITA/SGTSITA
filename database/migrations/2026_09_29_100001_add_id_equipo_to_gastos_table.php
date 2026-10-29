<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->foreignId('id_equipo')->nullable()->after('id_empresa')
                ->constrained('equipos')
                ->nullOnDelete();
            $table->index(['id_empresa', 'id_equipo'], 'gastos_empresa_equipo_index');
        });
    }

    public function down(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->dropForeign(['id_equipo']);
            $table->dropIndex('gastos_empresa_equipo_index');
            $table->dropColumn('id_equipo');
        });
    }
};
