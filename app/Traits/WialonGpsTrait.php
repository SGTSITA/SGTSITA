<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Models\Empresas;
use App\Models\ServicioGps;
use App\Dto\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

trait WialonGpsTrait
{
    /**
     * Fn validateOwner
     * Proposito: No existe capa de seguridad adicional, por lo que se hace una simulacion
     */
    public static function validateOwner($appKey)
    {
        return  true ;
    }

    public static function getLocation($token, $SID)
    {
        try {

            $endpoint = config('services.WialonGpsCustomized.url_base');

            $response =  Http::withHeaders([
                'User-Agent' => 'PostmanRuntime/7.37.0',
                'Accept' => '*/*',
            ])->withOptions([
                'allow_redirects' => true,
                'version' => 1.1,
                'sid' => $SID ,//'520e37eac52b4dd1e338fccec6d7bdea',
                'verify' => false, // solo para descartar problemas de SSL
            ])->get($endpoint, [
                'token' => $token,
            ]);

            // Puedes validar la respuesta aquí si tu API devuelve un código de error dentro del JSON
            if ($response->failed()) {
                Log::error('API request failed Wialon Gps', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return new ApiResponse(
                    success: false,
                    data: $response->json(),
                    message: 'Error al consultar Wialon GPS',
                    status: $response->status()
                );

            }

            return new ApiResponse(
                success: true,
                data: $response->json(),
                message: 'Consulta exitosa',
                status: $response->status()
            );

        } catch (RequestException $e) {
            Log::error('HTTP exception', [
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
            ]);

            return new ApiResponse(
                success: false,
                data: null,
                message: 'Excepción HTTP WialonGps::getLocation => ' .$e->getMessage(),
                status: 500
            );

        } catch (\Throwable $e) {
            Log::critical('Unexpected error ', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new ApiResponse(
                success: false,
                data: null,
                message: 'Error inesperado WialonGps::getLocation => ' .$e->getMessage(),
                status: 500
            );

        }
    }
}
