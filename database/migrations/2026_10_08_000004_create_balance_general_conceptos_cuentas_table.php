<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalanceGeneralConceptosCuentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('balance_general_conceptos_cuentas')) {
            Schema::create('balance_general_conceptos_cuentas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_empresa')->nullable()->comment('NULL=plantilla general; ID=personalizada por empresa');
                $table->enum('seccion', ['activo', 'pasivo', 'patrimonio', 'estado_resultados'])->default('activo');
                $table->string('concepto', 100);
                $table->string('cuenta_codigo_prefijo', 40)->nullable()->comment('Prefijo de código ej. 1.1.1.2% o 4.3.1%');
                $table->unsignedBigInteger('cuenta_id')->nullable();
                $table->tinyInteger('columna')->default(1)->comment('1=Activo izq, 2=Pasivo/Patrimonio der, 3=Estado de Resultados');
                $table->integer('orden')->default(0);
                $table->boolean('es_calculado')->default(false)->comment('1=fórmula o suma de utilidades; 0=saldo de cuentas');
                $table->boolean('activo')->default(true);
                $table->timestamps();

                $table->index('id_empresa');
                $table->index('seccion');
                $table->index('columna');
                $table->index('orden');

                if (Schema::hasTable('empresas')) {
                    $table->foreign('id_empresa')->references('id')->on('empresas')->onDelete('cascade');
                }
                $table->foreign('cuenta_id')->references('id')->on('cat_cuentas_contables')->onDelete('set null');
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
        Schema::dropIfExists('balance_general_conceptos_cuentas');
    }
}
