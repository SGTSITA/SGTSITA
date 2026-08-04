<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Gasto;
use App\Models\GastoPago;

class CorregirGastosOperadoresPagados extends Command
{
    protected $signature = 'gastos:corregir-operadores-pagados';

    protected $description = 'Corrige el estatus de los gastos migrados de gastos_operadores que debieron marcarse como pagados y les crea su respectivo GastoPago';

    public function handle()
    {
        $this->info('🚀 Iniciando corrección de gastos de operadores...');

        // Obtener todos los gastos de origen gastos_operadores
        $gastos = Gasto::where('origen_legacy', 'gastos_operadores')->get();

        $this->info('Se encontraron ' . $gastos->count() . ' gastos migrados de origen gastos_operadores.');

        $actualizadosAPagado = 0;
        $actualizadosAPendiente = 0;

        foreach ($gastos as $gasto) {
            // Buscar el registro original en la tabla legacy gastos_operadores
            $gOperador = DB::table('gastos_operadores')
                ->where('id', $gasto->origen_legacy_id)
                ->first();

            if (!$gOperador) {
                $this->warn("No se encontró el registro original en gastos_operadores para el Gasto ID: {$gasto->id} (Legacy ID: {$gasto->origen_legacy_id})");
                continue;
            }

            $legacyEstatus = strtolower($gOperador->estatus ?? '');

            if ($legacyEstatus === 'pagado') {
                // Si el origen es pagado pero en el sistema está como pendiente
                if ($gasto->estatus !== 'pagado') {
                    DB::transaction(function () use ($gasto, $gOperador, &$actualizadosAPagado) {
                        $gasto->update(['estatus' => 'pagado']);

                        $existePago = GastoPago::where('gasto_id', $gasto->id)->exists();
                        if (!$existePago) {
                            $monto = floatval($gOperador->cantidad ?? 0);
                            $fecha = $gOperador->fecha_pago ?? $gOperador->created_at ?? now()->toDateString();
                            if (is_string($fecha) && strlen($fecha) > 10) {
                                $fecha = substr($fecha, 0, 10);
                            }

                            $cuentaBancariaId = null;
                            if (!empty($gOperador->id_banco) && $gOperador->id_banco > 0) {
                                $existeBanco = DB::table('bancos')->where('id', $gOperador->id_banco)->exists();
                                if ($existeBanco) {
                                    $cuentaBancariaId = $gOperador->id_banco;
                                }
                            }

                            GastoPago::create([
                                'gasto_id' => $gasto->id,
                                'cuenta_bancaria_id' => $cuentaBancariaId,
                                'fecha_pago' => $fecha,
                                'monto' => $monto,
                                'comprobante' => $gOperador->comprobante,
                                'estatus' => 'aplicado',
                            ]);
                        }
                        $actualizadosAPagado++;
                    });
                }
            } else {
                // Si el origen NO es pagado (está pendiente) pero en el sistema está marcado como pagado
                if ($gasto->estatus === 'pagado') {
                    DB::transaction(function () use ($gasto, &$actualizadosAPendiente) {
                        // 1. Revertir estatus a pendiente_pago
                        $gasto->update(['estatus' => 'pendiente_pago']);

                        // 2. Eliminar registros de GastoPago asociados
                        GastoPago::where('gasto_id', $gasto->id)->delete();

                        $actualizadosAPendiente++;
                    });
                }
            }
        }

        $this->info("✔ Corrección finalizada. Resultados:");
        $this->info("- Gastos actualizados a 'pagado' (con GastoPago): {$actualizadosAPagado}");
        $this->info("- Gastos revertidos a 'pendiente_pago' (GastoPago eliminado): {$actualizadosAPendiente}");
    }
}
