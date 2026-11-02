<?php

namespace App\Http\Controllers;

use App\Services\CotizacionesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CotizacionesLocalesController extends Controller
{
    protected CotizacionesService $cotizacionesService;

    public function __construct(CotizacionesService $cotizacionesService)
    {
        $this->cotizacionesService = $cotizacionesService;
    }

    public function storelocal(Request $request)
    {
        try {
            $idEmpresa = $request->id_proveedor ?: (int) Auth::user()->id_empresa;
            $res = $this->cotizacionesService->storeLocal($request->all(), $idEmpresa, Auth::user());

            return response()->json([
                'success' => true,
                'message' => 'Cotización local guardada correctamente',
                'id' => $res['docum_cotizacion_id'],
                'cotizacion_id' => $res['cotizacion_id'],
                'docum_cotizacion_id' => $res['docum_cotizacion_id'],
                'num_contenedor' => $res['num_contenedor'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error guardando cotización local: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la cotización local',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function singleUpdatelocal(Request $request, $id)
    {
        try {
            $this->cotizacionesService->singleUpdateLocal((int) $id, $request->all(), (int) Auth::user()->id_empresa, Auth::user());

            return response()->json([
                'status' => 'success',
                'message' => 'Cotización actualizada correctamente.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
