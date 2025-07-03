<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->decimal('latitud', 10, 6)->nullable()->after('referencia_full');
            $table->decimal('longitud', 10, 6)->nullable()->after('latitud');
            $table->text('direccion_mapa')->nullable()->after('longitud');
            $table->dateTime('fecha_seleccion_ubicacion')->nullable()->after('direccion_mapa');
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud', 'direccion_mapa', 'fecha_seleccion_ubicacion']);
        });
    }
};
