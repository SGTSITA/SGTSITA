<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Models\Empresas;
use App\Models\ServicioGps;
use Illuminate\Support\Facades\Log;

trait JimiGpsTrait
{
    public static function getGpsAccessToken($accessAccount, bool $forceRefresh = false)
{
    $cacheKey = 'gps:jimi:token:' . md5(
        ($accessAccount['appkey'] ?? '') . '|' .
        ($accessAccount['account'] ?? '') . '|' .
        ($accessAccount['appsecret'] ?? '')
    );

    if ($forceRefresh) {
        Cache::forget($cacheKey);
        return self::fetchGpsAccessToken($accessAccount);
    }

    return Cache::remember($cacheKey, now()->addMinutes(115), function () use ($accessAccount) {
        return self::fetchGpsAccessToken($accessAccount);
    });
}

private static function fetchGpsAccessToken($accessAccount)
{
    $method = 'jimi.oauth.token.get';
    $timestamp = gmdate('Y-m-d H:i:s');

    $params = [
        'app_key'       => $accessAccount['appkey'],
        'method'        => $method,
        'user_id'       => $accessAccount['account'],
        'user_pwd_md5'  => $accessAccount['password'],
        'timestamp'     => $timestamp,
        'expires_in'    => 7200,
        'sign_method'   => 'md5',
        'format'        => 'json',
        'v'             => '1.0',
    ];

    $params['sign'] = self::generateGpsSign(
        $params,
        $accessAccount['appsecret']
    );

    $response = Http::asForm()
        ->connectTimeout(5)
        ->timeout(10)
        ->retry(1, 300)
        ->post(config('services.JimiGps.url_base'), $params);

    $data = $response->json();

    if (isset($data['result']['accessToken'])) {
        return $data['result']['accessToken'];
    }

    throw new \Exception('No se pudo obtener el token JIMI.');
}


    public static function callGpsApi($method, $accessAccount, array $additionalParams = [])
{
    $timestamp = gmdate('Y-m-d H:i:s');

    Log::info('JIMI ACCESS ACCOUNT RASTREO', [
    'keys' => array_keys($accessAccount),
    'appkey' => $accessAccount['appkey'] ?? null,
    'account' => $accessAccount['account'] ?? null,
    'password_len' => $accessAccount['password'] ?? '',
    'appsecret_len' => $accessAccount['appsecret'] ?? '',
]);

    $accessToken = self::getGpsAccessToken($accessAccount);

    if (!$accessToken) {
        return ['error' => 'No se pudo obtener access_token'];
    }

    $params = array_merge([
        'method'       => $method,
        'access_token' => $accessToken,
        'app_key'      => $accessAccount['appkey'],
        'timestamp'    => $timestamp,
        'format'       => 'json',
        'v'            => '1.0',
        'sign_method'  => 'md5',
    ], $additionalParams);

    $params['sign'] = self::generateGpsSign(
        $params,
        $accessAccount['appsecret']
    );

    $response = Http::asForm()
        ->connectTimeout(5)
        ->timeout(10)
        ->retry(1, 300)
        ->post(config('services.JimiGps.url_base'), $params);

    return $response->json();
}

    private static function generateGpsSign(array $params, $appSecret)
    {
        ksort($params);
        $signString = $appSecret;
        foreach ($params as $key => $value) {
            $signString .= $key . $value;
        }
        $signString .= $appSecret;
        // Log::debug('Cadena para firmar GPS:', ['cadena' => $signString]);
        return strtoupper(md5($signString));
    }
}
