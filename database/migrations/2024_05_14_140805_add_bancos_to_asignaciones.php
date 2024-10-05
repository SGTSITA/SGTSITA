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
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('id_banco1_dinero_viaje')->nullable();
            $table->foreign('id_banco1_dinero_viaje')
                ->references('id')->on('bancos')
                ->inDelete('set null');

            $table->text('cantidad_banco1_dinero_viaje')->nullable();

            $table->unsignedBigInteger('id_banco2_dinero_viaje')->nullable();
            $table->foreign('id_banco2_dinero_viaje')
                ->references('id')->on('bancos')
                ->inDelete('set null');

            $table->text('cantidad_banco2_dinero_viaje')->nullable();

            $table->unsignedBigInteger('id_banco1_pago_operador')->nullable();
            $table->foreign('id_banco1_pago_operador')
                ->references('id')->on('bancos')
                ->inDelete('set null');

            $table->text('cantidad_banco1_pago_operador')->nullable();

            $table->unsignedBigInteger('id_banco2_pago_operador')->nullable();
            $table->foreign('id_banco2_pago_operador')
                ->references('id')->on('bancos')
                ->inDelete('set null');

            $table->text('cantidad_banco2_pago_operador')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            //
        });
    }
};
