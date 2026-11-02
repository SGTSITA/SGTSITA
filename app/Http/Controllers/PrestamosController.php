<?php

namespace App\Http\Controllers;

use App\Exports\OperadorPrestamosExport;
use App\Http\Requests\AbonarPrestamoRequest;
use App\Http\Requests\StorePrestamoRequest;
use App\Services\PrestamoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PrestamosController extends Controller
{
    protected PrestamoService $prestamoService;

    public function __construct(PrestamoService $prestamoService)
    {
        $this->prestamoService = $prestamoService;
    }

    public function index(): View
    {
        $data = $this->prestamoService->getIndexData();

        return view('operadores.prestamos.registrar_prestamos', [
            'operadores' => $data['operadores'],
            'bancos' => $data['bancos']
        ]);
    }

    public function store(StorePrestamoRequest $request): JsonResponse
    {
        $result = $this->prestamoService->storePrestamo($request->validated());

        return response()->json($result, $result['status'] ?? 200);
    }

    public function getListaPrestamos(): JsonResponse
    {
        $prestamos = $this->prestamoService->getListaPrestamosAgrupados(auth()->user());

        return response()->json([
            'prestamos' => $prestamos
        ]);
    }

    public function getPrestamosPagos($prestamo): JsonResponse
    {
        $data = $this->prestamoService->getPrestamosPagosDetalle((int) $prestamo);

        return response()->json($data);
    }

    public function showOperador($id): View
    {
        $data = $this->prestamoService->getOperadorPrestamosDetalle((int) $id, auth()->user());

        return view('operadores.prestamos.show', $data);
    }

    public function abonar(AbonarPrestamoRequest $request, $id): JsonResponse
    {
        try {
            $result = $this->prestamoService->abonarPrestamo((int) $id, $request->validated());

            return response()->json($result, $result['status'] ?? 200);
        } catch (\Throwable $e) {
            Log::error('Error al registrar abono: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el abono',
                'errorAdmin' => $e->getMessage()
            ], 500);
        }
    }

    public function reportePrestamosPdf($id)
    {
        $data = $this->prestamoService->getDatosReporteOperador((int) $id, auth()->user());

        $pdf = Pdf::loadView('reporteria.operadores.prestamos', $data)
            ->setPaper('letter', 'portrait');

        return $pdf->stream('reporte-prestamos-' . $data['operador']->id . '.pdf');
    }

    public function reportePrestamosexcel($id): BinaryFileResponse
    {
        $data = $this->prestamoService->getDatosReporteOperador((int) $id, auth()->user());

        return Excel::download(
            new OperadorPrestamosExport(
                $data['operador'],
                $data['prestamos'],
                $data['totalPrestamos'],
                $data['totalAdelantos'],
                $data['totalDeuda'],
                $data['totalAbonos'],
                $data['saldoFinal']
            ),
            'reporte-prestamos-' . $data['operador']->id . '.xlsx'
        );
    }
}
