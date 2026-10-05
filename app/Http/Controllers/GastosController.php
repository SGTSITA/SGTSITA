<?php

namespace App\Http\Controllers;

use App\Models\Bancos;
use App\Models\CatBancoCuentasMovimientos;

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
        $idEmpresa = auth()->user()->id_empresa;
        $empresa = \App\Models\Empresas::withoutGlobalScopes()->find($idEmpresa);

        $catalogos = $this->gastosService->getCatalogosIndex($idEmpresa);
        $catalogos['empresaActual'] = $empresa;
        $catalogos['requiereUnidadGasto'] = (bool) ($empresa?->requiere_unidad_gasto ?? false);

        return view('gastos.index', $catalogos);
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
                'categoria_id' => $request->categoria_id,
                'subcategoria_id' => $request->subcategoria_id,
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
        // Compatibilidad con id_equipo / equipo_id único o general con unidad
        elseif (($request->tipo_gasto === 'unidad' || $request->filled('id_equipo') || $request->filled('equipo_id')) && ($request->filled('id_equipo') || $request->filled('equipo_id'))) {
            $equipoId = $request->id_equipo ?: $request->equipo_id;
            $equipo = Equipo::find($equipoId);
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

            $selectedEquipoId = $request->id_equipo ?: ($request->equipo_id ?: (is_array($request->unidades) && count($request->unidades) === 1 ? $request->unidades[0] : null));

            // Preparar datos para registrar
            $storeData = array_merge($validated, [
                'id_empresa' => auth()->user()->id_empresa,
                'id_equipo' => $selectedEquipoId,
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
                                'concepto_banco' => BancosService::generarConcepto('gasto', $gasto->concepto, null, null, ($equipo->id_equipo ?: $equipo->placas)),
                            ]);
                        }
                    }
                }
                elseif (in_array($request->tipo_gasto, ['viaje', 'contenedor', 'cotizacion']) && $request->filled('viajes')) {
                    $viajesIds = $request->viajes;
                    $count = count($viajesIds);
                    $montoProporcional = $count > 0 ? ($montoTotal / $count) : $montoTotal;

                    foreach ($viajesIds as $id) {
                        $asignacion = Asignaciones::with(['Contenedor', 'Camion'])->find($id);
                        if ($asignacion) {
                            $numContenedor = $asignacion->Contenedor?->num_contenedor ?: 'S/N';
                            $equipoNombre = $asignacion->Camion ? ($asignacion->Camion->id_equipo ?: $asignacion->Camion->placas) : null;
                            $ref = 'VIAJE: ' . $numContenedor;
                            $this->gastosService->pagar($gasto, [
                                'cuenta_bancaria_id' => $request->id_banco1,
                                'fecha_pago' => $request->fecha_gasto,
                                'monto' => $montoProporcional,
                                'metodo_pago' => 'Transferencia',
                                'referencia' => $ref,
                                'referencia_banco' => $ref,
                                'concepto_banco' => BancosService::generarConcepto('gasto', $gasto->concepto, $numContenedor, null, $equipoNombre),
                            ]);
                        }
                    }
                }
                else {
                    $equipoNombre = null;
                    if ($gasto->id_equipo) {
                        $eq = $gasto->equipo ?: Equipo::find($gasto->id_equipo);
                        $equipoNombre = $eq ? ($eq->id_equipo ?: $eq->placas) : null;
                    }

                    $this->gastosService->pagar($gasto, [
                        'cuenta_bancaria_id' => $request->id_banco1,
                        'fecha_pago' => $request->fecha_gasto,
                        'monto' => $gasto->monto_total,
                        'metodo_pago' => 'Transferencia',
                        'referencia' => 'Pago automático al registrar',
                        'referencia_banco' => 'GASTO',
                        'concepto_banco' => BancosService::generarConcepto('gasto', $gasto->concepto, null, null, $equipoNombre),
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

        $gasto->loadMissing(['equipo', 'vinculos']);
        $equipoNombre = null;
        if ($gasto->equipo) {
            $equipoNombre = $gasto->equipo->id_equipo ?: $gasto->equipo->placas;
        } elseif ($gasto->id_equipo) {
            $eq = Equipo::find($gasto->id_equipo);
            $equipoNombre = $eq ? ($eq->id_equipo ?: $eq->placas) : null;
        } else {
            $vUnidad = $gasto->vinculos->firstWhere('tipo_vinculo', 'unidad');
            if ($vUnidad) {
                $eq = Equipo::find($vUnidad->vinculable_id);
                $equipoNombre = $eq ? ($eq->id_equipo ?: $eq->placas) : null;
            }
        }

        $categoryName = $gasto->categoria?->categoria ?: 'Gasto';
        $data['concepto_banco'] = BancosService::generarConcepto('gasto', $gasto->concepto, null, null, $equipoNombre);
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

            // Registrar movimiento bancario UNICO
            $conceptos = [];
            $detalles = [];
            $unidadesMap = [];

            foreach ($gastos as $gasto) {
                if ($gasto->estatus !== 'pagado') {
                    $conceptos[] = $gasto->concepto;

                    $gasto->loadMissing(['vinculos', 'equipo']);
                    $vinculosInfo = [];
                    foreach ($gasto->vinculos as $v) {
                        $vinculosInfo[] = [
                            'tipo' => $v->tipo_vinculo,
                            'referencia' => $v->observaciones ?: $v->vinculable_id
                        ];
                    }

                    $equipoId = $gasto->id_equipo;
                    $equipoNombre = null;
                    if ($gasto->equipo) {
                        $equipoNombre = $gasto->equipo->id_equipo ?: $gasto->equipo->placas;
                    } elseif ($gasto->id_equipo) {
                        $eq = Equipo::find($gasto->id_equipo);
                        $equipoNombre = $eq ? ($eq->id_equipo ?: $eq->placas) : null;
                    } else {
                        $vUnidad = $gasto->vinculos->firstWhere('tipo_vinculo', 'unidad');
                        if ($vUnidad) {
                            $equipoId = $vUnidad->vinculable_id;
                            $eq = Equipo::find($equipoId);
                            $equipoNombre = $eq ? ($eq->id_equipo ?: $eq->placas) : null;
                        }
                    }
                    if ($equipoId) {
                        $unidadesMap[$equipoId] = $equipoNombre;
                    }

                    $detalles[] = [
                        'gasto_id' => $gasto->id,
                        'concepto' => $gasto->concepto,
                        'monto' => $gasto->saldo_pendiente,
                        'tipo_gasto' => $gasto->tipo_gasto,
                        'id_equipo' => $equipoId,
                        'unidad' => $equipoNombre,
                        'vinculos' => $vinculosInfo
                    ];
                }
            }

            $conceptosStr = implode(', ', $conceptos);
            if (strlen($conceptosStr) > 200) {
                $conceptosStr = substr($conceptosStr, 0, 197) . '...';
            }

            $conceptoMovimiento = '[PAGO MULTIPLE] Pago de gastos';
            if (count($unidadesMap) === 1) {
                $conceptoMovimiento .= ' - Unidad: ' . reset($unidadesMap);
            }

            $movimiento = $this->bancosService->registrarMovimiento([
                'cuenta_bancaria_id' => $data['cuenta_bancaria_id'],
                'tipo' => 'cargo',
                'monto' => $totalMonto,
                'concepto' => $conceptoMovimiento,
                'fecha_movimiento' => $data['fecha_pago'],
                'referencia' => $data['referencia'] ?? 'PAGO MULTIPLE',
                'detalles' => $detalles,
                'referenciaable_id' => null, // Multiple gastos
                'referenciaable_type' => \App\Models\Gasto::class,
            ]);

            foreach ($gastos as $gasto) {
                if ($gasto->estatus === 'pagado') {
                    continue;
                }

                $this->gastosService->pagarConMovimientoExistente($gasto, [
                    'cuenta_bancaria_id' => $data['cuenta_bancaria_id'],
                    'fecha_pago' => $data['fecha_pago'],
                    'monto' => $gasto->saldo_pendiente,
                    'referencia' => $data['referencia'] ?? 'Pago múltiple',
                    'movimiento_bancario_id' => $movimiento->id,
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

            // If there is an existing payment
            if ($pagoExistente) {
                $nuevoMontoPago = (float)$pagoExistente->monto + $montoDiferencia;
                $dateChanged = $request->fecha_gasto !== $gasto->fecha_gasto;

                // Validate balance if amount increased
                if ($montoDiferencia > 0) {
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

                // Update the payment amount and date
                $pagoExistente->update([
                    'monto' => $nuevoMontoPago,
                    'fecha_pago' => $request->fecha_gasto
                ]);

                // Update corresponding bank movement
                $movimiento = null;
                if ($pagoExistente->movimiento_bancario_id) {
                    $movimiento = \App\Models\CatBancoCuentasMovimientos::find($pagoExistente->movimiento_bancario_id);
                } elseif ($gasto->origen_legacy && $gasto->origen_legacy_id) {
                    // Try to find legacy bank movement polymorphically
                    $legacyTypes = [
                        'gastos_generales' => \App\Models\GastosGenerales::class,
                        'gastos_extras' => \App\Models\GastosExtras::class,
                        'gastos_operadores' => \App\Models\GastosOperadores::class,
                    ];
                    if (isset($legacyTypes[$gasto->origen_legacy])) {
                        $movimiento = \App\Models\CatBancoCuentasMovimientos::where('referenciaable_id', $gasto->origen_legacy_id)
                            ->where('referenciaable_type', $legacyTypes[$gasto->origen_legacy])
                            ->first();
                    }
                }

                if ($movimiento) {
                    $nuevoMontoMovimiento = (float)$movimiento->monto + $montoDiferencia;

                    $updateData = [
                        'monto' => $nuevoMontoMovimiento,
                        'fecha_movimiento' => $request->fecha_gasto,
                    ];

                    if (strpos($movimiento->concepto ?? '', '[PAGO MULTIPLE]') === false) {
                        $updateData['concepto'] = 'Pago gasto (Editado): ' . $request->concepto;
                    }

                    $movimiento->update($updateData);
                }
            }

            // Sync legacy tables if applicable
            if ($gasto->origen_legacy && $gasto->origen_legacy_id) {
                $legacyId = $gasto->origen_legacy_id;
                if ($gasto->origen_legacy === 'gastos_generales') {
                    \DB::table('gastos_generales')
                        ->where('id', $legacyId)
                        ->update([
                            'motivo' => $request->concepto,
                            'monto1' => $montoTotal,
                            'fecha' => $request->fecha_gasto,
                            'fecha_operacion' => $request->fecha_gasto,
                        ]);
                } elseif ($gasto->origen_legacy === 'gastos_extras') {
                    \DB::table('gastos_extras')
                        ->where('id', $legacyId)
                        ->update([
                            'descripcion' => $request->concepto,
                            'monto' => $montoTotal,
                            'fecha_aplicacion' => $request->fecha_gasto,
                        ]);
                } elseif ($gasto->origen_legacy === 'gastos_operadores') {
                    \DB::table('gastos_operadores')
                        ->where('id', $legacyId)
                        ->update([
                            'cantidad' => $montoTotal,
                            'fecha_pago' => $request->fecha_gasto,
                        ]);
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
        if (in_array($gasto->origen_legacy, ['viaticos_operadores', 'viaticos_operadores_excedente', 'gastos_operadores'])) {
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Acción rechazada',
                'Mensaje' => 'Este gasto proviene de justificaciones de viáticos o excedentes del viaje del operador y no puede ser eliminado desde este módulo, ya que su flujo de pago se gestiona a través de la liquidación del viaje.'
            ]);
        }

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
                } elseif (str_starts_with($gasto->origen_legacy, 'asignacion_planeacion')) {
                    if (str_contains($gasto->concepto, 'Diesel')) {
                        \DB::table('gastos_operadores')
                            ->where('id_asignacion', $legacyId)
                            ->where('tipo', 'Diesel')
                            ->update(['estatus' => 'eliminado']);
                    }
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

    /**
     * Retorna las cuentas bancarias activas con saldo disponible para la empresa.
     */
    public function getCuentasBancarias(Request $request)
    {
        $fecha = $request->input('fecha', now()->format('Y-m-d'));
        $validarSaldo = $request->boolean('validar_saldo', false);
        $empresaId = auth()->user()->id_empresa;

        $cuentas = $this->bancosService->getCuentasOption(
            $empresaId,
            $fecha,
            $fecha,
            $validarSaldo
        );

        return response()->json([
            'TMensaje' => 'success',
            'cuentas' => $cuentas,
        ]);
    }

    /**
     * Registra un movimiento bancario (abono o cargo) in-situ desde el módulo de gastos.
     */
    public function storeMovimientoBancario(Request $request)
    {
        $validated = $request->validate([
            'cuenta_bancaria_id' => 'required|exists:bancos,id',
            'tipo'               => 'required|in:abono,cargo',
            'concepto'           => 'required|string|max:255',
            'monto'              => 'required|numeric|not_in:0|min:0.01',
            'fecha_movimiento'   => 'required|date',
            'referencia'         => 'nullable|string|max:100',
            'origen'             => 'required|string|max:50',
        ]);

        $cuenta = Bancos::where('id_empresa', auth()->user()->id_empresa)
            ->findOrFail($validated['cuenta_bancaria_id']);

        try {
            \DB::beginTransaction();

            $movimiento = $cuenta->movimientos()->create([
                'tipo'             => $validated['tipo'],
                'concepto'         => $validated['concepto'],
                'monto'            => $validated['monto'],
                'fecha_movimiento' => $validated['fecha_movimiento'],
                'referencia'       => $validated['referencia'] ?? null,
                'origen'           => $validated['origen'] ?? 'manual',
                'user_id'          => auth()->id(),
            ]);

            \DB::commit();

            return response()->json([
                'TMensaje' => 'success',
                'Titulo' => 'Movimiento registrado',
                'Mensaje' => 'El movimiento bancario fue registrado correctamente.',
                'movimiento' => $movimiento,
                'cuenta_id' => $cuenta->id,
            ]);
        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Error al registrar',
                'Mensaje' => 'Ocurrió un error al registrar el movimiento bancario: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Registra una transferencia entre dos cuentas bancarias de la empresa in-situ.
     */
    public function storeTransferenciaBancaria(Request $request)
    {
        $validated = $request->validate([
            'cuenta_origen'    => 'required|different:cuenta_destino|exists:bancos,id',
            'cuenta_destino'   => 'required|exists:bancos,id',
            'concepto'         => 'required|string|max:255',
            'monto'            => 'required|numeric|min:0.01',
            'fecha_aplicacion' => 'required|date',
        ]);

        $empresaId = auth()->user()->id_empresa;

        // Verificar pertenencia a la empresa
        $cuentaOrigen = Bancos::where('id_empresa', $empresaId)->find($validated['cuenta_origen']);
        $cuentaDestino = Bancos::where('id_empresa', $empresaId)->find($validated['cuenta_destino']);

        if (!$cuentaOrigen || !$cuentaDestino) {
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Cuentas no válidas',
                'Mensaje' => 'Una o ambas cuentas bancarias no pertenecen a su empresa.',
            ], 422);
        }

        // Validar saldo suficiente en la cuenta de origen
        $validarSaldo = $this->bancosService->validarsaldoparacargo(
            $empresaId,
            $validated['cuenta_origen'],
            $validated['fecha_aplicacion'],
            $validated['monto']
        );

        if (!$validarSaldo['saldodisponible']) {
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Saldo insuficiente',
                'Mensaje' => $validarSaldo['message'],
            ], 422);
        }

        try {
            \DB::beginTransaction();

            // Cargo a cuenta origen
            CatBancoCuentasMovimientos::create([
                'cuenta_bancaria_id' => $validated['cuenta_origen'],
                'tipo'               => 'cargo',
                'monto'              => $validated['monto'],
                'concepto'           => $validated['concepto'],
                'fecha_movimiento'   => $validated['fecha_aplicacion'],
                'origen'             => 'transferencia',
                'referencia'         => 'TR',
                'user_id'            => auth()->id(),
            ]);

            // Abono a cuenta destino
            CatBancoCuentasMovimientos::create([
                'cuenta_bancaria_id' => $validated['cuenta_destino'],
                'tipo'               => 'abono',
                'monto'              => $validated['monto'],
                'concepto'           => $validated['concepto'],
                'fecha_movimiento'   => $validated['fecha_aplicacion'],
                'origen'             => 'transferencia',
                'referencia'         => 'TR',
                'user_id'            => auth()->id(),
            ]);

            \DB::commit();

            return response()->json([
                'TMensaje' => 'success',
                'Titulo' => 'Transferencia realizada',
                'Mensaje' => 'La transferencia entre cuentas se aplicó correctamente.',
                'cuenta_origen_id' => $cuentaOrigen->id,
                'cuenta_destino_id' => $cuentaDestino->id,
            ]);
        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Error al transferir',
                'Mensaje' => 'Ocurrió un error al procesar la transferencia: ' . $e->getMessage(),
            ], 500);
        }
    }
}
