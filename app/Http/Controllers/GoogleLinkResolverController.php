<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use  App\Services\GoogleMapsService;

class GoogleLinkResolverController extends Controller
{




public function resolver(Request $request, GoogleMapsService $service) {
  $request->validate([
            'shortUrl' => 'required|url',
        ]);

 $coords = $service->resolver($request->shortUrl);

    if (!$coords) {
        return response()->json(['error' => 'No se pudieron obtener coordenadas'], 422);
    }

    return response()->json($coords);
}



}
