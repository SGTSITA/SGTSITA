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
        Schema::create('gastos_extras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cotizacion')->nullable();
            $table->foreign('id_cotizacion')
                ->references('id')->on('cotizaciones')
                ->inDelete('set null');

            $table->text('descripcion')->nullable();
            $table->float('monto')->nullable();
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
        Schema::dropIfExists('gastos_extras');
    }
};
