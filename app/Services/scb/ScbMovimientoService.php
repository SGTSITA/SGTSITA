<?php

namespace App\Services\Scb;

use App\Models\ScbBancoModuloCuenta;
use App\Models\ScbBancoModuloCuentaMovimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScbMovimientoService
{
    public function getAll()
    {
        return ScbBancoModuloCuentaMovimiento::query()
            ->with(['cuenta.banco', 'usuario', 'detalles.unidad'])
            ->withSum('detalles as total', 'monto')
            ->orderByDesc('fecha_movimiento')
            ->orderByDesc('id')
            ->get();
    }

    public function findById(int $id): ScbBancoModuloCuentaMovimiento
    {
        return ScbBancoModuloCuentaMovimiento::query()
            ->with(['cuenta.banco', 'usuario', 'detalles.unidad'])
            ->withSum('detalles as total', 'monto')
            ->findOrFail($id);
    }

    public function create(array $data): ScbBancoModuloCuentaMovimiento
    {
        return DB::transaction(function () use ($data) {
            $this->validarTotalMovimientoContraDetalles($data);
            $this->validarSaldoParaCargo($data);

            $movimiento = ScbBancoModuloCuentaMovimiento::create([
                'cuenta_id' => $data['cuenta_id'],
                'tipo' => $data['tipo'],
                'fecha_movimiento' => $data['fecha_movimiento'],
                'concepto' => $data['concepto'],
                'referencia_bancaria' => $data['referencia_bancaria'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'user_id' => auth()->id(),
            ]);

            $this->guardarDetalles($movimiento, $data['detalles'] ?? []);

            return $this->findById($movimiento->id);
        });
    }

    public function update(int $id, array $data): ScbBancoModuloCuentaMovimiento
    {
        return DB::transaction(function () use ($id, $data) {
            $this->validarTotalMovimientoContraDetalles($data);
            $this->validarSaldoParaCargo($data, $id);

            $movimiento = ScbBancoModuloCuentaMovimiento::findOrFail($id);

            $movimiento->update([
                'cuenta_id' => $data['cuenta_id'],
                'tipo' => $data['tipo'],
                'fecha_movimiento' => $data['fecha_movimiento'],
                'concepto' => $data['concepto'],
                'referencia_bancaria' => $data['referencia_bancaria'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            $movimiento->detalles()->delete();

            $this->guardarDetalles($movimiento, $data['detalles'] ?? []);

            return $this->findById($movimiento->id);
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $movimiento = ScbBancoModuloCuentaMovimiento::findOrFail($id);
            $movimiento->delete();
        });
    }

    public function estadoCuenta(int $cuentaId, string $fechaInicio, string $fechaFin): array
    {
        $cuenta = ScbBancoModuloCuenta::findOrFail($cuentaId);

        $saldoInicial = (float) ($cuenta->saldo_inicial ?? 0);

        $movimientosAntes = ScbBancoModuloCuentaMovimiento::query()
            ->where('cuenta_id', $cuentaId)
            ->whereDate('fecha_movimiento', '<', $fechaInicio)
            ->withSum('detalles as total', 'monto')
            ->orderBy('fecha_movimiento')
            ->orderBy('id')
            ->get();

        foreach ($movimientosAntes as $movimiento) {
            $total = (float) ($movimiento->total ?? 0);

            if ($movimiento->tipo === 'abono') {
                $saldoInicial += $total;
            } else {
                $saldoInicial -= $total;
            }
        }

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
                'concepto' => $movimiento->concepto,
                'referencia' => $movimiento->referencia_bancaria,
                'cargo' => round($cargo, 2),
                'abono' => round($abono, 2),
                'saldo' => round($saldo, 2),
                'detalles_count' => $movimiento->detalles_count,
            ];
        });

        return [
            'success' => true,
            'saldo_inicial' => round($saldoInicial, 2),
            'total_cargos' => round($totalCargos, 2),
            'total_abonos' => round($totalAbonos, 2),
            'saldo_final' => round($saldo, 2),
            'movimientos' => $rows,
        ];
    }

    public function calcularSaldoCuenta(int $cuentaId, ?int $excluirMovimientoId = null): float
    {
        $cuenta = ScbBancoModuloCuenta::findOrFail($cuentaId);

        $movimientosQuery = ScbBancoModuloCuentaMovimiento::query()
            ->where('cuenta_id', $cuentaId)
            ->withSum('detalles as total', 'monto');

        if ($excluirMovimientoId) {
            $movimientosQuery->where('id', '!=', $excluirMovimientoId);
        }

        $saldo = (float) ($cuenta->saldo_inicial ?? 0);

        $movimientos = $movimientosQuery->get();

        foreach ($movimientos as $movimiento) {
            $total = (float) ($movimiento->total ?? 0);

            if ($movimiento->tipo === 'abono') {
                $saldo += $total;
            } else {
                $saldo -= $total;
            }
        }

        return round($saldo, 2);
    }

    public function validarSaldoParaCargo(array $data, ?int $excluirMovimientoId = null): void
    {
        if (($data['tipo'] ?? null) !== 'cargo') {
            return;
        }

        $totalCargo = $this->calcularTotalDetalles($data['detalles'] ?? []);

        $saldoDisponible = $this->calcularSaldoCuenta(
            cuentaId: (int) $data['cuenta_id'],
            excluirMovimientoId: $excluirMovimientoId
        );

        if ($totalCargo > $saldoDisponible) {
            throw ValidationException::withMessages([
                'total_movimiento' => [
                    'Saldo insuficiente. Saldo disponible: $' .
                    number_format($saldoDisponible, 2) .
                    ', cargo solicitado: $' .
                    number_format($totalCargo, 2),
                ],
            ]);
        }
    }

    private function validarTotalMovimientoContraDetalles(array $data): void
    {
        $totalMovimiento = round((float) ($data['total_movimiento'] ?? 0), 2);
        $totalDetalles = $this->calcularTotalDetalles($data['detalles'] ?? []);

        if ($totalMovimiento <= 0) {
            throw ValidationException::withMessages([
                'total_movimiento' => [
                    'El total del movimiento debe ser mayor a cero.',
                ],
            ]);
        }

        if (empty($data['detalles']) || $totalDetalles <= 0) {
            throw ValidationException::withMessages([
                'detalles' => [
                    'Agrega al menos un detalle con monto mayor a cero.',
                ],
            ]);
        }

        if ($totalDetalles > $totalMovimiento) {
            throw ValidationException::withMessages([
                'detalles' => [
                    'El total de detalles supera el total del movimiento.',
                ],
            ]);
        }

        if (abs($totalMovimiento - $totalDetalles) >= 0.01) {
            throw ValidationException::withMessages([
                'detalles' => [
                    'El total del movimiento debe coincidir exactamente con el total de detalles.',
                ],
            ]);
        }
    }

    private function calcularTotalDetalles(array $detalles): float
    {
        $total = collect($detalles)->sum(function ($detalle) {
            return (float) ($detalle['monto'] ?? 0);
        });

        return round((float) $total, 2);
    }

    private function guardarDetalles(ScbBancoModuloCuentaMovimiento $movimiento, array $detalles): void
    {
        foreach ($detalles as $detalle) {
            $movimiento->detalles()->create([
                'unidad_id' => $detalle['unidad_id'] ?? null,
                'descripcion' => $detalle['descripcion'],
                'referencia' => $detalle['referencia'] ?? null,
                'monto' => $detalle['monto'],
                'observaciones' => $detalle['observaciones'] ?? null,
            ]);
        }
    }

    private function formatearFecha($fecha): string
    {
        if (!$fecha) {
            return 'S/N';
        }

        return Carbon::parse($fecha)->format('d/m/Y');
    }
}
