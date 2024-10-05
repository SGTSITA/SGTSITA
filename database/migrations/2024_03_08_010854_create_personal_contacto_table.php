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
        Schema::create('personal_contacto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_operadores');
            $table->foreign('id_operadores')
                ->references('id')->on('operadores')
                ->inDelete('set null');

            $table->text('telefono')->nullable();
            $table->text('direccion')->nullable();
            $table->text('correo')->nullable();
            $table->text('nombre')->nullable();
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
        Schema::dropIfExists('personal_contacto');
    }
};
