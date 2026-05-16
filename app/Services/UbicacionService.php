<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Traits\GlobalGpsTrait as GlobalGps;
use App\Traits\SkyAngelGpsTrait as SkyAngel;
use App\Traits\JimiGpsTrait;
use App\Traits\LegoGpsTrait as LegoGps;
use App\Traits\GpsTrackerMXTrait as GpsTrackerMXTrait;
use App\Traits\CommonGpsTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Empresas;
use App\Services\GpsCredentialsService;
use App\Traits\BeyondGPSTrait;
use App\Traits\WialonGpsTrait;

use App\Traits\SISGPSTrait as SISGPSTrait;

class UbicacionService
{
 public function obtenerUbicacionByImei($datos)
    {


        if (!is_array($datos)) {
            $datos = explode(';', $datos);
        }
        $ubicaciones = [];
$resultados = [];
        $ubicacion = null;
        $tipoGpsresponse = "";

        // dd($datos);

        $index = 0;

        foreach ($datos as $dato) {
            if (!empty($dato)) {
                $esDatoEmp = "NO";

                [ $contenedor ,$imei,$id_contenendor,$tipoGps] = explode('|', $dato);
                // dd($imei);
                $RfcyEquipo = $this->buscartipoProveedor($contenedor, $id_contenendor, $imei);
                // dd($RfcyEquipo);

                [$Rfc,$equipo,$empresaIdRastro,$TipoEquipo,$gps_company_id,$tipo_viaje_contrato,$placas,$tipo_camion_rev,$tipoConfig,$id_equipoUnic] = explode('|', $RfcyEquipo);

                // dd($RfcyEquipo);



                $empresaIdRastro = (int) $empresaIdRastro;


                if ($empresaIdRastro === Auth::User()->id_empresa) {

                    $esDatoEmp = "SI";
                }
                //             $config = GpsCompanyProveedor::with('proveedor', 'empresa', 'gpsCompany')
                //     ->whereHas('proveedor', function ($q) use ($Rfc) {
                //         $q->where('RFC', $Rfc);
                //     })->where('id_gps_company', $gps_company_id)
                //     ->first();


                //             $account = $config
                //                    ? json_decode(
                //                        Crypt::decryptString($config->account_info),
                //                        true
                //                    )
                //                    : [];
                //dd($contenedor, $imei, $id_contenendor, $tipoGps);
                // dd($Rfc, $gps_company_id);
                $result = GpsCredentialsService::getByProveedor($Rfc, $gps_company_id, $tipo_viaje_contrato, $tipo_camion_rev, $tipoConfig, $equipo, $id_equipoUnic);
                //  dd($result);

                // if ($index == 1) {
                //     dd($result);
                // }

                if (!$result['success']) {
                    return $result;
                }


                $credenciales = $result['credentials'];
                //             $normalized = collect($account)
                // ->pluck('valor', 'field')
                // ->toArray();


                //dd($credenciales);


                $responseGps = $this->consultarGps(
    $tipoGps,
    $credenciales,
    $imei,
    $placas,
    $TipoEquipo,
    $esDatoEmp
);

$resultados[] = [
    'contenedor' => $contenedor,
    'ubicacion' => $responseGps['ubicacion'],
    'id_contenendor' => $id_contenendor,
    'tipogps' => $responseGps['tipogps'],
    'EquipoBD' => $equipo,
    'value' => $dato,
    'tiemporespuesta' => $responseGps['tiemporespuesta'],
    'messageAp' => $responseGps['messageAp'],
    'status' => $responseGps['status'],
    'new_id' => $gps_company_id,
];

            }
            //  dd($resultados);

            $index = $index + 1;

        }


        return $resultados;


    }


public function consultarGps(
    $tipoGps,
    $credenciales,
    $imei,
    $placas = null,
    $TipoEquipo = null,
    $esDatoEmp = false
) {

    $ubicacion = [
        'lat' => 0,
        'lng' => 0,
        'velocidad' => null,
        'imei' => $imei,
        'deviceName' => null,
        'mcType' => null,
        'datac' => null,
        'esDatoEmp' => $esDatoEmp,
        'tipoEquipo' => $TipoEquipo
    ];

    $tipoGpsresponse = '';
    $messageError = 'Sin error';
    $status = false;

    $inicio = microtime(true);

    try {

        switch ($tipoGps) {

            case 'https://open.iopgps.com':

                $data = GlobalGps::getDeviceRealTimeLocation(
                    $imei,
                    $credenciales['appkey'] ?? null,
                    $credenciales['account'] ?? null
                );

              //  dd( $data);
                $ubicacionApiResponse = $data->data ?? [];

                $ubicacion = [
                    'lat' => $ubicacionApiResponse['lat'] ?? 0,
                    'lng' => $ubicacionApiResponse['lng'] ?? 0,
                    'velocidad' => 0,
                    'imei' => $imei,
                    'deviceName' => '',
                    'mcType' => '',
                    'datac' => $data,
                    'esDatoEmp' => $esDatoEmp,
                    'tipoEquipo' => $TipoEquipo
                ];

                $tipoGpsresponse = "Global GPS";

            break;

            case 'http://sta.skyangel.com.mx:8085/api/tracks/v1':

                $accessToken = SkyAngel::getAccessToken(
                    $credenciales['email'] ?? null,
                    $credenciales['password'] ?? null
                );

                $response = SkyAngel::getLocation($accessToken);

                $ubicacionApiResponse = null;

                foreach ($response->data as $item) {

                    if ((string)$item['imei'] === (string)$imei) {
                        $ubicacionApiResponse = $item;
                        break;
                    }

                }

                $track = $ubicacionApiResponse['tracks'][0] ?? null;

                $ubicacion = [
                    'lat' => $track['position']['latitude'] ?? 0,
                    'lng' => $track['position']['longitude'] ?? 0,
                    'velocidad' => $track['velocity']['groundSpeed'] ?? 0,
                    'imei' => $imei,
                    'deviceName' => $ubicacionApiResponse['economico'] ?? null,
                    'mcType' => '',
                    'datac' => $ubicacionApiResponse,
                    'esDatoEmp' => $esDatoEmp,
                    'tipoEquipo' => $TipoEquipo
                ];

                $tipoGpsresponse = "SkyAngel";

            break;

            case 'https://www.tracksolidpro.com':

                $data = JimiGpsTrait::callGpsApi(
                    'jimi.device.location.get',
                    $credenciales,
                    ['imeis' => $imei]
                );

                $ubicacionApi = collect($data['result'] ?? [])->first();

                $ubicacion = [
                    'lat' => $ubicacionApi['lat'] ?? 0,
                    'lng' => $ubicacionApi['lng'] ?? 0,
                    'velocidad' => $ubicacionApi['speed'] ?? null,
                    'imei' => $ubicacionApi['imei'] ?? null,
                    'deviceName' => $ubicacionApi['deviceName'] ?? null,
                    'mcType' => $ubicacionApi['mcType'] ?? null,
                    'datac' => $data,
                    'esDatoEmp' => $esDatoEmp,
                    'tipoEquipo' => $TipoEquipo
                ];

                $tipoGpsresponse = "Jimi";

            break;

            case 'https://alxdevelopments.com':

                $data = LegoGps::getLocation($credenciales);

                $ubicacionApi = $data?->data[0] ?? null;

                $ubicacion = [
                    'lat' => $ubicacionApi['latitud'] ?? 0,
                    'lng' => $ubicacionApi['longitud'] ?? 0,
                    'velocidad' => $ubicacionApi['velocidad'] ?? null,
                    'imei' => $ubicacionApi['imei'] ?? null,
                    'deviceName' => $ubicacionApi['unidad'] ?? null,
                    'mcType' => '',
                    'datac' => $data,
                    'esDatoEmp' => $esDatoEmp,
                    'tipoEquipo' => $TipoEquipo
                ];

                $tipoGpsresponse = "Lego GPS";

            break;

            case 'https://gpstracker.mx':

                $data = GpsTrackerMXTrait::getMutiDevicePosition($credenciales);

               // $ubicacion['datac'] = $data;

                $eventos =  $data?->data ?? null;
              //  dd( $data ,$eventos,);

$ubicacionApi = collect($eventos)->first(function ($item) use ($imei) {

    return isset($item['deviceId']) &&
        (string)$item['deviceId'] === (string)$imei;

});

if ($ubicacionApi) {

    $ubicacion = [
        'lat' => $ubicacionApi['latitude'] ?? 0,
        'lng' => $ubicacionApi['longitude'] ?? 0,
        'velocidad' => $ubicacionApi['speed'] ?? null,
        'imei' => $imei,
        'deviceName' => $ubicacionApi['address'] ?? null,
        'mcType' => $ubicacionApi['course'] ?? null,
        'altitud' => $ubicacionApi['altitude'] ?? null,
        'timestamp' => $ubicacionApi['deviceTime'] ?? null,
        'datac' => $data,
        'esDatoEmp' => $esDatoEmp,
        'tipoEquipo' => $TipoEquipo
    ];

}

                $tipoGpsresponse = "Tracker GPS";

            break;

            case 'Beyond':

                $data = BeyondGPSTrait::getLocation(
                    $credenciales['user'] ?? null,
                    $credenciales['password'] ?? null,
                    $credenciales['endpoint'] ?? null,
                );

                $eventos = $data?->data['events'] ?? [];

                $ubicacionApi = collect($eventos)->first(function ($item) use ($imei) {

                    return isset($item['IMEI']) &&
                        (string)$item['IMEI'] === (string)$imei;

                });

                if ($ubicacionApi) {

                    $ubicacion = [
                        'lat' => $ubicacionApi['Lat'] ?? 0,
                        'lng' => $ubicacionApi['Lon'] ?? 0,
                        'velocidad' => $ubicacionApi['Speed'] ?? null,
                        'imei' => $imei,
                        'deviceName' => $ubicacionApi['Alias'] ?? null,
                        'mcType' => $ubicacionApi['Course'] ?? null,
                        'datac' => $ubicacionApi,
                        'esDatoEmp' => $esDatoEmp,
                        'tipoEquipo' => $TipoEquipo
                    ];

                }

                $tipoGpsresponse = "Beyond";

            break;

            case 'https://gpsv7.com/php/wialon_data.php':

              $data = WialonGpsTrait::getloginLocation(
    $credenciales['token'] ?? null,
    $credenciales['SID'] ?? null
);

$items = $data?->data['items'] ?? [];

$placasBuscar = str_replace('-', '', strtoupper($placas));

$ubicacionApi = collect($items)->first(function ($item) use ($placasBuscar) {

    $unidad = str_replace(
        '-',
        '',
        strtoupper($item['unidad'] ?? '')
    );

    $placasUnidad = str_replace(
        '-',
        '',
        strtoupper($item['placas'] ?? '')
    );

    return
        str_contains($unidad, $placasBuscar)
        ||
        str_contains($placasUnidad, $placasBuscar);
});

if ($ubicacionApi) {

    $ubicacion = [
        'lat' => $ubicacionApi['latitud'] ?? 0,
        'lng' => $ubicacionApi['longitud'] ?? 0,
        'velocidad' => $ubicacionApi['velocidad'] ?? null,
        'imei' => $ubicacionApi['imei'] ?? $imei,
        'deviceName' => $ubicacionApi['unidad'] ?? null,
        'mcType' => $ubicacionApi['rumbo'] ?? null,
        'timestamp' => $ubicacionApi['timestamp'] ?? null,
        'datac' => $data,
        'esDatoEmp' => $esDatoEmp,
        'tipoEquipo' => $TipoEquipo
    ];
}

                $tipoGpsresponse = "Wialon";

            break;

            case 'https://www.rastreogps.com/':

                $data = SISGPSTrait::sisGetLastPosition(
                    $credenciales['account'],
                    $credenciales['appkey'],
                    $imei
                );

                $raw = $data->data['raw']->return ?? null;

                $ubicacionApi = $raw
                    ? json_decode($raw, true)
                    : [];

                $ubicacion = [
                    'lat' => $ubicacionApi['Latitude'] ?? 0,
                    'lng' => $ubicacionApi['Longitude'] ?? 0,
                    'velocidad' => $ubicacionApi['Speed'] ?? null,
                    'imei' => $ubicacionApi['ID'] ?? null,
                    'deviceName' => $ubicacionApi['UnitType'] ?? null,
                    'mcType' => $ubicacionApi['DataCommType'] ?? null,
                    'datac' => $ $ubicacionApi,
                    'esDatoEmp' => $esDatoEmp,
                    'tipoEquipo' => $TipoEquipo
                ];

                $tipoGpsresponse = "SIS GPS";

            break;

            default:

                $messageError = 'No encontrado el servicio GPS';

            break;
        }

        if (
            floatval($ubicacion['lat']) != 0 &&
            floatval($ubicacion['lng']) != 0
        ) {
            $status = true;
        } else {
            $messageError = 'Sin ubicación para mostrar';
        }

    } catch (\Exception $e) {

        $messageError = $e->getMessage();
        $status = false;

    }

    $fin = microtime(true);

    $tiempoRespuesta = round(($fin - $inicio) * 1000, 2);

    return [
        'ubicacion' => $ubicacion,
        'tipogps' => $tipoGpsresponse,
        'status' => $status,
        'messageAp' => $messageError,
        'tiemporespuesta' => $tiempoRespuesta,
    ];
}

    public function getLocationSkyAngel()
    {
        //Sustituir por valores de BD cuando se tenga la implementacion
        $username = config('services.SkyAngelGps.username');
        $password = config('services.SkyAngelGps.password');

        $accessToken = SkyAngel::getAccessToken($username, $password);
        $location = SkyAngel::getLocation($accessToken);

        return $location;
    }






    public function buscartipoProveedor($num_Contenendor, $idKey, $imei)
    {
        //TP-001|865468051839242|5|https://open.iopgps.com
        $datosAll = null;

        $existeContenedor = DB::table('docum_cotizacion')->where('docum_cotizacion.num_contenedor', '=', $num_Contenendor)->exists();
        $Equipo = "";
        $TipoEquipo = "";
        $placas = "";
        $tipo_camion_rev = "";
        $tipoConfig = 1;
        $id_equipoUnic = null;
        if ($existeContenedor) {


            $asignaciones = DB::table('asignaciones')
            ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
            ->join('equipos', 'equipos.id', '=', 'asignaciones.id_camion')
            ->join('gps_company', 'gps_company.id', '=', 'equipos.gps_company_id')
            ->leftjoin('equipos as eq_chasis', 'eq_chasis.id', '=', 'asignaciones.id_chasis')

            ->select(
                'docum_cotizacion.id as id_contenedor',
                'docum_cotizacion.id_cotizacion as id_cotizacion_doc',
                'asignaciones.id',
                'asignaciones.id_camion',
                'asignaciones.id_chasis',
                'docum_cotizacion.num_contenedor',
                'asignaciones.fecha_inicio',
                'asignaciones.fecha_fin',
                'asignaciones.tipo_contrato as tipo_viaje_contratado',
                'equipos.imei',
                'equipos.id_equipo',
                'equipos.marca',
                'equipos.modelo',
                'equipos.placas',
                'equipos.usar_config_global',
                'gps_company.url_conexion as tipoGps',
                'gps_company.id as gps_company_id',
                'eq_chasis.imei as imei_chasis',
                'eq_chasis.id_equipo as id_equipo_chasis',
                'eq_chasis.placas as placas_chasis',
                'eq_chasis.usar_config_global as usar_config_global_chasis',
                'eq_chasis.gps_company_id as gps_company_id_chasis',
                DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN asignaciones.id_operador ELSE asignaciones.id_proveedor END as beneficiario_id"),
                DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN 'Propio' ELSE 'Subcontratado' END as tipo_contrato")
            );

            $beneficiarios = DB::table(function ($query) {
                $query->select('id', 'nombre', 'telefono', DB::raw("'buscarEmpresaRFC' as RFC"), DB::raw("'Propio' as tipo_contrato"), 'id_empresa')
                    ->from('operadores')
                    ->union(
                        DB::table('proveedores')
                            ->select('id', 'nombre', 'telefono', 'RFC', DB::raw("'Subcontratado' as tipo_contrato"), 'id_empresa')
                    );
            }, 'beneficiarios');


            $datosAll = DB::table('cotizaciones')
            ->select(
                'cotizaciones.id as id_cotizacion',
                'asig.id as id_asignacion',
                'clients.nombre as cliente',
                'cotizaciones.origen',
                'cotizaciones.destino',
                'asig.num_contenedor as contenedor',
                'cotizaciones.estatus',
                'asig.tipo_viaje_contratado',
                'asig.id_camion',
                'asig.id_chasis',
                'asig.imei',
                'asig.id_equipo',
                'asig.placas',
                'asig.usar_config_global',
                'asig.id_contenedor',
                'asig.tipo_contrato',
                'asig.fecha_inicio',
                'asig.fecha_fin',
                'asig.tipoGps',
                'asig.imei_chasis',
                'asig.id_equipo_chasis',
                'asig.placas_chasis',
                'asig.usar_config_global_chasis',
                'asig.gps_company_id',
                'asig.gps_company_id_chasis',
                'cotizaciones.id_empresa',
                'beneficiarios.RFC'
            )
            ->join('clients', 'cotizaciones.id_cliente', '=', 'clients.id')

            ->joinSub($asignaciones, 'asig', function ($join) {
                $join->on('asig.id_cotizacion_doc', '=', 'cotizaciones.id');
            })

            ->joinSub($beneficiarios, 'beneficiarios', function ($join) {
                $join->on('asig.beneficiario_id', '=', 'beneficiarios.id')
                    ->on('asig.tipo_contrato', '=', 'beneficiarios.tipo_contrato');
            })
            ->whereNotNull('asig.imei') ->where('cotizaciones.estatus', '=', 'Aprobada')
            ->where('asig.num_contenedor', '=', $num_Contenendor)
            ->first();




            //

        } else {

            $datosAll = DB::table('equipos')
            ->join('gps_company', 'gps_company.id', '=', 'equipos.gps_company_id')
            ->join('empresas', 'empresas.id', '=', 'equipos.id_empresa')
            ->leftjoin('user_proveedores', 'user_proveedores.user_id', '=', 'equipos.user_id')
            ->leftjoin('proveedores', 'proveedores.id', '=', 'user_proveedores.proveedor_id')

              ->select(
                  'equipos.id as id_camion',
                  'equipos.imei',
                  'equipos.id_equipo',
                  'equipos.placas',
                  'gps_company.url_conexion as tipoGps',
                  'equipos.id_empresa',
                  DB::raw("
                    COALESCE(proveedores.rfc,empresas.rfc )
                    as RFC
                "),
                  DB::raw("
                    CASE
                        WHEN proveedores.rfc IS NOT NULL AND proveedores.rfc <> ''
                        THEN 'camion_proveedor'
                        ELSE 'camion_propio'
                    END as tipo_camion_rev
                "),
                  'gps_company.id as gps_company_id',
                  DB::raw("'Propio' as tipo_viaje_contratado"),
                  'equipos.usar_config_global',
                  'equipos.id_equipo as id_equipo_chasis',
                  'equipos.usar_config_global as usar_config_global_chasis',
                  'equipos.placas as placas_chasis',
                  'equipos.id as id_chasis',
                  'gps_company.id as gps_company_id_chasis'
              )
            ->where('equipos.id', '=', $idKey)->first();

            $tipo_camion_rev = $datosAll?->tipo_camion_rev;
        }



        if ($datosAll) {
            $RFCContenedor = $datosAll?->RFC;
            //  $Equipo = $datosAll?->id_equipo;
            $empresaIdRastreo = $datosAll?->id_empresa;

            $tipoviaje =  $datosAll?->tipo_viaje_contratado;
            // $placas =  $datosAll?->placas;

            if ($imei === $datosAll?->imei) {
                //corresponde al equipo del contendor
                $Equipo = $datosAll?->id_equipo;
                $placas = $datosAll?->placas;
                $TipoEquipo = 'Camion';
                $tipoConfig = $datosAll?->usar_config_global;
                $id_equipoUnic = $datosAll?->id_camion;
                $gps_company_id = $datosAll?->gps_company_id;
            } elseif ($imei === $datosAll?->imei_chasis) {
                //corresponde al equipo del chasis
                $Equipo = $datosAll?->id_equipo_chasis;
                $TipoEquipo = 'Chasis';
                $placas = $datosAll?->placas_chasis;
                $tipoConfig = $datosAll?->usar_config_global_chasis;
                $id_equipoUnic = $datosAll?->id_chasis;
                $gps_company_id = $datosAll?->gps_company_id_chasis;


            }

            if ($RFCContenedor === 'buscarEmpresaRFC') {
                //buscamos el rfc de la empresa pues no tiene asignado un proveedor....
                // $empresas = Empresas::where('id', '=', auth()->user()->Empresa->id)->orderBy('created_at', 'desc')->first();

                $cotizaciones = DB::table('cotizaciones')->join(
                    'docum_cotizacion',
                    'docum_cotizacion.id_cotizacion',
                    '=',
                    'cotizaciones.id'
                )->join('proveedores', 'proveedores.id', '=', 'cotizaciones.id_proveedor')
                ->where(
                    'docum_cotizacion.num_contenedor',
                    '=',
                    $num_Contenendor
                )->first();



                // dd($empresas);
                $RFCContenedor =   $cotizaciones->rfc; //minusculas
                //dd($RFCContenedor);
            }
            // dd($datosAll, $cotizaciones);

            return   $RFCContenedor . '|'. $Equipo . '|'.  $empresaIdRastreo .'|'. $TipoEquipo.'|'. $gps_company_id.'|'.$tipoviaje.'|'.$placas.'|'.$tipo_camion_rev.'|'.$tipoConfig.'|'.$id_equipoUnic;


        }

    }

}
