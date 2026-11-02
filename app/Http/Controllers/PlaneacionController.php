<?php

namespace App\Http\Controllers;

use App\Http\Requests\AsignarPlaneacionRequest;
use App\Models\Asignaciones;
use App\Models\Bancos;
use App\Models\Client;
use App\Models\Coordenadas;
use App\Models\Cotizaciones;
use App\Models\DineroContenedor;
use App\Models\DocumCotizacion;
use App\Models\Equipo;
use App\Models\Operador;
use App\Models\Proveedor;
use App\Services\BancosService;
use App\Services\PlaneacionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlaneacionController extends Controller
{
    protected PlaneacionService $planeacionService;
    protected BancosService $bancosService;

    public function __construct(PlaneacionService $planeacionService, BancosService $bancosService)
    {
        $this->planeacionService = $planeacionService;
        $this->bancosService = $bancosService;
    }

    public function index()
    {
        return view('planeacion.index');
    }

    public function programarViaje()
    {
        $fecha = Carbon::now()->format('Y-m-d');
        $idEmpresa = Auth::user()->id_empresa;

        $proveedores = Proveedor::catalogoPrincipal()->where('id_empresa', '=', $idEmpresa)
            ->whereIn('tipo', ['servicio de burreo', 'servicio de viaje'])
            ->get();

        $equipos = Equipo::where('id_empresa', '=', $idEmpresa)->get();
        $operadores = Operador::where('id_empresa', '=', $idEmpresa)->get();
        $bancos = $this->bancosService->getCuentasOption($idEmpresa, $fecha, $fecha, true);

        return view('planeacion.planeacion-step', compact('equipos', 'operadores', 'proveedores', 'bancos'));
    }

    public function anularPlaneacion(Request $request)
    {
        try {
            $idContenedor = $request->idContenendor ?? $request->id_contenedor;
            $res = $this->planeacionService->anularPlaneacion(
                $idContenedor,
                $request->numContenedor,
                (int) Auth::user()->id_empresa,
                Auth::user()
            );

            return response()->json($res);
        } catch (\Throwable $t) {
            return response()->json([
                "Titulo" => "Error",
                "Mensaje" => "Error 500: " . $t->getMessage(),
                "TMensaje" => "error"
            ]);
        }
    }

    public function finalizarViaje(Request $request)
    {
        $idContenedor = $request->idContenendor ?? $request->id_contenedor;
        $res = $this->planeacionService->finalizarViaje($idContenedor, (int) Auth::user()->id_empresa);
        $status = $res['code'] ?? 200;

        return response()->json($res, $status);
    }

    public function infoViaje(Request $request)
    {
        $asignaciones = Asignaciones::where('id_contenedor', '=', $request->id)->first();
        $docCotizacion = DocumCotizacion::where('id', '=', $request->id)->first();
        $cotizacion = Cotizaciones::where('id', '=', $docCotizacion->id_cotizacion)->first();

        $documentos = Cotizaciones::query()
            ->where('cotizaciones.id', $cotizacion->id)
            ->join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
            ->leftJoin('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
            ->leftJoin('empresas as em', 'em.id', '=', 'asignaciones.id_empresa')
            ->leftJoin('empresas as emc', 'emc.id', '=', 'cotizaciones.id_empresa')
            ->leftJoin('clients', 'cotizaciones.id_cliente', '=', 'clients.id')
            ->leftJoin('equipos', 'asignaciones.id_camion', '=', 'equipos.id')
            ->leftJoin('equipos as chasis', 'asignaciones.id_chasis', '=', 'chasis.id')
            ->leftJoin('operadores', 'operadores.id', '=', 'asignaciones.id_operador')
            ->leftJoin('proveedores', 'proveedores.id', '=', 'asignaciones.id_proveedor')
            ->select(
                'asignaciones.id as asignacionId',
                'asignaciones.password_temporal',
                'cotizaciones.id',
                'clients.nombre as cliente',
                'docum_cotizacion.num_contenedor',
                'docum_cotizacion.doc_ccp',
                'docum_cotizacion.cima',
                'docum_cotizacion.boleta_liberacion',
                'docum_cotizacion.doda',
                'cotizaciones.referencia_full',
                'cotizaciones.carta_porte',
                'cotizaciones.carta_porte_xml',
                'cotizaciones.img_boleta AS boleta_vacio',
                'docum_cotizacion.doc_eir',
                'asignaciones.id_proveedor',
                'asignaciones.fecha_inicio',
                'asignaciones.fecha_fin',
                'equipos.placas as placas_camion',
                'equipos.id_equipo as id_equipo_camion',
                'equipos.marca as marca_camion',
                'equipos.imei as imei_camion',
                'chasis.id_equipo as id_equipo_chasis',
                'chasis.imei as imei_chasis',
                'asignaciones.tipo_contrato',
                DB::raw("COALESCE(NULLIF(em.nombre, ''), emc.nombre) as Empresa"),
                'operadores.nombre as operador',
                'proveedores.nombre as transportista_nombre',
                'cotizaciones.cp_contacto_entrega',
                DB::raw('COALESCE(operadores.telefono, proveedores.telefono) as beneficiario_telefono')
            )
            ->get();

        $misDocumentos = $documentos->map(function ($cot) {
            $numContenedor = $cot->num_contenedor;
            $docCCP = $cot->doc_ccp;
            $doda = $cot->doda;
            $boletaLiberacion = $cot->boleta_liberacion;
            $cartaPorte = $cot->carta_porte;
            $cartaPorteXml = $cot->carta_porte_xml;
            $boletaVacio = $cot->boleta_vacio;
            $docEir = $cot->doc_eir;
            $tipo = "--";

            if (!is_null($cot->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $cot->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion.Asignaciones')
                    ->first();

                $docCCP = ($docCCP && $secundaria?->DocCotizacion?->doc_ccp) ? true : false;
                $doda = ($doda && $secundaria?->DocCotizacion?->doda) ? true : false;
                $docEir = (!is_null($docEir) && !is_null($secundaria?->DocCotizacion?->doc_eir)) ? true : false;
                $boletaLiberacion = ($boletaLiberacion && $secundaria?->DocCotizacion?->boleta_liberacion) ? true : false;
                $cartaPorte = ($cartaPorte && $secundaria?->carta_porte) ? true : false;
                $cartaPorteXml = ($cartaPorteXml && $secundaria?->carta_porte_xml) ? true : false;
                $boletaVacio = ($boletaVacio && $secundaria?->img_boleta) ? true : false;

                if ($secundaria && $secundaria->DocCotizacion) {
                    $numContenedor .= ' / ' . $secundaria->DocCotizacion->num_contenedor;
                }
                $tipo = "Full";
            }

            return [
                "id" => $cot->id,
                "cliente" => $cot->cliente,
                "num_contenedor" => $numContenedor,
                "doc_ccp" => $docCCP,
                "boleta_liberacion" => $boletaLiberacion,
                "doda" => $doda,
                "cima" => $cot->cima,
                "carta_porte" => $cartaPorte,
                "carta_porte_xml" => $cartaPorteXml,
                "boleta_vacio" => $boletaVacio,
                "doc_eir" => $docEir,
                "id_proveedor" => $cot->id_proveedor,
                "fecha_inicio" => $cot->fecha_inicio,
                "fecha_fin" => $cot->fecha_fin,
                "tipo" => $tipo
            ];
        });

        $contenedorStr = $misDocumentos->first()['num_contenedor'] ?? '';
        $camion = $asignaciones ? Equipo::find($asignaciones->id_camion) : null;
        $unidadEco = $camion ? $camion->id_equipo : '';
        $origen = $cotizacion->origen ?? '';
        $direccion = $cotizacion->direccion_entrega ?? '';
        $lat = $cotizacion->latitud ?? '';
        $lng = $cotizacion->longitud ?? '';
        $contacto = $cotizacion->cp_contacto_entrega ?? '';
        $fechaEntregaRaw = $cotizacion->cp_fecha_tentativa_entrega ?: $cotizacion->fecha_entrega;
        $fechaEntrega = $fechaEntregaRaw ? Carbon::parse($fechaEntregaRaw)->format('d/m/Y') : '';
        $horaLlegada = $cotizacion->cp_hora_tentativa_entrega ?? '';
        $comentarios = $cotizacion->cp_comentarios ?? '';
        $mapLink = ($lat && $lng) ? "https://maps.google.com/?q={$lat},{$lng}" : '';

        if ($asignaciones && !empty($asignaciones->mensaje_compartido)) {
            $waText = $asignaciones->mensaje_compartido;
        } else {
            $hora = Carbon::now()->hour;
            $saludo = ($hora >= 6 && $hora < 12) ? "Buenos días" : (($hora >= 12 && $hora < 19) ? "Buenas tardes" : "Buenas noches");
            $nombreOp = ($asignaciones && $asignaciones->Operador) ? $asignaciones->Operador->nombre : '';

            $waText = "{$saludo} " . ($nombreOp ? trim($nombreOp) : "Operador") . ",\n\n";
            $waText .= "Comparto los datos de salida del día de hoy:\n\n";
            $waText .= "{$contenedorStr}" . ($unidadEco ? "-{$unidadEco}" : "") . "\n";
            $waText .= "Puerto / Lugar de salida:\n" . ($origen ?: "") . "\n";
            $waText .= "Domicilio de entrega: " . ($direccion ?: "") . "\n";
            $waText .= "Mapa: " . ($mapLink ?: "") . "\n";
            $waText .= "Contacto: " . ($contacto ?: "") . "\n";
            $waText .= "Fecha de entrega:\n" . ($fechaEntrega ?: "") . "\n";
            $waText .= "Hora de llegada a bodega:\n" . ($horaLlegada ?: "") . "\n";
            $waText .= "Hora de salida: \n";
            $waText .= "Comentarios:\n" . ($comentarios ?: "") . "\n\n";
        }

        return response()->json([
            "documentos" => $misDocumentos,
            "data" => $documentos->first(),
            "wa_text" => $waText,
            "asignacion_id" => $asignaciones?->id
        ]);
    }

    public function guardarMensajeWhatsApp(Request $request)
    {
        $asignacion = Asignaciones::find($request->asignacion_id);
        if ($asignacion) {
            $asignacion->mensaje_compartido = $request->mensaje;
            $asignacion->save();
            return response()->json(['success' => true, 'message' => 'Mensaje guardado correctamente']);
        }
        return response()->json(['success' => false, 'message' => 'Asignación no encontrada'], 404);
    }

    public function initBoard(Request $request)
    {
        $data = $this->planeacionService->initBoard($request->all(), (int) Auth::user()->id_empresa, Auth::user());
        return response()->json($data);
    }

    public function asignacion(AsignarPlaneacionRequest $request)
    {
        $res = $this->planeacionService->asignacion($request->all(), (int) Auth::user()->id_empresa, Auth::user());
        return response()->json($res);
    }

    public function asignacionElemental(Request $request)
    {
        try {
            $res = $this->planeacionService->asignacionElemental($request->all(), (int) Auth::user()->id_empresa, Auth::user());
            return response()->json($res);
        } catch (\Throwable $t) {
            Log::channel('daily')->info('No se guardó planeacion elemental: ' . $t->getMessage());
            return response()->json([
                "TMensaje" => "warning",
                "Titulo" => "No se pudo planear",
                "Mensaje" => "Ocurrio un error mientras procesabamos su solicitud (Elemental)",
                'success' => false,
                'cotizacion_data' => [],
                'ERROR-ADMIN' => $t->getMessage()
            ]);
        }
    }

    public function reprogramarViajes(Request $request)
    {
        try {
            $viajes = json_decode($request->ajustes, true) ?? [];
            $res = $this->planeacionService->reprogramarViajes($viajes, Auth::user());
            return response()->json($res);
        } catch (\Throwable $t) {
            Log::channel('daily')->info('No se guardó Reprogramacion viajes: ' . $t->getMessage());
            return response()->json([
                "TMensaje" => "warning",
                "Titulo" => "No se pudo reprogramar",
                "Mensaje" => "Ocurrio un error mientras procesabamos su solicitud",
                'success' => true
            ]);
        }
    }

    public function cambioFechas(Request $request, $id)
    {
        $res = $this->planeacionService->cambioFechas((int) $id, $request->fecha_inicio, $request->fecha_fin);
        return response()->json($res);
    }

    public function updatePlaneacion(Request $request, $idCotizacion)
    {
        try {
            $res = $this->planeacionService->updatePlaneacion(
                (int) $idCotizacion,
                $request->all(),
                (int) Auth::user()->id_empresa,
                Auth::user()
            );
            return response()->json($res);
        } catch (\Throwable $t) {
            Log::channel('daily')->info('No se actualizó planeacion: ' . $t->getMessage());
            return response()->json([
                "TMensaje" => "error",
                "Titulo" => "No se pudo actualizar",
                "Mensaje" => "Ocurrio un error: " . $t->getMessage(),
                'success' => false
            ]);
        }
    }

    public function editar($id)
    {
        $idEmpresa = Auth::user()->id_empresa;
        $cotizacion = Cotizaciones::with(['Cliente', 'Subcliente', 'DocCotizacion.Asignaciones'])->where('id', $id)->firstOrFail();

        if ($cotizacion->id_empresa != $idEmpresa) {
            abort(403);
        }

        $fecha = Carbon::now()->format('Y-m-d');
        $proveedores = Proveedor::catalogoPrincipal()->where('id_empresa', '=', $idEmpresa)
            ->whereIn('tipo', ['servicio de burreo', 'servicio de viaje'])
            ->get();

        $equipos = Equipo::where('id_empresa', '=', $idEmpresa)->get();
        $operadores = Operador::where('id_empresa', '=', $idEmpresa)->get();
        $bancos = $this->bancosService->getCuentasOption($idEmpresa, $fecha, $fecha, true);

        $asignacion = $cotizacion->DocCotizacion?->Asignaciones;
        $dineroViaje = null;
        $gastosAsignados = collect();

        if ($asignacion) {
            $dineroViaje = DineroContenedor::where('id_contenedor', $cotizacion->DocCotizacion->id)
                ->where('motivo', 'Dinero para viaje')
                ->first();

            $gastosAsignados = \App\Models\Gasto::with(['pagos.cuentaBancaria'])
                ->where(function ($query) use ($asignacion) {
                    $query->where(function ($q) use ($asignacion) {
                        $q->where('origen_legacy_id', $asignacion->id)
                          ->where('origen_legacy', 'like', 'asignacion_planeacion%');
                    })->orWhereHas('vinculos', function ($q) use ($asignacion) {
                        $q->where('vinculable_type', Asignaciones::class)
                          ->where('vinculable_id', $asignacion->id);
                    });
                })
                ->where('estatus', '!=', 'cancelado')
                ->get();
        }

        return view('planeacion.edit', compact(
            'cotizacion',
            'equipos',
            'operadores',
            'bancos',
            'proveedores',
            'dineroViaje',
            'gastosAsignados'
        ) + [
            'modoEdicion' => true,
            'tipo' => optional($cotizacion->DocCotizacion?->Asignaciones)->tipo_contrato ?? 'propio'
        ]);
    }

    public function equipos(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;
        $idEmpresa = Auth::user()->id_empresa;

        if ($fechaInicio && $fechaFin) {
            $camionesAsignados = Asignaciones::where('id_empresa', '=', $idEmpresa)
                ->whereNotNull('id_camion')
                ->where(fn ($q) => $q->where('fecha_inicio', '<=', $fechaFin)->where('fecha_fin', '>=', $fechaInicio))
                ->pluck('id_camion');

            $camionesNoAsignados = Equipo::where('id_empresa', '=', $idEmpresa)
                ->where('tipo', 'LIKE', '%Camiones%')
                ->whereNotIn('id', $camionesAsignados)
                ->get();

            $chasisAsignados = Asignaciones::where('id_empresa', '=', $idEmpresa)
                ->whereNotNull('id_chasis')
                ->where(fn ($q) => $q->where('fecha_inicio', '<=', $fechaFin)->where('fecha_fin', '>=', $fechaInicio))
                ->pluck('id_chasis');

            $chasisNoAsignados = Equipo::where('id_empresa', '=', $idEmpresa)
                ->where('tipo', 'LIKE', '%Chasis%')
                ->whereNotIn('id', $chasisAsignados)
                ->get();

            $dolysAsignados = Asignaciones::where('id_empresa', '=', $idEmpresa)
                ->whereNotNull('id_camion')
                ->where(fn ($q) => $q->where('fecha_inicio', '<=', $fechaFin)->where('fecha_fin', '>=', $fechaInicio))
                ->pluck('id_camion');

            $dolysNoAsignados = Equipo::where('id_empresa', '=', $idEmpresa)
                ->where('tipo', 'LIKE', '%Dolys%')
                ->whereNotIn('id', $dolysAsignados)
                ->get();

            return response()->json([
                "camiones" => $camionesNoAsignados,
                "chasis" => $chasisNoAsignados,
                "dolys" => $dolysNoAsignados,
            ]);
        }

        return response()->json(["camiones" => [], "chasis" => [], "dolys" => []]);
    }

    public function guardarOtrosGastosPlaneacion($r, $num_Contenedor, $idOperadorViaje)
    {
        $filas = is_array($r) ? $r : ($r->filasOtrosGastos ? (is_array($r->filasOtrosGastos) ? $r->filasOtrosGastos : json_decode($r->filasOtrosGastos, true)) : []);
        return $this->planeacionService->guardarOtrosGastosPlaneacion(
            $filas ?: [],
            $num_Contenedor,
            $idOperadorViaje,
            (int) Auth::user()->id_empresa,
            Auth::user()
        );
    }

    public function sincronizarOtrosGastosPlaneacion($r, Asignaciones $asignacion, DocumCotizacion $contenedor, $idOperadorViaje)
    {
        $filas = is_array($r->filasOtrosGastos) ? $r->filasOtrosGastos : json_decode($r->filasOtrosGastos, true);
        return $this->planeacionService->sincronizarOtrosGastosPlaneacion(
            $filas ?: [],
            $asignacion,
            $contenedor,
            $idOperadorViaje,
            (int) Auth::user()->id_empresa,
            Auth::user()
        );
    }

    public function edit_fecha(Request $request)
    {
        $cotizacion = Cotizaciones::find($request->get('urlId'));
        if ($request->get('finzalizar_vieje') != null && $cotizacion) {
            $cotizacion->estatus = $request->get('finzalizar_vieje');
            $cotizacion->save();
        }

        return redirect()->route('index.planeacion');
    }

    public function advance_planeaciones(Request $request)
    {
        $idEmpresa = Auth::user()->id_empresa;
        $cotizaciones = Cotizaciones::where('id_empresa', '=', $idEmpresa)->where('estatus', '=', 'Aprobada')->where('estatus_planeacion', '=', null)->get();
        $numCotizaciones = $cotizaciones->count();
        $proveedores = Proveedor::where('id_empresa', '=', $idEmpresa)
            ->where(fn ($q) => $q->where('tipo', '=', 'servicio de burreo')->orWhere('tipo', '>=', 'servicio de viaje'))
            ->get();

        $equipos = Equipo::where('id_empresa', '=', $idEmpresa)->get();
        $operadores = Operador::where('id_empresa', '=', $idEmpresa)->get();

        $planeaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
            ->whereNotNull('asignaciones.fecha_inicio')->where('asignaciones.id_empresa', '=', $idEmpresa)
            ->select('asignaciones.*', 'docum_cotizacion.num_contenedor')
            ->get();

        $asignaciones = Asignaciones::where('id_empresa', '=', $idEmpresa);
        if ($request->contenedor !== null) {
            $asignaciones = $asignaciones->where('id', $request->contenedor);
        }
        $asignaciones = $asignaciones->first();

        return view('planeacion.index', compact('equipos', 'operadores', 'cotizaciones', 'proveedores', 'numCotizaciones', 'asignaciones', 'planeaciones'));
    }

    public function advance_planeaciones_faltantes(Request $request)
    {
        $idEmpresa = Auth::user()->id_empresa;
        $cotizaciones = Cotizaciones::where('id_empresa', '=', $idEmpresa)->where('estatus', '=', 'Aprobada')->whereNull('estatus_planeacion')->get();
        $numCotizaciones = $cotizaciones->count();
        $proveedores = Proveedor::where('id_empresa', '=', $idEmpresa)
            ->where(fn ($q) => $q->where('tipo', '=', 'servicio de burreo')->orWhere('tipo', '>=', 'servicio de viaje'))
            ->get();

        $equipos = Equipo::where('id_empresa', '=', $idEmpresa)->get();
        $operadores = Operador::where('id_empresa', '=', $idEmpresa)->get();

        $planeaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
            ->whereNotNull('asignaciones.fecha_inicio')->where('asignaciones.id_empresa', '=', $idEmpresa)
            ->select('asignaciones.*', 'docum_cotizacion.num_contenedor')
            ->get();

        $cotizaciones_faltantes = Cotizaciones::where('id_empresa', '=', $idEmpresa)->where('estatus', '=', 'Aprobada')->whereNull('estatus_planeacion');
        if ($request->contenedor_faltantes !== null) {
            $cotizaciones_faltantes = $cotizaciones_faltantes->where('id', $request->contenedor_faltantes);
        }
        $cotizaciones_faltantes = $cotizaciones_faltantes->first();

        return view('planeacion.index', compact('equipos', 'operadores', 'cotizaciones', 'proveedores', 'numCotizaciones', 'cotizaciones_faltantes', 'planeaciones'));
    }
}
