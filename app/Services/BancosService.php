<?php

namespace App\Services;

use App\Models\Bancos;
use App\Models\CatBancoCuentasMovimientos;
use App\Models\Cotizaciones;
use App\Models\BancoDinero;
use App\Models\BancoDineroOpe;
use App\Models\GastosGenerales;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class BancosService
{
    public function getCuentasOption($empresaId, $fechaInicio, $fechaFin, $validarSaldo)
    {
        $cuentas = Bancos::with('catBanco')
        ->where('id_empresa', $empresaId)
        ->where('estado', 1)
        ->get()
        ->map(function ($cuenta) use ($fechaInicio, $fechaFin) {

            $resultado = $this->getSaldoActualFecha(
                $cuenta,
                $fechaInicio,
                $fechaFin
            );

            return [
                'id' => $cuenta->id,
                'tipo' => $cuenta->tipo,
                'nombre_beneficiario' => $cuenta->nombre_beneficiario,
                'banco' => optional($cuenta->catBanco)->nombre,
               'display' =>
                        (optional($cuenta->catBanco)->nombre ?? '')
                        . ' •••• '
                        . substr($cuenta->cuenta_bancaria, -4)
                        . ' '
                        . ($cuenta->tipo ?? '')
                        . ' / '
                        . ($cuenta->nombre_beneficiario ?? ''),
                'saldo_anterior' => $resultado['saldoAnterior'],
                'saldo_actual'   => $resultado['saldoActual'],
            ];
        });


        if ($validarSaldo) {
            $cuentas = $cuentas->filter(function ($cuenta) {
                return $cuenta['saldo_actual'] > 0;
            })->values();
        }
        //   dd($cuentas);

        return $cuentas;
    }


    public function obtenerDetalleCuenta($cuentaId, $empresaId, $fechaInicio = null, $fechaFin = null)
    {

        $cuenta = Bancos::where('id', $cuentaId)
            ->where('id_empresa', $empresaId)
            ->where('estado', 1)
            ->firstOrFail();

        $cuentas = Bancos::where('id_empresa', $empresaId)
          ->where('estado', 1)
          ->get();


        $resultado = $this->getsaldoActualFecha(
            $cuenta,
            $fechaInicio,
            $fechaFin
        );

        $movimientos =  $resultado["movimientos"];

        $conteoDepositos = $movimientos->where('tipo', 'abono')->count();
        $totalDepositos  = $movimientos->where('tipo', 'abono')->sum('monto');

        $conteoCargos = $movimientos->where('tipo', 'cargo')->count();
        $totalCargos  = $movimientos->where('tipo', 'cargo')->sum('monto');

        return [
            'cuenta' => $cuenta,
           'movimientos' => $resultado['movimientos'],
    'saldoAnterior' => $resultado['saldoAnterior'],
    'saldoActual' => $resultado['saldoActual'],
            'conteo_depositos' => $conteoDepositos,
            'total_depositos' => $totalDepositos,
            'conteo_cargos' => $conteoCargos,
            'total_cargos' => $totalCargos,
             'cuentas' => $cuentas
        ];
    }
    public function getsaldoActualFecha($cuenta, $fecha_ini, $fecha_fin)
    {
        $saldo = (float) $cuenta->inicial_saldo;


        if ($fecha_ini) {
            $previos = $cuenta->movimientos()
                ->where('fecha_movimiento', '<', $fecha_ini)
                ->orderBy('fecha_movimiento')
                ->get();

            foreach ($previos as $mov) {
                $saldo = $this->aplicarMovimiento($saldo, $mov);
            }
        }
        $saldoAnterior = $saldo;

        $query = $cuenta->movimientos()
            ->orderBy('fecha_movimiento');

        if ($fecha_ini && $fecha_fin) {
            $query->whereBetween('fecha_movimiento', [$fecha_ini, $fecha_fin]);
        }

        $movimientos = $query->get();

        $movimientos = $movimientos->map(function ($mov) use (&$saldo) {
            $saldo = $this->aplicarMovimiento($saldo, $mov);
            $mov->saldo_resultante = $saldo;
            return $mov;
        });

        return [
            'movimientos' => $movimientos,
            'saldoAnterior' => $saldoAnterior,
            'saldoActual' => $saldo
        ];


    }
    private function aplicarMovimiento(float $saldo, $mov): float
    {
        if ($mov->tipo === 'abono') {
            return $saldo + (float) $mov->monto;
        }

        return $saldo - (float) $mov->monto;
    }
    public function validarsaldoparacargo($empresaId, $cuentaId, $fechaAplicacion, $dineroRegistrar)
    {
        $cuenta =  Bancos::where('id', $cuentaId)
        ->where('id_empresa', $empresaId)
        ->where('estado', 1)
        ->first();

        $movimientos = $this->getsaldoActualFecha($cuenta, $fechaAplicacion, $fechaAplicacion);

        //

        if ($movimientos["saldoActual"] < $dineroRegistrar) {
            $faltante = $dineroRegistrar - $movimientos["saldoActual"];

            return [
                'message' => 'Saldo insuficiente para completar el cargo por : '.number_format($dineroRegistrar, 2),
                'saldodisponible' => false,
                'faltante' => $faltante,
            ];
        }
        //  dd($movimientos);
        return [
               'message' => 'Movimiento puede registrarse',
               'saldodisponible' => true,
               'faltante' => 0,
               'saldoActual' => $movimientos["saldoActual"],
               'movimientos' => $movimientos["movimientos"]
           ];


    }

    public function registrarMovimiento($data)
    {

        return DB::transaction(function () use ($data) {

            return CatBancoCuentasMovimientos::create([
                'cuenta_bancaria_id' => $data['cuenta_bancaria_id'],
                'tipo'               => $data['tipo'], // cargo | abono
                'monto'              => $data['monto'],
                'concepto'           => $data['concepto'] ?? null,
                'fecha_movimiento'   => $data['fecha_movimiento'],
                'origen'             => $data['origen'] ?? 'sistema',
                'referencia'         => $data['referencia'] ?? null,
                'referenciaable_type'         => $data['referenciaable_type'] ?? null,
                'referenciaable_id'         => $data['referenciaable_id'] ?? null,
                'detalles'           => $data['detalles'] ?? null,
                'observaciones'           => $data['observaciones'] ?? null,
                'user_id'            => auth()->id(),
            ]);

        });
    }


    public function findMovimiento($idBuscartipo, $modeloType, $cuentaId)
    {

        $movimiento = CatBancoCuentasMovimientos::where('referenciaable_id', $idBuscartipo)
           ->where('cancelado', 0)
           ->where('cuenta_bancaria_id', $cuentaId)
           ->where('referenciaable_type', $modeloType)
           ->where('origen', 'sistema')
           ->firstOrFail();

        return $movimiento;
    }
    public function cancelarMovimiento(int $cuentaId, int $movimientoId)
    {
        return DB::transaction(function () use ($cuentaId, $movimientoId) {

            $movimiento = CatBancoCuentasMovimientos::where('id', $movimientoId)
                ->where('cuenta_bancaria_id', $cuentaId)
                ->firstOrFail();


            if ($movimiento->cancelado) {
                return [
                    'status' => false,
                    'message' => 'Este movimiento ya fue cancelado.'
                ];
            }


            $tipoInverso = $movimiento->tipo === 'cargo'
                ? 'abono'
                : 'cargo';


            $cancelacion = CatBancoCuentasMovimientos::create([
                'cuenta_bancaria_id' => $cuentaId,
                'tipo'               => $tipoInverso,
                'monto'              => $movimiento->monto,
                'concepto'           => 'Devolución - ' . $movimiento->concepto,
                'fecha_movimiento'   => now(),
                'origen'             => 'sistema',
                'referencia'         => 'canleación' ,
                'referenciaable_type'         => $movimiento->referenciaable_type ?? null,
                'referenciaable_id'         => $movimiento->referenciaable_id ?? null,
                'detalles' => $movimiento->detalles,
                'cancelado' => false,
                'user_id'   => auth()->id(),
                'observaciones' =>  'movimiento_original_id |' . $movimiento->id . '|'. 'concepto_original|'  . $movimiento->concepto,
            ]);


            $movimiento->update([
                'cancelado' => true
            ]);

            return [
                'status' => true,
                'message' => 'Movimiento cancelado correctamente.',
                'data' => $cancelacion
            ];
        });
    }
}
