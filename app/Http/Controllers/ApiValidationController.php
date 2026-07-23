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

        $debugCot = \DB::table('cotizaciones')->where('id', 1192)->first();
        $debugAsig = \DB::table('asignaciones')->where('id_contenedor', 1192)->first();
        \Log::info("DEBUG COT 1192: " . json_encode($debugCot));
        \Log::info("DEBUG ASIG: " . json_encode($debugAsig));

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
}
