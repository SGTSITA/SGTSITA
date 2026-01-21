<?php

namespace App\Services;

use App\Models\GpsCompanyProveedor;
use Illuminate\Support\Facades\Crypt;

class GpsCredentialsService
{
    public static function getByProveedor(
        string $proveedorRFC,
        int $gpsCompanyId
    ): array {
        $config = GpsCompanyProveedor::with('proveedor', 'empresa', 'gpsCompany')
        ->whereHas('proveedor', function ($q) use ($proveedorRFC) {
            $q->where('RFC', $proveedorRFC);
        })->where('id_gps_company', $gpsCompanyId)
        ->first();

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
            'credentials' => self::normalize($config->account_info)
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
