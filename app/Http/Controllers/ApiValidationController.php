<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Operador;
use App\Models\Equipo;
use App\Models\DocumCotizacion;
use App\Models\Asignaciones;
use App\Models\Cotizaciones;
use App\Models\Client;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Coordenadas;
use App\Models\coordenadashistorial;
use App\Models\ComprobanteGastos;
use App\Models\GastosOperadores;
use Carbon\Carbon;

class ApiValidationController extends Controller
{
    /**
     * Helper to return standard JSON structure.
     */
    private function apiResponse($success, $message, $data = [], $status = 200)
    {
        return response()->json([
            'success' => $success,
            'mensaje' => $message,
            'data'    => $data
        ], $status);
    }

    /**
     * API Login for SGT system.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return $this->apiResponse(false, 'Las credenciales de acceso son incorrectas.', [], 401);
        }

        $user = Auth::user();

        if (!$user->can('SGT-Acceso')) {
            Auth::logout();
            return $this->apiResponse(false, 'Tu usuario no tiene acceso al sistema SGT.', [], 403);
        }

        // Return user info and basic token
        $token = $user->createToken('sgt-api-token')->plainTextToken;

        return $this->apiResponse(true, 'Inicio de sesión exitoso.', [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'id_empresa' => $user->id_empresa,
                'roles' => $user->roles()->pluck('name')->toArray(),
                'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            ]
        ]);
    }

    /**
     * Validate Operator, Trip and Unit details all in a single payload.
     */
    public function validateOperador(Request $request)
    {
        if ($request->has('contrasena')) {
            $request->validate([
                'nombre'     => 'required|string',
                'telefono'   => 'required|string',
                'contrasena' => 'required|string',
            ], [
                'nombre.required' => 'El nombre es requerido.',
                'telefono.required' => 'El teléfono es requerido.',
                'contrasena.required' => 'La contraseña es requerida.',
            ]);

            $reqTelefono = preg_replace('/\D/', '', $request->telefono);
            $operadores = Operador::all();
            $operador = $operadores->first(function($op) use ($reqTelefono) {
                return preg_replace('/\D/', '', $op->telefono) === $reqTelefono;
            });

            if (!$operador) {
                return $this->apiResponse(false, 'Operador no registrado.', [], 404);
            }

            if (stripos($operador->nombre, trim($request->nombre)) === false && stripos(trim($request->nombre), $operador->nombre) === false) {
                return $this->apiResponse(false, 'El nombre del operador no coincide.', [], 400);
            }

            $asignacion = Asignaciones::with(['Camion'])
                ->where('id_operador', $operador->id)
                ->where('password_temporal', $request->contrasena)
                ->first();

            if (!$asignacion) {
                return $this->apiResponse(false, 'Contraseña incorrecta o no hay viaje asignado con esa contraseña.', [], 400);
            }

            $contenedor = DocumCotizacion::find($asignacion->id_contenedor);
            $camion = $asignacion->Camion;

            return $this->apiResponse(true, 'Operador y viaje validados correctamente para ingresar operador.', [
                'id_contenedor' => $contenedor ? $contenedor->id : null,
                'id_operador'   => $operador->id,
                'id_asignacion' => $asignacion->id,
                'nombre'        => $operador->nombre,
                'num_contenedor' => $contenedor ? $contenedor->num_contenedor : '',
                'unidad'        => $camion ? $camion->id_equipo : '',
                'telefono'      => $operador->telefono,
                'token'         => 'operador_session_' . $operador->id,
                'id_equipo'     => $camion ? $camion->id_equipo : '',
            ]);
        } else {
            $request->validate([
                'nombre'     => 'required|string',
                'telefono'   => 'required|string',
                'unidad'     => 'required|string',
                'contenedor' => 'required|string',
            ]);

            // 1. Validate container exists
            $contenedor = DocumCotizacion::where('num_contenedor', $request->contenedor)->first();
            if (!$contenedor) {
                return $this->apiResponse(false, 'Contenedor no registrado en cotizaciones.', [], 404);
            }

            // 2. Validate Assignment (Trip) exists for this container
            $asignacion = Asignaciones::with(['Operador', 'Camion'])
                ->where('id_contenedor', $contenedor->id)
                ->first();

            if (!$asignacion) {
                return $this->apiResponse(false, 'El viaje/asignación no existe para este contenedor.', [
                    'id_contenedor' => $contenedor->id
                ], 404);
            }

            // 3. Validate Unit (Camion) matches the assignment
            $camion = $asignacion->Camion;
            if (!$camion || strtolower(trim($camion->id_equipo)) !== strtolower(trim($request->unidad))) {
                return $this->apiResponse(false, 'La unidad no coincide con el viaje asignado.', [
                    'id_contenedor' => $contenedor->id,
                    'unidad_asignada' => $camion ? $camion->id_equipo : 'Ninguna'
                ], 400);
            }

            // 4. Validate Operator matches the assignment
            $operador = $asignacion->Operador;
            if (!$operador) {
                return $this->apiResponse(false, 'No hay operador asignado a este viaje.', [
                    'id_contenedor' => $contenedor->id
                ], 400);
            }

            // Check name (soft match)
            if (stripos($operador->nombre, trim($request->nombre)) === false && stripos(trim($request->nombre), $operador->nombre) === false) {
                return $this->apiResponse(false, 'El nombre del operador no coincide con el viaje asignado.', [
                    'id_contenedor' => $contenedor->id
                ], 400);
            }

            // Check phone
            $reqTelefono = preg_replace('/\D/', '', $request->telefono);
            $dbTelefono = preg_replace('/\D/', '', $operador->telefono);
            if ($reqTelefono !== $dbTelefono) {
                return $this->apiResponse(false, 'El teléfono no coincide con el operador asignado.', [
                    'id_contenedor' => $contenedor->id
                ], 400);
            }

            // All details validated successfully!
            return $this->apiResponse(true, 'Operador y viaje validados correctamente para ingresar operador.', [
                'id_contenedor' => $contenedor->id,
                'id_operador'   => $operador->id,
                'id_asignacion' => $asignacion->id,
                'nombre'        => $operador->nombre,
                'num_contenedor' => $contenedor->num_contenedor,
                'unidad'        => $camion->id_equipo,
                'telefono'      => $operador->telefono,
                'token'         => 'operador_session_' . $operador->id,
                'id_equipo'     => $camion->id_equipo,
            ]);
        }
    }

    public function getOperacionActiva(Request $request)
    {
        $user = $request->user();
        $empresaId = $request->id_empresa ?? $user->id_empresa;

        // Construir la consulta base
        $query = Cotizaciones::where('id_empresa', $empresaId)
            ->where('jerarquia', '!=', 'Secundario')
            ->wherein('tipo_viaje_seleccion', ['foraneo','local_to_foraneo'])
            ->where(function($q) {
                $q->where('estatus', '!=', 'Finalizado')
                  ->orWhere('updated_at', '>=', now('America/Mexico_City')->subDays(15));
            });

        // Filtrar si el usuario tiene proveedores asociados (lógica MEP / Proveedores)
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
            // Obtener viaje activo
            $viaje = $cotizacion->viajes->firstWhere('estado', 'activo');

            $costosForm = [];
            $totalCostosViaje = 0;

            if ($viaje) {
                // Llenar el mapa de costos base para visualización con normalización de acentos y mayúsculas
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

                // Calcular total de costos específicos del viaje: base_factura, base_taref, iva, retencion
                $tieneRetencionCost = false;
                foreach ($viaje->costos as $costo) {
                    $conceptoNorm = trim(strtolower($costo->concepto));
                    $conceptoNorm = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $conceptoNorm);

                    if (in_array($conceptoNorm, ['base_factura', 'base_taref', 'iva', 'retencion'])) {
                        $monto = (float) $costo->monto;
                        if (str_contains($conceptoNorm, 'retencion')) {
                            $totalCostosViaje -= $monto; // Retencion siempre resta
                            $tieneRetencionCost = true;
                        } elseif ($costo->tipo_operacion === 'descuento') {
                            $totalCostosViaje -= $monto;
                        } else {
                            $totalCostosViaje += $monto;
                        }
                    }
                }

                // Determinar el monto de la retención de la cotización
                $retMonto = 0;
                if (!empty($cotizacion->retencion) && (float) $cotizacion->retencion > 0) {
                    $retMonto = (float) $cotizacion->retencion;
                } elseif ($cotizacion->retencion_automatica == 1 && !empty($cotizacion->base_factura)) {
                    $retMonto = (float) $cotizacion->base_factura * 0.04;
                }

                // Si el viaje no tiene costo de retención, pero la cotización sí tiene (o es automática), lo restamos y mostramos
                if (!$tieneRetencionCost && $retMonto > 0) {
                    $totalCostosViaje -= $retMonto;
                    $costosForm['retencion'] = $retMonto;
                }
            } else {
                // Si no hay viaje, pero la cotización tiene retención configurada o automática, la asignamos
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

            // Consultar los gastos extras del nuevo módulo refacturado de gastos
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
                // Omitir si falla el servicio
            }

            $costoTotalCalculado = $totalCostosViaje + $gastosTotal;

            // Concatenar contenedor primario y secundario si es Full
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


            $estatus =$cotizacion->estatus;

            if($cotizacion->estatus_planeacion ==1 && $estatus =='Aprobada'){
                 $estatus='Planeada';
            }

            $url_llegada =  $cotizacion->latitud . $cotizacion->longitud;

            // Debugging log to storage/logs/laravel.log
            \Log::info("Debug Operacion ID {$cotizacion->id}:", [
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
                'est_plane'=> $cotizacion->estatus_planeacion?? null,
                'total' => $cotizacion->total, // Total base de cotización
                'total_costos_viaje' => $totalCostosViaje,
                'gastos_total' => $gastosTotal,
                'gastos_detalle' => $gastosDetalle,
                'costo_total_calculado' => $costoTotalCalculado,
                'debug_viaje_costos' => $viaje ? $viaje->costos->map(fn($c) => ['concepto' => $c->concepto, 'monto' => $c->monto, 'tipo_operacion' => $c->tipo_operacion]) : [],
                'debug_cotizacion_retencion' => $cotizacion->retencion,

                // Detalle del operador y unidad (Viaje)
                'operador' => $asignacion?->Operador?->nombre ?? 'Sin Asignar',
                'unidad' => $asignacion?->Camion?->id_equipo ?? 'Ninguna',

                // Detalles del contenedor y terminal
                'terminal' => $cotizacion->DocCotizacion?->terminal ?? 'N/A',
                'naviera' => $cotizacion->DocCotizacion?->naviera?->naviera ?? 'N/A',
                'boleta_liberacion' => $cotizacion->DocCotizacion?->boleta_liberacion ?? '',
                'num_boleta_liberacion' => $cotizacion->DocCotizacion?->num_boleta_liberacion ?? '',

                // Costos detallados extraídos del mapa de costos
                'costos_detalle' => empty($costosForm) ? (object)[] : $costosForm
            ];
        });

        return $this->apiResponse(true, 'Operaciones activas de la empresa obtenidas con éxito.', [
            'data' => $data
        ]);
    }

    public function getCotizaciones(Request $request)
    {
        $user = $request->user();
        $empresaId = $request->id_empresa ?? $user->id_empresa;

        // Consultar cotizaciones uniendo con clientes y documentos de cotización para retornar los datos correctos
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

               return $this->apiResponse(true, 'Cotizaciones obtenidas con éxito.', [
            'data'        => $cotizaciones
        ]);
    }

    /**
     * Obtener viajes filtrados por la empresa del usuario
     */
    public function getViajes(Request $request)
    {
        $user = $request->user();
        $empresaId = $request->id_empresa ?? $user->id_empresa;

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

          return $this->apiResponse(true, 'Viajes obtenidos con éxito.', [
            'data'        => $viajes
        ]);

    }

    /**
     * Obtener contenedores filtrados por la empresa del usuario
     */
    public function getContenedores(Request $request)
    {
        $user = $request->user();
        $empresaId = $request->id_empresa ?? $user->id_empresa;

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

              return $this->apiResponse(true, 'Contenedores obtenidos con éxito.', [
            'data'        => $contenedores
        ]);

        }

    /**
     * Obtener estadísticas y reportes filtrados por la empresa del usuario
     */
    public function getReportes(Request $request)
    {
        $user = $request->user();
        $empresaId = $request->id_empresa ?? $user->id_empresa;

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
            return $this->apiResponse(true, 'Estadísticas y reportes de operación por empresa.', [
                    'data'        => $totales
                ]);

    }

    public function getMonitoreo(Request $request)
    {
        $coordenadasController = app(CoordenadasController::class);
        $response = $coordenadasController->getEquiposGps($request);

        return $this->apiResponse(true, 'Datos de monitoreo obtenidos con éxito.', $response->getData());
    }

    public function finalizarViaje(Request $request)
    {
        $idContenedor = $request->idContenedor ?? $request->idContenendor;
        if (empty($idContenedor)) {
            return $this->apiResponse(false, 'El ID de contenedor es requerido.', [], 400);
        }

        $contenedor = DocumCotizacion::find($idContenedor);
        if (!$contenedor) {
            return $this->apiResponse(false, 'Contenedor no encontrado.', [], 404);
        }

        $cotizacion = Cotizaciones::find($contenedor->id_cotizacion);
        if ($cotizacion) {
            $cotizacion->estatus = 'Finalizado';
            $cotizacion->update();
        }

        return $this->apiResponse(true, 'Viaje finalizado con éxito.', [
            'titulo' => 'Viaje finalizado',
            'mensaje' => 'Has finalizado correctamente el viaje'
        ]);
    }

    public function infoViaje(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ]);

        $docCotizacion = DocumCotizacion::where('id', '=', $request->id)->first();
        if (!$docCotizacion) {
            return $this->apiResponse(false, 'Contenedor no encontrado.', [], 404);
        }

        $asignaciones = Asignaciones::where('id_contenedor', '=', $request->id)->first();
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

            // Helper para validar si el archivo existe físicamente en el disco
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

        // Sincronizar el primer registro original con las rutas físicas validadas
        if ($documentosFirst && $firstChecked) {
            $documentosFirst->doc_ccp = $firstChecked['doc_ccp'];
            $documentosFirst->doda = $firstChecked['doda'];
            $documentosFirst->boleta_liberacion = $firstChecked['boleta_liberacion'];
            $documentosFirst->carta_porte = $firstChecked['carta_porte'];
            $documentosFirst->carta_porte_xml = $firstChecked['carta_porte_xml'];
            $documentosFirst->boleta_vacio = $firstChecked['boleta_vacio'];
            $documentosFirst->doc_eir = $firstChecked['doc_eir'];
        }

        return $this->apiResponse(true, 'Información del viaje obtenida con éxito.', [
            "nombre" => $asignaciones?->Operador?->nombre ?? $documentosFirst?->transportista_nombre ?? '',
            "tipo" => "Viaje " . ($documentosFirst?->tipo_contrato ?? ''),
            "cotizacion" => $cotizacion,
            "cliente" => $cotizacion->Cliente,
            "subcliente" => $cotizacion->Subcliente,
            "documentos" => $documentosFirst,
            "documents" => $firstChecked
        ]);
    }

    public function guardarCoordenadas(Request $request)
    {
        $idAsignacion = $request->id_asignacion;
        $asignacion = Asignaciones::find($idAsignacion);

        if (!$asignacion) {
            return $this->apiResponse(false, 'Asignación no encontrada.', [], 404);
        }

        // Save coordinate in history
        if ($request->latitud && $request->longitud) {
            coordenadashistorial::create([
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'registrado_en' => Carbon::now(),
                'ubicacionable_id' => $asignacion->id_camion,
                'ubicacionable_type' => 'App\Models\Equipo',
                'tipo' => 'OperadorMovil'
            ]);
        }

        // Save diesel ticket if uploaded
        if ($request->ticket_foto_base64) {
            $path = public_path('/uploads/diesel/' . $idAsignacion);
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            $fileName = uniqid() . '_diesel_ticket.jpg';
            file_put_contents($path . '/' . $fileName, base64_decode($request->ticket_foto_base64));

            // Get id_cotizacion
            $doc = DocumCotizacion::find($asignacion->id_contenedor);
            $idCotizacion = $doc ? $doc->id_cotizacion : null;
            $cotizacion = Cotizaciones::find($idCotizacion);

            // Create Gasto record
            GastosOperadores::create([
                'id_asignacion' => $idAsignacion,
                'id_operador' => $asignacion->id_operador,
                'id_cotizacion' => $idCotizacion,
                'cantidad' => $request->costo ?? 0.0,
                'tipo' => 'Diesel',
                'estatus' => 'pendiente',
                'comprobante' => 'uploads/diesel/' . $idAsignacion . '/' . $fileName,
                'fecha_pago' => Carbon::now()
            ]);

            // Save also in comprobantes_gastos
            ComprobanteGastos::create([
                'id_asignacion' => $idAsignacion,
                'imagen' => 'uploads/diesel/' . $idAsignacion . '/' . $fileName,
                'tipo' => 'diesel'
            ]);

            // Decode and save urea ticket if uploaded
            $ureaFileName = null;
            if ($request->ticket_foto_urea_base64) {
                $ureaFileName = uniqid() . '_urea_ticket.jpg';
                file_put_contents($path . '/' . $ureaFileName, base64_decode($request->ticket_foto_urea_base64));
            }

            // Save in detailed mobile records table
            \App\Models\RegistroDieselOperador::create([
                'id_asignacion' => $idAsignacion,
                'id_operador' => $asignacion->id_operador,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'litros' => $request->litros,
                'costo' => $request->costo,
                'odometro' => $request->odometro,
                'comprobante' => 'uploads/diesel/' . $idAsignacion . '/' . $fileName,
                'litros_urea' => $request->litros_urea,
                'costo_urea' => $request->costo_urea,
                'comprobante_urea' => $ureaFileName ? 'uploads/diesel/' . $idAsignacion . '/' . $ureaFileName : null,
            ]);


            //sincronizar con captura de litros diesel

             $cotizacion->litros_diesel = $request->litros;
             $cotizacion->litros_urea = $request->litros_urea;
             $cotizacion->update();
        }

        return $this->apiResponse(true, 'Coordenadas y registro de diésel guardados con éxito.');
    }

    public function iniciarViaje(Request $request)
    {
        $idAsignacion = $request->id_asignacion;
        $asignacion = Asignaciones::find($idAsignacion);

        if (!$asignacion) {
            return $this->apiResponse(false, 'Asignación no encontrada.', [], 404);
        }

        // Save coordinate in history
        if ($request->latitud && $request->longitud) {
            coordenadashistorial::create([
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'registrado_en' => Carbon::now(),
                'ubicacionable_id' => $asignacion->id_camion,
                'ubicacionable_type' => 'App\Models\Equipo',
                'tipo' => 'OperadorMovil'
            ]);
        }

        // Decode and save images
        $savedFilePaths = [];
        if ($request->fotos_base64 && is_array($request->fotos_base64)) {
            $path = public_path('/uploads/carga_contenedor/' . $idAsignacion);
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            foreach ($request->fotos_base64 as $index => $base64Str) {
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

        // Get id_cotizacion
        $doc = DocumCotizacion::find($asignacion->id_contenedor);
        $idCotizacion = $doc ? $doc->id_cotizacion : null;

        // Update tracking status to starting trip (legacy/admin compatibility)
        $coordenada = Coordenadas::firstOrCreate([
            'id_asignacion' => $idAsignacion,
            'id_cotizacion' => $idCotizacion
        ]);
        $coordenada->update([
            'cargado_contenedor' => 'Cargado - Inicio Viaje',
            'cargado_contenedor_datatime' => Carbon::now()
        ]);

        // Save in detailed operator flow tracking model
        $flowRecord = \App\Models\RegistroDieselOperador::firstOrCreate([
            'id_asignacion' => $idAsignacion
        ]);
        $flowRecord->update([
            'id_operador' => $asignacion->id_operador,
            'viaje_iniciado' => Carbon::now(),
            'fotos_carga' => json_encode($savedFilePaths),
        ]);

        return $this->apiResponse(true, 'Viaje iniciado y fotos guardadas correctamente.');
    }

    public function finalizarViajeOperador(Request $request)
    {
        $idAsignacion = $request->id_asignacion;
        $asignacion = Asignaciones::find($idAsignacion);

        if (!$asignacion) {
            return $this->apiResponse(false, 'Asignación no encontrada.', [], 404);
        }

        // Save coordinate in history
        if ($request->latitud && $request->longitud) {
            coordenadashistorial::create([
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'registrado_en' => Carbon::now(),
                'ubicacionable_id' => $asignacion->id_camion,
                'ubicacionable_type' => 'App\Models\Equipo',
                'tipo' => 'OperadorMovil'
            ]);
        }

        // Decode and save images
        $savedFilePaths = [];
        if ($request->fotos_base64 && is_array($request->fotos_base64)) {
            $path = public_path('/uploads/entrega_contenedor/' . $idAsignacion);
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            foreach ($request->fotos_base64 as $index => $base64Str) {
                $fileName = uniqid() . '_entrega_' . ($index + 1) . '.jpg';
                file_put_contents($path . '/' . $fileName, base64_decode($base64Str));

                $relativeUrl = 'uploads/entrega_contenedor/' . $idAsignacion . '/' . $fileName;
                $savedFilePaths[] = $relativeUrl;
            }
        }

        // Save in detailed operator flow tracking model
        $flowRecord = \App\Models\RegistroDieselOperador::firstOrCreate([
            'id_asignacion' => $idAsignacion
        ]);
        $flowRecord->update([
            'id_operador' => $asignacion->id_operador,
            'viaje_finalizado' => Carbon::now(),
            'fotos_fin' => json_encode($savedFilePaths),
            'latitud_fin' => $request->latitud,
            'longitud_fin' => $request->longitud,
        ]);

        return $this->apiResponse(true, 'Viaje finalizado correctamente.');
    }

    public function obtenerEstatusFlujo(Request $request)
    {
        $idAsignacion = $request->id_asignacion;
        if (!$idAsignacion) {
            return $this->apiResponse(false, 'Falta id_asignacion', [], 400);
        }

        $flowRecord = \App\Models\RegistroDieselOperador::where('id_asignacion', $idAsignacion)->first();

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

        return $this->apiResponse(true, 'Estatus obtenido con éxito.', [
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
        ]);
    }

    public function getEmpresasPropias(Request $request)
    {
        $empresas = DB::table('empresas')
            ->where('id_tipo_empresa', 1)
            ->where('estatus', 1)
            ->select('id', 'nombre')
            ->get();

        return $this->apiResponse(true, 'Empresas propias obtenidas con éxito.', [
            'data' => $empresas
        ]);
    }
}
