<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\GlobalGpsTrait as GlobalGps;
use App\Traits\SkyAngelGpsTrait as SkyAngel;

class GpsController extends Controller
{
    public function obtenerUbicacionByImei($imei){
        $response = GlobalGps::getDeviceRealTimeLocation($imei);
        return $response;
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
