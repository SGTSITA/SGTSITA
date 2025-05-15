<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotizaciones;
use App\Models\Client;
use App\Models\SatFormaPago;
use App\Models\SatMetodoPago;
use App\Models\SatUsoCfdi;
use App\Models\DocumCotizacion;
use App\Models\Correo;
use Illuminate\Support\Facades\Mail;
use App\Traits\CommonTrait;
use Carbon\Carbon;
use ZipArchive;
use Auth;

class ExternosController extends Controller
{
    public function solicitudSimple(){
        $formasPago = SatFormaPago::get();
        $metodosPago = SatMetodoPago::get();
        $usoCfdi = SatUsoCfdi::get();
        return view('cotizaciones.externos.solicitud_simple',["formasPago" => $formasPago, "metodosPago" => $metodosPago, "usoCfdi" => $usoCfdi]);
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

    public function ZipDownload($zipFile){
       return response()->download($zipFile)->deleteFileAfterSend(true);
    }

    public function CfdiToZip(Request $request){
        try{
            $zipName = "carta-porte-".uniqid().".zip";

            $zipPath = public_path($zipName); // Ruta en la carpeta public

            $zip = new ZipArchive;
    
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {

                foreach($request->contenedores as $c){
                    $cotizacionQuery = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
                    ->where('d.num_contenedor',$c['NumContenedor']);
    
                    $cotizacion = $cotizacionQuery->first();
                    $pdf = $cotizacion->carta_porte;
                    $xml = $cotizacion->carta_porte_xml;

                    if(\File::exists(public_path('/cotizaciones/cotizacion'.$cotizacion->id_cotizacion."/$xml"))){
                   
                        $zip->addFile(public_path('/cotizaciones/cotizacion'.$cotizacion->id_cotizacion."/$xml"),$c['NumContenedor'].'.xml');
                    }
    
                    if(\File::exists(public_path('/cotizaciones/cotizacion'.$cotizacion->id_cotizacion."/$pdf"))){
                        $zip->addFile(public_path('/cotizaciones/cotizacion'.$cotizacion->id_cotizacion."/$pdf"), $c['NumContenedor'].'.pdf');
                    }
                    
                   
                }
        
                $zip->close();
                
            } 

            

           
            return response()->json([
                'zipUrl' => ($zipName),
                'success' => true
            ]);
        }catch(\Throwable $t){
            return response()->json([
                'message' => $t->getMessage(),
                'success' =>false
            ]);
        }
    }

    public function getContenedoresPendientes(Request $request){
        $condicion = ($request->estatus == 'En espera') ? '=' : '!=';
        $contenedoresPendientes = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
                                                ->where('cotizaciones.id_cliente' ,'=',Auth::User()->id_cliente)
                                                ->where('estatus',$condicion,'En espera')
                                                ->orderBy('created_at', 'desc')
                                                ->selectRaw('cotizaciones.*, d.num_contenedor,d.doc_eir,doc_ccp ,d.boleta_liberacion,d.doda,d.foto_patio')
                                                ->get();
                                                

        $resultContenedores = 
        $contenedoresPendientes->map(function($c){
            return [
                "NumContenedor" => $c->num_contenedor,
                "Estatus" => ($c->estatus == "NO ASIGNADA") ? "Viaje solicitado" : $c->estatus,
                "Origen" => $c->origen, 
                "Destino" => $c->destino, 
                "Peso" => $c->peso_contenedor,
                "BoletaLiberacion" => ($c->boleta_liberacion == null) ? false : true,
                "DODA" => ($c->doda == null) ? false : true,
                "foto_patio" => ($c->foto_patio == null) ? false : true,
                "FormatoCartaPorte" => ($c->doc_ccp == null) ? false : true,
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
                "Estatus" => $c->estatus,
                "Origen" => $c->origen, 
                "Destino" => $c->destino, 
                "Peso" => $c->peso_contenedor,
                "BoletaLiberacion" => ($c->boleta_liberacion == null) ? false : true,
                "DODA" => ($c->doda == null) ? false : true,
                "FormatoCartaPorte" => ($c->doc_ccp == null) ? false : true,
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

                $cuentasCorreo = [env('MAIL_NOTIFICATIONS'),Auth::User()->email];
                $cuentasCorreo2 = Correo::where('cotizacion_nueva',1)->get()->pluck('correo')->toArray();
                \Log::debug($cuentasCorreo);
                Mail::to($cuentasCorreo)->send(new \App\Mail\NotificaCotizacionMail($contenedor,$cliente));

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
        return view('cotizaciones.externos.file-manager',["numContenedor" => $r->numContenedor]);
    }

    public function sendFiles1(Request $r){
        try{

            $files = ( $r->attachmentFiles);
$attachment = [];
            foreach($files as $file){
                array_push($attachment,public_path($file['file']));
            }

            $emailList = (strlen($r->secondaryEmail) > 0) ? [$r->email,$r->secondaryEmail] : [$r->email];

            Mail::to($emailList)
            ->send(new \App\Mail\CustomMessageMail($r->subject,$r->message, $attachment));
            return response()->json(["TMensaje" => "success", "Titulo" => "Mensaje enviado correctamente","Mensaje" => "Se ha enviado mensaje con los archivos seleccionados"]);

        }catch(\Trhowable $t){
            return response()->json(["TMensaje" => "error", "Titulo" => "Mensaje no enviado","Mensaje" => "Ocurrio un error mientras enviabamos su mensaje: ".$t->getMessage()]);
        }
    }

    public function fileProperties($id,$file,$title,){
        $path = public_path('cotizaciones/cotizacion'.$id.'/'.$file);
        if(\File::exists($path)){
            return [
                "filePath" => $file,
                'fileName'=> $title,
                "fileDate" => CommonTrait::obtenerFechaEnLetra(date("Y-m-d", filemtime($path))),
                "fileSize" => CommonTrait::calculateFileSize(filesize($path)),
                "fileType" => pathinfo($path, PATHINFO_EXTENSION),
                "identifier" => $id,
                "fileCode" => iconv('UTF-8', 'ASCII//TRANSLIT',str_replace(' ','-',$title))
                ];
                //iconv('UTF-8', 'ASCII//TRANSLIT', $cadena);
        }else{
            return [];
        }
    }

    public function getFilesProperties($numContenedor){

        $documentos = DocumCotizacion::where('num_contenedor',$numContenedor)->first();
        $folderId = $documentos->id;
        $documentList = array();

        if(!is_null($documentos->doda)){
            $doda = self::fileProperties($folderId,$documentos->doda,'Doda');
            if(sizeof($doda) > 0) array_push($documentList,$doda);
        }

        if(!is_null($documentos->boleta_liberacion)){
            $boleta_liberacion = self::fileProperties($folderId,$documentos->boleta_liberacion,'Boleta de liberación');
            if(sizeof($boleta_liberacion) > 0) array_push($documentList,$boleta_liberacion);
        }


        if(!is_null($documentos->doc_ccp)){
            $doc_ccp = self::fileProperties($folderId,$documentos->doc_ccp,'Formato para Carta porte');
            if(sizeof($doc_ccp) > 0) array_push($documentList,$doc_ccp);
        }

       

        if(!is_null($documentos->doc_eir)){
            $doc_eir = self::fileProperties($folderId,$documentos->doc_eir,'eir');
            if(sizeof($doc_eir) > 0) array_push($documentList,$doc_eir);
        }
        if(!is_null($documentos->foto_patio)){
           
            $doc_foto_patio = self::fileProperties($folderId, $documentos->foto_patio, 'Foto patio');
            if(sizeof($doc_foto_patio) > 0) array_push($documentList, $doc_foto_patio);
        }
        $cotizacion = Cotizaciones::where('id',$documentos->id_cotizacion)->first();

        if(!is_null($cotizacion->img_boleta)){
            $preAlta = self::fileProperties($folderId,$cotizacion->img_boleta,'Pre-Alta');
            if(sizeof($preAlta) > 0) array_push($documentList,$preAlta);
        }

        if(!is_null($cotizacion->carta_porte)){
            $cpPDF = self::fileProperties($folderId,$cotizacion->carta_porte,'Carta Porte');
            if(sizeof($cpPDF) > 0) array_push($documentList,$cpPDF);
        }

        if(!is_null($cotizacion->carta_porte_xml)){
            $cpXML = self::fileProperties($folderId,$cotizacion->carta_porte_xml,'Carta Porte XML');
            if(sizeof($cpXML) > 0) array_push($documentList,$cpXML);
        }
        

        return ["data"=>$documentList,"numContenedor" => $numContenedor,"documentos" =>$documentos];
                

    }

    public function filePropertiescoordenadas($id,$file,$title,){
        $path = public_path('coordenadas/'.$id.'/'.$file);
        if(\File::exists($path)){
            return [
                "filePath" => $file,
                'fileName'=> $title,
                "fileDate" => CommonTrait::obtenerFechaEnLetra(date("Y-m-d", filemtime($path))),
                "fileSize" => CommonTrait::calculateFileSize(filesize($path)),
                "fileType" => pathinfo($path, PATHINFO_EXTENSION),
                "identifier" => $id,
                "fileCode" => iconv('UTF-8', 'ASCII//TRANSLIT',str_replace(' ','-',$title))
                ];
                //iconv('UTF-8', 'ASCII//TRANSLIT', $cadena);
        }else{
            return [];
        }
    }
}
