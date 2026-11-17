<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLiquidacionAjusteRequest;
use App\Models\Bancos;
use App\Models\Liquidaciones;
use App\Models\LiquidacionAjuste;
use App\Models\LiquidacionAjusteDetalle;
use App\Models\Operador;
use App\Models\PagoPrestamo;
use App\Models\Prestamo;
use App\Services\BancosService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LiquidacionesAjustesController extends Controller
{
    protected BancosService $bancosService;

    public function __construct(BancosService $bancosService)
    {
        $this->bancosService = $bancosService;
    }

    public function index()
    {
        $idEmpresa = Auth::user()->id_empresa;

        $operadores = Operador::where('id_empresa', $idEmpresa)
            ->orderBy('nombre', 'asc')
            ->get();

        $bancos = Bancos::where('id_empresa', $idEmpresa)
            ->orderBy('nombre_banco', 'asc')
            ->get();

        return view('liquidaciones.ajustes.index', compact('operadores', 'bancos'));
    }

    public function data(Request $request)
    {
        $idEmpresa = Auth::user()->id_empresa;
        $fechaI = $request->startDate;
        $fechaF = $request->endDate;
        $idOperador = $request->id_operador;
        $idBanco = $request->id_banco;
        $estatus = $request->estatus;

        $query = LiquidacionAjuste::with([
            'operador',
            'banco',
            'usuario',
            'liquidacion.Viajes.Contenedores',
            'detalles.prestamo'
        ])->where('id_empresa', $idEmpresa);

        if ($fechaI && $fechaF) {
            $query->whereBetween('fecha', [$fechaI, $fechaF]);
        }

        if (!empty($idOperador)) {
            $query->where('id_operador', $idOperador);
        }

        if (!empty($idBanco)) {
            $query->where('id_banco', $idBanco);
        }

        if (!empty($estatus)) {
            $query->where('estatus', $estatus);
        }

        $ajustes = $query->orderBy('fecha', 'desc')->orderBy('id', 'desc')->get();

        $data = $ajustes->map(function ($a) {
            $contenedores = $a->liquidacion?->Viajes?->map(function ($v) {
                return $v->Contenedores?->num_contenedor;
            })->filter()->unique()->implode(', ') ?: 'N/A';

            return [
                'id' => $a->id,
                'folio' => $a->folio ?: 'AJ-' . str_pad($a->id, 5, '0', STR_PAD_LEFT),
                'fecha' => $a->fecha ? Carbon::parse($a->fecha)->format('Y-m-d') : '',
                'id_liquidacion' => $a->id_liquidacion,
                'folio_liquidacion' => 'LIQ #' . $a->id_liquidacion,
                'id_operador' => $a->id_operador,
                'operador' => $a->operador?->nombre ?? 'N/A',
                'contenedores' => $contenedores,
                'id_banco' => $a->id_banco,
                'banco' => $a->banco ? ($a->banco->nombre_banco . ' (' . $a->banco->cuenta_bancaria . ')') : 'Sin dispersión',
                'motivo' => $a->motivo,
                'total_percepciones' => (float) $a->total_percepciones,
                'total_deducciones' => (float) $a->total_deducciones,
                'total_neto' => (float) $a->total_neto,
                'estatus' => $a->estatus,
                'usuario' => $a->usuario?->name ?? 'Sistema',
                'detalles' => $a->detalles->map(function ($d) {
                    return [
                        'id' => $d->id,
                        'tipo' => $d->tipo,
                        'categoria_concepto' => $d->categoria_concepto,
                        'concepto_nombre' => $d->nombre_concepto,
                        'descripcion' => $d->descripcion,
                        'monto' => (float) $d->monto,
                        'prestamo_info' => $d->prestamo ? ('Folio #' . $d->prestamo->id . ' (' . ucfirst($d->prestamo->tipo) . ')') : null,
                    ];
                }),
            ];
        });

        return response()->json([
            'TMensaje' => 'success',
            'data' => $data,
        ]);
    }

    public function buscarLiquidaciones(Request $request)
    {
        $idEmpresa = Auth::user()->id_empresa;
        $q = trim($request->get('q', ''));

        $query = Liquidaciones::with(['Operadores', 'Viajes.Contenedores'])
            ->whereHas('Operadores', function ($sub) use ($idEmpresa) {
                $sub->where('id_empresa', $idEmpresa);
            });

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                if (is_numeric($q)) {
                    $sub->orWhere('id', (int) $q);
                }
                $sub->orWhereHas('Operadores', function ($o) use ($q) {
                    $o->where('nombre', 'like', "%{$q}%");
                })->orWhereHas('Viajes.Contenedores', function ($c) use ($q) {
                    $c->where('num_contenedor', 'like', "%{$q}%");
                });
            });
        }

        $liquidaciones = $query->orderBy('id', 'desc')->limit(20)->get();

        $resultados = $liquidaciones->map(function ($l) {
            $contenedores = $l->Viajes->map(function ($v) {
                return $v->Contenedores?->num_contenedor;
            })->filter()->unique()->implode(', ');

            return [
                'id' => $l->id,
                'folio' => 'LIQ #' . $l->id,
                'fecha' => $l->fecha,
                'id_operador' => $l->id_operador,
                'operador_nombre' => $l->Operadores?->nombre ?? 'N/A',
                'contenedores' => $contenedores ?: 'Sin contenedores',
                'viajes_realizados' => $l->viajes_realizados,
                'sueldo_operador' => (float) $l->sueldo_operador,
                'dinero_viaje' => (float) $l->dinero_viaje,
                'dinero_justificado' => (float) $l->dinero_justificado,
                'total_pagado' => (float) $l->total_pago,
                'texto' => "LIQ #{$l->id} - {$l->Operadores?->nombre} - Fecha: {$l->fecha} - Total: $" . number_format($l->total_pago, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'items' => $resultados,
        ]);
    }

    public function getPrestamosOperador($idOperador)
    {
        $operador = Operador::find($idOperador);
        if (!$operador) {
            return response()->json(['success' => false, 'items' => []], 404);
        }

        $prestamos = Prestamo::with(['pagoprestamos'])
            ->where('id_operador', $idOperador)
            ->get()
            ->filter(fn ($p) => $p->saldo_calculado > 0)
            ->values()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'tipo' => $p->tipo, // 'prestamo' o 'adelanto'
                    'tipo_texto' => ucfirst($p->tipo),
                    'fecha' => $p->fecha_prestamo ? Carbon::parse($p->fecha_prestamo)->format('Y-m-d') : '',
                    'cantidad_original' => (float) $p->cantidad,
                    'saldo_actual' => (float) $p->saldo_calculado,
                    'texto' => ucfirst($p->tipo) . " #{$p->id} - Saldo: $" . number_format($p->saldo_calculado, 2) . " (" . ($p->fecha_prestamo ? Carbon::parse($p->fecha_prestamo)->format('d/m/Y') : '') . ")",
                ];
            });

        return response()->json([
            'success' => true,
            'items' => $prestamos,
        ]);
    }

    public function store(StoreLiquidacionAjusteRequest $request)
    {
        try {
            DB::beginTransaction();

            $idEmpresa = Auth::user()->id_empresa;
            $userId = Auth::id();

            $liquidacion = Liquidaciones::with(['Operadores'])->findOrFail($request->id_liquidacion);
            $operador = $liquidacion->Operadores;

            $partidas = collect($request->partidas);

            $totalPercepciones = (float) $partidas->where('tipo', 'percepcion')->sum('monto');
            $totalDeducciones = (float) $partidas->where('tipo', 'deduccion')->sum('monto');
            $totalNeto = round(max(0, $totalPercepciones - $totalDeducciones), 2);

            $fechaAjuste = Carbon::parse($request->fecha)->format('Y-m-d');

            // Validar saldo bancario si hay desembolso neto y banco seleccionado
            if ($totalNeto > 0 && !empty($request->id_banco)) {
                $validar = $this->bancosService->validarsaldoparacargo(
                    $idEmpresa,
                    $request->id_banco,
                    Carbon::parse($request->fecha)->format('d/m/Y'),
                    $totalNeto
                );

                if (isset($validar['saldodisponible']) && $validar['saldodisponible'] === false) {
                    return response()->json([
                        'Titulo' => 'Saldo insuficiente en banco',
                        'Mensaje' => $validar['message'] ?? 'La cuenta seleccionada no dispone de saldo suficiente para cubrir este ajuste.',
                        'TMensaje' => 'warning',
                        'success' => false,
                    ]);
                }
            }

            // Crear cabecera del ajuste
            $ajuste = new LiquidacionAjuste();
            $ajuste->id_empresa = $idEmpresa;
            $ajuste->id_liquidacion = $liquidacion->id;
            $ajuste->id_operador = $liquidacion->id_operador;
            $ajuste->id_banco = $totalNeto > 0 ? $request->id_banco : null;
            $ajuste->fecha = $fechaAjuste;
            $ajuste->motivo = $request->motivo;
            $ajuste->total_percepciones = $totalPercepciones;
            $ajuste->total_deducciones = $totalDeducciones;
            $ajuste->total_neto = $totalNeto;
            $ajuste->estatus = 'aplicado';
            $ajuste->user_id = $userId;
            $ajuste->save();

            // Asignar folio consecutivo
            $ajuste->folio = 'AJ-' . date('Ym') . '-' . str_pad($ajuste->id, 4, '0', STR_PAD_LEFT);
            $ajuste->save();

            // Guardar partidas y aplicar amortizaciones si corresponden a préstamos
            foreach ($partidas as $p) {
                $montoPartida = (float) $p['monto'];
                $idPrestamo = !empty($p['id_prestamo']) ? (int) $p['id_prestamo'] : null;

                $detalle = new LiquidacionAjusteDetalle();
                $detalle->id_ajuste = $ajuste->id;
                $detalle->tipo = $p['tipo'];
                $detalle->categoria_concepto = $p['categoria_concepto'];
                $detalle->id_prestamo = $idPrestamo;
                $detalle->descripcion = $p['descripcion'] ?? null;
                $detalle->monto = $montoPartida;
                $detalle->save();

                // Si es deducción vinculada a préstamo/adelanto, amortizar
                if ($p['tipo'] === 'deduccion' && in_array($p['categoria_concepto'], ['abono_prestamo', 'abono_adelanto']) && $idPrestamo) {
                    $prestamo = Prestamo::find($idPrestamo);
                    if ($prestamo && $prestamo->id_operador == $liquidacion->id_operador) {
                        $montoAbonar = min($montoPartida, $prestamo->saldo_calculado);

                        $prestamo->saldo_actual = max(0, $prestamo->saldo_actual - $montoAbonar);
                        $prestamo->save();

                        PagoPrestamo::create([
                            'id_liquidacion' => $liquidacion->id,
                            'id_prestamo' => $prestamo->id,
                            'saldo_anterior' => $prestamo->saldo_actual + $montoAbonar,
                            'monto_pago' => $montoAbonar,
                            'tipo_origen' => 'ajuste_liquidacion',
                            'id_banco' => $request->id_banco,
                            'referencia' => 'Ajuste #' . $ajuste->id . ' (' . $ajuste->folio . ')',
                            'fecha_pago' => $fechaAjuste,
                        ]);
                    }
                }
            }

            // Registrar movimiento bancario si hubo desembolso neto y cuenta bancaria
            if ($totalNeto > 0 && !empty($request->id_banco)) {
                $bancoData = [
                    'cuenta_bancaria_id' => $request->id_banco,
                    'tipo' => 'cargo',
                    'monto' => $totalNeto,
                    'concepto' => BancosService::generarConcepto(
                        'viaje',
                        'Ajuste / Complemento Liquidación #' . $liquidacion->id,
                        null,
                        $operador?->nombre ?? null,
                        null
                    ),
                    'fecha_movimiento' => $fechaAjuste,
                    'origen' => null,
                    'referencia' => 'Ajuste Liquidacion ' . $ajuste->folio,
                    'detalles' => [
                        [
                            'num_contenedor' => 'Ajuste #' . $ajuste->id,
                            'abono' => $totalNeto,
                            'motivo' => $ajuste->motivo,
                        ]
                    ],
                    'referenciaable_id' => $ajuste->id,
                    'referenciaable_type' => LiquidacionAjuste::class,
                ];

                $movimiento = $this->bancosService->registrarMovimiento($bancoData);
                if (!$movimiento) {
                    throw new \Exception('No se pudo registrar la afectación en bancos para este ajuste.');
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'Titulo' => 'Ajuste Aplicado',
                'Mensaje' => 'El ajuste de liquidación ' . $ajuste->folio . ' fue registrado correctamente.',
                'TMensaje' => 'success',
                'id_ajuste' => $ajuste->id,
            ]);
        } catch (\Throwable $t) {
            DB::rollBack();
            Log::channel('daily')->error('LiquidacionesAjustesController->store: ' . $t->getMessage(), [
                'trace' => $t->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'Titulo' => 'Error al guardar',
                'Mensaje' => 'Ocurrió un error al procesar el ajuste: ' . $t->getMessage(),
                'TMensaje' => 'error',
            ], 500);
        }
    }

    public function comprobantePdf($id)
    {
        $idEmpresa = Auth::user()->id_empresa;

        $ajuste = LiquidacionAjuste::with([
            'operador',
            'banco',
            'usuario',
            'liquidacion.Viajes.Contenedores',
            'detalles.prestamo'
        ])->where('id_empresa', $idEmpresa)->findOrFail($id);

        $user = Auth::user();

        $pdf = Pdf::loadView('liquidaciones.ajustes.pdf', compact('ajuste', 'user'));
        $pdf->setPaper('letter', 'portrait');

        $fileName = 'Comprobante_Ajuste_' . ($ajuste->folio ?: $ajuste->id) . '.pdf';

        return $pdf->stream($fileName);
    }

    public function cancelar($id)
    {
        try {
            DB::beginTransaction();

            $idEmpresa = Auth::user()->id_empresa;

            $ajuste = LiquidacionAjuste::with(['detalles'])->where('id_empresa', $idEmpresa)->findOrFail($id);

            if ($ajuste->estatus === 'cancelado') {
                return response()->json([
                    'success' => false,
                    'Titulo' => 'Acción no permitida',
                    'Mensaje' => 'El ajuste ya se encuentra cancelado.',
                    'TMensaje' => 'warning',
                ]);
            }

            // 1. Revertir amortizaciones a préstamos
            $pagosPrestamos = PagoPrestamo::where('referencia', 'like', '%Ajuste #' . $ajuste->id . '%')->get();
            foreach ($pagosPrestamos as $pago) {
                $prestamo = Prestamo::find($pago->id_prestamo);
                if ($prestamo) {
                    $prestamo->saldo_actual += $pago->monto_pago;
                    $prestamo->save();
                }
                $pago->delete();
            }

            // 2. Revertir movimiento bancario si existe
            $movimiento = \App\Models\CatBancoCuentasMovimientos::where('referenciaable_type', LiquidacionAjuste::class)
                ->where('referenciaable_id', $ajuste->id)
                ->first();

            if ($movimiento) {
                $banco = Bancos::find($movimiento->cuenta_bancaria_id);
                if ($banco) {
                    // Si fue cargo (egreso), al cancelarlo se reintegra el saldo
                    $banco->saldo += $movimiento->monto;
                    $banco->save();
                }
                $movimiento->delete();
            }

            // 3. Cambiar estatus a cancelado
            $ajuste->estatus = 'cancelado';
            $ajuste->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'Titulo' => 'Ajuste cancelado',
                'Mensaje' => 'El ajuste fue cancelado y se revirtieron los saldos bancarios y amortizaciones de préstamos.',
                'TMensaje' => 'success',
            ]);
        } catch (\Throwable $t) {
            DB::rollBack();
            Log::channel('daily')->error('LiquidacionesAjustesController->cancelar: ' . $t->getMessage());

            return response()->json([
                'success' => false,
                'Titulo' => 'Error al cancelar',
                'Mensaje' => 'No se pudo cancelar el ajuste: ' . $t->getMessage(),
                'TMensaje' => 'error',
            ], 500);
        }
    }
}
