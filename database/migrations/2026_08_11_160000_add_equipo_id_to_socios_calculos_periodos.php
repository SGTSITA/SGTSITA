<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('socios_calculos_periodos', function (Blueprint $table) {
            $table->foreignId('equipo_id')->nullable()->after('id_empresa')->constrained('equipos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('socios_calculos_periodos', function (Blueprint $table) {
            $table->dropForeign(['equipo_id']);
            $table->dropColumn('equipo_id');
        });
    }
};
