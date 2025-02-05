<?php

namespace App\Http\Controllers;

use App\Models\Asignaciones;
use App\Models\BancoDineroOpe;
use App\Models\Bancos;
use App\Models\DocumCotizacion;
use App\Models\GastosOperadores;
use App\Models\Operador;
use Illuminate\Http\Request;
use DB;
use Log;

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
                "Operador" => $ope->operador->nombre,
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
            return [
                "IdAsignacion" => $c->id,
                "IdOperador" => $c->id_operador,
                "Contenedor" => $c->contenedor->num_contenedor,
                "SueldoViaje" => $c->sueldo_viaje,
                "DineroViaje" => $c->dinero_viaje,
                "MontoPago" => $c->restante_pago_operador,
                "FechaInicia" => $c->fecha_inicio,
                "FechaTermina" => $c->fecha_fin
            ];
        });

        $totalPago = $asignacion_operador->sum('restante_pago_operador');
        $numeroViajes = $asignacion_operador->count();

        $bancos = Bancos::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();
        $gastos_ope = GastosOperadores::where('id_operador', '=', $r->operador)->get();

        return response()->json(["viajes" => $contenedores, "totalPago" => $totalPago, "numViajes" => $numeroViajes]);
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
        
          //  return $contenedores->sum('MontoPago');
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
    
                $banco = new BancoDineroOpe;
                $banco->id_operador = $asignacion->id_operador;
                
                $banco->monto1 = $c['MontoPago'];
                $banco->metodo_pago1 = 'Tranferencia';
                $banco->descripcion_gasto = 'Sueldos y salarios';
                $banco->id_banco1 = $request->bancoId;

                $contenedoresAbonos[] = [
                    'num_contenedor' => $cotizacion->num_contenedor,
                    'abono' => $c['MontoPago']
                ];
                $contenedoresAbonosJson = json_encode($contenedoresAbonos);

                $banco->contenedores = $contenedoresAbonosJson;
            
                $banco->tipo = 'Salida';
                $banco->fecha_pago = date('Y-m-d');
                $banco->save();
            }

            Bancos::where('id' ,'=',$request->bancoId)->update(["saldo" => DB::raw("saldo - ". $request->totalMontoPago)]);
            DB::commit();

            return response()->json(["Titulo" => "Pago aplicado","Mensaje" => "Se aplicó el pago correctamente al operador","TMensaje" => "success"]);
        }catch(\Throwable $t){
            DB::rollback();
            Log::channel('daily')->info('LiquidacionesController->aplicarPago:'.$t->getMessage());
            return response()->json(["Titulo" => "Pago NO aplicado","Mensaje" => "Ocurrio un error al aplicar el pago: ".$t->getMessage(),"TMensaje" => "error"]);

        }
    }

    public function update_varios(Request $request)
    {
        $cotizacionesData = $request->get('id_cotizacion');
        $abonos = $request->get('abono');
        $remainingTotal = $request->get('remaining_total');

        // Array para almacenar contenedor y abono
        $contenedoresAbonos = [];

        foreach ($cotizacionesData as $id) {
            $cotizacion = Asignaciones::where('id', '=', $id)->first();

            // Establecer el abono y calcular el restante
            $abono = isset($abonos[$id]) ? floatval($abonos[$id]) : 0;
            $nuevoRestante = $cotizacion->restante_pago_operador - $abono;

            if ($nuevoRestante < 0) {
                $nuevoRestante = 0;
            }

            $cotizacion->restante_pago_operador = $nuevoRestante;
            if($nuevoRestante == 0){
                $cotizacion->estatus_pagado = 'Pagado';
            }
            $cotizacion->update();

            // Agregar contenedor y abono al array
            $contenedoresAbonos[] = [
                'num_contenedor' => $cotizacion->Contenedor->num_contenedor,
                'abono' => $abono
            ];
        }

        // Convertir el array de contenedores y abonos a JSON
        $contenedoresAbonosJson = json_encode($contenedoresAbonos);

        $banco = new BancoDineroOpe;
        if($request->get('monto1_varios') != NULL){
            $banco = new BancoDineroOpe;
            $banco->contenedores = $contenedoresAbonosJson;
            $banco->id_operador = $request->get('id_cliente');
            $banco->monto1 = $request->get('monto1_varios');
            $banco->metodo_pago1 = $request->get('metodo_pago1_varios');
            $banco->id_banco1 = $request->get('id_banco1_varios');
            if ($request->hasFile("comprobante1_varios")) {
                $file = $request->file('comprobante1_varios');
                $path = public_path() . '/pagos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $banco->comprobante_pago1 = $fileName;
            }
            $banco->tipo = 'Salida';
            $banco->fecha_pago = date('Y-m-d');
            $banco->save();
        }

        if($request->get('monto2_varios') != NULL){
            $banco = new BancoDineroOpe;
            $banco->contenedores = $contenedoresAbonosJson;
            $banco->monto2 = $request->get('monto2_varios');
            $banco->metodo_pago2 = $request->get('metodo_pago2_varios');
            $banco->id_banco2 = $request->get('id_banco2_varios');
            if ($request->hasFile("comprobante2_varios")) {
                $file = $request->file('comprobante2_varios');
                $path = public_path() . '/pagos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $banco->comprobante_pago2 = $fileName;
            }
           $banco->tipo = 'Salida';
           $banco->fecha_pago = date('Y-m-d');
        }

        $banco->save();

        return redirect()->back()->with('success', 'Liquedaciones exitosas');
    }
}
