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
        Schema::create('banco_dinero', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cliente');
            $table->foreign('id_cliente')
                ->references('id')->on('clients')
                ->inDelete('set null');

            $table->text('contenedores')->nullable();
            $table->float('monto1')->nullable();
            $table->text('metodo_pago1')->nullable();
            $table->text('comprobante_pago1')->nullable();

            $table->float('monto2')->nullable();
            $table->text('metodo_pago2')->nullable();
            $table->text('comprobante_pago2')->nullable();
            $table->date('fecha_pago')->nullable();

            $table->unsignedBigInteger('id_banco1')->nullable();
            $table->foreign('id_banco1')
                ->references('id')->on('bancos')
                ->inDelete('set null');

            $table->unsignedBigInteger('id_banco2')->nullable();
            $table->foreign('id_banco2')
                ->references('id')->on('bancos')
                ->inDelete('set null');

            $table->text('tipo')->nullable();
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
        Schema::dropIfExists('banco_dinero');
    }
};
