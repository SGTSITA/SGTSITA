<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coordenadas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CoordenadasController extends Controller
{



    public function indexMapa(){
        return view('coordendas.vercoor');

    }

    public function  getcoorcontenedor(Request  $request) 
    {
        $query = $request->query('query');

        $preguntas_A = [
            [ 'texto' => "1) ¿ Registro en Puerto ?", 'campo' => 'registro_puerto', 'tooltip' => "Registro en Puerto" ],
            [ 'texto' => "2) ¿ Dentro de Puerto ?", 'campo' => 'dentro_puerto', 'tooltip' => "Dentro de Puerto" ],
            [ 'texto' => "3) ¿ Descarga Vacío ?", 'campo' => 'descarga_vacio', 'tooltip' => "Descarga Vacío" ],
            [ 'texto' => "4) ¿ Cargado Contenedor ?", 'campo' => 'cargado_contenedor', 'tooltip' => "Cargado Contenedor" ],
            [ 'texto' => "5) ¿ En Fila Fiscal ?", 'campo' => 'fila_fiscal', 'tooltip' => "En Fila Fiscal" ],
            [ 'texto' => "6) ¿ Modulado ?", 'campo' => 'modulado_tipo', 'tooltip' => "Modulado" ],
            [ 'texto' => "7) ¿ Descarga en patio ?", 'campo' => 'descarga_patio', 'tooltip' => "Descarga en patio" ],
            [ 'texto' => "8) ¿Carga en patio?", 'campo' => 'cargado_patio', 'tooltip' => "Carga en patio" ],
            [ 'texto' => "9) ¿Inicio ruta?", 'campo' => 'en_destino', 'tooltip' => "Inicio ruta" ],
            [ 'texto' => "10)¿Inicia carga?", 'campo' => 'inicio_descarga', 'tooltip' => "Inicia carga" ],
            [ 'texto' => "11)¿Fin descarga?", 'campo' => 'fin_descarga', 'tooltip' => "Fin descarga" ],
            [ 'texto' => "12 ¿Recepción Doctos Firmados?", 'campo' => 'recepcion_doc_firmados', 'tooltip' => "Recepción Doctos Firmados" ],
        ];





        $datos = DB::table('cotizaciones')
            ->select(
                'cotizaciones.id',
                'asignaciones.id as idasignacion',
                'clients.nombre as cliente',
                'cotizaciones.origen',
                'cotizaciones.destino',
                'docum_cotizacion.num_contenedor as contenedor',
                'cotizaciones.estatus',

                // Coordenadas
                'coordenadas.registro_puerto',
                'coordenadas.registro_puerto_datatime',
                'coordenadas.dentro_puerto',
                'coordenadas.dentro_puerto_datatime',
                'coordenadas.descarga_vacio',
                'coordenadas.descarga_vacio_datatime',
                'coordenadas.cargado_contenedor',
                'coordenadas.cargado_contenedor_datatime',
                'coordenadas.fila_fiscal',
                'coordenadas.fila_fiscal_datatime',
                'coordenadas.modulado_tipo',
                'coordenadas.modulado_tipo_datatime',
                'coordenadas.modulado_coordenada',
                'coordenadas.modulado_coordenada_datatime',
                'coordenadas.en_destino',
                'coordenadas.en_destino_datatime',
                'coordenadas.inicio_descarga',
                'coordenadas.inicio_descarga_datatime',
                'coordenadas.fin_descarga',
                'coordenadas.fin_descarga_datatime',
                'coordenadas.recepcion_doc_firmados',
                'coordenadas.recepcion_doc_firmados_datatime',
                'coordenadas.descarga_patio',
                'coordenadas.descarga_patio_datetime',
                'coordenadas.cargado_patio',
                'coordenadas.cargado_patio_datetime'
            )
            ->join('clients', 'cotizaciones.id_cliente', '=', 'clients.id')
            ->join('docum_cotizacion', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->join('asignaciones', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
            ->leftJoin('coordenadas', 'coordenadas.id_asignacion', '=', 'asignaciones.id')
            ->where('docum_cotizacion.num_contenedor', $query) 
            ->first();

        if ($datos) {
            return response()->json([
                'success' => true,
                'datos' => $datos,
                'preguntas' => $preguntas_A,
            ]);
        } else {
            return response()->json(['success' => false]);
        }
        
    }
    public function index($id,$tipoCuestionario){

      
        $asignaciones = DB::table('asignaciones')
        ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
    ->select(
        'asignaciones.id',
        'asignaciones.id_camion',
        'docum_cotizacion.num_contenedor',
        DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN asignaciones.id_operador ELSE asignaciones.id_proveedor END as beneficiario_id"),
        DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN 'Propio' ELSE 'Subcontratado' END as tipo_contrato")
    );

$beneficiarios = DB::table(function ($query) {
    $query->select('id', 'nombre', 'telefono', DB::raw("'Propio' as tipo_contrato"), 'id_empresa')
        ->from('operadores')
        ->union(
            DB::table('proveedores')
                ->select('id', 'nombre', 'telefono', DB::raw("'Subcontratado' as tipo_contrato"), 'id_empresa')
        );
}, 'beneficiarios');

$coordenadas = DB::table('coordenadas as coor')
    ->select(
        'coor.id as id_coordenadas',
        'coor.id_asignacion',
        'coor.id_cotizacion',
        'beneficiarios.tipo_contrato',
        'beneficiarios.telefono',
        'eq.placas',
        'beneficiarios.nombre',
        'em.nombre as nombre_empresa',
        'asig.num_contenedor',
        'coor.registro_puerto' ,
        'coor.registro_puerto_datatime' ,
        'coor.dentro_puerto' ,
        'coor.dentro_puerto_datatime' ,
        'coor.descarga_vacio' ,
        'coor.descarga_vacio_datatime' ,
        'coor.cargado_contenedor' ,
        'coor.cargado_contenedor_datatime' ,
        'coor.fila_fiscal' ,
        'coor.fila_fiscal_datatime' ,
        'coor.modulado_tipo' ,
        'coor.modulado_tipo_datatime' ,
        'coor.modulado_coordenada' ,
        'coor.modulado_coordenada_datatime' ,
        'coor.en_destino' ,
        'coor.en_destino_datatime' ,
        'coor.inicio_descarga' ,
        'coor.inicio_descarga_datatime' ,
        'coor.fin_descarga' ,
        'coor.fin_descarga_datatime',
        'coor.recepcion_doc_firmados',
        'coor.recepcion_doc_firmados_datatime' ,
        'coor.descarga_patio' ,
        'coor.descarga_patio_datetime' ,
        'coor.cargado_patio',
        'coor.cargado_patio_datetime',
        'tipo_c_estado' ,
        'tipo_b_estado',
        'tipo_f_estado',

    )
    ->joinSub($asignaciones, 'asig', function ($join) {
        $join->on('coor.id_asignacion', '=', 'asig.id');
    })
    ->joinSub($beneficiarios, 'beneficiarios', function ($join) {
        $join->on('asig.beneficiario_id', '=', 'beneficiarios.id')
             ->on('asig.tipo_contrato', '=', 'beneficiarios.tipo_contrato');
    })
    ->join('empresas as em', 'em.id', '=', 'beneficiarios.id_empresa')
    ->leftJoin('equipos as eq', 'asig.id_camion', '=', 'eq.id')
    ->where('coor.id_cotizacion', $id)
    ->first();

    if (!$coordenadas) {
        $coordenadas = (object)[
            'id_coordenadas' => null,
            'id_asignacion' => 0,
            'id_cotizacion' => $id,
            'tipo_contrato' => '',
            'telefono' => '',
            'placas' => '',
            'nombre' => '',
            'nombre_empresa' => 'No se encontraron registros de coordenadas',
            'num_contenedor' => '',
            'registro_puerto' => '',
            'registro_puerto_datatime' => '',
            'dentro_puerto' => '',
            'dentro_puerto_datatime' => '',
            'descarga_vacio' => '',
            'descarga_vacio_datatime' => '',
            'cargado_contenedor' => '',
            'cargado_contenedor_datatime' => '',
            'fila_fiscal' => '',
            'fila_fiscal_datatime' => '',
            'modulado_tipo' => '',
            'modulado_tipo_datatime' => '',
            'modulado_coordenada' => '',
            'modulado_coordenada_datatime' => '',
            'en_destino' => '',
            'en_destino_datatime' => '',
            'inicio_descarga' => '',
            'inicio_descarga_datatime' => '',
            'fin_descarga' => '',
            'fin_descarga_datatime' => '',
            'recepcion_doc_firmados' => '',
            'recepcion_doc_firmados_datatime' => '',
            'descarga_patio' => '',
            'descarga_patio_datetime' => '',
            'cargado_patio' => '',
            'cargado_patio_datetime' => ''
        ];
    }

        return view('coordendas.index',compact('coordenadas','tipoCuestionario'));

    }

    public function store(Request $request) 
    {
        $cotizacionesEx = Coordenadas::where('id_asignacion', $request->idAsig)
    ->where('id_cotizacion', $request->idCotSave)
    ->first();
        if (!$cotizacionesEx)
        {//insertar
            $nuevaCot = Coordenadas::create([
                'id_asignacion' => $request->idAsig,
                'id_cotizacion' => $request->idCotSave,
                'tipo_flujo' => $request->tipo_flujo ?? null,
                'registro_puerto' => $request->registro_puerto ?? null,
                'dentro_puerto' => $request->dentro_puerto ?? null,
                'descarga_vacio' => $request->descarga_vacio ?? null,
                'cargado_contenedor' => $request->cargado_contenedor ?? null,
                'fila_fiscal' => $request->fila_fiscal ?? null,
                'modulado_tipo' => $request->modulado_tipo ?? null,
                'modulado_coordenada' => $request->modulado_coordenada ?? null,
                'en_destino' => $request->en_destino ?? null,
                'inicio_descarga' => $request->inicio_descarga ?? null,
                'fin_descarga' => $request->fin_descarga ?? null,
                'recepcion_doc_firmados' => $request->recepcion_doc_firmados ?? null,
        
                'tipo_flujo_datatime' => $request->tipo_flujo_datatime ?? null,
                'registro_puerto_datatime' => $request->registro_puerto_datatime ?? null,
                'dentro_puerto_datatime' => $request->dentro_puerto_datatime ?? null,
                'descarga_vacio_datatime' => $request->descarga_vacio_datatime ?? null,
                'cargado_contenedor_datatime' => $request->cargado_contenedor_datatime ?? null,
                'fila_fiscal_datatime' => $request->fila_fiscal_datatime ?? null,
                'modulado_tipo_datatime' => $request->modulado_tipo_datatime ?? null,
                'modulado_coordenada_datatime' => $request->modulado_coordenada_datatime ?? null,
                'en_destino_datatime' => $request->en_destino_datatime ?? null,
                'inicio_descarga_datatime' => $request->inicio_descarga_datatime ?? null,
                'fin_descarga_datatime' => $request->fin_descarga_datatime ?? null,
                'recepcion_doc_firmados_datatime' => $request->recepcion_doc_firmados_datatime ?? null,
                'tipo_c_estado' => $request->tipo_c_estado,
                'tipo_b_estado' => $request-> tipo_b_estado,
                'tipo_f_estado' => $request-> tipo_f_estado,
            ]);
        
            return response()->json([
                'message' => 'Coordenada guardada correctamente',
                'data' => $nuevaCot
            ]);

        }else {
            return response()->json([
                'message' => 'Coordenada guardada correctamente',
                'data' => $cotizacionesEx
            ]);
        }

    }
    public function guardarRespuesta(Request $request)
        {
      

                $coordenada = Coordenadas::find($request->id_coordenada);

                if ($coordenada) {
                    $fecha = Carbon::now();
                    // Realizar el UPDATE explícito para las columnas dinámicas
                    $coordenada->update([
                        $request->columna => $request->coordenadas,  
                        $request->columna_datetime =>  $fecha  
                       
                    ]);
            
                   
                    // Respuesta exitosa
                    return response()->json(['success' => true]);
                }
            
                // Si no se encuentra la coordenada
                return response()->json(['error' => 'Coordenada no encontrada'], 404);
        }

    public function edit(Request $request, $id){

        $fecha = Carbon::now();

        $coordenadas = Coordenadas::find($id);

        if($request->get('validaroperador')){
            $coordenadas->validaroperador = $request->get('validaroperador');
        }

        $coordenadas->registro_puerto = $request->get('latitud_longitud_registro_puerto');
        $coordenadas->registro_puerto_datatime = $fecha;

        $coordenadas->dentro_puerto = $request->get('latitud_longitud_dentro_puerto');
        $coordenadas->dentro_puerto_datatime = $fecha;

        $coordenadas->descarga_vacio = $request->get('latitud_longitud_descarga_vacio');
        $coordenadas->descarga_vacio_datatime = $fecha;

        $coordenadas->cargado_contenedor = $request->get('latitud_longitud_cargado_contenedor');
        $coordenadas->cargado_contenedor_datatime = $fecha;

        $coordenadas->modulado_tipo = $request->get('modulado_tipo');
        $coordenadas->modulado_tipo_datatime = $fecha;

        $coordenadas->fila_fiscal = $request->get('latitud_longitud_fila_fiscal');
        $coordenadas->fila_fiscal_datatime = $fecha;

        $coordenadas->en_destino = $request->get('latitud_longitud_en_destino');
        $coordenadas->en_destino_datatime = $fecha;

        $coordenadas->inicio_descarga = $request->get('latitud_longitud_inicio_descarga');
        $coordenadas->inicio_descarga_datatime = $fecha;

        $coordenadas->fin_descarga = $request->get('latitud_longitud_fin_descarga');
        $coordenadas->fin_descarga_datatime = $fecha;

        $coordenadas->recepcion_doc_firmados = $request->get('latitud_longitud_recepcion_doc_firmados');
        $coordenadas->recepcion_doc_firmados_datatime = $fecha;

        $coordenadas->update();

        return redirect()->back();

    }
}
