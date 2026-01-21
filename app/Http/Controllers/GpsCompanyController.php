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
use App\Traits\SISGPStrait;

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


        $config = GpsCompanyProveedor::where('id_empresa', $empresaId)
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

        $proveedorId = $user->proveedores()
            ->value('proveedor_id');


        $credenciales = collect($r->account)
            ->pluck('valor', 'field')
            ->toArray();

        /* ================= VALIDACIÓN ================= */

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

            case 5: // Beyond GPS
                $token = BeyondGPSTrait::validateOwner($credenciales);
                break;

            case 6: // Wialon GPS
                $token = true;
                break;

            case 7: //SIS GPS
                $response = SISGPStrait::sisValidarCredenciales(
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
                "Mensaje"  => "No se pudo validar el acceso al proveedor GPS",
                "TMensaje" => "warning"
            ]);
        }


        $config = GpsCompanyProveedor::updateOrCreate(
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
        // dd($config);

        return response()->json([
            "Titulo"   => "Correcto",
            "Mensaje"  => "Se guardó la configuración del servicio GPS",
            "TMensaje" => "success",
            "token"    => $token
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
                    $q->where('id_empresa', $empresaId)
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
                $data = BeyondGPSTrait::getLocation($credenciales['accessAccount']['user'], $credenciales['accessAccount']['password']);
                break;
            case 'WialonGps':
                $credenciales = CommonGpsTrait::getAuthenticationCredentials('AECC890930E41', 6);
                //    return $credenciales;
                $data = WialonGpsTrait::getLocation($credenciales['accessAccount']['token']);
                break;
            default:
                $data = "Bad GPS Config";
        }

        return $data;
    }

}
