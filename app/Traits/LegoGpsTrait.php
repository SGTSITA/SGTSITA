<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Models\Empresas;
use App\Models\ServicioGps;
use App\Dto\ApiResponse;
use Log;

/**
 * Lego GPS
 * Este es un servicio "Custom" que desarrollaron para Transportes LEGO.
 * El servicio no tiene metodos de seguridad, pero debemos garantizar el uso exclusivo del servicio API para el transportista mencionado
 */
trait LegoGpsTrait
{
    /**
     * Fn validateOwner
     * Proposito: Agregar "capa" de seguridad con la finalidad que el unico que lo pueda configurar/usar sea Transportes LEGO
     */
    public static function validateOwner($legoApiKey){
        return ($legoApiKey['lego_api_key']== config('services.LegoGps.appKey')) ? true : false;
    }

    public static function getLocation($accessAccount)
    {
        try {
            \Log::debug($accessAccount);
            if ($accessAccount['lego_api_key'] != config('services.LegoGps.appKey')) 
            return new ApiResponse(
                success: false,
                data: null,
                message: 'No está autorizado para utilizar este servicio',
                status: 401
            );

            $endpoint = config('services.LegoGps.url_base');
          
            $response = Http::get($endpoint);

            // Puedes validar la respuesta aquí si tu API devuelve un código de error dentro del JSON
            if ($response->failed()) {
                Log::error('API request failed Lego Gps', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return new ApiResponse(
                        success: false,
                        data: $response->json(),
                        message: 'Error al consultar Lego GPS',
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
                        message: 'Excepción HTTP LegoGps::getLocation => ' .$e->getMessage(),
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
                        message: 'Error inesperado LegoGps::getLocation => ' .$e->getMessage(),
                        status: 500
                    );

        }
    }
}