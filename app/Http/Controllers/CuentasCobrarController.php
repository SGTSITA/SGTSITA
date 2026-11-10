<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\BancosService;
use App\Services\CuentasCobrarService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CuentasCobrarController extends Controller
{
    public function __construct(
        protected BancosService $bancosService,
        protected CuentasCobrarService $cuentasCobrarService
    ) {
    }

    public function index()
    {
        $cotizacionesPorCliente = $this->cuentasCobrarService->obtenerporCliente();
        return view('cuentas_cobrar.index', compact('cotizacionesPorCliente'));
    }

    public function cobranza_v2($id)
    {
        $cotizacion = $this->cuentasCobrarService->obtenerporCliente($id)->first();
        $cliente = Client::findOrFail($id);
        $fecha = Carbon::now()->format('Y-m-d');
        $bancos = $this->bancosService->getCuentasOption(auth()->user()->id_empresa, $fecha, $fecha, false);

        return view('cuentas_cobrar.cobranza_cliente', compact('bancos', 'cliente', 'cotizacion'));
    }

    public function viajes_por_liquidar(Request $request)
    {
        $cotizacionesPorPagar = $this->cuentasCobrarService->obtenerporClienteId($request->client, true);

        $handsOnTableData = $cotizacionesPorPagar->map(function ($item) {
            return [
                $item->num_contenedor,
                $item->numero_edo_cuenta ?? 'NA',
                $item->nombre_subcliente,
                $item->tipo . ' ( ' . $item->tipo_viaje . ' )',
                ($item->estatus == 'Aprobada') ? "En Curso" : $item->estatus,
                $item->total_restante,
                $item->total_restante,
                0,
                0,
                0,
                $item->id,
            ];
        });

        return response()->json([
            "success" => true,
            "handsOnTableData" => $handsOnTableData,
            "cotizacionesPorPagar" => $cotizacionesPorPagar,
        ]);
    }

    public function aplicar_pagos(Request $request)
    {
        try {
            DB::beginTransaction();

            $resultado = $this->cuentasCobrarService->procesarCobro($request);
            $this->bancosService->guardarBancoLegacy($request, $resultado['contenedoresAbonos']);
            $this->bancosService->procesarIngresosDesdeCobro($request, $resultado, $resultado['cobroPago']->id);

            DB::commit();

            return response()->json([
                "success" => true,
                "Titulo" => "Cobro exitoso",
                "Mensaje" => "Hemos aplicado el cobro de los elementos indicados",
                "TMensaje" => "success",
            ]);
        } catch (\Throwable $t) {
            DB::rollback();
            return response()->json([
                "success" => false,
                "Mensaje" => $t->getMessage(),
            ]);
        }
    }

    public function show($id)
    {
        $cliente = Client::findOrFail($id);
        $cotizacionesPorPagar = $this->cuentasCobrarService->obtenerporClienteId($id, false);
        $cotizacion = $this->cuentasCobrarService->obtenerporCliente($id)->first();
        $fecha = Carbon::now()->format('Y-m-d');
        $bancos = $this->bancosService->getCuentasOption(auth()->user()->id_empresa, $fecha, $fecha, false);

        return view('cuentas_cobrar.show', compact('cotizacionesPorPagar', 'bancos', 'cliente', 'cotizacion'));
    }

    public function update(Request $request, $id)
    {
        $this->cuentasCobrarService->aplicarPagoDirectoLegacy((int) $id, $request->all(), $request);
        return redirect()->back()->with('success', 'Comprobante de pago exitosamente');
    }

    public function update_varios(Request $request)
    {
        $this->cuentasCobrarService->aplicarPagoVariosLegacy($request->all(), $request);
        return redirect()->back()->with('success', 'Cobro exitoso');
    }

    public function storeEdocuenta(Request $request)
    {
        $request->validate([
            'numero' => 'required|string|max:50',
            'cotizacionesId' => 'required|array|min:1',
        ]);

        $res = $this->cuentasCobrarService->guardarEstadoCuenta(
            $request->all(),
            auth()->user()->id_empresa,
            auth()->id()
        );

        if (!($res['ok'] ?? false)) {
            return response()->json($res, 400);
        }

        return response()->json($res);
    }

    public function buscarPagos(Request $request)
    {
        if (!$request->has('fecha_inicio') || !$request->has('fecha_fin')) {
            $request->merge([
                'fecha_inicio' => now()->subDays(6)->format('Y-m-d'),
                'fecha_fin' => now()->format('Y-m-d'),
            ]);
        }

        $catalogos = $this->cuentasCobrarService->getCatalogosHistorico(auth()->user()->id_empresa);
        $pagos = $this->cuentasCobrarService->obtenerPagosHistoricos($request->all());

        return view('cuentas_cobrar.historico', array_merge($catalogos, compact('pagos')));
    }

    public function eliminarCobroPago(Request $request, $id)
    {
        try {
            $this->cuentasCobrarService->eliminarCobroPago(
                (int) $id,
                $request->input('fecha_cancelacion')
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'El cobro/pago y sus movimientos bancarios asociados han sido cancelados y eliminados correctamente.'
                ]);
            }

            return redirect()->back()->with('success', 'El cobro/pago y sus movimientos bancarios asociados han sido cancelados y eliminados correctamente.');
        } catch (\Throwable $th) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al intentar eliminar el registro: ' . $th->getMessage()
                ], 422);
            }
            return redirect()->back()->with('error', 'Error al intentar eliminar el registro: ' . $th->getMessage());
        }
    }

    public function exportarExcel(Request $request)
    {
        $tipo = $request->input('tipo', 'cxc');
        $pagos = $this->cuentasCobrarService->obtenerPagosHistoricosSinPaginacion($request->all(), $tipo);

        $filename = "historial_" . $tipo . "_" . date('Y-m-d') . ".xls";

        return response()->view('cuentas_cobrar.excel_historico', compact('pagos', 'tipo'))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportarPdf(Request $request)
    {
        $tipo = $request->input('tipo', 'cxc');
        $pagos = $this->cuentasCobrarService->obtenerPagosHistoricosSinPaginacion($request->all(), $tipo);

        $pdf = Pdf::loadView('cuentas_cobrar.pdf_historico', compact('pagos', 'tipo'));
        return $pdf->download("historial_" . $tipo . "_" . date('Y-m-d') . ".pdf");
    }

    public function descargarComprobantePdf($id)
    {
        $pago = $this->cuentasCobrarService->obtenerPagoDetalle($id);

        $pdf = Pdf::loadView('cuentas_cobrar.pdf_comprobante', compact('pago'));
        return $pdf->download("comprobante_" . $pago->tipo . "_" . $pago->id . ".pdf");
    }
}
