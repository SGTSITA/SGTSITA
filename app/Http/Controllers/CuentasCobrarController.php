<?php

namespace App\Http\Controllers;

use App\Models\BancoDinero;
use App\Models\Bancos;
use App\Models\Client;
use App\Models\Cotizaciones;
use App\Models\Subclientes;
use Illuminate\Http\Request;
use DB;

class CuentasCobrarController extends Controller
{
    public function index(){
        $cotizacionesPorCliente = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->where('cotizaciones.estatus_pago', '=', '0')
        ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
        ->where('cotizaciones.restante', '>', 0)
        ->where(function($query) {
            $query->where('cotizaciones.estatus', '=', 'Aprobada')
                  ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
        })
        ->select(
            'cotizaciones.id_cliente',
            DB::raw('COUNT(*) as total_cotizaciones'),
            DB::raw('SUM(cotizaciones.restante) as total_restante') // Sumar la columna restante
        )
        ->groupBy('cotizaciones.id_cliente')
        ->get();

        return view('cuentas_cobrar.index', compact('cotizacionesPorCliente'));
    }

    public function cobranza_v2($id){
        $cotizacion = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
        ->where('cotizaciones.estatus_pago', '=', '0')
        ->where('cotizaciones.restante', '>', 0)
        ->where(function($query) {
            $query->where('cotizaciones.estatus', '=', 'Aprobada')
                  ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
        })
        ->where('cotizaciones.id_cliente', '=', $id)
        ->select(
            'cotizaciones.id_cliente',
            DB::raw('COUNT(*) as total_cotizaciones'),
            DB::raw('SUM(cotizaciones.restante) as total_restante') // Sumar la columna restante
        )
        ->groupBy('cotizaciones.id_cliente') // Agrupa por el ID de cotización
        ->first();

        $cliente = Client::where('id', '=', $id)->first();

        $bancos = Bancos::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();

        return view('cuentas_cobrar.cobranza_cliente',compact('bancos','cliente', 'cotizacion'));
    }

    public function viajes_por_liquidar(Request $request){
        $cotizacionesPorPagar = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
        ->where('cotizaciones.estatus_pago', '=', '0')
        ->where('cotizaciones.restante', '>', 0)
        ->where(function($query) {
            $query->where('cotizaciones.estatus', '=', 'Aprobada')
                  ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
        })
        ->where('cotizaciones.id_cliente', '=', $request->client)
        ->select('cotizaciones.*')
        ->get();

        $handsOnTableData = $cotizacionesPorPagar->map(function($item){
            $subCliente = ($item->id_subcliente != NULL) ? $item->Subcliente->nombre." / ".$item->Subcliente->telefono : "";
            $tipoViaje = ($item->tipo_viaje == NULL || $item->tipo_viaje == 'Seleccionar Opcion') ? "Subcontratado" : $item->tipo_viaje;

            return [
                $item->DocCotizacion->num_contenedor,
                $subCliente,
                $tipoViaje,
                ($item->estatus == 'Aprobada') ? "En Curso" : $item->estatus,
                $item->restante,
                $item->restante,
                0,
                0,
                0,
                $item->id

            ];
        });

        return response()->json(["success" => true,"handsOnTableData" => $handsOnTableData, "cotizacionesPorPagar" => $cotizacionesPorPagar]);
    }

    public function aplicar_pagos(Request $request){
        try{
            //Primero validaremos que los pagos/abonos de cada contenedor no sea mayor al saldo pendiente
            $cotizaciones = $request->datahotTable;
            foreach($cotizaciones as $c){
                if($c[8] > $c[4]) 
                  return response()->json([
                                            "success" => false, 
                                            "Titulo" => "Error en el contenedor $c[0]",
                                            "Mensaje" => "No se puede aplicar el pago para el contenedor porque existe un error monto del pago ó es mayor al Saldo Original",
                                            "TMensaje" => "warning"
                                        ]);
            }

            DB::beginTransaction();
            $contenedoresAbonos = [];
             foreach($cotizaciones as $c){
                if($c[8] > 0){ //Si total cobrado es mayor a cero
                    $id = $c[9]; //Obtenemos el ID
                    $cotizacion = Cotizaciones::where('id', '=', $id)->first();

                    // Establecer el abono y calcular el restante
                    $abono = $c[8];
                    $nuevoRestante = $cotizacion->restante - $abono;

                    if ($nuevoRestante < 0) {
                        $nuevoRestante = 0;
                    }

                    $cotizacion->restante = $nuevoRestante;
                    $cotizacion->estatus_pago = ($nuevoRestante == 0) ? 1 : 0;
                    $cotizacion->fecha_pago = date('Y-m-d');
                    $cotizacion->update();

                    // Agregar contenedor y abono al array
                    $contenedorAbono = [
                        'num_contenedor' => $c[0],
                        'abono' => $c[8]
                    ];

                    array_push($contenedoresAbonos, $contenedorAbono);
                }
                    
             }

            $banco = new BancoDinero;
            $banco->contenedores = json_encode($contenedoresAbonos);
            $banco->id_cliente = $request->theClient;
            $banco->monto1 = $request->amountPayOne;
            $banco->metodo_pago1 = "Transferencia"; //Metodo de pago por default, instrucciones JH
            $banco->id_banco1 = $request->bankOne;

            $banco->monto2 = $request->amountPayTwo;
            $banco->metodo_pago2 = "Transferencia"; //Metodo de pago por default, instrucciones JH
            $banco->id_banco2 = $request->bankTwo;

            $banco->fecha_pago = date('Y-m-d');
            $banco->tipo = 'Entrada';

            $banco->save();

            Bancos::where('id' ,'=',$request->bankOne)->update(["saldo" => DB::raw("saldo + ". $request->amountPayOne)]);
            Bancos::where('id' ,'=',$request->bankTwo)->update(["saldo" => DB::raw("saldo + ". $request->amountPayTwo)]);

            DB::commit();
            return response()->json(["success" => true, "Titulo" => "Cobro exitoso", "Mensaje" => "Hemos aplicado el cobro de los elementos indicados", "TMensaje" => "success"]);
        }catch(\Throwable $t){
            DB::rollback();
            return response()->json(["success" => false, "Titulo" => "Error", "Mensaje" => "No pudimos aplicar el cobro, existe un error", "TMensaje" => "error"]);
        }
    }

    public function show($id){
        $cliente = Client::where('id', '=', $id)->first();
        $cotizacionesPorPagar = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
        ->where('cotizaciones.estatus_pago', '=', '0')
        ->where('cotizaciones.restante', '>', 0)
        ->where(function($query) {
            $query->where('cotizaciones.estatus', '=', 'Aprobada')
                  ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
        })
        ->where('cotizaciones.id_cliente', '=', $id)
        ->select('cotizaciones.*')
        ->get();

        $cotizacion = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
        ->where('cotizaciones.estatus_pago', '=', '0')
        ->where('cotizaciones.restante', '>', 0)
        ->where(function($query) {
            $query->where('cotizaciones.estatus', '=', 'Aprobada')
                  ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
        })
        ->where('cotizaciones.id_cliente', '=', $id)
        ->select(
            'cotizaciones.id_cliente',
            DB::raw('COUNT(*) as total_cotizaciones'),
            DB::raw('SUM(cotizaciones.restante) as total_restante') // Sumar la columna restante
        )
        ->groupBy('cotizaciones.id_cliente') // Agrupa por el ID de cotización
        ->first();

        $bancos = Bancos::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();
        
        return view('cuentas_cobrar.show', compact('cotizacionesPorPagar', 'bancos', 'cliente', 'cotizacion'));
    }

    public function update(Request $request, $id){
        $cotizacion = Cotizaciones::where('id', '=', $id)->first();
        $cotizacion->monto1 = $request->get('monto1');
        $cotizacion->metodo_pago1 = $request->get('metodo_pago1');
        $cotizacion->id_banco1 = $request->get('id_banco1');
        if ($request->hasFile("comprobante1")) {
            $file = $request->file('comprobante1');
            $path = public_path() . '/pagos';
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $cotizacion->comprobante_pago1 = $fileName;
        }

        $cotizacion->monto2 = $request->get('monto2');
        $cotizacion->metodo_pago2 = $request->get('metodo_pago2');
        $cotizacion->id_banco2 = $request->get('id_banco2');
        if ($request->hasFile("comprobante2")) {
            $file = $request->file('comprobante2');
            $path = public_path() . '/pagos';
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $cotizacion->comprobante_pago2 = $fileName;
        }

        $suma = $request->get('monto1') + $request->get('monto2');
        $resta = $cotizacion->restante - $suma;
        $cotizacion->restante = $resta;
        $cotizacion->estatus_pago = 1;
        $cotizacion->fecha_pago = date('Y-m-d');
        $cotizacion->update();

        return redirect()->back()->with('success', 'Comprobante de pago exitosamente');
    }

    public function update_varios(Request $request)
    {
        $cotizacionesData = $request->get('id_cotizacion');
        $abonos = $request->get('abono');
        $remainingTotal = $request->get('remaining_total');

        // Array para almacenar contenedor y abono
        $contenedoresAbonos = [];

        foreach ($cotizacionesData as $id) {
            $cotizacion = Cotizaciones::where('id', '=', $id)->first();

            // Establecer el abono y calcular el restante
            $abono = isset($abonos[$id]) ? floatval($abonos[$id]) : 0;
            $nuevoRestante = $cotizacion->restante - $abono;

            if ($nuevoRestante < 0) {
                $nuevoRestante = 0;
            }

            $cotizacion->restante = $nuevoRestante;
            $cotizacion->estatus_pago = ($nuevoRestante == 0) ? 1 : 0;
            $cotizacion->fecha_pago = date('Y-m-d');
            $cotizacion->update();

            // Agregar contenedor y abono al array
            $contenedoresAbonos[] = [
                'num_contenedor' => $cotizacion->DocCotizacion->num_contenedor,
                'abono' => $abono
            ];
        }

        // Convertir el array de contenedores y abonos a JSON
        $contenedoresAbonosJson = json_encode($contenedoresAbonos);

        $banco = new BancoDinero;
        $banco->contenedores = $contenedoresAbonosJson;
        $banco->id_cliente = $request->get('id_cliente');
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

        $banco->fecha_pago = date('Y-m-d');
        $banco->tipo = 'Entrada';

        $banco->save();

        return redirect()->back()->with('success', 'Cobro exitoso');
    }
}
