<?php

namespace App\Http\Controllers;

use App\Models\BancoDinero;
use App\Models\Bancos;
use App\Models\Client;
use App\Models\Cotizaciones;
use App\Models\Estado_Cuenta;
use App\Models\Estado_Cuenta_Cotizaciones;
use App\Models\CobroPago;
use App\Models\CobroPagoCotizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Exists;
use PhpParser\Node\Stmt\TryCatch;
use App\Services\BancosService;
use Carbon\Carbon;

class CuentasCobrarController extends Controller
{
    protected $BancosService;

    public function __construct(BancosService $BancosService)
    {
        $this->BancosService = $BancosService;
    }

    public function index()
    {
        $cotizacionesPorCliente = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
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
        ->get();

        return view('cuentas_cobrar.index', compact('cotizacionesPorCliente'));
    }

    public function cobranza_v2($id)
    {
        $cotizacion = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
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

        $cliente = Client::where('id', '=', $id)->first();

        $bancos2 = Bancos::where('id_empresa', '=', auth()->user()->id_empresa)->get();
        $fecha = Carbon::now()->format('Y-m-d');
        $bancos = $this->BancosService->getCuentasOption(auth()->user()->id_empresa, $fecha, $fecha, false);

        return view('cuentas_cobrar.cobranza_cliente', compact('bancos', 'cliente', 'cotizacion'));
    }

    public function viajes_por_liquidar(Request $request)
    {
        $querybase = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
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
        $cotizacionesPorPagar = DB::query() ->fromSub($querybase, 't') ->whereRaw('(t.total - t.total_pagado_cxc) > 0') ->get();



        $handsOnTableData = $cotizacionesPorPagar->map(function ($item) {
            $subCliente = ($item->id_subcliente != null) ? $item->nombre_subcliente." / ".$item->telefono_subcliente : "";
            $tipoViaje = ($item->tipo_viaje == null || $item->tipo_viaje == 'Seleccionar Opcion') ? "Subcontratado" : $item->tipo_viaje;

            $numContenedor = $item->num_contenedor;

            if (!is_null($item->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $item->referencia_full)
                        ->where('jerarquia', 'Secundario')
                        ->with('DocCotizacion.Asignaciones')
                        ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $numContenedor .= ' / ' . $secundaria->DocCotizacion->num_contenedor;
                }
            }
            $restante = (float) $item->total - (float)$item->total_pagado_cxc;
            return [
                $numContenedor,
                $subCliente,
                $tipoViaje,
                ($item->estatus == 'Aprobada') ? "En Curso" : $item->estatus,
                  $restante,
                 $restante,
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
            //Primero validaremos que los pagos/abonos de cada contenedor no sea mayor al saldo pendiente
            $cotizaciones = json_decode($request->datahotTable);
            foreach ($cotizaciones as $c) {
                if ($c[8] > $c[4]) {
                    return response()->json([
                                              "success" => false,
                                              "Titulo" => "Error en el contenedor $c[0]",
                                              "Mensaje" => "No se puede aplicar el pago para el contenedor porque existe un error monto del pago ó es mayor al Saldo Original",
                                              "TMensaje" => "warning"
                                          ]);
                }
            }

            DB::beginTransaction();
            $contenedoresAbonos = [];
            $contenedoresAbonos1 = [];
            $contenedoresAbonos2 = [];
            $ids = [];

            //guardar cxc en tabla principal para despues guardar el detalle de contenendores

            $cobroPago = CobroPago::create([
        'tipo' => 'cxc',
        'cliente_id' => $request->theClient,

        'bancoA_id' => $request->bankOne,
        'monto_A' => $request->amountPayOne ?? 0,
        'fechaAplicacion1' => $request->FechaAplicacionbank1
            ? \Carbon\Carbon::createFromFormat('d/m/Y', $request->FechaAplicacionbank1)->format('Y-m-d')
            : null,

        'bancoB_id' => $request->bankTwo,
        'monto_B' => $request->amountPayTwo ?? 0,
        'fechaAplicacion2' => $request->FechaAplicacionbank2
            ? \Carbon\Carbon::createFromFormat('d/m/Y', $request->FechaAplicacionbank2)->format('Y-m-d')
            : null,

        'user_id' => auth()->id(),
        'observaciones' => null,
    ]);


            //

            foreach ($cotizaciones as $c) {
                if ($c[8] > 0) { //Si total cobrado es mayor a cero
                    $id = $c[9]; //Obtenemos el ID
                    $cotizacion = Cotizaciones::where('id', '=', $id)->first();

                    // Establecer el abono y calcular el restante
                    $abono = $c[8];
                    $nuevoRestante = $cotizacion->restante - $abono;

                    if ($nuevoRestante < 0) {
                        $nuevoRestante = 0;
                    }

                    $cotizacion->restante = $nuevoRestante;
                    $cotizacion->estatus_pago = ($nuevoRestante == 0) ? 1 : 0;
                    $cotizacion->fecha_pago = date('Y-m-d');
                    $cotizacion->update();

                    // Agregar contenedor y abono al array
                    $contenedorAbono = [
                        'num_contenedor' => $c[0],
                        'abono' => $c[8]
                    ];

                    $contenedorAbono1 = [
                        'num_contenedor' => $c[0],
                        'abono' => $c[6]
                    ];

                    $contenedorAbono2 = [
                     'num_contenedor' => $c[0],
                     'abono' => $c[7]
                    ];

                    array_push($contenedoresAbonos, $contenedorAbono);
                    array_push($contenedoresAbonos1, $contenedorAbono1);
                    array_push($contenedoresAbonos2, $contenedorAbono2);



                    if ($c[6] > 0) { //validamos si el pago 1 trae cantidad
                        CobroPagoCotizacion::create([
                                        'cobro_pago_id' => $cobroPago->id,
                                        'cotizacion_id' => $id ,
                                        'origen' => 'A',
                                        'monto' => $c[6],
                                    ]);

                    }

                    if ($c[7] > 0) { //validamos si el pago 2 trae cantidad
                        CobroPagoCotizacion::create([
                                      'cobro_pago_id' => $cobroPago->id,
                                      'cotizacion_id' =>  $id ,
                                      'origen' => 'B',
                                      'monto' => $c[7],
                                  ]);

                    }
                }

            }

            $banco = new BancoDinero();
            $banco->contenedores = json_encode($contenedoresAbonos);
            $banco->id_cliente = $request->theClient;
            $banco->monto1 = $request->amountPayOne;
            $banco->metodo_pago1 = "Transferencia"; //Metodo de pago por default, instrucciones JH
            $banco->id_banco1 = $request->bankOne;

            $banco->monto2 = $request->amountPayTwo;
            $banco->metodo_pago2 = "Transferencia"; //Metodo de pago por default, instrucciones JH
            $banco->id_banco2 = $request->bankTwo;

            $banco->fecha_pago = date('Y-m-d');
            $banco->tipo = 'Entrada';

            $banco->save();

            Bancos::where('id', '=', $request->bankOne)->update(["saldo" => DB::raw("COALESCE(saldo, 0) + ". $request->amountPayOne)]);
            Bancos::where('id', '=', $request->bankTwo)->update(["saldo" => DB::raw("COALESCE(saldo, 0) + ". $request->amountPayTwo)]);


            //proceso bancos nuevo
            if ($request->filled('bankOne') && $request->amountPayOne > 0) {
                $FechaAplicacionbank1 = $request->get('FechaAplicacionbank1');

                $cliente = client::find($request->theClient);




                $data = [
                        'cuenta_bancaria_id' => $request->get('bankOne'),            'tipo' => 'abono',
                        'monto' => floatval($request->amountPayOne),
                        'concepto' => 'Cobro viajes: '.'
                                '.  $cliente?->nombre ??  '',
                        'fecha_movimiento' => \Carbon\Carbon::createFromFormat(
                            'd/m/Y',
                            $FechaAplicacionbank1
                        )->format('Y-m-d'),
                        'origen' => null,
                        'referencia' => 'CXC A',
                        'detalles' => json_encode($contenedoresAbonos1),
                         'referenciaable_id' =>   $cobroPago->id,
                          'referenciaable_type' => \App\Models\CobroPago::class, //para polimorfismo
                    ];




                $movimeintoCrear = $this->BancosService->registrarMovimiento($data);
                //   dd('no pasar', $movimeintoCrear);

                if (!$movimeintoCrear) {
                    throw new \Exception('No se pudo crear el movimiento bancario, dinero para viaje adicional ');
                }


            }
            //proceso bancos nuevo
            if ($request->filled('bankTwo') && $request->amountPayTwo > 0) {
                $FechaAplicacionbank2 = $request->get('FechaAplicacionbank2');

                $cliente = client::find($request->theClient);

                $data = [
                        'cuenta_bancaria_id' => $request->get('bankTwo'),            'tipo' => 'abono',
                        'monto' => floatval($request->amountPayTwo),
                        'concepto' =>  'Cobro viajes: '.'
                                '.  $cliente?->nombre ??  '',
                        'fecha_movimiento' => \Carbon\Carbon::createFromFormat(
                            'd/m/Y',
                            $FechaAplicacionbank2
                        )->format('Y-m-d'),
                        'origen' => null,
                        'referencia' => 'CXC B',
                        'detalles' => json_encode($contenedoresAbonos2),
                         'referenciaable_id' => $cobroPago->id,
                          'referenciaable_type' => \App\Models\CobroPago::class, //para polimorfismo
                    ];




                $movimeintoCrear = $this->BancosService->registrarMovimiento($data);
                //   dd('no pasar', $movimeintoCrear);

                if (!$movimeintoCrear) {
                    throw new \Exception('No se pudo crear el movimiento bancario, dinero para viaje adicional ');
                }


            }



            DB::commit();
            return response()->json(["success" => true, "Titulo" => "Cobro exitoso", "Mensaje" => "Hemos aplicado el cobro de los elementos indicados", "TMensaje" => "success"]);
        } catch (\Throwable $t) {
            DB::rollback();

            return response()->json(["success" => false,"dataRequest" => $request->all(), "Titulo" => "Error", "Mensaje" => "No pudimos aplicar el cobro, existe un error: ".$t->getMessage(), "TMensaje" => "error"]);
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
