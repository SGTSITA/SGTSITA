<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\GlobalGpsTrait as GlobalGps;
use App\Traits\SkyAngelGpsTrait as SkyAngel;
use App\Traits\JimiGpsTrait as JimiGps;


class GpsController extends Controller
{
    
    public function obtenerUbicacionByImei(Request $request){
    

        $datos = $request->input('imeis');

            if (!is_array($datos)) {
                $datos = explode(';', $datos);  
            }
            $ubicaciones = [];

            $ubicacion = null;
            $tipoGpsresponse="";
            
            foreach ($datos as $dato) {
                if (!empty($dato)) {
                    [ $contenedor ,$imei,$id_contenendor,$tipoGps] = explode('|', $dato);

                    switch ($tipoGps) {
                        case 'https://open.iopgps.com': //global
                             $ubicacion = GlobalGps::getDeviceRealTimeLocation($imei);
                             $tipoGpsresponse="Global";
                            break;

                        case 'http://sta.skyangel.com.mx:8085/api/tracks/v1': //skyangel
                             $username = config('services.SkyAngelGps.username');
                            $password = config('services.SkyAngelGps.password');

                            $accessToken = SkyAngel::getAccessToken($username,$password);
                            $ubicacion = SkyAngel::getLocation($accessToken);
                            $tipoGpsresponse="skyGps";
                            break;

                        case 'https://us-open.tracksolidpro.com/route/rest'://jimi -Concox
                           $ubicacion = $this->detalleDispositivo($imei);
                           $tipoGpsresponse="jimi";
                            break;

                         default:
                             $ubicacion =[
                                'mesage'=>'No encontrado el servicio de GPS',
                                    'latitud' => 0,
                                    'longitud' => 0,
                                    'fecha' => null,
                             ];
                             break;
                    }
                    
                    $resultados[] = [
                        'contenedor' => $contenedor,
                        'ubicacion' => $ubicacion,
                        'id_contenendor' => $id_contenendor,
                        'tipogps'=> $tipoGpsresponse
                    ];
                }
            }


        return response()->json($resultados);
        // if ($tipor ==='Global'){
          
                   

        // } else {
           
        //      $resultados2[] = [
        //                 'contenedor' => 'Varios para filtrar ',
        //                 'ubicacion' => $location,
        //                   'id_contenendor' => '0',
        //             ];

        //             if (!$accessToken) {
        //                  dd('Error obteniendo token');
        //                 }

        //                 $location = SkyAngel::getLocation($accessToken);

        //                 if (!$location) {
        //                     dd('No hay datos de ubicación');
        //                 }

                       
        //   return response()->json($resultados2);

       // }
       
    }



    public function getLocationSkyAngel(){
        //Sustituir por valores de BD cuando se tenga la implementacion
        $username = config('services.SkyAngelGps.username');
        $password = config('services.SkyAngelGps.password');

        $accessToken = SkyAngel::getAccessToken($username,$password);
        $location = SkyAngel::getLocation($accessToken);

        return $location;
    }

    public function tokenJimi(){
        return $this->getGpsAccessToken();
    }

    public function detalleDispositivo($imei)
    {
        $data = $this->callGpsApi('jimi.track.device.detail', [
            'imei' => $imei
        ]);

        return response()->json($data);
    }
}
