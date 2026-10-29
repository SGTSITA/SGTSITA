<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContabilidadEntidadesCuentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('contabilidad_entidades_cuentas')) {
            Schema::create('contabilidad_entidades_cuentas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_empresa')->comment('Empresa a la que pertenece el mapeo');
                $table->string('modulo', 40)->comment('Modulo: bancos, clientes, proveedores, categorias_gastos');
                $table->unsignedBigInteger('entidad_id')->comment('ID de la entidad operativa (bancos.id, clientes.id, etc.)');
                $table->unsignedBigInteger('cuenta_contable_id')->comment('Subcuenta analitica asignada en cat_cuentas_contables');
                $table->boolean('activo')->default(true);
                $table->timestamps();

                $table->index('id_empresa');
                $table->index('modulo');
                $table->index('entidad_id');
                $table->index('cuenta_contable_id');
                $table->unique(['id_empresa', 'modulo', 'entidad_id'], 'uniq_empresa_mod_entidad');

                if (Schema::hasTable('empresas')) {
                    $table->foreign('id_empresa')->references('id')->on('empresas')->onDelete('cascade');
                }
                $table->foreign('cuenta_contable_id')->references('id')->on('cat_cuentas_contables')->onDelete('cascade');
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
        Schema::dropIfExists('contabilidad_entidades_cuentas');
    }
}
