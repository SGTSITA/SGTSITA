<?php

namespace App\Http\Controllers;

use App\Services\ApiValidationService;
use Illuminate\Http\Request;

class ApiValidationController extends Controller
{
    protected $apiValidationService;

    public function __construct(ApiValidationService $apiValidationService)
    {
        $this->apiValidationService = $apiValidationService;
    }

    private function apiResponse($success, $message, $data = [], $status = 200)
    {
        return response()->json([
            'success' => $success,
            'mensaje' => $message,
            'data'    => $data
        ], $status);
    }

    private function forwardResponse($res)
    {
        return $this->apiResponse($res['success'], $res['message'], $res['data'] ?? [], $res['status'] ?? 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $res = $this->apiValidationService->login($credentials);
        return $this->forwardResponse($res);
    }

    public function validateOperador(Request $request)
    {
        if ($request->has('contrasena')) {
            $request->validate([
                'nombre'     => 'required|string',
                'telefono'   => 'required|string',
                'contrasena' => 'required|string',
            ], [
                'nombre.required' => 'El nombre es requerido.',
                'telefono.required' => 'El teléfono es requerido.',
                'contrasena.required' => 'La contraseña es requerida.',
            ]);
        } else {
            $request->validate([
                'nombre'     => 'required|string',
                'telefono'   => 'required|string',
                'unidad'     => 'required|string',
                'contenedor' => 'required|string',
            ]);
        }

        $res = $this->apiValidationService->validateOperador($request->all());
        return $this->forwardResponse($res);
    }

    public function getOperacionActiva(Request $request)
    {
        $user = $request->user();
        $empresaId = $request->id_empresa ?? $user->id_empresa;

        $res = $this->apiValidationService->getOperacionActiva($user, $empresaId);
        return $this->forwardResponse($res);
    }

    public function getCotizaciones(Request $request)
    {
        $user = $request->user();
        $empresaId = $request->id_empresa ?? $user->id_empresa;

        $res = $this->apiValidationService->getCotizaciones($empresaId);
        return $this->forwardResponse($res);
    }

    public function getViajes(Request $request)
    {
        $user = $request->user();
        $empresaId = $request->id_empresa ?? $user->id_empresa;

        $res = $this->apiValidationService->getViajes($empresaId);
        return $this->forwardResponse($res);
    }

    public function getContenedores(Request $request)
    {
        $user = $request->user();
        $empresaId = $request->id_empresa ?? $user->id_empresa;

        $res = $this->apiValidationService->getContenedores($empresaId);
        return $this->forwardResponse($res);
    }

    public function getReportes(Request $request)
    {
        $user = $request->user();
        $empresaId = $request->id_empresa ?? $user->id_empresa;

        $res = $this->apiValidationService->getReportes($empresaId);
        return $this->forwardResponse($res);
    }

    public function getPlaneacion(Request $request)
    {
        $user = $request->user();
        $empresaId = $request->id_empresa ?? $user->id_empresa;

        $res = $this->apiValidationService->getPlaneacion($empresaId, $request->fecha_inicio, $request->fecha_fin);
        return $this->forwardResponse($res);
    }

    public function getMonitoreo(Request $request)
    {
        $coordenadasController = app(CoordenadasController::class);
        $response = $coordenadasController->getEquiposGps($request);
        $resData = $response->getData(true);

        if (isset($resData['datos']) && is_array($resData['datos'])) {
            $ubicacionService = app(\App\Services\UbicacionService::class);
            $items = [];

            foreach ($resData['datos'] as $dato) {
                if (!empty($dato['id_contenedor'])) {
                    $items[] = [
                        'tipo' => 'Contenedor',
                        'id' => $dato['id_contenedor']
                    ];
                }
            }

            $liveCoords = [];
            if (!empty($items)) {
                try {
                    $liveCoords = $ubicacionService->obtenerUbicacionPorItems($items);
                } catch (\Exception $e) {
                    \Log::error("Error al obtener coordenadas por items: " . $e->getMessage());
                }
            }

            $newDatos = [];
            if (is_array($liveCoords) || is_object($liveCoords)) {
                $originalDatos = [];
                foreach ($resData['datos'] as $d) {
                    $originalDatos[$d['id_contenedor']] = $d;
                }

                foreach ($liveCoords as $res) {
                    $idContenedor = $res['id_contenendor'] ?? $res['id_contenedor'] ?? null;
                    if (!$idContenedor || !isset($originalDatos[$idContenedor])) {
                        continue;
                    }

                    $orig = $originalDatos[$idContenedor];
                    $status = $res['status'] ?? false;
                    $ubicacion = $res['ubicacion'] ?? null;
                    $tipoEquipo = strtolower($res['TipoEquipo'] ?? '');

                    if ($status && $ubicacion && isset($ubicacion['lat']) && floatval($ubicacion['lat']) !== 0.0) {
                        $deviceDato = $orig;
                        $deviceDato['lat'] = $ubicacion['lat'];
                        $deviceDato['lng'] = $ubicacion['lng'];
                        $deviceDato['velocidad'] = $ubicacion['speed'] ?? $ubicacion['velocidad'] ?? 0;
                        $deviceDato['id_equipo'] = $res['equipo'] ?? $orig['id_equipo'];
                        $deviceDato['placas'] = $res['placas'] ?? $orig['placas'] ?? '';

                        if ($tipoEquipo === 'camion') {
                            $deviceDato['tipo'] = 'camion';
                        } elseif ($tipoEquipo === 'chasisb') {
                            $deviceDato['tipo'] = 'chasis b';
                        } else {
                            $deviceDato['tipo'] = 'chasis';
                        }

                        $newDatos[] = $deviceDato;
                    }
                }
            }
            $resData['datos'] = $newDatos;
        }

        return $this->apiResponse(true, 'Datos de monitoreo obtenidos con éxito.', $resData);
    }

    public function finalizarViaje(Request $request)
    {
        $idContenedor = $request->idContenedor ?? $request->idContenendor;
        $res = $this->apiValidationService->finalizarViaje($idContenedor);
        return $this->forwardResponse($res);
    }

    public function infoViaje(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ]);

        $res = $this->apiValidationService->infoViaje($request->id);
        return $this->forwardResponse($res);
    }

    public function guardarCoordenadas(Request $request)
    {
        $res = $this->apiValidationService->guardarCoordenadas($request->all());
        return $this->forwardResponse($res);
    }

    public function iniciarViaje(Request $request)
    {
        $res = $this->apiValidationService->iniciarViaje($request->all());
        return $this->forwardResponse($res);
    }

    public function finalizarViajeOperador(Request $request)
    {
        $res = $this->apiValidationService->finalizarViajeOperador($request->all());
        return $this->forwardResponse($res);
    }

    public function obtenerEstatusFlujo(Request $request)
    {
        $res = $this->apiValidationService->obtenerEstatusFlujo($request->id_asignacion);
        return $this->forwardResponse($res);
    }

    public function getEmpresasPropias(Request $request)
    {
        $res = $this->apiValidationService->getEmpresasPropias();
        return $this->forwardResponse($res);
    }

    public function getCatalogsProgramarViaje(Request $request)
    {
        $user = $request->user();
        $empresaId = $request->id_empresa ?? $user->id_empresa;

       /*  $debugCot = \DB::table('cotizaciones')->where('id', 1192)->first();
        $debugAsig = \DB::table('asignaciones')->where('id_contenedor', 1192)->first();
        \Log::info("DEBUG COT 1192: " . json_encode($debugCot));
        \Log::info("DEBUG ASIG: " . json_encode($debugAsig)); */

        $res = $this->apiValidationService->getCatalogsProgramarViaje($empresaId);
        return $this->forwardResponse($res);
    }

    public function programarViajeMobile(Request $request)
    {
        $res = $this->apiValidationService->programarViajeMobile($request->all());
        $status = $res['status'] ?? 200;
        unset($res['status']);
        return response()->json($res, $status);
    }

    public function anularPlaneacionMobile(Request $request)
    {
        $planeacionController = app(\App\Http\Controllers\PlaneacionController::class);
        $response = $planeacionController->anularPlaneacion($request);
        return response()->json($response->getData(), $response->getStatusCode());
    }

    public function finalizarViajeMobile(Request $request)
    {
        $planeacionController = app(\App\Http\Controllers\PlaneacionController::class);
        $response = $planeacionController->finalizarViaje($request);
        return response()->json($response->getData(), $response->getStatusCode());
    }

    public function getBancosMobile(Request $request)
    {
        $user = $request->user();
        $empresaId = $request->id_empresa ?? $user->id_empresa;
        if ($user) {
            $user->id_empresa = $empresaId;
        }
        $fechaCorte = $request->fecha_corte ?? date('Y-m-d');

        $bancosService = app(\App\Services\BancosService::class);
        $cuentas = $bancosService->getCuentasOption($empresaId, $fechaCorte, $fechaCorte, false);

        return $this->apiResponse(true, 'Cuentas bancarias obtenidas con éxito.', $cuentas);
    }

    public function exportarReporteBancoMobile(Request $request, $id)
    {
        $user = $request->user();
        if ($user) {
            if ($request->has('id_empresa')) {
                $user->id_empresa = $request->id_empresa;
            }
            auth()->setUser($user);
        }

        $fechaCorte = $request->fecha_de ?? date('Y-m-d');

        $request->merge([
            'cuenta_id' => $id,
            'formato' => $request->formato ?? 'pdf',
            'fecha_inicio' => $fechaCorte,
            'fecha_fin' => $fechaCorte,
        ]);

        $catBancoController = app(\App\Http\Controllers\CatBancoController::class);
        return $catBancoController->exportar($request);
    }

    public function generarReporteMobile(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(180);

        // Bypass de permisos Spatie Gate para llamadas API móviles autorizadas
        \Illuminate\Support\Facades\Gate::before(function () {
            return true;
        });

        $user = $request->user();
        if ($user) {
            if ($request->has('id_empresa')) {
                $user->id_empresa = $request->id_empresa;
            }
            auth()->setUser($user);
        }

        // Normalizar parámetros de fechas y tipos de archivos
        if ($request->has('fecha_inicio')) {
            $request->merge(['fechaInicio' => $request->fecha_inicio]);
        }
        if ($request->has('fecha_fin')) {
            $request->merge(['fechaFin' => $request->fecha_fin]);
        }
        if ($request->has('formato')) {
            $request->merge(['fileType' => $request->formato === 'excel' ? 'xlsx' : 'pdf']);
        }

        $tipoReporte = $request->tipo_reporte;

        // Auto-resolver selected_ids para cuentas por cobrar
        if ($tipoReporte === 'cxc' && !$request->has('selected_ids')) {
            $cxcService = app(\App\Services\CuentasCobrarService::class);
            $filtros = [
                'id_cliente' => $request->id_client,
                'id_subcliente' => $request->id_subcliente,
                'id_proveedor' => $request->id_proveedor,
            ];
            $items = $cxcService->getCuentasPorCobrar($filtros);
            $ids = collect($items)->pluck('id')->toArray();
            $request->merge(['selected_ids' => $ids]);
        }

        // Auto-resolver selected_ids para cuentas por pagar
        if ($tipoReporte === 'cxp' && !$request->has('selected_ids')) {
            $idProveedor = $request->id_proveedor ?? $request->proveedor_id;
            
            $query = \App\Models\Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
                ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
                ->where('cotizaciones.id_empresa', $user->id_empresa)
                ->where('asignaciones.tipo_contrato', 'Subcontratado')
                ->where(function ($q) {
                    $q->where('cotizaciones.estatus', 'Aprobada')
                      ->orWhere('cotizaciones.estatus', 'Finalizado');
                })
                ->where('cotizaciones.prove_restante', '>', 0)
                ->whereRaw('
                    asignaciones.total_proveedor - (
                        SELECT COALESCE(SUM(cpc.monto),0)
                        FROM cobros_pagos_cotizaciones cpc
                        JOIN cobros_pagos cp ON cp.id = cpc.cobro_pago_id
                        WHERE cpc.cotizacion_id = cotizaciones.id
                        AND cp.tipo = "cxp"
                    ) > 0
                ');

            if (!empty($idProveedor)) {
                $query->where('asignaciones.id_proveedor', $idProveedor);
            }

            $ids = $query->pluck('asignaciones.id')->toArray();
            $request->merge(['selected_ids' => $ids]);
        }

        // Auto-resolver selected_ids para documentos
        if ($tipoReporte === 'documentos' && !$request->has('selected_ids')) {
            $query = \App\Models\Cotizaciones::where('id_empresa', $user->id_empresa)
                ->where('estatus', 'Aprobada');
            if ($request->filled('id_client')) {
                $query->where('id_cliente', $request->id_client);
            }
            if ($request->filled('id_subcliente')) {
                $query->where('id_subcliente', $request->id_subcliente);
            }
            $ids = $query->pluck('id')->toArray();
            $request->merge(['selected_ids' => $ids]);
        }

        // Auto-resolver selected_ids para liquidados cxc
        if ($tipoReporte === 'liquidados_cxc' && !$request->has('selected_ids')) {
            $query = \App\Models\Cotizaciones::where('id_empresa', $user->id_empresa)
                ->where(function ($q) {
                    $q->where('estatus', 'Aprobada')
                      ->orWhere('estatus', 'Finalizado');
                })
                ->where('restante', '<=', 0);
            if ($request->filled('fechaInicio') && $request->filled('fechaFin')) {
                $query->whereBetween('fecha_pago', [$request->fechaInicio, $request->fechaFin]);
            }
            if ($request->filled('id_client')) {
                $query->where('id_cliente', $request->id_client);
            }
            if ($request->filled('id_subcliente')) {
                $query->where('id_subcliente', $request->id_subcliente);
            }
            $ids = $query->pluck('id')->toArray();
            $request->merge(['selected_ids' => $ids]);
        }

        // Auto-resolver selected_ids para liquidados cxp
        if ($tipoReporte === 'liquidados_cxp' && !$request->has('selected_ids')) {
            $idProveedor = $request->id_proveedor ?? $request->proveedor_id;
            $query = \App\Models\Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
                ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
                ->where('cotizaciones.id_empresa', $user->id_empresa)
                ->whereNull('asignaciones.id_camion')
                ->where(function ($q) {
                    $q->where('cotizaciones.estatus', 'Aprobada')
                      ->orWhere('cotizaciones.estatus', 'Finalizado');
                })
                ->where('cotizaciones.prove_restante', 0);
            if ($request->filled('fechaInicio') && $request->filled('fechaFin')) {
                $query->whereBetween('cotizaciones.fecha_pago_proveedor', [$request->fechaInicio, $request->fechaFin]);
            }
            if (!empty($idProveedor)) {
                $query->where('asignaciones.id_proveedor', $idProveedor);
            }
            $ids = $query->pluck('asignaciones.id')->toArray();
            $request->merge(['selected_ids' => $ids]);
        }

        // Auto-resolver cotizacion_ids para viajes
        if ($tipoReporte === 'viajes' && !$request->has('cotizacion_ids')) {
            $query = \App\Models\Asignaciones::where('id_empresa', $user->id_empresa);
            if ($request->filled('fechaInicio') && $request->filled('fechaFin')) {
                $query->whereBetween('fecha_inicio', [$request->fechaInicio, $request->fechaFin]);
            }
            $ids = $query->pluck('id')->toArray();
            $request->merge(['cotizacion_ids' => $ids]);
        }

        // Evitar que selected_ids / cotizacion_ids vacío cause redirecciones back() que rompen CORS (Failed to fetch)
        if ($tipoReporte === 'validacion_documentos') {
            $query = \App\Models\Cotizaciones::where('cotizaciones.id_empresa', $user->id_empresa)
                ->where('cotizaciones.estatus', '!=', 'Cancelada')
                ->where('cotizaciones.jerarquia', "Principal")
                ->join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
                ->leftJoin('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor');

            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $query->whereBetween('asignaciones.fecha_inicio', [
                    $request->fecha_inicio,
                    $request->fecha_fin
                ]);
            }
            if ($query->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron contenedores para generar el reporte.'
                ], 400);
            }
        }

        if (in_array($tipoReporte, ['cxc', 'cxp', 'documentos', 'liquidados_cxc', 'liquidados_cxp']) && empty($request->input('selected_ids'))) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron registros para generar este reporte con los filtros seleccionados.'
            ], 400);
        }
        if ($tipoReporte === 'viajes' && empty($request->input('cotizacion_ids'))) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron registros para generar este reporte con los filtros seleccionados.'
            ], 400);
        }

        $formato = $request->formato ?? 'pdf';
        $enviarCorreo = $request->enviar_correo ?? false;
        $correoDestinatario = $request->correo_destinatario;

        $reporteriaController = app(\App\Http\Controllers\ReporteriaController::class);
        $response = null;

        switch ($tipoReporte) {
            case 'cxc':
                if ($formato === 'excel') {
                    $response = $reporteriaController->exportExcel($request);
                } else {
                    $response = $reporteriaController->export($request);
                }
                break;
            case 'cxp':
                $response = $reporteriaController->export_cxp($request);
                break;
            case 'viajes':
                $response = $reporteriaController->export_viajes($request);
                break;
            case 'utilidad':
                $response = $reporteriaController->export_utilidad($request);
                break;
            case 'documentos':
                $response = $reporteriaController->export_documentos($request);
                break;
            case 'validacion_documentos':
                $response = $reporteriaController->pdf_validacion_documentos_multi($request);
                break;
            case 'liquidados_cxc':
                $response = $reporteriaController->export_liquidados_cxc($request);
                break;
            case 'liquidados_cxp':
                $response = $reporteriaController->export_liquidados_cxp($request);
                break;
            case 'rendimiento':
                $tipo = ($formato === 'excel' || $formato === 'xlsx') ? 'excel' : 'pdf';
                $response = $reporteriaController->exportarunidadesconsumo(
                    $request, 
                    $tipo, 
                    app(\App\Services\ConsumoUnidadesService::class)
                );
                break;
            case 'gastos_pagar':
                $response = $reporteriaController->exportGastosPorPagar($request);
                break;
            default:
                return $this->apiResponse(false, 'Tipo de reporte no soportado.', [], 400);
        }

        if (!$response) {
            return $this->apiResponse(false, 'No se pudo generar el reporte.', [], 500);
        }

        if ($enviarCorreo && !empty($correoDestinatario)) {
            try {
                $filePath = null;
                $fileName = 'reporte.' . ($formato === 'excel' ? 'xlsx' : 'pdf');

                if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
                    $filePath = $response->getFile()->getPathname();
                } else {
                    $content = $response->getContent();
                    $tempPath = tempnam(sys_get_temp_dir(), 'rep_');
                    file_put_contents($tempPath, $content);
                    $filePath = $tempPath;
                }

                if ($filePath && file_exists($filePath)) {
                    \Mail::raw("Hola, adjunto encontrarás el reporte solicitado desde la aplicación SGT.", function ($message) use ($correoDestinatario, $filePath, $fileName, $tipoReporte) {
                        $message->to($correoDestinatario)
                                ->subject("Reporte SGT: " . strtoupper($tipoReporte))
                                ->attach($filePath, ['as' => $fileName]);
                    });

                    return $this->apiResponse(true, 'El reporte ha sido enviado con éxito a ' . $correoDestinatario);
                }
            } catch (\Exception $e) {
                \Log::error("Error al enviar reporte por correo: " . $e->getMessage());
                return $this->apiResponse(false, 'Error al enviar por correo: ' . $e->getMessage(), [], 500);
            }
        }

        return $response;
    }

    private function authorizeTokenQuery(Request $request)
    {
        $token = $request->query('token') ?? $request->token;
        if (!$token) {
            return false;
        }
        $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        if (!$tokenModel || !$tokenModel->tokenable) {
            return false;
        }
        auth()->login($tokenModel->tokenable);
        return true;
    }

    public function descargarReporteBancoMobile(Request $request, $id)
    {
        if (!$this->authorizeTokenQuery($request)) {
            return response()->json(['success' => false, 'mensaje' => 'No autorizado.'], 401);
        }
        return $this->exportarReporteBancoMobile($request, $id);
    }

    public function descargarReporteMobile(Request $request)
    {
        if (!$this->authorizeTokenQuery($request)) {
            return response()->json(['success' => false, 'mensaje' => 'No autorizado.'], 401);
        }
        return $this->generarReporteMobile($request);
    }
}
