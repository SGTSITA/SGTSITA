<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GoogleLinkResolverController extends Controller
{
    public function resolver(Request $request)
    {
        $request->validate([
            'shortUrl' => 'required|url',
        ]);

        $shortUrl = $request->input('shortUrl');

        try {
            // Hacer petición GET sin seguir redirecciones automáticamente
            $response = Http::withoutRedirecting()->get($shortUrl);

            if ($response->status() == 301 || $response->status() == 302) {
                $redirectUrl = $response->header('Location');

                if (!$redirectUrl) {
                    return response()->json(['error' => 'No se encontró URL de redirección'], 500);
                }

                // Extraer coordenadas de la URL larga
                $coords = $this->extraerCoordenadasDesdeURL($redirectUrl);

                if (!$coords) {
                    return response()->json(['error' => 'No se pudieron extraer coordenadas'], 500);
                }

                // Aquí podrías llamar a Google Geocoding API para dirección formateada
                return response()->json([
                    'lat' => $coords['lat'],
                    'lng' => $coords['lng'],
                    'formatted_address' => null
                ]);
            } else {
                return response()->json(['error' => 'Respuesta inesperada: ' . $response->status()], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al resolver la URL: ' . $e->getMessage()], 500);
        }
    }

    private function extraerCoordenadasDesdeURL($url)
    {
        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return [
                'lat' => floatval($matches[1]),
                'lng' => floatval($matches[2]),
            ];
        }
        return null;
    }
}
