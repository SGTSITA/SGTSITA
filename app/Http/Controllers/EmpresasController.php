<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Configuracion;
use App\Models\Empresas;
use App\Models\TipoEmpresa;
use App\Models\User;
use App\Models\SatRegimenFiscal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class EmpresasController extends Controller
{
    public function index()
    {
        $empresas = Empresas::where('id', '=', auth()->user()->Empresa->id)->orderBy('created_at', 'desc')->get();

        $configuraciones = Configuracion::get();
        $tipoEmpresa = TipoEmpresa::get();
        $satRegimen = SatRegimenFiscal::get();

        return view('empresa.index', compact('empresas', 'configuraciones', 'tipoEmpresa', 'satRegimen'));
    }


    public function create()
    {
        $empresa = new Empresas();
        return view('empresa.create', compact('empresa'));
    }


    public function store(Request $request)
    {

        try {
            DB::beginTransaction();
            $this->validate($request, [
                'nombre' => 'required',
                'correo' => 'required|unique:empresas,correo',
                'rfc' => 'required|unique:empresas,rfc',
                'telefono' => 'required'
            ]);

            //return back()->withErrors(['correo' => 'El correo ya está registrado.'])->withInput();

            $fechaActual = date('Y-m-d');

            $empresa = new Empresas();
            $empresa->nombre = $request->get('nombre');
            $empresa->correo = $request->get('correo');
            $empresa->telefono = $request->get('telefono');
            $empresa->direccion = $request->get('direccion');
            $empresa->regimen_fiscal = $request->get('regimen_fiscal');
            $empresa->rfc = $request->get('rfc');
            $empresa->id_tipo_empresa = $request->id_tipo_empresa;
            $empresa->id_sat_regimen = $request->id_regimen_fiscal;
            $empresa->fecha = $fechaActual;
            $empresa->save();

            // Crear la configuración asociada
            $configuracion = new Configuracion();
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

            //crear el usuario con el correo proporcionado
            $pass = substr($request->rfc, 0, strlen($request->rfc) - 3);
            $rol = ($request->id_tipo_empresa == 2) ? 'PROVEEDOR' : 'Admin';
            $usuario =  new User();
            $usuario->name = 'Admin: '.$request->get('nombre');
            $usuario->email = $request->get('correo');
            $usuario->password = Hash::make(strtolower($pass));
            $usuario->id_cliente = 0;
            $usuario->id_empresa = $empresa->id;
            $usuario->assignRole($rol);

            $usuario->save();
            DB::commit();
            Session::flash('success', 'Se ha guardado sus datos con exito');
            return redirect()->back()
                ->with('success', 'empresae created successfully.');
        } catch (\Throwable $t) {
            Log::debug("error: ".$t->getMessage());
            DB::rollback();
            return redirect()->back();
        }


    }

    public function update(Request $request, Empresas $id)
    {


        $id->update($request->all());

        Session::flash('edit', 'Se ha editado sus datos con exito');
        return redirect()->route('empresas.index')
            ->with('success', 'empresa updated successfully');
    }
}
