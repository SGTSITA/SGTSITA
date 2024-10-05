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
            $table->text('otro2')->nullable();
            $table->text('otro3')->nullable();
            $table->text('otro4')->nullable();
            $table->text('otro5')->nullable();
            $table->text('otro6')->nullable();
            $table->text('otro7')->nullable();
            $table->text('otro8')->nullable();
            $table->text('otro9')->nullable();
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
