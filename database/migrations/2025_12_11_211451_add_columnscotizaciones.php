<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->boolean('confirmacion_sello')->nullable()->after('cp_clase_ped'); 
            $table->boolean('nuevo_sello')->nullable()->after('confirmacion_sello');

        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn(['confirmacion_sello', 'nuevo_sello']);
        });
    }
};
