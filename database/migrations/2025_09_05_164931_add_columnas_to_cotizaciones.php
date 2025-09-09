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
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->string('cp_fraccion')->nullable();
            $table->string('cp_clave_sat')->nullable();
            $table->string('cp_pedimento')->nullable();
            $table->string('cp_clase_ped')->nullable();
            $table->string('cp_cantidad')->nullable();
            $table->string('cp_valor')->nullable();
            $table->string('cp_moneda')->nullable();
            $table->string('cp_contacto_entrega')->nullable();
            $table->date('cp_fecha_tentativa_entrega')->nullable();
            $table->string('cp_hora_tentativa_entrega')->nullable();
            $table->string('cp_comentarios')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            //
        });
    }
};
