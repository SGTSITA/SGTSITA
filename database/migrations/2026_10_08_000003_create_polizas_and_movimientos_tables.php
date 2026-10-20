<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePolizasAndMovimientosTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('polizas')) {
            Schema::create('polizas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_empresa');
                $table->unsignedBigInteger('id_equipo')->nullable()->comment('FK opcional para contabilidad por camión');
                $table->enum('tipo', ['ingreso', 'egreso', 'diario'])->default('diario');
                $table->string('numero', 30)->comment('Folio consecutivo');
                $table->date('fecha');
                $table->string('concepto', 255);
                $table->string('origen_tipo', 60)->nullable()->comment('banco_movimiento, gasto, viaje, socio_corte');
                $table->unsignedBigInteger('origen_id')->nullable();
                $table->enum('estatus', ['borrador', 'aplicada', 'cancelada'])->default('aplicada');
                $table->decimal('total_debe', 15, 2)->default(0.00);
                $table->decimal('total_haber', 15, 2)->default(0.00);
                $table->unsignedBigInteger('creado_por')->nullable();
                $table->timestamps();

                $table->index('id_empresa');
                $table->index('id_equipo');
                $table->index('tipo');
                $table->index('fecha');
                $table->index(['origen_tipo', 'origen_id']);

                if (Schema::hasTable('empresas')) {
                    $table->foreign('id_empresa')->references('id')->on('empresas')->onDelete('cascade');
                }
                if (Schema::hasTable('equipos')) {
                    $table->foreign('id_equipo')->references('id')->on('equipos')->onDelete('set null');
                }
                if (Schema::hasTable('users')) {
                    $table->foreign('creado_por')->references('id')->on('users')->onDelete('set null');
                }
            });
        }

        if (!Schema::hasTable('poliza_movimientos')) {
            Schema::create('poliza_movimientos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('poliza_id');
                $table->unsignedBigInteger('cuenta_contable_id');
                $table->unsignedBigInteger('id_equipo')->nullable();
                $table->integer('linea')->default(1);
                $table->string('referencia', 100)->nullable();
                $table->string('concepto_detalle', 255)->nullable();
                $table->decimal('debe', 15, 2)->default(0.00);
                $table->decimal('haber', 15, 2)->default(0.00);
                $table->timestamps();

                $table->index('poliza_id');
                $table->index('cuenta_contable_id');
                $table->index('id_equipo');

                $table->foreign('poliza_id')->references('id')->on('polizas')->onDelete('cascade');
                $table->foreign('cuenta_contable_id')->references('id')->on('cat_cuentas_contables');
                if (Schema::hasTable('equipos')) {
                    $table->foreign('id_equipo')->references('id')->on('equipos')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('poliza_movimientos');
        Schema::dropIfExists('polizas');
    }
}
