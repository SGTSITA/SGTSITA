<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipoConceptoAndPolizaIdToBancosMovimientosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('cat_bancos_cuentas_movimientos')) {
            Schema::table('cat_bancos_cuentas_movimientos', function (Blueprint $table) {
                if (!Schema::hasColumn('cat_bancos_cuentas_movimientos', 'tipo_concepto')) {
                    $table->string('tipo_concepto', 40)->default('operativo')->after('tipo')->comment('operativo, interes, traspaso, comision');
                }
                if (!Schema::hasColumn('cat_bancos_cuentas_movimientos', 'poliza_id')) {
                    $table->unsignedBigInteger('poliza_id')->nullable()->after('referenciaable_id');
                    $table->foreign('poliza_id')->references('id')->on('polizas')->onDelete('set null');
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
        if (Schema::hasTable('cat_bancos_cuentas_movimientos')) {
            Schema::table('cat_bancos_cuentas_movimientos', function (Blueprint $table) {
                if (Schema::hasColumn('cat_bancos_cuentas_movimientos', 'poliza_id')) {
                    $table->dropForeign(['poliza_id']);
                    $table->dropColumn('poliza_id');
                }
                if (Schema::hasColumn('cat_bancos_cuentas_movimientos', 'tipo_concepto')) {
                    $table->dropColumn('tipo_concepto');
                }
            });
        }
    }
}
