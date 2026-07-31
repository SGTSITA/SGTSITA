<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balance_general_saldos_iniciales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_empresa');
            $table->unsignedBigInteger('config_id');
            $table->integer('ejercicio'); // e.g. 2026
            $table->date('fecha_inicio'); // e.g. 2026-01-01
            $table->decimal('monto', 15, 2)->default(0.00); // Supports negative values
            $table->timestamps();

            $table->foreign('id_empresa')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('config_id')->references('id')->on('balance_general_configs')->onDelete('cascade');
            $table->unique(['id_empresa', 'config_id', 'ejercicio'], 'bg_saldos_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('balance_general_saldos_iniciales');
    }
};
