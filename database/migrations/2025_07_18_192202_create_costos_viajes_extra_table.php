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
    Schema::create('costos_viajes_extra', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('viaje_id')->unique(); // Asume 1:1 con tabla de viajes
        $table->decimal('base1', 10, 4)->nullable();
        $table->decimal('base2', 10, 4)->nullable();
        $table->timestamps();

        $table->foreign('viaje_id')->references('id')->on('viajes')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('costos_viajes_extra');
    }
};
