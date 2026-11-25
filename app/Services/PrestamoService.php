<?php

namespace App\Services;

use App\Models\Bancos;
use App\Models\Operador;
use App\Models\PagoPrestamo;
use App\Models\Prestamo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class PrestamoService
{
    protected BancosService $bancosService;

    public function __construct(BancosService $bancosService)
    {
        $this->bancosService = $bancosService;
    }

    /**
     * Obtiene los operadores y las opciones de bancos disponibles para la vista de registro.
     */
    public function getIndexData(?int $idEmpresa = null): array
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;
        $operadores = Operador::where('id_empresa', $idEmpresa)->get();

        $fecha = Carbon::now()->format('Y-m-d');
        $bancos = $this->bancosService->getCuentasOption($idEmpresa, $fecha, $fecha, true);

        return [
            'operadores' => $operadores,
            'bancos' => $bancos,
        ];
    }

    /**
     * Registra un nuevo préstamo u adelanto, valida saldo bancario y genera egreso polimórfico.
     */
    public function storePrestamo(array $data, ?int $idEmpresa = null): array
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;
        $montoPrestamo = (float) $data['cantidad'];
        $idBanco = (int) $data['id_banco'];
        $fechaAplicacionRaw = $data['FechaAplicacion'];

        // 1. Validar saldo disponible en banco
        $validarSaldos = $this->bancosService->validarsaldoparacargo(
            $idEmpresa,
            $idBanco,
            $fechaAplicacionRaw,
            $montoPrestamo
        );

        if ($validarSaldos["saldodisponible"] === false) {
            return [
                'success' => false,
                'status' => 422,
                'TMensaje' => "error",
                'Titulo' => "Saldo bancos",
                'Mensaje' => $validarSaldos["message"]
            ];
        }

        return DB::transaction(function () use ($data, $montoPrestamo, $idBanco, $fechaAplicacionRaw) {
            $fechaPrestamo = Carbon::createFromFormat('d/m/Y', $fechaAplicacionRaw)->format('Y-m-d');

            $prestamo = Prestamo::create([
                'id_operador' => $data['id_operador'],
                'id_banco' => $idBanco,
                'cantidad' => $montoPrestamo,
                'tipo' => $data['tipo'] ?? Prestamo::TIPO_PRESTAMO,
                'pagos' => 0,
                'saldo_actual' => $montoPrestamo,
                'fecha_prestamo' => $fechaPrestamo,
            ]);

            $operador = Operador::find($data["id_operador"]);
            $operadorNombre = $operador?->nombre ?? '';

            $datosMovimiento = [
                'cuenta_bancaria_id' => $idBanco,
                'tipo' => 'cargo',
                'monto' => $montoPrestamo,
                'concepto' => "Prestamo Operador: {$operadorNombre}",
                'fecha_movimiento' => $fechaPrestamo,
                'origen' => null,
                'referencia' => 'Prestamo',
                'detalles' => [],
                'referenciaable_id' => $prestamo->id,
                'referenciaable_type' => Prestamo::class,
            ];

            $movimientoCrear = $this->bancosService->registrarMovimiento($datosMovimiento);

            if (!$movimientoCrear) {
                throw new Exception('No se pudo crear el movimiento bancario para el préstamo del operador.');
            }

            return [
                'success' => true,
                'status' => 201,
                'message' => 'Préstamo guardado correctamente',
                'data' => $prestamo
            ];
        });
    }

    /**
     * Obtiene el resumen de préstamos y adelantos agrupados por operador para AG Grid.
     */
    public function getListaPrestamosAgrupados($user): Collection
    {
        return Prestamo::with(['operador', 'pagoprestamos'])
            ->when(!$user->is_admin, function ($q) use ($user) {
                $q->whereHas('operador', function ($q2) use ($user) {
                    $q2->where('id_empresa', $user->id_empresa);
                });
            })
            ->get()
            ->groupBy('id_operador')
            ->map(function ($items) {
                $operador = $items->first()->operador;

                $prestamos = $items->where('tipo', Prestamo::TIPO_PRESTAMO);
                $conteoPrestamos = $prestamos->count();
                $totalPrestamos = $prestamos->sum('cantidad');

                $adelantos = $items->where('tipo', Prestamo::TIPO_ADELANTO);
                $conteoAdelantos = $adelantos->count();
                $totalAdelantos = $adelantos->sum('cantidad');

                $totalDeuda = $totalPrestamos + $totalAdelantos;

                $totalAbonos = $items
                    ->flatMap(fn ($p) => $p->pagoprestamos)
                    ->sum('monto_pago');

                $saldoFinal = $totalDeuda - $totalAbonos;

                $conteoMovimientos = $items
                    ->flatMap(fn ($p) => $p->pagoprestamos)
                    ->count();

                return [
                    'id_operador' => $operador->id,
                    'operador' => $operador->nombre,
                    'conteo_prestamos' => $conteoPrestamos,
                    'total_prestamos' => $totalPrestamos,
                    'conteo_adelantos' => $conteoAdelantos,
                    'total_adelantos' => $totalAdelantos,
                    'total_deuda' => $totalDeuda,
                    'conteo_movimientos' => $conteoMovimientos,
                    'total_abonos' => $totalAbonos,
                    'saldo_final' => $saldoFinal,
                ];
            })
            ->values();
    }

    /**
     * Obtiene los pagos realizados a un préstamo en particular.
     */
    public function getPrestamosPagosDetalle(int $idPrestamo): array
    {
        $prestamo = Prestamo::with(['operador', 'pagoprestamos'])->findOrFail($idPrestamo);
        $historial = $prestamo->pagoprestamos()->orderBy('created_at')->get();

        return [
            'prestamos' => $prestamo,
            'historial' => $historial,
            'id_prestamo' => $idPrestamo,
            'total' => (float) $prestamo->cantidad,
        ];
    }

    /**
     * Obtiene la vista de detalle de préstamos de un operador.
     */
    public function getOperadorPrestamosDetalle(int $idOperador, $user): array
    {
        $fecha = Carbon::now()->format('Y-m-d');
        $bancos = $this->bancosService->getCuentasOption($user->id_empresa, $fecha, $fecha, true);

        $operador = Operador::findOrFail($idOperador);

        $prestamos = Prestamo::with(['operador', 'banco', 'pagoprestamos'])
            ->where('id_operador', $idOperador)
            ->when(!$user->is_admin, function ($q) use ($user) {
                $q->whereHas('operador', function ($q2) use ($user) {
                    $q2->where('id_empresa', $user->id_empresa);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPrestamos = $prestamos->where('tipo', Prestamo::TIPO_PRESTAMO)->sum('cantidad');
        $totalAdelantos = $prestamos->where('tipo', Prestamo::TIPO_ADELANTO)->sum('cantidad');
        $totalDeuda = $totalPrestamos + $totalAdelantos;
        $totalAbonos = $prestamos->flatMap(fn ($p) => $p->pagoprestamos)->sum('monto_pago');
        $saldoFinal = $totalDeuda - $totalAbonos;

        return compact(
            'operador',
            'prestamos',
            'totalPrestamos',
            'totalAdelantos',
            'totalDeuda',
            'totalAbonos',
            'saldoFinal',
            'bancos'
        );
    }

    /**
     * Registra un abono manual a un préstamo con ingreso bancario polimórfico.
     */
    public function abonarPrestamo(int $idPrestamo, array $data): array
    {
        $monto = (float) $data['monto'];
        $idBancoAbono = (int) $data['id_banco_abono'];
        $fechaAbonoRaw = $data['fechaAbono'];
        $referencia = $data['referencia'] ?? '';

        return DB::transaction(function () use ($idPrestamo, $monto, $idBancoAbono, $fechaAbonoRaw, $referencia) {
            $prestamo = Prestamo::findOrFail($idPrestamo);
            $saldoActual = $prestamo->cantidad - $prestamo->TotalPagado;

            if ($monto > $saldoActual) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'No puedes abonar más de la deuda actual. Tu saldo pendiente es de $' . number_format($saldoActual, 2),
                    'titulo' => 'Validacion saldo '
                ];
            }

            $fechaPago = Carbon::createFromFormat('d/m/Y', $fechaAbonoRaw)->format('Y-m-d');

            $nuevoAbono = PagoPrestamo::create([
                'id_liquidacion' => 0, // Pago directo del operador
                'id_prestamo' => $prestamo->id,
                'saldo_anterior' => $prestamo->saldo_actual,
                'monto_pago' => $monto,
                'tipo_origen' => 'directo',
                'id_banco' => $idBancoAbono,
                'referencia' => $referencia,
                'fecha_pago' => $fechaPago,
            ]);

            // Actualizar saldo del préstamo
            $prestamo->pagos += $monto;
            $prestamo->saldo_actual = max($prestamo->cantidad - $prestamo->pagos, 0);
            $prestamo->save();

            // Registrar movimiento de abono (ingreso) en banco
            $operador = $prestamo->operador?->nombre ?? '';
            $datosMovimiento = [
                'cuenta_bancaria_id' => $idBancoAbono,
                'tipo' => 'abono',
                'monto' => $monto,
                'concepto' => "Abono a préstamo de operador {$referencia} {$operador}",
                'fecha_movimiento' => $fechaPago,
                'origen' => null,
                'referencia' => 'Abono prestamo',
                'detalles' => [],
                'referenciaable_id' => $nuevoAbono->id,
                'referenciaable_type' => PagoPrestamo::class,
            ];

            $movimientoCrear = $this->bancosService->registrarMovimiento($datosMovimiento);

            if (!$movimientoCrear) {
                throw new Exception('No se pudo crear el movimiento bancario para el abono del préstamo.');
            }

            $prestamosActualizados = Prestamo::with(['operador', 'banco'])
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'success' => true,
                'status' => 200,
                'message' => 'Abono registrado correctamente',
                'prestamosActualizados' => $prestamosActualizados
            ];
        });
    }

    /**
     * Obtiene los datos completos para los reportes PDF y Excel de préstamos de un operador.
     */
    public function getDatosReporteOperador(int $idOperador, $user): array
    {
        $operador = Operador::when(!$user->is_admin, function ($q) use ($user) {
            $q->where('id_empresa', $user->id_empresa);
        })->findOrFail($idOperador);

        $prestamos = Prestamo::with([
            'operador',
            'banco.catBanco',
            'pagoprestamos.liquidacion.Viajes.Contenedores'
        ])
            ->where('id_operador', $idOperador)
            ->when(!$user->is_admin, function ($q) use ($user) {
                $q->whereHas('operador', function ($q2) use ($user) {
                    $q2->where('id_empresa', $user->id_empresa);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPrestamos = $prestamos->where('tipo', Prestamo::TIPO_PRESTAMO)->sum('cantidad');
        $totalAdelantos = $prestamos->where('tipo', Prestamo::TIPO_ADELANTO)->sum('cantidad');
        $totalDeuda = $totalPrestamos + $totalAdelantos;
        $totalAbonos = $prestamos->flatMap(fn ($p) => $p->pagoprestamos)->sum('monto_pago');
        $saldoFinal = $totalDeuda - $totalAbonos;

        return compact(
            'operador',
            'prestamos',
            'totalPrestamos',
            'totalAdelantos',
            'totalDeuda',
            'totalAbonos',
            'saldoFinal'
        );
    }
}
