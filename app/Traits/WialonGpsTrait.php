<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Models\Empresas;
use App\Models\ServicioGps;
use App\Dto\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

trait WialonGpsTrait
{
    /**
     * Fn validateOwner
     * Proposito: No existe capa de seguridad adicional, por lo que se hace una simulacion
     */
    public static function validateOwner($appKey)
    {
        return  true ;
    }


    public static function getloginLocation($token, $SID = null)
{
    $endpoint = 'https://hst-api.wialon.com/wialon/ajax.html';

    $sid = null;

    try {


        $login = Http::asForm()
            ->withOptions([
                'verify' => false,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                ],
            ])
            ->timeout(30)
            ->connectTimeout(10)
            ->post($endpoint, [
                'svc' => 'token/login',
                'params' => json_encode([
                    'token' => $token
                ]),
            ]);

        Log::info('Wialon login response', [
            'status' => $login->status(),
            'body' => $login->body(),
        ]);

        if ($login->failed()) {

            return new ApiResponse(
                success: false,
                data: $login->json(),
                message: 'Error HTTP al iniciar sesión en Wialon',
                status: $login->status()
            );
        }

        $loginData = $login->json();

        if (!isset($loginData['eid'])) {

            return new ApiResponse(
                success: false,
                data: $loginData,
                message: 'Wialon no devolvió SID',
                status: 401
            );
        }

        $sid = $loginData['eid'];


        $search = Http::asForm()
            ->withOptions([
                'verify' => false,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                ],
            ])
            ->timeout(60)
            ->connectTimeout(10)
            ->post($endpoint, [
                'svc' => 'core/search_items',
                'sid' => $sid,
                'params' => json_encode([
                    'spec' => [
                        'itemsType' => 'avl_unit',
                        'propName' => 'sys_name',
                        'propValueMask' => '*',
                        'sortType' => 'sys_name',
                    ],
                    'force' => 1,
                    'flags' => 1033,
                    'from' => 0,
                    'to' => 0,
                ]),
            ]);

        Log::info('Wialon search response', [
            'status' => $search->status(),
            'body' => $search->body(),
        ]);

        if ($search->failed()) {

            return new ApiResponse(
                success: false,
                data: $search->json(),
                message: 'Error HTTP al consultar dispositivos',
                status: $search->status()
            );
        }

        $searchData = $search->json();


        if (isset($searchData['error'])) {

            return new ApiResponse(
                success: false,
                data: $searchData,
                message: 'Error Wialon código: ' . $searchData['error'],
                status: 400
            );
        }

      $itemsNormalizados = collect($searchData['items'] ?? [])
    ->map(function ($item) {

        $placas = null;

        foreach (($item['pflds'] ?? []) as $field) {

            if (
                ($field['n'] ?? null) === 'registration_plate'
            ) {

                $placas = $field['v'] ?? null;
                break;
            }
        }

        return [
            'unidad' => $item['nm'] ?? null,
            'placas' => $placas,
            'imei' => $item['uid'] ?? null,

            'latitud' => $item['pos']['y'] ?? null,
            'longitud' => $item['pos']['x'] ?? null,

            'altitud' => $item['pos']['z'] ?? null,
            'velocidad' => $item['pos']['s'] ?? null,
            'rumbo' => $item['pos']['c'] ?? null,
            'timestamp' => $item['pos']['t'] ?? null,

            // RAW original por si luego ocupas algo
            'raw' => $item,
        ];
    })
    ->values()
    ->toArray();

$searchData['items_original'] = $searchData['items'] ?? [];

$searchData['items'] = $itemsNormalizados;

return new ApiResponse(
    success: true,
    data: $searchData,
    message: 'Consulta exitosa',
    status: 200
);

    } catch (RequestException $e) {

        Log::error('HTTP exception Wialon', [
            'message' => $e->getMessage(),
        ]);

        return new ApiResponse(
            success: false,
            data: null,
            message: 'Excepción HTTP => ' . $e->getMessage(),
            status: 500
        );

    } catch (\Throwable $e) {

        Log::critical('Unexpected error Wialon', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return new ApiResponse(
            success: false,
            data: null,
            message: 'Error inesperado => ' . $e->getMessage(),
            status: 500
        );

    } finally {


        if ($sid) {

            try {

                Http::asForm()
                    ->withOptions([
                        'verify' => false,
                    ])
                    ->post($endpoint, [
                        'svc' => 'core/logout',
                        'sid' => $sid,
                        'params' => json_encode([]),
                    ]);

            } catch (\Throwable $e) {

                Log::warning('Error logout Wialon', [
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}

    public static function getLocation($token, $SID)
    {
        try {

            $endpoint = config('services.WialonGpsCustomized.url_base');

           /*   $response =  Http::withHeaders([
                'User-Agent' => 'PostmanRuntime/7.37.0',
                'Accept' => '',
            ])->withOptions([
                'allow_redirects' => true,
                'version' => 1.1,
                'sid' => $SID ,//'520e37eac52b4dd1e338fccec6d7bdea',
                'verify' => false, // solo para descartar problemas de SSL
            ])->get($endpoint, [
                'token' => $token,
            ]);
 */
$response = Http::withHeaders([
        'User-Agent' => 'PostmanRuntime/7.37.0',
        'Cache-Control' => 'no-cache',
        'Pragma' => 'no-cache',
    ])
    ->timeout(30)
    ->connectTimeout(10)
    ->withOptions([
        'verify' => false,
        'curl' => [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ],
    ])
    ->get($endpoint, [
        'token' => $token,

        '_t' => now()->timestamp, // evita cache
    ]);

Log::info('URL EFECTIVA', [
    'url' => (string) $response->effectiveUri(),
]);
            // Puedes validar la respuesta aquí si tu API devuelve un código de error dentro del JSON
            if ($response->failed()) {
                Log::error('API request failed Wialon Gps', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return new ApiResponse(
                    success: false,
                    data: $response->json(),
                    message: 'Error al consultar Wialon GPS',
                    status: $response->status()
                );

            }

            Log::info($response->json());

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
                message: 'Excepción HTTP WialonGps::getLocation => ' .$e->getMessage(),
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
                message: 'Error inesperado WialonGps::getLocation => ' .$e->getMessage(),
                status: 500
            );

        }
    }
}
