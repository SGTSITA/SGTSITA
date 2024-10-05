<?php

namespace App\Http\Controllers;

use App\Models\Bancos;
use App\Models\GastosGenerales;
use Illuminate\Http\Request;

class GastosGeneralesController extends Controller
{
    public function index(){
        $gastos = GastosGenerales::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $bancos = Bancos::get();

        return view('gastos_generales.index', compact('gastos', 'bancos'));
    }

    public function store(Request $request, GastosGenerales $id)
    {
        $fechaActual = date('Y-m-d');
        $gasto_general = new GastosGenerales;
        $gasto_general->motivo = $request->get('motivo');
        $gasto_general->monto1 = $request->get('monto1');
        $gasto_general->metodo_pago1 = $request->get('metodo_pago1');
        $gasto_general->id_banco1 = $request->get('id_banco1');
        $gasto_general->fecha = $fechaActual;
        $gasto_general->id_empresa = auth()->user()->id_empresa;
        $gasto_general->save();

        return redirect()->back()->with('success', 'Gasto agregado exitosamente');
    }
}
