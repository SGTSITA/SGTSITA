<?php

namespace App\Http\Controllers;

use App\Models\Asignaciones;
use App\Models\BancoDinero;
use App\Models\Bancos;
use App\Models\Client;
use App\Models\Cotizaciones;
use App\Models\DocumCotizacion;
use App\Models\GastosExtras;
use App\Models\GastosGenerales;
use App\Models\Proveedor;
use App\Models\Subclientes;
use App\Models\User;
use App\Exports\CotizacionesCXC;
use App\Exports\GenericExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
use DB;
use PDF;
use Excel;


class ReporteriaController extends Controller
{
    public function index(){

        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $subclientes = Subclientes::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $proveedores = Proveedor::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        return view('reporteria.cxc.index', compact('clientes', 'subclientes', 'proveedores'));
    }

    public function advance(Request $request) {

        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $proveedores = Proveedor::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();


        $id_client = $request->id_client;
        $id_subcliente = $request->id_subcliente;
        $id_proveedor = $request->id_proveedor;

        $cotizaciones = [];

        if ($id_client !== null) {
            $query = Cotizaciones::where('id_empresa', '=', auth()->user()->id_empresa)
                ->where('id_cliente', $id_client)
                ->where(function ($query) {
                    $query->where('estatus', '=', 'Aprobada')
                          ->orWhere('estatus', '=', 'Finalizado');
                })
                ->where('estatus_pago', '=', '0')
                ->where('restante', '>', 0);

            // Agrega la condición de proveedor solo si se selecciona uno
            if ($id_proveedor !== null && $id_proveedor !== '') {
                $query->whereHas('DocCotizacion.Asignaciones', function ($query) use ($id_proveedor) {
                    $query->where('id_proveedor', $id_proveedor);
                });
            }

            if ($id_subcliente !== null && $id_subcliente !== '') {
                $query->where('id_subcliente', $id_subcliente);
            }

            $cotizaciones = $query->get();
        }

        return view('reporteria.cxc.index', compact('clientes', 'cotizaciones', 'proveedores'));
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
        $bancos_oficiales = Bancos::where('tipo', '=', 'Oficial')->get();
        $bancos_no_oficiales = Bancos::where('tipo', '=', 'No Oficial')->get();

        $pdf = PDF::loadView('reporteria.cxc.pdf', compact('cotizaciones', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales', 'cotizacion', 'user'))->setPaper([0, 0, 595, 1200], 'landscape');

        // Generar el nombre del archivo
        $fileName = 'cxc_' . implode('_', $cotizacionIds) . '.pdf';
        // Guardar el PDF en la carpeta storage
        $pdf->save(storage_path('app/public/' . $fileName));

        // Devolver el archivo PDF como respuesta
        $filePath = storage_path('app/public/' . $fileName);
        return Response::download($filePath, $fileName)->deleteFileAfterSend(true);
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
    public function index_cxp(){

        $proveedores = Proveedor::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        return view('reporteria.cxp.index', compact('proveedores'));
    }

    public function advance_cxp(Request $request) {
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
            ->where('cotizaciones.prove_restante', '>', 0)
            ->select('asignaciones.*', 'docum_cotizacion.num_contenedor', 'docum_cotizacion.id_cotizacion', 'cotizaciones.origen', 'cotizaciones.destino', 'cotizaciones.estatus', 'cotizaciones.prove_restante')
            ->get();

            $proveedor_cxp = Proveedor::where('id', '=', $request->id_proveedor)->first();
        }

        return view('reporteria.cxp.index', compact('proveedores', 'cotizaciones', 'proveedor_cxp'));
    }

    public function export_cxp(Request $request){
        $fecha = date('Y-m-d');
        $fechaCarbon = Carbon::parse($fecha);

        $cotizacionIds = $request->input('selected_ids', []);
        if (empty($cotizacionIds)) {
            return redirect()->back()->with('error', 'No se seleccionaron cotizaciones.');
        }

        $cotizaciones = Asignaciones::whereIn('id', $cotizacionIds)->get();
        $bancos_oficiales = Bancos::where('tipo', '=', 'Oficial')->get();
        $bancos_no_oficiales = Bancos::where('tipo', '=', 'No Oficial')->get();

        $cotizacion = Asignaciones::where('id', $cotizacionIds)->first();
        $user = User::where('id', '=', auth()->user()->id)->first();

        $pdf = PDF::loadView('reporteria.cxp.pdf', compact('cotizaciones', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales', 'cotizacion', 'user'))->setPaper('a4', 'landscape');

        $fileName = 'cxp_' . implode('_', $cotizacionIds) . '.pdf';

        // Guardar el PDF en la carpeta storage
        $pdf->save(storage_path('app/public/' . $fileName));

        // Devolver el archivo PDF como respuesta
        $filePath = storage_path('app/public/' . $fileName);
        //  return $pdf->stream();
      return Response::download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    // ==================== V I A J E S ====================
    public function index_viajes(){

        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $subclientes = Subclientes::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $proveedores = Proveedor::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        return view('reporteria.asignaciones.index', compact('clientes', 'subclientes', 'proveedores'));
    }

    public function advance_viajes(Request $request) {
        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $subclientes = Subclientes::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $proveedores = Proveedor::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $id_client = $request->id_client;
        $id_subcliente = $request->id_subcliente;
        $id_proveedor = $request->id_proveedor;

        $asignaciones = Asignaciones::
        join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id') // Unir con la tabla 'docum_cotizacion' primero
        ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id') // Luego unir con 'cotizaciones'
        ->where('asignaciones.id_empresa' ,'=', auth()->user()->id_empresa)
        ->select('asignaciones.*');

        if ($request->fecha_de && $request->fecha_hasta) {
            $inicio = Carbon::parse($request->fecha_de)->startOfDay();
            $fin = Carbon::parse($request->fecha_hasta)->endOfDay();

            $asignaciones = $asignaciones->where(function($query) use ($inicio, $fin) {
                $query->whereBetween('asignaciones.fehca_inicio_guard', [$inicio, $fin])
                      ->orWhere(function($query) use ($inicio, $fin) {
                          $query->where('asignaciones.fehca_inicio_guard', '>=', $inicio)
                                ->where('asignaciones.fehca_inicio_guard', '<=', $fin);
                      });
            });

        }

        if ($id_client !== null) {
            $asignaciones = $asignaciones->where('cotizaciones.id_cliente', $id_client);

            if ($id_subcliente !== null && $id_subcliente !== '') {
                $asignaciones = $asignaciones->where('cotizaciones.id_subcliente', $id_subcliente);
            }
        }

        if ($id_proveedor !== null) {
            $asignaciones = $asignaciones->where('asignaciones.id_proveedor', $id_proveedor);
        }

        if ($request->estatus) {
            $asignaciones = $asignaciones->where('cotizaciones.estatus', '=', $request->estatus);
        }

        $asignaciones = $asignaciones->get();

        return view('reporteria.asignaciones.index', compact('asignaciones', 'clientes', 'subclientes', 'proveedores'));
    }

    public function export_viajes(Request $request){
        $fecha = date('Y-m-d');
        $fechaCarbon = Carbon::parse($fecha);

        $cotizacionIds = $request->input('cotizacion_ids', []);
        // if (empty($cotizacionIds)) {
        //     return redirect()->back()->with('error', 'No se seleccionaron cotizaciones.');
        // }

        $cotizaciones = Asignaciones::whereIn('id', $cotizacionIds)->get();
        $bancos_oficiales = Bancos::where('tipo', '=', 'Oficial')->get();
        $bancos_no_oficiales = Bancos::where('tipo', '=', 'No Oficial')->get();

        $cotizacion = Asignaciones::where('id', $cotizacionIds)->first();
        $user = User::where('id', '=', auth()->user()->id)->first();

        $pdf = PDF::loadView('reporteria.asignaciones.pdf', compact('cotizaciones', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales', 'cotizacion', 'user'))->setPaper('a4', 'landscape');
        return $pdf->stream();
        // return $pdf->download('cotizaciones_seleccionadas.pdf');
    }

    // ==================== U T I L I D A D E S ====================
    public function index_utilidad(){

        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $subclientes = Subclientes::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $contenedores = DocumCotizacion::
        join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
        ->where('docum_cotizacion.num_contenedor' ,'!=', NULL)
        ->where('docum_cotizacion.id_empresa' ,'=',auth()->user()->id_empresa)
        ->where('cotizaciones.estatus' ,'=', 'Aprobada')
        ->orderBy('docum_cotizacion.created_at', 'desc')->get();

        return view('reporteria.utilidad.index', compact('clientes', 'subclientes', 'contenedores'));
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

    public function export_utilidad(Request $request){
        $fechaDe = $request->input('fecha_de');
        $fechaHasta = $request->input('fecha_hasta');

        $fecha = date('Y-m-d');
        $fechaCarbon = Carbon::parse($fecha);

        $cotizacionIds = $request->input('cotizacion_ids', []);
        if (empty($cotizacionIds)) {
            return redirect()->back()->with('error', 'No se seleccionaron cotizaciones.');
        }

        $cotizaciones = Asignaciones::whereIn('id', $cotizacionIds)->get();

        $cotizacion = Asignaciones::where('id', $cotizacionIds)->first();
        $user = User::where('id', '=', auth()->user()->id)->first();

        $gastos = GastosGenerales::where('id_empresa', auth()->user()->id_empresa);
        if ($fechaDe && $fechaHasta) {
            $gastos = $gastos->where('fecha', '>=', $fechaDe)
                             ->where('fecha', '<=', $fechaHasta);
        }

        $gastos = $gastos->get();

        $pdf = PDF::loadView('reporteria.utilidad.pdf', compact('cotizaciones', 'fechaCarbon', 'cotizacion', 'user', 'gastos'))->setPaper('a4', 'landscape');
         // return $pdf->stream();
       return $pdf->download('cotizaciones_seleccionadas.pdf');
    }

    // ==================== D O C U M E N T O S ====================
    public function index_documentos(){

        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $subclientes = Subclientes::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        return view('reporteria.documentos.index', compact('clientes', 'subclientes'));
    }

    public function advance_documentos(Request $request) {
        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();
        $subclientes = Subclientes::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $id_client = $request->id_client;
        $id_subcliente = $request->id_subcliente;

        // Construir la consulta inicial
        $cotizaciones = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
            ->where('cotizaciones.id_empresa', auth()->user()->id_empresa)
            ->where('cotizaciones.estatus', 'Aprobada')
            ->select('docum_cotizacion.num_contenedor', 'docum_cotizacion.doc_ccp', 'docum_cotizacion.boleta_liberacion', 'docum_cotizacion.doda', 'cotizaciones.carta_porte', 'docum_cotizacion.boleta_vacio', 'docum_cotizacion.doc_eir', 'cotizaciones.id');

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

    public function export_documentos(Request $request){

        $fecha = date('Y-m-d');
        $fechaCarbon = Carbon::parse($fecha);

        $cotizacionIds = $request->input('selected_ids', []);

        if (empty($cotizacionIds)) {
            return redirect()->back()->with('error', 'se seleccionaron cotizaciones.');
        }

        $cotizaciones = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->whereIn('cotizaciones.id', $cotizacionIds)
        ->select('docum_cotizacion.num_contenedor', 'docum_cotizacion.doc_ccp', 'docum_cotizacion.boleta_liberacion', 'docum_cotizacion.doda', 'cotizaciones.carta_porte', 'docum_cotizacion.boleta_vacio', 'docum_cotizacion.doc_eir')
        ->get();

        $cotizacion = Cotizaciones::where('id', $cotizacionIds)->first();
        $user = User::where('id', '=', auth()->user()->id)->first();

        $pdf = PDF::loadView('reporteria.documentos.pdf', compact('cotizaciones', 'fechaCarbon', 'cotizacion', 'user'))->setPaper('a4', 'landscape');
        return $pdf->stream();
        // return $pdf->download('cotizaciones_seleccionadas.pdf');
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
