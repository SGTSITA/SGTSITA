<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\GlobalGpsTrait;

class GloblaGpsController extends Controller
{
    public function obtenerUbicacionByImei(Request $request){
        $imei = "865468051800335";
        $response = GlobalGpsTrait::getDeviceRealTimeLocation($imei);
       // echo $response;
        return $response;
    }
}
