<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cobros_pagos', function (Blueprint $table) {
            $table->id();

            $table->enum('tipo', ['cxc', 'cxp']);

            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('proveedor_id')->nullable();


            $table->unsignedBigInteger('bancoA_id')->nullable();
            $table->decimal('monto_A', 15, 2)->default(0);
            $table->date('fechaAplicacion1')->nullable();
            $table->unsignedBigInteger('banco_proveedor_idA')->nullable();


            $table->unsignedBigInteger('bancoB_id')->nullable();
            $table->decimal('monto_B', 15, 2)->default(0);
            $table->date('fechaAplicacion2')->nullable();
            $table->unsignedBigInteger('banco_proveedor_idB')->nullable();


            $table->unsignedBigInteger('user_id');

            $table->text('observaciones')->nullable();

            $table->timestamps();


            $table->foreign('cliente_id')->references('id')->on('clients')->restrictOnDelete();
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();

            $table->foreign('bancoB_id')->references('id')->on('bancos')->restrictOnDelete();
            $table->foreign('bancoA_id')->references('id')->on('bancos')->restrictOnDelete();
            $table->foreign('banco_proveedor_idA')->references('id')->on('cuentas_bancarias')->restrictOnDelete();
            $table->foreign('banco_proveedor_idB')->references('id')->on('cuentas_bancarias')->restrictOnDelete();

            $table->index('tipo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cobros_pagos');
    }
};
