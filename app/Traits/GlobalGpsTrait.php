<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Dto\ApiResponse;
use Carbon\Carbon;
use Log;

trait GlobalGpsTrait
{
    public static function generateSignature($secretKey, $timestamp) {
        $step1 = md5($secretKey);                  // md5(secret_key)
        $step2 = $step1 . $timestamp;              // md5(secret_key) + time
        $signature = md5($step2);                  // md5(md5(secret_key) + time)
        return $signature;
    }

    public static function getAccessToken()
    {
		
        return Cache::remember('api_bearer_token', 115 * 60, function () { //ApiToken será recordado por 1 hora y 55 minutos
			$endpoint = config('services.globalGps.url_base').'/api/auth';
			
            $timestamp = time();
            $key = config('services.globalGps.appkey');

            $signature = self::generateSignature($key, $timestamp);

            $response = Http::post($endpoint, [
                'appid' => config('services.globalGps.appid'),
                'time' => $timestamp,
                'signature' => $signature,
            ]);
			
			

            if ($response->successful() && isset($response->json()['accessToken'])) {
             return $response->json()['accessToken'];
            }

            throw new \Exception('No se pudo obtener el token.');
			
        });
    }

    public static function getDeviceRealTimeLocation($imei)
    {
        try {

            $endpoint = config('services.globalGps.url_base').'/api/device/location';

            $accessToken = self::getAccessToken();

            $headers = [
                'accessToken' => $accessToken,
            ];

            $response = Http::withHeaders($headers)
                ->get($endpoint,['imei' => $imei]);

            // Puedes validar la respuesta aquí si tu API devuelve un código de error dentro del JSON
            if ($response->failed()) {
                Log::error('API request failed', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

              return  new ApiResponse(
                        success: false,
                        data: $response->json(),
                        message: 'Consulta exitosa',
                        status: $response->status()
                    );

            }

            return new ApiResponse(
                success: true,
                data: $response->json(),
                message: 'Consulta exitosa',
                status: $response->status()
            );

        } catch (RequestException $e) {
            Log::error('HTTP exception', [
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
            ]);

          return  new ApiResponse(
                        success: false,
                        data: null,
                        message: 'Excepción HTTP: ' .$e->getMessage(),
                        status: 500
                    );

        } catch (\Throwable $e) {
            Log::critical('Unexpected error ', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
         return   new ApiResponse(
                        success: false,
                        data: null,
                        message: 'Error inesperado::getDeviceRealTimeLocation => ' .$e->getMessage(),
                        status: 500
                    );

        }
    }
}