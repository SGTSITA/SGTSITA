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
use App\Models\GastosOperadores;
use App\Models\BitacoraViajeOperador;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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

        $cliente = \App\Models\Client::find($user->id_cliente);

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
                    'id_cliente' => $user->id_cliente,
                    'cliente_nombre' => $cliente?->nombre ?? null,
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

// Buscar la asignación por la contraseña temporal
$asignacion = Asignaciones::with(['Camion', 'Operador'])
    ->where('password_temporal', $contrasena)
    ->first();

if (!$asignacion) {
    return [
        'success' => false,
        'message' => 'Contraseña incorrecta o no hay viaje asignado con esa contraseña.',
        'data' => [],
        'status' => 400
    ];
}

// Obtener el operador de esa asignación
$operador = $asignacion->Operador;

// Validar teléfono
if (preg_replace('/\D/', '', $operador->telefono) !== $reqTelefono) {
    return [
        'success' => false,
        'message' => 'El teléfono no corresponde a la asignación.',
        'data' => [],
        'status' => 400
    ];
}

// Validar nombre
$nombreBDClean = $this->normalizarTexto($operador->nombre);
$nombreAppClean = $this->normalizarTexto($nombre);

if (
    stripos($nombreBDClean, $nombreAppClean) === false &&
    stripos($nombreAppClean, $nombreBDClean) === false
) {
    return [
        'success' => false,
        'message' => 'El nombre del operador no coincide.',
        'data' => [],
        'status' => 400
    ];
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
        }
    }

    function normalizarTexto($texto) {
        // Convertir a minúsculas
        $texto = mb_strtolower(trim($texto), 'UTF-8');

        // Reemplazar tildes/acentos
        $buscar   = array('á','é','í','ó','ú','ä','ë','ï','ö','ü','à','è','ì','ò','ù');
        $reemplazar = array('a','e','i','o','u','a','e','i','o','u','a','e','i','o','u');
        $texto = str_replace($buscar, $reemplazar, $texto);

        // Normalizar la Ñ (opcional, si quieres que 'ñ' sea igual a 'n')
        // $texto = str_replace('ñ', 'n', $texto);
        // Reemplazar múltiples espacios internos por uno solo
        $texto = preg_replace('/\s+/', ' ', $texto);

        return $texto;
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

    public function getCotizaciones($empresaId, $idCliente = null)
    {
        if ($idCliente === null) {
            $idCliente = auth()->user()->id_cliente ?? 0;
        }

        $query = DB::table('cotizaciones')
            ->leftJoin('clients', 'cotizaciones.id_cliente', '=', 'clients.id')
            ->leftJoin('docum_cotizacion', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->where('cotizaciones.jerarquia', '!=', 'Secundario');

        if ($idCliente != 0) {
            $query->where('cotizaciones.id_cliente', $idCliente);
        } else {
            $query->where('cotizaciones.id_empresa', $empresaId);
        }

        $cotizaciones = $query->select(
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

    public function getViajes($empresaId, $idCliente = null)
    {
        if ($idCliente === null) {
            $idCliente = auth()->user()->id_cliente ?? 0;
        }

        $query = DB::table('asignaciones')
            ->leftJoin('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
            ->leftJoin('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->leftJoin('operadores', 'asignaciones.id_operador', '=', 'operadores.id');

        if ($idCliente != 0) {
            $query->where('cotizaciones.id_cliente', $idCliente);
        } else {
            $query->where('asignaciones.id_empresa', $empresaId);
        }

        $viajes = $query->select(
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

    public function getContenedores($empresaId, $idCliente = null)
    {
        if ($idCliente === null) {
            $idCliente = auth()->user()->id_cliente ?? 0;
        }

        $query = DB::table('docum_cotizacion')
            ->leftJoin('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id');

        if ($idCliente != 0) {
            $query->where('cotizaciones.id_cliente', $idCliente);
        } else {
            $query->where('docum_cotizacion.id_empresa', $empresaId);
        }

        $contenedores = $query->select(
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

    public function getPlaneacion($empresaId, $fechaInicio = null, $fechaFin = null)
    {
        $startDate = $fechaInicio ? \Carbon\Carbon::parse($fechaInicio)->toDateString() : \Carbon\Carbon::now()->subDays(15)->toDateString();
        $endDate = $fechaFin ? \Carbon\Carbon::parse($fechaFin)->toDateString() : \Carbon\Carbon::now()->addDays(15)->toDateString();

        $planeaciones = DB::table('asignaciones')
            ->leftJoin('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
            ->leftJoin('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->leftJoin('operadores', 'asignaciones.id_operador', '=', 'operadores.id')
            ->leftJoin('proveedores', function($join) {
                $join->on('proveedores.id', '=', DB::raw('COALESCE(NULLIF(asignaciones.id_proveedor, 0), NULLIF(cotizaciones.id_proveedor, 0))'));
            })
            ->leftJoin('empresas as em', 'em.id', '=', 'asignaciones.id_empresa')
            ->leftJoin('empresas as emc', 'emc.id', '=', 'cotizaciones.id_empresa')
            ->where('asignaciones.id_empresa', $empresaId)
            ->where('cotizaciones.estatus_planeacion', 1)
            ->whereBetween('asignaciones.fecha_inicio', [$startDate, $endDate])
            ->select(
                'asignaciones.id',
                'docum_cotizacion.num_contenedor as contenedor',
                'asignaciones.fecha_inicio',
                'asignaciones.fecha_fin',
                'operadores.nombre as operador',
                DB::raw("COALESCE(NULLIF(em.nombre, ''), emc.nombre) as proveedor"),
                'proveedores.nombre as transportista',
                'cotizaciones.origen',
                'cotizaciones.destino',
                'cotizaciones.id as cotizacion_id',
                'docum_cotizacion.id as contenedor_id',
                DB::raw("'Planeada' as estatus")
            )
            ->orderBy('asignaciones.fecha_inicio', 'asc')
            ->get();

        return [
            'success' => true,
            'message' => 'Planeaciones obtenidas con éxito.',
            'data' => $planeaciones,
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
            ->leftJoin('proveedores', function($join) {
                $join->on('proveedores.id', '=', DB::raw('COALESCE(NULLIF(asignaciones.id_proveedor, 0), NULLIF(cotizaciones.id_proveedor, 0))'));
            })
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

        // Construct the WhatsApp text for resending:
        $contenedorStr = $firstChecked['num_contenedor'] ?? '';
        $camion = $asignaciones ? \App\Models\Equipo::find($asignaciones->id_camion) : null;
        $unidadEco = $camion ? $camion->id_equipo : '';
        $origen = $cotizacion->origen ?? '';
        $direccion = $cotizacion->direccion_entrega ?? '';
        $lat = $cotizacion->latitud ?? '';
        $lng = $cotizacion->longitud ?? '';
        $contacto = $cotizacion->cp_contacto_entrega ?? '';
        $fechaEntregaRaw = $cotizacion->cp_fecha_tentativa_entrega ?: $cotizacion->fecha_entrega;
        $fechaEntrega = $fechaEntregaRaw ? \Carbon\Carbon::parse($fechaEntregaRaw)->format('d/m/Y') : '';
        $horaLlegada = $cotizacion->cp_hora_tentativa_entrega ?? '';
        $comentarios = $cotizacion->cp_comentarios ?? '';
        $mapLink = ($lat && $lng) ? "https://maps.google.com/?q={$lat},{$lng}" : '';
        $passwordTemporal = $asignaciones ? $asignaciones->password_temporal : '';

        if ($asignaciones && !empty($asignaciones->mensaje_compartido)) {
            $waText = $asignaciones->mensaje_compartido;
        } else {
            $hora = \Carbon\Carbon::now()->hour;
            if ($hora >= 6 && $hora < 12) {
                $saludo = "Buenos días";
            } elseif ($hora >= 12 && $hora < 19) {
                $saludo = "Buenas tardes";
            } else {
                $saludo = "Buenas noches";
            }
            $nombreOp = ($asignaciones && $asignaciones->Operador) ? $asignaciones->Operador->nombre : '';
            $clienteObj = $cotizacion->Cliente;
            $capturaFcpp = $clienteObj ? (bool) $clienteObj->captura_fcpp : false;

            if ($capturaFcpp) {
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
                $waText .= "Contraseña temporal para Operador: " . $passwordTemporal;
            } else {
                $waText = "{$saludo} " . ($nombreOp ? trim($nombreOp) : "Operador") . ",\n\n";
                $waText .= "Comparto los datos de salida del día de hoy:\n\n";
                $waText .= "{$contenedorStr}" . ($unidadEco ? "-{$unidadEco}" : "") . "\n";
                $waText .= "Puerto / Lugar de salida:\n" . ($origen ?: "") . "\n";
                $waText .= "Domicilio de entrega: " . ($direccion ?: "") . "\n";
                $waText .= "Mapa: " . ($mapLink ?: "") . "\n";
                $waText .= "Contacto: \n";
                $waText .= "Fecha de entrega:\n" . ($fechaEntrega ?: "") . "\n";
                $waText .= "Hora de llegada a bodega:\n\n";
                $waText .= "Hora de salida: \n";
                $waText .= "Comentarios:\n\n";
                $waText .= "Contraseña temporal para Operador: " . $passwordTemporal;
            }
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
                "documents" => $firstChecked,
                "wa_text" => $waText
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

        // Verificar si ya existe un gasto de diésel pagado en esta asignación
        $dieselPagadoExistente = \App\Models\Gasto::where('origen_legacy_id', $idAsignacion)
            ->where('origen_legacy', 'like', 'asignacion_planeacion%')
            ->where('concepto', 'like', '%Diesel%')
            ->where('estatus', 'pagado')
            ->exists();

        // Verificar si ya existe un gasto de urea pagado en esta asignación
        $ureaPagadaExistente = \App\Models\Gasto::where('origen_legacy_id', $idAsignacion)
            ->where('origen_legacy', 'like', 'asignacion_planeacion%')
            ->where('concepto', 'like', '%Urea%')
            ->where('estatus', 'pagado')
            ->exists();

        $path = public_path('/uploads/diesel/' . $idAsignacion);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $savedFilePaths = [];
        if (isset($data['ticket_foto_base64']) && !empty($data['ticket_foto_base64'])) {
            $rawFotos = $data['ticket_foto_base64'];
            if (is_string($rawFotos)) {
                $decoded = json_decode($rawFotos, true);
                if (is_array($decoded)) {
                    $rawFotos = $decoded;
                } else {
                    $rawFotos = [$rawFotos];
                }
            }
            if (is_array($rawFotos)) {
                $rawFotos = array_slice($rawFotos, 0, 3);
                foreach ($rawFotos as $index => $base64Str) {
                    if (empty($base64Str)) continue;
                    $cleanDieselBase64 = $base64Str;
                    if (preg_match('/^data:image\/(\w+);base64,/', $cleanDieselBase64, $type)) {
                        $cleanDieselBase64 = substr($cleanDieselBase64, strpos($cleanDieselBase64, ',') + 1);
                    }
                    $fileSuffix = uniqid() . '_diesel_ticket_' . ($index + 1) . '.jpg';
                    file_put_contents($path . '/' . $fileSuffix, base64_decode($cleanDieselBase64));
                    $savedFilePaths[] = 'uploads/diesel/' . $idAsignacion . '/' . $fileSuffix;
                }
            }
        }
        $fileName = !empty($savedFilePaths) ? json_encode($savedFilePaths) : null;

        if ($fileName) {
            if (!$dieselPagadoExistente) {
                $doc = DocumCotizacion::find($asignacion->id_contenedor);
                $idCotizacion = $doc ? $doc->id_cotizacion : null;

                $gastoOperador = GastosOperadores::create([
                    'id_asignacion' => $idAsignacion,
                    'id_operador' => $asignacion->id_operador,
                    'id_cotizacion' => $idCotizacion,
                    'cantidad' => $data['costo'] ?? 0.0,
                    'tipo' => 'Diesel',
                    'estatus' => 'pendiente',
                    'comprobante' => $fileName,
                    'fecha_pago' => Carbon::now()
                ]);

                try {
                    app(\App\Services\GastosService::class)->registrarDesdeGastoOperador($gastoOperador);
                } catch (\Exception $e) {
                    \Log::error("Error registrando gasto de diesel en gastos: " . $e->getMessage());
                }
            } else {
                \Log::info("El diésel para la asignación ID {$idAsignacion} ya se encuentra pagado. Se omitió la sobrescritura del gasto.");
            }
        }

        $savedUreaFilePaths = [];
        if (isset($data['ticket_foto_urea_base64']) && !empty($data['ticket_foto_urea_base64'])) {
            $rawUreaFotos = $data['ticket_foto_urea_base64'];
            if (is_string($rawUreaFotos)) {
                $decoded = json_decode($rawUreaFotos, true);
                if (is_array($decoded)) {
                    $rawUreaFotos = $decoded;
                } else {
                    $rawUreaFotos = [$rawUreaFotos];
                }
            }
            if (is_array($rawUreaFotos)) {
                $rawUreaFotos = array_slice($rawUreaFotos, 0, 3);
                foreach ($rawUreaFotos as $index => $base64Str) {
                    if (empty($base64Str)) continue;
                    $cleanUreaBase64 = $base64Str;
                    if (preg_match('/^data:image\/(\w+);base64,/', $cleanUreaBase64, $type)) {
                        $cleanUreaBase64 = substr($cleanUreaBase64, strpos($cleanUreaBase64, ',') + 1);
                    }
                    $ureaFileSuffix = uniqid() . '_urea_ticket_' . ($index + 1) . '.jpg';
                    file_put_contents($path . '/' . $ureaFileSuffix, base64_decode($cleanUreaBase64));
                    $savedUreaFilePaths[] = 'uploads/diesel/' . $idAsignacion . '/' . $ureaFileSuffix;
                }
            }
        }
        $ureaFileName = !empty($savedUreaFilePaths) ? json_encode($savedUreaFilePaths) : null;

        if (isset($data['costo_urea']) && floatval($data['costo_urea']) > 0) {
            if (!$ureaPagadaExistente) {
                $this->registrarGastoUreaDesdeApp(intval($idAsignacion), floatval($data['costo_urea']));
            } else {
                \Log::info("La urea para la asignación ID {$idAsignacion} ya se encuentra pagada. Se omitió la sobrescritura del gasto.");
            }
        }

        $flowRecord = BitacoraViajeOperador::firstOrCreate([
            'id_asignacion' => $idAsignacion
        ]);

        $updateFields = [
            'id_operador' => $asignacion->id_operador,
            'latitud' => $data['latitud'] ?? null,
            'longitud' => $data['longitud'] ?? null,
            'litros' => $data['litros'] ?? null,
            'costo' => $data['costo'] ?? null,
            'odometro' => $data['odometro'] ?? null,
            'comprobante' => $fileName ? $fileName : $flowRecord->comprobante,
            'litros_urea' => $data['litros_urea'] ?? null,
            'costo_urea' => $data['costo_urea'] ?? null,
            'comprobante_urea' => $ureaFileName ? $ureaFileName : $flowRecord->comprobante_urea,
        ];

        if (isset($data['litros']) || isset($data['costo']) || $fileName) {
            $updateFields['fecha_carga_diesel'] = Carbon::now();
        }

        if (isset($data['litros_urea']) || isset($data['costo_urea']) || $ureaFileName) {
            $updateFields['fecha_carga_urea'] = Carbon::now();
        }

        $flowRecord->update($updateFields);

        try {
            $this->actualizarKmRecorridosPorCoordenadas($asignacion);
        } catch (\Exception $e) {
            \Log::error("Error actualizando kilometraje por coordenadas: " . $e->getMessage());
        }

        if (isset($data['odometro']) && floatval($data['odometro']) > 0) {
            $this->actualizarKmRecorridosPorOdometro($asignacion, floatval($data['odometro']));
        }

        // Actualizar cotización si no están pagados
        $doc = DocumCotizacion::find($asignacion->id_contenedor);
        $idCotizacion = $doc ? $doc->id_cotizacion : null;
        $cotizacion = Cotizaciones::find($idCotizacion);

        if ($cotizacion) {
            if (!$dieselPagadoExistente && isset($data['litros'])) {
                $cotizacion->litros_diesel = $data['litros'];
            }
            if (!$ureaPagadaExistente && isset($data['litros_urea'])) {
                $cotizacion->litros_urea = $data['litros_urea'];
            }
            $cotizacion->update();
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
        $rawFotos = $data['fotos_base64'] ?? [];
        if (is_string($rawFotos)) {
            $decoded = json_decode($rawFotos, true);
            if (is_array($decoded)) {
                $rawFotos = $decoded;
            } else {
                $rawFotos = [$rawFotos];
            }
        }

        if (is_array($rawFotos) && !empty($rawFotos)) {
            $path = public_path('/uploads/carga_contenedor/' . $idAsignacion);
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            foreach ($rawFotos as $index => $base64Str) {
                if (empty($base64Str)) continue;
                $cleanBase64 = $base64Str;
                if (preg_match('/^data:image\/(\w+);base64,/', $cleanBase64, $type)) {
                    $cleanBase64 = substr($cleanBase64, strpos($cleanBase64, ',') + 1);
                }
                $fileName = uniqid() . '_carga_' . ($index + 1) . '.jpg';
                file_put_contents($path . '/' . $fileName, base64_decode($cleanBase64));

                $relativeUrl = 'uploads/carga_contenedor/' . $idAsignacion . '/' . $fileName;
                $savedFilePaths[] = $relativeUrl;
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

        $flowRecord = BitacoraViajeOperador::firstOrCreate([
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
        $rawFotos = $data['fotos_base64'] ?? [];
        if (is_string($rawFotos)) {
            $decoded = json_decode($rawFotos, true);
            if (is_array($decoded)) {
                $rawFotos = $decoded;
            } else {
                $rawFotos = [$rawFotos];
            }
        }

        if (is_array($rawFotos) && !empty($rawFotos)) {
            $path = public_path('/uploads/entrega_contenedor/' . $idAsignacion);
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            foreach ($rawFotos as $index => $base64Str) {
                if (empty($base64Str)) continue;
                $cleanBase64 = $base64Str;
                if (preg_match('/^data:image\/(\w+);base64,/', $cleanBase64, $type)) {
                    $cleanBase64 = substr($cleanBase64, strpos($cleanBase64, ',') + 1);
                }
                $fileName = uniqid() . '_entrega_' . ($index + 1) . '.jpg';
                file_put_contents($path . '/' . $fileName, base64_decode($cleanBase64));

                $relativeUrl = 'uploads/entrega_contenedor/' . $idAsignacion . '/' . $fileName;
                $savedFilePaths[] = $relativeUrl;
            }
        }

        $flowRecord = BitacoraViajeOperador::firstOrCreate([
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

        $flowRecord = BitacoraViajeOperador::where('id_asignacion', $idAsignacion)->first();

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

        $documentos = [];
        $asignacion = Asignaciones::with('Contenedor')->find($idAsignacion);
        if ($asignacion && $asignacion->Contenedor) {
            $contenedor = $asignacion->Contenedor;
            $idCotizacion = $contenedor->id_cotizacion;

            // Consultar la configuración global de documentos permitidos para el operador
            $allowedFields = null;
            try {
                $config = DB::table('global_configs')->where('key', 'documentos_operador')->first();
                if ($config && !empty($config->value)) {
                    $decoded = json_decode($config->value, true);
                    if (is_array($decoded)) {
                        $allowedFields = array_map(function($val) {
                            return strtolower(trim($val));
                        }, $decoded);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("No se pudo consultar global_configs o decodificar su valor: " . $e->getMessage());
            }

            $fields = [
                'boleta_liberacion' => 'Boleta de Liberación',
                'doda' => 'Documento DODA',
                'boleta_vacio' => 'Boleta de Vacío',
                'doc_eir' => 'Documento EIR',
                'doc_ccp' => 'Documento CCP',
                'boleta_patio' => 'Boleta de Patio',
                'evidencia_descarga' => 'Evidencia de Descarga'
            ];

            foreach ($fields as $field => $label) {
                // Si la configuración especifica qué documentos mostrar, filtrar
                if ($allowedFields !== null) {
                    $fieldLower = strtolower($field);
                    $labelLower = strtolower($label);
                    $isAllowed = false;
                    foreach ($allowedFields as $allowed) {
                        if ($allowed === $fieldLower || $allowed === $labelLower || str_contains($fieldLower, $allowed) || str_contains($labelLower, $allowed)) {
                            $isAllowed = true;
                            break;
                        }
                    }
                    if (!$isAllowed) {
                        continue;
                    }
                }

                if (!empty($contenedor->$field)) {
                    $fileName = $contenedor->$field;
                    $url = str_starts_with($fileName, 'http')
                        ? $fileName
                        : asset('cotizaciones/cotizacion' . $idCotizacion . '/' . $fileName);

                    $documentos[] = [
                        'nombre' => $label,
                        'url' => $url
                    ];
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
                    'comprobante' => self::formatAssetUrls($flowRecord->comprobante),
                    'litros' => $flowRecord->litros,
                    'odometro' => $flowRecord->odometro,
                    'latitud' => $flowRecord->latitud,
                    'longitud' => $flowRecord->longitud,
                    'litros_urea' => $flowRecord->litros_urea,
                    'costo_urea' => $flowRecord->costo_urea,
                    'comprobante_urea' => self::formatAssetUrls($flowRecord->comprobante_urea),
                ] : null,
                'viaje_iniciado' => $viajeIniciado,
                'fotos' => $fotos,
                'viaje_finalizado' => $viajeFinalizado,
                'fotos_fin' => $fotosFin,
                'id_cotizacion' => $asignacion->Contenedor->id_cotizacion ?? null,
                'documentos_viaje' => $documentos
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

    /**
     * Registra el gasto de urea como tipo periodo, al inicio del mes y pendiente de pago.
     *
     * @param int $idAsignacion
     * @param float $montoUrea
     * @return void
     */
    public function registrarGastoUreaDesdeApp(int $idAsignacion, float $montoUrea)
    {
        if ($montoUrea <= 0) {
            return;
        }

        try {
            $asignacion = Asignaciones::find($idAsignacion);
            if (!$asignacion) {
                Log::warning("No se encontró la asignación ID {$idAsignacion} al registrar gasto de urea.");
                return;
            }

            // Resolver id_empresa dinámicamente
            $idEmpresa = $asignacion->id_empresa;
            $contenedor = DocumCotizacion::find($asignacion->id_contenedor);
            $cotizacion = null;
            if ($contenedor) {
                $cotizacion = Cotizaciones::find($contenedor->id_cotizacion);
                if ($cotizacion && !$idEmpresa) {
                    $idEmpresa = $cotizacion->id_empresa;
                }
            }
            if (!$idEmpresa) {
                $idEmpresa = 27; // Default to 27 or 1
            }

            $categoriaId = 1; // Combustible / Combustibles

            $con = DB::table('gasto_conceptos')->where('clave', 'GUREA')->orWhere('nombre', 'like', '%Urea%')->first();
            $conceptoId = $con ? $con->id : 42;

            app(\App\Services\GastosService::class)->registrar([
                'id_empresa'          => $idEmpresa,
                'categoria_gasto_id'  => $categoriaId,
                'gasto_concepto_id'   => $conceptoId,
                'concepto'            => 'GU001 - Urea',
                'monto_total'         => $montoUrea,
                'tipo_gasto'          => 'viaje',
                'metodo_imputacion'   => 'directo',
                'estatus'             => 'pendiente_pago',
                'fecha_gasto'         => Carbon::now()->toDateString(),
                'origen_legacy'       => 'asignacion_planeacionGU001 - Urea',
                'origen_legacy_id'    => $asignacion->id,
                'user_id'             => auth()->id() ?? 81,
                'vinculos'            => array_filter([
                    $cotizacion ? [
                        'tipo_vinculo'    => 'cotizacion',
                        'vinculable_type' => get_class($cotizacion),
                        'vinculable_id'   => $cotizacion->id,
                    ] : null,
                    $contenedor ? [
                        'tipo_vinculo'    => 'contenedor',
                        'vinculable_type' => get_class($contenedor),
                        'vinculable_id'   => $contenedor->id,
                        'observaciones'   => $contenedor->num_contenedor,
                    ] : null,
                    $asignacion ? [
                        'tipo_vinculo'    => 'asignacion',
                        'vinculable_type' => get_class($asignacion),
                        'vinculable_id'   => $asignacion->id,
                    ] : null,
                    $asignacion->id_operador ? [
                        'tipo_vinculo'    => 'operador',
                        'vinculable_type' => \App\Models\Operador::class,
                        'vinculable_id'   => $asignacion->id_operador,
                    ] : null,
                ]),
                'imputaciones'        => []
            ]);

            Log::info("Gasto de Urea (GUREA) registrado exitosamente para asignación ID: {$idAsignacion}");
        } catch (\Exception $e) {
            Log::error("Error en ApiValidationService al registrar gasto de urea: " . $e->getMessage());
        }
    }

    public function getCatalogsProgramarViaje($empresaId)
    {
        // 1. Contenedores aprobados no planeados
        $contenedores = DB::table('docum_cotizacion')
            ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->where('cotizaciones.id_empresa', $empresaId)
            ->where('cotizaciones.estatus', 'Aprobada')
            ->where(function($q) {
                $q->where('cotizaciones.estatus_planeacion', 0)
                  ->orWhereNull('cotizaciones.estatus_planeacion');
            })
            ->select('docum_cotizacion.num_contenedor as nombre', 'docum_cotizacion.num_contenedor as id', 'cotizaciones.referencia_full as referencia_full')
            ->get();

        // 2. Operadores
        $operadores = DB::table('operadores')
            ->where('id_empresa', $empresaId)
            ->select('nombre', 'id')
            ->get();

        // 3. Camiones / Tractos (Equipos tipo camion/tracto)
        $camiones = DB::table('equipos')
            ->where('id_empresa', $empresaId)
            ->where('tipo', 'Tractos / Camiones')
            ->select('id_equipo as nombre', 'id')
            ->get();

        // 4. Chasis / Plataformas (Equipos tipo chasis/plataforma)
        $chasis = DB::table('equipos')
            ->where('id_empresa', $empresaId)
            ->where('tipo', 'Chasis / Plataforma')
            ->select('id_equipo as nombre', 'id')
            ->get();

        return [
            'success' => true,
            'message' => 'Catálogos cargados con éxito.',
            'data' => [
                'contenedores' => $contenedores,
                'operadores' => $operadores,
                'camiones' => $camiones,
                'chasis' => $chasis
            ],
            'status' => 200
        ];
    }

    public function programarViajeMobile(array $data)
    {
        $request = new \Illuminate\Http\Request($data);
        $planeacionController = app(\App\Http\Controllers\PlaneacionController::class);
        $response = $planeacionController->asignacionElemental($request);

        $resData = json_decode($response->getContent(), true) ?? [];
        $resData['status'] = $response->getStatusCode();

        return $resData;
    }

    private static function formatAssetUrls($value)
    {
        if (empty($value)) {
            return null;
        }

        // Check if JSON array
        if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return implode(',', array_map(function($path) {
                    return asset($path);
                }, $decoded));
            }
        }

        // Check if comma separated
        if (str_contains($value, ',')) {
            return implode(',', array_map(function($path) {
                return asset(trim($path));
            }, explode(',', $value)));
        }

        return asset($value);
    }

    private function actualizarKmRecorridosPorOdometro(Asignaciones $asignacion, float $odometroActual)
    {
        if ($odometroActual <= 0) {
            return;
        }

        // 1. UPDATE PREVIOUS TRIP'S KM
        // Find the previous trip for the same truck
        $prevAsignacion = Asignaciones::where('id_camion', $asignacion->id_camion)
            ->where('id', '!=', $asignacion->id)
            ->where(function ($query) use ($asignacion) {
                $query->where('fecha_inicio', '<', $asignacion->fecha_inicio)
                      ->orWhere(function ($q) use ($asignacion) {
                          $q->where('fecha_inicio', '=', $asignacion->fecha_inicio)
                            ->where('id', '<', $asignacion->id);
                      });
            })
            ->orderBy('fecha_inicio', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($prevAsignacion) {
            $prevBitacora = $prevAsignacion->bitacoraViaje;
            if ($prevBitacora && $prevBitacora->odometro > 0) {
                $km = $odometroActual - floatval($prevBitacora->odometro);
                if ($km > 0) {
                    $prevDoc = DocumCotizacion::find($prevAsignacion->id_contenedor);
                    $prevIdCoti = $prevDoc ? $prevDoc->id_cotizacion : null;
                    $prevCotizacion = Cotizaciones::find($prevIdCoti);
                    if ($prevCotizacion) {
                        $prevCotizacion->km_recorridos = $km;
                        $prevCotizacion->save();
                        \Log::info("Auto-calculo de KM: Asignación ID {$prevAsignacion->id} actualizada a {$km} km.");
                    }
                }
            }
        }

        // 2. UPDATE CURRENT TRIP'S KM (If next trip already has odometer)
        // Find the next trip for the same truck
        $nextAsignacion = Asignaciones::where('id_camion', $asignacion->id_camion)
            ->where('id', '!=', $asignacion->id)
            ->where(function ($query) use ($asignacion) {
                $query->where('fecha_inicio', '>', $asignacion->fecha_inicio)
                      ->orWhere(function ($q) use ($asignacion) {
                          $q->where('fecha_inicio', '=', $asignacion->fecha_inicio)
                            ->where('id', '>', $asignacion->id);
                      });
            })
            ->orderBy('fecha_inicio', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        if ($nextAsignacion) {
            $nextBitacora = $nextAsignacion->bitacoraViaje;
            if ($nextBitacora && $nextBitacora->odometro > 0) {
                $km = floatval($nextBitacora->odometro) - $odometroActual;
                if ($km > 0) {
                    $currDoc = DocumCotizacion::find($asignacion->id_contenedor);
                    $currIdCoti = $currDoc ? $currDoc->id_cotizacion : null;
                    $currCotizacion = Cotizaciones::find($currIdCoti);
                    if ($currCotizacion) {
                        $currCotizacion->km_recorridos = $km;
                        $currCotizacion->save();
                        \Log::info("Auto-calculo de KM: Asignación ID {$asignacion->id} actualizada a {$km} km.");
                    }
                }
            }
        }
    }

    private function actualizarKmRecorridosPorCoordenadas(Asignaciones $asignacion)
    {
        $bitacora = $asignacion->bitacoraViaje;
        if (!$bitacora || !$bitacora->latitud || !$bitacora->longitud) {
            return;
        }

        // 1. UPDATE PREVIOUS TRIP'S KM
        $prevAsignacion = Asignaciones::where('id_camion', $asignacion->id_camion)
            ->where('id', '!=', $asignacion->id)
            ->where(function ($query) use ($asignacion) {
                $query->where('fecha_inicio', '<', $asignacion->fecha_inicio)
                      ->orWhere(function ($q) use ($asignacion) {
                          $q->where('fecha_inicio', '=', $asignacion->fecha_inicio)
                            ->where('id', '<', $asignacion->id);
                      });
            })
            ->orderBy('fecha_inicio', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($prevAsignacion) {
            $prevBitacora = $prevAsignacion->bitacoraViaje;
            if ($prevBitacora && $prevBitacora->latitud && $prevBitacora->longitud) {
                $km = $this->obtenerDistanciaPorCarretera(
                    floatval($prevBitacora->latitud),
                    floatval($prevBitacora->longitud),
                    floatval($bitacora->latitud),
                    floatval($bitacora->longitud)
                );
                if ($km > 0) {
                    $prevDoc = DocumCotizacion::find($prevAsignacion->id_contenedor);
                    $prevIdCoti = $prevDoc ? $prevDoc->id_cotizacion : null;
                    $prevCotizacion = Cotizaciones::find($prevIdCoti);
                    if ($prevCotizacion) {
                        $prevCotizacion->km_recorridos = $km;
                        $prevCotizacion->save();
                        \Log::info("Auto-calculo de KM por Coordenadas Diésel: Asignación ID {$prevAsignacion->id} actualizada a {$km} km.");
                    }
                }
            }
        }

        // 2. UPDATE CURRENT TRIP'S KM
        $nextAsignacion = Asignaciones::where('id_camion', $asignacion->id_camion)
            ->where('id', '!=', $asignacion->id)
            ->where(function ($query) use ($asignacion) {
                $query->where('fecha_inicio', '>', $asignacion->fecha_inicio)
                      ->orWhere(function ($q) use ($asignacion) {
                          $q->where('fecha_inicio', '=', $asignacion->fecha_inicio)
                            ->where('id', '>', $asignacion->id);
                      });
            })
            ->orderBy('fecha_inicio', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        if ($nextAsignacion) {
            $nextBitacora = $nextAsignacion->bitacoraViaje;
            if ($nextBitacora && $nextBitacora->latitud && $nextBitacora->longitud) {
                $km = $this->obtenerDistanciaPorCarretera(
                    floatval($bitacora->latitud),
                    floatval($bitacora->longitud),
                    floatval($nextBitacora->latitud),
                    floatval($nextBitacora->longitud)
                );
                if ($km > 0) {
                    $currDoc = DocumCotizacion::find($asignacion->id_contenedor);
                    $currIdCoti = $currDoc ? $currDoc->id_cotizacion : null;
                    $currCotizacion = Cotizaciones::find($currIdCoti);
                    if ($currCotizacion) {
                        $currCotizacion->km_recorridos = $km;
                        $currCotizacion->save();
                        \Log::info("Auto-calculo de KM por Coordenadas Diésel: Asignación ID {$asignacion->id} actualizada a {$km} km.");
                    }
                }
            }
        }
    }

    private function obtenerDistanciaPorCarretera($lat1, $lon1, $lat2, $lon2): float
    {
        if (empty($lat1) || empty($lon1) || empty($lat2) || empty($lon2)) {
            return 0.0;
        }

        $apiKey = env('GOOLEAPIMAPS');
        if ($apiKey) {
            try {
                $url = "https://maps.googleapis.com/maps/api/directions/json?origin={$lat1},{$lon1}&destination={$lat2},{$lon2}&key={$apiKey}";
                $response = Http::timeout(5)->get($url);
                if ($response->successful()) {
                    $json = $response->json();
                    if (isset($json['routes'][0]['legs'][0]['distance']['value'])) {
                        $meters = (float) $json['routes'][0]['legs'][0]['distance']['value'];
                        return round($meters / 1000, 2);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("Error al consultar Google Maps Directions: " . $e->getMessage());
            }
        }

        try {
            $url = "http://router.project-osrm.org/route/v1/driving/{$lon1},{$lat1};{$lon2},{$lat2}?overview=false";
            $response = Http::timeout(5)->get($url);
            if ($response->successful()) {
                $json = $response->json();
                if (isset($json['routes'][0]['distance'])) {
                    $meters = (float) $json['routes'][0]['distance'];
                    return round($meters / 1000, 2);
                }
            }
        } catch (\Exception $e) {
            \Log::warning("Error al consultar OSRM API: " . $e->getMessage());
        }

        return $this->calcularDistanciaHaversine($lat1, $lon1, $lat2, $lon2);
    }

    private function calcularDistanciaHaversine($lat1, $lon1, $lat2, $lon2): float
    {
        if (empty($lat1) || empty($lon1) || empty($lat2) || empty($lon2)) {
            return 0.0;
        }

        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return round($distance * 1.18, 2);
    }
}

