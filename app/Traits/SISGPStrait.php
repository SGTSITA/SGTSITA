<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Models\Empresas;
use App\Models\ServicioGps;
use App\Dto\ApiResponse;
use Illuminate\Support\Facades\Log;
use SoapClient;
use Exception;

trait SISGPSTrait
{
    protected static function sisClient()
    {
        $endpoint = config('services.GPS_SIS_URL.urlbasesoap');

        return new \SoapClient($endpoint, [
            'trace'      => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 20,
        ]);
    }

    public static function sisValidarCredenciales($user, $key)
    {
        try {
            $client = self::sisClient();

            $client->getIdsListXml([
                'username' => $user,
                'key'      => $key,
            ]);

            if ($client) {
                $fakeToken = hash('sha256', $user.'|'.$key.'|SIS');
                Cache::put('sis_gps_token_'.$user, $fakeToken, now()->addMinutes(28));
                return new ApiResponse(
                    success: true,
                    data: [
                              'token' => $fakeToken,
                          ],
                    message: 'Credenciales SIS válidas',
                    status: 200
                );
            }

        } catch (\SoapFault $e) {
            Log::warning('SIS GPS credenciales inválidas', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }



    //metodo para obtener la ultima posicion del dispositivo en soap segun documentacion SIS
    public static function sisGetLastPosition(string $user, string $key, string $deviceId): ApiResponse
    {
        try {
            $client = self::sisClient($user, $key);

            $response = $client->__soapCall(
                'getLastPosition',
                [[
                    'username' => $user,
                    'key'      => $key,
                    'deviceid' => $deviceId
                ]]
            );

            return new ApiResponse(
                success: true,
                data: [
                    'device_id' => $deviceId,
                    'raw' => $response
                ],
                message: 'Posición obtenida correctamente',
                status: 200
            );

        } catch (\SoapFault $e) {

            logger()->error('SIS SOAP getLastPosition error', [
                'device_id' => $deviceId,
                'faultcode' => $e->faultcode ?? null,
                'message'   => $e->getMessage()
            ]);

            return new ApiResponse(
                success: false,
                data: null,
                message: 'Error SOAP al consultar la posición',
                status: 502
            );

        } catch (\Exception $e) {

            logger()->error('SIS getLastPosition error', [
                'device_id' => $deviceId,
                'message'   => $e->getMessage()
            ]);

            return new ApiResponse(
                success: false,
                data: null,
                message: 'Error interno al obtener la posición',
                status: 500
            );
        }
    }

}
