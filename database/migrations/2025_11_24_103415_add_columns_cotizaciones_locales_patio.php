<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {

            $table->boolean('en_patio')->nullable()->default(0)->after('bloque_hora_i_local');
            $table->time('fecha_en_patio')->nullable()->after('en_patio');


        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn([
                'en_patio',
                'fecha_en_patio'
            ]);
        });
    }
};
