<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    /**
     * Resuelve una URL de Google Maps (corta o larga) y obtiene coordenadas y dirección.
     *
     * @param string $url
     * @return array|null
     */
    public function resolver($url)
    {
        try {
            $url = trim($url);
            if (empty($url)) {
                return null;
            }

            // 1. Extraer directamente de la URL provista si ya contiene coordenadas / datos
            $datos = $this->extraerDatosDesdeURL($url);
            $lat = $datos['lat'] ?? null;
            $lng = $datos['lng'] ?? null;
            $placeName = $datos['place_name'] ?? null;
            $finalUrl = $url;

            // 2. Si no hay coordenadas (o es enlace corto), seguir redirecciones
            if (!$lat || !$lng) {
                $chain = $this->seguirRedirecciones($url);
                $finalUrl = $chain['final_url'] ?? $url;

                if (!empty($chain['lat']) && !empty($chain['lng'])) {
                    $lat = $chain['lat'];
                    $lng = $chain['lng'];
                }
                if (!empty($chain['place_name'])) {
                    $placeName = $chain['place_name'];
                }
            }

            // 3. Resolver dirección y reforzar búsqueda si falta algo
            $direccion = $placeName;

            if ($lat && $lng) {
                if (empty($direccion)) {
                    $direccion = $this->obtenerDireccion($lat, $lng);
                }
            } elseif (!empty($placeName) && (!$lat || !$lng)) {
                // Si tenemos nombre de lugar pero no coordenadas, geocodificar para obtener coordenadas
                $geo = $this->geocodificarTexto($placeName);
                if ($geo) {
                    $lat = $geo['lat'];
                    $lng = $geo['lng'];
                    $direccion = $geo['direccion'] ?? $placeName;
                }
            }

            if ($lat && $lng) {
                return [
                    'lat' => (float)$lat,
                    'lng' => (float)$lng,
                    'direccion' => $direccion,
                    'place_name' => $placeName,
                    'final_url' => $finalUrl,
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error resolviendo URL Google Maps', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Extrae coordenadas y nombre de lugar desde cualquier formato de URL de Google Maps.
     */
    public function extraerDatosDesdeURL($url)
    {
        $data = [
            'lat' => null,
            'lng' => null,
            'place_name' => null,
        ];

        if (empty($url) || !is_string($url)) {
            return $data;
        }

        // 1. Extraer nombre de lugar de /place/nombre/ o /search/nombre/
        if (preg_match('/\/maps\/(?:place|search)\/([^\/@?]+)/i', $url, $m)) {
            $rawPlace = urldecode(str_replace('+', ' ', $m[1]));
            if (!preg_match('/^-?\d+\.\d+,-?\d+\.\d+$/', trim($rawPlace))) {
                $data['place_name'] = trim($rawPlace);
            }
        }

        // 2. Coordenadas de marcador pin (!3d<lat>!4d<lng> en protobuf de Google) - PRIORIDAD MÁXIMA
        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $m)) {
            $data['lat'] = (float)$m[1];
            $data['lng'] = (float)$m[2];
            return $data;
        }

        // 3. Parámetro q con coordenadas: ?q=lat,lng
        if (preg_match('/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            $data['lat'] = (float)$m[1];
            $data['lng'] = (float)$m[2];
            return $data;
        }

        // 4. Parámetro ll con coordenadas: ?ll=lat,lng
        if (preg_match('/[?&]ll=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            $data['lat'] = (float)$m[1];
            $data['lng'] = (float)$m[2];
            return $data;
        }

        // 5. Coordenadas de cámara/vista: @lat,lng
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            $data['lat'] = (float)$m[1];
            $data['lng'] = (float)$m[2];
            return $data;
        }

        // 6. Path con coordenadas: /lat,lng
        if (preg_match('/\/(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            $data['lat'] = (float)$m[1];
            $data['lng'] = (float)$m[2];
            return $data;
        }

        return $data;
    }

    /**
     * Sigue la cadena de redirecciones HTTP para resolver enlaces cortos como maps.app.goo.gl o goo.gl
     */
    private function seguirRedirecciones($url)
    {
        $currentUrl = $url;
        $maxHops = 6;
        $hops = 0;
        $bestLat = null;
        $bestLng = null;
        $bestPlaceName = null;

        while ($hops < $maxHops) {
            $hops++;

            try {
                $response = Http::withoutRedirecting()
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                        'Accept-Language' => 'es-MX,es;q=0.9,en;q=0.8',
                    ])
                    ->timeout(10)
                    ->get($currentUrl);

                $statusCode = $response->status();
                $location = $response->header('Location');

                // Si hay redirección
                if (in_array($statusCode, [301, 302, 303, 307, 308]) && $location) {
                    if (str_starts_with($location, '/')) {
                        $parsed = parse_url($currentUrl);
                        $scheme = $parsed['scheme'] ?? 'https';
                        $host = $parsed['host'] ?? '';
                        $location = "{$scheme}://{$host}{$location}";
                    }
                    $currentUrl = $location;

                    $locData = $this->extraerDatosDesdeURL($currentUrl);
                    if (!empty($locData['lat']) && !empty($locData['lng'])) {
                        $bestLat = $locData['lat'];
                        $bestLng = $locData['lng'];
                    }
                    if (!empty($locData['place_name'])) {
                        $bestPlaceName = $locData['place_name'];
                    }

                    // Si ya encontramos el pin exacto (!3d), podemos terminar
                    if ($bestLat && $bestLng && preg_match('/!3d/', $currentUrl)) {
                        break;
                    }
                    continue;
                }

                // Si es respuesta 200, inspeccionar HTML por si hay meta refresh, canonical o datos embebidos
                if ($statusCode === 200) {
                    $body = $response->body();

                    // 1. Meta itemprop="url"
                    if (preg_match('/<meta[^>]+content=[\'"]([^\'"]*maps[^\'"]*)[\'"][^>]*itemprop=[\'"]url[\'"]/i', $body, $bm)) {
                        $metaUrl = html_entity_decode($bm[1]);
                        $metaData = $this->extraerDatosDesdeURL($metaUrl);
                        if (!empty($metaData['lat']) && !empty($metaData['lng'])) {
                            $bestLat = $metaData['lat'];
                            $bestLng = $metaData['lng'];
                            $currentUrl = $metaUrl;
                        }
                        if (!empty($metaData['place_name'])) {
                            $bestPlaceName = $metaData['place_name'];
                        }
                    }

                    // 2. Coordenadas !3d en el body
                    if (!$bestLat || !$bestLng) {
                        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $body, $bm)) {
                            $bestLat = (float)$bm[1];
                            $bestLng = (float)$bm[2];
                        } elseif (preg_match('/APP_INITIALIZATION_STATE=\[\[\[[^\]]*,(-?\d+\.\d+),(-?\d+\.\d+)\]/', $body, $bm)) {
                            $bestLat = (float)$bm[1];
                            $bestLng = (float)$bm[2];
                        }
                    }

                    // 3. Nombre de lugar en el <title> si aún no tenemos
                    if (empty($bestPlaceName) && preg_match('/<title>(.*?)(?: - Google Maps)?<\/title>/i', $body, $tm)) {
                        $title = trim(html_entity_decode($tm[1]));
                        if (!empty($title) && !str_contains(strtolower($title), 'google maps')) {
                            $bestPlaceName = $title;
                        }
                    }

                    break;
                }

                break;
            } catch (\Exception $e) {
                Log::warning('Error en salto de redirección Google Maps: ' . $e->getMessage());
                break;
            }
        }

        return [
            'lat' => $bestLat,
            'lng' => $bestLng,
            'place_name' => $bestPlaceName,
            'final_url' => $currentUrl,
        ];
    }

    /**
     * Obtiene dirección legible mediante Reverse Geocoding.
     */
    public function obtenerDireccion($lat, $lng)
    {
        try {
            $apiKey = env('GOOLEAPIMAPS') ?: config('services.GooMaps.apikey');
            if ($apiKey) {
                $response = Http::timeout(5)->get("https://maps.googleapis.com/maps/api/geocode/json", [
                    'latlng' => "{$lat},{$lng}",
                    'key' => $apiKey,
                    'language' => 'es',
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    if (!empty($json['results'][0]['formatted_address'])) {
                        return $json['results'][0]['formatted_address'];
                    }
                }
            }

            // Fallback: OpenStreetMap Nominatim
            $response = Http::withHeaders([
                'User-Agent' => 'SGTSITA/1.0 (soporte@sita.mx)'
            ])->timeout(5)->get("https://nominatim.openstreetmap.org/reverse", [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'json'
            ]);

            if ($response->successful()) {
                return $response['display_name'] ?? null;
            }
        } catch (\Exception $e) {
            Log::warning('Error en reverse geocoding: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Geocodifica texto o nombre de lugar a coordenadas (Reforzar búsqueda).
     */
    public function geocodificarTexto($direccion)
    {
        try {
            $apiKey = env('GOOLEAPIMAPS') ?: config('services.GooMaps.apikey');
            if ($apiKey) {
                $response = Http::timeout(5)->get("https://maps.googleapis.com/maps/api/geocode/json", [
                    'address' => $direccion,
                    'key' => $apiKey,
                    'language' => 'es',
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    if (!empty($json['results'][0]['geometry']['location'])) {
                        return [
                            'lat' => (float)$json['results'][0]['geometry']['location']['lat'],
                            'lng' => (float)$json['results'][0]['geometry']['location']['lng'],
                            'direccion' => $json['results'][0]['formatted_address'] ?? $direccion,
                        ];
                    }
                }
            }

            // Fallback: OpenStreetMap Nominatim
            $response = Http::withHeaders([
                'User-Agent' => 'SGTSITA/1.0 (soporte@sita.mx)'
            ])->timeout(5)->get("https://nominatim.openstreetmap.org/search", [
                'q' => $direccion,
                'format' => 'json',
                'limit' => 1,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                if (!empty($json[0]['lat']) && !empty($json[0]['lon'])) {
                    return [
                        'lat' => (float)$json[0]['lat'],
                        'lng' => (float)$json[0]['lon'],
                        'direccion' => $json[0]['display_name'] ?? $direccion,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Error en geocodificarTexto: ' . $e->getMessage());
        }

        return null;
    }
}
