<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->string('origen_local')->nullable()->after('cp_comentarios');
            $table->string('destino_local')->nullable()->after('origen_local');
            $table->decimal('costo_maniobra_local', 10, 2)->nullable()->after('destino_local');

            $table->string('estado_contenedor')->nullable()->after('costo_maniobra_local'); // VERDE, AMARILLO, ROJO, OVT

            $table->dateTime('fecha_modulacion_local')->nullable()->after('estado_contenedor');
            $table->unsignedBigInteger('empresa_local')->nullable()->after('fecha_modulacion_local');
            $table->unsignedBigInteger('sub_cliente_local')->nullable()->after('empresa_local');
            $table->unsignedBigInteger('transportista_local')->nullable()->after('sub_cliente_local');
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn([
                'origen_local',
                'destino_local',
                'costo_maniobra_local',
                'estado_contenedor',
                'fecha_modulacion_local',
                'empresa_local',
                'sub_cliente_local',
                'transportista_local',
            ]);
        });
    }
};
