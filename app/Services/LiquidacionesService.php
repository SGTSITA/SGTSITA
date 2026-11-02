<?php

namespace App\Services;

use App\Models\Asignaciones;
use App\Models\BancoDineroOpe;
use App\Models\Bancos;
use App\Models\CatBancoCuentasMovimientos;
use App\Models\Cotizaciones;
use App\Models\DineroContenedor;
use App\Models\DocumCotizacion;
use App\Models\Gasto;
use App\Models\GastoPago;
use App\Models\Liquidaciones;
use App\Models\LiquidacionAjuste;
use App\Models\LiquidacionAjusteDetalle;
use App\Models\LiquidacionContenedor;
use App\Models\Operador;
use App\Models\PagoPrestamo;
use App\Models\Prestamo;
use App\Models\ViaticosOperador;
use App\Exceptions\SaldoInsuficienteException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LiquidacionesService
{
    public function __construct(
        protected BancosService $bancosService,
        protected GastosService $gastosService,
        protected EquipoService $equipoService
    ) {
    }

    /**
     * Obtiene el resumen de pagos pendientes por operador (modelo simple).
     */
    public function getPagosOperadores(int $idEmpresa): array
    {
        $asignacionOperador = Asignaciones::join('operadores', 'asignaciones.id_operador', '=', 'operadores.id')
            ->where('asignaciones.id_empresa', $idEmpresa)
            ->where('asignaciones.tipo_contrato', 'Propio')
            ->where('asignaciones.estatus_pagado', 'Pendiente Pago')
            ->select(
                'asignaciones.id_operador',
                DB::raw('COUNT(*) as total_cotizaciones'),
                DB::raw('SUM(sueldo_viaje) as sueldo_viaje'),
                DB::raw('SUM(dinero_viaje) as dinero_viaje'),
                DB::raw('SUM(restante_pago_operador) as total_pago')
            )
            ->groupBy('asignaciones.id_operador')
            ->get();

        return $asignacionOperador->map(function ($ope) {
            return [
                "IdOperador" => $ope->id_operador,
                "Operador" => (!is_null($ope->operador)) ? $ope->operador->nombre : 'N/A',
                "SueldoViaje" => $ope->sueldo_viaje,
                "DineroViaje" => $ope->dinero_viaje,
                "MontoPago" => $ope->total_pago,
                "ViajesRealizados" => $ope->total_cotizaciones
            ];
        })->toArray();
    }

    /**
     * Obtiene los pagos de operadores calculando saldos reales considerando dinero entregado y justificado.
     */
    public function getPagosOperadoresSaldo(int $idEmpresa): array
    {
        $subLiquidacion = DB::table('liquidacion_contenedor')
            ->select(
                'id_contenedor',
                DB::raw('SUM(total_pagado) as pagado'),
                DB::raw('SUM(dinero_justificado) as justificado')
            )
            ->groupBy('id_contenedor');

        $subDinero = DB::table('dinero_contenedor')
            ->select(
                'id_contenedor',
                DB::raw('SUM(monto) as total_dinero')
            )
            ->groupBy('id_contenedor');

        $asignaciones = DB::table('asignaciones as a')
            ->join('operadores as o', 'a.id_operador', '=', 'o.id')
            ->leftJoinSub($subLiquidacion, 'lc', fn ($j) => $j->on('lc.id_contenedor', '=', 'a.id_contenedor'))
            ->leftJoinSub($subDinero, 'dc2', fn ($j) => $j->on('dc2.id_contenedor', '=', 'a.id_contenedor'))
            ->where('a.id_empresa', $idEmpresa)
            ->where('a.tipo_contrato', 'Propio')
            ->where('a.estatus_pagado', 'Pendiente Pago')
            ->whereRaw('
                (
                    (
                        a.sueldo_viaje
                        - IFNULL(dc2.total_dinero, 0)
                        + IFNULL(lc.justificado, 0)
                    )
                    - IFNULL(lc.pagado, 0)
                ) != 0
            ')
            ->select(
                'a.id_operador',
                'o.nombre',
                DB::raw('SUM(a.restante_pago_operador) as restante_pago_operador'),
                DB::raw('SUM(IFNULL(dc2.total_dinero,0)) as total_dinero'),
                DB::raw('COUNT(*) as total_cotizaciones'),
                DB::raw('SUM(a.sueldo_viaje) as sueldo_viaje'),
                DB::raw('SUM(
                    (
                        a.sueldo_viaje
                        - IFNULL(dc2.total_dinero,0)
                        + IFNULL(lc.justificado,0)
                    )
                    - IFNULL(lc.pagado,0)
                ) as total_pago_real')
            )
            ->groupBy('a.id_operador', 'o.nombre')
            ->havingRaw('
                SUM(
                    (
                        a.sueldo_viaje
                        - IFNULL(dc2.total_dinero,0)
                        + IFNULL(lc.justificado,0)
                    )
                    - IFNULL(lc.pagado,0)
                ) != 0
            ')
            ->get();

        return $asignaciones->map(function ($ope) {
            return [
                "IdOperador" => $ope->id_operador,
                "Operador" => $ope->nombre ?? 'N/A',
                "SueldoViaje" => $ope->sueldo_viaje,
                "DineroViaje" => $ope->total_dinero,
                "MontoPago" => $ope->total_pago_real,
                "ViajesRealizados" => $ope->total_cotizaciones
            ];
        })->toArray();
    }

    /**
     * Obtiene los catálogos para la pantalla de liquidación individual (show).
     */
    public function getDatosShow(int $idOperador, int $idEmpresa): ?array
    {
        $fecha = Carbon::now()->format('Y-m-d');
        $operador = Operador::where('id', $idOperador)
            ->where('id_empresa', $idEmpresa)
            ->first();

        if (!$operador) {
            return null;
        }

        $bancos = $this->bancosService->getCuentasOption($idEmpresa, $fecha, $fecha, true);

        return [
            'operador' => $operador,
            'id' => $idOperador,
            'bancos' => $bancos,
        ];
    }

    /**
     * Obtiene los viajes pendientes de liquidar y préstamos activos de un operador.
     */
    public function getViajesOperador(int $idOperador, int $idEmpresa): array
    {
        $subLiquidacion = DB::table('liquidacion_contenedor')
            ->select(
                'id_contenedor',
                DB::raw('SUM(total_pagado) as pagado'),
                DB::raw('SUM(dinero_justificado) as justificado')
            )
            ->groupBy('id_contenedor');

        $subDinero = DB::table('dinero_contenedor')
            ->select(
                'id_contenedor',
                DB::raw('SUM(monto) as total_dinero')
            )
            ->groupBy('id_contenedor');

        $subJustificados = DB::table('viaticos_operadores as vo')
            ->join('docum_cotizacion as dc', 'vo.id_cotizacion', '=', 'dc.id_cotizacion')
            ->select(
                'dc.id as id_contenedor',
                DB::raw('SUM(vo.monto) as total_justificado')
            )
            ->groupBy('dc.id');

        $asignaciones = DB::table('asignaciones as a')
            ->leftJoinSub($subLiquidacion, 'lc', fn ($j) => $j->on('lc.id_contenedor', '=', 'a.id_contenedor'))
            ->leftJoinSub($subDinero, 'dc', fn ($j) => $j->on('dc.id_contenedor', '=', 'a.id_contenedor'))
            ->leftJoinSub($subJustificados, 'j', fn ($j) => $j->on('j.id_contenedor', '=', 'a.id_contenedor'))
            ->where('a.id_empresa', $idEmpresa)
            ->whereNull('a.id_proveedor')
            ->where('a.id_operador', $idOperador)
            ->where('a.estatus_pagado', 'Pendiente Pago')
            ->select(
                'a.id',
                'a.id_operador',
                'a.id_contenedor',
                'a.sueldo_viaje',
                'a.fecha_inicio',
                'a.fecha_fin',
                DB::raw('IFNULL(dc.total_dinero,0) as dinero_viaje'),
                DB::raw('IFNULL(j.total_justificado,0) as gastos_justificados'),
                DB::raw('IFNULL(lc.justificado,0) as justificado_pagado'),
                DB::raw('IFNULL(lc.pagado,0) as pagado'),
                DB::raw('
                    (
                        a.sueldo_viaje
                        - IFNULL(dc.total_dinero,0)
                        + IFNULL(lc.justificado,0)
                    ) - IFNULL(lc.pagado,0)
                as saldo_real')
            )
            ->having('saldo_real', '!=', 0)
            ->get();

        $justificaciones = DB::table('viaticos_operadores as vo')
            ->join('docum_cotizacion as dc', 'vo.id_cotizacion', '=', 'dc.id_cotizacion')
            ->select(
                'dc.id as id_contenedor',
                'vo.id',
                'vo.descripcion_gasto',
                'vo.monto'
            )
            ->get()
            ->groupBy('id_contenedor');

        $asignaciones->each(function ($a) use ($justificaciones) {
            $a->justificacion = $justificaciones[$a->id_contenedor] ?? [];
        });

        $contenedores = $asignaciones->map(function ($c) {
            $contenedor = DocumCotizacion::with('cotizacion')
                ->where('id', $c->id_contenedor)
                ->first();

            $numContenedor = $contenedor?->num_contenedor ?? 'N/A';

            if (!is_null($contenedor?->cotizacion?->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $contenedor->cotizacion->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion')
                    ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $numContenedor .= ' / ' . $secundaria->DocCotizacion->num_contenedor;
                }
            }

            return [
                "IdAsignacion" => $c->id,
                "IdOperador" => $c->id_operador,
                "IdContenedor" => $c->id_contenedor,
                "Contenedores" => $numContenedor,
                "ContenedorPrincipal" => $contenedor?->num_contenedor,
                "SueldoViaje" => $c->sueldo_viaje,
                "DineroViaje" => $c->dinero_viaje,
                "GastosJustificados" => $c->gastos_justificados,
                "MontoPago" => $c->sueldo_viaje - $c->dinero_viaje + $c->gastos_justificados - $c->pagado,
                "pagado" => $c->pagado,
                "FechaInicia" => $c->fecha_inicio,
                "FechaTermina" => $c->fecha_fin
            ];
        });

        $totalPago = $asignaciones->sum('saldo_real');
        $numeroViajes = $asignaciones->count();

        $prestamos = Prestamo::with(['operador', 'pagoprestamos'])
            ->where('id_operador', $idOperador)
            ->get()
            ->filter(fn ($prestamo) => $prestamo->saldo_calculado > 0)
            ->values();

        return [
            "viajes" => $contenedores,
            "totalPago" => $totalPago,
            "prestamos" => $prestamos,
            "numViajes" => $numeroViajes,
            "data" => $asignaciones,
        ];
    }

    /**
     * Justifica un gasto individual de viático para un contenedor y sincroniza con el módulo de gastos.
     */
    public function justificarGasto(array $data, $user): void
    {
        DB::transaction(function () use ($data, $user) {
            $idEmpresa = $user->id_empresa;
            $documCotizacion = DocumCotizacion::where('num_contenedor', $data['numContenedor'])
                ->where('id_empresa', $idEmpresa)
                ->firstOrFail();

            $viaticoEntidad = ViaticosOperador::create([
                "id_cotizacion" => $documCotizacion->id_cotizacion,
                "descripcion_gasto" => $data['txtDescripcion'],
                "monto" => $data['montoJustificacion']
            ]);

            Asignaciones::where('id_contenedor', $documCotizacion->id)->update([
                "restante_pago_operador" => DB::raw('restante_pago_operador + ' . $data['montoJustificacion'])
            ]);

            $asignacion = Asignaciones::where('id_contenedor', $documCotizacion->id)->first();
            $sinJustificar = (float) ($data['sinJustificar'] ?? 0);
            $montoPrincipal = ($data['montoJustificacion'] > $sinJustificar) ? $sinJustificar : $data['montoJustificacion'];

            $this->gastosService->registrar([
                'id_empresa' => $idEmpresa,
                'concepto' => $data['txtDescripcion'],
                'monto_total' => $montoPrincipal,
                'tipo_gasto' => 'operador',
                'estatus' => 'pagado',
                'fecha_gasto' => Carbon::now(),
                'origen_modulo' => 'liquidacion_operador',
                'origen_legacy' => 'viaticos_operadores',
                'origen_legacy_id' => $viaticoEntidad->id,
                'user_id' => $user->id,
                'vinculos' => array_filter([
                    [
                        'tipo_vinculo' => 'cotizacion',
                        'vinculable_type' => Cotizaciones::class,
                        'vinculable_id' => $documCotizacion->id_cotizacion,
                    ],
                    [
                        'tipo_vinculo' => 'contenedor',
                        'vinculable_type' => DocumCotizacion::class,
                        'vinculable_id' => $documCotizacion->id,
                    ],
                    [
                        'tipo_vinculo' => 'asignacion',
                        'vinculable_type' => Asignaciones::class,
                        'vinculable_id' => $asignacion->id,
                    ],
                    $asignacion->id_operador ? [
                        'tipo_vinculo' => 'operador',
                        'vinculable_type' => Operador::class,
                        'vinculable_id' => $asignacion->id_operador,
                    ] : null
                ]),
                'imputaciones' => [
                    [
                        'fecha_imputacion' => Carbon::now(),
                        'tipo_imputacion' => 'viaje',
                        'imputable_type' => Asignaciones::class,
                        'imputable_id' => $asignacion->id,
                        'monto_imputado' => $montoPrincipal,
                        'origen' => 'directo',
                    ]
                ]
            ]);

            if ($data['montoJustificacion'] > $sinJustificar) {
                $this->gastosService->registrar([
                    'id_empresa' => $idEmpresa,
                    'concepto' => $data['txtDescripcion'] . " ** Excedente",
                    'monto_total' => $data['montoJustificacion'] - $sinJustificar,
                    'tipo_gasto' => 'operador',
                    'estatus' => 'pendiente_pago',
                    'fecha_gasto' => Carbon::now(),
                    'origen_modulo' => 'liquidacion_operador',
                    'origen_legacy' => 'viaticos_operadores_excedente',
                    'origen_legacy_id' => $viaticoEntidad->id,
                    'user_id' => $user->id,
                    'vinculos' => array_filter([
                        [
                            'tipo_vinculo' => 'cotizacion',
                            'vinculable_type' => Cotizaciones::class,
                            'vinculable_id' => $documCotizacion->id_cotizacion,
                        ],
                        [
                            'tipo_vinculo' => 'contenedor',
                            'vinculable_type' => DocumCotizacion::class,
                            'vinculable_id' => $documCotizacion->id,
                        ],
                        [
                            'tipo_vinculo' => 'asignacion',
                            'vinculable_type' => Asignaciones::class,
                            'vinculable_id' => $asignacion->id,
                        ],
                        $asignacion->id_operador ? [
                            'tipo_vinculo' => 'operador',
                            'vinculable_type' => Operador::class,
                            'vinculable_id' => $asignacion->id_operador,
                        ] : null
                    ]),
                    'imputaciones' => [
                        [
                            'fecha_imputacion' => Carbon::now(),
                            'tipo_imputacion' => 'viaje',
                            'imputable_type' => Asignaciones::class,
                            'imputable_id' => $asignacion->id,
                            'monto_imputado' => $data['montoJustificacion'] - $sinJustificar,
                            'origen' => 'directo',
                        ]
                    ]
                ]);
            }
        });
    }

    /**
     * Justifica múltiples partidas de viáticos por lote.
     */
    public function justificarGastosMultiples(array $filas, $user): void
    {
        if (empty($filas)) {
            throw new \InvalidArgumentException("No hay datos válidos para procesar");
        }

        DB::transaction(function () use ($filas, $user) {
            $idEmpresa = $user->id_empresa;

            foreach ($filas as $item) {
                $documCotizacion = DocumCotizacion::where('id', $item['IdContenedor'])
                    ->where('id_empresa', $idEmpresa)
                    ->first();

                if (!$documCotizacion) {
                    continue;
                }

                $viaticoEntidad = null;

                if (!empty($item['idviatico'])) {
                    $viaticoAntes = ViaticosOperador::where('id', $item['idviatico'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    $montoAntes = (float) $viaticoAntes->monto;
                    $montoNuevo = (float) $item['monto'];
                    $diferencia = $montoNuevo - $montoAntes;

                    $viaticoAntes->update([
                        "descripcion_gasto" => $item['motivo'],
                        "monto" => $montoNuevo,
                    ]);

                    $viaticoEntidad = $viaticoAntes->fresh();

                    if ($diferencia != 0) {
                        Asignaciones::where('id_contenedor', $documCotizacion->id)
                            ->update([
                                "restante_pago_operador" => DB::raw(
                                    'COALESCE(restante_pago_operador, 0) + ' . number_format($diferencia, 2, '.', '')
                                )
                            ]);
                    }
                } else {
                    $viaticoEntidad = ViaticosOperador::create([
                        "id_cotizacion" => $documCotizacion->id_cotizacion,
                        "descripcion_gasto" => $item['motivo'],
                        "monto" => $item['monto'],
                    ]);

                    Asignaciones::where('id_contenedor', $documCotizacion->id)
                        ->update([
                            "restante_pago_operador" => DB::raw('restante_pago_operador + ' . $item['monto'])
                        ]);
                }

                $asignacion = Asignaciones::where('id_contenedor', $documCotizacion->id)->first();
                $montoJustificacion = (float) $item['monto'];
                $sinJustificar = (float) ($asignacion->restante_pago_operador ?? 0);

                $gastoOp = Gasto::where('origen_legacy', 'viaticos_operadores')
                    ->where('origen_legacy_id', $viaticoEntidad->id)
                    ->first();

                $montoPrincipal = ($montoJustificacion > $sinJustificar) ? $sinJustificar : $montoJustificacion;

                if ($gastoOp) {
                    $gastoOp->update([
                        "monto_total" => $montoPrincipal,
                        "concepto" => $item['motivo'],
                    ]);
                } else {
                    $this->gastosService->registrar([
                        'id_empresa' => $idEmpresa,
                        'concepto' => $item['motivo'],
                        'monto_total' => $montoPrincipal,
                        'tipo_gasto' => 'operador',
                        'estatus' => 'pagado',
                        'fecha_gasto' => Carbon::now(),
                        'origen_modulo' => 'liquidacion_operador',
                        'origen_legacy' => 'viaticos_operadores',
                        'origen_legacy_id' => $viaticoEntidad->id,
                        'user_id' => $user->id,
                        'vinculos' => array_filter([
                            [
                                'tipo_vinculo' => 'cotizacion',
                                'vinculable_type' => Cotizaciones::class,
                                'vinculable_id' => $documCotizacion->id_cotizacion,
                            ],
                            [
                                'tipo_vinculo' => 'contenedor',
                                'vinculable_type' => DocumCotizacion::class,
                                'vinculable_id' => $documCotizacion->id,
                            ],
                            [
                                'tipo_vinculo' => 'asignacion',
                                'vinculable_type' => Asignaciones::class,
                                'vinculable_id' => $asignacion->id,
                            ],
                            $asignacion->id_operador ? [
                                'tipo_vinculo' => 'operador',
                                'vinculable_type' => Operador::class,
                                'vinculable_id' => $asignacion->id_operador,
                            ] : null
                        ]),
                        'imputaciones' => [
                            [
                                'fecha_imputacion' => Carbon::now(),
                                'tipo_imputacion' => 'viaje',
                                'imputable_type' => Asignaciones::class,
                                'imputable_id' => $asignacion->id,
                                'monto_imputado' => $montoPrincipal,
                                'origen' => 'directo',
                            ]
                        ]
                    ]);
                }

                if ($montoJustificacion > $sinJustificar) {
                    $excedenteGasto = Gasto::where('origen_legacy', 'viaticos_operadores_excedente')
                        ->where('origen_legacy_id', $viaticoEntidad->id)
                        ->first();

                    if ($excedenteGasto) {
                        $excedenteGasto->update([
                            'monto_total' => $montoJustificacion - $sinJustificar,
                            'concepto' => $item['motivo'] . " **Excedente",
                        ]);
                    } else {
                        $this->gastosService->registrar([
                            'id_empresa' => $idEmpresa,
                            'concepto' => $item['motivo'] . " **Excedente",
                            'monto_total' => $montoJustificacion - $sinJustificar,
                            'tipo_gasto' => 'operador',
                            'estatus' => 'pendiente_pago',
                            'fecha_gasto' => Carbon::now(),
                            'origen_modulo' => 'liquidacion_operador',
                            'origen_legacy' => 'viaticos_operadores_excedente',
                            'origen_legacy_id' => $viaticoEntidad->id,
                            'user_id' => $user->id,
                            'vinculos' => array_filter([
                                [
                                    'tipo_vinculo' => 'cotizacion',
                                    'vinculable_type' => Cotizaciones::class,
                                    'vinculable_id' => $documCotizacion->id_cotizacion,
                                ],
                                [
                                    'tipo_vinculo' => 'contenedor',
                                    'vinculable_type' => DocumCotizacion::class,
                                    'vinculable_id' => $documCotizacion->id,
                                ],
                                [
                                    'tipo_vinculo' => 'asignacion',
                                    'vinculable_type' => Asignaciones::class,
                                    'vinculable_id' => $asignacion->id,
                                ],
                                $asignacion->id_operador ? [
                                    'tipo_vinculo' => 'operador',
                                    'vinculable_type' => Operador::class,
                                    'vinculable_id' => $asignacion->id_operador,
                                ] : null
                            ]),
                            'imputaciones' => [
                                [
                                    'fecha_imputacion' => Carbon::now(),
                                    'tipo_imputacion' => 'viaje',
                                    'imputable_type' => Asignaciones::class,
                                    'imputable_id' => $asignacion->id,
                                    'monto_imputado' => $montoJustificacion - $sinJustificar,
                                    'origen' => 'directo',
                                ]
                            ]
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Agrega dinero para viaje con cargo bancario y decremento del restante del operador.
     */
    public function agregarDineroViaje(array $data, $user): void
    {
        $idEmpresa = $user->id_empresa;
        $monto = (float) ($data['montoJustificacion'] ?? 0);
        $bankId = $data['bank'] ?? null;
        $fechaAplicacionDinero = $data['FechaAplicacionDinero'] ?? date('d/m/Y');

        if ($bankId && $monto > 0) {
            $val = $this->bancosService->validarsaldoparacargo($idEmpresa, $bankId, $fechaAplicacionDinero, $monto);
            if (!$val["saldodisponible"]) {
                throw new SaldoInsuficienteException($val["message"]);
            }
        }

        DB::transaction(function () use ($data, $user, $idEmpresa, $monto, $bankId, $fechaAplicacionDinero) {
            $documCotizacion = DocumCotizacion::where('num_contenedor', $data['numContenedor'])
                ->where('id_empresa', $idEmpresa)
                ->firstOrFail();

            Asignaciones::where('id_contenedor', $documCotizacion->id)->update([
                "restante_pago_operador" => DB::raw('restante_pago_operador - ' . $monto)
            ]);

            $asignacion = Asignaciones::where('id_contenedor', $documCotizacion->id)->firstOrFail();

            $dineroViaje = new DineroContenedor();
            $dineroViaje->id_contenedor = $asignacion->id_contenedor;
            $dineroViaje->id_banco = $bankId;
            $dineroViaje->motivo = $data['txtDescripcion'] ?? '';
            $dineroViaje->monto = $monto;
            $dineroViaje->es_principal = 0;
            $dineroViaje->fecha_entrega_monto = !empty($fechaAplicacionDinero)
                ? Carbon::createFromFormat('d/m/Y', $fechaAplicacionDinero)->format('Y-m-d')
                : date('Y-m-d');
            $dineroViaje->save();

            $contenedoresAbonos = [
                [
                    'num_contenedor' => $data['numContenedor'],
                    'abono' => $monto
                ]
            ];

            Bancos::where('id', $bankId)->update(["saldo" => DB::raw("saldo - " . $monto)]);

            BancoDineroOpe::insert([[
                'id_operador' => $asignacion->id_operador,
                'id_banco1' => $bankId,
                'monto1' => $monto,
                'fecha_pago' => date('Y-m-d'),
                'tipo' => 'Salida',
                'id_empresa' => $idEmpresa,
                'contenedores' => json_encode($contenedoresAbonos),
                'descripcion_gasto' => 'Dinero para viaje: ' . ($data['txtDescripcion'] ?? '')
            ]]);

            if ($bankId && $monto > 0) {
                $operadorN = Operador::find($asignacion->id_operador);

                $movimientoData = [
                    'cuenta_bancaria_id' => $bankId,
                    'tipo' => 'cargo',
                    'monto' => $monto,
                    'concepto' => BancosService::generarConcepto('viaje', 'Dinero para viaje: ' . ($data['txtDescripcion'] ?? ''), $data['numContenedor'], $operadorN?->nombre ?? null),
                    'fecha_movimiento' => Carbon::createFromFormat('d/m/Y', $fechaAplicacionDinero)->format('Y-m-d'),
                    'origen' => 'sistema',
                    'referencia' => $data['numContenedor'],
                    'detalles' => json_encode($contenedoresAbonos),
                    'referenciaable_id' => $asignacion->id,
                    'referenciaable_type' => Asignaciones::class,
                ];

                $movimientoCrear = $this->bancosService->registrarMovimiento($movimientoData);
                if ($movimientoCrear) {
                    $dineroViaje->id_movimiento_bancario = $movimientoCrear->id;
                    $dineroViaje->save();
                }
            }
        });
    }

    /**
     * Obtiene los viáticos guardados para los contenedores indicados.
     */
    public function getViaticosGuardados(?array $idContenedores, ?string $numContenedor, int $idEmpresa): array
    {
        if (empty($idContenedores) && !empty($numContenedor)) {
            $doc = DocumCotizacion::where('num_contenedor', $numContenedor)
                ->where('id_empresa', $idEmpresa)
                ->first();
            if ($doc) {
                $idContenedores = [$doc->id];
            }
        }

        if (empty($idContenedores)) {
            return [];
        }

        $contenedores = DocumCotizacion::whereIn('id', (array) $idContenedores)->get()->keyBy('id');
        $idCotizaciones = $contenedores->pluck('id_cotizacion')->filter()->unique();

        $viaticos = ViaticosOperador::whereIn('id_cotizacion', $idCotizaciones)
            ->orderBy('id', 'asc')
            ->get();

        $cotizacionToContenedor = [];
        foreach ($contenedores as $cont) {
            $cotizacionToContenedor[$cont->id_cotizacion] = $cont->num_contenedor;
        }

        return $viaticos->map(function ($v) use ($cotizacionToContenedor) {
            return [
                'id' => $v->id,
                'id_cotizacion' => $v->id_cotizacion,
                'contenedor' => $cotizacionToContenedor[$v->id_cotizacion] ?? 'Contenedor',
                'descripcion' => $v->descripcion_gasto,
                'monto' => floatval($v->monto),
                'fecha' => $v->created_at ? $v->created_at->format('d/m/Y') : '',
            ];
        })
        ->sortBy([
            ['contenedor', 'asc'],
            ['id', 'asc']
        ])
        ->values()
        ->toArray();
    }

    /**
     * Obtiene el desglose de dinero entregado para viaje.
     */
    public function getDinerosViaje(?array $idContenedores, ?string $numContenedor, int $idEmpresa): array
    {
        if (empty($idContenedores) && !empty($numContenedor)) {
            $doc = DocumCotizacion::where('num_contenedor', $numContenedor)
                ->where('id_empresa', $idEmpresa)
                ->first();
            if ($doc) {
                $idContenedores = [$doc->id];
            }
        }

        if (empty($idContenedores)) {
            return [];
        }

        $dineros = DineroContenedor::with(['banco.catBanco', 'DocCotizacion.Asignaciones', 'movimientoBancario.cuentaBancaria.catBanco'])
            ->whereIn('id_contenedor', (array) $idContenedores)
            ->orderBy('id_contenedor')
            ->orderBy('id', 'asc')
            ->get();

        return $dineros->map(function ($d) {
            $nombreBanco = 'N/A';
            if ($d->banco) {
                $nombreBanco = optional($d->banco->catBanco)->nombre 
                    ?: ($d->banco->nombre_banco ?: ($d->banco->nombre_beneficiario ?: 'N/A'));
                if (!empty($d->banco->cuenta_bancaria)) {
                    $nombreBanco .= ' (••' . substr($d->banco->cuenta_bancaria, -4) . ')';
                }
            } elseif ($d->movimientoBancario?->cuentaBancaria) {
                $cb = $d->movimientoBancario->cuentaBancaria;
                $nombreBanco = optional($cb->catBanco)->nombre ?: ($cb->nombre_banco ?: 'N/A');
                if (!empty($cb->cuenta_bancaria)) {
                    $nombreBanco .= ' (••' . substr($cb->cuenta_bancaria, -4) . ')';
                }
            }

            $asignacion = $d->DocCotizacion?->Asignaciones;
            $dineroAsignacion = floatval($asignacion?->dinero_viaje ?? 0);
            $esPrincipal = (bool) $d->es_principal 
                || (strtolower(trim($d->motivo)) === 'dinero para viaje' && (abs(floatval($d->monto) - 1000.0) < 0.01 || (abs(floatval($d->monto) - $dineroAsignacion) < 0.01 && $dineroAsignacion > 0)));

            return [
                'id' => $d->id,
                'id_contenedor' => $d->id_contenedor,
                'num_contenedor' => $d->DocCotizacion?->num_contenedor ?? 'N/A',
                'motivo' => $d->motivo,
                'monto' => floatval($d->monto),
                'monto_formateado' => number_format($d->monto, 2),
                'fecha' => $d->fecha_entrega_monto ? Carbon::parse($d->fecha_entrega_monto)->format('d/m/Y') : '',
                'es_principal' => $esPrincipal,
                'banco' => $nombreBanco,
                'id_banco' => $d->id_banco,
                'id_movimiento_bancario' => $d->id_movimiento_bancario,
                'movimiento_cancelado' => (bool) optional($d->movimientoBancario)->cancelado,
                'es_cancelacion' => floatval($d->monto) < 0,
            ];
        })->toArray();
    }

    /**
     * Cancela un registro de dinero de viaje con neteo negativo y reversión bancaria.
     */
    public function eliminarDineroViaje(int $idDinero, $user): array
    {
        return DB::transaction(function () use ($idDinero) {
            $dinero = DineroContenedor::with('DocCotizacion.Asignaciones')->findOrFail($idDinero);

            if (floatval($dinero->monto) <= 0) {
                throw new \DomainException('No es posible cancelar un registro que ya representa una cancelación o saldo negativo.');
            }

            $montoPositivo = floatval($dinero->monto);
            $asignacion = $dinero->DocCotizacion?->Asignaciones ?? Asignaciones::where('id_contenedor', $dinero->id_contenedor)->first();
            $dineroAsignacion = floatval($asignacion?->dinero_viaje ?? 0);
            $esPrincipal = (bool) $dinero->es_principal 
                || (strtolower(trim($dinero->motivo)) === 'dinero para viaje' && (abs(floatval($dinero->monto) - 1000.0) < 0.01 || (abs(floatval($dinero->monto) - $dineroAsignacion) < 0.01 && $dineroAsignacion > 0)));

            $movimientoBancario = null;
            if ($dinero->id_movimiento_bancario) {
                $movimientoBancario = CatBancoCuentasMovimientos::where('id', $dinero->id_movimiento_bancario)
                    ->where('cancelado', 0)
                    ->first();
            }

            if (!$movimientoBancario && $asignacion) {
                $movimientoBancario = CatBancoCuentasMovimientos::where('referenciaable_id', $asignacion->id)
                    ->where(function ($q) {
                        $q->where('referenciaable_type', Asignaciones::class)
                          ->orWhere('referenciaable_type', 'like', '%Asignaciones%');
                    })
                    ->where('cancelado', 0)
                    ->where('tipo', 'cargo')
                    ->where('monto', $montoPositivo)
                    ->where('concepto', 'not like', 'Devolución%')
                    ->latest('id')
                    ->first();
            }

            if ($movimientoBancario && !$movimientoBancario->cancelado) {
                $fechaCancelacion = $movimientoBancario->fecha_movimiento
                    ? Carbon::parse($movimientoBancario->fecha_movimiento)->format('Y-m-d')
                    : now()->format('Y-m-d');

                $this->bancosService->cancelarMovimiento(
                    $movimientoBancario->cuenta_bancaria_id,
                    $movimientoBancario->id,
                    $fechaCancelacion
                );
            }

            $dineroNeteo = new DineroContenedor();
            $dineroNeteo->id_contenedor = $dinero->id_contenedor;
            $dineroNeteo->id_banco = $dinero->id_banco;
            $dineroNeteo->motivo = 'Cancelación: ' . $dinero->motivo;
            $dineroNeteo->monto = -$montoPositivo;
            $dineroNeteo->fecha_entrega_monto = date('Y-m-d');
            $dineroNeteo->es_principal = $esPrincipal ? 1 : 0;
            $dineroNeteo->save();

            if ($esPrincipal) {
                Asignaciones::where('id_contenedor', $dinero->id_contenedor)->update([
                    'dinero_viaje' => 0,
                    'id_banco1_dinero_viaje' => null,
                    'cantidad_banco1_dinero_viaje' => 0,
                    'restante_pago_operador' => DB::raw('COALESCE(restante_pago_operador, 0) + ' . $montoPositivo)
                ]);
                $mensajeExito = 'Se canceló el dinero principal de viaje por $' . number_format($montoPositivo, 2) . ', se restableció el dinero de viaje a $0.00 en la asignación y se devolvió el saldo en bancos.';
            } else {
                Asignaciones::where('id_contenedor', $dinero->id_contenedor)->update([
                    'restante_pago_operador' => DB::raw('COALESCE(restante_pago_operador, 0) + ' . $montoPositivo)
                ]);
                $mensajeExito = 'Se canceló el dinero extra de viaje por $' . number_format($montoPositivo, 2) . ', se reintegró a la asignación y se devolvió el saldo en bancos.';
            }

            return [
                'Titulo' => $esPrincipal ? 'Dinero principal cancelado' : 'Dinero extra cancelado',
                'Mensaje' => $mensajeExito,
            ];
        });
    }

    /**
     * Elimina una justificación de viático y cancela sus impactos en gastos.
     */
    public function eliminarViatico(int $idViatico, $user): float
    {
        return DB::transaction(function () use ($idViatico) {
            $viatico = ViaticosOperador::findOrFail($idViatico);
            $montoViatico = floatval($viatico->monto);
            $idCotizacion = $viatico->id_cotizacion;

            $documCotizacion = DocumCotizacion::where('id_cotizacion', $idCotizacion)->first();
            if ($documCotizacion) {
                Asignaciones::where('id_contenedor', $documCotizacion->id)->update([
                    'restante_pago_operador' => DB::raw('COALESCE(restante_pago_operador, 0) - ' . $montoViatico)
                ]);
            }

            $gastoOp = Gasto::where('origen_legacy', 'viaticos_operadores')
                ->where('origen_legacy_id', $viatico->id)
                ->first();

            if ($gastoOp) {
                foreach ($gastoOp->pagos()->where('estatus', '!=', 'cancelado')->get() as $pago) {
                    $this->gastosService->cancelarPago($pago, now()->format('Y-m-d'));
                }
                $gastoOp->estatus = 'cancelado';
                $gastoOp->save();
            }

            $excedenteGasto = Gasto::where('origen_legacy', 'viaticos_operadores_excedente')
                ->where('origen_legacy_id', $viatico->id)
                ->first();

            if ($excedenteGasto) {
                foreach ($excedenteGasto->pagos()->where('estatus', '!=', 'cancelado')->get() as $pago) {
                    $this->gastosService->cancelarPago($pago, now()->format('Y-m-d'));
                }
                $excedenteGasto->estatus = 'cancelado';
                $excedenteGasto->save();
            }

            $viatico->delete();

            return $montoViatico;
        });
    }

    /**
     * Aplica la liquidación final a un operador, amortizando préstamos/adelantos y generando cargos bancarios.
     */
    public function aplicarLiquidacion(array $data, $user): int
    {
        $idOperador = $data['_IdOperador'];
        $bancoId = $data['bancoId'] ?? null;
        $totalPagar = (float) $data['totalMontoPago'];
        $totalPagoPrestamo = (float) ($data['totalPagoPrestamo'] ?? 0);
        $totalPagoAdelanto = (float) ($data['totalPagoAdelanto'] ?? 0);
        $fechaAplicacionDinero = $data['FechaAplicacionPago'];

        $prestamosVigentes = Prestamo::with(['operador', 'pagoprestamos'])
            ->where('id_operador', $idOperador)
            ->where('tipo', Prestamo::TIPO_PRESTAMO)
            ->get()
            ->filter(fn ($p) => $p->saldo_calculado > 0)
            ->values();

        if ($totalPagoPrestamo > $prestamosVigentes->sum('saldo_calculado')) {
            throw new \DomainException("No se puede aplicar porque el saldo del operador es menor a la cantidad que intenta cobrar de préstamos.");
        }

        $padelantosVigentes = Prestamo::with(['operador', 'pagoprestamos'])
            ->where('id_operador', $idOperador)
            ->where('tipo', Prestamo::TIPO_ADELANTO)
            ->get()
            ->filter(fn ($p) => $p->saldo_calculado > 0)
            ->values();

        if ($totalPagoAdelanto > $padelantosVigentes->sum('saldo_calculado')) {
            throw new \DomainException("No se puede aplicar porque el saldo del operador es menor a la cantidad que intenta cobrar de adelantos.");
        }

        if ($bancoId && $totalPagar > 0) {
            $val = $this->bancosService->validarsaldoparacargo($user->id_empresa, $bancoId, $fechaAplicacionDinero, $totalPagar);
            if (!$val["saldodisponible"]) {
                throw new SaldoInsuficienteException($val["message"]);
            }
        }

        return DB::transaction(function () use ($data, $user, $prestamosVigentes, $padelantosVigentes, $idOperador, $bancoId, $totalPagar, $totalPagoPrestamo, $totalPagoAdelanto, $fechaAplicacionDinero) {
            $contenedores = collect($data['pagoContenedores']);

            $liquidacion = new Liquidaciones();
            $liquidacion->id_operador = $idOperador;
            $liquidacion->id_banco = $bancoId;
            $liquidacion->fecha = Carbon::createFromFormat('d/m/Y', $fechaAplicacionDinero)->format('Y-m-d');
            $liquidacion->viajes_realizados = $contenedores->count();
            $liquidacion->sueldo_operador = $contenedores->sum('SueldoViaje');
            $liquidacion->dinero_viaje = $contenedores->sum('DineroViaje');
            $liquidacion->dinero_justificado = $contenedores->sum('GastosJustificados');
            $liquidacion->pago_prestamos = $totalPagoPrestamo;
            $liquidacion->pago_adelantos = $totalPagoAdelanto;
            $liquidacion->total_pago = ($totalPagoPrestamo > 0) ? $contenedores->sum('MontoPago') - $totalPagoPrestamo : $contenedores->sum('MontoPago');
            $liquidacion->user_id = $user->id;
            $liquidacion->save();

            // Amortizar préstamos
            $pagoPrestamos = $totalPagoPrestamo;
            foreach ($prestamosVigentes as $p) {
                if ($pagoPrestamos > 0) {
                    $prestamo = Prestamo::findOrFail($p->id);
                    $montoAmortizar = ($pagoPrestamos > $p->saldo_actual) ? $p->saldo_actual : $pagoPrestamos;
                    $prestamo->saldo_actual = ($pagoPrestamos > $p->saldo_actual) ? 0 : $p->saldo_actual - $pagoPrestamos;
                    $prestamo->save();

                    PagoPrestamo::create([
                        'id_liquidacion' => $liquidacion->id,
                        'id_prestamo' => $p->id,
                        'saldo_anterior' => $p->saldo_actual,
                        'monto_pago' => $montoAmortizar,
                        'tipo_origen' => 'liquidacion',
                        'id_banco' => $bancoId,
                        'referencia' => 'liq #' . $liquidacion->id,
                        'fecha_pago' => Carbon::createFromFormat('d/m/Y', $fechaAplicacionDinero)->format('Y-m-d'),
                    ]);

                    $pagoPrestamos = ($pagoPrestamos > $p->saldo_actual) ? $pagoPrestamos - $p->saldo_actual : 0;
                }
            }

            // Amortizar adelantos
            $pagoAdelanto = $totalPagoAdelanto;
            foreach ($padelantosVigentes as $p) {
                if ($pagoAdelanto > 0) {
                    $prestamo = Prestamo::findOrFail($p->id);
                    $montoAmortizar = ($pagoAdelanto > $p->saldo_actual) ? $p->saldo_actual : $pagoAdelanto;
                    $prestamo->saldo_actual = ($pagoAdelanto > $p->saldo_actual) ? 0 : $p->saldo_actual - $pagoAdelanto;
                    $prestamo->save();

                    PagoPrestamo::create([
                        'id_liquidacion' => $liquidacion->id,
                        'id_prestamo' => $p->id,
                        'saldo_anterior' => $p->saldo_actual,
                        'monto_pago' => $montoAmortizar,
                        'tipo_origen' => 'liquidacion',
                        'id_banco' => $bancoId,
                        'referencia' => 'liq #' . $liquidacion->id,
                        'fecha_pago' => Carbon::createFromFormat('d/m/Y', $fechaAplicacionDinero)->format('Y-m-d'),
                    ]);

                    $pagoAdelanto = ($pagoAdelanto > $p->saldo_actual) ? $pagoAdelanto - $p->saldo_actual : 0;
                }
            }

            // LiquidacionContenedor
            $contenedores->each(function ($c) use ($liquidacion) {
                LiquidacionContenedor::insert([
                    'id_liquidacion' => $liquidacion->id,
                    'id_contenedor' => $c['IdContenedor'],
                    'sueldo_operador' => $c['SueldoViaje'],
                    'dinero_viaje' => $c['DineroViaje'] ?? 0,
                    'dinero_justificado' => $c['GastosJustificados'],
                    'total_pagado' => $c['MontoPago'],
                ]);
            });

            $contenedoresAbonos = [];
            $unidadesLiquidacionMap = [];

            foreach ($contenedores as $c) {
                $asignacion = Asignaciones::findOrFail($c['IdAsignacion']);
                $saldoContenedor = (float) $asignacion->restante_pago_operador;
                $asignacion->restante_pago_operador = $saldoContenedor - (float) $c['MontoPago'];
                $asignacion->fecha_pago_operador = Carbon::createFromFormat('d/m/Y', $fechaAplicacionDinero)->format('Y-m-d');

                if (($saldoContenedor - (float) $c['MontoPago']) <= 0) {
                    $asignacion->estatus_pagado = 'Pagado';
                }
                $asignacion->save();

                // Gastos de viáticos y excedentes asociados a esta asignación pasan a pagado
                $gastosAsociados = Gasto::where('estatus', 'pendiente_pago')
                    ->whereIn('origen_legacy', ['viaticos_operadores', 'viaticos_operadores_excedente'])
                    ->whereHas('vinculos', function ($query) use ($asignacion) {
                        $query->where('tipo_vinculo', 'asignacion')
                              ->where('vinculable_id', $asignacion->id);
                    })
                    ->get();

                foreach ($gastosAsociados as $gasto) {
                    $gasto->update(['estatus' => 'pagado']);

                    $existePago = GastoPago::where('gasto_id', $gasto->id)->exists();
                    if (!$existePago) {
                        $cuentaBancariaId = null;
                        if ($gasto->origen_legacy === 'viaticos_operadores_excedente') {
                            $cuentaBancariaId = $bancoId;
                        } else {
                            $cuentaBancariaId = $asignacion->id_banco1_dinero_viaje ?? $bancoId;
                        }

                        if ($cuentaBancariaId && !DB::table('bancos')->where('id', $cuentaBancariaId)->exists()) {
                            $cuentaBancariaId = null;
                        }

                        GastoPago::create([
                            'gasto_id' => $gasto->id,
                            'cuenta_bancaria_id' => $cuentaBancariaId,
                            'fecha_pago' => Carbon::createFromFormat('d/m/Y', $fechaAplicacionDinero)->format('Y-m-d'),
                            'monto' => $gasto->monto_total,
                            'estatus' => 'aplicado',
                            'user_id' => $user->id,
                        ]);
                    }
                }

                $cotizacion = DocumCotizacion::find($asignacion->id_contenedor);

                $asignacion->loadMissing('Camion');
                $equipoId = $asignacion->id_camion;
                $equipoNombre = $asignacion->Camion ? ($asignacion->Camion->id_equipo ?: $asignacion->Camion->placas) : null;
                if ($equipoId) {
                    $unidadesLiquidacionMap[$equipoId] = $equipoNombre;
                }

                $contenedoresAbonos[] = [
                    'num_contenedor' => $cotizacion?->num_contenedor,
                    'abono' => $c['MontoPago'],
                    'id_equipo' => $equipoId,
                    'unidad' => $equipoNombre,
                ];
            }

            // Registrar movimiento bancario de cargo
            if ($bancoId && $contenedores->sum('MontoPago') > 0) {
                $operadorN = Operador::find($idOperador);
                $unidadConcepto = count($unidadesLiquidacionMap) === 1 ? reset($unidadesLiquidacionMap) : null;

                $this->bancosService->registrarMovimiento([
                    'cuenta_bancaria_id' => $bancoId,
                    'tipo' => 'cargo',
                    'monto' => $totalPagar,
                    'concepto' => BancosService::generarConcepto('viaje', 'Pago Operador / Liquidación', null, $operadorN?->nombre ?? null, $unidadConcepto),
                    'fecha_movimiento' => Carbon::createFromFormat('d/m/Y', $fechaAplicacionDinero)->format('Y-m-d'),
                    'origen' => null,
                    'referencia' => 'Liquidacion',
                    'detalles' => $contenedoresAbonos,
                    'referenciaable_id' => $liquidacion->id,
                    'referenciaable_type' => Liquidaciones::class,
                ]);
            }

            return $liquidacion->id;
        });
    }

    /**
     * Obtiene todos los datos para la impresión del comprobante PDF de una liquidación.
     */
    public function getDatosComprobantePago(int $idOperacion, $user): array
    {
        $liquidacionViajes = Liquidaciones::with([
            'viajes.Contenedores',
            'Operadores',
            'Banco'
        ])->findOrFail($idOperacion);

        $idViajes = $liquidacionViajes->viajes->pluck('id_contenedor');
        $cotizaciones = DocumCotizacion::whereIn('id', $idViajes)->get()->pluck('id_cotizacion');

        $viaticos = ViaticosOperador::whereIn('id_cotizacion', $cotizaciones)->get();

        $dineroViaje = DineroContenedor::whereIn('id_contenedor', $idViajes)
            ->orderBy('id_contenedor')
            ->get();

        $prestamosPagados = DB::table('prestamos')
            ->leftJoin('pagos_prestamos', function ($join) use ($idOperacion) {
                $join->on('prestamos.id', '=', 'pagos_prestamos.id_prestamo')
                     ->where('pagos_prestamos.id_liquidacion', '<>', $idOperacion);
            })
            ->select(
                'prestamos.id',
                'prestamos.id_operador',
                'prestamos.id_banco as id_banco_salida_dinero',
                'prestamos.cantidad',
                'prestamos.fecha_prestamo',
                'prestamos.tipo'
            )
            ->selectRaw('COALESCE(SUM(pagos_prestamos.monto_pago),0) as abonos')
            ->selectRaw('prestamos.cantidad - COALESCE(SUM(pagos_prestamos.monto_pago),0) as saldo_final')
            ->where('prestamos.id_operador', $liquidacionViajes->id_operador)
            ->groupBy(
                'prestamos.id',
                'prestamos.id_operador',
                'prestamos.id_banco',
                'prestamos.cantidad',
                'prestamos.fecha_prestamo',
                'prestamos.tipo'
            )
            ->havingRaw('saldo_final > 0')
            ->get();

        $abonoprestamosLiquidacion = PagoPrestamo::where('id_liquidacion', $idOperacion)->get();

        $totales = [
            'sueldo' => $liquidacionViajes->viajes->sum('sueldo_operador'),
            'dinero_viaje' => $liquidacionViajes->viajes->sum('dinero_viaje'),
            'justificado' => $liquidacionViajes->viajes->sum('dinero_justificado'),
            'adelantos' => $liquidacionViajes->pago_adelantos,
            'deudas' => $liquidacionViajes->pago_prestamos,
            'pagado' => $liquidacionViajes->viajes->sum('total_pagado') - ($liquidacionViajes->pago_prestamos + $liquidacionViajes->pago_adelantos),
        ];

        return compact(
            'user',
            'viaticos',
            'dineroViaje',
            'totales',
            'prestamosPagados',
            'abonoprestamosLiquidacion',
            'liquidacionViajes'
        );
    }

    /**
     * Obtiene el listado de historial de liquidaciones filtrado.
     */
    public function getHistorialPagosData(array $filters, int $idEmpresa): array
    {
        $idOperadores = Operador::where('id_empresa', $idEmpresa)->pluck('id');
        $fechaI = $filters['startDate'] ?? null;
        $fechaF = $filters['endDate'] ?? null;
        $numContenedor = $filters['num_contenedor'] ?? null;
        $idEquipo = $filters['id_equipo'] ?? null;

        $query = Liquidaciones::with(['Operadores', 'Viajes.Contenedores.Asignaciones.Camion'])
            ->whereIn('id_operador', $idOperadores);

        if (!empty($numContenedor)) {
            $query->whereHas('Viajes.Contenedores', function ($q) use ($numContenedor) {
                $q->where('num_contenedor', 'like', "%{$numContenedor}%");
            });
        }

        if (!empty($idEquipo)) {
            $query->whereExists(function ($sub) use ($idEquipo) {
                $sub->select(DB::raw(1))
                    ->from('liquidacion_contenedor')
                    ->join('asignaciones', 'asignaciones.id_contenedor', '=', 'liquidacion_contenedor.id_contenedor')
                    ->whereColumn('liquidacion_contenedor.id_liquidacion', 'liquidaciones.id')
                    ->where('asignaciones.id_camion', $idEquipo);
            });
        }

        if ($fechaI && $fechaF) {
            $query->whereBetween('fecha', [$fechaI, $fechaF]);
        }

        $historial = $query->get();

        return $historial->map(function ($h) {
            $contenedores = $h->Viajes->map(function ($v) {
                return $v->Contenedores?->num_contenedor;
            })->filter()->unique()->implode(', ');

            $contenedoresDetalle = $h->Viajes->map(function ($v) {
                return [
                    'id_contenedor' => $v->id_contenedor,
                    'num_contenedor' => $v->Contenedores?->num_contenedor ?? 'N/A',
                    'unidad' => $v->Contenedores?->Asignaciones?->Camion?->texto_select ?? 'N/A',
                    'sueldo_operador' => (float) $v->sueldo_operador,
                    'dinero_viaje' => (float) $v->dinero_viaje,
                    'dinero_justificado' => (float) $v->dinero_justificado,
                    'total_pagado' => (float) $v->total_pagado,
                ];
            })->values();

            return [
                "IdPago" => $h->id,
                "IdOperador" => $h->id_operador,
                "IdBanco" => $h->id_banco,
                "Operador" => $h->Operadores?->nombre ?? 'N/A',
                "NumContenedor" => $contenedores ?: 'N/A',
                "ContenedoresDetalle" => $contenedoresDetalle,
                "Fecha" => $h->fecha,
                "ViajesRealizados" => $h->viajes_realizados,
                "SueldoOperador" => $h->sueldo_operador,
                "DineroViaje" => $h->dinero_viaje,
                "DineroJustificado" => $h->dinero_justificado,
                "TotalPagado" => $h->total_pago
            ];
        })->toArray();
    }

    /**
     * Obtiene los datos de vista previa para liquidación de viajes seleccionados.
     */
    public function getVistaPreviaLiqu(array $viajes, int $operadorId): array
    {
        $idCotizacion = DocumCotizacion::whereIn('id', $viajes)->get()->pluck('id_cotizacion');
        $viaticos = ViaticosOperador::whereIn('id_cotizacion', $idCotizacion)->get();

        $dineroViaje = DineroContenedor::with('DocCotizacion')
            ->whereIn('id_contenedor', $viajes)
            ->orderBy('id_contenedor')
            ->get()->map(function ($dinero) {
                return [
                    'num_contenedor' => $dinero->DocCotizacion?->num_contenedor ?? 'N/A',
                    'monto' => $dinero->monto,
                    'motivo' => $dinero->motivo,
                    'fecha_entrega_monto' => $dinero->fecha_entrega_monto,
                ];
            });

        $prestamoPendiente = Prestamo::with('pagoprestamos')
            ->where('id_operador', $operadorId)
            ->where('tipo', Prestamo::TIPO_PRESTAMO)
            ->get()
            ->filter(fn ($p) => $p->saldo_calculado > 0)
            ->sum('saldo_calculado');

        $adelantoPendiente = Prestamo::with('pagoprestamos')
            ->where('id_operador', $operadorId)
            ->where('tipo', Prestamo::TIPO_ADELANTO)
            ->get()
            ->filter(fn ($p) => $p->saldo_calculado > 0)
            ->sum('saldo_calculado');

        $prestamosadelantosDetalles = Prestamo::with(['operador', 'pagoprestamos'])
            ->where('id_operador', $operadorId)
            ->get()
            ->filter(fn ($p) => $p->saldo_calculado > 0)
            ->values();

        return [
            'justificados' => $viaticos,
            'prestamo_pendiente' => $prestamoPendiente,
            'adelanto_pendiente' => $adelantoPendiente,
            'prestamosadelantosDetalles' => $prestamosadelantosDetalles,
            'dinero_viaje' => $dineroViaje,
        ];
    }

    /**
     * Elimina completamente una liquidación realizada, revirtiendo préstamos, asignaciones, viáticos y bancos.
     */
    public function deleteHistorialPago(int $id): void
    {
        DB::transaction(function () use ($id) {
            $liquidacion = Liquidaciones::findOrFail($id);

            // 1. Regresar saldo a préstamos
            $pagos = PagoPrestamo::where('id_liquidacion', $id)->get();
            foreach ($pagos as $pago) {
                $prestamo = Prestamo::find($pago->id_prestamo);
                if ($prestamo) {
                    $prestamo->saldo_actual += $pago->monto_pago;
                    $prestamo->save();
                }
            }

            // 2. Regresar saldo a asignaciones y eliminar viáticos/gastos creados por la liquidación
            $contenedores = LiquidacionContenedor::where('id_liquidacion', $id)->get();
            foreach ($contenedores as $c) {
                $asignacion = Asignaciones::where('id_contenedor', $c->id_contenedor)->first();
                if ($asignacion) {
                    $asignacion->restante_pago_operador = ($asignacion->restante_pago_operador - $c->dinero_justificado) + $c->total_pagado;
                    if ($asignacion->restante_pago_operador > 0) {
                        $asignacion->estatus_pagado = 'Pendiente Pago';
                    }
                    $asignacion->save();

                    $documCotizacion = DocumCotizacion::find($c->id_contenedor);
                    if ($documCotizacion) {
                        ViaticosOperador::where('id_cotizacion', $documCotizacion->id_cotizacion)->delete();
                    }

                    $gastosAsociados = Gasto::whereIn('origen_legacy', ['viaticos_operadores', 'viaticos_operadores_excedente'])
                        ->whereHas('vinculos', function ($query) use ($asignacion) {
                            $query->where('tipo_vinculo', 'asignacion')
                                  ->where('vinculable_id', $asignacion->id);
                        })
                        ->get();

                    foreach ($gastosAsociados as $gasto) {
                        GastoPago::where('gasto_id', $gasto->id)->delete();
                        $gasto->forceDelete();
                    }
                }
            }

            // 3. Regresar dinero al banco
            if ($liquidacion->id_banco) {
                Bancos::where('id', $liquidacion->id_banco)->update([
                    "saldo" => DB::raw("saldo + " . $liquidacion->total_pago)
                ]);
            }

            // 4. Borrar movimientos bancarios
            CatBancoCuentasMovimientos::where('referenciaable_id', $liquidacion->id)
                ->where('referenciaable_type', Liquidaciones::class)
                ->delete();

            // 5. Borrar pagos de préstamos y detalles
            PagoPrestamo::where('id_liquidacion', $id)->delete();
            LiquidacionContenedor::where('id_liquidacion', $id)->delete();

            // 6. Borrar liquidación
            $liquidacion->delete();
        });
    }

    /**
     * Obtiene los ajustes de liquidación con filtros.
     */
    public function getAjustesData(array $filters, int $idEmpresa): array
    {
        $fechaI = $filters['startDate'] ?? null;
        $fechaF = $filters['endDate'] ?? null;
        $idOperador = $filters['id_operador'] ?? null;
        $idBanco = $filters['id_banco'] ?? null;
        $estatus = $filters['estatus'] ?? null;

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

        return $ajustes->map(function ($a) {
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
        })->toArray();
    }

    /**
     * Búsqueda dinámica de liquidaciones para autocompletar en Ajustes.
     */
    public function buscarLiquidaciones(string $q, int $idEmpresa): array
    {
        $q = trim($q);
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

        return $liquidaciones->map(function ($l) {
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
        })->toArray();
    }

    /**
     * Obtiene los préstamos y adelantos activos con saldo pendiente de un operador.
     */
    public function getPrestamosOperador(int $idOperador): ?array
    {
        $operador = Operador::find($idOperador);
        if (!$operador) {
            return null;
        }

        return Prestamo::with(['pagoprestamos'])
            ->where('id_operador', $idOperador)
            ->get()
            ->filter(fn ($p) => $p->saldo_calculado > 0)
            ->values()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'tipo' => $p->tipo,
                    'tipo_texto' => ucfirst($p->tipo),
                    'fecha' => $p->fecha_prestamo ? Carbon::parse($p->fecha_prestamo)->format('Y-m-d') : '',
                    'cantidad_original' => (float) $p->cantidad,
                    'saldo_actual' => (float) $p->saldo_calculado,
                    'texto' => ucfirst($p->tipo) . " #{$p->id} - Saldo: $" . number_format($p->saldo_calculado, 2) . " (" . ($p->fecha_prestamo ? Carbon::parse($p->fecha_prestamo)->format('d/m/Y') : '') . ")",
                ];
            })
            ->toArray();
    }

    /**
     * Registra un nuevo ajuste de liquidación, amortiza deducciones y emite cargo bancario si procede.
     */
    public function storeAjuste(array $validated, $user): LiquidacionAjuste
    {
        $idEmpresa = $user->id_empresa;
        $userId = $user->id;

        $liquidacion = Liquidaciones::with(['Operadores'])->findOrFail($validated['id_liquidacion']);
        $operador = $liquidacion->Operadores;

        $partidas = collect($validated['partidas'] ?? $validated['detalles'] ?? []);

        $totalPercepciones = (float) $partidas->where('tipo', 'percepcion')->sum('monto');
        $totalDeducciones = (float) $partidas->where('tipo', 'deduccion')->sum('monto');
        $totalNeto = round(max(0, $totalPercepciones - $totalDeducciones), 2);

        $fechaAjuste = Carbon::parse($validated['fecha'])->format('Y-m-d');
        $idBanco = !empty($validated['id_banco']) ? (int) $validated['id_banco'] : null;

        // Validar saldo bancario si hay desembolso neto y banco seleccionado
        if ($totalNeto > 0 && $idBanco) {
            $validar = $this->bancosService->validarsaldoparacargo(
                $idEmpresa,
                $idBanco,
                Carbon::parse($validated['fecha'])->format('d/m/Y'),
                $totalNeto
            );

            if (isset($validar['saldodisponible']) && $validar['saldodisponible'] === false) {
                throw new SaldoInsuficienteException($validar['message'] ?? 'La cuenta seleccionada no dispone de saldo suficiente para cubrir este ajuste.');
            }
        }

        return DB::transaction(function () use ($validated, $user, $idEmpresa, $userId, $liquidacion, $operador, $partidas, $totalPercepciones, $totalDeducciones, $totalNeto, $fechaAjuste, $idBanco) {
            $ajuste = new LiquidacionAjuste();
            $ajuste->id_empresa = $idEmpresa;
            $ajuste->id_liquidacion = $liquidacion->id;
            $ajuste->id_operador = $liquidacion->id_operador;
            $ajuste->id_banco = $totalNeto > 0 ? $idBanco : null;
            $ajuste->fecha = $fechaAjuste;
            $ajuste->motivo = $validated['motivo'];
            $ajuste->total_percepciones = $totalPercepciones;
            $ajuste->total_deducciones = $totalDeducciones;
            $ajuste->total_neto = $totalNeto;
            $ajuste->estatus = 'aplicado';
            $ajuste->user_id = $userId;
            $ajuste->save();

            $ajuste->folio = 'AJ-' . date('Ym') . '-' . str_pad($ajuste->id, 4, '0', STR_PAD_LEFT);
            $ajuste->save();

            foreach ($partidas as $p) {
                $montoPartida = (float) $p['monto'];
                $idPrestamo = !empty($p['id_prestamo']) ? (int) $p['id_prestamo'] : (!empty($p['prestamo_id']) ? (int) $p['prestamo_id'] : null);
                $categoriaConcepto = $p['categoria_concepto'] ?? ($p['concepto'] ?? 'Ajuste');

                $detalle = new LiquidacionAjusteDetalle();
                $detalle->id_ajuste = $ajuste->id;
                $detalle->tipo = $p['tipo'];
                $detalle->categoria_concepto = $categoriaConcepto;
                $detalle->id_prestamo = $idPrestamo;
                $detalle->descripcion = $p['descripcion'] ?? ($p['observaciones'] ?? null);
                $detalle->monto = $montoPartida;
                $detalle->save();

                if ($p['tipo'] === 'deduccion' && in_array($categoriaConcepto, ['abono_prestamo', 'abono_adelanto']) && $idPrestamo) {
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
                            'id_banco' => $idBanco,
                            'referencia' => 'Ajuste #' . $ajuste->id . ' (' . $ajuste->folio . ')',
                            'fecha_pago' => $fechaAjuste,
                        ]);
                    }
                }
            }

            if ($totalNeto > 0 && $idBanco) {
                $bancoData = [
                    'cuenta_bancaria_id' => $idBanco,
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

            return $ajuste;
        });
    }

    /**
     * Cancela un ajuste de liquidación y revierte deducciones y bancos.
     */
    public function cancelarAjuste(int $id, int $idEmpresa, $user): void
    {
        DB::transaction(function () use ($id, $idEmpresa) {
            $ajuste = LiquidacionAjuste::with(['detalles'])->where('id_empresa', $idEmpresa)->findOrFail($id);

            if ($ajuste->estatus === 'cancelado') {
                throw new \DomainException('El ajuste ya se encuentra cancelado.');
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
            $movimiento = CatBancoCuentasMovimientos::where('referenciaable_type', LiquidacionAjuste::class)
                ->where('referenciaable_id', $ajuste->id)
                ->first();

            if ($movimiento) {
                $banco = Bancos::find($movimiento->cuenta_bancaria_id);
                if ($banco) {
                    $banco->saldo += $movimiento->monto;
                    $banco->save();
                }
                $movimiento->delete();
            }

            // 3. Cambiar estatus a cancelado
            $ajuste->estatus = 'cancelado';
            $ajuste->save();
        });
    }

    /**
     * Obtiene el contexto de liquidaciones y préstamos activos de un operador para la modal de ajuste.
     */
    public function getOperadorContextAjuste(int $idOperador, int $idEmpresa): array
    {
        $liquidaciones = Liquidaciones::where('id_operador', $idOperador)
            ->with(['Viajes.Contenedores'])
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->limit(25)
            ->get()
            ->map(function ($liq) {
                $contenedores = $liq->Viajes->map(function ($v) {
                    return $v->Contenedores?->num_contenedor;
                })->filter()->unique()->implode(', ') ?: 'N/A';

                return [
                    'id' => $liq->id,
                    'fecha' => $liq->fecha ? Carbon::parse($liq->fecha)->format('Y-m-d') : '',
                    'total_pago' => (float) $liq->total_pago,
                    'viajes_realizados' => $liq->viajes_realizados,
                    'contenedores' => $contenedores,
                ];
            });

        $prestamos = Prestamo::with('pagoprestamos')
            ->where('id_operador', $idOperador)
            ->get()
            ->filter(fn ($p) => $p->saldo_calculado > 0)
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'tipo' => $p->tipo,
                    'concepto' => $p->concepto ?: ($p->tipo === 'adelanto' ? 'Adelanto de sueldo' : 'Préstamo'),
                    'cantidad' => (float) $p->cantidad,
                    'saldo_calculado' => (float) $p->saldo_calculado,
                    'fecha_prestamo' => $p->fecha_prestamo ? Carbon::parse($p->fecha_prestamo)->format('Y-m-d') : '',
                ];
            })
            ->values();

        return [
            'liquidaciones' => $liquidaciones,
            'prestamos' => $prestamos,
        ];
    }

    /**
     * Obtiene los datos para generar el comprobante PDF de un ajuste de liquidación.
     */
    public function getDatosComprobanteAjuste(int $id, int $idEmpresa): array
    {
        $ajuste = LiquidacionAjuste::with([
            'operador',
            'banco',
            'usuario',
            'liquidacion.Viajes.Contenedores',
            'detalles.prestamo'
        ])->where('id_empresa', $idEmpresa)->findOrFail($id);

        $percepciones = $ajuste->detalles->where('tipo', 'percepcion')->values();
        $deducciones = $ajuste->detalles->where('tipo', 'deduccion')->values();

        return compact('ajuste', 'percepciones', 'deducciones');
    }
}