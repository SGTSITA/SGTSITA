<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

trait JimiGpsTrait
{
    protected function getGpsAppKey()
    {
        return config('services.JimiGps.appKey');
    }

    protected function getGpsAppSecret()
    {
        return config('services.JimiGps.appSecret');
    }

    protected function getGpsBaseUrl()
    {
        return config('services.JimiGps.url_base');
    }

    public static function getGpsAccessToken($empresaId, $accessAccount)
    {

    $cacheKey = 'api_jimi_token_' . $empresaId;

    try {
        return Cache::remember($cacheKey, 115 * 60, function () use ($accessAccount) {
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

            $params['sign'] = self::generateGpsSign($params, $accessAccount['appsecret']);

            $response = Http::asForm()->post('https://us-open.tracksolidpro.com/route/rest', $params);
            $data = $response->json();

            \Log::info('Respuesta completa del token JIMI:', $data);
            \Log::info('Params:', $params);

            if (isset($data['result']['accessToken'])) {
                return $data['result']['accessToken'];
            }

            throw new \Exception('No se pudo obtener el token.');
        });
    } catch (\Exception $e) {
        \Log::error('Error al obtener token GPS JIMI: ' . $e->getMessage());
        Cache::forget($cacheKey);
        return false;
    }
}


    public function callGpsApi($method, array $additionalParams = [])
    {
        $timestamp = gmdate('Y-m-d H:i:s');
        $accessToken = $this->getGpsAccessToken();

        if (!$accessToken) {
            return ['error' => 'No se pudo obtener access_token'];
        }

        $params = array_merge([
            'app_key'      => $this->getGpsAppKey(),
            'method'       => $method,
            'timestamp'    => $timestamp,
            'sign_method'  => 'md5',
            'access_token' => $accessToken,
        ], $additionalParams);

        $params['sign'] = $this->generateGpsSign($params);

        $response = Http::asForm()->post($this->getGpsBaseUrl(), $params);
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
    \Log::debug('Cadena para firmar GPS:', ['cadena' => $signString]);
        return strtoupper(md5($signString));
    }
}
