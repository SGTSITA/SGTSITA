<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Proveedor;
use App\Services\BancosService;
use App\Services\CuentasPagarService;
use App\Exceptions\SaldoInsuficienteException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CuentasPagarController extends Controller
{
    public function __construct(
        protected BancosService $bancosService,
        protected CuentasPagarService $cuentasPagarService
    ) {
    }

    public function index()
    {
        $cotizacionesPorCliente = $this->cuentasPagarService->obtenerProveedoresConSaldo(auth()->user()->id_empresa);
        return view('cuentas_pagar.index', compact('cotizacionesPorCliente'));
    }

    public function viajes_por_liquidar(Request $request)
    {
        $data = $this->cuentasPagarService->obtenerViajesPorLiquidar(
            auth()->user()->id_empresa,
            (int) $request->proveedor
        );

        return response()->json([
            "success" => true,
            "handsOnTableData" => $data['handsOnTableData'],
            "cotizacionesPorPagar" => $data['cotizacionesPorPagar'],
        ]);
    }

    public function show($id)
    {
        $data = $this->cuentasPagarService->getDatosShow((int) $id, auth()->user()->id_empresa);
        return view('cuentas_pagar.pagos_v2', $data);
    }

    public function aplicar_pagos(Request $request)
    {
        try {
            $resultado = $this->cuentasPagarService->procesarPagoProveedor(
                $request->all(),
                auth()->user()
            );

            return response()->json($resultado);
        } catch (SaldoInsuficienteException $e) {
            return response()->json([
                "TMensaje" => "error",
                "Titulo" => "Saldo bancario insuficiente",
                "Mensaje" => $e->getMessage(),
                'success' => false,
            ]);
        } catch (\Throwable $t) {
            Log::channel('daily')->error('Error en CuentasPagarController->aplicar_pagos: ' . $t->getMessage());
            return response()->json([
                "success" => false,
                "Titulo" => "Error",
                "Mensaje" => "No pudimos aplicar el pago, existe un error: " . $t->getMessage(),
                "TMensaje" => "error",
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $this->cuentasPagarService->aplicarPagoDirectoLegacy((int) $id, $request->all(), $request);
        return redirect()->back()->with('success', 'Comprobante de pago exitosamente');
    }

    public function update_varios(Request $request)
    {
        $this->cuentasPagarService->aplicarPagoVariosLegacy($request->all(), $request);
        return redirect()->back()->with('success', 'Cobro exitoso');
    }

    public function buscarPagos(Request $request)
    {
        $idEmpresa = auth()->user()->id_empresa;

        if (!$request->has('fecha_inicio') || !$request->has('fecha_fin')) {
            $request->merge([
                'fecha_inicio' => now()->subDays(6)->format('Y-m-d'),
                'fecha_fin' => now()->format('Y-m-d'),
            ]);
        }

        $clientes = Client::join('client_empresa as ce', 'clients.id', '=', 'ce.id_client')
            ->where('ce.id_empresa', $idEmpresa)
            ->where('is_active', 1)
            ->orderBy('nombre')
            ->select('clients.*')
            ->get();

        $proveedores = Proveedor::where('id_empresa', $idEmpresa)
            ->orderBy('nombre')
            ->get();

        $pagos = $this->cuentasPagarService->obtenerPagosHistoricos($request->all(), $idEmpresa);

        return view('cuentas_pagar.historico', compact('pagos', 'clientes', 'proveedores'));
    }
}
