<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ScbReporteExport;
use App\Models\ScbBancoModuloCuenta;
use Barryvdh\DomPDF\Facade\Pdf;
use  App\Services\scb\ScbReporteService;
use Maatwebsite\Excel\Facades\Excel;

class ScbReporteController extends Controller
{
    public function __construct(
        protected ScbReporteService $reporteService
    ) {
    }

     public function index()
    {
        $cuentas = ScbBancoModuloCuenta::query()
            ->with('banco')
            ->orderBy('id')
            ->get();

        return view('scb.reportes.index', compact('cuentas'));
    }

    public function consultar(Request $request)
    {
        $data = $this->validarFiltros($request);

        $reporte = $this->reporteService->generar($data);

        return response()->json([
            'success' => true,
            'data' => $reporte,
        ]);
    }

    public function pdf(Request $request)
    {
        $data = $this->validarFiltros($request);

        $reporte = $this->reporteService->generar($data);

        $pdf = Pdf::loadView('scb.reportes.pdf.estado-cuenta', [
            'reporte' => $reporte,
        ])->setPaper('letter', 'landscape');

        $nombre = $this->nombreArchivo($data, 'pdf');

        return $pdf->stream($nombre);
    }

    public function excel(Request $request)
    {
        $data = $this->validarFiltros($request);

        $reporte = $this->reporteService->generar($data);

        $nombre = $this->nombreArchivo($data, 'xlsx');

        return Excel::download(new ScbReporteExport($reporte), $nombre);
    }

    private function validarFiltros(Request $request): array
    {
        return $request->validate([
            'cuenta_id' => ['required', 'exists:scb_bancos_modulo_cuentas,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'tipo_reporte' => ['required', 'in:estado_cuenta,detallado'],
        ]);
    }

    private function nombreArchivo(array $data, string $extension): string
    {
        $tipo = $data['tipo_reporte'] === 'detallado'
            ? 'detallado'
            : 'estado-cuenta';

        return "scb-{$tipo}-{$data['fecha_inicio']}-{$data['fecha_fin']}.{$extension}";
    }
}

