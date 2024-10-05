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
            $table->unsignedBigInteger('id_proveedor')->nullable();
            $table->foreign('id_proveedor')
                ->references('id')->on('proveedores')
                ->inDelete('set null');

            $table->float('precio')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            //
        });
    }
};
