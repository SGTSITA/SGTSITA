<?php

namespace App\Http\Controllers;

use App\Models\Operador;
use Illuminate\Http\Request;

use App\Models\Cotizaciones;
use App\Models\DocumCotizacion;
use App\Models\Asignaciones;
use App\Models\Bancos;
use Illuminate\Support\Facades\Validator;
use App\Models\ComprobanteGastos;
use App\Models\GastosOperadores;
use Session;

class OperadorController extends Controller
{
    public function index(){
        $operadores = Operador::withTrashed() 
        ->where('id_empresa', auth()->user()->id_empresa)
        ->orderBy('id', 'asc')
        ->get();
        $pagos_pendientes = Asignaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('estatus_pagado', '=', 'Pendiente Pago')->get();
        return view('operadores.index', compact('operadores', 'pagos_pendientes'));
    }

    public function show($id){
        $operador = Operador::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('id', '=', $id)->first();
        $pagos = Asignaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('estatus_pagado', '=', 'Pagado')->where('id_operador', '=', $id)->get();
        $comprobantes_gasolina = ComprobanteGastos::join('asignaciones', 'comprobantes_gastos.id_asignacion', 'asignaciones.id')
                                            ->where('asignaciones.id_operador', '=', $id)
                                            ->where('id_empresa' ,'=',auth()->user()->id_empresa)
                                            ->where('comprobantes_gastos.tipo', '=', 'Gasolina')
                                            ->select('comprobantes_gastos.*')
                                            ->get();

        $comprobantes_casetas = ComprobanteGastos::join('asignaciones', 'comprobantes_gastos.id_asignacion', 'asignaciones.id')
                                                    ->where('asignaciones.id_operador', '=', $id)
                                                    ->where('id_empresa' ,'=',auth()->user()->id_empresa)
                                                    ->where('comprobantes_gastos.tipo', '=', 'Casetas')
                                                    ->select('comprobantes_gastos.*')
                                                    ->get();

        $comprobantes_otros = ComprobanteGastos::join('asignaciones', 'comprobantes_gastos.id_asignacion', 'asignaciones.id')
                                                    ->where('asignaciones.id_operador', '=', $id)
                                                    ->where('id_empresa' ,'=',auth()->user()->id_empresa)
                                                    ->where('comprobantes_gastos.tipo', '=', 'Otros')
                                                    ->select('comprobantes_gastos.*')
                                                    ->get();

        return view('operadores.show', compact('operador', 'pagos', 'comprobantes_gasolina', 'comprobantes_casetas', 'comprobantes_otros'));
    }
    public function update(Request $request, Operador $id)
    {
        $validator = Validator::make($request->all(), [
            'curp' => 'required|string|unique:operadores,curp,' . $id->id . ',id,id_empresa,' . auth()->user()->id_empresa,
        ], [
            'curp.required' => 'El campo CURP es obligatorio.',
            'curp.unique' => 'validacion_curp_personalizada', // marcador especial
        ]);
    
        if ($validator->fails()) {
            $errores = $validator->errors();
    
            if ($errores->has('curp') && $errores->first('curp') === 'validacion_curp_personalizada') {
                $operadorExistente = Operador::where('curp', $request->curp)
                    ->where('id_empresa', auth()->user()->id_empresa)
                    ->where('id', '!=', $id->id)
                    ->first();
    
                if ($operadorExistente) {
                    $mensaje = "El CURP <strong>{$operadorExistente->curp}</strong> ya está registrado para el operador <strong>{$operadorExistente->nombre}</strong>.";
    
                    return redirect()->back()
                        ->withInput()
                        ->with('curp_error_update_' . $id->id, $mensaje); // usamos clave única por modal
                }
            }
    
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        $id->update($request->all());
    
        Session::flash('edit', 'Se ha editado sus datos con éxito');
        return redirect()->back()->with('success', 'Operador actualizado correctamente');
    }
    

    public function store(Request $request)
    {
        // Validación con Validator
        $validator = Validator::make($request->all(), [
            'curp' => 'required|string|unique:operadores,curp,NULL,id,id_empresa,' . auth()->user()->id_empresa,
        ], [
            'curp.required' => 'El campo CURP es obligatorio.',
            'curp.unique' => 'validacion_curp_personalizada', // marcador especial
        ]);
    
        // Si hay errores
        if ($validator->fails()) {
            $errores = $validator->errors();
    
            // Si el error es por CURP duplicado, lo personalizamos
            if ($errores->has('curp') && $errores->first('curp') === 'validacion_curp_personalizada') {
                $operadorExistente = Operador::where('curp', $request->curp)
                    ->where('id_empresa', auth()->user()->id_empresa)
                    ->first();
    
                if ($operadorExistente) {
                    $mensaje = "El CURP <strong>{$operadorExistente->curp}</strong> ya está registrado para el operador <strong>{$operadorExistente->nombre}</strong>.";
                    return redirect()->back()
                        ->withInput()
                        ->with('curp_error', $mensaje);
                }
            }
    
            // Otros errores (no de CURP)
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        // Crear nuevo operador (misma lógica tuya, intacta)
        $operador = new Operador;
        $operador->curp = $request->get('curp');
        $operador->nombre = $request->get('nombre');
        $operador->correo = $request->get('correo');
        $operador->telefono = $request->get('telefono');
        $operador->domicilio = $request->get('domicilio');
        $operador->fecha_nacimiento = $request->get('fecha_nacimiento');
        $operador->acceso = $request->get('acceso');
        $operador->tipo_sangre = $request->get('tipo_sangre');
        $operador->nss = $request->get('nss');
        $operador->recomendacion = $request->get('recomendacion');
        $operador->foto = $request->get('foto');
    
        // Manejo de archivos
        $path = public_path() . '/operador';
    
        if ($request->hasFile("comprobante_domicilio")) {
            $file = $request->file('comprobante_domicilio');
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $operador->comprobante_domicilio = $fileName;
        }
    
        if ($request->hasFile("ine")) {
            $file = $request->file('ine');
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $operador->ine = $fileName;
        }
    
        if ($request->hasFile("cedula_fiscal")) {
            $file = $request->file('cedula_fiscal');
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $operador->cedula_fiscal = $fileName;
        }
    
        if ($request->hasFile("licencia_conducir")) {
            $file = $request->file('licencia_conducir');
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $operador->licencia_conducir = $fileName;
        }
    
        $operador->save();
    
        Session::flash('success', 'Se ha guardado sus datos con éxito');
        return redirect()->back()->with('success', 'Operador creado correctamente.');
    }
    

    public function update_pago(Request $request, $id){

        $asignaciones = Asignaciones::where('id', '=', $id)->first();
        $asignaciones->id_banco1_pago_operador = $request->get('id_banco1_pago_operador');
        $asignaciones->cantidad_banco1_pago_operador = $request->get('cantidad_banco1_pago_operador');
        $asignaciones->id_banco2_pago_operador = $request->get('id_banco2_pago_operador');
        $asignaciones->cantidad_banco2_pago_operador = $request->get('cantidad_banco2_pago_operador');
        $asignaciones->fecha_pago_operador = date('Y-m-d');
        $asignaciones->estatus_pagado = 'Pagado';
        $asignaciones->update();

        $gasolina = $request->get('gasolina_'.$id);
        $casetas = $request->get('casetas');
        $otros = $request->get('otros');

        for ($count = 0; $count < count($gasolina); $count++) {
            $data = array(
                'id_asignacion' => $asignaciones->id,
                'id_operador' => $request->get('id_operador'),
                'gasolina' => $gasolina[$count],
            );

            GastosOperadores::create($data);
        }

        for ($count = 0; $count < count($casetas); $count++) {
            $data = array(
                'id_asignacion' => $asignaciones->id,
                'id_operador' => $request->get('id_operador'),
                'casetas' => $casetas[$count],
            );

            GastosOperadores::create($data);
        }

        for ($count = 0; $count < count($otros); $count++) {
            $data = array(
                'id_asignacion' => $asignaciones->id,
                'id_operador' => $request->get('id_operador'),
                'otros' => $otros[$count],
            );

            GastosOperadores::create($data);
        }


        if ($request->hasFile('comprobante_gasolina')) {
            // Itera sobre cada imagen cargada
            foreach ($request->file('comprobante_gasolina') as $image) {
                // Genera un nombre único para la imagen
                $fileName = uniqid() . $image->getClientOriginalName();

                // Guarda la imagen en la carpeta deseada
                $path = public_path() . '/comprobantes';
                $image->move($path, $fileName);

                // Crea una nueva instancia de ImagenProducto y guarda los datos en la base de datos
                $imagenProducto = new ComprobanteGastos;
                $imagenProducto->imagen = $fileName;
                $imagenProducto->id_asignacion = $cotizaciones->id;
                $imagenProducto->tipo = 'Gasolina';
                $imagenProducto->save();
            }
        }

        if ($request->hasFile('comprobante_casetas')) {
            // Itera sobre cada imagen cargada
            foreach ($request->file('comprobante_casetas') as $image) {
                // Genera un nombre único para la imagen
                $fileName = uniqid() . $image->getClientOriginalName();

                // Guarda la imagen en la carpeta deseada
                $path = public_path() . '/comprobantes';
                $image->move($path, $fileName);

                // Crea una nueva instancia de ImagenProducto y guarda los datos en la base de datos
                $imagenProducto = new ComprobanteGastos;
                $imagenProducto->imagen = $fileName;
                $imagenProducto->id_asignacion = $cotizaciones->id;
                $imagenProducto->tipo = 'Casetas';
                $imagenProducto->save();
            }
        }

        if ($request->hasFile('comprobante_otros')) {
            // Itera sobre cada imagen cargada
            foreach ($request->file('comprobante_otros') as $image) {
                // Genera un nombre único para la imagen
                $fileName = uniqid() . $image->getClientOriginalName();

                // Guarda la imagen en la carpeta deseada
                $path = public_path() . '/comprobantes';
                $image->move($path, $fileName);

                // Crea una nueva instancia de ImagenProducto y guarda los datos en la base de datos
                $imagenProducto = new ComprobanteGastos;
                $imagenProducto->imagen = $fileName;
                $imagenProducto->id_asignacion = $cotizaciones->id;
                $imagenProducto->tipo = 'Otros';
                $imagenProducto->save();
            }
        }

        Session::flash('edit', 'Se ha editado sus datos con exito');
        return redirect()->back()
            ->with('success', 'Estatus updated successfully');
    }

    public function show_pagos($id){
        $operador = Operador::find($id);
        $pagos_pendientes = Asignaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('estatus_pagado', '=', 'Pendiente Pago')
        ->where('id_operador', '=', $id)
        ->get();

        $bancos = Bancos::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();

        return view('operadores.pagos_pendientes', compact('pagos_pendientes', 'operador', 'bancos'));
    }

    public function destroy($id)
    {
        $operador = Operador::findOrFail($id);
    
        // Verificar si tiene pagos pendientes
        $tieneRestante = Asignaciones::where('id_operador', $id)
            ->where('id_empresa', auth()->user()->id_empresa)
            ->where('restante_pago_operador', '>', 0)
            ->exists();
    
        if ($tieneRestante) {
            $nombre = $operador->nombre;
            return redirect()->back()->with('operador_con_restante', "El operador <strong>$nombre</strong> tiene pagos pendientes y no puede ser dado de baja.");
        }
    
        $operador->delete();
    
        Session::flash('success', 'Operador dado de baja correctamente.');
        return redirect()->back();
    }
    
public function restore($id)
{
    $operador = Operador::withTrashed()->findOrFail($id);
    $operador->restore();

    Session::flash('success', 'Operador reactivado correctamente.');
    return redirect()->back();
}
}
