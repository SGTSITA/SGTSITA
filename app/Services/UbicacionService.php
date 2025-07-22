<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class UbicacionService
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

                        case 'http://open.10000track.com/route/rest'://jimi -Concox
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
      
       
    }



    public function getLocationSkyAngel(){
        //Sustituir por valores de BD cuando se tenga la implementacion
        $username = config('services.SkyAngelGps.username');
        $password = config('services.SkyAngelGps.password');

        $accessToken = SkyAngel::getAccessToken($username,$password);
        $location = SkyAngel::getLocation($accessToken);

        return $location;
    }


    public function detalleDispositivo($imei)
    {
        $data = $this->callGpsApi('jimi.track.device.detail', [
            'imei' => $imei
        ]);

        return response()->json($data);
    }
}
