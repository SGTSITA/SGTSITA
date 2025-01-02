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
use Illuminate\Http\Request;
use Session;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Validator;

class CotizacionesController extends Controller
{
    public function index(){

        $cotizaciones_planeadas = Cotizaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('estatus','=','Aprobada')->where('estatus_planeacion','=', 1)->orderBy('created_at', 'desc')
        ->select('id_cliente', 'origen', 'destino', 'id', 'estatus')->get();

        $equipos_dolys = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Dolys')->get();
        $equipos_chasis = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Chasis / Plataforma')->get();
        $equipos_camiones = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Tractos / Camiones')->get();
        $operadores = Operador::get();
        $bancos = Bancos::get();
        $proveedores = Proveedor::get();
        $empresas = Empresas::get();

        return view('cotizaciones.index', compact('empresas', 'proveedores','bancos','operadores','equipos_dolys','equipos_chasis','equipos_camiones','cotizaciones_planeadas'));
    }

    public function index_externo(){

        $cotizaciones_emitidas = Cotizaciones::where('id_cliente' ,'=', auth()->user()->id_cliente)->orderBy('created_at', 'desc')
        ->select('id_cliente', 'origen', 'destino', 'id', 'estatus')->get();

        return view('cotizaciones.externos.index', compact('cotizaciones_emitidas'));
    }

    public function index_finzaliadas(){

        $cotizaciones_finalizadas = Cotizaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('estatus','=','Finalizado')->orderBy('created_at', 'desc')
        ->select('id_cliente', 'origen', 'destino', 'id', 'estatus')->get();

        $equipos_dolys = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Dolys')->get();
        $equipos_chasis = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Chasis / Plataforma')->get();
        $equipos_camiones = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Tractos / Camiones')->get();
        $operadores = Operador::get();
        $bancos = Bancos::get();
        $proveedores = Proveedor::get();
        $empresas = Empresas::get();

        return view('cotizaciones.index_finalizadas', compact('empresas', 'proveedores','bancos','operadores','equipos_dolys','equipos_chasis','equipos_camiones','cotizaciones_finalizadas'));
    }

    public function index_espera(){

        $cotizaciones = Cotizaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('estatus','=','Pendiente')->orderBy('created_at', 'desc')
        ->select('id_cliente', 'origen', 'destino', 'id', 'estatus')->get();

        $equipos_dolys = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Dolys')->get();
        $equipos_chasis = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Chasis / Plataforma')->get();
        $equipos_camiones = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Tractos / Camiones')->get();
        $operadores = Operador::get();
        $bancos = Bancos::get();
        $proveedores = Proveedor::get();
        $empresas = Empresas::get();

        return view('cotizaciones.index_espera', compact('empresas', 'proveedores','bancos','operadores','equipos_dolys','equipos_chasis','equipos_camiones','cotizaciones'));
    }

    public function index_aprobadas(){

        $cotizaciones_aprovadas = Cotizaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('estatus','=','Aprobada')
        ->where(function($query) {
            $query->where('estatus_planeacion', '=', 0)
                  ->orWhere('estatus_planeacion', '=', NULL);
        })->orderBy('created_at', 'desc')
        ->select('id_cliente', 'origen', 'destino', 'id', 'estatus')->get();

        $equipos_dolys = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Dolys')->get();
        $equipos_chasis = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Chasis / Plataforma')->get();
        $equipos_camiones = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Tractos / Camiones')->get();
        $operadores = Operador::get();
        $bancos = Bancos::get();
        $proveedores = Proveedor::get();
        $empresas = Empresas::get();

        return view('cotizaciones.index_aprovada', compact('empresas', 'proveedores','bancos','operadores','equipos_dolys','equipos_chasis','equipos_camiones','cotizaciones_aprovadas'));
    }

    public function index_canceladas(){

        $cotizaciones_canceladas = Cotizaciones::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('estatus','=','Cancelada')->orderBy('created_at', 'desc')
        ->select('id_cliente', 'origen', 'destino', 'id', 'estatus')->get();

        $equipos_dolys = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Dolys')->get();
        $equipos_chasis = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Chasis / Plataforma')->get();
        $equipos_camiones = Equipo::where('id_empresa' ,'=',auth()->user()->id_empresa)->where('tipo','=','Tractos / Camiones')->get();
        $operadores = Operador::get();
        $bancos = Bancos::get();
        $proveedores = Proveedor::get();
        $empresas = Empresas::get();

        return view('cotizaciones.index_cancelada', compact('empresas', 'proveedores','bancos','operadores','equipos_dolys','equipos_chasis','equipos_camiones','cotizaciones_canceladas'));
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
        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();

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

    public function store(Request $request){
        
        if($request->get('num_contenedor') != NULL){
            $numContenedor = $request->input('num_contenedor');
            $idEmpresa = auth()->user()->id_empresa;

            $contenedorExistente = DocumCotizacion::where('num_contenedor', $numContenedor)
                                                ->where('id_empresa', $idEmpresa)
                                                ->first();

            if ($contenedorExistente) {
                return response()->json(["Titulo" => "Contenedor creado previamente", "Mensaje" => "El contenedor ya existe en la empresa", "TMensaje" => "warning"]);
               // return redirect()->back()->with('error', 'El contenedor ya existe en la empresa.');
            }
        }

        if($request->get('id_cliente_clientes') !== NULL){
            $cliente = $request->get('id_cliente_clientes');
        }else if($request->get('nombre_cliente') == NULL){
            $cliente = $request->get('id_cliente');
        }else{
            $cliente = new Client;
            $cliente->nombre = $request->get('nombre_cliente');
            $cliente->correo = $request->get('correo_cliente');
            $cliente->telefono = $request->get('telefono_cliente');
            $cliente->id_empresa = auth()->user()->id_empresa;
            $cliente->save();

            $cliente = $cliente->id;
        }

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
        $cotizaciones->precio_tonelada = $request->get('precio_tonelada');

        if($request->get('id_cliente_clientes') == NULL){
            $precio_tonelada = str_replace(',', '', $request->get('precio_tonelada'));
            $cotizaciones->precio_tonelada = $precio_tonelada;

            if($request->get('total') == NULL){
                $total = 0;
             }else{
                $total = str_replace(',', '', $request->get('total'));
            }
        }else{
            $cotizaciones->precio_tonelada = $request->get('precio_tonelada');
            $total = 0;
        }

        $cotizaciones->total = $total;
        $cotizaciones->restante = $cotizaciones->total;
        $cotizaciones->estatus_pago = '0';
        $cotizaciones->save();

        $doc_cotizaciones = Cotizaciones::where('id', '=', $cotizaciones->id)->first();
        if ($request->hasFile("excel_clientes")) {
            $file = $request->file('excel_clientes');
            $path = public_path() . '/cotizaciones/cotizacion'. $cotizaciones->id;
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move($path, $fileName);
            $doc_cotizaciones->excel_clientes = $fileName;
        }
        $doc_cotizaciones->update();

        $docucotizaciones = new DocumCotizacion;
        $docucotizaciones->id_cotizacion = $cotizaciones->id;
        $docucotizaciones->num_contenedor = $request->get('num_contenedor');
        $docucotizaciones->save();

        return response()->json(["Titulo" => "Proceso satisfactorio", "Mensaje" => "Cotización creada con exito", "TMensaje" => "success"]);


        if($request->get('id_cliente_clientes')){
            Session::flash('success', 'Se ha guardado sus datos con exito');
            return redirect()->route('index.cotizaciones')
                ->with('success', 'Cotizacion created successfully.');
        }else{
            Session::flash('success', 'Se ha guardado sus datos con exito');
            return redirect()->route('index.cotizaciones_manual')
                ->with('success', 'Cotizacion created successfully.');
        }
    }

    public function storeMultiple(Request $request){
        try{
            DB::beginTransaction();
            $contenedores = $request->contenedores;
            $row = 1;

            foreach($contenedores as $cont){
                //validaremos que los contenedores no existan
                $numContenedor = $cont[1];
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
                $numContenedor = $contenedor[1];

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

    public function update_estatus(Request $request, $id){
        $this->validate($request, [
            'estatus' => 'required',
        ]);

        $cotizaciones = Cotizaciones::find($id);
        $cotizaciones->estatus = $request->get('estatus');
        $cotizaciones->estatus_planeacion = null;
        $cotizaciones->update();


        if($request->get('estatus') == 'Cancelada' || $request->get('estatus') == 'Pendiente'){
            if($cotizaciones->DocCotizacion){
                if($cotizaciones->DocCotizacion->Asignaciones){
                    $asignaciones_id = $cotizaciones->DocCotizacion->Asignaciones->id;
                    $asignaciones = Asignaciones::find($asignaciones_id);

                        $asignaciones->fecha_inicio = null;
                        $asignaciones->fecha_fin = null;

                    $asignaciones->update();
                }
            }
        }

        Session::flash('edit', 'Se ha editado sus datos con exito');
        return redirect()->route('index.cotizaciones')
            ->with('success', 'Estatus updated successfully');
    }

    public function edit($id){
        $cotizacion = Cotizaciones::where('id', '=', $id)->first();
        $documentacion = DocumCotizacion::where('id_cotizacion', '=', $cotizacion->id)->first();
        $gastos_extras = GastosExtras::where('id_cotizacion', '=', $cotizacion->id)->get();
        $clientes = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->get();
        $gastos_ope = GastosOperadores::where('id_cotizacion', '=', $cotizacion->id)->get();
        $proveedores = Proveedor::where('id_empresa', '=', auth()->user()->id_empresa)->get();

        return view('cotizaciones.edit', compact('cotizacion', 'documentacion', 'clientes','gastos_extras', 'gastos_ope','proveedores'));
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

    public function update(Request $request, $id){

            $numContenedor = $request->input('num_contenedor');
            $idEmpresa = auth()->user()->id_empresa;

            // Verificar si se está editando un registro existente
            if ($numContenedor != NULL) {
                // Verificar si el contenedor ya existe en la misma empresa, excluyendo el registro actual
                $contenedorExistente = DocumCotizacion::where('num_contenedor', $numContenedor)
                                                    ->where('id_empresa', $idEmpresa)
                                                    ->where('id_cotizacion', '!=', $id) // Excluir el registro actual
                                                    ->first();

                if ($contenedorExistente) {
                    // Si el contenedor ya existe, redirigir a la vista con un mensaje de error
                    return redirect()->back()->with('error', 'El contenedor ya existe en la empresa.');
                }
            }

            $doc_cotizaciones = DocumCotizacion::where('id_cotizacion', '=', $id)->first();
            $doc_cotizaciones->num_contenedor = $request->get('num_contenedor');
            $doc_cotizaciones->terminal = $request->get('terminal');
            $doc_cotizaciones->num_autorizacion = $request->get('num_autorizacion');
            $doc_cotizaciones->num_boleta_liberacion = $request->get('num_boleta_liberacion');
            $doc_cotizaciones->num_doda = $request->get('num_doda');
            $doc_cotizaciones->num_carta_porte = $request->get('num_carta_porte');
            $doc_cotizaciones->boleta_vacio = $request->get('boleta_vacio');
            $doc_cotizaciones->fecha_boleta_vacio = $request->get('fecha_boleta_vacio');
            $doc_cotizaciones->eir = $request->get('eir');

            if ($request->hasFile("doc_eir")) {
                $file = $request->file('doc_eir');
                $path = public_path() . '/cotizaciones/cotizacion'. $id;
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $doc_cotizaciones->doc_eir = $fileName;
            }

            if ($request->hasFile("boleta_liberacion")) {
                $file = $request->file('boleta_liberacion');
                $path = public_path() . '/cotizaciones/cotizacion'. $id;
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $doc_cotizaciones->boleta_liberacion = $fileName;
            }

            if ($request->hasFile("doda")) {
                $file = $request->file('doda');
                $path = public_path() . '/cotizaciones/cotizacion'. $id;
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $doc_cotizaciones->doda = $fileName;
            }

            $doc_cotizaciones->ccp = $request->get('ccp');

            if ($request->hasFile("doc_ccp")) {
                $file = $request->file('doc_ccp');
                $path = public_path() . '/cotizaciones/cotizacion'. $id;
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $doc_cotizaciones->doc_ccp = $fileName;
            }

            $doc_cotizaciones->update();

            $cotizaciones = Cotizaciones::where('id', '=', $id)->first();
            $cotizaciones->id_cliente = $request->get('id_cliente');
            $cotizaciones->id_subcliente = $request->get('id_subcliente');
            $cotizaciones->origen = $request->get('origen');
            $cotizaciones->destino = $request->get('destino');
            $cotizaciones->burreo = $request->get('burreo');
            $cotizaciones->estadia = $request->get('estadia');
            $cotizaciones->fecha_modulacion = $request->get('fecha_modulacion');
            $cotizaciones->fecha_entrega = $request->get('fecha_entrega');
            $cotizaciones->precio_viaje = $request->get('precio_viaje');
            $cotizaciones->tamano = $request->get('tamano');
            $cotizaciones->peso_contenedor = $request->get('peso_contenedor');
            $cotizaciones->maniobra = $request->get('maniobra');
            $cotizaciones->otro = $request->get('otro');
            $cotizaciones->iva = $request->get('iva');
            $cotizaciones->retencion = $request->get('retencion');
            $cotizaciones->bloque = $request->get('bloque');
            $cotizaciones->bloque_hora_i = $request->get('bloque_hora_i');
            $cotizaciones->bloque_hora_f = $request->get('bloque_hora_f');
            $cotizaciones->peso_reglamentario = $request->get('peso_reglamentario');
            $cotizaciones->fecha_eir = $request->get('fecha_eir');
            $cotizaciones->base_factura = $request->get('base_factura');
            $cotizaciones->base_taref = $request->get('base_taref');

            if($request->get('id_cliente_clientes') == NULL){
                if($request->get('cot_peso_contenedor') > $request->get('peso_reglamentario')){
                    $sobrepeso = $request->get('cot_peso_contenedor') - $request->get('peso_reglamentario');
                }else{
                    $sobrepeso = 0;
                }
                $cotizaciones->sobrepeso = $sobrepeso;
                $precio_tonelada = str_replace(',', '', $request->get('precio_sobre_peso'));
                $cotizaciones->precio_sobre_peso = $precio_tonelada;
                $cotizaciones->precio_tonelada = $request->get('precio_tonelada');
                $total = ($cotizaciones->precio_tonelada + $request->get('cot_precio_viaje') + $request->get('cot_burreo') + $request->get('cot_maniobra') + $request->get('cot_estadia') + $request->get('cot_otro') + $request->get('cot_iva')) - $request->get('cot_retencion');
                $cotizaciones->total = $request->get('total');
            }

            if ($request->hasFile("carta_porte")) {
                $file = $request->file('carta_porte');
                $path = public_path() . '/cotizaciones/cotizacion'. $id;
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $cotizaciones->carta_porte = $fileName;
            }

            if ($request->hasFile("img_boleta")) {
                $file = $request->file('img_boleta');
                $path = public_path() . '/cotizaciones/cotizacion'. $id;
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $cotizaciones->img_boleta = $fileName;
            }

            $cotizaciones->update();

            if($request->get('id_cliente_clientes') == NULL){
                $gasto_descripcion = $request->input('gasto_descripcion');
                $gasto_monto = $request->input('gasto_monto');
                $ticket_ids = $request->input('ticket_id');

                for ($count = 0; $count < count($gasto_descripcion); $count++) {
                    $data = array(
                        'id_cotizacion' => $cotizaciones->id,
                        'descripcion' => $gasto_descripcion[$count],
                        'monto' => $gasto_monto[$count],
                    );

                    if (isset($ticket_ids[$count])) {
                        // Actualizar el ticket existente
                        $ticket = GastosExtras::findOrFail($ticket_ids[$count]);
                        $ticket->update($data);
                    } elseif($gasto_descripcion[$count] != NULL) {
                        // Crear un nuevo ticket
                        GastosExtras::create($data);
                    }
                }

                // Convertir los valores a números si son cadenas
                // $maniobra = is_numeric($cotizaciones->maniobra) ? $cotizaciones->maniobra : 0;
                // $burreo = is_numeric($cotizaciones->burreo) ? $cotizaciones->burreo : 0;
                // $otro = is_numeric($cotizaciones->otro) ? $cotizaciones->otro : 0;
                // $estadia = is_numeric($cotizaciones->estadia) ? $cotizaciones->estadia : 0;
                // $precio_viaje = is_numeric($cotizaciones->precio_viaje) ? $cotizaciones->precio_viaje : 0;
                // $iva = is_numeric($cotizaciones->iva) ? $cotizaciones->iva : 0;

                // SUMA TOTAL DE COTIZACION
                $suma =  $cotizaciones->total;

                foreach ($gasto_monto as $monto) {
                    // Convertir el valor a número si es una cadena
                    $monto = is_numeric($monto) ? $monto : 0; // Si $monto no es numérico, se asume 0
                    $suma += $monto;
                }
                $cotizaciones->total = $suma;
                $cotizaciones->restante = $cotizaciones->total;
                $cotizaciones->update();

                $asignacion = Asignaciones::where('id_contenedor', '=', $doc_cotizaciones->id)->first();
               

                if ($asignacion) {
                    if($asignacion->id_proveedor != NULL){
                        $cotizaciones->prove_restante = $request->get('total_proveedor');
                    }
                    $cotizaciones->update();

                    if($asignacion->id_proveedor == NULL){
                        $cantidad_ope = $request->input('cantidad_ope');
                        $tipo_ope = $request->input('tipo_ope');
                        $ticket_ids_ope = $request->input('ticket_id_ope');
                        $suma_cantidad_ope = 0;

                        for ($count = 0; $count < count($cantidad_ope); $count++) {
                            $suma_cantidad_ope += $cantidad_ope[$count];

                            $data = array(
                                'id_asignacion' => $asignacion->id,
                                'id_operador' => $asignacion->id_operador,
                                'id_cotizacion' => $cotizaciones->id,
                                'cantidad' => $cantidad_ope[$count],
                                'tipo' => $tipo_ope[$count],
                            );

                            if (isset($ticket_ids_ope[$count])) {
                                // Actualizar el ticket existente
                                $ticket = GastosOperadores::findOrFail($ticket_ids_ope[$count]);
                                $ticket->update($data);
                            } elseif($cantidad_ope[$count] != NULL) {
                                // Crear un nuevo ticket
                                GastosOperadores::create($data);
                            }
                        }

                        $suma_ope = ($asignacion->sueldo_viaje + $suma_cantidad_ope) - $asignacion->dinero_viaje;
                        $asignacion->pago_operador = $suma_ope;
                        $asignacion->restante_pago_operador = $suma_ope;
                        $asignacion->update();
                    }else if($asignacion->id_operador == NULL){

                        $asignacion->precio = $request->get('precio_proveedor');
                        $asignacion->burreo = $request->get('burreo_proveedor');
                        $asignacion->maniobra = $request->get('maniobra_proveedor');
                        $asignacion->estadia = $request->get('estadia_proveedor');
                        $asignacion->otro = $request->get('otro_proveedor');
                        $asignacion->sobrepeso_proveedor = $request->get('sobrepeso_proveedor');
                        $asignacion->total_tonelada = $request->get('total_tonelada');
                        $asignacion->base1_proveedor = $request->get('base1_proveedor');
                        $asignacion->base2_proveedor = $request->get('base2_proveedor');

                        $asignacion->otro2 = $request->get('otro2');
                        $asignacion->otro3 = $request->get('otro3');
                        $asignacion->otro4 = $request->get('otro4');
                        $asignacion->otro5 = $request->get('otro5');
                        $asignacion->otro6 = $request->get('otro6');
                        $asignacion->otro7 = $request->get('otro7');
                        $asignacion->otro8 = $request->get('otro8');
                        $asignacion->otro9 = $request->get('otro9');

                        $asignacion->descripcion_otro1 = $request->get('descripcion_otro1');
                        $asignacion->descripcion_otro2 = $request->get('descripcion_otro2');
                        $asignacion->descripcion_otro3 = $request->get('descripcion_otro3');
                        $asignacion->descripcion_otro4 = $request->get('descripcion_otro4');
                        $asignacion->descripcion_otro5 = $request->get('descripcion_otro5');
                        $asignacion->descripcion_otro6 = $request->get('descripcion_otro6');
                        $asignacion->descripcion_otro7 = $request->get('descripcion_otro7');
                        $asignacion->descripcion_otro8 = $request->get('descripcion_otro8');
                        $asignacion->descripcion_otro9 = $request->get('descripcion_otro9');
                        $asignacion->descripcion_otro10 = $request->get('descripcion_otro10');

                        $asignacion->iva = $request->get('iva_proveedor');
                        $asignacion->retencion = $request->get('retencion_proveedor');
                        $asignacion->total_proveedor = $request->get('total_proveedor');
                        $asignacion->id_proveedor = $request->id_proveedor;
                        $asignacion->update();
                    }
                }
            }
            Session::flash('edit', 'Se ha editado sus datos con exito');
            return redirect()->back()
                ->with('success', 'Estatus updated successfully');
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

        $directorio =  public_path().'/cotizaciones/cotizacion'.$cotizacion->id_cotizacion;
        if (!is_dir($directorio)) {
            mkdir($directorio);
        }
		$FileUploader = new FileUploader('files', array(
        'uploadDir' => public_path()."/cotizaciones/cotizacion$cotizacion->id_cotizacion/",
        ));

	// call to upload the files
		$upload = $FileUploader->upload();
		if ($upload['isSuccess']) {
			foreach($upload['files'] as $key=>$item) {
				$upload['files'][$key] = array(
					'extension' => $item['extension'],
					'format' => $item['format'],
					'file' =>   public_path()."/cotizaciones/cotizacion$cotizacion->id_cotizacion/".$item['name'],
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
            case 'CartaPorte': $update = ["carta_porte" => $item['name']]; break;
            case 'PreAlta': $update = ["img_boleta" => $item['name']]; break;

         }
         
         ($r->urlRepo != 'CartaPorte' && $r->urlRepo != 'PreAlta' ) 
         ? DocumCotizacion::where('id',$cotizacion->id_cotizacion)->update($update)
         : Cotizaciones::where('id',$cotizacion->id_cotizacion)->update($update);

         if ($r->urlRepo == 'PreAlta')  DocumCotizacion::where('id',$cotizacion->id_cotizacion)->update(['boleta_vacio'=>'si']);

         event(new \App\Events\ConfirmarDocumentosEvent($cotizacion->id_cotizacion));

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
                    'estatus' => 'Aprobada'
                ]);
        
                $contenedores = DB::table('docum_cotizacion')
                ->where('id_cotizacion',  '=', $c->IdContenedor)
                ->update(['id_empresa' => $request->empresa]);
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

        if($doc->num_contenedor != NULL){
            $numContenedor = $doc->num_contenedor;
            $idEmpresa = $request->get('id_empresa');

            $contenedorExistente = DocumCotizacion::where('num_contenedor', $numContenedor)
                                                ->where('id_empresa', $idEmpresa)
                                                ->first();

            if ($contenedorExistente) {
                return redirect()->back()->with('error', 'El contenedor ya existe en la empresa a la que se iba a asignar.');
            }
        }

        // Obtener el id_cliente actual de la empresa anterior
        $idClienteAnterior = DB::table('clients')
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
        }

        if ($nuevoIdCliente) {
            $contenedor = DocumCotizacion::where('id_cotizacion',  '=', $cotizacion->id)->first();

            if ($contenedor) {
                $asignacionExiste = Asignaciones::where('id_contenedor', '=', $contenedor->id)->exists();

                if ($asignacionExiste) {
                    // Obtener la asignación correspondiente
                    $asignacion = Asignaciones::where('id_contenedor', '=', $contenedor->id)->first();

                    // Obtener los datos necesarios del request
                    $nuevoIdEmpresa = $request->get('id_empresa');

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
                'id_empresa' => $nuevoIdEmpresa,
                'id_subcliente' => $nuevoIdSubCliente,
                'id_cliente' => $nuevoIdCliente
            ]);

            $contenedores = DB::table('docum_cotizacion')
            ->where('id_cotizacion',  '=', $cotizacion->id)
            ->update(['id_empresa' => $request->get('id_empresa')]);

            return redirect()->route('index.cotizaciones')
                ->with('success', 'Se ha editado sus datos con exito');
        } else {
            return redirect()->route('index.cotizaciones')
                ->with('error', 'No tiene cliente con el mismo correo a la empresa que quiere cambiar');
        }


    }
}
