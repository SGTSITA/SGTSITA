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
        Schema::create('coordenadas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_asignacion')->nullable();
            $table->foreign('id_asignacion')
                ->references('id')->on('asignaciones')
                ->inDelete('set null');

            $table->unsignedBigInteger('id_cotizacion')->nullable();
            $table->foreign('id_cotizacion')
                ->references('id')->on('cotizaciones')
                ->inDelete('set null');

            $table->text('tipo_flujo')->nullable();
            $table->dateTime('tipo_flujo_datatime')->nullable();

            $table->text('registro_puerto')->nullable();
            $table->dateTime('registro_puerto_datatime')->nullable();

            $table->text('dentro_puerto')->nullable();
            $table->dateTime('dentro_puerto_datatime')->nullable();

            $table->text('descarga_vacio')->nullable();
            $table->dateTime('descarga_vacio_datatime')->nullable();

            $table->text('cargado_contenedor')->nullable();
            $table->dateTime('cargado_contenedor_datatime')->nullable();

            $table->text('fila_fiscal')->nullable();
            $table->dateTime('fila_fiscal_datatime')->nullable();

            $table->text('modulado_tipo')->nullable();
            $table->dateTime('modulado_tipo_datatime')->nullable();

            $table->text('modulado_coordenada')->nullable();
            $table->dateTime('modulado_coordenada_datatime')->nullable();

            $table->text('en_destino')->nullable();
            $table->dateTime('en_destino_datatime')->nullable();

            $table->text('inicio_descarga')->nullable();
            $table->dateTime('inicio_descarga_datatime')->nullable();

            $table->text('fin_descarga')->nullable();
            $table->dateTime('fin_descarga_datatime')->nullable();

            $table->text('recepcion_doc_firmados')->nullable();
            $table->dateTime('recepcion_doc_firmados_datatime')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coordenadas');
    }
};
