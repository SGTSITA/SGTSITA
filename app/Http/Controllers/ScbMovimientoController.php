<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScbBancoModuloCuenta;
use App\Models\ScbUnidadModulo;
use App\Services\scb\ScbMovimientoService;
use Illuminate\Validation\ValidationException;
use Throwable;
use Exception;

class ScbMovimientoController extends Controller
{
     public function __construct(
        protected ScbMovimientoService $movimientoService
    ) {
    }

    public function index()
    {
        $movimientos = $this->movimientoService->getAll();

        $cuentas = ScbBancoModuloCuenta::query()
            ->with('banco')
            ->where('activo', true)
            ->orderBy('beneficiario')
            ->get();

        $unidades = ScbUnidadModulo::query()
            ->where('activo', true)
            ->orderBy('descripcion')
            ->get();

        return view('scb.movimientos.index', compact(
            'movimientos',
            'cuentas',
            'unidades'
        ));
    }

    public function show($id)
    {
        try {
            $movimiento = $this->movimientoService->findById((int) $id);

            return response()->json([
                'success' => true,
                'data' => $movimiento,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo consultar el movimiento.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $this->validateData($request);

            $movimiento = $this->movimientoService->create($data);

            return response()->json([
                'success' => true,
                'message' => 'Movimiento registrado correctamente.',
                'data' => $movimiento,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Revisa los campos del formulario.',
                'errors' => $e->errors(),
            ], 422);
            } catch (Exception $e) {
    return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
    ], 422);
} catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al registrar el movimiento.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->validateData($request);

            $movimiento = $this->movimientoService->update((int) $id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Movimiento actualizado correctamente.',
                'data' => $movimiento,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Revisa los campos del formulario.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
    return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
    ], 422);
} catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al actualizar el movimiento.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->movimientoService->delete((int) $id);

            return response()->json([
                'success' => true,
                'message' => 'Movimiento eliminado correctamente.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el movimiento.',
            ], 500);
        }
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
        'cuenta_id' => ['required', 'exists:scb_bancos_modulo_cuentas,id'],
        'tipo' => ['required', 'in:cargo,abono'],
        'fecha_movimiento' => ['required', 'date'],
        'concepto' => ['required', 'string', 'max:255'],
        'referencia_bancaria' => ['nullable', 'string', 'max:150'],
        'observaciones' => ['nullable', 'string'],
        'total_movimiento' => ['required', 'numeric'],

        'detalles' => ['required', 'array', 'min:1'],
        'detalles.*.unidad_id' => ['nullable', 'exists:scb_bancos_unidades_modulo,id'],
        'detalles.*.descripcion' => ['required', 'string', 'max:255'],
        'detalles.*.referencia' => ['nullable', 'string', 'max:150'],
        'detalles.*.monto' => ['required', 'numeric'],
        'detalles.*.observaciones' => ['nullable', 'string'],
    ]);

    $totalMovimientoCentavos = $this->moneyToCents($data['total_movimiento']);

    $totalDetallesCentavos = collect($data['detalles'])
        ->sum(fn ($detalle) => $this->moneyToCents($detalle['monto']));

    if ($totalMovimientoCentavos !== $totalDetallesCentavos) {
        $diferencia = ($totalMovimientoCentavos - $totalDetallesCentavos) / 100;

        throw ValidationException::withMessages([
            'total_movimiento' => [
                'Debe coincidir con el total de detalles. Diferencia: $' . number_format($diferencia, 2, '.', ','),
            ],
        ]);
    }


    $data['total_movimiento'] = number_format($totalMovimientoCentavos / 100, 2, '.', '');

    $data['detalles'] = collect($data['detalles'])
        ->map(function ($detalle) {
            $detalle['monto'] = number_format($this->moneyToCents($detalle['monto']) / 100, 2, '.', '');

            return $detalle;
        })
        ->values()
        ->toArray();

    return $data;
    }
private function moneyToCents($value): int
{
    $value = str_replace(',', '', (string) $value);

    return (int) round(((float) $value) * 100);
}
    public function estadoCuenta(Request $request)
{
    $data = $request->validate([
        'cuenta_id' => ['required', 'exists:scb_bancos_modulo_cuentas,id'],
         'unidad_id' => ['nullable','exists:scb_bancos_unidades_modulo,id'],
        'fecha_inicio' => ['required', 'date'],
        'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
    ]);


   // dd($request->all());

    $unidadId = $request->filled('unidad_id')
        ? (int) $request->input('unidad_id')
        : null;

    return response()->json(
        $this->movimientoService->estadoCuenta(
            cuentaId: (int) $data['cuenta_id'],
            unidadId: $unidadId,
            fechaInicio: $data['fecha_inicio'],
            fechaFin: $data['fecha_fin'],

        )
    );
}
}
