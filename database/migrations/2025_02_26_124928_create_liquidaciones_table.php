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
        Schema::create('liquidaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_operador');
            $table->unsignedBigInteger('id_banco');
            $table->date('fecha');
            $table->integer('viajes_realizados');
            $table->decimal('sueldo_operador',10,2);
            $table->decimal('dinero_viaje',10,2);
            $table->decimal('dinero_justificado',10,2);
            $table->decimal('total_pago',10,2);
            $table->timestamps();

            $table->foreign('id_operador')->references('id')->on('operadores');
            $table->foreign('id_banco')->references('id')->on('bancos');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('liquidaciones');
    }
};
