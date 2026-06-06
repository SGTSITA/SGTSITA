<?php

namespace App\Services\scb;

use App\Models\ScbBancoModulo;
use App\Models\ScbBancoModuloCuenta;
use App\Models\ScbUnidadModulo;
use App\Models\ScbBancoModuloCuentaMovimiento;
use Carbon\Carbon;

class ScbDashboardService
{
    public function getDashboardData(): array
    {
        $inicioMes = Carbon::now()->startOfMonth()->toDateString();
        $finMes = Carbon::now()->endOfMonth()->toDateString();

        $totalBancos = ScbBancoModulo::count();
        $totalBancosActivos = ScbBancoModulo::where('activo', 1)->count();

        $totalCuentas = ScbBancoModuloCuenta::count();
        $totalCuentasActivas = ScbBancoModuloCuenta::where('activo', 1)->count();

        $totalUnidades = ScbUnidadModulo::count();

        $movimientosMes = ScbBancoModuloCuentaMovimiento::query()
            ->whereBetween('fecha_movimiento', [$inicioMes, $finMes])
            ->withSum('detalles as total', 'monto')
            ->get();

        $totalMovimientosMes = $movimientosMes->count();

        $totalAbonosMes = $movimientosMes
            ->where('tipo', 'abono')
            ->sum('total');

        $totalCargosMes = $movimientosMes
            ->where('tipo', 'cargo')
            ->sum('total');

        $flujoNetoMes = $totalAbonosMes - $totalCargosMes;

        $saldoInicialTotal = ScbBancoModuloCuenta::sum('saldo_inicial');
        $promedioSaldoInicial = (float) ScbBancoModuloCuenta::avg('saldo_inicial');

        $movimientosGlobal = ScbBancoModuloCuentaMovimiento::query()
            ->withSum('detalles as total', 'monto')
            ->get();

        $totalAbonosGlobal = $movimientosGlobal
            ->where('tipo', 'abono')
            ->sum('total');

        $totalCargosGlobal = $movimientosGlobal
            ->where('tipo', 'cargo')
            ->sum('total');

        $saldoGlobal = $saldoInicialTotal + $totalAbonosGlobal - $totalCargosGlobal;

        $montoMovidoMes = $movimientosMes->sum('total');
        $ticketPromedioMes = $totalMovimientosMes > 0
            ? ($montoMovidoMes / $totalMovimientosMes)
            : 0;

        $ultimosMovimientos = ScbBancoModuloCuentaMovimiento::query()
            ->with(['cuenta.banco', 'usuario'])
            ->withSum('detalles as total', 'monto')
            ->orderByDesc('fecha_movimiento')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return [
            'totalBancos' => $totalBancos,
            'totalBancosActivos' => $totalBancosActivos,

            'totalCuentas' => $totalCuentas,
            'totalCuentasActivas' => $totalCuentasActivas,

            'totalUnidades' => $totalUnidades,
            'totalMovimientosMes' => $totalMovimientosMes,

            'totalAbonosMes' => $totalAbonosMes,
            'totalCargosMes' => $totalCargosMes,
            'flujoNetoMes' => $flujoNetoMes,

            'saldoGlobal' => $saldoGlobal,
            'promedioSaldoInicial' => $promedioSaldoInicial,
            'ticketPromedioMes' => $ticketPromedioMes,

            'ultimosMovimientos' => $ultimosMovimientos,
        ];
    }
}
