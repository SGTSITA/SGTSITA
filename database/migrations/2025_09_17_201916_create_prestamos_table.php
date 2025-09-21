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
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_operador'); // Relación con operador
            $table->unsignedBigInteger('id_banco'); // Relación con bancos
            $table->decimal('cantidad', 12, 2);
            $table->enum('tipo_descuento', ['exhibicion', 'parcialidades']);
            $table->date('fecha_pago'); // Fecha compromiso / primer pago
            $table->integer('num_parcialidades')->nullable(); // Solo si aplica
            $table->enum('frecuencia', ['semanal', 'quincenal', 'mensual'])->nullable(); // Solo si aplica
            $table->timestamps();


            $table->foreign('id_operador')->references('id')->on('operadores')->onDelete('cascade');
            $table->foreign('id_banco')->references('id')->on('bancos');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prestamos');
    }
};
