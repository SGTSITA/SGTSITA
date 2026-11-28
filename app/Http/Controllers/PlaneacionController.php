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

    public function resumenGastosCancelacion($idContenedor)
    {
        try {
            $contenedor = DocumCotizacion::findOrFail($idContenedor);
            $cotizacion = Cotizaciones::findOrFail($contenedor->id_cotizacion);
            $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();

            if (!$asignacion) {
                return response()->json([
                    'success' => true,
                    'es_elemental' => true,
                    'mensaje' => 'Sin asignación activa'
                ]);
            }

            // 1. Dinero para viaje
            $tieneDineroViaje = !is_null($asignacion->id_operador) && !is_null($asignacion->id_banco1_dinero_viaje) && floatval($asignacion->dinero_viaje) > 0;
            $movimientoBanco = null;
            if ($tieneDineroViaje) {
                $movimientoBanco = $this->bancosService->findMovimiento($asignacion->id, \App\Models\Asignaciones::class, $asignacion->id_banco1_dinero_viaje);
            }

            $dineroViajeData = null;
            if ($tieneDineroViaje && $movimientoBanco) {
                $operador = \App\Models\Operador::find($asignacion->id_operador);
                $banco = \App\Models\Bancos::find($asignacion->id_banco1_dinero_viaje);
                $dineroViajeData = [
                    'monto' => floatval($asignacion->dinero_viaje),
                    'monto_formateado' => '$' . number_format($asignacion->dinero_viaje, 2),
                    'operador_nombre' => $operador?->nombre ?? 'Operador asignado',
                    'operador_id' => $asignacion->id_operador,
                    'banco_nombre' => $banco?->nombre_banco ?? 'Banco',
                    'banco_id' => $asignacion->id_banco1_dinero_viaje,
                    'fecha' => $movimientoBanco->fecha_movimiento ? Carbon::parse($movimientoBanco->fecha_movimiento)->format('d/m/Y') : null,
                ];
            }

            // 2. Gastos unificados (Diésel y otros)
            $gastos = \App\Models\Gasto::with(['pagos.cuentaBancaria'])
                ->where(function ($query) use ($asignacion) {
                    $query->where(function ($q) use ($asignacion) {
                        $q->where('origen_legacy_id', $asignacion->id)
                          ->where('origen_legacy', 'like', 'asignacion_planeacion%');
                    })->orWhereHas('vinculos', function ($q) use ($asignacion) {
                        $q->where('vinculable_type', \App\Models\Asignaciones::class)
                          ->where('vinculable_id', $asignacion->id);
                    });
                })
                ->where('tipo_gasto', '!=', 'sueldo')
                ->where('estatus', '!=', 'cancelado')
                ->get();

            $dieselData = null;
            $otrosGastosData = [];

            foreach ($gastos as $g) {
                $esDiesel = str_contains(strtolower($g->concepto), 'diesel') ||
                            str_contains(strtolower($g->concepto), 'diésel') ||
                            str_contains($g->concepto, 'GDI02') ||
                            str_contains($g->origen_legacy ?? '', 'GDI02');

                $primerPago = $g->pagos->first();
                $bancoNombre = $primerPago?->cuentaBancaria?->nombre_banco ?? 'Banco';
                $cuentaNum = $primerPago?->cuentaBancaria?->cuenta ? ' •••• ' . substr($primerPago->cuentaBancaria->cuenta, -4) : '';
                $itemInfo = [
                    'id' => $g->id,
                    'concepto' => $g->concepto,
                    'monto' => floatval($g->monto_total),
                    'monto_formateado' => '$' . number_format($g->monto_total, 2),
                    'banco' => $primerPago ? ($bancoNombre . $cuentaNum) : 'Sin banco',
                    'banco_id' => $primerPago?->cuenta_bancaria_id,
                    'fecha' => $primerPago?->fecha_pago ? Carbon::parse($primerPago->fecha_pago)->format('d/m/Y') : null,
                    'pagado' => (bool) $primerPago,
                ];

                if ($esDiesel && !$dieselData) {
                    $itemInfo['es_precarga'] = (bool) $g->es_precarga_combustible;
                    $itemInfo['id_equipo_precarga'] = $g->id_equipo_precarga;
                    $dieselData = $itemInfo;
                } else {
                    $otrosGastosData[] = $itemInfo;
                }
            }

            $esElemental = is_null($dineroViajeData) && is_null($dieselData) && empty($otrosGastosData);

            $sueldoViajeData = null;
            if (floatval($asignacion->sueldo_viaje) > 0) {
                $operador = \App\Models\Operador::find($asignacion->id_operador);
                $sueldoViajeData = [
                    'monto' => floatval($asignacion->sueldo_viaje),
                    'monto_formateado' => '$' . number_format($asignacion->sueldo_viaje, 2),
                    'operador_nombre' => $operador?->nombre ?? 'Operador asignado',
                ];
            }

            $tracto = null;
            $unidad = null;
            $idTracto = $asignacion->id_camion ?: $asignacion->id_equipo;
            if ($idTracto) {
                $eq = \App\Models\Equipo::find($idTracto);
                if ($eq) {
                    $nombreUnidad = ($eq->id_equipo ? $eq->id_equipo . ' - ' : '') . ($eq->marca ?? 'Unidad');
                    $tracto = [
                        'id' => $eq->id,
                        'id_equipo' => $eq->id_equipo,
                        'placas' => $eq->placas,
                        'descripcion' => $nombreUnidad,
                        'nombre' => $nombreUnidad,
                    ];
                    $unidad = [
                        'id' => $eq->id,
                        'id_equipo' => $eq->id_equipo,
                        'placas' => $eq->placas,
                        'nombre' => $nombreUnidad,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'es_elemental' => $esElemental,
                'contenedor' => [
                    'id' => $contenedor->id,
                    'num_contenedor' => $contenedor->num_contenedor,
                ],
                'dinero_viaje' => $dineroViajeData,
                'sueldo_viaje' => $sueldoViajeData,
                'diesel' => $dieselData,
                'otros_gastos' => $otrosGastosData,
                'tracto' => $tracto,
                'unidad' => $unidad,
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener resumen de gastos: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function combustiblePrecargadoEquipo($idEquipo)
    {
        try {
            $gasto = \App\Models\Gasto::with(['pagos.cuentaBancaria'])
                ->where('es_precarga_combustible', true)
                ->where('id_equipo_precarga', $idEquipo)
                ->where('estatus', '!=', 'cancelado')
                ->latest()
                ->first();

            if (!$gasto) {
                return response()->json([
                    'success' => true,
                    'tiene_precarga' => false,
                    'items' => [],
                ]);
            }

            $primerPago = $gasto->pagos->first();
            $bancoNombre = $primerPago?->cuentaBancaria?->nombre_banco ?? 'Banco';
            $cuentaNum = $primerPago?->cuentaBancaria?->cuenta ? ' •••• ' . substr($primerPago->cuentaBancaria->cuenta, -4) : '';

            $gastoData = [
                'id' => $gasto->id,
                'concepto' => $gasto->concepto,
                'monto' => floatval($gasto->monto_total),
                'monto_formateado' => '$' . number_format($gasto->monto_total, 2),
                'origen_contenedor' => $gasto->origen_contenedor_num,
                'origen_contenedor_num' => $gasto->origen_contenedor_num,
                'origen_cotizacion_id' => $gasto->origen_cotizacion_id,
                'banco' => $primerPago ? ($bancoNombre . $cuentaNum) : 'Pre-pagado en banco',
                'banco_id' => $primerPago?->cuenta_bancaria_id,
                'fecha_aplicacion' => $primerPago?->fecha_pago ? Carbon::parse($primerPago->fecha_pago)->format('d/m/Y') : null,
                'historial' => $gasto->historial_reasignaciones ?? [],
            ];

            return response()->json([
                'success' => true,
                'tiene_precarga' => true,
                'gasto' => $gastoData,
                'items' => [$gastoData],
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al verificar combustible precargado: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function anularPlaneacion(Request $request)
    {
        try {
            $idContenedor = $request->idContenendor ?? $request->id_contenedor;
            $res = $this->planeacionService->anularPlaneacion(
                $idContenedor,
                $request->numContenedor,
                (int) Auth::user()->id_empresa,
                Auth::user(),
                $request->all()
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
        $cotizacion = $docCotizacion ? Cotizaciones::where('id', '=', $docCotizacion->id_cotizacion)->first() : null;

        $documentos = Cotizaciones::query()
            ->where('docum_cotizacion.id', $request->id)
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
                'chasis.placas as placas_chasis',
                'chasis.imei as imei_chasis',
                'operadores.nombre as operador',
                'operadores.nombre as nombre_operador',
                'proveedores.nombre as nombre_proveedor',
                'operadores.telefono as beneficiario_telefono',
                'operadores.telefono as telefono_operador',
                'cotizaciones.tipo_viaje',
                'cotizaciones.cp_contacto_entrega',
                'em.nombre as nombre_empresa_asignacion',
                'emc.nombre as nombre_empresa_cotizacion',
                DB::raw("COALESCE(em.nombre, emc.nombre, 'Transporte Propio') as Empresa"),
                DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN COALESCE(em.nombre, emc.nombre, 'Transporte Propio') ELSE proveedores.nombre END as transportista_nombre"),
                DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN 'Propio' ELSE 'Subcontratado' END as tipo_contrato"),
                'cotizaciones.estatus',
                'asignaciones.ruta_coordenadas'
            )
            ->get();

        $docFirst = $documentos->first();
        if ($docFirst && $asignaciones) {
            if (empty($docFirst->operador) && $asignaciones->Operador) {
                $docFirst->operador = $asignaciones->Operador->nombre;
                $docFirst->nombre_operador = $asignaciones->Operador->nombre;
            }
            if (empty($docFirst->beneficiario_telefono) && $asignaciones->Operador) {
                $docFirst->beneficiario_telefono = $asignaciones->Operador->telefono;
                $docFirst->telefono_operador = $asignaciones->Operador->telefono;
            }
            if (empty($docFirst->marca_camion) && $asignaciones->Camion) {
                $docFirst->marca_camion = $asignaciones->Camion->marca;
            }
            if (empty($docFirst->imei_camion) && $asignaciones->Camion) {
                $docFirst->imei_camion = $asignaciones->Camion->imei;
            }
            if (empty($docFirst->imei_chasis) && $asignaciones->Chasis) {
                $docFirst->imei_chasis = $asignaciones->Chasis->imei;
            }
            if (empty($docFirst->nombre_proveedor) && $asignaciones->Proveedor) {
                $docFirst->nombre_proveedor = $asignaciones->Proveedor->nombre;
            }
        }

        $misDocumentos = Cotizaciones::where('cotizaciones.id', $cotizacion->id)
            ->join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
            ->select('docum_cotizacion.*')
            ->get();

        $waText = "";
        if ($asignaciones) {
            if (!empty($asignaciones->mensaje_compartido)) {
                $waText = $asignaciones->mensaje_compartido;
            } else {
                $hora = Carbon::now()->hour;
                if ($hora >= 6 && $hora < 12) {
                    $saludo = "Buenos días";
                } elseif ($hora >= 12 && $hora < 19) {
                    $saludo = "Buenas tardes";
                } else {
                    $saludo = "Buenas noches";
                }

                $nombreOp = $asignaciones->Operador?->nombre ?? ($docFirst?->operador ?? '');

                $tipoViaje = $cotizacion->tipo_viaje;
                $contenedorStr = $documentos->first()?->num_contenedor ?? "";

                if (!empty($cotizacion->referencia_full)) {
                    $contenedoresFull = DocumCotizacion::join('cotizaciones', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
                        ->where('cotizaciones.referencia_full', $cotizacion->referencia_full)
                        ->pluck('docum_cotizacion.num_contenedor')
                        ->toArray();

                    if (!empty($contenedoresFull)) {
                        $contenedorStr = implode(' - ', $contenedoresFull);
                    }
                }

                $unidadEco = $documentos->first()?->id_equipo_camion;
                $origen = $cotizacion->origen;
                $direccion = $cotizacion->direccion_mapa;
                $mapLink = $cotizacion->latitud && $cotizacion->longitud
                    ? "https://maps.google.com/?q={$cotizacion->latitud},{$cotizacion->longitud}"
                    : "";
                $contacto = $cotizacion->cp_contacto_entrega ?? "";
                $comentarios = $cotizacion->observaciones ?? "";
                $fechaEntrega = $cotizacion->fecha_entrega ? Carbon::parse($cotizacion->fecha_entrega)->format('d/m/Y') : "";
                $horaLlegada = "";

                $waText = "{$saludo}" . ($nombreOp ? " " . trim($nombreOp) : "") . ",\n\n";
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
        }

        $dieselPrecarga = null;
        if ($asignaciones || $docCotizacion || $cotizacion) {
            $idAsig = $asignaciones?->id;
            $idDoc = $docCotizacion?->id;
            $idCot = $cotizacion?->id;

            $gastoDiesel = \App\Models\Gasto::where(function ($q) use ($idAsig, $idDoc, $idCot) {
                if ($idAsig) {
                    $q->where('origen_legacy_id', $idAsig)
                      ->orWhereHas('imputaciones', function ($imp) use ($idAsig) {
                          $imp->where('imputable_type', \App\Models\Asignaciones::class)
                              ->where('imputable_id', $idAsig);
                      });
                }
                if ($idCot) {
                    $q->orWhere('origen_cotizacion_id', $idCot);
                }
                $q->orWhereHas('vinculos', function ($v) use ($idAsig, $idDoc, $idCot) {
                    $v->where(function ($vQ) use ($idAsig, $idDoc, $idCot) {
                        if ($idAsig) {
                            $vQ->where(function ($sq) use ($idAsig) {
                                $sq->where('vinculable_type', \App\Models\Asignaciones::class)
                                   ->where('vinculable_id', $idAsig);
                            });
                        }
                        if ($idDoc) {
                            $vQ->orWhere(function ($sq) use ($idDoc) {
                                $sq->where('vinculable_type', \App\Models\DocumCotizacion::class)
                                   ->where('vinculable_id', $idDoc);
                            });
                        }
                        if ($idCot) {
                            $vQ->orWhere(function ($sq) use ($idCot) {
                                $sq->where('vinculable_type', \App\Models\Cotizaciones::class)
                                   ->where('vinculable_id', $idCot);
                            });
                        }
                    });
                });
            })
            ->where(function ($q) {
                $q->where('concepto', 'like', '%diesel%')
                  ->orWhere('concepto', 'like', '%diésel%')
                  ->orWhere('concepto', 'like', '%GDI02%')
                  ->orWhere('origen_legacy', 'like', '%Diesel%')
                  ->orWhere('origen_legacy', 'like', '%GDI02%');
            })
            ->where(function ($q) {
                $q->where('es_precarga_combustible', true)
                  ->orWhereNotNull('origen_contenedor_num')
                  ->orWhereNotNull('historial_reasignaciones');
            })
            ->first();

            if ($gastoDiesel) {
                $historial = is_array($gastoDiesel->historial_reasignaciones)
                    ? $gastoDiesel->historial_reasignaciones
                    : (json_decode($gastoDiesel->historial_reasignaciones, true) ?? []);

                $origenContenedor = $gastoDiesel->origen_contenedor_num;
                if (empty($origenContenedor) && !empty($historial)) {
                    $origenContenedor = $historial[0]['contenedor'] ?? null;
                }

                $dieselPrecarga = [
                    'monto' => $gastoDiesel->monto_total,
                    'monto_formateado' => '$' . number_format($gastoDiesel->monto_total, 2),
                    'historial' => $historial,
                    'origen_contenedor' => $origenContenedor ?: 'Viaje previo',
                    'origen_contenedor_num' => $origenContenedor ?: 'Viaje previo',
                    'es_activa' => (bool) $gastoDiesel->es_precarga_combustible,
                ];
            }
        }

        return response()->json([
            "nombre" => ($asignaciones?->id_proveedor == null) ? $asignaciones?->Operador?->nombre : $asignaciones?->Proveedor?->nombre,
            "tipo" => "Viaje " . $documentos->first()?->tipo_contrato,
            "cotizacion" => $cotizacion,
            "cliente" => $cotizacion?->Cliente,
            "subcliente" => $cotizacion?->Subcliente,
            "documentos" => $documentos->first(),
            "documents" => $misDocumentos->first(),
            "password_temporal" => null,
            "data" => $documentos->first(),
            "wa_text" => $waText,
            "asignacion_id" => $asignaciones?->id,
            "diesel_precarga" => $dieselPrecarga,
        ]);
    }

    public function guardarMensajeWhatsApp(Request $request)
    {
        $asignacion = Asignaciones::find($request->id_asignacion ?? $request->asignacion_id);
        if ($asignacion) {
            $asignacion->mensaje_compartido = $request->mensaje;
            $asignacion->save();
            return response()->json([
                'ok' => true,
                'success' => true,
                'msg' => 'Mensaje guardado correctamente',
                'message' => 'Mensaje guardado correctamente'
            ]);
        }
        return response()->json([
            'ok' => false,
            'success' => false,
            'msg' => 'Asignación no encontrada',
            'message' => 'Asignación no encontrada'
        ], 404);
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

    public function validarSiEsFull($idContenedor, $numContenedor)
    {
        $esFull = false;
        $doc = DocumCotizacion::where('num_contenedor', $numContenedor)->first();
        if ($doc && !empty($doc->Cotizacion?->referencia_full)) {
            $esFull = true;
        }

        return response()->json([
            "esFull" => $esFull,
        ]);
    }

    public function reprogramarViajes(Request $request)
    {
        $res = $this->planeacionService->reprogramarViajes((array) $request->viajes, Auth::user());
        return response()->json($res);
    }

    public function editar($id)
    {
        $idEmpresa = Auth::user()->id_empresa;
        $cotizacion = Cotizaciones::with(['DocCotizacion.Asignaciones', 'Cliente', 'Subcliente'])->findOrFail($id);

        $asignacionIdEmpresa = $cotizacion->DocCotizacion?->Asignaciones?->id_empresa;
        if ($cotizacion->id_empresa != $idEmpresa && $idEmpresa != 0 && $asignacionIdEmpresa != $idEmpresa) {
            abort(403, 'No tienes permiso para editar esta planeación.');
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
        $comprobacionesOperador = collect();

        if ($asignacion) {
            $dineroViaje = DineroContenedor::where('id_contenedor', $cotizacion->DocCotizacion->id)
                ->where('motivo', 'Dinero para viaje')
                ->first();

            // Sincronizar gastos de operador que pudieran no tener registro en gastos unificados
            $gastosOperadoresLegacy = \App\Models\GastosOperadores::withoutGlobalScope('no_eliminados')
                ->where('id_asignacion', $asignacion->id)
                ->where('estatus', '!=', 'eliminado')
                ->get();

            foreach ($gastosOperadoresLegacy as $go) {
                $existeGasto = \App\Models\Gasto::where(function ($q) use ($go, $asignacion) {
                    $q->where('origen_legacy', 'gastos_operadores')
                      ->where('origen_legacy_id', $go->id);
                })->orWhere(function ($q) use ($go, $asignacion) {
                    $tipoClean = strtolower(trim($go->tipo));
                    $prefijo = match (true) {
                        str_contains($tipoClean, 'diesel') || str_contains($tipoClean, 'diésel') => 'asignacion_planeacionGDI02',
                        str_contains($tipoClean, 'urea') => 'asignacion_planeacionGU001',
                        default => 'asignacion_planeacion' . $go->tipo,
                    };
                    $q->where('origen_legacy_id', $asignacion->id)
                      ->where('origen_legacy', 'like', $prefijo . '%');
                })->exists();

                if (!$existeGasto) {
                    try {
                        app(\App\Services\GastosService::class)->registrarDesdeGastoOperador($go);
                    } catch (\Throwable $t) {
                        Log::warning('No se pudo auto-sincronizar gasto operador en editar planeacion: ' . $t->getMessage());
                    }
                }
            }

            // Gastos de planeación asignados al viaje (excluye comprobaciones de viáticos del chofer)
            $gastosAsignados = \App\Models\Gasto::with(['pagos.cuentaBancaria'])
                ->where(function ($query) use ($asignacion) {
                    $query->where('origen_legacy_id', $asignacion->id)
                          ->where('origen_legacy', 'like', 'asignacion_planeacion%');
                })
                ->where('estatus', '!=', 'cancelado')
                ->where('tipo_gasto', '!=', 'sueldo')
                ->where('tipo_gasto', '!=', 'operador')
                ->where(function ($q) {
                    $q->whereNull('origen_legacy')->orWhereNotIn('origen_legacy', [
                        'asignacion_sueldo_operador',
                        'asignacion_anticipo_operador',
                        'gastos_operadores',
                        'viaticos_operadores',
                        'comprobacion_viaticos',
                    ]);
                })
                ->get();

            // Comprobaciones de viáticos subidas por el operador
            $comprobacionesOperador = \App\Models\Gasto::with(['pagos.cuentaBancaria'])
                ->where(function ($query) use ($asignacion) {
                    $query->where(function ($q) use ($asignacion) {
                        $q->where('origen_legacy_id', $asignacion->id)
                          ->whereIn('origen_legacy', ['gastos_operadores', 'viaticos_operadores', 'comprobacion_viaticos']);
                    })->orWhere(function ($q) use ($asignacion) {
                        $q->where('origen_legacy_id', $asignacion->id)
                          ->where('tipo_gasto', 'operador');
                    })->orWhereHas('vinculos', function ($q) use ($asignacion) {
                        $q->where('vinculable_type', Asignaciones::class)
                          ->where('vinculable_id', $asignacion->id)
                          ->where('tipo_vinculo', 'operador');
                    });
                })
                ->where('estatus', '!=', 'cancelado')
                ->where('concepto', 'not like', '%sueldo%')
                ->where('concepto', 'not like', '%dinero para viaje%')
                ->get();
        }

        return view('planeacion.edit', compact(
            'cotizacion',
            'equipos',
            'operadores',
            'proveedores',
            'bancos',
            'asignacion',
            'dineroViaje',
            'gastosAsignados',
            'comprobacionesOperador'
        ));
    }

    public function updatePlaneacion(Request $request, $idCotizacion)
    {
        $res = $this->planeacionService->updatePlaneacion((int) $idCotizacion, $request->all(), (int) Auth::user()->id_empresa, Auth::user());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($res);
        }

        return redirect()->route('index.planeacion')->with('success', $res['Mensaje'] ?? 'Planeación actualizada correctamente.');
    }

    public function update(Request $request, $id)
    {
        return $this->updatePlaneacion($request, $id);
    }

    public function comboUnidades(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;
        $idEmpresa = Auth::user()->id_empresa;

        if ($fechaInicio && $fechaFin) {
            $asignacionesEnRango = Asignaciones::where('id_empresa', '=', $idEmpresa)
                ->where(function ($query) use ($fechaInicio, $fechaFin) {
                    $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                        ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                        ->orWhere(function ($q) use ($fechaInicio, $fechaFin) {
                            $q->where('fecha_inicio', '<=', $fechaInicio)
                              ->where('fecha_fin', '>=', $fechaFin);
                        });
                });

            $camionesAsignados = (clone $asignacionesEnRango)
                ->whereNotNull('id_camion')
                ->pluck('id_camion');

            $camionesNoAsignados = Equipo::where('id_empresa', '=', $idEmpresa)
                ->where('tipo', 'LIKE', '%Tractos%')
                ->whereNotIn('id', $camionesAsignados)
                ->get();

            $chasisAsignados = (clone $asignacionesEnRango)
                ->whereNotNull('id_chasis')
                ->pluck('id_chasis');

            $chasisNoAsignados = Equipo::where('id_empresa', '=', $idEmpresa)
                ->where('tipo', 'LIKE', '%Chasis%')
                ->whereNotIn('id', $chasisAsignados)
                ->get();

            $dolysAsignados = (clone $asignacionesEnRango)
                ->whereNotNull('id_dolys')
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