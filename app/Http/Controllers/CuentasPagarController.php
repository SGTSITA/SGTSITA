<?php

namespace App\Http\Controllers;

use App\Models\Asignaciones;
use App\Models\BancoDinero;
use App\Models\Bancos;
use App\Models\Client;
use App\Models\Cotizaciones;
use App\Models\CuentasBancarias;
use App\Models\DocumCotizacion;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use DB;

class CuentasPagarController extends Controller
{
    public function index(){
        $cotizacionIds = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
        ->whereNull('asignaciones.id_camion')
        ->where('cotizaciones.id_empresa', '=',auth()->user()->id_empresa)
        ->where(function($query) {
            $query->where('cotizaciones.estatus', '=', 'Aprobada')
                  ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
        })
        ->where('cotizaciones.prove_restante', '>', 0)
        ->select('cotizaciones.id')
        ->pluck('cotizaciones.id');

        $cotizacionesPorCliente = Cotizaciones::whereIn('id', $cotizacionIds)
        ->with(['DocCotizacion.Asignaciones.Proveedor']) // Carga las relaciones necesarias
        ->get()
        ->groupBy('DocCotizacion.Asignaciones.id_proveedor')
        ->map(function ($group) {
            $totalRestante = $group->sum('prove_restante'); // Suma los valores de prove_restante

            return [
                'id_proveedor' => $group->first()->DocCotizacion->Asignaciones->id_proveedor,
                'total_cotizaciones' => $group->count(),
                'proveedor' => $group->first()->DocCotizacion->Asignaciones->Proveedor,
                'total_restante_formateado' => number_format($totalRestante, 0, '.', ','), // Formatea la suma
            ];
        });
        return view('cuentas_pagar.index', compact('cotizacionesPorCliente'));
    }

    public function viajes_por_liquidar(Request $request){
        $cotizacionesPorPagar = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
        ->where('asignaciones.id_camion', '=', NULL)
        ->where(function($query) {
            $query->where('cotizaciones.estatus', '=', 'Aprobada')
                  ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
        })
        ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
        ->where('asignaciones.id_proveedor', '=', $request->proveedor)
        ->where('cotizaciones.prove_restante', '>', 0)
        ->select(
            'asignaciones.*',
            'docum_cotizacion.num_contenedor',
            'docum_cotizacion.id_cotizacion',
            'cotizaciones.estatus',
            'cotizaciones.prove_restante',
            'cotizaciones.id_cuenta_prov',
            'cotizaciones.dinero_cuenta_prov',
            'cotizaciones.id_cuenta_prov2',
            'cotizaciones.dinero_cuenta_prov2'
        )
        ->get();

        $handsOnTableData = $cotizacionesPorPagar->map(function($item){
            return [
                $item->num_contenedor,
                ($item->estatus == 'Aprobada') ? "En Curso" : $item->estatus,
                $item->prove_restante,
                $item->prove_restante,
                0,
                0,
                0,
                $item->id_cotizacion

            ];
        });


        
        return response()->json(["success" => true,"handsOnTableData" => $handsOnTableData, "cotizacionesPorPagar" => $cotizacionesPorPagar]);
        
    }

    public function show($id){
        $bancos = Bancos::where('id_empresa', '=',auth()->user()->id_empresa)->get();
        $cliente = Proveedor::where('id', '=', $id)->first();
        $banco_proveedor = CuentasBancarias::where('id_proveedores', '=', $cliente->id)->get();

        /*$cotizacionesPorPagar = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
        ->where('asignaciones.id_camion', '=', NULL)
        ->where(function($query) {
            $query->where('cotizaciones.estatus', '=', 'Aprobada')
                  ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
        })
        ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
        ->where('asignaciones.id_proveedor', '=', $id)
        ->where('cotizaciones.prove_restante', '>', 0)
        ->select(
            'asignaciones.*',
            'docum_cotizacion.num_contenedor',
            'docum_cotizacion.id_cotizacion',
            'cotizaciones.estatus',
            'cotizaciones.prove_restante',
            'cotizaciones.id_cuenta_prov',
            'cotizaciones.dinero_cuenta_prov',
            'cotizaciones.id_cuenta_prov2',
            'cotizaciones.dinero_cuenta_prov2'
        )
        ->get();

        // Agrupar por proveedor y calcular los totales
        $cotizacionesAgrupadas = $cotizacionesPorPagar->groupBy('asignaciones.id_proveedor')->map(function ($group) {
            // Sumar el total restante
            $totalRestante = $group->sum('prove_restante');

            // Contar el total de cotizaciones
            $totalCotizaciones = $group->count();

            return [
                'total_cotizaciones' => $totalCotizaciones,
                'total_restante_formateado' => number_format($totalRestante, 0, '.', ','),
                'cotizaciones' => $group // Esto mantiene las cotizaciones para mostrarlas si es necesario
            ];
        });

        return view('cuentas_pagar.show', compact('cotizacionesPorPagar', 'bancos', 'cliente', 'banco_proveedor', 'cotizacionesAgrupadas'));*/

        return view('cuentas_pagar.pagos_v2', compact('bancos', 'cliente', 'banco_proveedor'));
       
    }

    public function aplicar_pagos(Request $request){
        try{
        //Primero validaremos que los pagos/abonos de cada contenedor no sea mayor al saldo pendiente
        $cotizaciones = $request->datahotTableCXP;
        foreach($cotizaciones as $c){
            if($c[6] > $c[2]) 
              return response()->json([
                                        "success" => false, 
                                        "Titulo" => "Error en el contenedor $c[0]",
                                        "Mensaje" => "No se puede aplicar el pago para el contenedor porque existe un error monto del pago ó es mayor al Saldo Original",
                                        "TMensaje" => "warning"
                                    ]);
        }

        $bankOne = Bancos::where('id' ,'=',$request->bankOne);
        $bankTwo = Bancos::where('id' ,'=',$request->bankTwo);

        $saldoCtaOne = $bankOne->first()->saldo;
        $saldoCtaTwo = $bankTwo->first()->saldo;

        if($saldoCtaOne < $request->amountPayOne){
            return response()->json([
                "success" => false, 
                "Titulo" => "Saldo insuficiente",
                "Mensaje" => "No se puede aplicar el pago porque el saldo en el Banco 1 es insuficiente",
                "TMensaje" => "warning"
            ]);
        }

        if($saldoCtaTwo < $request->amountPayTwo){
            return response()->json([
                "success" => false, 
                "Titulo" => "Saldo insuficiente",
                "Mensaje" => "No se puede aplicar el pago porque el saldo en el Banco 2 es insuficiente",
                "TMensaje" => "warning"
            ]);
        }

        DB::beginTransaction();
        $contenedoresAbonos = [];

        foreach($cotizaciones as $c){
            if($c[6] > 0){//Si el pago es mayor a cero
                $id = $c[7];
                $cotizacion = Cotizaciones::where('id', '=', $id)->first();

                // Establecer el abono y calcular el restante
                $abono = $c[6];
                $nuevoRestante = $cotizacion->prove_restante - $abono;
                if ($nuevoRestante < 0) {
                    $nuevoRestante = 0;
                }

                $cotizacion->prove_restante = $nuevoRestante;
             //   $cotizacion->estatus_pago = ($nuevoRestante == 0) ? 1 : 0;
                $cotizacion->fecha_pago_proveedor = date('Y-m-d');
                $cotizacion->save();

                // Agregar contenedor y abono al array
                $contenedorAbono = [
                    'num_contenedor' => $c[0],
                    'abono' => $c[6]
                ];

                \Log::debug($contenedorAbono);

                array_push($contenedoresAbonos, $contenedorAbono);
            }
        }

        $banco = new BancoDinero();
        $banco->contenedores = json_encode($contenedoresAbonos);;
        $banco->id_proveedor = $request->theProvider;
        $banco->monto1 = $request->amountPayOne;
        $banco->metodo_pago1 = 'TRANSFERENCIA';
        $banco->id_banco1 = $request->bankOne;

        $banco->monto2 = $request->amountPayTwo;
        $banco->metodo_pago2 = 'TRANSFERENCIA';
        $banco->id_banco2 = $request->bankTwo;

        $banco->fecha_pago = date('Y-m-d');
        $banco->tipo = 'Salida';

        $banco->save();

        //Actualizamos cuentas bancarias de retiro
        Bancos::where('id' ,'=',$request->bankOne)->update(["saldo" => DB::raw("saldo - ". $request->amountPayOne)]);
        Bancos::where('id' ,'=',$request->bankTwo)->update(["saldo" => DB::raw("saldo - ". $request->amountPayTwo)]);

        //Actualizamos cuentas bancarias de deposito
       // CuentasBancarias::where('id' ,'=',$request->bankProvOne)->update(["saldo" => DB::raw("saldo + ". $request->amountPayOne)]);
        //CuentasBancarias::where('id' ,'=',$request->bankProvTwo)->update(["saldo" => DB::raw("saldo + ". $request->amountPayTwo)]);

        DB::commit();
        return response()->json(["success" => true, "Titulo" => "Pago exitoso", "Mensaje" => "Hemos aplicado el pago a los elementos indicados", "TMensaje" => "success"]);

     }catch(\Throwable $t){
        DB::rollback();
        \Log::debug($t->getMessage());
        return response()->json(["success" => false, "Titulo" => "Error", "Mensaje" => "No pudimos aplicar el pago, existe un error", "TMensaje" => "error"]);

     }
    }

    public function update(Request $request, $id){
        $cotizacion = Cotizaciones::where('id', '=', $id)->first();
        $cotizacion->prove_monto1 = $request->get('monto1');
        $cotizacion->prove_metodo_pago1 = $request->get('metodo_pago1');
        $cotizacion->id_prove_banco1 = $request->get('id_banco1');
        if ($request->hasFile("comprobante1")) {
            $file = $request->file('comprobante1');
            $path = public_path() . '/pagos';
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $cotizacion->prove_comprobante_pago1 = $fileName;
        }

        $cotizacion->prove_monto2 = $request->get('monto2');
        $cotizacion->prove_metodo_pago2 = $request->get('metodo_pago2');
        $cotizacion->id_prove_banco2 = $request->get('id_banco2');
        if ($request->hasFile("comprobante2")) {
            $file = $request->file('comprobante2');
            $path = public_path() . '/pagos';
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $cotizacion->prove_comprobante_pago2 = $fileName;
        }

        $suma = $request->get('monto1') + $request->get('monto2');
        $resta = $cotizacion->prove_restante - $suma;
        $cotizacion->prove_restante = $resta;

        $cotizacion->id_cuenta_prov = $request->get('id_cuenta_prov');
        $cotizacion->dinero_cuenta_prov = $request->get('dinero_cuenta_prov');
        $cotizacion->id_cuenta_prov2 = $request->get('id_cuenta_prov2');
        $cotizacion->dinero_cuenta_prov2 = $request->get('dinero_cuenta_prov2');
        $cotizacion->fecha_pago_proveedor = date('Y-m-d');
        $cotizacion->update();

        return redirect()->back()->with('success', 'Comprobante de pago exitosamente');
    }

    public function update_varios(Request $request){
        $cotizacionesData = $request->get('id_cotizacion');
        $remainingTotal = $request->get('remaining_total');
        $abonos = $request->get('abono');

        // Array para almacenar contenedor y abono
        $contenedoresAbonos = [];

        foreach ($cotizacionesData as $id) {
            $cotizacion = Cotizaciones::where('id', '=', $id)->first();

            // Establecer el abono y calcular el restante
            $abono = isset($abonos[$id]) ? floatval($abonos[$id]) : 0;
            $nuevoRestante = $cotizacion->prove_restante - $abono;
            if ($nuevoRestante < 0) {
                $nuevoRestante = 0;
            }

            $cotizacion->prove_restante = $nuevoRestante;
            $cotizacion->fecha_pago_proveedor = date('Y-m-d');
            $cotizacion->update();

            // Agregar contenedor y abono al array
            $contenedoresAbonos[] = [
                'num_contenedor' => $cotizacion->DocCotizacion->num_contenedor,
                'abono' => $abono
            ];
        }

        // Convertir el array de contenedores y abonos a JSON
        $contenedoresAbonosJson = json_encode($contenedoresAbonos);


        $banco = new BancoDinero();
        $banco->contenedores = $contenedoresAbonosJson;
        $banco->id_proveedor = $request->get('id_cliente');
        $banco->monto1 = $request->get('monto1_varios');
        $banco->metodo_pago1 = $request->get('metodo_pago1_varios');
        $banco->id_banco1 = $request->get('id_banco1_varios');
        if ($request->hasFile("comprobante1_varios")) {
            $file = $request->file('comprobante1');
            $path = public_path() . '/pagos';
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $banco->comprobante_pago1 = $fileName;
        }

        $banco->monto2 = $request->get('monto2_varios');
        $banco->metodo_pago2 = $request->get('metodo_pago2_varios');
        $banco->id_banco2 = $request->get('id_banco2_varios');
        if ($request->hasFile("comprobante2_varios")) {
            $file = $request->file('comprobante2');
            $path = public_path() . '/pagos';
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $banco->comprobante_pago2 = $fileName;
        }

        $banco->fecha_pago = date('Y-m-d');
        $banco->tipo = 'Salida';

        $banco->save();

        return redirect()->back()->with('success', 'Cobro exitoso');
    }
}
