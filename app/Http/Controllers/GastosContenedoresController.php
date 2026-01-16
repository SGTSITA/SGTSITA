<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asignaciones;
use App\Models\Bancos;
use App\Models\BancoDinero;
use App\Models\CategoriasGastos;
use App\Models\Cotizaciones;
use App\Models\DocumCotizacion;
use App\Models\Equipo;
use App\Models\GastosOperadores;
use App\Models\GastosGenerales;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\GastosPorPagarExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class GastosContenedoresController extends Controller
{
    public function IndexPayment()
    {
        $bancos = Bancos::where('id_empresa', Auth::User()->id_empresa)->get();
        return view('gastos_contenedor.index', ["bancos" => $bancos]);
    }

    public function getGxp()
    {
        //Gastos por pagar
        $gastosOperadores = GastosOperadores::with([
          'Asignaciones.Proveedor',
          'Asignaciones.Contenedor.Cotizacion.Cliente',
          'Asignaciones.Contenedor.Cotizacion.Subcliente'
    ])
    ->whereHas('Asignaciones', fn ($q) => $q->where('id_empresa', auth()->user()->id_empresa))
    ->where('estatus', '!=', 'Pagado')
    ->get()
    ->map(function ($g) {
        $asignacion = $g->Asignaciones;
        $contenedor = optional($asignacion->Contenedor)->num_contenedor;
        $contenedorB = self::getContenedorSecundario(optional(optional($asignacion->Contenedor)->Cotizacion)->referencia_full);

        return [
            'IdGasto'       => $g->id,
            'Descripcion'   => $g->tipo,
            'NumContenedor' => $contenedor . ($contenedorB ?? ''),
            'Monto'         => $g->cantidad ?? 0,
            'FechaGasto'    => Carbon::parse($g->created_at)->format('Y-m-d'),
            'FechaPago'     => $g->fecha_pago,
            'fecha_inicio'  => optional($asignacion)->fecha_inicio,
            'fecha_fin'     => optional($asignacion)->fecha_fin,
                        'Origen'        => 'Operador'
        ];
    });

        $gastosGenerales = DB::table('gastos_generales')
        ->join('categorias_gastos', 'categorias_gastos.id', '=', 'gastos_generales.id_categoria')
        ->select(
            'gastos_generales.id as IdGasto',
            'motivo as  NumContenedor',
            'categorias_gastos.categoria as Descripcion',
            'monto1 as Monto',
            DB::raw("DATE(gastos_generales.fecha) as FechaGasto"),
            'fecha as FechaPago',
            DB::raw('fecha_diferido_inicial as fecha_inicio'),
            DB::raw('fecha_diferido_final as fecha_fin'),
            DB::raw("'General' as Origen")
        )
        ->where('gastos_generales.is_active', 1)
        ->where('id_empresa', auth()->user()->id_empresa)
        ->where('pago_realizado', 0)
        //->whereNotNull('gasto_origen_id')
        ->get();


        $data = $gastosOperadores->merge($gastosGenerales);

        return response()->json(["TMensaje" => "success", "contenedores" => $data]);
    }

    public static function getContenedorSecundario($referencia_full)
    {
        if (!is_null($referencia_full)) {
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

    public function PagarGastosMultiple(Request $r)
    {
        try {

            DB::beginTransaction();
            $bancos = Bancos::where('id_empresa', Auth::User()->id_empresa)->where('id', $r->bank)->first();
            $saldoActual = $bancos->saldo;

            if ($saldoActual < $r->totalPago) {
                return response()->json(["Titulo" => "Saldo insuficiente en Banco","Mensaje" => "La cuenta bancaria seleccionada no cuenta con saldo suficiente para registrar esta transacción","TMensaje" => "warning"]);
            }

            Bancos::where('id', '=', $r->bank)->update(["saldo" => DB::raw("saldo - ". $r->totalPago)]);

            $idEmpresa = auth()->user()->id_empresa;
            $pagando = $r->gastosPagar;

            foreach ($pagando as $c) {
                $contenedoresAbonos[] = [
                    'num_contenedor' => $c['NumContenedor'],
                    'abono' => $c['Monto']
                ];
            }

            /* $contenedor = DocumCotizacion::where('num_contenedor', $c->numContenedor)
             ->where('id_empresa', $idEmpresa)
             ->first();

             $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();*/

            $banco = new BancoDinero();
            //$banco->id_operador = $asignacion->id_operador;

            $banco->monto1 = $r->totalPago;
            $banco->metodo_pago1 = 'Transferencia';
            $banco->descripcion = "Pago Gastos Contenedor";
            $banco->id_banco1 = $r->bank;

            $Gastos = Collect($r->gastosPagar);
            $IdGastos = $Gastos->pluck('IdGasto');
            $contenedoresAbonosJson = json_encode($contenedoresAbonos);
            GastosOperadores::whereIn('id', $IdGastos)->update(["estatus" => "Pagado","id_banco" => $r->bank, "fecha_pago" => Carbon::now()->format('Y-m-d')]);
            //gastos generales
            $gastogral =  GastosGenerales::whereIn('id', $IdGastos)->update(["pago_realizado" => 1,"id_banco2" => $r->bank, "fecha_operacion" => Carbon::now()->format('Y-m-d'),"is_active" => 0]);
            if ($gastogral) {
                $banco->descripcion = "Pago Gastos Generales";
            }

            $banco->contenedores = $contenedoresAbonosJson;

            $banco->tipo = 'Salida';
            $banco->fecha_pago = date('Y-m-d');
            $banco->save();

            DB::commit();
            return response()->json(["Titulo" => "Pago aplicado",
                                     "Mensaje" => "Se aplicó el pago correctamente. Movimiento registrado en el historial bancario",
                                     "TMensaje" => "success"
                                    ]);
        } catch (\Throwable $t) {
            DB::rollback();
            $idError = uniqid();
            Log::channel('daily')->info("$idError : ".$t->getMessage());
            return response()->json([
                "Titulo" => "Ha ocurrido un error",
                "Mensaje" => "Ocurrio un error mientras procesabamos su solicitud. Cod Error $idError",
                "TMensaje" => "error" . $t->getMessage()
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

    public function indexGastosViaje()
    {

        $bancos = Bancos::where('id_empresa', auth()->user()->id_empresa)->get();
        $categorias = CategoriasGastos::orderBy('categoria')->get();
        $empresa = Auth::User()->id_empresa;
        $equipos = Equipo::where('id_empresa', $empresa)->get();

        return view('gastos_contenedor.index-gastos-v2', compact('bancos', 'categorias', 'equipos'));
    }

    public function gastosViajesList(Request $r)
    {
        $fechaInicio = $r->from;
        $fechaFin = $r->to;

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
            ->whereIn('estatus', ['Finalizado', 'Aprobada'])
            ->where('estatus_planeacion', '=', 1)
            ->where('jerarquia', "!=", 'Secundario')
            ->whereHas('DocCotizacion.Asignaciones', function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin]);
            })
            ->with(['cliente', 'DocCotizacion.Asignaciones' => function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin]);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        $handsOnTableData = $cotizaciones->map(function ($cotizacion) use ($gastosUnidad) {
            $contenedor = $cotizacion->DocCotizacion ? $cotizacion->DocCotizacion->num_contenedor : 'N/A';
            $gastosDiferidos = (sizeof($gastosUnidad) > 0) ? $gastosUnidad->where('id_camion', $cotizacion->DocCotizacion->Asignaciones->id_camion)->sum('gasto_por_viaje') : 0;
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

            $Gastos = GastosOperadores::where('id_cotizacion', $cotizacion->id)->get();

            return [
                $contenedor,
                $Gastos->filter(function ($gasto) {return str_contains($gasto->tipo, 'GCM01');})->sum('cantidad'),
                $Gastos->filter(function ($gasto) {return str_contains($gasto->tipo, 'GDI02');})->sum('cantidad'),
                $Gastos->filter(function ($gasto) {return str_contains($gasto->tipo, 'GCP03');})->sum('cantidad'),
                $Gastos->filter(function ($gasto) {return !str_contains($gasto->tipo, 'GCM01') && !str_contains($gasto->tipo, 'GDI02') && !str_contains($gasto->tipo, 'GCP03');})->sum('cantidad'),
                $gastosDiferidos,
                ($Gastos->filter(function ($gasto) {return str_contains($gasto->tipo, 'GCM01');})->first()?->estatus == 'Pagado') ? 1 : 0,
                ($Gastos->filter(function ($gasto) {return str_contains($gasto->tipo, 'GDI02');})->first()?->estatus == 'Pagado') ? 1 : 0,
                ($Gastos->filter(function ($gasto) {return str_contains($gasto->tipo, 'GCP03');})->first()?->estatus == 'Pagado') ? 1 : 0,
                '',
                $cotizacion->id,
            ];
        });



        return response()->json(['handsOnTableData' => $handsOnTableData]);
    }

    public function confirmarGastos(Request $r)
    {
        try {
            $gastosContenedores = json_decode($r->datahandsOnTableGastos);
            DB::beginTransaction();
            $nuevosGastos = [];
            $datosGasto = [];
            $bancoDinero = [];
            foreach ($gastosContenedores as $gasto) {
                $numContenedor = $gasto[0];
                $camposGastos = [1,2,3];
                $camposGastoInmediato = [6,7,8];
                $descripcionGastos = ['GCM01 - Comisión','GDI02 - Diesel','GCP03 - Casetas / Peaje'];
                $idEmpresa = auth()->user()->id_empresa;

                $contenedor = DocumCotizacion::where('num_contenedor', $numContenedor)
                                                   ->where('id_empresa', $idEmpresa)
                                                   ->first();

                $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();

                for ($e = 0; $e <= 2; $e++) {
                    if ($gasto[$camposGastos[$e]] > 0) {

                        $esPagoInmediato = $gasto[$camposGastoInmediato[$e]];
                        $bank = trim(substr($gasto[9], 0, 5));

                        $gastoViaje = GastosOperadores::where('id_cotizacion', $contenedor->id_cotizacion)->where('tipo', $descripcionGastos[$e]);
                        $existeGasto = $gastoViaje->exists();

                        //Una vez superada la validacion proceder a guardar
                        if (!$existeGasto) {

                            if ($esPagoInmediato && empty($bank)) {
                                return response()->json([
                                    "Titulo" => "Debe seleccionar banco",
                                    "Mensaje" => "Ha seleccionado la opción de pago inmediato. Por favor, indique el banco desde el cual se realizará el retiro.",
                                    "TMensaje" => "warning"
                                ]);
                            }

                            $nuevosGastos[] = [
                                "banco" => intval($bank),
                                "gasto" => $gasto[$camposGastos[$e]]
                            ];

                            $datosGasto[] = [
                               "id_cotizacion" => $contenedor->id_cotizacion,
                               "id_banco" => $esPagoInmediato != 0 ? intval($bank) : null,
                               "id_asignacion" => $asignacion->id,
                               "id_operador" => $asignacion->id_operador,
                               "cantidad" => $gasto[$camposGastos[$e]],
                               "tipo" => $descripcionGastos[$e],
                               "estatus" => $esPagoInmediato != 0 ? 'Pagado' : 'Pago Pendiente',
                               "fecha_pago" => $esPagoInmediato != 0 ? Carbon::now() : null,
                               "pago_inmediato" => $esPagoInmediato,
                               "created_at" => Carbon::now()
                              ];

                            if ($esPagoInmediato) {

                                $contenedoresAbonos[] = [
                                    'num_contenedor' => $numContenedor,
                                    'abono' => $gasto[$camposGastos[$e]]
                                ];

                                $contenedoresAbonosJson = json_encode($contenedoresAbonos);
                                $bancoDinero[] = [
                                    "monto1" => $gasto[$camposGastos[$e]],
                                    "metodo_pago1" => 'Transferencia',
                                    "descripcion" => $descripcionGastos[$e]. " ".$numContenedor,
                                    "id_banco1" => $bank,
                                    "contenedores" => $contenedoresAbonosJson,
                                    "tipo" => 'Salida',
                                    "fecha_pago" =>  date('Y-m-d'),
                                ];

                            }

                        } else {
                            $detalleGasto = $gastoViaje->first();
                            if ($detalleGasto->cantidad != $gasto[$camposGastos[$e]]) {
                                $detalleGasto->update([
                                    "cantidad" => $gasto[$camposGastos[$e]]
                                ]);

                            }
                        }

                    }
                }

            }

            //Validemos que el banco tenga saldo suficiente para pagar el total de los gastos segun la seleccion del usuario
            $gastosBancos = collect($nuevosGastos)
            ->groupBy('banco')
            ->map(fn ($items) => $items->sum('gasto'));


            $idsBancos = $gastosBancos->keys(); //Obtener los id de banco de la suma anterior

            $bancos = Bancos::where('id_empresa', Auth::user()->id_empresa)
                            ->whereIn('id', $idsBancos)
                            ->get()
                            ->keyBy('id');

            foreach ($gastosBancos as $idBanco => $totalGasto) {
                $banco = $bancos->get($idBanco);

                if (!is_null($banco) && $banco->saldo < $totalGasto) {
                    return response()->json([
                        "Titulo" => "Saldo insuficiente en Banco",
                        "Mensaje" => "La cuenta bancaria seleccionada no cuenta con saldo suficiente para registrar esta transacción",
                        "TMensaje" => "warning"
                    ]);

                }
            }

            GastosOperadores::insert($datosGasto);
            BancoDinero::insert($bancoDinero);

            //Descontar la suma de los bancos correspondientes

            foreach ($gastosBancos as $idBanco => $totalGasto) {
                Bancos::where('id', '=', $idBanco)->update(
                    ["saldo" => DB::raw("saldo - ". $totalGasto)
                ]
                );
            }

            DB::commit();


            return response()->json(["Titulo" => "Gasto agregado","Mensaje" => "Se agregó el gasto","TMensaje" => "success"]);

        } catch (\Throwable $t) {
            DB::rollback();
            $idError = uniqid();
            Log::channel('daily')->info("$idError : ".$t->getMessage());
            return response()->json([
                "Titulo" => "Ha ocurrido un error",
                "Mensaje" => "Ocurrio un error mientras procesabamos su solicitud. Cod Error $idError",
                "TMensaje" => "warning"
            ]);
        }


    }
}
