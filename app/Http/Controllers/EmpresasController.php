<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Configuracion;
use App\Models\Empresas;
use Session;

class EmpresasController extends Controller
{
    public function index()
    {
        $empresas = Empresas::where('id','=',auth()->user()->Empresa->id)->orderBy('created_at', 'desc')->get();

        $configuraciones = Configuracion::get();

        return view('empresa.index', compact('empresas','configuraciones'));
    }


    public function create()
    {
        $empresa = new Empresas();
        return view('empresa.create', compact('empresa'));
    }


    public function store(Request $request){

        $this->validate($request, [
            'nombre' => 'required',
            'correo' => 'required',
            'telefono' => 'required'
        ]);

        $fechaActual = date('Y-m-d');

        $empresa = new Empresas;
        $empresa->nombre = $request->get('nombre');
        $empresa->correo = $request->get('correo');
        $empresa->telefono = $request->get('telefono');
        $empresa->direccion = $request->get('direccion');
        $empresa->regimen_fiscal = $request->get('regimen_fiscal');
        $empresa->rfc = $request->get('rfc');
        $empresa->fecha = $fechaActual;
        $empresa->save();

        // Crear la configuración asociada
        $configuracion = new Configuracion;
        $configuracion->nombre_sistema = $request->get('nombre');
        $configuracion->color_principal = '#ccc'; // Asignar otros valores predeterminados si es necesario
        $configuracion->logo = '';
        $configuracion->favicon = '';
        $configuracion->color_iconos_sidebar = '';
        $configuracion->color_iconos_cards = '';
        $configuracion->color_boton_add = '';
        $configuracion->icon_boton_add = '';
        $configuracion->color_boton_save = '';
        $configuracion->icon_boton_save = '';
        $configuracion->color_boton_close = '';
        $configuracion->icon_boton_close = '';
        $configuracion->save();

        // Actualizar la empresa con el id_configuracion
        $empresa->id_configuracion = $configuracion->id;
        $empresa->save();

        Session::flash('success', 'Se ha guardado sus datos con exito');
        return redirect()->back()
            ->with('success', 'empresae created successfully.');

    }

    public function update(Request $request, Empresas $id)
    {


        $id->update($request->all());

        Session::flash('edit', 'Se ha editado sus datos con exito');
        return redirect()->route('empresas.index')
            ->with('success', 'empresa updated successfully');
    }
}
