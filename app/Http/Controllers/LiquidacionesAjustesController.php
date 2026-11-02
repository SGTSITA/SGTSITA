<?php

namespace App\Http\Controllers;

use App\Exceptions\SaldoInsuficienteException;
use App\Http\Requests\StoreLiquidacionAjusteRequest;
use App\Models\Bancos;
use App\Models\Operador;
use App\Services\LiquidacionesService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LiquidacionesAjustesController extends Controller
{
    protected LiquidacionesService $liquidacionesService;

    public function __construct(LiquidacionesService $liquidacionesService)
    {
        $this->liquidacionesService = $liquidacionesService;
    }

    public function index()
    {
        $idEmpresa = Auth::user()->id_empresa;

        $operadores = Operador::where('id_empresa', $idEmpresa)
            ->orderBy('nombre', 'asc')
            ->get();

        $bancos = Bancos::where('id_empresa', $idEmpresa)
            ->orderBy('nombre_banco', 'asc')
            ->get();

        return view('liquidaciones.ajustes.index', compact('operadores', 'bancos'));
    }

    public function data(Request $request)
    {
        $data = $this->liquidacionesService->getAjustesData($request->all(), (int) Auth::user()->id_empresa);

        return response()->json([
            'TMensaje' => 'success',
            'data' => $data,
        ]);
    }

    public function buscarLiquidaciones(Request $request)
    {
        $items = $this->liquidacionesService->buscarLiquidaciones($request->get('q', ''), (int) Auth::user()->id_empresa);

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    public function getPrestamosOperador($idOperador)
    {
        $items = $this->liquidacionesService->getPrestamosOperador((int) $idOperador);

        if ($items === null) {
            return response()->json(['success' => false, 'items' => []], 404);
        }

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    public function store(StoreLiquidacionAjusteRequest $request)
    {
        try {
            $ajuste = $this->liquidacionesService->storeAjuste($request->validated(), Auth::user());

            return response()->json([
                'success' => true,
                'Titulo' => 'Ajuste Aplicado',
                'Mensaje' => 'El ajuste de liquidación ' . $ajuste->folio . ' fue registrado correctamente.',
                'TMensaje' => 'success',
                'id_ajuste' => $ajuste->id,
            ]);
        } catch (SaldoInsuficienteException $e) {
            return response()->json([
                'Titulo' => 'Saldo insuficiente en banco',
                'Mensaje' => $e->getMessage(),
                'TMensaje' => 'warning',
                'success' => false,
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'Titulo' => 'Acción no permitida',
                'Mensaje' => $e->getMessage(),
                'TMensaje' => 'warning',
                'success' => false,
            ]);
        } catch (\Throwable $t) {
            Log::channel('daily')->error('LiquidacionesAjustesController->store: ' . $t->getMessage(), [
                'trace' => $t->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'Titulo' => 'Error al guardar',
                'Mensaje' => 'Ocurrió un error al procesar el ajuste: ' . $t->getMessage(),
                'TMensaje' => 'error',
            ], 500);
        }
    }

    public function comprobantePdf($id)
    {
        $datos = $this->liquidacionesService->getDatosComprobanteAjuste((int) $id, (int) Auth::user()->id_empresa);
        $ajuste = $datos['ajuste'];
        $user = Auth::user();

        $pdf = Pdf::loadView('liquidaciones.ajustes.pdf', compact('ajuste', 'user'));
        $pdf->setPaper('letter', 'portrait');

        $fileName = 'Comprobante_Ajuste_' . ($ajuste->folio ?: $ajuste->id) . '.pdf';

        return $pdf->stream($fileName);
    }

    public function cancelar($id)
    {
        try {
            $this->liquidacionesService->cancelarAjuste((int) $id, (int) Auth::user()->id_empresa, Auth::user());

            return response()->json([
                'success' => true,
                'Titulo' => 'Ajuste cancelado',
                'Mensaje' => 'El ajuste fue cancelado y se revirtieron los saldos bancarios y amortizaciones de préstamos.',
                'TMensaje' => 'success',
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'Titulo' => 'Acción no permitida',
                'Mensaje' => $e->getMessage(),
                'TMensaje' => 'warning',
            ]);
        } catch (\Throwable $t) {
            Log::channel('daily')->error('LiquidacionesAjustesController->cancelar: ' . $t->getMessage());

            return response()->json([
                'success' => false,
                'Titulo' => 'Error al cancelar',
                'Mensaje' => 'No se pudo cancelar el ajuste: ' . $t->getMessage(),
                'TMensaje' => 'error',
            ], 500);
        }
    }
}
