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
use App\Models\EstatusManiobra;
use App\Models\BitacoraCotizacionesEstatus;
use Illuminate\Support\Facades\Mail;
use App\Traits\CommonTrait;
use Carbon\Carbon;
use ZipArchive;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ExternosController extends Controller
{
    public function initBoard(Request $request)
    {
        $planeaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
                        ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
                        ->where('asignaciones.fecha_inicio', '>=', $request->fromDate)
                        ->where('cotizaciones.id_cliente', auth()->user()->id_cliente)
                        ->where('cotizaciones.estatus', 'Aprobada')
                        ->where('estatus_planeacion', '=', 1)
                        ->select('asignaciones.*', 'docum_cotizacion.num_contenedor', 'cotizaciones.id_cliente', 'cotizaciones.referencia_full', 'cotizaciones.tipo_viaje')
                        ->orderBy('fecha_inicio')
                        ->get();

        $extractor = $planeaciones->map(function ($p) {
            $itemNumContenedor = $p->num_contenedor;
            if (!is_null($p->referencia_full)) {
                $cotizacionFull = Cotizaciones::where('referencia_full', $p->referencia_full)->where('jerarquia', 'Secundario')->first();
                $contenedorSecundario = DocumCotizacion::where("id_cotizacion", $cotizacionFull->id)->first();
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
        $clientesData = Empresas::whereIn('id', $clientes)->selectRaw('id, nombre as name, '."'true'".' as expanded')->get();

        $board = [];
        $board[] = ["name" => "Proveedores", "id" => "S", "expanded" => true, "children" => $clientesData];

        $fecha = Carbon::now()->subdays(10)->format('Y-m-d');
        return response()->json(["boardCentros" => $board,"extractor" => $extractor,"scrollDate" => $fecha]);
    }

    public function transportistasList(Request $r)
    {
        return Proveedor::catalogoPrincipal()
        ->where('id_empresa', $r->proveedor)
        ->get();
    }
    public function transportistasListLocal(Request $r)
    {
        return Proveedor::catalogoLocal()
        ->where('id_empresa', $r->proveedor)
        ->get();
    }

    public function solicitarIndex()
    {
        return view('cotizaciones.externos.step_one');
    }



    public function solicitudSimple()
    {
        $formasPago = SatFormaPago::get();
        $metodosPago = SatMetodoPago::get();
        $usoCfdi = SatUsoCfdi::get();
        $clienteEmpresa = ClientEmpresa::where('id_client', auth()->user()->id_cliente)->get()->pluck('id_empresa');
        //return $clienteEmpresa;
        $empresas = Empresas::whereIn('id', $clienteEmpresa)->get();

        $transportista = Proveedor::catalogoPrincipal()->whereIn('id_empresa', $clienteEmpresa)->get();

        return view('cotizaciones.externos.solicitud_simple', [
                    "action" => "crear",
                    "formasPago" => $formasPago,
                    "metodosPago" => $metodosPago,
                    "usoCfdi" => $usoCfdi,
                    "proveedores" => $empresas,
                      "transportista" => $transportista
                ]);
    }

    public function editForm(Request $request)
    {
        $formasPago = SatFormaPago::get();
        $metodosPago = SatMetodoPago::get();
        $usoCfdi = SatUsoCfdi::get();
        $clienteEmpresa = ClientEmpresa::where('id_client', auth()->user()->id_cliente)->get()->pluck('id_empresa');
        $empresas = Empresas::whereIn('id', $clienteEmpresa)->get();

        $cotizacion = Cotizaciones::with(['cliente', 'DocCotizacion'])
        ->whereHas('DocCotizacion', function ($query) use ($request) {
            $query->where('num_contenedor', $request->numContenedor);
        })
        ->first();

        $transportista = Proveedor::catalogoPrincipal()->whereIn('id_empresa', $clienteEmpresa)->get();
        // dd($transportista, $clienteEmpresa);
        // $transportista = Proveedor::get();
        // where('id_empresa',$cotizacion->id_proveedor)->
        // where('id_empresa',$cotizacion->id_proveedor)->first();

        return view(
            'cotizaciones.externos.solicitud_simple',
            ["action" => "editar",
                                                            "formasPago" => $formasPago,
                                                            "metodosPago" => $metodosPago,
                                                            "usoCfdi" => $usoCfdi,
                                                            "cotizacion" => $cotizacion,
                                                            "proveedores" => $empresas,
                                                            "transportista" => $transportista
                                                        ]
        );
    }

    public function solicitudMultiple()
    {

        $clienteEmpresa = ClientEmpresa::where('id_client', auth()->user()->id_cliente)->get()->pluck('id_empresa');
        //return $clienteEmpresa;
        $empresas = Empresas::whereIn('id', $clienteEmpresa)->get();

        $transportista = Proveedor::catalogoPrincipal()->whereIn('id_empresa', $clienteEmpresa)->get();

        return view('cotizaciones.externos.solicitud_multiple', [
            "action" => "crear",
            "proveedores" => $empresas,
            "transportista" => $transportista
        ]);
    }

    public function viajesDocuments()
    {
        return view('cotizaciones.externos.viajes_documentacion');
    }

    public function misViajes()
    {
        return view('cotizaciones.externos.viajes_solicitados');
    }

    public function ZipDownload($zipFile)
    {
        return response()->download($zipFile)->deleteFileAfterSend(true);
    }

    public function CfdiToZip(Request $request)
    {
        try {
            $zipName = "carta-porte-".uniqid().".zip";

            $zipPath = public_path($zipName); // Ruta en la carpeta public

            $zip = new ZipArchive();

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {

                foreach ($request->contenedores as $c) {
                    $cotizacionQuery = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
                    ->where('d.num_contenedor', $c['NumContenedor']);

                    $cotizacion = $cotizacionQuery->first();
                    $pdf = $cotizacion->carta_porte;
                    $xml = $cotizacion->carta_porte_xml;

                    if (File::exists(public_path('/cotizaciones/cotizacion'.$cotizacion->id_cotizacion."/$xml"))) {

                        $zip->addFile(public_path('/cotizaciones/cotizacion'.$cotizacion->id_cotizacion."/$xml"), $c['NumContenedor'].'.xml');
                    }

                    if (File::exists(public_path('/cotizaciones/cotizacion'.$cotizacion->id_cotizacion."/$pdf"))) {
                        $zip->addFile(public_path('/cotizaciones/cotizacion'.$cotizacion->id_cotizacion."/$pdf"), $c['NumContenedor'].'.pdf');
                    }


                }

                $zip->close();

            }




            return response()->json([
                'zipUrl' => ($zipName),
                'success' => true
            ]);
        } catch (\Throwable $t) {
            return response()->json([
                'message' => $t->getMessage(),
                'success' => false
            ]);
        }
    }


    public function getContenedoresPendientes(Request $request)
    {
        $proveedorEmpresa = DB::table('empresas')
            ->select(
                'id',
                'nombre',
                DB::raw("'Empresa' as tipo"),
                'id as relacion_id'
            )
            ->unionAll(
                DB::table('proveedores')
                    ->select(
                        'id',
                        'nombre',
                        DB::raw("'Proveedor' as tipo"),
                        'id as relacion_id'
                    )
            );

        $docCotizacion = DB::table('cotizaciones')
    ->join('docum_cotizacion as d', 'd.id_cotizacion', '=', 'cotizaciones.id')
    ->select(
        'cotizaciones.*',
        'd.num_contenedor',
        'd.doc_eir',
        'd.doc_ccp',
        'd.boleta_liberacion',
        'd.doda',
        'd.foto_patio',
        'd.boleta_patio',
        DB::raw("
            CASE
                WHEN cotizaciones.id_proveedor IS NULL
                THEN cotizaciones.id_empresa
                ELSE cotizaciones.id_proveedor
            END as proveedor_empresa_id
        "),
        DB::raw("
            CASE
                WHEN cotizaciones.id_proveedor IS NULL
                THEN 'Empresa'
                ELSE 'Proveedor'
            END as proveedor_empresa_tipo
        ")
    );


        $condicion = ($request->estatus == 'Documentos Faltantes') ? '=' : '!=';
        // $contenedoresPendientes = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
        // ->leftjoin('proveedores as prov', 'cotizaciones.id_proveedor', '=', 'prov.id')
        // ->leftjoin('empresa as empres', 'cotizaciones.id_empresa', '=', 'empres.id')
        //                                         ->where('cotizaciones.id_cliente', '=', Auth::User()->id_cliente)
        //                                         ->where('estatus', $condicion, 'Documentos Faltantes')
        //                                         ->whereIn('tipo_viaje_seleccion', ['foraneo', 'local_to_foraneo'])
        //                                         ->where('jerarquia', "!=", 'Secundario')
        //                                         ->orderBy('created_at', 'desc')
        //                                         ->selectRaw('cotizaciones.*, d.num_contenedor,d.doc_eir,doc_ccp ,d.boleta_liberacion,d.doda,d.foto_patio,case when prov.razon_social is null then empres.nombre else prov.razon_social end as transportista ')
        //                                         ->get();

        $contenedoresPendientes = DB::table('cotizaciones')
        ->join('clients', 'cotizaciones.id_cliente', '=', 'clients.id')

        ->joinSub($docCotizacion, 'cotidoc', function ($join) {
            $join->on('cotidoc.id', '=', 'cotizaciones.id');
        })

        ->leftJoinSub($proveedorEmpresa, 'proveedor_empresa', function ($join) {
            $join->on('proveedor_empresa.relacion_id', '=', 'cotidoc.proveedor_empresa_id')
                 ->on('proveedor_empresa.tipo', '=', 'cotidoc.proveedor_empresa_tipo');
        })



        ->select(
            'cotizaciones.*',
            'clients.nombre as cliente',
            'cotidoc.num_contenedor',
            'cotidoc.doc_eir',
            'cotidoc.doc_ccp',
            'cotidoc.boleta_liberacion',
            'cotidoc.doda',
            'cotidoc.foto_patio',
            'cotidoc.boleta_patio',
            'proveedor_empresa.nombre as transportista',
            'proveedor_empresa.tipo as tipo_transportista',
            'cotizaciones.estatus'
        )
->where('cotizaciones.id_cliente', '=', Auth::User()->id_cliente)
                                              ->where('cotizaciones.estatus', $condicion, 'Documentos Faltantes')
                                                ->whereIn('cotizaciones.tipo_viaje_seleccion', ['foraneo', 'local_to_foraneo'])
                                                ->where('cotizaciones.jerarquia', "!=", 'Secundario')

        ->orderBy('cotizaciones.created_at', 'desc')
        ->get();

        // dd($contenedoresPendientes);
        $resultContenedores =
        $contenedoresPendientes->map(function ($c) {

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
                "docEir" => $docEir,
                "FechaSolicitud" => Carbon::parse($c->created_at)->format('Y-m-d'),
                "tipo" => $tipo,
                "id" => $c->id,
                "transportista" => $c->transportista,
            ];
        });

        return $resultContenedores;
    }

    public function getContenedoresAsignables(Request $request)
    {
        $contenedoresPendientes = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
                                                ->join('clients as cl', 'cotizaciones.id_cliente', '=', 'cl.id')
                                                ->join('subclientes as sc', 'cotizaciones.id_subcliente', '=', 'sc.id')
                                                ->where('estatus', '=', 'NO ASIGNADA')
                                                ->where('jerarquia', "!=", 'Secundario')
                                                ->orderBy('created_at', 'desc')
                                                ->selectRaw('cotizaciones.*, d.num_contenedor,d.doc_eir,doc_ccp ,d.boleta_liberacion,d.doda,cl.nombre as cliente,sc.nombre as subcliente')
                                                ->get();




        $resultContenedores =
        $contenedoresPendientes->map(function ($c) {

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
    public static function confirmarDocumentos($cotizacion)
    {
        try {
            Log::channel('daily')->info('Maniobra  '.$cotizacion);

            $contenedor = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
            ->where('cotizaciones.id', '=', $cotizacion)
            ->where('estatus', '=', 'Documentos Faltantes')
            ->orderBy('created_at', 'desc')
            ->selectRaw('cotizaciones.*, d.num_contenedor,d.doc_eir,doc_ccp ,d.boleta_liberacion,d.doda')
            ->first();
            if ($contenedor) {
                Log::channel('daily')->info('Doda: '.$contenedor->doda.' / liberacion:'.$contenedor->boleta_liberacion);

            }



            if ($contenedor && $contenedor->doda != null && $contenedor->boleta_liberacion != null) {
                Log::channel('daily')->info('Doda2: '.$contenedor->doda.' / liberacion2:'.$contenedor->boleta_liberacion);
                $cotizacionC = Cotizaciones::where('id', $cotizacion)->first();
                $cotizacionC->estatus = (is_null($cotizacionC->id_proveedor)) ? 'NO ASIGNADA' : 'Pendiente';
                $cliente = Client::where('id', $cotizacionC->id_cliente)->first();
                $cotizacionC->save();

                $cuentasCorreo = [env('MAIL_NOTIFICATIONS'),Auth::User()->email];
                $cuentasCorreo2 = Correo::where('cotizacion_nueva', 1)->get()->pluck('correo')->toArray();

                Mail::to($cuentasCorreo)->send(new \App\Mail\NotificaCotizacionMail($contenedor, $cliente));

            }
        } catch (\Throwable $t) {
            Log::channel('daily')->info('Maniobra no se pudo enviar al admin. id: '.$cotizacion.'. '.$t->getMessage());
        }

    }

    public function cancelarViaje(Request $request)
    {
        // $documCotizacion = DocumCotizacion::where('num_contenedor',$request->numContenedor)->first();
        $cotizacion = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
        ->where('d.num_contenedor', $request->numContenedor)
        ->first();

        if ($cotizacion->estatus == 'Cancelada') {
            return response()->json(["Titulo" => "Previamente Cancelado","Mensaje" => "El contenedor $request->numContenedor fue cancelado previamente","TMensaje" => "info"]);
        }

        Cotizaciones::where('id', $cotizacion->id)->update(['estatus' => 'Cancelada']);

        $emailList = [env('MAIL_NOTIFICATIONS'),Auth::User()->email];
        $cotizacionCancelar = Cotizaciones::where('id', $cotizacion->id)->first();
        Mail::to($emailList)->send(new \App\Mail\NotificaCancelarViajeMail($cotizacionCancelar, $request->numContenedor));

        return response()->json(["Titulo" => "Cancelado correctamente","Mensaje" => "Se canceló el viaje con el Núm. Contenedor $request->numContenedor","TMensaje" => "success"]);
    }

    public function selector(Request $request)
    {
        // return $request->transac;
        switch ($request->transac) {
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



    public function fileManager(Request $r)
    {

        return view('cotizaciones.externos.file-manager', ["numContenedor" => $r->numContenedor]);
    }

    public function fileManagerlocal(Request $r)
    {
        return view('cotizaciones.externos.file-manager-local', ["numContenedor" => $r->numContenedor]);
    }

    public function sendFiles1(Request $r)
    {
        try {

            $files = ($r->attachmentFiles);
            $attachment = [];

            if ($r->channel == "WhatsApp") {
                if (sizeof($r->wa_phone) == 0) {
                    return response()->json(["Titulo" => "Seleccione contactos","TMensaje" => "warning" , "Mensaje" => "Para enviar la documentación seleccionada debe seleccionar un contacto"]);
                }

                foreach ($r->wa_phone as $phone) {
                    //WhatsAppController::sendWhatsAppMessage($r->wa_phone, $r->message);
                    foreach ($files as $file) {
                        $urlFile = "https://sgt.gologipro.com/".public_path($file['file']);
                        $urlFile = str_replace('/var/www/html/SGTSITA/public/', '', $urlFile);
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

            foreach ($files as $file) {
                array_push($attachment, public_path($file['file']));
            }

            $emailList = (strlen($r->secondaryEmail) > 0) ? [$r->email,$r->secondaryEmail] : [$r->email];

            Mail::to($emailList)
            ->send(new \App\Mail\CustomMessageMail($r->subject, $r->message, $attachment));
            return response()->json(["TMensaje" => "success", "Titulo" => "Mensaje enviado correctamente","Mensaje" => "Se ha enviado mensaje con los archivos seleccionados"]);

        } catch (\Throwable $t) {
            return response()->json(["TMensaje" => "error", "Titulo" => "Mensaje no enviado","Mensaje" => "Ocurrio un error mientras enviabamos su mensaje: ".$t->getMessage()]);
        }
    }

    public function fileProperties($id, $file, $title, $contenedor)
    {
        $path = public_path('cotizaciones/cotizacion'.$id.'/'.$file);

        if (File::exists($path)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE); // Abrir la base de datos de tipos MIME
            $mimeType = finfo_file($finfo, $path);
            finfo_close($finfo);

            return [
                "filePath" => $file,
                'fileName' => $title,
                "folder" => $id,
                'secondaryFileName' => $title.' '.$contenedor,
                "fileDate" => CommonTrait::obtenerFechaEnLetra(date("Y-m-d", filemtime($path))),
                "fileSize" => CommonTrait::calculateFileSize(filesize($path)),
                "fileSizeBytes" => (filesize($path)),
                "fileType" => pathinfo($path, PATHINFO_EXTENSION),
                "mimeType" => $mimeType,
                "identifier" => $id,
                "fileCode" => iconv('UTF-8', 'ASCII//TRANSLIT', str_replace(' ', '-', $title))
                ];
            //iconv('UTF-8', 'ASCII//TRANSLIT', $cadena);
        } else {
            return [];
        }
    }

    public function getFilesProperties($numContenedor)
    {

        $numContenedor = preg_replace('/\s+/', '*', $numContenedor);
        $contenedores = explode('*', $numContenedor);
        $documentList = array();

        foreach ($contenedores as $cont) {
            $documentos = DocumCotizacion::with('Cotizacion')
                ->where('num_contenedor', $cont)
                ->first();


            $folderId = $documentos->id_cotizacion;  //si se guarda con id cotizacion , buscamos con esa clave

            if (!is_null($documentos->doda)) {
                $doda = self::fileProperties($folderId, $documentos->doda, 'Doda', $cont);
                if (sizeof($doda) > 0) {
                    array_push($documentList, $doda);
                }
            }

            if (!is_null($documentos->boleta_liberacion)) {
                $boleta_liberacion = self::fileProperties($folderId, $documentos->boleta_liberacion, 'Boleta de liberación', $cont);
                if (sizeof($boleta_liberacion) > 0) {
                    array_push($documentList, $boleta_liberacion);
                }
            }


            if (!is_null($documentos->doc_ccp)) {
                $doc_ccp = self::fileProperties($folderId, $documentos->doc_ccp, 'Formato para Carta porte', $cont);
                if (sizeof($doc_ccp) > 0) {
                    array_push($documentList, $doc_ccp);
                }
            }

            if (!is_null($documentos->doc_eir)) {
                $doc_eir = self::fileProperties($folderId, $documentos->doc_eir, 'eir', $cont);
                if (sizeof($doc_eir) > 0) {
                    array_push($documentList, $doc_eir);
                }
            }
            if (!is_null($documentos->foto_patio)) {

                $doc_foto_patio = self::fileProperties($folderId, $documentos->foto_patio, 'Foto patio', $cont);
                if (sizeof($doc_foto_patio) > 0) {
                    array_push($documentList, $doc_foto_patio);
                }
            }
            if (!is_null($documentos->boleta_patio)) {

                $doc_boleta_patio = self::fileProperties($folderId, $documentos->boleta_patio, 'Boleta de patio', $cont);
                if (sizeof($doc_boleta_patio) > 0) {
                    array_push($documentList, $doc_boleta_patio);
                }
            }

            $cotizacion = Cotizaciones::where('id', $documentos->id_cotizacion)->first();

            if (!is_null($cotizacion->img_boleta)) {
                $preAlta = self::fileProperties($folderId, $cotizacion->img_boleta, 'Pre-Alta', $cont);
                if (sizeof($preAlta) > 0) {
                    array_push($documentList, $preAlta);
                }
            }

            if (!is_null($cotizacion->carta_porte)) {
                $cpPDF = self::fileProperties($folderId, $cotizacion->carta_porte, 'Carta Porte', $cont);
                if (sizeof($cpPDF) > 0) {
                    array_push($documentList, $cpPDF);
                }
            }

            if (!is_null($cotizacion->carta_porte_xml)) {
                $cpXML = self::fileProperties($folderId, $cotizacion->carta_porte_xml, 'Carta Porte XML', $cont);
                if (sizeof($cpXML) > 0) {
                    array_push($documentList, $cpXML);
                }
            }
        }

        return ["data" => $documentList,"numContenedor" => $numContenedor,"documentos" => $documentos];


    }

    public function filePropertiescoordenadas($id, $file, $title)
    {
        $path = public_path('coordenadas/'.$id.'/'.$file);
        if (File::exists($path)) {
            return [
                "folder" => $id,
                "filePath" => $file,
                'fileName' => $title,
                "fileDate" => CommonTrait::obtenerFechaEnLetra(date("Y-m-d", filemtime($path))),
                "fileSize" => CommonTrait::calculateFileSize(filesize($path)),
                "fileType" => pathinfo($path, PATHINFO_EXTENSION),
                "identifier" => $id,
                "fileCode" => iconv('UTF-8', 'ASCII//TRANSLIT', str_replace(' ', '-', $title))
                ];
            //iconv('UTF-8', 'ASCII//TRANSLIT', $cadena);
        } else {
            return [];
        }
    }



    //viajes locales desde mec
    public function solicitarIndexlocal()
    {
        return view('cotizaciones.externos.step_one_local');
    }

    public function selectorlocal(Request $request)
    {
        // return $request->transac;
        switch ($request->transac) {
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
    public function solicitudSimplelocal()
    {
        $formasPago = SatFormaPago::get();
        $metodosPago = SatMetodoPago::get();
        $usoCfdi = SatUsoCfdi::get();
        $clienteEmpresa = ClientEmpresa::where('id_client', auth()->user()->id_cliente)->get()->pluck('id_empresa');
        //return $clienteEmpresa;
        $empresas = Empresas::whereIn('id', $clienteEmpresa)->get();


        $opciones = config('CatAuxiliares.opciones');
        $opcionesColores = config('CatAuxiliares.opcionesColores');
        $Puertos = config('CatAuxiliares.puertos');
        $opcionesPuertos = config('CatAuxiliares.puertosOpciones');


        $transportista = Proveedor::CatalogoLocal()->whereIn('id_empresa', $clienteEmpresa)->get();

        return view('cotizaciones.externos.solicitud_simple_local', [
                    "action" => "crear",
                    "formasPago" => $formasPago,
                    "metodosPago" => $metodosPago,
                    "usoCfdi" => $usoCfdi,
                    "proveedores" => $empresas,
                      "transportista" => $transportista,
                        "opciones" => $opciones,
                        "opcionesColores" => $opcionesColores,
                        'Puertos' => $Puertos,
                        'opcionesPuertos' => $opcionesPuertos,
                ]);
    }
    public function editFormlocal(Request $request)
    {
        $formasPago = SatFormaPago::get();
        $metodosPago = SatMetodoPago::get();
        $usoCfdi = SatUsoCfdi::get();
        $clienteEmpresa = ClientEmpresa::where('id_client', auth()->user()->id_cliente)->get()->pluck('id_empresa');
        $empresas = Empresas::whereIn('id', $clienteEmpresa)->get();

        $opciones = config('CatAuxiliares.opciones');
        $opcionesColores = config('CatAuxiliares.opcionesColores');
        $Puertos = config('CatAuxiliares.puertos');
        $opcionesPuertos = config('CatAuxiliares.puertosOpciones');


        $cotizacion = Cotizaciones::with(['cliente', 'DocCotizacion'])
        ->whereHas('DocCotizacion', function ($query) use ($request) {
            $query->where('num_contenedor', $request->numContenedor);
        })
        ->first();

        // dd($request->numContenedor, $cotizacion);
        $transportista = Proveedor::CatalogoLocal()->whereIn('id_empresa', $clienteEmpresa)->get();
        // dd($transportista, $clienteEmpresa);
        // $transportista = Proveedor::get();
        // where('id_empresa',$cotizacion->id_proveedor)->
        // where('id_empresa',$cotizacion->id_proveedor)->first();
        //dd($cotizacion);
        return view(
            'cotizaciones.externos.solicitud_simple_local',
            ["action" => "editar",
                                                            "formasPago" => $formasPago,
                                                            "metodosPago" => $metodosPago,
                                                            "usoCfdi" => $usoCfdi,
                                                            "cotizacion" => $cotizacion,
                                                            "proveedores" => $empresas,
                                                            "transportista" => $transportista,
                                                            "opciones" => $opciones,
                                                            "opcionesColores" => $opcionesColores,
                                                            'Puertos' => $Puertos,
                                                            'opcionesPuertos' => $opcionesPuertos

                                                        ]
        );
    }

    public function solicitudMultiplelocal()
    {

        $clienteEmpresa = ClientEmpresa::where('id_client', auth()->user()->id_cliente)->get()->pluck('id_empresa');
        //return $clienteEmpresa;
        $empresas = Empresas::whereIn('id', $clienteEmpresa)->get();

        $transportista = Proveedor::CatalogoLocal()->whereIn('id_empresa', $clienteEmpresa)->get();

        return view('cotizaciones.externos.solicitud_multiple_local', [
            "action" => "crear",
            "proveedores" => $empresas,
            "transportista" => $transportista
        ]);
    }

    public function viajesDocumentslocal()
    {
        return view('cotizaciones.externos.viajes_documentacion_local');

    }

    public function misViajeslocal()
    {
        $estatusManiobras  = EstatusManiobra::all();
        return view('cotizaciones.externos.viajes_solicitados-local', compact('estatusManiobras'));
    }


    public function getlistPatio()
    {
        $contenedoresPendientes = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
                                               ->where('cotizaciones.id_cliente', '=', Auth::User()->id_cliente)
                                               ->where('estatus', '=', 'local')
                                               ->whereIn('tipo_viaje_seleccion', ['local', 'local_to_foraneo'])
                                               ->where('jerarquia', "!=", 'Secundario')
                                               ->where('en_patio', "=", '1')
                                               ->orderBy('created_at', 'desc')
                                               ->selectRaw('cotizaciones.*, d.num_contenedor,d.doc_eir,doc_ccp ,d.boleta_liberacion,d.doda,d.foto_patio,d.boleta_patio')
                                               ->get();





        $resultPatioList =
        $contenedoresPendientes->map(function ($c) {

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
                "Estatus" => ($c->estatus == "Local") ? "Patio" : $c->estatus,
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

        return $resultPatioList;
    }

    public function listPatio()
    {
        return view('cotizaciones.externos.viajes_patio-local');
    }
    public function getContenedoreslocalesPendientes(Request $request)
    {
        $ocultarforaneo = $request->boolean('OcultarForaneos');
        $condicion = ($request->estatus != 'all') ? '=' : '!=';
        $contenedoresPendientes = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
           ->join('estatus_maniobras as estat', 'estat.id', '=', 'cotizaciones.estatus_maniobra_id')
           ->join('subclientes', 'subclientes.id', '=', 'cotizaciones.sub_cliente_local')
           ->join('clients', 'clients.id', '=', 'subclientes.id_cliente')
           ->join('empresas', 'empresas.id', '=', 'cotizaciones.empresa_local')
           ->join('proveedores', 'proveedores.id', '=', 'cotizaciones.transportista_local')
           ->where('cotizaciones.id_cliente', Auth::user()->id_cliente)
           ->where('cotizaciones.estatus_maniobra_id', $condicion, $request->estatus)
           ->when($ocultarforaneo, function ($query) {
               $query->where('tipo_viaje_seleccion', 'local');
           }, function ($query) {
               $query->whereIn('tipo_viaje_seleccion', ['local', 'local_to_foraneo']);
           })
           ->where('jerarquia', '!=', 'Secundario')
           ->orderBy('cotizaciones.created_at', 'desc')
           ->select([
                      'cotizaciones.*',

                      // docum
                      'd.num_contenedor',
                      'd.doc_eir',
                      'd.doc_ccp',
                      'd.boleta_liberacion',
                      'd.doda',
                      'd.foto_patio',
                      'd.boleta_patio',
                      'd.terminal',
                      'd.num_autorizacion',

                      // estatus
                      'estat.nombre as estatus_maniobra',

                      // NUEVOS
                      'empresas.nombre as empresa',
                      'proveedores.nombre as proveedor',
                      'clients.nombre as cliente',
                      'subclientes.nombre as subcliente',
                  ])
           ->get();




        $resultContenedores = $contenedoresPendientes->map(function ($c) {

            $numContenedor = $c->num_contenedor;

            $docCCP = !is_null($c->doc_ccp);
            $doda = !is_null($c->doda);
            $boletaLiberacion = !is_null($c->boleta_liberacion);
            $boletaVacio = !is_null($c->img_boleta);
            $docEir = !is_null($c->doc_eir);
            $fotoPatio = !is_null($c->foto_patio);
            $boleta_patio = !is_null($c->boleta_patio);

            $tipo = "Sencillo";

            if (!is_null($c->referencia_full)) {

                $secundaria = Cotizaciones::where('referencia_full', $c->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion')
                    ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $docCCP &= (bool) $secundaria->DocCotizacion->doc_ccp;
                    $doda &= (bool) $secundaria->DocCotizacion->doda;
                    $docEir &= (bool) $secundaria->DocCotizacion->doc_eir;
                    $boletaLiberacion &= (bool) $secundaria->DocCotizacion->boleta_liberacion;
                    $boletaVacio &= (bool) $secundaria->img_boleta;
                    $fotoPatio &= (bool) $secundaria->foto_patio;

                    $numContenedor .= ' '.$secundaria->DocCotizacion->num_contenedor;
                }

                $tipo = "Full";
            }

            return [
                "NUM_CONTENEDOR_REFER" => $numContenedor,
                "NumContenedor" => $this->limpiarNumContenedor($numContenedor),
                "Referencia" => $this->obtenerReferencias($numContenedor),
                "EstatusManiobra" => $c->estatus_maniobra,
                "Origen" => $c->origen_local,
                "Destino" => $c->destino_local,
                "Peso" => $c->peso_contenedor,

                "BoletaLiberacion" => $boletaLiberacion,
                "FechaModulacion" => Carbon::parse($c->fecha_modulacion)->format('Y-m-d'),
                "DODA" => $doda,
                "foto_patio" => $fotoPatio,
                "FormatoCartaPorte" => $docCCP,
                "PreAlta" => $boletaVacio,
                "BoletaPatio" => $boleta_patio,

                "FechaSolicitud" => Carbon::parse($c->created_at)->format('Y-m-d'),
                "tipo" => $tipo,
                "Observaciones" => (
                    is_null($c->observaciones) ||
                        $c->observaciones === 'null' ||
                        trim($c->observaciones) === ''
                )
                        ? ''
                        : $c->observaciones,

                "Terminal" => $c->terminal,
                "Puerto" => $c->puerto,
                "NAutorizacion" => $c->num_autorizacion,
                "cp_pedimento" => $c->cp_pedimento,
                "cp_clase_ped" => $c->cp_clase_ped,
                "dias_estadia" => $c->dias_estadia,
                "dias_pernocta" => $c->dias_pernocta,
                "tarifa_estadia" => $c->tarifa_estadia,
                "tarifa_pernocta" => $c->tarifa_pernocta,
                "total_estadia" => $c->total_estadia,
                "total_pernocta" => $c->total_pernocta,
                "total_general" => $c->total_general,
                "costo_maniobra_local" => $c->costo_maniobra_local,
               "estado_contenedor" => (
                   is_null($c->estado_contenedor) ||
                        $c->estado_contenedor === 'null' ||
                        trim($c->estado_contenedor) === ''
               )
                        ? 'Ninguno'
                        : $c->estado_contenedor,
                'agente_aduanal' => (
                    is_null($c->agente_aduanal) ||
                        $c->agente_aduanal === 'null' ||
                        trim($c->agente_aduanal) === ''
                )
                        ? ''
                        : $c->agente_aduanal,
                "Empresa" => $c->empresa,
                "Proveedor" => $c->proveedor,
                "Cliente" => $c->cliente,
                "Subcliente" => $c->subcliente,

                "id" => $c->id,
                "estatus_maniobra_id" => $c->estatus_maniobra_id,
                "convertido_foraneo" => str_contains($c->tipo_viaje_seleccion, 'foraneo'),
            ];
        });

        return $resultContenedores;
    }
    public function obtenerReferencias($texto)
    {
        preg_match_all('/-([A-Za-z0-9]+)/', $texto, $matches);
        return !empty($matches[1])
            ? implode(' / ', $matches[1])
            : null;
    }

    public function limpiarNumContenedor($texto)
    {

        return trim(preg_replace('/-[A-Za-z0-9]+/', '', $texto));
    }
    public function listarDocumentos(Request $request)
    {

        $folderId = $request->idSolicitud;

        $uploadDir = public_path("cotizaciones/cotizacion{$folderId}/");
        $uploadUrl = asset("cotizaciones/cotizacion{$folderId}/");

        $docs = DocumCotizacion::where('id_cotizacion', $request->idSolicitud)
                   ->where('num_contenedor', $request->numContenedor)
                   ->first();


        if (!$docs) {
            return response()->json([]);
        }


        $documentList = [];

        $docsMap = [
            'doda' => 'Doda',
            'boleta_liberacion' => 'Boleta de liberación',
            'doc_ccp' => 'Formato para Carta porte',
            'doc_eir' => 'EIR',
            'foto_patio' => 'Foto patio',
            'boleta_patio' => 'Boleta de patio',
        ];

        foreach ($docsMap as $col => $title) {

            if (!is_null($docs->$col)) {

                $docProps = self::fileProperties(
                    $folderId,
                    $docs->$col,
                    $title,
                    $request->numContenedor
                );

                if (sizeof($docProps) > 0) {
                    $docProps['publicUrl'] = $uploadUrl . '/';
                    $documentList[] = $docProps;
                }
            }
        }

        return response()->json($documentList);
    }

    public function infoManiobra(Request $request)
    {

        $folderId = $request->id_cotizacion;

        $uploadDir = public_path("cotizaciones/cotizacion{$folderId}/");
        $uploadUrl = asset("cotizaciones/cotizacion{$folderId}/");

        $docs = DocumCotizacion::where('id_cotizacion', $request->id_cotizacion)
                   ->where('num_contenedor', $request->num_contenedor)
                   ->first();


        $cotiInfoManiobra =  DB::table('docum_cotizacion')
    ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
    ->join('subclientes', 'subclientes.id', '=', 'cotizaciones.sub_cliente_local')
    ->join('clients', 'clients.id', '=', 'subclientes.id_cliente')
    ->join('empresas', 'empresas.id', '=', 'cotizaciones.empresa_local')
    ->join('proveedores', 'proveedores.id', '=', 'cotizaciones.transportista_local')
    ->where('cotizaciones.id', $folderId)
    ->where('docum_cotizacion.num_contenedor', $request->num_contenedor)
    ->select([
          'cotizaciones.id as id_cotizacion',
          'docum_cotizacion.id as id_contenendor',
          'docum_cotizacion.num_contenedor',
          'cotizaciones.puerto',
          'docum_cotizacion.num_autorizacion',
          'docum_cotizacion.terminal',
          'cotizaciones.empresa_local',
          'empresas.nombre as empresa',
          'cotizaciones.transportista_local',
          'proveedores.nombre as proveedor',
          'cotizaciones.origen_local',
          'cotizaciones.destino_local',
          'cotizaciones.tamano',
          'cotizaciones.estado_contenedor',
          'cotizaciones.peso_contenedor',
          'cotizaciones.peso_reglamentario',
          'cotizaciones.sobrepeso',
          'cotizaciones.precio_sobre_peso',
          'cotizaciones.precio_tonelada',
          'cotizaciones.fecha_modulacion_local',
          'cotizaciones.cp_pedimento',
          'cotizaciones.cp_clase_ped',
          'cotizaciones.bloque_hora_i_local',
          'cotizaciones.bloque_hora_f_local',
          'cotizaciones.observaciones',
          'cotizaciones.confirmacion_sello',
          'cotizaciones.nuevo_sello',
          'cotizaciones.costo_maniobra_local',
          'cotizaciones.tarifa_estadia',
          'cotizaciones.dias_estadia',
          'cotizaciones.total_estadia',
          'cotizaciones.tarifa_pernocta',
          'cotizaciones.dias_pernocta',
          'cotizaciones.total_pernocta',
          'cotizaciones.total_general',
          'clients.nombre as cliente',
          'subclientes.nombre as subcliente',
    ])
    ->first();

        $documentList = [];

        $docsMap = [
            'doda' => 'Doda',
            'boleta_liberacion' => 'Boleta de liberación',
            'doc_ccp' => 'Formato para Carta porte',
            'doc_eir' => 'EIR',
            'foto_patio' => 'Foto patio',
            'boleta_patio' => 'Boleta de patio',
        ];

        foreach ($docsMap as $col => $title) {

            if (!is_null($docs->$col)) {

                $docProps = self::fileProperties(
                    $folderId,
                    $docs->$col,
                    $title,
                    $request->numContenedor
                );

                if (sizeof($docProps) > 0) {
                    $docProps['publicUrl'] = $uploadUrl . '/';
                    $documentList[] = $docProps;
                }
            }
        }

        return response()->json(['documentList' => $documentList,'cotiInfoManiobra' => $cotiInfoManiobra]);
    }

    public function cambiarestatuslocal(Request $request)
    {
        try {
            DB::beginTransaction();


            $cotizacion = Cotizaciones::where('id', $request->idCotizacion)->first();

            $cotizacion->estatus_maniobra_id = $request->estatus_id;
            $cotizacion->save();


            $bitacora = new BitacoraCotizacionesEstatus();
            $bitacora->cotizaciones_id = $cotizacion->id;
            $bitacora->estatus_id = $request->estatus_id;
            $bitacora->user_id = Auth::user()->id;
            $bitacora->nota = $request->notaEstatus;
            $bitacora->save();

            DB::commit();

            return response()->json(["Titulo" => "Estatus actualizado","Mensaje" => "El estatus de la maniobra se actualizó correctamente","TMensaje" => "success"]);
        } catch (\Throwable $t) {
            DB::rollBack();
            return response()->json(["Titulo" => "Error al actualizar estatus","Mensaje" => "Ocurrió un error al actualizar el estatus: ".$t->getMessage(),"TMensaje" => "error"]);
        }

    }

    public function historialEstatus($idCotizacion)
    {
        $historial = BitacoraCotizacionesEstatus::with('estatus', 'usuario')
    ->where('cotizaciones_id', $idCotizacion)
    ->orderBy('created_at', 'desc')
    ->get()
    ->map(function ($h) {
        return [
            'estatus' => $h->estatus->nombre,
            'nota' => $h->nota,
            'usuario' => $h->usuario->name,
            'created_at' => $h->created_at->format('d/m/Y H:i'),
        ];
    });

        return response()->json($historial);
    }


    public function Exportpdf(Request $request)
    {
        $data = $request->all();

        $pdf = Pdf::loadView('pdf.maniobra', compact('data'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream('maniobra.pdf');
    }

}
