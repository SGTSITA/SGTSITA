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
        Schema::table('gastos_diferidos_detalle', function (Blueprint $table) {
            $table->unsignedBigInteger('id_equipo')->after('gasto_dia')->nullable();
            $table->foreign('id_equipo')->references('id')->on('equipos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gastos_diferidos_detalle', function (Blueprint $table) {
            //
        });
    }
};
