<?php

namespace App\Http\Controllers;

use App\Models\Asignaciones;
use App\Models\Bancos;
use App\Models\Client;
use App\Models\ComprobanteGastos;
use App\Models\Configuracion;
use App\Models\Coordenadas;
use App\Models\Cotizaciones;
use App\Models\DocumCotizacion;
use App\Models\Empresas;
use App\Models\Equipo;
use App\Models\GastosExtras;
use App\Models\GastosOperadores;
use App\Models\Operador;
use App\Models\Proveedor;
use App\Models\Subclientes;
use App\Models\ClientEmpresa;
use App\Models\EmpresaGps;
use App\Models\GpsCompany;
use App\Models\BancoDineroOpe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;
use DB;
use Auth;
USE File;
use App\Events\EnvioCorreoCoordenadasEvent;
use App\Traits\CommonTrait as Common;

class CotizacionesController extends Controller
{
    public function index(){
        $empresas = Empresas::get();
        $EmpresasGPS = EmpresaGps::where('id_empresa',auth()->user()->id_empresa)->with('serviciosGps')->get()->pluck('id_gps_company');
        $gpsCompanies = GpsCompany::whereIn('id',$EmpresasGPS)->get();

        return view('cotizaciones.index', compact('empresas','gpsCompanies'));
    }

    public function getCotizacionesList()
    {
        $cotizaciones = Cotizaciones::where('id_empresa', auth()->user()->id_empresa)
            ->where('estatus', '=', 'Aprobada')
            ->where('estatus_planeacion', '=', 1)
            ->where('jerarquia', "!=",'Secundario')
            ->orderBy('created_at', 'desc')
            ->with(['cliente', 'DocCotizacion.Asignaciones'])
            ->get()
            ->map(function ($cotizacion) {
                $contenedor = $cotizacion->DocCotizacion ? $cotizacion->DocCotizacion->num_contenedor : 'N/A';

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

                return [
                    'id' => $cotizacion->id,
                    'cliente' => $cotizacion->cliente ? $cotizacion->cliente->nombre : 'N/A',
                    'origen' => $cotizacion->origen,
                    'destino' => $cotizacion->destino,
                    'contenedor' => $contenedor,
                    'estatus' => $cotizacion->estatus,
                    'coordenadas' => optional($cotizacion->DocCotizacion)->Asignaciones ? 'Ver' : '',
                    'id_asignacion' => optional($cotizacion->DocCotizacion)->Asignaciones->id ?? null,
                    'edit_url' => route('edit.cotizaciones', $cotizacion->id),
                    'tipo' => (!is_null($cotizacion->referencia_full)) ? 'Full' : 'Sencillo'
                ];
            });

        return response()->json(['list' => $cotizaciones]);
    }

    public function getDocumentos($id)

    {
        $cotizacion = Cotizaciones::with('DocCotizacion')->findOrFail($id);
        return response()->json([
            'num_contenedor' => $cotizacion->DocCotizacion->num_contenedor ?? null,
            'doc_ccp' => $cotizacion->DocCotizacion->doc_ccp ?? null,
            'boleta_liberacion' => $cotizacion->DocCotizacion->boleta_liberacion ?? null,
            'doda' => $cotizacion->DocCotizacion->doda ?? null,
            'carta_porte' => $cotizacion->carta_porte ?? null,
            'boleta_vacio' => $cotizacion->DocCotizacion->boleta_vacio ?? null,
            'doc_eir' => $cotizacion->doc_eir ?? null,
            'foto_patio' => $cotizacion->DocCotizacion->foto_patio ?? null,
        ]);
    }


    public function index_externo(){

        $cotizaciones_emitidas = Cotizaciones::where('id_cliente' ,'=', auth()->user()->id_cliente)->orderBy('created_at', 'desc')
        ->select('id_cliente', 'origen', 'destino', 'id', 'estatus')->get();

        return view('cotizaciones.externos.index', compact('cotizaciones_emitidas'));
    }

   
    public function getCotizacionesFinalizadas()
    {
        $cotizaciones = Cotizaciones::where('id_empresa', auth()->user()->id_empresa)
            ->where('estatus', 'Finalizado')
            ->where('jerarquia', "!=",'Secundario')
            ->orderBy('created_at', 'desc')
            ->with([
                'Cliente',
                'DocCotizacion.Asignaciones'
            ])
            ->get()
            ->map(function ($cotizacion) {
                $contenedor = $cotizacion->DocCotizacion ? $cotizacion->DocCotizacion->num_contenedor : 'N/A';

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

                return [
                    'id' => $cotizacion->id,
                    'cliente' => $cotizacion->Cliente ? $cotizacion->Cliente->nombre : 'N/A',
                    'origen' => $cotizacion->origen,
                    'destino' => $cotizacion->destino,
                    'contenedor' => $contenedor,
                    'estatus' => $cotizacion->estatus,
                    'coordenadas' => optional($cotizacion->DocCotizacion)->Asignaciones ? 'Ver' : 'N/A',
                    'id_asignacion' => optional($cotizacion->DocCotizacion)->Asignaciones->id ?? null,
                    'edit_url' => route('edit.cotizaciones', $cotizacion->id),
                    'pdf_url' => route('pdf.cotizaciones', $cotizacion->id),
                    'tipo' => (!is_null($cotizacion->referencia_full)) ? 'Full' : 'Sencillo'
                ];
            });

        return response()->json(['list' => $cotizaciones]);
    }



   

    public function getCotizacionesEnEspera()
    {
        $cotizaciones = Cotizaciones::where('id_empresa', auth()->user()->id_empresa)
            ->where('estatus', 'Pendiente')
            ->where('jerarquia', 'Principal')
            ->orderBy('created_at', 'desc')
            ->with(['cliente', 'DocCotizacion.Asignaciones'])
            ->get()
            ->map(function ($cotizacion) {
                $contenedor = $cotizacion->DocCotizacion ? $cotizacion->DocCotizacion->num_contenedor : 'N/A';

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

                return [
                    'id' => $cotizacion->id,
                    'cliente' => $cotizacion->cliente ? $cotizacion->cliente->nombre : 'N/A',
                    'origen' => $cotizacion->origen,
                    'destino' => $cotizacion->destino,
                    'contenedor' => $contenedor,
                    'estatus' => $cotizacion->estatus,
                    'coordenadas' => optional($cotizacion->DocCotizacion)->Asignaciones ? 'Ver' : '',
                    'edit_url' => route('edit.cotizaciones', $cotizacion->id),
                    'tipo' => (!is_null($cotizacion->referencia_full)) ? 'Full' : 'Sencillo'
                ];


            });

        return response()->json(['list' => $cotizaciones]);
    }


   

    public function getCotizacionesAprobadas()
    {
        $cotizaciones = Cotizaciones::where('id_empresa', auth()->user()->id_empresa)
            ->where('estatus', 'Aprobada')
            ->where('jerarquia', "!=",'Secundario')
            ->where(function($query) {
                $query->where('estatus_planeacion', 0)
                    ->orWhereNull('estatus_planeacion');
            })
            ->orderBy('created_at', 'desc')
            ->with(['cliente', 'DocCotizacion.Asignaciones'])
            ->get()
            ->map(function ($cotizacion) {
                $contenedor = $cotizacion->DocCotizacion ? $cotizacion->DocCotizacion->num_contenedor : 'N/A';

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

                return [
                    'id' => $cotizacion->id,
                    'cliente' => $cotizacion->cliente ? $cotizacion->cliente->nombre : 'N/A',
                    'subcliente' => $cotizacion->subcliente ? $cotizacion->subcliente->nombre : 'N/A',
                    'origen' => $cotizacion->origen,
                    'destino' => $cotizacion->destino,
                    'contenedor' => $contenedor,
                    'labelContenedor' => $cotizacion->DocCotizacion ? $cotizacion->DocCotizacion->num_contenedor : 'N/A',
                    'estatus' => $cotizacion->estatus,
                    'coordenadas' => optional($cotizacion->DocCotizacion)->Asignaciones ? 'Ver' : '',
                    'edit_url' => route('edit.cotizaciones', $cotizacion->id),
                    'tipo' => (!is_null($cotizacion->referencia_full)) ? 'Full' : 'Sencillo',
                    'referencia_full' => $cotizacion->referencia_full
                ];
            });

        return response()->json(['list' => $cotizaciones]);
    }

public function getCotizacionesCanceladas()
{
    $cotizaciones = Cotizaciones::where('id_empresa', auth()->user()->id_empresa)
        ->where('estatus', 'Cancelada')
        ->where('jerarquia', "!=",'Secundario')
        ->orderBy('created_at', 'desc')
        ->with(['cliente', 'DocCotizacion.Asignaciones'])
        ->get()
        ->map(function ($cotizacion) {
            $contenedor = $cotizacion->DocCotizacion ? $cotizacion->DocCotizacion->num_contenedor : 'N/A';

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

            return [
                'id' => $cotizacion->id,
                'cliente' => $cotizacion->cliente ? $cotizacion->cliente->nombre : 'N/A',
                'origen' => $cotizacion->origen,
                'destino' => $cotizacion->destino,
                'contenedor' => $contenedor,
                'estatus' => $cotizacion->estatus,
                'coordenadas' => optional($cotizacion->DocCotizacion)->Asignaciones ? 'Ver' : '',
                'edit_url' => route('edit.cotizaciones', $cotizacion->id),
                'tipo' => (!is_null($cotizacion->referencia_full)) ? 'Full' : 'Sencillo'
            ];
        });

    return response()->json(['list' => $cotizaciones]);
}

   

    public function getCotizacionesId($id)
    {
        $cotizacion = DB::table('cotizaciones')
        ->join('clients', 'cotizaciones.id_cliente', '=', 'clients.id')
        ->join('docum_cotizacion', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
        ->join('asignaciones', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
        ->where('cotizaciones.id', $id)
        ->select('cotizaciones.id','asignaciones.id as id_Asignacion', 'clients.nombre as cliente', 'cotizaciones.origen', 'cotizaciones.destino',
        'docum_cotizacion.num_contenedor as contenedor','cotizaciones.estatus')
        ->get();

        $idAsignacion = $cotizacion->isNotEmpty() ? $cotizacion->first()->id_Asignacion : null;

        $coordenada = Coordenadas::where('id_asignacion', $idAsignacion)
        ->where('id_cotizacion', $id)
        ->select('tipo_c_estado', 'tipo_b_estado', 'tipo_f_estado')
        ->first();

        return response()->json(['list' =>  $cotizacion , 'coordenada' => $coordenada]);
    }

    public function solicitudesEntrantes(){
        $empresas = Empresas::get();
        return view('cotizaciones.solicitudes_clientes',compact('empresas'));
    }

    public function find(){
        return view('cotizaciones.busqueda');
    }

    public function findExecute(Request $request){
        $where = 'id_empresa = '.auth()->user()->id_empresa;
        if($request->txtCliente != null){
            $clientes = Client::where('nombre','like','%'.$request->txtCliente.'%')->get()->pluck('id')->toArray();
            if($clientes != null ){
                $where .= ' and id_cliente in('.implode(',',$clientes).')';
            }else{
               return redirect('/cotizaciones/busqueda')->with('message','Cliente no existe');
            }
        }

        if($request->txtContenedor != null){
            $documCotizacion = DocumCotizacion::where('num_contenedor','like','%'.$request->txtContenedor.'%')
                                                ->get()
                                                ->pluck('id_cotizacion')
                                                ->toArray();

          //  $where .= ($documCotizacion != null ) ? ' and id in('.implode(',',$documCotizacion).')' : '';
            if($documCotizacion != null ){
                $where .= ' and id in('.implode(',',$documCotizacion).')';
            }else{
               return redirect('/cotizaciones/busqueda')->with('message','Núm. Contenedor no existe');
            }
        }

        $where .= ($request->txtOrigen != null ) ? " and origen like '%".$request->txtOrigen."%'" : '';
        $where .= ($request->txtDestino != null ) ? " and destino like '%".$request->txtDestino."%'" : '';

        $cotizaciones = Cotizaciones::whereRaw($where)
                                    ->orderBy('created_at', 'desc')
                                    ->select('id_cliente', 'origen', 'destino', 'id', 'estatus')
                                    ->get();

        $equipos_dolys = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Dolys')->get();
        $equipos_chasis = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Chasis / Plataforma')->get();
        $equipos_camiones = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Tractos / Camiones')->get();
        $operadores = Operador::get();
        $bancos = Bancos::get();
        $proveedores = Proveedor::get();
        $empresas = Empresas::get();
        return ($cotizaciones != null) ? view('cotizaciones.busqueda_results', compact('empresas', 'proveedores','bancos','operadores','equipos_dolys','equipos_chasis','equipos_camiones','cotizaciones')) : view('cotizaciones.busqueda')->with('find-message','No se encontraron resultados');
    }

    public function create(){
        //$clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();
        $clientes = Client::join('client_empresa as ce','clients.id','=','ce.id_client')
                            ->where('ce.id_empresa',Auth::User()->id_empresa)
                            ->where('is_active',1)
                            ->orderBy('nombre')->get();

        return view('cotizaciones.create',compact('clientes'));
    }

    public function create_externo(){
        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();
        $subclientes = Subclientes::where('id_cliente' ,'=',auth()->user()->id_cliente)->get();

        return view('cotizaciones.externos.create',compact('clientes','subclientes'));
    }

    public function getSubclientes($clienteId)
    {
        // Buscar los subclientes asociados al cliente
        $subclientes = Subclientes::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('id_cliente', $clienteId)->get();

        // Devolver los subclientes en formato JSON
        return response()->json($subclientes);
    }

    public function storeV2(Request $request){

        $Contenedores = $request->Contenedores;
        $idEmpresa = auth()->user()->id_empresa;
        $cliente = $request->get('id_cliente');

        try{
            DB::beginTransaction();
            //Primero validar si el contenedor existe
            foreach($Contenedores as $contenedor){
                $numContenedor = $contenedor['num_contenedor'];

                $contenedorExistente = DocumCotizacion::where('num_contenedor', $numContenedor)
                                                    ->where('id_empresa', $idEmpresa)
                                                    ->first();

                if ($contenedorExistente) {
                    return response()->json(["Titulo" => "Contenedor $numContenedor creado previamente", "Mensaje" => "El contenedor ya existe en la empresa", "TMensaje" => "warning"]);
                }
            }


            $fullUUID = Common::generarUuidV4();


            //Ahora guardamos
            foreach($Contenedores as $contenedor){
                $cotizaciones = new Cotizaciones;
                $cotizaciones->id_cliente = $cliente;
                $cotizaciones->id_subcliente = $request->get('id_subcliente');;
                $cotizaciones->origen = $request->origen;
                $cotizaciones->destino = $request->destino;
                $cotizaciones->tamano = $contenedor['tamano'];
                $cotizaciones->peso_contenedor = $contenedor['peso_contenedor'];

                $cotizaciones->otro = $request->otro;
                $cotizaciones->fecha_modulacion = $contenedor['fecha_modulacion'];
                $cotizaciones->fecha_entrega = $contenedor['fecha_entrega'];
                $cotizaciones->iva = $request->iva;
                $cotizaciones->retencion = $request->retencion;
                $cotizaciones->peso_reglamentario = $request->peso_reglamentario;
                $cotizaciones->precio_sobre_peso = $request->precio_sobre_peso;
                $cotizaciones->sobrepeso = $contenedor['sobrepeso'];
                $cotizaciones->estatus = ($request->has('uuid')) ? 'En espera' : 'Pendiente';
                $cotizaciones->precio_viaje = $request->precio_viaje;
                $cotizaciones->burreo = $request->burreo;
                $cotizaciones->maniobra = $request->maniobra;
                $cotizaciones->estadia = $request->estadia;
                $cotizaciones->base_factura = $request->base_factura;
                $cotizaciones->base_taref = $request->base_taref;
                $cotizaciones->recinto_clientes = null;
                $cotizaciones->precio_tonelada = $contenedor['precio_tonelada'];

                $cotizaciones->tipo_viaje = $request->TipoCotizacion;
                $cotizaciones->jerarquia = $contenedor['jerarquia'];
                if($request->TipoCotizacion == "Full"){
                    $cotizaciones->referencia_full =  $fullUUID;
                }


                $cotizaciones->total = $request->total;
                $cotizaciones->restante = $cotizaciones->total;
                $cotizaciones->estatus_pago = '0';

                $cotizaciones->save();

                $docucotizaciones = new DocumCotizacion;
                $docucotizaciones->id_cotizacion = $cotizaciones->id;
                $docucotizaciones->num_contenedor = $contenedor['num_contenedor'];
                $docucotizaciones->save();
            }

            DB::commit();
            return response()->json(["TMensaje" => "success", "Mensaje" =>  "Creado Correctamente","datos"=> $request->all()]);

        }catch(\Throwable $t){
            DB::rollback();
            return response()->json(["TMensaje" => "error", "Mensaje" =>  "No se pudo crear el viaje: ".$t->getMessage()]);

        }

    }

    public function store(Request $request){

        $idEmpresa = ($request->has('id_proveedor')) ? $request->id_proveedor : auth()->user()->id_empresa;
        if($request->get('num_contenedor') != NULL){
            $numContenedor = str_replace(' ','',$request->input('num_contenedor'));

            $contenedorExistente = DocumCotizacion::where('num_contenedor', $numContenedor)
                                                ->where('id_empresa', $idEmpresa)
                                                ->first();

            if ($contenedorExistente) {
                return response()->json(["Titulo" => "Contenedor creado previamente", "Mensaje" => "El contenedor ya existe en la empresa", "TMensaje" => "warning"]);
            }
        }

    /*if($request->get('id_cliente_clientes') !== NULL){
            $cliente = $request->get('id_cliente_clientes');
        }else if($request->get('nombre_cliente') == NULL){
            $cliente = $request->get('id_cliente');
        }else{
            $cliente = new Client;
            $cliente->nombre = $request->get('nombre_cliente');
            $cliente->correo = $request->get('correo_cliente');
            $cliente->telefono = $request->get('telefono_cliente');
            $cliente->id_empresa =  6;
            $cliente->save();

            $cliente = $cliente->id;
        }*/

        $cliente = $request->get('id_cliente');

        $cotizaciones = new Cotizaciones;
        $cotizaciones->id_cliente = $cliente;
        $cotizaciones->id_subcliente = $request->get('id_subcliente');
        $cotizaciones->origen = $request->get('origen');
        $cotizaciones->destino = $request->get('destino');
        $cotizaciones->tamano = $request->get('tamano');
        $cotizaciones->peso_contenedor = $request->get('peso_contenedor');

        $cotizaciones->otro = $request->get('otro');
        $cotizaciones->fecha_modulacion = $request->get('fecha_modulacion');
        $cotizaciones->fecha_entrega = $request->get('fecha_entrega');
        $cotizaciones->iva = $request->get('iva');
        $cotizaciones->retencion = $request->get('retencion');
        $cotizaciones->peso_reglamentario = $request->get('peso_reglamentario');
        $cotizaciones->precio_sobre_peso = $request->get('precio_sobre_peso');
        $cotizaciones->sobrepeso = $request->get('sobrepeso');
        $cotizaciones->estatus = ($request->has('uuid')) ? 'En espera' : 'Pendiente';
        $cotizaciones->precio_viaje = $request->get('precio_viaje');
        $cotizaciones->burreo = $request->get('burreo');
        $cotizaciones->maniobra = $request->get('maniobra');
        $cotizaciones->estadia = $request->get('estadia');
        $cotizaciones->base_factura = $request->get('base_factura');
        $cotizaciones->base_taref = $request->get('base_taref');
        $cotizaciones->recinto_clientes = $request->get('recinto_clientes');
        $cotizaciones->precio_tonelada = 0;

        //

        if($request->get('id_cliente_clientes') == NULL){
            $precio_tonelada = 0;
            $cotizaciones->precio_tonelada = $precio_tonelada;

            if($request->get('total') == NULL){
                $total = 0;
             }else{
                $total = str_replace(',', '', $request->get('total'));
            }
        }else{
            $cotizaciones->precio_tonelada = 0;
            $total = 0;
        }

        $cotizaciones->total = $total;
        $cotizaciones->restante = $cotizaciones->total;
        $cotizaciones->estatus_pago = '0';
        $cotizaciones->save();

        $docucotizaciones = new DocumCotizacion;
        $docucotizaciones->id_cotizacion = $cotizaciones->id;
        $docucotizaciones->num_contenedor = str_replace(' ','',$request->get('num_contenedor'));
        $docucotizaciones->save();
        // Definir ruta dentro de public
        $path = public_path('cotizaciones/cotizacion'.$docucotizaciones->id.'/formato_carta_porte_' . $numContenedor . '.pdf');

        if($request->has('uuid')){
            
            $cotizaciones->sat_uso_cfdi_id = $request->id_uso_cfdi;
            $cotizaciones->sat_forma_pago_id = $request->id_forma_pago;
            $cotizaciones->sat_metodo_pago_id = $request->id_metodo_pago;
            $cotizaciones->direccion_entrega = $request->direccion_entrega;
            $cotizaciones->direccion_recinto = $request->direccion_recinto;
            $cotizaciones->uso_recinto = ($request->text_recinto == 'recinto-si') ? 1 : 0;

            //Nuevos campos para datos de CartaPorte 09/2025
            
            $cotizaciones->cp_fraccion = $request->cp_fraccion;
            $cotizaciones->cp_clave_sat = $request->cp_clave_sat; 
            $cotizaciones->cp_pedimento = $request->cp_pedimento;
            $cotizaciones->cp_clase_ped = $request->cp_clase_pedimento;
            $cotizaciones->cp_cantidad = $request->cp_cantidad;
            $cotizaciones->cp_valor = $request->cp_valor;
            $cotizaciones->cp_moneda = $request->cp_moneda_valor; 
            $cotizaciones->cp_contacto_entrega = $request->cp_contacto_entrega;
            $cotizaciones->cp_fecha_tentativa_entrega = $request->cp_fecha_tentativa_entrega;
            $cotizaciones->cp_hora_tentativa_entrega = $request->cp_hora_tentativa_entrega;
            $cotizaciones->cp_comentarios = $request->cp_comentarios;

            $subCliente = Subclientes::where('id',$cotizaciones->id_subcliente)->first();
            
            $pdf = \PDF::loadView('cotizaciones.carta_porte_pdf', compact('cotizaciones','numContenedor','subCliente'));

            // Crear la carpeta si no existe
            if (!File::exists(public_path('cotizaciones/cotizacion'.$docucotizaciones->id.''))) {
                File::makeDirectory(public_path('cotizaciones/cotizacion'.$docucotizaciones->id.''), 0755, true);
            }

            // Guardar PDF
            $pdf->save($path);

            if($request->has('id_proveedor')){
                $cotizaciones->id_proveedor = $request->id_transportista;
            }
        }

        $cotizaciones->latitud=  $request->latitud;
        $cotizaciones->longitud = $request->longitud;
        $cotizaciones->direccion_mapa = $request->direccion_mapa;
        $cotizaciones->update();

        $doc_cotizaciones = Cotizaciones::where('id', '=', $cotizaciones->id)->first();
        if ($request->hasFile("excel_clientes")) {
            $file = $request->file('excel_clientes');
            $path = public_path() . '/cotizaciones/cotizacion'. $cotizaciones->id;
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $doc_cotizaciones->excel_clientes = $fileName;
        }

        $doc_cotizaciones->update();

       

        if(File::exists($path)){
            $docucotizaciones->ccp = 'si';
            $docucotizaciones->doc_ccp = 'formato_carta_porte_' . $numContenedor . '.pdf';
        }

        $docucotizaciones->update();

        if($idEmpresa != auth()->user()->id_empresa){
            DB::table('cotizaciones')->where('id',$cotizaciones->id)->update([
                'id_empresa' => $idEmpresa
            ]);

            DB::table('docum_cotizacion')->where('id_cotizacion')->update([
                'id_empresa' => $idEmpresa
            ]);
        }

        return response()->json(["Titulo" => "Proceso satisfactorio", "Mensaje" => "Cotización creada con exito", "TMensaje" => "success","folio" => $cotizaciones->id,"proveedor" =>$cotizaciones->id_empresa]);

    }

    public function storeMultiple(Request $request){
        try{
            DB::beginTransaction();
            $contenedores = $request->contenedores;
            $row = 1;

            foreach($contenedores as $cont){
                //validaremos que los contenedores no existan
                $numContenedor = str_replace(' ','',$cont[1]);
                $idEmpresa = auth()->user()->id_empresa;

                $contenedorExistente = DocumCotizacion::where('num_contenedor', $numContenedor)
                                                    ->where('id_empresa', $idEmpresa)
                                                    ->first();

                if ($contenedorExistente) {
                    return response()->json(["Titulo" => "Contenedor creado previamente", "Mensaje" => "El contenedor ya existe en la empresa", "TMensaje" => "warning"]);
                }

                //Validaremos que el num de sub-cliente sea valido
                $numSubCliente = substr($cont[0],0,5);
                $subCliente = Subclientes::where('id',$numSubCliente);
                if(!$subCliente->exists()){
                    return response()->json(["Titulo" => "SubCliente NO Valido", "Mensaje" => "El subcliente de la fila $row no es un cliente registrado", "TMensaje" => "warning"]);
                }

            }

            //una vez superada todas las validaciones procedemos a guardar los datos
            foreach($contenedores as $contenedor){

                $numSubCliente = substr($contenedor[0],0,5);
                $pesoReglamentario = 22;
                $numContenedor = str_replace(' ','',$contenedor[1]);

                $cotizaciones = new Cotizaciones;
                $cotizaciones->id_cliente = \Auth::User()->id_cliente;
                $cotizaciones->id_subcliente = $numSubCliente;
                $cotizaciones->origen = $contenedor[2];
                $cotizaciones->destino = $contenedor[3];
                $cotizaciones->tamano = $contenedor[4];
                $cotizaciones->peso_contenedor = $contenedor[5];
                $cotizaciones->bloque = $contenedor[6];
                $cotizaciones->bloque_hora_i = $contenedor[7];
                $cotizaciones->bloque_hora_f = $contenedor[8];

                $cotizaciones->otro = 0;
               // $cotizaciones->fecha_modulacion = $request->get('fecha_modulacion');
                //$cotizaciones->fecha_entrega = $request->get('fecha_entrega');
                $cotizaciones->iva = 0;
                $cotizaciones->retencion = 0;
                $cotizaciones->peso_reglamentario = $pesoReglamentario;
                $cotizaciones->precio_sobre_peso = 0;
                $cotizaciones->sobrepeso = ($contenedor[5] > $pesoReglamentario) ? $contenedor[5] - $pesoReglamentario : 0;
                $cotizaciones->estatus = ($request->has('uuid')) ? 'En espera' : 'Pendiente';
                $cotizaciones->precio_viaje = 0;
                $cotizaciones->burreo = 0;
                $cotizaciones->maniobra = 0;
                $cotizaciones->estadia = 0;
                $cotizaciones->base_factura = 0;
                $cotizaciones->base_taref = 0;
              //  $cotizaciones->recinto_clientes = $request->get('recinto_clientes');
                $cotizaciones->precio_tonelada = 0;

                $cotizaciones->total = 0;
                $cotizaciones->restante = 0;
                $cotizaciones->estatus_pago = '0';
                $cotizaciones->save();


                $docucotizaciones = new DocumCotizacion;
                $docucotizaciones->id_cotizacion = $cotizaciones->id;
                $docucotizaciones->num_contenedor = $numContenedor;
                $docucotizaciones->save();


            }

            DB::commit();
            return response()->json(["Titulo" => "Proceso satisfactorio", "Mensaje" => "Cotización creada con exito", "TMensaje" => "success"]);

        }catch(\Trhowable $t){
             DB::rollback();
            \Log::channel('daily')->info($t->getMessage());
            return response()->json(["Titulo" => "Ocurrion un error", "Mensaje" => "Ocurrio un error mientras procesabamos su solicitud. ".$t->getMessage(), "TMensaje" => "error"]);
        }
    }

    public function update_estatus(Request $request, $id)
{
    $request->validate([
        'estatus' => 'required',
    ]);

    $cotizaciones = Cotizaciones::findOrFail($id);
    $cotizaciones->estatus = $request->get('estatus');
    $cotizaciones->estatus_planeacion = null;
    $cotizaciones->save();

    if (in_array($request->get('estatus'), ['Cancelada', 'Pendiente'])) {
        if ($cotizaciones->DocCotizacion && $cotizaciones->DocCotizacion->Asignaciones) {
            $asignaciones = $cotizaciones->DocCotizacion->Asignaciones;
            $asignaciones->fecha_inicio = null;
            $asignaciones->fecha_fin = null;
            $asignaciones->save();
        }
    }



    if ($request->ajax()) {
        return response()->json(['success' => true, 'message' => 'Estatus actualizado correctamente.']);



    }


    return redirect()->route('index.cotizaciones')
        ->with('success', 'Estatus actualizado correctamente.');
}


    public function edit($id){
        $cotizacion = Cotizaciones::where('id', '=', $id)->first();
        $documentacion = DocumCotizacion::where('id_cotizacion', '=', $cotizacion->id)->first();
        $gastos_extras = GastosExtras::where('id_cotizacion', '=', $cotizacion->id)->get();
        //$clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();
        $clientes = Client::join('client_empresa as ce','clients.id','=','ce.id_client')
                            ->where('ce.id_empresa',Auth::User()->id_empresa)
                            ->where('is_active',1)
                            ->orderBy('nombre')->get();

        $gastos_ope = GastosOperadores::where('id_cotizacion', '=', $cotizacion->id)->get();
        $proveedores = Proveedor::where('id_empresa', '=', auth()->user()->id_empresa)->get();
       // dd( $proveedores );
        $bancos = Bancos::where('id_empresa',Auth::User()->id_empresa)->get();

        return view('cotizaciones.editv1', compact('bancos','cotizacion', 'documentacion', 'clientes','gastos_extras', 'gastos_ope','proveedores'));
    }

    public function cotizacionesFull(Request $request){
        $cotizaciones = Cotizaciones::leftJoin('docum_cotizacion as d','cotizaciones.id','=','d.id_cotizacion')
                                                ->where('referencia_full', '=', $request->uuid)
                                                ->orderBy('jerarquia')
                                                ->get();
        return $cotizaciones;
    }

    public function convertirFull(Request $request){
        $fullUUID = Common::generarUuidV4();
        $viajes = $request->seleccion;
       
        for($x = 0; $x < (sizeof($viajes)); $x++){
            $jerarquia = ($x == 0) ? 'Principal' : 'Secundario';
            Cotizaciones::where('id', $viajes[$x]['id'])->update(["tipo_viaje" => "Full","referencia_full" => $fullUUID, 'jerarquia' => $jerarquia]);
        }
        return response()->json(["Titulo" => "Proceso satisfactorio", "Mensaje" => "Se ha realizado la fusion de viajes a tipo Full", "TMensaje" => "success"]);
    }

    public function edit_externo($id){
        $cotizacion = Cotizaciones::where('id', '=', $id)->first();
        $documentacion = DocumCotizacion::where('id_cotizacion', '=', $cotizacion->id)->first();
        $gastos_extras = GastosExtras::where('id_cotizacion', '=', $cotizacion->id)->get();
        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();
        $gastos_ope = GastosOperadores::where('id_cotizacion', '=', $cotizacion->id)->get();
        $subclientes = Subclientes::where('id_cliente' ,'=',auth()->user()->id_cliente)->get();

        return view('cotizaciones.externos.edit', compact('cotizacion', 'documentacion', 'clientes','gastos_extras', 'gastos_ope', 'subclientes'));
    }

    public function pdf($id){
        $cotizacion = Cotizaciones::where('id', '=', $id)->first();
        $documentacion = DocumCotizacion::where('id_cotizacion', '=', $cotizacion->id)->first();
        $gastos_extras = GastosExtras::where('id_cotizacion', '=', $cotizacion->id)->get();
        $clientes = Client::get();
        $configuracion = Configuracion::first();
        $bancos_oficiales = Bancos::where('tipo', '=', 'Oficial')->get();
        $bancos_no_oficiales = Bancos::where('tipo', '=', 'No Oficial')->get();

        $fecha = date('Y-m-d');
        $fechaCarbon = Carbon::parse($fecha);

        $pdf = \PDF::loadView('cotizaciones.pdf', compact('cotizacion', 'documentacion', 'clientes','gastos_extras', 'configuracion', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales'));
        //return $pdf->stream();
        return $pdf->download('cotizacion'.$cotizacion->Cliente->nombre.'_#'.$cotizacion->id.'.pdf');
    }

    public function singleUpdate(Request $request, $id){
        try{

            DB::beginTransaction();
            $idEmpresa = auth()->user()->id_empresa;
            $numContenedor = str_replace(' ','',$request['num_contenedor']);

            $contenedorExistente = DocumCotizacion::where('num_contenedor', $numContenedor)
                                                ->where('id_empresa', $idEmpresa)
                                                ->where('id_cotizacion', '!=', $id)
                                                ->first();

            if ($contenedorExistente) {
                return response()->json(["Titulo" => "Contenedor $numContenedor creado previamente", "Mensaje" => "El contenedor ya existe en la empresa", "TMensaje" => "warning"]);
            }
            
            $doc_cotizaciones = DocumCotizacion::where('id_cotizacion', '=', $id)->first();
            $doc_cotizaciones->num_contenedor = $numContenedor;
            //$doc_cotizaciones->terminal = $contenedor['terminal'];
            //$doc_cotizaciones->num_autorizacion = $contenedor['num_autorizacion'];
            //$doc_cotizaciones->num_boleta_liberacion = $contenedor['num_boleta_liberacion'] || '';
            //$doc_cotizaciones->num_doda = $contenedor['num_doda'];
            //$doc_cotizaciones->num_carta_porte = $contenedor['num_carta_porte'];
            //$doc_cotizaciones->fecha_boleta_vacio = $contenedor['fecha_boleta_vacio'];
            //$doc_cotizaciones->ccp = $contenedor['ccp'];
            //$doc_cotizaciones->cima = $contenedor['cima'];
            $doc_cotizaciones->update();

            $cotizaciones = Cotizaciones::where('id', '=', $id)->first();
            $cotizaciones->id_cliente = $request->id_cliente;
            $cotizaciones->id_subcliente = $request->id_subcliente;
            $cotizaciones->origen = $request->origen;
            $cotizaciones->destino = $request->destino;
            $cotizaciones->direccion_entrega = $request['direccion_entrega'];
            $cotizaciones->uso_recinto = ($request->text_recinto == 'recinto-si') ? 1 : 0;
            $cotizaciones->direccion_recinto = $request->direccion_recinto ;
            
            $cotizaciones->fecha_modulacion = $request['fecha_modulacion'];
            $cotizaciones->fecha_entrega = $request['fecha_entrega'];
            
            $cotizaciones->tamano = $request['tamano'];
            $cotizaciones->peso_contenedor = $request['peso_contenedor'];
           
            $cotizaciones->bloque = $request['bloque'];
            $cotizaciones->bloque_hora_i = $request['bloque_hora_i'];
            $cotizaciones->bloque_hora_f = $request['bloque_hora_f'];
           
            $cotizaciones->sobrepeso = $request['sobrepeso'];
           
            //coordenadas para comparar
            $cotizaciones->latitud=  $request->latitud;
            $cotizaciones->longitud = $request->longitud;
            $cotizaciones->direccion_mapa = $request->direccion_mapa;

            $cotizaciones->save();

            DB::commit();

            return response()->json([
                "Titulo" => "Datos actualizados correctamente",
                "Mensaje" => "Los datos del viaje se han actualizado correctamente",
                "TMensaje" => "success",
            ]);

        }catch(\Throwable $t){
            DB::rollback();
            return response()->json([
                "Titulo" => "Error al actualizar",
                "Mensaje" => $t->getMessage(),
                "TMensaje" => "error",
            ]);
        }
    }

    public function update(Request $request, $id){
            $idEmpresa = auth()->user()->id_empresa;
            $Contenedores = $request->Contenedores;
            $referencia_full = null;
            if($request->TipoCotizacion == "Full"){
                $cotizaciones = Cotizaciones::where('id',$id)->first();
                $referencia_full = $cotizaciones->referencia_full;
            }
            DB::beginTransaction();
            //Primero validar si el contenedor existe
      
            foreach($Contenedores as $contenedor){

                $numContenedor = str_replace(' ','',$contenedor['num_contenedor']);

                if($request->TipoCotizacion == "Full"){
                    $cotizacion = Cotizaciones::where('referencia_full',$referencia_full)->where('jerarquia',$contenedor['jerarquia'])->first();
                    $id = $cotizacion->id;
                }

                $contenedorExistente = DocumCotizacion::where('num_contenedor', $numContenedor)
                                                    ->where('id_empresa', $idEmpresa)
                                                    ->where('id_cotizacion', '!=', $id)
                                                    ->first();

                if ($contenedorExistente) {
                    return response()->json(["Titulo" => "Contenedor $numContenedor creado previamente", "Mensaje" => "El contenedor ya existe en la empresa", "TMensaje" => "warning"]);
                }
            
            $doc_cotizaciones = DocumCotizacion::where('id_cotizacion', '=', $id)->first();
            $doc_cotizaciones->num_contenedor = $numContenedor;
            $doc_cotizaciones->terminal = $contenedor['terminal'];
            $doc_cotizaciones->num_autorizacion = $contenedor['num_autorizacion'];
            $doc_cotizaciones->num_boleta_liberacion = $contenedor['num_boleta_liberacion'] || '';
            $doc_cotizaciones->num_doda = $contenedor['num_doda'];
            $doc_cotizaciones->num_carta_porte = $contenedor['num_carta_porte'];
            $doc_cotizaciones->fecha_boleta_vacio = $contenedor['fecha_boleta_vacio'];
            $doc_cotizaciones->ccp = $contenedor['ccp'];
            $doc_cotizaciones->cima = $contenedor['cima'];
            $doc_cotizaciones->update();

            $cotizaciones = Cotizaciones::where('id', '=', $id)->first();
            $cotizaciones->id_cliente = $request->id_cliente;
            $cotizaciones->id_subcliente = $request->id_subcliente;
            $cotizaciones->origen = $request->origen;
            $cotizaciones->destino = $request->destino;
            $cotizaciones->direccion_entrega = $contenedor['direccion_entrega'];
            $cotizaciones->uso_recinto = ($request->text_recinto == 'recinto-si') ? 1 : 0;
            $cotizaciones->direccion_recinto = $request->direccion_recinto ;
            $cotizaciones->burreo = $request->burreo;
            $cotizaciones->estadia = $request->estadia;
            $cotizaciones->fecha_modulacion = $contenedor['fecha_modulacion'];
            $cotizaciones->fecha_entrega = $contenedor['fecha_entrega'];
            $cotizaciones->precio_viaje = $request->precio_viaje;
            $cotizaciones->tamano = $contenedor['tamano'];
            $cotizaciones->peso_contenedor = $contenedor['peso_contenedor'];
            $cotizaciones->maniobra = $request->maniobra;
            $cotizaciones->otro = $request->otro;
            $cotizaciones->iva = $request->iva;
            $cotizaciones->retencion = $request->retencion;
            $cotizaciones->bloque = $contenedor['bloque'];
            $cotizaciones->bloque_hora_i = $contenedor['bloque_hora_i'];
            $cotizaciones->bloque_hora_f = $contenedor['bloque_hora_f'];
            $cotizaciones->peso_reglamentario = $request->peso_reglamentario;
            $cotizaciones->fecha_eir = $contenedor['fecha_eir'];
            $cotizaciones->base_factura = $request->base_factura;
            $cotizaciones->base_taref = $request->base_taref;
            $cotizaciones->sobrepeso = $contenedor['sobrepeso'];
            $cotizaciones->precio_sobre_peso = $request->precioSobrePeso;
            //coordenadas para comparar
            $cotizaciones->total = $request->total;
            $cotizaciones->latitud=  $request->latitud;
            $cotizaciones->longitud = $request->longitud;
             $cotizaciones->direccion_mapa = $request->direccion_mapa;

           /* if ($request->hasFile("carta_porte")) {
                $file = $request->file('carta_porte');
                $path = public_path() . '/cotizaciones/cotizacion'. $id;
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $cotizaciones->carta_porte = $fileName;

                //Notificamos al cliente que se ha adjuntado el PDF de Carta Porte

                event(new \App\Events\GenericNotificationEvent([$cotizaciones->cliente->correo],'Se cargó Carta Porte: '.$doc_cotizaciones->num_contenedor,'Hola, tu transportista cargó el documento "Carta Porte" del contenedor '.$doc_cotizaciones->num_contenedor));
            }

*/
            $cotizaciones->restante = $request->total;

            $cotizaciones->latitud=  $request->latitud;
            $cotizaciones->longitud = $request->longitud;
             $cotizaciones->direccion_mapa = $request->direccion_mapa;
             
            

            $asignacion = Asignaciones::where('id_contenedor', $id)->first();
            if(!is_null($asignacion)){
                $cotizaciones->prove_restante = $request->get('total_proveedor');

                $asignacion->precio = $request->get('precio_proveedor');
                $asignacion->burreo = $request->get('burreo_proveedor');
                $asignacion->maniobra = $request->get('maniobra_proveedor');
                $asignacion->estadia = $request->get('estadia_proveedor');
                $asignacion->otro = $request->get('otro_proveedor');
                $asignacion->sobrepeso_proveedor = $request->get('sobrepeso_proveedor');
                $asignacion->total_tonelada = $request->get('total_tonelada');
                $asignacion->base1_proveedor = $request->get('base1_proveedor');
                $asignacion->base2_proveedor = $request->get('base2_proveedor');

                $asignacion->iva = $request->get('iva_proveedor');
                $asignacion->retencion = $request->get('retencion_proveedor');
                $asignacion->total_proveedor = $request->get('total_proveedor');
                $asignacion->id_proveedor = $request->id_proveedor;
                $asignacion->update();
            }

            $cotizaciones->update();

        }
           /* if($request->get('id_cliente_clientes') == NULL){
                $gasto_descripcion = $request->input('gasto_descripcion');
                $ticket_ids = $request->input('ticket_id');

                $suma =  $cotizaciones->total;
                $cotizaciones->total = $suma;
                $cotizaciones->restante = $cotizaciones->total;
                $cotizaciones->update();

                


                if ($asignacion) {
                    if($asignacion->id_proveedor != NULL){
                        
                    }
                    $cotizaciones->update();

                    if($asignacion->id_proveedor == NULL){

                    }else if($asignacion->id_operador == NULL){
                        
                    }
                }
            }*/
            DB::commit();

            return response()->json([
                                        "Titulo" => "Datos actualizados correctamente",
                                        "Mensaje" => "Los datos del viaje se han actualizado correctamente",
                                        "TMensaje" => "info"
                                    ]);
    }

    //Obtener una lista de gastos del contenedor

    public function get_gastos(Request $r){
        $numContenedor = $r->input('numContenedor');
        $idEmpresa = auth()->user()->id_empresa;

        $contenedor = DocumCotizacion::where('num_contenedor', $numContenedor)
                                            ->where('id_empresa', $idEmpresa)
                                            ->first();

        $gastosExtra = GastosExtras::where('id_cotizacion',$contenedor->id_cotizacion)->get();
        $gastosContenedor =
        $gastosExtra->map(function($g){
            return [
                "IdContenedor" => $g->id_cotizacion,
                "IdGasto" => $g->id,
                "Gasto" => $g->descripcion,
                "Monto" => $g->monto,
                "Fecha" => $g->created_at
            ];
        });

        return $gastosContenedor;
    }


    public function get_gastos_operador(Request $r){
        $numContenedor = $r->input('numContenedor');
        $idEmpresa = auth()->user()->id_empresa;

        $contenedor = DocumCotizacion::where('num_contenedor', $numContenedor)
                                            ->where('id_empresa', $idEmpresa)
                                            ->first();

        $gastosOperador = GastosOperadores::leftJoin('bancos','gastos_operadores.id_banco','=','bancos.id')
        ->where('id_cotizacion',$contenedor->id_cotizacion)
        ->select('bancos.nombre_banco','bancos.nombre_beneficiario','gastos_operadores.*')
        ->get();
        $gastosContenedor =
        $gastosOperador->map(function($g){
            return [
                "IdContenedor" => $g->id_cotizacion,
                "IdGasto" => $g->id,
                "Gasto" => $g->tipo,
                "Monto" => $g->cantidad,
                "Estatus" => $g->estatus,
                "Fecha" => Carbon::parse($g->created_at)->format('Y-m-d'),
                "FechaPago" => $g->fecha_pago,
                "BancoPago" => (!is_null($g->nombre_banco) ) ? $g->nombre_banco.' / '.$g->nombre_beneficiario : '',
            ];
        });

        return $gastosContenedor;
    }

    // Agregar Gastos Operador
    public function agregar_gasto_operador(Request $r){

        try{
            DB::beginTransaction();

             $numContenedor = $r->input('numContenedor');
             $idEmpresa = auth()->user()->id_empresa;

             $contenedor = DocumCotizacion::where('num_contenedor', $numContenedor)
                                                ->where('id_empresa', $idEmpresa)
                                                ->first();
                                                
             $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();

            if($r->pagoInmediato != "false" ){
                //validar que el banco tenga saldo suficiente para efectuar el pago del gasto
                $bancos = Bancos::where('id_empresa',Auth::User()->id_empresa)->where('id',$r->bancoPago)->first();
                $saldoActual = $bancos->saldo;

                if($saldoActual < $r->montoGasto){
                    return response()->json(["Titulo" => "Saldo insuficiente en Banco","Mensaje" => "La cuenta bancaria seleccionada no cuenta con saldo suficiente para registrar esta transacción","TMensaje" => "warning"]);
                }

                Bancos::where('id' ,'=',$r->bancoPago)->update(["saldo" => DB::raw("saldo - ". $r->montoGasto)]);

                $banco = new BancoDineroOpe;
                $banco->id_operador = $asignacion->id_operador;

                $banco->monto1 = $r->montoGasto;
                $banco->metodo_pago1 = 'Transferencia';
                $banco->descripcion_gasto = "Gasto ope: ".$r->descripcion;
                $banco->id_banco1 = $r->bancoPago;

                $contenedoresAbonos[] = [
                    'num_contenedor' => $numContenedor,
                    'abono' => $r->montoGasto
                ];
                $contenedoresAbonosJson = json_encode($contenedoresAbonos);

                $banco->contenedores = $contenedoresAbonosJson;

                $banco->tipo = 'Salida';
                $banco->fecha_pago = date('Y-m-d');
                $banco->save();
             }

             $datosGasto = [
                            "id_cotizacion" => $contenedor->id_cotizacion,
                            "id_banco" => $r->pagoInmediato != "false" ? $r->bancoPago : null,
                            "id_asignacion" => $asignacion->id,
                            "id_operador" => $asignacion->id_operador,
                            "cantidad" => $r->montoGasto,
                            "tipo" => $r->descripcion,
                            "estatus" => $r->pagoInmediato != "false" ? 'Pagado' : 'Pago Pendiente',
                            "fecha_pago" => $r->pagoInmediato != "false" ? Carbon::now() : null,
                            "pago_inmediato" => $r->pagoInmediato != "false" ? 1 : 0,
                            "created_at" => Carbon::now()
                           ];

             GastosOperadores::insert($datosGasto);

             DB::commit();
             return response()->json(["Titulo" => "Gasto agregado","Mensaje" => "Se agregó el gasto: \"{$r->descripcion}\"","TMensaje" => "success", "pagoInmediato"=> boolval($r->pagoInmediato)]);

        }catch(\Throwable $t){
            DB::rollback();
            $idError = uniqid();
            \Log::channel('daily')->info("$idError : ".$t->getMessage());
            return response()->json([
                "Titulo" => "Ha ocurrido un error",
                "Mensaje" => "Ocurrio un error mientras procesabamos su solicitud. Cod Error $idError",
                "TMensaje" => "warning"
            ]);
        }
    }

    public function pagar_gasto_operador(Request $r){
        try{

            DB::beginTransaction();
            $bancos = Bancos::where('id_empresa',Auth::User()->id_empresa)->where('id',$r->bank)->first();
            $saldoActual = $bancos->saldo;

            if($saldoActual < $r->totalPago){
                return response()->json(["Titulo" => "Saldo insuficiente en Banco","Mensaje" => "La cuenta bancaria seleccionada no cuenta con saldo suficiente para registrar esta transacción","TMensaje" => "warning"]);
            }

            Bancos::where('id' ,'=',$r->bank)->update(["saldo" => DB::raw("saldo - ". $r->totalPago)]);

            $idEmpresa = auth()->user()->id_empresa;

            $contenedor = DocumCotizacion::where('num_contenedor', $r->numContenedor)
                                                ->where('id_empresa', $idEmpresa)
                                                ->first();

            $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();

            $banco = new BancoDineroOpe;
            $banco->id_operador = $asignacion->id_operador;

            $banco->monto1 = $r->totalPago;
            $banco->metodo_pago1 = 'Transferencia';
            $banco->descripcion_gasto = "Pago Gastos Operador";
            $banco->id_banco1 = $r->bank;

            $pagando = $r->gastosPagar;

            foreach( $pagando as $c){
                $contenedoresAbonos[] = [
                    'num_contenedor' => $r->numContenedor,
                    'abono' => $c['Monto']
                ];


            }

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

    public function eliminar_gasto_operador(Request $r){
        try{
            DB::beginTransaction();
            $gastos = $r->seleccionEliminarPago;
            foreach($gastos as $g){
                $gasto = GastosOperadores::where('id',$g['IdGasto'])->first();
                //Devolver el dinero al banco, siempre y cuando el estatus sea "Pagado"
                if($gasto->estatus === "Pagado"){
                    Bancos::where('id' ,'=',$gasto->id_banco)->update(["saldo" => DB::raw("saldo + ". $gasto->cantidad)]);

                    $asignacion = Asignaciones::where('id_contenedor', $g['IdContenedor'])->first();

                    $banco = new BancoDineroOpe;
                    $banco->id_operador = $asignacion->id_operador;

                    $banco->monto1 = $gasto->cantidad;
                    $banco->metodo_pago1 = 'Transferencia';
                    $banco->descripcion_gasto = "Gasto Anulado: ".$g['Gasto'];
                    $banco->id_banco1 = $gasto->id_banco;

                    $contenedoresAbonos[] = [
                        'num_contenedor' => $r->numContenedor,
                        'abono' => $gasto->cantidad
                    ];
                    $contenedoresAbonosJson = json_encode($contenedoresAbonos);

                    $banco->contenedores = $contenedoresAbonosJson;

                    $banco->tipo = 'Entrada';
                    $banco->fecha_pago = date('Y-m-d');
                    $banco->save();

                }

                $gasto->delete();
            }

            DB::commit();
            return response()->json(["Titulo" => "Eliminado Correctamente", "Mensaje" => "El gasto fue eliminado.", "TMensaje" => "success"]);

        }catch(\Trhowable $t){
            DB::rollback();
            return response()->json(["Titulo" => "No pudimos eliminar el gasto", "Mensaje" => "Lo sentimos, ocurrio un error $t->getMessage()", "TMensaje" => "error"]);

        }
    }

    //Agregar gastos a cotizacion
    public function agregar_gasto_cotizacion(Request $r){
        try{
            $numContenedor = $r->input('numContenedor');
            $idEmpresa = auth()->user()->id_empresa;
            DB::beginTransaction();
            $contenedor = DocumCotizacion::where('num_contenedor', $numContenedor)
                                                ->where('id_empresa', $idEmpresa)
                                                ->first();
            $data = array(
                'id_cotizacion' => $contenedor->id_cotizacion,
                'descripcion' => $r->descripcion,
                'monto' => $r->montoGasto,
            );

            GastosExtras::create($data);

           // $gastosContenedor = GastosExtras::where('id_cotizacion',$contenedor->id_cotizacion)->get();
           // $totalGastos = $gastosContenedor->sum('monto');

            Cotizaciones::where('id',$contenedor->id_cotizacion)->update(["restante" => DB::raw('restante + '.$r->montoGasto)]);
            DB::commit();
            return response()->json(["TMensaje" => "success", "Mensaje" => "Agregado correctamente", "Titulo" => "Gasto agregado"]);
        }catch(\Trhowable $t){
            DB::rollback();
            return response()->json(["TMensaje" => "error", "Mensaje" => $t->getMessage(), "Titulo" => "Error al agregar gasto"]);

        }

    }

    public function eliminar_gasto_cotizacion(Request $r){
        try{
            $numContenedor = $r->input('numContenedor');
            $idEmpresa = auth()->user()->id_empresa;
            DB::beginTransaction();
            $contenedor = DocumCotizacion::where('num_contenedor', $numContenedor)
                                         ->where('id_empresa', $idEmpresa)
                                         ->first();

            $gastos = Collect($r->seleccionGastos);
            $eliminar = $gastos->pluck('IdGasto');

            $gastosContenedor = GastosExtras::where('id_cotizacion',$contenedor->id_cotizacion)->whereIn('id',$eliminar);
            $totalGastosDescontar = $gastosContenedor->sum('monto');

            $gastosContenedor->delete();

            Cotizaciones::where('id',$contenedor->id_cotizacion)->update(["restante" => DB::raw('restante - '.$totalGastosDescontar)]);

            DB::commit();
            return response()->json(["TMensaje" => "success", "Mensaje" => "Eliminado correctamente", "Titulo" => "Gasto eliminado del contenedor"]);
        }catch(\Trhowable $t){
            DB::rollback();
            return response()->json(["TMensaje" => "error", "Mensaje" => $t->getMessage(), "Titulo" => "Error al agregar gasto"]);

        }


    }

    public function update_cambio(Request $request, $id){
        // Capturar todos los inputs
        $inputs = $request->all();

        // Encontrar el radio input seleccionado
        $tipo_cambio = null;
        foreach ($inputs as $key => $value) {
            if (strpos($key, 'formType') === 0 && $value) {
                $tipo_cambio = $value;
                break;
            }
        }


        $asignacion = Asignaciones::find($id);

        if ($tipo_cambio  == 'propio') {
            // Cambiar a propio
            $asignacion->id_proveedor = null;
            $asignacion->precio = null;
            $asignacion->burreo = null;
            $asignacion->maniobra = null;
            $asignacion->estadia = null;
            $asignacion->otro = null;
            $asignacion->iva = null;
            $asignacion->retencion = null;
            $asignacion->total_proveedor = null;
            $asignacion->id_camion = $request->camion;
            $asignacion->id_chasis = $request->chasis;
            $asignacion->id_dolys = $request->nuevoCampoDoly;
            $asignacion->id_operador = $request->operador;
            $asignacion->id_chasis2 = $request->chasisAdicional1;
            $asignacion->fecha_inicio = $request->fecha_inicio;
            $asignacion->fecha_fin = $request->fecha_fin . ' 23:00:00';
            $asignacion->sueldo_viaje = $request->sueldo_viaje;
            $asignacion->dinero_viaje = $request->dinero_viaje;
            $asignacion->id_banco1_dinero_viaje = $request->id_banco1_dinero_viaje;
            $asignacion->cantidad_banco1_dinero_viaje = $request->cantidad_banco1_dinero_viaje;
            $asignacion->id_banco2_dinero_viaje = $request->id_banco2_dinero_viaje;
            $asignacion->cantidad_banco2_dinero_viaje = $request->cantidad_banco2_dinero_viaje;
            $asignacion->id_banco1_pago_operador = $request->id_banco1_pago_operador;
            $asignacion->cantidad_banco1_pago_operador = $request->cantidad_banco1_pago_operador;
            $asignacion->id_banco2_pago_operador = $request->id_banco2_pago_operador;
            $asignacion->cantidad_banco2_pago_operador = $request->cantidad_banco2_pago_operador;
            $asignacion->fecha_pago_salida = date('Y-m-d');
            $asignacion->estatus_pagado = 'Pendiente Pago';
            $asignacion->update();
        } else if ($tipo_cambio  == 'subcontratado'){
            // Cambiar a subcontratado
            $asignacion->id_camion = null;
            $asignacion->id_chasis = null;
            $asignacion->id_dolys = null;
            $asignacion->id_operador = null;
            $asignacion->id_chasis2 = null;
            $asignacion->sueldo_viaje = null;
            $asignacion->dinero_viaje = null;
            $asignacion->id_banco1_dinero_viaje = null;
            $asignacion->cantidad_banco1_dinero_viaje = null;
            $asignacion->id_banco2_dinero_viaje = null;
            $asignacion->cantidad_banco2_dinero_viaje = null;
            $asignacion->id_banco1_pago_operador = null;
            $asignacion->cantidad_banco1_pago_operador = null;
            $asignacion->id_banco2_pago_operador = null;
            $asignacion->cantidad_banco2_pago_operador = null;
            $asignacion->fecha_pago_salida = null;
            $asignacion->fecha_pago_operador = null;
            // Actualizar otros campos según el formulario de subcontratado
            $asignacion->id_proveedor = $request->id_proveedor;
            $asignacion->precio = $request->precio;
            $asignacion->burreo = $request->cot_burreo;
            $asignacion->maniobra = $request->cot_maniobra;
            $asignacion->estadia = $request->cot_estadia;
            $asignacion->otro = $request->cot_otro;
            $asignacion->iva = $request->cot_iva;
            $asignacion->retencion = $request->cot_retencion;
            $asignacion->total_proveedor = $request->total_proveedor;
            $asignacion->fecha_inicio = $request->fecha_inicio_proveedor;
            $asignacion->fecha_fin = $request->fecha_fin_proveedor . ' 23:00:00';
            $asignacion->update();

            $doc = DocumCotizacion::where('id',  '=', $asignacion->id_contenedor)->first();

            $cotizacionesPorPagar = Cotizaciones::where('id', '=', $doc->id_cotizacion)->first();
            $cotizacionesPorPagar->prove_restante = $request->total_proveedor;
            $cotizacionesPorPagar->update();
        }



        return redirect()->back()->with('success', 'Ha sido cambiado exitosamente.');
    }

    public function adjuntarDocumentos(Request $r){

        include('Fileuploader/class.fileuploader.php');
        $cotizacionQuery = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
        ->where('d.num_contenedor',$r->numContenedor);

        $cotizacion = $cotizacionQuery->first();
        
        if(is_null($cotizacion)){
            $upload['hasWarnings'] = true;
            $upload['warnings'] = ['Guarde los datos del viaje antes de cargar archivos'];
            return response()->json($upload);
            exit;
        }

        $estatus = $cotizacion->estatus;

        $directorio =  public_path().'/cotizaciones/cotizacion'.$cotizacion->id;
        if (!is_dir($directorio)) {
            mkdir($directorio);
        }
		$FileUploader = new FileUploader('files', array(
        'uploadDir' => public_path()."/cotizaciones/cotizacion$cotizacion->id/",
        ));

	// call to upload the files
		$upload = $FileUploader->upload();
		if ($upload['isSuccess']) {
			foreach($upload['files'] as $key=>$item) {
				$upload['files'][$key] = array(
					'extension' => $item['extension'],
					'format' => $item['format'],
					'file' =>   public_path()."/cotizaciones/cotizacion$cotizacion->id/".$item['name'],
					'name' => $item['name'],
					'old_name' => $item['old_name'],
					'size' => $item['size'],
					'size2' => $item['size2'],
					'title' => $item['title'],
					'type' => $item['type'],
					'url' => asset( $directorio.'/'.$item['name'])
				);

                //$fileName = uniqid() . $item['name'];
			}

			$json = $upload['files'];
            //   $upload['typeOfDocument'] = $r->urlRepo;
            switch($r->urlRepo){
                case 'BoletaLib': $update = ["boleta_liberacion" => $item['name']]; break;
                case 'Doda': $update = ["doda" => $item['name']]; break;
                case 'CartaPorte': $update = ["doc_ccp" => $item['name'],"ccp" => "si"]; break;
                case 'PreAlta': $update = ["img_boleta" => $item['name']]; break;
                case 'CartaPortePDF': $update = ["carta_porte" => $item['name']]; break;
                case 'CartaPorteXML': $update = ["carta_porte_xml" => $item['name']]; break;
                case 'EIR': $update = ["doc_eir" => $item['name'], 'eir' => "si"]; break;
                case 'CCP': $update = ["doc_ccp" => $item['name'], 'ccp' => "si"]; break;

            }
          
            ($r->urlRepo != 'PreAlta' && $r->urlRepo != 'CartaPortePDF' && $r->urlRepo != 'CartaPorteXML')
            ? DocumCotizacion::where('id',$cotizacion->id)->update($update)
            : Cotizaciones::where('id',$cotizacion->id)->update($update);

            if ($r->urlRepo == 'PreAlta')  DocumCotizacion::where('id',$cotizacion->id)->update(['boleta_vacio'=>'si']);

            if(Auth::User()->id_cliente != 0){
                event(new \App\Events\GenericNotificationEvent([$cotizacion->cliente->correo],'Se cargó '.$r->urlRepo.': '.$r->numContenedor,'Hola, tu transportista cargó el documento "'.$r->urlRepo.'" del contenedor '.$r->numContenedor));
                event(new \App\Events\ConfirmarDocumentosEvent($cotizacion->id));
            }

            if($estatus != 'En espera' && Auth::User()->id_cliente != 0){
                event(new \App\Events\NotificaNuevoDocumentoEvent($cotizacion,$r->urlRepo));
            }

		}
		return response()->json($upload);
		exit;
	}

    /**
     * Esta metodo se utiliza para asignar los contenedores solicitados desde el modulo de clientes externos
     */
    public function asignar_empresa(Request $request){
        try{
            DB::beginTransaction();
            $contenedores = json_decode($request->seleccionContenedores);

            foreach($contenedores as $c){
                $cotizaciones = DB::table('cotizaciones')
                ->where('id', $c->IdContenedor)
                ->update([
                    'id_empresa' => $request->empresa,
                    'estatus' => 'Pendiente'
                ]);

                $contenedores = DB::table('docum_cotizacion')
                ->where('id_cotizacion',  '=', $c->IdContenedor)
                ->update(['id_empresa' => $request->empresa]);

                //Verificar que el cliente este visible en la empresa a la que se asigna

               // $cotizacion = Cotizaciones::where('id',$c->IdContenedor)->first();

                $clientEmpresa = ClientEmpresa::where('id_client',$c->IdCliente)->where('id_empresa',$request->empresa);
                if(!$clientEmpresa->exists()){
                    ClientEmpresa::create([
                        'id_client' => $c->IdCliente,
                        'id_empresa' => $request->empresa
                    ]);
                }
            }

            DB::commit();
            return response()->json(["TMensaje" => "success", "Mensaje" => "Contenedores asignados correctamente","Titulo" => "Proceso satisfactorio"]);
        }catch(\Trhowable $t){
            DB::rollback();
            return response()->json(["TMensaje" => "error", "Mensaje" => "No fue posible asignar los contenedores. $t->getMessage()","Titulo" => "No asignado"]);

        }


    }

    public function cambiar_empresa(Request $request,$id){
        // Obtener la cotización actual
        $cotizacion = DB::table('cotizaciones')->where('id', $id)->first();
        $doc = DocumCotizacion::where('id_cotizacion', $id)->first();

        $idEmpresa = $request->get('id_empresa');

        $clientEmpresa = ClientEmpresa::where('id_client',$cotizacion->id_cliente)->where('id_empresa',$idEmpresa);
        if(!$clientEmpresa->exists()){
            ClientEmpresa::create([
                'id_client' => $cotizacion->id_cliente,
                'id_empresa' => $idEmpresa
            ]);
        }

        if($doc->num_contenedor != NULL){
            $numContenedor = $doc->num_contenedor;


            $contenedorExistente = DocumCotizacion::where('num_contenedor', $numContenedor)
                                                ->where('id_empresa', $idEmpresa)
                                                ->first();

            if ($contenedorExistente) {
                return redirect()->back()->with('error', 'El contenedor ya existe en la empresa a la que se iba a asignar.');
            }
        }

        // Obtener el id_cliente actual de la empresa anterior
      /*  $idClienteAnterior = DB::table('clients')
            ->where('id', $cotizacion->id_cliente)
            ->value('id');

        // Obtener el correo del cliente anterior
        $correoCliente = DB::table('clients')
            ->where('id', $idClienteAnterior)
            ->value('correo');

        // Verificar si hay un cliente con el mismo correo en la nueva empresa
        $nuevoIdEmpresa = $request->get('id_empresa');
        $nuevoIdCliente = DB::table('clients')
            ->where('correo', $correoCliente)
            ->where('id_empresa', $nuevoIdEmpresa)
            ->value('id');

        if($cotizacion->id_subcliente != NULL){
            // Obtener el id_subcliente actual de la empresa anterior
            $idSubClienteAnterior = DB::table('subclientes')
            ->where('id', $cotizacion->id_subcliente)
            ->value('id');

            // Obtener el correo del cliente anterior
            $correoSubCliente = DB::table('subclientes')
            ->where('id', $idSubClienteAnterior)
            ->value('correo');

            // Verificar si hay un cliente con el mismo correo en la nueva empresa
            $nuevoIdSubCliente = DB::table('subclientes')
                ->where('correo', $correoSubCliente)
                ->where('id_empresa', $nuevoIdEmpresa)
                ->value('id');

            if ($nuevoIdSubCliente) {

            } else {
                return redirect()->route('index.cotizaciones')
                    ->with('error', 'No tiene SubCliente con el mismo correo a la empresa que quiere cambiar');
            }
        }else{
            $nuevoIdSubCliente = NULL;
        }*/

            $contenedor = DocumCotizacion::where('id_cotizacion',  '=', $cotizacion->id)->first();
            $nuevoIdEmpresa = $request->get('id_empresa');
            if ($contenedor) {
                $asignacionExiste = Asignaciones::where('id_contenedor', '=', $contenedor->id)->exists();

                if ($asignacionExiste) {
                    // Obtener la asignación correspondiente
                    $asignacion = Asignaciones::where('id_contenedor', '=', $contenedor->id)->first();

                    // Obtener los datos necesarios del request


                    // Verificar si id_operador es null y actualizar id_proveedor
                    if (is_null($asignacion->id_operador)) {
                        // Obtener el id_proveedor actual
                        $idProveedorAnterior = $asignacion->id_proveedor;

                        // Obtener el correo del proveedor anterior
                        $correoProveedor = DB::table('proveedores')
                            ->where('id', $idProveedorAnterior)
                            ->value('correo');

                        // Buscar el nuevo id_proveedor en la nueva empresa
                        $nuevoIdProveedor = DB::table('proveedores')
                            ->where('correo', $correoProveedor)
                            ->where('id_empresa', $nuevoIdEmpresa)
                            ->value('id');

                        if ($nuevoIdProveedor) {
                            // Actualizar id_proveedor y id_empresa
                            DB::table('asignaciones')
                                ->where('id_contenedor', '=', $contenedor->id)
                                ->update([
                                    'id_empresa' => $nuevoIdEmpresa,
                                    'id_proveedor' => $nuevoIdProveedor
                                ]);
                        }else{
                            $ProveedorAnterior = DB::table('proveedores')
                            ->where('id', $idProveedorAnterior)
                            ->first();

                            $proveedor = new Proveedor;
                            $proveedor->nombre = $ProveedorAnterior->nombre;
                            $proveedor->correo = $ProveedorAnterior->correo;
                            $proveedor->telefono = $ProveedorAnterior->telefono;
                            $proveedor->id_empresa = $request->get('id_empresa');
                            $proveedor->save();

                            DB::table('asignaciones')
                            ->where('id_contenedor', '=', $contenedor->id)
                            ->update([
                                'id_empresa' => $nuevoIdEmpresa,
                                'id_proveedor' => $proveedor->id
                            ]);
                        }
                    }

                    // Verificar si id_proveedor es null y actualizar id_operador
                    if (is_null($asignacion->id_proveedor)) {
                        // Obtener el id_operador actual
                        $idOperadorAnterior = $asignacion->id_operador;

                        // Obtener el correo del operador anterior
                        $correoOperador = DB::table('operadores')
                            ->where('id', $idOperadorAnterior)
                            ->value('correo');

                        // Buscar el nuevo id_operador en la nueva empresa
                        $nuevoIdOperador = DB::table('operadores')
                            ->where('correo', $correoOperador)
                            ->where('id_empresa', $nuevoIdEmpresa)
                            ->value('id');

                        if ($nuevoIdOperador) {
                            // Actualizar id_operador y id_empresa
                            DB::table('asignaciones')
                                ->where('id_contenedor', '=', $contenedor->id)
                                ->update([
                                    'id_empresa' => $nuevoIdEmpresa,
                                    'id_operador' => $nuevoIdOperador
                                ]);
                        }else{
                            $OperadorAnterior = DB::table('operadores')
                            ->where('id', $idOperadorAnterior)
                            ->first();

                            $proveedor = new Operador;
                            $proveedor->nombre = $OperadorAnterior->nombre;
                            $proveedor->correo = $OperadorAnterior->correo;
                            $proveedor->telefono = $OperadorAnterior->telefono;
                            $proveedor->id_empresa = $request->get('id_empresa');
                            $proveedor->save();

                            DB::table('asignaciones')
                            ->where('id_contenedor', '=', $contenedor->id)
                            ->update([
                                'id_empresa' => $nuevoIdEmpresa,
                                'id_operador' => $proveedor->id
                            ]);
                        }
                    }
                }
            }

            // Actualizar la cotización con el nuevo id_empresa y id_cliente
            $cotizaciones = DB::table('cotizaciones')
            ->where('id', $id)
            ->update([
                'id_empresa' => $nuevoIdEmpresa
            ]);

            $contenedores = DB::table('docum_cotizacion')
            ->where('id_cotizacion',  '=', $cotizacion->id)
            ->update(['id_empresa' => $request->get('id_empresa')]);

            return redirect()->route('index.cotizaciones')
                ->with('success', 'Se ha editado sus datos con exito');

    }
    public function enviarCorreo(Request $request)
    {
        $datos = $request->only('correo', 'asunto', 'mensaje','link');
        \Log::info('Evento disparado con datos: ', $datos);
        event(new \App\Events\EnvioCorreoCoordenadasEvent($datos));

        return response()->json(['success' => true]);
    }



}
