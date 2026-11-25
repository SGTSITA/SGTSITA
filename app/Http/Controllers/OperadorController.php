<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOperadorRequest;
use App\Http\Requests\UpdateOperadorRequest;
use App\Models\Operador;
use App\Services\OperadorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class OperadorController extends Controller
{
    protected OperadorService $operadorService;

    public function __construct(OperadorService $operadorService)
    {
        $this->operadorService = $operadorService;
    }

    public function index(): View
    {
        $data = $this->operadorService->getOperadoresIndexData();

        return view('operadores.index', [
            'operadores' => $data['operadores'],
            'pagos_pendientes' => $data['pagos_pendientes']
        ]);
    }

    public function show($id): View
    {
        $data = $this->operadorService->getOperadorDetalleData((int) $id);

        if (!$data) {
            abort(404, 'Operador no encontrado');
        }

        return view('operadores.show', [
            'operador' => $data['operador'],
            'pagos' => $data['pagos'],
            'comprobantes_gasolina' => $data['comprobantes_gasolina'],
            'comprobantes_casetas' => $data['comprobantes_casetas'],
            'comprobantes_otros' => $data['comprobantes_otros']
        ]);
    }

    public function update(UpdateOperadorRequest $request, Operador $id): RedirectResponse
    {
        if ($request->has('curp_duplicada')) {
            return redirect()->back()
                ->withInput()
                ->with('curp_error_update_' . $id->id, $request->get('curp_duplicada'));
        }

        $this->operadorService->updateOperador($id->id, $request->validated());

        Session::flash('edit', 'Se ha editado sus datos con éxito');

        return redirect()->back()->with('success', 'Operador actualizado correctamente');
    }

    public function store(StoreOperadorRequest $request): JsonResponse
    {
        try {
            if ($request->has('curp_duplicada')) {
                return response()->json([
                    'success' => false,
                    'message' => $request->get('curp_duplicada'),
                ], 422);
            }

            $operador = $this->operadorService->storeOperador($request->validated(), $request->allFiles());

            return response()->json([
                'success' => true,
                'message' => 'Operador creado correctamente.',
                'data' => $operador,
            ], 200);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error al guardar operador: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el operador: ' . $e->getMessage(),
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function update_pago(Request $request, $id): RedirectResponse
    {
        $this->operadorService->updatePagoOperador((int) $id, $request->all(), $request->allFiles());

        Session::flash('edit', 'Se ha editado sus datos con exito');

        return redirect()->back()->with('success', 'Estatus updated successfully');
    }

    public function show_pagos($id): View
    {
        $data = $this->operadorService->getPagosPendientesOperador((int) $id);

        return view('operadores.pagos_pendientes', [
            'pagos_pendientes' => $data['pagos_pendientes'],
            'operador' => $data['operador'],
            'bancos' => $data['bancos']
        ]);
    }

    public function destroy($id): RedirectResponse
    {
        $result = $this->operadorService->deleteOperador((int) $id);

        if (!$result['success']) {
            return redirect()->back()->with('operador_con_restante', $result['message']);
        }

        Session::flash('success', $result['message']);

        return redirect()->back();
    }

    public function restore($id): RedirectResponse
    {
        $this->operadorService->restoreOperador((int) $id);

        Session::flash('success', 'Operador reactivado correctamente.');

        return redirect()->back();
    }
}
