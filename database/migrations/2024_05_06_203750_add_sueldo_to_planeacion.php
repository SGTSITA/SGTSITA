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
            $table->float('sueldo_viaje')->nullable();
            $table->float('dinero_viaje')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('planeacion', function (Blueprint $table) {
            //
        });
    }
};
