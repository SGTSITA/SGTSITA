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

trait BeyondGPSTrait
{
    public static function getLocation(
    $username,
    $password,
    $endpointuser = null,
    bool $forceRefresh = false
) {
    $endpoint = $endpointuser ?: config('services.BeyondGpsCustomized.url_base');

    $cacheKey = 'gps:beyond:locations:' . md5(
        ($username ?? '') . '|' .
        ($password ?? '') . '|' .
        ($endpoint ?? '')
    );

    if ($forceRefresh) {
        Cache::forget($cacheKey);
        return self::fetchLocation($username, $password, $endpoint);
    }

    if (Cache::has($cacheKey)) {
        return Cache::get($cacheKey);
    }

    $lockKey = 'lock:' . $cacheKey;

    return Cache::lock($lockKey, 10)->block(5, function () use (
        $cacheKey,
        $username,
        $password,
        $endpoint
    ) {
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        Log::warning('BEYOND GPS LOCATION REAL API HIT');

        $response = self::fetchLocation($username, $password, $endpoint);

        if ($response->success) {
            Cache::put($cacheKey, $response, now()->addSeconds(30));
        }

        return $response;
    });
}

   private static function fetchLocation($username, $password, $endpoint)
{
    try {
        $response = Http::connectTimeout(10)
            ->timeout(20)
            ->retry(1, 300)
            ->post($endpoint, [
                'User' => $username,
                'Password' => $password,
            ]);

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

    } catch (\Throwable $e) {
        Log::error('BEYOND GPS LOCATION ERROR', [
            'endpoint' => $endpoint,
            'message' => $e->getMessage(),
        ]);

        return new ApiResponse(
            success: false,
            data: null,
            message: 'Error BeyondGps::fetchLocation => ' . $e->getMessage(),
            status: 500
        );
    }
}
}
