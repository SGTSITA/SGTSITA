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
        Schema::table('empresas', function (Blueprint $table) {
            
           
            $table->unsignedBigInteger('id_sat_regimen')->after('fecha')->nullable();
            $table->unsignedBigInteger('id_tipo_empresa')->after('fecha')->nullable();

            $table->foreign('id_tipo_empresa')->references('id')->on('tipos_empresa');
           
            $table->foreign('id_sat_regimen')->references('id')->on('sat_regimen_fiscal');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('empresas', function (Blueprint $table) {
            //
        });
    }
};
