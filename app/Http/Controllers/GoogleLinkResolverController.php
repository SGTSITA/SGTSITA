<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleMapsService;

class GoogleLinkResolverController extends Controller
{
    public function resolver(Request $request, GoogleMapsService $service)
    {
        $rawUrl = $request->input('shortUrl') ?? $request->input('url');

        if (empty($rawUrl)) {
            return response()->json(['error' => 'La URL es requerida'], 422);
        }

        $url = trim($rawUrl);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'El enlace proporcionado no es una URL válida'], 422);
        }

        $coords = $service->resolver($url);

        if (!$coords || empty($coords['lat']) || empty($coords['lng'])) {
            return response()->json(['error' => 'No se pudieron obtener coordenadas desde el enlace proporcionado'], 422);
        }

        return response()->json($coords);
    }
}
