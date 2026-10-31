<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Gasto;
use App\Models\GastoImputacion;

class CorregirImputacionesHistoricas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gastos:corregir-imputaciones-historicas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea las imputaciones faltantes para los gastos generales migrados históricamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Iniciando corrección de imputaciones para gastos generales migrados...');

        $gastosMigrados = Gasto::where('origen_legacy', 'gastos_generales')->get();
        $total = $gastosMigrados->count();
        $corregidos = 0;

        $this->info("Se encontraron {$total} gastos migrados desde gastos_generales.");

        foreach ($gastosMigrados as $gasto) {
            // Verificar si ya tiene imputación
            $existeImputacion = DB::table('gasto_imputaciones')
                ->where('gasto_id', $gasto->id)
                ->exists();

            if ($existeImputacion) {
                continue;
            }

            // Buscar el registro original en gastos_generales para saber si era diferido
            $original = DB::table('gastos_generales')
                ->where('id', $gasto->origen_legacy_id)
                ->first();

            $diferido = false;
            if ($original && isset($original->diferir_gasto)) {
                $diferido = (bool) $original->diferir_gasto;
            }

            $tipoImputacion = $diferido ? 'periodo' : 'empresa';
            $origen = $diferido ? 'diferido' : 'directo';

            // Crear la imputación faltante
            DB::table('gasto_imputaciones')->insert([
                'gasto_id' => $gasto->id,
                'gasto_partida_id' => null,
                'periodo_id' => null,
                'fecha_imputacion' => $gasto->fecha_gasto,
                'tipo_imputacion' => $tipoImputacion,
                'imputable_type' => null,
                'imputable_id' => null,
                'monto_imputado' => $gasto->monto_total,
                'origen' => $origen,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $corregidos++;
        }

        $this->info("✔ Proceso terminado. Se corrigieron {$corregidos} de {$total} gastos.");
    }
}
