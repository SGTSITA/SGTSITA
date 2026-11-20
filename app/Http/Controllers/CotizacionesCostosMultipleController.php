<?php

namespace App\Http\Controllers;

use App\Exports\CostosMultipleExport;
use App\Http\Requests\GuardarCostosCotizacionMultipleRequest;
use App\Http\Requests\GuardarCostosProveedorMultipleRequest;
use App\Models\Client;
use App\Models\Proveedor;
use App\Services\CotizacionesService;
use App\Services\EquipoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CotizacionesCostosMultipleController extends Controller
{
    public function __construct(
        protected CotizacionesService $cotizacionesService,
        protected EquipoService $equipoService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:costos-multiples-coti');
    }

    /**
     * Muestra la vista principal de Captura Múltiple de Costos.
     *
     * @return View
     */
    public function index(): View
    {
        $idEmpresa = (int) auth()->user()->id_empresa;

        // Validar con el servicio si la empresa es multi-equipo global
        $esGlobal = $this->equipoService->esEmpresaGlobal($idEmpresa);
        $equipos = $esGlobal ? $this->equipoService->getTractocamiones($idEmpresa) : collect();

        // Obtener únicamente clientes activos que tengan viajes registrados en esta empresa
        $clientIdsConViajes = \App\Models\Cotizaciones::where(function ($q) use ($idEmpresa) {
                if ($idEmpresa != 0) {
                    $q->where('id_empresa', $idEmpresa);
                }
            })
            ->where('jerarquia', '!=', 'Secundario')
            ->whereNotIn('estatus', ['Cancelada', 'Pendiente'])
            ->whereNotNull('id_cliente')
            ->distinct()
            ->pluck('id_cliente');

        $clientes = Client::whereIn('id', $clientIdsConViajes)
            ->where('is_active', 1)
            ->select('id', 'nombre')
            ->orderBy('nombre', 'asc')
            ->get();

        // Catálogo de proveedores para asignaciones (solo tipo de viaje foráneo y local_foráneo)
        $proveedores = Proveedor::where(function ($q) use ($idEmpresa) {
                $q->where('id_empresa', $idEmpresa)
                  ->orWhereNull('id_empresa');
            })
            ->whereIn('tipo_viaje', ['foraneo', 'local_foraneo'])
            ->orderBy('nombre', 'asc')
            ->get(['id', 'nombre']);

        return view('cotizaciones.costos_multiple', compact(
            'clientes',
            'equipos',
            'esGlobal',
            'proveedores'
        ));
    }

    /**
     * Retorna los datos estructurados en formato JSON para Handsontable.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function datos(Request $request): JsonResponse
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(120);

        try {
            $idEmpresa = (int) auth()->user()->id_empresa;
            $idCliente = $request->filled('cliente_id') ? (int) $request->cliente_id : null;
            $idCamion = $request->filled('equipo_id') ? (int) $request->equipo_id : null;
            $estadoCosto = (string) $request->input('estado_costo', 'todos');

            $datos = $this->cotizacionesService->obtenerViajesParaCapturaCostos($idEmpresa, $idCliente, $idCamion, $estadoCosto);

            return response()->json([
                'TMensaje' => 'success',
                'cotizaciones' => $datos['cotizaciones'],
                'proveedores' => $datos['proveedores'],
                'total_registros' => $datos['total_registros'],
            ]);
        } catch (\Throwable $e) {
            Log::error("Error en CotizacionesCostosMultipleController@datos: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Error al consultar viajes',
                'Mensaje' => $e->getMessage(),
                'cotizaciones' => [],
                'proveedores' => [],
                'total_registros' => 0,
            ], 500);
        }
    }

    /**
     * Guarda en lote los costos de cotización en viajes->costos (viajes_costos) y actualiza cotizaciones.
     *
     * @param GuardarCostosCotizacionMultipleRequest $request
     * @return JsonResponse
     */
    public function guardarCotizacion(GuardarCostosCotizacionMultipleRequest $request): JsonResponse
    {
        $idEmpresa = (int) auth()->user()->id_empresa;
        $filas = $request->validated()['filas'];

        $actualizados = $this->cotizacionesService->guardarCostosCotizacionMultiple($filas, $idEmpresa);

        return response()->json([
            'TMensaje' => 'success',
            'Titulo' => 'Costos de Cotización Guardados',
            'Mensaje' => "Se guardaron exitosamente los costos de {$actualizados} viaje(s) en seguimiento y se actualizó la provisión contable (CxC).",
            'actualizados' => $actualizados,
        ]);
    }

    /**
     * Guarda en lote los costos de proveedor directamente en la tabla asignaciones.
     *
     * @param GuardarCostosProveedorMultipleRequest $request
     * @return JsonResponse
     */
    public function guardarProveedor(GuardarCostosProveedorMultipleRequest $request): JsonResponse
    {
        $idEmpresa = (int) auth()->user()->id_empresa;
        $filas = $request->validated()['filas'];

        $actualizados = $this->cotizacionesService->guardarCostosProveedorMultiple($filas, $idEmpresa);

        return response()->json([
            'TMensaje' => 'success',
            'Titulo' => 'Costos de Proveedor Guardados',
            'Mensaje' => "Se actualizaron directamente los costos de {$actualizados} asignación(es) de proveedor.",
            'actualizados' => $actualizados,
        ]);
    }

    /**
     * Exporta a Excel los viajes filtrados con información detallada de cotización y proveedor.
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function exportar(Request $request): BinaryFileResponse
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        $idEmpresa = (int) auth()->user()->id_empresa;
        $idCliente = $request->filled('cliente_id') ? (int) $request->cliente_id : null;
        $idCamion = $request->filled('equipo_id') ? (int) $request->equipo_id : null;
        $estadoCosto = (string) $request->input('estado_costo', 'todos');

        $datos = $this->cotizacionesService->obtenerViajesParaCapturaCostos($idEmpresa, $idCliente, $idCamion, $estadoCosto);

        $nombreArchivo = 'costos_seguimiento_' . date('Ymd_His') . '.xlsx';

        return Excel::download(
            new CostosMultipleExport($datos['cotizaciones'], $datos['proveedores']),
            $nombreArchivo
        );
    }
}
