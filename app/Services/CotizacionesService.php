<?php

namespace App\Services;

use App\Models\DocumCotizacion;
use App\Models\Asignaciones;
use App\Models\BitacoraViajeOperador;
use App\Models\Cotizaciones;
use App\Models\User;
use App\Models\Viajes;
use App\Models\ViajesCostos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CotizacionesService
{
    /**
     * Consulta unificada de cotizaciones / viajes.
     * Soporta filtrado por empresa (internos), id_cliente (externos), estatus, estatus_planeacion, rango de fechas y exclusión de locales.
     *
     * @param string|null $estatusSearch
     * @param int|null $estatus_planeacion
     * @param int|null $validarNoplaneadas
     * @param int|null $idCliente
     * @param string|null $fechaInicio
     * @param string|null $fechaFin
     * @param bool $excluirLocales
     * @return \Illuminate\Support\Collection
     */
    public function obtenerCotizacionesparametros(
        $estatusSearch = 'Aprobada',
        $estatus_planeacion = null,
        $validarNoplaneadas = null,
        $idCliente = null,
        $fechaInicio = null,
        $fechaFin = null,
        $excluirLocales = false
    ) {
        $cotizacionesQuery = Cotizaciones::query();

        // 1. Filtrado de Cliente vs Empresa
        if (!is_null($idCliente)) {
            // Si viene idCliente (modo cliente externo / mis-viajes), se filtra por id_cliente y NO por id_empresa
            $cotizacionesQuery->where('id_cliente', $idCliente);
        } else {
            // Modo interno (cotizaciones/index): se filtra por empresa del usuario autenticado si aplica
            if (auth()->check()) {
                $ID_EMPRESA = auth()->user()->id_empresa ?? 0;
                if ($ID_EMPRESA != 0) {
                    $cotizacionesQuery->where('id_empresa', $ID_EMPRESA);
                }
            }
        }

        // 2. Filtro de estatus (si no es nulo ni 'all')
        if (!is_null($estatusSearch) && $estatusSearch !== 'all') {
            $cotizacionesQuery->where('estatus', $estatusSearch);
        }

        // 3. Filtros de planeación
        if (!is_null($estatus_planeacion) && is_null($validarNoplaneadas)) {
            $cotizacionesQuery->where('estatus_planeacion', $estatus_planeacion);
        }

        if ($validarNoplaneadas == 1) {
            $cotizacionesQuery->where(function ($q) {
                $q->where('estatus_planeacion', 0)
                  ->orWhereNull('estatus_planeacion');
            });
        }

        // 4. Exclusión de viajes locales si se requiere
        if ($excluirLocales) {
            $cotizacionesQuery->whereIn('tipo_viaje_seleccion', ['foraneo', 'local_to_foraneo']);
        }

        // 5. Excluir siempre contenedores secundarios directos
        $cotizacionesQuery->where('jerarquia', '!=', 'Secundario');

        // 6. Filtro opcional por rango de fechas en fecha_entrega
        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $cotizacionesQuery->whereBetween('fecha_entrega', [$fechaInicio, $fechaFin]);
        }

        // 7. Filtro por proveedor si el usuario interno pertenece a proveedores
        if (is_null($idCliente) && auth()->check()) {
            $userProveedores = User::find(auth()->user()->id);
            if ($userProveedores && $userProveedores->proveedores()->exists()) {
                $cotizacionesQuery->whereIn(
                    'id_proveedor',
                    $userProveedores->proveedores()->pluck('proveedor_id')
                );
            }
        }

        // 8. Eager loading
        $cotizacionesQuery->orderBy('created_at', 'desc')
            ->with([
                'cliente',
                'subcliente',
                'DocCotizacion.Asignaciones',
                'Empresa',
                'Proveedor'
            ])
            ->withExists([
                'costosViajes as tiene_costos' => fn ($q) => $q->tieneValores()
            ]);

        $cotizaciones = $cotizacionesQuery->get();

        return $cotizaciones->map(function ($cotizacion) {
            $doc = $cotizacion->DocCotizacion;
            $contenedor = $doc ? $doc->num_contenedor : 'N/A';

            // Flags de documentos
            $docCCP = $doc ? !empty($doc->doc_ccp) : false;
            $doda = $doc ? !empty($doc->doda) : false;
            $boletaLiberacion = $doc ? !empty($doc->boleta_liberacion) : false;
            $boletaVacio = !empty($cotizacion->img_boleta) || ($doc && !empty($doc->boleta_vacio) && $doc->boleta_vacio === 'si');
            $docEir = $doc ? !empty($doc->doc_eir) : false;
            $fotoPatio = $doc ? !empty($doc->foto_patio) : false;
            $boletaPatio = $doc ? !empty($doc->boleta_patio) : false;
            $cartaPortepdf = !empty($cotizacion->carta_porte);
            $carta_porte_xml = !empty($cotizacion->carta_porte_xml);

            $tipo = "Sencillo";
            $numAutorizacion = $doc ? ($doc->num_autorizacion ?? '') : '';
            $pesoContenedor = $cotizacion->peso_contenedor;

            // Si es tipo Full, concatenar contenedor secundario
            if (!is_null($cotizacion->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $cotizacion->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion.Asignaciones')
                    ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $docCCP = ($docCCP && !empty($secundaria->DocCotizacion->doc_ccp));
                    $doda = ($doda && !empty($secundaria->DocCotizacion->doda));
                    $docEir = ($docEir && !empty($secundaria->DocCotizacion->doc_eir));
                    $boletaLiberacion = ($boletaLiberacion && !empty($secundaria->DocCotizacion->boleta_liberacion));
                    $cartaPortepdf = ($cartaPortepdf && !empty($secundaria->carta_porte));
                    $carta_porte_xml = ($carta_porte_xml && !empty($secundaria->carta_porte_xml));
                    $boletaVacio = ($boletaVacio && (!empty($secundaria->img_boleta) || ($secundaria->DocCotizacion && !empty($secundaria->DocCotizacion->boleta_vacio) && $secundaria->DocCotizacion->boleta_vacio === 'si')));
                    $fotoPatio = ($fotoPatio && !empty($secundaria->foto_patio));
                    $contenedor .= ' ' . $secundaria->DocCotizacion->num_contenedor;
                    if (!empty($secundaria->DocCotizacion->num_autorizacion)) {
                        $numAutorizacion = trim($numAutorizacion . ' ' . $secundaria->DocCotizacion->num_autorizacion);
                    }
                    if (!empty($secundaria->peso_contenedor)) {
                        $pesoContenedor .= ' / ' . $secundaria->peso_contenedor;
                    }
                }

                $tipo = "Full";
            }

            // Estatus mapeado
            $estatusMapeado = $cotizacion->estatus;
            if ($cotizacion->estatus == "NO ASIGNADA" || $cotizacion->estatus == "Pendiente") {
                $esPorAsignar = false;
                if (empty($cotizacion->id_proveedor)) {
                    $esPorAsignar = true;
                } elseif ($cotizacion->Proveedor) {
                    $provNombre = strtolower($cotizacion->Proveedor->nombre ?? $cotizacion->Proveedor->razon_social ?? '');
                    $tipoViajeProv = strtolower($cotizacion->Proveedor->tipo_viaje ?? '');
                    if ($tipoViajeProv === 'local' || str_contains($provNombre, 'xyz') || str_contains($provNombre, 'sin asignacion')) {
                        $esPorAsignar = true;
                    }
                }

                if ($esPorAsignar) {
                    $estatusMapeado = "Por Asignar";
                } else {
                    $estatusMapeado = "Viaje solicitado";
                }
            } elseif ($cotizacion->estatus == "Aprobada" && $cotizacion->estatus_planeacion == 1) {
                $estatusMapeado = "Planeado";
            }

            // Nombre transportista
            $transportistaNombre = 'N/A';
            if ($cotizacion->Proveedor) {
                $transportistaNombre = $cotizacion->Proveedor->razon_social ?? $cotizacion->Proveedor->nombre ?? 'Proveedor';
            } elseif ($cotizacion->Empresa) {
                $transportistaNombre = $cotizacion->Empresa->nombre ?? 'Empresa';
            }

            return [
                'id' => $cotizacion->id,
                'cliente' => optional($cotizacion->cliente)->nombre ?? 'N/A',
                'subcliente' => optional($cotizacion->subcliente)->nombre ?? 'N/A',
                'origen' => $cotizacion->origen,
                'Origen' => $cotizacion->origen,
                'destino' => $cotizacion->destino,
                'Destino' => $cotizacion->destino,
                'contenedor' => $contenedor,
                'NumContenedor' => $contenedor,
                'labelContenedor' => $doc ? $doc->num_contenedor : 'N/A',
                'estatus' => $cotizacion->estatus,
                'Estatus' => $estatusMapeado,
                'coordenadas' => optional($doc?->Asignaciones)->id ? 'Ver' : '',
                'id_asignacion' => optional($doc?->Asignaciones)->id,
                'edit_url' => route('edit.cotizaciones', $cotizacion->id),
                'tipo' => $tipo,
                'referencia_full' => $cotizacion->referencia_full,
                'peso_contenedor' => $pesoContenedor,
                'Peso' => $pesoContenedor,
                'fecha_entrega' => !empty($cotizacion->fecha_entrega) ? Carbon::parse($cotizacion->fecha_entrega)->format('Y-m-d') : '',
                'bloque_hora_i' => $cotizacion->bloque_hora_i ?? '',
                'bloque_hora_f' => $cotizacion->bloque_hora_f ?? '',
                'bloque_hora_i_local' => $cotizacion->bloque_hora_i_local ?? '',
                'bloque_hora_f_local' => $cotizacion->bloque_hora_f_local ?? '',
                'terminal' => $doc ? ($doc->terminal ?? '') : '',
                'confirmacion_sello' => (bool)$cotizacion->confirmacion_sello,
                'observaciones' => $cotizacion->observaciones ?? '',
                'num_autorizacion' => $numAutorizacion,
                'tipo_viaje_seleccion' => $cotizacion->tipo_viaje_seleccion,
                'tamano' => $cotizacion->tamano,
                'precio_viaje' => $cotizacion->precio_viaje,
                'direccion_entrega' => $cotizacion->direccion_entrega,
                'total' => $cotizacion->total,
                'estatus_planeacion' => $cotizacion->estatus_planeacion,
                'valores' => $cotizacion->tiene_costos,
                // Campos requeridos por módulo de cliente / documentación
                'BoletaLiberacion' => $boletaLiberacion,
                'DODA' => $doda,
                'foto_patio' => $fotoPatio,
                'FormatoCartaPorte' => $docCCP,
                'PreAlta' => $boletaVacio,
                'BoletaPatio' => $boletaPatio,
                'docEir' => $docEir,
                'cartaPortepdf' => $cartaPortepdf,
                'carta_porte_xml' => $carta_porte_xml,
                'FechaSolicitud' => Carbon::parse($cotizacion->created_at)->format('Y-m-d'),
                'transportista' => $transportistaNombre,
                'convertido_local' => $cotizacion->tipo_viaje_seleccion === 'local',
            ];
        });
    }

    /**
     * Reutilizador específico para clientes externos (mis-viajes / documentacion).
     */
    public function getContenedoresCliente($idCliente, $fechaInicio = null, $fechaFin = null, $estatusSearch = null)
    {
        return $this->obtenerCotizacionesparametros(
            $estatusSearch = $estatusSearch,
            $estatus_planeacion = null,
            $validarNoplaneadas = null,
            $idCliente = $idCliente,
            $fechaInicio = $fechaInicio,
            $fechaFin = $fechaFin,
            true // $excluirLocales
        );
    }





    /**
     * Get operator files by container number(s).
     *
     * @param string $numContenedor
     * @return array
     */
    public function getOperatorFilesByContenedor($numContenedor)
    {
        $numContenedor = preg_replace('/\s+/', '*', $numContenedor);
        $contenedores = explode('*', $numContenedor);
        $filesList = [];
        $uniquePaths = [];

        foreach ($contenedores as $cont) {
            $contenedor = DocumCotizacion::where('num_contenedor', $cont)->first();
            if ($contenedor) {
                $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();
                if ($asignacion) {
                    $idAsignacion = $asignacion->id;

                    // Retrieve operator files from BitacoraViajeOperador
                    $bitacoras = BitacoraViajeOperador::where('id_asignacion', $idAsignacion)->get();
                    foreach ($bitacoras as $bitacora) {
                        // 1. Start trip / loading photos
                        if (!empty($bitacora->fotos_carga)) {
                            $decodedCarga = json_decode($bitacora->fotos_carga, true);
                            if (is_string($decodedCarga)) {
                                $decodedCarga = [$decodedCarga];
                            } elseif (!is_array($decodedCarga)) {
                                $decodedCarga = [$bitacora->fotos_carga];
                            }
                            foreach ($decodedCarga as $path) {
                                if (is_string($path) && !empty(trim($path)) && !isset($uniquePaths[$path])) {
                                    $uniquePaths[$path] = 'Evidencia de Carga';
                                }
                            }
                        }
                        // 2. Container opening photos
                        if (!empty($bitacora->fotos_apertura)) {
                            $decodedApertura = json_decode($bitacora->fotos_apertura, true);
                            if (is_string($decodedApertura)) {
                                $decodedApertura = [$decodedApertura];
                            } elseif (!is_array($decodedApertura)) {
                                $decodedApertura = [$bitacora->fotos_apertura];
                            }
                            foreach ($decodedApertura as $path) {
                                if (is_string($path) && !empty(trim($path)) && !isset($uniquePaths[$path])) {
                                    $uniquePaths[$path] = 'Apertura de Contenedor';
                                }
                            }
                        }
                        // 3. End trip / delivery photos
                        if (!empty($bitacora->fotos_fin)) {
                            $decodedFin = json_decode($bitacora->fotos_fin, true);
                            if (is_string($decodedFin)) {
                                $decodedFin = [$decodedFin];
                            } elseif (!is_array($decodedFin)) {
                                $decodedFin = [$bitacora->fotos_fin];
                            }
                            foreach ($decodedFin as $path) {
                                if (is_string($path) && !empty(trim($path)) && !isset($uniquePaths[$path])) {
                                    $uniquePaths[$path] = 'Conclusión de Viaje';
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($uniquePaths as $relativePath => $labelTipo) {
            $relativePath = ltrim($relativePath, '/');
            $fullPath = public_path($relativePath);
            if (file_exists($fullPath)) {
                $filesList[] = [
                    'name' => basename($relativePath),
                    'url' => asset($relativePath),
                    'size' => round(filesize($fullPath) / 1024, 2) . ' KB',
                    'date' => date("d/m/Y H:i:s", filemtime($fullPath)),
                    'tipo' => $labelTipo
                ];
            }
        }

        return $filesList;
    }

    /**
     * Contabiliza automáticamente la provisión del flete por viaje concluido (PROVISION_VIAJE).
     */
    public function contabilizarProvisionViaje(Cotizaciones $cotizacion): ?\App\Models\Poliza
    {
        return DB::transaction(function () use ($cotizacion) {
            // Regla de Negocio para viajes Full:
            // La cotización Secundaria comparte tracto y viaja en conjunto; la deuda y facturación
            // comercial se centraliza en la cotización Principal. La secundaria no se provisiona para evitar doble ingreso y saldo falso en CxC.
            if ($cotizacion->jerarquia === 'Secundario') {
                \App\Models\Poliza::where('origen_tipo', 'cotizacion')
                    ->where('origen_id', $cotizacion->id)
                    ->delete();
                return null;
            }

            $cotizacion->loadMissing(['cliente', 'DocCotizacion.Asignaciones']);

            $precioViaje = (float) ($cotizacion->total ?? 0);
            if ($precioViaje <= 0) {
                $precioViaje = (float) ($cotizacion->precio_viaje ?? 0)
                    + (float) ($cotizacion->burreo ?? 0)
                    + (float) ($cotizacion->maniobra ?? 0)
                    + (float) ($cotizacion->estadia ?? 0)
                    + (float) ($cotizacion->otro ?? 0);
            }

            // Si es Principal de un viaje Full, sumarle el importe de la cotización secundaria
            if (!empty($cotizacion->referencia_full) && $cotizacion->jerarquia === 'Principal') {
                $secundarias = Cotizaciones::where('referencia_full', $cotizacion->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->where('id', '!=', $cotizacion->id)
                    ->get();
                foreach ($secundarias as $sec) {
                    $montoSec = (float) ($sec->total ?? 0);
                    if ($montoSec <= 0) {
                        $montoSec = (float) ($sec->precio_viaje ?? 0)
                            + (float) ($sec->burreo ?? 0)
                            + (float) ($sec->maniobra ?? 0)
                            + (float) ($sec->estadia ?? 0)
                            + (float) ($sec->otro ?? 0);
                    }
                    $precioViaje += $montoSec;
                }
            }

            if ($precioViaje <= 0) {
                return null;
            }

            // Buscar tractocamión imputado
            $camionId = null;
            $doc = $cotizacion->DocCotizacion;
            if ($doc && $doc->Asignaciones && $doc->Asignaciones->id_camion) {
                $camionId = $doc->Asignaciones->id_camion;
            }

            // Resolver referencia de contenedor(es) incluyendo pareja de Full si aplica
            $contenedor = $doc?->num_contenedor;
            if (!empty($cotizacion->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $cotizacion->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion')
                    ->first();
                $contSec = $secundaria?->DocCotizacion?->num_contenedor;
                if ($contSec && $contenedor) {
                    $contenedor = "{$contenedor} / {$contSec}";
                } elseif ($contSec) {
                    $contenedor = $contSec;
                }
            }

            $polizaService = app(\App\Services\PolizaService::class);

            // Eliminar previas si existían para evitar duplicidad
            \App\Models\Poliza::where('origen_tipo', 'cotizacion')
                ->where('origen_id', $cotizacion->id)
                ->get()
                ->each(function ($pol) {
                    $pol->delete();
                });

            $fecha = !empty($cotizacion->fecha_entrega)
                ? Carbon::parse($cotizacion->fecha_entrega)->format('Y-m-d')
                : (!empty($doc?->Asignaciones?->fehca_inicio_guard)
                    ? Carbon::parse($doc->Asignaciones->fehca_inicio_guard)->format('Y-m-d')
                    : ($cotizacion->created_at ? $cotizacion->created_at->format('Y-m-d') : now()->format('Y-m-d')));

            $conceptoDetalle = 'Provisión viaje #' . $cotizacion->id . ($contenedor ? " [{$contenedor}]" : '') . ' Cliente: ' . ($cotizacion->cliente?->nombre ?? 'N/A');

            $asientoData = $polizaService->resolverAsientoPorEvento('PROVISION_VIAJE', null, $cotizacion->id_empresa ?? 1, [
                'id_cliente'  => $cotizacion->id_cliente,
                'id_equipo'   => $camionId,
                'monto'       => $precioViaje,
                'fecha'       => $fecha,
                'concepto'    => $conceptoDetalle,
                'referencia'  => $contenedor ?: ('VIAJE_' . $cotizacion->id),
                'origen_tipo' => 'cotizacion',
                'origen_id'   => $cotizacion->id,
            ]);

            return $polizaService->crearPoliza($asientoData);
        });
    }

    /**
     * Contabiliza el cobro al cliente por el flete del viaje (COBRO_CXC).
     */
    public function contabilizarCobroCliente(Cotizaciones $cotizacion, float $monto, int $bancoId, string $fecha): ?\App\Models\Poliza
    {
        return DB::transaction(function () use ($cotizacion, $monto, $bancoId, $fecha) {
            if ($monto <= 0) {
                return null;
            }

            $cotizacion->loadMissing(['cliente', 'DocCotizacion.Asignaciones']);
            $camionId = null;
            $doc = $cotizacion->DocCotizacion;
            if ($doc && $doc->Asignaciones && $doc->Asignaciones->id_camion) {
                $camionId = $doc->Asignaciones->id_camion;
            }

            $polizaService = app(\App\Services\PolizaService::class);

            // Resolver referencia de contenedor(es) incluyendo pareja de Full si aplica
            $contenedor = $doc?->num_contenedor;
            if (!empty($cotizacion->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $cotizacion->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion')
                    ->first();
                $contSec = $secundaria?->DocCotizacion?->num_contenedor;
                if ($contSec && $contenedor) {
                    $contenedor = "{$contenedor} / {$contSec}";
                } elseif ($contSec) {
                    $contenedor = $contSec;
                }
            }

            $conceptoDetalle = 'Cobro CXC viaje #' . $cotizacion->id . ($contenedor ? " [{$contenedor}]" : '') . ' Cliente: ' . ($cotizacion->cliente?->nombre ?? 'N/A');

            $asientoData = $polizaService->resolverAsientoPorEvento('COBRO_CXC', null, $cotizacion->id_empresa ?? 1, [
                'id_equipo'          => $camionId,
                'monto'              => $monto,
                'fecha'              => $fecha,
                'concepto'           => $conceptoDetalle,
                'referencia'         => $contenedor ?: ('COBRO_' . $cotizacion->id),
                'cuenta_bancaria_id' => $bancoId,
                'origen_tipo'        => 'cotizacion_cobro',
                'origen_id'          => $cotizacion->id,
            ]);

            return $polizaService->crearPoliza($asientoData);
        });
    }

    /**
     * Obtiene los ejercicios / años disponibles a partir de cotizaciones en la base de datos,
     * garantizando cubrir dinámicamente hasta el año actual + 1 sin requerir ajustes manuales futuros.
     *
     * @return array
     */
    public function getPeriodosCotizaciones(): array
    {
        try {
            return Cache::remember('sgt_periodos_cotizaciones', 3600, function () {
                return $this->calcularPeriodosCotizaciones();
            });
        } catch (\Throwable $e) {
            return $this->calcularPeriodosCotizaciones();
        }
    }

    /**
     * Calcula los rangos de años y fechas basados en cotizaciones en BD.
     *
     * @return array
     */
    protected function calcularPeriodosCotizaciones(): array
    {
        $minYear = (int) (DB::table('cotizaciones')->min(DB::raw('YEAR(created_at)')) ?? 2024);
        $maxBd = (int) (DB::table('cotizaciones')->max(DB::raw('YEAR(created_at)')) ?? date('Y'));
        $maxYear = max($maxBd, (int) date('Y') + 1);

        $anios = [];
        for ($y = $maxYear; $y >= $minYear; $y--) {
            $anios[] = $y;
        }

        return [
            'ejercicio_ini_bd' => $minYear,
            'ejercicio_fin_bd' => $maxYear,
            'anios'            => $anios,
            'min_date'         => "{$minYear}-01-01",
            'max_date'         => "{$maxYear}-12-31",
        ];
    }

    /**
     * Consulta las cotizaciones en seguimiento para el módulo de captura múltiple de costos.
     * Carga costos de cotización desde viajes->costos y costos de proveedor desde asignaciones.
     *
     * @param int $idEmpresa
     * @param int|null $idCliente
     * @param int|null $idCamion
     * @param string $estadoCosto 'no_capturados' | 'capturados' | 'todos'
     * @return array
     */
    public function obtenerViajesParaCapturaCostos(
        int $idEmpresa,
        ?int $idCliente = null,
        ?int $idCamion = null,
        string $estadoCosto = 'todos'
    ): array
    {
        $query = Cotizaciones::query()
            ->select([
                'cotizaciones.id',
                'cotizaciones.id_empresa',
                'cotizaciones.id_cliente',
                'cotizaciones.id_proveedor',
                'cotizaciones.destino',
                'cotizaciones.peso_contenedor',
                'cotizaciones.peso_reglamentario',
                'cotizaciones.precio_viaje',
                'cotizaciones.burreo',
                'cotizaciones.maniobra',
                'cotizaciones.estadia',
                'cotizaciones.otro',
                'cotizaciones.precio_sobre_peso',
                'cotizaciones.precio_tonelada',
                'cotizaciones.base_factura',
                'cotizaciones.base_taref',
                'cotizaciones.iva',
                'cotizaciones.retencion',
                'cotizaciones.total',
                'cotizaciones.restante',
                'cotizaciones.prove_restante',
                'cotizaciones.retencion_automatica',
                'cotizaciones.latitud',
                'cotizaciones.longitud',
                'cotizaciones.direccion_mapa',
                'cotizaciones.direccion_entrega',
                'cotizaciones.estatus',
                'cotizaciones.referencia_full',
                'cotizaciones.created_at',
            ])
            ->when($idEmpresa != 0, function ($q) use ($idEmpresa) {
                $q->where('cotizaciones.id_empresa', $idEmpresa);
            })
            ->where('cotizaciones.jerarquia', '!=', 'Secundario')
            ->where('cotizaciones.estatus', '!=', 'Cancelada')
            ->where('cotizaciones.id_proveedor', '!=', 59); //tEMPORAL EXCLUI ESTE VAMOS A PONERLE ALGUN CANDADO "Transporte XYZ" (id_proveedor=59) from cost capture
        if (!empty($idCliente)) {
            $query->where('cotizaciones.id_cliente', $idCliente);
        }

        if (!empty($idCamion)) {
            $query->whereHas('DocCotizacion.Asignaciones', function ($q) use ($idCamion) {
                $q->where('id_camion', $idCamion);
            });
        }

        $cotizaciones = $query->with([
            'DocCotizacion:id,id_cotizacion,num_contenedor',
            'DocCotizacion.Asignaciones:id,id_contenedor,id_proveedor,id_camion,precio,burreo,maniobra,estadia,sobrepeso_proveedor,total_tonelada,base1_proveedor,base2_proveedor,otro,iva,retencion,total_proveedor',
            'DocCotizacion.Asignaciones.Proveedor:id,nombre,tipo_viaje',
            'Proveedor:id,nombre,tipo_viaje',
            'viajes.costos',
            'movimientos' => function ($q) {
                $q->select('id', 'cobro_pago_id', 'cotizacion_id', 'monto')
                  ->with('cobroPago:id,tipo');
            },
        ])
    ->wherehas('Proveedor', function ($q) {
            $q->whereIn('tipo_viaje', ['foraneo', 'local_foraneo']);
        })

        ->orderBy('cotizaciones.created_at', 'desc')
        ->limit(1000)
        ->get();

        $rowsCotizacion = [];
        $rowsProveedor = [];

        foreach ($cotizaciones as $cot) {
            $doc = $cot->DocCotizacion;
            $asignacion = $doc?->Asignaciones;
            $viaje = $cot->viajes->sortByDesc('id')->first();
            $costos = $viaje?->costos ?? collect();

            // Link de mapas
            $mapaUrl = '';
            if (!empty($cot->latitud) && !empty($cot->longitud)) {
                $mapaUrl = "https://www.google.com/maps/search/?api=1&query={$cot->latitud},{$cot->longitud}";
            } elseif (!empty($cot->direccion_mapa)) {
                $mapaUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($cot->direccion_mapa);
            } elseif (!empty($cot->direccion_entrega)) {
                $mapaUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($cot->direccion_entrega);
            }

            $numContenedor = $doc?->num_contenedor ?? 'S/C';
            $destino = $cot->destino ?? '';
            $pesoContenedor = floatval($cot->peso_contenedor ?? 0);
            $pesoReglamentario = floatval($cot->peso_reglamentario ?: 22);
            $sobrepesoTon = max($pesoContenedor - $pesoReglamentario, 0);

            // Cobros CXC (clientes) y Pagos CXP (proveedores) desde pagos_cobros / cobros_pagos_cotizaciones
            $cobrosCXC = floatval($cot->movimientos->filter(function ($m) {
                return $m->cobroPago && $m->cobroPago->tipo === 'cxc';
            })->sum('monto'));

            $pagosCXP = floatval($cot->movimientos->filter(function ($m) {
                return $m->cobroPago && $m->cobroPago->tipo === 'cxp';
            })->sum('monto'));

            // --- Costos Cotización (desde viajes->costos con fallback a cotizaciones) ---
            $costoViaje = floatval($costos->firstWhere('concepto', 'viaje')?->monto ?? $cot->precio_viaje ?? 0);
            $costoBurreo = floatval($costos->firstWhere('concepto', 'burreo')?->monto ?? $cot->burreo ?? 0);
            $costoManiobra = floatval($costos->firstWhere('concepto', 'maniobra')?->monto ?? $cot->maniobra ?? 0);
            $costoEstadia = floatval($costos->firstWhere('concepto', 'estadia')?->monto ?? $cot->estadia ?? 0);
            $costoOtro = floatval($costos->firstWhere('concepto', 'otro')?->monto ?? $cot->otro ?? 0);
            $costoBaseFactura = floatval($costos->firstWhere('concepto', 'base_factura')?->monto ?? $cot->base_factura ?? 0);
            $costoBaseTaref = floatval($costos->firstWhere('concepto', 'base_taref')?->monto ?? $cot->base_taref ?? 0);
            $costoIva = floatval($costos->firstWhere('concepto', 'iva')?->monto ?? $cot->iva ?? 0);
            $costoRetencion = floatval($costos->firstWhere('concepto', 'retencion')?->monto ?? $cot->retencion ?? 0);

            $costoSobrepesoObj = $costos->firstWhere('concepto', 'sobrepeso');
            $precioSobrepeso = floatval($costoSobrepesoObj?->meta['precio_sobre_peso'] ?? $cot->precio_sobre_peso ?? 0);
            $totalSobrepeso = floatval($costoSobrepesoObj?->monto ?? $cot->precio_tonelada ?? round($sobrepesoTon * $precioSobrepeso, 2));
            $totalCotizacion = floatval($cot->total ?? 0);

            // Validación de saldo pendiente por cobrar para Cotización (CXC):
            // Solo debe aparecer si el contenedor aún no ha sido cobrado completamente
            $tieneSaldoPendienteCot = ($totalCotizacion > 0)
                ? (round($totalCotizacion - $cobrosCXC, 2) > 0)
                : ($cobrosCXC == 0);

            if ($tieneSaldoPendienteCot) {
                $tieneCostoCotizacion = ($costoViaje > 0 || $totalCotizacion > 0 || $costoBaseFactura > 0);

                $incluirCot = match ($estadoCosto) {
                    'no_capturados' => !$tieneCostoCotizacion,
                    'capturados' => $tieneCostoCotizacion,
                    default => true,
                };

                if ($incluirCot) {
                    $rowsCotizacion[] = [
                        'id' => $cot->id,
                        'contenedor' => $numContenedor,
                        'destino' => $destino,
                        'mapas' => $mapaUrl,
                        'peso_contenedor' => $pesoContenedor,
                        'peso_reglamentario' => $pesoReglamentario,
                        'sobrepeso' => $sobrepesoTon,
                        'precio_viaje' => $costoViaje,
                        'burreo' => $costoBurreo,
                        'maniobra' => $costoManiobra,
                        'estadia' => $costoEstadia,
                        'otro' => $costoOtro,
                        'precio_sobre_peso' => $precioSobrepeso,
                        'total_sobrepeso' => $totalSobrepeso,
                        'base_factura' => $costoBaseFactura,
                        'base_taref' => $costoBaseTaref,
                        'iva' => $costoIva,
                        'retencion' => $costoRetencion,
                        'total' => $totalCotizacion,
                        'retencion_automatica' => (bool) ($cot->retencion_automatica ?? true),
                    ];
                }
            }

            // --- Costos Proveedor (directo en asignaciones) ---
            $proveedorObj = $asignacion?->Proveedor ?? $cot->Proveedor;
            $tipoViajeProv = strtolower(trim((string) ($proveedorObj?->tipo_viaje ?? '')));

            // Regla de Negocio: En la pestaña Proveedor solo deben mostrarse viajes/contenedores
            // asignados a un proveedor de tipo foráneo o local_foráneo (ambos).
            $esProveedorValido = $proveedorObj && in_array($tipoViajeProv, ['foraneo', 'local_foraneo']);

            if ($esProveedorValido) {
                $proveedorNombre = $proveedorObj->nombre ?? 'Sin proveedor';
                $precioProveedor = floatval($asignacion?->precio ?? 0);
                $burreoProveedor = floatval($asignacion?->burreo ?? 0);
                $maniobraProveedor = floatval($asignacion?->maniobra ?? 0);
                $estadiaProveedor = floatval($asignacion?->estadia ?? 0);
                $sobrepesoProveedor = floatval($asignacion?->sobrepeso_proveedor ?? 0);
                $totalTonelada = floatval($asignacion?->total_tonelada ?? round($sobrepesoTon * $sobrepesoProveedor, 2));
                $base1Proveedor = floatval($asignacion?->base1_proveedor ?? 0);
                $base2Proveedor = floatval($asignacion?->base2_proveedor ?? 0);
                $otroProveedor = floatval($asignacion?->otro ?? 0);
                $ivaProveedor = floatval($asignacion?->iva ?? 0);
                $retencionProveedor = floatval($asignacion?->retencion ?? 0);
                $totalProveedor = floatval($asignacion?->total_proveedor ?? 0);

                // Validación de saldo pendiente por pagar a Proveedor (CXP):
                // Solo debe aparecer si (total del proveedor - lo que ya se pagó) aún queda saldo pendiente
                $tieneSaldoPendienteProv = ($totalProveedor > 0)
                    ? (round($totalProveedor - $pagosCXP, 2) > 0)
                    : ($pagosCXP == 0);

                if ($tieneSaldoPendienteProv) {
                    $tieneCostoProveedor = ($precioProveedor > 0 || $totalProveedor > 0 || $base1Proveedor > 0);

                    $incluirProv = match ($estadoCosto) {
                        'no_capturados' => !$tieneCostoProveedor,
                        'capturados' => $tieneCostoProveedor,
                        default => true,
                    };

                    if ($incluirProv) {
                        $rowsProveedor[] = [
                            'id' => $cot->id,
                            'id_asignacion' => $asignacion?->id,
                            'id_proveedor' => $asignacion?->id_proveedor ?? $cot->id_proveedor,
                            'contenedor' => $numContenedor,
                            'destino' => $destino,
                            'mapas' => $mapaUrl,
                            'peso_contenedor' => $pesoContenedor,
                            'sobrepeso' => $sobrepesoTon,
                            'proveedor' => $proveedorNombre,
                            'precio_proveedor' => $precioProveedor,
                            'burreo_proveedor' => $burreoProveedor,
                            'maniobra_proveedor' => $maniobraProveedor,
                            'estadia_proveedor' => $estadiaProveedor,
                            'sobrepeso_proveedor' => $sobrepesoProveedor,
                            'total_tonelada' => $totalTonelada,
                            'base1_proveedor' => $base1Proveedor,
                            'base2_proveedor' => $base2Proveedor,
                            'otro_proveedor' => $otroProveedor,
                            'iva_proveedor' => $ivaProveedor,
                            'retencion_proveedor' => $retencionProveedor,
                            'total_proveedor' => $totalProveedor,
                        ];
                    }
                }
            }
        }

        return [
            'cotizaciones' => $rowsCotizacion,
            'proveedores' => $rowsProveedor,
            'total_registros' => max(count($rowsCotizacion), count($rowsProveedor)),
        ];
    }

    /**
     * Guarda en lote los costos de cotización en viajes->costos y actualiza cotizaciones bajo transacción.
     *
     * @param array $items
     * @param int $idEmpresa
     * @return int Cantidad de registros actualizados
     */
    public function guardarCostosCotizacionMultiple(array $items, int $idEmpresa): int
    {
        return DB::transaction(function () use ($items, $idEmpresa) {
            $costosService = app(ViajesCostosService::class);
            $actualizados = 0;
            $cotizacionesActualizadas = [];

            foreach ($items as $item) {
                if (empty($item['id'])) {
                    continue;
                }

                $cot = Cotizaciones::where('id_empresa', $idEmpresa)->findOrFail($item['id']);

                $pesoContenedor = floatval($item['peso_contenedor'] ?? $cot->peso_contenedor ?? 0);
                $pesoReglamentario = floatval($item['peso_reglamentario'] ?? $cot->peso_reglamentario ?: 22);
                $sobrepeso = max($pesoContenedor - $pesoReglamentario, 0);

                $precioSobrepeso = floatval(str_replace(',', '', (string) ($item['precio_sobre_peso'] ?? 0)));
                $totalSobrepeso = round($sobrepeso * $precioSobrepeso, 2);

                $precioViaje = floatval(str_replace(',', '', (string) ($item['precio_viaje'] ?? 0)));
                $burreo = floatval(str_replace(',', '', (string) ($item['burreo'] ?? 0)));
                $maniobra = floatval(str_replace(',', '', (string) ($item['maniobra'] ?? 0)));
                $estadia = floatval(str_replace(',', '', (string) ($item['estadia'] ?? 0)));
                $otro = floatval(str_replace(',', '', (string) ($item['otro'] ?? 0)));
                $baseFactura = floatval(str_replace(',', '', (string) ($item['base_factura'] ?? 0)));
                $baseTaref = floatval(str_replace(',', '', (string) ($item['base_taref'] ?? 0)));
                $iva = floatval(str_replace(',', '', (string) ($item['iva'] ?? 0)));
                $retencion = floatval(str_replace(',', '', (string) ($item['retencion'] ?? 0)));
                $total = floatval(str_replace(',', '', (string) ($item['total'] ?? 0)));

                // Actualizar cotización
                $cot->peso_contenedor = $pesoContenedor;
                $cot->peso_reglamentario = $pesoReglamentario;
                $cot->sobrepeso = $sobrepeso;
                $cot->precio_sobre_peso = $precioSobrepeso;
                $cot->precio_tonelada = $totalSobrepeso;
                $cot->precio_viaje = $precioViaje;
                $cot->burreo = $burreo;
                $cot->maniobra = $maniobra;
                $cot->estadia = $estadia;
                $cot->otro = $otro;
                $cot->base_factura = $baseFactura;
                $cot->base_taref = $baseTaref;
                $cot->iva = $iva;
                $cot->retencion = $retencion;
                $cot->total = $total;

                $totalCobrado = method_exists($cot, 'cobros') && $cot->cobros()->exists() ? floatval($cot->cobros()->sum('monto')) : 0;
                $nuevoRestante = max(round($total - $totalCobrado, 2), 0);
                $cot->restante = $nuevoRestante;
                $cot->estatus_pago = ($nuevoRestante <= 0) ? '1' : '0';
                $cot->save();

                // Resolver viaje y sincronizar en viajes->costos (viajes_costos)
                $viaje = $cot->viajes()->orderByRaw("CASE WHEN estado = 'activo' THEN 0 ELSE 1 END")->first();
                if (!$viaje) {
                    $esFull = (!empty($cot->referencia_full) || (isset($cot->tipo_viaje) && strcasecmp((string) $cot->tipo_viaje, 'Full') === 0));
                    $viaje = Viajes::create([
                        'tipo' => $esFull ? 'full' : 'sencillo',
                        'estado' => 'activo',
                    ]);
                    $viaje->cotizaciones()->syncWithoutDetaching([$cot->id]);
                }

                $costosService->syncDesdeRequest($viaje, [
                    'precio_viaje' => $precioViaje,
                    'burreo' => $burreo,
                    'maniobra' => $maniobra,
                    'estadia' => $estadia,
                    'otro' => $otro,
                    'base_factura' => $baseFactura,
                    'base_taref' => $baseTaref,
                    'iva' => $iva,
                    'retencion' => $retencion,
                    'precio_sobre_peso' => $precioSobrepeso,
                    'sobrePeso' => $sobrepeso,
                ]);

                $actualizados++;
                $cotizacionesActualizadas[] = $cot;
            }

            // Sincronizar pólizas contables de provisión (CxC vs Ingresos por fletes)
            $principalesProcesadas = [];
            foreach ($cotizacionesActualizadas as $cotActualizada) {
                try {
                    $cotActualizada->refresh();

                    if (!empty($cotActualizada->referencia_full)) {
                        $cotPrincipal = ($cotActualizada->jerarquia === 'Principal')
                            ? $cotActualizada
                            : Cotizaciones::where('referencia_full', $cotActualizada->referencia_full)
                                ->where('jerarquia', 'Principal')
                                ->first();

                        if ($cotPrincipal && !in_array($cotPrincipal->id, $principalesProcesadas)) {
                            $cotPrincipal->refresh();
                            $this->contabilizarProvisionViaje($cotPrincipal);
                            $principalesProcesadas[] = $cotPrincipal->id;
                        } elseif (!$cotPrincipal) {
                            $this->contabilizarProvisionViaje($cotActualizada);
                        }
                    } else {
                        $this->contabilizarProvisionViaje($cotActualizada);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Error actualizando provisión contable de cotización #{$cotActualizada->id}: " . $e->getMessage());
                }
            }

            return $actualizados;
        });
    }

    /**
     * Guarda en lote los costos de proveedor directamente en la tabla asignaciones y recalcula prove_restante.
     *
     * @param array $items
     * @param int $idEmpresa
     * @return int Cantidad de registros actualizados
     */
    public function guardarCostosProveedorMultiple(array $items, int $idEmpresa): int
    {
        return DB::transaction(function () use ($items, $idEmpresa) {
            $actualizados = 0;

            foreach ($items as $item) {
                if (empty($item['id'])) {
                    continue;
                }

                $cot = Cotizaciones::where('id_empresa', $idEmpresa)->findOrFail($item['id']);
                $asignacion = $cot->DocCotizacion?->Asignaciones;

                if (!$asignacion) {
                    continue;
                }

                $asignacion->precio = floatval(str_replace(',', '', (string) ($item['precio_proveedor'] ?? 0)));
                $asignacion->burreo = floatval(str_replace(',', '', (string) ($item['burreo_proveedor'] ?? 0)));
                $asignacion->maniobra = floatval(str_replace(',', '', (string) ($item['maniobra_proveedor'] ?? 0)));
                $asignacion->estadia = floatval(str_replace(',', '', (string) ($item['estadia_proveedor'] ?? 0)));
                $asignacion->otro = floatval(str_replace(',', '', (string) ($item['otro_proveedor'] ?? 0)));
                $asignacion->sobrepeso_proveedor = floatval(str_replace(',', '', (string) ($item['sobrepeso_proveedor'] ?? 0)));
                $asignacion->total_tonelada = floatval(str_replace(',', '', (string) ($item['total_tonelada'] ?? 0)));
                $asignacion->base1_proveedor = floatval(str_replace(',', '', (string) ($item['base1_proveedor'] ?? 0)));
                $asignacion->base2_proveedor = floatval(str_replace(',', '', (string) ($item['base2_proveedor'] ?? 0)));
                $asignacion->iva = floatval(str_replace(',', '', (string) ($item['iva_proveedor'] ?? 0)));
                $asignacion->retencion = floatval(str_replace(',', '', (string) ($item['retencion_proveedor'] ?? 0)));
                $asignacion->total_proveedor = floatval(str_replace(',', '', (string) ($item['total_proveedor'] ?? 0)));

                if (!empty($item['id_proveedor'])) {
                    $asignacion->id_proveedor = $item['id_proveedor'];
                    $cot->id_proveedor = $item['id_proveedor'];
                }

                $asignacion->save();

                // Recalcular saldo proveedor en cotización
                $totalPagadoProv = method_exists($cot, 'pagos') && $cot->pagos()->exists() ? floatval($cot->pagos()->sum('monto')) : 0;
                $cot->prove_restante = max(round($asignacion->total_proveedor - $totalPagadoProv, 2), 0);
                $cot->save();

                $actualizados++;
            }

            return $actualizados;
        });
    }
}
