<?php

namespace App\Http\Controllers\MEP;

use App\Http\Controllers\Controller;
use App\Services\CostosViajeMepService;
use Illuminate\Http\Request;

class CostosViajeMEPController extends Controller
{
    protected CostosViajeMepService $costosService;

    public function __construct(CostosViajeMepService $costosService)
    {
        $this->costosService = $costosService;
    }

    public function index()
    {
        return view('mep.costos.index');
    }

    public function getCostosViajes(Request $request)
    {
        $rfcEmpresa = auth()->user()->Empresa?->rfc;
        if (!$rfcEmpresa) {
            return response()->json([]);
        }

        $costos = $this->costosService->getCostosViajes(
            $rfcEmpresa,
            $request->input('fecha_inicio'),
            $request->input('fecha_fin')
        );

        return response()->json($costos);
    }

    public function guardarCambio(Request $request)
    {
        $validated = $request->validate([
            'id_asignacion' => 'required|exists:asignaciones,id',
            'precio_viaje' => 'nullable|numeric',
            'burreo' => 'nullable|numeric',
            'maniobra' => 'nullable|numeric',
            'estadia' => 'nullable|numeric',
            'otro' => 'nullable|numeric',
            'iva' => 'nullable|numeric',
            'retencion' => 'nullable|numeric',
            'base1' => 'nullable|numeric',
            'base2' => 'nullable|numeric',
            'sobrepeso' => 'nullable|numeric',
            'precio_sobrepeso' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'motivo_cambio' => 'nullable|string|max:500',
        ]);

        $res = $this->costosService->guardarCambio($validated);
        return response()->json($res);
    }

    public function getPendientes()
    {
        return response()->json($this->costosService->getPendientes());
    }

    public function vistaPendientes()
    {
        return view('mep.costos.pendientes');
    }

    public function contarPendientes()
    {
        return response()->json(['total' => $this->costosService->contarPendientes()]);
    }

    public function compararCostos($id)
    {
        return response()->json($this->costosService->compararCostos((int) $id));
    }

    public function aceptarCambio(Request $request, $id)
    {
        $res = $this->costosService->aceptarCambio((int) $id);
        $status = $res['success'] ? 200 : 422;
        return response()->json($res, $status);
    }

    public function rechazarCambio(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'nullable|string|max:500',
            'campo_observado' => 'nullable|array'
        ]);

        $res = $this->costosService->rechazarCambio(
            (int) $id,
            $request->motivo,
            $request->campo_observado
        );

        return response()->json($res);
    }

    public function dashboard()
    {
        $stats = $this->costosService->contarPorEstatus();
        return view('mep.costos.dashboard', $stats);
    }

    public function contarPorEstatus()
    {
        return response()->json($this->costosService->contarPorEstatus());
    }

    public function vistaViajesCambios()
    {
        return view('mep.costos.viajes_cambios');
    }

    public function getCambios()
    {
        return response()->json($this->costosService->getCambios());
    }

    public function detalleCambio($id)
    {
        return response()->json($this->costosService->detalleCambio((int) $id));
    }

    public function reenviarCambio(Request $request, $id)
    {
        $validated = $request->validate([
            'precio_viaje'        => 'nullable|numeric',
            'burreo'              => 'nullable|numeric',
            'maniobra'            => 'nullable|numeric',
            'estadia'             => 'nullable|numeric',
            'otro'                => 'nullable|numeric',
            'iva'                 => 'nullable|numeric',
            'retencion'           => 'nullable|numeric',
            'base1'               => 'nullable|numeric',
            'base2'               => 'nullable|numeric',
            'sobrepeso'           => 'nullable|numeric',
            'precio_sobrepeso'    => 'nullable|numeric',
            'total'               => 'nullable|numeric',
            'motivo_cambio'       => 'nullable|string|max:500',
        ]);

        $res = $this->costosService->reenviarCambio((int) $id, $validated);
        return response()->json($res);
    }
}
