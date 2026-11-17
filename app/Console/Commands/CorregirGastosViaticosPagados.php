<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Gasto;
use App\Models\GastoPago;
use Carbon\Carbon;

class CorregirGastosViaticosPagados extends Command
{
    protected $signature = 'gastos:corregir-viaticos-pagados';

    protected $description = 'Corrige el estatus de los gastos de viáticos y excedentes de operador a pagados si el viaje ya fue liquidado/pagado, y les genera su GastoPago';

    public function handle()
    {
        $this->info('🚀 Iniciando corrección de gastos de viáticos/excedentes de operadores...');

        // Obtener todos los gastos de origen viaticos_operadores, viaticos_operadores_excedente o gastos_operadores en estatus pendiente_pago
        $gastos = Gasto::whereIn('origen_legacy', ['viaticos_operadores', 'viaticos_operadores_excedente', 'gastos_operadores'])
            ->where('estatus', 'pendiente_pago')
            ->get();

        $this->info('Se encontraron ' . $gastos->count() . ' gastos de viáticos/operadores en estatus pendiente_pago.');

        $corregidos = 0;

        foreach ($gastos as $gasto) {
            // 1. Obtener la asignación (viaje) asociada a través de gasto_vinculos
            $vinculo = DB::table('gasto_vinculos')
                ->where('gasto_id', $gasto->id)
                ->where('tipo_vinculo', 'asignacion')
                ->first();

            if (!$vinculo) {
                // Si no hay vínculo de asignación, intentar buscar por contenedor
                $vinculoContenedor = DB::table('gasto_vinculos')
                    ->where('gasto_id', $gasto->id)
                    ->where('tipo_vinculo', 'contenedor')
                    ->first();

                if ($vinculoContenedor) {
                    $asignacion = DB::table('asignaciones')
                        ->where('id_contenedor', $vinculoContenedor->vinculable_id)
                        ->first();
                } else {
                    $this->warn("No se encontró vínculo de asignación ni contenedor para el Gasto ID: {$gasto->id}");
                    continue;
                }
            } else {
                $asignacion = DB::table('asignaciones')
                    ->where('id', $vinculo->vinculable_id)
                    ->first();
            }

            if (!$asignacion) {
                $this->warn("No se encontró la asignación correspondiente en la base de datos para el Gasto ID: {$gasto->id}");
                continue;
            }

            // 2. Verificar si el viaje ya está pagado o liquidado
            $liqCont = DB::table('liquidacion_contenedor')
                ->where('id_contenedor', $asignacion->id_contenedor)
                ->first();

            $esViajePagado = (strtolower($asignacion->estatus_pagado ?? '') === 'pagado' || !is_null($liqCont));

            if ($esViajePagado) {
                DB::transaction(function () use ($gasto, $asignacion, $liqCont, &$corregidos) {
                    // Actualizar el estatus del gasto a pagado
                    $gasto->update(['estatus' => 'pagado']);

                    // Verificar si ya existe un pago asociado
                    $existePago = GastoPago::where('gasto_id', $gasto->id)->exists();

                    if (!$existePago) {
                        $cuentaBancariaId = null;
                        $fechaPago = null;

                        // Intentar obtener datos de la liquidación
                        if ($liqCont) {
                            $liquidacion = DB::table('liquidaciones')
                                ->where('id', $liqCont->id_liquidacion)
                                ->first();

                            if ($liquidacion) {
                                $cuentaBancariaId = $liquidacion->id_banco;
                                $fechaPago = $liquidacion->fecha;
                            }
                        }

                        // Si no hay liquidación o faltan datos, usar fallbacks de la asignación
                        if (!$cuentaBancariaId) {
                            if ($gasto->origen_legacy === 'viaticos_operadores_excedente') {
                                // Excedentes fallan al banco de la asignación si no hay liquidación
                                $cuentaBancariaId = null; 
                            } else {
                                // Viáticos normales fallan al banco del dinero del viaje
                                $cuentaBancariaId = $asignacion->id_banco1_dinero_viaje ?? $asignacion->id_banco2_dinero_viaje ?? null;
                            }
                        }

                        if (!$fechaPago) {
                            $fechaPago = $asignacion->fecha_pago_operador ?? $asignacion->fecha_fin ?? $asignacion->fecha_inicio ?? now()->toDateString();
                        }

                        // Validar que el banco realmente exista en la tabla de bancos
                        if ($cuentaBancariaId) {
                            $existeBanco = DB::table('bancos')->where('id', $cuentaBancariaId)->exists();
                            if (!$existeBanco) {
                                $cuentaBancariaId = null;
                            }
                        }

                        // Crear el registro de pago
                        GastoPago::create([
                            'gasto_id' => $gasto->id,
                            'cuenta_bancaria_id' => $cuentaBancariaId,
                            'fecha_pago' => $fechaPago,
                            'monto' => $gasto->monto_total,
                            'estatus' => 'aplicado',
                            'user_id' => null,
                        ]);
                    }

                    $corregidos++;
                });
            }
        }

        $this->info("✔ Corrección finalizada. Se actualizaron {$corregidos} gastos de viáticos a 'pagado' y se les generó su registro de GastoPago.");
    }
}
