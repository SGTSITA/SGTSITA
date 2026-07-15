<?php

namespace App\Services;

use App\Models\User;
use App\Models\Operador;
use App\Models\Equipo;
use App\Models\DocumCotizacion;
use App\Models\Asignaciones;
use App\Models\Cotizaciones;
use App\Models\Client;
use App\Models\Proveedor;
use App\Models\Coordenadas;
use App\Models\coordenadashistorial;
use App\Models\ComprobanteGastos;
use App\Models\GastosOperadores;
use App\Models\RegistroDieselOperador;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiValidationService
{
    public function login(array $credentials)
    {
        if (!Auth::attempt($credentials)) {
            return ['success' => false, 'message' => 'Las credenciales de acceso son incorrectas.', 'data' => [], 'status' => 401];
        }

        $user = Auth::user();

        if (!$user->can('SGT-Acceso')) {
            Auth::logout();
            return ['success' => false, 'message' => 'Tu usuario no tiene acceso al sistema SGT.', 'data' => [], 'status' => 403];
        }

        $token = $user->createToken('sgt-api-token')->plainTextToken;

        return [
            'success' => true,
            'message' => 'Inicio de sesión exitoso.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'id_empresa' => $user->id_empresa,
                    'roles' => $user->roles()->pluck('name')->toArray(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                ]
            ],
            'status' => 200
        ];
    }

    public function validateOperador(array $data)
    {
        if (isset($data['contrasena'])) {
            $nombre = $data['nombre'];
            $telefono = $data['telefono'];
            $contrasena = $data['contrasena'];

            $reqTelefono = preg_replace('/\D/', '', $telefono);
            $operadores = Operador::all();
            $operador = $operadores->first(function($op) use ($reqTelefono) {
                return preg_replace('/\D/', '', $op->telefono) === $reqTelefono;
            });

            if (!$operador) {
                return ['success' => false, 'message' => 'Operador no registrado.', 'data' => [], 'status' => 404];
            }

            if (stripos($operador->nombre, trim($nombre)) === false && stripos(trim($nombre), $operador->nombre) === false) {
                return ['success' => false, 'message' => 'El nombre del operador no coincide.', 'data' => [], 'status' => 400];
            }

            $asignacion = Asignaciones::with(['Camion'])
                ->where('id_operador', $operador->id)
                ->where('password_temporal', $contrasena)
                ->first();

            if (!$asignacion) {
                return ['success' => false, 'message' => 'Contraseña incorrecta o no hay viaje asignado con esa contraseña.', 'data' => [], 'status' => 400];
            }

            $contenedor = DocumCotizacion::find($asignacion->id_contenedor);
            $camion = $asignacion->Camion;

            return [
                'success' => true,
                'message' => 'Operador y viaje validados correctamente para ingresar operador.',
                'data' => [
                    'id_contenedor' => $contenedor ? $contenedor->id : null,
                    'id_operador'   => $operador->id,
                    'id_asignacion' => $asignacion->id,
                    'nombre'        => $operador->nombre,
                    'num_contenedor' => $contenedor ? $contenedor->num_contenedor : '',
                    'unidad'        => $camion ? $camion->id_equipo : '',
                    'telefono'      => $operador->telefono,
                    'token'         => 'operador_session_' . $operador->id,
                    'id_equipo'     => $camion ? $camion->id_equipo : '',
                ],
                'status' => 200
            ];
        } else {
            $nombre = $data['nombre'];
            $telefono = $data['telefono'];
            $unidad = $data['unidad'];
            $contenedorNum = $data['contenedor'];

            $contenedor = DocumCotizacion::where('num_contenedor', $contenedorNum)->first();
            if (!$contenedor) {
                return ['success' => false, 'message' => 'Contenedor no registrado en cotizaciones.', 'data' => [], 'status' => 404];
            }

            $asignacion = Asignaciones::with(['Operador', 'Camion'])
                ->where('id_contenedor', $contenedor->id)
                ->first();

            if (!$asignacion) {
                return [
                    'success' => false,
                    'message' => 'El viaje/asignación no existe para este contenedor.',
                    'data' => ['id_contenedor' => $contenedor->id],
                    'status' => 404
                ];
            }

            $camion = $asignacion->Camion;
            if (!$camion || strtolower(trim($camion->id_equipo)) !== strtolower(trim($unidad))) {
                return [
                    'success' => false,
                    'message' => 'La unidad no coincide con el viaje asignado.',
                    'data' => [
                        'id_contenedor' => $contenedor->id,
                        'unidad_asignada' => $camion ? $camion->id_equipo : 'Ninguna'
                    ],
                    'status' => 400
                ];
            }

            $operador = $asignacion->Operador;
            if (!$operador) {
                return [
                    'success' => false,
                    'message' => 'No hay operador asignado a este viaje.',
                    'data' => ['id_contenedor' => $contenedor->id],
                    'status' => 400
                ];
            }

            if (stripos($operador->nombre, trim($nombre)) === false && stripos(trim($nombre), $operador->nombre) === false) {
                return [
                    'success' => false,
                    'message' => 'El nombre del operador no coincide con el viaje asignado.',
                    'data' => ['id_contenedor' => $contenedor->id],
                    'status' => 400
                ];
            }

            $reqTelefono = preg_replace('/\D/', '', $telefono);
            $dbTelefono = preg_replace('/\D/', '', $operador->telefono);
            if ($reqTelefono !== $dbTelefono) {
                return [
                    'success' => false,
                    'message' => 'El teléfono no coincide con el operador asignado.',
                    'data' => ['id_contenedor' => $contenedor->id],
                    'status' => 400
                ];
            }

            return [
                'success' => true,
                'message' => 'Operador y viaje validados correctamente para ingresar operador.',
                'data' => [
                    'id_contenedor' => $contenedor->id,
                    'id_operador'   => $operador->id,
                    'id_asignacion' => $asignacion->id,
                    'nombre'        => $operador->nombre,
                    'num_contenedor' => $contenedor->num_contenedor,
                    'unidad'        => $camion->id_equipo,
                    'telefono'      => $operador->telefono,
                    'token'         => 'operador_session_' . $operador->id,
                    'id_equipo'     => $camion->id_equipo,
                ],
                'status' => 200
            ];
        }
    }

    public function getOperacionActiva($user, $empresaId)
    {
        $query = Cotizaciones::where('id_empresa', $empresaId)
            ->where('jerarquia', '!=', 'Secundario')
            ->wherein('tipo_viaje_seleccion', ['foraneo','local_to_foraneo'])
            ->where(function($q) {
                $q->where('estatus', '!=', 'Finalizado')
                  ->orWhere('updated_at', '>=', now('America/Mexico_City')->subDays(15));
            });

        $userProveedores = User::find($user->id);
        if ($userProveedores && $userProveedores->proveedores()->exists()) {
            $query->whereIn(
                'id_proveedor',
                $userProveedores->proveedores()->pluck('proveedor_id')
            );
        }

        $cotizaciones = $query->with(['Cliente', 'DocCotizacion.Asignaciones.Operador', 'DocCotizacion.Asignaciones.Camion', 'DocCotizacion.naviera', 'viajes.costos'])
            ->orderBy('created_at', 'desc')
            ->get();

        $mapaCostos = config('CatAuxiliares.costosViajes') ?? [];

        $data = $cotizaciones->map(function ($cotizacion) use ($mapaCostos, $empresaId) {
            $viaje = $cotizacion->viajes->firstWhere('estado', 'activo');
            $costosForm = [];
            $totalCostosViaje = 0;

            if ($viaje) {
                foreach ($mapaCostos as $input => $config) {
                    $concepto = $config['concepto'];
                    $conceptoBuscado = trim(strtolower($concepto));
                    $conceptoBuscado = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $conceptoBuscado);

                    $costo = $viaje->costos->first(function ($c) use ($conceptoBuscado) {
                        $cNorm = trim(strtolower($c->concepto));
                        $cNorm = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $cNorm);
                        return $cNorm === $conceptoBuscado;
                    });

                    $montoCosto = $costo?->monto ?? 0;
                    $costosForm[$input] = $montoCosto;
                }

                $sobrepeso = $viaje->costos->first(function ($c) {
                    $cNorm = trim(strtolower($c->concepto));
                    $cNorm = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $cNorm);
                    return $cNorm === 'sobrepeso';
                });
                if ($sobrepeso) {
                    $costosForm['precio_sobre_peso'] = $sobrepeso->meta['precio_sobre_peso'] ?? 0;
                    $costosForm['sobrepeso_viaje'] = $sobrepeso->meta['peso'] ?? 0;
                    $costosForm['precio_tonelada'] = $sobrepeso->meta['precio_tonelada'] ?? 0;
                    $costosForm['total_sobrepeso_viaje'] = $sobrepeso->monto;
                }

                $tieneRetencionCost = false;
                foreach ($viaje->costos as $costo) {
                    $conceptoNorm = trim(strtolower($costo->concepto));
                    $conceptoNorm = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $conceptoNorm);

                    if (in_array($conceptoNorm, ['base_factura', 'base_taref', 'iva', 'retencion'])) {
                        $monto = (float) $costo->monto;
                        if (str_contains($conceptoNorm, 'retencion')) {
                            $totalCostosViaje -= $monto;
                            $tieneRetencionCost = true;
                        } elseif ($costo->tipo_operacion === 'descuento') {
                            $totalCostosViaje -= $monto;
                        } else {
                            $totalCostosViaje += $monto;
                        }
                    }
                }

                $retMonto = 0;
                if (!empty($cotizacion->retencion) && (float) $cotizacion->retencion > 0) {
                    $retMonto = (float) $cotizacion->retencion;
                } elseif ($cotizacion->retencion_automatica == 1 && !empty($cotizacion->base_factura)) {
                    $retMonto = (float) $cotizacion->base_factura * 0.04;
                }

                if (!$tieneRetencionCost && $retMonto > 0) {
                    $totalCostosViaje -= $retMonto;
                    $costosForm['retencion'] = $retMonto;
                }
            } else {
                $retMonto = 0;
                if (!empty($cotizacion->retencion) && (float) $cotizacion->retencion > 0) {
                    $retMonto = (float) $cotizacion->retencion;
                } elseif ($cotizacion->retencion_automatica == 1 && !empty($cotizacion->base_factura)) {
                    $retMonto = (float) $cotizacion->base_factura * 0.04;
                }
                if ($retMonto > 0) {
                    $costosForm['retencion'] = $retMonto;
                }
            }

            $gastosTotal = 0;
            $gastosDetalle = [];
            try {
                $gastosService = app(\App\Services\GastosService::class);
                $gastosList = $gastosService->listar([
                    'id_empresa' => $empresaId,
                    'cotizacion_id' => $cotizacion->id,
                    'tipo_gasto' => 'cotizacion'
                ]);
                foreach ($gastosList as $gasto) {
                    $montoG = (float) $gasto['monto_total'];
                    $gastosTotal += $montoG;
                    $gastosDetalle[] = [
                        'folio' => $gasto['folio'] ?? 'S/F',
                        'concepto' => $gasto['concepto'] ?? 'Gastos Extra',
                        'categoria' => $gasto['categoria'] ?? 'N/A',
                        'monto' => $montoG,
                        'fecha' => $gasto['fecha_gasto'] ?? ''
                    ];
                }
            } catch (\Exception $e) {
                // Ignore errors
            }

            $costoTotalCalculado = $totalCostosViaje + $gastosTotal;

            $contenedor = $cotizacion->DocCotizacion ? $cotizacion->DocCotizacion->num_contenedor : 'N/A';
            if (!is_null($cotizacion->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $cotizacion->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion')
                    ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $contenedor .= ' / ' . $secundaria->DocCotizacion->num_contenedor;
                }
            }

            $asignacion = $cotizacion->DocCotizacion?->Asignaciones;
            $estatus = $cotizacion->estatus;

            if ($cotizacion->estatus_planeacion == 1 && $estatus == 'Aprobada') {
                 $estatus = 'Planeada';
            }

            $url_llegada = $cotizacion->latitud . $cotizacion->longitud;

            Log::info("Debug Operacion ID {$cotizacion->id}:", [
                'retencion_col' => $cotizacion->retencion,
                'viaje_costos' => $viaje ? $viaje->costos->map(fn($c) => [$c->concepto => $c->monto])->toArray() : 'sin viaje'
            ]);

            return [
                'id' => $cotizacion->id,
                'contenedor_id' => $cotizacion->DocCotizacion?->id,
                'cliente' => $cotizacion->Cliente ? $cotizacion->Cliente->nombre : 'N/A',
                'contenedor' => $contenedor,
                'origen' => $cotizacion->origen,
                'destino' => $cotizacion->destino,
                'url_llegada' => $url_llegada,
                'estatus' => $estatus,
                'est_plane'=> $cotizacion->estatus_planeacion ?? null,
                'total' => $cotizacion->total,
                'total_costos_viaje' => $totalCostosViaje,
                'gastos_total' => $gastosTotal,
                'gastos_detalle' => $gastosDetalle,
                'costo_total_calculado' => $costoTotalCalculado,
                'debug_viaje_costos' => $viaje ? $viaje->costos->map(fn($c) => ['concepto' => $c->concepto, 'monto' => $c->monto, 'tipo_operacion' => $c->tipo_operacion]) : [],
                'debug_cotizacion_retencion' => $cotizacion->retencion,
                'operador' => $asignacion?->Operador?->nombre ?? 'Sin Asignar',
                'container_num' => $cotizacion->DocCotizacion?->num_contenedor ?? '',
                'unidad' => $asignacion?->Camion?->id_equipo ?? 'Ninguna',
                'terminal' => $cotizacion->DocCotizacion?->terminal ?? 'N/A',
                'naviera' => $cotizacion->DocCotizacion?->naviera?->naviera ?? 'N/A',
                'boleta_liberacion' => $cotizacion->DocCotizacion?->boleta_liberacion ?? '',
                'num_boleta_liberacion' => $cotizacion->DocCotizacion?->num_boleta_liberacion ?? '',
                'costos_detalle' => empty($costosForm) ? (object)[] : $costosForm
            ];
        });

        return [
            'success' => true,
            'message' => 'Operaciones activas de la empresa obtenidas con éxito.',
            'data' => $data,
            'status' => 200
        ];
    }

    public function getCotizaciones($empresaId)
    {
        $cotizaciones = DB::table('cotizaciones')
            ->leftJoin('clients', 'cotizaciones.id_cliente', '=', 'clients.id')
            ->leftJoin('docum_cotizacion', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->where('cotizaciones.id_empresa', $empresaId)
            ->where('cotizaciones.jerarquia', '!=', 'Secundario')
            ->select(
                'cotizaciones.id',
                'clients.nombre as cliente',
                'docum_cotizacion.num_contenedor as contenedor',
                'cotizaciones.total',
                'cotizaciones.estatus'
            )
            ->orderBy('cotizaciones.created_at', 'desc')
            ->limit(100)
            ->get();

        return [
            'success' => true,
            'message' => 'Cotizaciones obtenidas con éxito.',
            'data' => $cotizaciones,
            'status' => 200
        ];
    }

    public function getViajes($empresaId)
    {
        $viajes = DB::table('asignaciones')
            ->leftJoin('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
            ->leftJoin('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->leftJoin('operadores', 'asignaciones.id_operador', '=', 'operadores.id')
            ->where('asignaciones.id_empresa', $empresaId)
            ->select(
                'asignaciones.id',
                'cotizaciones.origen',
                'cotizaciones.destino',
                'operadores.nombre as operador',
                'asignaciones.total_viaje as costo',
                'asignaciones.estatus_viaje as estatus'
            )
            ->orderBy('asignaciones.created_at', 'desc')
            ->limit(100)
            ->get();

        return [
            'success' => true,
            'message' => 'Viajes obtenidos con éxito.',
            'data' => $viajes,
            'status' => 200
        ];
    }

    public function getContenedores($empresaId)
    {
        $contenedores = DB::table('docum_cotizacion')
            ->leftJoin('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->where('docum_cotizacion.id_empresa', $empresaId)
            ->select(
                'docum_cotizacion.id',
                'docum_cotizacion.num_contenedor as numero',
                'cotizaciones.referencia_full as tipo',
                'cotizaciones.tamano',
                'docum_cotizacion.terminal as ubicacion',
                'cotizaciones.estatus'
            )
            ->orderBy('docum_cotizacion.created_at', 'desc')
            ->limit(100)
            ->get();

        return [
            'success' => true,
            'message' => 'Contenedores obtenidos con éxito.',
            'data' => $contenedores,
            'status' => 200
        ];
    }

    public function getReportes($empresaId)
    {
        $totales = [
            'total_cotizaciones' => DB::table('cotizaciones')->where('id_empresa', $empresaId)->count(),
            'total_viajes_activos' => DB::table('asignaciones')->where('id_empresa', $empresaId)->count(),
            'total_contenedores' => DB::table('docum_cotizacion')->where('id_empresa', $empresaId)->count(),
            'cotizaciones_aprobadas' => DB::table('cotizaciones')
                ->where('id_empresa', $empresaId)
                ->where('estatus', 'Aprobada')
                ->count(),
            'cotizaciones_pendientes' => DB::table('cotizaciones')
                ->where('id_empresa', $empresaId)
                ->where('estatus', 'Pendiente')
                ->count(),
        ];

        return [
            'success' => true,
            'message' => 'Estadísticas y reportes de operación por empresa.',
            'data' => $totales,
            'status' => 200
        ];
    }

    public function finalizarViaje($idContenedor)
    {
        if (empty($idContenedor)) {
            return ['success' => false, 'message' => 'El ID de contenedor es requerido.', 'data' => [], 'status' => 400];
        }

        $contenedor = DocumCotizacion::find($idContenedor);
        if (!$contenedor) {
            return ['success' => false, 'message' => 'Contenedor no encontrado.', 'data' => [], 'status' => 404];
        }

        $cotizacion = Cotizaciones::find($contenedor->id_cotizacion);
        if ($cotizacion) {
            $cotizacion->estatus = 'Finalizado';
            $cotizacion->update();
        }

        return [
            'success' => true,
            'message' => 'Viaje finalizado con éxito.',
            'data' => [
                'titulo' => 'Viaje finalizado',
                'mensaje' => 'Has finalizado correctamente el viaje'
            ],
            'status' => 200
        ];
    }

    public function infoViaje($idContenedor)
    {
        $docCotizacion = DocumCotizacion::where('id', '=', $idContenedor)->first();
        if (!$docCotizacion) {
            return ['success' => false, 'message' => 'Contenedor no encontrado.', 'data' => [], 'status' => 404];
        }

        $asignaciones = Asignaciones::where('id_contenedor', '=', $idContenedor)->first();
        $cotizacion = Cotizaciones::where('id', '=', $docCotizacion->id_cotizacion)->first();

        $documentos = Cotizaciones::query()
            ->where('cotizaciones.id', $cotizacion->id)
            ->join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
            ->leftJoin('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
            ->leftJoin('empresas as em', 'em.id', '=', 'asignaciones.id_empresa')
            ->leftJoin('empresas as emc', 'emc.id', '=', 'cotizaciones.id_empresa')
            ->leftJoin('clients', 'cotizaciones.id_cliente', '=', 'clients.id')
            ->leftjoin('equipos', 'asignaciones.id_camion', '=', 'equipos.id')
            ->leftjoin('equipos as chasis', 'asignaciones.id_chasis', '=', 'chasis.id')
            ->leftjoin('operadores', 'operadores.id', '=', 'asignaciones.id_operador')
            ->leftjoin('proveedores', 'proveedores.id', '=', 'asignaciones.id_proveedor')
            ->select(
                'asignaciones.id as asignacionId',
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

            $checkFile = function($file, $id) {
                if (empty($file)) return null;
                $path = public_path('cotizaciones/cotizacion' . $id . '/' . $file);
                return \File::exists($path) ? $file : null;
            };

            $docCCP = $checkFile($cot->doc_ccp, $cot->id);
            $doda = $checkFile($cot->doda, $cot->id);
            $boletaLiberacion = $checkFile($cot->boleta_liberacion, $cot->id);
            $cartaPorte = $checkFile($cot->carta_porte, $cot->id);
            $cartaPorteXml = $checkFile($cot->carta_porte_xml, $cot->id);
            $boletaVacio = $checkFile($cot->boleta_vacio, $cot->id);
            $docEir = $checkFile($cot->doc_eir, $cot->id);
            $tipo = "--";

            if (!is_null($cot->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $cot->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion.Asignaciones')
                    ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $secCCP = $checkFile($secundaria->DocCotizacion->doc_ccp, $secundaria->id);
                    $secDoda = $checkFile($secundaria->DocCotizacion->doda, $secundaria->id);
                    $secEir = $checkFile($secundaria->DocCotizacion->doc_eir, $secundaria->id);
                    $secBoletaLiberacion = $checkFile($secundaria->DocCotizacion->boleta_liberacion, $secundaria->id);
                    $secCartaPorte = $checkFile($secundaria->carta_porte, $secundaria->id);
                    $secCartaPorteXml = $checkFile($secundaria->carta_porte_xml, $secundaria->id);
                    $secBoletaVacio = $checkFile($secundaria->img_boleta, $secundaria->id);

                    $docCCP = ($docCCP && $secCCP) ? $docCCP : null;
                    $doda = ($doda && $secDoda) ? $doda : null;
                    $docEir = ($docEir !== null && $secEir !== null) ? $docEir : null;
                    $boletaLiberacion = ($boletaLiberacion && $secBoletaLiberacion) ? $boletaLiberacion : null;
                    $cartaPorte = ($cartaPorte && $secCartaPorte) ? $cartaPorte : null;
                    $cartaPorteXml = ($cartaPorteXml && $secCartaPorteXml) ? $cartaPorteXml : null;
                    $boletaVacio = ($boletaVacio && $secBoletaVacio) ? $boletaVacio : null;

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

        $documentosFirst = $documentos->first();
        $firstChecked = $misDocumentos->first();

        if ($documentosFirst && $firstChecked) {
            $documentosFirst->doc_ccp = $firstChecked['doc_ccp'];
            $documentosFirst->doda = $firstChecked['doda'];
            $documentosFirst->boleta_liberacion = $firstChecked['boleta_liberacion'];
            $documentosFirst->carta_porte = $firstChecked['carta_porte'];
            $documentosFirst->carta_porte_xml = $firstChecked['carta_porte_xml'];
            $documentosFirst->boleta_vacio = $firstChecked['boleta_vacio'];
            $documentosFirst->doc_eir = $firstChecked['doc_eir'];
        }

        return [
            'success' => true,
            'message' => 'Información del viaje obtenida con éxito.',
            'data' => [
                "nombre" => $asignaciones?->Operador?->nombre ?? $documentosFirst?->transportista_nombre ?? '',
                "tipo" => "Viaje " . ($documentosFirst?->tipo_contrato ?? ''),
                "cotizacion" => $cotizacion,
                "cliente" => $cotizacion->Cliente,
                "subcliente" => $cotizacion->Subcliente,
                "documentos" => $documentosFirst,
                "documents" => $firstChecked
            ],
            'status' => 200
        ];
    }

    public function guardarCoordenadas(array $data)
    {
        $idAsignacion = $data['id_asignacion'];
        $asignacion = Asignaciones::find($idAsignacion);

        if (!$asignacion) {
            return ['success' => false, 'message' => 'Asignación no encontrada.', 'data' => [], 'status' => 404];
        }

        if (isset($data['latitud']) && isset($data['longitud'])) {
            coordenadashistorial::create([
                'latitud' => $data['latitud'],
                'longitud' => $data['longitud'],
                'registrado_en' => Carbon::now(),
                'ubicacionable_id' => $asignacion->id_camion,
                'ubicacionable_type' => 'App\Models\Equipo',
                'tipo' => 'OperadorMovil'
            ]);
        }

        if (isset($data['ticket_foto_base64'])) {
            $path = public_path('/uploads/diesel/' . $idAsignacion);
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            $fileName = uniqid() . '_diesel_ticket.jpg';
            file_put_contents($path . '/' . $fileName, base64_decode($data['ticket_foto_base64']));

            $doc = DocumCotizacion::find($asignacion->id_contenedor);
            $idCotizacion = $doc ? $doc->id_cotizacion : null;
            $cotizacion = Cotizaciones::find($idCotizacion);

            GastosOperadores::create([
                'id_asignacion' => $idAsignacion,
                'id_operador' => $asignacion->id_operador,
                'id_cotizacion' => $idCotizacion,
                'cantidad' => $data['costo'] ?? 0.0,
                'tipo' => 'Diesel',
                'estatus' => 'pendiente',
                'comprobante' => 'uploads/diesel/' . $idAsignacion . '/' . $fileName,
                'fecha_pago' => Carbon::now()
            ]);

            ComprobanteGastos::create([
                'id_asignacion' => $idAsignacion,
                'imagen' => 'uploads/diesel/' . $idAsignacion . '/' . $fileName,
                'tipo' => 'diesel'
            ]);

            $ureaFileName = null;
            if (isset($data['ticket_foto_urea_base64'])) {
                $ureaFileName = uniqid() . '_urea_ticket.jpg';
                file_put_contents($path . '/' . $ureaFileName, base64_decode($data['ticket_foto_urea_base64']));
            }

            RegistroDieselOperador::create([
                'id_asignacion' => $idAsignacion,
                'id_operador' => $asignacion->id_operador,
                'latitud' => $data['latitud'] ?? null,
                'longitud' => $data['longitud'] ?? null,
                'litros' => $data['litros'] ?? null,
                'costo' => $data['costo'] ?? null,
                'odometro' => $data['odometro'] ?? null,
                'comprobante' => 'uploads/diesel/' . $idAsignacion . '/' . $fileName,
                'litros_urea' => $data['litros_urea'] ?? null,
                'costo_urea' => $data['costo_urea'] ?? null,
                'comprobante_urea' => $ureaFileName ? 'uploads/diesel/' . $idAsignacion . '/' . $ureaFileName : null,
            ]);

            if ($cotizacion) {
                $cotizacion->litros_diesel = $data['litros'] ?? null;
                $cotizacion->litros_urea = $data['litros_urea'] ?? null;
                $cotizacion->update();
            }
        }

        return ['success' => true, 'message' => 'Coordenadas y registro de diésel guardados con éxito.', 'data' => [], 'status' => 200];
    }

    public function iniciarViaje(array $data)
    {
        $idAsignacion = $data['id_asignacion'];
        $asignacion = Asignaciones::find($idAsignacion);

        if (!$asignacion) {
            return ['success' => false, 'message' => 'Asignación no encontrada.', 'data' => [], 'status' => 404];
        }

        if (isset($data['latitud']) && isset($data['longitud'])) {
            coordenadashistorial::create([
                'latitud' => $data['latitud'],
                'longitud' => $data['longitud'],
                'registrado_en' => Carbon::now(),
                'ubicacionable_id' => $asignacion->id_camion,
                'ubicacionable_type' => 'App\Models\Equipo',
                'tipo' => 'OperadorMovil'
            ]);
        }

        $savedFilePaths = [];
        if (isset($data['fotos_base64']) && is_array($data['fotos_base64'])) {
            $path = public_path('/uploads/carga_contenedor/' . $idAsignacion);
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            foreach ($data['fotos_base64'] as $index => $base64Str) {
                $fileName = uniqid() . '_carga_' . ($index + 1) . '.jpg';
                file_put_contents($path . '/' . $fileName, base64_decode($base64Str));

                $relativeUrl = 'uploads/carga_contenedor/' . $idAsignacion . '/' . $fileName;
                $savedFilePaths[] = $relativeUrl;

                ComprobanteGastos::create([
                    'id_asignacion' => $idAsignacion,
                    'imagen' => $relativeUrl,
                    'tipo' => 'carga_contenedor'
                ]);
            }
        }

        $doc = DocumCotizacion::find($asignacion->id_contenedor);
        $idCotizacion = $doc ? $doc->id_cotizacion : null;

        $coordenada = Coordenadas::firstOrCreate([
            'id_asignacion' => $idAsignacion,
            'id_cotizacion' => $idCotizacion
        ]);
        $coordenada->update([
            'cargado_contenedor' => 'Cargado - Inicio Viaje',
            'cargado_contenedor_datatime' => Carbon::now()
        ]);

        $flowRecord = RegistroDieselOperador::firstOrCreate([
            'id_asignacion' => $idAsignacion
        ]);
        $flowRecord->update([
            'id_operador' => $asignacion->id_operador,
            'viaje_iniciado' => Carbon::now(),
            'fotos_carga' => json_encode($savedFilePaths),
        ]);

        return ['success' => true, 'message' => 'Viaje iniciado y fotos guardadas correctamente.', 'data' => [], 'status' => 200];
    }

    public function finalizarViajeOperador(array $data)
    {
        $idAsignacion = $data['id_asignacion'];
        $asignacion = Asignaciones::find($idAsignacion);

        if (!$asignacion) {
            return ['success' => false, 'message' => 'Asignación no encontrada.', 'data' => [], 'status' => 404];
        }

        if (isset($data['latitud']) && isset($data['longitud'])) {
            coordenadashistorial::create([
                'latitud' => $data['latitud'],
                'longitud' => $data['longitud'],
                'registrado_en' => Carbon::now(),
                'ubicacionable_id' => $asignacion->id_camion,
                'ubicacionable_type' => 'App\Models\Equipo',
                'tipo' => 'OperadorMovil'
            ]);
        }

        $savedFilePaths = [];
        if (isset($data['fotos_base64']) && is_array($data['fotos_base64'])) {
            $path = public_path('/uploads/entrega_contenedor/' . $idAsignacion);
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            foreach ($data['fotos_base64'] as $index => $base64Str) {
                $fileName = uniqid() . '_entrega_' . ($index + 1) . '.jpg';
                file_put_contents($path . '/' . $fileName, base64_decode($base64Str));

                $relativeUrl = 'uploads/entrega_contenedor/' . $idAsignacion . '/' . $fileName;
                $savedFilePaths[] = $relativeUrl;
            }
        }

        $flowRecord = RegistroDieselOperador::firstOrCreate([
            'id_asignacion' => $idAsignacion
        ]);
        $flowRecord->update([
            'id_operador' => $asignacion->id_operador,
            'viaje_finalizado' => Carbon::now(),
            'fotos_fin' => json_encode($savedFilePaths),
            'latitud_fin' => $data['latitud'] ?? null,
            'longitud_fin' => $data['longitud'] ?? null,
        ]);

        return ['success' => true, 'message' => 'Viaje finalizado correctamente.', 'data' => [], 'status' => 200];
    }

    public function obtenerEstatusFlujo($idAsignacion)
    {
        if (!$idAsignacion) {
            return ['success' => false, 'message' => 'Falta id_asignacion', 'data' => [], 'status' => 400];
        }

        $flowRecord = RegistroDieselOperador::where('id_asignacion', $idAsignacion)->first();

        $dieselRegistrado = $flowRecord && $flowRecord->comprobante !== null;
        $viajeIniciado = $flowRecord && $flowRecord->viaje_iniciado !== null;
        $viajeFinalizado = $flowRecord && $flowRecord->viaje_finalizado !== null;

        $fotos = [];
        if ($flowRecord && $flowRecord->fotos_carga) {
            $decoded = json_decode($flowRecord->fotos_carga, true);
            if (is_array($decoded)) {
                foreach ($decoded as $path) {
                    $fotos[] = asset($path);
                }
            }
        }

        $fotosFin = [];
        if ($flowRecord && $flowRecord->fotos_fin) {
            $decodedFin = json_decode($flowRecord->fotos_fin, true);
            if (is_array($decodedFin)) {
                foreach ($decodedFin as $path) {
                    $fotosFin[] = asset($path);
                }
            }
        }

        return [
            'success' => true,
            'message' => 'Estatus obtenido con éxito.',
            'data' => [
                'diesel_registrado' => $dieselRegistrado,
                'diesel_datos' => $dieselRegistrado ? [
                    'costo' => $flowRecord->costo,
                    'fecha' => $flowRecord->created_at ? $flowRecord->created_at->toDateString() : Carbon::now()->toDateString(),
                    'comprobante' => asset($flowRecord->comprobante),
                    'litros' => $flowRecord->litros,
                    'odometro' => $flowRecord->odometro,
                    'latitud' => $flowRecord->latitud,
                    'longitud' => $flowRecord->longitud,
                    'litros_urea' => $flowRecord->litros_urea,
                    'costo_urea' => $flowRecord->costo_urea,
                    'comprobante_urea' => $flowRecord->comprobante_urea ? asset($flowRecord->comprobante_urea) : null,
                ] : null,
                'viaje_iniciado' => $viajeIniciado,
                'fotos' => $fotos,
                'viaje_finalizado' => $viajeFinalizado,
                'fotos_fin' => $fotosFin
            ],
            'status' => 200
        ];
    }

    public function getEmpresasPropias()
    {
        $empresas = DB::table('empresas')
            ->where('id_tipo_empresa', 1)
            ->where('estatus', 1)
            ->select('id', 'nombre')
            ->get();

        return [
            'success' => true,
            'message' => 'Empresas propias obtenidas con éxito.',
            'data' => $empresas,
            'status' => 200
        ];
    }
}
