<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Models\Empresas;
use App\Models\ServicioGps;

trait CommonGpsTrait
{
    public static function getAuthenticationCredentials($rfc,$companyId){
        //Obtener las credenciales previamente guardadas, correspondiente a una empresa
       $empresa = Empresas::where('rfc',$rfc)->first();
       if(is_null($empresa)) return ["success" => false, "mensaje" => "No existe empresa", "account" => null];

       $data = ServicioGps::where('id_empresa',$empresa->id)->where('id_gps_company',$companyId)->first();
       if(is_null($data)) return ["success" => false, "mensaje" => "No existe configuración para TrackSolid PRO", "account" => null];

       $detailAccount = json_decode(Crypt::decryptString($data->account_info));
       $credenciales = [];
        foreach($detailAccount as $a){
            $credenciales[$a->field] =  $a->valor;
        }

       return ["success" => true, "mensaje" => "Credenciales correctamente configuradas", "accessAccount" => $credenciales];
    }
}