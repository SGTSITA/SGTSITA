<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Models\Empresas;
use App\Models\ServicioGps;
use App\Dto\ApiResponse;
use Log;

trait GpsTrackerMXTrait
{
    public static function getGpsAccessToken($empresaId, $accessAccount)
    {

        $cacheKey = 'gps_tracker_mx_token_' . $empresaId;
        try {
            return Cache::remember($cacheKey, 115 * 60, function () use ($accessAccount) {
                $endpoint = config('services.GpsTrackerMX.url_base').'/api/session';
                $response = Http::asForm()->post($endpoint, $accessAccount);
                $data = $response->json();

                $cookies = $response->cookies();
                // Acceder a la cookie JSESSIONID
                $jsessionid = $cookies->getCookieByName('JSESSIONID')?->getValue();

                if ($response->status() == 200) {
                    return $jsessionid;
                }

                throw new \Exception('No se pudo obtener el token.');
            });
        } catch (\Exception $e) {
            \Log::error('Error al obtener token GPS Tracker MX: ' . $e->getMessage());
            Cache::forget($cacheKey);
            return false;
        }
    }

    public static function getMutiDevicePosition($accessAccount){
        $basePath = config('services.GpsTrackerMX.url_base');
        $endpoint = $basePath.'/api/positions';

        $empresaId = auth()->user()->id_empresa;
        $jsessionid = self::getGpsAccessToken($empresaId, $accessAccount);

        Log::debug("JSESSIONID: ".$jsessionid);

        if (!$jsessionid) {
            return new ApiResponse(
                success: false,
                data: [],
                message: 'Credenciales de acceso incorrectas. GpsTrackeMX',
                status: 401
            );
            return ['error' => 'No se pudo obtener access_token'];
        }

        $response = Http::withHeaders([
            'Cookie' => "JSESSIONID=$jsessionid"
        ])->get($endpoint);

        if ($response->failed()) {
            Log::error('API request failed GpsTrackeMX', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return new ApiResponse(
                    success: false,
                    data: $response->json(),
                    message: 'Error al consultar GpsTrackeMX',
                    status: $response->status()
                );

        }

        return new ApiResponse(
            success: true,
            data: $response->json(),
            message: 'Consulta exitosa',
            status: $response->status()
        );
    }
}