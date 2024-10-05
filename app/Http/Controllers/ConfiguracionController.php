<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use DB;
use Session;

class ConfiguracionController extends Controller
{
    public function index(Request $request ,$id)
    {
        $configuracion = Configuracion::find($id);

        return view('configuracion.index', compact('configuracion'));
    }

    public function update(Request $request,$id)
    {
        $this->validate($request, [
            'nombre_sistema' => 'required',
            'color_principal' => 'required',
        ]);

        $configuracion = Configuracion::find($id);
        $configuracion->nombre_sistema = $request->get('nombre_sistema');
        $configuracion->color_principal = $request->get('color_principal');
        $configuracion->color_iconos_sidebar = $request->get('color_iconos_sidebar');
        $configuracion->color_iconos_cards = $request->get('color_iconos_cards');
        $configuracion->color_boton_add = $request->get('color_boton_add');
        $configuracion->icon_boton_add = $request->get('icon_boton_add');
        $configuracion->color_boton_save = $request->get('color_boton_save');
        $configuracion->icon_boton_save = $request->get('icon_boton_save');
        $configuracion->color_boton_close = $request->get('color_boton_close');
        $configuracion->icon_boton_close = $request->get('icon_boton_close');

        if ($request->hasFile("logo")) {

            $file = $request->file('logo');
            $path = public_path() . '/logo';
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);

            $configuracion->logo = $fileName;
        }

        if ($request->hasFile("favicon")) {

            $file = $request->file('favicon');
            $path = public_path() . '/favicon';
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);

            $configuracion->favicon = $fileName;
        }
        $configuracion->update();

        Session::flash('edit', 'Se ha editado sus datos con exito');
        return redirect()->back()
        ->with('success', 'empresae created successfully.');
    }
}
