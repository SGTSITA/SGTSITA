<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Models\Empresas;
use App\Models\ServicioGps;
use App\Dto\ApiResponse;
use Illuminate\Support\Facades\Log;

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
    public static function validateOwner($legoApiKey)
    {
        return ($legoApiKey['lego_api_key'] == config('services.LegoGps.appKey')) ? true : false;
    }

public static function getLocation(
    $accessAccount,
    bool $forceRefresh = false
) {

    $cacheKey = 'gps:lego:locations:' . md5(
        $accessAccount['lego_api_key'] ?? 'default'
    );

    try {

        if ($forceRefresh) {
            Cache::forget($cacheKey);

            return self::fetchLocation($accessAccount);
        }

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $lockKey = 'lock:' . $cacheKey;

        return Cache::lock($lockKey, 10)->block(5, function () use (
            $cacheKey,
            $accessAccount
        ) {

            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            Log::warning('LEGO GPS LOCATION REAL API HIT');

            $response = self::fetchLocation($accessAccount);

            if ($response->success) {
                Cache::put(
                    $cacheKey,
                    $response,
                    now()->addSeconds(30)
                );
            }

            return $response;
        });

    } catch (\Throwable $e) {

        Log::error('LEGO GPS CACHE WRAPPER ERROR', [
            'message' => $e->getMessage(),
        ]);

        return self::fetchLocation($accessAccount);
    }
}

 private static function fetchLocation($accessAccount)
{
    try {
        if (($accessAccount['lego_api_key'] ?? null) != config('services.LegoGps.appKey')) {
            return new ApiResponse(
                success: false,
                data: null,
                message: 'No está autorizado para utilizar este servicio',
                status: 401
            );
        }

        $endpoint = config('services.LegoGps.url_base');

        $response = Http::connectTimeout(5)
            ->timeout(10)
            ->retry(1, 300)
            ->get($endpoint);

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

    } catch (\Throwable $e) {
        Log::error('LEGO GPS LOCATION ERROR', [
            'message' => $e->getMessage(),
        ]);

        return new ApiResponse(
            success: false,
            data: null,
            message: 'Error LegoGps::fetchLocation => ' . $e->getMessage(),
            status: 500
        );
    }
}
}
