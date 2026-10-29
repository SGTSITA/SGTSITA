<?php

namespace App\Http\Controllers;

use App\Models\Asignaciones;
use App\Models\BancoDineroOpe;
use App\Models\Bancos;
use App\Models\CatBancoCuentasMovimientos;
use App\Models\DocumCotizacion;
use App\Models\GastosOperadores;
use App\Models\Cotizaciones;
use App\Models\Operador;
use App\Models\Liquidaciones;
use App\Models\LiquidacionContenedor;
use App\Models\ViaticosOperador;
use App\Models\Prestamo;
use App\Models\PagoPrestamo;
use App\Models\DineroContenedor;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\BancosService;
use App\Services\EquipoService;

class LiquidacionesController extends Controller
{
    protected $BancosService;
    protected $equipoService;

    public function __construct(BancosService $BancosService, EquipoService $equipoService)
    {
        $this->BancosService = $BancosService;
        $this->equipoService = $equipoService;
    }


    public function index()
    {
        return view('liquidaciones.index');
    }

    public function getPagosOperadores(Request $r)
    {
        $asignacion_operador = Asignaciones::join('operadores', 'asignaciones.id_operador', '=', 'operadores.id')
        ->where('asignaciones.id_empresa', '=', auth()->user()->id_empresa)
        ->where('asignaciones.tipo_contrato', '=', 'Propio')
        ->where('asignaciones.estatus_pagado', '=', 'Pendiente Pago')

        ->select(
            'asignaciones.id_operador',
            DB::raw('COUNT(*) as total_cotizaciones'),
            DB::raw('SUM(sueldo_viaje) as sueldo_viaje'),
            DB::raw('SUM(dinero_viaje) as dinero_viaje'),
            DB::raw('SUM(restante_pago_operador) as total_pago')
        )
        ->groupBy('asignaciones.id_operador')
        ->get();

        $datosPago = $asignacion_operador->map(function ($ope) {
            return [
                "IdOperador" => $ope->id_operador,
                "Operador" => (!is_null($ope->operador)) ? $ope->operador->nombre : 'N/A',
                "SueldoViaje" => $ope->sueldo_viaje,
                "DineroViaje" => $ope->dinero_viaje,
                "MontoPago" => $ope->total_pago,
                "ViajesRealizados" => $ope->total_cotizaciones
            ];
        });

        return $datosPago;
    }

    public function getpagosOperadoressaldo()
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

        ->leftJoinSub($subLiquidacion, 'lc', function ($join) {
            $join->on('lc.id_contenedor', '=', 'a.id_contenedor');
        })

        ->leftJoinSub($subDinero, 'dc2', function ($join) {
            $join->on('dc2.id_contenedor', '=', 'a.id_contenedor');
        })

        ->where('a.id_empresa', auth()->user()->id_empresa)
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
    )
    != 0
')
        ->get();

        $datosPago = $asignaciones->map(function ($ope) {
            return [
                "IdOperador" => $ope->id_operador,
                "Operador" => $ope->nombre ?? 'N/A',
                "SueldoViaje" => $ope->sueldo_viaje,
                 "DineroViaje" => $ope->total_dinero,
                "MontoPago" => $ope->total_pago_real,
                "ViajesRealizados" => $ope->total_cotizaciones
            ];
        });

        return $datosPago;

    }

    public function show($id)
    {
        $fecha = Carbon::now()->format('Y-m-d');
        $operador = Operador::where('id', '=', $id)->where('id_empresa', '=', auth()->user()->id_empresa)->first();
        $bancos2 = Bancos::where('id_empresa', '=', auth()->user()->id_empresa)->get();
        $bancos2 = Bancos::where('id_empresa', '=', auth()->user()->id_empresa)->get();
        $bancos = $this->BancosService->getCuentasOption(auth()->user()->id_empresa, $fecha, $fecha, true);
        if (is_null($operador)) {
            return "No existe información";
        }

        return view('liquidaciones.show', compact('operador', 'id', 'bancos'));
    }

    public function getViajesOperador(Request $r)
    {

        /*   $suma_por_asignacion = DB::table('dinero_contenedor')
          ->join('asignaciones', 'dinero_contenedor.id_contenedor', '=', 'asignaciones.id_contenedor')
          ->where('asignaciones.id_empresa', auth()->user()->id_empresa)
          ->where('asignaciones.estatus_pagado', 'Pendiente Pago')
          ->whereNull('asignaciones.id_proveedor')
          ->where('asignaciones.id_operador', $r->operador)
          ->select(
              'dinero_contenedor.id_contenedor',
              DB::raw('SUM(dinero_contenedor.monto) as total_monto')
          )
          ->groupBy('dinero_contenedor.id_contenedor')
          ->get();

          $montos = $suma_por_asignacion->keyBy('id_contenedor');

          $asignacion_operador = Asignaciones::where('id_empresa', '=', auth()->user()->id_empresa)
          ->where('estatus_pagado', '=', 'Pendiente Pago')
          ->where('id_proveedor', '=', null)
          ->where('id_operador', '=', $r->operador)
          ->get();

          $asignacion_operador->each(function ($asignacion) use ($montos) {
              $asignacion->total_monto = $montos[$asignacion->id_contenedor]->total_monto ?? 0;
          });


          $contenedores = $asignacion_operador->map(function ($c) {

              $numContenedor = $c->contenedor->num_contenedor;

              if (!is_null($c->contenedor->cotizacion->referencia_full)) {
                  $secundaria = Cotizaciones::where('referencia_full', $c->contenedor->cotizacion->referencia_full)
                          ->where('jerarquia', 'Secundario')
                          ->with('DocCotizacion.Asignaciones')
                          ->first();

                  if ($secundaria && $secundaria->DocCotizacion) {
                      $numContenedor .= ' / ' . $secundaria->DocCotizacion->num_contenedor;
                  }
              }

              $justificaciones = (!is_null($c->justificacion)) ? $c->justificacion->sum('monto') : 0;

              return [
                  "IdAsignacion" => $c->id,
                  "IdOperador" => $c->id_operador,
                  "IdContenedor" => $c->id_contenedor,
                  "Contenedores" => $numContenedor,
                  "ContenedorPrincipal" => $c->contenedor->num_contenedor,
                  "SueldoViaje" => $c->sueldo_viaje,
                  "DineroViaje" => $c->total_monto,
                  "GastosJustificados" => $justificaciones,
                  "MontoPago" => $c->sueldo_viaje - $c->total_monto + $justificaciones,
                  "FechaInicia" => $c->fecha_inicio,
                  "FechaTermina" => $c->fecha_fin
              ];
          });

          $totalPago = $asignacion_operador->sum('restante_pago_operador');
          $numeroViajes = $asignacion_operador->count();

          $bancos = Bancos::where('id_empresa', '=', auth()->user()->id_empresa)->get();
          $gastos_ope = GastosOperadores::where('id_operador', '=', $r->operador)->get();

          // $prestamos = Prestamo::where('id_operador', $r->operador)->where('saldo_actual', '>', 0)->get();

          $prestamos = Prestamo::with(['operador', 'pagoprestamos'])
         ->where('id_operador', $r->operador)
    ->get()
->filter(function ($prestamo) {
    return $prestamo->saldo_calculado > 0;
})
          ;

          return response()->json([
              "viajes" => $contenedores,
              "totalPago" => $totalPago,
              "prestamos" => $prestamos,
              "numViajes" => $numeroViajes,
              "data" => $asignacion_operador
          ]); */


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

->leftJoinSub($subLiquidacion, 'lc', function ($join) {
    $join->on('lc.id_contenedor', '=', 'a.id_contenedor');
})

->leftJoinSub($subDinero, 'dc', function ($join) {
    $join->on('dc.id_contenedor', '=', 'a.id_contenedor');
})

->leftJoinSub($subJustificados, 'j', function ($join) {
    $join->on('j.id_contenedor', '=', 'a.id_contenedor');
})

->where('a.id_empresa', auth()->user()->id_empresa)
->whereNull('a.id_proveedor')
->where('a.id_operador', $r->operador)
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
    as saldo_real
    ')
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

            $numContenedor = $contenedor->num_contenedor;

            if (!is_null($contenedor->cotizacion->referencia_full)) {

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
                "ContenedorPrincipal" => $contenedor->num_contenedor,

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


        $gastos_ope = GastosOperadores::where('id_operador', '=', $r->operador)->get();

        // $prestamos = Prestamo::where('id_operador', $r->operador)->where('saldo_actual', '>', 0)->get();

        $prestamos = Prestamo::with(['operador', 'pagoprestamos'])
         ->where('id_operador', $r->operador)
    ->get()
->filter(function ($prestamo) {
    return $prestamo->saldo_calculado > 0;
})
        ;

        return response()->json([
            "viajes" => $contenedores,
            "totalPago" => $totalPago,
            "prestamos" => $prestamos,
            "numViajes" => $numeroViajes,
            "data" => $asignaciones,
           // "justificaciones" => $justificacionesV2
        ]);
    }

    public function justificarGastos(Request $r)
    {
        try {
            DB::beginTransaction();
            $idEmpresa = auth()->user()->id_empresa;
            $documCotizacion = DocumCotizacion::where('num_contenedor', $r->numContenedor)
                                              ->where('id_empresa', $idEmpresa)
                                              ->first();
            $viaticoEntidad = ViaticosOperador::create([
                "id_cotizacion" => $documCotizacion->id_cotizacion,
                "descripcion_gasto" => $r->txtDescripcion,
                "monto" => $r->montoJustificacion
            ]);

            $asignacion = Asignaciones::where('id_contenedor', $documCotizacion->id)->update([
                "restante_pago_operador" => DB::raw('restante_pago_operador + '.$r->montoJustificacion)
            ]);

            //Los gastos que sean justificados por el operador, deberan reflejarse en el viaje correspondiente

            $asignacion = Asignaciones::where('id_contenedor', $documCotizacion->id)->first();

            $montoPrincipal = ($r->montoJustificacion > $r->sinJustificar) ? $r->sinJustificar : $r->montoJustificacion;

            $gasto = app(\App\Services\GastosService::class)->registrar([
                'id_empresa' => $idEmpresa,
                'concepto' => $r->txtDescripcion,
                'monto_total' => $montoPrincipal,
                'tipo_gasto' => 'operador',
                'estatus' => 'pagado',
                'fecha_gasto' => Carbon::now(),
                'origen_modulo' => 'liquidacion_operador',
                'origen_legacy' => 'viaticos_operadores',
                'origen_legacy_id' => $viaticoEntidad->id,
                'user_id' => auth()->id(),
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
                        'vinculable_type' => \App\Models\Operador::class,
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

            //Registramos el excedente en una nueva partida y lo dejamos como pendiente de pago
            if ($r->montoJustificacion > $r->sinJustificar) {
                $excedenteGasto = app(\App\Services\GastosService::class)->registrar([
                    'id_empresa' => $idEmpresa,
                    'concepto' => $r->txtDescripcion . " ** Excedente",
                    'monto_total' => $r->montoJustificacion - $r->sinJustificar,
                    'tipo_gasto' => 'operador',
                    'estatus' => 'pendiente_pago',
                    'fecha_gasto' => Carbon::now(),
                    'origen_modulo' => 'liquidacion_operador',
                    'origen_legacy' => 'viaticos_operadores_excedente',
                    'origen_legacy_id' => $viaticoEntidad->id,
                    'user_id' => auth()->id(),
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
                            'vinculable_type' => \App\Models\Operador::class,
                            'vinculable_id' => $asignacion->id_operador,
                        ] : null
                    ]),
                    'imputaciones' => [
                        [
                            'fecha_imputacion' => Carbon::now(),
                            'tipo_imputacion' => 'viaje',
                            'imputable_type' => Asignaciones::class,
                            'imputable_id' => $asignacion->id,
                            'monto_imputado' => $r->montoJustificacion - $r->sinJustificar,
                            'origen' => 'directo',
                        ]
                    ]
                ]);
            }
            //Fin

            DB::commit();

            return response()->json(["Titulo" => "Correcto", "Mensaje" => "Datos guardados con exito", "TMensaje" => "success"]);
        } catch (\Throwable $t) {
            DB::rollback();
            $uniqid = uniqid();
            Log::channel('daily')->info("Codigo: $uniqid, Error: LiquidacionesController/justificarGastos ".$t->getMessage());
            return response()->json(["Titulo" => "Ha ocurrido un problema", "Mensaje" => "Codigo error: $uniqid. Mensaje: ".$t->getMessage(), "TMensaje" => "error"]);

        }
    }



    public function justificarGastosMultiples(Request $r)
    {
        try {
            DB::beginTransaction();
            $idEmpresa = auth()->user()->id_empresa;

            $filas = $r->input('filas');

            if (!is_array($filas) || empty($filas)) {
                throw new \Exception("No hay datos válidos para procesar");
            }

            foreach ($filas as $item) {
                $documCotizacion = DocumCotizacion::where('id', $item['IdContenedor'])
                    ->where('id_empresa', $idEmpresa)
                    ->first();

                if (!$documCotizacion) {
                    continue;
                }

                $viaticoEntidad= null;

                if (!empty($item['idviatico'])) { // Actualizar registro existente
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
                    // Nuevo registro
                  $viaticoEntidad = ViaticosOperador::create([
     "id_cotizacion" => $documCotizacion->id_cotizacion,
     "descripcion_gasto" => $item['motivo'],
     "monto" => $item['monto'],
]);

                    Asignaciones::where('id_contenedor', $documCotizacion->id)
                        ->update([
                            "restante_pago_operador" => DB::raw('restante_pago_operador + '.$item['monto'])
                        ]);

                }

                $asignacion = Asignaciones::where('id_contenedor', $documCotizacion->id)->first();

                $montoJustificacion = $item['monto'];
                $sinJustificar = $asignacion->restante_pago_operador ?? 0;

                $gastoOp = \App\Models\Gasto::where('origen_legacy', 'viaticos_operadores')
                    ->where('origen_legacy_id', $viaticoEntidad->id)
                    ->first();

                if($gastoOp){
                    $gastoOp->update([
                        "monto_total" => ($montoJustificacion > $sinJustificar) ? $sinJustificar : $montoJustificacion,
                        "concepto" => $item['motivo'],
                    ]);
                } else {
                    $gastoOp = app(\App\Services\GastosService::class)->registrar([
                        'id_empresa' => $idEmpresa,
                        'concepto' => $item['motivo'],
                        'monto_total' => ($montoJustificacion > $sinJustificar) ? $sinJustificar : $montoJustificacion,
                        'tipo_gasto' => 'operador',
                        'estatus' => 'pagado',
                        'fecha_gasto' => Carbon::now(),
                        'origen_modulo' => 'liquidacion_operador',
                        'origen_legacy' => 'viaticos_operadores',
                        'origen_legacy_id' => $viaticoEntidad->id,
                        'user_id' => auth()->id(),
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
                                'vinculable_type' => \App\Models\Operador::class,
                                'vinculable_id' => $asignacion->id_operador,
                            ] : null
                        ]),
                        'imputaciones' => [
                            [
                                'fecha_imputacion' => Carbon::now(),
                                'tipo_imputacion' => 'viaje',
                                'imputable_type' => Asignaciones::class,
                                'imputable_id' => $asignacion->id,
                                'monto_imputado' => ($montoJustificacion > $sinJustificar) ? $sinJustificar : $montoJustificacion,
                                'origen' => 'directo',
                            ]
                        ]
                    ]);
                }

                // Si excede
                if ($montoJustificacion > $sinJustificar) {
                    $excedenteGasto = \App\Models\Gasto::where('origen_legacy', 'viaticos_operadores_excedente')
                        ->where('origen_legacy_id', $viaticoEntidad->id)
                        ->first();

                    if ($excedenteGasto) {
                        $excedenteGasto->update([
                            'monto_total' => $montoJustificacion - $sinJustificar,
                            'concepto' => $item['motivo'] . " **Excedente",
                        ]);
                    } else {
                        $excedenteGasto = app(\App\Services\GastosService::class)->registrar([
                            'id_empresa' => $idEmpresa,
                            'concepto' => $item['motivo'] . " **Excedente",
                            'monto_total' => $montoJustificacion - $sinJustificar,
                            'tipo_gasto' => 'operador',
                            'estatus' => 'pendiente_pago',
                            'fecha_gasto' => Carbon::now(),
                            'origen_modulo' => 'liquidacion_operador',
                            'origen_legacy' => 'viaticos_operadores_excedente',
                            'origen_legacy_id' => $viaticoEntidad->id,
                            'user_id' => auth()->id(),
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
                                    'vinculable_type' => \App\Models\Operador::class,
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

            DB::commit();

            return response()->json([
                "Titulo" => "Correcto",
                "Mensaje" => "Justificaciones guardadas correctamente",
                "TMensaje" => "success"
            ]);
        } catch (\Throwable $t) {
            DB::rollback();
            $uniqid = uniqid();
            Log::channel('daily')->error("Codigo: $uniqid, Error: justificarGastosMultiples ".$t->getMessage());
            return response()->json([
                "Titulo" => "Error",
                "Mensaje" => "Codigo error: $uniqid. Mensaje: ".$t->getMessage(),
                "TMensaje" => "error"
            ]);
        }
    }

    public function agregarDineroViaje(Request $request)
    {
        try {
            DB::beginTransaction();
            $idEmpresa = auth()->user()->id_empresa;


            //validar saldo nuevo banco
            if ($request->filled('bank') && $request->montoJustificacion > 0) {
                $fechaAplicacionDinero = $request->get('FechaAplicacionDinero');

                $validarSaldos = $this->BancosService->validarsaldoparacargo($idEmpresa, $request->get('bank'), $fechaAplicacionDinero, $request->montoJustificacion);
                //  dd($validarSaldos);
                if ($validarSaldos["saldodisponible"] == false) {

                    return response()->json([
                                      "TMensaje" => "error",
                                   "Titulo" => "Saldo bancos",
                                      "Mensaje" => $validarSaldos["message"],
                                      'success' => false,
                                      'RESP' => $validarSaldos
                                     ]);

                }
            }



            //finaliza validacion saldos

            $documCotizacion = DocumCotizacion::where('num_contenedor', $request->numContenedor)
                                              ->where('id_empresa', $idEmpresa)
                                              ->first();


            $asignacion = Asignaciones::where('id_contenedor', $documCotizacion->id)->update([
                "restante_pago_operador" => DB::raw('restante_pago_operador - '.$request->montoJustificacion)
            ]);

            $asignacion = Asignaciones::where('id_contenedor', $documCotizacion->id)->first();

            $dineroViaje = new DineroContenedor();
            $dineroViaje->id_contenedor = $asignacion->id_contenedor;
            $dineroViaje->id_banco = $request->get('bank');
            $dineroViaje->motivo = $request->txtDescripcion;
            $dineroViaje->monto = $request->montoJustificacion;
            $dineroViaje->es_principal = 0;
            $dineroViaje->fecha_entrega_monto = $request->filled('FechaAplicacionDinero')
                ? \Carbon\Carbon::createFromFormat('d/m/Y', $request->get('FechaAplicacionDinero'))->format('Y-m-d')
                : date('Y-m-d');
            $dineroViaje->save();

            $contenedoresAbonos = [];
            $contenedorAbono = [
                'num_contenedor' => $request->numContenedor,
                'abono' => $request->get('montoJustificacion')
            ];

            array_push($contenedoresAbonos, $contenedorAbono);

            Bancos::where('id', '=', $request->get('bank'))->update(["saldo" => DB::raw("saldo - ". $request->get('montoJustificacion'))]);
            BancoDineroOpe::insert([[
                                    'id_operador' => $asignacion->id_operador,
                                    'id_banco1' => $request->get('bank'),
                                    'monto1' => $request->get('montoJustificacion'),
                                    'fecha_pago' => date('Y-m-d'),
                                    'tipo' => 'Salida',
                                    'id_empresa' => auth()->user()->id_empresa,
                                    'contenedores' => json_encode($contenedoresAbonos),
                                    'descripcion_gasto' => 'Dinero para viaje: '.$request->txtDescripcion
                                ]]);


            //proceso bancos nuevo
            if ($request->filled('bank') && $request->montoJustificacion > 0) {
                $fechaAplicacionDinero = $request->get('FechaAplicacionDinero');

                $operadorN = Operador::find($asignacion->id_operador);

                $data = [
                        'cuenta_bancaria_id' => $request->get('bank'),            'tipo' => 'cargo',
                        'monto' => floatval($request->montoJustificacion),
                        'concepto' => \App\Services\BancosService::generarConcepto('viaje', 'Dinero para viaje: '.$request->txtDescripcion, $request->numContenedor, $operadorN?->nombre ?? null),
                        'fecha_movimiento' => \Carbon\Carbon::createFromFormat(
                            'd/m/Y',
                            $fechaAplicacionDinero
                        )->format('Y-m-d'),
                        'origen' => 'sistema',
                        'referencia' => $request->numContenedor,
                        'detalles' => json_encode($contenedoresAbonos),
                         'referenciaable_id' => $asignacion->id,
                          'referenciaable_type' => \App\Models\Asignaciones::class, //para polimorfismo
                    ];




                $movimeintoCrear = $this->BancosService->registrarMovimiento($data);

                if (!$movimeintoCrear) {
                    throw new \Exception('No se pudo crear el movimiento bancario, dinero para viaje adicional ');
                }

                $dineroViaje->id_movimiento_bancario = $movimeintoCrear->id;
                $dineroViaje->save();
            }


            DB::commit();
            return response()->json(["Titulo" => "Correcto", "Mensaje" => "Datos guardados con exito", "TMensaje" => "success"]);

        } catch (\Throwable $t) {
            DB::rollback();
            $uniqid = uniqid();
            Log::channel('daily')->info("Codigo: $uniqid, Error: LiquidacionesController/agregarDineroViaje ".$t->getMessage());
            return response()->json(["Titulo" => "Ha ocurrido un problema", "Mensaje" => "Codigo error: $uniqid. Mensaje: ".$t->getMessage(), "TMensaje" => "error"]);
        }
    }

    public function getViaticosGuardados(Request $request)
    {
        try {
            $idContenedores = $request->get('id_contenedores');
            if (!$idContenedores && $request->filled('id_contenedor')) {
                $idContenedores = [$request->get('id_contenedor')];
            }
            if (!$idContenedores && $request->filled('numContenedor')) {
                $doc = DocumCotizacion::where('num_contenedor', $request->get('numContenedor'))
                    ->where('id_empresa', auth()->user()->id_empresa)
                    ->first();
                if ($doc) {
                    $idContenedores = [$doc->id];
                }
            }

            if (empty($idContenedores)) {
                return response()->json(['success' => false, 'data' => [], 'message' => 'No se especificaron contenedores']);
            }

            $contenedores = DocumCotizacion::whereIn('id', (array) $idContenedores)->get()->keyBy('id');
            $idCotizaciones = $contenedores->pluck('id_cotizacion')->filter()->unique();

            $viaticos = ViaticosOperador::whereIn('id_cotizacion', $idCotizaciones)
                ->orderBy('id', 'asc')
                ->get();

            // Mapear cada viático a su contenedor correspondiente
            $cotizacionToContenedor = [];
            foreach ($contenedores as $cont) {
                $cotizacionToContenedor[$cont->id_cotizacion] = $cont->num_contenedor;
            }

            $resultado = $viaticos->map(function ($v) use ($cotizacionToContenedor) {
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
            ->values();

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Throwable $t) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener viáticos: ' . $t->getMessage()
            ], 500);
        }
    }

    public function getDinerosViaje(Request $request)
    {
        try {
            $idContenedores = $request->get('id_contenedores');
            if (!$idContenedores && $request->filled('id_contenedor')) {
                $idContenedores = [$request->get('id_contenedor')];
            }
            if (!$idContenedores && $request->filled('numContenedor')) {
                $doc = DocumCotizacion::where('num_contenedor', $request->get('numContenedor'))
                    ->where('id_empresa', auth()->user()->id_empresa)
                    ->first();
                if ($doc) {
                    $idContenedores = [$doc->id];
                }
            }

            if (empty($idContenedores)) {
                return response()->json(['success' => false, 'data' => [], 'message' => 'No se especificaron contenedores']);
            }

            $dineros = DineroContenedor::with(['banco.catBanco', 'DocCotizacion.Asignaciones', 'movimientoBancario.cuentaBancaria.catBanco'])
                ->whereIn('id_contenedor', (array) $idContenedores)
                ->orderBy('id_contenedor')
                ->orderBy('id', 'asc')
                ->get();

            $resultado = $dineros->map(function ($d) {
                $nombreBanco = 'N/A';
                if ($d->banco) {
                    $nombreBanco = optional($d->banco->catBanco)->nombre 
                        ?: ($d->banco->nombre_banco 
                        ?: ($d->banco->nombre_beneficiario ?: 'N/A'));
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
                    'fecha' => $d->fecha_entrega_monto ? \Carbon\Carbon::parse($d->fecha_entrega_monto)->format('d/m/Y') : '',
                    'es_principal' => $esPrincipal,
                    'banco' => $nombreBanco,
                    'id_banco' => $d->id_banco,
                    'id_movimiento_bancario' => $d->id_movimiento_bancario,
                    'movimiento_cancelado' => (bool) optional($d->movimientoBancario)->cancelado,
                    'es_cancelacion' => floatval($d->monto) < 0,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Throwable $t) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener dinero de viaje: ' . $t->getMessage()
            ], 500);
        }
    }

    public function eliminarDineroViaje(Request $request)
    {
        try {
            DB::beginTransaction();
            $idDinero = $request->get('id_dinero_contenedor');
            $dinero = DineroContenedor::with('DocCotizacion.Asignaciones')->findOrFail($idDinero);

            if (floatval($dinero->monto) <= 0) {
                return response()->json([
                    'success' => false,
                    'Titulo' => 'Operación no permitida',
                    'Mensaje' => 'No es posible cancelar un registro que ya representa una cancelación o saldo negativo.',
                    'TMensaje' => 'warning'
                ], 422);
            }

            $montoPositivo = floatval($dinero->monto);
            $asignacion = $dinero->DocCotizacion?->Asignaciones ?? Asignaciones::where('id_contenedor', $dinero->id_contenedor)->first();
            $dineroAsignacion = floatval($asignacion?->dinero_viaje ?? 0);
            $esPrincipal = (bool) $dinero->es_principal 
                || (strtolower(trim($dinero->motivo)) === 'dinero para viaje' && (abs(floatval($dinero->monto) - 1000.0) < 0.01 || (abs(floatval($dinero->monto) - $dineroAsignacion) < 0.01 && $dineroAsignacion > 0)));

            // 1. Cancelar movimiento bancario si existe
            $movimientoBancario = null;
            if ($dinero->id_movimiento_bancario) {
                $movimientoBancario = \App\Models\CatBancoCuentasMovimientos::where('id', $dinero->id_movimiento_bancario)
                    ->where('cancelado', 0)
                    ->first();
            }

            // Fallback para registros sin id_movimiento_bancario
            if (!$movimientoBancario && $asignacion) {
                $movimientoBancario = \App\Models\CatBancoCuentasMovimientos::where('referenciaable_id', $asignacion->id)
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
                    ? \Carbon\Carbon::parse($movimientoBancario->fecha_movimiento)->format('Y-m-d')
                    : now()->format('Y-m-d');

                $this->BancosService->cancelarMovimiento(
                    $movimientoBancario->cuenta_bancaria_id,
                    $movimientoBancario->id,
                    $fechaCancelacion
                );
            }

            // 2. Insertar registro negativo en dinero_contenedor para netear
            $dineroNeteo = new DineroContenedor();
            $dineroNeteo->id_contenedor = $dinero->id_contenedor;
            $dineroNeteo->id_banco = $dinero->id_banco;
            $dineroNeteo->motivo = 'Cancelación: ' . $dinero->motivo;
            $dineroNeteo->monto = -$montoPositivo;
            $dineroNeteo->fecha_entrega_monto = date('Y-m-d');
            $dineroNeteo->es_principal = $esPrincipal ? 1 : 0;
            $dineroNeteo->save();

            // 3. Reintegrar saldo al operador en asignaciones
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

            DB::commit();

            return response()->json([
                'success' => true,
                'Titulo' => $esPrincipal ? 'Dinero principal cancelado' : 'Dinero extra cancelado',
                'Mensaje' => $mensajeExito,
                'TMensaje' => 'success'
            ]);
        } catch (\Throwable $t) {
            DB::rollBack();
            Log::channel('daily')->error('Error en eliminarDineroViaje: ' . $t->getMessage());
            return response()->json([
                'success' => false,
                'Titulo' => 'Error al cancelar',
                'Mensaje' => 'Ocurrió un error al procesar la cancelación: ' . $t->getMessage(),
                'TMensaje' => 'error'
            ], 500);
        }
    }

    public function eliminarViatico(Request $request)
    {
        try {
            DB::beginTransaction();
            $idViatico = $request->get('idviatico');
            $viatico = ViaticosOperador::findOrFail($idViatico);

            $montoViatico = floatval($viatico->monto);
            $idCotizacion = $viatico->id_cotizacion;

            // 1. Localizar contenedor y descontar de restante_pago_operador
            $documCotizacion = DocumCotizacion::where('id_cotizacion', $idCotizacion)->first();
            if ($documCotizacion) {
                Asignaciones::where('id_contenedor', $documCotizacion->id)->update([
                    'restante_pago_operador' => DB::raw('COALESCE(restante_pago_operador, 0) - ' . $montoViatico)
                ]);
            }

            // 2. Cancelar Gasto unificado vinculado si existe
            $gastoOp = \App\Models\Gasto::where('origen_legacy', 'viaticos_operadores')
                ->where('origen_legacy_id', $viatico->id)
                ->first();

            if ($gastoOp) {
                foreach ($gastoOp->pagos()->where('estatus', '!=', 'cancelado')->get() as $pago) {
                    app(\App\Services\GastosService::class)->cancelarPago($pago, now()->format('Y-m-d'));
                }
                $gastoOp->estatus = 'cancelado';
                $gastoOp->save();
            }

            // 3. Cancelar excedente vinculado si existe
            $excedenteGasto = \App\Models\Gasto::where('origen_legacy', 'viaticos_operadores_excedente')
                ->where('origen_legacy_id', $viatico->id)
                ->first();

            if ($excedenteGasto) {
                foreach ($excedenteGasto->pagos()->where('estatus', '!=', 'cancelado')->get() as $pago) {
                    app(\App\Services\GastosService::class)->cancelarPago($pago, now()->format('Y-m-d'));
                }
                $excedenteGasto->estatus = 'cancelado';
                $excedenteGasto->save();
            }

            // 4. Eliminar el registro de ViaticosOperador
            $viatico->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'Titulo' => 'Viático eliminado',
                'Mensaje' => 'La justificación por $' . number_format($montoViatico, 2) . ' fue eliminada correctamente y el saldo del operador fue recalculado.',
                'TMensaje' => 'success'
            ]);
        } catch (\Throwable $t) {
            DB::rollBack();
            Log::channel('daily')->error('Error en eliminarViatico: ' . $t->getMessage());
            return response()->json([
                'success' => false,
                'Titulo' => 'Error al eliminar viático',
                'Mensaje' => 'Ocurrió un error: ' . $t->getMessage(),
                'TMensaje' => 'error'
            ], 500);
        }
    }

    public function aplicarPago(Request $request)
    {
        try {
            DB::beginTransaction();

            //  $prestamosVigentes = Prestamo::where('id_operador', $request->_IdOperador)->where('saldo_actual', '>', 0)->get();
            $prestamosVigentes = Prestamo::with(['operador', 'pagoprestamos'])
    ->where('id_operador', $request->_IdOperador)
    ->where('tipo', Prestamo::TIPO_PRESTAMO)
    ->get()
   ->filter(fn ($p) => $p->saldo_calculado > 0)
    ->values();

            //dd($prestamosVigentes);

            if ($request->totalPagoPrestamo > $prestamosVigentes->sum('saldo_calculado')) {
                return response()->json([
                    "Titulo" => "Acción rechazada",
                    "Mensaje" => "No se puede aplicar porque el saldo del operador es menor a la cantidad que intenta cobrar de prestamos",
                    "TMensaje" => "warning"
                ]);
            }

            //nuevo proceso de validacion de adelantos, abonos en vista se valida pero tambien aki

            $padelantosVigentes = Prestamo::with(['operador', 'pagoprestamos'])
    ->where('id_operador', $request->_IdOperador)
    ->where('tipo', Prestamo::TIPO_ADELANTO)
        ->get()
    ->filter(fn ($p) => $p->saldo_calculado > 0)
    ->values();


            if ($request->totalPagoAdelanto > $padelantosVigentes->sum('saldo_calculado')) {
                return response()->json([
                    "Titulo" => "Acción rechazada",
                    "Mensaje" => "No se puede aplicar porque el saldo del operador es menor a la cantidad que intenta cobrar de adelantos",
                    "TMensaje" => "warning"
                ]);
            }

            // $bancos = Bancos::where('id', '=', $request->bancoId)->first();
            $saldoActual = 0;
            $fechaAplicacionDinero = $request->get('FechaAplicacionPago');
            $totalPagar = $request->totalMontoPago ;//- $request->totalPagoPrestamo;
            if ($request->filled('bancoId') && $totalPagar > 0) {
                $idEmpresa = auth()->user()->id_empresa;


                $validarSaldos = $this->BancosService->validarsaldoparacargo($idEmpresa, $request->get('bancoId'), $fechaAplicacionDinero, $totalPagar);
                //  dd($validarSaldos);
                if ($validarSaldos["saldodisponible"] == false) {

                    return response()->json([
                                      "TMensaje" => "error",
                                   "Titulo" => "Saldo bancos",
                                      "Mensaje" => $validarSaldos["message"],
                                      'success' => false
                                     ]);

                }
            }
            // if ($totalPagar > $saldoActual) {
            //     return response()->json(["Titulo" => "Saldo insuficiente","Mensaje" => "No se puede aplicar el pago en la cuenta seleccionada","TMensaje" => "warning"]);
            // }

            $contenedores = collect($request->pagoContenedores);

            $liquidacion = new Liquidaciones();
            $liquidacion->id_operador = $request->_IdOperador;
            $liquidacion->id_banco = $request->bancoId;
            $liquidacion->fecha = \Carbon\Carbon::createFromFormat(
                'd/m/Y',
                $fechaAplicacionDinero
            )->format('Y-m-d');
            $liquidacion->viajes_realizados = $contenedores->count();
            $liquidacion->sueldo_operador = $contenedores->sum('SueldoViaje');
            $liquidacion->dinero_viaje = $contenedores->sum('DineroViaje');
            $liquidacion->dinero_justificado = $contenedores->sum('GastosJustificados');
            $liquidacion->pago_prestamos = $request->totalPagoPrestamo;
            $liquidacion->pago_adelantos = $request->totalPagoAdelanto;
            $liquidacion->total_pago = ($request->totalPagoPrestamo > 0) ? $contenedores->sum('MontoPago') - $request->totalPagoPrestamo : $contenedores->sum('MontoPago');
            $liquidacion->user_id = auth()->user()->id;
            $liquidacion->save();

            //Registrar abonos por orden de prestamo. Se abona la mayor cantidad posible al prestamo
            $pagoPrestamos = $request->totalPagoPrestamo;
            foreach ($prestamosVigentes as $p) {
                if ($pagoPrestamos > 0) {
                    $prestamo = Prestamo::where('id', $p->id)->first();
                    $prestamo->saldo_actual = ($pagoPrestamos > $p->saldo_actual) ? 0 : $p->saldo_actual - $pagoPrestamos;
                    $prestamo->save();

                    $detalle = [
                        'id_liquidacion' => $liquidacion->id,
                        'id_prestamo' => $p->id,
                        'saldo_anterior' => $p->saldo_actual,
                        'monto_pago' => ($pagoPrestamos > $p->saldo_actual) ? $p->saldo_actual : $pagoPrestamos,
                         // NUEVOS CAMPOS para tener los abonos por pago directo
                        'tipo_origen'     => 'liquidacion',
                        'id_banco'        => $request->bancoId,     // para mantener la relacion unicamente no se ejecuta en bancos
                        'referencia'      => 'liq #'. $liquidacion->id,     // no aplica en liquidación
                        'fecha_pago'      => \Carbon\Carbon::createFromFormat(
                            'd/m/Y',
                            $fechaAplicacionDinero
                        )->format('Y-m-d'),
                    ];

                    PagoPrestamo::create($detalle);

                    $pagoPrestamos = ($pagoPrestamos > $p->saldo_actual) ? $pagoPrestamos - $p->saldo_actual : 0;



                }
            }


            //nuevo modalidad adelanto

            $pagoAdelanto = $request->totalPagoAdelanto;
            foreach ($padelantosVigentes as $p) {
                if ($pagoAdelanto > 0) {
                    $prestamo = Prestamo::where('id', $p->id)->first();
                    $prestamo->saldo_actual = ($pagoAdelanto > $p->saldo_actual) ? 0 : $p->saldo_actual - $pagoAdelanto;
                    $prestamo->save();

                    $detalle = [
                        'id_liquidacion' => $liquidacion->id,
                        'id_prestamo' => $p->id,
                        'saldo_anterior' => $p->saldo_actual,
                        'monto_pago' => ($pagoAdelanto > $p->saldo_actual) ? $p->saldo_actual : $pagoAdelanto,
                         // NUEVOS CAMPOS para tener los abonos por pago directo
                        'tipo_origen'     => 'liquidacion',
                        'id_banco'        => $request->bancoId,    // para mantener la relacion unicamente no se ejecuta en bancos
                        'referencia'      => 'liq #'. $liquidacion->id,     // no aplica en liquidación
                        'fecha_pago'      => \Carbon\Carbon::createFromFormat(
                            'd/m/Y',
                            $fechaAplicacionDinero
                        )->format('Y-m-d'),
                    ];

                    PagoPrestamo::create($detalle);

                    $pagoPrestamos = ($pagoPrestamos > $p->saldo_actual) ? $pagoPrestamos - $p->saldo_actual : 0;



                }
            }



            $contenedores->map(function ($contenedor) use ($liquidacion) {
                $cont = [
                        'id_liquidacion' => $liquidacion->id,
                        'id_contenedor' => $contenedor['IdContenedor'] ,
                        'sueldo_operador' => $contenedor['SueldoViaje'] ,
                        'dinero_viaje' => $contenedor['DineroViaje'] ?? 0,
                        'dinero_justificado' => $contenedor['GastosJustificados'] ,
                        'total_pagado' => $contenedor['MontoPago'] ,
                        ];
                LiquidacionContenedor::insert($cont);
            });

            $contenedoresAbonos = [];
            $unidadesLiquidacionMap = [];

            foreach ($contenedores as $c) {
                $asignacion = Asignaciones::where('id', '=', $c['IdAsignacion'])->first();
                $saldoContenedor = $asignacion->restante_pago_operador;
                $asignacion->restante_pago_operador = $saldoContenedor - $c['MontoPago'];
                $asignacion->fecha_pago_operador = \Carbon\Carbon::createFromFormat(
                    'd/m/Y',
                    $fechaAplicacionDinero
                )->format('Y-m-d');

                if (($saldoContenedor - $c['MontoPago']) <= 0) {
                    $asignacion->estatus_pagado = 'Pagado';
                }
                $asignacion->update();

                // Buscar gastos de viáticos/excedentes asociados a esta asignación que estén pendientes
                $gastosAsociados = \App\Models\Gasto::where('estatus', 'pendiente_pago')
                    ->whereIn('origen_legacy', ['viaticos_operadores', 'viaticos_operadores_excedente'])
                    ->whereHas('vinculos', function ($query) use ($asignacion) {
                        $query->where('tipo_vinculo', 'asignacion')
                              ->where('vinculable_id', $asignacion->id);
                    })
                    ->get();

                foreach ($gastosAsociados as $gasto) {
                    $gasto->update(['estatus' => 'pagado']);

                    // Crear GastoPago si no existe
                    $existePago = \App\Models\GastoPago::where('gasto_id', $gasto->id)->exists();
                    if (!$existePago) {
                        $cuentaBancariaId = null;
                        if ($gasto->origen_legacy === 'viaticos_operadores_excedente') {
                            $cuentaBancariaId = $request->bancoId;
                        } else {
                            $cuentaBancariaId = $asignacion->id_banco1_dinero_viaje ?? $request->bancoId;
                        }

                        if ($cuentaBancariaId) {
                            $existeBanco = \DB::table('bancos')->where('id', $cuentaBancariaId)->exists();
                            if (!$existeBanco) {
                                $cuentaBancariaId = null;
                            }
                        }

                        \App\Models\GastoPago::create([
                            'gasto_id' => $gasto->id,
                            'cuenta_bancaria_id' => $cuentaBancariaId,
                            'fecha_pago' => \Carbon\Carbon::createFromFormat('d/m/Y', $fechaAplicacionDinero)->format('Y-m-d'),
                            'monto' => $gasto->monto_total,
                            'estatus' => 'aplicado',
                            'user_id' => auth()->id(),
                        ]);
                    }
                }

                $cotizacion = DocumCotizacion::where('id', '=', $asignacion->id_contenedor)->first();

                $asignacion->loadMissing('Camion');
                $equipoId = $asignacion->id_camion;
                $equipoNombre = $asignacion->Camion ? ($asignacion->Camion->id_equipo ?: $asignacion->Camion->placas) : null;
                if ($equipoId) {
                    $unidadesLiquidacionMap[$equipoId] = $equipoNombre;
                }

                $contenedoresAbonos[] = [
                    'num_contenedor' => $cotizacion->num_contenedor,
                    'abono' => $c['MontoPago'],
                    'id_equipo' => $equipoId,
                    'unidad' => $equipoNombre,
                ];

            }

            //bancos nuevo registrar movi
            if ($request->filled('bancoId') &&  $contenedores->sum('MontoPago') > 0) {
                $FechaAplicacionPago = $request->get('FechaAplicacionPago');

                $operadorN = Operador::find($request->_IdOperador);
                $unidadConcepto = count($unidadesLiquidacionMap) === 1 ? reset($unidadesLiquidacionMap) : null;

                $data = [
                        'cuenta_bancaria_id' => $request->get('bancoId'),
                        'tipo' => 'cargo',
                        'monto' => floatval($totalPagar),
                        'concepto' => \App\Services\BancosService::generarConcepto('viaje', 'Pago Operador / Liquidación', null, $operadorN?->nombre ?? null, $unidadConcepto),
                        'fecha_movimiento' => \Carbon\Carbon::createFromFormat(
                            'd/m/Y',
                            $FechaAplicacionPago
                        )->format('Y-m-d'),
                        'origen' => null,
                        'referencia' => 'Liquidacion',
                        'detalles' =>  $contenedoresAbonos,
                         'referenciaable_id' => $liquidacion->id,
                          'referenciaable_type' => \App\Models\Liquidaciones::class, //para polimorfismo
                    ];

                $movimeintoCrear = $this->BancosService->registrarMovimiento($data);
                //   dd('no pasar', $movimeintoCrear);

                if (!$movimeintoCrear) {
                    throw new \Exception('No se pudo crear el movimiento bancario, liquidacion operador  ');
                }


            }
            //termina nuevo bancos


            DB::commit();

            return response()->json(["Titulo" => "Pago aplicado","Mensaje" => "Se aplicó el pago correctamente al operador","TMensaje" => "success", "IdLiquidacion" => $liquidacion->id]);
        } catch (\Throwable $t) {
            DB::rollback();
            Log::channel('daily')->info('LiquidacionesController->aplicarPago:'.$t->getMessage());
            return response()->json(["Titulo" => "Pago NO aplicado","Mensaje" => "Ocurrio un error al aplicar el pago: ".$t->getMessage(),"TMensaje" => "error"]);

        }
    }

    public function comprobantePago(Request $r)
    {
        $liquidacionViajes = Liquidaciones::with([
    'viajes.Contenedores',
    'Operadores',
    'Banco'
])->findOrFail($r->IdOperacion);

        $user = Auth::user();

        $idViajes = $liquidacionViajes->viajes->pluck('id_contenedor'); //quedo con la relacion correcta  del contendor id , y se agrego el cotizaciones para saber los viaticos
        //correccion de fk column es contenendor apunta a id de doc cotizacion entonces  el array no puedo usarlo para cotizacion sera enytonces asi y funciona

        $cotizaciones = DocumCotizacion::whereIn('id', $idViajes)->get()->pluck('id_cotizacion');


        $viaticos = ViaticosOperador::whereIn('id_cotizacion', $cotizaciones)->get();



        $dineroViaje = DineroContenedor::whereIn('id_contenedor', $idViajes)
                        ->orderBy('id_contenedor')
                        ->get();

        $prestamosPagados = DB::table('prestamos')
            ->leftJoin('pagos_prestamos', function ($join) use ($r) {
                $join->on('prestamos.id', '=', 'pagos_prestamos.id_prestamo')
                     ->where('pagos_prestamos.id_liquidacion', '<>', $r->IdOperacion);
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

        $abonoprestamosLiquidacion = PagoPrestamo::where('id_liquidacion', $r->IdOperacion)->get();

        // dd($prestamosPagados);

        $totales = [
            'sueldo' => $liquidacionViajes->viajes->sum('sueldo_operador'),
            'dinero_viaje' => $liquidacionViajes->viajes->sum('dinero_viaje'),
            'justificado' => $liquidacionViajes->viajes->sum('dinero_justificado'),
            'adelantos' => $liquidacionViajes->pago_adelantos,
            'deudas' => $liquidacionViajes->pago_prestamos ,
            'pagado' => $liquidacionViajes->viajes->sum('total_pagado') - ($liquidacionViajes->pago_prestamos + $liquidacionViajes->pago_adelantos),
        ];
        //dd($liquidacionViajes);

        $pdf = PDF::loadView('liquidaciones.pdf', compact(
            'user',
            'viaticos',
            'dineroViaje',
            'totales',
            'prestamosPagados',
            'abonoprestamosLiquidacion',
            'liquidacionViajes'
        ));
        return $pdf->stream('utilidades_rpt.pdf');
    }

    public function historialPagos()
    {
        $equipos = $this->equipoService->getTractocamiones();

        return view('liquidaciones.historial', compact('equipos'));
    }

    public function historialPagosData(Request $r)
    {
        $idOperadores = Operador::where('id_empresa', Auth::User()->id_empresa)->get()->pluck('id');
        $fechaI = $r->startDate;
        $fechaF = $r->endDate;
        $numContenedor = $r->num_contenedor;
        $idEquipo = $r->id_equipo;

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

        $mapHistory = $historial->map(function ($h) {
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
        });

        return response()->json(["TMensaje" => "success", "data" => $mapHistory]);

    }

    public function vistaPrevia(Request $request)
    {
        $viajes = Asignaciones::with([
            'Contenedor.Cotizacion',
            'Prestamos',
            'Justificaciones'
        ])
        ->whereIn('id', $request->viajes)
        ->get();

        $totalViajes = $viajes->sum('monto_viaje');
        $totalPrestamos = $viajes->flatMap->Prestamos->sum('monto');
        $totalJustificaciones = $viajes->flatMap->Justificaciones->sum('monto');

        return view('liquidaciones.vista-previa', compact(
            'viajes',
            'totalViajes',
            'totalPrestamos',
            'totalJustificaciones'
        ));
    }

    public function vistaPrevialiqu(Request $r)
    {
        $viajes = $r->viajes;

        $idCotizacion = DocumCotizacion::whereIn('id', $viajes)->get()->pluck('id_cotizacion');

        $viaticos = ViaticosOperador::whereIn('id_cotizacion', $idCotizacion)->get();

        $dineroViaje = DineroContenedor::with('DocCotizacion')
            ->whereIn('id_contenedor', $viajes)
            ->orderBy('id_contenedor')
            ->get()->map(function ($dinero) {
                return [
                    'num_contenedor' => $dinero->DocCotizacion->num_contenedor ?? 'N/A',
                    'monto' => $dinero->monto,
                    'motivo' => $dinero->motivo,
                    'fecha_entrega_monto' => $dinero->fecha_entrega_monto,
                ];
            });



        // Obtener préstamo pendiente REAL del operador
        $prestamoPendiente = Prestamo::with('pagoprestamos')
         ->where('id_operador', $r->operador_id)
         ->where('tipo', Prestamo::TIPO_PRESTAMO)
         ->get()
         ->filter(function ($prestamo) {
             return $prestamo->saldo_calculado > 0;
         })
         ->sum('saldo_calculado');



        $adelantoPendiente = Prestamo::with('pagoprestamos')
                ->where('id_operador', $r->operador_id)
                ->where('tipo', Prestamo::TIPO_ADELANTO)
                ->get()
                ->filter(function ($prestamo) {
                    return $prestamo->saldo_calculado > 0;
                })
                ->sum('saldo_calculado');

        //sacar prestamos y adelantos pendientes detalles para vista previa
        $prestamosadelantosDetalles = Prestamo::with(['operador', 'pagoprestamos'])
       ->where('id_operador', $r->operador_id)
    ->get()
->filter(function ($prestamo) {
    return $prestamo->saldo_calculado > 0;
}) ->values()
        ;



        return response()->json([
        'justificados' => $viaticos,
        'prestamo_pendiente' => $prestamoPendiente,
        'adelanto_pendiente' => $adelantoPendiente,
        'prestamosadelantosDetalles' => $prestamosadelantosDetalles,
                'dinero_viaje' => $dineroViaje
    ]);
    }


    public function deleteHistorialPago($id)
    {

        try {

            DB::beginTransaction();

            $liquidacion = Liquidaciones::find($id);

            if (!$liquidacion) {
                return response()->json([
                    "TMensaje" => "error",
                    "Titulo" => "Liquidaciones",
                    "Mensaje" => "Liquidación no encontrada"
                ]);
            }

            /*
            |---------------------------------------------
            | 1. REGRESAR SALDO A PRESTAMOS
            |---------------------------------------------
            */

            $pagos = PagoPrestamo::where('id_liquidacion', $id)->get();

            foreach ($pagos as $pago) {

                $prestamo = Prestamo::find($pago->id_prestamo);

                if ($prestamo) {
                    $prestamo->saldo_actual += $pago->monto_pago;
                    $prestamo->save();
                }
            }

            /*
            |---------------------------------------------
            | 2. REGRESAR SALDO A ASIGNACIONES
            |---------------------------------------------
            */

            $contenedores = LiquidacionContenedor::where('id_liquidacion', $id)->get();


            foreach ($contenedores as $c) {

                $asignacion = Asignaciones::where('id_contenedor', $c->id_contenedor)->first();

                if ($asignacion) {
                    // Restar lo justificado y sumar lo pagado para regresar al saldo original antes de justificaciones y pago
                    $asignacion->restante_pago_operador = ($asignacion->restante_pago_operador - $c->dinero_justificado) + $c->total_pagado;

                    if ($asignacion->restante_pago_operador > 0) {
                        $asignacion->estatus_pagado = 'Pendiente Pago';
                    }

                    $asignacion->save();

                    // 1. Eliminar justificaciones de la tabla viaticos_operadores
                    $documCotizacion = DocumCotizacion::find($c->id_contenedor);
                    if ($documCotizacion) {
                        ViaticosOperador::where('id_cotizacion', $documCotizacion->id_cotizacion)->delete();
                    }

                    // 2. Buscar y eliminar por completo los gastos de viáticos y excedentes asociados a este viaje
                    $gastosAsociados = \App\Models\Gasto::whereIn('origen_legacy', ['viaticos_operadores', 'viaticos_operadores_excedente'])
                        ->whereHas('vinculos', function ($query) use ($asignacion) {
                            $query->where('tipo_vinculo', 'asignacion')
                                  ->where('vinculable_id', $asignacion->id);
                        })
                        ->get();

                    foreach ($gastosAsociados as $gasto) {
                        // Eliminar pagos del gasto
                        \App\Models\GastoPago::where('gasto_id', $gasto->id)->delete();
                        // Forzar la eliminación del gasto
                        $gasto->forceDelete();
                    }
                }
            }

            /*
            |---------------------------------------------
            | 3. REGRESAR DINERO AL BANCO
            |---------------------------------------------
            */

            if ($liquidacion->id_banco) {

                Bancos::where('id', $liquidacion->id_banco)
                    ->update([
                        "saldo" => DB::raw("saldo + ".$liquidacion->total_pago)
                    ]);
            }

            /*
            |---------------------------------------------
            | 4. BORRAR MOVIMIENTOS BANCARIOS
            |---------------------------------------------
            */

            CatBancoCuentasMovimientos::where('referenciaable_id', $liquidacion->id)
                ->where('referenciaable_type', Liquidaciones::class)
                ->delete();

            /*
            |---------------------------------------------
            | 5. BORRAR REGISTRO BANCO OPERADOR
            |---------------------------------------------
            */

            // BancoDineroOpe::where('referencia', 'Liquidacion')
            //     ->where('id_operador', $liquidacion->id_operador)
            //     ->whereDate('fecha_pago', $liquidacion->fecha)
            //     ->delete();

            /*
            |---------------------------------------------
            | 6. BORRAR PAGOS PRESTAMOS
            |---------------------------------------------
            */

            PagoPrestamo::where('id_liquidacion', $id)->delete();

            /*
            |---------------------------------------------
            | 7. BORRAR CONTENEDORES
            |---------------------------------------------
            */

            LiquidacionContenedor::where('id_liquidacion', $id)->delete();

            /*
            |---------------------------------------------
            | 8. BORRAR LIQUIDACION
            |---------------------------------------------
            */

            $liquidacion->delete();

            DB::commit();

            return response()->json([
                "TMensaje" => "success",
                "Titulo" => "Liquidaciones",
                "Mensaje" => "Liquidación eliminada correctamente"
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                "TMensaje" => "error",
                "Titulo" => "Error",
                "Mensaje" => $e->getMessage()
            ]);
        }
    }

}
