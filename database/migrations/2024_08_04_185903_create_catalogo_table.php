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
        Schema::create('catalogo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cliente');
            $table->foreign('id_cliente')
                ->references('id')->on('clients')
                ->inDelete('set null');

            $table->unsignedBigInteger('id_subcliente')->nullable();
            $table->foreign('id_subcliente')
                ->references('id')->on('subclientes')
                ->inDelete('set null');

            $table->unsignedBigInteger('id_empresa');
            $table->foreign('id_empresa')
                ->references('id')->on('empresas')
                ->inDelete('set null');

            $table->text('destino')->nullable();
            $table->text('num_contenedor')->nullable();
            $table->text('tamano')->nullable();
            $table->text('peso_reglamentario')->nullable();
            $table->text('peso_contenedor')->nullable();
            $table->text('sobrepeso')->nullable();
            $table->text('precio_sobre_peso')->nullable();
            $table->text('precio_tonelada')->nullable();
            $table->text('precio_viaje')->nullable();
            $table->text('burreo')->nullable();
            $table->text('maniobra')->nullable();
            $table->text('estadia')->nullable();
            $table->text('otro')->nullable();
            $table->text('iva')->nullable();
            $table->text('retencion')->nullable();
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
        Schema::dropIfExists('catalogo');
    }
};
