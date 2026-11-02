<?php

namespace App\Http\Controllers;

use App\Models\Empresas;
use App\Models\GpsCompany;
use App\Models\User;
use App\Services\MepService;
use App\Services\UbicacionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class MepController extends Controller
{
    protected MepService $mepService;
    protected UbicacionService $ubicacionService;

    public function __construct(MepService $mepService, UbicacionService $ubicacionService)
    {
        $this->mepService = $mepService;
        $this->ubicacionService = $ubicacionService;
    }

    public function index()
    {
        $empresas = Empresas::get();
        $gpsCompanies = GpsCompany::orderBy('nombre')->get();
        return view('mep.viajes.index', compact('empresas', 'gpsCompanies'));
    }

    public function getCatalogosMep(Request $request)
    {
        $data = $this->mepService->getCatalogosMep(auth()->user());
        return response()->json($data);
    }

    public function getCotizacionesList()
    {
        $cotizaciones = $this->mepService->getCotizacionesList(auth()->user()->id_empresa);
        return response()->json(['list' => $cotizaciones]);
    }

    public function getCotizacionesFinalizadas()
    {
        $cotizaciones = $this->mepService->getCotizacionesFinalizadas(auth()->user()->id_empresa);
        return response()->json(['list' => $cotizaciones]);
    }

    public function validarEquiposEmpresa($numUnidad, $imei, $placas, $serie, $provGps, $tipoEquipo, $idEquipoUnico = null)
    {
        return $this->mepService->validarEquiposEmpresa(
            $numUnidad,
            $imei,
            $placas,
            $serie,
            $provGps,
            $tipoEquipo,
            $idEquipoUnico,
            auth()->user()
        );
    }

    public function asignarOperador(Request $r)
    {
        $res = $this->mepService->asignarOperador($r->formData, $r->input('idContenedor'), auth()->user());
        return response()->json($res);
    }

    public function verAsignacion(Request $request)
    {
        $asignacion = $this->mepService->verAsignacion($request->idContenedor);
        return response()->json($asignacion);
    }

    public function parseFecha($fecha)
    {
        return $this->mepService->parseFecha($fecha);
    }

    public function getUbicacionesPlanear(Request $request)
    {
        $equiposIds = array_values(array_filter((array) ($request->equipos ?? [])));
        if (empty($equiposIds)) {
            return response()->json([]);
        }

        $equipos = DB::table('equipos as e')
            ->leftJoin('gps_company', 'e.gps_company_id', '=', 'gps_company.id')
            ->select(
                'e.id',
                'e.tipo',
                'e.id_equipo',
                'e.placas',
                'e.imei',
                'e.gps_company_id',
                'e.usar_config_global',
                'e.credenciales_gps',
                'e.user_id',
                'e.id_empresa',
                'gps_company.url as tipogps'
            )
            ->whereIn('e.id', $equiposIds)
            ->get();

        $itemsGps = [];
        $resultados = [];

        foreach ($equipos as $equipo) {
            try {
                if (empty($equipo->tipogps)) {
                    $resultados[] = [
                        'id' => $equipo->id,
                        'equipo' => $equipo->id_equipo,
                        'status' => false,
                        'messageAp' => 'Compañía GPS no configurada',
                        'ubicacion' => null,
                    ];
                    continue;
                }

                $credenciales = [];

                if ($equipo->usar_config_global == 0) {
                    if (!empty($equipo->credenciales_gps)) {
                        try {
                            $rawCreds = json_decode(Crypt::decryptString($equipo->credenciales_gps), true) ?? [];
                        } catch (\Throwable $e) {
                            $rawCreds = json_decode($equipo->credenciales_gps, true) ?? [];
                        }
                        if (is_array($rawCreds)) {
                            if (isset($rawCreds[0]) && is_array($rawCreds[0]) && array_key_exists('field', $rawCreds[0])) {
                                $credenciales = collect($rawCreds)->pluck('valor', 'field')->toArray();
                            } else {
                                $credenciales = $rawCreds;
                            }
                        }
                    }
                } else {
                    $proveedorIds = [];
                    $userActual = User::find(auth()->id());
                    if ($userActual) {
                        $proveedorIds = $userActual->proveedores()->pluck('proveedor_id')->toArray();
                    }

                    if (!empty($equipo->user_id)) {
                        $userEquipo = User::find($equipo->user_id);
                        if ($userEquipo) {
                            $equipoProvIds = $userEquipo->proveedores()->pluck('proveedor_id')->toArray();
                            $proveedorIds = array_unique(array_merge($proveedorIds, $equipoProvIds));
                        }
                    }

                    $credencialesGlobal = null;

                    if (!empty($proveedorIds)) {
                        $credencialesGlobal = DB::table('gps_company_proveedores')
                            ->whereIn('id_proveedor', $proveedorIds)
                            ->where('id_gps_company', $equipo->gps_company_id)
                            ->where('estado', 1)
                            ->value('account_info');
                    }

                    if (!empty($credencialesGlobal)) {
                        try {
                            $raw = json_decode(Crypt::decryptString($credencialesGlobal), true) ?? [];
                        } catch (\Throwable $e) {
                            $raw = json_decode($credencialesGlobal, true) ?? [];
                        }
                        if (is_array($raw)) {
                            if (isset($raw[0]) && is_array($raw[0]) && array_key_exists('field', $raw[0])) {
                                $credenciales = collect($raw)->pluck('valor', 'field')->toArray();
                            } else {
                                $credenciales = $raw;
                            }
                        }
                    }
                }

                if (empty($credenciales)) {
                    $resultados[] = [
                        'id' => $equipo->id,
                        'equipo' => $equipo->id_equipo,
                        'status' => false,
                        'messageAp' => 'Sin credenciales GPS',
                        'ubicacion' => null,
                    ];
                    continue;
                }

                $itemsGps[] = [
                    'id' => $equipo->id,
                    'equipo' => $equipo->id_equipo,
                    'imei' => $equipo->imei,
                    'placas' => $equipo->placas,
                    'tipoGps' => $equipo->tipogps,
                    'tipo' => $equipo->tipo,
                    'gps_company_id' => $equipo->gps_company_id,
                    'credenciales' => $credenciales,
                ];

            } catch (\Throwable $e) {
                $resultados[] = [
                    'id' => $equipo->id,
                    'equipo' => $equipo->id_equipo,
                    'status' => false,
                    'messageAp' => $e->getMessage(),
                    'ubicacion' => null,
                ];
            }
        }

        if (!empty($itemsGps)) {
            $responseGps = $this->ubicacionService->consultarEquiposGps($itemsGps);
            $resultados = array_merge($resultados, $responseGps);
        }

        return response()->json($resultados);
    }
}
