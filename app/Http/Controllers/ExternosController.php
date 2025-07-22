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
        return view('cotizaciones.externos.solicitud_simple',["action" => "crear","formasPago" => $formasPago, "metodosPago" => $metodosPago, "usoCfdi" => $usoCfdi]);
    }

    public function editForm(Request $request){
        $formasPago = SatFormaPago::get();
        $metodosPago = SatMetodoPago::get();
        $usoCfdi = SatUsoCfdi::get();
        $cotizacion = Cotizaciones::with(['cliente', 'DocCotizacion'])
        ->whereHas('DocCotizacion', function ($query) use ($request) {
            $query->where('num_contenedor', $request->numContenedor);
        })
        ->first();
 
        return view('cotizaciones.externos.solicitud_simple',["action" => "editar","formasPago" => $formasPago, "metodosPago" => $metodosPago, "usoCfdi" => $usoCfdi, "cotizacion" => $cotizacion]);
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
                                                ->where('jerarquia', "!=",'Secundario')
                                                ->orderBy('created_at', 'desc')
                                                ->selectRaw('cotizaciones.*, d.num_contenedor,d.doc_eir,doc_ccp ,d.boleta_liberacion,d.doda,d.foto_patio')
                                                ->get();
                                                

        $resultContenedores = 
        $contenedoresPendientes->map(function($c){

            $numContenedor = $c->num_contenedor;
            $docCCP = ($c->doc_ccp == null) ? false : true;
            $doda = ($c->doda == null) ? false : true;
            $boletaLiberacion = ($c->boleta_liberacion == null) ? false : true;
            $cartaPorte = $c->carta_porte;
            $boletaVacio = ($c->img_boleta == null) ? false : true;
            $docEir = $c->doc_eir;
            $fotoPatio = ($c->foto_patio == null) ? false : true;
            $tipo = "Sencillo";

            if (!is_null($c->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $c->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion.Asignaciones')
                    ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $docCCP = ($docCCP && $secundaria->DocCotizacion->doc_ccp) ? true : false;
                    $doda = ($doda && $secundaria->DocCotizacion->doda) ? true : false;
                    $docEir = ($docEir && $secundaria->DocCotizacion->doc_eir) ? true : false;
                    $boletaLiberacion = ($boletaLiberacion && $secundaria->DocCotizacion->boleta_liberacion) ? true : false;
                    $cartaPorte = ($cartaPorte && $secundaria->carta_porte) ? true : false;
                    $boletaVacio = ($boletaVacio && $secundaria->img_boleta) ? true : false;
                    $fotoPatio = ($fotoPatio && $secundaria->foto_patio) ? true : false;
                    $numContenedor .= '  ' . $secundaria->DocCotizacion->num_contenedor;
                }

                $tipo = "Full";
            }

            return [
                "NumContenedor" => $numContenedor,
                "Estatus" => ($c->estatus == "NO ASIGNADA") ? "Viaje solicitado" : $c->estatus,
                "Origen" => $c->origen, 
                "Destino" => $c->destino, 
                "Peso" => $c->peso_contenedor,
                "BoletaLiberacion" => $boletaLiberacion,
                "DODA" => $doda,
                "foto_patio" => $fotoPatio,
                "FormatoCartaPorte" => $docCCP,
                "PreAlta" => $boletaVacio,
                "FechaSolicitud" => Carbon::parse($c->created_at)->format('Y-m-d'),
                "tipo" => $tipo,
                "id" => $c->id
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

        
        $docCCP = ($c->doc_ccp == null) ? false : true;
        $doda = ($c->doda == null) ? false : true;
        $boletaLiberacion = ($c->boleta_liberacion == null) ? false : true;

        $resultContenedores = 
        $contenedoresPendientes->map(function($c){

            if (!is_null($c->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $c->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion.Asignaciones')
                    ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $docCCP = ($docCCP && $secundaria->DocCotizacion->doc_ccp) ? true : false;
                    $doda = ($doda && $secundaria->DocCotizacion->doda) ? true : false;
                    $boletaLiberacion = ($boletaLiberacion && $secundaria->DocCotizacion->boleta_liberacion) ? true : false;
                    $numContenedor .= '  ' . $secundaria->DocCotizacion->num_contenedor;
                }

                $tipo = "Full";
            }

 
            return [
                "IdContenedor" => $c->id,
                "Cliente" => $c->cliente,
                "SubCliente" => $c->subcliente,
                "NumContenedor" => $numContenedor,
                "Estatus" => $c->estatus,
                "Origen" => $c->origen, 
                "Destino" => $c->destino, 
                "Peso" => $c->peso_contenedor,
                "BoletaLiberacion" => $boletaLiberacion,
                "DODA" => $doda,
                "FormatoCartaPorte" => $docCCP,
                "IdCliente" => $c->id_cliente,
                "tipo" => $tipo,
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

            if($r->channel == "WhatsApp"){
                if(sizeof($r->wa_phone) == 0) return response()->json(["Titulo" => "Seleccione contactos","TMensaje" => "warning" , "Mensaje" => "Para enviar la documentación seleccionada debe seleccionar un contacto"]);

                foreach($r->wa_phone as $phone){
                    //WhatsAppController::sendWhatsAppMessage($r->wa_phone, $r->message);
                    foreach($files as $file){
                        $urlFile = "https://sgt.gologipro.com/".public_path($file['file']);
                        $urlFile = str_replace('/var/www/html/SGTSITA/public/','',$urlFile);
                        $fileLabel = $file['documentSubject']." del contenedor ".$r->numContenedor;

                        $data = [
                            "messaging_product" => "whatsapp",
                            "to" => "52$phone",
                            "type" => "template",
                            "template" => [
                                "name" => "purchase_receipt_1",
                                "language" => [
                                    "code" => "es_MX"
                                ],
                                "components" => [
                                    [
                                        "type" => "header",
                                        "parameters" => [
                                            [
                                                "type" => "document",
                                                "document" => [
                                                    "link" => $urlFile,
                                                    "filename" => $file['documentSubject']
                                                ]
                                            ]
                                        ]
                                    ],
                                    [
                                        "type" => "body",
                                        "parameters" => [
                                            [
                                                "type" => "text",
                                                "text" => "Buen día 👋"
                                            ],
                                            [
                                                "type" => "text",
                                                "text" => $file['documentSubject']
                                            ],
                                            [
                                                "type" => "text",
                                                "text" => $r->numContenedor
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ];

                        $enviar = WhatsAppController::sendWhatsAppMessage($data);

                    }
                }
                    return response()->json(["TMensaje" => "success", "Titulo" => "Mensaje WhatsApp enviado correctamente","Mensaje" => "Se ha enviado mensaje con los archivos seleccionados"]);

            }
            
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

    public function fileProperties($id,$file,$title,$contenedor){
        $path = public_path('cotizaciones/cotizacion'.$id.'/'.$file);
        if(\File::exists($path)){
            return [
                "filePath" => $file,
                'fileName'=> $title,
                'secondaryFileName'=> $title.' '.$contenedor,
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

        $numContenedor = preg_replace('/\s+/', '*', $numContenedor);
        $contenedores = explode('*',$numContenedor);
        $documentList = array();

        foreach($contenedores as $cont){
            $documentos = DocumCotizacion::where('num_contenedor',$cont)->first();
            $folderId = $documentos->id;

            if(!is_null($documentos->doda)){
                $doda = self::fileProperties($folderId,$documentos->doda,'Doda',$cont);
                if(sizeof($doda) > 0) array_push($documentList,$doda);
            }

            if(!is_null($documentos->boleta_liberacion)){
                $boleta_liberacion = self::fileProperties($folderId,$documentos->boleta_liberacion,'Boleta de liberación',$cont);
                if(sizeof($boleta_liberacion) > 0) array_push($documentList,$boleta_liberacion);
            }


            if(!is_null($documentos->doc_ccp)){
                $doc_ccp = self::fileProperties($folderId,$documentos->doc_ccp,'Formato para Carta porte',$cont);
                if(sizeof($doc_ccp) > 0) array_push($documentList,$doc_ccp);
            }

            if(!is_null($documentos->doc_eir)){
                $doc_eir = self::fileProperties($folderId,$documentos->doc_eir,'eir',$cont);
                if(sizeof($doc_eir) > 0) array_push($documentList,$doc_eir);
            }
            if(!is_null($documentos->foto_patio)){
            
                $doc_foto_patio = self::fileProperties($folderId, $documentos->foto_patio, 'Foto patio',$cont);
                if(sizeof($doc_foto_patio) > 0) array_push($documentList, $doc_foto_patio);
            }
            $cotizacion = Cotizaciones::where('id',$documentos->id_cotizacion)->first();

            if(!is_null($cotizacion->img_boleta)){
                $preAlta = self::fileProperties($folderId,$cotizacion->img_boleta,'Pre-Alta',$cont);
                if(sizeof($preAlta) > 0) array_push($documentList,$preAlta);
            }

            if(!is_null($cotizacion->carta_porte)){
                $cpPDF = self::fileProperties($folderId,$cotizacion->carta_porte,'Carta Porte',$cont);
                if(sizeof($cpPDF) > 0) array_push($documentList,$cpPDF);
            }

            if(!is_null($cotizacion->carta_porte_xml)){
                $cpXML = self::fileProperties($folderId,$cotizacion->carta_porte_xml,'Carta Porte XML',$cont);
                if(sizeof($cpXML) > 0) array_push($documentList,$cpXML);
            }
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
