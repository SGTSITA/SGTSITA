<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatCuentasContablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cat_cuentas_contables')) {
            Schema::create('cat_cuentas_contables', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_empresa')->nullable()->comment('NULL para cuentas generales CONAC; ID para cuentas propias de la filial');
                $table->string('codigo', 40)->comment('Código jerárquico contable ej. 1.1.1.2.001');
                $table->string('nombre', 191);
                $table->tinyInteger('nivel')->default(1)->comment('1=Género, 2=Grupo, 3=Rubro, 4=Cuenta, 5=Subcuenta analítica');
                $table->unsignedBigInteger('cuenta_padre_id')->nullable();
                $table->enum('rubro', ['activo', 'pasivo', 'capital', 'ingresos', 'costos', 'gastos', 'resultado'])->default('activo');
                $table->enum('naturaleza', ['deudora', 'acreedora'])->default('deudora');
                $table->boolean('es_afectable')->default(false)->comment('1=Afectable por pólizas; 0=Cuenta acumulativa/mayor');
                $table->boolean('activo')->default(true);
                $table->timestamps();

                $table->index('id_empresa');
                $table->index('codigo');
                $table->index('nivel');
                $table->index('cuenta_padre_id');
                $table->index('es_afectable');
                $table->index('rubro');

                if (Schema::hasTable('empresas')) {
                    $table->foreign('id_empresa')->references('id')->on('empresas')->onDelete('cascade');
                }
                $table->foreign('cuenta_padre_id')->references('id')->on('cat_cuentas_contables')->onDelete('set null');
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
        Schema::dropIfExists('cat_cuentas_contables');
    }
}
