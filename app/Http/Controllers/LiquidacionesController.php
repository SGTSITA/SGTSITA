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

class LiquidacionesController extends Controller
{
    protected $BancosService;

    public function __construct(BancosService $BancosService)
    {
        $this->BancosService = $BancosService;
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
    > 0
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

        $subJustificados = DB::table('viaticos_operadores as v')
    ->join('docum_cotizacion as dc', 'v.id_cotizacion', '=', 'dc.id_cotizacion')
    ->select(
        'dc.id as id_contenedor',
        DB::raw('SUM(monto) as total_justificado')
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

->having('saldo_real', '>', 0)

->get();



        $justificaciones = DB::table('viaticos_operadores as v')
    ->join('docum_cotizacion as dc', 'v.id_cotizacion', '=', 'dc.id_cotizacion')
    ->select(
        'dc.id as id_contenedor',
        'v.id',
        'v.descripcion_gasto',
        'v.monto'
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
            ViaticosOperador::insert([
                                        "id_cotizacion" => $documCotizacion->id_cotizacion,
                                        "descripcion_gasto" => $r->txtDescripcion,
                                        "monto" => $r->montoJustificacion
                                    ]);

            $asignacion = Asignaciones::where('id_contenedor', $documCotizacion->id)->update([
                "restante_pago_operador" => DB::raw('restante_pago_operador + '.$r->montoJustificacion)
            ]);

            //Los gastos que sean justificados por el operador, deberan reflejarse en el viaje correspondiente

            $asignacion = Asignaciones::where('id_contenedor', $documCotizacion->id)->first();

            $datosGasto = [
                           "id_cotizacion" => $documCotizacion->id_cotizacion,
                           "id_banco" => null,
                           "id_asignacion" => $asignacion->id,
                           "id_operador" => $asignacion->id_operador,
                           "cantidad" => ($r->montoJustificacion > $r->sinJustificar) ? $r->sinJustificar : $r->montoJustificacion, //Registrar solo el monto por justificar del dinero entregado para viaje, el excedente se registra en otra partida
                           "tipo" => $r->txtDescripcion,
                           "estatus" =>  'Pagado' ,
                           "fecha_pago" => null,
                           "pago_inmediato" => 1,
                           "created_at" => Carbon::now()
                          ];

            GastosOperadores::insert($datosGasto);

            //Registramos el excedente en una nueva partida y lo dejamos como pendiente de pago
            if ($r->montoJustificacion > $r->sinJustificar) {
                $datosGasto = [
                    "id_cotizacion" => $documCotizacion->id_cotizacion,
                    "id_banco" => null,
                    "id_asignacion" => $asignacion->id,
                    "id_operador" => $asignacion->id_operador,
                    "cantidad" => $r->montoJustificacion - $r->sinJustificar, //Registrar solo el monto por justificar del dinero entregado para viaje, el exedente se registra en otra partida
                    "tipo" => $r->txtDescripcion . "** Excedente",
                    "estatus" =>  'Pago Pendiente' ,
                    "fecha_pago" => null,
                    "pago_inmediato" => 0,
                    "created_at" => Carbon::now()
                   ];

                GastosOperadores::insert($datosGasto);
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
            //dd($filas);

            foreach ($filas as $item) {
                $documCotizacion = DocumCotizacion::where('id', $item['IdContenedor'])
                    ->where('id_empresa', $idEmpresa)
                    ->first();

                if (!$documCotizacion) {
                    continue;
                }

                if (!empty($item['idviatico'])) { // Actualizar registro existente
                    $viaticoAntes = ViaticosOperador::where('id', $item['idviatico'])->first();
                    $montoAntes = $viaticoAntes->monto;
                    ViaticosOperador::where('id', $item['idviatico'])
                        ->update([
                            "descripcion_gasto" => $item['motivo'],
                            "monto" => $item['monto'],
                        ]);

                    $diferencia = $item['monto'] - $montoAntes;
                    Asignaciones::where('id_contenedor', $documCotizacion->id)
                        ->update([
                            "restante_pago_operador" => DB::raw('restante_pago_operador + ' . $diferencia)
                        ]);

                } else {
                    // Nuevo registro
                    ViaticosOperador::insert([
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

                $datosGasto = [
                    "id_cotizacion" => $documCotizacion->id_cotizacion,
                    "id_banco" => null,
                    "id_asignacion" => $asignacion->id,
                    "id_operador" => $asignacion->id_operador,
                    "cantidad" => ($montoJustificacion > $sinJustificar) ? $sinJustificar : $montoJustificacion,
                    "tipo" => $item['motivo'],
                    "estatus" => 'Pagado',
                    "fecha_pago" => null,
                    "pago_inmediato" => 1,
                    "created_at" => Carbon::now(),
                ];

                GastosOperadores::insert($datosGasto);

                // Si excede
                if ($montoJustificacion > $sinJustificar) {
                    $excedente = [
                        "id_cotizacion" => $documCotizacion->id_cotizacion,
                        "id_banco" => null,
                        "id_asignacion" => $asignacion->id,
                        "id_operador" => $asignacion->id_operador,
                        "cantidad" => $montoJustificacion - $sinJustificar,
                        "tipo" => $item['motivo'] . " **Excedente",
                        "estatus" => 'Pago Pendiente',
                        "fecha_pago" => null,
                        "pago_inmediato" => 0,
                        "created_at" => Carbon::now(),
                    ];

                    GastosOperadores::insert($excedente);
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
            $dineroViaje->fecha_entrega_monto = date('Y-m-d');
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
                        'concepto' => 'Dinero para viaje: '.$request->txtDescripcion .' - ' .'
                                '.  $operadorN?->nombre ??  '',
                        'fecha_movimiento' => \Carbon\Carbon::createFromFormat(
                            'd/m/Y',
                            $fechaAplicacionDinero
                        )->format('Y-m-d'),
                        'origen' => null,
                        'referencia' => $request->numContenedor,
                        'detalles' => json_encode($contenedoresAbonos),
                         'referenciaable_id' => $asignacion->id,
                          'referenciaable_type' => \App\Models\Asignaciones::class, //para polimorfismo
                    ];




                $movimeintoCrear = $this->BancosService->registrarMovimiento($data);
                //   dd('no pasar', $movimeintoCrear);

                if (!$movimeintoCrear) {
                    throw new \Exception('No se pudo crear el movimiento bancario, dinero para viaje adicional ');
                }


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
            $totalPagar = $request->totalMontoPago - $request->totalPagoPrestamo;
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
                        'id_banco'        => null,     // no aplica en liquidación
                        'referencia'      => null,     // no aplica en liquidación
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
                        'id_banco'        => null,     // no aplica en liquidación
                        'referencia'      => null,     // no aplica en liquidación
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

                $cotizacion = DocumCotizacion::where('id', '=', $asignacion->id_contenedor)->first();



                $contenedoresAbonos[] = [
                    'num_contenedor' => $cotizacion->num_contenedor,
                    'abono' => $c['MontoPago']
                ];
                $contenedoresAbonosJson = json_encode($contenedoresAbonos);

            }

            $banco = new BancoDineroOpe();
            $banco->id_operador = $request->_IdOperador;

            $banco->monto1 = $totalPagar;
            $banco->metodo_pago1 = 'Transferencia';
            $banco->descripcion_gasto = 'Pago operador';
            $banco->id_banco1 = $request->bancoId;
            $banco->contenedores = $contenedoresAbonosJson;

            $banco->tipo = 'Salida';
            $banco->fecha_pago = \Carbon\Carbon::createFromFormat(
                'd/m/Y',
                $fechaAplicacionDinero
            )->format('Y-m-d');
            $banco->save();

            Bancos::where('id', '=', $request->bancoId)->update(["saldo" => DB::raw("saldo - ". $request->totalMontoPago)]);


            //bancos nuevo registrar movi

            if ($request->filled('bancoId') &&  $contenedores->sum('MontoPago') > 0) {
                $FechaAplicacionPago = $request->get('FechaAplicacionPago');

                $operadorN = Operador::find($request->_IdOperador);

                $data = [
                        'cuenta_bancaria_id' => $request->get('bancoId'),
                        'tipo' => 'cargo',
                        'monto' => floatval($totalPagar),
                        'concepto' => 'Pago Operador: ' .'
                                '.  $operadorN?->nombre ??  '',
                        'fecha_movimiento' => \Carbon\Carbon::createFromFormat(
                            'd/m/Y',
                            $FechaAplicacionPago
                        )->format('Y-m-d'),
                        'origen' => null,
                        'referencia' => 'Liquidacion',
                        'detalles' =>  $contenedoresAbonosJson,
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

        $idViajes = $liquidacionViajes->viajes->pluck('id_contenedor'); //deberia ser id_cotizacion pero la relacion esta con esa columna en liquidacioneconenedor checar eso despues

        $viaticos = ViaticosOperador::whereIn('id_cotizacion', $idViajes)->get();

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

        return view('liquidaciones.historial');
    }

    public function historialPagosData(Request $r)
    {
        $idOperadores = Operador::where('id_empresa', Auth::User()->id_empresa)->get()->pluck('id');
        $fechaI = $r->startDate;
        $fechaF = $r->endDate;

        $historial = Liquidaciones::whereIn('id_operador', $idOperadores)->whereBetween('fecha', [$fechaI, $fechaF])->get();

        $mapHistory = $historial->map(function ($h) {
            return
            [
                "IdPago" => $h->id,
                "IdOperador" => $h->id_operador,
                "IdBanco" => $h->id_banco,
                "Operador" => $h->operadores->nombre ,
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

                    $asignacion->restante_pago_operador += $c->total_pagado;

                    if ($asignacion->restante_pago_operador > 0) {
                        $asignacion->estatus_pagado = 'Pendiente Pago';
                    }

                    $asignacion->save();
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
