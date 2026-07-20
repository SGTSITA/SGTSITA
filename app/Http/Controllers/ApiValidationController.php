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

    public function getMonitoreo(Request $request)
    {
        $coordenadasController = app(CoordenadasController::class);
        $response = $coordenadasController->getEquiposGps($request);

        return $this->apiResponse(true, 'Datos de monitoreo obtenidos con éxito.', $response->getData());
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
}
