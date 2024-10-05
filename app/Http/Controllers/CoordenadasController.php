<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coordenadas;
use Carbon\Carbon;

class CoordenadasController extends Controller
{
    public function index($id){

        $coordenadas = Coordenadas::where('id_asignacion' ,'=', $id)->first();

        return view('coordendas.index',compact('coordenadas'));

    }

    public function edit(Request $request, $id){

        $fecha = Carbon::now();

        $coordenadas = Coordenadas::find($id);

        if($request->get('validaroperador')){
            $coordenadas->validaroperador = $request->get('validaroperador');
        }

        $coordenadas->registro_puerto = $request->get('latitud_longitud_registro_puerto');
        $coordenadas->registro_puerto_datatime = $fecha;

        $coordenadas->dentro_puerto = $request->get('latitud_longitud_dentro_puerto');
        $coordenadas->dentro_puerto_datatime = $fecha;

        $coordenadas->descarga_vacio = $request->get('latitud_longitud_descarga_vacio');
        $coordenadas->descarga_vacio_datatime = $fecha;

        $coordenadas->cargado_contenedor = $request->get('latitud_longitud_cargado_contenedor');
        $coordenadas->cargado_contenedor_datatime = $fecha;

        $coordenadas->modulado_tipo = $request->get('modulado_tipo');
        $coordenadas->modulado_tipo_datatime = $fecha;

        $coordenadas->fila_fiscal = $request->get('latitud_longitud_fila_fiscal');
        $coordenadas->fila_fiscal_datatime = $fecha;

        $coordenadas->en_destino = $request->get('latitud_longitud_en_destino');
        $coordenadas->en_destino_datatime = $fecha;

        $coordenadas->inicio_descarga = $request->get('latitud_longitud_inicio_descarga');
        $coordenadas->inicio_descarga_datatime = $fecha;

        $coordenadas->fin_descarga = $request->get('latitud_longitud_fin_descarga');
        $coordenadas->fin_descarga_datatime = $fecha;

        $coordenadas->recepcion_doc_firmados = $request->get('latitud_longitud_recepcion_doc_firmados');
        $coordenadas->recepcion_doc_firmados_datatime = $fecha;

        $coordenadas->update();

        return redirect()->back();

    }
}
