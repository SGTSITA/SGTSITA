<?php

namespace App\Http\Controllers;

use App\Services\ApiValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'email'    => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        if (!$request->has('email') && $request->has('usuario')) {
            $credentials['email'] = $request->usuario;
        }
        $res = $this->apiValidationService->login($credentials);
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
                    Log::error("Error al obtener coordenadas por items: " . $e->getMessage());
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

    public function getContenedoresEmpresas24h(Request $request)
    {
        $res = $this->apiValidationService->getContenedoresEmpresas24h();
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
                    Log::info("Enviando reporte por correo a: " . $correoDestinatario);
                    \Mail::raw("Hola, adjunto encontrarás el reporte solicitado desde la aplicación SGT.", function ($message) use ($correoDestinatario, $filePath, $fileName, $tipoReporte) {
                        $message->to($correoDestinatario)
                                ->subject("Reporte SGT: " . strtoupper($tipoReporte))
                                ->attach($filePath, ['as' => $fileName]);
                    });

                    return $this->apiResponse(true, 'El reporte ha sido enviado con éxito a ' . $correoDestinatario);
                }
            } catch (\Exception $e) {
                Log::error("Error al enviar reporte por correo: " . $e->getMessage());
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

    public function checkAsignacion(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 401);
        }

        // Obtener IDs de operadores vinculados a este usuario
        $operadorIds = DB::table('operador_usuario')
            ->where('user_id', $user->id)
            ->pluck('id_operador');

        if ($operadorIds->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No hay operadores vinculados a este usuario.'], 404);
        }

        // Buscar si tiene algún viaje ya aceptado y activo
        $activo = DB::table('asignaciones')
            ->join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
            ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->whereIn('asignaciones.id_operador', $operadorIds)
            ->where('asignaciones.estatus_viaje', 'Aceptado')
            ->where('cotizaciones.estatus_planeacion', 1)
            ->where('cotizaciones.estatus', 'Aprobada')
            ->select('asignaciones.id')
            ->first();

        $viajeActivoId = $activo ? $activo->id : null;

        // Buscar asignación activa o pendiente de aceptar
        // Filtros estrictos: estatus_planeacion = 1 y estatus = 'Aprobada'
        $asignacion = DB::table('asignaciones')
            ->join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
            ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->whereIn('asignaciones.id_operador', $operadorIds)
            ->where('cotizaciones.estatus_planeacion', 1)
            ->where('cotizaciones.estatus', 'Aprobada')
            ->where(function($q) {
                $q->whereNull('asignaciones.estatus_viaje')
                  ->orWhere('asignaciones.estatus_viaje', 'Pendiente');
            })
            ->select('asignaciones.*')
            ->first();

        if ($asignacion) {
            $contenedor = DB::table('docum_cotizacion')->where('id', $asignacion->id_contenedor)->first();
            $cotizacion = $contenedor ? DB::table('cotizaciones')->where('id', $contenedor->id_cotizacion)->first() : null;

            $empresaId = $cotizacion ? $cotizacion->id_empresa : $asignacion->id_empresa;
            $empresa = $empresaId ? DB::table('empresas')->where('id', $empresaId)->first() : null;

            $camion = DB::table('equipos')->where('id', $asignacion->id_camion)->first();

            return response()->json([
                'success' => true,
                'viaje_activo_id' => $viajeActivoId,
                'data' => [
                    'id_asignacion' => $asignacion->id,
                    'nombre_empresa' => $empresa ? $empresa->nombre : 'Empresa Asignada',
                    'origen_destino' => $cotizacion ? ($cotizacion->origen . ' - ' . $cotizacion->destino) : 'Sin ruta especificada',
                    'num_contenedor' => $contenedor ? $contenedor->num_contenedor : 'Sin Contenedor',
                    'camion'         => $camion ? $camion->id_equipo : 'Sin Unidad Asignada'
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'viaje_activo_id' => $viajeActivoId,
            'message' => 'No hay asignaciones pendientes.'
        ]);
    }

    public function aceptarAsignacion(Request $request)
    {
        $request->validate([
            'id_asignacion' => 'required|integer',
        ]);

        DB::table('asignaciones')
            ->where('id', $request->id_asignacion)
            ->update(['estatus_viaje' => 'Aceptado']);

        $asignacion = DB::table('asignaciones')->where('id', $request->id_asignacion)->first();
        if (!$asignacion) {
            return response()->json(['success' => false, 'message' => 'Asignación no encontrada.'], 404);
        }

        $operador = DB::table('operadores')->where('id', $asignacion->id_operador)->first();
        $camion = DB::table('equipos')->where('id', $asignacion->id_camion)->first();
        $contenedor =  DB::table('docum_cotizacion')->where('id', $asignacion->id_contenedor)->first();

        return response()->json([
            'success' => true,
            'message' => 'Asignación aceptada con éxito.',
            'data' => [
                'id_asignacion' => $asignacion->id,
                'nombre' => $operador ? $operador->nombre : '',
                'unidad' => $camion ? $camion->id_equipo : 'N/A',
                'id_equipo' => $camion ? $camion->id_equipo : 'N/A',
                'num_contenedor' => $contenedor ? $contenedor->num_contenedor : 'N/A',
            ]
        ]);
    }

    public function getHistorial(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 401);
        }

        $operadorIds = DB::table('operador_usuario')
            ->where('user_id', $user->id)
            ->pluck('id_operador');

        if ($operadorIds->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No hay operadores vinculados a este usuario.', 'data' => []]);
        }

        $query = DB::table('bitacora_viajes_operadores')
            ->join('asignaciones', 'bitacora_viajes_operadores.id_asignacion', '=', 'asignaciones.id')
            ->join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
            ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->leftJoin('empresas', 'cotizaciones.id_empresa', '=', 'empresas.id')
            ->whereIn('bitacora_viajes_operadores.id_operador', $operadorIds);

        // Filtro de periodo (default este mes)
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('asignaciones.fecha_inicio', [$request->fecha_inicio . ' 00:00:00', $request->fecha_fin . ' 23:59:59']);
        } else {
            $startOfMonth = \Carbon\Carbon::now()->startOfMonth()->toDateString();
            $endOfMonth = \Carbon\Carbon::now()->endOfMonth()->toDateString();
            $query->whereBetween('asignaciones.fecha_inicio', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59']);
        }

        $historial = $query->select(
                'bitacora_viajes_operadores.*',
                'asignaciones.fecha_inicio',
                'asignaciones.fecha_fin',
                'docum_cotizacion.num_contenedor',
                'docum_cotizacion.id_cotizacion',
                'empresas.nombre as nombre_empresa',
                'cotizaciones.origen',
                'cotizaciones.destino'
            )
            ->orderBy('bitacora_viajes_operadores.created_at', 'desc')
            ->get();

        // Cargar gastos/viáticos asociados a la cotización de cada viaje
        foreach ($historial as $item) {
            $item->gastos = DB::table('viaticos_operadores')
                ->where('id_cotizacion', $item->id_cotizacion)
                ->select('id', 'descripcion_gasto as concepto', 'monto', 'comprobante')
                ->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'Historial obtenido con éxito.',
            'data' => $historial
        ]);
    }

    public function getViajesPendientesLiquidar(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 401);
        }

        $operadorIds = DB::table('operador_usuario')
            ->where('user_id', $user->id)
            ->pluck('id_operador');

        if ($operadorIds->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No hay operadores vinculados a este usuario.', 'data' => []]);
        }

        $viajes = \App\Models\Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
            ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->leftJoin('liquidacion_contenedor', 'docum_cotizacion.id', '=', 'liquidacion_contenedor.id_contenedor')
            ->leftJoin('equipos', 'asignaciones.id_camion', '=', 'equipos.id')
            ->whereIn('asignaciones.id_operador', $operadorIds)
            ->whereNull('liquidacion_contenedor.id_liquidacion')
            ->select(
                'asignaciones.id as id_asignacion',
                'docum_cotizacion.id as id_contenedor',
                'docum_cotizacion.num_contenedor',
                'cotizaciones.id as id_cotizacion',
                'cotizaciones.referencia_full',
                'asignaciones.fecha_inicio',
                'equipos.id_equipo as economico_camion',
                'equipos.placas as placas_camion'
            )
            ->orderBy('asignaciones.created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Viajes pendientes de liquidar obtenidos con éxito.',
            'data' => $viajes
        ]);
    }

    public function registrarGastosViaje(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 401);
        }

        $request->validate([
            'id_asignacion' => 'required|exists:asignaciones,id',
            'has_gastos'    => 'required',
        ]);

        $asignacion = \App\Models\Asignaciones::findOrFail($request->id_asignacion);
        $doc = \App\Models\DocumCotizacion::find($asignacion->id_contenedor);
        $idCotizacion = $doc ? $doc->id_cotizacion : null;

        if (!$idCotizacion) {
            return response()->json(['success' => false, 'message' => 'No se encontró cotización asociada al viaje.'], 404);
        }

        $hasGastos = filter_var($request->has_gastos, FILTER_VALIDATE_BOOLEAN) || in_array(strtolower($request->has_gastos), ['1', 'si']);

        if ($hasGastos && is_array($request->gastos)) {
            foreach ($request->gastos as $gasto) {
                if (empty($gasto['concepto']) || empty($gasto['monto'])) {
                    continue;
                }

                $comprobantePath = null;
                if (!empty($gasto['evidencia_base64'])) {
                    $base64Str = $gasto['evidencia_base64'];
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64Str, $type)) {
                        $base64Str = substr($base64Str, strpos($base64Str, ',') + 1);
                    }
                    $fileName = uniqid() . '_gasto_ticket.jpg';
                    $path = public_path('/uploads/viaticos_operadores/' . $idCotizacion);
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    file_put_contents($path . '/' . $fileName, base64_decode($base64Str));
                    $comprobantePath = 'uploads/viaticos_operadores/' . $idCotizacion . '/' . $fileName;
                }

                // 1. Crear registro en viaticos_operadores
                $viaticoEntidad = \App\Models\ViaticosOperador::create([
                    'id_cotizacion'     => $idCotizacion,
                    'descripcion_gasto' => $gasto['concepto'],
                    'monto'             => $gasto['monto'],
                    'fecha_comprobante' => \Carbon\Carbon::now(),
                    'comprobante'       => $comprobantePath,
                ]);

                // 2. Incrementar restante_pago_operador en asignaciones
                $asignacion->increment('restante_pago_operador', $gasto['monto']);

                // 3. Sincronizar en el ledged/servicio de gastos
                try {
                    $idEmpresa = $asignacion->id_empresa ?? $user->id_empresa;

                    app(\App\Services\GastosService::class)->registrar([
                        'id_empresa' => $idEmpresa,
                        'concepto' => $gasto['concepto'],
                        'monto_total' => $gasto['monto'],
                        'tipo_gasto' => 'operador',
                        'estatus' => 'pagado',
                        'fecha_gasto' => \Carbon\Carbon::now(),
                        'origen_modulo' => 'liquidacion_operador',
                        'origen_legacy' => 'viaticos_operadores',
                        'origen_legacy_id' => $viaticoEntidad->id,
                        'user_id' => $user->id,
                        'vinculos' => array_filter([
                            [
                                'tipo_vinculo' => 'cotizacion',
                                'vinculable_type' => \App\Models\Cotizaciones::class,
                                'vinculable_id' => $idCotizacion,
                            ],
                            [
                                'tipo_vinculo' => 'contenedor',
                                'vinculable_type' => \App\Models\DocumCotizacion::class,
                                'vinculable_id' => $asignacion->id_contenedor,
                            ],
                            [
                                'tipo_vinculo' => 'asignacion',
                                'vinculable_type' => \App\Models\Asignaciones::class,
                                'vinculable_id' => $asignacion->id,
                            ],
                            $asignacion->id_operador ? [
                                'tipo_vinculo' => 'operador',
                                'vinculable_type' => \App\Models\Operador::class,
                                'vinculable_id' => $asignacion->id_operador,
                            ] : null
                        ]),
                        'imputaciones' => [
                            [
                                'fecha_imputacion' => \Carbon\Carbon::now(),
                                'tipo_imputacion' => 'viaje',
                                'imputable_type' => \App\Models\Asignaciones::class,
                                'imputable_id' => $asignacion->id,
                                'monto_imputado' => $gasto['monto'],
                                'origen' => 'directo',
                            ]
                        ]
                    ]);
                } catch (\Exception $ex) {
                    Log::error("Error al registrar gasto en GastosService desde App Operador: " . $ex->getMessage());
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Información de gastos de viaje registrada correctamente.'
        ]);
    }

    public function obtenerGastosViaje($idAsignacion)
    {
        $asignacion = \App\Models\Asignaciones::find($idAsignacion);
        if (!$asignacion) {
            return response()->json(['success' => false, 'message' => 'Asignación no encontrada.', 'data' => []]);
        }
        $doc = \App\Models\DocumCotizacion::find($asignacion->id_contenedor);
        if (!$doc) {
            return response()->json(['success' => false, 'message' => 'Contenedor no encontrado.', 'data' => []]);
        }

        $viaticos = \App\Models\ViaticosOperador::where('id_cotizacion', $doc->id_cotizacion)
            ->select('id', 'descripcion_gasto as concepto', 'monto', 'comprobante')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $viaticos
        ]);
    }

    public function eliminarGastoViaje(Request $request)
    {
        $request->validate([
            'id_gasto' => 'required',
        ]);

        $viatico = \App\Models\ViaticosOperador::find($request->id_gasto);
        if (!$viatico) {
            return response()->json(['success' => false, 'message' => 'El gasto no existe.'], 404);
        }

        $idCotizacion = $viatico->id_cotizacion;
        $monto = $viatico->monto;

        $doc = \App\Models\DocumCotizacion::where('id_cotizacion', $idCotizacion)->first();
        if ($doc) {
            $asignacion = \App\Models\Asignaciones::where('id_contenedor', $doc->id)->first();
            if ($asignacion) {
                // Decrementar
                $asignacion->decrement('restante_pago_operador', $monto);

                // Eliminar el gasto global
                $gastosAsociados = \App\Models\Gasto::whereIn('origen_legacy', ['viaticos_operadores', 'viaticos_operadores_excedente'])
                    ->where('origen_legacy_id', $viatico->id)
                    ->get();
                foreach ($gastosAsociados as $gasto) {
                    \App\Models\GastoPago::where('gasto_id', $gasto->id)->delete();
                    $gasto->forceDelete();
                }
            }
        }

        $viatico->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gasto eliminado con éxito.'
        ]);
    }

    public function getNotificationConfig()
    {
        $config = DB::table('global_configs')->where('key', 'tiempo_notificacion_captura_gastos')->first();

        if ($config) {
            return response()->json([
                'success' => true,
                'key' => $config->key,
                'value' => $config->value
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Configuracion no encontrada'
        ], 404);
    }

    public function guardarAppLogs(Request $request)
    {
        $logs = $request->input('logs', []);
        $device = $request->input('device', 'N/A');
        $usuario = $request->input('usuario', 'Desconocido');

        if (!is_array($logs)) {
            $logs = [$logs];
        }

        $today = \Carbon\Carbon::now()->format('Y-m-d');
        $logDir = storage_path('logs/app_movil');
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . "/app_logs_{$today}.json";

        $existingLogs = [];
        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $existingLogs = json_decode($content, true) ?: [];
        }

        foreach ($logs as $logItem) {
            $entry = [
                'id' => uniqid('log_'),
                'timestamp' => $logItem['timestamp'] ?? \Carbon\Carbon::now()->toIso8601String(),
                'usuario' => $usuario,
                'device' => $device,
                'level' => $logItem['level'] ?? 'INFO',
                'message' => $logItem['message'] ?? '',
                'error' => $logItem['error'] ?? null,
                'server_time' => \Carbon\Carbon::now()->toDateTimeString()
            ];
            $existingLogs[] = $entry;

            $msg = is_array($logItem) ? json_encode($logItem, JSON_UNESCAPED_UNICODE) : (string)$logItem;
            Log::channel('daily')->error("[APP MOBILE LOG] [User: {$usuario}] [Device: {$device}] " . $msg);
        }

        // Mantener hasta 2000 logs por día para no sobrecargar el archivo
        if (count($existingLogs) > 2000) {
            $existingLogs = array_slice($existingLogs, -2000);
        }

        file_put_contents($logFile, json_encode($existingLogs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json([
            'success' => true,
            'mensaje' => 'Logs registrados correctamente en storage/logs/app_movil.'
        ]);
    }
}
