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
            $table->text('burreo')->nullable();
            $table->text('maniobra')->nullable();
            $table->text('estadia')->nullable();
            $table->text('otro')->nullable();
            $table->float('iva')->nullable();
            $table->text('retencion')->nullable();
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
