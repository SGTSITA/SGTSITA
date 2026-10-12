<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuiaContabilizadoraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('guia_contabilizadora')) {
            Schema::create('guia_contabilizadora', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_empresa')->nullable()->comment('NULL=regla default global; ID=regla específica de empresa');
                $table->string('evento', 60)->comment('Clave del evento operativo: INTERES_BANCARIO, GASTO_OPERATIVO, etc.');
                $table->string('subtipo', 60)->nullable();
                $table->enum('tipo_poliza', ['ingreso', 'egreso', 'diario'])->default('diario');
                $table->unsignedBigInteger('cuenta_cargo_id')->nullable();
                $table->unsignedBigInteger('cuenta_abono_id')->nullable();
                $table->string('regla_dinamica', 60)->nullable()->comment('cuenta_banco, cuenta_categoria_gasto, etc.');
                $table->string('descripcion_asiento', 191)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();

                $table->index('id_empresa');
                $table->index('evento');
                $table->index('activo');

                if (Schema::hasTable('empresas')) {
                    $table->foreign('id_empresa')->references('id')->on('empresas')->onDelete('cascade');
                }
                $table->foreign('cuenta_cargo_id')->references('id')->on('cat_cuentas_contables')->onDelete('set null');
                $table->foreign('cuenta_abono_id')->references('id')->on('cat_cuentas_contables')->onDelete('set null');
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
        Schema::dropIfExists('guia_contabilizadora');
    }
}
