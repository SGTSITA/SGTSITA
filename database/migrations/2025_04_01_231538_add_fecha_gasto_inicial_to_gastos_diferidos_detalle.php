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
        Schema::table('gastos_diferidos_detalle', function (Blueprint $table) {
            $table->date('fecha_gasto_final')->after('fecha_gasto')->nullable();
            $table->date('fecha_gasto_inicial')->after('fecha_gasto')->nullable();
            $table->dropColumn('fecha_gasto');
            

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gastos_diferidos_detalle', function (Blueprint $table) {
            //
        });
    }
};
