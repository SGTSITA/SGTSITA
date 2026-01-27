<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresas;
use App\Models\Proveedor;
use App\Models\Asignaciones;
use App\Models\Cotizaciones;
use App\Models\Equipo;
use App\Models\Operador;
use App\Models\GpsCompany;
use App\Models\DocumCotizacion;
use App\Traits\CommonTrait as common;

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
        $unidades = Equipo::where('id_empresa', auth()->user()->id_empresa)->get();
        $operadores = Operador::where('id_empresa', auth()->user()->id_empresa)->get();

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
        $unidad = Equipo::where('id_empresa', auth()->user()->id_empresa)->where('id_equipo', $numUnidad);
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

        //Verificar operador
        $operador = Operador::where('id_empresa', auth()->user()->id_empresa)->where('nombre', $formData['txtOperador']);
        if (!$operador->exists()) {
            $operador = new Operador();
            $operador->nombre = $formData['txtOperador'];
            $operador->telefono = $formData['txtTelefono'];
            $operador->save();
        } else {
            $operador = $operador->first();
            $operador->telefono = $formData['txtTelefono'];
            $operador->update();
        }

        $idOperador = $operador->id;
        $numeroUnidad = strtoupper(trim($formData['txtNumUnidad']));
        //TractoCamion
        // $idUnidad = self::validarEquiposEmpresa($formData['txtNumUnidad'], $formData['txtImei'],$formData['txtPlacas'],$formData['txtSerie'],$formData['selectGPS'],'Tractos / Camiones');
        $unidadQuery = Equipo::where('id_empresa', auth()->user()->id_empresa)->where('id_equipo', $numeroUnidad);


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
        $asignacion = Asignaciones::where('id_contenedor', $idContenedor);
        $fechaI =  date('Y-m-d');
        $fechaF =  date('Y-m-d');


        if ($planearViaje == 1) {
            $fechaI = $formData['txtFechaInicio'];
            $fechaF = $formData['txtFechaFinal'];

            // dd($fechaI);
        }
        $fechaI  = common::TransformaFecha($fechaI);
        $fechaF = common::TransformaFecha($fechaF);




        $TituloResponse = 'Se ha realizado la asignacion correctamente';
        $MessageResponse = '';

        $proveedorid = $formData['cmbProveedor'];



        if ($asignacion->exists()) {
            // $asignacion1 = $asignacion->first();
            $asignacion->update([
                "id_operador" => $idOperador,
                "id_camion" => $idunidad,
                "id_chasis" => $idChasisA,
                "id_chasis2" => $idChasisB,
                "fecha_inicio" => $fechaI,
                "fecha_fin" => $fechaF. ' 23:00:00',
                "fehca_inicio_guard" => $fechaI,
                "fehca_fin_guard" => $fechaF. ' 23:00:00',
                "id_proveedor" => $proveedorid,
                'tipo_contrato' => 'Subcontratado', //mep siempre sera subcontratado , aun asi tenga unidad , camion y id proveedor
            ]);

            $TituloResponse = 'Actualizado correctamente';
            $MessageResponse = 'Los datos fueron modificados con exito';


        } else {
            $fecha = date('Y-m-d');
            $asignacion = new Asignaciones();
            $asignacion->id_empresa = auth()->user()->id_empresa;
            $asignacion->id_contenedor = $idContenedor;
            $asignacion->id_camion = $idunidad;
            $asignacion->id_chasis = $idChasisA;
            $asignacion->id_chasis2 = $idChasisB;
            $asignacion->id_operador = $idOperador;
            $asignacion->fecha_inicio  = $fechaI ;
            $asignacion->fecha_fin = $fechaF. ' 23:00:00';
            $asignacion->fehca_inicio_guard =  $fechaI ;
            $asignacion->fehca_fin_guard = $fechaF. ' 23:00:00';
            $asignacion->id_proveedor = $proveedorid;
            $asignacion->tipo_contrato = 'Subcontratado'; //mep siempre sera subcontratado , aun asi tenga unidad , camion y id proveedor
            $asignacion->save();


        }
        //  dd($asignacion, $proveedorid);

        if ($planearViaje == 1) { // validar desde el form
            //dd($planearViaje);
            $contenedor = DocumCotizacion::where('id', $idContenedor)->first(); //buscamos la relacion no siempre sera el mismo id
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
        $asignacion = Asignaciones::with(['Camion', 'Chasis', 'Chasis2','Operador',
                'Contenedor' => function ($q) {
                    $q->select('id', 'id_cotizacion');
                },
            'Contenedor.Cotizacion' => function ($q) {
                $q->select('id', 'estatus', 'origen', 'destino', 'estatus_planeacion');
            }

        ])->where('id_contenedor', $request->idContenedor)->get();
        return $asignacion;

    }
}
