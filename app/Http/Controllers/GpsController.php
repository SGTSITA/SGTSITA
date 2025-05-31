<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Traits\GlobalGpsTrait as GlobalGps;

class GpsController extends Controller
{
    public function ubicacion()
{
    $imei="864520060147537";
$response = GlobalGps::getDeviceRealTimeLocation($imei);
   

    $data = $response->json();

        return  $data ;
}
}
