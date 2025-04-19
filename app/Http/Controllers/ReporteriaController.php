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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
use DB;
use PDF;
use Excel;
use Auth;


class ReporteriaController extends Controller
{
    public function index(){

        //$clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $clientes = Client::join('client_empresa as ce','clients.id','=','ce.id_client')
                            ->where('ce.id_empresa',Auth::User()->id_empresa)
                            ->where('is_active',1)
                            ->orderBy('nombre')->get();

        $clientesIds = $clientes->pluck('id');

        $subclientes = Subclientes::whereIn('id_cliente' ,$clientesIds)->orderBy('created_at', 'desc')->get();
        $proveedores = Proveedor::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        return view('reporteria.cxc.index', compact('clientes', 'subclientes', 'proveedores'));
    }

    public function advance(Request $request) {
        // Obtener los datos de los filtros
        $id_client = $request->input('id_client');
        $id_subcliente = $request->input('id_subcliente');
        $id_proveedor = $request->input('id_proveedor');
    
        // Obtener los clientes, subclientes y proveedores para mostrarlos en el formulario
        //$clientes = Client::where('id_empresa', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $clientes = Client::join('client_empresa as ce','clients.id','=','ce.id_client')
                            ->where('ce.id_empresa',Auth::User()->id_empresa)
                            ->where('is_active',1)
                            ->orderBy('nombre')->get();

        $clientesIds = $clientes->pluck('id');

        $subclientes = Subclientes::whereIn('id_cliente', $clientesIds)->orderBy('created_at', 'desc')->get();
        $proveedores = Proveedor::where('id_empresa', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
    
        // Inicializar la consulta de cotizaciones
        $cotizaciones = Cotizaciones::where('id_empresa', auth()->user()->id_empresa)
            ->where(function ($query) {
                $query->where('estatus', '=', 'Aprobada')
                    ->orWhere('estatus', '=', 'Finalizado');
            })
            ->where('estatus_pago', '=', '0')
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
    
        // Ejecutar la consulta
        $cotizaciones = $cotizaciones->get();
    
        // Devolver la vista con los filtros y las cotizaciones
        return view('reporteria.cxc.index', compact('clientes', 'subclientes', 'proveedores', 'cotizaciones'));
    }
    

    public function getSubclientes($clienteId){
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

        $cotizaciones = Cotizaciones::whereIn('id', $cotizacionIds)->get();
        if(in_array($cotizacion->id_empresa,[2,6])){
            $bancos_oficiales = Bancos::where('id_empresa', '=', $cotizacion->id_empresa)->get();
            $bancos_no_oficiales = Bancos::where('id_empresa', '=', $cotizacion->id_empresa)->get();
        }else{
            $bancos_oficiales = Bancos::where('tipo', '=', 'Oficial')->get();
            $bancos_no_oficiales = Bancos::where('tipo', '=', 'No Oficial')->get();
        }
        

        if($request->fileType == "xlsx"){
            Excel::store(new CxcExport($cotizaciones, $fechaCarbon, $bancos_oficiales, $bancos_no_oficiales, $cotizacion, $user), 'cotizaciones_cxc.xlsx','public');
            return Response::download(storage_path('app/public/cotizaciones_cxc.xlsx'), "cxc.xlsx")->deleteFileAfterSend(true);
        }else{
            $pdf = PDF::loadView('reporteria.cxc.pdf', compact('cotizaciones', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales', 'cotizacion', 'user'))->setPaper([0, 0, 595, 1200], 'landscape');

            // Generar el nombre del archivo
            $fileName = 'cxc_' . implode('_', $cotizacionIds) . '.pdf';
            // Guardar el PDF en la carpeta storage
            $pdf->save(storage_path('app/public/' . $fileName));
    
            // Devolver el archivo PDF como respuesta
            $filePath = storage_path('app/public/' . $fileName);
            return Response::download($filePath, $fileName)->deleteFileAfterSend(true);
        }
    }

    public function exportExcel(request $r){
        $collection = Collect(json_decode($r->dataExport));
        $procesedData = $collection->map(function($c){
            $fecha = optional($c->doc_cotizacion->asignaciones)->fehca_inicio_guard;
            return ["id"=>$c->id,
                    "fecha"=> ($fecha != null) ? Carbon::parse($fecha)->format('d/m/Y') : "Sin Fecha",
                    "cliente"=>$c->cliente->nombre,
                    "subcliente"=>$c->subcliente->nombre ?? "-",
                    "origen"=>$c->origen,
                    "dest"=>$c->destino,
                    "contenedor"=>$c->doc_cotizacion->num_contenedor,
                    "estatus"=>$c->estatus
                ];
        });

        Excel::store(new CotizacionesCXC(json_decode($procesedData)), 'cotizaciones_cxc.xlsx','public');
        return Response::download(storage_path('app/public/cotizaciones_cxc.xlsx'), "cxc.xlsx")->deleteFileAfterSend(true);
    }

    public function buildExcelData($excelData, $report = 0){
        $collection = Collect(json_decode($excelData));
        $procesedData = $collection->map(function($c) use ($report){
            switch($report){
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
                    if($c->total_proveedor == NULL){
                        $utilidad = $c->total - $c->pago_operador;
                    }elseif($c->total_proveedor != NULL){
                        $utilidad = $c->total - $c->total_proveedor;
                    }else{
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

    public function exportGenericExcel(Request $r){
        $fileName = "generic_excel_".uniqid().".xlsx";
        $data = self::buildExcelData($r->dataExport, $r->reportNumber);
        $headers = json_decode($r->reportHeaders);

        Excel::store(new GenericExport($headers,$data), $fileName, 'public');
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
    $clientes = Client::join('client_empresa as ce','clients.id','=','ce.id_client')
                            ->where('ce.id_empresa',Auth::User()->id_empresa)
                            ->where('is_active',1)
                            ->orderBy('nombre')->get();

    $clientesIds = $clientes->pluck('id');

    $subclientes = Subclientes::whereIn('id_cliente' ,$clientesIds)->orderBy('created_at', 'desc')->get();

    return view('reporteria.cxp.index', compact('proveedores', 'clientes','subclientes'));
}
public function advance_cxp(Request $request)
{
    // Obtener proveedores y clientes de la empresa actual
    $proveedores = Proveedor::where('id_empresa', '=', auth()->user()->id_empresa)
                             ->orderBy('created_at', 'desc')
                             ->get();

    $clientes = Client::join('client_empresa as ce','clients.id','=','ce.id_client')
                        ->where('ce.id_empresa',Auth::User()->id_empresa)
                        ->where('is_active',1)
                        ->orderBy('nombre')->get();

    $clientesIds = $clientes->pluck('id');                        

    $subclientes = Subclientes::whereIn('id_cliente' ,$clientesIds)->orderBy('created_at', 'desc')->get();

    // Obtener el ID del proveedor y del cliente desde la solicitud
    $id_proveedor = $request->input('id_proveedor');

    // Mostrar advertencia si no se seleccionó proveedor
    $showWarning = empty($id_proveedor);

    // Inicializar variables
    $cotizaciones = [];
    $proveedor_cxp = null;

    // Si se seleccionó un proveedor, filtrar las cotizaciones correspondientes
    if ($id_proveedor) {
        $cotizaciones = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
            ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
            ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
            ->whereNull('asignaciones.id_camion') // Verificar que el camión sea NULL
            ->where(function($query) {
                $query->where('cotizaciones.estatus', '=', 'Aprobada')
                      ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
            })
            ->where('asignaciones.id_proveedor', '=', $id_proveedor)
            ->where('cotizaciones.prove_restante', '>', 0)
            ->select(
                'asignaciones.*',
                'docum_cotizacion.num_contenedor',
                'docum_cotizacion.id_cotizacion',
                'cotizaciones.origen',
                'cotizaciones.destino',
                'cotizaciones.estatus',
                'cotizaciones.prove_restante'
            )
            ->get();

        // Obtener datos del proveedor seleccionado
        $proveedor_cxp = Proveedor::find($id_proveedor);
    }

    // Retornar la vista con los datos necesarios
    return view('reporteria.cxp.index', compact('proveedores', 'clientes', 'cotizaciones', 'proveedor_cxp', 'showWarning', 'id_proveedor','subclientes'));
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
    $cotizaciones = Asignaciones::with('Proveedor.CuentasBancarias')->whereIn('id', $cotizacionIds)->get();
    $bancos_oficiales = Bancos::where('tipo', '=', 'Oficial')->get();
    $bancos_no_oficiales = Bancos::where('tipo', '=', 'No Oficial')->get();

    // Obtener la primera cotización (si es necesario)
    $cotizacion = Asignaciones::where('id', $cotizacionIds)->first();

    // Obtener los datos del usuario autenticado
    $user = User::where('id', '=', auth()->user()->id)->first();

    // Generar el archivo Excel o PDF según el tipo de archivo solicitado
    if ($request->fileType == "xlsx") {
        // Generar el archivo Excel y almacenarlo
        Excel::store(new CxpExport($cotizaciones, $fechaCarbon, $bancos_oficiales, $bancos_no_oficiales, $cotizacion, $user), 'cotizaciones_cxp.xlsx', 'public');
        
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
    public function index_viajes(){

        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $subclientes = Subclientes::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $proveedores = Proveedor::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $estatus = \DB::table('cotizaciones')
    ->where('id_empresa', auth()->user()->id_empresa)
    ->select('estatus')
    ->distinct()
    ->pluck('estatus');
    $asignaciones = Asignaciones::with([
        'Contenedor.Cotizacion.Cliente',
        'Contenedor.Cotizacion.Subcliente',
        'Proveedor'
    ])
    ->where('id_empresa', auth()->user()->id_empresa)
    ->get();

    $viajesData = $asignaciones->map(function ($a) {
        return [
            'id' => $a->id,
            'cliente' => $a->Contenedor->Cotizacion->Cliente->nombre ?? '-',
            'subcliente' => $a->Contenedor->Cotizacion->Subcliente->nombre ?? '-',
            'origen' => $a->Contenedor->Cotizacion->origen ?? '',
            'destino' => $a->Contenedor->Cotizacion->destino ?? '',
            'contenedor' => $a->Contenedor->num_contenedor ?? '',
            'fecha_salida' => \Carbon\Carbon::parse($a->fehca_inicio_guard)->format('d-m-Y'),
            'fecha_llegada' => \Carbon\Carbon::parse($a->fehca_fin_guard)->format('d-m-Y'),
            'estatus' => $a->Contenedor->Cotizacion->estatus ?? '',
            'proveedor' => $a->Proveedor->nombre ?? '-',
        ];
    })->toArray();

    return view('reporteria.asignaciones.index', compact('clientes', 'subclientes', 'proveedores', 'estatus', 'asignaciones', 'viajesData'));
    }
    public function getViajesFiltrados(Request $request)
    {
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');
    
        $asignaciones = Asignaciones::with([
            'Contenedor.Cotizacion.Cliente',
            'Contenedor.Cotizacion.Subcliente',
            'Proveedor'
        ])
        ->where('id_empresa', auth()->user()->id_empresa)
        ->whereBetween('fehca_inicio_guard', [
            Carbon::parse($fechaInicio)->startOfDay(),
            Carbon::parse($fechaFin)->endOfDay()
        ])
        ->get();
    
        $viajesData = $asignaciones->map(function ($a) {
            return [
                'id' => $a->id,
                'cliente' => $a->Contenedor->Cotizacion->Cliente->nombre ?? '-',
                'subcliente' => $a->Contenedor->Cotizacion->Subcliente->nombre ?? '-',
                'origen' => $a->Contenedor->Cotizacion->origen ?? '',
                'destino' => $a->Contenedor->Cotizacion->destino ?? '',
                'contenedor' => $a->Contenedor->num_contenedor ?? '',
                'fecha_salida' => optional($a->fehca_inicio_guard)->format('d-m-Y'),
                'fecha_llegada' => optional($a->fehca_fin_guard)->format('d-m-Y'),
                'estatus' => $a->Contenedor->Cotizacion->estatus ?? '',
                'proveedor' => $a->Proveedor->nombre ?? '-',
            ];
        });
    
        return response()->json($viajesData);
    }


    public function advance_viajes(Request $request)
    {
        $clientes = Client::where('id_empresa', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $subclientes = Subclientes::where('id_empresa', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $proveedores = Proveedor::where('id_empresa', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $estatus = \DB::table('cotizaciones')
            ->where('id_empresa', auth()->user()->id_empresa)
            ->select('estatus')
            ->distinct()
            ->pluck('estatus');
    
        $id_client = $request->id_client;
        $id_subcliente = $request->id_subcliente;
        $id_proveedor = $request->id_proveedor;
    
        $asignaciones = Asignaciones::
            join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
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
            return [
                'id' => $a->id,
                'cliente' => $a->Contenedor->Cotizacion->Cliente->nombre ?? '-',
                'subcliente' => $a->Contenedor->Cotizacion->Subcliente->nombre ?? '-',
                'origen' => $a->Contenedor->Cotizacion->origen ?? '',
                'destino' => $a->Contenedor->Cotizacion->destino ?? '',
                'contenedor' => $a->Contenedor->num_contenedor ?? '',
                'fecha_salida' => \Carbon\Carbon::parse($a->fehca_inicio_guard)->format('d-m-Y'),
                'fecha_llegada' => \Carbon\Carbon::parse($a->fehca_fin_guard)->format('d-m-Y'),
                'estatus' => $a->Contenedor->Cotizacion->estatus ?? '',
                'proveedor' => $a->Proveedor->nombre ?? '-', // ✅ Añádelo aquí también
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

    // ✅ Detectar si se exporta todo o solo selección
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

    public function index_utilidad(){

        return view('reporteria.utilidad.index');
    }

    public function advance_utilidad(Request $request) {
        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $subclientes = Subclientes::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $contenedores = DocumCotizacion::
        join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
        ->where('docum_cotizacion.num_contenedor' ,'!=', NULL)
        ->where('docum_cotizacion.id_empresa' ,'=',auth()->user()->id_empresa)
        ->where('cotizaciones.estatus' ,'=', 'Aprobada')
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
            $asignaciones = $asignaciones->where('asignaciones.fecha_inicio', '>=', $inicio)
                                         ->where('asignaciones.fecha_inicio', '<=', $fin);
        }

        // Obtener los resultados
        $asignaciones = $asignaciones->get();

        $fechaDe = $request->query('fecha_de');
        $fechaHasta = $request->query('fecha_hasta');

        return view('reporteria.utilidad.index', compact('asignaciones', 'clientes', 'subclientes', 'contenedores', 'fechaDe', 'fechaHasta'));
    }

    public function getContenedorUtilidad(Request $r){
        $fechaI = $r->startDate.' 00:00:00';
        $fechaF = $r->endDate.' 00:00:00';
        $datos = DB::select('select c.id as id_cotizacion,a.id_camion,dc.num_contenedor,cl.nombre as cliente, op.nombre Operador, a.sueldo_viaje,dinero_viaje, pr.nombre as Proveedor,total_proveedor,
        c.total, c.estatus,estatus_pago,c.fecha_pago,a.fecha_inicio,a.fecha_fin,DATEDIFF(a.fecha_fin,a.fecha_inicio) as tiempo_viaje
        from cotizaciones c
        left join clients cl on c.id_cliente = cl.id
        left join docum_cotizacion dc on c.id = dc.id_cotizacion
        left join asignaciones a on dc.id = a.id_contenedor
        left join operadores op on a.id_operador = op.id
        left join proveedores pr on a.id_proveedor = pr.id
        where a.fecha_inicio between '."'".$fechaI."'".' and '."'".$fechaF."' and c.estatus != 'Cancelada' and c.id_empresa = ".Auth::User()->id_empresa);

        $Info = [];
        foreach($datos as $d){
            $diferido = 
            GastosDiferidosDetalle::join('gastos_generales as g','gastos_diferidos_detalle.id_gasto','=','g.id')
            ->whereBetween('fecha_gasto',[$d->fecha_inicio,$d->fecha_fin])
            ->where('gastos_diferidos_detalle.id_equipo',$d->id_camion)
            ->where('g.id_empresa',Auth::User()->id_empresa)
            ->get();

            $detalleGastos = null;

            foreach($diferido as $d1){
                $detalleGastos [] = ["fecha_gasto" => $d1->fecha_gasto, 
                                    "monto_gasto" => $d1->gasto_dia, 
                                    "tipo_gasto" => "DIFERIDO", 
                                    "motivo_gasto" => $d1->motivo
                                ];
            }

            $gastosExtra = GastosExtras::where('id_cotizacion',$d->id_cotizacion)->get();
            $gastosOperador = GastosOperadores::where('id_cotizacion',$d->id_cotizacion)->get();

            foreach($gastosExtra as $ge){
                $detalleGastos [] = ["fecha_gasto" => $ge->created_at, 
                "monto_gasto" => $ge->monto, 
                "tipo_gasto" => "Gasto Extra", 
                "motivo_gasto" => $ge->descripcion
            ];
            }

            foreach($gastosOperador as $go){
                $detalleGastos [] = ["fecha_gasto" => $go->fecha_pago, 
                "monto_gasto" => $go->cantidad, 
                "tipo_gasto" => "Gastos Viaje", 
                "motivo_gasto" => $go->tipo
            ];
            }

            $pagoOperacion = (is_null($d->Proveedor)) ? $d->sueldo_viaje : $d->total_proveedor;
            $gastosDiferidos = $diferido->sum('gasto_dia');
            $Columns = [
                        "numContenedor" => $d->num_contenedor,
                        "cliente" => $d->cliente,
                        "precioViaje" => $d->total + $gastosExtra->sum('monto'),
                        "transportadoPor" => (is_null($d->Proveedor)) ? 'Operador' : 'Proveedor',
                        "operadorOrProveedor" => (is_null($d->Proveedor)) ? $d->Operador : $d->Proveedor,
                        "pagoOperacion" => $pagoOperacion,
                        "gastosExtra" => $gastosExtra->sum('monto'),
                        "gastosViaje" => $gastosOperador->sum('cantidad'),
                        "viajeInicia"=> $d->fecha_inicio,
                        "viajeTermina"=> $d->fecha_fin, 
                        "estatusViaje" => $d->estatus,     
                        "estatusPago" => ($d->estatus_pago == 1) ? 'Pagado' : 'Por Cobrar',
                        "gastosDiferidos" =>  $gastosDiferidos,
                        "detalleGastos" => $detalleGastos,
                        "utilidad" => $d->total  - $pagoOperacion - $gastosDiferidos - $gastosExtra->sum('monto') - $gastosOperador->sum('cantidad')             
                        ];
            $Info[] = $Columns;
        }

        return $Info;

    }

    public function export_utilidad(Request $request){
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');

        $fecha = date('Y-m-d');
        $fechaCarbon = Carbon::parse($fecha);
        $cotizaciones = Collect($request->rowData);//Asignaciones::whereIn('id', $cotizacionIds)->get();
        $cotizacion = [];//Asignaciones::where('id', $cotizacionIds)->first();
        $user = User::where('id', '=', auth()->user()->id)->first();

        $gastos = GastosGenerales::where('id_empresa', auth()->user()->id_empresa)
                                ->where('diferir_gasto',0)
                                ->whereBetween('fecha',[$fechaInicio,$fechaFin])->sum('monto1');
       // $gastos = [];

        $utilidad = $cotizaciones->sum('utilidad');

        $totalRows = $request->totalRows;
        $selectedRows = $cotizaciones->count();
  

        if($request->fileType == "xlsx"){
            Excel::store(new \App\Exports\UtilidadExport($cotizaciones, $fechaCarbon, $cotizacion, $user, []), 'utilidad.xlsx','public');
            return Response::download(storage_path('app/public/utilidad.xlsx'), "utilidad.xlsx")->deleteFileAfterSend(true);
        }else{
        $pdf = PDF::loadView('reporteria.utilidad.pdf', 
                compact('cotizaciones','utilidad', 'fechaInicio','fechaFin', 'cotizacion', 'user', 'gastos','totalRows','selectedRows'))
                ->setPaper('a4', 'landscape');
            return $pdf->stream('utilidades_rpt.pdf');
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
                'asignaciones.id_proveedor',
                'asignaciones.fecha_inicio',
                'asignaciones.fecha_fin'
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
    
        return view('reporteria.documentos.index', compact('cotizaciones', 'clientes', 'subclientes', 'proveedores'));
    }

    
    

public function advance_documentos(Request $request) {
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

    $cotizaciones = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->whereIn('cotizaciones.id', $cotizacionIds)
        ->select(
            'docum_cotizacion.num_contenedor',
            'docum_cotizacion.doc_ccp',
            'docum_cotizacion.boleta_liberacion',
            'docum_cotizacion.doda',
            'cotizaciones.carta_porte',
            'docum_cotizacion.boleta_vacio',
            'docum_cotizacion.doc_eir'
        )
        ->get();

    $cotizacion = Cotizaciones::whereIn('id', $cotizacionIds)->get(); 
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
    public function index_liquidados_cxc(){

        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $subclientes = Subclientes::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        return view('reporteria.liquidados.cxc.index', compact('clientes', 'subclientes'));
    }

    public function advance_liquidados_cxc(Request $request) {

        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $proveedores = Proveedor::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();


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

        if($request->fileType == "xlsx"){
            Excel::store(new \App\Exports\LiquidadosCxcExport($cotizaciones, $fechaCarbon, $bancos_oficiales, $bancos_no_oficiales, $registrosBanco, $user,$cotizacion_first), 'liquidados_cxc.xlsx','public');
            return Response::download(storage_path('app/public/liquidados_cxc.xlsx'), "liquidados_cxp.xlsx")->deleteFileAfterSend(true);
        }else{
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

    public function index_liquidados_cxp(){

        $proveedores = Proveedor::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        return view('reporteria.liquidados.cxp.index', compact('proveedores'));
    }

    public function advance_liquidados_cxp(Request $request) {

        $proveedores = Proveedor::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $id_proveedor = $request->id_proveedor;

        if ($id_proveedor !== null) {
            $cotizaciones = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
            ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
            ->where('cotizaciones.id_empresa' ,'=',auth()->user()->id_empresa)
            ->where('asignaciones.id_camion', '=', NULL)
            ->where(function($query) {
                $query->where('cotizaciones.estatus', '=', 'Aprobada')
                    ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
            })
            ->where('asignaciones.id_proveedor', '=', $id_proveedor)
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

        if($request->fileType == "xlsx"){
            Excel::store(new \App\Exports\LiquidadosCxpExport($cotizaciones, $fechaCarbon, $bancos_oficiales, $bancos_no_oficiales, $registrosBanco, $user,$cotizacion), 'liquidados_cxp.xlsx','public');
            return Response::download(storage_path('app/public/liquidados_cxp.xlsx'), "liquidados_cxp.xlsx")->deleteFileAfterSend(true);
        }else{
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
}
