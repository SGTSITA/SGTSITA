<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CotizacionesLocales;
use App\Models\DocumCotizacion;

class CotizacionesLocalesController extends Controller
{
    

public function storelocal(Request $request)
{
        DB::beginTransaction();

        try {
            $idempresa = auth()->user()->id_empresa;
            if($request->id_proveedor){
                $idempresa = $request->id_proveedor;
            }

            // 1️⃣ Guardar en docum_cotizacion
            $doc = DocumCotizacion::create([
                'id_cotizacion'          => 0,//default 0 por que es local , se cambiara cuado se pase a viaje foraneo y haga el proceso normal
                'id_empresa'             => $idempresa,
                'num_contenedor'         => $request->num_contenedor,
                'terminal'               => $request->terminal ?? null,
                'num_autorizacion'       => $request->num_autorizacion ?? null,
                'boleta_liberacion'      => $request->boleta_liberacion ?? null,
                'doda'                   => $request->doda ?? null,
                'num_boleta_liberacion'  => $request->num_boleta_liberacion ?? null,
                'num_doda'               => $request->num_doda ?? null,
                'num_carta_porte'        => $request->num_carta_porte ?? null,
                'boleta_vacio'           => $request->boleta_vacio ?? null,
                'fecha_boleta_vacio'     => $request->fecha_boleta_vacio ?? null,
                'eir'                    => $request->eir ?? null,
                'doc_eir'                => $request->doc_eir ?? null,
                'ccp'                    => $request->ccp ?? null,
                'doc_ccp'                => $request->doc_ccp ?? null,
                'foto_patio'             => $request->foto_patio ?? null,
                'boleta_patio'           => $request->boleta_patio ?? null,
                'fecha_boleta_patio'     => $request->fecha_boleta_patio ?? null,
                'cima'                   => $request->cima ?? 0,
            ]);

            // 2️⃣ Guardar en cotizaciones_locales usando el id generado
            $local = CotizacionesLocales::create([
                'id_contenedor'       => $doc->id,  // FK hacia docum_cotizacion
                'id_subcliente'       => $request->id_subcliente ?? null,
                'id_empresa'          => $idempresa ?? null,
                'id_proveedor'        => $idempresa ?? null,
                'id_transportista'    => $request->id_transportista ?? null,
                'origen'              => $request->origen,
                'tamano'              => $request->tamano ?? null,
                'peso_contenedor'     => $request->peso_contenedor ?? null,
                'fecha_modulacion'    => $request->fecha_modulacion ?? null,
                'puerto'              => $request->puerto ?? null,
                'fecha_ingreso_puerto'=> $request->fecha_ingreso_puerto ?? null,
                'fecha_salida_puerto' => $request->fecha_salida_puerto ?? null,
                'dias_estadia'        => $request->dias_estadia ?? 0,
                'dias_pernocta'       => $request->dias_pernocta ?? 0,
                'tarifa_estadia'      => $request->tarifa_estadia ?? 0,
                'tarifa_pernocta'     => $request->tarifa_pernocta ?? 0,
                'total_estadia'       => $request->total_estadia ?? 0,
                'total_pernocta'      => $request->total_pernocta ?? 0,
                'total_general'       => $request->total_general ?? 0,
                'motivo_demora'       => $request->motivo_demora ?? null,
                'liberado'            => $request->liberado ?? 0,
                'fecha_liberacion'    => $request->fecha_liberacion ?? null,
                'responsable'         => $request->responsable ?? null,
                'observaciones'       => $request->observaciones ?? null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cotización local guardada correctamente',
                'docum_cotizacion_id' => $doc->id,
                'cotizacion_local_id' => $local->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error guardando cotización local: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la cotización local',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


public function singleUpdatelocal(Request $request, $id)
{
    DB::beginTransaction();

    try {

        // ==========================================
        // 1. OBTENER REGISTROS BASE
        // ==========================================
        $cot = Cotizacion::findOrFail($id);
        $doc = DocumCotizacion::where('id_cotizacion', $id)->firstOrFail();

        $contenedorOriginal = $doc->num_contenedor;


        // ==========================================
        // 2. ACTUALIZAR COTIZACIÓN
        // ==========================================
        $cot->id_subcliente        = $request->id_subcliente ?? null;
        $cot->id_empresa           = auth()->user()->id_empresa;
        $cot->id_proveedor         = $request->id_proveedor ?? null;
        $cot->id_transportista     = $request->id_transportista ?? null;

        $cot->origen               = $request->origen;
        $cot->tamano               = $request->tamano;
        $cot->peso_contenedor      = $request->peso_contenedor;
        $cot->fecha_modulacion     = $request->fecha_modulacion;

        // Campos nuevos que migramos desde local
        $cot->puerto               = $request->puerto ?? null;
        $cot->fecha_ingreso_puerto = $request->fecha_ingreso_puerto ?? null;
        $cot->fecha_salida_puerto  = $request->fecha_salida_puerto ?? null;
        $cot->dias_estadia         = $request->dias_estadia ?? 0;
        $cot->tarifa_estadia       = $request->tarifa_estadia ?? 0;
        $cot->total_estadia        = $request->total_estadia ?? 0;
        $cot->dias_pernocta        = $request->dias_pernocta ?? 0;
        $cot->tarifa_pernocta      = $request->tarifa_pernocta ?? 0;
        $cot->total_pernocta       = $request->total_pernocta ?? 0;
        $cot->total_general        = $request->total_general ?? 0;
        $cot->motivo_demora        = $request->motivo_demora ?? null;
        $cot->liberado             = $request->liberado ?? 0;
        $cot->fecha_liberacion     = $request->fecha_liberacion ?? null;
        $cot->responsable          = $request->responsable ?? null;
        $cot->observaciones        = $request->observaciones ?? null;

        $cot->save();


        // ==========================================
        // 3. SI CAMBIÓ EL NÚMERO DE CONTENEDOR
        // ==========================================
        if ($request->num_contenedor != $contenedorOriginal) {
            $doc->num_contenedor = $request->num_contenedor;
        }


               // ==========================================
        // 6. TODO CORRECTO
        // ==========================================
        DB::commit();

        return response()->json([
            'status'  => 'success',
            'message' => 'Cotización actualizada correctamente.'
        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        return response()->json([
            'status'  => 'error',
            'message' => 'Error al actualizar.',
            'error'   => $e->getMessage()
        ], 500);
    }
}

}
