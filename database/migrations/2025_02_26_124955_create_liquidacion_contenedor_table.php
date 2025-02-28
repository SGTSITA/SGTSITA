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
        Schema::create('liquidacion_contenedor', function (Blueprint $table) {
            $table->unsignedBigInteger('id_liquidacion');
            $table->unsignedBigInteger('id_contenedor');
            $table->decimal('sueldo_operador',10,2);
            $table->decimal('dinero_viaje',10,2);
            $table->decimal('dinero_justificado',10,2);
            $table->decimal('total_pagado',10,2);

            $table->timestamps();
            $table->foreign('id_liquidacion')->references('id')->on('liquidaciones');
            $table->foreign('id_contenedor')->references('id')->on('docum_cotizacion');

            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('liquidacion_contenedor');
    }
};
