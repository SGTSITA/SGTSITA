<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNuevasColumnasPatioToCoordenadasTable  extends Migration
{
    public function up(): void
    {
        Schema::table('coordenadas', function (Blueprint $table) {
            $table->text('descarga_patio')->nullable()->after('recepcion_doc_firmados_datatime'); // reemplazá esto si querés un orden
            $table->dateTime('descarga_patio_datetime')->nullable()->after('descarga_patio');
            $table->text('cargado_patio')->nullable()->after('descarga_patio_datetime');
            $table->dateTime('cargado_patio_datetime')->nullable()->after('cargado_patio');
        });
    }

    public function down(): void
    {
        Schema::table('coordenadas', function (Blueprint $table) {
            $table->dropColumn([
                'descarga_patio',
                'descarga_patio_datetime',
                'cargado_patio',
                'cargado_patio_datetime',
            ]);
        });
    }
}
