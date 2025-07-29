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
        Schema::create('servicio_gps_empresa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_gps_company');
            $table->unsignedBigInteger('id_empresa');
            $table->longText('account_info');

            $table->foreign('id_empresa')->references('id')->on('empresas');
            $table->foreign('id_gps_company')->references('id')->on('gps_company');
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
        Schema::dropIfExists('servicio_gps_empresa');
    }
};
