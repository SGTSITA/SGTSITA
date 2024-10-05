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
        Schema::create('operadores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->text('domicilio')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('comprobante_domicilio')->nullable();
            $table->text('ine')->nullable();
            $table->text('cedula_fiscal')->nullable();
            $table->text('licencia_conducir')->nullable();
            $table->text('acceso')->nullable();
            $table->text('correo')->nullable();
            $table->text('telefono')->nullable();
            $table->text('tipo_sangre')->nullable();
            $table->text('nss')->nullable();
            $table->text('recomendacion')->nullable();
            $table->text('foto')->nullable();
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
        Schema::dropIfExists('operadores');
    }
};
