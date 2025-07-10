<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

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

    public function getGpsAccessToken()
    {
        $method = 'jimi.oauth.token.get';
        $timestamp = gmdate('Y-m-d H:i:s');

        $params = [
            'app_key'     => $this->getGpsAppKey(),
            'method'      => $method,
            'timestamp'   => $timestamp,
            'sign_method' => 'md5',
        ];

        $params['sign'] = $this->generateGpsSign($params);

        $response = Http::asForm()->post($this->getGpsBaseUrl(), $params);
        $data = $response->json();
  \Log::info('Respuesta completa del token JIMI:', $data);

\Log::info('Params:',  $params );
  
        return $data['data']['access_token'] ?? null;
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

    private function generateGpsSign(array $params)
    {
        ksort($params);
        $signString = $this->getGpsAppSecret();
        foreach ($params as $key => $value) {
            $signString .= $key . $value;
        }
        $signString .= $this->getGpsAppSecret();
    \Log::debug('Cadena para firmar GPS:', ['cadena' => $signString]);
        return strtoupper(md5($signString));
    }
}
