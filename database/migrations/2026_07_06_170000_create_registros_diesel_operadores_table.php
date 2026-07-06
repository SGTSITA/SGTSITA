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
        Schema::create('registros_diesel_operadores', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('id_asignacion')->nullable();
            $table->foreign('id_asignacion')
                ->references('id')->on('asignaciones')
                ->onDelete('set null');

            $table->unsignedBigInteger('id_operador')->nullable();
            $table->foreign('id_operador')
                ->references('id')->on('operadores')
                ->onDelete('set null');

            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->decimal('litros', 10, 2)->nullable();
            $table->decimal('costo', 12, 2)->nullable();
            $table->string('odometro')->nullable();
            $table->string('comprobante')->nullable();
            
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
        Schema::dropIfExists('registros_diesel_operadores');
    }
};
