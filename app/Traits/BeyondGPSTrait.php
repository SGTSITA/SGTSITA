<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Models\Empresas;
use App\Models\ServicioGps;
use App\Dto\ApiResponse;
use Log;

trait BeyondGPSTrait
{
    /**
     * Fn validateOwner
     * Proposito: No existe capa de seguridad adicional, por lo que se hace una simulacion
     */
    public static function validateOwner($appKey){
        return  true ;
    }

    public static function getLocation($username, $password)
    {
        try {

            $endpoint = config('services.BeyondGpsCustomized.url_base');
          
            $response = Http::post($endpoint, [
                'User' => $username,
                'Password' => $password,
            ]);

            // Puedes validar la respuesta aquí si tu API devuelve un código de error dentro del JSON
            if ($response->failed()) {
                Log::error('API request failed Beyond Gps', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return new ApiResponse(
                        success: false,
                        data: $response->json(),
                        message: 'Error al consultar Beyond GPS',
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
                        message: 'Excepción HTTP BeyondGps::getLocation => ' .$e->getMessage(),
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
                        message: 'Error inesperado BeyondGps::getLocation => ' .$e->getMessage(),
                        status: 500
                    );

        }
    }
}