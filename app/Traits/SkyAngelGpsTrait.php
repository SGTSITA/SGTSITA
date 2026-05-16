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
    public static function getAccessToken($username, $password)
    {
        $tokenFor = 'api_sky_angel_token_for_'.$username;
        return Cache::remember($tokenFor, 28 * 60, function () use ($username, $password) { //ApiToken será recordado por 28 minutos

            $endpoint = config('services.SkyAngelGps.url_base').'/token';

            $response = Http::post($endpoint, [
                'username' => $username,
                'password' => $password,
            ]);



            if ($response->successful() && isset($response->json()['token'])) {
                return $response->json()['token'];
            }

            throw new \Exception('No se pudo obtener el token Sky Angel.');

        });
    }

    public static function getLocation($accessToken)
    {
        try {

            $endpoint = config('services.SkyAngelGps.url_base').'/unidades';

            $headers = [
                'Authorization' => $accessToken,
            ];

            $response = Http::withHeaders($headers)->get($endpoint);

            // Puedes validar la respuesta aquí si tu API devuelve un código de error dentro del JSON
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

            return new ApiResponse(
                success: false,
                data: null,
                message: 'Excepción HTTP SkyAngel::getLocation => ' .$e->getMessage(),
                status: 500
            );

        } catch (\Throwable $e) {
            Log::critical('Unexpected error ', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new ApiResponse(
                success: false,
                data: null,
                message: 'Error inesperado SkyAngel::getLocation => ' .$e->getMessage(),
                status: 500
            );

        }
    }
}
