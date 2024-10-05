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
        Schema::create('banco_dinero_operadores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_operador');
            $table->foreign('id_operador')
                ->references('id')->on('operadores')
                ->inDelete('set null');

            $table->unsignedBigInteger('id_asignacion');
            $table->foreign('id_asignacion')
                ->references('id')->on('asignaciones')
                ->inDelete('set null');

            $table->unsignedBigInteger('id_cotizacion');
            $table->foreign('id_cotizacion')
                ->references('id')->on('cotizaciones')
                ->inDelete('set null');

            $table->unsignedBigInteger('id_banco1')->nullable();
            $table->foreign('id_banco1')
                ->references('id')->on('bancos')
                ->inDelete('set null');

            $table->float('monto1')->nullable();
            $table->text('metodo_pago1')->nullable();
            $table->text('comprobante_pago1')->nullable();

            $table->unsignedBigInteger('id_banco2')->nullable();
            $table->foreign('id_banco2')
                ->references('id')->on('bancos')
                ->inDelete('set null');

            $table->float('monto2')->nullable();
            $table->text('metodo_pago2')->nullable();
            $table->text('comprobante_pago2')->nullable();

            $table->date('fecha_pago')->nullable();
            $table->text('tipo')->nullable();

            $table->unsignedBigInteger('id_empresa');
            $table->foreign('id_empresa')
                ->references('id')->on('empresas')
                ->inDelete('set null');
                
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
        Schema::dropIfExists('banco_dinero_operadores');
    }
};
