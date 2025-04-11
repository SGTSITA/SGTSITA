<?php

namespace App\Http\Controllers;

use App\Models\Asignaciones;
use App\Models\Bancos;
use App\Models\Cotizaciones;
use App\Models\Equipo;
use App\Models\Operador;
use App\Models\Planeacion;
use App\Models\Proveedor;
use App\Models\Client;
use App\Models\Subclientes;
use App\Models\Coordenadas;
use App\Models\GastosOperadores;
use App\Models\BancoDineroOpe;
use App\Models\DocumCotizacion;
use Illuminate\Http\Request;
use DB;
use Log;
use Session;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PlaneacionController extends Controller
{
    public function index(){

       /* $cotizaciones = Cotizaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('estatus', '=', 'Aprobada')->where('estatus_planeacion', '=', NULL)->get();
     
        $numCotizaciones = $cotizaciones->count();
        $proveedores = Proveedor::where('id_empresa' ,'=',auth()->user()->id_empresa)
        ->where(function ($query) {
            $query->where('tipo', '=', 'servicio de burreo')
                  ->orwhere('tipo', '>=', 'servicio de viaje');
        })
        ->get();

        $equipos = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();
        $operadores = Operador::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();
        $events = [];

        $appointments = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
        ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
        ->where('asignaciones.id_empresa' ,'=',auth()->user()->id_empresa)->where('cotizaciones.estatus', '=', 'Aprobada')->get();

        foreach ($appointments as $appointment) {
            if($appointment->id_operador == NULL){
                $description = 'Proveedor: ' . $appointment->Proveedor->nombre . ' - ' . $appointment->Proveedor->telefono . '<br>' . 'Costo viaje: ' . $appointment->precio;
                $tipo = 'S';
            }else{
                if($appointment->Contenedor->Cotizacion->tipo_viaje == 'Sencillo'){
                    $description = 'Tipo viaje: ' . $appointment->Contenedor->Cotizacion->tipo_viaje . '<br> <br>' .
                    'Operador: ' . $appointment->Operador->nombre . ' - ' . $appointment->Operador->telefono . '<br>' .
                    'Camion: ' . ' #' . $appointment->Camion->id_equipo . ' - ' . $appointment->Camion->num_serie . ' - ' . $appointment->Camion->modelo . '<br>' .
                    'Chasis: ' . $appointment->Chasis->num_serie . ' - ' . $appointment->Chasis->modelo . '<br>';
                }elseif($appointment->Contenedor->Cotizacion->tipo_viaje == 'Full'){
                    $description = 'Tipo viaje: ' . $appointment->Contenedor->Cotizacion->tipo_viaje . '<br> <br>' .
                    'Operador: ' . $appointment->Operador->nombre . ' - ' . $appointment->Operador->telefono . '<br>' .
                    'Camion: ' . ' #' . $appointment->Camion->id_equipo . ' - ' . $appointment->Camion->num_serie . ' - ' . $appointment->Camion->modelo . '<br>' .
                    'Chasis: ' . $appointment->Chasis->num_serie . ' - ' . $appointment->Chasis->modelo . '<br>' .
                    'Chasis 2: ' . $appointment->Chasis2->num_serie . ' - ' . $appointment->Chasis2->modelo . '<br>' .
                    'Doly: ' . $appointment->Doly->num_serie . ' - ' . $appointment->Doly->modelo . '<br>';
                }
                $tipo = 'P';
            }

            $coordenadas = Coordenadas::where('id_asignacion', '=', $appointment->id)->first();

            $description = str_replace('<br>', "\n", $description);

            $isOperadorNull = $appointment->id_operador === NULL;

            $event = [
                'title' => $tipo .' / '. $appointment->Contenedor->Cotizacion->Cliente->nombre . ' / #' . $appointment->Contenedor->Cotizacion->DocCotizacion->num_contenedor,
                'description' => $description,
                'start' => $appointment->fecha_inicio,
                'end' => $appointment->fecha_fin,
                'urlId' => $appointment->id,
                'idCotizacion' => $appointment->Contenedor->id_cotizacion,
                'isOperadorNull' => $isOperadorNull,
                'nombreOperadorSub' => $appointment->nombreOperadorSub ?? '',
                'telefonoOperadorSub' => $appointment->telefonoOperadorSub ?? '',
                'placasOperadorSub' => $appointment->placasOperadorSub ?? '',
            ];


            // Verificar si $coordenadas no es null antes de acceder a su propiedad id
            if ($coordenadas !== null) {
                $event['idCoordenda'] = $appointment->id;
            }

            if ($appointment->Operador !== null) {
                $event['telOperador'] = $appointment->Operador->telefono;
            }
            // else{
            //     $event['telOperador'] = '5585314498';
            // }

            $events[] = $event;

        }

        $planeaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
                        ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
                        ->where('asignaciones.fecha_inicio', '!=', NULL)
                        ->where('asignaciones.id_empresa' ,'=',auth()->user()->id_empresa)
                        ->select('cotizaciones.estatus', 'Aprobada')
                        ->select('asignaciones.*', 'docum_cotizacion.num_contenedor')
                        ->get();*/
        //compact('equipos', 'operadores', 'events',  'cotizaciones', 'proveedores', 'numCotizaciones', 'planeaciones')
        return view('planeacion.index');
    }

    public function programarViaje(){
        return view('planeacion.planeacion-step');
    }

    public function initBoard(){
        
        $proveedores = Proveedor::where('id_empresa' ,'=',auth()->user()->id_empresa)->selectRaw('CONCAT(id,7000) as id,nombre as name, '."'true'".' as expanded')->get();
        $equipos = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->selectRaw('CONCAT(id,5000) as id, id_equipo as name, '."'true'".' as expanded')->get();
        $board = [];
        $board[] = ["name" => "Sub Contratados", "id" => "S", "expanded" => true, "children" => $proveedores];
        $board[] = ["name" => "Propios", "id" => "P", "expanded" => true, "children" => $equipos];

        $planeaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
                        ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
                        ->where('asignaciones.fecha_inicio', '!=', NULL)
                        ->where('asignaciones.id_empresa' ,'=',auth()->user()->id_empresa)
                        ->select('cotizaciones.estatus', 'Aprobada')
                        ->select('asignaciones.*', 'docum_cotizacion.num_contenedor')
                        ->get();

      //  $extractor = [];//extractor_ventas::where('fecha_operacion','>=','2023-09-05')->orderBy('estatus_id','desc')->get();
        $fecha = Carbon::now()->subdays(10)->format('Y-m-d');
        return response()->json(["boardCentros"=> $board,"extractor"=>$planeaciones,"scrollDate"=> $fecha,"ek" => $equipos]);  
    }

    public function equipos(Request $request){
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        if($fechaInicio  &&  $fechaFin){
            $camionesAsignados = Asignaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)
            ->whereNotNull('id_camion')
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('fecha_inicio', '<=', $fechaFin)
                      ->where('fecha_fin', '>=', $fechaInicio);
            })
            ->pluck('id_camion');

            $camionesNoAsignados = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)
            ->where('tipo', 'LIKE', '%Camiones%')
            ->whereNotIn('id', $camionesAsignados)
            ->orWhereNotIn('id', function ($query) {
                $query->select('id_camion')->from('asignaciones')->whereNull('id_camion');
            })
            ->get();

            $chasisAsignados = Asignaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)
            ->whereNotNull('id_chasis')
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('fecha_inicio', '<=', $fechaFin)
                      ->where('fecha_fin', '>=', $fechaInicio);
            })
            ->pluck('id_chasis');

            $chasisNoAsignados = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)
            ->where('tipo', 'LIKE', '%Chasis%')
                ->whereNotIn('id', $chasisAsignados)
                ->orWhereNotIn('id', function ($query) {
                    $query->select('id_chasis')->from('asignaciones')->whereNull('id_chasis');
                })
                ->get();

            $dolysAsignados = Asignaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)
            ->whereNotNull('id_camion')
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('fecha_inicio', '<=', $fechaFin)
                        ->where('fecha_fin', '>=', $fechaInicio);
            })
            ->pluck('id_camion');

            $dolysNoAsignados = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)
            ->where('tipo', 'LIKE', '%Dolys%')
                ->whereNotIn('id', $dolysAsignados)
                ->orWhereNotIn('id', function ($query) {
                    $query->select('id_camion')->from('asignaciones')->whereNull('id_camion');
                })
                ->get();

            $operadorAsignados = Asignaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)
            ->whereNotNull('id_operador')
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('fecha_inicio', '<=', $fechaFin)
                        ->where('fecha_fin', '>=', $fechaInicio);
            })
            ->pluck('id_operador');

            $operadorNoAsignados = Operador::where('id_empresa' ,'=',auth()->user()->id_empresa)
            ->whereNotIn('id', $operadorAsignados)
                ->orWhereNotIn('id', function ($query) {
                    $query->select('id_operador')->from('asignaciones')->whereNull('id_operador');
                })
                ->get();


            $bancos = Bancos::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('saldo', '>', '0')->get();

            return view('planeacion.resultado_equipos', ['bancos' => $bancos, 'dolysNoAsignados' => $dolysNoAsignados, 'camionesNoAsignados' => $camionesNoAsignados, 'chasisNoAsignados' => $chasisNoAsignados, 'operadorNoAsignados' => $operadorNoAsignados]);

        }
    }

    public function asignacion(Request $request){

        $validator = Validator::make($request->all(), [
            'id_proveedor' => 'required_if:viaje,Camion Subcontratado',
            'precio_proveedor' => 'required_if:viaje,Camion Subcontratado',
            'camion' => 'required_if:viaje,Camion Propio',
            'operador' => 'required_if:viaje,Camion Propio',
            'chasis' => 'required_if:viaje,Camion Propio',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $numContenedor = $request->get('num_contenedor');
        $exists = Asignaciones::where('id_contenedor', $numContenedor)->exists();

        if ($exists) {
            $asignaciones = Asignaciones::where('id_contenedor','=',$numContenedor)->first();
            $asignaciones->id_chasis = $request->get('chasis');
            $asignaciones->id_chasis2 = $request->get('chasisAdicional1');
            $asignaciones->id_dolys = $request->get('nuevoCampoDoly');
            $asignaciones->id_camion = $request->get('camion');
            $asignaciones->id_contenedor = $request->get('num_contenedor');
            $asignaciones->id_operador = $request->get('operador');
            $asignaciones->id_proveedor = $request->get('id_proveedor');
            $asignaciones->sueldo_viaje = $request->get('sueldo_viaje');
            $asignaciones->estatus_pagado = 'Pendiente Pago';
            $asignaciones->dinero_viaje = $request->get('dinero_viaje');

            if($request->get('id_proveedor') == NULL){
               // $asignaciones->fecha_inicio = $request->get('fecha_inicio');
                //$asignaciones->fecha_fin = $request->get('fecha_fin') . ' 23:00:00';

                $asignaciones->fehca_inicio_guard = $request->get('fecha_inicio');
                $asignaciones->fehca_fin_guard = $request->get('fecha_fin') . ' 23:00:00';
            }else{
               // $asignaciones->fecha_inicio = $request->get('fecha_inicio_proveedor');
                //$asignaciones->fecha_fin = $request->get('fecha_fin_proveedor') . ' 23:00:00';

                $asignaciones->fehca_inicio_guard = $request->get('fecha_inicio_proveedor');
                $asignaciones->fehca_fin_guard = $request->get('fecha_fin_proveedor') . ' 23:00:00';
            }

            $asignaciones->fecha_inicio = $asignaciones->fehca_inicio_guard;
            $asignaciones->fecha_fin = $asignaciones->fehca_fin_guard;

            $asignaciones->precio = $request->get('precio_proveedor');
            $asignaciones->burreo = $request->get('burreo_proveedor');
            $asignaciones->maniobra = $request->get('maniobra_proveedor');
            $asignaciones->estadia = $request->get('estadia_proveedor');
            $asignaciones->otro = $request->get('otro_proveedor');
            $asignaciones->iva = $request->get('iva_proveedor');
            $asignaciones->retencion = $request->get('retencion_proveedor');
            $asignaciones->total_proveedor = $request->get('total_proveedor');
            $asignaciones->sobrepeso_proveedor = $request->get('sobrepeso_proveedor');
            $asignaciones->total_tonelada = round(floatVal($request->get('sobrepeso_proveedor')) * floatVal($request->get('cantidad_sobrepeso_proveedor')),4);

            $asignaciones->id_banco1_dinero_viaje = $request->get('id_banco1_dinero_viaje');
            $asignaciones->cantidad_banco1_dinero_viaje = $request->get('cantidad_banco1_dinero_viaje');
            $asignaciones->id_banco2_dinero_viaje = $request->get('id_banco2_dinero_viaje');
            $asignaciones->cantidad_banco2_dinero_viaje = $request->get('cantidad_banco2_dinero_viaje');
            $asignaciones->base1_proveedor = $request->get('base_factura');
            $asignaciones->base2_proveedor = $request->get('base_taref');

            if($request->get('sueldo_viaje') > $request->get('dinero_viaje')){
                $resta = $request->get('sueldo_viaje') - $request->get('dinero_viaje');
                $asignaciones->pago_operador = $resta;
                $asignaciones->restante_pago_operador = $resta;                
            }

            $asignaciones->update();

           // Bancos::where('id1' ,'=',$request->get('id_banco1_dinero_viaje'))->update(["saldo" => DB::raw("saldo - ". $request->get('dinero_viaje'))]);

            $cotizacion = Cotizaciones::where('id', '=',  $request->get('cotizacion'))->first();

            if($request->get('id_proveedor') == NULL){
            }else{
                $cotizacion->prove_restante = $asignaciones->total_proveedor;
            }
            
            $cotizacion->estatus_planeacion = 1;
            $cotizacion->tipo_viaje = $request->get('tipo');
            $cotizacion->update();
        } else {
            try{

            DB::beginTransaction();
            $asignaciones = new Asignaciones;
            $asignaciones->id_chasis = $request->get('chasis');
            $asignaciones->id_chasis2 = $request->get('chasisAdicional1');
            $asignaciones->id_dolys = $request->get('nuevoCampoDoly');
            $asignaciones->id_camion = $request->get('camion');
            $asignaciones->id_contenedor = $request->get('num_contenedor');
            $asignaciones->id_operador = $request->get('operador');
            $asignaciones->id_proveedor = $request->get('id_proveedor');
            $asignaciones->sueldo_viaje = $request->get('sueldo_viaje');
            $asignaciones->estatus_pagado = 'Pendiente Pago';
            $asignaciones->dinero_viaje = $request->get('dinero_viaje');

            if($request->get('id_proveedor') == NULL){
                //$asignaciones->fecha_inicio = $request->get('fecha_inicio');
                //$asignaciones->fecha_fin = $request->get('fecha_fin') . ' 23:00:00';

                $asignaciones->fehca_inicio_guard = $request->get('fecha_inicio');
                $asignaciones->fehca_fin_guard = $request->get('fecha_fin') . ' 23:00:00';
            }else{
                //$asignaciones->fecha_inicio = $request->get('fecha_inicio_proveedor');
                //$asignaciones->fecha_fin = $request->get('fecha_fin_proveedor') . ' 23:00:00';

                $asignaciones->fehca_inicio_guard = $request->get('fecha_inicio_proveedor');
                $asignaciones->fehca_fin_guard = $request->get('fecha_fin_proveedor') . ' 23:00:00';
            }

            $asignaciones->fecha_inicio = $asignaciones->fehca_inicio_guard;
            $asignaciones->fecha_fin = $asignaciones->fehca_fin_guard;

            $asignaciones->precio = $request->get('precio_proveedor');
            $asignaciones->burreo = $request->get('burreo_proveedor');
            $asignaciones->maniobra = $request->get('maniobra_proveedor');
            $asignaciones->estadia = $request->get('estadia_proveedor');
            $asignaciones->otro = $request->get('otro_proveedor');
            $asignaciones->iva = $request->get('iva_proveedor');
            $asignaciones->retencion = $request->get('retencion_proveedor');
            $asignaciones->total_proveedor = $request->get('total_proveedor');
            $asignaciones->sobrepeso_proveedor = $request->get('sobrepeso_proveedor');

            $asignaciones->id_banco1_dinero_viaje = $request->get('id_banco1_dinero_viaje');
            $asignaciones->cantidad_banco1_dinero_viaje = $request->get('cantidad_banco1_dinero_viaje');
            $asignaciones->id_banco2_dinero_viaje = $request->get('id_banco2_dinero_viaje');
            $asignaciones->cantidad_banco2_dinero_viaje = $request->get('cantidad_banco2_dinero_viaje');
            $asignaciones->base1_proveedor = $request->get('base_factura');
            $asignaciones->base2_proveedor = $request->get('base_taref');
            $asignaciones->total_tonelada = round(floatVal($request->get('sobrepeso_proveedor')) * floatVal($request->get('cantidad_sobrepeso_proveedor')),4);

            
            
            if($request->get('sueldo_viaje') > $request->get('dinero_viaje')){
            $resta = $request->get('sueldo_viaje') - $request->get('dinero_viaje');
            $asignaciones->pago_operador = $resta;
            $asignaciones->restante_pago_operador = $resta;
            }
            $asignaciones->save();

            $cotizacion = Cotizaciones::where('id', '=',  $request->get('cotizacion'))->first();
            $contenedor = DocumCotizacion::where('id_cotizacion',$request->get('cotizacion'))->first();
            if($request->get('id_proveedor') == NULL){
                
                    $contenedoresAbonos = [];
                    $contenedorAbono = [
                        'num_contenedor' => $contenedor->num_contenedor,
                        'abono' => $request->get('dinero_viaje')
                    ];
    
                    array_push($contenedoresAbonos, $contenedorAbono);

                   
    
                    Bancos::where('id' ,'=',$request->get('id_banco1_dinero_viaje'))->update(["saldo" => DB::raw("saldo - ". $request->get('dinero_viaje'))]);
                    BancoDineroOpe::insert([[
                                            'id_operador' => $request->get('operador'), 
                                           
                                            'id_banco1' => $request->get('id_banco1_dinero_viaje'),
                                            'monto1' => $request->get('dinero_viaje'),
                                            'fecha_pago' => date('Y-m-d'),
                                            'tipo' => 'Salida',
                                            'id_empresa' => auth()->user()->id_empresa,
                                            'contenedores' => json_encode($contenedoresAbonos),
                                            'descripcion_gasto' => 'Dinero para viaje'
                                        ]]);
                  
                
                
                }
                DB::commit();
            }catch(\Trowable $t){
                DB::rollback();
                \Log::channel('daily')->info('No se guardó operación bancaria: '.$t->getMessage());
               }
            
            if($request->get('id_proveedor') == NULL){
            }else{
                $cotizacion->prove_restante = $asignaciones->total_proveedor;
            }
            $cotizacion->estatus_planeacion = 1;
            $cotizacion->tipo_viaje = $request->get('tipo');
        //    $cotizacion->precio_tonelada = 
            $cotizacion->update();

            $coordenada = new Coordenadas;
            $coordenada->id_cotizacion = $cotizacion->id;
            $coordenada->id_asignacion = $asignaciones->id;
            $coordenada->save();

        }
        $cotizacion_data = [
            "tipo_viaje" => $cotizacion->tipo_viaje,
        ];

        return response()->json(['success' => true, 'cotizacion_data' => $cotizacion_data]);
    }

    public function edit_fecha(Request $request)
    {
        $id = $request->get('urlId');
        $urlId = $request->get('urlId');

        $cotizaciones = Cotizaciones::find($urlId);
        
        if($request->get('finzalizar_vieje') != NULL){
            
            $cotizaciones->estatus = $request->get('finzalizar_vieje');
            
        }
        $cotizaciones->update();

        $asignaciones = Asignaciones::where('id_contenedor','=',$cotizaciones->id)->first();

        if($request->get('finzalizar_vieje') == 'Finalizado'){
            $asignaciones->fecha_inicio = null;
            $asignaciones->fecha_fin = null;
        }else{
            $asignaciones->fecha_inicio = $request->get('nuevaFechaInicio');
            $asignaciones->fecha_fin = $request->get('nuevaFechaFin');
        }

        $asignaciones->nombreOperadorSub = $request->get('nombreOperadorSub');
        $asignaciones->telefonoOperadorSub = $request->get('telefonoOperadorSub');
        $asignaciones->placasOperadorSub = $request->get('placasOperadorSub');

        $asignaciones->update();

        // Devuelve una respuesta, por ejemplo:
        return response()->json(['message' => 'Cambios aplicados correctamente','TMensaje' => 'success']);
    }

    public function advance_planeaciones(Request $request) {
        $cotizaciones = Cotizaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('estatus', '=', 'Aprobada')->where('estatus_planeacion', '=', NULL)->get();
        $numCotizaciones = $cotizaciones->count();
        $proveedores = Proveedor::where('id_empresa' ,'=',auth()->user()->id_empresa)
        ->where(function ($query) {
            $query->where('tipo', '=', 'servicio de burreo')
                  ->orwhere('tipo', '>=', 'servicio de viaje');
        })
        ->get();

        $equipos = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();
        $operadores = Operador::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();
        $events = [];

        $appointments = Asignaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();


        foreach ($appointments as $appointment) {
            if($appointment->id_operador == NULL){
                $description = 'Proveedor: ' . $appointment->Proveedor->nombre . ' - ' . $appointment->Proveedor->telefono . '<br>' . 'Costo viaje: ' . $appointment->precio;
                $tipo = 'S';
            }else{
                if($appointment->Contenedor->Cotizacion->tipo_viaje == 'Sencillo'){
                    $description = 'Tipo viaje: ' . $appointment->Contenedor->Cotizacion->tipo_viaje . '<br> <br>' .
                    'Operador: ' . $appointment->Operador->nombre . ' - ' . $appointment->Operador->telefono . '<br>' .
                    'Camion: ' . ' #' . $appointment->Camion->id_equipo . ' - ' . $appointment->Camion->num_serie . ' - ' . $appointment->Camion->modelo . '<br>' .
                    'Chasis: ' . $appointment->Chasis->num_serie . ' - ' . $appointment->Chasis->modelo . '<br>';
                }elseif($appointment->Contenedor->Cotizacion->tipo_viaje == 'Full'){
                    $description = 'Tipo viaje: ' . $appointment->Contenedor->Cotizacion->tipo_viaje . '<br> <br>' .
                    'Operador: ' . $appointment->Operador->nombre . ' - ' . $appointment->Operador->telefono . '<br>' .
                    'Camion: ' . ' #' . $appointment->Camion->id_equipo . ' - ' . $appointment->Camion->num_serie . ' - ' . $appointment->Camion->modelo . '<br>' .
                    'Chasis: ' . $appointment->Chasis->num_serie . ' - ' . $appointment->Chasis->modelo . '<br>' .
                    'Chasis 2: ' . $appointment->Chasis2->num_serie . ' - ' . $appointment->Chasis2->modelo . '<br>' .
                    'Doly: ' . $appointment->Doly->num_serie . ' - ' . $appointment->Doly->modelo . '<br>';
                }
                $tipo = 'P';
            }

            $coordenadas = Coordenadas::where('id_asignacion', '=', $appointment->id)->first();

            $description = str_replace('<br>', "\n", $description);

            $isOperadorNull = $appointment->id_operador === NULL;

            $event = [
                'title' => $tipo .' / '. $appointment->Contenedor->Cotizacion->Cliente->nombre . ' / #' . $appointment->Contenedor->Cotizacion->DocCotizacion->num_contenedor,
                'description' => $description,
                'start' => $appointment->fecha_inicio,
                'end' => $appointment->fecha_fin,
                'urlId' => $appointment->id,
                'idCotizacion' => $appointment->Contenedor->id_cotizacion,
                'isOperadorNull' => $isOperadorNull,
                'nombreOperadorSub' => $appointment->nombreOperadorSub ?? '',
                'telefonoOperadorSub' => $appointment->telefonoOperadorSub ?? '',
                'placasOperadorSub' => $appointment->placasOperadorSub ?? '',
            ];


            // Verificar si $coordenadas no es null antes de acceder a su propiedad id
            if ($coordenadas !== null) {
                $event['idCoordenda'] = $appointment->id;
            }

            if ($appointment->Operador !== null) {
                $event['telOperador'] = $appointment->Operador->telefono;
            }
            // else{
            //     $event['telOperador'] = '5585314498';
            // }

            $events[] = $event;

        }

        $planeaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
        ->where('asignaciones.fecha_inicio', '!=', NULL)->where('asignaciones.id_empresa' ,'=',auth()->user()->id_empresa)
        ->select('asignaciones.*', 'docum_cotizacion.num_contenedor')
        ->get();

        $asignaciones = Asignaciones::where('id_empresa' ,'=', auth()->user()->id_empresa);

        if ($request->contenedor !== null) {
            $asignaciones = $asignaciones->where('id', $request->contenedor);
        }

        $asignaciones = $asignaciones->first();

        return view('planeacion.index', compact('equipos', 'operadores', 'events',  'cotizaciones', 'proveedores', 'numCotizaciones', 'asignaciones', 'planeaciones'));
    }

    public function advance_planeaciones_faltantes(Request $request) {
        $cotizaciones = Cotizaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('estatus', '=', 'Aprobada')->where('estatus_planeacion', '=', NULL)->get();
        $numCotizaciones = $cotizaciones->count();
        $proveedores = Proveedor::where('id_empresa' ,'=',auth()->user()->id_empresa)
        ->where(function ($query) {
            $query->where('tipo', '=', 'servicio de burreo')
                  ->orwhere('tipo', '>=', 'servicio de viaje');
        })
        ->get();

        $equipos = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();
        $operadores = Operador::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();
        $events = [];

        $appointments = Asignaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();


        foreach ($appointments as $appointment) {
            if($appointment->id_operador == NULL){
                $description = 'Proveedor: ' . $appointment->Proveedor->nombre . ' - ' . $appointment->Proveedor->telefono . '<br>' . 'Costo viaje: ' . $appointment->precio;
                $tipo = 'S';
            }else{
                if($appointment->Contenedor->Cotizacion->tipo_viaje == 'Sencillo'){
                    $description = 'Tipo viaje: ' . $appointment->Contenedor->Cotizacion->tipo_viaje . '<br> <br>' .
                    'Operador: ' . $appointment->Operador->nombre . ' - ' . $appointment->Operador->telefono . '<br>' .
                    'Camion: ' . ' #' . $appointment->Camion->id_equipo . ' - ' . $appointment->Camion->num_serie . ' - ' . $appointment->Camion->modelo . '<br>' .
                    'Chasis: ' . $appointment->Chasis->num_serie . ' - ' . $appointment->Chasis->modelo . '<br>';
                }elseif($appointment->Contenedor->Cotizacion->tipo_viaje == 'Full'){
                    $description = 'Tipo viaje: ' . $appointment->Contenedor->Cotizacion->tipo_viaje . '<br> <br>' .
                    'Operador: ' . $appointment->Operador->nombre . ' - ' . $appointment->Operador->telefono . '<br>' .
                    'Camion: ' . ' #' . $appointment->Camion->id_equipo . ' - ' . $appointment->Camion->num_serie . ' - ' . $appointment->Camion->modelo . '<br>' .
                    'Chasis: ' . $appointment->Chasis->num_serie . ' - ' . $appointment->Chasis->modelo . '<br>' .
                    'Chasis 2: ' . $appointment->Chasis2->num_serie . ' - ' . $appointment->Chasis2->modelo . '<br>' .
                    'Doly: ' . $appointment->Doly->num_serie . ' - ' . $appointment->Doly->modelo . '<br>';
                }
                $tipo = 'P';
            }

            $coordenadas = Coordenadas::where('id_asignacion', '=', $appointment->id)->first();

            $description = str_replace('<br>', "\n", $description);

            $isOperadorNull = $appointment->id_operador === NULL;

            $event = [
                'title' => $tipo .' / '. $appointment->Contenedor->Cotizacion->Cliente->nombre . ' / #' . $appointment->Contenedor->Cotizacion->DocCotizacion->num_contenedor,
                'description' => $description,
                'start' => $appointment->fecha_inicio,
                'end' => $appointment->fecha_fin,
                'urlId' => $appointment->id,
                'idCotizacion' => $appointment->Contenedor->id_cotizacion,
                'isOperadorNull' => $isOperadorNull,
                'nombreOperadorSub' => $appointment->nombreOperadorSub ?? '',
                'telefonoOperadorSub' => $appointment->telefonoOperadorSub ?? '',
                'placasOperadorSub' => $appointment->placasOperadorSub ?? '',
            ];


            // Verificar si $coordenadas no es null antes de acceder a su propiedad id
            if ($coordenadas !== null) {
                $event['idCoordenda'] = $appointment->id;
            }

            if ($appointment->Operador !== null) {
                $event['telOperador'] = $appointment->Operador->telefono;
            }
            // else{
            //     $event['telOperador'] = '5585314498';
            // }

            $events[] = $event;

        }

        $planeaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
        ->where('asignaciones.fecha_inicio', '!=', NULL)->where('asignaciones.id_empresa' ,'=',auth()->user()->id_empresa)
        ->select('asignaciones.*', 'docum_cotizacion.num_contenedor')
        ->get();

        $cotizaciones_faltantes = Cotizaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('estatus', '=', 'Aprobada')->where('estatus_planeacion', '=', NULL);

        if ($request->contenedor_faltantes !== null) {
            $cotizaciones_faltantes = $cotizaciones_faltantes->where('id', $request->contenedor_faltantes);
        }

        $cotizaciones_faltantes = $cotizaciones_faltantes->first();

        return view('planeacion.index', compact('equipos', 'operadores', 'events',  'cotizaciones', 'proveedores', 'numCotizaciones', 'cotizaciones_faltantes', 'planeaciones'));
    }

}
