<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coordenadas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CoordenadasController extends Controller
{
    public function index($id,$tipoCuestionario){

       
        $asignaciones = DB::table('asignaciones')
    ->select(
        'asignaciones.id',
        'asignaciones.id_camion',
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
        'registro_puerto',
        'coor.dentro_puerto' ,
        'coor.descarga_vacio' ,
        'coor.cargado_contenedor' ,
        'coor.fila_fiscal' ,
        'coor.modulado_tipo' ,
        'coor.modulado_coordenada' ,
        'coor.en_destino' ,
        'coor.inicio_descarga' ,
        'coor.fin_descarga' ,
        'coor.recepcion_doc_firmados',
        'coor.descarga_patio',
        'coor.cargado_patio'

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
    ->where('coor.id_asignacion', $id)
    ->first();

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
