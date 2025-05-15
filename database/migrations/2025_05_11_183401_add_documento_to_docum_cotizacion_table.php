<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
      public function up(): void
    {
        Schema::table('docum_cotizacion', function (Blueprint $table) {
            $table->string('foto_patio')->nullable()->after('doc_ccp'); // Ajusta la posición según tus columnas
        });
    }

    public function down(): void
    {
        Schema::table('docum_cotizacion', function (Blueprint $table) {
            $table->dropColumn('foto_patio');
        });
    }
};
