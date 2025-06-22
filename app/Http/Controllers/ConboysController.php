<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conboys;
use App\Models\conboysContenedores;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\coordenadashistorial;
use Illuminate\Support\Facades\Auth;

class ConboysController extends Controller
{
     public function index()
    {
         

        return view('conboys.index');
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
            'conboys.user_id'
        )
        ->whereIn('conboys.id', function ($query) use ($idEmpresa) {
            $query->select('conboys_contenedores.conboy_id')
                ->from('conboys_contenedores')
                ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'conboys_contenedores.id_contenedor')
                ->where('docum_cotizacion.id_empresa', '=', $idEmpresa);
        })
        ->get();
 

        $conboysdetalle =  DB::table('conboys_contenedores')
        ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'conboys_contenedores.id_contenedor')
        ->join('asignaciones', 'asignaciones.id_contenedor', '=', 'conboys_contenedores.id_contenedor')
        ->join('equipos', 'equipos.id', '=', 'asignaciones.id_camion')
        ->select(
            'conboys_contenedores.conboy_id',
            'conboys_contenedores.id_contenedor',
            'docum_cotizacion.num_contenedor',
            'equipos.imei',
            
        )->where('docum_cotizacion.id_empresa','=',$idEmpresa)
        ->get();
            return response()->json([
                    'success' => true,
                    'message' => 'lista devuelta.'  ,
                    'data'=>  $conboys,
                    'dataConten'=>  $conboysdetalle,
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
        ]);

        $conboy = Conboys::create([
            'nombre' => $nombre,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'user_id' => auth()->id(),
            'no_conboy' => $no_conboy
        ]);

        $mesageDic="antes de for";
        if ($conboy ){
            $mesageDic="si gurado conboys";
           if (is_array($items)) {
            $mesageDic="si es array";
                foreach ($items as $item) {
                    [$contenedor, $id_contenedor] = explode('-', $item);
                    conboysContenedores::create([
                        'conboy_id' => $conboy->id,
                        'id_contenedor' => $id_contenedor
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

        $id_convoy=$request->idconvoy;
        $convoy= Conboys::find($id_convoy);


        if (!$convoy) {
            return response()->json(['success' => false, 'message' => 'Conboy no encontrado'], 404);
        }

        // Validar que las fechas y el nombre estén presentes si así lo deseas
        $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);

     
        $convoy->nombre = $request->nombre;
        $convoy->fecha_inicio = $request->fecha_inicio;
        $convoy->fecha_fin = $request->fecha_fin;

        $convoy->save();



        if ($convoy ){
            
           if (is_array($items)) {
         
         
                foreach ($items as $item) {
                    [$contenedor, $id_contenedor] = explode('-', $item);

                    $existe = ConboysContenedores::where('conboy_id', $id_convoy)
                                    ->where('id_contenedor', $id_contenedor)
                                    ->exists();


                        if (!$existe) {
                            conboysContenedores::create([
                                                    'conboy_id' => $convoy->id,
                                                    'id_contenedor' => $id_contenedor
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
            'ubicacionable_type'=>'rastreo SGT',
            'tipo' => $request->tipo,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coordenada registrada correctamente.',
            'data' => $coordenada
        ]);

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
                [$contenedor, $id_contenedor] = explode('-', $item);

                 $existe = conboysContenedores::where('conboy_id', $convoy->id)
                ->where('id_contenedor', $id_contenedor)
                ->exists();

                if (!$existe) {
                    conboysContenedores::create([
                        'conboy_id' => $convoy->id,
                        'id_contenedor' => $id_contenedor
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

   
}
