<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('docum_cotizacion', function (Blueprint $table) {
            $table->string('boleta_patio')->nullable()->after('foto_patio');
            $table->timestamp('fecha_boleta_patio')->nullable()->after('boleta_patio');
        });
    }

    public function down(): void
    {
        Schema::table('docum_cotizacion', function (Blueprint $table) {
            $table->dropColumn(['boleta_patio', 'fecha_boleta_patio']);
        });
    }
};
