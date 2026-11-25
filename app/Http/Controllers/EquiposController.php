<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Services\EquipoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class EquiposController extends Controller
{
    protected EquipoService $equipoService;

    public function __construct(EquipoService $equipoService)
    {
        $this->equipoService = $equipoService;
    }

    public function index(): View
    {
        $data = $this->equipoService->getEquiposCatalogosIndex();

        return view('equipos.index', [
            'equipos_dolys' => $data['equipos_dolys'],
            'equipos_chasis' => $data['equipos_chasis'],
            'equipos_camiones' => $data['equipos_camiones'],
            'fechaActual' => $data['fechaActual'],
            'gps_companies' => $data['gps_companies'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->equipoService->storeEquipo($request->all(), $request->allFiles());

        Session::flash('success', 'Se ha guardado sus datos con éxito');

        return redirect()->back()->with('success', 'Equipo creado con éxito.');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $result = $this->equipoService->updateEquipo(
            (int) $id,
            (string) $request->tipo,
            $request->all(),
            $request->allFiles()
        );

        return response()->json($result);
    }

    public function desactivar(Request $request, $id): JsonResponse
    {
        $result = $this->equipoService->toggleActivo((int) $id, (string) $request->input('tipo'));

        return response()->json($result);
    }

    public function data(): JsonResponse
    {
        $equipos = $this->equipoService->getEquiposPorEmpresa();

        return response()->json($equipos);
    }

    public function asignarGps(Request $request, $id): JsonResponse
    {
        $gpsCompanyId = $request->gps_company_id ? (int) $request->gps_company_id : null;
        $imei = $request->imei ? (string) $request->imei : null;

        $this->equipoService->asignarGps((int) $id, $gpsCompanyId, $imei);

        return response()->json([
            'success' => true,
            'message' => 'Asignación de GPS e IMEI actualizada correctamente.'
        ]);
    }

    public function index_gps(): View
    {
        $data = $this->equipoService->getEquiposGpsData(auth()->user());

        return view('equipos.index-gps', [
            'equipos' => $data['equipos'],
            'gps_companies' => $data['gps_companies']
        ]);
    }

    public function updateMep(Request $request): JsonResponse
    {
        $result = $this->equipoService->updateMepEquipo($request->all());

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message']
        ], $result['status'] ?? 200);
    }
}
