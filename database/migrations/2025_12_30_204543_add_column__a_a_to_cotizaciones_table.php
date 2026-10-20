<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bitacora_cotizaciones_estatus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cotizaciones_id');
            $table->unsignedBigInteger('estatus_id');
            $table->text('nota')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('cotizaciones_id')->references('id')->on('cotizaciones');
            $table->foreign('estatus_id')->references('id')->on('estatus_maniobras');
            $table->foreign('user_id')->references('id')->on('users');
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
