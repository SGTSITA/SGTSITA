<?php

namespace App\Http\Controllers;

use App\Models\GpsCompany;
use App\Models\User;
use App\Models\GpsCompanyProveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\ServicioGps;
use App\Traits\JimiGpsTrait;
use App\Traits\LegoGpsTrait;
use App\Traits\CommonGpsTrait;
use App\Traits\GpsTrackerMXTrait;
use App\Traits\BeyondGPSTrait;
use App\Traits\WialonGpsTrait;
use App\Traits\GlobalGpsTrait;
use App\Traits\SISGPSTrait;
use App\Models\Equipo;
use Illuminate\Support\Facades\Log;

class GpsCompanyController extends Controller
{
    public function index()
    {
        return view('gps.index');
    }

    public function setupGps()
    {
        // $empresaId = auth()->user()->id_empresa;
        // $gpsCompanies = GpsCompany::with(['serviciosGps' => function ($q) use ($empresaId) {
        //     $q->where('id_empresa', $empresaId);
        // }])->get();

        // $companies = $gpsCompanies->map(function ($g) {
        //     $g->estado = $g->serviciosGps->isNotEmpty() ? 'Activo' : 'No contratado';
        //     return $g;
        // });
        $user =  User::find(auth()->user()->id);
        $empresaId   = auth()->user()->id_empresa;
        $proveedorId =   $user ->proveedores()
            ->value('proveedor_id');

        $gpsCompanies = GpsCompany::with([
            'empresas' => function ($q) use ($empresaId, $proveedorId) {
                $q->where('id_empresa', $empresaId)
                  ->where('id_proveedor', $proveedorId)
                  ->where('estado', 1);
            }
        ])->get();

        $companies = $gpsCompanies->map(function ($g) {
            $g->estado = $g->empresas->isNotEmpty()
                ? 'Activo'
                : 'No contratado';


            $g->configuracion = $g->empresas->first();

            return $g;
        });
        return view('gps.setup', ["companies" => $companies]);
    }

    //Obtiene la configuración GPS-Proveedor, verificar con los usuarios nuevos con proveedor asignado
    public function getConfig(Request $r)
    {
        // $empresaId = auth()->user()->id_empresa;

        // $gpsConfig = GpsCompany::leftJoin('servicio_gps_empresa as ge', 'gps_company.id', "=", 'ge.id_gps_company')
        //                         ->where('gps_company.id', $r->gps)
        //                         ->get();

        // $cuenta = $gpsConfig->where('id_empresa', $empresaId);
        // $account = (sizeof($cuenta) > 0) ? json_decode(Crypt::decryptString($gpsConfig[0]->account_info)) : [];
        $user =  User::find(auth()->user()->id);

        $empresaId   = auth()->user()->id_empresa;
        $proveedorId = $user->proveedores()
            ->value('proveedor_id');


        $gpsCompany = GpsCompany::findOrFail($r->gps);


        $config = GpsCompanyProveedor::when($empresaId != 0, function ($q) use ($empresaId) {
            $q->where('id_empresa', $empresaId);
        })
            ->where('id_proveedor', $proveedorId)
            ->where('id_gps_company', $r->gps)
            ->first();

        $account = $config
            ? json_decode(
                Crypt::decryptString($config->account_info),
                true
            )
            : [];

        return response()->json([
            'data'    => $gpsCompany,
            'account' => $account,
            'estado'  => $config?->estado ?? 0
        ]);

        return response()->json(["data" => $gpsConfig,"account" => ($account)]);
    }

    public function setConfig(Request $r)
    {

        /*
            - Primero validaremos las credenciales del GPS Seleccionado.
            - Si la conexion al servicio Falla con las credenciales proporcionadas no se Guardaran cambios
        */
        $empresaId = auth()->user()->id_empresa;
        $user =  User::find(auth()->user()->id);

        $proveedorIds = $user->proveedores()
    ->pluck('proveedor_id');


        $credenciales = collect($r->account)
            ->pluck('valor', 'field')
            ->toArray();

        /* ================= VALIDACIÓN ================= */
        $mensajeError = null;
        $resp = null;
        switch ((int) $r->gps) {
            case 1: //Global GPS
                $token = GlobalGpsTrait::getAccessToken(
                    $credenciales['appkey'] ?? null,
                    $credenciales['account'] ?? null
                );
                break;

            case 2: //Jimi GPS
                $token = JimiGpsTrait::getGpsAccessToken(
                    $empresaId,
                    $credenciales
                );
                break;

            case 3: //Lego GPS
                $token = LegoGpsTrait::validateOwner($credenciales);
                break;

            case 4: //Tracker GPS MX
                $token = GpsTrackerMXTrait::getGpsAccessToken(
                    $empresaId,
                    $credenciales
                );
                break;

            case 5: // Beyond GPS tajiro

                $response = BeyondGPSTrait::getLocation(
                    $credenciales['user'] ?? null,
                    $credenciales['password'] ?? null,
                    $credenciales['endpoint'] ?? null
                );
                $resp = $response;
                if (!$response->success) {
                    return response()->json([
                        'success' => false,
                        'message' => $response->message ?? 'Error en conexión'
                    ]);
                }

                $data = $response->data;

                if (!($data['success'] ?? false)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Proveedor GPS respondió sin éxito'
                    ]);
                }


                if (empty($data['events'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sin eventos / sin datos de ubicación'
                    ]);
                }


                $token = true;


                break;

            case 6: // Wialon GPS
                $responseWialon =   WialonGpsTrait::getLocation(
                    $credenciales['token'] ?? null,
                    $credenciales['SID'] ?? null
                );
                $resp = $responseWialon;
                if (
                    !$responseWialon->success ||
                    !is_array($responseWialon->data) ||
                    count($responseWialon->data) === 0
                ) {
                    $mensajeError = "Error al validar las credenciales con Wialon GPS";
                    $token = null;
                    break;
                }

                $token = true;
                break;

            case 7: //SIS GPS
                $response = SISGPSTrait::sisValidarCredenciales(
                    $credenciales['account'] ?? '',
                    $credenciales['key'] ?? ''
                );

                if ($response->success) {
                    $token = $response->data['token'] ?? null;
                } else {
                    throw new \Exception($response->message);
                }
                break;

            default:
                $token = false;
        }

        if (!$token) {
            return response()->json([
                "Titulo"   => "Credenciales incorrectas",
                "Mensaje"  => "No se pudo validar el acceso al proveedor GPS ,".   $mensajeError,
                "TMensaje" => "warning",
                 "resp"    => $token,
                "r" => $resp
            ]);
        }

        foreach ($proveedorIds as $proveedorId) {

            GpsCompanyProveedor::updateOrCreate(
                [
                    'id_empresa'     => $empresaId,
                    'id_proveedor'   => $proveedorId,
                    'id_gps_company' => $r->gps,
                ],
                [
                    'account_info' => Crypt::encryptString(
                        json_encode($r->account)
                    ),
                    'estado' => 1
                ]
            );

        }
        // $config = GpsCompanyProveedor::updateOrCreate(
        //     [
        //         'id_empresa'     => $empresaId,
        //         'id_proveedor'   => $proveedorId,
        //         'id_gps_company' => $r->gps,
        //     ],
        //     [
        //         'account_info' => Crypt::encryptString(
        //             json_encode($r->account)
        //         ),
        //         'estado' => 1
        //     ]
        // );
        // dd($config);

        return response()->json([
            "Titulo"   => "Correcto",
            "Mensaje"  => "Se guardó la configuración del servicio GPS",
            "TMensaje" => "success",
            "token"    => $token,
             "r" => $resp
        ]);
    }

    public function data()
    {
        $user =  User::find(auth()->user()->id);
        $empresaId = auth()->user()->id_empresa;
        $proveedorId = $user->proveedores()
            ->value('proveedor_id');

        return GpsCompany::withTrashed()
            ->with([
                'empresas' => function ($q) use ($empresaId, $proveedorId) {
                    $q->when($empresaId != 0, function ($q) use ($empresaId) {
                        $q->where('id_empresa', $empresaId);
                    })
                      ->where('id_proveedor', $proveedorId);
                }
            ])
            ->get()
            ->map(function ($gps) {
                $config = $gps->empresas->first();

                return [
                    'id'        => $gps->id,
                    'nombre'    => $gps->nombre,
                    'url'       => $gps->url,
                    'estado'    => $config ? ($config->estado ? 'Activo' : 'Inactivo') : 'No configurado',
                    'config_id' => $config?->id,
                    'deleted_at' => $gps->deleted_at,
                ];
            });
    }


    public function store(Request $request)
    {
        return GpsCompany::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $company = GpsCompany::findOrFail($id);

        // Descarta campos que no deben actualizarse
        $data = $request->except(['_token', '_method', 'id']);

        $company->update($data);

        return response()->json(['success' => true, 'data' => $company]);
    }


    public function destroy($id)
    {
        $company = GpsCompany::findOrFail($id);
        $company->delete();
        return response()->json(['success' => true]);
    }

    public function restore($id)
    {
        $company = GpsCompany::withTrashed()->findOrFail($id);
        $company->restore();

        return response()->json(['success' => true]);
    }

    public function testGpsApi()
    {

        $toTest = 'JimiGps';

        $empresaId = auth()->user()->id_empresa;

        switch ($toTest) {
            case 'LegoGPS':
                $credenciales = CommonGpsTrait::getAuthenticationCredentials('AECC890930E41', 3);
                $data = ($credenciales['success']) ? LegoGpsTrait::getLocation($credenciales['accessAccount']) : [];
                break;
            case 'JimiGps':
                //Datos de dispositivo por IMEI
                $adicionales['imeis'] = '356153592336785'; //869066061506169 y  356153592336785
                $credenciales = JimiGpsTrait::getAuthenticationCredentials('XAXA890930E41');

                $data = JimiGpsTrait::callGpsApi('jimi.device.location.get', $credenciales['accessAccount'], $adicionales);
                break;
            case 'TrackerGps':
                $credenciales = CommonGpsTrait::getAuthenticationCredentials('AECC890930E41', 4);
                $data = GpsTrackerMXTrait::getMutiDevicePosition($credenciales['accessAccount']);
                break;
            case 'BeyondGps':
                $credenciales = CommonGpsTrait::getAuthenticationCredentials('AECC890930E41', 5);
                $data = BeyondGPSTrait::getLocation($credenciales['accessAccount']['user'], $credenciales['accessAccount']['password'], $credenciales['accessAccount']['endpoint']);
                break;
            case 'WialonGps':
                $credenciales = CommonGpsTrait::getAuthenticationCredentials('AECC890930E41', 6);
                //    return $credenciales;
                $data = WialonGpsTrait::getLocation($credenciales['accessAccount']['token'], $credenciales['accessAccount']['SID']);
                break;
            default:
                $data = "Bad GPS Config";
        }

        return $data;
    }


    public function setConfigEquipo(Request $r)
    {
        try {

            $equipo = Equipo::findOrFail($r->equipo_id);


            $credenciales = collect($r->cuentaConfig)
                ->pluck('valor', 'field')
                ->toArray();


            //  dd($credenciales, $r->cuentaConfig);

            $mensajeError = null;
            $resp = null;
            $token = null;

            /* ================= VALIDACIÓN ================= */
            switch ((int) $r->gps_company_id) {
                case 1: // Global GPS
                    $token = GlobalGpsTrait::getAccessToken(
                        $credenciales['appkey'] ?? null,
                        $credenciales['account'] ?? null
                    );
                    $resp = $token;
                    break;

                case 2: // Jimi
                    $token = JimiGpsTrait::getGpsAccessToken(
                        auth()->user()->id_empresa,
                        $credenciales
                    );
                    $resp = $token;
                    break;

                case 3: // Lego
                    $token = LegoGpsTrait::validateOwner($credenciales);
                    $resp = $token;
                    break;

                case 4: // Tracker
                    $token = GpsTrackerMXTrait::getGpsAccessToken(
                        auth()->user()->id_empresa,
                        $credenciales
                    );
                    $resp = $token;
                    break;


                case 5: // Beyond GPS

                    $response = BeyondGPSTrait::getLocation(
                        $credenciales['user'] ?? null,
                        $credenciales['password'] ?? null,
                        $credenciales['endpoint'] ?? null
                    );

                    $resp = $response;

                    if (!$response->success) {
                        return response()->json([
                            'success' => false,
                            'message' => $response->message ?? 'Error en conexión'
                        ]);
                    }

                    $data = $response->data;

                    if (!($data['success'] ?? false)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Proveedor GPS respondió sin éxito'
                        ]);
                    }

                    if (empty($data['events'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Sin eventos / sin datos de ubicación'
                        ]);
                    }

                    $token = true;
                    break;

                case 6: // Wialon

                    $responseWialon = WialonGpsTrait::getLocation(
                        $credenciales['token'] ?? null,
                        $credenciales['SID'] ?? null
                    );

                    $resp = $responseWialon;

                    if (
                        !$responseWialon->success ||
                        !is_array($responseWialon->data) ||
                        count($responseWialon->data) === 0
                    ) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Error al validar credenciales con Wialon'
                        ]);
                    }

                    $token = true;
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Proveedor GPS no soportado'
                    ]);
            }


            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ]);
            }



            // dd($equipo, $credenciales);

            $equipo->update([
                'gps_company_id'   => $r->gps_company_id,
                'usar_config_global' => 0,
                'credenciales_gps' => Crypt::encryptString(
                    json_encode($credenciales)
                ),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuración guardada correctamente',
                'data' => $resp
            ]);

        } catch (\Throwable $e) {

            Log::error('Error setConfigEquipo', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al configurar GPS',
                'erradmin' => $e->getMessage()
            ], 500);
        }
    }

}
