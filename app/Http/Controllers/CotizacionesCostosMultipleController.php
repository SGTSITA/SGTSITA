<?php

namespace App\Http\Controllers;

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

class CotizacionesCostosMultipleController extends Controller
{
    public function __construct(
        protected CotizacionesService $cotizacionesService,
        protected EquipoService $equipoService
    ) {
        $this->middleware('auth');
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

        // Obtener clientes activos vinculados a la empresa
        $clientes = Client::join('client_empresa as ce', 'clients.id', '=', 'ce.id_client')
            ->when($idEmpresa != 0, function ($query) use ($idEmpresa) {
                $query->where('ce.id_empresa', $idEmpresa);
            })
            ->where('is_active', 1)
            ->select('clients.id', 'clients.nombre')
            ->orderBy('clients.nombre', 'asc')
            ->get();

        if ($clientes->isEmpty()) {
            $clientes = Client::where('id_empresa', $idEmpresa)
                ->where('is_active', 1)
                ->orderBy('nombre', 'asc')
                ->get(['id', 'nombre']);
        }

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
}
