<?php

namespace App\Http\Controllers;

use App\Models\Bancos;

use App\Models\Gasto;
use App\Models\GastoPago;
use App\Models\CategoriasGastos;
use App\Models\Equipo;
use App\Models\Operador;
use App\Models\Asignaciones;
use App\Models\DocumCotizacion;
use App\Models\Cotizaciones;
use App\Services\GastosService;
use App\Services\BancosService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\StoreGastoRequest;

class GastosController extends Controller
{
    public function __construct(
        private GastosService $gastosService,
        private BancosService $bancosService
    ) {
    }

   public function index()
    {
        return view(
            'gastos.index',
            $this->gastosService->getCatalogosIndex(
                auth()->user()->id_empresa
            )
        );
    }

   public function data(Request $request)
    {
        return response()->json([
            'TMensaje' => 'success',
            'gastos' => $this->gastosService->listar([
                'id_empresa' => auth()->user()->id_empresa,
                'from' => $request->from,
                'to' => $request->to,
                'tipo_gasto' => $request->tipo_gasto,
                'search' => $request->search,
                'cotizacion_id' => $request->cotizacion_id,
            ]),
        ]);
    }

    public function store(StoreGastoRequest  $request)
    {
        $validated = $request->validated();

  /*   $validated['id_empresa'] = auth()->user()->id_empresa;
    $validated['user_id'] = auth()->id(); */

        $vinculos = [];
        $imputaciones = [];
        $programaciones = [];
        $montoTotal = (float) $request->monto_total;

        $impacto = $request->impacto ?? 'periodo';

        $tipoImputacion = match ($impacto) {
            'cotizacion' => 'cotizacion',
            'viaje'      => 'viaje',
            'periodo'    => 'periodo',
            default      => 'periodo',
        };

        // 0. Si viene cotizacion_id directamente
        if ($request->filled('cotizacion_id')) {
            $cotizacionId = $request->cotizacion_id;
            $cotizacion = Cotizaciones::find($cotizacionId);
            if ($cotizacion) {
                $vinculos[] = [
                    'tipo_vinculo' => 'cotizacion',
                    'vinculable_type' => Cotizaciones::class,
                    'vinculable_id' => $cotizacion->id,
                    'observaciones' => 'Vinculo a cotización unificado.',
                ];
                
                $contenedor = DocumCotizacion::where('id_cotizacion', $cotizacion->id)->first();
                if ($contenedor) {
                    $vinculos[] = [
                        'tipo_vinculo' => 'contenedor',
                        'vinculable_type' => DocumCotizacion::class,
                        'vinculable_id' => $contenedor->id,
                        'observaciones' => $contenedor->num_contenedor,
                    ];
                    
                    $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();
                    if ($asignacion) {
                        $vinculos[] = [
                            'tipo_vinculo' => 'asignacion',
                            'vinculable_type' => Asignaciones::class,
                            'vinculable_id' => $asignacion->id,
                            'observaciones' => 'Vinculo a viaje/asignación unificado.',
                        ];
                        
                        if ($request->tipo_gasto === 'operador' || $request->tipo_gasto === 'viaje') {
                            if ($asignacion->id_operador) {
                                $vinculos[] = [
                                    'tipo_vinculo' => 'operador',
                                    'vinculable_type' => Operador::class,
                                    'vinculable_id' => $asignacion->id_operador,
                                    'observaciones' => 'Vinculo a operador de la asignación.',
                                ];
                            }
                        }
                    }
                }
                
                $imputaciones[] = [
                    'fecha_imputacion' => $request->fecha_gasto,
                    'tipo_imputacion' =>  $tipoImputacion,
                    'imputable_type' => Cotizaciones::class,
                    'imputable_id' => $cotizacion->id,
                    'monto_imputado' => $montoTotal,
                    'origen' => 'directo',
                ];
            }
        }
        // 1. Si es tipo de gasto "unidad" y tiene unidades seleccionadas (múltiple)
        elseif ($request->tipo_gasto === 'unidad' && $request->filled('unidades')) {
            $unidadesIds = $request->unidades;
            $count = count($unidadesIds);
            $montoProporcional = $count > 0 ? ($montoTotal / $count) : $montoTotal;

            foreach ($unidadesIds as $id) {
                $equipo = Equipo::find($id);
                if ($equipo) {
                    $vinculos[] = [
                        'tipo_vinculo' => 'unidad',
                        'vinculable_type' => Equipo::class,
                        'vinculable_id' => $equipo->id,
                        'observaciones' => 'Vinculo manual a unidad: ' . ($equipo->id_equipo ?: $equipo->placas),
                    ];
                    $imputaciones[] = [
                        'fecha_imputacion' => $request->fecha_gasto,
                        'tipo_imputacion' => $tipoImputacion,
                        'imputable_type' => Equipo::class,
                        'imputable_id' => $equipo->id,
                        'monto_imputado' => $montoProporcional,
                        'origen' => 'directo',
                    ];
                }
            }
        }
        // Compatibilidad anterior con equipo_id único
        elseif ($request->tipo_gasto === 'unidad' && $request->filled('equipo_id')) {
            $equipo = Equipo::find($request->equipo_id);
            if ($equipo) {
                $vinculos[] = [
                    'tipo_vinculo' => 'unidad',
                    'vinculable_type' => Equipo::class,
                    'vinculable_id' => $equipo->id,
                    'observaciones' => 'Vinculo manual a unidad: ' . ($equipo->id_equipo ?: $equipo->placas),
                ];
                $imputaciones[] = [
                    'fecha_imputacion' => $request->fecha_gasto,
                    'tipo_imputacion' =>  $tipoImputacion,
                    'imputable_type' => Equipo::class,
                    'imputable_id' => $equipo->id,
                    'monto_imputado' => $montoTotal,
                    'origen' => 'directo',
                ];
            }
        }
        // 2. Si es tipo de gasto "viaje"/"contenedor" y tiene viajes seleccionados (múltiple)
        elseif (in_array($request->tipo_gasto, ['viaje', 'contenedor', 'cotizacion']) && $request->filled('viajes')) {
            $viajesIds = $request->viajes;
            $count = count($viajesIds);
            $montoProporcional = $count > 0 ? ($montoTotal / $count) : $montoTotal;

            foreach ($viajesIds as $id) {
                $asignacion = Asignaciones::with('Contenedor.Cotizacion')->find($id);
                if ($asignacion) {
                    $vinculos[] = [
                        'tipo_vinculo' => 'asignacion',
                        'vinculable_type' => Asignaciones::class,
                        'vinculable_id' => $asignacion->id,
                        'observaciones' => 'Vinculo manual a viaje.',
                    ];
                    $imputaciones[] = [
                        'fecha_imputacion' => $request->fecha_gasto,
                        'tipo_imputacion' =>  $tipoImputacion,
                        'imputable_type' => Asignaciones::class,
                        'imputable_id' => $asignacion->id,
                        'monto_imputado' => $montoProporcional,
                        'origen' => 'directo',
                    ];

                    if ($asignacion->Contenedor) {
                        $vinculos[] = [
                            'tipo_vinculo' => 'contenedor',
                            'vinculable_type' => DocumCotizacion::class,
                            'vinculable_id' => $asignacion->Contenedor->id,
                            'observaciones' => $asignacion->Contenedor->num_contenedor,
                        ];
                        if ($asignacion->Contenedor->Cotizacion) {
                            $vinculos[] = [
                                'tipo_vinculo' => 'cotizacion',
                                'vinculable_type' => Cotizaciones::class,
                                'vinculable_id' => $asignacion->Contenedor->Cotizacion->id,
                                'observaciones' => 'Vinculo a cotizacion via asignacion.',
                            ];
                        }
                    }
                }
            }
        }
        // Compatibilidad anterior con asignacion_id único
        elseif (in_array($request->tipo_gasto, ['viaje', 'contenedor', 'cotizacion']) && $request->filled('asignacion_id')) {
            $asignacion = Asignaciones::with('Contenedor.Cotizacion')->find($request->asignacion_id);
            if ($asignacion) {
                $vinculos[] = [
                    'tipo_vinculo' => 'asignacion',
                    'vinculable_type' => Asignaciones::class,
                    'vinculable_id' => $asignacion->id,
                    'observaciones' => 'Vinculo manual a viaje.',
                ];
                $imputaciones[] = [
                    'fecha_imputacion' => $request->fecha_gasto,
                    'tipo_imputacion' =>  $tipoImputacion,
                    'imputable_type' => Asignaciones::class,
                    'imputable_id' => $asignacion->id,
                    'monto_imputado' => $montoTotal,
                    'origen' => 'directo',
                ];

                if ($asignacion->Contenedor) {
                    $vinculos[] = [
                        'tipo_vinculo' => 'contenedor',
                        'vinculable_type' => DocumCotizacion::class,
                        'vinculable_id' => $asignacion->Contenedor->id,
                        'observaciones' => $asignacion->Contenedor->num_contenedor,
                    ];
                    if ($asignacion->Contenedor->Cotizacion) {
                        $vinculos[] = [
                            'tipo_vinculo' => 'cotizacion',
                            'vinculable_type' => Cotizaciones::class,
                            'vinculable_id' => $asignacion->Contenedor->Cotizacion->id,
                            'observaciones' => 'Vinculo a cotizacion via asignacion.',
                        ];
                    }
                }
            }
        }
        // 3. Operador (compatibilidad anterior)
        elseif ($request->tipo_gasto === 'operador' && $request->filled('operador_id')) {
            $operador = Operador::find($request->operador_id);
            if ($operador) {
                $vinculos[] = [
                    'tipo_vinculo' => 'operador',
                    'vinculable_type' => Operador::class,
                    'vinculable_id' => $operador->id,
                    'observaciones' => 'Vinculo manual a operador: ' . $operador->nombre,
                ];
                $imputaciones[] = [
                    'fecha_imputacion' => $request->fecha_gasto,
                    'tipo_imputacion' => $tipoImputacion,
                    'imputable_type' => Operador::class,
                    'imputable_id' => $operador->id,
                    'monto_imputado' => $montoTotal,
                    'origen' => 'directo',
                ];
            }
        }

        // 4. Si es "Periodo" y Diferido
        if ($request->tipo_gasto === 'periodo' && $request->metodo_imputacion === 'diferido' && $request->filled('numPeriodos')) {
            $numPeriodos = (int) $request->numPeriodos;
            $montoPeriodo = $montoTotal / $numPeriodos;
            $fechaDesde = Carbon::parse($request->txtDiferirFechaInicia);
            $fechaHasta = Carbon::parse($request->txtDiferirFechaTermina);
            $fechaIniciaPeriodo = $fechaDesde->toDateString();

            for ($periodo = 1; $periodo <= $numPeriodos; $periodo++) {
                $finalMes = Carbon::parse($fechaIniciaPeriodo)->endOfMonth();
                $fechaFinPeriodo = ($finalMes > $fechaHasta) ? $fechaHasta->toDateString() : $finalMes->toDateString();
                $fechaIni = $fechaIniciaPeriodo;

                $programaciones[] = [
                    'numero_periodo' => $periodo,
                    'fecha_programada' => $fechaIni,
                    'fecha_vencimiento' => $fechaFinPeriodo,
                    'monto_programado' => $montoPeriodo,
                    'monto_pagado' => 0.00,
                    'estatus' => 'pendiente',
                ];

                $fechaIniciaPeriodo = $finalMes->addDay()->toDateString();
            }

            $imputaciones[] = [
                'fecha_imputacion' => $request->fecha_gasto,
                'tipo_imputacion' =>  $tipoImputacion,
                'imputable_type' => null,
                'imputable_id' => null,
                'monto_imputado' => $montoTotal,
                'origen' => 'diferido',
            ];
        }

        // Si no se definió imputación (es general, o periodo no diferido)
        if (empty($imputaciones)) {
            $imputaciones[] = [
                'fecha_imputacion' => $request->fecha_gasto,
                'tipo_imputacion' => $request->tipo_gasto === 'periodo' ? 'periodo' : 'empresa',
                'imputable_type' => null,
                'imputable_id' => null,
                'monto_imputado' => $montoTotal,
                'origen' => 'directo',
            ];
        }

        try {
            \DB::beginTransaction();

            // Validar saldo para cobros de contado (tipoPago == 0) y cuenta bancaria seleccionada
            if ($request->tipoPago == 0 && $request->filled('id_banco1')) {
                $validacion = $this->bancosService->validarsaldoparacargo(
                    auth()->user()->id_empresa,
                    $request->id_banco1,
                    $request->fecha_gasto,
                    $montoTotal
                );

                if (!$validacion['saldodisponible']) {
                    \DB::rollBack();
                    return response()->json([
                        'TMensaje' => 'error',
                        'Titulo' => 'Saldo insuficiente',
                        'Mensaje' => $validacion['message'],
                    ]);
                }
            }

            // Preparar datos para registrar
            $storeData = array_merge($validated, [
                'id_empresa' => auth()->user()->id_empresa,
                'origen_modulo' => 'manual',
                'estatus' => 'pendiente_pago',
                'vinculos' => $vinculos,
                'imputaciones' => $imputaciones,
                'programaciones' => $programaciones,
            ]);

            // Registrar el gasto a través de GastosService
            $gasto = $this->gastosService->registrar($storeData);

            // Si es pago de contado y tiene banco, aplicar pago de inmediato (1 a 1 para no perder la referencia)
            if ($request->tipoPago == 0 && $request->filled('id_banco1')) {
                $categoryName = $gasto->categoria?->categoria ?: 'Gasto';

                if ($request->tipo_gasto === 'unidad' && $request->filled('unidades')) {
                    $unidadesIds = $request->unidades;
                    $count = count($unidadesIds);
                    $montoProporcional = $count > 0 ? ($montoTotal / $count) : $montoTotal;

                    foreach ($unidadesIds as $id) {
                        $equipo = Equipo::find($id);
                        if ($equipo) {
                            $ref = 'UNIDAD: ' . ($equipo->id_equipo ?: $equipo->placas);
                            $this->gastosService->pagar($gasto, [
                                'cuenta_bancaria_id' => $request->id_banco1,
                                'fecha_pago' => $request->fecha_gasto,
                                'monto' => $montoProporcional,
                                'metodo_pago' => 'Transferencia',
                                'referencia' => $ref,
                                'referencia_banco' => $ref,
                                'concepto_banco' => BancosService::generarConcepto('gasto', $gasto->concepto, null, 'Unidad: ' . ($equipo->id_equipo ?: $equipo->placas)),
                            ]);
                        }
                    }
                }
                elseif (in_array($request->tipo_gasto, ['viaje', 'contenedor', 'cotizacion']) && $request->filled('viajes')) {
                    $viajesIds = $request->viajes;
                    $count = count($viajesIds);
                    $montoProporcional = $count > 0 ? ($montoTotal / $count) : $montoTotal;

                    foreach ($viajesIds as $id) {
                        $asignacion = Asignaciones::with('Contenedor')->find($id);
                        if ($asignacion) {
                            $numContenedor = $asignacion->Contenedor?->num_contenedor ?: 'S/N';
                            $ref = 'VIAJE: ' . $numContenedor;
                            $this->gastosService->pagar($gasto, [
                                'cuenta_bancaria_id' => $request->id_banco1,
                                'fecha_pago' => $request->fecha_gasto,
                                'monto' => $montoProporcional,
                                'metodo_pago' => 'Transferencia',
                                'referencia' => $ref,
                                'referencia_banco' => $ref,
                                'concepto_banco' => BancosService::generarConcepto('gasto', $gasto->concepto, $numContenedor, null),
                            ]);
                        }
                    }
                }
                else {
                    $this->gastosService->pagar($gasto, [
                        'cuenta_bancaria_id' => $request->id_banco1,
                        'fecha_pago' => $request->fecha_gasto,
                        'monto' => $gasto->monto_total,
                        'metodo_pago' => 'Transferencia',
                        'referencia' => 'Pago automático al registrar',
                        'referencia_banco' => 'GASTO',
                        'concepto_banco' => BancosService::generarConcepto('gasto', $gasto->concepto, null, null),
                    ]);
                }
            }

            \DB::commit();

            return response()->json([
                'TMensaje' => 'success',
                'Titulo' => 'Gasto registrado',
                'Mensaje' => 'El gasto se registró correctamente.',
                'gasto' => $gasto,
            ]);

        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Error al registrar',
                'Mensaje' => 'Ocurrió un error al guardar el gasto: ' . $e->getMessage(),
            ]);
        }
    }

    public function pay(Request $request, Gasto $gasto)
    {
        abort_unless($gasto->id_empresa === auth()->user()->id_empresa, 403);

        $data = $request->validate([
            'cuenta_bancaria_id' => ['required', 'exists:bancos,id'],
            'fecha_pago' => ['required', 'date'],
            'monto' => ['nullable', 'numeric', 'min:0.01'],
            'referencia' => ['nullable', 'string', 'max:100'],
            'comprobante' => ['nullable', 'string', 'max:255'],
        ]);

        $categoryName = $gasto->categoria?->categoria ?: 'Gasto';
        $data['concepto_banco'] = BancosService::generarConcepto('gasto', $gasto->concepto, null, null);
        $data['referencia_banco'] = $data['referencia'] ?? 'PAGO GASTO';

        $pago = $this->gastosService->pagar($gasto, $data);

        return response()->json([
            'TMensaje' => 'success',
            'Titulo' => 'Pago aplicado',
            'Mensaje' => 'El pago se registro correctamente en gastos y bancos.',
            'pago' => $pago,
        ]);
    }

    public function historialPagos(Gasto $gasto)
    {
        return response()->json([
            'TMensaje' => 'success',
            'pagos' => $this->gastosService
                ->obtenerHistorialPagos($gasto),
        ]);
    }

    public function payMultiple(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
            'cuenta_bancaria_id' => ['required', 'exists:bancos,id'],
            'fecha_pago' => ['required', 'date'],
            'referencia' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            \DB::beginTransaction();

            $totalMonto = 0;
            $gastos = Gasto::whereIn('id', $data['ids'])->get();
            foreach ($gastos as $gasto) {
                if ($gasto->estatus !== 'pagado') {
                    $totalMonto += (float) $gasto->saldo_pendiente;
                }
            }

            // Validar saldo total
            $validacion = $this->bancosService->validarsaldoparacargo(
                auth()->user()->id_empresa,
                $data['cuenta_bancaria_id'],
                $data['fecha_pago'],
                $totalMonto
            );

            if (!$validacion['saldodisponible']) {
                \DB::rollBack();
                return response()->json([
                    'TMensaje' => 'error',
                    'Titulo' => 'Saldo insuficiente',
                    'Mensaje' => $validacion['message'],
                ]);
            }

            foreach ($gastos as $gasto) {
                if ($gasto->estatus === 'pagado') {
                    continue;
                }

                $categoryName = $gasto->categoria?->categoria ?: 'Gasto';
                $this->gastosService->pagar($gasto, [
                    'cuenta_bancaria_id' => $data['cuenta_bancaria_id'],
                    'fecha_pago' => $data['fecha_pago'],
                    'monto' => $gasto->saldo_pendiente,
                    'referencia' => $data['referencia'] ?? 'Pago múltiple',
                    'concepto_banco' => BancosService::generarConcepto('gasto', $gasto->concepto, null, null),
                    'referencia_banco' => $data['referencia'] ?? 'PAGO MULTIPLE GASTO',
                ]);
            }

            \DB::commit();

            return response()->json([
                'TMensaje' => 'success',
                'Titulo' => 'Pagos aplicados',
                'Mensaje' => 'Los pagos se aplicaron correctamente en gastos y bancos.',
            ]);

        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Error al pagar',
                'Mensaje' => 'Ocurrió un error al procesar los pagos: ' . $e->getMessage(),
            ]);
        }
    }

    public function cancelarPago(Request $request,GastoPago $pago) {

        $this->gastosService->cancelarPago(
            $pago,
            $request->fecha_cancelacion
                ?? now()->format('Y-m-d')
        );

        return response()->json([
            'TMensaje' => 'success',
            'Titulo' => 'Pago cancelado',
            'Mensaje' => 'El pago fue cancelado correctamente.',
        ]);
    }

    public function update(StoreGastoRequest $request, Gasto $gasto)
    {
        abort_unless($gasto->id_empresa === auth()->user()->id_empresa, 403);

        if ($gasto->estatus === 'cancelado') {
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Gasto cancelado',
                'Mensaje' => 'No es posible editar un gasto que ya ha sido cancelado.',
            ], 422);
        }

        $vinculos = [];
        $imputaciones = [];
        $programaciones = [];
        $montoTotal = (float) $request->monto_total;
        $montoDiferencia = $montoTotal - (float)$gasto->monto_total;

        $impacto = $request->impacto ?? 'periodo';

        $tipoImputacion = match ($impacto) {
            'cotizacion' => 'cotizacion',
            'viaje'      => 'viaje',
            'periodo'    => 'periodo',
            default      => 'periodo',
        };

        // 0. Cotizacion link
        if ($request->filled('cotizacion_id')) {
            $cotizacionId = $request->cotizacion_id;
            $cotizacion = Cotizaciones::find($cotizacionId);
            if ($cotizacion) {
                $vinculos[] = [
                    'tipo_vinculo' => 'cotizacion',
                    'vinculable_type' => Cotizaciones::class,
                    'vinculable_id' => $cotizacion->id,
                    'observaciones' => 'Vinculo a cotización unificado.',
                ];
                
                $contenedor = DocumCotizacion::where('id_cotizacion', $cotizacion->id)->first();
                if ($contenedor) {
                    $vinculos[] = [
                        'tipo_vinculo' => 'contenedor',
                        'vinculable_type' => DocumCotizacion::class,
                        'vinculable_id' => $contenedor->id,
                        'observaciones' => $contenedor->num_contenedor,
                    ];
                    
                    $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();
                    if ($asignacion) {
                        $vinculos[] = [
                            'tipo_vinculo' => 'asignacion',
                            'vinculable_type' => Asignaciones::class,
                            'vinculable_id' => $asignacion->id,
                        ];
                    }
                }
            }
        }
        // 1. Unidades links
        elseif ($request->tipo_gasto === 'unidad' && $request->filled('unidades')) {
            $unidadesIds = $request->unidades;
            $count = count($unidadesIds);
            $montoProporcional = $count > 0 ? ($montoTotal / $count) : $montoTotal;

            foreach ($unidadesIds as $id) {
                $equipo = Equipo::find($id);
                if ($equipo) {
                    $vinculos[] = [
                        'tipo_vinculo' => 'unidad',
                        'vinculable_type' => Equipo::class,
                        'vinculable_id' => $equipo->id,
                        'observaciones' => 'Vinculo manual a unidad: ' . ($equipo->id_equipo ?: $equipo->placas),
                    ];
                    $imputaciones[] = [
                        'fecha_imputacion' => $request->fecha_gasto,
                        'tipo_imputacion' => $tipoImputacion,
                        'imputable_type' => Equipo::class,
                        'imputable_id' => $equipo->id,
                        'monto_imputado' => $montoProporcional,
                        'origen' => 'directo',
                    ];
                }
            }
        }
        // 2. Viajes links
        elseif (in_array($request->tipo_gasto, ['viaje', 'contenedor', 'cotizacion']) && $request->filled('viajes')) {
            $viajesIds = $request->viajes;
            $count = count($viajesIds);
            $montoProporcional = $count > 0 ? ($montoTotal / $count) : $montoTotal;

            foreach ($viajesIds as $id) {
                $asignacion = Asignaciones::with('Contenedor.Cotizacion')->find($id);
                if ($asignacion) {
                    $vinculos[] = [
                        'tipo_vinculo' => 'asignacion',
                        'vinculable_type' => Asignaciones::class,
                        'vinculable_id' => $asignacion->id,
                        'observaciones' => 'Vinculo manual a viaje.',
                    ];
                    $imputaciones[] = [
                        'fecha_imputacion' => $request->fecha_gasto,
                        'tipo_imputacion' => $tipoImputacion,
                        'imputable_type' => Asignaciones::class,
                        'imputable_id' => $asignacion->id,
                        'monto_imputado' => $montoProporcional,
                        'origen' => 'directo',
                    ];

                    if ($asignacion->Contenedor) {
                        $vinculos[] = [
                            'tipo_vinculo' => 'contenedor',
                            'vinculable_type' => DocumCotizacion::class,
                            'vinculable_id' => $asignacion->Contenedor->id,
                            'observaciones' => $asignacion->Contenedor->num_contenedor,
                        ];
                        if ($asignacion->Contenedor->Cotizacion) {
                            $vinculos[] = [
                                'tipo_vinculo' => 'cotizacion',
                                'vinculable_type' => Cotizaciones::class,
                                'vinculable_id' => $asignacion->Contenedor->Cotizacion->id,
                                'observaciones' => 'Vinculo a cotizacion via asignacion.',
                            ];
                        }
                    }
                }
            }
        }

        // 4. Periodo / Diferido
        if ($request->tipo_gasto === 'periodo' && $request->metodo_imputacion === 'diferido' && $request->filled('numPeriodos')) {
            $numPeriodos = (int) $request->numPeriodos;
            $montoPeriodo = $montoTotal / $numPeriodos;
            $fechaDesde = Carbon::parse($request->txtDiferirFechaInicia);
            $fechaHasta = Carbon::parse($request->txtDiferirFechaTermina);
            $fechaIniciaPeriodo = $fechaDesde->toDateString();

            for ($periodo = 1; $periodo <= $numPeriodos; $periodo++) {
                $finalMes = Carbon::parse($fechaIniciaPeriodo)->endOfMonth();
                $fechaFinPeriodo = ($finalMes > $fechaHasta) ? $fechaHasta->toDateString() : $finalMes->toDateString();
                $fechaIni = $fechaIniciaPeriodo;

                $programaciones[] = [
                    'numero_periodo' => $periodo,
                    'fecha_programada' => $fechaIni,
                    'fecha_vencimiento' => $fechaFinPeriodo,
                    'monto_programado' => $montoPeriodo,
                    'monto_pagado' => 0.00,
                    'estatus' => 'pendiente',
                ];

                $fechaIniciaPeriodo = $finalMes->addDay()->toDateString();
            }

            $imputaciones[] = [
                'fecha_imputacion' => $request->fecha_gasto,
                'tipo_imputacion' => $tipoImputacion,
                'imputable_type' => null,
                'imputable_id' => null,
                'monto_imputado' => $montoTotal,
                'origen' => 'diferido',
            ];
        }

        if (empty($imputaciones)) {
            $imputaciones[] = [
                'fecha_imputacion' => $request->fecha_gasto,
                'tipo_imputacion' => $request->tipo_gasto === 'periodo' ? 'periodo' : 'empresa',
                'imputable_type' => null,
                'imputable_id' => null,
                'monto_imputado' => $montoTotal,
                'origen' => 'directo',
            ];
        }

        try {
            \DB::beginTransaction();

            $pagoExistente = $gasto->pagos()->where('estatus', 'aplicado')->first();

            // If the amount changes and there is an existing payment, adjust or validate balance
            if ($pagoExistente && $montoDiferencia != 0) {
                if ($montoDiferencia > 0) {
                    // Validate if bank has enough funds to cover the difference
                    $validacion = $this->bancosService->validarsaldoparacargo(
                        auth()->user()->id_empresa,
                        $pagoExistente->cuenta_bancaria_id,
                        $request->fecha_gasto,
                        $montoDiferencia
                    );

                    if (!$validacion['saldodisponible']) {
                        \DB::rollBack();
                        return response()->json([
                            'TMensaje' => 'error',
                            'Titulo' => 'Saldo insuficiente en banco',
                            'Mensaje' => $validacion['message'],
                        ]);
                    }
                }

                // Update the payment amount
                $nuevoMontoPago = (float)$pagoExistente->monto + $montoDiferencia;
                $pagoExistente->update([
                    'monto' => $nuevoMontoPago,
                    'fecha_pago' => $request->fecha_gasto
                ]);

                // Update corresponding bank movement
                if ($pagoExistente->movimiento_bancario_id) {
                    $movimiento = \App\Models\CatBancoCuentasMovimientos::find($pagoExistente->movimiento_bancario_id);
                    if ($movimiento) {
                        $movimiento->update([
                            'monto' => $nuevoMontoPago,
                            'fecha_movimiento' => $request->fecha_gasto,
                            'concepto' => 'Pago gasto (Editado): ' . $request->concepto
                        ]);
                    }
                }
            }

            // Save updated gasto details
            $storeData = array_merge($request->validated(), [
                'id' => $gasto->id,
                'id_empresa' => auth()->user()->id_empresa,
                'vinculos' => $vinculos,
                'imputaciones' => $imputaciones,
                'programaciones' => $programaciones,
            ]);

            $gastoUpdated = $this->gastosService->registrar($storeData);

            \DB::commit();

            return response()->json([
                'TMensaje' => 'success',
                'Titulo' => 'Gasto actualizado',
                'Mensaje' => 'El gasto y sus imputaciones se actualizaron correctamente.',
                'gasto' => $gastoUpdated,
            ]);

        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Error al actualizar',
                'Mensaje' => 'Ocurrió un error al actualizar el gasto: ' . $e->getMessage(),
            ]);
        }
    }

    public function getConceptosByCategoria($categoriaId)
    {
        $conceptos = \App\Models\GastoConcepto::where('categoria_gasto_id', $categoriaId)
            ->where('is_active', 1)
            ->orderBy('nombre')
            ->get();
            
        return response()->json($conceptos);
    }

    public function destroy(Request $request, Gasto $gasto)
    {
        try {
            \DB::beginTransaction();

            $fechaCancelacion = $request->fecha_cancelacion ?? now()->format('Y-m-d');

            // 1. Cancelar todos los pagos del gasto (y sus movimientos bancarios asociados)
            foreach ($gasto->pagos()->where('estatus', '!=', 'cancelado')->get() as $pago) {
                $this->gastosService->cancelarPago($pago, $fechaCancelacion);
            }

            // 2. Si tiene origen legacy, sincronizar la eliminación
            if ($gasto->origen_legacy && $gasto->origen_legacy_id) {
                $legacyId = $gasto->origen_legacy_id;
                
                if ($gasto->origen_legacy === 'gastos_operadores') {
                    \DB::table('gastos_operadores')
                        ->where('id', $legacyId)
                        ->update(['estatus' => 'eliminado']);
                } elseif ($gasto->origen_legacy === 'gastos_extras') {
                    \DB::table('gastos_extras')
                        ->where('id', $legacyId)
                        ->update(['estatus' => 'eliminado']);

                    // Ajustar el restante de la cotización si es un gasto extra
                    $gExtra = \DB::table('gastos_extras')->where('id', $legacyId)->first();
                    if ($gExtra && !empty($gExtra->id_cotizacion)) {
                        $monto = floatval($gExtra->monto ?? 0);
                        if ($monto > 0) {
                            \DB::table('cotizaciones')
                                ->where('id', $gExtra->id_cotizacion)
                                ->update([
                                    'restante' => \DB::raw("restante - {$monto}")
                                ]);
                        }
                    }
                } elseif ($gasto->origen_legacy === 'gastos_generales') {
                    \DB::table('gastos_generales')
                        ->where('id', $legacyId)
                        ->delete();

                    \DB::table('gastos_operadores')
                        ->where('id_gasto_origen', $legacyId)
                        ->update(['estatus' => 'eliminado']);
                }
            }

            // 3. Cambiar estatus a cancelado y soft delete del Gasto
            $gasto->update(['estatus' => 'cancelado']);
            $gasto->delete();

            \DB::commit();

            return response()->json([
                'TMensaje' => 'success',
                'Titulo' => 'Gasto eliminado',
                'Mensaje' => 'El gasto y sus pagos asociados fueron eliminados correctamente.',
            ]);

        } catch (\Throwable $t) {
            \DB::rollBack();
            \Log::channel('daily')->error('Error al eliminar gasto desde modulo unificado', [
                'gasto_id' => $gasto->id,
                'error' => $t->getMessage(),
            ]);

            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Error al eliminar',
                'Mensaje' => 'Ocurrió un error al intentar eliminar el gasto: ' . $t->getMessage(),
            ]);
        }
    }
}
