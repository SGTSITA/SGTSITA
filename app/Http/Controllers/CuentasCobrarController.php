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

            $numContenedor = $item->num_contenedor;
            $edoCuenta = $item->numero_edo_cuenta ?? 'NA';

            return [
                $numContenedor, // index 0
                $edoCuenta, // index 1 (new column)
                $item->nombre_subcliente, // index 2
                $item->tipo . ' ( ' . $item->tipo_viaje . ' )', // index 3
                ($item->estatus == 'Aprobada') ? "En Curso" : $item->estatus, // index 4

                $item->total_restante, // index 5
                $item->total_restante, // index 6

                0, // index 7
                0, // index 8
                0, // index 9

                $item->id // index 10
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
            $numeroEdoCuenta = trim($request->numero);
            $idsSeleccionados = array_filter(array_map('intval', (array) $request->cotizacionesId));
            $modo = $request->input('modo', 'crear');
            $idEmpresa = auth()->user()->id_empresa;
            $estadoCuentaActualId = $request->input('edo_cuenta_actual_id');
            $soloEsta = $request->boolean("solo_esta");
            $confirmarTransferencia = $request->boolean("confirmar_transferencia");

            if (empty($numeroEdoCuenta)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'El número de estado de cuenta no puede estar vacío.'
                ], 400);
            }

            if (empty($idsSeleccionados)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Debe seleccionar al menos una cotización.'
                ], 400);
            }

            // Verificar si ya existe un Estado de Cuenta con este número en la empresa actual
            $estadoCuentaExistente = Estado_Cuenta::where('numero', $numeroEdoCuenta)
                ->where('id_empresa', $idEmpresa)
                ->first();

            // 1. Si el modo es 'crear' y YA EXISTE, detener y advertir duplicidad para anexar
            if ($modo === 'crear' && $estadoCuentaExistente) {
                return response()->json([
                    'ok' => false,
                    'already_exists' => true,
                    'message' => 'El número de estado de cuenta "' . $numeroEdoCuenta . '" ya existe en el sistema.'
                ], 400);
            }

            // 2. Si el modo es 'anexar' y NO EXISTE, advertir que no se encontró para anexar
            if ($modo === 'anexar' && !$estadoCuentaExistente) {
                return response()->json([
                    'ok' => false,
                    'message' => 'El número de estado de cuenta "' . $numeroEdoCuenta . '" no existe en el sistema para anexar.'
                ], 400);
            }

            // 3. Si el modo es 'editar' y el número pertenece a OTRO estado de cuenta distinto
            if ($modo === 'editar' && !empty($estadoCuentaActualId) && !$confirmarTransferencia) {
                if ($estadoCuentaExistente && $estadoCuentaExistente->id != $estadoCuentaActualId) {
                    return response()->json([
                        'ok' => false,
                        'already_exists' => true,
                        'message' => 'El número de estado de cuenta "' . $numeroEdoCuenta . '" ya pertenece a otro estado de cuenta registrado.'
                    ], 400);
                }
            }

            DB::beginTransaction();

            $estadoCuentaTarget = null;

            if ($modo === 'editar' && !empty($estadoCuentaActualId)) {
                $estadoCuentaActual = Estado_Cuenta::where('id', $estadoCuentaActualId)
                    ->where('id_empresa', $idEmpresa)
                    ->first();

                if (!$estadoCuentaActual) {
                    $estadoCuentaActual = $estadoCuentaExistente;
                }

                if ($estadoCuentaActual) {
                    // Subcaso 3A: El número ya pertenece a otro estado de cuenta distinto (transferencia confirmada)
                    if ($estadoCuentaExistente && $estadoCuentaExistente->id != $estadoCuentaActual->id) {
                        $estadoCuentaTarget = $estadoCuentaExistente;
                    }
                    // Subcaso 3B: El número es idéntico al actual (no hay cambio de folio)
                    elseif ($estadoCuentaActual->numero === $numeroEdoCuenta) {
                        $estadoCuentaTarget = $estadoCuentaActual;
                    }
                    // Subcaso 3C: El número es nuevo (renombrar o crear subconjunto)
                    else {
                        $totalCotizacionesEdoActual = Estado_Cuenta_Cotizaciones::where('estado_cuenta_id', $estadoCuentaActual->id)->count();
                        $seleccionadasDeEdoActual = Estado_Cuenta_Cotizaciones::where('estado_cuenta_id', $estadoCuentaActual->id)
                            ->whereIn('cotizacion_id', $idsSeleccionados)
                            ->count();

                        // Si no se marcó "solo esta" o si se seleccionaron todas las cotizaciones de este estado de cuenta:
                        // Se renombra el registro actual directamente sin crear un duplicado ni dejar huérfanos.
                        $esRenombreCompleto = (!$soloEsta) || ($seleccionadasDeEdoActual >= $totalCotizacionesEdoActual);

                        if ($esRenombreCompleto) {
                            $estadoCuentaActual->update([
                                'numero' => $numeroEdoCuenta,
                                'updated_by' => auth()->id(),
                            ]);
                            $estadoCuentaTarget = $estadoCuentaActual;
                        } else {
                            // Se seleccionó solo un subconjunto: crear el nuevo estado de cuenta para este grupo
                            $estadoCuentaTarget = Estado_Cuenta::create([
                                'numero' => $numeroEdoCuenta,
                                'id_empresa' => $idEmpresa,
                                'created_by' => auth()->id(),
                            ]);
                        }
                    }

                    // Asignación de cotizaciones:
                    if (!$soloEsta && $estadoCuentaTarget->id != $estadoCuentaActual->id) {
                        // Transferir todas las cotizaciones del estado de cuenta actual
                        Estado_Cuenta_Cotizaciones::where('estado_cuenta_id', $estadoCuentaActual->id)
                            ->update([
                                'estado_cuenta_id' => $estadoCuentaTarget->id,
                                'assigned_by' => auth()->id(),
                                'updated_at' => now()
                            ]);
                    } else {
                        // Mover únicamente las cotizaciones seleccionadas
                        foreach ($idsSeleccionados as $idcot) {
                            Estado_Cuenta_Cotizaciones::updateOrCreate(
                                ['cotizacion_id' => $idcot],
                                [
                                    'estado_cuenta_id' => $estadoCuentaTarget->id,
                                    'assigned_by' => auth()->id(),
                                ]
                            );
                        }
                    }
                }
            }

            // Si es modo 'crear' o 'anexar' (o fallback si no se definió target)
            if (!$estadoCuentaTarget) {
                if ($estadoCuentaExistente) {
                    $estadoCuentaTarget = $estadoCuentaExistente;
                } else {
                    $estadoCuentaTarget = Estado_Cuenta::create([
                        'numero' => $numeroEdoCuenta,
                        'id_empresa' => $idEmpresa,
                        'created_by' => auth()->id(),
                    ]);
                }

                foreach ($idsSeleccionados as $idcot) {
                    Estado_Cuenta_Cotizaciones::updateOrCreate(
                        ['cotizacion_id' => $idcot],
                        [
                            'estado_cuenta_id' => $estadoCuentaTarget->id,
                            'assigned_by' => auth()->id(),
                        ]
                    );
                }
            }

            // LIMPIEZA AUTOMÁTICA DE HUÉRFANOS:
            // Cualquier estado de cuenta que haya quedado con 0 cotizaciones se elimina
            Estado_Cuenta::where('id_empresa', $idEmpresa)
                ->doesntHave('cotizaciones')
                ->delete();

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => $modo === 'anexar' 
                    ? 'Cotizaciones anexadas al estado de cuenta correctamente.' 
                    : ($modo === 'editar' ? 'Estado de cuenta actualizado correctamente.' : 'Estado de cuenta guardado correctamente.'),
                'id' => $estadoCuentaTarget->id,
                'numero' => $estadoCuentaTarget->numero,
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo procesar el estado de cuenta: ' . $th->getMessage(),
                'error' => $th->getMessage()
            ], 422);
        }
    }

    public function buscarPagos(Request $request)
    {
        $idEmpresa = auth()->user()->id_empresa;

        if (!$request->has('fecha_inicio') || !$request->has('fecha_fin')) {
            $request->merge([
                'fecha_inicio' => now()->subDays(6)->format('Y-m-d'),
                'fecha_fin' => now()->format('Y-m-d'),
            ]);
        }

        // Clientes para filtro
        $clientes = Client::join('client_empresa as ce', 'clients.id', '=', 'ce.id_client')
            ->where('ce.id_empresa', $idEmpresa)
            ->where('is_active', 1)
            ->orderBy('nombre')
            ->select('clients.*')
            ->get();

        // Proveedores para filtro
        $proveedores = \App\Models\Proveedor::where('id_empresa', $idEmpresa)
            ->orderBy('nombre')
            ->get();

        $pagos = $this->CuentasCobrarService->obtenerPagosHistoricos($request->all());

        return view('cuentas_cobrar.historico', compact('pagos', 'clientes', 'proveedores'));
    }

    public function eliminarCobroPago(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $cobroPago = \App\Models\CobroPago::with('detalles.cotizacion')->findOrFail($id);

            // 1. Revertir saldos y estatus en las cotizaciones vinculadas
            foreach ($cobroPago->detalles as $detalle) {
                $cotizacion = $detalle->cotizacion;
                if ($cotizacion) {
                    if ($cobroPago->tipo === 'cxc') {
                        $cotizacion->restante = $cotizacion->restante + $detalle->monto;
                        $cotizacion->estatus_pago = ($cotizacion->restante == 0);
                        if ($cotizacion->restante > 0) {
                            $cotizacion->fecha_pago = null;
                        }
                    } elseif ($cobroPago->tipo === 'cxp') {
                        $cotizacion->prove_restante = $cotizacion->prove_restante + $detalle->monto;
                        if ($cotizacion->prove_restante > 0) {
                            $cotizacion->fecha_pago_proveedor = null;
                        }
                    }
                    $cotizacion->save();
                }
                // Eliminar el detalle del cobro/pago
                $detalle->delete();
            }

            // 2. Buscar y cancelar movimientos bancarios
            $movimientos = \App\Models\CatBancoCuentasMovimientos::where('referenciaable_id', $cobroPago->id)
                ->where('referenciaable_type', \App\Models\CobroPago::class)
                ->where('cancelado', false)
                ->get();

            $fechaCancelacion = $request->input('fecha_cancelacion', now()->format('Y-m-d'));

            foreach ($movimientos as $mov) {
                // Cancelamos el movimiento en el sistema de bancos
                $this->BancosService->cancelarMovimiento($mov->cuenta_bancaria_id, $mov->id, $fechaCancelacion);

                // Actualizar manualmente el saldo de la cuenta bancaria para mantener coherencia
                if ($mov->tipo === 'abono') {
                    // Si era abono (ingreso), restamos del saldo
                    Bancos::where('id', $mov->cuenta_bancaria_id)
                        ->update(['saldo' => DB::raw("COALESCE(saldo, 0) - " . floatval($mov->monto))]);
                } elseif ($mov->tipo === 'cargo') {
                    // Si era cargo (egreso), sumamos de vuelta al saldo
                    Bancos::where('id', $mov->cuenta_bancaria_id)
                        ->update(['saldo' => DB::raw("COALESCE(saldo, 0) + " . floatval($mov->monto))]);
                }
            }

            // 3. Eliminar el registro principal de CobroPago
            $cobroPago->delete();

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'El cobro/pago y sus movimientos bancarios asociados han sido cancelados y eliminados correctamente.'
                ]);
            }

            return redirect()->back()->with('success', 'El cobro/pago y sus movimientos bancarios asociados han sido cancelados y eliminados correctamente.');

        } catch (\Throwable $th) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al intentar eliminar el registro: ' . $th->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Error al intentar eliminar el registro: ' . $th->getMessage());
        }
    }

    public function exportarExcel(Request $request)
    {
        $tipo = $request->input('tipo', 'cxc');
        $pagos = $this->CuentasCobrarService->obtenerPagosHistoricosSinPaginacion($request->all(), $tipo);

        $filename = "historial_" . $tipo . "_" . date('Y-m-d') . ".xls";

        return response()->view('cuentas_cobrar.excel_historico', compact('pagos', 'tipo'))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportarPdf(Request $request)
    {
        $tipo = $request->input('tipo', 'cxc');
        $pagos = $this->CuentasCobrarService->obtenerPagosHistoricosSinPaginacion($request->all(), $tipo);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('cuentas_cobrar.pdf_historico', compact('pagos', 'tipo'));
        return $pdf->download("historial_" . $tipo . "_" . date('Y-m-d') . ".pdf");
    }

    public function descargarComprobantePdf($id)
    {
        $pago = $this->CuentasCobrarService->obtenerPagoDetalle($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('cuentas_cobrar.pdf_comprobante', compact('pago'));
        return $pdf->download("comprobante_" . $pago->tipo . "_" . $pago->id . ".pdf");
    }
}
