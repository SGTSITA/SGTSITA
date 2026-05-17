<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Models\Empresas;
use App\Models\ServicioGps;
use App\Dto\ApiResponse;
use Illuminate\Support\Facades\Log;
use SoapClient;
use Exception;

trait SISGPSTrait
{
    protected static function sisClient()
    {
        $endpoint = config('services.GPS_SIS_URL.urlbasesoap');

        return new \SoapClient($endpoint, [
            'trace'      => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 20,
        ]);
    }

   public static function sisValidarCredenciales($user, $key): ApiResponse
{
    try {
        $client = self::sisClient();

        $response = $client->getIdsListXml([
            'username' => $user,
            'key'      => $key,
        ]);

        if (!$response) {
            return new ApiResponse(
                success: false,
                data: null,
                message: 'Respuesta vacía del servicio SIS',
                status: 401
            );
        }

        return new ApiResponse(
            success: true,
            data: $response,
            message: 'Credenciales SIS válidas',
            status: 200
        );

    } catch (\SoapFault $e) {
        Log::warning('SIS GPS credenciales inválidas', [
            'error' => $e->getMessage()
        ]);

        return new ApiResponse(
            success: false,
            data: null,
            message: 'Error de autenticación con SIS: ' . $e->getMessage(),
            status: 401
        );
    }
}



   public static function sisGetLastPosition(
    string $user,
    string $key,
    string $deviceId
): ApiResponse {
    $cacheKey = 'gps:sis:position:' . md5($user . '|' . $key . '|' . $deviceId);



    if (Cache::has($cacheKey)) {
        return Cache::get($cacheKey);
    }

    $lockKey = 'lock:' . $cacheKey;

    return Cache::lock($lockKey, 10)->block(5, function () use ($cacheKey, $user, $key, $deviceId) {
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = self::fetchSisLastPosition($user, $key, $deviceId);

        if ($response->success) {
            Cache::put($cacheKey, $response, now()->addSeconds(30));
        }

        return $response;
    });
}

private static function fetchSisLastPosition(
    string $user,
    string $key,
    string $deviceId
): ApiResponse {
    try {
        $client = self::sisClient();

        $response = $client->__soapCall(
            'getLastPosition',
            [[
                'username' => $user,
                'key'      => $key,
                'deviceid' => $deviceId,
            ]]
        );

        return new ApiResponse(
            success: true,
            data: [
                'device_id' => $deviceId,
                'raw' => $response,
            ],
            message: 'Posición obtenida correctamente',
            status: 200
        );

    } catch (\SoapFault $e) {
        Log::error('SIS SOAP getLastPosition error', [
            'device_id' => $deviceId,
            'faultcode' => $e->faultcode ?? null,
            'message'   => $e->getMessage(),
        ]);

        return new ApiResponse(
            success: false,
            data: null,
            message: 'Error SOAP al consultar la posición',
            status: 502
        );

    } catch (\Throwable $e) {
        Log::error('SIS getLastPosition error', [
            'device_id' => $deviceId,
            'message'   => $e->getMessage(),
        ]);

        return new ApiResponse(
            success: false,
            data: null,
            message: 'Error interno al obtener la posición',
            status: 500
        );
    }
}
}
