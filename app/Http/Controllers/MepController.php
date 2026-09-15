<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresas;
use App\Models\Proveedor;
use App\Models\Asignaciones;
use App\Models\Cotizaciones;
use App\Models\Equipo;
use App\Models\User;
use App\Models\Operador;
use App\Models\GpsCompany;
use App\Models\DocumCotizacion;
use App\Traits\CommonTrait as common;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\UbicacionService;
use Illuminate\Support\Facades\Crypt;

class MepController extends Controller
{
    public function index()
    {
        $empresas = Empresas::get();

        $gpsCompanies = GpsCompany::orderBy('nombre')->get();
        return view('mep.viajes.index', compact('empresas', 'gpsCompanies'));
    }

    public function getCatalogosMep(Request $request)
    {
        $userProveedores = User::find(auth()->user()->id);

        $proveedorIds = $userProveedores->proveedores()->pluck('proveedor_id');


        $usuariosRelacionados = User::whereHas('proveedores', function ($q) use ($proveedorIds) {
            $q->whereIn('proveedor_id', $proveedorIds);
        })->pluck('id');

        $unidades = Equipo::whereIn('user_id', $usuariosRelacionados)
             ->where('equipos.activo', true)
         ->orderBy('equipos.created_at', 'desc')
                 ->get();
        //$unidades = Equipo::where('id_empresa', auth()->user()->id_empresa)->where('user_id', auth()->user()->id)->get();
        // $operadores = Operador::where('id_empresa', auth()->user()->id_empresa)->get();
        $operadores = Operador::whereHas('proveedores', function ($q) use ($proveedorIds) {
            $q->whereIn('proveedor_id', $proveedorIds)
              ->where('proveedor_operador.estado', 1);
        })->get();
        return response()->json(["TMensaje" => "success", "unidades" => $unidades, "operadores" => $operadores]);
    }

    public function getCotizacionesList()
    {
        $empresa = Empresas::where('id', auth()->user()->id_empresa)->first();
        $proveedor = Proveedor::catalogoPrincipal()->where('rfc', $empresa->rfc)->pluck('id');
        $contenedoresAsignados = Asignaciones::whereIn('id_proveedor', $proveedor)->get()->pluck('id_contenedor');

        $cotizaciones = Cotizaciones::whereIn('id', $contenedoresAsignados)
            ->where('estatus', '=', 'Aprobada')
            ->where('estatus_planeacion', '=', 1)
            ->where('jerarquia', "!=", 'Secundario')
            ->orderBy('created_at', 'desc')
            ->with(['cliente', 'DocCotizacion.Asignaciones'])
            ->get()
            ->map(function ($cotizacion) {
                $contenedor = $cotizacion->DocCotizacion ? $cotizacion->DocCotizacion->num_contenedor : 'N/A';

                // Si es tipo 'Full', buscamos la secundaria para obtener su contenedor
                if (!is_null($cotizacion->referencia_full)) {
                    $secundaria = Cotizaciones::where('referencia_full', $cotizacion->referencia_full)
                        ->where('jerarquia', 'Secundario')
                        ->with('DocCotizacion.Asignaciones')
                        ->first();

                    if ($secundaria && $secundaria->DocCotizacion) {
                        $contenedor .= ' ' . $secundaria->DocCotizacion->num_contenedor;
                    }
                }

                return [
                    'id' => $cotizacion->id,
                    'cliente' => $cotizacion->cliente ? $cotizacion->cliente->nombre : 'N/A',
                    'origen' => $cotizacion->origen,
                    'destino' => $cotizacion->destino,
                    'contenedor' => $contenedor,
                    'estatus' => $cotizacion->estatus,
                    'coordenadas' => optional($cotizacion->DocCotizacion)->Asignaciones ? 'Ver' : '',
                    'id_asignacion' => optional($cotizacion->DocCotizacion)->Asignaciones->id ?? null,
                    'edit_url' => route('edit.cotizaciones', $cotizacion->id),
                    'tipo' => (!is_null($cotizacion->referencia_full)) ? 'Full' : 'Sencillo'
                ];
            });

        return response()->json(['list' => $cotizaciones]);
    }

    public function getCotizacionesFinalizadas()
    {
        $empresa = Empresas::where('id', auth()->user()->id_empresa)->first();
        $proveedor = Proveedor::catalogoPrincipal()->where('rfc', $empresa->rfc)->pluck('id');
        $contenedoresAsignados = Asignaciones::whereIn('id_proveedor', $proveedor)->get()->pluck('id_contenedor');

        $cotizaciones = Cotizaciones::whereIn('id', $contenedoresAsignados)
            ->where('estatus', 'Finalizado')
            ->where('jerarquia', "!=", 'Secundario')
            ->orderBy('created_at', 'desc')
            ->with([
                'Cliente',
                'DocCotizacion.Asignaciones'
            ])
            ->get()
            ->map(function ($cotizacion) {
                $contenedor = $cotizacion->DocCotizacion ? $cotizacion->DocCotizacion->num_contenedor : 'N/A';

                // Si es tipo 'Full', buscamos la secundaria para obtener su contenedor
                if (!is_null($cotizacion->referencia_full)) {
                    $secundaria = Cotizaciones::where('referencia_full', $cotizacion->referencia_full)
                        ->where('jerarquia', 'Secundario')
                        ->with('DocCotizacion.Asignaciones')
                        ->first();

                    if ($secundaria && $secundaria->DocCotizacion) {
                        $contenedor .= ' ' . $secundaria->DocCotizacion->num_contenedor;
                    }
                }

                return [
                    'id' => $cotizacion->id,
                    'cliente' => $cotizacion->Cliente ? $cotizacion->Cliente->nombre : 'N/A',
                    'origen' => $cotizacion->origen,
                    'destino' => $cotizacion->destino,
                    'contenedor' => $contenedor,
                    'estatus' => $cotizacion->estatus,
                    'coordenadas' => optional($cotizacion->DocCotizacion)->Asignaciones ? 'Ver' : 'N/A',
                    'id_asignacion' => optional($cotizacion->DocCotizacion)->Asignaciones->id ?? null,
                    'edit_url' => route('edit.cotizaciones', $cotizacion->id),
                    'pdf_url' => route('pdf.cotizaciones', $cotizacion->id),
                    'tipo' => (!is_null($cotizacion->referencia_full)) ? 'Full' : 'Sencillo'
                ];
            });

        return response()->json(['list' => $cotizaciones]);
    }

    public function validarEquiposEmpresa($numUnidad, $imei, $placas, $serie, $provGps, $tipoEquipo)
    {
        if (empty($numUnidad)) {
            return null; // Si no se proporciona número de unidad, no hacemos nada
        }

        $unidad = Equipo::where('id_empresa', auth()->user()->id_empresa)->where('id_equipo', $numUnidad)->where('user_id', auth()->user()->id);


        if (!$unidad->exists()) {
            $unidad = new Equipo();
            $unidad->id_equipo = $numUnidad;
            $unidad->imei = $imei;
            $unidad->placas = $placas;
            $unidad->num_serie = $serie;
            $unidad->gps_company_id = $provGps;
            $unidad->tipo = $tipoEquipo;
            $unidad->user_id = auth()->user()->id;
            $unidad->save();
        } else {
            $unidad = $unidad->first();
            $unidad->imei = $imei;
            $unidad->placas = $placas;
            $unidad->num_serie = $serie;
            $unidad->gps_company_id = $provGps;
            $unidad->update();
        }

        return $unidad->id;
    }

    public function asignarOperador(Request $r)
    {
        $formData = $r->formData;
        $planearViaje = $formData['planear'];

        $idOperador = $formData['mepOperador'] ?? null;


        $user = User::find(auth()->user()->id);

        $proveedorIds = $user->proveedores()->pluck('proveedor_id');
        $id_empresa = auth()->user()->id_empresa;
        if ($idOperador) {

            $operador = Operador::find($idOperador);

            if ($operador) {
                $operador->nombre = $formData['txtOperador'];
                $operador->telefono = $formData['txtTelefono'];
                $operador->update();
            }
        } else {

            $operador = new Operador();
            $operador->nombre = $formData['txtOperador'];
            $operador->telefono = $formData['txtTelefono'];
            $operador->id_empresa = $id_empresa;
            $operador->save();
        }

        $idOperador = $operador->id;


        foreach ($proveedorIds as $proveedorId) {

            $existeRelacion = DB::table('proveedor_operador')
                ->where('operador_id', $idOperador)
                ->where('proveedor_id', $proveedorId)
                ->exists();

            if (!$existeRelacion) {
                DB::table('proveedor_operador')->insert([
                    'operador_id' => $idOperador,
                    'proveedor_id' => $proveedorId,
                    'empresa_id' => $id_empresa,
                    'estado' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }


        $numeroUnidad = strtoupper(trim($formData['txtNumUnidad']));
        //TractoCamion
        // $idUnidad = self::validarEquiposEmpresa($formData['txtNumUnidad'], $formData['txtImei'],$formData['txtPlacas'],$formData['txtSerie'],$formData['selectGPS'],'Tractos / Camiones');
        $unidadQuery = Equipo::where('id_empresa', auth()->user()->id_empresa)->where('id_equipo', $numeroUnidad)->where('user_id', auth()->user()->id);


        if (!$unidadQuery->exists()) {

            $unidad = new Equipo();
            $unidad->id_empresa = auth()->user()->id_empresa;
            $unidad->id_equipo = $numeroUnidad;
            $unidad->imei = strtoupper(trim($formData['txtImei']));
            $unidad->placas = strtoupper(trim($formData['txtPlacas']));
            $unidad->num_serie = strtoupper(trim($formData['txtSerie']));
            $unidad->gps_company_id = $formData['selectGPS'];
            $unidad->tipo = 'Tractos / Camiones';
            $unidad->user_id = auth()->user()->id;
            $unidad->save();

        } else {

            $unidad = $unidadQuery->first();
            $unidad->imei = strtoupper(trim($formData['txtImei']));
            $unidad->placas = strtoupper(trim($formData['txtPlacas']));
            $unidad->num_serie = strtoupper(trim($formData['txtSerie']));
            $unidad->gps_company_id = $formData['selectGPS'];
            $unidad->update();
        }

        $idunidad = $unidad->id;
        //Chasis / Plataforma
        $idChasisA = self::validarEquiposEmpresa($formData['txtNumChasisA'], $formData['txtImeiChasisA'], $formData['txtPlacasA'], '', $formData['selectChasisAGPS'], 'Chasis / Plataforma');

        $idChasisB = self::validarEquiposEmpresa($formData['txtNumChasisB'], $formData['txtImeiChasisB'], $formData['txtPlacasB'], '', $formData['selectChasisBGPS'], 'Chasis / Plataforma');


        $idContenedor = $r->input('idContenedor');

          $documento= DocumCotizacion::where('id_cotizacion', $idContenedor)->first(); // mandan asi y el valor es cotizacion id
        $asignacion = Asignaciones::where('id_contenedor', $documento->id)->first();

        $fechaI = $formData['txtFechaInicio'] ?? null;
        $fechaF = $formData['txtFechaFinal'] ?? null;


        if (empty($fechaI)) {
            $fechaBaseInicio = $asignacion?->fecha_inicio ?? now();
            $fechaInicio = Carbon::parse($fechaBaseInicio)->startOfDay();
        } else {
            $fechaInicio = $this->parseFecha($fechaI)->startOfDay();
        }


        if (empty($fechaF)) {
            $fechaBaseFin = $asignacion?->fecha_fin ?? now();
            $fechaFin = Carbon::parse($fechaBaseFin)->endOfDay();
        } else {
            $fechaFin = $this->parseFecha($fechaF)->endOfDay();
        }


        // $fechaInicio = Carbon::parse($fechaBaseInicio)->startOfDay(); // 00:00:00
        //$fechaFin    = Carbon::parse($fechaBaseFin)->endOfDay();      // 23:59:59

        //dd($fechaInicio, $fechaFin);


        $TituloResponse = 'Se ha realizado la asignacion correctamente';
        $MessageResponse = '';

        $proveedorid = $formData['cmbProveedor'];

        $DocCotizacion = DocumCotizacion::where('id',$documento->id)->first();

        if ($asignacion) {

            $asignacionModel = $asignacion;

            $asignacionModel->update([
                "id_operador" => $idOperador,
                "id_camion" => $idunidad,
                "id_chasis" => $idChasisA,
                "id_chasis2" => $idChasisB,
                "fecha_inicio" => $fechaInicio,
                "fecha_fin" => $fechaFin,
                "fehca_inicio_guard" => $fechaInicio,
                "fehca_fin_guard" => $fechaFin,
                "id_proveedor" => $proveedorid,
                'tipo_contrato' => 'Subcontratado',
            ]);


            $TituloResponse = 'Actualizado correctamente';
            $MessageResponse = 'Los datos fueron modificados con exito';


        } else {
            $fecha = date('Y-m-d');
            $asignacion = new Asignaciones();
            $asignacion->id_empresa = $DocCotizacion?->Cotizacion?->id_empresa ?? auth()->user()->id_empresa;
            $asignacion->id_contenedor = $documento->id;
            $asignacion->id_camion = $idunidad;
            $asignacion->id_chasis = $idChasisA;
            $asignacion->id_chasis2 = $idChasisB;
            $asignacion->id_operador = $idOperador;
            $asignacion->fecha_inicio  = $fechaInicio ;
            $asignacion->fecha_fin = $fechaFin;
            $asignacion->fehca_inicio_guard =   $fechaInicio;
            $asignacion->fehca_fin_guard = $fechaFin;
            $asignacion->id_proveedor = $proveedorid;
            $asignacion->tipo_contrato = 'Subcontratado'; //mep siempre sera subcontratado , aun asi tenga unidad , camion y id proveedor
            $asignacion->save();


        }
        //  dd($asignacion, $proveedorid);

        if ($planearViaje == 1) { // validar desde el form
            //dd($planearViaje);
            $contenedor = DocumCotizacion::where('id',$documento->id)->first(); //buscamos la relacion no siempre sera el mismo id
            Cotizaciones::where('id', $contenedor->id_cotizacion)->update(['estatus_planeacion' => 1]);
            $TituloResponse = 'Datos guardados correctamente';
            $MessageResponse = 'Viaje planeado con exito';
        }
        //$idAsignacion = $contenedor['id_asignacion'];

        //Asignaciones::where('id',$idAsignacion)->update(["id_operador"=>$idOperador,"id_camion" => $idunidad]);
        return response()->json(["TMensaje" => "success", "Titulo" =>  $TituloResponse,"Mensaje" => $MessageResponse]);
    }

    public function verAsignacion(Request $request)
    {

    $documento= DocumCotizacion::where('id_cotizacion', $request->idContenedor)->first(); // mandan asi y el valor es cotizacion id

        $asignacion = Asignaciones::with(['Camion', 'Chasis', 'Chasis2','Operador',
                'Contenedor' => function ($q) {
                    $q->select('id', 'id_cotizacion');
                },
            'Contenedor.Cotizacion' => function ($q) {
                $q->select('id', 'estatus', 'origen', 'destino', 'estatus_planeacion');
            }

        ])->where('id_contenedor', $documento->id)->get();
        return $asignacion;

    }
    public function parseFecha($fecha)
    {
        try {
            if (str_contains($fecha, '/')) {
                return Carbon::createFromFormat('d/m/Y', $fecha);
            }
            return Carbon::createFromFormat('Y-m-d', $fecha);
        } catch (\Exception $e) {
            return Carbon::parse($fecha); // fallback
        }
    }


    public function getUbicacionesPlanear(Request $request, UbicacionService $ubicacionService)
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
                            $credenciales = json_decode(Crypt::decryptString($equipo->credenciales_gps), true) ?? [];
                        } catch (\Throwable $e) {
                            $credenciales = json_decode($equipo->credenciales_gps, true) ?? [];
                        }
                    }
                } else {
                    // 1. Buscar si el usuario actual tiene proveedor
                    $userActual = User::find(auth()->id());
                    $proveedorIds = $userActual ? $userActual->proveedores()->pluck('proveedor_id')->toArray() : [];

                    // 2. Si el usuario actual es admin o no tiene proveedor asignado, buscar por el creador del equipo
                    if (empty($proveedorIds) && !empty($equipo->user_id)) {
                        $userEquipo = User::find($equipo->user_id);
                        if ($userEquipo) {
                            $proveedorIds = $userEquipo->proveedores()->pluck('proveedor_id')->toArray();
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

                    if (!$credencialesGlobal && !empty(auth()->user()->id_empresa)) {
                        $credencialesGlobal = DB::table('gps_company_proveedores')
                            ->where('id_empresa', auth()->user()->id_empresa)
                            ->where('id_gps_company', $equipo->gps_company_id)
                            ->where('estado', 1)
                            ->value('account_info');
                    }

                    if (!$credencialesGlobal && !empty($equipo->id_empresa)) {
                        $credencialesGlobal = DB::table('gps_company_proveedores')
                            ->where('id_empresa', $equipo->id_empresa)
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
                        $credenciales = collect($raw)->pluck('valor', 'field')->toArray();
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
            $responseGps = $ubicacionService->consultarEquiposGps($itemsGps);
            $resultados = array_merge($resultados, $responseGps);
        }

        return response()->json($resultados);
    }
}
