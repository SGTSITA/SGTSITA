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
        Schema::create('scb_bancos_modulo_cuentas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('banco_id')->constrained('scb_bancos_modulo');
    $table->string('beneficiario', 150)->nullable();
    $table->string('numero_cuenta', 100)->nullable();
    $table->string('clabe', 100)->nullable();
    $table->string('moneda', 10)->default('MXN');
    $table->decimal('saldo_inicial', 14, 2)->default(0);
    $table->boolean('activo')->default(true);
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
        //
    }
};
