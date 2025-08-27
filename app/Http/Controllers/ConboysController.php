<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\conboys;
use App\Models\conboysContenedores;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\coordenadashistorial;
use Illuminate\Support\Facades\Auth;

class ConboysController extends Controller
{
    //externos
    public function exindex()
    {
         

        return view('mec.coordenadas.index');
    }

 public function  exindexconvoy()
    {
            return view('mec.coordenadas.indexconvoy');

    }

    public function extHistorialUbicaciones(){
        return view('mec.coordenadas.contenedores-ubic');
    }

    //end externos
     public function index()
    {
         

        return view('conboys.index');
    }

    public function getconboysFinalizados(Request $request)
    {
        $idEmpresa = Auth::User()->id_empresa;

        $fechaInicio = $request->query('inicio');
        $fechaFin = $request->query('fin');

     $infoSubquery = "
        (
      select cotizaciones.id as id_union,
           asig.id as id_asignacion,
           clients.nombre as cliente,
           cotizaciones.origen,
           cotizaciones.destino,
           asig.num_contenedor as contenedor,
           cotizaciones.estatus,
           asig.tipo_contrato,
           asig.fecha_inicio,
           asig.fecha_fin,
           id_equipo,
           marca,
           placas,
           cotizaciones.id_empresa,
           'Cotizaciones' as tipoConsulta
        from cotizaciones
        inner join clients on cotizaciones.id_cliente = clients.id
        inner join (
        select docum_cotizacion.id as id_contenedor,
               asignaciones.id,
               asignaciones.id_camion,
               docum_cotizacion.num_contenedor,
               asignaciones.fecha_inicio,
               asignaciones.fecha_fin,
               equipos.id_equipo,
               equipos.marca,
               equipos.placas,
               CASE WHEN asignaciones.id_proveedor IS NULL
                    THEN asignaciones.id_operador
                    ELSE asignaciones.id_proveedor END as beneficiario_id,
               CASE WHEN asignaciones.id_proveedor IS NULL
                    THEN 'Propio'
                    ELSE 'Subcontratado' END as tipo_contrato
        from asignaciones
        inner join docum_cotizacion on docum_cotizacion.id = asignaciones.id_contenedor
        inner join equipos on equipos.id = asignaciones.id_camion
        inner join gps_company on gps_company.id = equipos.gps_company_id
        left join equipos as eq_chasis on eq_chasis.id = asignaciones.id_chasis
       
        ) as asig on asig.id_contenedor = cotizaciones.id
        inner join (
        select * from (
            (select id, nombre, telefono, 'Propio' as tipo_contrato, id_empresa from operadores)
            union
            (select id, nombre, telefono, 'Subcontratado' as tipo_contrato, id_empresa from proveedores)
        ) as beneficiarios
        ) as beneficiarios
        on asig.beneficiario_id = beneficiarios.id
        and asig.tipo_contrato = beneficiarios.tipo_contrato
    where cotizaciones.id_empresa = ?
    union

    select equipos.id as id_union,
           0 as id_asignacion,
           CONCAT(equipos.id_equipo, ' ', COALESCE(equipos.marca,''), ' ', COALESCE(placas, 'SIN PLACA')) as cliente,
           'NA' as origen,
           'NA' as destino,
           equipos.id_equipo as contenedor,
           case when equipos.activo = 1 then 'Activo' else 'Inactivo' end as estatus,
           '' as tipo_contrato,
           NOW() as fecha_inicio,
           NOW() as fecha_fin,
           equipos.id_equipo,
           equipos.marca,
           equipos.placas,
           equipos.id_empresa,
           'Equipos' as tipoConsulta
    from equipos
        where  equipos.id_empresa = ?
    ) as info
    ";

    $data = DB::table('coordenadas_historial')
    ->distinct()
    ->select([
        'coordenadas_historial.id_convoy',
        'coordenadas_historial.ubicacionable_type',
        'coordenadas_historial.ubicacionable_id',
        'coordenadas_historial.tipo',
        'coordenadas_historial.user_id',
        DB::raw("COALESCE(conboys.id,0) as id"),
        DB::raw("COALESCE(conboys.nombre,'NA') as nombre_convoy"),
        DB::raw("COALESCE(conboys.no_conboy,'NA') as no_conboy"),
        DB::raw("COALESCE(conboys.tipo_disolucion,'NA') as tipo_disolucion"),
        DB::raw("COALESCE(conboys.geocerca_lat,'NA') as geocerca_lat"),
        DB::raw("COALESCE(conboys.geocerca_lng,'NA') as geocerca_lng"),
        DB::raw("COALESCE(conboys.geocerca_radio,'NA') as geocerca_radio"),
        DB::raw("COALESCE(conboys.fecha_disolucion,'NA') as fecha_disolucion"),
        'info.id_union',
        'info.cliente',
        'info.origen',
        'info.destino',
        'info.contenedor',
        'info.fecha_inicio as fecha_inicio_viaje',
        'info.fecha_fin as fecha_fin_viaje',
    ])
    ->leftJoin('conboys', 'conboys.id', '=', 'coordenadas_historial.id_convoy')
    ->join(DB::raw($infoSubquery), function($join) use ($idEmpresa) {
        $join->on('info.id_union', '=', 'coordenadas_historial.ubicacionable_id');
    })
    ->setBindings([$idEmpresa, $idEmpresa]) 
    ->whereBetween('coordenadas_historial.registrado_en', [$fechaInicio, $fechaFin])
    ->get();

      
            return response()->json([
                    'success' => true,
                    'message' => 'lista devuelta.'  ,
                    'data'=>  $data                    
                ]); 
    }
    public function getConboys()
    {
        $idEmpresa = Auth::User()->id_empresa;

       $conboys = DB::table('conboys')
        ->select(
            'conboys.id',
            'conboys.no_conboy',
            'conboys.nombre',
            'conboys.fecha_inicio',
            'conboys.fecha_fin',
            'conboys.user_id',
             'tipo_disolucion' ,
            'estatus' ,
            'fecha_disolucion'  ,
            'geocerca_lat' ,
            'geocerca_lng',
            'geocerca_radio' ,
        )->where('estatus','=','activo')
        ->whereIn('conboys.id', function ($query) use ($idEmpresa) {
            $query->select('conboys_contenedores.conboy_id')
                ->from('conboys_contenedores')
                ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'conboys_contenedores.id_contenedor')
                ->where('docum_cotizacion.id_empresa', '=', $idEmpresa);
        })
        ->get()
        ->map(function ($conboy) {
            $conboy->BlockUser = $conboy->user_id !== optional(Auth::user())->id;
            return $conboy;
        });
 

        $conboysdetalleC =  DB::table('conboys_contenedores')
        ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'conboys_contenedores.id_contenedor')
        ->join('asignaciones', 'asignaciones.id_contenedor', '=', 'conboys_contenedores.id_contenedor')
        ->join('equipos', 'equipos.id', '=', 'asignaciones.id_camion')
        ->select(
            'conboys_contenedores.conboy_id',
            'conboys_contenedores.id_contenedor',
            'docum_cotizacion.num_contenedor',
            'equipos.imei',
            'docum_cotizacion.id_empresa'
        )
        ->get();

        $conboysdetalle = $conboysdetalleC->where('id_empresa', $idEmpresa)->values();;
            return response()->json([
                    'success' => true,
                    'message' => 'lista devuelta.'  ,
                    'data'=>  $conboys,
                    'dataConten'=>  $conboysdetalle,
                   'dataConten2'=>  $conboysdetalleC 
                ]); 
    }

    public function create()
    {
        return view('conboys.create');
    }

    function obtenerIniciales($nombreUser)
    {
        $palabras = explode(' ', $nombreUser);
        $iniciales = '';

        foreach ($palabras as $palabra) {
            if (strlen($palabra) > 0) {
                $iniciales .= strtoupper($palabra[0]);
            }
        }

        return $iniciales;
    }

    public function store(Request $request)
    {

        $inicio = $request->input('fecha_inicio');
        $fin = $request->input('fecha_fin');
        $items = $request->input('items_selects');
        $nombre = $request->input('nombre');
        $tipo_disolucion = $request->input('tipo_disolucion');
        $fecha_disolucion = $request->input('fecha_disolucion');
        $geocerca_lat = $request->input('geocerca_lat');
        $geocerca_lng = $request->input('geocerca_lng');
        $geocerca_radio = $request->input('geocerca_radio');
       
       //iniciales cliente + fecha creación formato ddMMyy + consecutivo personalizado(5)
        $userEmp =  Auth::User();
       
       $conce=  $userEmp->consecutivo_conboy += 1;
         $userEmp->save();

         $iniciales = $this->obtenerIniciales($userEmp->name);
         $fecha = now()->format('dmy');
          $consecutivo_formateado = str_pad($conce, 5, '0', STR_PAD_LEFT);

         // Generar no_conboy
        $no_conboy = $iniciales . $fecha . $consecutivo_formateado;

        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_disolucion' => 'required|string|max:50',
        ]);

        $conboy = Conboys::create([
            'nombre' => $nombre,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'user_id' => auth()->id(),
            'no_conboy' => $no_conboy,
            'tipo_disolucion'  => $tipo_disolucion,
            'estatus' =>'Activo',
            'fecha_disolucion'  => $fecha_disolucion ?? null,
            'geocerca_lat' => $geocerca_lat ?? null,
            'geocerca_lng'  =>$geocerca_lng ?? null,
            'geocerca_radio'  => $geocerca_radio ?? null
        ]);

        $mesageDic="antes de for";
        if ($conboy ){
            $mesageDic="si gurado conboys";
           if (is_array($items)) {
            $mesageDic="si es array";
                foreach ($items as $item) {
                    $esPrimero = !conboysContenedores::where('conboy_id', $conboy->id)->exists();
                    [$contenedor, $id_contenedor,$imei] = explode('|', $item);
                    conboysContenedores::create([
                        'conboy_id' => $conboy->id,
                        'id_contenedor' => $id_contenedor,
                        'es_primero'=>  $esPrimero,
                        'usuario'=> auth()->id(),
                        'imei'=> $imei
                        ]);
                    }
                }
                else{
                    $mesageDic="no es array";
                }
            }

        

        return response()->json([
                'success' => true,
                'message' => 'Conboy creado exitosamente.'  ,
                'no_conboy'=> $no_conboy,
            ]);
    }

    public function edit(Conboys $conboy)
    {
        return view('conboys.edit', compact('conboy'));
    }

    public function update(Request $request)
    {
        
        $inicio = $request->input('fecha_inicio');
        $fin = $request->input('fecha_fin');
        $items = $request->input('items_selects');
        $nombre = $request->input('nombre');
        $tipo_disolucion = $request->input('tipo_disolucion');
       
        $geocerca_lat = $request->input('geocerca_lat');
        $geocerca_lng = $request->input('geocerca_lng');
        $geocerca_radio = $request->input('geocerca_radio');

        $id_convoy=$request->idconvoy;
        $convoy= Conboys::find($id_convoy);


        if (!$convoy) {
            return response()->json(['success' => false, 'message' => 'Conboy no encontrado'], 404);
        }

       
        $request->validate([
            'nombre' => 'required|string|max:255'
                   ]);

     
        $convoy->nombre = $request->nombre;
        $convoy->fecha_inicio = $request->fecha_inicio;
        $convoy->fecha_fin = $request->fecha_fin;
        $convoy->tipo_disolucion = $tipo_disolucion;
        $convoy->geocerca_lat = $geocerca_lat;
        $convoy->geocerca_lng = $geocerca_lng;
        $convoy->geocerca_radio = $geocerca_radio;

        $convoy->save();



        if ($convoy ){
            
           if (is_array($items)) {
         
         
                foreach ($items as $item) {
                      [$contenedor, $id_contenedor,$imei] = explode('|', $item);

                    $existe = conboysContenedores::where('conboy_id', $id_convoy)
                                    ->where('id_contenedor', $id_contenedor)
                                    ->exists();


                        if (!$existe) {
                            conboysContenedores::create([
                                                    'conboy_id' => $convoy->id,
                                                    'id_contenedor' => $id_contenedor,
                                                     'es_primero'=>  0,
                                                        'usuario'=> auth()->id(),
                                                        'imei'=> $imei
                                                    ]);


                        }
                   
                    }
                }
                else{
                    $mesageDic="no es array";
                }
        }

        return response()->json([
            'success' => true,
            'message' => 'Conboy actualizado correctamente',
            'no_conboy' => $convoy->no_conboy,
        ]);
      
    }

    public function destroy(Conboys $conboy)
    {
        $conboy->delete();

        return redirect()->route('conboys.index')->with('success', 'Conboy eliminado.');
    }
    public function eliminarContenedor($contenedor, $convoy)
    {
        DB::table('conboys_contenedores')
            ->where('id_contenedor', $contenedor)
            ->where('conboy_id', $convoy)
            ->delete();

        return response()->json(['success' => true]);
    }


    //guardar historial de ubicaciones seguimiento traks


    public function guardarCoordenadasseguimintos(Request $request){
       $request->validate([
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'ubicacionable_id' => 'required|integer',
            'tipo' => 'required|string',
        ]);

        $coordenada = CoordenadasHistorial::create([
            'latitud' => $request->latitud,
            'longitud' => $request->longitud,
            'registrado_en' => now(),
            'user_id' => auth()->id(), 
            'ubicacionable_id' => $request->ubicacionable_id,
            'ubicacionable_type'=>$request-> tipoRastreo,
            'tipo' => $request->tipo,
            'id_convoy' => $request->idProceso,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coordenada registrada correctamente.',
            'data' => $coordenada
        ]);

    }
    function rastreohistorialUbicaciones(Request $request)  {
         $idSearch = $request->query('idSearch'); 
        $type = $request->query('type'); 
          $contenedor = $request->query('contenedor');

            $ubicaciones = CoordenadasHistorial::where('ubicacionable_id', $idSearch)
            ->where('ubicacionable_type', $type)
                ->orderBy('registrado_en', 'asc')
                ->get();


                $inicio = $ubicaciones->first();
$fin    = $ubicaciones->last();


$intermedios = $ubicaciones->slice(1, -1)
    ->take(23 - 2); // máximo 21 waypoints intermedios


$waypoints = collect([$inicio])
    ->merge($intermedios)
    ->merge([$fin]);
        //dd($ubicaciones);
            return view('conboys.mapa_comparacion', compact('waypoints', 'contenedor'));
        }

    public function indexconvoy()
    {
        

        return view('conboys.indexconvoy');


    }

    public function buscarPorNumero($numero)
    {
        $convoy = Conboys::where('no_conboy', $numero)->first();

        if (!$convoy) {
            return response()->json(['success' => false]);
        }

         $idEmpresa = Auth::User()->id_empresa;
        $contenedoresPropios  = DB::table('asignaciones')
        ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
         ->join('equipos', 'equipos.id', '=', 'asignaciones.id_camion')
           ->join('cotizaciones', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
         ->select(
        'docum_cotizacion.id as id_contenedor',
        'asignaciones.id',
        'asignaciones.id_camion',
        'docum_cotizacion.num_contenedor',
        'asignaciones.fecha_inicio',
        
        'equipos.imei',
         )->where('cotizaciones.estatus', '=', 'Aprobada')
         ->where('asignaciones.id_empresa','=',$idEmpresa)
         ->get();


         $contenedoresPropiosAsignados = DB::table('conboys')
        ->join('conboys_contenedores', 'conboys_contenedores.conboy_id', '=', 'conboys.id')
        ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'conboys_contenedores.id_contenedor')
        ->where('docum_cotizacion.id_empresa', $idEmpresa)
        ->where('conboys.no_conboy',  $numero)
        ->select('conboys.id','docum_cotizacion.num_contenedor','docum_cotizacion.id as id_contenedor') 
        ->get();



            return response()->json([
                'success' => true,
                'data' => [
                    'nombre' => $convoy->nombre,
                    'fecha_inicio' => $convoy->fecha_inicio,
                    'fecha_fin' => $convoy->fecha_fin,
                    'no_conboy'=> $convoy->no_conboy,
                    'idconvoy'=> $convoy->id,
                    'BockUser '=> $convoy->user_id !==  optional(Auth::user())->id,
                    
                    'contenedoresPropios'=>$contenedoresPropios,
                    'contenedoresPropiosAsignados'=>$contenedoresPropiosAsignados,
                        ]
            ]);
    }

    public function addContenedores(Request $request)
    {
        try {
            $items = $request->input('items_selects');
            $id_convoy = $request->input('idconvoy');
            $numero_convoy = $request->input('numero_convoy');

            if (!is_array($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El formato de contenedores no es válido.'
                ], 422);
            }

            $convoy = Conboys::where('no_conboy', $numero_convoy)->first();

            if (!$convoy) {
                return response()->json([
                    'success' => false,
                    'message' => 'Convoy no encontrado.'
                ], 404);
            }

            foreach ($items as $item) {
                [$contenedor, $id_contenedor,$imei] = explode('|', $item);

                 $existe = conboysContenedores::where('conboy_id', $convoy->id)
                ->where('id_contenedor', $id_contenedor)
                ->exists();

                if (!$existe) {
                    conboysContenedores::create([
                                                    'conboy_id' => $convoy->id,
                                                    'id_contenedor' => $id_contenedor,
                                                     'es_primero'=>  0,
                                                        'usuario'=> auth()->id(),
                                                        'imei'=> $imei
                                                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Contenedores agregados correctamente.',
                'no_conboy' => $convoy->no_conboy
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error: ' . $e->getMessage()
            ], 500);
        }
    }


    public function HistorialUbicaciones(){
        return view('conboys.ubicaciones');
    }


    public function getHistorialUbicaciones(){
 $idEmpresa = Auth::User()->id_empresa;
$cotizacion = DB::table('cotizaciones')
    ->join('clients', 'cotizaciones.id_cliente', '=', 'clients.id')
    ->join('docum_cotizacion', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
    ->join('asignaciones', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
    ->join('coordenadas_historial', 'coordenadas_historial.ubicacionable_id', '=', 'docum_cotizacion.id')
     ->where('docum_cotizacion.id_empresa', '=', $idEmpresa)
    ->groupBy(
        'cotizaciones.id',
        'asignaciones.id',
        'clients.nombre',
        'cotizaciones.origen',
        'cotizaciones.destino',
        'docum_cotizacion.num_contenedor',
        'cotizaciones.estatus'
    )
    ->select(
        'cotizaciones.id',
        'asignaciones.id as id_Asignacion',
        'clients.nombre as cliente',
        'cotizaciones.origen',
        'cotizaciones.destino',
        'docum_cotizacion.num_contenedor as contenedor',
        'cotizaciones.estatus',
        'cotizaciones.latitud',
        'cotizaciones.longitud',
        DB::raw('MAX(coordenadas_historial.registrado_en) as ultima_fecha'),
        DB::raw('MAX(coordenadas_historial.latitud) as latitud_seguimiento'),
        DB::raw('MAX(coordenadas_historial.longitud) as longitud_seguimiento')
    )
    ->get();



    return response()->json($cotizacion);

    }


    public function updateEstatus(Request $request)
{
    $idConvoy = $request->input('idconvoy'); 
    $idConvoy = $request->input('idconvoy'); 

    
    $convoy = Conboys::find($idConvoy);

    if (!$convoy) {
        return response()->json([
            'success' => false,
            'message' => 'Convoy no encontrado'
        ]);
    }

    // Aquí aplicas los cambios que recibas
    $convoy->estatus = $request->input('nuevoEstatus');
    $convoy->fecha_disolucion = now();
    $convoy->save();

    return response()->json([
        'success' => true,
        'message' => 'Estatus actualizado con éxito'
    ]);
}
public function mapaRastreoVarios(Request $request)
{


    $idCliente =0;
    $cliendID = auth()->user()->id_cliente;
    if($cliendID !== 0)
    {
        $idCliente =$cliendID;
    }
         
        
    $idEmpresa = Auth::User()->id_empresa;
    $asignaciones = DB::table('asignaciones')
    ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
    ->join('equipos', 'equipos.id', '=', 'asignaciones.id_camion')
    ->join('gps_company','gps_company.id','=','equipos.gps_company_id')
    ->leftjoin('equipos as eq_chasis', 'eq_chasis.id', '=', 'asignaciones.id_chasis')
    ->select(
        'docum_cotizacion.id as id_contenedor',
        'asignaciones.id',
        'asignaciones.id_camion',
        'docum_cotizacion.num_contenedor',
        'asignaciones.fecha_inicio',
        'asignaciones.fecha_fin',      
        'equipos.imei',      
        'gps_company.url_conexion as tipoGps',
        'eq_chasis.imei as imei_chasis',
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
            'clients.nombre as cliente',
            'cotizaciones.origen',
            'cotizaciones.destino',
            'asig.num_contenedor as contenedor', 
            'cotizaciones.estatus',
            'asig.imei',
            'asig.id_contenedor',
            'asig.tipo_contrato',
            'asig.fecha_inicio',
            'asig.fecha_fin',
            'asig.tipoGps',
            'asig.imei_chasis',
            'cotizaciones.id_empresa'
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
    ->when($idCliente !== 0, function ($query) use ($idCliente) {
    return $query->where('cotizaciones.id_cliente', $idCliente);
    })   
    ->where('cotizaciones.estatus', '=', 'Aprobada')
    ->get();

    $ids = explode(',', $request->input('ids')); 
  
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
    )->wherein('conboys.id',$ids) 
    ->distinct()
    ->get();

    $conboysdetalleAll =  DB::table('conboys_contenedores')
        ->join('conboys', 'conboys.id', '=', 'conboys_contenedores.conboy_id')
        ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'conboys_contenedores.id_contenedor')
        ->leftjoin('asignaciones', 'asignaciones.id_contenedor', '=', 'conboys_contenedores.id_contenedor')
        ->join('cotizaciones', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->join('equipos', 'equipos.id', '=', 'asignaciones.id_camion')
         ->join('gps_company','gps_company.id','=','equipos.gps_company_id')
        ->select(
            'conboys_contenedores.conboy_id',
            'conboys.no_conboy',
            'conboys_contenedores.id_contenedor',
            'docum_cotizacion.num_contenedor',
            'equipos.imei',
            'gps_company.url_conexion as tipoGps',
            'es_primero',
            
        )
        ->when($idCliente !== 0, function ($query) use ($idCliente) {
                 return $query->where('cotizaciones.id_cliente', $idCliente);
            })
        ->where('conboys.estatus','=','activo')  
        ->wherein('conboys.id',$ids) 
        ->get();
    
    return view('coordenadas.mapas_rastreoAll', compact('conboys','conboysdetalleAll','datosAll'));
}
   
}
