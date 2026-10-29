<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bitacora_viajes_operadores', function (Blueprint $table) {
            $table->dateTime('fecha_carga_diesel')->nullable()->after('comprobante');
            $table->dateTime('fecha_carga_urea')->nullable()->after('comprobante_urea');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bitacora_viajes_operadores', function (Blueprint $table) {
            $table->dropColumn(['fecha_carga_diesel', 'fecha_carga_urea']);
        });
    }
};
