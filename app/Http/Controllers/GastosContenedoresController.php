<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bancos;
use App\Models\CategoriasGastos;
use App\Models\Cotizaciones;
use App\Models\Equipo;
use Carbon\Carbon;
use App\Exports\GastosPorPagarExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\BancosService;
use App\Services\GastosService;
use App\Exceptions\SaldoInsuficienteException;
use Illuminate\Support\Facades\Log;

class GastosContenedoresController extends Controller
{
    public function __construct(
        protected BancosService $bancosService,
        protected GastosService $gastosService
    ) {
    }

    public function IndexPayment()
    {
        $fecha = Carbon::now()->format('Y-m-d');
        $bancos = $this->bancosService->getCuentasOption(auth()->user()->id_empresa, $fecha, $fecha, true);
        return view('gastos_contenedor.index', ["bancos" => $bancos]);
    }

    public function getGxp()
    {
        $data = $this->gastosService->getGastosPorPagarConsolidados(auth()->user()->id_empresa);
        return response()->json(["TMensaje" => "success", "contenedores" => $data]);
    }

    public static function getContenedorSecundario($referencia_full)
    {
        if (!is_null($referencia_full)) {
            $secundaria = Cotizaciones::where('referencia_full', $referencia_full)
                ->where('jerarquia', 'Secundario')
                ->with('DocCotizacion.Asignaciones')
                ->first();

            if ($secundaria && $secundaria->DocCotizacion) {
                return ' / ' . $secundaria->DocCotizacion->num_contenedor;
            }
        }
        return '';
    }

    public function PagarGastosMultiple(Request $r)
    {
        try {
            $fechaAplicacion = $r->get('fecha_aplicacion', Carbon::now()->format('Y-m-d'));
            $this->gastosService->pagarGastosMultipleContenedores(
                $r->gastosPagar ?? [],
                (int) $r->bank,
                $fechaAplicacion,
                (float) ($r->totalPago ?? 0),
                auth()->user()->id_empresa
            );

            return response()->json([
                "Titulo" => "Pago aplicado",
                "Mensaje" => "Se aplicó el pago correctamente. Movimiento registrado en el historial bancario",
                "TMensaje" => "success"
            ]);
        } catch (SaldoInsuficienteException $e) {
            return response()->json([
                "TMensaje" => "error",
                "Titulo" => "Saldo bancario insuficiente",
                "Mensaje" => $e->getMessage(),
                'success' => false
            ]);
        } catch (\Throwable $t) {
            $idError = uniqid();
            Log::channel('daily')->error("$idError : " . $t->getMessage());
            return response()->json([
                "Titulo" => "Ha ocurrido un error",
                "Mensaje" => "Ocurrio un error mientras procesabamos su solicitud. Cod Error $idError: " . $t->getMessage(),
                "TMensaje" => "error"
            ]);
        }
    }

    public function exportarSeleccionados(Request $request)
    {
        $ids = $request->input('selected_ids', []);
        $tipo = $request->input('fileType');

        if (empty($ids)) {
            return response()->json(['error' => 'No hay registros seleccionados.'], 422);
        }

        $exportador = new GastosPorPagarExport($ids);

        if ($tipo === 'xlsx') {
            return Excel::download($exportador, 'gastos_seleccionados.xlsx');
        }

        if ($tipo === 'pdf') {
            $pdf = Pdf::loadView('reporteria.gxp.excel', [
                'gastos' => $exportador->getGastosData($ids),
                'isExcel' => false
            ]);
            return $pdf->download('gastos_seleccionados.pdf');
        }

        return response()->json(['error' => 'Tipo no válido'], 400);
    }

    public function indexGastosViaje()
    {
        $fecha = Carbon::now()->format('Y-m-d');
        $bancos = $this->bancosService->getCuentasOption(auth()->user()->id_empresa, $fecha, $fecha, true);
        $categorias = CategoriasGastos::orderBy('categoria')->get();
        $equipos = Equipo::where('id_empresa', auth()->user()->id_empresa)->get();

        return view('gastos_contenedor.index-gastos-v2', compact('bancos', 'categorias', 'equipos'));
    }

    public function gastosViajesList(Request $r)
    {
        $handsOnTableData = $this->gastosService->getGastosViajesHandsOnTable(
            auth()->user()->id_empresa,
            $r->from,
            $r->to
        );

        return response()->json(['handsOnTableData' => $handsOnTableData]);
    }

    public function confirmarGastos(Request $r)
    {
        try {
            $this->gastosService->confirmarGastosContenedores(
                $r->datahandsOnTableGastos ?? [],
                auth()->user()->id_empresa
            );

            return response()->json([
                "Titulo" => "Gasto agregado",
                "Mensaje" => "Se agregó el gasto",
                "TMensaje" => "success"
            ]);
        } catch (SaldoInsuficienteException $e) {
            return response()->json([
                "TMensaje" => "error",
                "Titulo" => "Saldo bancos 2",
                "Mensaje" => $e->getMessage(),
                'success' => false
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                "Titulo"  => "Debe seleccionar banco",
                "Mensaje" => $e->getMessage(),
                "TMensaje" => "warning"
            ]);
        } catch (\Throwable $t) {
            $idError = uniqid();
            Log::channel('daily')->error("$idError : " . $t->getMessage());
            return response()->json([
                "Titulo" => "Ha ocurrido un error",
                "Mensaje" => "Ocurrio un error mientras procesabamos su solicitud. Cod Error $idError",
                "TMensaje" => "warning",
                "eerr admin" => $t->getMessage()
            ]);
        }
    }
}
