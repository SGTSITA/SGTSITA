<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPagosSociosToBalanceGeneralConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('balance_general_configs')) {
            return;
        }

        // 1. Insert default template for future enterprises
        $defaultExists = DB::table('balance_general_configs')
            ->whereNull('id_empresa')
            ->where('tipo_calculo', 'pagos_socios')
            ->exists();

        if (!$defaultExists) {
            DB::table('balance_general_configs')->insert([
                'id_empresa'   => null,
                'grupo'        => 'capital',
                'concepto'     => '(-) RETIROS A SOCIOS',
                'tipo_calculo' => 'pagos_socios',
                'valor_manual' => 0.00,
                'orden'        => 15,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // 2. Insert for existing enterprises configured in balance_general_configs
        $empresasWithConfigs = DB::table('balance_general_configs')
            ->whereNotNull('id_empresa')
            ->select('id_empresa')
            ->distinct()
            ->pluck('id_empresa');

        foreach ($empresasWithConfigs as $empId) {
            $exists = DB::table('balance_general_configs')
                ->where('id_empresa', $empId)
                ->where('tipo_calculo', 'pagos_socios')
                ->exists();

            if (!$exists) {
                DB::table('balance_general_configs')->insert([
                    'id_empresa'   => $empId,
                    'grupo'        => 'capital',
                    'concepto'     => '(-) RETIROS A SOCIOS',
                    'tipo_calculo' => 'pagos_socios',
                    'valor_manual' => 0.00,
                    'orden'        => 15,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('balance_general_configs')) {
            DB::table('balance_general_configs')
                ->where('tipo_calculo', 'pagos_socios')
                ->delete();
        }
    }
}
