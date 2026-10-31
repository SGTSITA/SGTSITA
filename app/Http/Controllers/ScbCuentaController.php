<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;
use App\Services\scb\ScbCuentaService;
use App\Models\ScbBancoModulo;

class ScbCuentaController extends Controller
{
      public function __construct(
        protected ScbCuentaService $cuentaService
    ) {
    }

    public function index()
    {
        $cuentas = $this->cuentaService->getAll();

        $bancos = ScbBancoModulo::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('scb.cuentas.index', compact('cuentas', 'bancos'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'banco_id' => ['required', 'exists:scb_bancos_modulo,id'],
                'beneficiario' => ['nullable', 'string', 'max:150'],
                'numero_cuenta' => ['nullable', 'string', 'max:100'],
                'clabe' => ['nullable', 'string', 'max:100'],
                'moneda' => ['required', 'string', 'max:10'],
                'saldo_inicial' => ['nullable', 'numeric', 'min:0'],
                'activo' => ['nullable'],
            ]);

            $cuenta = $this->cuentaService->create($data);
            $cuenta->load('banco');

            return response()->json([
                'success' => true,
                'message' => 'Cuenta registrada correctamente.',
                'data' => $cuenta,
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
                'message' => 'Ocurrió un error al registrar la cuenta.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'banco_id' => ['required', 'exists:scb_bancos_modulo,id'],
                'beneficiario' => ['nullable', 'string', 'max:150'],
                'numero_cuenta' => ['nullable', 'string', 'max:100'],
                'clabe' => ['nullable', 'string', 'max:100'],
                'moneda' => ['required', 'string', 'max:10'],
                'saldo_inicial' => ['nullable', 'numeric', 'min:0'],
                'activo' => ['nullable'],
            ]);

            $cuenta = $this->cuentaService->update((int) $id, $data);
            $cuenta->load('banco');

            return response()->json([
                'success' => true,
                'message' => 'Cuenta actualizada correctamente.',
                'data' => $cuenta,
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
                'message' => 'Ocurrió un error al actualizar la cuenta.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->cuentaService->delete((int) $id);

            return response()->json([
                'success' => true,
                'message' => 'Cambios realizados correctamente.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo actualizar la cuenta.',
            ], 500);
        }
    }
}
