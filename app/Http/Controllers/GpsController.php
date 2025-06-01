<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\GlobalGpsTrait as GlobalGps;
use App\Traits\SkyAngelGpsTrait as SkyAngel;

class GpsController extends Controller
{
    public function obtenerUbicacionByImei(Request $request){
       $datos = $request->input('imeis');

       if (!is_array($datos)) {
            $datos = explode(';', $datos);  
        }

    $ubicaciones = [];

    foreach ($datos as $dato) {
        if (!empty($dato)) {
            [ $contenedor ,$imei] = explode('-', $dato);
            $ubicacion = GlobalGps::getDeviceRealTimeLocation($imei);
            $resultados[] = [
                'contenedor' => $contenedor,
                'ubicacion' => $ubicacion,
            ];
        }
    }
    
  
    return response()->json($resultados);
    }



    public function getLocationSkyAngel(){

        
        //Sustituir por valores de BD cuando se tenga la implementacion
        $username = config('services.SkyAngelGps.username');
        $password = config('services.SkyAngelGps.password');

        $accessToken = SkyAngel::getAccessToken($username,$password);
        $location = SkyAngel::getLocation($accessToken);

        return $location;
    }
}
