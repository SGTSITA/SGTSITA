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
public static function getManyByCredentialGroups(array $gruposGlobal): array
{
    $endpointAuth = config('services.globalGps.url_base') . '/api/auth';
    $endpointLocation = config('services.globalGps.url_base') . '/api/device/location';

    $tokens = [];
    $authPendientes = [];
    $resultados = [];

    foreach ($gruposGlobal as $index => $grupo) {
        $credenciales = $grupo['credenciales'];

        $key = $credenciales['appkey'] ?? config('services.globalGps.appkey');
        $apiid = $credenciales['account'] ?? config('services.globalGps.appid');

        $tokenCacheKey = 'gps:globalgps:token:' . md5($apiid . '|' . $key);

        $cachedToken = self::getStoredToken($tokenCacheKey);
        if ($cachedToken) {
            $tokens[$index] = $cachedToken;
        } else {
            $authPendientes[$index] = [
                'key' => $key,
                'apiid' => $apiid,
                'cacheKey' => $tokenCacheKey,
            ];
        }
    }

    if (!empty($authPendientes)) {
        foreach ($authPendientes as $index => $auth) {
            $lockKey = 'gps:globalgps:lock:' . md5($auth['apiid'] . '|' . $auth['key']);

            // Si otra petición ya está logueando este grupo, esperamos hasta 5 segundos (10 intentos de 0.5s)
            $attempts = 0;
            while (\Cache::has($lockKey) && $attempts < 10) {
                usleep(500000); // Esperar 0.5 segundos
                $attempts++;
            }

            // Validar si la otra petición ya obtuvo y guardó el token en la caché/archivo
            $cachedToken = self::getStoredToken($auth['cacheKey']);
            if ($cachedToken) {
                $tokens[$index] = $cachedToken;
                continue; // Avanzar al siguiente sin iniciar sesión otra vez
            }

            // Adquirir bloqueo temporal para este grupo por 15 segundos
            \Cache::put($lockKey, true, 15);

            try {
                $timestamp = time();
                $signature = self::generateSignature($auth['key'], $timestamp);

                $response = Http::withOptions([
                        'curl' => [
                            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
                        ]
                    ])
                    ->asJson()
                    ->acceptJson()
                    ->connectTimeout(10)
                    ->timeout(20)
                    ->post($endpointAuth, [
                        'appid'     => $auth['apiid'],
                        'time'      => $timestamp,
                        'signature' => $signature,
                    ]);

                if ($response->successful() && $response->json('accessToken')) {
                    $token = $response->json('accessToken');
                    self::storeToken($auth['cacheKey'], $token);
                    $tokens[$index] = $token;

                    Log::info('GLOBAL GPS AUTH ok', [
                        'grupo' => $index,
                        'appid' => $auth['apiid'],
                        'status' => $response->status(),
                        'token_length' => is_string($token) ? strlen($token) : null,
                    ]);
                } else {
                    Log::error('GLOBAL GPS AUTH ERROR', [
                        'grupo' => $index,
                        'appid' => $auth['apiid'],
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    $tokens[$index] = null;
                }
            } catch (\Exception $e) {
                Log::error('GLOBAL GPS AUTH EXCEPTION', [
                    'grupo' => $index,
                    'appid' => $auth['apiid'],
                    'message' => $e->getMessage(),
                ]);
                $tokens[$index] = null;
            } finally {
                // Liberar el bloqueo
                \Cache::forget($lockKey);
            }

            // Pequeña pausa para no saturar al servidor y evitar que retorne 503 o caiga en timeout
            usleep(250000);
        }
    }

    $locationPendientes = [];

    foreach ($gruposGlobal as $index => $grupo) {
        $token = $tokens[$index] ?? null;
        $credenciales = $grupo['credenciales'];

        $key = $credenciales['appkey'] ?? config('services.globalGps.appkey');
        $apiid = $credenciales['account'] ?? config('services.globalGps.appid');

        foreach ($grupo['items'] as $item) {
            $imei = trim($item['imei'] ?? '');

            if (!$imei) {
                continue;
            }

            $locationCacheKey = 'gps:globalgps:location:' . md5($apiid . '|' . $key . '|' . $imei);

            if (Cache::has($locationCacheKey)) {
                $resultados[$index][$imei] = Cache::get($locationCacheKey);
                continue;
            }

            if (!$token) {
                $resultados[$index][$imei] = new ApiResponse(
                    success: false,
                    data: null,
                    message: 'No se pudo obtener token Global GPS',
                    status: 401
                );
                continue;
            }

            $locationPendientes[$index . '|' . $imei] = [
                'grupo' => $index,
                'imei' => $imei,
                'token' => $token,
                'cacheKey' => $locationCacheKey,
            ];
        }
    }

    if (!empty($locationPendientes)) {
        $locationResponses = Http::pool(function ($pool) use ($locationPendientes, $endpointLocation) {
            $requests = [];

            foreach ($locationPendientes as $requestKey => $info) {
                $requests[$requestKey] = $pool
                    ->as($requestKey)
                    ->withOptions([
                        'curl' => [
                            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
                        ]
                    ])
                    ->withHeaders([
                        'accessToken' => $info['token'],
                        'Accept' => 'application/json',
                    ])
                    ->connectTimeout(3)
                    ->timeout(20)
                    ->get($endpointLocation, [
                        'imei' => $info['imei'],
                    ]);
            }

            return $requests;
        });

foreach ($locationPendientes as $requestKey => $info) {
    $response = $locationResponses[$requestKey] ?? null;

    if (!$response instanceof \Illuminate\Http\Client\Response) {
        Log::error('GLOBAL GPS LOCATION POOL EXCEPTION', [
            'grupo' => $info['grupo'],
            'imei' => $info['imei'],
            'message' => $response?->getMessage(),
        ]);

        $resultados[$info['grupo']][$info['imei']] = new ApiResponse(
            success: false,
            data: null,
            message: 'Excepción al consultar ubicación Global GPS',
            status: 500
        );

        continue;
    }

    $json = $response->json();

    $esExitosa = $response->successful()
        && isset($json['lat'], $json['lng'])
        && (string)($json['code'] ?? '0') === '0';

    if (!$esExitosa) {
        Log::error('GLOBAL GPS LOCATION POOL ERROR', [
            'grupo' => $info['grupo'],
            'imei' => $info['imei'],
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        $resultados[$info['grupo']][$info['imei']] = new ApiResponse(
            success: false,
            data: $json,
            message: 'Error al consultar ubicación Global GPS',
            status: $response->status()
        );

        continue;
    }

    $apiResponse = new ApiResponse(
        success: true,
        data: $json,
        message: 'Consulta exitosa',
        status: $response->status()
    );

    Cache::put($info['cacheKey'], $apiResponse, now()->addSeconds(30));

    $resultados[$info['grupo']][$info['imei']] = $apiResponse;
}

    }

    return $resultados;
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
            ->timeout(20)
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
            $filePath = storage_path('app/gps_tokens.json');
            if (file_exists($filePath)) {
                $data = json_decode(file_get_contents($filePath), true);
                if (is_array($data)) {
                    unset($data[$cacheKey]);
                    file_put_contents($filePath, json_encode($data));
                }
            }
        } else {
            $cachedToken = self::getStoredToken($cacheKey);
            if ($cachedToken) {
                return $cachedToken;
            }
        }

        // Semáforo (Lock/Mutex) para evitar tormenta de peticiones concurrentes
        $lockKey = 'gps:globalgps:lock:' . md5($apiid . '|' . $key);
        $attempts = 0;
        while (Cache::has($lockKey) && $attempts < 10) {
            usleep(500000); // Esperar 0.5 segundos
            $attempts++;
        }

        // Validar si otra petición concurrente ya guardó el token
        $cachedToken = self::getStoredToken($cacheKey);
        if ($cachedToken) {
            return $cachedToken;
        }

        Cache::put($lockKey, true, 15);

        try {
            $token = self::fetchAccessToken($key, $apiid);
            self::storeToken($cacheKey, $token);
            return $token;
        } finally {
            Cache::forget($lockKey);
        }

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

    $response = Http::withOptions([
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
            ]
        ])
        ->asJson()
        ->acceptJson()
        ->connectTimeout(10)
        ->timeout(25)
        ->retry(1, 300)
        ->post($endpoint, [
            'appid'     => $apiid,
            'time'      => $timestamp,
            'signature' => $signature,
        ]);

    if ($response->successful() && $response->json('accessToken')) {
        return $response->json('accessToken');
    }

    $errorBody = $response->json();
    $errorMessage = 'No se pudo obtener el token Global GPS';
    if (isset($errorBody['code']) && $errorBody['code'] == 10004) {
        $errorMessage = 'Credenciales de GPS inválidas (AppID o AppKey incorrectos)';
    }

    Log::error('GLOBAL GPS AUTH ERROR', [
        'status' => $response->status(),
        'body'   => $response->body(),
        'appid'  => $apiid,
    ]);

    throw new \Exception($errorMessage);
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
            ->connectTimeout(3)
            ->timeout(6)
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

private static function getStoredToken(string $cacheKey): ?string
{
    if (\Cache::has($cacheKey)) {
        return \Cache::get($cacheKey);
    }

    $filePath = storage_path('app/gps_tokens.json');
    if (file_exists($filePath)) {
        $data = json_decode(file_get_contents($filePath), true);
        if (is_array($data) && isset($data[$cacheKey])) {
            $item = $data[$cacheKey];
            if (isset($item['token'], $item['expires_at']) && time() < $item['expires_at']) {
                \Cache::put($cacheKey, $item['token'], now()->addMinutes(115));
                return $item['token'];
            }
        }
    }
    return null;
}

private static function storeToken(string $cacheKey, string $token): void
{
    \Cache::put($cacheKey, $token, now()->addMinutes(115));

    $filePath = storage_path('app/gps_tokens.json');
    $data = [];
    if (file_exists($filePath)) {
        $data = json_decode(file_get_contents($filePath), true);
        if (!is_array($data)) {
            $data = [];
        }
    }

    $data[$cacheKey] = [
        'token' => $token,
        'expires_at' => time() + (115 * 60)
    ];

    file_put_contents($filePath, json_encode($data));
    @chmod($filePath, 0666);
}
}
