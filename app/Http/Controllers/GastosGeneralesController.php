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
       $fechaInicial = Carbon::parse($r->from)->format('Y-m-d');
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
                     "FechaContabilizado" => $g->fecha,
                     "Estatus" => true,
                     "Diferido" => ($g->diferir_gasto == 1) ? 'Diferido' : ''
                   ];
        });

        return $gastosInformacion;

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
            $gasto_general->pago_realizado = ($request->get('tipoPago') == 1) ? 0 : 1;
            
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
            $bancos = Bancos::where('id_empresa',auth()->user()->id_empresa)->get();
            return response()->json([
                                        "Titulo" => "Gasto registrado",
                                        "Mensaje" => "Se registró el gasto en la cuenta seleccionada",
                                        "TMensaje" => "success",
                                        "Bancos" => $bancos
                                    ]);
            
        }catch(\Throwable $t){
            DB::rollback();
            \Log::info($t);
            return response()->json(["Titulo" => "Gasto no aplicado","Mensaje" => "Ha ocurrido un error, no se puede aplicar el gasto", "TMensaje" => "danger"]);

        }

    }

    public function aplicarGastos(Request $r){
        try{

            DB::beginTransaction();
            
            $gastosGenerales = GastosGenerales::where('id',$r->_IdGasto)->first();

            switch($r->formasAplicar){
                case  "Viaje":
                    $listaViajes = $r->viajes;
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
                            "pago_inmediato" => 0,
                            "created_at" => Carbon::now()
                        ];
    
                        GastosOperadores::insert($datosGasto);
                    }
    
                    $aplicacion = ["aplicacion" => "viajes", "elementos" => $contenedores];
                    
                break;
                case "Equipo":
                    $listaEquipos = $r->unidades;
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

           /* $montoGasto = $gastosGenerales->first()->monto1;
            $montoDiario = $montoGasto / $r->diasContados;
           // $cantEquipos = sizeof($r->unidades);
            $montoDiario = $montoDiario / $cantEquipos;

            $gastosGenerales->update([
                "diferir_gasto" => 1,
                "diferir_contador_periodos" => $r->diasContados,
                "fecha_diferido_inicial" => $r->fechaDesde,
                "fecha_diferido_final" => $r->fechaHasta
            ]);

            $fechaDesde = Carbon::parse($r->fechaDesde);
            $fechaHasta = Carbon::parse($r->fechaHasta);
            $fechaIniciaPeriodo = $fechaDesde->toDateString();
            
            for($periodo = 1; $periodo <= $r->diasContados; $periodo++){
                $finalMes = Carbon::parse($fechaIniciaPeriodo);
                $finalMes = $finalMes->endOfMonth();
                $fechaFinPeriodo = ($finalMes > $fechaHasta) ? $fechaHasta->toDateString() : $finalMes->toDateString();
                $fechaIni = $fechaIniciaPeriodo;

                $date = [
                    "id_gasto" => $r->_IdGasto,
                    "fecha_gasto_inicial" => $fechaIni,
                    "fecha_gasto_final" => $fechaFinPeriodo,
                    "gasto_dia" => $montoDiario
                ];
                $Daily[] = $date;

                $fechaIniciaPeriodo = $finalMes->addDay()->toDateString();
            }

            GastosDiferidosDetalle::insert($Daily);*/

            /*foreach($r->unidades as $u){
                $fechaDesde = Carbon::parse($r->fechaDesde);
                $fechaHasta = Carbon::parse($r->fechaHasta);
    
                $Daily = [];
                while($fechaDesde < $fechaHasta){
                    $newDate = $fechaDesde->addDay();
                    $fechaDesde = $newDate;
                    $date = [
                                "id_gasto" => $r->_IdGasto,
                                "id_equipo" => $u,
                                "fecha_gasto" => $newDate->format('Y-m-d'),
                                "gasto_dia" => $montoDiario
                            ];
                    $Daily[] = $date;
                }*/
    
             //   
            //}

            return response()->json([
                                    "Titulo" => "Exito!", 
                                    "Mensaje" => "Se ha diferido el gasto exitosamente", 
                                    "TMensaje" => "success",
                           
                                    
                            ]);
        }catch(\Throwable $t){

            DB::rollback();
            \Log::channel('daily')->info("Error: GastosGeneralesController->diferir->".$t->getMessage());
            return response()->json(["Titulo" => "Gasto no aplicado","Mensaje" => "Ha ocurrido un error, no se puede aplicar el gasto", "TMensaje" => "error"]);

        }
        
    }
}
