<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatEmpresaCuentasConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cat_empresa_cuentas_config')) {
            Schema::create('cat_empresa_cuentas_config', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_empresa')->nullable()->comment('NULL=regla global; ID=específica de empresa');
                $table->string('rol_clave', 60)->comment('Clave del rol funcional ej: CLIENTES_CXC, PROVEEDORES_CXP, CAJA_OPERATIVA');
                $table->unsignedBigInteger('cuenta_contable_id')->comment('ID en cat_cuentas_contables');
                $table->string('descripcion', 191)->nullable()->comment('Nota u orientación del rol');
                $table->boolean('activo')->default(true);
                $table->timestamps();

                $table->index('id_empresa');
                $table->index('rol_clave');
                $table->index('activo');
                $table->unique(['id_empresa', 'rol_clave'], 'uniq_empresa_rol_config');

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
        Schema::dropIfExists('cat_empresa_cuentas_config');
    }
}
