<?php

namespace App\Services\scb;

use App\Models\ScbBancoModulo;
use App\Models\ScbBancoModuloCuenta;
use App\Models\ScbBancoModuloCuentaMovimiento;
use App\Models\ScbUnidadModulo;
use Carbon\Carbon;

class ScbDashboardService
{
    public function __construct(
        protected ScbMovimientoService $movimientoService
    ) {
    }

    public function getDashboardData(): array
    {
        $inicioMes = Carbon::now()->startOfMonth()->toDateString();
        $finMes = Carbon::now()->endOfMonth()->toDateString();

        $totalBancos = ScbBancoModulo::count();
        $totalBancosActivos = ScbBancoModulo::where('activo', 1)->count();

        $cuentas = ScbBancoModuloCuenta::query()
            ->with('banco')
            ->get();

        $totalCuentas = $cuentas->count();
        $totalCuentasActivas = $cuentas->where('activo', 1)->count();

        $totalUnidades = ScbUnidadModulo::count();

        $movimientosMes = ScbBancoModuloCuentaMovimiento::query()
            ->whereDate('fecha_movimiento', '>=', $inicioMes)
            ->whereDate('fecha_movimiento', '<=', $finMes)
            ->withSum('detalles as total', 'monto')
            ->get();

        $totalMovimientosMes = $movimientosMes->count();

        /*
         * Importante:
         * No usamos abs aquí porque quieres respetar el monto real capturado.
         * Si un detalle viene negativo, debe afectar el total tal cual.
         */
        $totalCargosMes = $movimientosMes
            ->where('tipo', 'cargo')
            ->sum(fn ($movimiento) => (float) ($movimiento->total ?? 0));

        $totalAbonosMes = $movimientosMes
            ->where('tipo', 'abono')
            ->sum(fn ($movimiento) => (float) ($movimiento->total ?? 0));

        /*
         * En tu lógica:
         * Cargo suma.
         * Abono resta.
         */
        $flujoNetoMes = $totalCargosMes - $totalAbonosMes;

        /*
         * Saldo global real:
         * Se calcula cuenta por cuenta usando el mismo service de movimientos.
         * Así coincide con el estado de cuenta final.
         */
        $saldoGlobal = $cuentas->sum(function ($cuenta) {
            return $this->movimientoService->calcularSaldoCuenta($cuenta->id);
        });

        $promedioSaldoActual = $totalCuentas > 0
            ? ($saldoGlobal / $totalCuentas)
            : 0;

        /*
         * Para ticket promedio sí conviene medir volumen movido.
         * Aquí usamos abs solo para saber cuánto se movió, no para saldo.
         */
        $montoMovidoMes = $movimientosMes->sum(function ($movimiento) {
            return abs((float) ($movimiento->total ?? 0));
        });

        $ticketPromedioMes = $totalMovimientosMes > 0
            ? ($montoMovidoMes / $totalMovimientosMes)
            : 0;

       $ultimosMovimientos = ScbBancoModuloCuentaMovimiento::query()
    ->with(['cuenta.banco', 'usuario'])
    ->withSum('detalles as total', 'monto')
    ->orderByDesc('fecha_movimiento')
    ->orderByDesc('id')
    ->limit(8)
    ->get()
    ->each(function ($movimiento) {
        $total = (float) ($movimiento->total ?? 0);

        $impacto = $movimiento->tipo === 'cargo'
            ? $total
            : ($total * -1);

        $movimiento->setAttribute('monto_real', round($total, 2));
        $movimiento->setAttribute('impacto_saldo', round($impacto, 2));
        $movimiento->setAttribute(
            'impacto_label',
            $impacto >= 0
                ? '+' . number_format($impacto, 2)
                : '-' . number_format(abs($impacto), 2)
        );
    });

        return [
            'totalBancos' => $totalBancos,
            'totalBancosActivos' => $totalBancosActivos,

            'totalCuentas' => $totalCuentas,
            'totalCuentasActivas' => $totalCuentasActivas,

            'totalUnidades' => $totalUnidades,
            'totalMovimientosMes' => $totalMovimientosMes,

            'totalAbonosMes' => round($totalAbonosMes, 2),
            'totalCargosMes' => round($totalCargosMes, 2),
            'flujoNetoMes' => round($flujoNetoMes, 2),

            'saldoGlobal' => round($saldoGlobal, 2),
            'promedioSaldoInicial' => round($promedioSaldoActual, 2),
            'ticketPromedioMes' => round($ticketPromedioMes, 2),

            'ultimosMovimientos' => $ultimosMovimientos,
        ];
    }
}
