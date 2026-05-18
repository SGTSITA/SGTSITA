<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Dto\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

trait SkyAngelGpsTrait
{


public static function getAccessToken(
    $username,
    $password,
    bool $forceRefresh = false
) {
    $cacheKey = 'gps:skyangel:token:' . md5($username);

    try {

        if ($forceRefresh) {
            Cache::forget($cacheKey);

            return self::fetchAccessToken(
                $username,
                $password
            );
        }

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(28),
            function () use ($username, $password) {

                return self::fetchAccessToken(
                    $username,
                    $password
                );
            }
        );

    } catch (\Throwable $e) {

        Cache::forget($cacheKey);

        Log::error('SKYANGEL TOKEN ERROR', [
            'user' => $username,
            'message' => $e->getMessage(),
        ]);

        return false;
    }
}
  private static function fetchAccessToken(
    $username,
    $password
) {

    Log::warning('SKYANGEL TOKEN REAL API HIT', [
        'user' => $username,
    ]);

    $endpoint = config('services.SkyAngelGps.url_base') . '/token';

    $response = Http::connectTimeout(10)
        ->timeout(20)
        ->retry(1, 300)
        ->post($endpoint, [
            'username' => $username,
            'password' => $password,
        ]);

    if (
        $response->successful() &&
        isset($response->json()['token'])
    ) {
        return $response->json()['token'];
    }

    Log::error('SKYANGEL TOKEN ERROR', [
        'status' => $response->status(),
        'body' => $response->body(),
    ]);

    throw new \Exception(
        'No se pudo obtener el token Sky Angel.'
    );
}

   public static function getLocation($accessToken)
{
    $cacheKey = 'gps:skyangel:locations:' . md5($accessToken);

    Log::info('SKYANGEL LOCATION CACHE', [
        'exists' => Cache::has($cacheKey),
    ]);

    if (Cache::has($cacheKey)) {
        return Cache::get($cacheKey);
    }

    $lockKey = 'lock:' . $cacheKey;

    return Cache::lock($lockKey, 10)->block(5, function () use ($cacheKey, $accessToken) {

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        Log::warning('SKYANGEL LOCATION REAL API HIT');

        $response = self::fetchLocation($accessToken);

        Cache::put($cacheKey, $response, now()->addSeconds(30));

        return $response;
    });
}

private static function fetchLocation($accessToken)
{
    $endpoint = config('services.SkyAngelGps.url_base') . '/unidades';

    try {
        $response = Http::withHeaders([
                'Authorization' => $accessToken,
            ])
            ->connectTimeout(10)
            ->timeout(20)
            ->retry(1, 300)
            ->get($endpoint);

        if ($response->failed()) {
            Log::error('API request failed SkyAngel', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return new ApiResponse(
                success: false,
                data: $response->json(),
                message: 'Error al consultar SkyAngel',
                status: $response->status()
            );
        }

        Log::info('SKYANGEL LOCATION API RESPONSE', [
            'endpoint' => $endpoint,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return new ApiResponse(
            success: true,
            data: $response->json(),
            message: 'Consulta exitosa',
            status: $response->status()
        );

    } catch (\Throwable $e) {
        Log::error('SKYANGEL LOCATION EXCEPTION', [
            'endpoint' => $endpoint,
            'message' => $e->getMessage(),
        ]);

        return new ApiResponse(
            success: false,
            data: null,
            message: 'Error SkyAngel::fetchLocation => ' . $e->getMessage(),
            status: 500
        );
    }
}
}
