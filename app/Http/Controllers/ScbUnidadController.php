<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\scb\ScbUnidadService;
use Illuminate\Validation\ValidationException;
use Throwable;

class ScbUnidadController extends Controller
{
   public function __construct(
        protected ScbUnidadService $unidadService
    ) {
    }

    public function index()
    {
        $unidades = $this->unidadService->getAll();

        return view('scb.unidades.index', compact('unidades'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'descripcion' => ['required', 'string', 'max:150'],
                'placas' => ['nullable', 'string', 'max:50'],
                'activo' => ['nullable'],
            ]);

            $unidad = $this->unidadService->create($data);

            return response()->json([
                'success' => true,
                'message' => 'Unidad registrada correctamente.',
                'data' => $unidad,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Revisa los campos del formulario.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al registrar la unidad.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'descripcion' => ['required', 'string', 'max:150'],
                'placas' => ['nullable', 'string', 'max:50'],
                'activo' => ['nullable'],
            ]);

            $unidad = $this->unidadService->update((int) $id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Unidad actualizada correctamente.',
                'data' => $unidad,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Revisa los campos del formulario.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al actualizar la unidad.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->unidadService->delete((int) $id);

            return response()->json([
                'success' => true,
                'message' => 'Cambios realizados correctamente.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo actualizar la unidad.',
            ], 500);
        }
    }
}
