<?php

namespace App\Http\Controllers;
use App\Models\Client;
use App\Models\Catalogo;
use Carbon\Carbon;
use Session;
use PDF;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index(){

        $catalogos = Catalogo::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        return view('catalogo.index', compact('catalogos'));
    }

    public function create(){
        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();

        return view('catalogo.crear',compact('clientes'));
    }

    public function store(Request $request){

        if($request->get('nombre_cliente') == NULL){
            $cliente = $request->get('id_cliente');
        }else{
            $cliente = new Client;
            $cliente->nombre = $request->get('nombre_cliente');
            $cliente->correo = $request->get('correo_cliente');
            $cliente->telefono = $request->get('telefono_cliente');
            $cliente->id_empresa = auth()->user()->id_empresa;
            $cliente->save();

            $cliente = $cliente->id;
        }

        $catalogo = new Catalogo;
        $catalogo->id_cliente = $cliente;
        $catalogo->id_subcliente = $request->get('id_subcliente');
        $catalogo->destino = $request->get('destino');
        $catalogo->tamano = $request->get('tamano');
        $catalogo->peso_contenedor = $request->get('peso_contenedor');
        $catalogo->otro = $request->get('otro');
        $catalogo->iva = $request->get('iva');
        $catalogo->retencion = $request->get('retencion');
        $catalogo->peso_reglamentario = $request->get('peso_reglamentario');
        $catalogo->precio_sobre_peso = $request->get('precio_sobre_peso');
        $catalogo->sobrepeso = $request->get('sobrepeso');
        $catalogo->precio_viaje = $request->get('precio_viaje');
        $catalogo->burreo = $request->get('burreo');
        $catalogo->maniobra = $request->get('maniobra');
        $catalogo->estadia = $request->get('estadia');
        $precio_tonelada = str_replace(',', '', $request->get('precio_tonelada'));
        $catalogo->precio_tonelada = $precio_tonelada;
        $catalogo->id_empresa = auth()->user()->id_empresa;
        $catalogo->save();


        Session::flash('success', 'Se ha guardado sus datos con exito');
        return redirect()->route('index.catalogo')
            ->with('success', 'catalogo created successfully.');

    }

    public function pdf($id){
        $catalogo = Catalogo::where('id', '=', $id)->first();

        $fecha = date('Y-m-d');
        $fechaCarbon = Carbon::parse($fecha);

        $pdf = PDF::loadView('catalogo.pdf', compact('catalogo','fecha', 'fechaCarbon'))->setPaper([0, 0, 595, 1200], 'landscape');
       return $pdf->stream();
       //  return $pdf->download('catalogo'.$catalogo->Cliente->nombre.'_#'.$catalogo->id.'.pdf');
    }
}
