<?php

namespace App\Http\Controllers;

use App\Models\BancoDinero;
use App\Models\Bancos;
use App\Models\Client;
use App\Models\Cotizaciones;
use App\Models\Estado_Cuenta;
use App\Models\Estado_Cuenta_Cotizaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Exists;
use PhpParser\Node\Stmt\TryCatch;
use App\Services\BancosService;
use App\Services\CuentasCobrarService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\ThrottlesLogins;

class CuentasCobrarController extends Controller
{
    protected $BancosService;
    protected $CuentasCobrarService;

    public function __construct(BancosService $BancosService, CuentasCobrarService $CuentasCobrarService)
    {
        $this->BancosService = $BancosService;
        $this->CuentasCobrarService = $CuentasCobrarService;
    }

    public function index()
    {
        /*   $cotizacionesPorCliente = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')

          ->where('cotizaciones.estatus_pago', '=', '0')
          ->where('jerarquia', 'Principal')
          ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
          ->where('cotizaciones.restante', '>', 0)
          ->where(function ($query) {
              $query->where('cotizaciones.estatus', '=', 'Aprobada')
                    ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
          })
          ->select(
              'cotizaciones.id_cliente',
              DB::raw('COUNT(*) as total_cotizaciones'),
              DB::raw('SUM(cotizaciones.restante) as total_restante') // Sumar la columna restante
          )
          ->groupBy('cotizaciones.id_cliente')
          ->get(); */

        $cotizacionesPorCliente = $this->CuentasCobrarService->obtenerporCliente();

        //  dd($cotizacionesPorCliente);
        return view('cuentas_cobrar.index', compact('cotizacionesPorCliente'));
    }

    public function cobranza_v2($id)
    {
        /*  $cotizacionold = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
         ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
         ->where('cotizaciones.estatus_pago', '=', '0')
         ->where('jerarquia', 'Principal')
        // ->where('cotizaciones.restante', '>', 0)
         ->where(function ($query) {
             $query->where('cotizaciones.estatus', '=', 'Aprobada')
                   ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
         })
         ->where('cotizaciones.id_cliente', '=', $id)
         ->select(
             'cotizaciones.id_cliente',
             DB::raw('COUNT(*) as total_cotizaciones'),
             DB::raw('SUM(cotizaciones.restante) as total_restante') // Sumar la columna restante
         )
         ->groupBy('cotizaciones.id_cliente') // Agrupa por el ID de cotización
         ->havingRaw('SUM(cotizaciones.total) - SUM((SELECT COALESCE(SUM(cpc.monto),0)
                   FROM cobros_pagos_cotizaciones cpc
                   JOIN cobros_pagos cp
                     ON cp.id = cpc.cobro_pago_id
                   WHERE cpc.cotizacion_id = cotizaciones.id
                     AND cp.tipo = "cxc")) > 0')
         ->first();
 */


        $cotizacion = $this->CuentasCobrarService->obtenerporCliente($id)->first();

        //dd($cotizacion);

        $cliente = Client::where('id', '=', $id)->first();

        $bancos2 = Bancos::where('id_empresa', '=', auth()->user()->id_empresa)->get();
        $fecha = Carbon::now()->format('Y-m-d');
        $bancos = $this->BancosService->getCuentasOption(auth()->user()->id_empresa, $fecha, $fecha, false);

        return view('cuentas_cobrar.cobranza_cliente', compact('bancos', 'cliente', 'cotizacion'));
    }

    public function viajes_por_liquidar(Request $request)
    {
        /* old  $querybase = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
         ->leftjoin('subclientes', 'subclientes.id', '=', 'cotizaciones.id_subcliente')
         ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
         ->where('cotizaciones.estatus_pago', '=', '0')
         ->where('jerarquia', 'Principal')
         ->where('cotizaciones.restante', '>', 0)
         ->where(function ($query) {
             $query->where('cotizaciones.estatus', '=', 'Aprobada')
        ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
         }) ->where('cotizaciones.id_cliente', '=', $request->client)
         ->select(
             'cotizaciones.*',
             'subclientes.nombre as nombre_subcliente',
             'subclientes.telefono as telefono_subcliente',
             'docum_cotizacion.num_contenedor',
             DB::raw(
                 ' ( SELECT COALESCE(SUM(cpc.monto),0)
         FROM cobros_pagos_cotizaciones cpc JOIN cobros_pagos cp
         ON cp.id = cpc.cobro_pago_id
         WHERE cpc.cotizacion_id = cotizaciones.id
         AND cp.tipo = "cxc"
         ) as total_pagado_cxc'
             )
         );
         $cotizacionesPorPagar = DB::query() ->fromSub($querybase, 't') ->whereRaw('(t.total - t.total_pagado_cxc) > 0') ->get(); */


        $cotizacionesPorPagar = $this->CuentasCobrarService->obtenerporClienteId($request->client, true); // true para resolver fulles;

        //dd($cotizacionesPorPagar);
        $handsOnTableData = $cotizacionesPorPagar->map(function ($item) {



            return [
                $item->num_contenedor,
                 $item->nombre_subcliente,
              $item->tipo . ' ( ' . $item->tipo_viaje . ' )',
                ($item->estatus == 'Aprobada') ? "En Curso" : $item->estatus,

                $item->total_restante,
                $item->total_restante,

                0,
                0,
                0,

                $item->id
            ];
        });

        return response()->json(["success" => true,"handsOnTableData" => $handsOnTableData, "cotizacionesPorPagar" => $cotizacionesPorPagar]);
    }

    public function aplicar_pagos(Request $request)
    {
        try {

            DB::beginTransaction();

            $resultado = $this->CuentasCobrarService->procesarCobro($request);


            $this->BancosService->guardarBancoLegacy($request, $resultado['contenedoresAbonos']); //old


            $this->BancosService->procesarIngresosDesdeCobro(
                $request,
                $resultado,
                $resultado['cobroPago']->id
            );

            DB::commit();

            return response()->json(["success" => true, "Titulo" => "Cobro exitoso", "Mensaje" => "Hemos aplicado el cobro de los elementos indicados", "TMensaje" => "success"]);

        } catch (\Throwable $t) {
            DB::rollback();

            return response()->json([
                "success" => false,
                "Mensaje" => $t->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        $cliente = Client::where('id', '=', $id)->first();
        $cotizacionesPorPagar = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
        ->where('cotizaciones.estatus_pago', '=', '0') // revisar
        ->where('cotizaciones.restante', '>', 0)
        ->where(function ($query) {
            $query->where('cotizaciones.estatus', '=', 'Aprobada')
                  ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
        })
        ->where('cotizaciones.id_cliente', '=', $id)
        ->select('cotizaciones.*')
        ->get();

        $cotizacion = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
        ->where('cotizaciones.estatus_pago', '=', '0')
        ->where('cotizaciones.restante', '>', 0)
        ->where(function ($query) {
            $query->where('cotizaciones.estatus', '=', 'Aprobada')
                  ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
        })
        ->where('cotizaciones.id_cliente', '=', $id)
        ->select(
            'cotizaciones.id_cliente',
            DB::raw('COUNT(*) as total_cotizaciones'),
            DB::raw('SUM(cotizaciones.restante) as total_restante') // Sumar la columna restante
        )
        ->groupBy('cotizaciones.id_cliente') // Agrupa por el ID de cotización
        ->first();

        $bancos2 = Bancos::where('id_empresa', '=', auth()->user()->id_empresa)->get();
        $fecha = Carbon::now()->format('Y-m-d');
        $bancos = $this->BancosService->getCuentasOption(auth()->user()->id_empresa, $fecha, $fecha, false);
        return view('cuentas_cobrar.show', compact('cotizacionesPorPagar', 'bancos', 'cliente', 'cotizacion'));
    }

    public function update(Request $request, $id)
    {
        $cotizacion = Cotizaciones::where('id', '=', $id)->first();
        $cotizacion->monto1 = $request->get('monto1');
        $cotizacion->metodo_pago1 = $request->get('metodo_pago1');
        $cotizacion->id_banco1 = $request->get('id_banco1');
        if ($request->hasFile("comprobante1")) {
            $file = $request->file('comprobante1');
            $path = public_path() . '/pagos';
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $cotizacion->comprobante_pago1 = $fileName;
        }

        $cotizacion->monto2 = $request->get('monto2');
        $cotizacion->metodo_pago2 = $request->get('metodo_pago2');
        $cotizacion->id_banco2 = $request->get('id_banco2');
        if ($request->hasFile("comprobante2")) {
            $file = $request->file('comprobante2');
            $path = public_path() . '/pagos';
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $cotizacion->comprobante_pago2 = $fileName;
        }

        $suma = $request->get('monto1') + $request->get('monto2');
        $resta = $cotizacion->restante - $suma;
        $cotizacion->restante = $resta;
        $cotizacion->estatus_pago = 1;
        $cotizacion->fecha_pago = date('Y-m-d');
        $cotizacion->update();

        return redirect()->back()->with('success', 'Comprobante de pago exitosamente');
    }

    public function update_varios(Request $request)
    {
        $cotizacionesData = $request->get('id_cotizacion');
        $abonos = $request->get('abono');
        $remainingTotal = $request->get('remaining_total');

        // Array para almacenar contenedor y abono
        $contenedoresAbonos = [];

        foreach ($cotizacionesData as $id) {
            $cotizacion = Cotizaciones::where('id', '=', $id)->first();

            // Establecer el abono y calcular el restante
            $abono = isset($abonos[$id]) ? floatval($abonos[$id]) : 0;
            $nuevoRestante = $cotizacion->restante - $abono;

            if ($nuevoRestante < 0) {
                $nuevoRestante = 0;
            }

            $cotizacion->restante = $nuevoRestante;
            $cotizacion->estatus_pago = ($nuevoRestante == 0) ? 1 : 0;
            $cotizacion->fecha_pago = date('Y-m-d');
            $cotizacion->update();

            // Agregar contenedor y abono al array
            $contenedoresAbonos[] = [
                'num_contenedor' => $cotizacion->DocCotizacion->num_contenedor,
                'abono' => $abono
            ];
        }

        // Convertir el array de contenedores y abonos a JSON
        $contenedoresAbonosJson = json_encode($contenedoresAbonos);

        $banco = new BancoDinero();
        $banco->contenedores = $contenedoresAbonosJson;
        $banco->id_cliente = $request->get('id_cliente');
        $banco->monto1 = $request->get('monto1_varios');
        $banco->metodo_pago1 = $request->get('metodo_pago1_varios');
        $banco->id_banco1 = $request->get('id_banco1_varios');
        if ($request->hasFile("comprobante1_varios")) {
            $file = $request->file('comprobante1_varios');
            $path = public_path() . '/pagos';
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $banco->comprobante_pago1 = $fileName;
        }

        $banco->monto2 = $request->get('monto2_varios');
        $banco->metodo_pago2 = $request->get('metodo_pago2_varios');
        $banco->id_banco2 = $request->get('id_banco2_varios');
        if ($request->hasFile("comprobante2_varios")) {
            $file = $request->file('comprobante2_varios');
            $path = public_path() . '/pagos';
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $banco->comprobante_pago2 = $fileName;
        }

        $banco->fecha_pago = date('Y-m-d');
        $banco->tipo = 'Entrada';

        $banco->save();

        return redirect()->back()->with('success', 'Cobro exitoso');
    }





    public function storeEdocuenta(Request $request)
    {

        $request->validate([
    'numero' => 'required|string|max:50',
    'cotizacionesId' => 'required|array|min:1',
]);

        try {

            DB::beginTransaction();

            $numeroEdoCuenta = trim($request->numero);
            $idsSeleccionados = $request->cotizacionesId;


            $estadoCuentaNuevo = Estado_Cuenta::firstOrCreate(
                [
        'numero'     => $numeroEdoCuenta,
        'id_empresa' => auth()->user()->id_empresa,
    ],
                [
        'created_by' => auth()->id(),
    ]
            );

            if ($request->modo === 'editar') {

                $estadoCuentaActualId = $request->edo_cuenta_actual_id;
                $soloEsta = $request->boolean("solo_esta");

                if ($soloEsta) {

                    Estado_Cuenta_Cotizaciones::whereIn('cotizacion_id', $idsSeleccionados)
                        ->update([
                            'estado_cuenta_id' => $estadoCuentaNuevo->id,
                            'assigned_by' => auth()->id(),
                            'updated_at' => now()
                        ]);
                } else {

                    Estado_Cuenta_Cotizaciones::where('estado_cuenta_id', $estadoCuentaActualId)
                        ->update([
                            'estado_cuenta_id' => $estadoCuentaNuevo->id,
                            'assigned_by' => auth()->id(),
                            'updated_at' => now()
                        ]);
                }

            } else {

                //  dd($estadoCuentaNuevo);

                foreach ($idsSeleccionados as $idcot) {
                    Estado_Cuenta_Cotizaciones::updateOrCreate(
                        ['cotizacion_id' => $idcot],
                        [
                            'estado_cuenta_id' => $estadoCuentaNuevo->id,
                            'assigned_by' => auth()->id(),
                           'id_empresa' =>  auth()->user()->id_empresa
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Estado de cuenta asignado correctamente'
            ]);

        } catch (\Throwable $th) {

            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo asignar el estado de cuenta',
                'error' => $th->getMessage()
            ], 422);
        }

    }
}
