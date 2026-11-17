<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlinearFechasGastosViajeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contabilidad:alinear-fechas-gastos-viaje
                            {empresa? : ID de la empresa a procesar (opcional, procesa todas si se omite)}
                            {--dry-run : Simula la ejecución sin realizar cambios en la base de datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alinea la fecha contable y operativa de los gastos vinculados a viajes con la fecha real del viaje';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("================================================================================");
        $this->info("     SGTSITA - ALINEACIÓN DE FECHAS CONTABLES Y OPERATIVAS DE GASTOS DE VIAJE   ");
        $this->info("================================================================================");

        $empresaInput = $this->argument('empresa');
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn(">>> MODO SIMULACIÓN (--dry-run) ACTIVO: No se modificarán datos. <<<");
        }

        // 1. Obtener empresas a procesar
        $empresasQuery = DB::table('empresas')->orderBy('id');
        if ($empresaInput) {
            $empresasQuery->where('id', $empresaInput);
        }
        $empresas = $empresasQuery->get();

        if ($empresas->isEmpty()) {
            $this->error("No se encontraron empresas para procesar.");
            return 1;
        }

        $resumenGlobal = [];

        foreach ($empresas as $emp) {
            $idEmpresa = $emp->id;
            $this->line("\nAnalizando Empresa #{$idEmpresa} - {$emp->nombre}...");

            // 1. Gastos vinculados por gasto_vinculos a cotizaciones
            $candidatosVinculos = DB::table('gasto_vinculos as gv')
                ->join('gastos as g', 'g.id', '=', 'gv.gasto_id')
                ->join('cotizaciones as c', 'c.id', '=', 'gv.vinculable_id')
                ->leftJoin('docum_cotizacion as dc', 'c.id', '=', 'dc.id_cotizacion')
                ->leftJoin('asignaciones as a', 'dc.id', '=', 'a.id_contenedor')
                ->where('gv.tipo_vinculo', 'cotizacion')
                ->where('g.id_empresa', $idEmpresa)
                ->whereNull('g.deleted_at')
                ->where('g.fecha_gasto', '>=', '2026-11-01')
                ->where(function ($q) {
                    $q->where('a.fecha_inicio', '<', '2026-10-01')
                      ->orWhere('c.created_at', '<', '2026-10-01');
                })
                ->select(
                    'g.id as gasto_id',
                    'g.monto_total',
                    'g.fecha_gasto',
                    'g.concepto',
                    'g.origen_legacy',
                    'a.fecha_inicio as trip_inicio',
                    'a.fecha_fin as trip_fin',
                    'c.created_at as cot_created_at'
                )
                ->get();

            // 2. Sueldos vinculados por origen_legacy_id = asignacion.id
            $candidatosSueldos = DB::table('gastos as g')
                ->join('asignaciones as a', 'a.id', '=', 'g.origen_legacy_id')
                ->where('g.origen_legacy', 'asignacion_sueldo_operador')
                ->where('g.id_empresa', $idEmpresa)
                ->whereNull('g.deleted_at')
                ->where('g.fecha_gasto', '>=', '2026-11-01')
                ->where(function ($q) {
                    $q->where('a.fecha_inicio', '<', '2026-10-01')
                      ->orWhere('a.created_at', '<', '2026-10-01');
                })
                ->select(
                    'g.id as gasto_id',
                    'g.monto_total',
                    'g.fecha_gasto',
                    'g.concepto',
                    'g.origen_legacy',
                    'a.fecha_inicio as trip_inicio',
                    'a.fecha_fin as trip_fin',
                    'a.created_at as cot_created_at'
                )
                ->get();

            $todosCandidatos = $candidatosVinculos->concat($candidatosSueldos)->unique('gasto_id');

            if ($todosCandidatos->isEmpty()) {
                $this->info("  -> Sin gastos desfasados a noviembre.");
                continue;
            }

            $gastosActualizados = 0;
            $polizasActualizadas = 0;
            $montoTotalAlineado = 0.0;

            if (!$dryRun) {
                DB::transaction(function () use (
                    $todosCandidatos,
                    &$gastosActualizados,
                    &$polizasActualizadas,
                    &$montoTotalAlineado
                ) {
                    foreach ($todosCandidatos as $item) {
                        $fechaReal = $item->trip_inicio ?: $item->trip_fin ?: $item->cot_created_at;
                        if (!$fechaReal) {
                            continue;
                        }
                        $fechaDestino = Carbon::parse($fechaReal)->format('Y-m-d');

                        // 1. Actualizar gasto
                        DB::table('gastos')
                            ->where('id', $item->gasto_id)
                            ->update(['fecha_gasto' => $fechaDestino]);

                        // 2. Actualizar imputaciones
                        DB::table('gasto_imputaciones')
                            ->where('gasto_id', $item->gasto_id)
                            ->update(['fecha_imputacion' => $fechaDestino]);

                        // 3. Actualizar pagos del gasto
                        DB::table('gasto_pagos')
                            ->where('gasto_id', $item->gasto_id)
                            ->update(['fecha_pago' => $fechaDestino]);

                        // 4. Actualizar pólizas contables vinculadas
                        $polizasUpdated = DB::table('polizas')
                            ->where('origen_id', $item->gasto_id)
                            ->whereIn('origen_tipo', [
                                'gasto_devengado',
                                'gasto_pago',
                                'gasto_comprobacion_operador',
                                'gasto_comprobacion_operador_excedente',
                                'sueldo_operador_devengado',
                            ])
                            ->update(['fecha' => $fechaDestino]);

                        $gastosActualizados++;
                        $polizasActualizadas += $polizasUpdated;
                        $montoTotalAlineado += (float) $item->monto_total;
                    }
                });
            } else {
                $gastosActualizados = $todosCandidatos->count();
                $montoTotalAlineado = (float) $todosCandidatos->sum('monto_total');
                $gastoIds = $todosCandidatos->pluck('gasto_id')->toArray();
                $polizasActualizadas = DB::table('polizas')
                    ->whereIn('origen_id', $gastoIds)
                    ->whereIn('origen_tipo', [
                        'gasto_devengado',
                        'gasto_pago',
                        'gasto_comprobacion_operador',
                        'gasto_comprobacion_operador_excedente',
                        'sueldo_operador_devengado',
                    ])
                    ->count();
            }

            $this->info("  [OK] Empresa {$idEmpresa}: {$gastosActualizados} gastos y {$polizasActualizadas} pólizas " . ($dryRun ? 'por alinear' : 'alineadas') . " ($" . number_format($montoTotalAlineado, 2) . ")");

            $resumenGlobal[] = [
                'empresa' => "#{$idEmpresa} - {$emp->nombre}",
                'gastos' => $gastosActualizados,
                'polizas' => $polizasActualizadas,
                'monto' => '$' . number_format($montoTotalAlineado, 2),
            ];
        }

        $this->line("");
        $this->table(['Empresa', 'Gastos Alineados', 'Pólizas Contables', 'Monto Reubicado'], $resumenGlobal);

        if (!$dryRun) {
            $this->info("\n¡Alineación completada exitosamente! Se recomienda ejecutar 'php artisan optimize:clear'.");
        } else {
            $this->warn("\nSimulación completada. Para aplicar los cambios reales ejecuta el comando sin '--dry-run'.");
        }

        return 0;
    }
}
