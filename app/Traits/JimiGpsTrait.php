<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Models\Empresas;
use App\Models\ServicioGps;

trait JimiGpsTrait
{
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

                $response = Http::asForm()->post(config('services.JimiGps.url_base'), $params);
                $data = $response->json();

                Log::info('Respuesta completa del token JIMI:', $data);

                if (isset($data['result']['accessToken'])) {
                    return $data['result']['accessToken'];
                }

                throw new \Exception('No se pudo obtener el token.');
            });
        } catch (\Exception $e) {
            Log::error('Error al obtener token GPS JIMI: ' . $e->getMessage());
            Cache::forget($cacheKey);
            return false;
        }
    }

    public static function getAuthenticationCredentials($rfc)
    {
        //Obtener las credenciales previamente guardadas, correspondiente a una empresa
        $empresa = Empresas::where('rfc', $rfc)->first();
        if (is_null($empresa)) {
            return ["success" => false, "mensaje" => "No existe empresa", "account" => null];
        }

        $data = ServicioGps::where('id_empresa', $empresa->id)->where('id_gps_company', 2)->first();
        if (is_null($data)) {
            return ["success" => false, "mensaje" => "No existe configuración para TrackSolid PRO", "account" => null];
        }


        $detailAccount = json_decode(Crypt::decryptString($data->account_info));
        $credenciales = [];
        foreach ($detailAccount as $a) {
            $credenciales[$a->field] =  $a->valor;
        }

        return ["success" => true, "mensaje" => "Credenciales correctamente configuradas", "accessAccount" => $credenciales];
    }


    public static function callGpsApi($method, $accessAccount, array $additionalParams = [])
    {
        $timestamp = gmdate('Y-m-d H:i:s');
        $empresaId = auth()->user()->id_empresa;
        $accessToken = self::getGpsAccessToken($empresaId, $accessAccount);

        if (!$accessToken) {
            return ['error' => 'No se pudo obtener access_token'];
        }
        /*'app_key'       => ,
        'method'        => $method,
        'user_id'       => $accessAccount['account'],
        'user_pwd_md5'  => $accessAccount['password'],
        'timestamp'     => $timestamp,
        'expires_in'    => 7200,
        'sign_method'   => 'md5',*/

        $params = array_merge([
            'method'       => $method,
            'access_token' => $accessToken,
            'app_key'      => $accessAccount['appkey'],
            'timestamp'    => $timestamp,
            'format'        => 'json',
            'v'            => '1.0',
            'sign_method'  => 'md5',

        ], $additionalParams);

        $params['sign'] = self::generateGpsSign($params, $accessAccount['appsecret']);

        $response = Http::asForm()->post(config('services.JimiGps.url_base'), $params);
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
