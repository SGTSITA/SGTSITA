<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Coordenadas;
use App\Models\Client;
use App\Models\Subclientes;
use App\Models\Proveedor;
use App\Models\Cotizaciones;
use App\Models\Equipo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\RastreoIntervals;


use Auth;

class CoordenadasController extends Controller
{

    
    
    //externos cliente MEC
    public function extindexMapa(){
        $idCliente = Auth::User()->id_cliente;
        return view('cotizaciones.externos.coorVer',compact('idCliente'));

        }
        public function extindexSeach(){
            $idCliente = Auth::User()->id_cliente;
        return view('cotizaciones.externos.coorSearch',compact('idCliente'));
        }


        public function extcompartir(){
            $idCliente = Auth::User()->id_cliente;
        return view('cotizaciones.externos.coorCompartir',compact('idCliente'));
        }


        public function getCotizCoordenadasList()
        {
            $cotizaciones = Cotizaciones::where('id_empresa', auth()->user()->id_empresa)
                ->where('estatus', '=', 'Aprobada')
                ->where('estatus_planeacion', '=', 1)
                ->orderBy('created_at', 'desc')
                ->with(['cliente', 'DocCotizacion.Asignaciones'])             
                ->get()
                ->map(function ($cotizacion) {
                    return [
                        'id' => $cotizacion->id,
                        'cliente' => $cotizacion->cliente ? $cotizacion->cliente->nombre : 'N/A',
                        'origen' => $cotizacion->origen,
                        'destino' => $cotizacion->destino,
                        'contenedor' => $cotizacion->DocCotizacion ? $cotizacion->DocCotizacion->num_contenedor : 'N/A',
                        'estatus' => $cotizacion->estatus,
                        'coordenadas' => optional($cotizacion->DocCotizacion)->Asignaciones ? 'Compartir' : '',
                        'id_asignacion' => optional($cotizacion->DocCotizacion)->Asignaciones->id ?? null
                        
                    ];
                });
        
            return response()->json(['list' => $cotizaciones]);
        }




        public function encontrarURLfoto(Request $request)
{
    $idCotizacion = $request->id_cotizacion;
    $numContenedor = $request->contenedor;

    $documentos = DocumCotizacion::where('num_contenedor', $numContenedor)
                                 ->where('id_cotizacion', $idCotizacion)
                                 ->first();

    $documentList = [];

    if ($documentos && !is_null($documentos->foto_patio)) {
        $folderId = $documentos->id_cotizacion; // Usa id_cotizacion para coincidir con la ruta del archivo
        $doc_foto_patio = self::fileProperties($folderId, $documentos->foto_patio, 'Foto patio');
        if (sizeof($doc_foto_patio) > 0) {
            array_push($documentList, $doc_foto_patio);
        }
    }

    return response()->json($documentList);

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



     public function exrastrearIndex(){

             return view('mec.coordenadas.rastrear');
        }


    //end externos

    public function indexMapa(){
             return view('coordenadas.vercoor');

    }
    public function indexSeach(){
        return view('coordenadas.search');
    }

    public function  getcoorcontenedor(Request  $request) 
    {
        $preguntas_A = [
            [ 'texto' => "1) ¿ Registro en Puerto ?", 'campo' => 'registro_puerto', 'tooltip' => "Registro en Puerto" ],
            [ 'texto' => "2) ¿ Dentro de Puerto ?", 'campo' => 'dentro_puerto', 'tooltip' => "Dentro de Puerto" ],
            [ 'texto' => "3) ¿ Cargado Contenedor ?", 'campo' => 'cargado_contenedor', 'tooltip' => "Cargado Contenedor" ],
            [ 'texto' => "4) ¿ En Fila Fiscal ?", 'campo' => 'fila_fiscal', 'tooltip' => "En Fila Fiscal" ],
            [ 'texto' => "5) ¿ Modulado ?", 'campo' => 'modulado_tipo', 'tooltip' => "Modulado" ],
            [ 'texto' => "6) ¿ Descarga en patio ?", 'campo' => 'descarga_patio', 'tooltip' => "Descarga en patio" ],
            [ 'texto' => "7)  Toma Foto de Boleta de Patio", 'campo' => 'toma_foto_patio', 'tooltip' => "Toma Foto de Boleta de Patio" ],
            [ 'texto' => "8) ¿Carga en patio?", 'campo' => 'cargado_patio', 'tooltip' => "Carga en patio" ],
            [ 'texto' => "9) ¿Inicio ruta?", 'campo' => 'en_destino', 'tooltip' => "Inicio ruta" ],
            [ 'texto' => "10)¿Inicia carga?", 'campo' => 'inicio_descarga', 'tooltip' => "Inicia carga" ],
            [ 'texto' => "11)¿Fin descarga?", 'campo' => 'fin_descarga', 'tooltip' => "Fin descarga" ],
            [ 'texto' => "12) ¿Recepción Doctos Firmados?", 'campo' => 'recepcion_doc_firmados', 'tooltip' => "Recepción Doctos Firmados" ],
        ];

        $params = $request->query();

        $proveedor = $params['proveedor'] ?? null;  
        $cliente = $params['cliente'] ?? null;
        $subcliente = $params['subcliente'] ?? null;
        $fecha_inicio = $params['fecha_inicio'] ?? null;
        $fecha_fin = $params['fecha_fin'] ?? null;
        $contenedor = $params['contenedor'] ?? null;
        $idCliente = $params['idCliente'] ?? null;
        $contenedoresVarios = $params['contenedores'] ?? null;
        
        
        $asignaciones = DB::table('asignaciones')
        ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
        ->select(
        'asignaciones.id',
        'asignaciones.id_camion',
        'docum_cotizacion.num_contenedor','asignaciones.fecha_inicio','asignaciones.id_contenedor','foto_patio',
        DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN asignaciones.id_operador ELSE asignaciones.id_proveedor END as beneficiario_id"),
        DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN 'Propio' ELSE 'Subcontratado' END as tipo_contrato")
        );

        $beneficiarios = DB::table(function ($query) {
            $query->select('id', 'nombre', 'telefono', DB::raw("'Propio' as tipo_contrato"), 'id_empresa')
                ->from('operadores')
                ->union(
                    DB::table('proveedores')
                        ->select('id', 'nombre', 'telefono', DB::raw("'Subcontratado' as tipo_contrato"), 'id_empresa')
                );
        }, 'beneficiarios');

  
        $datos = DB::table('cotizaciones')
     ->select(
        'cotizaciones.id as id_cotizacion',
        'asig.id as id_asignacion',
        'coordenadas.id as id_coordenada',
        'clients.nombre as cliente',
        'cotizaciones.origen',
        'cotizaciones.destino',
        'asig.num_contenedor as contenedor', // Cambié esto para que utilice el campo correcto de asignaciones
        'cotizaciones.estatus',
        // Coordenadas
        'coordenadas.registro_puerto',
        'coordenadas.registro_puerto_datatime',
        'coordenadas.dentro_puerto',
        'coordenadas.dentro_puerto_datatime',
        'coordenadas.descarga_vacio',
        'coordenadas.descarga_vacio_datatime',
        'coordenadas.cargado_contenedor',
        'coordenadas.cargado_contenedor_datatime',
        'coordenadas.fila_fiscal',
        'coordenadas.fila_fiscal_datatime',
        'coordenadas.modulado_tipo',
        'coordenadas.modulado_tipo_datatime',
        'coordenadas.modulado_coordenada',
        'coordenadas.modulado_coordenada_datatime',
        'coordenadas.en_destino',
        'coordenadas.en_destino_datatime',
        'coordenadas.inicio_descarga',
        'coordenadas.inicio_descarga_datatime',
        'coordenadas.fin_descarga',
        'coordenadas.fin_descarga_datatime',
        'coordenadas.recepcion_doc_firmados',
        'coordenadas.recepcion_doc_firmados_datatime',
        'coordenadas.descarga_patio',
        'coordenadas.descarga_patio_datetime',
        'coordenadas.cargado_patio',
        'coordenadas.cargado_patio_datetime',
        'tipo_b_estado',
        DB::raw("CASE tipo_b_estado 
        WHEN 0 THEN 'No Iniciado' 
        WHEN 1 THEN 'Iniciado' 
        WHEN 2 THEN 'Finalizado' 
         END as Estatus_Burrero"),
         'tipo_f_estado',
        DB::raw("CASE tipo_f_estado 
        WHEN 0 THEN 'No Iniciado' 
        WHEN 1 THEN 'Iniciado' 
        WHEN 2 THEN 'Finalizado' 
         END as Estatus_Foraneo"),
         'tipo_c_estado',
        DB::raw("CASE tipo_c_estado 
        WHEN 0 THEN 'No Iniciado' 
        WHEN 1 THEN 'Iniciado' 
        WHEN 2 THEN 'Finalizado' 
     END as Estatus_Completo"),
     'asig.foto_patio',
     'coordenadas.toma_foto_patio'
     )
    ->join('clients', 'cotizaciones.id_cliente', '=', 'clients.id')
    
   ->joinSub($asignaciones, 'asig', function ($join) {
      $join->on('asig.id_contenedor', '=', 'cotizaciones.id'); 
    })
    ->Join('coordenadas', 'coordenadas.id_asignacion', '=', 'asig.id') 
    ->joinSub($beneficiarios, 'beneficiarios', function ($join) {
        $join->on('asig.beneficiario_id', '=', 'beneficiarios.id')
             ->on('asig.tipo_contrato', '=', 'beneficiarios.tipo_contrato');
    })
    //->leftJoin('subclientes','subclientes.id_cliente','=','beneficiarios.id')
    ->when($proveedor, function ($query) use ($proveedor) {
        return $query->where('beneficiarios.id', $proveedor);
    })
    ->when($cliente, function ($query) use ($cliente) {
        return $query->where('clients.id', $cliente);
    })
    ->when($idCliente, function ($query) use ($idCliente) {
        return $query->where('clients.id', $idCliente);
    })
    ->when($subcliente, function ($query) use ($subcliente) {
        return $query->whereExists(function ($subq) use ($subcliente) {
            $subq->select(DB::raw(1))
                ->from('subclientes')
                ->where('subclientes.id', '=', $subcliente)
                ->whereColumn('subclientes.id_cliente', '=', 'clients.id');
        });
    })
    ->when($fecha_inicio && $fecha_fin, function ($query) use ($fecha_inicio, $fecha_fin) {
        return $query->whereBetween('asig.fecha_inicio', [$fecha_inicio, $fecha_fin]);
    })
    ->when($contenedor, function ($query) use ($contenedor) {
        return $query->where('asig.num_contenedor', $contenedor); 
    })
    ->when($contenedoresVarios, function ($query) use ($contenedoresVarios) {
        $contenedores = array_filter(array_map('trim', explode(';', $contenedoresVarios)));
    
        if (count($contenedores) > 1) {
            return $query->whereIn('asig.num_contenedor', $contenedores);
        } else {
            return $query->where('asig.num_contenedor', $contenedores[0]);
        }
    }) ->where(function($query) {
        $query->whereNotNull('tipo_flujo')
            ->orWhereNotNull('registro_puerto')
            ->orWhereNotNull('dentro_puerto')
            ->orWhereNotNull('descarga_vacio')
            ->orWhereNotNull('cargado_contenedor')
            ->orWhereNotNull('fila_fiscal')
            ->orWhereNotNull('modulado_tipo')
            ->orWhereNotNull('modulado_coordenada')
            ->orWhereNotNull('en_destino')
            ->orWhereNotNull('inicio_descarga')
            ->orWhereNotNull('fin_descarga')
            ->orWhereNotNull('recepcion_doc_firmados')
            ->orWhereNotNull('descarga_patio')
            ->orWhereNotNull('cargado_patio');
    })
    ->get();
            
          if ($datos) {
            return response()->json([
                'success' => true,
                'datos' => $datos,
                'preguntas' => $preguntas_A,
            ]);
        } else {
            return response()->json(['success' => false]);
        }
        
    }
    public function index($id,$tipoCuestionario){

      
        $asignaciones = DB::table('asignaciones')
        ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
        ->select(
            'asignaciones.id',
            'asignaciones.id_camion',
            'docum_cotizacion.num_contenedor',
            DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN asignaciones.id_operador ELSE asignaciones.id_proveedor END as beneficiario_id"),
            DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN 'Propio' ELSE 'Subcontratado' END as tipo_contrato")
        );

        $beneficiarios = DB::table(function ($query) {
            $query->select('id', 'nombre', 'telefono', DB::raw("'Propio' as tipo_contrato"), 'id_empresa')
                ->from('operadores')
                ->union(
                    DB::table('proveedores')
                        ->select('id', 'nombre', 'telefono', DB::raw("'Subcontratado' as tipo_contrato"), 'id_empresa')
                );
        }, 'beneficiarios');

        $coordenadas = DB::table('coordenadas as coor')
            ->select(
                'coor.id as id_coordenadas',
                'coor.id_asignacion',
                'coor.id_cotizacion',
                'beneficiarios.tipo_contrato',
                'beneficiarios.telefono',
                'eq.placas',
                'beneficiarios.nombre',
                'em.nombre as nombre_empresa',
                'asig.num_contenedor',
                'coor.registro_puerto' ,
                'coor.registro_puerto_datatime' ,
                'coor.dentro_puerto' ,
                'coor.dentro_puerto_datatime' ,
                'coor.descarga_vacio' ,
                'coor.descarga_vacio_datatime' ,
                'coor.cargado_contenedor' ,
                'coor.cargado_contenedor_datatime' ,
                'coor.fila_fiscal' ,
                'coor.fila_fiscal_datatime' ,
                'coor.modulado_tipo' ,
                'coor.modulado_tipo_datatime' ,
                'coor.modulado_coordenada' ,
                'coor.modulado_coordenada_datatime' ,
                'coor.en_destino' ,
                'coor.en_destino_datatime' ,
                'coor.inicio_descarga' ,
                'coor.inicio_descarga_datatime' ,
                'coor.fin_descarga' ,
                'coor.fin_descarga_datatime',
                'coor.recepcion_doc_firmados',
                'coor.recepcion_doc_firmados_datatime' ,
                'coor.descarga_patio' ,
                'coor.descarga_patio_datetime' ,
                'coor.cargado_patio',
                'coor.cargado_patio_datetime',
                'tipo_c_estado' ,
                'tipo_b_estado',
                'tipo_f_estado',
                'toma_foto_patio',
                'toma_foto_patio_datetime'

            )
            ->joinSub($asignaciones, 'asig', function ($join) {
                $join->on('coor.id_asignacion', '=', 'asig.id');
            })
            ->joinSub($beneficiarios, 'beneficiarios', function ($join) {
                $join->on('asig.beneficiario_id', '=', 'beneficiarios.id')
                    ->on('asig.tipo_contrato', '=', 'beneficiarios.tipo_contrato');
            })
            ->join('empresas as em', 'em.id', '=', 'beneficiarios.id_empresa')
            ->leftJoin('equipos as eq', 'asig.id_camion', '=', 'eq.id')
            ->where('coor.id_cotizacion', $id)
            ->first();

        if (!$coordenadas) {
            $coordenadas = (object)[
                'id_coordenadas' => null,
                'id_asignacion' => 0,
                'id_cotizacion' => $id,
                'tipo_contrato' => '',
                'telefono' => '',
                'placas' => '',
                'nombre' => '',
                'nombre_empresa' => 'No se encontraron registros de coordenadas',
                'num_contenedor' => '',
                'registro_puerto' => '',
                'registro_puerto_datatime' => '',
                'dentro_puerto' => '',
                'dentro_puerto_datatime' => '',
                'descarga_vacio' => '',
                'descarga_vacio_datatime' => '',
                'cargado_contenedor' => '',
                'cargado_contenedor_datatime' => '',
                'fila_fiscal' => '',
                'fila_fiscal_datatime' => '',
                'modulado_tipo' => '',
                'modulado_tipo_datatime' => '',
                'modulado_coordenada' => '',
                'modulado_coordenada_datatime' => '',
                'en_destino' => '',
                'en_destino_datatime' => '',
                'inicio_descarga' => '',
                'inicio_descarga_datatime' => '',
                'fin_descarga' => '',
                'fin_descarga_datatime' => '',
                'recepcion_doc_firmados' => '',
                'recepcion_doc_firmados_datatime' => '',
                'descarga_patio' => '',
                'descarga_patio_datetime' => '',
                'cargado_patio' => '',
                'cargado_patio_datetime' => ''
            ];
        }
$id_cotizacion = $id;
$idCordenada= $coordenadas->id_coordenadas;
        return view('coordenadas.index',compact('coordenadas','tipoCuestionario','id_cotizacion','idCordenada'));

    }

    public function store(Request $request) 
    {
        $cotizacionesEx = Coordenadas::where('id_asignacion', $request->idAsig)
    ->where('id_cotizacion', $request->idCotSave)
    ->first();
        if (!$cotizacionesEx)
        {//insertar
            $nuevaCot = Coordenadas::create([
                'id_asignacion' => $request->idAsig,
                'id_cotizacion' => $request->idCotSave,
                'tipo_flujo' => $request->tipo_flujo ?? null,
                'registro_puerto' => $request->registro_puerto ?? null,
                'dentro_puerto' => $request->dentro_puerto ?? null,
                'descarga_vacio' => $request->descarga_vacio ?? null,
                'cargado_contenedor' => $request->cargado_contenedor ?? null,
                'fila_fiscal' => $request->fila_fiscal ?? null,
                'modulado_tipo' => $request->modulado_tipo ?? null,
                'modulado_coordenada' => $request->modulado_coordenada ?? null,
                'en_destino' => $request->en_destino ?? null,
                'inicio_descarga' => $request->inicio_descarga ?? null,
                'fin_descarga' => $request->fin_descarga ?? null,
                'recepcion_doc_firmados' => $request->recepcion_doc_firmados ?? null,
        
                'tipo_flujo_datatime' => $request->tipo_flujo_datatime ?? null,
                'registro_puerto_datatime' => $request->registro_puerto_datatime ?? null,
                'dentro_puerto_datatime' => $request->dentro_puerto_datatime ?? null,
                'descarga_vacio_datatime' => $request->descarga_vacio_datatime ?? null,
                'cargado_contenedor_datatime' => $request->cargado_contenedor_datatime ?? null,
                'fila_fiscal_datatime' => $request->fila_fiscal_datatime ?? null,
                'modulado_tipo_datatime' => $request->modulado_tipo_datatime ?? null,
                'modulado_coordenada_datatime' => $request->modulado_coordenada_datatime ?? null,
                'en_destino_datatime' => $request->en_destino_datatime ?? null,
                'inicio_descarga_datatime' => $request->inicio_descarga_datatime ?? null,
                'fin_descarga_datatime' => $request->fin_descarga_datatime ?? null,
                'recepcion_doc_firmados_datatime' => $request->recepcion_doc_firmados_datatime ?? null,
                'tipo_c_estado' => $request->tipo_c_estado,
                'tipo_b_estado' => $request-> tipo_b_estado,
                'tipo_f_estado' => $request-> tipo_f_estado,
            ]);
        
            return response()->json([
                'message' => 'Coordenada guardada correctamente',
                'data' => $nuevaCot
            ]);

        }else {
            return response()->json([
                'message' => 'Coordenada guardada correctamente',
                'data' => $cotizacionesEx
            ]);
        }

    }
    public function guardarRespuesta(Request $request)
        {
                $coordenada = Coordenadas::find($request->id_coordenada);
            $message='';
                if ($coordenada) {
                    $fecha = Carbon::now();
                   $idCotizacion = $coordenada->id_cotizacion;

                    $coordenada->update([
                        $request->columna => $request->coordenadas,  
                        $request->columna_datetime =>  $fecha  
                       
                    ]);
                      $message='Coordenada guardada.';
                    if($request->columna === 'recepcion_doc_firmados'){
                            //finalizar viaje 
                         $cotizacion=    Cotizaciones::find($idCotizacion);
                         if ($cotizacion){
                                   $cotizacion->update([
                                            'estatus' => 'Finalizado'
                                             
                                        
                                        ]);

                             $message='Coordenada guardada. Viaje finalizado';
                         }

                    }
                   
                    
                    return response()->json(['success' => true,'message'=> $message]);
                }
            
                
                return response()->json(['error' => 'Coordenada no encontrada'], 404);
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
    
    public function getEntidadesPC()
    {
       
        
        $proveedores = Proveedor::where('id_empresa', auth()->user()->id_empresa)
                                ->orderBy('created_at', 'desc')
                                ->get(['id', 'nombre']); 

       
        $clientes = Client::join('client_empresa as ce', 'clients.id', '=', 'ce.id_client')
                        ->where('ce.id_empresa', Auth::User()->id_empresa)
                        ->where('is_active', 1)
                        ->orderBy('nombre')
                        ->get(['clients.id', 'clients.nombre']); 

        
        return response()->json([
            'proveedor' => $proveedores,
            'client' => $clientes
        ]);
    }
    public function getSubclientes($clienteId)
        {
            $subclientes = Subclientes::where('id_cliente', $clienteId)
                            ->orderBy('created_at', 'desc')
                            ->get(['id', 'nombre']); // Trae solo lo necesario

            return response()->json($subclientes);
        }
  

    public function subirArchivo(Request $request)
        {
            if ($request->hasFile('documento_pregunta_8')) {
                $idArc = $request->cotizacion_id;
                
                $file = $request->file('documento_pregunta_8');
                 $path = public_path() . '/cotizaciones/cotizacion'. $idArc;
               
 
                
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }

                $fileName = uniqid() . '_' . $file->getClientOriginalName();
                $file->move($path, $fileName);

             
                $doc = \App\Models\DocumCotizacion::firstOrNew([
                    'id_cotizacion' =>  $idArc 
                ]);

                $doc->foto_patio = $fileName;
                $doc->save();


                 $coordenada = Coordenadas::find($request->id_coordenada);

                if ($coordenada && $doc) {
                    $fecha = Carbon::now();
                   
                    $coordenada->update([
                        'toma_foto_patio' => '1',  
                        'toma_foto_patio_datetime' =>  $fecha  
                       
                    ]);
            
                    
                }
            

                return response()->json([
                    'success' => true,
                    'mensaje' => 'Archivo guardado correctamente',
                    'nombre_archivo' => $fileName
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se recibió ningún archivo'
            ]);
        }


        //rastreo de gps camiones 
    public function rastrearIndex(){

        return view('coordenadas.rastrear');
    }


    public function  getEquiposGps(Request  $request) 
    {

        $idCliente =0;
        $cliendID = auth()->user()->id_cliente;
        if($cliendID !== 0)
        {
            $idCliente =$cliendID;
        }
         

        $params = $request->query();

      
        $contenedoresVarios = $params['contenedores'] ?? null;
        
        $idEmpresa = Auth::User()->id_empresa;
        $asignaciones = DB::table('asignaciones')
        ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
        ->join('equipos', 'equipos.id', '=', 'asignaciones.id_camion')
        ->join('gps_company','gps_company.id','=','equipos.gps_company_id')
        ->leftjoin('equipos as eq_chasis', 'eq_chasis.id', '=', 'asignaciones.id_chasis')
        ->leftjoin('gps_company as gps_companyChasis', 'gps_companyChasis.id', '=', 'eq_chasis.gps_company_id')

        ->select(
        'docum_cotizacion.id as id_contenedor',
        'asignaciones.id',
        'asignaciones.id_camion',
        'docum_cotizacion.num_contenedor',
        'asignaciones.fecha_inicio',
        'asignaciones.fecha_fin',
        'equipos.id as id_equipo_unico',
        'equipos.id_equipo',
        'equipos.imei', 
        
        'gps_company.url_conexion as tipoGps',
        'eq_chasis.imei as imei_chasis',
         'eq_chasis.id_equipo as id_equipo_chasis',
        'gps_companyChasis.url_conexion as tipoGpsChasis',
        DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN asignaciones.id_operador ELSE asignaciones.id_proveedor END as beneficiario_id"),
        DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN 'Propio' ELSE 'Subcontratado' END as tipo_contrato")
        );

        $beneficiarios = DB::table(function ($query) {
            $query->select('id', 'nombre', 'telefono', DB::raw("'Propio' as tipo_contrato"), 'id_empresa')
                ->from('operadores')
                ->union(
                    DB::table('proveedores')
                        ->select('id', 'nombre', 'telefono', DB::raw("'Subcontratado' as tipo_contrato"), 'id_empresa')
                );
        }, 'beneficiarios');

  
        $datosAll = DB::table('cotizaciones')
         ->select(
            'cotizaciones.id as id_cotizacion',
            'asig.id as id_asignacion',
            'coordenadas.id as id_coordenada',
            'clients.id as id_cliente',
            'clients.nombre as cliente',
            'cotizaciones.origen',
            'cotizaciones.destino',
            'asig.num_contenedor as contenedor', 
            'cotizaciones.estatus',
            'asig.id_equipo_unico',
            'asig.imei',
            'asig.id_equipo',
            'asig.id_contenedor',
            'asig.tipo_contrato',
            'asig.fecha_inicio',
            'asig.fecha_fin',
            'asig.tipoGps',
            'asig.imei_chasis',
            'asig.id_equipo_chasis',
            'asig.tipoGpsChasis',
            'cotizaciones.id_empresa',
            'cotizaciones.latitud',
            'cotizaciones.longitud',
            'cotizaciones.cp_contacto_entrega',
            'beneficiarios.nombre as beneficiario',
            'beneficiarios.telefono as telefono_beneficiario'
        )
    ->join('clients', 'cotizaciones.id_cliente', '=', 'clients.id')
    
   ->joinSub($asignaciones, 'asig', function ($join) {
      $join->on('asig.id_contenedor', '=', 'cotizaciones.id'); 
    })
    ->LeftJoin('coordenadas', 'coordenadas.id_asignacion', '=', 'asig.id') 
    ->joinSub($beneficiarios, 'beneficiarios', function ($join) {
        $join->on('asig.beneficiario_id', '=', 'beneficiarios.id')
             ->on('asig.tipo_contrato', '=', 'beneficiarios.tipo_contrato');
    })
    ->whereNotNull('asig.imei')
    ->whereDate('asig.fecha_fin', '>=',  Carbon::now()->toDateString())
   
    ->when($contenedoresVarios, function ($query) use ($contenedoresVarios) {
        $contenedores = array_filter(array_map('trim', explode(';', $contenedoresVarios)));
    
        if (count($contenedores) > 1) {
            return $query->whereIn('asig.num_contenedor', $contenedores);
        } else {
            return $query->where('asig.num_contenedor', $contenedores[0]);
        }
    })->where('cotizaciones.estatus', '=', 'Aprobada')
    
    ->get();
     $datos = [];
    if($idCliente !== 0){
        $datos = $datosAll ->where('id_empresa', $idEmpresa)->values();
    }
   


    $conboys = DB::table('conboys')
    ->join('conboys_contenedores', 'conboys.id', '=', 'conboys_contenedores.conboy_id')
    ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'conboys_contenedores.id_contenedor')
    ->join('cotizaciones', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
    ->select(
        'conboys.id',
        'conboys.no_conboy',
        'conboys.nombre',
        'conboys.fecha_inicio',
        'conboys.fecha_fin',
        'conboys.user_id',
        'conboys.tipo_disolucion',
        'conboys.estatus',
        'conboys.fecha_disolucion',
        'conboys.geocerca_lat',
        'conboys.geocerca_lng',
        'conboys.geocerca_radio',
    )
    ->whereDate('conboys.fecha_fin', '>=', Carbon::now()->toDateString())
    ->whereDate('conboys.fecha_inicio', '<=', Carbon::now()->toDateString())
  ->where('conboys.estatus', '=', 'Activo')
    ->when($idCliente !== 0, function ($query) use ($idCliente) {
    return $query->where('cotizaciones.id_cliente', $idCliente);
})   
    ->distinct()
    ->get();


     $conboysdetalleAll =  DB::table('conboys_contenedores')
        ->join('conboys', 'conboys.id', '=', 'conboys_contenedores.conboy_id')
        ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'conboys_contenedores.id_contenedor')
        ->leftjoin('asignaciones', 'asignaciones.id_contenedor', '=', 'conboys_contenedores.id_contenedor')
        ->join('cotizaciones', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->join('equipos', 'equipos.id', '=', 'asignaciones.id_camion')
         ->join('gps_company','gps_company.id','=','equipos.gps_company_id')
          ->leftjoin('equipos as eq_chasis', 'eq_chasis.id', '=', 'asignaciones.id_chasis')
        ->leftjoin('gps_company as gps_companyChasis', 'gps_companyChasis.id', '=', 'eq_chasis.gps_company_id')
        ->select(
            'conboys_contenedores.conboy_id',
            'conboys.no_conboy',
            'conboys_contenedores.id_contenedor',
            'docum_cotizacion.num_contenedor',
            'equipos.imei',
                'equipos.id_equipo',
            'gps_company.url_conexion as tipoGps',
            'es_primero',
            'eq_chasis.imei as imei_chasis',
            'gps_companyChasis.url_conexion as tipoGpsChasis',
                'eq_chasis.id_equipo as id_equipo_chasis',

        )
            ->when($idCliente !== 0, function ($query) use ($idCliente) {
            return $query->where('cotizaciones.id_cliente', $idCliente);
        })   ->where('conboys.estatus', '=', 'Activo')  
        ->get();


        $conboysdetalle=$conboysdetalleAll;// ->where('id_empresa', $idEmpresa)->values();

       $equiposAll = Equipo::select(
        'equipos.id',
        'equipos.imei',
        'equipos.tipo',
        'equipos.marca',
        'equipos.id_equipo',
        'equipos.placas',
        'gps_company.url_conexion as tipoGps',
        'equipos.id_empresa'
        )
        ->join('gps_company', 'gps_company.id', '=', 'equipos.gps_company_id')
        //->where('equipos.id_empresa', $idEmpresa)
       // ->where('equipos.tipo','Tractos / Camiones')
        ->whereNotNull('equipos.imei')

        ->get();

        $equipos=$equiposAll->where('id_empresa', $idEmpresa)->values();

          if ($datos) {
            return response()->json([
                'success' => true,
                'datos' => $datos,
                'conboys'=> $conboys,
                'dataConten'=> $conboysdetalle,
                'equipos'=> $equipos,
                'equiposAll'=> $equiposAll,
                'datosAll'=> $datosAll,
                'dataContenAll'=> $conboysdetalleAll
            ]);
        } else {
            return response()->json(['success' => false]);
        }
        
    }


    public function RastreoTabs()  {

        $intervals = RastreoIntervals::where('task_name', 'rastreo_gps_interval')->first();

    
        return view('coordenadas.rastreoTab',compact('intervals'));
        
    }
}