<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixDeudoresDiversosCuentaAndConcepto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Asegurar la subcuenta 1.1.2.3.004 (Otros Deudores Diversos)
        if (Schema::hasTable('cat_cuentas_contables')) {
            $padre = DB::table('cat_cuentas_contables')->where('codigo', '1.1.2.3')->first();
            $padreId = $padre?->id;

            $exists = DB::table('cat_cuentas_contables')->where('codigo', '1.1.2.3.004')->exists();
            if (!$exists) {
                DB::table('cat_cuentas_contables')->insert([
                    'codigo'          => '1.1.2.3.004',
                    'nombre'          => 'Otros Deudores Diversos',
                    'cuenta_padre_id' => $padreId,
                    'id_empresa'      => null,
                    'nivel'           => 5,
                    'rubro'           => 'activo',
                    'naturaleza'      => 'deudora',
                    'es_afectable'    => true,
                    'activo'          => true,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        // 2. Corregir mapeo en balance_general_conceptos_cuentas
        if (Schema::hasTable('balance_general_conceptos_cuentas')) {
            DB::table('balance_general_conceptos_cuentas')
                ->where('concepto', 'DEUDORES DIVERSOS')
                ->update([
                    'cuenta_codigo_prefijo' => '1.1.2.3.004%',
                    'updated_at'            => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('balance_general_conceptos_cuentas')) {
            DB::table('balance_general_conceptos_cuentas')
                ->where('concepto', 'DEUDORES DIVERSOS')
                ->update([
                    'cuenta_codigo_prefijo' => '1.1.2.3%',
                    'updated_at'            => now(),
                ]);
        }

        if (Schema::hasTable('cat_cuentas_contables')) {
            DB::table('cat_cuentas_contables')->where('codigo', '1.1.2.3.004')->delete();
        }
    }
}
