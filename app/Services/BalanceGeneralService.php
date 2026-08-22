<?php

namespace App\Services;

use App\Models\BalanceGeneralConfig;
use App\Models\BalanceGeneralSaldoInicial;
use App\Models\Cotizaciones;
use App\Models\Gasto;
use App\Models\Bancos;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BalanceGeneralService
{
    protected BancosService $bancosService;

    public function __construct(BancosService $bancosService)
    {
        $this->bancosService = $bancosService;
    }

    /**
     * Get configurations for an enterprise, seeding default ones if they don't exist.
     */
    public function getConfigurations(int $idEmpresa)
    {
        $configs = BalanceGeneralConfig::where('id_empresa', $idEmpresa)
            ->orderBy('grupo')
            ->orderBy('orden')
            ->get();

        if ($configs->isEmpty()) {
            $defaults = BalanceGeneralConfig::whereNull('id_empresa')->get();
            foreach ($defaults as $default) {
                BalanceGeneralConfig::create([
                    'id_empresa' => $idEmpresa,
                    'grupo' => $default->grupo,
                    'concepto' => $default->concepto,
                    'tipo_calculo' => $default->tipo_calculo,
                    'valor_manual' => $default->valor_manual,
                    'detalles_calculo' => $default->detalles_calculo,
                    'orden' => $default->orden,
                ]);
            }
            $configs = BalanceGeneralConfig::where('id_empresa', $idEmpresa)
                ->orderBy('grupo')
                ->orderBy('orden')
                ->get();
        }

        return $configs;
    }

    /**
     * Calculate Balance General metrics as of a specific cut-off date.
     */
    public function calculate(int $idEmpresa, string $fechaCorte): array
    {
        $configs = $this->getConfigurations($idEmpresa);
        $date = Carbon::parse($fechaCorte)->endOfDay();

        $rows = [];
        $totales = [
            'activo' => 0.00,
            'pasivo' => 0.00,
            'capital' => 0.00,
        ];

        foreach ($configs as $config) {
            $valor = $this->calculateConceptBalance($idEmpresa, $config, $date);

            $rows[] = [
                'id' => $config->id,
                'grupo' => $config->grupo,
                'concepto' => $config->concepto,
                'tipo_calculo' => $config->tipo_calculo,
                'valor' => $valor,
                'orden' => $config->orden,
            ];

            if (isset($totales[$config->grupo])) {
                $totales[$config->grupo] += $valor;
            }
        }

        return [
            'fecha_corte' => $fechaCorte,
            'rows' => $rows,
            'totales' => $totales,
            'cuadrado' => round($totales['activo'] - ($totales['pasivo'] + $totales['capital']), 2) === 0.00,
            'diferencia' => round($totales['activo'] - ($totales['pasivo'] + $totales['capital']), 2),
        ];
    }

    /**
     * Recursive/iterative method to carry forward ending balances of previous years as starting balances.
     */
    protected function calculateConceptBalance(int $idEmpresa, BalanceGeneralConfig $config, Carbon $fechaCorte): float
    {
        $targetYear = $fechaCorte->year;

        // Find the earliest year that has an initial balance config <= targetYear
        $firstSaldo = BalanceGeneralSaldoInicial::where('id_empresa', $idEmpresa)
            ->where('config_id', $config->id)
            ->where('ejercicio', '<=', $targetYear)
            ->orderBy('ejercicio', 'asc')
            ->first();

        // If no config exists, start from the target year with 0 starting balance
        $startYear = $firstSaldo ? $firstSaldo->ejercicio : $targetYear;

        $currentBalance = 0.00;

        for ($year = $startYear; $year <= $targetYear; $year++) {
            // If there's an initial balance configuration for this specific year, override the start of the year balance
            $saldoConfig = BalanceGeneralSaldoInicial::where('id_empresa', $idEmpresa)
                ->where('config_id', $config->id)
                ->where('ejercicio', $year)
                ->first();

            $fechaInicio = Carbon::create($year, 1, 1)->startOfDay();
            if ($saldoConfig) {
                $currentBalance = (float) $saldoConfig->monto;
                $fechaInicio = Carbon::parse($saldoConfig->fecha_inicio)->startOfDay();
            }

            $fechaFin = ($year === $targetYear) ? $fechaCorte : Carbon::create($year, 12, 31)->endOfDay();

            // Calculate movements for this concept in the range
            $movements = $this->calculateMovementsForType($config->tipo_calculo, $idEmpresa, $fechaInicio, $fechaFin);
            $currentBalance += $movements;
        }

        if (in_array($config->tipo_calculo, ['cxc', 'cxp', 'gxp'])) {
            return max(0.00, $currentBalance);
        }

        return $currentBalance;
    }

    /**
     * Helper to sum net movements for a specific calculation type and date range.
     */
    protected function calculateMovementsForType(string $tipoCalculo, int $idEmpresa, Carbon $start, Carbon $end): float
    {
        switch ($tipoCalculo) {
            case 'bancos':
                // Sum of abonos - Sum of cargos from active bank accounts
                $cuentaIds = Bancos::where('id_empresa', $idEmpresa)->where('estado', 1)->pluck('id');
                if ($cuentaIds->isEmpty()) {
                    return 0.00;
                }
                
                $abonos = DB::table('cat_bancos_cuentas_movimientos')
                    ->whereIn('cuenta_bancaria_id', $cuentaIds)
                    ->where('cancelado', false)
                    ->where('tipo', 'abono')
                    ->whereBetween('fecha_movimiento', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                    ->sum('monto');

                $cargos = DB::table('cat_bancos_cuentas_movimientos')
                    ->whereIn('cuenta_bancaria_id', $cuentaIds)
                    ->where('cancelado', false)
                    ->where('tipo', 'cargo')
                    ->whereBetween('fecha_movimiento', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                    ->sum('monto');

                return (float) ($abonos - $cargos);

            case 'cxc':
                // Approved/Finalized Cotizaciones in range - payments applied to cxc in range
                $totalCotizado = DB::table('cotizaciones')
                    ->where('id_empresa', $idEmpresa)
                    ->whereIn('estatus', ['Aprobada', 'Finalizado'])
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('total');

                $totalCobrado = DB::table('cobros_pagos_cotizaciones as cpc')
                    ->join('cobros_pagos as cp', 'cp.id', '=', 'cpc.cobro_pago_id')
                    ->join('cotizaciones as c', 'c.id', '=', 'cpc.cotizacion_id')
                    ->where('c.id_empresa', $idEmpresa)
                    ->where('cp.tipo', 'cxc')
                    ->where(function($query) use ($start, $end) {
                        $query->whereBetween('cp.fechaAplicacion1', [$start, $end])
                              ->orWhereBetween('cp.fechaAplicacion2', [$start, $end]);
                    })
                    ->sum('cpc.monto');

                return (float) ($totalCotizado - $totalCobrado);

            case 'cxp':
                // Total subcontracted assignments - payments applied to providers in range
                $totalSubcontratado = DB::table('cotizaciones as c')
                    ->join('docum_cotizacion as dc', 'c.id', '=', 'dc.id_cotizacion')
                    ->join('asignaciones as a', 'dc.id', '=', 'a.id_contenedor')
                    ->where('c.id_empresa', $idEmpresa)
                    ->where('a.tipo_contrato', 'Subcontratado')
                    ->whereIn('c.estatus', ['Aprobada', 'Finalizado'])
                    ->whereBetween('c.created_at', [$start, $end])
                    ->sum('a.total_proveedor');

                $totalPagadoProveedor = DB::table('cobros_pagos_cotizaciones as cpc')
                    ->join('cobros_pagos as cp', 'cp.id', '=', 'cpc.cobro_pago_id')
                    ->join('cotizaciones as c', 'c.id', '=', 'cpc.cotizacion_id')
                    ->where('c.id_empresa', $idEmpresa)
                    ->where('cp.tipo', 'cxp')
                    ->where(function($query) use ($start, $end) {
                        $query->whereBetween('cp.fechaAplicacion1', [$start, $end])
                              ->orWhereBetween('cp.fechaAplicacion2', [$start, $end]);
                    })
                    ->sum('cpc.monto');

                return (float) ($totalSubcontratado - $totalPagadoProveedor);

            case 'gxp':
                // Expenses created in range - Payments applied in range
                $totalGastado = DB::table('gastos')
                    ->where('id_empresa', $idEmpresa)
                    ->whereNull('deleted_at')
                    ->where('estatus', '!=', 'cancelado')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('monto_total');

                $totalPagado = DB::table('gasto_pagos as gp')
                    ->join('gastos as g', 'g.id', '=', 'gp.gasto_id')
                    ->where('g.id_empresa', $idEmpresa)
                    ->whereNull('g.deleted_at')
                    ->whereBetween('gp.fecha_pago', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                    ->sum('gp.monto');

                return (float) ($totalGastado - $totalPagado);

            case 'utilidad_ejercicio':
                // Income - Expenses in range
                $ingresos = DB::table('cotizaciones')
                    ->where('id_empresa', $idEmpresa)
                    ->whereIn('estatus', ['Aprobada', 'Finalizado'])
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('total');

                $egresos = DB::table('gastos')
                    ->where('id_empresa', $idEmpresa)
                    ->whereNull('deleted_at')
                    ->where('estatus', '!=', 'cancelado')
                    ->whereBetween('fecha_gasto', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                    ->sum('monto_total');

                return (float) ($ingresos - $egresos);

            case 'utilidades_acumuladas':
            default:
                return 0.00;
        }
    }
}
