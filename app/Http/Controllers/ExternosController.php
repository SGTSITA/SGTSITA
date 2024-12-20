<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotizaciones;
use App\Models\Client;
use Illuminate\Support\Facades\Mail;

class ExternosController extends Controller
{
    public function solicitudSimple(){
        return view('cotizaciones.externos.solicitud_simple');
    }

    public function solicitudMultiple(){
        return view('cotizaciones.externos.solicitud_multiple');
    }

    public function viajesDocuments(){
        return view('cotizaciones.externos.viajes_documentacion');
    }

    public function getContenedoresPendientes(Request $request){
        $contenedoresPendientes = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
                                                ->where('cotizaciones.id_empresa' ,'=',auth()->user()->id_empresa)
                                                ->where('estatus','=','En espera')
                                                ->orderBy('created_at', 'desc')
                                                ->selectRaw('cotizaciones.*, d.num_contenedor,d.doc_eir,doc_ccp ,d.boleta_liberacion,d.doda')
                                                ->get();

        $resultContenedores = 
        $contenedoresPendientes->map(function($c){
            return [
                "NumContenedor" => $c->num_contenedor,
                "Origen" => $c->origen, 
                "Destino" => $c->destino, 
                "Peso" => $c->peso_contenedor,
                "BoletaLiberacion" => ($c->boleta_liberacion == null) ? false : true,
                "DODA" => ($c->doda == null) ? false : true,
                "CartaPorte" => ($c->carta_porte == null) ? false : true,
                "PreAlta" => ($c->img_boleta == null) ? false : true
            ];
        });

        return $resultContenedores;
    }

    public function getContenedoresAsignables(Request $request){
        $contenedoresPendientes = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
                                                ->where('estatus','=','NO ASIGNADA')
                                                ->orderBy('created_at', 'desc')
                                                ->selectRaw('cotizaciones.*, d.num_contenedor,d.doc_eir,doc_ccp ,d.boleta_liberacion,d.doda')
                                                ->get();

        $resultContenedores = 
        $contenedoresPendientes->map(function($c){
            return [
                "NumContenedor" => $c->num_contenedor,
                "Origen" => $c->origen, 
                "Destino" => $c->destino, 
                "Peso" => $c->peso_contenedor,
                "BoletaLiberacion" => ($c->boleta_liberacion == null) ? false : true,
                "DODA" => ($c->doda == null) ? false : true,
                "CartaPorte" => ($c->carta_porte == null) ? false : true
            ];
        });

        return $resultContenedores;
    }

    public static function confirmarDocumentos($cotizacion){
        try{
            $contenedor = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
            ->where('cotizaciones.id' ,'=',$cotizacion)
            ->where('estatus','=','En espera')
            ->orderBy('created_at', 'desc')
            ->selectRaw('cotizaciones.*, d.num_contenedor,d.doc_eir,doc_ccp ,d.boleta_liberacion,d.doda')
            ->first();

            if($contenedor->doda != null && $contenedor->boleta_liberacion != null && $contenedor->img_boleta != null){

                $contenedor->estatus = 'NO ASIGNADA';
                

                $cliente = Client::where('id',$contenedor->id_cliente)->first();
                $contenedor->save();

                Mail::to('alejandroc.carlos@gmail.com')->send(new \App\Mail\NotificaCotizacionMail($contenedor,$cliente));

            }
        }catch(\Throwable $t){
          \Log::channel('daily')->info('Maniobra no se pudo enviar al admin. id: '.$cotizacion.'. '.$t->getMessage());
        }
        
    }

    public function selector(Request $request){
       // return $request->transac;
        switch($request->transac){
            case "simple":
                $path = 'viajes.simple';
                break;
            case "multiple":
                $path = 'viajes.multiple';
                break;
            case "documents":
                $path = 'viajes.documents';
                break;
            default:
                $path = 'viajes.index';
        }

        return redirect()->route($path);
    }
}
