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

        // Obtener todos los gastos de origen gastos_operadores en estatus pendiente_pago
        $gastos = Gasto::where('origen_legacy', 'gastos_operadores')
            ->where('estatus', 'pendiente_pago')
            ->get();

        $this->info('Se encontraron ' . $gastos->count() . ' gastos migrados en estatus pendiente_pago.');

        $corregidos = 0;

        foreach ($gastos as $gasto) {
            // Buscar el registro original en la tabla legacy gastos_operadores
            $gOperador = DB::table('gastos_operadores')
                ->where('id', $gasto->origen_legacy_id)
                ->whereNotNull('id_banco')
                ->first();

            if (!$gOperador) {
                $this->warn("No se encontró el registro original en gastos_operadores para el Gasto ID: {$gasto->id} (Legacy ID: {$gasto->origen_legacy_id})");
                continue;
            }

            // Validar si el estatus original es "pagado" (insensible a mayúsculas/minúsculas)
            if (strtolower($gOperador->estatus ?? '') === 'pagado') {
                DB::transaction(function () use ($gasto, $gOperador, &$corregidos) {
                    // 1. Actualizar el estatus del Gasto a pagado
                    $gasto->update(['estatus' => 'pagado']);

                    // 2. Verificar si ya existe un pago asociado (por seguridad)
                    $existePago = GastoPago::where('gasto_id', $gasto->id)->exists();

                    if (!$existePago) {
                        $monto = floatval($gOperador->cantidad ?? 0);

                        $fecha = $gOperador->fecha_pago ?? $gOperador->created_at ?? now()->toDateString();
                        if (is_string($fecha) && strlen($fecha) > 10) {
                            $fecha = substr($fecha, 0, 10);
                        }

                        // Validar si el banco existe en la tabla de bancos
                        $cuentaBancariaId = null;
                        if (!empty($gOperador->id_banco) && $gOperador->id_banco > 0) {
                            $existeBanco = DB::table('bancos')->where('id', $gOperador->id_banco)->exists();
                            if ($existeBanco) {
                                $cuentaBancariaId = $gOperador->id_banco;
                            }
                        }

                        // 3. Crear el registro en GastoPago
                        GastoPago::create([
                            'gasto_id' => $gasto->id,
                            'cuenta_bancaria_id' => $cuentaBancariaId,
                            'fecha_pago' => $fecha,
                            'monto' => $monto,
                            'comprobante' => $gOperador->comprobante,
                            'estatus' => 'aplicado',
                        ]);
                    }

                    $corregidos++;
                });
            }
        }

        $this->info("✔ Corrección finalizada. Se actualizaron {$corregidos} gastos a 'pagado' y se les generó su registro de GastoPago.");
    }
}
