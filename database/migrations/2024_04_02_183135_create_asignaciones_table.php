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
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_camion')->nullable();
            $table->foreign('id_camion')
                ->references('id')->on('equipos')
                ->inDelete('set null');

            $table->unsignedBigInteger('id_chasis')->nullable();
            $table->foreign('id_chasis')
                ->references('id')->on('equipos')
                ->inDelete('set null');

            $table->unsignedBigInteger('id_dolys')->nullable();
            $table->foreign('id_dolys')
                ->references('id')->on('equipos')
                ->inDelete('set null');

            $table->unsignedBigInteger('id_contenedor')->nullable();
            $table->foreign('id_contenedor')
                ->references('id')->on('docum_cotizacion')
                ->inDelete('set null');

            $table->unsignedBigInteger('id_operador')->nullable();
            $table->foreign('id_operador')
                ->references('id')->on('operadores')
                ->inDelete('set null');

            $table->datetime('fecha_inicio');
            $table->datetime('fecha_fin');
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
        Schema::dropIfExists('asignaciones');
    }
};
