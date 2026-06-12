<?php

namespace App\Services\scb;

use App\Models\ScbBancoModuloCuenta;
use App\Services\Scb\ScbMovimientoService;

class ScbReporteService
{
    public function __construct(
        protected ScbMovimientoService $movimientoService
    ) {
    }

    public function generar(array $filtros): array
    {
        $cuentaId = (int) $filtros['cuenta_id'];

        $unidadId = !empty($filtros['unidad_id'])
            ? (int) $filtros['unidad_id']
            : null;

        $estadoCuenta = $this->movimientoService->estadoCuenta(
            cuentaId: $cuentaId,
            unidadId: $unidadId,
            fechaInicio: $filtros['fecha_inicio'],
            fechaFin: $filtros['fecha_fin'],
        );

        if ($filtros['tipo_reporte'] === 'detallado') {
            return $this->formatearDetallado($estadoCuenta, $filtros, $cuentaId, $unidadId);
        }

        return $this->formatearEstadoCuenta($estadoCuenta, $filtros, $cuentaId, $unidadId);
    }

    private function formatearEstadoCuenta(
        array $estadoCuenta,
        array $filtros,
        int $cuentaId,
        ?int $unidadId
    ): array {
        $rows = collect($estadoCuenta['movimientos'] ?? [])
            ->map(function ($movimiento) {
             $detalles = collect($movimiento['detalles'] ?? [])
    ->map(function ($detalle) {
        $unidad = $detalle['unidad'] ?? null;

        $descripcionUnidad = $unidad['descripcion'] ?? 'S/N';
        $placas = $unidad['placas'] ?? null;

        return [
            'unidad' => $placas
                ? "{$descripcionUnidad} - {$placas}"
                : $descripcionUnidad,
            'descripcion' => $detalle['descripcion'] ?? '',
            'referencia' => $detalle['referencia'] ?? 'S/N',
            'monto' => round((float) ($detalle['importe'] ?? $detalle['monto'] ?? 0), 2),
        ];
    })
    ->values();

return [
    'id' => $movimiento['id'],
    'fecha' => $movimiento['fecha'],
    'concepto' => $movimiento['concepto'] ?? '',
    'referencia' => $movimiento['referencia'] ?? 'S/N',
    'cargo' => round((float) ($movimiento['cargo'] ?? 0), 2),
    'abono' => round((float) ($movimiento['abono'] ?? 0), 2),
    'saldo' => round((float) ($movimiento['saldo'] ?? 0), 2),
    'detalles_count' => (int) ($movimiento['detalles_count'] ?? $detalles->count()),
    'detalles' => $detalles,
];
            })
            ->values();

        return [
            'tipo_reporte' => 'estado_cuenta',
            'titulo' => 'Estado de cuenta',
            'cuenta' => $this->infoCuenta($cuentaId),
            'unidad' => $this->resolverUnidadDesdeEstadoCuenta($estadoCuenta, $unidadId),
            'fecha_inicio' => $filtros['fecha_inicio'],
            'fecha_fin' => $filtros['fecha_fin'],
            'saldo_inicial' => round((float) ($estadoCuenta['saldo_inicial'] ?? 0), 2),
            'total_cargos' => round((float) ($estadoCuenta['total_cargos'] ?? 0), 2),
            'total_abonos' => round((float) ($estadoCuenta['total_abonos'] ?? 0), 2),
            'saldo_final' => round((float) ($estadoCuenta['saldo_final'] ?? 0), 2),
            'rows' => $rows,
        ];
    }

    private function formatearDetallado(
        array $estadoCuenta,
        array $filtros,
        int $cuentaId,
        ?int $unidadId
    ): array {
        $rows = collect($estadoCuenta['movimientos'] ?? [])
            ->map(function ($movimiento) {
                $detalles = collect($movimiento['detalles'] ?? [])
                    ->map(function ($detalle) {
                        return [
                            'unidad' => $this->nombreUnidadDetalle($detalle),
                            'descripcion' => $detalle['descripcion'] ?? '',
                            'referencia' => $detalle['referencia'] ?? 'S/N',
                            'monto' => round((float) ($detalle['importe'] ?? $detalle['monto'] ?? 0), 2),
                            'observaciones' => $detalle['observaciones'] ?? null,
                        ];
                    })
                    ->values();

                return [
                    'id' => $movimiento['id'],
                    'fecha' => $movimiento['fecha'],
                    'concepto' => $movimiento['concepto'] ?? '',
                    'referencia_bancaria' => $movimiento['referencia'] ?? 'S/N',
                    'cargo' => round((float) ($movimiento['cargo'] ?? 0), 2),
                    'abono' => round((float) ($movimiento['abono'] ?? 0), 2),
                    'saldo' => round((float) ($movimiento['saldo'] ?? 0), 2),
                    'total_detalles' => round($detalles->sum('monto'), 2),
                    'detalles_count' => $detalles->count(),
                    'detalles' => $detalles,
                ];
            })
            ->values();

        return [
            'tipo_reporte' => 'detallado',
            'titulo' => 'Reporte detallado de movimientos',
            'cuenta' => $this->infoCuenta($cuentaId),
            'unidad' => $this->resolverUnidadDesdeEstadoCuenta($estadoCuenta, $unidadId),
            'fecha_inicio' => $filtros['fecha_inicio'],
            'fecha_fin' => $filtros['fecha_fin'],
            'saldo_inicial' => round((float) ($estadoCuenta['saldo_inicial'] ?? 0), 2),
            'total_cargos' => round((float) ($estadoCuenta['total_cargos'] ?? 0), 2),
            'total_abonos' => round((float) ($estadoCuenta['total_abonos'] ?? 0), 2),
            'saldo_final' => round((float) ($estadoCuenta['saldo_final'] ?? 0), 2),
            'rows' => $rows,
        ];
    }

    private function infoCuenta(int $cuentaId): array
    {
        $cuenta = ScbBancoModuloCuenta::query()
            ->with('banco')
            ->findOrFail($cuentaId);

        return [
            'id' => $cuenta->id,
            'banco' => $cuenta->banco?->nombre ?? 'S/N',
            'beneficiario' => $cuenta->beneficiario ?? 'S/N',
            'numero_cuenta' => $cuenta->numero_cuenta ?? 'Sin cuenta',
        ];
    }

    private function resolverUnidadDesdeEstadoCuenta(array $estadoCuenta, ?int $unidadId): ?array
    {
        if (!$unidadId) {
            return null;
        }

        $movimientos = collect($estadoCuenta['movimientos'] ?? []);

        $detalle = $movimientos
            ->flatMap(fn ($movimiento) => $movimiento['detalles'] ?? [])
            ->first(function ($detalle) use ($unidadId) {
                return (int) ($detalle['unidad_id'] ?? 0) === $unidadId;
            });

        if (!$detalle) {
            return [
                'id' => $unidadId,
                'nombre' => "Unidad #{$unidadId}",
            ];
        }

        return [
            'id' => $unidadId,
            'nombre' => $this->nombreUnidadDetalle($detalle),
        ];
    }

    private function nombreUnidadDetalle(array $detalle): string
    {
        $unidad = $detalle['unidad'] ?? null;

        if (!$unidad) {
            return 'S/N';
        }

        $descripcion = $unidad['descripcion'] ?? 'S/N';
        $placas = $unidad['placas'] ?? null;

        if (!$placas) {
            return $descripcion;
        }

        return "{$descripcion} - {$placas}";
    }
}
