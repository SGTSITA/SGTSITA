<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\scb\ScbBancoService;
use Illuminate\Validation\ValidationException;
use Throwable;

class ScbBancoController extends Controller
{
   public function __construct(
        protected ScbBancoService $bancoService
    ) {
    }

      public function index()
    {
        $bancos = $this->bancoService->getAll();

        return view('scb.bancos.index', compact('bancos'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'nombre' => ['required', 'string', 'max:150'],
                'clave' => ['nullable', 'string', 'max:50'],
                'activo' => ['nullable'],
            ]);

            $banco = $this->bancoService->create($data);

            return response()->json([
                'success' => true,
                'message' => 'Banco registrado correctamente.',
                'data' => $banco,
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
                'message' => 'Ocurrió un error al registrar el banco.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'nombre' => ['required', 'string', 'max:150'],
                'clave' => ['nullable', 'string', 'max:50'],
                'activo' => ['nullable'],
            ]);

            $banco = $this->bancoService->update((int) $id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Banco actualizado correctamente.',
                'data' => $banco,
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
                'message' => 'Ocurrió un error al actualizar el banco.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->bancoService->delete((int) $id);

            return response()->json([
                'success' => true,
                'message' => 'Cambios realizados correctamente.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo actualizar  el banco.',
            ], 500);
        }
    }
}
