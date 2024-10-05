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
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cliente');
            $table->foreign('id_cliente')
                ->references('id')->on('clients')
                ->inDelete('set null');

            $table->text('origen')->nullable();
            $table->text('destino')->nullable();
            $table->text('tamano')->nullable();
            $table->text('peso_contenedor')->nullable();
            $table->float('precio_viaje')->nullable();
            $table->text('burreo')->nullable();
            $table->text('maniobra')->nullable();
            $table->text('estadia')->nullable();
            $table->text('otro')->nullable();
            $table->date('fecha_modulacion')->nullable();
            $table->date('fecha_entrega')->nullable();
            $table->float('iva')->nullable();
            $table->text('retencion')->nullable();
            $table->text('estatus')->nullable();
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
        Schema::dropIfExists('cotizaciones');
    }
};
