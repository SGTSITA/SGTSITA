<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotizaciones;
use App\Models\Client;
use App\Models\SatFormaPago;
use App\Models\SatMetodoPago;
use App\Models\SatUsoCfdi;
use App\Models\DocumCotizacion;
use App\Models\Empresas;
use App\Models\ClientEmpresa;
use App\Models\Asignaciones;
use App\Models\Correo;
use App\Models\Proveedor;
use Illuminate\Support\Facades\Mail;
use App\Traits\CommonTrait;
use Carbon\Carbon;
use ZipArchive;
use Auth;

class ExternosController extends Controller
{
    public function initBoard(Request $request){
        $planeaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
                        ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
                        ->where('asignaciones.fecha_inicio', '>=', $request->fromDate)
                        ->where('cotizaciones.id_cliente', auth()->user()->id_cliente)
                        ->where('cotizaciones.estatus', 'Aprobada')
                        ->where('estatus_planeacion','=', 1)
                        ->select('asignaciones.*', 'docum_cotizacion.num_contenedor','cotizaciones.id_cliente','cotizaciones.referencia_full','cotizaciones.tipo_viaje')
                        ->orderBy('fecha_inicio')
                        ->get();

        $extractor = $planeaciones->map(function($p){
            $itemNumContenedor = $p->num_contenedor;
            if(!is_null($p->referencia_full)){
                $cotizacionFull = Cotizaciones::where('referencia_full',$p->referencia_full)->where('jerarquia','Secundario')->first();
                $contenedorSecundario = DocumCotizacion::where("id_cotizacion",$cotizacionFull->id)->first();
                $itemNumContenedor .= " / ".$contenedorSecundario->num_contenedor;
            }
            return [
                    "fecha_inicio" => $p->fecha_inicio,
                    "fecha_fin" => $p->fecha_fin,
                    "id_contenedor" => $p->id_contenedor,
                    "id_cliente" => $p->id_empresa,
                    "num_contenedor" => $itemNumContenedor
                   ];
        });

        $clientes = $planeaciones->unique('id_empresa')->pluck('id_empresa');
        $clientesData = Empresas::whereIn('id' ,$clientes)->selectRaw('id, nombre as name, '."'true'".' as expanded')->get();

        $board = [];
        $board[] = ["name" => "Proveedores", "id" => "S", "expanded" => true, "children" => $clientesData];
      
        $fecha = Carbon::now()->subdays(10)->format('Y-m-d');
        return response()->json(["boardCentros"=> $board,"extractor"=>$extractor,"scrollDate"=> $fecha]);  
    }

    public function transportistasList(Request $r){
        return Proveedor::where('id_empresa',$r->proveedor)->get();
    }

    public function solicitarIndex(){
        return view('cotizaciones.externos.step_one');
    }



   public function solicitudSimple(){
        $formasPago = SatFormaPago::get();
        $metodosPago = SatMetodoPago::get();
        $usoCfdi = SatUsoCfdi::get();
        $clienteEmpresa = ClientEmpresa::where('id_client',auth()->user()->id_cliente)->get()->pluck('id_empresa');
        //return $clienteEmpresa;
        $empresas = Empresas::whereIn('id',$clienteEmpresa)->get();

         $transportista = Proveedor::whereIn('id_empresa', $clienteEmpresa)->get();
        
        return view('cotizaciones.externos.solicitud_simple',[
                    "action" => "crear",
                    "formasPago" => $formasPago, 
                    "metodosPago" => $metodosPago, 
                    "usoCfdi" => $usoCfdi, 
                    "proveedores" => $empresas,
                      "transportista" => $transportista
                ]);
    }

    public function editForm(Request $request){
        $formasPago = SatFormaPago::get();
        $metodosPago = SatMetodoPago::get();
        $usoCfdi = SatUsoCfdi::get();
        $clienteEmpresa = ClientEmpresa::where('id_client',auth()->user()->id_cliente)->get()->pluck('id_empresa');
        $empresas = Empresas::whereIn('id',$clienteEmpresa)->get();

        $cotizacion = Cotizaciones::with(['cliente', 'DocCotizacion'])
        ->whereHas('DocCotizacion', function ($query) use ($request) {
            $query->where('num_contenedor', $request->numContenedor);
        })
        ->first();

        $transportista = Proveedor::whereIn('id_empresa', $clienteEmpresa)->get();
       // dd($transportista, $clienteEmpresa);
       // $transportista = Proveedor::get();
       // where('id_empresa',$cotizacion->id_proveedor)->
      // where('id_empresa',$cotizacion->id_proveedor)->first();
 
        return view('cotizaciones.externos.solicitud_simple',
                                                            ["action" => "editar",
                                                            "formasPago" => $formasPago, 
                                                            "metodosPago" => $metodosPago, 
                                                            "usoCfdi" => $usoCfdi, 
                                                            "cotizacion" => $cotizacion,
                                                            "proveedores" => $empresas,
                                                            "transportista" => $transportista
                                                        ]);
    }

    public function solicitudMultiple(){

         $clienteEmpresa = ClientEmpresa::where('id_client',auth()->user()->id_cliente)->get()->pluck('id_empresa');
        //return $clienteEmpresa;
        $empresas = Empresas::whereIn('id',$clienteEmpresa)->get();

         $transportista = Proveedor::whereIn('id_empresa', $clienteEmpresa)->get();

        return view('cotizaciones.externos.solicitud_multiple',[
            "action" => "crear",
            "proveedores" => $empresas,
            "transportista" => $transportista
        ]);
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
                                                ->whereIn('tipo_viaje_seleccion', ['foraneo', 'local_to_foraneo'])
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
            $boletaPatio = ($c->boleta_patio == null) ? false : true;
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
                "BoletaPatio" => $boletaPatio,
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
                                                ->where('jerarquia', "!=",'Secundario')
                                                ->orderBy('created_at', 'desc')
                                                ->selectRaw('cotizaciones.*, d.num_contenedor,d.doc_eir,doc_ccp ,d.boleta_liberacion,d.doda,cl.nombre as cliente,sc.nombre as subcliente')
                                                ->get();

        
        

        $resultContenedores = 
        $contenedoresPendientes->map(function($c){

        $numContenedor = $c->num_contenedor;
        $tipo = "Sencillo";
        $docCCP = ($c->doc_ccp == null) ? false : true;
        $doda = ($c->doda == null) ? false : true;
        $boletaLiberacion = ($c->boleta_liberacion == null) ? false : true;

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
          \Log::channel('daily')->info('Maniobra  '.$cotizacion);

            $contenedor = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
            ->where('cotizaciones.id' ,'=',$cotizacion)
            ->where('estatus','=','En espera')
            ->orderBy('created_at', 'desc')
            ->selectRaw('cotizaciones.*, d.num_contenedor,d.doc_eir,doc_ccp ,d.boleta_liberacion,d.doda')
            ->first();
            \Log::channel('daily')->info('Doda: '.$contenedor->doda.' / liberacion:'.$contenedor->boleta_liberacion);

            if($contenedor->doda != null && $contenedor->boleta_liberacion != null){
                $cotizacion = Cotizaciones::where('id',$cotizacion)->first();
                $cotizacion->estatus = (is_null($cotizacion->id_proveedor)) ? 'NO ASIGNADA' :'Pendiente';
                $cliente = Client::where('id',$cotizacion->id_cliente)->first();
                $cotizacion->save();

                $cuentasCorreo = [env('MAIL_NOTIFICATIONS'),Auth::User()->email];
                $cuentasCorreo2 = Correo::where('cotizacion_nueva',1)->get()->pluck('correo')->toArray();
                
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

    public function fileManagerlocal(Request $r){
        return view('cotizaciones.externos.file-manager-local',["numContenedor" => $r->numContenedor]);
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
            $finfo = finfo_open(FILEINFO_MIME_TYPE); // Abrir la base de datos de tipos MIME
            $mimeType = finfo_file($finfo, $path);
            finfo_close($finfo);

            return [
                "filePath" => $file,
                'fileName'=> $title,
                "folder" => $id,
                'secondaryFileName'=> $title.' '.$contenedor,
                "fileDate" => CommonTrait::obtenerFechaEnLetra(date("Y-m-d", filemtime($path))),
                "fileSize" => CommonTrait::calculateFileSize(filesize($path)),
                "fileSizeBytes" => (filesize($path)),
                "fileType" => pathinfo($path, PATHINFO_EXTENSION),
                "mimeType" => $mimeType,
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
             if(!is_null($documentos->boleta_patio)){
            
                $doc_boleta_patio = self::fileProperties($folderId, $documentos->boleta_patio, 'Boleta de patio',$cont);
                if(sizeof($doc_boleta_patio) > 0) array_push($documentList, $doc_boleta_patio);
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
                "folder" => $id,
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



    //viajes locales desde mec
    function solicitarIndexlocal(){
        return view('cotizaciones.externos.step_one_local');
    }
    
    public function selectorlocal(Request $request){
        // return $request->transac;
            switch($request->transac){
                case "simple":
                    $path = 'viajes.simplelocal';
                    break;
                case "multiple":
                    $path = 'viajes.multiplelocal';
                    break;
                case "documents":
                    $path = 'viajes.documentslocal';
                    break;
                default:
                    $path = 'viajes.index';
            }

            return redirect()->route($path);
    }
    public function solicitudSimplelocal(){
        $formasPago = SatFormaPago::get();
        $metodosPago = SatMetodoPago::get();
        $usoCfdi = SatUsoCfdi::get();
        $clienteEmpresa = ClientEmpresa::where('id_client',auth()->user()->id_cliente)->get()->pluck('id_empresa');
        //return $clienteEmpresa;
        $empresas = Empresas::whereIn('id',$clienteEmpresa)->get();

       
  $opciones = ['A1', 'M3', 'R1', 'A4', 'IN'];


         $transportista = Proveedor::whereIn('id_empresa', $clienteEmpresa)->get();
        
        return view('cotizaciones.externos.solicitud_simple_local',[
                    "action" => "crear",
                    "formasPago" => $formasPago, 
                    "metodosPago" => $metodosPago, 
                    "usoCfdi" => $usoCfdi, 
                    "proveedores" => $empresas,
                      "transportista" => $transportista,
                        "opciones" => $opciones
                ]);
    }
     public function editFormlocal(Request $request){
        $formasPago = SatFormaPago::get();
        $metodosPago = SatMetodoPago::get();
        $usoCfdi = SatUsoCfdi::get();
        $clienteEmpresa = ClientEmpresa::where('id_client',auth()->user()->id_cliente)->get()->pluck('id_empresa');
        $empresas = Empresas::whereIn('id',$clienteEmpresa)->get();

        $opciones = ['A1', 'M3', 'R1', 'A4', 'IN'];

        $cotizacion = Cotizaciones::with(['cliente', 'DocCotizacion'])
        ->whereHas('DocCotizacion', function ($query) use ($request) {
            $query->where('num_contenedor', $request->numContenedor);
        })
        ->first();

        $transportista = Proveedor::whereIn('id_empresa', $clienteEmpresa)->get();
       // dd($transportista, $clienteEmpresa);
       // $transportista = Proveedor::get();
       // where('id_empresa',$cotizacion->id_proveedor)->
      // where('id_empresa',$cotizacion->id_proveedor)->first();
 //dd($cotizacion);
        return view('cotizaciones.externos.solicitud_simple_local',
                                                            ["action" => "editar",
                                                            "formasPago" => $formasPago, 
                                                            "metodosPago" => $metodosPago, 
                                                            "usoCfdi" => $usoCfdi, 
                                                            "cotizacion" => $cotizacion,
                                                            "proveedores" => $empresas,
                                                            "transportista" => $transportista,
                                                            "opciones" => $opciones
                                                        ]);
    }

    public function solicitudMultiplelocal(){

        return view('cotizaciones.externos.solicitud_multiple_local',[
            "action" => "crear"
        ]);
    }

    public function viajesDocumentslocal(){
        return view('cotizaciones.externos.viajes_documentacion_local');

    }

    public function misViajeslocal(){
        return view('cotizaciones.externos.viajes_solicitados-local');
    }

      public function getContenedoreslocalesPendientes(Request $request){
        $condicion = ($request->estatus == 'Local') ? '=' : '!=';
        $contenedoresPendientes = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
                                                ->where('cotizaciones.id_cliente' ,'=',Auth::User()->id_cliente)
                                                ->where('estatus',$condicion,$request->estatus)
                                                ->whereIn('tipo_viaje_seleccion', ['local', 'local_to_foraneo'])
                                                ->where('jerarquia', "!=",'Secundario')
                                                ->orderBy('created_at', 'desc')
                                                ->selectRaw('cotizaciones.*, d.num_contenedor,d.doc_eir,doc_ccp ,d.boleta_liberacion,d.doda,d.foto_patio,d.boleta_patio')
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
            $boleta_patio = ($c->boleta_patio == null) ? false : true;
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
                "Estatus" => ($c->estatus == "Local") ? "Local Viaje solicitado" : $c->estatus,
                "Origen" => $c->origen, 
                "Destino" => $c->destino, 
                "Peso" => $c->peso_contenedor,
                "BoletaLiberacion" => $boletaLiberacion,
                "DODA" => $doda,
                "foto_patio" => $fotoPatio,
                "FormatoCartaPorte" => $docCCP,
                "PreAlta" => $boletaVacio,
                "BoletaPatio" => $boleta_patio,
                "FechaSolicitud" => Carbon::parse($c->created_at)->format('Y-m-d'),
                "tipo" => $tipo,
                "id" => $c->id
            ];
        });

        return $resultContenedores;
    }

    
}
