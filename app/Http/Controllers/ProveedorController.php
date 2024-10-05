<?php

namespace App\Http\Controllers;

use App\Models\CuentasBancarias;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Session;

class ProveedorController extends Controller
{
    public function index(){
        $proveedores = Proveedor::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $cuentas = CuentasBancarias::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        return view('proveedores.index', compact('proveedores', 'cuentas'));
    }

    public function store(Request $request){
        $this->validate($request, [
            'nombre' => 'required',
            'correo' => 'required',
            'telefono' => 'required'
        ]);

        $fechaActual = date('Y-m-d');

        $proveedor = new Proveedor;
        $proveedor->nombre = $request->get('nombre');
        $proveedor->correo = $request->get('correo');
        $proveedor->telefono = $request->get('telefono');
        $proveedor->regimen_fiscal = $request->get('regimen_fiscal');
        $proveedor->direccion = $request->get('direccion');
        $proveedor->rfc = $request->get('rfc');
        $proveedor->tipo = $request->get('tipo');
        $proveedor->fecha = $fechaActual;
        $proveedor->save();

        Session::flash('success', 'Se ha guardado sus datos con exito');
        return redirect()->route('index.proveedores')
            ->with('success', 'Proveedor created successfully.');

    }

    public function update(Request $request, Proveedor $id)
    {

        $id->update($request->all());

        Session::flash('edit', 'Se ha editado sus datos con exito');
        return redirect()->back()->with('success', 'Client updated successfully');
    }

    public function cuenta(Request $request){

        $banco = new CuentasBancarias;
        $banco->nombre_beneficiario = $request->get('nombre_beneficiario');
        $banco->id_proveedores = $request->get('id_proveedores');
        $banco->cuenta_bancaria = $request->get('cuenta_bancaria');
        $banco->nombre_banco = $request->get('nombre_banco');
        $banco->cuenta_clabe = $request->get('cuenta_clabe');
        $banco->save();

        Session::flash('success', 'Se ha guardado sus datos con exito');
        return redirect()->route('index.proveedores')
            ->with('success', 'Proveedor created successfully.');

    }

    public function destroy($id){
    $cuenta = CuentasBancarias::find($id);

    if ($cuenta) {
        $cuenta->delete();
        return redirect()->back()->with('success', 'Cuenta bancaria eliminada correctamente.');
    }

    return redirect()->back()->with('error', 'La cuenta bancaria no fue encontrada.');
}
}
