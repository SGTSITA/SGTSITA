<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorreosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correos', function (Blueprint $table) {
            $table->id(); // ID único y autoincremental
            $table->string('correo'); // Dirección de correo
            $table->enum('tipo_correo', ['SGT', 'MEC', 'Otro']); // Tipo de correo
            $table->string('referencia')->nullable(); // Referencia
            $table->boolean('notificacion_nueva')->default(false); // Notificación nueva
            $table->boolean('cancelacion_viaje')->default(false); // Cancelación de viaje
            $table->boolean('nuevo_documento')->default(false); // Nuevo documento cargado
            $table->boolean('viaje_modificado')->default(false); // Viaje modificado
            $table->timestamps(); // Timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('correos');
    }
}