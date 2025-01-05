<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;
use Session;

class EquiposController extends Controller
{
    public function index(){
        $fechaActual = date('Y-m-d');

        $equipos_dolys = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Dolys')->orderBy('created_at', 'desc')->get();
        $equipos_chasis = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Chasis / Plataforma')->orderBy('created_at', 'desc')->get();
        $equipos_camiones = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Tractos / Camiones')->orderBy('created_at', 'desc')->get();

        return view('equipos.index', compact('equipos_dolys','equipos_chasis','equipos_camiones', 'fechaActual'));
    }

    public function store(Request $request){


        $proveedor = new Equipo;

        if($request->get('marca') != NULL){
            $proveedor->folio = $request->get('folio');
            $proveedor->tipo = 'Tractos / Camiones';
            $proveedor->id_equipo = $request->get('id_equipo');
            $proveedor->marca = $request->get('marca');
            $proveedor->motor = $request->get('motor');
            $proveedor->placas = $request->get('placas');
            $proveedor->year = $request->get('year');
            $proveedor->num_serie = $request->get('num_serie');
            $proveedor->modelo = $request->get('modelo');
            $proveedor->acceso = $request->get('acceso');

            if ($request->hasFile("tarjeta_circulacion")) {
                $file = $request->file('tarjeta_circulacion');
                $path = public_path() . '/equipos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $proveedor->tarjeta_circulacion = $fileName;
            }

            if ($request->hasFile("poliza_seguro")) {
                $file = $request->file('poliza_seguro');
                $path = public_path() . '/equipos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $proveedor->poliza_seguro = $fileName;
            }

            $proveedor->fecha = $request->get('fecha');
            $proveedor->save();
        }else if($request->get('marca_chasis') != NULL){

            $proveedor->tipo = 'Chasis / Plataforma';
            $proveedor->id_equipo = $request->get('id_equipo_chasis');
            $proveedor->marca = $request->get('marca_chasis');
            $proveedor->motor = $request->get('motor_chasis');
            $proveedor->placas = $request->get('placas_chasis');
            $proveedor->year = $request->get('year_chasis');
            $proveedor->num_serie = $request->get('num_serie_chasis');
            $proveedor->modelo = $request->get('modelo_chasis');
            $proveedor->acceso = $request->get('acceso_chasis');

            if ($request->hasFile("tarjeta_circulacion_chasis")) {
                $file = $request->file('tarjeta_circulacion_chasis');
                $path = public_path() . '/equipos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $proveedor->tarjeta_circulacion = $fileName;
            }

            if ($request->hasFile("poliza_seguro_chasis")) {
                $file = $request->file('poliza_seguro_chasis');
                $path = public_path() . '/equipos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $proveedor->poliza_seguro = $fileName;
            }

            $proveedor->folio = $request->get('folio');
            $proveedor->fecha = $request->get('fecha_chasis');
            $proveedor->save();

        }else if($request->get('marca_doly') != NULL){
            $proveedor->tipo = 'Dolys';
            $proveedor->folio = $request->get('folio');
            $proveedor->id_equipo = $request->get('id_equipo_doly');
            $proveedor->year = $request->get('year_doly');
            $proveedor->marca = $request->get('marca_doly');
            $proveedor->placas = $request->get('placas_doly');
            $proveedor->num_serie = $request->get('num_serie_doly');
            $proveedor->fecha = $request->get('fecha_doly');

            if ($request->hasFile("tarjeta_circulacion_doly")) {
                $file = $request->file('tarjeta_circulacion_doly');
                $path = public_path() . '/equipos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $proveedor->tarjeta_circulacion = $fileName;
            }

            if ($request->hasFile("poliza_seguro_doly")) {
                $file = $request->file('poliza_seguro_doly');
                $path = public_path() . '/equipos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $proveedor->poliza_seguro = $fileName;
            }

            $proveedor->save();
        }

        Session::flash('success', 'Se ha guardado sus datos con exito');
        return redirect()->back()
            ->with('success', 'Equipo creado con exito.');

    }

    public function update(Request $request, Equipo $id)
    {

        if ($request->hasFile("tarjeta_circulacion")) {
            $file = $request->file('tarjeta_circulacion');
            $path = public_path() . '/equipos';
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $request->tarjeta_circulacion = $fileName;
        }

        if ($request->hasFile("poliza_seguro")) {
            $file = $request->file('poliza_seguro');
            $path = public_path() . '/equipos';
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $request->poliza_seguro = $fileName;
        }

        $id->update($request->all());

        Session::flash('edit', 'Se ha editado sus datos con exito');
        return redirect()->back()->with('success', 'Equipo actualizado exitosamente');

    }

    public function desactivar(Request $request, Equipo $id)
    {

        $id->update($request->all());

        Session::flash('edit', 'Se ha editado sus datos con exito');
        return redirect()->back()->with('success', 'Equipo actualizado exitosamente');

    }
}
