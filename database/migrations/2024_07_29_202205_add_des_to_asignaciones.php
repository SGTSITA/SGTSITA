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
            $table->text('descripcion_otro1')->nullable();
            $table->text('descripcion_otro2')->nullable();
            $table->text('descripcion_otro3')->nullable();
            $table->text('descripcion_otro4')->nullable();
            $table->text('descripcion_otro5')->nullable();
            $table->text('descripcion_otro6')->nullable();
            $table->text('descripcion_otro7')->nullable();
            $table->text('descripcion_otro8')->nullable();
            $table->text('descripcion_otro9')->nullable();
            $table->text('descripcion_otro10')->nullable();
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
