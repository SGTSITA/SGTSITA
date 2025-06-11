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
        Schema::table('gastos_generales', function (Blueprint $table) {
            $table->integer('gasto_origen_id')->nullable();
            $table->boolean('pago_realizado')->nullable();
            $table->string('aplicacion_gasto')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gastos_generales', function (Blueprint $table) {
            //
        });
    }
};
