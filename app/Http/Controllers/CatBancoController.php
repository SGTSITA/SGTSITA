<?php

namespace App\Http\Controllers;

use App\Exports\CuentaBancosExport;
use App\Http\Requests\StoreCatBancoRequest;
use App\Http\Requests\StoreCuentaBancariaRequest;
use App\Http\Requests\TransferenciaBancariaRequest;
use App\Http\Requests\UpdateCatBancoRequest;
use App\Models\Bancos;
use App\Services\BancosService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CatBancoController extends Controller
{
    protected BancosService $bankService;

    public function __construct(BancosService $bankService)
    {
        $this->bankService = $bankService;
    }

    public function index(): View
    {
        $data = $this->bankService->getCatBancosIndexData(Auth::user());

        return view('bancos.indexv2', $data);
    }

    public function store(StoreCatBancoRequest $request): JsonResponse
    {
        $this->bankService->storeCatBanco($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Banco creado correctamente'
        ]);
    }

    public function update(UpdateCatBancoRequest $request, $id): JsonResponse
    {
        $this->bankService->updateCatBanco((int) $id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Banco actualizado correctamente'
        ]);
    }

    public function destroy($id): RedirectResponse
    {
        $this->bankService->desactivarCatBanco((int) $id);

        return redirect()->back()->with('success', 'Banco desactivado');
    }

    public function create_cuentas($id): View
    {
        $data = $this->bankService->getCuentasPorCatBanco((int) $id);

        return view('bancos.index-cuentas', $data);
    }

    public function store_cuentas(StoreCuentaBancariaRequest $request): JsonResponse
    {
        try {
            $cuenta = $this->bankService->storeCuentaBancaria($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Cuenta bancaria registrada correctamente',
                'data' => $cuenta
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la cuenta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update_cuentas(Request $request, $id): JsonResponse
    {
        $request->validate([
            'banco_id' => 'required|integer',
            'tipo_cuenta' => 'required|string',
            'moneda' => 'required|string',
            'numero_cuenta' => 'required|string|max:50',
            'clabe' => 'nullable|string|max:18',
            'beneficiario' => 'required|string|max:255',
            'saldo_inicial' => 'required|numeric',
        ]);

        try {
            $cuenta = $this->bankService->updateCuentaBancaria((int) $id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Cuenta bancaria actualizada correctamente',
                'data' => $cuenta
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la cuenta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function mostrar_movimientos($id): View
    {
        $empresaId = auth()->user()->id_empresa;
        $data = $this->bankService->obtenerDetalleCuenta($id, $empresaId);

        return view('bancos.catbancos-cuentas-movimientos', $data);
    }

    public function getmovimientosperiodo(Request $request, $idcuenta): JsonResponse
    {
        $empresaId = auth()->user()->id_empresa;
        $fecha_ini = $request->fecha_inicio;
        $fecha_fin = $request->fecha_fin;

        $data = $this->bankService->obtenerDetalleCuenta($idcuenta, $empresaId, $fecha_ini, $fecha_fin);

        return response()->json($data);
    }

    public function store_movimientos_cuentas(Request $request, Bancos $cuenta): JsonResponse
    {
        $request->validate([
            'tipo' => ['required', 'in:abono,cargo'],
            'concepto' => ['required', 'string'],
            'monto' => ['required', 'numeric', 'not_in:0'],
            'fecha_movimiento' => ['required', 'date'],
            'referencia' => ['nullable', 'string'],
            'origen' => 'required|in:manual,banco,ajuste,importacion',
            'tipo_concepto' => 'nullable|in:operativo,interes,traspaso,comision',
        ]);

        $mov = $this->bankService->registrarMovimiento([
            'cuenta_bancaria_id' => $cuenta->id,
            'tipo' => $request->tipo,
            'concepto' => $request->concepto,
            'monto' => $request->monto,
            'fecha_movimiento' => $request->fecha_movimiento,
            'referencia' => $request->referencia,
            'origen' => $request->origen,
            'tipo_concepto' => $request->tipo_concepto ?? 'operativo',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'El movimiento se registró correctamente',
            'data' => $mov,
        ]);
    }

    public function transferencia(TransferenciaBancariaRequest $request): JsonResponse
    {
        try {
            $result = $this->bankService->transferenciaEntreCuentas($request->validated());

            return response()->json($result, $result['status'] ?? 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aplicar la transferencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportar(Request $request)
    {
        $request->validate([
            'cuenta_id' => 'required|integer',
            'formato' => 'required|in:pdf,excel',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
        ]);

        $empresaId = auth()->user()->id_empresa;
        $data = $this->bankService->obtenerDetalleCuenta($request->cuenta_id, $empresaId, $request->fecha_inicio, $request->fecha_fin);

        if ($request->formato === 'excel') {
            return Excel::download(
                new CuentaBancosExport(
                    $data['movimientos'],
                    $data['cuenta'],
                    $data['saldoAnterior'],
                    $data['total_depositos'],
                    $data['total_cargos'],
                    $data['saldoActual']
                ),
                'movimientos.xlsx'
            );
        }

        $data['fecha_inicio'] = Carbon::parse($request->fecha_inicio)->toDateString();
        $data['fecha_fin'] = Carbon::parse($request->fecha_fin)->toDateString();

        if ($request->formato === 'pdf') {
            $pdf = Pdf::loadView('bancos.pdf_catbancos_cuentas_movimientos', $data);
            return $pdf->download('movimientos.pdf');
        }

        abort(400);
    }

    public function getMovimientosPorUnidad(Request $request): JsonResponse
    {
        $request->validate([
            'id_equipo' => 'required|integer|exists:equipos,id',
        ]);

        $data = $this->bankService->getMovimientosPorUnidad((int) $request->id_equipo);

        return response()->json($data);
    }
}
