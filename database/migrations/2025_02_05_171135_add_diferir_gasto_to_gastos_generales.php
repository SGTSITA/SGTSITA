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
            $table->date('fecha_diferido_final')->after('fecha_operacion')->nullable();
            $table->date('fecha_diferido_inicial')->after('fecha_operacion')->nullable();
            $table->integer('diferir_contador_periodos')->default(0)->after('fecha_operacion');
            $table->boolean('diferir_gasto')->default(0)->after('fecha_operacion');
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
