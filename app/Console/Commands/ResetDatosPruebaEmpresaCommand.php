<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Empresas;
use App\Models\Cotizaciones;
use App\Models\DocumCotizacion;
use App\Models\Asignaciones;
use App\Models\DineroContenedor;
use App\Models\BitacoraViajeOperador;
use App\Models\Gasto;
use App\Models\GastoPartida;
use App\Models\GastoImputacion;
use App\Models\GastoProgramacion;
use App\Models\GastoPago;
use App\Models\GastoRecuperacion;
use App\Models\GastoVinculo;
use App\Models\GastosOperadores;
use App\Models\ViaticosOperador;
use App\Models\Operador;
use App\Models\Prestamo;
use App\Models\PagoPrestamo;
use App\Models\Poliza;
use App\Models\PolizaMovimiento;
use App\Models\Bancos;
use App\Models\CatBancoCuentasMovimientos;
use App\Models\Liquidaciones;
use App\Models\LiquidacionContenedor;
use App\Models\LiquidacionAjuste;
use App\Models\LiquidacionAjusteDetalle;

class ResetDatosPruebaEmpresaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'empresa:reset-pruebas
                            {--empresa=28 : ID de la empresa a reiniciar (por defecto 28 - H00 Global)}
                            {--confirm : Omitir solicitud de confirmación interactiva}
                            {--dry-run : Solo simular e informar los registros encontrados sin eliminar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reestablece contenedores a Aprobada, limpia asignaciones, gastos operativos y contabilidad de prueba para una empresa';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->warn("================================================================================");
        $this->warn("      SGTSITA - REINICIO DE DATOS DE PRUEBA: VIAJES, GASTOS Y CONTABILIDAD      ");
        $this->warn("================================================================================");

        $empresaId = (int) $this->option('empresa');
        $dryRun = (bool) $this->option('dry-run');
        $confirm = (bool) $this->option('confirm');

        $empresa = Empresas::withoutGlobalScopes()->find($empresaId);
        if (!$empresa) {
            $this->error("No se encontró la empresa con ID: {$empresaId}");
            return Command::FAILURE;
        }

        $this->info("Empresa seleccionada: [ID {$empresa->id}] {$empresa->nombre}");

        // 1. Recolección de IDs de Cotizaciones, Contenedores y Asignaciones
        $cotizaciones = Cotizaciones::where('id_empresa', $empresaId)->get();
        $cotizacionIds = $cotizaciones->pluck('id')->toArray();

        $documCotizaciones = DocumCotizacion::whereIn('id_cotizacion', $cotizacionIds)->get();
        $documIds = $documCotizaciones->pluck('id')->toArray();

        $asignaciones = Asignaciones::whereIn('id_contenedor', $documIds)
            ->orWhere('id_empresa', $empresaId)
            ->get();
        $asignacionIds = $asignaciones->pluck('id')->toArray();

        // 2. Recolección de Gastos y Vinculados
        $gastos = Gasto::where('id_empresa', $empresaId)
            ->orWhereIn('origen_cotizacion_id', $cotizacionIds)
            ->get();
        $gastoIds = $gastos->pluck('id')->toArray();

        // 3. Recolección de Pólizas Contables
        $polizas = Poliza::where('id_empresa', $empresaId)
            ->where('es_apertura', false)
            ->get();
        $polizaIds = $polizas->pluck('id')->toArray();

        // 4. Recolección de Bancos y Operadores
        $bancos = Bancos::where('id_empresa', $empresaId)->get();
        $bancosIds = $bancos->pluck('id')->toArray();
        $operadorIds = Operador::where('id_empresa', $empresaId)->pluck('id')->toArray();

        // 5. Recolección de Liquidaciones y Ajustes
        $liquidaciones = Liquidaciones::where(function ($q) use ($operadorIds, $bancosIds, $documIds) {
            $hasCondition = false;
            if (!empty($operadorIds)) {
                $q->whereIn('id_operador', $operadorIds);
                $hasCondition = true;
            }
            if (!empty($bancosIds)) {
                if ($hasCondition) {
                    $q->orWhereIn('id_banco', $bancosIds);
                } else {
                    $q->whereIn('id_banco', $bancosIds);
                    $hasCondition = true;
                }
            }
            if (!empty($documIds)) {
                $q->orWhereHas('Viajes', function ($vQ) use ($documIds) {
                    $vQ->whereIn('id_contenedor', $documIds);
                });
                $hasCondition = true;
            }
            if (!$hasCondition) {
                $q->whereRaw('1 = 0');
            }
        })->get();
        $liquidacionIds = $liquidaciones->pluck('id')->toArray();

        $ajustes = LiquidacionAjuste::where('id_empresa', $empresaId)
            ->orWhereIn('id_liquidacion', $liquidacionIds)
            ->get();
        $ajusteIds = $ajustes->pluck('id')->toArray();

        // 6. Constructor de consulta para movimientos bancarios operativos
        $movimientosQueryBuilder = function () use ($bancosIds, $asignacionIds, $gastoIds, $cotizacionIds, $documIds, $liquidacionIds) {
            return CatBancoCuentasMovimientos::whereIn('cuenta_bancaria_id', $bancosIds)
                ->where(function ($q) use ($asignacionIds, $gastoIds, $cotizacionIds, $documIds, $liquidacionIds) {
                    $q->whereIn('referenciaable_type', [
                        Asignaciones::class,
                        Gasto::class,
                        ViaticosOperador::class,
                        DineroContenedor::class,
                        Prestamo::class,
                        Cotizaciones::class,
                        Liquidaciones::class,
                        LiquidacionAjuste::class,
                    ])
                    ->orWhereIn('referenciaable_id', $asignacionIds)
                    ->orWhereIn('referenciaable_id', $gastoIds)
                    ->orWhereIn('referenciaable_id', $liquidacionIds)
                    ->orWhere('origen', 'sistema')
                    ->orWhere('concepto', 'like', '%viaje%')
                    ->orWhere('concepto', 'like', '%diesel%')
                    ->orWhere('concepto', 'like', '%diésel%')
                    ->orWhere('concepto', 'like', '%anticipo%')
                    ->orWhere('concepto', 'like', '%burrero%')
                    ->orWhere('concepto', 'like', '%GDI%')
                    ->orWhere('concepto', 'like', '%GBV%')
                    ->orWhere('concepto', 'like', '%Dinero para viaje%')
                    ->orWhere('concepto', 'like', '%Liquidación%')
                    ->orWhere('concepto', 'like', '%Liquidacion%');
                });
        };
        $totalMovimientos = !empty($bancosIds) ? $movimientosQueryBuilder()->count() : 0;

        // 7. Recolección de Préstamos (relacionados por origen_asignacion_id, id_banco o id_operador de la empresa)
        $prestamos = Prestamo::where(function ($q) use ($asignacionIds, $bancosIds, $operadorIds) {
            $hasCondition = false;
            if (!empty($asignacionIds)) {
                $q->whereIn('origen_asignacion_id', $asignacionIds);
                $hasCondition = true;
            }
            if (!empty($bancosIds)) {
                if ($hasCondition) {
                    $q->orWhereIn('id_banco', $bancosIds);
                } else {
                    $q->whereIn('id_banco', $bancosIds);
                    $hasCondition = true;
                }
            }
            if (!empty($operadorIds)) {
                if ($hasCondition) {
                    $q->orWhereIn('id_operador', $operadorIds);
                } else {
                    $q->whereIn('id_operador', $operadorIds);
                    $hasCondition = true;
                }
            }
            if (!$hasCondition) {
                $q->whereRaw('1 = 0');
            }
        })->get();
        $prestamoIds = $prestamos->pluck('id')->toArray();

        // Conteo general
        $this->line("--------------------------------------------------------------------------------");
        $this->line("Registros identificados para reinicio:");
        $this->line(" - Cotizaciones a reestablecer (estatus='Aprobada', estatus_planeacion=0): " . count($cotizacionIds));
        $this->line(" - Asignaciones de viaje a eliminar: " . count($asignacionIds));
        $this->line(" - Gastos operativos de la empresa a eliminar: " . count($gastoIds));
        $this->line(" - Liquidaciones de operador a eliminar: " . count($liquidacionIds));
        $this->line(" - Ajustes de liquidación a eliminar: " . count($ajusteIds));
        $this->line(" - Pólizas contables operativas a eliminar: " . count($polizaIds));
        $this->line(" - Movimientos bancarios operativos a eliminar: {$totalMovimientos}");
        $this->line(" - Préstamos generados por cancelaciones a eliminar: " . count($prestamoIds));
        $this->line("--------------------------------------------------------------------------------");

        if ($dryRun) {
            $this->warn("[DRY-RUN] Modo simulación activo. No se realizó ninguna modificación en la base de datos.");
            return Command::SUCCESS;
        }

        if (!$confirm && !$this->confirm("¿Deseas proceder con el reinicio de datos para la empresa ID {$empresaId}?", true)) {
            $this->line("Operación cancelada.");
            return Command::SUCCESS;
        }

        DB::transaction(function () use (
            $empresaId,
            $cotizacionIds,
            $documIds,
            $asignacionIds,
            $gastoIds,
            $liquidacionIds,
            $ajusteIds,
            $polizaIds,
            $bancosIds,
            $movimientosQueryBuilder,
            $prestamoIds,
            $bancos
        ) {
            // A. Reestablecer cotizaciones a estado Aprobada (no planeadas)
            if (!empty($cotizacionIds)) {
                Cotizaciones::whereIn('id', $cotizacionIds)->update([
                    'estatus' => 'Aprobada',
                    'estatus_planeacion' => 0,
                    'referencia_full' => null,
                    'jerarquia' => 'Principal',
                    'tipo_viaje' => 'Sencillo',
                    'litros_diesel' => 0,
                    'litros_urea' => 0,
                    'cp_fecha_tentativa_entrega' => null,
                    'cp_hora_tentativa_entrega' => null,
                    'cp_comentarios' => null,
                ]);
            }

            // Asegurar que ninguna cotización quede con jerarquía null o no principal
            Cotizaciones::where('id_empresa', $empresaId)
                ->where(function ($q) {
                    $q->whereNull('jerarquia')->orWhere('jerarquia', '');
                })
                ->update(['jerarquia' => 'Principal']);

            // B. Eliminar bitácoras y dineros de contenedores/asignaciones
            if (!empty($documIds)) {
                DineroContenedor::whereIn('id_contenedor', $documIds)->delete();
                ViaticosOperador::whereIn('id_cotizacion', $documIds)->delete();
            }

            if (!empty($cotizacionIds)) {
                ViaticosOperador::whereIn('id_cotizacion', $cotizacionIds)->delete();
                GastosOperadores::whereIn('id_cotizacion', $cotizacionIds)->delete();
            }

            if (!empty($asignacionIds)) {
                BitacoraViajeOperador::whereIn('id_asignacion', $asignacionIds)->delete();
                GastosOperadores::whereIn('id_asignacion', $asignacionIds)->delete();
                Asignaciones::whereIn('id', $asignacionIds)->delete();
            }

            // C. Eliminar gastos operativos y todas sus tablas hijas
            if (!empty($gastoIds)) {
                GastoPartida::whereIn('gasto_id', $gastoIds)->forceDelete();
                GastoImputacion::whereIn('gasto_id', $gastoIds)->forceDelete();
                GastoProgramacion::whereIn('gasto_id', $gastoIds)->forceDelete();
                GastoPago::whereIn('gasto_id', $gastoIds)->forceDelete();
                GastoRecuperacion::whereIn('gasto_id', $gastoIds)->forceDelete();
                GastoVinculo::whereIn('gasto_id', $gastoIds)->forceDelete();
                Gasto::whereIn('id', $gastoIds)->forceDelete();
            }

            // D. Eliminar préstamos a choferes
            if (!empty($prestamoIds)) {
                PagoPrestamo::whereIn('id_prestamo', $prestamoIds)->delete();
                Prestamo::whereIn('id', $prestamoIds)->delete();
            }

            // E. Eliminar liquidaciones de operador y ajustes
            if (!empty($ajusteIds)) {
                LiquidacionAjusteDetalle::whereIn('id_ajuste', $ajusteIds)->delete();
                LiquidacionAjuste::whereIn('id', $ajusteIds)->delete();
            }

            if (!empty($liquidacionIds)) {
                PagoPrestamo::whereIn('id_liquidacion', $liquidacionIds)->delete();
                LiquidacionContenedor::whereIn('id_liquidacion', $liquidacionIds)->delete();
                Liquidaciones::whereIn('id', $liquidacionIds)->delete();
            }

            // F. Eliminar pólizas contables y sus movimientos
            if (!empty($polizaIds)) {
                PolizaMovimiento::whereIn('poliza_id', $polizaIds)->delete();
                Poliza::whereIn('id', $polizaIds)->delete();
            }

            // G. Eliminar movimientos bancarios operativos
            if (!empty($bancosIds)) {
                $movimientosQueryBuilder()->delete();
            }

            // H. Recalcular saldo de las cuentas bancarias de la empresa
            foreach ($bancos as $banco) {
                $saldoBase = (float) ($banco->inicial_saldo ?? $banco->saldo_inicial ?? 0);
                $restantes = CatBancoCuentasMovimientos::where('cuenta_bancaria_id', $banco->id)
                    ->orderBy('fecha_movimiento')
                    ->orderBy('id')
                    ->get();

                $saldoCalc = $saldoBase;
                foreach ($restantes as $m) {
                    $monto = (float) $m->monto;
                    if ($m->tipo === 'abono' || $m->tipo === 'deposito') {
                        $saldoCalc += $monto;
                    } elseif ($m->tipo === 'cargo' || $m->tipo === 'retiro') {
                        $saldoCalc -= $monto;
                    }
                }

                $banco->saldo = $saldoCalc;
                $banco->saveQuietly();
            }
        });

        $this->info("✓ Reinicio completado exitosamente para la empresa ID {$empresaId}.");
        $this->info("✓ Todos los contenedores están en estatus 'Aprobada' y listos para programar.");
        $this->info("✓ Gastos, viáticos, asignaciones y pólizas contables operativas han sido eliminados.");
        $this->info("✓ Las cuentas bancarias conservan su saldo de apertura y están listas para nuevas pruebas.");

        return Command::SUCCESS;
    }
}
