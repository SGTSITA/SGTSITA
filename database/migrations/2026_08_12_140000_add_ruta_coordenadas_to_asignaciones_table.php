<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->longText('ruta_coordenadas')->nullable()->after('mensaje_compartido');
        });
    }

    public function down(): void
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->dropColumn('ruta_coordenadas');
        });
    }
};
