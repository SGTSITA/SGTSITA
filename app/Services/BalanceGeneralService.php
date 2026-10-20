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

        // Asegurar que exista la fila de Pagos/Retiros a Socios en la empresa
        $hasPagosSocios = $configs->contains('tipo_calculo', 'pagos_socios');
        if (!$hasPagosSocios) {
            $newConfig = BalanceGeneralConfig::create([
                'id_empresa' => $idEmpresa,
                'grupo' => 'capital',
                'concepto' => '(-) RETIROS A SOCIOS',
                'tipo_calculo' => 'pagos_socios',
                'valor_manual' => 0.00,
                'detalles_calculo' => 'Pagos y retiros a cuenta de utilidades de socios',
                'orden' => 15,
            ]);
            $configs->push($newConfig);
            $configs = $configs->sortBy(['grupo', 'orden'])->values();
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

        $totales['activo'] = round($totales['activo'], 2);
        $totales['pasivo'] = round($totales['pasivo'], 2);
        $totales['capital'] = round($totales['capital'], 2);

        $diferencia = round($totales['activo'] - ($totales['pasivo'] + $totales['capital']), 2);

        return [
            'fecha_corte' => $fechaCorte,
            'rows' => $rows,
            'totales' => $totales,
            'cuadrado' => abs($diferencia) < 0.01,
            'diferencia' => $diferencia,
        ];
    }

    /**
     * Recursive/iterative method to carry forward ending balances of previous years as starting balances.
     */
    protected function calculateConceptBalance(int $idEmpresa, BalanceGeneralConfig $config, Carbon $fechaCorte): float
    {
        $targetYear = $fechaCorte->year;

        // 1. Bancos: si no hay saldo inicial manual en el ejercicio actual, obtener saldo acumulado real
        if ($config->tipo_calculo === 'bancos') {
            $hasSaldoManual = BalanceGeneralSaldoInicial::where('id_empresa', $idEmpresa)
                ->where('config_id', $config->id)
                ->where('ejercicio', $targetYear)
                ->exists();

            if (!$hasSaldoManual) {
                return $this->calcularSaldoBancos($idEmpresa, $fechaCorte);
            }
        }

        // 2. Gastos por Pagar (Pasivo): saldo insoluto real de gastos no cancelados al corte
        if ($config->tipo_calculo === 'gxp') {
            return $this->calcularGastosPorPagar($idEmpresa, $fechaCorte);
        }

        // 3. Utilidad del Ejercicio / Operación (Capital): vinculada a Reporte de Resultados y Socios
        if ($config->tipo_calculo === 'utilidad_ejercicio') {
            return $this->calcularUtilidadOperacion($idEmpresa, $fechaCorte);
        }

        // 4. Pagos / Retiros a Socios (Capital): deducción exacta de retiros aplicados
        if ($config->tipo_calculo === 'pagos_socios') {
            return $this->calcularPagosSocios($idEmpresa, $fechaCorte);
        }

        // 5. Conceptos estándar o con saldos iniciales manuales configurados
        $firstSaldo = BalanceGeneralSaldoInicial::where('id_empresa', $idEmpresa)
            ->where('config_id', $config->id)
            ->where('ejercicio', '<=', $targetYear)
            ->orderBy('ejercicio', 'asc')
            ->first();

        // If no config exists, start from the target year with 0 starting balance
        $startYear = $firstSaldo ? $firstSaldo->ejercicio : $targetYear;

        $currentBalance = 0.00;

        for ($year = $startYear; $year <= $targetYear; $year++) {
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

        if (in_array($config->tipo_calculo, ['cxc', 'cxp'])) {
            return max(0.00, round($currentBalance, 2));
        }

        return round($currentBalance, 2);
    }

    /**
     * Calcula el saldo acumulado real de todas las cuentas bancarias activas de la empresa al corte.
     */
    public function calcularSaldoBancos(int $idEmpresa, Carbon $fechaCorte): float
    {
        $cuentas = Bancos::where('id_empresa', $idEmpresa)->where('estado', 1)->get();
        if ($cuentas->isEmpty()) {
            return 0.00;
        }

        $totalBancos = 0.00;
        foreach ($cuentas as $cuenta) {
            $res = $this->bancosService->getsaldoActualFecha($cuenta, null, $fechaCorte->format('Y-m-d'));
            $totalBancos += (float) ($res['saldoActual'] ?? 0.00);
        }

        return round($totalBancos, 2);
    }

    /**
     * Calcula el saldo insoluto pendiente de pago de los gastos registrados al corte.
     */
    public function calcularGastosPorPagar(int $idEmpresa, Carbon $fechaCorte): float
    {
        $fechaCorteStr = $fechaCorte->format('Y-m-d');

        $gastos = DB::table('gastos')
            ->where('id_empresa', $idEmpresa)
            ->whereNull('deleted_at')
            ->where('estatus', '!=', 'cancelado')
            ->where('fecha_gasto', '<=', $fechaCorteStr)
            ->select('id', 'monto_total')
            ->get();

        if ($gastos->isEmpty()) {
            return 0.00;
        }

        $gastoIds = $gastos->pluck('id');

        $pagosPorGasto = DB::table('gasto_pagos')
            ->whereIn('gasto_id', $gastoIds)
            ->where('fecha_pago', '<=', $fechaCorteStr)
            ->groupBy('gasto_id')
            ->select('gasto_id', DB::raw('SUM(monto) as total_pagado'))
            ->pluck('total_pagado', 'gasto_id');

        $saldoInsoluto = 0.00;
        foreach ($gastos as $g) {
            $pagado = (float) ($pagosPorGasto[$g->id] ?? 0.00);
            $pendiente = (float) $g->monto_total - $pagado;
            if ($pendiente > 0.009) {
                $saldoInsoluto += $pendiente;
            }
        }

        return round($saldoInsoluto, 2);
    }

    /**
     * Calcula la Utilidad de Operación acumulada sincronizada con Reporte de Resultados y Socios.
     */
    public function calcularUtilidadOperacion(int $idEmpresa, Carbon $fechaCorte): float
    {
        $earliestTrip = DB::table('asignaciones as a')
            ->join('docum_cotizacion as dc', 'dc.id', '=', 'a.id_contenedor')
            ->join('cotizaciones as c', 'c.id', '=', 'dc.id_cotizacion')
            ->where('c.id_empresa', $idEmpresa)
            ->where('c.estatus', '!=', 'Cancelada')
            ->min('a.fecha_inicio');

        $startDate = $earliestTrip 
            ? Carbon::parse($earliestTrip)->format('Y-m-d') 
            : Carbon::create($fechaCorte->year, 1, 1)->format('Y-m-d');
        $endDate = $fechaCorte->format('Y-m-d');

        try {
            $sociosService = app(\App\Services\SociosService::class);
            $partnerUtil = $sociosService->calculatePartnerUtility($startDate, $endDate, $idEmpresa);

            if (isset($partnerUtil['utilidad_neta_distribuible'])) {
                return round((float) $partnerUtil['utilidad_neta_distribuible'], 2);
            }
        } catch (\Throwable $e) {
            // Fallback en caso de que SociosService arroje error
        }

        // Fallback operativo: cotizaciones finalizadas/aprobadas menos gastos operativos
        $ingresos = DB::table('cotizaciones')
            ->where('id_empresa', $idEmpresa)
            ->whereIn('estatus', ['Aprobada', 'Finalizado'])
            ->where('created_at', '<=', $fechaCorte)
            ->sum('total');

        $egresos = DB::table('gastos')
            ->where('id_empresa', $idEmpresa)
            ->whereNull('deleted_at')
            ->where('estatus', '!=', 'cancelado')
            ->where('fecha_gasto', '<=', $endDate)
            ->sum('monto_total');

        return round((float) ($ingresos - $egresos), 2);
    }

    /**
     * Calcula los pagos/anticipos acumulados aplicados a socios al corte (monto negativo deducible en Capital).
     */
    public function calcularPagosSocios(int $idEmpresa, Carbon $fechaCorte): float
    {
        $pagosSocios = DB::table('socio_pagos as sp')
            ->join('socios as s', 's.id', '=', 'sp.socio_id')
            ->where('s.id_empresa', $idEmpresa)
            ->where('sp.fecha_aplicacion', '<=', $fechaCorte->format('Y-m-d'))
            ->sum('sp.monto');

        return -1 * abs(round((float) $pagosSocios, 2));
    }

    /**
     * Helper to sum net movements for a specific calculation type and date range.
     */
    protected function calculateMovementsForType(string $tipoCalculo, int $idEmpresa, Carbon $start, Carbon $end): float
    {
        switch ($tipoCalculo) {
            case 'bancos':
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
                $totalCotizado = DB::table('cotizaciones')
                    ->where('id_empresa', $idEmpresa)
                    ->whereIn('estatus', ['Aprobada', 'Finalizado'])
                    ->where('created_at', '<=', $end)
                    ->sum('total');

                $totalCobrado = DB::table('cobros_pagos_cotizaciones as cpc')
                    ->join('cobros_pagos as cp', 'cp.id', '=', 'cpc.cobro_pago_id')
                    ->join('cotizaciones as c', 'c.id', '=', 'cpc.cotizacion_id')
                    ->where('c.id_empresa', $idEmpresa)
                    ->where('cp.tipo', 'cxc')
                    ->where(function($query) use ($end) {
                        $query->where('cp.fechaAplicacion1', '<=', $end)
                              ->orWhere('cp.fechaAplicacion2', '<=', $end);
                    })
                    ->sum('cpc.monto');

                return (float) ($totalCotizado - $totalCobrado);

            case 'cxp':
                $totalSubcontratado = DB::table('cotizaciones as c')
                    ->join('docum_cotizacion as dc', 'c.id', '=', 'dc.id_cotizacion')
                    ->join('asignaciones as a', 'dc.id', '=', 'a.id_contenedor')
                    ->where('c.id_empresa', $idEmpresa)
                    ->where('a.tipo_contrato', 'Subcontratado')
                    ->whereIn('c.estatus', ['Aprobada', 'Finalizado'])
                    ->where('c.created_at', '<=', $end)
                    ->sum('a.total_proveedor');

                $totalPagadoProveedor = DB::table('cobros_pagos_cotizaciones as cpc')
                    ->join('cobros_pagos as cp', 'cp.id', '=', 'cpc.cobro_pago_id')
                    ->join('cotizaciones as c', 'c.id', '=', 'cpc.cotizacion_id')
                    ->where('c.id_empresa', $idEmpresa)
                    ->where('cp.tipo', 'cxp')
                    ->where(function($query) use ($end) {
                        $query->where('cp.fechaAplicacion1', '<=', $end)
                              ->orWhere('cp.fechaAplicacion2', '<=', $end);
                    })
                    ->sum('cpc.monto');

                return (float) ($totalSubcontratado - $totalPagadoProveedor);

            case 'gxp':
                return $this->calcularGastosPorPagar($idEmpresa, $end);

            case 'utilidad_ejercicio':
                return $this->calcularUtilidadOperacion($idEmpresa, $end);

            case 'pagos_socios':
                return $this->calcularPagosSocios($idEmpresa, $end);

            case 'utilidades_acumuladas':
            default:
                return 0.00;
        }
    }
}
