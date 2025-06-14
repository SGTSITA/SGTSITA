<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GastosOperadores;
use App\Models\Cotizaciones;
use App\Models\Bancos;
use App\Models\BancoDinero;
use Auth;
use DB;
use Carbon\Carbon;
use App\Exports\GastosPorPagarExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;


class GastosContenedoresController extends Controller
{
    public function IndexPayment(){
        $bancos = Bancos::where('id_empresa',Auth::User()->id_empresa)->get();
        return view('gastos_contenedor.index',["bancos" => $bancos]);
    }

    public function getGxp(){
      //Gastos por pagar
      $gastos = GastosOperadores::with([
        'Asignaciones.Proveedor',
        'Asignaciones.Contenedor.Cotizacion.Cliente',
        'Asignaciones.Contenedor.Cotizacion.Subcliente'
    ])
    ->whereHas('Asignaciones', fn ($q) => $q->where('id_empresa', auth()->user()->id_empresa))
    ->where('estatus', '!=', 'Pagado');

    $data = $gastos->get()->map(function ($g) {
        $asignacion = $g->Asignaciones;
        $contenedor =  optional($asignacion->Contenedor)->num_contenedor;
        $contenedorB = self::getContenedorSecundario(optional($asignacion->Contenedor->Cotizacion)->referencia_full);
        return [
            'IdGasto' => $g->id,
            'Descripcion' => $g->tipo,
            'NumContenedor' => $contenedor.$contenedorB ?? '-',
            'Monto' => $g->cantidad ?? 0,
          
            'FechaGasto' => Carbon::parse($g->created_at)->format('Y-m-d'),
            'FechaPago' => $g->fecha_pago,
             'fecha_inicio' => optional($asignacion)->fecha_inicio,
'fecha_fin' => optional($asignacion)->fecha_fin,
        ];
    });

    return response()->json(["TMensaje" => "success", "contenedores" => $data]);
    }

    public static function getContenedorSecundario($referencia_full){
        if(!is_null($referencia_full)){
            $secundaria = Cotizaciones::where('referencia_full', $referencia_full)
            ->where('jerarquia', 'Secundario')
            ->with('DocCotizacion.Asignaciones')
            ->first();

            if ($secundaria && $secundaria->DocCotizacion) {
                $numContenedor = ' / ' . $secundaria->DocCotizacion->num_contenedor;
                return $numContenedor;
            }

        }
                    
    }

    public function PagarGastosMultiple(Request $r){
        try{

            DB::beginTransaction();
            $bancos = Bancos::where('id_empresa',Auth::User()->id_empresa)->where('id',$r->bank)->first();
            $saldoActual = $bancos->saldo;

            if($saldoActual < $r->totalPago){
                return response()->json(["Titulo" => "Saldo insuficiente en Banco","Mensaje" => "La cuenta bancaria seleccionada no cuenta con saldo suficiente para registrar esta transacción","TMensaje" => "warning"]);
            }

            Bancos::where('id' ,'=',$r->bank)->update(["saldo" => DB::raw("saldo - ". $r->totalPago)]);

            $idEmpresa = auth()->user()->id_empresa;
            $pagando = $r->gastosPagar;

            foreach( $pagando as $c){
                $contenedoresAbonos[] = [
                    'num_contenedor' => $c['NumContenedor'],
                    'abono' => $c['Monto']
                ];
            }

           /* $contenedor = DocumCotizacion::where('num_contenedor', $c->numContenedor)
            ->where('id_empresa', $idEmpresa)
            ->first();

            $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();*/

            $banco = new BancoDinero;
            //$banco->id_operador = $asignacion->id_operador;

            $banco->monto1 = $r->totalPago;
            $banco->metodo_pago1 = 'Transferencia';
            $banco->descripcion = "Pago Gastos Contenedor";
            $banco->id_banco1 = $r->bank;
               
            $Gastos = Collect($r->gastosPagar);
            $IdGastos = $Gastos->pluck('IdGasto');
            $contenedoresAbonosJson = json_encode($contenedoresAbonos);
            GastosOperadores::whereIn('id',$IdGastos)->update(["estatus" => "Pagado","id_banco" => $r->bank, "fecha_pago" => Carbon::now()->format('Y-m-d')]);

            $banco->contenedores = $contenedoresAbonosJson;

            $banco->tipo = 'Salida';
            $banco->fecha_pago = date('Y-m-d');
            $banco->save();

            DB::commit();
            return response()->json(["Titulo" => "Pago aplicado",
                                     "Mensaje" => "Se aplicó el pago correctamente. Movimiento registrado en el historial bancario",
                                     "TMensaje" => "success"
                                    ]);
        }catch(\Throwable $t){
            DB::rollback();
            $idError = uniqid();
            \Log::channel('daily')->info("$idError : ".$t->getMessage());
            return response()->json([
                "Titulo" => "Ha ocurrido un error",
                "Mensaje" => "Ocurrio un error mientras procesabamos su solicitud. Cod Error $idError",
                "TMensaje" => "error"
            ]);

        }
    }

    public function exportarSeleccionados(Request $request)
{
    $ids = $request->input('selected_ids', []);
    $tipo = $request->input('fileType');

    if (empty($ids)) {
        return response()->json(['error' => 'No hay registros seleccionados.'], 422);
    }

    $exportador = new GastosPorPagarExport($ids); // ✅ Ya recibe los IDs

    if ($tipo === 'xlsx') {
        return Excel::download($exportador, 'gastos_seleccionados.xlsx');
    }

    if ($tipo === 'pdf') {
        $pdf = PDF::loadView('reporteria.gxp.excel', [
            'gastos' => $exportador->getGastosData($ids), // Usa tu lógica existente
            'isExcel' => false
        ]);
        return $pdf->download('gastos_seleccionados.pdf');
    }

    return response()->json(['error' => 'Tipo no válido'], 400);
}
}
