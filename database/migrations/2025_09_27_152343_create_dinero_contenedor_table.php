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
        Schema::create('dinero_contenedor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_contenedor');
            $table->unsignedBigInteger('id_banco');
            $table->string('motivo');
            $table->decimal('monto');
            $table->date('fecha_entrega_monto');
            $table->timestamps();

            $table->foreign('id_contenedor')->references('id')->on('docum_cotizacion');
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
        Schema::dropIfExists('dinero_asignacion');
    }
};
