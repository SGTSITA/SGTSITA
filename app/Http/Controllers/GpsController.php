<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\GlobalGpsTrait as GlobalGps;
use App\Traits\SkyAngelGpsTrait as SkyAngel;

class GpsController extends Controller
{
    public function obtenerUbicacionByImei(Request $request){
       $tipor= $request->input('tipo');
        if ($tipor ==='Global'){
            $datos = $request->input('imeis');

            if (!is_array($datos)) {
                $datos = explode(';', $datos);  
            }

            $ubicaciones = [];

            foreach ($datos as $dato) {
                if (!empty($dato)) {
                    [ $contenedor ,$imei,$id_contenendor] = explode('|', $dato);
                    $ubicacion = GlobalGps::getDeviceRealTimeLocation($imei);
                    $resultados[] = [
                        'contenedor' => $contenedor,
                        'ubicacion' => $ubicacion,
                        'id_contenendor' => $id_contenendor,
                    ];
                }
            }

            return response()->json($resultados);

        } else {
            $username = config('services.SkyAngelGps.username');
             $password = config('services.SkyAngelGps.password');

            $accessToken = SkyAngel::getAccessToken($username,$password);
            $location = SkyAngel::getLocation($accessToken);
             $resultados2[] = [
                        'contenedor' => 'Varios para filtrar ',
                        'ubicacion' => $location,
                          'id_contenendor' => '0',
                    ];

                    if (!$accessToken) {
                         dd('Error obteniendo token');
                        }

                        $location = SkyAngel::getLocation($accessToken);

                        if (!$location) {
                            dd('No hay datos de ubicación');
                        }

                       
          return response()->json($resultados2);

        }
       
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
