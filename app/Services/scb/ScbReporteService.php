<?php

namespace App\Services\scb;

use App\Models\ScbBancoModuloCuenta;
use App\Models\ScbBancoModuloCuentaMovimiento;
use Carbon\Carbon;

class ScbReporteService
{
    public function generar(array $filtros): array
    {
        return match ($filtros['tipo_reporte']) {
            'detallado' => $this->detallado(
                cuentaId: (int) $filtros['cuenta_id'],
                fechaInicio: $filtros['fecha_inicio'],
                fechaFin: $filtros['fecha_fin'],
            ),

            default => $this->estadoCuenta(
                cuentaId: (int) $filtros['cuenta_id'],
                fechaInicio: $filtros['fecha_inicio'],
                fechaFin: $filtros['fecha_fin'],
            ),
        };
    }

    public function estadoCuenta(int $cuentaId, string $fechaInicio, string $fechaFin): array
    {
        $cuenta = ScbBancoModuloCuenta::query()
            ->with('banco')
            ->findOrFail($cuentaId);

        $saldoInicial = $this->calcularSaldoInicial($cuenta, $fechaInicio);

        $movimientos = ScbBancoModuloCuentaMovimiento::query()
            ->where('cuenta_id', $cuentaId)
            ->whereDate('fecha_movimiento', '>=', $fechaInicio)
            ->whereDate('fecha_movimiento', '<=', $fechaFin)
            ->withCount('detalles')
            ->withSum('detalles as total', 'monto')
            ->orderBy('fecha_movimiento')
            ->orderBy('id')
            ->get();

        $saldo = $saldoInicial;
        $totalCargos = 0;
        $totalAbonos = 0;

        $rows = $movimientos->map(function ($movimiento) use (&$saldo, &$totalCargos, &$totalAbonos) {
            $total = (float) ($movimiento->total ?? 0);

            $cargo = 0;
            $abono = 0;

            if ($movimiento->tipo === 'abono') {
                $abono = $total;
                $saldo += $abono;
                $totalAbonos += $abono;
            } else {
                $cargo = $total;
                $saldo -= $cargo;
                $totalCargos += $cargo;
            }

            return [
                'id' => $movimiento->id,
                'fecha' => $this->formatearFecha($movimiento->fecha_movimiento),
                'tipo' => $movimiento->tipo,
                'concepto' => $movimiento->concepto,
                'referencia' => $movimiento->referencia_bancaria,
                'cargo' => round($cargo, 2),
                'abono' => round($abono, 2),
                'saldo' => round($saldo, 2),
                'detalles_count' => $movimiento->detalles_count,
            ];
        })->values();

        return [
            'tipo_reporte' => 'estado_cuenta',
            'titulo' => 'Estado de cuenta',
            'cuenta' => $this->infoCuenta($cuenta),
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'saldo_inicial' => round($saldoInicial, 2),
            'total_cargos' => round($totalCargos, 2),
            'total_abonos' => round($totalAbonos, 2),
            'saldo_final' => round($saldo, 2),
            'rows' => $rows,
        ];
    }

   public function detallado(int $cuentaId, string $fechaInicio, string $fechaFin): array
{
    $cuenta = ScbBancoModuloCuenta::query()
        ->with('banco')
        ->findOrFail($cuentaId);

    $saldoInicial = $this->calcularSaldoInicial($cuenta, $fechaInicio);

    $movimientos = ScbBancoModuloCuentaMovimiento::query()
        ->where('cuenta_id', $cuentaId)
        ->whereDate('fecha_movimiento', '>=', $fechaInicio)
        ->whereDate('fecha_movimiento', '<=', $fechaFin)
        ->with(['detalles.unidad'])
        ->withSum('detalles as total', 'monto')
        ->orderBy('fecha_movimiento')
        ->orderBy('id')
        ->get();

    $saldo = $saldoInicial;
    $totalCargos = 0;
    $totalAbonos = 0;

    $rows = $movimientos->map(function ($movimiento) use (&$saldo, &$totalCargos, &$totalAbonos) {
        $totalMovimiento = (float) ($movimiento->total ?? 0);

        $cargo = 0;
        $abono = 0;

        if ($movimiento->tipo === 'abono') {
            $abono = $totalMovimiento;
            $saldo += $abono;
            $totalAbonos += $abono;
        } else {
            $cargo = $totalMovimiento;
            $saldo -= $cargo;
            $totalCargos += $cargo;
        }

        $detalles = $movimiento->detalles->map(function ($detalle) {
            return [
                'unidad' => $detalle->unidad?->descripcion ?? 'S/N',
                'descripcion' => $detalle->descripcion,
                'referencia' => $detalle->referencia,
                'monto' => round((float) $detalle->monto, 2),
                'observaciones' => $detalle->observaciones,
            ];
        })->values();

        return [
            'id' => $movimiento->id,
            'fecha' => $this->formatearFecha($movimiento->fecha_movimiento),
            'tipo' => $movimiento->tipo,
            'concepto' => $movimiento->concepto,
            'referencia_bancaria' => $movimiento->referencia_bancaria,
            'cargo' => round($cargo, 2),
            'abono' => round($abono, 2),
            'saldo' => round($saldo, 2),
            'total_detalles' => round($detalles->sum('monto'), 2),
            'detalles_count' => $detalles->count(),
            'detalles' => $detalles,
        ];
    })->values();

    return [
        'tipo_reporte' => 'detallado',
        'titulo' => 'Reporte detallado de movimientos',
        'cuenta' => $this->infoCuenta($cuenta),
        'fecha_inicio' => $fechaInicio,
        'fecha_fin' => $fechaFin,
        'saldo_inicial' => round($saldoInicial, 2),
        'total_cargos' => round($totalCargos, 2),
        'total_abonos' => round($totalAbonos, 2),
        'saldo_final' => round($saldo, 2),
        'rows' => $rows,
    ];
}

    private function calcularSaldoInicial(ScbBancoModuloCuenta $cuenta, string $fechaInicio): float
    {
        $saldo = (float) ($cuenta->saldo_inicial ?? 0);

        $movimientosAntes = ScbBancoModuloCuentaMovimiento::query()
            ->where('cuenta_id', $cuenta->id)
            ->whereDate('fecha_movimiento', '<', $fechaInicio)
            ->withSum('detalles as total', 'monto')
            ->orderBy('fecha_movimiento')
            ->orderBy('id')
            ->get();

        foreach ($movimientosAntes as $movimiento) {
            $total = (float) ($movimiento->total ?? 0);

            if ($movimiento->tipo === 'abono') {
                $saldo += $total;
            } else {
                $saldo -= $total;
            }
        }

        return round($saldo, 2);
    }

    private function infoCuenta(ScbBancoModuloCuenta $cuenta): array
    {
        return [
            'id' => $cuenta->id,
            'banco' => $cuenta->banco?->nombre ?? 'S/N',
            'beneficiario' => $cuenta->beneficiario ?? 'S/N',
            'numero_cuenta' => $cuenta->numero_cuenta ?? 'Sin cuenta',
        ];
    }

    private function formatearFecha($fecha): string
    {
        if (!$fecha) {
            return 'S/N';
        }

        return Carbon::parse($fecha)->format('d/m/Y');
    }
}
