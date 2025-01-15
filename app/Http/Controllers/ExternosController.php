<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotizaciones;
use App\Models\Client;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Auth;

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

    public function misViajes(){
        return view('cotizaciones.externos.viajes_solicitados');
    }

    public function getContenedoresPendientes(Request $request){
        $condicion = ($request->estatus == 'En espera') ? '=' : '!=';
        $contenedoresPendientes = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
                                                ->where('cotizaciones.id_cliente' ,'=',Auth::User()->id_cliente)
                                                ->where('estatus',$condicion,'En espera')
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
                "FormatoCartaPorte" => ($c->carta_porte == null) ? false : true,
                "PreAlta" => ($c->img_boleta == null) ? false : true,
                "FechaSolicitud" => Carbon::parse($c->created_at)->format('Y-m-d')
            ];
        });

        return $resultContenedores;
    }

    public function getContenedoresAsignables(Request $request){
        $contenedoresPendientes = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
                                                ->join('clients as cl','cotizaciones.id_cliente','=','cl.id')
                                                ->join('subclientes as sc','cotizaciones.id_subcliente','=','sc.id')
                                                ->where('estatus','=','NO ASIGNADA')
                                                ->orderBy('created_at', 'desc')
                                                ->selectRaw('cotizaciones.*, d.num_contenedor,d.doc_eir,doc_ccp ,d.boleta_liberacion,d.doda,cl.nombre as cliente,sc.nombre as subcliente')
                                                ->get();

        $resultContenedores = 
        $contenedoresPendientes->map(function($c){
            return [
                "IdContenedor" => $c->id,
                "Cliente" => $c->cliente,
                "SubCliente" => $c->subcliente,
                "NumContenedor" => $c->num_contenedor,
                "Origen" => $c->origen, 
                "Destino" => $c->destino, 
                "Peso" => $c->peso_contenedor,
                "BoletaLiberacion" => ($c->boleta_liberacion == null) ? false : true,
                "DODA" => ($c->doda == null) ? false : true,
                "FormatoCartaPorte" => ($c->carta_porte == null) ? false : true,
                "IdCliente" => $c->id_cliente,
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

            if($contenedor->doda != null && $contenedor->boleta_liberacion != null){
                $cotizacion = Cotizaciones::where('id',$cotizacion)->first();
                $cotizacion->estatus = 'NO ASIGNADA';
                $cliente = Client::where('id',$cotizacion->id_cliente)->first();
                $cotizacion->save();

                $emailList = [env('MAIL_NOTIFICATIONS'),Auth::User()->email];

                Mail::to($emailList)->send(new \App\Mail\NotificaCotizacionMail($contenedor,$cliente));

            }
        }catch(\Throwable $t){
          \Log::channel('daily')->info('Maniobra no se pudo enviar al admin. id: '.$cotizacion.'. '.$t->getMessage());
        }
        
    }

    public function cancelarViaje(Request $request){
       // $documCotizacion = DocumCotizacion::where('num_contenedor',$request->numContenedor)->first();
        $cotizacion = Cotizaciones::join('docum_cotizacion as d','cotizaciones.id', '=', 'd.id_cotizacion')
        ->where('d.num_contenedor',$request->numContenedor)
        ->first();

        if($cotizacion->estatus == 'Cancelada') return response()->json(["Titulo" => "Previamente Cancelado","Mensaje" => "El contenedor $request->numContenedor fue cancelado previamente","TMensaje" => "info"]);

        Cotizaciones::where('id',$cotizacion->id)->update(['estatus'=>'Cancelada']);

        $emailList = [env('MAIL_NOTIFICATIONS'),Auth::User()->email];
        $cotizacionCancelar = Cotizaciones::where('id',$cotizacion->id)->first();
        Mail::to($emailList)->send(new \App\Mail\NotificaCancelarViajeMail($cotizacionCancelar,$request->numContenedor));

        return response()->json(["Titulo" => "Cancelado correctamente","Mensaje" => "Se canceló el viaje con el Núm. Contenedor $request->numContenedor","TMensaje" => "success"]);
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

    public function fileManager(Request $r){
        return view('cotizaciones.externos.file-manager');
    }

    public function getFilesProperties(Request $r){
        $documentos = DocumCotizacion::where('id',754)->first();
        $documentList = ["file" => $documentos->boleta_liberacion,'name'=> "Boleta de liberacion"];
        return json_enconde($documentList);
    }
}
