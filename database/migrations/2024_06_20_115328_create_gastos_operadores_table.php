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
        Schema::create('gastos_operadores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cotizacion');
            $table->foreign('id_cotizacion')
                ->references('id')->on('cotizaciones')
                ->inDelete('set null');

            $table->unsignedBigInteger('id_asignacion');
            $table->foreign('id_asignacion')
                ->references('id')->on('asignaciones')
                ->inDelete('set null');

            $table->unsignedBigInteger('id_operador');
            $table->foreign('id_operador')
                ->references('id')->on('operadores')
                ->inDelete('set null');

            $table->text('cantidad')->nullable();
            $table->text('tipo')->nullable();
            $table->text('comprobante')->nullable();
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
        Schema::dropIfExists('gastos_operadores');
    }
};
