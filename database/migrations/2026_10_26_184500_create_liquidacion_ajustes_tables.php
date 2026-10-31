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
        Schema::create('liquidacion_ajustes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_empresa')->index();
            $table->unsignedBigInteger('id_liquidacion')->index();
            $table->unsignedBigInteger('id_operador')->index();
            $table->unsignedBigInteger('id_banco')->nullable()->index();
            $table->date('fecha');
            $table->string('folio', 50)->nullable()->index();
            $table->text('motivo');
            $table->decimal('total_percepciones', 10, 2)->default(0);
            $table->decimal('total_deducciones', 10, 2)->default(0);
            $table->decimal('total_neto', 10, 2)->default(0);
            $table->string('estatus', 30)->default('aplicado');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('id_liquidacion')->references('id')->on('liquidaciones')->onDelete('cascade');
            $table->foreign('id_operador')->references('id')->on('operadores')->onDelete('cascade');
            $table->foreign('id_banco')->references('id')->on('bancos')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('liquidacion_ajustes_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_ajuste')->index();
            $table->enum('tipo', ['percepcion', 'deduccion'])->index();
            $table->string('categoria_concepto', 50)->index();
            $table->unsignedBigInteger('id_prestamo')->nullable()->index();
            $table->text('descripcion')->nullable();
            $table->decimal('monto', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('id_ajuste')->references('id')->on('liquidacion_ajustes')->onDelete('cascade');
            $table->foreign('id_prestamo')->references('id')->on('prestamos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('liquidacion_ajustes_detalles');
        Schema::dropIfExists('liquidacion_ajustes');
    }
};
