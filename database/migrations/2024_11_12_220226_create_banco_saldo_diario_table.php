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
        Schema::create('banco_saldo_diario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_banco')->nullable();
            $table->date('fecha');
            $table->float('saldo_inicial');
            $table->float('saldo_final');
            $table->foreign('id_banco')->references('id')->on('bancos');

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
        Schema::dropIfExists('banco_saldo_diario');
    }
};
