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
    Schema::create('costos_mep_pendientes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('id_asignacion'); // FK a asignaciones

        // Cambiar de (10,2) a (12,4) para más precisión
        $table->decimal('precio_viaje', 12, 4)->nullable();
        $table->decimal('burreo', 12, 4)->nullable();
        $table->decimal('maniobra', 12, 4)->nullable();
        $table->decimal('estadia', 12, 4)->nullable();
        $table->decimal('otro', 12, 4)->nullable();
        $table->decimal('iva', 12, 4)->nullable();
        $table->decimal('retencion', 12, 4)->nullable();
        $table->decimal('base1', 12, 4)->nullable();
        $table->decimal('base2', 12, 4)->nullable();
        $table->decimal('sobrepeso', 12, 4)->nullable();
        $table->decimal('precio_sobrepeso', 12, 4)->nullable();

        $table->enum('estatus', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
        $table->text('motivo_cambio')->nullable();
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
        Schema::dropIfExists('costos_mep_pendientes');
    }
};
