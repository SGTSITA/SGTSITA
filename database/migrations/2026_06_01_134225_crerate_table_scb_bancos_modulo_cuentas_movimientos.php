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
       Schema::create('scb_bancos_modulo_cuentas_movimientos', function (Blueprint $table) {
   $table->id();

    $table->foreignId('cuenta_id')->constrained('scb_bancos_modulo_cuentas');

    $table->enum('tipo', ['cargo', 'abono']);
    $table->date('fecha_movimiento');

    $table->string('concepto', 255);
    $table->string('referencia_bancaria', 150)->nullable();

    $table->text('observaciones')->nullable();

    $table->foreignId('user_id')->nullable()->constrained('users');

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
