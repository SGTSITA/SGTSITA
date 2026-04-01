<?php

namespace App\Services;

use App\Models\GpsCompanyProveedor;
use App\Models\Equipo;
use Illuminate\Support\Facades\Crypt;

class GpsCredentialsService
{
    public static function getByProveedor(
        string $proveedorRFC,
        int $gpsCompanyId,
        string $tipo_viaje_contrato,
        string  $tipo_camion_rev,
        int $tipoConfig,
        string $Equipo,
        int $id_equipoUnic
    ): array {
        $config = [];
        $desencript = true;
        if ($tipo_viaje_contrato == 'Propio' && $tipo_camion_rev != 'camion_proveedor') {
            //buscar en la config anterior por empresa para global y camiones propios nada mas
            $key = config('services.globalGps.appkey');
            $apiid = config('services.globalGps.appid');

            $config =  [
             'success' => true,
             'message' => 'Credenciales obtenidas propias',
             'credentials' => ['key' => $key, 'apiid' => $apiid ]
        ];



            $desencript = false;


        } else {
            $config = GpsCompanyProveedor::with('proveedor', 'empresa', 'gpsCompany')
                  ->whereHas('proveedor', function ($q) use ($proveedorRFC) {
                      $q->where('RFC', $proveedorRFC);
                  })->where('id_gps_company', $gpsCompanyId)
                  ->first();
            //  dd($tipoConfig, $Equipo);
            if ($tipoConfig == 0 && $Equipo) {
                //   dd('entra');
                $equipo =  Equipo::find($id_equipoUnic);

                $config = (object)[
                    'account_info' => $equipo->credenciales_gps
                ];
            }
        }






        if (!$config) {
            return [
                'success' => false,
                'message' => 'No existe configuración GPS para el proveedor',
                'credentials' => []
            ];
        }

        return [
            'success' => true,
            'message' => 'Credenciales obtenidas',
           'credentials' => $desencript
    ? self::normalize($config->account_info)
    : $config,
        ];
    }


    private static function normalize(string $encrypted): array
    {
        $raw = json_decode(
            Crypt::decryptString($encrypted),
            true
        );

        return collect($raw)
            ->pluck('valor', 'field')
            ->toArray();
    }
}
