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
       Schema::create('scb_bancos_modulo_cuentas_movimientos_detalles', function (Blueprint $table) {
    $table->id();

    $table->unsignedBigInteger('movimiento_id');

$table->foreign('movimiento_id', 'fk_scb_mov_det_mov')
    ->references('id')
    ->on('scb_bancos_modulo_cuentas_movimientos')
    ->cascadeOnDelete();

    $table->string('descripcion', 255);
    $table->string('referencia', 150)->nullable();

     $table->unsignedBigInteger('unidad_id')->nullable();

$table->foreign('unidad_id', 'fk_scb_mov_det_unidad')
    ->references('id')
    ->on('scb_bancos_unidades_modulo')
    ->nullOnDelete();

    $table->decimal('monto', 14, 2);

    $table->text('observaciones')->nullable();

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
