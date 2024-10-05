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

class LiquidacionesController extends Controller
{
    public function index(){
        $asignacion_operador = Asignaciones::join('operadores', 'asignaciones.id_operador', '=', 'operadores.id')
        ->where('asignaciones.id_empresa', '=',auth()->user()->id_empresa)
        ->where('asignaciones.id_proveedor', '=', NULL)
        ->where('asignaciones.estatus_pagado', '=', 'Pendiente Pago')
        ->where('asignaciones.restante_pago_operador', '>', '0')
        ->select('asignaciones.id_operador', DB::raw('COUNT(*) as total_cotizaciones'), DB::raw('SUM(pago_operador) as total_pago'))
        ->groupBy('asignaciones.id_operador')
        ->get();

        return view('liquidaciones.index', compact('asignacion_operador'));
    }

    public function show($id){
        $operador = Operador::where('id', '=', $id)->first();

        $asignacion_operador = Asignaciones::where('id_empresa', '=',auth()->user()->id_empresa)
        ->where('estatus_pagado', '=', 'Pendiente Pago')
        ->where('restante_pago_operador', '>', '0')
        ->where('id_proveedor', '=', NULL)
        ->where('id_operador', '=', $id)
        ->get();

        $bancos = Bancos::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();
        $gastos_ope = GastosOperadores::where('id_operador', '=', $operador->id)->get();

        return view('liquidaciones.show', compact('asignacion_operador', 'bancos', 'operador', 'gastos_ope'));
    }

    public function update(Request $request, $id){

        $asignacion = Asignaciones::where('id', '=', $id)->first();

        $suma_ope = $asignacion->restante_pago_operador - ($request->get('monto1') + $request->get('monto2'));
        $asignacion->restante_pago_operador = $suma_ope;
        $asignacion->fecha_pago_operador = date('Y-m-d');
        if($suma_ope == 0){
            $asignacion->estatus_pagado = 'Pagado';
        }
        $asignacion->update();

        $cotizacion = DocumCotizacion::where('id', '=', $asignacion->id_contenedor)->first();

        if($request->get('monto1') != NULL){
            $banco = new BancoDineroOpe;
            $banco->id_operador = $asignacion->id_operador;
            $banco->id_asignacion = $asignacion->id;
            $banco->id_cotizacion = $cotizacion->id_cotizacion;
            $banco->monto1 = $request->get('monto1');
            $banco->metodo_pago1 = $request->get('metodo_pago1');
            $banco->id_banco1 = $request->get('id_banco1');
            if ($request->hasFile("comprobante1")) {
                $file = $request->file('comprobante1');
                $path = public_path() . '/pagos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $banco->comprobante_pago1 = $fileName;
            }
            $banco->tipo = 'Salida';
            $banco->fecha_pago = date('Y-m-d');
            $banco->save();
        }

        if($request->get('monto2') != NULL){
            $banco = new BancoDineroOpe;
            $banco->id_operador = $asignacion->id_operador;
            $banco->id_asignacion = $asignacion->id;
            $banco->id_cotizacion = $cotizacion->id_cotizacion;
            $banco->monto2 = $request->get('monto2');
            $banco->metodo_pago2 = $request->get('metodo_pago2');
            $banco->id_banco2 = $request->get('id_banco2');
            if ($request->hasFile("comprobante2")) {
                $file = $request->file('comprobante2');
                $path = public_path() . '/pagos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $banco->comprobante_pago2 = $fileName;
            }
           $banco->tipo = 'Salida';
           $banco->fecha_pago = date('Y-m-d');
           $banco->save();
        }

        return redirect()->back()->with('success', 'Liquidado exitosamente');
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
