<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Models\Empresas;
use App\Models\ServicioGps;
use App\Dto\ApiResponse;
use Illuminate\Support\Facades\Log;

trait GpsTrackerMXTrait
{
   public static function getGpsAccessToken(array $accessAccount, bool $forceRefresh = false)
{
    $cacheKey = 'gps:trackermx:token:' . md5(json_encode([
        'email' => $accessAccount['email'] ?? null,
        'username' => $accessAccount['username'] ?? null,
        'user' => $accessAccount['user'] ?? null,
        'password' => $accessAccount['password'] ?? null,
    ]));

    if ($forceRefresh) {
        Cache::forget($cacheKey);
        return self::fetchGpsAccessToken($accessAccount);
    }

    return Cache::remember($cacheKey, now()->addMinutes(115), function () use ($accessAccount) {
        return self::fetchGpsAccessToken($accessAccount);
    });
}

private static function fetchGpsAccessToken(array $accessAccount)
{
    $endpoint = config('services.GpsTrackerMX.url_base') . '/api/session';

    try {
        $response = Http::asForm()
            ->connectTimeout(10)
            ->timeout(20)
            ->retry(1, 300)
            ->post($endpoint, $accessAccount);

        $cookies = $response->cookies();
        $jsessionid = $cookies->getCookieByName('JSESSIONID')?->getValue();

        if ($response->status() == 200 && $jsessionid) {
            return $jsessionid;
        }

        Log::error('GPS TRACKER MX TOKEN ERROR', [
            'status' => $response->status(),
            'body' => $response->body(),
            'has_jsessionid' => !empty($jsessionid),
        ]);

        throw new \Exception('No se pudo obtener el token GPS Tracker MX.');

    } catch (\Throwable $e) {
        Log::error('GPS TRACKER MX TOKEN EXCEPTION', [
            'message' => $e->getMessage(),
        ]);

        return false;
    }
}

   public static function getMutiDevicePosition(array $accessAccount, bool $forceRefresh = false)
{
    $basePath = config('services.GpsTrackerMX.url_base');
    $endpoint = $basePath . '/api/positions';

    $locationCacheKey = 'gps:trackermx:positions:' . md5(json_encode([
        'email' => $accessAccount['email'] ?? null,
        'username' => $accessAccount['username'] ?? null,
        'user' => $accessAccount['user'] ?? null,
    ]));

    if (!$forceRefresh && Cache::has($locationCacheKey)) {
        return Cache::get($locationCacheKey);
    }

    $jsessionid = self::getGpsAccessToken($accessAccount, $forceRefresh);

    if (!$jsessionid) {
        return new ApiResponse(
            success: false,
            data: [],
            message: 'Credenciales de acceso incorrectas. GpsTrackerMX',
            status: 401
        );
    }

    try {
        $response = Http::withHeaders([
                'Cookie' => "JSESSIONID=$jsessionid",
            ])
            ->connectTimeout(10)
            ->timeout(20)
            ->retry(1, 300)
            ->get($endpoint);

        if ($response->failed()) {
            Cache::forget($locationCacheKey);

            Log::error('API request failed GpsTrackerMX', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return new ApiResponse(
                success: false,
                data: $response->json(),
                message: 'Error al consultar GpsTrackerMX',
                status: $response->status()
            );
        }

        $apiResponse = new ApiResponse(
            success: true,
            data: $response->json(),
            message: 'Consulta exitosa',
            status: $response->status()
        );

        if (!$forceRefresh) {
            Cache::put($locationCacheKey, $apiResponse, now()->addSeconds(30));
        }

        return $apiResponse;

    } catch (\Throwable $e) {
        Log::error('GPS TRACKER MX POSITIONS EXCEPTION', [
            'endpoint' => $endpoint,
            'message' => $e->getMessage(),
        ]);

        return new ApiResponse(
            success: false,
            data: [],
            message: 'Error GpsTrackerMX::getMutiDevicePosition => ' . $e->getMessage(),
            status: 500
        );
    }
}

}
