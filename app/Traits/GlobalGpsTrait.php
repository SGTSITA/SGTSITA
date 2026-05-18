<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Dto\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait GlobalGpsTrait
{
    public static function generateSignature($secretKey, $timestamp)
    {
        $step1 = md5($secretKey);                  // md5(secret_key)
        $step2 = $step1 . $timestamp;              // md5(secret_key) + time
        $signature = md5($step2);                  // md5(md5(secret_key) + time)
        return $signature;
    }
    public static function getManyDeviceRealTimeLocations(array $imeis, $apikey, $idUs): array
{
    $key   = $apikey ?: config('services.globalGps.appkey');
    $apiid = $idUs ?: config('services.globalGps.appid');

    $resultados = [];
    $pendientes = [];

    foreach ($imeis as $imei) {
        $imei = trim($imei);

        if (!$imei) {
            continue;
        }

        $cacheKey = 'gps:globalgps:location:' . md5($apiid . '|' . $key . '|' . $imei);

        if (Cache::has($cacheKey)) {
            $resultados[$imei] = Cache::get($cacheKey);
        } else {
            $pendientes[$imei] = $cacheKey;
        }
    }

    if (empty($pendientes)) {
        return $resultados;
    }

    $accessToken = self::getAccessToken($key, $apiid);

    if (!$accessToken) {
        foreach ($pendientes as $imei => $cacheKey) {
            $resultados[$imei] = new ApiResponse(
                success: false,
                data: null,
                message: 'No se pudo obtener token Global GPS',
                status: 401
            );
        }

        return $resultados;
    }

    $endpoint = config('services.globalGps.url_base') . '/api/device/location';

   $responses = Http::pool(function ($pool) use ($pendientes, $endpoint, $accessToken) {
    $requests = [];

    foreach ($pendientes as $imei => $cacheKey) {
        $requests[$imei] = $pool
            ->as($imei)
            ->withHeaders([
                'accessToken' => $accessToken,
                'Accept' => 'application/json',
            ])
            ->connectTimeout(10)
            ->timeout(15)
            ->get($endpoint, [
                'imei' => $imei,
            ]);
    }

    return $requests;
});

    foreach ($pendientes as $imei => $cacheKey) {
        $response = $responses[$imei] ?? null;

        if (!$response || $response->failed()) {
            $apiResponse = new ApiResponse(
                success: false,
                data: $response?->json(),
                message: 'Error al consultar ubicación Global GPS',
                status: $response?->status() ?? 500
            );
        } else {
            $apiResponse = new ApiResponse(
                success: true,
                data: $response->json(),
                message: 'Consulta exitosa',
                status: $response->status()
            );



            Cache::put($cacheKey, $apiResponse, now()->addSeconds(30));
        }

        if (!$response || $response->failed()) {
    Log::error('GLOBAL GPS POOL LOCATION ERROR', [
        'imei' => $imei,
        'has_response' => !is_null($response),
        'status' => $response?->status(),
        'body' => $response?->body(),
    ]);
}

        $resultados[$imei] = $apiResponse;
    }

    return $resultados;
}
public static function getAccessToken($apikey, $idUs, bool $forceRefresh = false)
{
    $key   = $apikey ?: config('services.globalGps.appkey');
    $apiid = $idUs ?: config('services.globalGps.appid');

    $cacheKey = 'gps:globalgps:token:' . md5($apiid . '|' . $key);

    try {
        if ($forceRefresh) {
            Cache::forget($cacheKey);

            return self::fetchAccessToken($key, $apiid);
        }

        return Cache::remember($cacheKey, now()->addMinutes(115), function () use ($key, $apiid) {
            return self::fetchAccessToken($key, $apiid);
        });

    } catch (\Throwable $e) {
        Cache::forget($cacheKey);

        Log::error('GLOBAL GPS AUTH ERROR', [
            'message' => $e->getMessage(),
            'appid'   => $apiid,
        ]);

        throw $e;
    }
}
   private static function fetchAccessToken($key, $apiid)
{
    Log::warning('GLOBAL GPS AUTH REAL API HIT', [
        'appid' => $apiid,
    ]);

    $endpoint = config('services.globalGps.url_base') . '/api/auth';

    $timestamp = time();
    $signature = self::generateSignature($key, $timestamp);

    $response = Http::asJson()
        ->acceptJson()
        ->connectTimeout(10)
        ->timeout(20)
        ->retry(1, 300)
        ->post($endpoint, [
            'appid'     => $apiid,
            'time'      => $timestamp,
            'signature' => $signature,
        ]);

    if ($response->successful() && $response->json('accessToken')) {
        return $response->json('accessToken');
    }

    Log::error('GLOBAL GPS AUTH ERROR', [
        'status' => $response->status(),
        'body'   => $response->body(),
        'appid'  => $apiid,
    ]);

    throw new \Exception('No se pudo obtener el token Global GPS');
}
public static function getDeviceRealTimeLocation($imei, $apikey, $idUs)
{
    $key   = $apikey ?: config('services.globalGps.appkey');
    $apiid = $idUs ?: config('services.globalGps.appid');

    $cacheKey = 'gps:globalgps:location:' . md5($apiid . '|' . $key . '|' . $imei);
    $lockKey = 'lock:' . $cacheKey;

    Log::info('GLOBAL GPS LOCATION CACHE', [
        'imei' => $imei,
        'exists' => Cache::has($cacheKey),
    ]);

    if (Cache::has($cacheKey)) {
        return Cache::get($cacheKey);
    }

    return Cache::lock($lockKey, 10)->block(5, function () use ($cacheKey, $imei, $apikey, $idUs) {

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        Log::warning('GLOBAL GPS LOCATION REAL API HIT', [
            'imei' => $imei,
        ]);

        $response = self::fetchDeviceRealTimeLocation($imei, $apikey, $idUs);

        Cache::put($cacheKey, $response, now()->addSeconds(30));

        return $response;
    });
}
 private static function fetchDeviceRealTimeLocation(
    $imei,
    $apikey,
    $idUs
)
{
    $endpoint = config('services.globalGps.url_base') . '/api/device/location';

    try {

        $accessToken = self::getAccessToken($apikey, $idUs);

        $response = Http::withHeaders([
                'accessToken' => $accessToken,
            ])
            ->connectTimeout(10)
            ->timeout(20)
            ->retry(1, 300)
            ->get($endpoint, [
                'imei' => $imei,
            ]);

        if ($response->failed()) {

            Log::error('GLOBAL GPS LOCATION ERROR', [
                'endpoint' => $endpoint,
                'imei' => $imei,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return new ApiResponse(
                success: false,
                data: $response->json(),
                message: 'Error al consultar ubicación Global GPS',
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

        Log::error('GLOBAL GPS HTTP EXCEPTION', [
            'endpoint' => $endpoint,
            'imei' => $imei,
            'message' => $e->getMessage(),
        ]);

        return new ApiResponse(
            success: false,
            data: null,
            message: 'Excepción HTTP: ' . $e->getMessage(),
            status: 500
        );
    }
}
}
