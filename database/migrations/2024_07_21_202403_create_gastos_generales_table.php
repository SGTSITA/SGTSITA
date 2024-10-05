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
        Schema::create('gastos_generales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_empresa');
            $table->foreign('id_empresa')
                ->references('id')->on('empresas')
                ->inDelete('set null');

            $table->text('motivo')->nullable();
            $table->text('monto1')->nullable();
            $table->text('metodo_pago1')->nullable();
            $table->unsignedBigInteger('id_banco1')->nullable();
            $table->foreign('id_banco1')
                ->references('id')->on('bancos')
                ->inDelete('set null');

            $table->text('monto2')->nullable();
            $table->text('metodo_pago2')->nullable();
            $table->unsignedBigInteger('id_banco2')->nullable();
            $table->foreign('id_banco2')
                ->references('id')->on('bancos')
                ->inDelete('set null');

            $table->date('fecha');
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
        Schema::dropIfExists('gastos_generales');
    }
};
