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
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\BancosService;
use App\Services\GastosService;
use Illuminate\Support\Facades\Log;

class GastosContenedoresController extends Controller
{
    protected $BancosService;
    protected $GastosService;

    public function __construct(BancosService $BancosService, GastosService $GastosService)
    {
        $this->BancosService = $BancosService;
        $this->GastosService = $GastosService;
    }


    public function IndexPayment()
    {
        $bancos2 = Bancos::where('id_empresa', Auth::User()->id_empresa)->get();
        $fecha = Carbon::now()->format('Y-m-d');
        $bancos = $this->BancosService->getCuentasOption(auth()->user()->id_empresa, $fecha, $fecha, true);
        return view('gastos_contenedor.index', ["bancos" => $bancos]);
    }

    public function getGxp()
    {
        //Gastos por pagar desde la tabla unificada de gastos
        $gastosOperadores = \App\Models\Gasto::with([
            'vinculos.vinculable',
            'categoria'
        ])
        ->where('id_empresa', auth()->user()->id_empresa)
        ->whereIn('tipo_gasto', ['operador', 'viaje'])
        ->where('estatus', '!=', 'Pagado')
        ->get()
        ->map(function ($g) {
            $vinculoAsignacion = $g->vinculos->firstWhere('tipo_vinculo', 'asignacion');
            $asignacion = $vinculoAsignacion ? $vinculoAsignacion->vinculable : null;

            $contenedor = $asignacion ? optional($asignacion->Contenedor)->num_contenedor : '';
            $contenedorB = $asignacion ? self::getContenedorSecundario(optional(optional($asignacion->Contenedor)->Cotizacion)->referencia_full) : '';

            return [
                'IdGasto'       => $g->id,
                'Descripcion'   => $g->concepto,
                'NumContenedor' => $contenedor . ($contenedorB ?? ''),
                'Monto'         => $g->monto_total ?? 0,
                'FechaGasto'    => Carbon::parse($g->fecha_gasto)->format('Y-m-d'),
                'FechaPago'     => null,
                'fecha_inicio'  => optional($asignacion)->fecha_inicio,
                'fecha_fin'     => optional($asignacion)->fecha_fin,
                'Origen'        => 'Operador'
            ];
        });

        /*
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
        */
        $gastosGenerales = DB::table('gasto_imputaciones')
            ->join('gastos', 'gastos.id', '=', 'gasto_imputaciones.gasto_id')
            ->leftJoin('categorias_gastos', 'categorias_gastos.id', '=', 'gastos.categoria_gasto_id')
            ->leftJoin('gasto_programaciones', 'gasto_programaciones.gasto_imputacion_id', '=', 'gasto_imputaciones.id')
            ->whereNull('gastos.deleted_at')
            ->where('gastos.estatus', '!=', 'cancelado')
            ->where('gastos.estatus', '!=', 'pagado')
            ->where('gastos.id_empresa', auth()->user()->id_empresa)
            ->whereIn('gasto_imputaciones.tipo_imputacion', ['periodo', 'empresa'])
            ->select(
                'gasto_imputaciones.id as IdGasto',
                'gastos.concepto as NumContenedor',
                'categorias_gastos.categoria as Descripcion',
                'gasto_imputaciones.monto_imputado as Monto',
                DB::raw("DATE(gasto_imputaciones.fecha_imputacion) as FechaGasto"),
                'gasto_imputaciones.fecha_imputacion as FechaPago',
                DB::raw('gasto_programaciones.fecha_programada as fecha_inicio'),
                DB::raw('gasto_programaciones.fecha_vencimiento as fecha_fin'),
                DB::raw("'General' as Origen")
            )
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

            $idEmpresa = auth()->user()->id_empresa;
            $fecha_aplicacion = $r->get('fecha_aplicacion', Carbon::now()->format('Y-m-d'));

            if ($r->filled('bank') && $r->totalPago > 0) {
                $validarSaldos = $this->BancosService->validarsaldoparacargo($idEmpresa, $r->get('bank'), $fecha_aplicacion, $r->totalPago);
                if ($validarSaldos["saldodisponible"] == false) {
                    return response()->json([
                        "TMensaje" => "error",
                        "Titulo" => "Saldo bancario insuficiente",
                        "Mensaje" => $validarSaldos["message"],
                        'success' => false
                    ]);
                }
            }

            $pagando = $r->gastosPagar;

            foreach ($pagando as $item) {
                if (($item['Origen'] ?? null) === 'Operador') {
                    $gasto = \App\Models\Gasto::find($item['IdGasto']);
                    if ($gasto) {
                        $contenedorVinculo = $gasto->vinculos()->where('tipo_vinculo', 'contenedor')->first();
                        $numContenedor = $contenedorVinculo ? $contenedorVinculo->observaciones : null;

                        $operadorVinculo = $gasto->vinculos()->where('tipo_vinculo', 'operador')->first();
                        $operadorNombre = $operadorVinculo ? str_replace('Operador: ', '', $operadorVinculo->observaciones) : null;

                        $this->GastosService->pagar($gasto, [
                            'cuenta_bancaria_id' => $r->bank,
                            'monto' => $item['Monto'],
                            'fecha_pago' => $fecha_aplicacion,
                            'concepto_banco' => \App\Services\BancosService::generarConcepto('gasto', $gasto->concepto, $numContenedor, $operadorNombre),
                            'referencia_banco' => 'GASTO_CONTENEDOR'
                        ]);
                    }
                } elseif (($item['Origen'] ?? null) === 'General') {
                    GastosGenerales::where('id', $item['IdGasto'])->update([
                        "pago_realizado" => 1,
                        "id_banco2" => $r->bank,
                        "fecha_operacion" => $fecha_aplicacion,
                        "is_active" => 0
                    ]);

                    // Registrar movimiento de bancos para gasto general
                    $this->BancosService->registrarMovimiento([
                        'cuenta_bancaria_id' => $r->bank,
                        'tipo' => 'cargo',
                        'monto' => floatval($item['Monto']),
                        'concepto' => 'Pago Gasto General: ' . ($item['NumContenedor'] ?? 'S/N'),
                        'fecha_movimiento' => $fecha_aplicacion,
                        'referencia' => 'GASTO_GENERAL',
                        'referenciaable_id' => $item['IdGasto'],
                        'referenciaable_type' => \App\Models\GastosGenerales::class,
                    ]);

                    $gastoGeneral = GastosGenerales::find($item['IdGasto']);
                    if ($gastoGeneral) {
                        $this->sincronizarGastoGeneralNew($gastoGeneral);
                    }
                }
            }

            DB::commit();
            return response()->json([
                "Titulo" => "Pago aplicado",
                "Mensaje" => "Se aplicó el pago correctamente. Movimiento registrado en el historial bancario",
                "TMensaje" => "success"
            ]);
        } catch (\Throwable $t) {
            DB::rollback();
            $idError = uniqid();
            Log::channel('daily')->info("$idError : ".$t->getMessage());
            return response()->json([
                "Titulo" => "Ha ocurrido un error",
                "Mensaje" => "Ocurrio un error mientras procesabamos su solicitud. Cod Error $idError: " . $t->getMessage(),
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

    public function indexGastosViaje()
    {

        $bancos2 = Bancos::where('id_empresa', auth()->user()->id_empresa)->get();
        $fecha = Carbon::now()->format('Y-m-d');
        $bancos = $this->BancosService->getCuentasOption(auth()->user()->id_empresa, $fecha, $fecha, true);

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
        /*
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
        */
        $gastosUnidadQuery = "SELECT
        a.id_camion,
        g.concepto as motivo,
        COUNT(DISTINCT a.id) AS total_asignaciones,
        COALESCE(SUM(gi.monto_imputado), 0) AS total_gastos_periodo,
        COALESCE(SUM(gi.monto_imputado), 0) / COUNT(DISTINCT a.id) AS gasto_por_viaje
        FROM asignaciones a
        LEFT JOIN gasto_imputaciones gi
            ON gi.tipo_imputacion = 'unidad'
            AND gi.imputable_id = a.id_camion
            AND gi.fecha_imputacion BETWEEN '$fechaInicio' AND '$fechaFin'
        LEFT JOIN gastos g
            ON g.id = gi.gasto_id
            AND g.deleted_at IS NULL
            AND g.estatus != 'cancelado'
        WHERE a.fecha_inicio BETWEEN '$fechaInicio' AND '$fechaFin'
        AND a.id_camion IS NOT NULL
        GROUP BY a.id_camion, g.concepto;";

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

        /* $handsOnTableData = $cotizaciones->map(function ($cotizacion) use ($gastosUnidad) {
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
 */

        $handsOnTableData = $cotizaciones->map(function ($cotizacion) use ($gastosUnidad) {

            $doc = $cotizacion->DocCotizacion;
            $asignacion = $doc?->Asignaciones;

            $contenedor = $doc?->num_contenedor ?? 'N/A';

            if (!is_null($cotizacion->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $cotizacion->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion')
                    ->first();

                if ($secundaria?->DocCotizacion) {
                    $contenedor .= ' ' . $secundaria->DocCotizacion->num_contenedor;
                }
            }

            /*
            $gastos = GastosOperadores::where('id_cotizacion', $cotizacion->id)->get();
            */
            $gastos = \App\Models\GastoImputacion::join('gastos', 'gastos.id', '=', 'gasto_imputaciones.gasto_id')
                ->join('gasto_vinculos', 'gasto_vinculos.gasto_id', '=', 'gastos.id')
                ->whereNull('gastos.deleted_at')
                ->where('gastos.estatus', '!=', 'cancelado')
                ->where('gasto_vinculos.tipo_vinculo', '=', 'cotizacion')
                ->where('gasto_vinculos.vinculable_type', '=', \App\Models\Cotizaciones::class)
                ->where('gasto_vinculos.vinculable_id', '=', $cotizacion->id)
                ->where('gasto_imputaciones.tipo_imputacion', '=', 'operador')
                ->select(
                    'gasto_imputaciones.*',
                    'gasto_imputaciones.monto_imputado as cantidad',
                    'gastos.concepto as tipo',
                    DB::raw("CASE WHEN gastos.estatus = 'pagado' THEN 'Pagado' ELSE gastos.estatus END as estatus")
                )
                ->get();

            $comision = $gastos->first(fn ($g) => str_contains($g->tipo, 'GCM01'));
            $diesel   = $gastos->first(fn ($g) => str_contains($g->tipo, 'GDI02'));
            $casetas  = $gastos->first(fn ($g) => str_contains($g->tipo, 'GCP03'));

            $gastosDiferidos = $gastosUnidad
                ->where('id_camion', $asignacion?->id_camion)
                ->sum('gasto_por_viaje');

            return [
                "contenedor"       => $contenedor,
                "comision"         => $gastos->where('tipo', 'like', '%GCM01%')->sum('cantidad'),
                "diesel"           => $gastos->where('tipo', 'like', '%GDI02%')->sum('cantidad'),
                "casetas"          => $gastos->where('tipo', 'like', '%GCP03%')->sum('cantidad'),
                "varios"           => $gastos->reject(
                    fn ($g) =>
                                            str_contains($g->tipo, 'GCM01') ||
                                            str_contains($g->tipo, 'GDI02') ||
                                            str_contains($g->tipo, 'GCP03')
                )->sum('cantidad'),
                "diferidos"        => $gastosDiferidos,

                "pago_comision"    => $comision?->estatus === 'Pagado' ? 1 : 0,
                "pago_diesel"      => $diesel?->estatus === 'Pagado' ? 1 : 0,
                "pago_casetas"     => $casetas?->estatus === 'Pagado' ? 1 : 0,

                "banco"            => null,
                "fecha_aplicacion" => null,

                "id_cotizacion"    => $cotizacion->id,
            ];
        });


        return response()->json(['handsOnTableData' => $handsOnTableData]);
    }

    public function confirmarGastos(Request $r)
    {
        try {
            $gastosContenedores = $r->datahandsOnTableGastos;
            //  $gastosContenedores = json_decode($r->datahandsOnTableGastos);
            /*  DB::beginTransaction();
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

                         /*
                         $gastoViaje = GastosOperadores::where('id_cotizacion', $contenedor->id_cotizacion)->where('tipo', $descripcionGastos[$e]);
                         */
                      /*    $gastoViaje = \App\Models\GastoImputacion::join('gastos', 'gastos.id', '=', 'gasto_imputaciones.gasto_id')
                             ->join('gasto_vinculos', 'gasto_vinculos.gasto_id', '=', 'gastos.id')
                             ->whereNull('gastos.deleted_at')
                             ->where('gastos.estatus', '!=', 'cancelado')
                             ->where('gasto_vinculos.tipo_vinculo', '=', 'cotizacion')
                             ->where('gasto_vinculos.vinculable_type', '=', \App\Models\Cotizaciones::class)
                             ->where('gasto_vinculos.vinculable_id', '=', $contenedor->id_cotizacion)
                             ->where('gasto_imputaciones.tipo_imputacion', '=', 'operador')
                             ->where('gastos.concepto', '=', $descripcionGastos[$e])
                             ->select('gasto_imputaciones.*', 'gasto_imputaciones.monto_imputado as cantidad', 'gastos.concepto as tipo', 'gastos.estatus as estatus');
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

             } */

             //Validemos que el banco tenga saldo suficiente para pagar el total de los gastos segun la seleccion del usuario
          /*    $gastosBancos = collect($nuevosGastos)
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
             BancoDinero::insert($bancoDinero); */

             //Descontar la suma de los bancos correspondientes
/*
             foreach ($gastosBancos as $idBanco => $totalGasto) {
                 Bancos::where('id', '=', $idBanco)->update(
                     ["saldo" => DB::raw("saldo - ". $totalGasto)
                 ] */
              //   );
            // }

            // DB::commit(); */

            DB::beginTransaction();

            $nuevosGastos = [];
            $dataBancoNuevo = [];
            $datosGasto   = [];
            $bancoDinero  = [];
            $contenedoresAbonos = [];

            $idEmpresa = auth()->user()->id_empresa;

            foreach ($gastosContenedores as $gasto) {

                $numContenedor = $gasto['contenedor'];
                $bank          = isset($gasto['banco']) ? trim(substr($gasto['banco'], 0, 5)) : null;
                $fechaPago     = $gasto['fecha_aplicacion']
                                    ? Carbon::parse($gasto['fecha_aplicacion'])
                                    : null;






                $contenedor = DocumCotizacion::where('num_contenedor', $numContenedor)
                                ->where('id_empresa', $idEmpresa)
                                ->first();

                if (!$contenedor) {
                    continue;
                }

                $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();

                $tipos = [
                    'comision' => [
                        'codigo' => 'GCM01 - Comisión',
                        'monto'  => $gasto['comision'],
                        'pago'   => $gasto['pago_comision']
                    ],
                    'diesel' => [
                        'codigo' => 'GDI02 - Diesel',
                        'monto'  => $gasto['diesel'],
                        'pago'   => $gasto['pago_diesel']
                    ],
                    'casetas' => [
                        'codigo' => 'GCP03 - Casetas / Peaje',
                        'monto'  => $gasto['casetas'],
                        'pago'   => $gasto['pago_casetas']
                    ],
                ];

                foreach ($tipos as $tipo) {

                    if ($tipo['monto'] <= 0) {
                        continue;
                    }

                    if ($tipo['pago'] && empty($bank)) {
                        DB::rollBack();
                        return response()->json([
                            "Titulo"  => "Debe seleccionar banco",
                            "Mensaje" => "Ha seleccionado pago inmediato pero no indicó banco.",
                            "TMensaje" => "warning"
                        ]);
                    }

                    /*
                    $existeGasto = GastosOperadores::where('id_cotizacion', $contenedor->id_cotizacion)
                                        ->where('tipo', $tipo['codigo'])
                                        ->first();
                    */
                    $existeGasto = \App\Models\GastoImputacion::join('gastos', 'gastos.id', '=', 'gasto_imputaciones.gasto_id')
                        ->join('gasto_vinculos', 'gasto_vinculos.gasto_id', '=', 'gastos.id')
                        ->whereNull('gastos.deleted_at')
                        ->where('gastos.estatus', '!=', 'cancelado')
                        ->where('gasto_vinculos.tipo_vinculo', '=', 'cotizacion')
                        ->where('gasto_vinculos.vinculable_type', '=', \App\Models\Cotizaciones::class)
                        ->where('gasto_vinculos.vinculable_id', '=', $contenedor->id_cotizacion)
                        ->where('gasto_imputaciones.tipo_imputacion', '=', 'operador')
                        ->where('gastos.concepto', '=', $tipo['codigo'])
                        ->select('gasto_imputaciones.*', 'gasto_imputaciones.monto_imputado as cantidad', 'gastos.concepto as tipo', 'gastos.estatus as estatus')
                        ->first();

                    if (!$existeGasto) {

                        $nuevosGastos[] = [
                            "banco" => intval($bank),
                            "gasto" => $tipo['monto'],
                            "fechaAplicacion" =>  $fechaPago
                        ];

                        $datosGasto = [
                            "id_cotizacion" => $contenedor->id_cotizacion,
                            "id_banco"      => $tipo['pago'] ? intval($bank) : null,
                            "id_asignacion" => $asignacion?->id,
                            "id_operador"   => $asignacion?->id_operador,
                            "cantidad"      => $tipo['monto'],
                            "tipo"          => $tipo['codigo'],
                            "estatus"       => $tipo['pago'] ? 'Pagado' : 'Pago Pendiente',
                            "fecha_pago"    => $tipo['pago'] ? $fechaPago : null,
                            "pago_inmediato" => $tipo['pago'],
                            "created_at"    => now(),

                        ];

                        if ($tipo['pago']) {



                            if ($bank &&  $tipo['monto'] > 0) {
                                $fechaPago = $fechaPago;

                                $validarSaldos = $this->BancosService->validarsaldoparacargo($idEmpresa, intval($bank), $fechaPago, $tipo['monto']);
                                //  dd($validarSaldos);
                                if ($validarSaldos["saldodisponible"] == false) {
                                    DB::rollBack();
                                    return response()->json([
                                                      "TMensaje" => "error",
                                                   "Titulo" => "Saldo bancos 2",
                                                      "Mensaje" => $validarSaldos["message"],
                                                      'success' => false
                                                     ]);

                                }
                            }

                            $contenedoresAbonos[] = [
                                'num_contenedor' => $numContenedor,
                                'tipo' => $tipo['codigo'],
                                'abono' => $tipo['monto']
                            ];

                            $bancoDinero[] = [
                                "monto1"        => $tipo['monto'],
                                "metodo_pago1"  => 'Transferencia',
                                "descripcion"   => $tipo['codigo']." ".$numContenedor,
                                "id_banco1"     => $bank,
                                "contenedores"  => json_encode($contenedoresAbonos),
                                "tipo"          => 'Salida',
                                "fecha_pago"    => $fechaPago?->format('Y-m-d'),
                            ];

                            $gasto = GastosOperadores::create($datosGasto);
                            $this->sincronizarGastoOperadorNew($gasto);

                            $dataBancoNuevo = [
                                'cuenta_bancaria_id' =>  intval($bank),
                                'tipo' => 'cargo',
                                'monto' => floatval($tipo['monto']),
                                'concepto' =>   $tipo['codigo']." ".$numContenedor,
                                'fecha_movimiento' =>  $fechaPago?->format('Y-m-d'),
                                'origen' => null,
                                'referencia' => 'GOP',
                                'detalles' => json_encode($contenedoresAbonos),
                                   'referenciaable_id' => $gasto->id,
                                'referenciaable_type' => \App\Models\GastosOperadores::class, //para polimorfismo
                            ];

                            // dd($dataBancoNuevo);

                            $movimeintoCrear   = $this->BancosService->registrarMovimiento($dataBancoNuevo);

                            if (!$movimeintoCrear) {
                                throw new \Exception('No se pudo crear el movimiento bancario, dinero para viaje adicional ');
                            }
                        }

                    } else {

                        if ($existeGasto->cantidad != $tipo['monto']) {
                            $existeGasto->update([
                                "cantidad" => $tipo['monto']
                            ]);
                        }

                        $this->sincronizarGastoOperadorNew($existeGasto->fresh());
                    }
                }
            }


            // $bancos = Bancos::where('id_empresa', $idEmpresa)
            //     ->whereIn('id', $gastosBancos->keys())
            //     ->get()
            //     ->keyBy('id');



            // foreach ($gastosBancos as $idBanco => $totalGasto) {





            //     if ($banco && $banco->saldo < $totalGasto) {

            //         DB::rollBack();

            //         return response()->json([
            //             "Titulo"  => "Saldo insuficiente en Banco",
            //             "Mensaje" => "La cuenta bancaria seleccionada no cuenta con saldo suficiente.",
            //             "TMensaje" => "warning"
            //         ]);
            //     }
            // }

            $gastosBancos = collect($nuevosGastos)
            ->groupBy('banco')
            ->map(fn ($items) => $items->sum('gasto'));


            if (!empty($bancoDinero)) {
                BancoDinero::insert($bancoDinero);

            }



            foreach ($gastosBancos as $idBanco => $totalGasto) {
                Bancos::where('id', $idBanco)
                    ->update([
                        "saldo" => DB::raw("saldo - {$totalGasto}")
                    ]);
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
                "TMensaje" => "warning",
                "eerr admin"  => $t->getMessage()
            ]);
        }


    }

    private function sincronizarGastoOperadorNew(GastosOperadores $gasto): void
    {
        try {
            $this->GastosService->registrarDesdeGastoOperador($gasto->fresh());
        } catch (\Throwable $t) {
            Log::channel('daily')->warning('No se pudo sincronizar gastos_operadores con gastos new desde gastos contenedor', [
                'gasto_operador_id' => $gasto->id,
                'error' => $t->getMessage(),
            ]);
        }
    }

    private function sincronizarGastoGeneralNew(GastosGenerales $gasto): void
    {
        try {
            $this->GastosService->registrarDesdeGastoGeneral($gasto->fresh());
        } catch (\Throwable $t) {
            Log::channel('daily')->warning('No se pudo sincronizar gastos_generales con gastos new desde gastos por pagar', [
                'gasto_general_id' => $gasto->id,
                'error' => $t->getMessage(),
            ]);
        }
    }
}
