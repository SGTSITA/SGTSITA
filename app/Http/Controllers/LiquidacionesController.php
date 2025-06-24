<?php

namespace App\Http\Controllers;

use App\Models\Asignaciones;
use App\Models\BancoDineroOpe;
use App\Models\Bancos;
use App\Models\DocumCotizacion;
use App\Models\GastosOperadores;
use App\Models\Cotizaciones;
use App\Models\Operador;
use App\Models\Liquidaciones;
use App\Models\LiquidacionContenedor;
use App\Models\ViaticosOperador;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Log;
use Auth;
use PDF;


class LiquidacionesController extends Controller
{
    public function index(){
       return view('liquidaciones.index');
    }

    public function getPagosOperadores(Request $r){
        $asignacion_operador = Asignaciones::join('operadores', 'asignaciones.id_operador', '=', 'operadores.id')
        ->where('asignaciones.id_empresa', '=',auth()->user()->id_empresa)
        ->where('asignaciones.id_proveedor', '=', NULL)
        ->where('asignaciones.estatus_pagado', '=', 'Pendiente Pago')
        ->where('asignaciones.restante_pago_operador', '>', '0')
        ->select('asignaciones.id_operador', DB::raw('COUNT(*) as total_cotizaciones'), DB::raw('SUM(sueldo_viaje) as sueldo_viaje'),DB::raw('SUM(dinero_viaje) as dinero_viaje'),DB::raw('SUM(restante_pago_operador) as total_pago'))
        ->groupBy('asignaciones.id_operador')
        ->get();

        $datosPago = $asignacion_operador->map(function($ope){
            return [
                "IdOperador" => $ope->id_operador,
                "Operador" => (!is_null($ope->operador)) ?$ope->operador->nombre: 'N/A',
                "SueldoViaje" => $ope->sueldo_viaje,
                "DineroViaje" => $ope->dinero_viaje,
                "MontoPago" => $ope->total_pago,
                "ViajesRealizados" => $ope->total_cotizaciones
            ];
        });

        return $datosPago;
    }

    public function show($id){
        $operador = Operador::where('id', '=', $id)->where('id_empresa', '=',auth()->user()->id_empresa)->first();
        $bancos = Bancos::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();

        if(is_null($operador)){
            return "No existe información";
        }

        return view('liquidaciones.show', compact('operador','id','bancos'));
    }

    public function getViajesOperador(Request $r){
        $asignacion_operador = Asignaciones::where('id_empresa', '=',auth()->user()->id_empresa)
        ->where('estatus_pagado', '=', 'Pendiente Pago')
        ->where('restante_pago_operador', '>', '0')
        ->where('id_proveedor', '=', NULL)
        ->where('id_operador', '=', $r->operador)
        ->get();

        $contenedores = $asignacion_operador->map(function($c){
            
            $numContenedor = $c->contenedor->num_contenedor;

            if(!is_null($c->contenedor->cotizacion->referencia_full)){
                $secundaria = Cotizaciones::where('referencia_full', $c->contenedor->cotizacion->referencia_full)
                        ->where('jerarquia', 'Secundario')
                        ->with('DocCotizacion.Asignaciones')
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
                "ContenedorPrincipal" => $c->contenedor->num_contenedor,
                "SueldoViaje" => $c->sueldo_viaje,
                "DineroViaje" => $c->dinero_viaje,
                "GastosJustificados" => (!is_null($c->justificacion)) ? $c->justificacion->sum('monto') : 0,
                "MontoPago" => $c->restante_pago_operador,
                "FechaInicia" => $c->fecha_inicio,
                "FechaTermina" => $c->fecha_fin
            ];
        });

        $totalPago = $asignacion_operador->sum('restante_pago_operador');
        $numeroViajes = $asignacion_operador->count();

        $bancos = Bancos::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();
        $gastos_ope = GastosOperadores::where('id_operador', '=', $r->operador)->get();

        return response()->json(["viajes" => $contenedores, "totalPago" => $totalPago, "numViajes" => $numeroViajes, "data" => $asignacion_operador]);
    }

    public function justificarGastos(Request $r){
        try{
            DB::beginTransaction();
            $idEmpresa = auth()->user()->id_empresa;
            $documCotizacion = DocumCotizacion::where('num_contenedor',$r->numContenedor)
                                              ->where('id_empresa', $idEmpresa)
                                              ->first();
            ViaticosOperador::insert([
                                        "id_cotizacion" => $documCotizacion->id_cotizacion,
                                        "descripcion_gasto" => $r->txtDescripcion,
                                        "monto" => $r->montoJustificacion
                                    ]);

            $asignacion = Asignaciones::where('id_contenedor',$documCotizacion->id)->update([
                "restante_pago_operador" => DB::raw('restante_pago_operador + '.$r->montoJustificacion)
            ]);

            //Los gastos que sean justificados por el operador, deberan reflejarse en el viaje correspondiente
                                                
             $asignacion = Asignaciones::where('id_contenedor', $documCotizacion->id)->first();

            /*if($r->pagoInmediato != "false" ){
                //validar que el banco tenga saldo suficiente para efectuar el pago del gasto
                $bancos = Bancos::where('id_empresa',Auth::User()->id_empresa)->where('id',$r->bancoPago)->first();
                $saldoActual = $bancos->saldo;

                if($saldoActual < $r->montoGasto){
                    return response()->json(["Titulo" => "Saldo insuficiente en Banco","Mensaje" => "La cuenta bancaria seleccionada no cuenta con saldo suficiente para registrar esta transacción","TMensaje" => "warning"]);
                }

                Bancos::where('id' ,'=',$r->bancoPago)->update(["saldo" => DB::raw("saldo - ". $r->montoGasto)]);

                $banco = new BancoDineroOpe;
                $banco->id_operador = $asignacion->id_operador;

                $banco->monto1 = $r->montoGasto;
                $banco->metodo_pago1 = 'Transferencia';
                $banco->descripcion_gasto = "Gasto ope: ".$r->descripcion;
                $banco->id_banco1 = $r->bancoPago;

                $contenedoresAbonos[] = [
                    'num_contenedor' => $r->numContenedor,
                    'abono' => $r->montoGasto
                ];
                $contenedoresAbonosJson = json_encode($contenedoresAbonos);

                $banco->contenedores = $contenedoresAbonosJson;

                $banco->tipo = 'Salida';
                $banco->fecha_pago = date('Y-m-d');
                $banco->save();
             }*/

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
             if($r->montoJustificacion > $r->sinJustificar){
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
        }catch(\Throwable $t){
            DB::rollback();
            $uniqid = uniqid();
            Log::channel('daily')->info("Codigo: $uniqid, Error: LiquidacionesController/justificarGastos ".$t->getMessage());
            return response()->json(["Titulo" =>"Ha ocurrido un problema", "Mensaje" => "Codigo error: $uniqid. Mensaje: ".$t->getMessage(), "TMensaje" => "error"]);

        }
    }

    public function aplicarPago(Request $request){
        try{
            DB::beginTransaction();
            $bancos = Bancos::where('id' ,'=',$request->bancoId)->first();
            $saldoActual = $bancos->saldo;
            if($request->totalMontoPago > $saldoActual){
                return response()->json(["Titulo" => "Saldo insuficiente","Mensaje" => "No se puede aplicar el pago en la cuenta seleccionada","TMensaje" => "warning"]);
            }

            $contenedores = collect($request->pagoContenedores);

            $liquidacion = new Liquidaciones;
            $liquidacion->id_operador = $request->_IdOperador;
            $liquidacion->id_banco = $request->bancoId;
            $liquidacion->fecha = Carbon::now()->format('Y-m-d');
            $liquidacion->viajes_realizados = $contenedores->count();
            $liquidacion->sueldo_operador = $contenedores->sum('SueldoViaje');
            $liquidacion->dinero_viaje = $contenedores->sum('DineroViaje');
            $liquidacion->dinero_justificado = $contenedores->sum('GastosJustificados');
            $liquidacion->total_pago = $contenedores->sum('MontoPago');
            $liquidacion->save();

            $contenedores->map(function($contenedor) use ($liquidacion){
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

            foreach($contenedores as $c){
                $asignacion = Asignaciones::where('id', '=', $c['IdAsignacion'])->first();
                $saldoContenedor = $asignacion->restante_pago_operador;
                $asignacion->restante_pago_operador = $saldoContenedor - $c['MontoPago'];
                $asignacion->fecha_pago_operador = date('Y-m-d');

                if(($saldoContenedor - $c['MontoPago']) == 0){
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

            $banco = new BancoDineroOpe;
            $banco->id_operador = $request->_IdOperador;
            
            $banco->monto1 = $contenedores->sum('MontoPago');;
            $banco->metodo_pago1 = 'Transferencia';
            $banco->descripcion_gasto = 'Pago operador';
            $banco->id_banco1 = $request->bancoId;
            $banco->contenedores = $contenedoresAbonosJson;
        
            $banco->tipo = 'Salida';
            $banco->fecha_pago = date('Y-m-d');
            $banco->save();

            Bancos::where('id' ,'=',$request->bancoId)->update(["saldo" => DB::raw("saldo - ". $request->totalMontoPago)]);
            DB::commit();

            return response()->json(["Titulo" => "Pago aplicado","Mensaje" => "Se aplicó el pago correctamente al operador","TMensaje" => "success"]);
        }catch(\Throwable $t){
            DB::rollback();
            Log::channel('daily')->info('LiquidacionesController->aplicarPago:'.$t->getMessage());
            return response()->json(["Titulo" => "Pago NO aplicado","Mensaje" => "Ocurrio un error al aplicar el pago: ".$t->getMessage(),"TMensaje" => "error"]);

        }
    }

    public function comprobantePago(Request $r){
        $liquidacion = Liquidaciones::where('id',$r->IdOperacion)->first();
        $user = Auth::User();

        $idViajes = $liquidacion->viajes->pluck('id_contenedor');
        $viaticos = ViaticosOperador::whereIn('id_cotizacion',$idViajes)->get();

        $pdf = PDF::loadView('liquidaciones.pdf', compact('liquidacion','user','viaticos'));
        return $pdf->stream('utilidades_rpt.pdf');
    }

   public function historialPagos(){
    
    return view('liquidaciones.historial');
   }

   public function historialPagosData(Request $r){
    $idOperadores = Operador::where('id_empresa',Auth::User()->id_empresa)->get()->pluck('id');
    $fechaI = $r->startDate;
    $fechaF = $r->endDate;

    $historial = Liquidaciones::whereIn('id_operador',$idOperadores)->whereBetween('fecha',[$fechaI, $fechaF])->get();

    $mapHistory = $historial->map(function($h){
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
}
