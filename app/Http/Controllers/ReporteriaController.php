<?php

namespace App\Http\Controllers;

use App\Models\Asignaciones;
use App\Models\BancoDinero;
use App\Models\Bancos;
use App\Models\Client;
use App\Models\Cotizaciones;
use App\Models\DocumCotizacion;
use App\Models\Equipo;
use App\Models\GastosExtras;
use App\Models\GastosOperadores;
use App\Models\GastosGenerales;
use App\Models\Proveedor;
use App\Models\Subclientes;
use App\Models\User;
use App\Exports\CotizacionesCXC;
use App\Exports\GenericExport;
use App\Exports\CxcExport;
use App\Exports\CxpExport;
use App\Models\GastosDiferidosDetalle;
use App\Models\DineroContenedor;
use App\Models\ViaticosOperador;
use App\Models\CuentaGlobal;
use App\Models\Estado_Cuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Traits\CommonTrait as Common;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReporteriaController extends Controller
{
    public function index()
    {

        //$clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $clientes = Client::join('client_empresa as ce', 'clients.id', '=', 'ce.id_client')
                            ->where('ce.id_empresa', Auth::User()->id_empresa)
                            ->where('is_active', 1)
                            ->orderBy('nombre')->get();

        $clientesIds = $clientes->pluck('id');

        $subclientes = Subclientes::whereIn('id_cliente', $clientesIds)->orderBy('created_at', 'desc')->get();
        $proveedores = Proveedor::where('id_empresa', '=', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $estadosCuentas = Estado_Cuenta::where('id_empresa', '=', auth()->user()->id_empresa)->get();

        return view('reporteria.cxc.index', compact('clientes', 'subclientes', 'proveedores', 'estadosCuentas'));
    }

    public function advance(Request $request)
    {
        // Obtener los datos de los filtros
        $id_client = $request->input('id_client');
        $id_subcliente = $request->input('id_subcliente');
        $id_proveedor = $request->input('id_proveedor');
        $numeroEdoCuenta = $request->numero_edo_cuenta ?? null;

        // Obtener los clientes, subclientes y proveedores para mostrarlos en el formulario
        //$clientes = Client::where('id_empresa', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $clientes = Client::join('client_empresa as ce', 'clients.id', '=', 'ce.id_client')
                            ->where('ce.id_empresa', Auth::User()->id_empresa)
                            ->where('is_active', 1)
                            ->orderBy('nombre')->get();

        $clientesIds = $clientes->pluck('id');

        $subclientes = Subclientes::whereIn('id_cliente', $clientesIds)->orderBy('created_at', 'desc')->get();
        $proveedores = Proveedor::where('id_empresa', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $estadosCuentas = Estado_Cuenta::where('id_empresa', '=', auth()->user()->id_empresa)->get();

        // Inicializar la consulta de cotizaciones
        $cotizaciones = Cotizaciones::query()
    ->select(
        'cotizaciones.*',
        'estado_cuenta.numero as numero_edo_cuenta',
        'estado_cuenta.id as id_numero_edo_cuenta'
    )
    ->leftJoin(
        'estado_cuenta_cotizaciones',
        'estado_cuenta_cotizaciones.cotizacion_id',
        '=',
        'cotizaciones.id'
    )
    ->leftJoin(
        'estado_cuenta',
        'estado_cuenta.id',
        '=',
        'estado_cuenta_cotizaciones.estado_cuenta_id'
    )->where('cotizaciones.id_empresa', auth()->user()->id_empresa)
            ->where(function ($query) {
                $query->where('estatus', '=', 'Aprobada')
                    ->orWhere('estatus', '=', 'Finalizado');
            })
           ->where(function ($q) {
               $q->where('estatus_pago', 0)
                 ->orWhereNull('estatus_pago');
           })
            ->where('restante', '>', 0);  // Solo cotizaciones con saldo restante

        // Filtrar por cliente si se selecciona uno
        if ($id_client) {
            $cotizaciones->where('id_cliente', $id_client);
        }

        // Filtrar por subcliente si se selecciona uno
        if ($id_subcliente) {
            $cotizaciones->where('id_subcliente', $id_subcliente);
        }

        // Filtrar por proveedor si se selecciona uno
        if ($id_proveedor) {
            $cotizaciones->whereHas('DocCotizacion.Asignaciones', function ($query) use ($id_proveedor) {
                $query->where('id_proveedor', $id_proveedor);
            });
        }
        //nuevo para el num edo cuenta x id
        if ($numeroEdoCuenta) {
            $cotizaciones->where('estado_cuenta.id', '=', $numeroEdoCuenta);
        }

        // Ejecutar la consulta
        $cotizaciones = $cotizaciones->get();

        // Devolver la vista con los filtros y las cotizaciones
        return view('reporteria.cxc.index', compact('clientes', 'subclientes', 'proveedores', 'cotizaciones', 'estadosCuentas'));
    }


    public function getSubclientes($clienteId)
    {
        $subclientes = Subclientes::where('id_cliente', $clienteId)->get();
        return response()->json($subclientes);
    }

    public function export(Request $request)
    {
        $fecha = date('Y-m-d');
        $fechaCarbon = Carbon::parse($fecha);

        $cotizacionIds = $request->input('selected_ids', []);
        if (empty($cotizacionIds)) {
            return redirect()->back()->with('error', 'No se seleccionaron cotizaciones.');
        }

        $cotizacion = Cotizaciones::where('id', $cotizacionIds)->first();
        $user = User::where('id', '=', auth()->user()->id)->first();
        $cuentaGlobal = CuentaGlobal::first();

        $cotizaciones = Cotizaciones::with('estadoCuenta')->whereIn('id', $cotizacionIds)->get();
        if (in_array($cotizacion->id_empresa, [2,6])) {
            $bancos_oficiales = Bancos::where('id_empresa', '=', $cotizacion->id_empresa)->get();
            $bancos_no_oficiales = Bancos::where('id_empresa', '=', $cotizacion->id_empresa)->get();
        } else {
            $bancos_oficiales = Bancos::where('tipo', '=', 'Oficial')->get();
            $bancos_no_oficiales = Bancos::where('tipo', '=', 'No Oficial')->get();
        }


        if ($request->fileType == "xlsx") {
            Excel::store(new CxcExport($cotizaciones, $fechaCarbon, $bancos_oficiales, $bancos_no_oficiales, $cotizacion, $user), 'cotizaciones_cxc.xlsx', 'public');
            return Response::download(storage_path('app/public/cotizaciones_cxc.xlsx'), "cxc.xlsx")->deleteFileAfterSend(true);
        } else {
            $pdf = PDF::loadView('reporteria.cxc.pdf', compact('cotizaciones', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales', 'cotizacion', 'user', 'cuentaGlobal'))->setPaper([0, 0, 595, 1200], 'landscape');

            // Generar el nombre del archivo
            $fileName = 'cxc_' . implode('_', $cotizacionIds) . '.pdf';
            // Guardar el PDF en la carpeta storage
            $pdf->save(storage_path('app/public/' . $fileName));

            // Devolver el archivo PDF como respuesta
            $filePath = storage_path('app/public/' . $fileName);
            return Response::download($filePath, $fileName)->deleteFileAfterSend(true);
        }
    }

    public function exportExcel(request $r)
    {
        $collection = Collect(json_decode($r->dataExport));
        $procesedData = $collection->map(function ($c) {
            $fecha = optional($c->doc_cotizacion->asignaciones)->fehca_inicio_guard;
            return ["id" => $c->id,
                    "fecha" => ($fecha != null) ? Carbon::parse($fecha)->format('d/m/Y') : "Sin Fecha",
                    "cliente" => $c->cliente->nombre,
                    "subcliente" => $c->subcliente->nombre ?? "-",
                    "origen" => $c->origen,
                    "dest" => $c->destino,
                    "contenedor" => $c->doc_cotizacion->num_contenedor,
                    "estatus" => $c->estatus
                ];
        });

        Excel::store(new CotizacionesCXC(json_decode($procesedData)), 'cotizaciones_cxc.xlsx', 'public');
        return Response::download(storage_path('app/public/cotizaciones_cxc.xlsx'), "cxc.xlsx")->deleteFileAfterSend(true);
    }

    public function buildExcelData($excelData, $report = 0)
    {
        $collection = Collect(json_decode($excelData));
        $procesedData = $collection->map(function ($c) use ($report) {
            switch ($report) {
                case 0:
                    return [
                        $c->id,
                        $c->origen,
                        $c->destino,
                        $c->num_contenedor,
                        $c->estatus
                   ];
                    break;
                case 1:
                    return [
                            $c->contenedor->cotizacion->cliente->nombre,
                            ($c->contenedor->cotizacion->id_subcliente != null) ? $c->contenedor->cotizacion->subcliente->nombre." / ".$c->contenedor->cotizacion->subcliente->telefono : '-',
                            $c->contenedor->cotizacion->origen,
                            $c->contenedor->cotizacion->destino,
                            $c->contenedor->num_contenedor,
                            Carbon::parse($c->fehca_inicio_guard)->format('d-m-Y') ,
                            Carbon::parse($c->fehca_fin_guard)->format('d-m-Y') ,
                            $c->contenedor->cotizacion->estatus
                         ];
                    break;
                case 2:
                    if ($c->total_proveedor == null) {
                        $utilidad = $c->total - $c->pago_operador;
                    } elseif ($c->total_proveedor != null) {
                        $utilidad = $c->total - $c->total_proveedor;
                    } else {
                        $utilidad = 0;
                    }
                    return [
                            $c->contenedor->cotizacion->cliente->nombre,
                            ($c->contenedor->cotizacion->id_subcliente != null) ? $c->contenedor->cotizacion->subcliente->nombre." / ".$c->contenedor->cotizacion->subcliente->telefono : '-',
                            $c->contenedor->cotizacion->origen,
                            $c->contenedor->cotizacion->destino,
                            $c->contenedor->num_contenedor,
                            $utilidad
                         ];
                    break;
                case 3: // Liquidados cxc

                    return [
                        $c->cliente->nombre,
                        ($c->subcliente != null) ? $c->subcliente->nombre." / ".$c->subcliente->telefono : '-',
                        $c->origen,
                        $c->destino,
                        $c->doc_cotizacion->num_contenedor,
                        $c->estatus
                     ];
                    break;
                case 4: // Liquidados cxp
                    return [
                        $c->origen,
                        $c->destino,
                        $c->num_contenedor,
                        $c->estatus
                     ];
                    break;

            }

        })->toArray();
        return $procesedData;
    }

    public function exportGenericExcel(Request $r)
    {
        $fileName = "generic_excel_".uniqid().".xlsx";
        $data = self::buildExcelData($r->dataExport, $r->reportNumber);
        $headers = json_decode($r->reportHeaders);

        Excel::store(new GenericExport($headers, $data), $fileName, 'public');
        return Response::download(storage_path('app/public/'.$fileName), "excel_export.xlsx")->deleteFileAfterSend(true);
    }

    // ==================== C U E N T A S  P O R  P A G A R ====================
    public function index_cxp()
    {
        $proveedores = Proveedor::where('id_empresa', '=', auth()->user()->id_empresa)
                                 ->orderBy('created_at', 'desc')
                                 ->get();

        /*$clientes = Client::where('id_empresa', '=', auth()->user()->id_empresa)
                            ->orderBy('nombre', 'asc')
                            ->get();*/
        $clientes = Client::join('client_empresa as ce', 'clients.id', '=', 'ce.id_client')
                                ->where('ce.id_empresa', Auth::User()->id_empresa)
                                ->where('is_active', 1)
                                ->orderBy('nombre')->get();

        $estadosCuentas = Estado_Cuenta::where('id_empresa', '=', auth()->user()->id_empresa)->get();

        $clientesIds = $clientes->pluck('id');

        $subclientes = Subclientes::whereIn('id_cliente', $clientesIds)->orderBy('created_at', 'desc')->get();

        return view('reporteria.cxp.index', compact('proveedores', 'clientes', 'subclientes', 'estadosCuentas'));
    }
    public function advance_cxp(Request $request)
    {
        // Obtener proveedores y clientes de la empresa actual
        $proveedores = Proveedor::where('id_empresa', '=', auth()->user()->id_empresa)
                                 ->orderBy('created_at', 'desc')
                                 ->get();

        $clientes = Client::join('client_empresa as ce', 'clients.id', '=', 'ce.id_client')
                            ->where('ce.id_empresa', Auth::User()->id_empresa)
                            ->where('is_active', 1)
                            ->orderBy('nombre')->get();
        $estadosCuentas = Estado_Cuenta::where('id_empresa', '=', auth()->user()->id_empresa)->get();

        $clientesIds = $clientes->pluck('id');

        $subclientes = Subclientes::whereIn('id_cliente', $clientesIds)->orderBy('created_at', 'desc')->get();

        // Obtener el ID del proveedor y del cliente desde la solicitud
        $id_proveedor = $request->input('id_proveedor');
        $numeroEdoCuenta = $request->numero_edo_cuenta ?? null;

        // Mostrar advertencia si no se seleccionó proveedor
        $showWarning = empty($id_proveedor);

        // Inicializar variables
        $cotizaciones = [];
        $proveedor_cxp = null;

        // Si se seleccionó un proveedor, filtrar las cotizaciones correspondientes
        //     if ($id_proveedor) {
        //         $cotizaciones = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        //             ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
        //              ->leftJoin(
        //                  'estado_cuenta_cotizaciones',
        //                  'estado_cuenta_cotizaciones.cotizacion_id',
        //                  '=',
        //                  'cotizaciones.id'
        //              )
        // ->leftJoin(
        //     'estado_cuenta',
        //     'estado_cuenta.id',
        //     '=',
        //     'estado_cuenta_cotizaciones.estado_cuenta_id'
        // )
        //             ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
        //           //  ->whereNull('asignaciones.id_camion') // Verificar que el camión sea NULL , se rompio la logica al agregar contratos de tipo propio
        //             ->where('asignaciones.tipo_contrato', 'Subcontratado')
        //             ->where(function ($query) {
        //                 $query->where('cotizaciones.estatus', '=', 'Aprobada')
        //                       ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
        //             })
        //             ->where('asignaciones.id_proveedor', '=', $id_proveedor)
        //             ->where('cotizaciones.prove_restante', '>', 0)
        //             ->select(
        //                 'asignaciones.*',
        //                 'docum_cotizacion.num_contenedor',
        //                 'docum_cotizacion.id_cotizacion',
        //                 'cotizaciones.origen',
        //                 'cotizaciones.destino',
        //                 'cotizaciones.estatus',
        //                 'cotizaciones.prove_restante',
        //                 'estado_cuenta.numero as numero_edo_cuenta'
        //             )
        //             ->get();

        //         // Obtener datos del proveedor seleccionado
        //         $proveedor_cxp = Proveedor::find($id_proveedor);
        //     }



        $cotizacionesQuery = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
        ->leftJoin(
            'estado_cuenta_cotizaciones',
            'estado_cuenta_cotizaciones.cotizacion_id',
            '=',
            'cotizaciones.id'
        )
        ->leftJoin(
            'estado_cuenta',
            'estado_cuenta.id',
            '=',
            'estado_cuenta_cotizaciones.estado_cuenta_id'
        )
        ->where('cotizaciones.id_empresa', auth()->user()->id_empresa)
        ->where('asignaciones.tipo_contrato', 'Subcontratado')
        ->where(function ($query) {
            $query->where('cotizaciones.estatus', 'Aprobada')
                  ->orWhere('cotizaciones.estatus', 'Finalizado');
        })
        ->where('cotizaciones.prove_restante', '>', 0);

        if (!empty($id_proveedor)) {
            $cotizacionesQuery->where('asignaciones.id_proveedor', $id_proveedor);
        }

        if (!empty($numeroEdoCuenta)) {
            $cotizacionesQuery->where('estado_cuenta.id', $numeroEdoCuenta);
        }

        $cotizaciones = $cotizacionesQuery
    ->select(
        'asignaciones.*',
        'docum_cotizacion.num_contenedor',
        'docum_cotizacion.id_cotizacion',
        'cotizaciones.origen',
        'cotizaciones.destino',
        'cotizaciones.estatus',
        'cotizaciones.prove_restante',
        'estado_cuenta.numero as numero_edo_cuenta',
        'estado_cuenta.id as id_numero_edo_cuenta'
    )
    ->get();


        if (!empty($id_proveedor)) {
            $proveedor_cxp = Proveedor::find($id_proveedor);
        }

        // Retornar la vista con los datos necesarios
        return view('reporteria.cxp.index', compact('proveedores', 'clientes', 'cotizaciones', 'proveedor_cxp', 'showWarning', 'id_proveedor', 'subclientes', 'estadosCuentas'));
    }


    public function export_cxp(Request $request)
    {
        // Obtener la fecha actual
        $fecha = date('Y-m-d');
        $fechaCarbon = Carbon::parse($fecha);

        // Obtener los IDs de las cotizaciones seleccionadas
        $cotizacionIds = $request->input('selected_ids', []);
        if (empty($cotizacionIds)) {
            return redirect()->back()->with('error', 'No se seleccionaron cotizaciones.');
        }

        // Cargar las cotizaciones con sus proveedores y cuentas bancarias
        $cotizaciones = Asignaciones::with([
            'Proveedor.CuentasBancarias',
            'Contenedor.Cotizacion.Subcliente',
            'Contenedor.Cotizacion.estadoCuenta'
        ])->whereIn('id', $cotizacionIds)->get();




        $bancos_oficiales = Bancos::where('tipo', '=', 'Oficial')->get();
        $bancos_no_oficiales = Bancos::where('tipo', '=', 'No Oficial')->get();

        // Obtener la primera cotización (si es necesario)
        $cotizacion = Asignaciones::with('Contenedor.Cotizacion.estadoCuenta')->where('id', $cotizacionIds)->first();
        //  dd($cotizacion);

        // Obtener los datos del usuario autenticado
        $user = User::where('id', '=', auth()->user()->id)->first();

        // Generar el archivo Excel o PDF según el tipo de archivo solicitado
        if ($request->fileType == "xlsx") {
            // Generar el archivo Excel y almacenarlo
            Excel::store(new CxpExport($cotizaciones, $fechaCarbon, $bancos_oficiales, $bancos_no_oficiales, $cotizacion, $user, true), 'cotizaciones_cxp.xlsx', 'public');

            // Devolver el archivo Excel como descarga
            return Response::download(storage_path('app/public/cotizaciones_cxp.xlsx'), "cxp.xlsx")->deleteFileAfterSend(true);
        } else {
            // Generar el archivo PDF
            $pdf = PDF::loadView('reporteria.cxp.pdf', compact('cotizaciones', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales', 'cotizacion', 'user'))->setPaper('a4', 'landscape');

            // Crear un nombre para el archivo PDF
            $fileName = 'cxp_' . implode('_', $cotizacionIds) . '.pdf';

            // Guardar el archivo PDF en la carpeta de almacenamiento
            $pdf->save(storage_path('app/public/' . $fileName));

            // Devolver el archivo PDF como descarga
            $filePath = storage_path('app/public/' . $fileName);
            return Response::download($filePath, $fileName)->deleteFileAfterSend(true);
        }
    }


    // ==================== V I A J E S ====================
    public function index_viajes()
    {

        /*   $clientes = Client::where('id_empresa', '=', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

          $subclientes = Subclientes::where('id_empresa', '=', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
          $proveedores = Proveedor::where('id_empresa', '=', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
          $estatus = DB::table('cotizaciones')
                      ->where('id_empresa', auth()->user()->id_empresa)
                      ->select('estatus')
                      ->distinct()
                      ->pluck('estatus');
          $asignaciones = Asignaciones::with([
              'Contenedor.Cotizacion.Cliente',
              'Contenedor.Cotizacion.Subcliente',
              'Proveedor',
              'Operador'
          ])
          ->where('id_empresa', auth()->user()->id_empresa)
          ->get();

          $viajesData = $asignaciones->map(function ($a) {
              $numContenedor = $a->Contenedor->num_contenedor;
              if (!is_null($a->Contenedor->Cotizacion->referencia_full)) {
                  $secundaria = Cotizaciones::where('referencia_full', $a->Contenedor->Cotizacion->referencia_full)
                  ->where('jerarquia', 'Secundario')
                  ->with('DocCotizacion.Asignaciones')
                  ->first();

                  if ($secundaria && $secundaria->DocCotizacion) {
                      $numContenedor .= ' / ' . $secundaria->DocCotizacion->num_contenedor;
                  }
              }
              return [
                  'id' => $a->id,
                  'cliente' => $a->Contenedor->Cotizacion->Cliente->nombre ?? '-',
                  'subcliente' => $a->Contenedor->Cotizacion->Subcliente->nombre ?? '-',
                  'origen' => $a->Contenedor->Cotizacion->origen ?? '',
                  'destino' => $a->Contenedor->Cotizacion->destino ?? '',
                  'contenedor' => $numContenedor ?? '',
                  'fecha_salida' => \Carbon\Carbon::parse($a->fecha_inicio)->format('d-m-Y'),
                  'fecha_llegada' => \Carbon\Carbon::parse($a->fecha_fin)->format('d-m-Y'),
                  'estatus' => $a->Contenedor->Cotizacion->estatus ?? '',
                  'proveedor' => $a->Proveedor->nombre ?? '-',
                  'operador' => $a->Operador->nombre ?? '-',
              ];
          })->toArray(); */

        return view('reporteria.asignaciones.index');
    }

    public function getViajesFiltrados(Request $request)
    {
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');

        $asignaciones = Asignaciones::with([
            'Contenedor.Cotizacion.Cliente',
            'Contenedor.Cotizacion.Subcliente',
            'Proveedor',
            'Operador'
        ])
        ->where('id_empresa', auth()->user()->id_empresa)
        ->whereBetween('fecha_inicio', [
            Carbon::parse($fechaInicio)->startOfDay(),
            Carbon::parse($fechaFin)->endOfDay()
        ])
        ->get();

        //  dd(Carbon::parse($fechaFin)->endOfDay(), $asignaciones);

        $viajesData = $asignaciones->map(function ($a) {
            $numContenedor = $a->Contenedor->num_contenedor;
            if (!is_null($a->Contenedor->Cotizacion->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $a->Contenedor->Cotizacion->referencia_full)
                ->where('jerarquia', 'Secundario')
                ->with('DocCotizacion.Asignaciones')
                ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $numContenedor .= ' / ' . $secundaria->DocCotizacion->num_contenedor;
                }
            }
            return [
                'id' => $a->id,
                'cliente' => $a->Contenedor->Cotizacion->Cliente->nombre ?? '-',
                'subcliente' => $a->Contenedor->Cotizacion->Subcliente->nombre ?? '-',
                'origen' => $a->Contenedor->Cotizacion->origen ?? '',
                'destino' => $a->Contenedor->Cotizacion->destino ?? '',
                'contenedor' => $numContenedor ?? '',
                'fecha_salida' => optional($a->fehca_inicio_guard)->format('d-m-Y'),
                'fecha_llegada' => optional($a->fehca_fin_guard)->format('d-m-Y'),
                'estatus' => $a->Contenedor->Cotizacion->estatus ?? '',
                'proveedor' => $a->Proveedor->nombre ?? '-',
                 'operador' => $a->Operador->nombre ?? '-',
            ];
        });

        return response()->json($viajesData);
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

    public function advance_viajes(Request $request)
    {
        $clientes = Client::where('id_empresa', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $subclientes = Subclientes::where('id_empresa', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $proveedores = Proveedor::where('id_empresa', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $estatus = DB::table('cotizaciones')
            ->where('id_empresa', auth()->user()->id_empresa)
            ->select('estatus')
            ->distinct()
            ->pluck('estatus');

        $id_client = $request->id_client;
        $id_subcliente = $request->id_subcliente;
        $id_proveedor = $request->id_proveedor;

        $asignaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
            ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->where('asignaciones.id_empresa', auth()->user()->id_empresa)
            ->select('asignaciones.*');

        // ✅ Reemplazamos filtro por fechas anteriores por el nuevo daterange
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');

        if ($fechaInicio && $fechaFin) {
            $inicio = Carbon::parse($fechaInicio)->startOfDay();
            $fin = Carbon::parse($fechaFin)->endOfDay();

            $asignaciones = $asignaciones->whereBetween('asignaciones.fehca_inicio_guard', [$inicio, $fin]);
        }

        if (!is_null($id_client)) {
            $asignaciones = $asignaciones->where('cotizaciones.id_cliente', $id_client);

            if (!is_null($id_subcliente) && $id_subcliente !== '') {
                $asignaciones = $asignaciones->where('cotizaciones.id_subcliente', $id_subcliente);
            }
        }

        if (!is_null($id_proveedor)) {
            $asignaciones = $asignaciones->where('asignaciones.id_proveedor', $id_proveedor);
        }

        if ($request->filled('estatus')) {
            $asignaciones = $asignaciones->where('cotizaciones.estatus', $request->estatus);
        }

        $asignaciones = $asignaciones->get();

        $asignaciones = $asignaciones->get();

        $viajesData = $asignaciones->map(function ($a) {
            $numContenedor = $a->Contenedor->num_contenedor;
            if (!is_null($a->Contenedor->Cotizacion->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $a->Contenedor->Cotizacion->referencia_full)
                ->where('jerarquia', 'Secundario')
                ->with('DocCotizacion.Asignaciones')
                ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $numContenedor .= ' / ' . $secundaria->DocCotizacion->num_contenedor;
                }
            }
            return [
                'id' => $a->id,
                'cliente' => $a->Contenedor->Cotizacion->Cliente->nombre ?? '-',
                'subcliente' => $a->Contenedor->Cotizacion->Subcliente->nombre ?? '-',
                'origen' => $a->Contenedor->Cotizacion->origen ?? '',
                'destino' => $a->Contenedor->Cotizacion->destino ?? '',
                'contenedor' => $numContenedor ?? '',
                'fecha_salida' => \Carbon\Carbon::parse($a->fehca_inicio_guard)->format('d-m-Y'),
                'fecha_llegada' => \Carbon\Carbon::parse($a->fehca_fin_guard)->format('d-m-Y'),
                'estatus' => $a->Contenedor->Cotizacion->estatus ?? '',
                'proveedor' => $a->Proveedor->nombre ?? '-',
            ];
        })->toArray();

        return view('reporteria.asignaciones.index', compact(
            'asignaciones',
            'clientes',
            'subclientes',
            'proveedores',
            'estatus',
            'viajesData'
        ));

    }


    public function export_viajes(Request $request)
    {
        $fecha = date('Y-m-d');
        $fechaCarbon = Carbon::parse($fecha);

        $fileType = $request->input('fileType');
        $exportAll = $request->input('exportAll') === 'true';
        $cotizacionIds = $request->input('cotizacion_ids', []);

        //  Detectar si se exporta todo o solo selección
        if ($exportAll) {
            $cotizaciones = Asignaciones::with(['Contenedor.Cotizacion.Cliente', 'Contenedor.Cotizacion.Subcliente'])
                ->where('id_empresa', auth()->user()->id_empresa)
                ->get();
        } elseif (!empty($cotizacionIds)) {
            $cotizaciones = Asignaciones::with(['Contenedor.Cotizacion.Cliente', 'Contenedor.Cotizacion.Subcliente'])
                ->whereIn('id', $cotizacionIds)
                ->get();
        } else {
            return back()->with('error', 'No se seleccionaron viajes para exportar.');
        }

        $bancos_oficiales = Bancos::where('tipo', '=', 'Oficial')->get();
        $bancos_no_oficiales = Bancos::where('tipo', '=', 'No Oficial')->get();
        $cotizacion = $cotizaciones->first();
        $user = User::find(auth()->id());

        if ($fileType === "xlsx") {
            Excel::store(
                new \App\Exports\AsignacionesExport(
                    $cotizaciones,
                    $fechaCarbon,
                    $bancos_oficiales,
                    $bancos_no_oficiales,
                    $cotizacion,
                    $user
                ),
                'viajes_' . $fecha . '.xlsx',
                'public'
            );

            return response()->download(
                storage_path('app/public/viajes_' . $fecha . '.xlsx'),
                'viajes_' . $fecha . '.xlsx',
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            )->deleteFileAfterSend(true);
        }

        if ($fileType === "pdf") {
            $pdf = PDF::loadView('reporteria.asignaciones.pdf', [
                'cotizaciones' => $cotizaciones,
                'fechaCarbon' => $fechaCarbon,
                'bancos_oficiales' => $bancos_oficiales,
                'bancos_no_oficiales' => $bancos_no_oficiales,
                'cotizacion' => $cotizacion,
                'user' => $user
            ])->setPaper('a4', 'landscape');
            set_time_limit(120); // 2 minutos
            ini_set('memory_limit', '512M');

            return $pdf->download('viajes_' . $fecha . '.pdf');
        }

        return back()->with('error', 'Formato no soportado.');
    }


    // ==================== U T I L I D A D E S ====================

    public function index_utilidad()
    {

        return view('reporteria.utilidad.index');
    }

    public function advance_utilidad(Request $request)
    {
        $clientes = Client::where('id_empresa', '=', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $subclientes = Subclientes::where('id_empresa', '=', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $contenedores = DocumCotizacion::join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
        ->where('docum_cotizacion.num_contenedor', '!=', null)
        ->where('docum_cotizacion.id_empresa', '=', auth()->user()->id_empresa)
        ->where('cotizaciones.estatus', '=', 'Aprobada')
        ->orderBy('docum_cotizacion.created_at', 'desc')->get();

        $id_client = $request->id_client;
        $id_subcliente = $request->id_subcliente;
        $contenedor = $request->contenedor;

        // Construir la consulta inicial
        $asignaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
            ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->where('cotizaciones.id_empresa', auth()->user()->id_empresa)
            ->where('cotizaciones.estatus', 'Aprobada')
            ->select('asignaciones.*', 'cotizaciones.total');

        // Agregar filtros opcionales
        if ($request->fecha_de && $request->fecha_hasta) {
            $inicio = $request->fecha_de;
            $fin = $request->fecha_hasta;
            $asignaciones = $asignaciones->whereBetween('asignaciones.fecha_inicio', [
        Carbon::parse($inicio)->startOfDay(),
        Carbon::parse($fin)->endOfDay()
    ]);
            // ->where('asignaciones.fecha_inicio', '>=', $inicio)
            //                                      ->where('asignaciones.fecha_inicio', '<=', $fin);
        }

        // Obtener los resultados
        $asignaciones = $asignaciones->get();

        $fechaDe = $request->query('fecha_de');
        $fechaHasta = $request->query('fecha_hasta');

        return view('reporteria.utilidad.index', compact('asignaciones', 'clientes', 'subclientes', 'contenedores', 'fechaDe', 'fechaHasta'));
    }

    public function getContenedorUtilidad(Request $r)
    {
        /* */

        $contadorPeriodos = Common::contadorPeriodos($r->startDate, $r->endDate);
        // $datos = Collect();

        // $datos = $datos->merge($viajes);

        $fechaIniciaPeriodo = $r->startDate;
        $fechaHasta = $r->endDate;
        $Info = [];
        $Diferidos = [];
        for ($periodo = 1; $periodo <= $contadorPeriodos; $periodo++) {
            $finalMes = Carbon::parse($fechaIniciaPeriodo);
            $finalMes = $finalMes->endOfMonth();
            $fechaFinPeriodo = $finalMes->toDateString();
            $fechaInialGastos = Carbon::parse($fechaIniciaPeriodo)->startOfMonth();
            $inicio = Carbon::parse($fechaIniciaPeriodo)->startOfDay();
            $fin = Carbon::parse($fechaHasta)->endOfDay();

            $fechaI =  $inicio;
            $fechaF =  $fin; //no tomaba el ultimo dia completo, se agrego hora para incluirlo
            //  dd($fechaI, $fechaF);
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
                AND gg.fecha BETWEEN '$fechaI' AND '$fechaF'
            WHERE a.fecha_inicio BETWEEN '$fechaI' AND '$fechaF'
            AND a.id_camion IS NOT NULL
            GROUP BY a.id_camion, gg.motivo;";

            $gastosUnidad = Collect(DB::select($gastosUnidadQuery));

            $consultar = 'select c.id as id_cotizacion,a.id_camion,dc.num_contenedor,cl.nombre as cliente, op.nombre Operador, a.sueldo_viaje,dinero_viaje, pr.nombre as Proveedor,total_proveedor,
            c.total, c.estatus,estatus_pago,c.fecha_pago,a.fecha_inicio,a.fecha_fin,DATEDIFF(a.fecha_fin,a.fecha_inicio) as tiempo_viaje,c.referencia_full
            from cotizaciones c
            left join clients cl on c.id_cliente = cl.id
            left join docum_cotizacion dc on c.id = dc.id_cotizacion
            left join asignaciones a on dc.id = a.id_contenedor
            left join operadores op on a.id_operador = op.id
            left join proveedores pr on a.id_proveedor = pr.id
            left join equipos eq on a.id_camion = eq.id
            where a.fecha_inicio between '."'".$fechaI."'".' and '."'".$fechaF."' and c.estatus != 'Cancelada' and c.id_empresa = ".Auth::User()->id_empresa;

            $viajes = DB::select($consultar);

            $viajesPeriodo = sizeof($viajes);

            foreach ($viajes as $d) {
                $detalleGastos = null;

                $camion = $gastosUnidad->where('id_camion', $d->id_camion);

                foreach ($camion as $gc) {
                    // Accedés a $fila->total_asignaciones, $fila->total_gastos_periodo, etc.
                    $detalleGastos [] = ["fecha_gasto" => $fechaI,
                                        "monto_gasto" => round($gc->gasto_por_viaje, 2),
                                        "tipo_gasto" => "DIFERIDO",
                                        "motivo_gasto" => $gc->motivo
                                    ];
                }

                $gastosExtra = GastosExtras::where('id_cotizacion', $d->id_cotizacion)->get();
                $gastosOperador = GastosOperadores::where('id_cotizacion', $d->id_cotizacion)->get();

                $dineroViaje = DineroContenedor::where('id_contenedor', $d->id_cotizacion)->get()->sum('monto');
                $dineroViajeJustificado = ViaticosOperador::where('id_cotizacion', $d->id_cotizacion)->get()->sum('monto');

                $sinJustificar = $dineroViaje - $dineroViajeJustificado;

                foreach ($gastosExtra as $ge) {
                    $detalleGastos [] = ["fecha_gasto" => $ge->created_at,
                    "monto_gasto" => $ge->monto,
                    "tipo_gasto" => "Gasto Extra",
                    "motivo_gasto" => $ge->descripcion
                ];
                }

                foreach ($gastosOperador as $go) {
                    $detalleGastos [] = ["fecha_gasto" => $go->fecha_pago,
                    "monto_gasto" => $go->cantidad,
                    "tipo_gasto" => "Gastos Viaje",
                    "motivo_gasto" => $go->tipo
                ];
                }

                $contenedor = $d->num_contenedor;

                if (!is_null($d->referencia_full)) {
                    $secundaria = Cotizaciones::where('referencia_full', $d->referencia_full)
                        ->where('jerarquia', 'Secundario')
                        ->with('DocCotizacion.Asignaciones')
                        ->first();

                    if ($secundaria && $secundaria->DocCotizacion) {
                        $contenedor .= ' / ' . $secundaria->DocCotizacion->num_contenedor;
                    }
                }


                $pagoOperacion = (is_null($d->Proveedor)) ? $d->sueldo_viaje : $d->total_proveedor;
                $gastosDiferidos = round($camion->sum('gasto_por_viaje'), 2);
                $Columns = [
                            "numContenedor" => $contenedor,
                            "cliente" => $d->cliente,
                            "precioViaje" => $d->total + $gastosExtra->sum('monto'),
                            "transportadoPor" => (is_null($d->Proveedor)) ? 'Operador' : 'Proveedor',
                            "operadorOrProveedor" => (is_null($d->Proveedor)) ? $d->Operador : $d->Proveedor,
                            "pagoOperacion" => $pagoOperacion - abs($sinJustificar),
                            "gastosExtra" => $gastosExtra->sum('monto'),
                            "dineroViajeSinJustificar" => abs($sinJustificar),
                            "gastosViaje" => $gastosOperador->sum('cantidad'),
                            "viajeInicia" => $d->fecha_inicio,
                            "viajeTermina" => $d->fecha_fin,
                            "estatusViaje" => $d->estatus,
                            "estatusPago" => ($d->estatus_pago == 1) ? 'Pagado' : 'Por Cobrar',
                            "gastosDiferidos" =>  $gastosDiferidos,
                            "detalleGastos" => $detalleGastos,
                            "utilidad" => $d->total  - $pagoOperacion - $gastosDiferidos - $gastosExtra->sum('monto') - $gastosOperador->sum('cantidad') ,

                            ];
                $Info[] = $Columns;
            }

            $fechaIniciaPeriodo = $finalMes->addDay()->toDateString();

        }

        return json_encode(["Info" => $Info,"contadorPeriodos" => $contadorPeriodos, "Diferidos" => $Diferidos]);
        return $Info;

    }

    public function export_utilidad(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');

        $fecha = date('Y-m-d');
        $fechaCarbon = Carbon::parse($fecha);
        $cotizaciones = Collect(json_decode($request->rowData, true));//Asignaciones::whereIn('id', $cotizacionIds)->get();
        $cotizacion = [];//Asignaciones::where('id', $cotizacionIds)->first();
        $user = User::where('id', '=', auth()->user()->id)->first();

        $gastosGenerales = GastosGenerales::with('Categoria')
    ->where('id_empresa', auth()->user()->id_empresa)
    ->where('aplicacion_gasto', 'like', "%periodo%")
    ->whereBetween('fecha', [$fechaInicio,$fechaFin])
    ->get();

        $gastos = $gastosGenerales->sum('monto1');

        // $gastos = [];

        $utilidad = $cotizaciones->sum('utilidad');

        $totalRows = $request->totalRows;
        $selectedRows = $cotizaciones->count();


        if ($request->fileType == "xlsx") {
            return Excel::download(
                new \App\Exports\UtilidadExport(
                    $cotizaciones,
                    $fechaCarbon,
                    $cotizacion,
                    $user,
                    $gastos,
                    $gastosGenerales,
                    $utilidad,
                    $fechaInicio,
                    $fechaFin,
                    $totalRows,
                    $selectedRows
                ),
                'Resultados_' . now()->format('d-m-Y') . '.xlsx'
            );


        } else {
            $pdf = PDF::loadView('reporteria.utilidad.pdf', compact(
                'cotizaciones',
                'utilidad',
                'fechaInicio',
                'fechaFin',
                'cotizacion',
                'user',
                'gastos',
                'gastosGenerales',
                'totalRows',
                'selectedRows'
            ))->setPaper('a4', 'landscape');

            return $pdf->stream('Resultados_rpt.pdf');
        }

    }

    // ==================== D O C U M E N T O S ====================

    public function index_documentos(Request $request)
    {
        $clientes = Client::where('id_empresa', auth()->user()->id_empresa)
            ->orderBy('created_at', 'desc')
            ->get();

        $subclientes = Subclientes::where('id_empresa', auth()->user()->id_empresa)
            ->orderBy('created_at', 'desc')
            ->get();

        $proveedores = Proveedor::where('id_empresa', auth()->user()->id_empresa)
            ->orderBy('created_at', 'desc')
            ->get();

        // Construir consulta base
        $cotizacionesQuery = Cotizaciones::query()

            ->where('cotizaciones.id_empresa', auth()->user()->id_empresa)
            ->where('cotizaciones.estatus', '!=', 'Cancelada')
            ->where('cotizaciones.jerarquia', "Principal")
            ->leftJoin('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
            ->leftJoin('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
            ->leftJoin('clients', 'cotizaciones.id_cliente', '=', 'clients.id')
            ->select(
                'cotizaciones.id',
                'clients.nombre as cliente',
                'docum_cotizacion.num_contenedor',
                'docum_cotizacion.doc_ccp',
                'docum_cotizacion.boleta_liberacion',
                'docum_cotizacion.doda',
                'cotizaciones.carta_porte',
                'cotizaciones.img_boleta AS boleta_vacio',
                'docum_cotizacion.doc_eir',
                'docum_cotizacion.cima',
                'asignaciones.id_proveedor',
                'asignaciones.fecha_inicio',
                'asignaciones.fecha_fin',
                'cotizaciones.referencia_full',
            )
            ->distinct();


        // Aplicar filtro por fechas si vienen del request
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $cotizacionesQuery->whereBetween('asignaciones.fecha_inicio', [
                $request->fecha_inicio,
                $request->fecha_fin
            ]);
        }

        $cotizaciones = $cotizacionesQuery->get();

        $cotizaciones = $cotizaciones->map(function ($cot) {
            $numContenedor = $cot->num_contenedor;
            $docCCP = $cot->doc_ccp;
            $doda = $cot->doda;
            $boletaLiberacion = $cot->boleta_liberacion;
            $cartaPorte = $cot->carta_porte;
            $boletaVacio = $cot->boleta_vacio;
            $docEir = $cot->doc_eir;

            $tipo = "";
            $eirPrimario = $cot->doc_eir;
            $cimaPrimario = $cot->cima;
            $eirSecundario = null;
            $cimaSecundario = null;

            $proveedorNombre = null;
            if ($cot->id_proveedor) {
                $proveedor = \App\Models\Proveedor::find($cot->id_proveedor);
                $proveedorNombre = $proveedor ? $proveedor->nombre : null;
            }

            if (!is_null($cot->referencia_full)) {
                $secundaria = \App\Models\Cotizaciones::where('referencia_full', $cot->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion')
                    ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $eirSecundario = $secundaria->DocCotizacion->doc_eir;
                    $cimaSecundario = $secundaria->DocCotizacion->cima;

                    $docCCP = ($docCCP && $secundaria->DocCotizacion->doc_ccp) ? true : false;
                    $doda = ($doda && $secundaria->DocCotizacion->doda) ? true : false;
                    $docEir = ($docEir && $secundaria->DocCotizacion->doc_eir) ? true : false;
                    $boletaLiberacion = ($boletaLiberacion && $secundaria->DocCotizacion->boleta_liberacion) ? true : false;
                    $cartaPorte = ($cartaPorte && $secundaria->carta_porte) ? true : false;
                    $boletaVacio = ($boletaVacio && $secundaria->img_boleta) ? true : false;
                    $numContenedor .= '/' . $secundaria->DocCotizacion->num_contenedor;
                }

                $tipo = 'Full';
            }

            return [
             "id" => $cot->id,
             "cliente" => $cot->cliente,
             "num_contenedor" => $numContenedor,
             "doc_ccp" => $docCCP,
             "boleta_liberacion" => $boletaLiberacion,
             "doda" => $doda,
             "carta_porte" => $cartaPorte,
             "boleta_vacio" => $boletaVacio,
             "doc_eir" => $docEir,
             "proveedor" => $proveedorNombre,
             "fecha_inicio" => $cot->fecha_inicio,
             "fecha_fin" => $cot->fecha_fin,
             "tipo" => $tipo,
             "cima" => $cot->cima, //  agrega esta línea
             "eir_primario" => $eirPrimario,
"eir_secundario" => $eirSecundario,
"cima_primario" => $cimaPrimario,
"cima_secundario" => $cimaSecundario,
];

        });


        return view('reporteria.documentos.index', compact('cotizaciones', 'clientes', 'subclientes', 'proveedores'));
    }




    public function advance_documentos(Request $request)
    {
        $clientes = Client::where('id_empresa', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $subclientes = Subclientes::where('id_empresa', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $id_client = $request->id_client;
        $id_subcliente = $request->id_subcliente;

        // Construir la consulta inicial
        $cotizaciones = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
            ->where('cotizaciones.id_empresa', auth()->user()->id_empresa)
            ->where('cotizaciones.estatus', 'Aprobada')
            ->select(
                'cotizaciones.id',
                'docum_cotizacion.num_contenedor',
                'docum_cotizacion.doc_ccp',
                'docum_cotizacion.boleta_liberacion',
                'docum_cotizacion.doda',
                'cotizaciones.carta_porte',
                'cotizaciones.img_boleta AS boleta_vacio',
                'docum_cotizacion.doc_eir',
                DB::raw('EXISTS(SELECT 1 FROM docum_cotizacion WHERE docum_cotizacion.id_cotizacion = cotizaciones.id) as documentos_existen')
            );

        if ($id_client !== null) {
            $cotizaciones = $cotizaciones->where('cotizaciones.id_cliente', $id_client);

            if ($id_subcliente !== null && $id_subcliente !== '') {
                $cotizaciones = $cotizaciones->where('cotizaciones.id_subcliente', $id_subcliente);
            }
        }

        // Obtener los resultados
        $cotizaciones = $cotizaciones->get();

        return view('reporteria.documentos.index', compact('cotizaciones', 'clientes', 'subclientes'));
    }




    public function export_documentos(Request $request)
    {

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 120);
        $fecha = date('Y-m-d');
        $fechaCarbon = Carbon::parse($fecha);
        $cotizacionIds = $request->input('selected_ids', []);

        if (empty($cotizacionIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No se seleccionaron cotizaciones.'
            ], 400);
        }

        $cotizaciones1 = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
            ->whereIn('cotizaciones.id', $cotizacionIds)
            ->where('cotizaciones.jerarquia', "Principal")
            ->select(
                'docum_cotizacion.num_contenedor',
                'docum_cotizacion.doc_ccp',
                'docum_cotizacion.boleta_liberacion',
                'docum_cotizacion.doda',
                'cotizaciones.carta_porte',
                'docum_cotizacion.boleta_vacio',
                'docum_cotizacion.doc_eir',
                'docum_cotizacion.cima',
                'cotizaciones.referencia_full'
            )
            ->get();

        $cotizacion = Cotizaciones::whereIn('id', $cotizacionIds)->get();

        $cotizaciones = $cotizaciones1->map(function ($cot) {
            $numContenedor = $cot->num_contenedor;
            $docCCP = $cot->doc_ccp;
            $doda = $cot->doda;
            $boletaLiberacion = $cot->boleta_liberacion;
            $cartaPorte = $cot->carta_porte;
            $boletaVacio = $cot->boleta_vacio;
            $docEir = $cot->doc_eir;
            $tipo = "";

            // Nuevos campos para EIR y CIMA individuales
            $eirPrimario = $docEir;
            $cimaPrimario = $cot->cima;
            $eirSecundario = null;
            $cimaSecundario = null;

            if (!is_null($cot->referencia_full)) {
                $secundaria = \App\Models\Cotizaciones::where('referencia_full', $cot->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion')
                    ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $eirSecundario = $secundaria->DocCotizacion->doc_eir;
                    $cimaSecundario = $secundaria->DocCotizacion->cima;

                    $docCCP = ($docCCP && $secundaria->DocCotizacion->doc_ccp) ? true : false;
                    $doda = ($doda && $secundaria->DocCotizacion->doda) ? true : false;
                    $docEir = ($docEir && $secundaria->DocCotizacion->doc_eir) ? true : false;
                    $boletaLiberacion = ($boletaLiberacion && $secundaria->DocCotizacion->boleta_liberacion) ? true : false;
                    $cartaPorte = ($cartaPorte && $secundaria->carta_porte) ? true : false;
                    $boletaVacio = ($boletaVacio && $secundaria->img_boleta) ? true : false;

                    $numContenedor .= ' / ' . $secundaria->DocCotizacion->num_contenedor;
                    $tipo = "Full";
                }
            }

            return [
                "id" => $cot->id,
                "cliente" => $cot->cliente ?? null,
                "num_contenedor" => $numContenedor,
                "doc_ccp" => $docCCP,
                "boleta_liberacion" => $boletaLiberacion,
                "doda" => $doda,
                "carta_porte" => $cartaPorte,
                "boleta_vacio" => $boletaVacio,
                "doc_eir" => $docEir,
                "id_proveedor" => $cot->id_proveedor ?? null,
                "fecha_inicio" => $cot->fecha_inicio ?? null,
                "fecha_fin" => $cot->fecha_fin ?? null,
                "tipo" => $tipo,
                "cima" => $cot->cima,

                // 👇 Agregados para PDF dinámico
                "eir_primario" => $eirPrimario,
                "eir_secundario" => $eirSecundario,
                "cima_primario" => $cimaPrimario,
                "cima_secundario" => $cimaSecundario,
            ];
        });


        $user = auth()->user();

        if ($request->fileType === 'xlsx') {
            return Excel::download(
                new \App\Exports\DocumentosExport($cotizaciones, $fechaCarbon, $cotizacion, $user),
                'documentos.xlsx'
            );
        }

        if ($request->fileType === 'pdf') {
            $pdf = PDF::loadView('reporteria.documentos.pdf', compact('cotizaciones', 'fechaCarbon', 'cotizacion', 'user'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('documentos.pdf');
        }

        return response()->json([
            'success' => false,
            'message' => 'Tipo de archivo no soportado.'
        ], 400);
    }




    // ==================== L I Q U I D A D O S CXC ====================
    public function index_liquidados_cxc()
    {

        $clientes = Client::where('id_empresa', '=', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $subclientes = Subclientes::where('id_empresa', '=', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        return view('reporteria.liquidados.cxc.index', compact('clientes', 'subclientes'));
    }

    public function advance_liquidados_cxc(Request $request)
    {

        $clientes = Client::where('id_empresa', '=', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $proveedores = Proveedor::where('id_empresa', '=', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();


        $id_client = $request->id_client;
        $id_subcliente = $request->id_subcliente;

        $cotizaciones = [];
        $registrosBanco = [];

        if ($id_client !== null) {
            $query = Cotizaciones::where('id_empresa', '=', auth()->user()->id_empresa)
                ->where('id_cliente', $id_client)
                ->where(function ($query) {
                    $query->where('estatus', '=', 'Aprobada')
                        ->orWhere('estatus', '=', 'Finalizado');
                })
                ->where('restante', '<=', 0);

            if ($id_subcliente !== null && $id_subcliente !== '') {
                $query->where('id_subcliente', $id_subcliente);
            }

            $cotizaciones = $query->get();

            // Obtener los números de contenedor de las cotizaciones seleccionadas
            $contenedores = $cotizaciones->pluck('DocCotizacion.num_contenedor')->toArray();

            // Buscar en banco_dinero donde los contenedores contengan los números de las cotizaciones
            $registrosBanco = BancoDinero::where('tipo', 'Entrada')
            ->whereJsonContains('contenedores', function ($query) use ($contenedores) {
                foreach ($contenedores as $contenedor) {
                    $query->orWhereJsonContains('contenedores->num_contenedor', $contenedor);
                }
            })->get();
        }

        return view('reporteria.liquidados.cxc.index', compact('clientes', 'cotizaciones', 'registrosBanco'));
    }

    public function export_liquidados_cxc(Request $request)
    {
        $fecha = date('Y-m-d');
        $fechaCarbon = Carbon::parse($fecha);

        // Obtener los IDs de cotizaciones seleccionadas desde la solicitud
        $cotizacionIds = $request->input('selected_ids', []);
        if (empty($cotizacionIds)) {
            return redirect()->back()->with('error', 'No se seleccionaron cotizaciones.');
        }

        // Obtener las cotizaciones seleccionadas
        $cotizaciones = Cotizaciones::whereIn('id', $cotizacionIds)->get();

        // Obtener los números de contenedor de las cotizaciones seleccionadas
        $contenedores = $cotizaciones->pluck('DocCotizacion.num_contenedor')->toArray();

        // Obtener los registros de BancoDinero con tipo 'Entrada' relacionados a los números de contenedor
        $registrosBanco = BancoDinero::where('tipo', 'Entrada')
            ->whereJsonContains('contenedores', function ($query) use ($contenedores) {
                foreach ($contenedores as $contenedor) {
                    $query->orWhereJsonContains('contenedores->num_contenedor', $contenedor);
                }
            })->get();

        $bancos_oficiales = Bancos::where('tipo', '=', 'Oficial')->get();
        $bancos_no_oficiales = Bancos::where('tipo', '=', 'No Oficial')->get();
        $user = User::where('id', '=', auth()->user()->id)->first();
        $cotizacion_first = Cotizaciones::where('id', $cotizacionIds)->first();

        if ($request->fileType == "xlsx") {
            Excel::store(new \App\Exports\LiquidadosCxcExport($cotizaciones, $fechaCarbon, $bancos_oficiales, $bancos_no_oficiales, $registrosBanco, $user, $cotizacion_first), 'liquidados_cxc.xlsx', 'public');
            return Response::download(storage_path('app/public/liquidados_cxc.xlsx'), "liquidados_cxp.xlsx")->deleteFileAfterSend(true);
        } else {
            // Generar el PDF con los datos necesarios
            $pdf = PDF::loadView('reporteria.liquidados.cxc.pdf', compact('cotizaciones', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales', 'registrosBanco', 'user', 'cotizacion_first'))
                ->setPaper([0, 0, 595, 1200], 'landscape');

            // Generar el nombre del archivo
            $fileName = 'cxc_' . implode('_', $cotizacionIds) . '.pdf';

            // Guardar el PDF en la carpeta storage
            $pdf->save(storage_path('app/public/' . $fileName));

            // Devolver el archivo PDF como respuesta
            $filePath = storage_path('app/public/' . $fileName);
            return Response::download($filePath, $fileName)->deleteFileAfterSend(true);
        }
    }

    // ==================== L I Q U I D A D O S CXP ====================

    public function index_liquidados_cxp()
    {

        $proveedores = Proveedor::where('id_empresa', '=', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        return view('reporteria.liquidados.cxp.index', compact('proveedores'));
    }

    public function advance_liquidados_cxp(Request $request)
    {

        $proveedores = Proveedor::where('id_empresa', '=', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $id_proveedor = $request->id_proveedor;

        if ($id_proveedor !== null) {
            $cotizaciones = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
            ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
            ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
            ->where('asignaciones.id_camion', '=', null)
            ->where(function ($query) {
                $query->where('cotizaciones.estatus', '=', 'Aprobada')
                    ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
            })
            ->where('asignaciones.id_proveedor', '=', $id_proveedor) //checar despues de corregir cxp detallado
            ->where('cotizaciones.prove_restante', '=', 0)
            ->select('asignaciones.*', 'docum_cotizacion.num_contenedor', 'docum_cotizacion.id_cotizacion', 'cotizaciones.origen', 'cotizaciones.destino', 'cotizaciones.estatus', 'cotizaciones.prove_restante')
            ->get();
            $proveedor_cxp = Proveedor::where('id', '=', $request->id_proveedor)->first();
        }

        return view('reporteria.liquidados.cxp.index', compact('proveedores', 'cotizaciones', 'proveedor_cxp'));
    }

    public function export_liquidados_cxp(Request $request)
    {
        $fecha = date('Y-m-d');
        $fechaCarbon = Carbon::parse($fecha);

        $cotizacionIds = $request->input('selected_ids', []);
        if (empty($cotizacionIds)) {
            return redirect()->back()->with('error', 'No se seleccionaron cotizaciones.');
        }

        // Obtener las cotizaciones seleccionadas
        $cotizaciones = Asignaciones::whereIn('id', $cotizacionIds)->get();

        // Obtener los números de contenedor relacionados a las cotizaciones seleccionadas
        $contenedores = $cotizaciones->pluck('DocumCotizacion.num_contenedor')->toArray();

        // Obtener los registros de BancoDinero con tipo 'Salida' relacionados a los números de contenedor
        $registrosBanco = BancoDinero::where('tipo', 'Salida')
            ->whereJsonContains('contenedores', function ($query) use ($contenedores) {
                foreach ($contenedores as $contenedor) {
                    $query->orWhereJsonContains('contenedores->num_contenedor', $contenedor);
                }
            })->get();

        $bancos_oficiales = Bancos::where('tipo', '=', 'Oficial')->get();
        $bancos_no_oficiales = Bancos::where('tipo', '=', 'No Oficial')->get();

        $user = User::where('id', '=', auth()->user()->id)->first();
        $cotizacion = Asignaciones::where('id', $cotizacionIds)->first();

        if ($request->fileType == "xlsx") {
            Excel::store(new \App\Exports\LiquidadosCxpExport($cotizaciones, $fechaCarbon, $bancos_oficiales, $bancos_no_oficiales, $registrosBanco, $user, $cotizacion), 'liquidados_cxp.xlsx', 'public');
            return Response::download(storage_path('app/public/liquidados_cxp.xlsx'), "liquidados_cxp.xlsx")->deleteFileAfterSend(true);
        } else {
            // Generar el PDF con los datos necesarios
            $pdf = PDF::loadView('reporteria.liquidados.cxp.pdf', compact('cotizaciones', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales', 'registrosBanco', 'user', 'cotizacion'))
                ->setPaper('a4', 'landscape');

            $fileName = 'cxp_' . implode('_', $cotizacionIds) . '.pdf';

            // Guardar el PDF en la carpeta storage
            $pdf->save(storage_path('app/public/' . $fileName));

            // Devolver el archivo PDF como respuesta
            $filePath = storage_path('app/public/' . $fileName);
            return Response::download($filePath, $fileName)->deleteFileAfterSend(true);
        }
    }

    // Dentro de ReporteriaController.php

    public function index_gxp(Request $request)
    {
        $gastos = GastosOperadores::with([
            'Asignaciones.Proveedor',
            'Asignaciones.Contenedor.Cotizacion.Cliente',
            'Asignaciones.Contenedor.Cotizacion.Subcliente'
        ])
        ->whereHas('Asignaciones', fn ($q) => $q->where('id_empresa', auth()->user()->id_empresa))
        ->where('estatus', '!=', 'Pagado')
        ->get();

        $data = $gastos->map(function ($g) {
            $asignacion = $g->Asignaciones;

            return [
                'id' => $g->id,
                'operador' => optional($g->Operador)->nombre ?? '-',
                'cliente' => optional($asignacion->Contenedor->Cotizacion->Cliente)->nombre ?? '-',
                'subcliente' => optional($asignacion->Contenedor->Cotizacion->Subcliente)->nombre ?? '-',
                'num_contenedor' => optional($asignacion->Contenedor)->num_contenedor ?? '-',
                'monto' => $g->cantidad ?? 0,
                'motivo' => $g->tipo ?? 'Gasto pendiente',
                'fecha_inicio' => $asignacion?->fecha_inicio,
                'fecha_fin' => $asignacion?->fecha_fin,
                'fecha_movimiento' => $g->created_at,
                'fecha_aplicacion' => $g->fecha_pago,
            ];
        });

        return view('reporteria.gxp.index', ['gastos' => $data]);
    }



    public function getGastosPorPagarData()
    {
        $idEmpresa = auth()->user()->id_empresa;

        $gastos = GastosOperadores::with([
            'Asignaciones.Contenedor.Cotizacion.Cliente',
            'Asignaciones.Contenedor.Cotizacion.Subcliente'
        ])
        ->whereHas('Asignaciones', function ($q) use ($idEmpresa) {
            $q->where('id_empresa', $idEmpresa);
        })
        ->where('estatus', '!=', 'Pagado')
        ->get();

        $data = $gastos->map(function ($g) {
            $asignacion = $g->Asignaciones;

            $proveedorNombre = '-';
            if ($asignacion && $asignacion->id_proveedor) {
                $proveedor = \App\Models\Proveedor::find($asignacion->id_proveedor);
                $proveedorNombre = $proveedor?->nombre ?? '-';
            }

            return [
                'id' => $g->id,
                'operador' => optional($g->Operador)->nombre ?? '-',
                'cliente' => optional($asignacion?->Contenedor?->Cotizacion?->Cliente)->nombre ?? '-',
                'subcliente' => optional($asignacion?->Contenedor?->Cotizacion?->Subcliente)->nombre ?? '-',
                'num_contenedor' => optional($asignacion?->Contenedor)->num_contenedor ?? '-',
                'monto' => $g->cantidad ?? 0,
                'motivo' => $g->tipo ?? 'Gasto pendiente',
                'fecha_movimiento' => $g->created_at ? Carbon::parse($g->created_at)->format('Y-m-d') : null,
                'fecha_aplicacion' => $g->fecha_pago ? Carbon::parse($g->fecha_pago)->format('Y-m-d') : null,
                'fecha_inicio' => $asignacion?->fecha_inicio ? Carbon::parse($asignacion->fecha_inicio)->format('Y-m-d') : null,
                'fecha_fin' => $asignacion?->fecha_fin ? Carbon::parse($asignacion->fecha_fin)->format('Y-m-d') : null,
            ];
        });

        return response()->json($data);
    }


    public function exportGastosPorPagar(Request $request)
    {
        try {
            $ids = $request->input('selected_ids', []);
            $fileType = $request->input('fileType');

            $export = new \App\Exports\GastosPorPagarExport($ids);

            if ($fileType === 'xlsx') {
                return \Maatwebsite\Excel\Facades\Excel::download($export, 'gastos_por_pagar.xlsx');
            }

            if ($fileType === 'pdf') {
                $gastos = collect($export->getGastosData())->map(function ($g) {
                    return is_array($g) ? $g : (array) $g;
                });

                return PDF::loadView('reporteria.gxp.pdf', [
    'gastos' => $gastos,
    'empresa' => auth()->user()->Empresa->nombre ?? 'Sin Empresa'
])->setPaper('a4', 'landscape')->download('gastos_por_pagar.pdf');


            }

            return response()->json(['error' => 'Tipo de archivo no válido'], 400);

        } catch (\Throwable $e) {
            Log::error('Error al exportar gastos', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    public function indexVXC()
    {
        $user = auth()->user();

        if (!$user->hasRole('Proveedor')) {
            abort(403, 'No autorizado');
        }

        $rfc = $user->rfc;

        $cotizaciones = Cotizaciones::whereHas('DocCotizacion.Asignaciones.Proveedor', function ($query) use ($rfc) {
            $query->where('rfc', $rfc);
        })
        ->where('restante', '>', 0)
        ->where('estatus', '!=', 'Cancelada')
        ->with([
            'DocCotizacion.Asignaciones.Proveedor',
            'DocCotizacion',
            'Subcliente'
        ])
        ->get();

        $totalGenerado = $cotizaciones->sum('restante');

        $retenido = $cotizaciones->filter(function ($c) {
            return !$c->carta_porte || !$c->carta_porte_xml;
        })->sum('restante');

        $pagoNeto = $cotizaciones->filter(function ($c) {
            return $c->carta_porte && $c->carta_porte_xml;
        })->sum('restante');

        return view('reporteria.vxc.index', compact('cotizaciones', 'totalGenerado', 'retenido', 'pagoNeto'));
    }




    public function dataVXC()
    {
        $user = auth()->user();

        if (!$user->hasRole('Proveedor')) {
            abort(403, 'No autorizado');
        }

        $rfc = $user->rfc;

        $cotizaciones = Cotizaciones::whereHas('DocCotizacion.Asignaciones.Proveedor', function ($query) use ($rfc) {
            $query->where('rfc', $rfc);
        })
        ->where('restante', '>', 0)
        ->where('estatus', '!=', 'Cancelada')
        ->with([
            'DocCotizacion.Asignaciones.Proveedor',
            'DocCotizacion',
            'Subcliente'
        ])
        ->get()
        ->map(function ($c) {
            return [
                'id' => $c->id,
                'restante' => $c->restante,
                'tipo_viaje' => $c->tipo_viaje ?? 'Subcontratado',
                'estatus' => $c->estatus === 'Aprobada' ? 'En curso' : $c->estatus,
                'subcliente' => $c->Subcliente->nombre ?? '-',
                'num_contenedor' => $c->DocCotizacion->num_contenedor ?? '-',
                'proveedor' => $c->DocCotizacion->Asignaciones->Proveedor->nombre ?? '-',
                'carta_porte' => $c->carta_porte ?? false,
                'carta_porte_xml' => $c->carta_porte_xml ?? false
            ];
        });

        return response()->json($cotizaciones);
    }


    public function exportarVXC(Request $request)
    {
        try {
            $tipo = $request->input('tipo');

            $cotizaciones = collect($request->input('cotizaciones', []));
            $totalGenerado = $request->input('totales.totalGenerado', 0);
            $retenido = $request->input('totales.retenido', 0);
            $pagoNeto = $request->input('totales.pagoNeto', 0);

            if ($tipo === 'pdf') {
                $pdf = PDF::loadView('reporteria.vxc.pdf', [
                    'cotizaciones' => $cotizaciones,
                    'totalGenerado' => $totalGenerado,
                    'retenido' => $retenido,
                    'pagoNeto' => $pagoNeto
                ])->setPaper('A4', 'landscape');

                return $pdf->download('viajes_por_cobrar.pdf');
            }

            if ($tipo === 'excel') {
                return Excel::download(
                    new \App\Exports\VXCExport($cotizaciones, $totalGenerado, $retenido, $pagoNeto),
                    'viajes_por_cobrar.xlsx'
                );
            }

            return response()->json(['error' => 'Tipo de exportación no válido.'], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Error interno',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }





}
