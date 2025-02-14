<?php

namespace App\Http\Controllers;

use App\Models\Bancos;
use App\Models\GastosGenerales;
use App\Models\CategoriasGastos;
use App\Models\GastosDiferidosDetalle;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class GastosGeneralesController extends Controller
{
    public function index(){
        $bancos = Bancos::where('id_empresa',auth()->user()->id_empresa)->get();
        $categorias = CategoriasGastos::orderBy('categoria')->get();
        $now = Carbon::now()->format('d.m.Y');
        $initDay = Carbon::now()->subDays(15)->format('d.m.Y');

        return view('gastos_generales.index', compact( 'bancos','categorias','now','initDay'));
    }

    public function getGastos(Request $r){
       $fechaInicial = Carbon::parse($r->from)->format('Y-m-d');
       $fechaFinal = Carbon::parse($r->to)->format('Y-m-d');

       $gastos = GastosGenerales::where('id_empresa' ,'=',auth()->user()->id_empresa)
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
            $gasto_general->is_active = 1;
            $gasto_general->save();

            Bancos::where('id' ,'=',$request->get('id_banco1'))->update(["saldo" => DB::raw("saldo - ". $montoGasto)]);
          
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
            return response()->json(["Titulo" => "Gasto no aplicado","Mensaje" => "Ha ocurrido un error, no se puede aplicar el gasto", "TMensaje" => "danger"]);

        }

    }

    public function diferir(Request $r){
        try{
            DB::beginTransaction();
            DB::commit();
            $gastosGenerales = GastosGenerales::where('id',$r->_IdGasto);

        $montoGasto = $gastosGenerales->first()->monto1;
        $montoDiario = $montoGasto / $r->diasContados;

        $gastosGenerales->update([
            "diferir_gasto" => 1,
            "diferir_contador_periodos" => $r->diasContados,
            "fecha_diferido_inicial" => $r->fechaDesde,
            "fecha_diferido_final" => $r->fechaHasta
        ]);

        $fechaDesde = Carbon::parse($r->fechaDesde);
        $fechaHasta = Carbon::parse($r->fechaHasta);

        $Daily = [];
        while($fechaDesde < $fechaHasta){
            $newDate = $fechaDesde->addDay();
            $fechaDesde = $newDate;
            $date = ["id_gasto" => $r->_IdGasto,"fecha_gasto" => $newDate->format('Y-m-d'),"gasto_dia" => $montoDiario];
            $Daily[] = $date;
        }

        GastosDiferidosDetalle::insert($Daily);

        return response()->json([
                                "Titulo" => "Exito!", 
                                "Mensaje" => "Se ha diferido el gasto exitosamente", 
                                "TMensaje" => "success",
                                "Fechas" => $Daily
                           ]);
        }catch(\Throwable $t){

            DB::rollback();
            \Log::channel('daily')->info("Error: GastosGeneralesController->diferir->".$t->getMessage());
            return response()->json(["Titulo" => "Gasto no aplicado","Mensaje" => "Ha ocurrido un error, no se puede aplicar el gasto", "TMensaje" => "error"]);

        }
        
    }
}
