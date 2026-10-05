<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Dto\ApiResponse;
use Carbon\Carbon;
use Exception;
use Throwable;

/**
 * Trait NaanixGPSTrait
 *
 * Implementa la integración con el servicio Web REST/JSON MClientesExternosJSPY
 * de Naanix / SIS Technologies (Versión 1.0).
 * Reemplaza el servicio SOAP legacy conservando compatibilidad de contratos.
 */
trait NaanixGPSTrait
{
    /**
     * Obtiene la URL base del servicio Naanix.
     */
    protected static function getNaanixBaseUrl(): string
    {
        return rtrim(
            config('services.GPS_SIS_URL.url_base', 'https://3.90.229.208:8058/MClientesExternosJSPY'),
            '/'
        );
    }

    /**
     * Obtiene el IdCliente configurado por defecto.
     */
    protected static function getNaanixDefaultClientId(): int
    {
        return (int) config('services.GPS_SIS_URL.client_id', 937184269);
    }

    /**
     * Obtiene el timeout de conexión en segundos.
     */
    protected static function getNaanixTimeout(): int
    {
        return (int) config('services.GPS_SIS_URL.timeout', 10);
    }

    /**
     * Determina si debe verificar el certificado SSL.
     */
    protected static function getNaanixVerifySsl(): bool
    {
        return (bool) config('services.GPS_SIS_URL.verify_ssl', false);
    }

    /**
     * Calcula la llave matemática de autenticación dinámica requerida por Naanix.
     * Fórmula oficial: DateTime.UtcNow.Hour + DateTime.UtcNow.Day + IdCliente
     *
     * @param int|string|null $idCliente
     * @return int
     */
    public static function sisCalcularLlave($idCliente = null): int
    {
        $clienteId = !empty($idCliente) ? (int) $idCliente : self::getNaanixDefaultClientId();
        $utcNow = Carbon::now('UTC');

        return (int) $utcNow->hour + (int) $utcNow->day + $clienteId;
    }

    /**
     * Obtiene un token de autenticación para el cliente Naanix.
     * El token tiene una vigencia de 1 hora. Se cachea por 50 minutos.
     *
     * @param int|string|null $idCliente
     * @param bool $forceRefresh
     * @return ApiResponse
     */
    public static function sisObtenerToken($idCliente = null, bool $forceRefresh = false): ApiResponse
    {
        $clienteId = !empty($idCliente) ? (int) $idCliente : self::getNaanixDefaultClientId();
        $cacheKey = 'gps:naanix:token:' . $clienteId;

        if (!$forceRefresh && Cache::has($cacheKey)) {
            $cachedToken = Cache::get($cacheKey);
            if (!empty($cachedToken)) {
                return new ApiResponse(
                    success: true,
                    data: ['token' => $cachedToken],
                    message: 'Token obtenido de caché',
                    status: 200
                );
            }
        }

        $url = self::getNaanixBaseUrl() . '/General/ObtenerToken';
        $llave = self::sisCalcularLlave($clienteId);

        try {
            $httpClient = Http::timeout(self::getNaanixTimeout())
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ]);

            if (!self::getNaanixVerifySsl()) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->post($url, [
                'Cliente' => $clienteId,
                'Llave'   => $llave,
            ]);

            if (!$response->successful()) {
                Log::error('Naanix ObtenerToken error HTTP', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return new ApiResponse(
                    success: false,
                    data: null,
                    message: 'Error HTTP ' . $response->status() . ' al contactar Naanix',
                    status: $response->status()
                );
            }

            $data = $response->json();
            $resultado = $data['Resultado'] ?? null;
            $token = $data['Datos']['Token'] ?? null;

            if ($resultado === 1 && !empty($token)) {
                // Cachear por 50 minutos (la vigencia es de 1 hora)
                Cache::put($cacheKey, $token, now()->addMinutes(50));

                return new ApiResponse(
                    success: true,
                    data: [
                        'token' => $token,
                        'raw'   => $data,
                    ],
                    message: $data['Mensaje'] ?? 'Token obtenido exitosamente',
                    status: 200
                );
            }

            $errorMsg = self::traducirCodigoError($resultado, $data['Mensaje'] ?? 'Error desconocido');

            Log::warning('Naanix ObtenerToken rechazado por API', [
                'cliente'   => $clienteId,
                'resultado' => $resultado,
                'mensaje'   => $errorMsg,
            ]);

            return new ApiResponse(
                success: false,
                data: $data,
                message: $errorMsg,
                status: 401
            );

        } catch (Throwable $e) {
            Log::error('Excepción al conectar con Naanix ObtenerToken', [
                'cliente' => $clienteId,
                'error'   => $e->getMessage(),
            ]);

            return new ApiResponse(
                success: false,
                data: null,
                message: 'No fue posible conectar con el servicio Naanix: ' . $e->getMessage(),
                status: 500
            );
        }
    }

    /**
     * Valida credenciales con el servicio Naanix.
     * Compatible con llamadas existentes de GpsCompanyController y GpsController.
     *
     * @param string|int|null $user (Puede ser el IdCliente o cuenta)
     * @param string|null $key (Opcional, en Naanix la llave se autogenera)
     * @return ApiResponse
     */
    public static function sisValidarCredenciales($user = null, $key = null): ApiResponse
    {
        $clienteId = !empty($user) && is_numeric($user)
            ? (int) $user
            : self::getNaanixDefaultClientId();

        return self::sisObtenerToken($clienteId, true);
    }

    /**
     * Obtiene el catálogo de unidades registradas en Naanix para el cliente.
     * Permite indexar por DispositivoAsignado (IMEI), IdUnidad y Placas.
     *
     * @param int|string|null $idCliente
     * @param string|null $modificacion
     * @param bool $forceRefresh
     * @return ApiResponse
     */
    public static function sisObtenerUnidades($idCliente = null, ?string $modificacion = null, bool $forceRefresh = false): ApiResponse
    {
        $clienteId = !empty($idCliente) ? (int) $idCliente : self::getNaanixDefaultClientId();
        $cacheKey = 'gps:naanix:unidades_map:' . $clienteId;

        if (!$forceRefresh && empty($modificacion) && Cache::has($cacheKey)) {
            $cachedMap = Cache::get($cacheKey);
            return new ApiResponse(
                success: true,
                data: $cachedMap,
                message: 'Catálogo de unidades obtenido de caché',
                status: 200
            );
        }

        $tokenResp = self::sisObtenerToken($clienteId);
        if (!$tokenResp->success) {
            return $tokenResp;
        }

        $token = $tokenResp->data['token'];
        $llave = self::sisCalcularLlave($clienteId);
        $url = self::getNaanixBaseUrl() . '/General/ObtenerUnidades';

        try {
            $httpClient = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ]);

            if (!self::getNaanixVerifySsl()) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->post($url, [
                'Token'        => $token,
                'Cliente'      => $clienteId,
                'Llave'        => $llave,
                'Modificacion' => $modificacion,
            ]);

            $data = $response->json();
            $resultado = $data['Resultado'] ?? null;

            // Si el token expiró en el servidor remoto, refrescar una vez
            if ($resultado === -1000) {
                Cache::forget('gps:naanix:token:' . $clienteId);
                $retryTokenResp = self::sisObtenerToken($clienteId, true);
                if ($retryTokenResp->success) {
                    $token = $retryTokenResp->data['token'];
                    $response = $httpClient->post($url, [
                        'Token'        => $token,
                        'Cliente'      => $clienteId,
                        'Llave'        => self::sisCalcularLlave($clienteId),
                        'Modificacion' => $modificacion,
                    ]);
                    $data = $response->json();
                    $resultado = $data['Resultado'] ?? null;
                }
            }

            if ($resultado === 1) {
                $unidades = $data['Datos'] ?? [];
                
                // Mapeos indexados para búsquedas inmediatas O(1)
                $mapPorImei = [];
                $mapPorIdUnidad = [];
                $mapPorPlacas = [];

                foreach ($unidades as $u) {
                    $dispositivo = trim((string)($u['DispositivoAsignado'] ?? ''));
                    $idUnidad = (int)($u['IdUnidad'] ?? 0);
                    $placas = strtoupper(trim((string)($u['Placas'] ?? '')));

                    if ($dispositivo !== '') {
                        $mapPorImei[$dispositivo] = $u;
                    }
                    if ($idUnidad > 0) {
                        $mapPorIdUnidad[$idUnidad] = $u;
                    }
                    if ($placas !== '') {
                        $mapPorPlacas[$placas] = $u;
                    }
                }

                $resultadoMapeo = [
                    'unidades'          => $unidades,
                    'map_por_imei'      => $mapPorImei,
                    'map_por_id_unidad' => $mapPorIdUnidad,
                    'map_por_placas'    => $mapPorPlacas,
                ];

                // Cachear catálogo por 6 horas si fue carga total
                if (empty($modificacion)) {
                    Cache::put($cacheKey, $resultadoMapeo, now()->addHours(6));
                }

                return new ApiResponse(
                    success: true,
                    data: $resultadoMapeo,
                    message: 'Unidades obtenidas correctamente',
                    status: 200
                );
            }

            return new ApiResponse(
                success: false,
                data: $data,
                message: self::traducirCodigoError($resultado, $data['Mensaje'] ?? 'Error al obtener unidades'),
                status: 400
            );

        } catch (Throwable $e) {
            Log::error('Excepción en Naanix ObtenerUnidades', [
                'error' => $e->getMessage()
            ]);

            return new ApiResponse(
                success: false,
                data: null,
                message: 'Error al consultar unidades Naanix: ' . $e->getMessage(),
                status: 500
            );
        }
    }

    /**
     * Consulta las posiciones actuales generales de todas las unidades.
     * Si se envía $idPosicion > 0, devuelve únicamente las posiciones posteriores a dicho ID.
     *
     * @param int|string|null $idCliente
     * @param int $idPosicion
     * @return ApiResponse
     */
    public static function sisGetPosicionesActuales($idCliente = null, int $idPosicion = 0): ApiResponse
    {
        $clienteId = !empty($idCliente) ? (int) $idCliente : self::getNaanixDefaultClientId();
        $tokenResp = self::sisObtenerToken($clienteId);

        if (!$tokenResp->success) {
            return $tokenResp;
        }

        $token = $tokenResp->data['token'];
        $llave = self::sisCalcularLlave($clienteId);
        $url = self::getNaanixBaseUrl() . '/General/ObtenerPosicionActualGeneral';

        try {
            $httpClient = Http::timeout(self::getNaanixTimeout())
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ]);

            if (!self::getNaanixVerifySsl()) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->post($url, [
                'Token'      => $token,
                'Cliente'    => $clienteId,
                'Llave'      => $llave,
                'IdPosicion' => $idPosicion,
            ]);

            $data = $response->json();
            $resultado = $data['Resultado'] ?? null;

            // Si el token caducó en el servidor remoto (-1000), refrescar y reintentar 1 vez
            if ($resultado === -1000) {
                Cache::forget('gps:naanix:token:' . $clienteId);
                $retryTokenResp = self::sisObtenerToken($clienteId, true);
                if ($retryTokenResp->success) {
                    $token = $retryTokenResp->data['token'];
                    $response = $httpClient->post($url, [
                        'Token'      => $token,
                        'Cliente'    => $clienteId,
                        'Llave'      => self::sisCalcularLlave($clienteId),
                        'IdPosicion' => $idPosicion,
                    ]);
                    $data = $response->json();
                    $resultado = $data['Resultado'] ?? null;
                }
            }

            if ($resultado === 1) {
                return new ApiResponse(
                    success: true,
                    data: $data['Datos'] ?? ['Posiciones' => [], 'IdPosicionMaxima' => 0],
                    message: $data['Mensaje'] ?? 'Posiciones obtenidas correctamente',
                    status: 200
                );
            }

            return new ApiResponse(
                success: false,
                data: $data,
                message: self::traducirCodigoError($resultado, $data['Mensaje'] ?? 'Error al consultar posiciones'),
                status: 400
            );

        } catch (Throwable $e) {
            Log::error('Excepción en Naanix ObtenerPosicionActualGeneral', [
                'error' => $e->getMessage()
            ]);

            return new ApiResponse(
                success: false,
                data: null,
                message: 'Error al consultar posiciones en Naanix: ' . $e->getMessage(),
                status: 500
            );
        }
    }

    /**
     * Consulta la última posición de un dispositivo específico.
     * Mantiene compatibilidad hacia atrás con el método sisGetLastPosition anterior.
     *
     * @param string|int|null $user (IdCliente)
     * @param string|null $key (No requerido en REST, autocalculado)
     * @param string $deviceId (IMEI o ID de unidad)
     * @return ApiResponse
     */
    public static function sisGetLastPosition($user = null, $key = null, string $deviceId = ''): ApiResponse
    {
        $clienteId = !empty($user) && is_numeric($user)
            ? (int) $user
            : self::getNaanixDefaultClientId();

        $cacheKey = 'gps:naanix:last_position:' . md5($clienteId . '|' . $deviceId);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // 1. Consultar posiciones actuales
            $posicionesResp = self::sisGetPosicionesActuales($clienteId, 0);

            if (!$posicionesResp->success) {
                return $posicionesResp;
            }

            $posiciones = $posicionesResp->data['Posiciones'] ?? [];

            // 2. Resolver mapa de unidades para cruzar DeviceId (IMEI) con IdUnidad
            $unidadesResp = self::sisObtenerUnidades($clienteId);
            $mapUnidadesImei = $unidadesResp->success ? ($unidadesResp->data['map_por_imei'] ?? []) : [];
            $targetIdUnidad = null;

            if (isset($mapUnidadesImei[$deviceId])) {
                $targetIdUnidad = (int) ($mapUnidadesImei[$deviceId]['IdUnidad'] ?? null);
            }

            // 3. Buscar la posición correspondiente
            $posicionEncontrada = null;
            foreach ($posiciones as $pos) {
                $posIdUnidad = (int) ($pos['IdUnidad'] ?? 0);
                if (
                    ($targetIdUnidad !== null && $posIdUnidad === $targetIdUnidad) ||
                    (string) $posIdUnidad === (string) $deviceId
                ) {
                    $posicionEncontrada = $pos;
                    break;
                }
            }

            if ($posicionEncontrada) {
                $response = new ApiResponse(
                    success: true,
                    data: [
                        'device_id' => $deviceId,
                        'posicion'  => $posicionEncontrada,
                        'raw'       => (object) [
                            'Latitude'     => $posicionEncontrada['Latitud'] ?? 0,
                            'Longitude'    => $posicionEncontrada['Longitud'] ?? 0,
                            'Speed'        => $posicionEncontrada['Velocidad'] ?? 0,
                            'ID'           => $deviceId,
                            'UnitType'     => $posicionEncontrada['Empresa'] ?? 'Naanix GPS',
                            'DataCommType' => $posicionEncontrada['Evento'] ?? null,
                            'Date'         => $posicionEncontrada['Fecha'] ?? null,
                            'Location'     => $posicionEncontrada['Ubicacion'] ?? null,
                            'Site'         => $posicionEncontrada['Sitio'] ?? null,
                        ],
                    ],
                    message: 'Posición obtenida correctamente',
                    status: 200
                );

                Cache::put($cacheKey, $response, now()->addSeconds(30));
                return $response;
            }

            // Si no se encuentra posición reportada
            return new ApiResponse(
                success: false,
                data: null,
                message: 'No se encontró posición reciente para la unidad ' . $deviceId,
                status: 404
            );

        } catch (Throwable $e) {
            Log::error('Naanix sisGetLastPosition error', [
                'device_id' => $deviceId,
                'error'     => $e->getMessage(),
            ]);

            return new ApiResponse(
                success: false,
                data: null,
                message: 'Error al consultar posición en Naanix: ' . $e->getMessage(),
                status: 500
            );
        }
    }

    /**
     * Traduce los códigos de error estándar de Naanix / SIS Technologies.
     */
    protected static function traducirCodigoError(?int $codigo, string $defaultMensaje = ''): string
    {
        return match ($codigo) {
            1     => 'Realizado exitosamente.',
            -1000 => 'Error de validación de acceso (Token, Llave o Cliente no válidos).',
            -1001 => 'Error de parámetros en la petición.',
            -1    => 'Error de procesamiento de solicitud en el servidor de rastreo.',
            -2    => 'Error interno del servicio de rastreo.',
            -3    => 'Servicio de rastreo no disponible.',
            default => $defaultMensaje ?: 'Respuesta no exitosa (' . $codigo . ')',
        };
    }

    /**
     * Devuelve la descripción textual según el catálogo de eventos de Naanix.
     */
    public static function traducirEvento(?int $codigoEvento): string
    {
        return match ($codigoEvento) {
            4  => 'En Movimiento',
            2  => 'Detenido',
            35 => 'Ignición ON',
            36 => 'Ignición OFF',
            39 => 'Botón Pánico',
            47 => 'Frenado brusco',
            48 => 'Aceleración Brusca',
            51 => 'Falla de Energía',
            52 => 'Energía Restablecida',
            79 => 'Detección de posible Jammer',
            88 => 'Detección de Jamming de GPS encendido',
            89 => 'Detección de Jamming de GPS apagado',
            90 => 'Detección de Jamming de GPRS encendido',
            91 => 'Detección de Jamming de GPRS apagado',
            96 => 'Inicio de Ciclo Jammer',
            92 => 'Alerta de Bloqueo',
            default => 'Evento ' . ($codigoEvento ?? 'desconocido'),
        };
    }
}
