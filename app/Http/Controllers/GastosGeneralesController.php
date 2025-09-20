<?php

namespace App\Http\Controllers;

use App\Models\Bancos;
use App\Models\Equipo;
use App\Models\GastosGenerales;
use App\Models\GastosOperadores;
use App\Models\CategoriasGastos;
use App\Models\GastosDiferidosDetalle;
use App\Models\Asignaciones;
use App\Models\Planeacion;
use App\Models\Cotizaciones;
use App\Models\DocumCotizacion;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;
use DB;

class GastosGeneralesController extends Controller
{
    public function index(){
        $bancos = Bancos::where('id_empresa',auth()->user()->id_empresa)->get();
        $categorias = CategoriasGastos::orderBy('categoria')->get();
        $now = Carbon::now()->format('d.m.Y');
        $initDay = Carbon::now()->subDays(15)->format('d.m.Y');

        $empresa = Auth::User()->id_empresa;
        $equipos = Equipo::where('id_empresa',$empresa)->get();

        $firstDay = Carbon::now()->startOfMonth(); // 1er día del mes actual
        $lastDay = Carbon::now()->endOfMonth();

        //Obtenemos los viajes "planeados" del periodo
        $planeaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
                        ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
                        ->where('asignaciones.fecha_inicio', '>=', $firstDay)
                        ->where('asignaciones.id_empresa' ,'=',auth()->user()->id_empresa)
                        ->where('cotizaciones.estatus', 'Aprobada')
                        ->where('estatus_planeacion','=', 1)
                        ->select('asignaciones.*', 'docum_cotizacion.num_contenedor','cotizaciones.id_cliente','cotizaciones.referencia_full','cotizaciones.tipo_viaje')
                        ->get();

        $viajes = $planeaciones->map(function ($p) {
            $itemNumContenedor = $p->num_contenedor;
        
            if ($p->tipo_viaje == "Full") {
                $cotizacionFull = Cotizaciones::where('referencia_full', $p->referencia_full)
                                                ->where('jerarquia', 'Secundario')
                                                ->first();
        
                if ($cotizacionFull) {
                    $contenedorSecundario = DocumCotizacion::where("id_cotizacion", $cotizacionFull->id)->first();
        
                    if ($contenedorSecundario) {
                        $itemNumContenedor .= " / " . $contenedorSecundario->num_contenedor;
                    }
                }
            }
        
            return (object)[
                'fecha_inicio' => $p->fecha_inicio,
                'fecha_fin' => $p->fecha_fin,
                'id_contenedor' => $p->id_contenedor,
                'id_cliente' => $p->id_cliente,
                'num_contenedor' => $itemNumContenedor,
            ];
        });
                        

        return view('gastos_generales.index', compact( 'bancos','categorias','now','initDay','equipos','viajes'));
    }

    public function getGastos(Request $r){
       $fechaInicial = Carbon::parse($r->from)->startOfMonth()->format('Y-m-d');
       $fechaFinal = Carbon::parse($r->to)->format('Y-m-d');

       $gastos = GastosGenerales::where('id_empresa' ,'=',auth()->user()->id_empresa)
                                ->where('is_active',1)
                                ->whereBetween('fecha',[$fechaInicial,$fechaFinal])
                                ->orderBy('created_at', 'desc')->get();
                              

        $gastosInformacion = $gastos->map(function($g){
            return [
                     "IdGasto" => $g->id,
                     "Descripcion" => $g->motivo,
                     "Monto" => $g->monto1,
                     "Categoria" => $g->categoria->categoria,
                     "CuentaOrigen" => $g->banco1->nombre_banco,
                     "FechaGasto" => $g->fecha,
                     "FechaContabilizado" => $g->fecha_operacion ? $g->fecha_operacion : $g->fecha,
                     "Estatus" => true,
                     "Diferido" => ($g->diferir_gasto == 1) ? 'Diferido' : '',
                     "GastoAplicado" => (!is_null($g->aplicacion_gasto)) ? true : false
                   ];
        });


         $fechaInicio = $fechaInicial;
        $fechaFin = $fechaFinal;

        //Obtener los gastos de las unidades (vehiculos)
        $gastosUnidadQuery = "SELECT 
        a.id_camion,
        gg.motivo,
        COUNT(DISTINCT a.id) AS total_asignaciones,
        COALESCE(SUM(DISTINCT gg.monto1 / JSON_LENGTH(JSON_EXTRACT(gg.aplicacion_gasto, '$.elementos'))), 0) AS total_gastos_periodo,
        COALESCE(SUM(DISTINCT gg.monto1 / JSON_LENGTH(JSON_EXTRACT(gg.aplicacion_gasto, '$.elementos'))), 0) / COUNT(DISTINCT a.id) AS gasto_por_viaje
        FROM asignaciones a
        LEFT JOIN gastos_generales gg 
            ON gg.aplicacion_gasto IS NOT NULL
            AND JSON_VALID(gg.aplicacion_gasto) = 1
            AND JSON_UNQUOTE(JSON_EXTRACT(gg.aplicacion_gasto, '$.aplicacion')) = 'equipos'
            AND JSON_CONTAINS(
                JSON_EXTRACT(gg.aplicacion_gasto, '$.elementos'),
                JSON_OBJECT('equipo', CAST(a.id_camion AS CHAR)),
                '$'
            )
            AND gg.fecha BETWEEN '$fechaInicio' AND '$fechaFin'
        WHERE a.fecha_inicio BETWEEN '$fechaInicio' AND '$fechaFin'
        AND a.id_camion IS NOT NULL AND gg.is_active = 1
        GROUP BY a.id_camion, gg.motivo;";

        $gastosUnidad = Collect(DB::select($gastosUnidadQuery));

        $cotizaciones = Cotizaciones::where('id_empresa', auth()->user()->id_empresa)
            ->whereIn('estatus',['Finalizado', 'Aprobada'])
            ->where('estatus_planeacion', '=', 1)
            ->where('jerarquia', "!=",'Secundario')
            ->whereHas('DocCotizacion.Asignaciones', function($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin]); 
            })
            ->with(['cliente', 'DocCotizacion.Asignaciones' => function($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin]);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

            $handsOnTableData = $cotizaciones->map(function ($cotizacion) use ($gastosUnidad){
                $contenedor = $cotizacion->DocCotizacion ? $cotizacion->DocCotizacion->num_contenedor : 'N/A';
                $gastosDiferidos = (sizeof($gastosUnidad)>0) ? $gastosUnidad->where('id_camion', $cotizacion->DocCotizacion->Asignaciones->id_camion)->sum('gasto_por_viaje') : 0;
                // Si es tipo 'Full', buscamos la secundaria para obtener su contenedor
                if (!is_null($cotizacion->referencia_full)) {
                    $secundaria = Cotizaciones::where('referencia_full', $cotizacion->referencia_full)
                        ->where('jerarquia', 'Secundario')
                        ->with('DocCotizacion.Asignaciones')
                        ->first();

                    if ($secundaria && $secundaria->DocCotizacion) {
                        $contenedor .= ' ' . $secundaria->DocCotizacion->num_contenedor;
                    }
                }

                $Gastos = GastosOperadores::where('id_cotizacion',$cotizacion->id)->get();

                return [
                    $contenedor,
                    $Gastos->filter(function($gasto) {return str_contains($gasto->tipo, 'GCM01');})->sum('cantidad'),
                    $Gastos->filter(function($gasto) {return str_contains($gasto->tipo, 'GDI02');})->sum('cantidad'),
                    $Gastos->filter(function($gasto) {return str_contains($gasto->tipo, 'GCP03');})->sum('cantidad'),
                    $Gastos->filter(function($gasto) {return !str_contains($gasto->tipo, 'GCM01') && !str_contains($gasto->tipo, 'GDI02') && !str_contains($gasto->tipo, 'GCP03');})->sum('cantidad'),
                    $gastosDiferidos,
                    ($Gastos->filter(function($gasto) {return str_contains($gasto->tipo, 'GCM01');})->first()?->estatus == 'Pagado') ? 1 : 0,
                    ($Gastos->filter(function($gasto) {return str_contains($gasto->tipo, 'GDI02');})->first()?->estatus == 'Pagado') ? 1 : 0,
                    ($Gastos->filter(function($gasto) {return str_contains($gasto->tipo, 'GCP03');})->first()?->estatus == 'Pagado') ? 1 : 0,
                    '',
                    $cotizacion->id,
                ];
            });

             return response()->json(['handsOnTableData' => $handsOnTableData,'gastosInformacion' => $gastosInformacion]);
   

    }

    public function store(Request $request, GastosGenerales $id)
    {
        try{
            DB::beginTransaction();
            $bancoAfectado = Bancos::where('id' ,'=',$request->get('id_banco1'));
            $saldoActualBanco = $bancoAfectado->first()->saldo;
            $montoGasto = $request->get('monto1');

            if($saldoActualBanco < $montoGasto){
                return response()->json(["Titulo" => "Gasto no aplicado","Mensaje" => "No se puede aplicar el gasto en la cuenta seleccionada ya que el saldo es insuficiente", "TMensaje" => "warning"]);
            }

            $fechaActual = date('Y-m-d');
            $gasto_general = new GastosGenerales;
            $gasto_general->motivo = $request->get('motivo');
            $gasto_general->monto1 = $montoGasto;
            $gasto_general->id_categoria = $request->get('categoria_movimiento');
            $gasto_general->metodo_pago1 = 'Transferencia';
            $gasto_general->id_banco1 = $request->get('id_banco1');
            $gasto_general->fecha = $fechaActual;
            $gasto_general->fecha_operacion = $request->fecha_movimiento;
            $gasto_general->id_empresa = auth()->user()->id_empresa;
            $gasto_general->is_active = ($request->get('tipoPago') == 1) ? 0 : 1;
            $gasto_general->diferir_gasto = $request->get('tipoPago');
            $gasto_general->pago_realizado = (intval($request->get('tipoPago')) == 0) ? 1 : 0;
            
            $gasto_general->save();

            if($request->get('tipoPago') == 1){                 
                $gasto_general->diferir_contador_periodos = $request->numPeriodos;
                $gasto_general->fecha_diferido_inicial = $request->txtDiferirFechaInicia;
                $gasto_general->fecha_diferido_final = $request->txtDiferirFechaTermina;

                $gasto_general->save();

                //Insertar un registro para cada periodo 
                $fechaDesde = Carbon::parse($request->txtDiferirFechaInicia);
                $fechaHasta = Carbon::parse($request->txtDiferirFechaTermina);
                $fechaIniciaPeriodo = $fechaDesde->toDateString();

                $montoPeriodo = $montoGasto / $request->numPeriodos;

                for($periodo = 1; $periodo <= $request->numPeriodos; $periodo++){
                    $finalMes = Carbon::parse($fechaIniciaPeriodo);
                    $finalMes = $finalMes->endOfMonth();
                    $fechaFinPeriodo = ($finalMes > $fechaHasta) ? $fechaHasta->toDateString() : $finalMes->toDateString();
                    $fechaIni = $fechaIniciaPeriodo;
    
                    $date = [
                        "motivo" => $request->get('motivo'). ": Parcialidad $periodo de $request->numPeriodos",
                        "monto1" => $montoPeriodo,
                        "id_categoria" => $request->get('categoria_movimiento'),
                        "metodo_pago1" => 'Transferencia',
                        "id_banco1" => $request->get('id_banco1'),
                        "fecha" => $fechaIni,
                        "fecha_operacion" => $request->fecha_movimiento,
                        "id_empresa" => auth()->user()->id_empresa,
                        "is_active" => 1,
                        "diferir_gasto" => $request->get('tipoPago'),
                        "fecha_diferido_inicial" => $fechaIni,
                        "fecha_diferido_final" => $fechaFinPeriodo,
                        "gasto_origen_id" => $gasto_general->id,
                        "pago_realizado" => 0,
                        "created_at" => date('Y-m-d H:i:s')
                        
                    ];

                    $Daily[] = $date;
    
                    $fechaIniciaPeriodo = $finalMes->addDay()->toDateString();
                }
    
                GastosGenerales::insert($Daily);
            }else{
                Bancos::where('id' ,'=',$request->get('id_banco1'))->update(["saldo" => DB::raw("saldo - ". $montoGasto)]);
            }
          
            DB::commit();

            $aplicarGasto = self::aplicarGastos($gasto_general->id, $request->formasAplicar, $request->viajes,$request->unidades);

            if($aplicarGasto["TMensaje"] == "success"){
                GastosGenerales::where('gasto_origen_id',$gasto_general->id)->update(["aplicacion_gasto" => $aplicarGasto["Aplicacion"]]);
            }
            $bancos = Bancos::where('id_empresa',auth()->user()->id_empresa)->get();
            
            return response()->json([
                                        "Titulo" => "Gasto registrado",
                                        "Mensaje" => ($aplicarGasto["TMensaje"] != "success") ? "Se ha registrado el gasto, pero no pudo ser aplicado a su selección" : "Se registró el gasto en la cuenta seleccionada y aplicado correctamente",
                                        "TMensaje" => ($aplicarGasto["TMensaje"] != "success") ? "info" : "success",
                                        "Bancos" => $bancos
                                    ]);
            
        }catch(\Throwable $t){
            DB::rollback();
            \Log::info($t);
            return response()->json(["Titulo" => "Gasto no aplicado","Mensaje" => "Ha ocurrido un error, no se puede aplicar el gasto", "TMensaje" => "danger"]);

        }

    }

    public function aplicarGastos($gastoId, $formaAplicacion, $viajes,$unidades){
        try{

            DB::beginTransaction();
            
            $gastosGenerales = GastosGenerales::where('id',$gastoId)->first();

            switch($formaAplicacion){
                case  "Viaje":
                    $listaViajes = $viajes;
                    $numViajes = sizeof($listaViajes);
                    $montoGasto = $gastosGenerales->monto1;
                    $montoViaje = $montoGasto / $numViajes;
    
                    //Generar Json
                    //{"aplicacion":"viajes","elementos":[{"contenedor": "abcd"},{"contenedor":"defg"}]}
                    $contenedores = [];
    
                    foreach($listaViajes as $lv){
                        $contenedor = DocumCotizacion::where('id_cotizacion', $lv)
                                                    ->first();
    
                        $contenedores[] = [
                            'num_contenedor' => $contenedor->num_contenedor,
                            'monto' => $montoViaje
                        ];
    
                        $asignacion = Asignaciones::where('id_contenedor', $lv)->first();
    
                        //Añadir la parte proporcional a cada viaje de la lista
                        $datosGasto = [
                            "id_cotizacion" => $contenedor->id_cotizacion,
                            "id_banco" =>  null,
                            "id_asignacion" => $asignacion->id,
                            "id_operador" => $asignacion->id_operador,
                            "cantidad" => $montoViaje,
                            "tipo" => $gastosGenerales->motivo,
                            "estatus" => 'Pago Pendiente',
                            "fecha_pago" => null,
                            "pago_inmediato" => $gastosGenerales->diferir_gasto  ,
                            "id_gasto_origen" => $gastoId,
                            "created_at" => Carbon::now()
                        ];
                        
                        GastosOperadores::insert($datosGasto);
                    }
    
                    $aplicacion = ["aplicacion" => "viajes", "elementos" => $contenedores];
                    
                break;
                case "Equipo":
                    $listaEquipos = $unidades;
                    $equipos = [];

                    foreach ($listaEquipos as $le){
                        $equipos[] = [
                            'equipo' => $le
                         
                        ];
                    }

                    $aplicacion = ["aplicacion" => "equipos", "elementos" => $equipos];

                    break;
                default:
                    $aplicacion = ["aplicacion" => "periodo", "elementos" => []];
                break;
            }
            
            $gastosGenerales->aplicacion_gasto =  json_encode($aplicacion);
            $gastosGenerales->save();
            
            DB::commit();

            return [
                "Titulo" => "Exito!", 
                "Mensaje" => "Se ha diferido el gasto exitosamente", 
                "TMensaje" => "success",
                "Aplicacion" => $aplicacion
                ];
            /*return response()->json([
                                    "Titulo" => "Exito!", 
                                    "Mensaje" => "Se ha diferido el gasto exitosamente", 
                                    "TMensaje" => "success",
                            ]);*/
        }catch(\Throwable $t){

            DB::rollback();
            \Log::channel('daily')->info("Error: GastosGeneralesController->diferir->".$t->getMessage());
            return ["Titulo" => "Gasto no aplicado","Mensaje" => "Ha ocurrido un error, no se puede aplicar el gasto", "TMensaje" => "error"];

        }
        
    }

    public function eliminarGasto(Request $r){
        try{
            DB::beginTransaction();
            $gastosGenerales = GastosGenerales::where('id',$r->IdGasto)->first();

            GastosOperadores::where('id_gasto_origen',$r->IdGasto)->delete();
            //Devolver el dinero al banco, siempre y cuando el estatus sea "Pagado"
            if($gastosGenerales->pago_realizado === 1){
                Bancos::where('id' ,'=',$gastosGenerales->id_banco1)->update(["saldo" => DB::raw("saldo + ". $gastosGenerales->monto1)]);

            }
            $gastosGenerales->delete();
            DB::commit();

            return response()->json([
                                    "Titulo" => "Exito!", 
                                    "Mensaje" => "Se ha eliminado el gasto exitosamente", 
                                    "TMensaje" => "success",                                    
                            ]);
        }catch(\Throwable $t){

            DB::rollback();
            \Log::channel('daily')->info("Error: GastosGeneralesController->eliminarGasto->".$t->getMessage());
            return response()->json(["Titulo" => "Gasto no aplicado","Mensaje" => "Ha ocurrido un error, no se puede aplicar el gasto", "TMensaje" => "error"]);

        }
    }
}
