<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
 public function resolver($url)
    {

  try {


        $coords = $this->extraerCoordenadasDesdeURL($url);

        if ($coords) {
            return $coords;
        }


        $response = Http::withoutRedirecting()->get($url);

        if (in_array($response->status(), [301, 302])) {

            $redirectUrl = $response->header('Location');

            if ($redirectUrl) {
                $coords = $this->extraerCoordenadasDesdeURL($redirectUrl);

                if ($coords) {
                    return $coords;
                }
            }
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

    private function extraerCoordenadasDesdeURL($url)
    {

        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
        }


        if (preg_match('/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
        }

        if (preg_match('/\/(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
        }

        return null;
    }


    private function obtenerDireccion($lat, $lng)
{
    $response = Http::withHeaders([
        'User-Agent' => 'TuApp/1.0'
    ])->get("https://nominatim.openstreetmap.org/reverse", [
        'lat' => $lat,
        'lon' => $lng,
        'format' => 'json'
    ]);

    if ($response->successful()) {
        return $response['display_name'] ?? null;
    }

    return null;
}

}



