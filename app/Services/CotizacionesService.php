<?php

namespace App\Services;

use App\Models\DocumCotizacion;
use App\Models\Asignaciones;
use App\Models\BitacoraViajeOperador;
use App\Models\Cotizaciones;
use App\Models\User;
use App\Models\Client;
use App\Models\Subclientes;
use App\Models\Viajes;
use App\Models\Empresas;
use App\Models\Proveedor;
use App\Models\BitacoraCotizacionesEstatus;
use App\Traits\CommonTrait as Common;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CotizacionesService
{
    public const MAX_PESO_CONTENEDOR_TON = 60;

    /**
     * Normaliza y valida que el peso del contenedor esté en toneladas (0 < peso <= 60)
     * y calcula el sobrepeso real sobre el peso reglamentario.
     *
     * @throws \InvalidArgumentException
     */
    public function validarYCalcularPesoSobrepeso($pesoContenedorRaw, $pesoReglamentarioRaw = 22): array
    {
        if (is_null($pesoContenedorRaw) || trim((string) $pesoContenedorRaw) === '') {
            throw new \InvalidArgumentException(
                'El Peso de Contenedor es obligatorio y debe expresarse en toneladas (máximo ' . self::MAX_PESO_CONTENEDOR_TON . ' toneladas).'
            );
        }

        $pesoLimpio = str_replace(',', '', trim((string) $pesoContenedorRaw));
        if (!is_numeric($pesoLimpio)) {
            throw new \InvalidArgumentException(
                'El Peso de Contenedor debe ser un valor numérico válido en toneladas.'
            );
        }

        $pesoContenedor = round((float) $pesoLimpio, 2);
        if ($pesoContenedor <= 0 || $pesoContenedor > self::MAX_PESO_CONTENEDOR_TON) {
            throw new \InvalidArgumentException(
                'El Peso de Contenedor debe expresarse en toneladas (mayor a 0 y máximo ' . self::MAX_PESO_CONTENEDOR_TON . ' toneladas). Verifique que no esté capturando kilogramos.'
            );
        }

        $reglamentarioLimpio = str_replace(',', '', trim((string) ($pesoReglamentarioRaw ?? 22)));
        $pesoReglamentario = (is_numeric($reglamentarioLimpio) && (float) $reglamentarioLimpio > 0)
            ? (float) $reglamentarioLimpio
            : 22.0;

        $sobrepeso = $pesoContenedor > $pesoReglamentario
            ? round($pesoContenedor - $pesoReglamentario, 2)
            : 0.0;

        return [
            'peso_contenedor' => $pesoContenedor,
            'peso_reglamentario' => $pesoReglamentario,
            'sobrepeso' => $sobrepeso,
        ];
    }
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

            // Eliminar previa si existía para evitar duplicidad
            $polizaPrevia = \App\Models\Poliza::where('origen_tipo', 'cotizacion')
                ->where('origen_id', $cotizacion->id)
                ->first();
            if ($polizaPrevia) {
                $polizaPrevia->delete();
            }

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
     * Resuelve o asocia el viaje (sencillo o full) a partir de una cotización,
     * garantizando que una cotización nunca tenga más de un id_viaje asignado.
     */
    public function resolverViajeDesdeRequest(string $tipoCotizacion, int $idCotizacion): Viajes
    {
        if (strcasecmp($tipoCotizacion, 'Full') === 0) {
            $referencia = Cotizaciones::find($idCotizacion)?->referencia_full;
            $cotizaciones = Cotizaciones::where('referencia_full', $referencia)
                ->orderByRaw("CASE WHEN jerarquia = 'Principal' THEN 0 ELSE 1 END")
                ->get();

            if ($cotizaciones->count() < 2) {
                throw new \DomainException('No es un viaje FULL válido (requiere al menos 2 contenedores vinculados).');
            }

            $cotizacionesIds = $cotizaciones->pluck('id')->toArray();
            $principal = $cotizaciones->firstWhere('jerarquia', 'Principal') ?? $cotizaciones->first();

            // 1. Reutilizar el viaje existente de la cotización Principal (o de cualquiera del grupo Full)
            $viajeExistente = $principal->viajes()
                ->orderByRaw("CASE WHEN estado = 'activo' THEN 0 ELSE 1 END")
                ->orderByRaw("CASE WHEN tipo = 'full' THEN 0 ELSE 1 END")
                ->first();

            if (!$viajeExistente) {
                $viajeExistente = Viajes::whereHas('cotizaciones', function ($q) use ($cotizacionesIds) {
                        $q->whereIn('cotizacion_id', $cotizacionesIds);
                    })
                    ->orderByRaw("CASE WHEN estado = 'activo' THEN 0 ELSE 1 END")
                    ->orderByRaw("CASE WHEN tipo = 'full' THEN 0 ELSE 1 END")
                    ->first();
            }

            if ($viajeExistente) {
                if ($viajeExistente->tipo !== 'full' || $viajeExistente->estado !== 'activo') {
                    $viajeExistente->update([
                        'tipo' => 'full',
                        'estado' => 'activo',
                    ]);
                }

                // Si alguna cotización del Full tenía otro viaje_id distinto (ej. de cuando era sencilla), limpiarlo
                $otrosViajesIds = DB::table('viajes_cotizacion')
                    ->whereIn('cotizacion_id', $cotizacionesIds)
                    ->where('viaje_id', '!=', $viajeExistente->id)
                    ->pluck('viaje_id')
                    ->unique();

                if ($otrosViajesIds->isNotEmpty()) {
                    DB::table('viajes_cotizacion')
                        ->whereIn('cotizacion_id', $cotizacionesIds)
                        ->whereIn('viaje_id', $otrosViajesIds)
                        ->delete();

                    Viajes::whereIn('id', $otrosViajesIds)
                        ->whereDoesntHave('cotizaciones')
                        ->update(['estado' => 'cancelado']);
                }

                $viajeExistente->cotizaciones()->syncWithoutDetaching($cotizacionesIds);

                return $viajeExistente;
            }

            $viaje = Viajes::create([
                'tipo' => 'full',
                'estado' => 'activo',
            ]);

            $viaje->cotizaciones()->syncWithoutDetaching($cotizacionesIds);

            return $viaje;
        }

        $cotizacion = Cotizaciones::findOrFail($idCotizacion);
        $viaje = $cotizacion->viajes()
            ->orderByRaw("CASE WHEN estado = 'activo' THEN 0 ELSE 1 END")
            ->first();

        if ($viaje) {
            // Si el viaje estaba compartido con otra cotización (ej. ex-Full separado),
            // verificar si otra cotización sigue usándolo
            $compartidoConOtra = DB::table('viajes_cotizacion')
                ->where('viaje_id', $viaje->id)
                ->where('cotizacion_id', '!=', $cotizacion->id)
                ->exists();

            if (!$compartidoConOtra) {
                if ($viaje->tipo !== 'sencillo' || $viaje->estado !== 'activo') {
                    $viaje->update([
                        'tipo' => 'sencillo',
                        'estado' => 'activo',
                    ]);
                }

                // Limpiar cualquier otro registro duplicado en viajes_cotizacion para esta cotización
                DB::table('viajes_cotizacion')
                    ->where('cotizacion_id', $cotizacion->id)
                    ->where('viaje_id', '!=', $viaje->id)
                    ->delete();

                return $viaje;
            }

            // Si estaba compartido con otra cotización tras separar un Full, desvincular esta antes de crear su viaje individual
            DB::table('viajes_cotizacion')
                ->where('cotizacion_id', $cotizacion->id)
                ->delete();
        }

        $viaje = Viajes::create([
            'tipo' => 'sencillo',
            'estado' => 'activo',
        ]);

        $viaje->cotizaciones()->syncWithoutDetaching([$cotizacion->id]);

        return $viaje;
    }

    /**
     * Registra una cotización individual tradicional.
     */
    public function storeCotizacion(array $data, int $idEmpresa, $user): Cotizaciones
    {
        $datosPeso = $this->validarYCalcularPesoSobrepeso(
            $data['peso_contenedor'] ?? null,
            $data['peso_reglamentario'] ?? 22
        );

        return DB::transaction(function () use ($data, $idEmpresa, $user, $datosPeso) {
            if (!empty($data['num_contenedor'])) {
                $numContenedor = str_replace(' ', '', $data['num_contenedor']);
                $contenedorExistente = DocumCotizacion::where('num_contenedor', $numContenedor)->first();
                if ($contenedorExistente) {
                    throw new \DomainException("El contenedor {$numContenedor} ya existe en el sistema.");
                }
            } else {
                $numContenedor = '';
            }

            $total = !empty($data['total']) ? floatval(str_replace(',', '', $data['total'])) : 0.0;
            $empresaFinal = !empty($data['id_proveedor']) ? (int) $data['id_proveedor'] : $idEmpresa;

            $cotizacion = new Cotizaciones();
            $cotizacion->id_cliente = $data['id_cliente'] ?? null;
            $cotizacion->id_subcliente = $data['id_subcliente'] ?? null;
            $cotizacion->origen = $data['origen'] ?? null;
            $cotizacion->destino = $data['destino'] ?? null;
            $cotizacion->tamano = $data['tamano'] ?? null;
            $cotizacion->peso_contenedor = $datosPeso['peso_contenedor'];
            $cotizacion->otro = $data['otro'] ?? null;
            $cotizacion->fecha_modulacion = $data['fecha_modulacion'] ?? null;
            $cotizacion->fecha_entrega = $data['fecha_entrega'] ?? null;
            $cotizacion->iva = $data['iva'] ?? null;
            $cotizacion->retencion = $data['retencion'] ?? null;
            $cotizacion->peso_reglamentario = $data['peso_reglamentario'] ?? $datosPeso['peso_reglamentario'];
            $cotizacion->precio_sobre_peso = $data['precio_sobre_peso'] ?? null;
            $cotizacion->sobrepeso = $datosPeso['sobrepeso'];
            $cotizacion->estatus = !empty($data['uuid']) ? 'Documentos Faltantes' : 'Pendiente';
            $cotizacion->precio_viaje = $data['precio_viaje'] ?? 0;
            $cotizacion->burreo = $data['burreo'] ?? 0;
            $cotizacion->maniobra = $data['maniobra'] ?? 0;
            $cotizacion->estadia = $data['estadia'] ?? 0;
            $cotizacion->base_factura = $data['base_factura'] ?? null;
            $cotizacion->base_taref = $data['base_taref'] ?? null;
            $cotizacion->recinto_clientes = $data['recinto_clientes'] ?? null;
            $cotizacion->precio_tonelada = 0;
            $cotizacion->total = $total;
            $cotizacion->restante = $total;
            $cotizacion->estatus_pago = '0';
            $cotizacion->origen_captura = $data['origen_captura'] ?? null;
            $cotizacion->user_id = $user->id;
            $cotizacion->direccion_entrega = $data['direccion_entrega'] ?? null;
            $cotizacion->latitud = $data['latitud'] ?? null;
            $cotizacion->longitud = $data['longitud'] ?? null;
            $cotizacion->direccion_mapa = $data['direccion_mapa'] ?? null;
            if (array_key_exists('id_proveedor', $data) && !empty($data['id_transportista'])) {
                $cotizacion->id_proveedor = $data['id_transportista'];
            }
            $cotizacion->id_empresa = $empresaFinal;
            $cotizacion->save();

            $docCot = new DocumCotizacion();
            $docCot->id_cotizacion = $cotizacion->id;
            $docCot->num_contenedor = $numContenedor;
            $docCot->id_empresa = $empresaFinal;
            $docCot->save();

            // Generar carta porte PDF si procede
            if (!$user->can('mec-ocultar_datos_facturacion')) {
                $cotizacion->sat_uso_cfdi_id = $data['id_uso_cfdi'] ?? null;
                $cotizacion->sat_forma_pago_id = $data['id_forma_pago'] ?? null;
                $cotizacion->sat_metodo_pago_id = $data['id_metodo_pago'] ?? null;
                $cotizacion->direccion_recinto = $data['direccion_recinto'] ?? null;
                $cotizacion->uso_recinto = ($data['text_recinto'] ?? '') === 'recinto-si' ? 1 : 0;
                $cotizacion->cp_fraccion = $data['cp_fraccion'] ?? null;
                $cotizacion->cp_clave_sat = $data['cp_clave_sat'] ?? null;
                $cotizacion->cp_pedimento = $data['cp_pedimento'] ?? null;
                $cotizacion->cp_clase_ped = $data['cp_clase_pedimento'] ?? null;
                $cotizacion->cp_cantidad = $data['cp_cantidad'] ?? null;
                $cotizacion->cp_valor = $data['cp_valor'] ?? null;
                $cotizacion->cp_moneda = $data['cp_moneda_valor'] ?? null;
                $cotizacion->cp_contacto_entrega = $data['cp_contacto_entrega'] ?? null;
                $cotizacion->cp_fecha_tentativa_entrega = $data['cp_fecha_tentativa_entrega'] ?? null;
                $cotizacion->cp_hora_tentativa_entrega = $data['cp_hora_tentativa_entrega'] ?? null;
                $cotizacion->cp_comentarios = $data['cp_comentarios'] ?? null;
                $cotizacion->save();

                if (!empty($numContenedor)) {
                    $subCliente = Subclientes::find($cotizacion->id_subcliente);
                    $pathDir = public_path("cotizaciones/cotizacion{$cotizacion->id}");
                    if (!File::exists($pathDir)) {
                        File::makeDirectory($pathDir, 0755, true);
                    }
                    $filePath = "{$pathDir}/formato_carta_porte_{$numContenedor}.pdf";
                    try {
                        $pdf = Pdf::loadView('cotizaciones.carta_porte_pdf', [
                            'cotizaciones' => $cotizacion,
                            'cotizacion' => $cotizacion,
                            'numContenedor' => $numContenedor,
                            'subCliente' => $subCliente,
                        ]);
                        $pdf->save($filePath);

                        if (File::exists($filePath)) {
                            $docCot->ccp = 'si';
                            $docCot->doc_ccp = "formato_carta_porte_{$numContenedor}.pdf";
                            $docCot->save();
                        }
                    } catch (\Throwable $ePdf) {
                        Log::channel('daily')->warning('No se pudo generar formato carta porte PDF al guardar cotización: ' . $ePdf->getMessage());
                    }
                }
            }

            return $cotizacion;
        });
    }

    /**
     * Modifica el estatus de una cotización respetando la jerarquía de avance de operaciones.
     */
    public function updateEstatusCotizacion(int $id, string $newEstatus, $user): array
    {
        $cotizacion = Cotizaciones::findOrFail($id);

        $statusWeights = [
            'Pendiente' => 1,
            'Documentos Faltantes' => 1,
            'Aprobada' => 2,
            'Planeada' => 3,
            'Cancelada' => 4,
        ];

        $currentEstatusName = $cotizacion->estatus;
        if ($currentEstatusName == 'Aprobada' && $cotizacion->estatus_planeacion == 1) {
            $currentEstatusName = 'Planeada';
        }

        if ($currentEstatusName === 'Planeada') {
            throw new \DomainException('No se puede cambiar el estatus de una cotización ya planeada. Debe anular la planeación primero desde el tablero.');
        }

        if ($currentEstatusName === 'Cancelada' && !in_array($newEstatus, ['Cancelada', 'Aprobada'])) {
            throw new \DomainException('De una cotización cancelada solo se puede cambiar el estatus a Aprobada.');
        }

        $currentWeight = $statusWeights[$currentEstatusName] ?? 0;
        $newWeight = $statusWeights[$newEstatus] ?? 0;
        $isCancelledToApproved = ($currentEstatusName === 'Cancelada' && $newEstatus === 'Aprobada');

        if ($newWeight < $currentWeight && !$isCancelledToApproved) {
            throw new \DomainException("No se puede cambiar el estatus hacia atrás (de {$currentEstatusName} a {$newEstatus}).");
        }

        $cotizacion->estatus = $newEstatus;
        $cotizacion->estatus_planeacion = null;
        $cotizacion->save();

        if (in_array($newEstatus, ['Cancelada', 'Pendiente'])) {
            if ($cotizacion->DocCotizacion && $cotizacion->DocCotizacion->Asignaciones) {
                $asignacion = $cotizacion->DocCotizacion->Asignaciones;
                $asignacion->fecha_inicio = null;
                $asignacion->fecha_fin = null;
                $asignacion->save();
            }
        }

        return ['success' => true, 'message' => 'Estatus actualizado correctamente.'];
    }

    /**
     * Normaliza los IDs de viajes asegurando compatibilidad con arrays simples o estructurados.
     */
    private function normalizarIdsViajes(array $viajes): array
    {
        return collect($viajes)->map(function ($item) {
            if (is_array($item)) {
                return $item['id'] ?? null;
            }
            if (is_object($item)) {
                return $item->id ?? null;
            }
            return $item;
        })->filter()->values()->toArray();
    }

    /**
     * Convierte dos o más cotizaciones en un viaje FULL unificado.
     */
    public function convertirFull(array $viajes, $user): array
    {
        $ids = $this->normalizarIdsViajes($viajes);

        $estatus = Cotizaciones::whereIn('id', $ids)->pluck('estatus')->unique();
        if ($estatus->count() > 1) {
            throw new \DomainException('Los viajes seleccionados deben tener el mismo estatus para poder convertirlos a Full.');
        }

        $viajesExistentes = Cotizaciones::whereIn('id', $ids)
            ->whereNotNull('referencia_full')
            ->pluck('id')
            ->toArray();

        if (count($viajesExistentes) > 0) {
            throw new \DomainException('Los siguientes viajes ya pertenecen a un viaje Full: ' . implode(', ', $viajesExistentes));
        }

        $fullUUID = Common::generarUuidV4();

        DB::transaction(function () use ($ids, $fullUUID) {
            for ($x = 0; $x < sizeof($ids); $x++) {
                $jerarquia = ($x == 0) ? 'Principal' : 'Secundario';
                Cotizaciones::where('id', $ids[$x])->update([
                    'tipo_viaje' => 'Full',
                    'referencia_full' => $fullUUID,
                    'jerarquia' => $jerarquia,
                ]);
            }
        });

        return [
            "Titulo" => "Proceso satisfactorio",
            "Mensaje" => "Se ha realizado la fusión de viajes a tipo Full",
            "TMensaje" => "success",
        ];
    }

    /**
     * Cancela la fusión de viajes Full y los regresa a modalidad sencilla.
     */
    public function cancelarFull(array $viajes, $user): array
    {
        $ids = $this->normalizarIdsViajes($viajes);
        $estatus = Cotizaciones::whereIn('id', $ids)->whereIn('estatus', ['Finalizado', 'Cancelada'])->pluck('estatus')->unique();

        if ($estatus->count() > 0) {
            throw new \DomainException('Los viajes seleccionados no pueden ser cancelados de tipo Full porque ya están Finalizados o Cancelados.');
        }

        DB::transaction(function () use ($ids) {
            for ($x = 0; $x < sizeof($ids); $x++) {
                Cotizaciones::where('id', $ids[$x])->update([
                    'tipo_viaje' => 'Sencillo',
                    'referencia_full' => null,
                    'jerarquia' => 'Principal',
                ]);
            }
        });

        return [
            "Titulo" => "Proceso satisfactorio",
            "Mensaje" => "Se ha cancelado la fusión de viajes Full",
            "TMensaje" => "success",
        ];
    }

    /**
     * Obtiene cotizaciones vinculadas por el UUID de referencia Full.
     */
    public function getCotizacionesFull(string $uuid): array
    {
        $uuid = trim($uuid);
        if (empty($uuid)) {
            return [];
        }

        return Cotizaciones::leftJoin('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
            ->where('referencia_full', '=', $uuid)
            ->orderBy('jerarquia')
            ->get()
            ->toArray();
    }

    /**
     * Convierte viajes locales en viajes foráneos.
     */
    public function convertirLocalForaneo(array $viajes, $user): array
    {
        $ids = $this->normalizarIdsViajes($viajes);

        DB::transaction(function () use ($ids) {
            foreach ($ids as $id) {
                $doc = DocumCotizacion::where('id_cotizacion', $id)->first();
                $estatusA = "Documentos Faltantes";
                if ($doc && $doc->boleta_liberacion != null && $doc->doda != null) {
                    $estatusA = "Pendiente";
                }

                $cotizacion = Cotizaciones::find($id);
                if ($cotizacion) {
                    $cotizacion->tipo_viaje_seleccion = "local_to_foraneo";
                    $cotizacion->en_patio = 0;
                    $cotizacion->estatus = $estatusA;
                    $cotizacion->fecha_entrega = Carbon::now();
                    $cotizacion->save();
                }
            }
        });

        return [
            "Titulo" => "Proceso satisfactorio",
            "Mensaje" => "Se ha realizado el proceso de conversión a viaje foráneo",
            "TMensaje" => "success",
        ];
    }

    /**
     * Revierte viajes convertidos a foráneo de regreso a modalidad local.
     */
    public function revertirForaneo(array $viajes, $user): array
    {
        $ids = $this->normalizarIdsViajes($viajes);

        DB::transaction(function () use ($ids) {
            foreach ($ids as $id) {
                $cotizacion = Cotizaciones::find($id);
                if ($cotizacion) {
                    $cotizacion->tipo_viaje_seleccion = "local";
                    $cotizacion->en_patio = 1;
                    $cotizacion->estatus = "Documentos Faltantes";
                    $cotizacion->fecha_entrega = null;
                    $cotizacion->save();
                }
            }
        });

        return [
            "Titulo" => "Proceso satisfactorio",
            "Mensaje" => "Se ha revertido el proceso a viaje local",
            "TMensaje" => "success",
        ];
    }

    /**
     * Asigna o cambia la empresa responsable de una cotización.
     */
    public function cambiarEmpresa(int $idCotizacion, int $idEmpresaNueva, $user): void
    {
        DB::transaction(function () use ($idCotizacion, $idEmpresaNueva) {
            Cotizaciones::where('id', $idCotizacion)->update(['id_empresa' => $idEmpresaNueva]);
            DocumCotizacion::where('id_cotizacion', $idCotizacion)->update(['id_empresa' => $idEmpresaNueva]);
            Asignaciones::whereHas('contenedor', fn ($q) => $q->where('id_cotizacion', $idCotizacion))
                ->update(['id_empresa' => $idEmpresaNueva]);
        });
    }

    /**
     * Registra una cotización local.
     */
    public function storeLocal(array $data, int $idEmpresa, $user): array
    {
        return DB::transaction(function () use ($data, $idEmpresa, $user) {
            $numContenedor = preg_replace('/\s+/', '', (string) ($data['num_contenedor'] ?? ''));
            if ($numContenedor === '') {
                throw new \DomainException('Debe indicar el número de contenedor.');
            }

            if (DocumCotizacion::where('num_contenedor', $numContenedor)->exists()) {
                throw new \DomainException('El contenedor ya existe en la empresa.');
            }

            $datosPesoLocal = $this->validarYCalcularPesoSobrepeso($data['peso_contenedor'] ?? null, 22);

            $idEmpresaFinal = !empty($data['id_proveedor']) ? (int) $data['id_proveedor'] : $idEmpresa;
            $idTransportista = !empty($data['id_transportista']) ? (int) $data['id_transportista'] : null;
            $normalizarFecha = static function ($valor) {
                if ($valor === null) return null;
                $valor = trim((string) $valor);
                return $valor === '' || strtolower($valor) === 'null' ? null : $valor;
            };

            $cotizacion = Cotizaciones::create([
                'id_cliente' => $data['id_cliente'] ?? null,
                'id_empresa' => $idEmpresaFinal,
                'id_subcliente' => $data['id_subcliente'] ?? null,
                'id_proveedor' => $idTransportista,
                'cp_pedimento' => $data['num_pedimento'] ?? null,
                'cp_clase_ped' => $data['cp_clase_ped'] ?? null,
                'bloque' => $data['bloque'] ?? null,
                'bloque_hora_i' => $data['bloque_hora_i'] ?? null,
                'bloque_hora_f' => $data['bloque_hora_f'] ?? null,
                'bloque_local' => $data['bloque'] ?? null,
                'bloque_hora_i_local' => $data['bloque_hora_i'] ?? null,
                'bloque_hora_f_local' => $data['bloque_hora_f'] ?? null,
                'peso_reglamentario' => 22,
                'sobrepeso' => $datosPesoLocal['sobrepeso'],
                'origen' => $data['origen'] ?? null,
                'tamano' => $data['tamano'] ?? null,
                'peso_contenedor' => $datosPesoLocal['peso_contenedor'],
                'fecha_modulacion' => $data['fecha_modulacion'] ?? null,
                'origen_local' => $data['origen'] ?? null,
                'destino_local' => $data['destino'] ?? null,
                'costo_maniobra_local' => (float) ($data['Costomaniobra'] ?? 0),
                'estado_contenedor' => $data['estado_contenedor'] ?? '',
                'fecha_modulacion_local' => $data['fecha_modulacion'] ?? null,
                'empresa_local' => $idEmpresaFinal,
                'sub_cliente_local' => $data['id_subcliente'] ?? null,
                'transportista_local' => $idTransportista,
                'en_patio' => 1,
                'estatus' => 'Documentos Faltantes',
                'tipo_viaje_seleccion' => 'local',
                'puerto' => $data['puerto'] ?? '',
                'fecha_ingreso_puerto' => $data['fecha_ingreso_puerto'] ?? null,
                'fecha_salida_puerto' => $data['fecha_salida_puerto'] ?? null,
                'dias_estadia' => (int) ($data['dias_estadia'] ?? 0),
                'tarifa_estadia' => (float) ($data['tarifa_estadia'] ?? 0),
                'total_estadia' => (float) ($data['total_estadia'] ?? 0),
                'dias_pernocta' => (int) ($data['dias_pernocta'] ?? 0),
                'tarifa_pernocta' => (float) ($data['tarifa_pernocta'] ?? 0),
                'total_pernocta' => (float) ($data['total_pernocta'] ?? 0),
                'total_general' => (float) ($data['total_general'] ?? 0),
                'motivo_demora' => $data['motivo_demora'] ?? null,
                'liberado' => $data['liberado'] ?? 0,
                'fecha_liberacion' => $data['fecha_liberacion'] ?? null,
                'responsable' => 'Viaje local',
                'observaciones' => $data['observaciones'] ?? null,
                'nuevo_sello' => array_key_exists('nuevo_sello', $data) ? filter_var($data['nuevo_sello'], FILTER_VALIDATE_BOOLEAN) : null,
                'confirmacion_sello' => array_key_exists('confirmacion_sello', $data) ? filter_var($data['confirmacion_sello'], FILTER_VALIDATE_BOOLEAN) : null,
                'estatus_maniobra_id' => 4,
                'origen_captura' => $data['origen_captura'] ?? null,
                'user_id' => $user->id,
                'agente_aduanal' => $data['agente_aduanal'] ?? '',
                'estatus_pago' => '0',
            ]);

            BitacoraCotizacionesEstatus::create([
                'cotizaciones_id' => $cotizacion->id,
                'estatus_id' => 4,
                'user_id' => $user->id,
                'nota' => 'Creación de cotización local',
            ]);

            $naviera = $data['naviera_id'] ?? $data['naviera'] ?? null;
            $doc = DocumCotizacion::create([
                'id_cotizacion' => $cotizacion->id,
                'id_empresa' => $idEmpresaFinal,
                'num_contenedor' => $numContenedor,
                'terminal' => $data['terminal_local'] ?? null,
                'num_autorizacion' => $data['num_autorizacion'] ?? null,
                'boleta_liberacion' => $data['boleta_liberacion'] ?? null,
                'doda' => $data['doda'] ?? null,
                'num_boleta_liberacion' => $data['num_boleta_liberacion'] ?? null,
                'num_doda' => $data['num_doda'] ?? null,
                'num_carta_porte' => $data['num_carta_porte'] ?? null,
                'boleta_vacio' => $data['boleta_vacio'] ?? null,
                'fecha_boleta_vacio' => $data['fecha_boleta_vacio'] ?? null,
                'eir' => $data['eir'] ?? null,
                'doc_eir' => $data['doc_eir'] ?? null,
                'ccp' => $data['ccp'] ?? null,
                'doc_ccp' => $data['doc_ccp'] ?? null,
                'foto_patio' => $data['foto_patio'] ?? null,
                'boleta_patio' => $data['boleta_patio'] ?? null,
                'fecha_boleta_patio' => $data['fecha_boleta_patio'] ?? null,
                'cima' => $data['cima'] ?? 0,
                'cita_at' => $normalizarFecha($data['cita_at'] ?? null),
                'eta' => $normalizarFecha($data['eta'] ?? null),
                'pedimento_recibido_at' => $normalizarFecha($data['pedimento_recibido_at'] ?? null),
                'naviera_id' => ($naviera === null || $naviera === '' || strtolower(trim((string) $naviera)) === 'null' || (int) $naviera <= 0) ? null : (int) $naviera,
            ]);

            return [
                'cotizacion_id' => $cotizacion->id,
                'docum_cotizacion_id' => $doc->id,
                'num_contenedor' => $numContenedor,
            ];
        });
    }

    /** Procesa un archivo adjunto de la solicitud del viaje. */
    public function procesarDocumento($file, int $cotizacionId, int $idDoc, string $tipo, ?string $folio = null): void
    {
        if (!$file || !$file->isValid()) {
            throw new \RuntimeException('El archivo del documento no se recibió correctamente.');
        }

        $cotizacion = Cotizaciones::findOrFail($cotizacionId);
        $doc = DocumCotizacion::where('id', $idDoc)
            ->where('id_cotizacion', $cotizacionId)
            ->firstOrFail();

        $campoArchivo = match ($tipo) {
            'BoletaLib' => 'boleta_liberacion',
            'Doda' => 'doda',
            'BoletaPatio' => 'boleta_patio',
            'CCP' => 'doc_ccp',
            default => throw new \InvalidArgumentException("Tipo de documento no soportado: {$tipo}"),
        };

        $directorio = public_path("cotizaciones/cotizacion{$cotizacionId}");
        if (!File::isDirectory($directorio)) {
            File::makeDirectory($directorio, 0755, true);
        }

        $extension = preg_replace('/[^a-zA-Z0-9]/', '', $file->getClientOriginalExtension());
        $nombreArchivo = bin2hex(random_bytes(12)) . ($extension !== '' ? ".{$extension}" : '');
        $file->move($directorio, $nombreArchivo);

        $doc->{$campoArchivo} = $nombreArchivo;
        if ($tipo === 'CCP') {
            $doc->ccp = 'si';
        }

        $usuario = auth()->user();
        $esCliente = $usuario && $usuario->id_cliente != 0;
        if ($esCliente && $tipo === 'BoletaLib' && filled($folio)) {
            $doc->num_boleta_liberacion = $folio;
        } elseif ($esCliente && $tipo === 'Doda' && filled($folio)) {
            $doc->num_doda = $folio;
        }
        $doc->save();

        if ($cotizacion->tipo_viaje_seleccion === 'local') {
            $this->confirmarDocumentosLocal($cotizacionId);
        } elseif ($esCliente) {
            if (!empty($cotizacion->cliente?->correo)) {
                event(new \App\Events\GenericNotificationEvent(
                    [$cotizacion->cliente->correo],
                    "Se cargó {$tipo}: {$doc->num_contenedor}",
                    "Se cargó el documento {$tipo} del contenedor {$doc->num_contenedor}."
                ));
            }
            event(new \App\Events\ConfirmarDocumentosEvent($cotizacionId));
            if ($cotizacion->estatus !== 'Documentos Faltantes') {
                event(new \App\Events\NotificaNuevoDocumentoEvent($cotizacion, $tipo));
            }
        }

        $this->notificarDocumentoSubido($doc->id, $cotizacionId, $nombreArchivo, $esCliente, $folio, $tipo);
    }

    public function notificarDocumentoSubido(int $idDoc, int $cotizacionId, string $nombreArchivo, bool $esCliente, ?string $folio, string $tipo): void
    {
        $doc = DocumCotizacion::with('cotizacion')->find($idDoc);
        if (!$doc) {
            return;
        }

        app(\App\Services\NotificacionesService::class)->notificarDocumentoSubido(
            urlRepo: $tipo,
            empresaId: $doc->cotizacion->id_empresa ?? null,
            id_cliente: $doc->cotizacion->id_cliente ?? null,
            id_proveedor: $doc->cotizacion->id_proveedor ?? null,
            nombreArchivo: $nombreArchivo,
            modelo: $doc,
            url: asset("cotizaciones/cotizacion{$cotizacionId}/{$nombreArchivo}"),
            data: [
                'docum_cotizacion_id' => $doc->id,
                'cotizacion_id' => $doc->id_cotizacion,
                'contenedor' => $doc->num_contenedor ?? null,
                'folio' => $folio,
                'es_cliente' => $esCliente,
            ]
        );
    }

    public function confirmarDocumentosLocal(int $cotizacionId): void
    {
        try {
            $doc = DocumCotizacion::where('id_cotizacion', $cotizacionId)->first();
            $cotizacion = Cotizaciones::where('id', $cotizacionId)
                ->where('estatus', 'Documentos Faltantes')
                ->where('tipo_viaje_seleccion', 'local')
                ->first();

            if ($cotizacion && $doc && $doc->doda && $doc->boleta_liberacion) {
                $cotizacion->estatus = 'Local';
                $cotizacion->estatus_maniobra_id = 1;
                $cotizacion->en_patio = 0;
                $cotizacion->save();
            }
        } catch (\Throwable $error) {
            Log::channel('daily')->warning('No fue posible confirmar documentos del viaje local.', [
                'cotizacion' => $cotizacionId,
                'error' => $error->getMessage(),
            ]);
        }
    }

    /** Actualiza una cotización local individual. */
    public function singleUpdateLocal(int $id, array $data, int $idEmpresa, $user): array
    {
        $resultado = DB::transaction(function () use ($id, $data, $idEmpresa) {
            $cot = Cotizaciones::findOrFail($id);
            $doc = DocumCotizacion::where('id_cotizacion', $id)->firstOrFail();

            $contenedorOriginal = strtoupper(trim(str_replace(' ', '', $doc->num_contenedor ?? '')));
            $contenedorupdate = strtoupper(trim(str_replace(' ', '', $data['num_contenedor'] ?? '')));

            if (!empty($contenedorupdate) && $contenedorupdate !== $contenedorOriginal) {
                $contenedorExistente = DocumCotizacion::where('id', '!=', $doc->id)
                    ->where(function ($query) use ($contenedorupdate) {
                        $query->whereRaw("UPPER(REPLACE(TRIM(num_contenedor), ' ', '')) = ?", [$contenedorupdate])
                              ->orWhere('num_contenedor', $contenedorupdate);
                    })
                    ->exists();

                if ($contenedorExistente) {
                    throw new \DomainException("El contenedor $contenedorupdate ya existe en el sistema y no puede duplicarse");
                }
            }

            $datosPesoLocalUpd = $this->validarYCalcularPesoSobrepeso($data['peso_contenedor'] ?? $cot->peso_contenedor, 22);

            $idEmpresaFinal = !empty($data['id_proveedor']) ? (int) $data['id_proveedor'] : $idEmpresa;
            $idTransportista = !empty($data['id_transportista']) ? (int) $data['id_transportista'] : null;
            $numero = static function ($valor, bool $entero = false) {
                if ($valor === null || trim((string) $valor) === '' || strtolower(trim((string) $valor)) === 'null') {
                    return 0;
                }

                if (!is_numeric($valor)) {
                    return 0;
                }

                return $entero ? (int) $valor : (float) $valor;
            };
            $fecha = static function ($valor) {
                if ($valor === null) return null;
                $valor = trim((string) $valor);
                return $valor === '' || strtolower($valor) === 'null' ? null : $valor;
            };

            $cot->id_subcliente = $data['id_subcliente'] ?? null;
            $cot->id_empresa = $idEmpresaFinal;
            $cot->id_proveedor = $idTransportista;
            $cot->empresa_local = $idEmpresaFinal;
            $cot->sub_cliente_local = $data['id_subcliente'] ?? null;
            $cot->transportista_local = $idTransportista;
            $cot->peso_reglamentario = 22;
            $cot->origen = $data['origen'] ?? $cot->origen;
            $cot->tamano = $data['tamano'] ?? $cot->tamano;
            $cot->peso_contenedor = $datosPesoLocalUpd['peso_contenedor'];
            $cot->sobrepeso = $datosPesoLocalUpd['sobrepeso'];
            $cot->fecha_modulacion = $fecha($data['fecha_modulacion'] ?? null);
            $cot->cp_pedimento = $data['num_pedimento'] ?? $cot->cp_pedimento;
            $cot->cp_clase_ped = $data['cp_clase_ped'] ?? $cot->cp_clase_ped;
            $cot->bloque_local = $data['bloque'] ?? $cot->bloque_local;
            $cot->bloque_hora_i_local = $data['bloque_hora_i'] ?? $cot->bloque_hora_i_local;
            $cot->bloque_hora_f_local = $data['bloque_hora_f'] ?? $cot->bloque_hora_f_local;
            $cot->origen_local = $data['origen'] ?? $cot->origen_local;
            $cot->destino_local = $data['destino'] ?? $cot->destino_local;
            $cot->costo_maniobra_local = $data['Costomaniobra'] ?? $cot->costo_maniobra_local;
            $cot->estado_contenedor = $data['estado_contenedor'] ?? $cot->estado_contenedor;
            $cot->fecha_modulacion_local = $fecha($data['fecha_modulacion'] ?? null) ?? $cot->fecha_modulacion_local;
            $cot->puerto = $data['puerto'] ?? '';
            $cot->fecha_ingreso_puerto = $fecha($data['fecha_ingreso_puerto'] ?? null);
            $cot->fecha_salida_puerto = $fecha($data['fecha_salida_puerto'] ?? null);
            $cot->dias_estadia = $numero($data['dias_estadia'] ?? null, true);
            $cot->tarifa_estadia = $numero($data['tarifa_estadia'] ?? null);
            $cot->total_estadia = $numero($data['total_estadia'] ?? null);
            $cot->dias_pernocta = $numero($data['dias_pernocta'] ?? null, true);
            $cot->tarifa_pernocta = $numero($data['tarifa_pernocta'] ?? null);
            $cot->total_pernocta = $numero($data['total_pernocta'] ?? null);
            $cot->total_general = $numero($data['total_general'] ?? null);
            $cot->motivo_demora = $data['motivo_demora'] ?? null;
            $cot->liberado = $numero($data['liberado'] ?? null, true);
            $cot->fecha_liberacion = $fecha($data['fecha_liberacion'] ?? null);
            $cot->responsable = ($data['responsable'] ?? null) === 'null' ? null : ($data['responsable'] ?? null);
            $cot->observaciones = ($data['observaciones'] ?? null) === 'null' ? null : ($data['observaciones'] ?? null);
            $cot->nuevo_sello = array_key_exists('nuevo_sello', $data) ? filter_var($data['confirmacion_sello'] ?? $data['nuevo_sello'], FILTER_VALIDATE_BOOLEAN) : null;
            $cot->confirmacion_sello = array_key_exists('confirmacion_sello', $data) ? filter_var($data['confirmacion_sello'], FILTER_VALIDATE_BOOLEAN) : null;
            $cot->agente_aduanal = $data['agente_aduanal'] ?? '';
            $cot->save();

            $doc->id_empresa = $idEmpresaFinal;

            if (!empty($contenedorupdate) && $contenedorupdate !== $contenedorOriginal) {
                $doc->num_contenedor = $contenedorupdate;
            }

            $doc->num_boleta_liberacion = $fecha($data['numBoleta'] ?? $data['num_boleta_liberacion'] ?? null) ?? ($doc->num_boleta_liberacion ?? '');
            $doc->num_doda = $fecha($data['numDoda'] ?? $data['num_doda'] ?? null) ?? ($doc->num_doda ?? '');
            if (array_key_exists('terminal_local', $data)) {
                $doc->terminal = $data['terminal_local'];
            }
            if (array_key_exists('num_autorizacion', $data)) {
                $doc->num_autorizacion = $data['num_autorizacion'];
            }
            if (array_key_exists('cita_at', $data)) {
                $doc->cita_at = $fecha($data['cita_at']);
            }
            if (array_key_exists('eta', $data)) {
                $doc->eta = $fecha($data['eta']);
            }
            if (array_key_exists('naviera_id', $data) || array_key_exists('naviera', $data)) {
                $naviera = $data['naviera_id'] ?? $data['naviera'] ?? null;
                $doc->naviera_id = ($naviera === null || $naviera === '' || strtolower(trim((string) $naviera)) === 'null' || (int) $naviera <= 0) ? null : (int) $naviera;
            }
            if (array_key_exists('pedimento_recibido_at', $data)) {
                $doc->pedimento_recibido_at = $fecha($data['pedimento_recibido_at']);
            }
            $doc->save();

            return [
                'cotizacion_id' => $cot->id,
                'docum_cotizacion_id' => $doc->id,
                'num_contenedor' => $doc->num_contenedor,
            ];
        });

        foreach ([
            'boleta_liberacion_file' => ['BoletaLib', $data['numBoleta'] ?? null],
            'doda_file' => ['Doda', $data['numDoda'] ?? null],
            'BoletaPatio' => ['BoletaPatio', null],
        ] as $campoArchivo => [$tipoDocumento, $folio]) {
            $archivo = $data[$campoArchivo] ?? null;
            if ($archivo instanceof \Illuminate\Http\UploadedFile) {
                $this->procesarDocumento(
                    $archivo,
                    (int) $resultado['cotizacion_id'],
                    (int) $resultado['docum_cotizacion_id'],
                    $tipoDocumento,
                    $folio
                );
            }
        }

        return $resultado;
    }

    /**
     * Guarda cotizaciones en lote desde matriz de datos (Handsontable / Excel).
     */
    public function storeMultipleCotizaciones(array $contenedores, array $params, $user): array
    {
        $idEmpresa = $user->id_empresa;
        $row = 1;
        $sumarIndex = (!empty($params['permiso_proveedor']) && $params['permiso_proveedor'] == 1) ? 2 : 0;
        $mapsService = app(\App\Services\GoogleMapsService::class);

        // 1. Validaciones previas de todas las filas
        foreach ($contenedores as $cont) {
            $numContenedor = str_replace(' ', '', $cont[3 + $sumarIndex] ?? '');
            if (empty($numContenedor)) {
                throw new \DomainException("El número de contenedor en la fila {$row} es requerido.");
            }

            $contenedorExistente = DocumCotizacion::where('num_contenedor', $numContenedor)->first();
            if ($contenedorExistente) {
                throw new \DomainException("El contenedor {$numContenedor} en la fila {$row} ya existe en el sistema.");
            }

            $numSubCliente = substr($cont[0], 0, 5);
            if (!Subclientes::where('id', $numSubCliente)->exists()) {
                throw new \DomainException("El subcliente de la fila {$row} no es válido.");
            }

            if (!empty($params['permiso_proveedor']) && $params['permiso_proveedor'] == 1) {
                $numProveedor = substr($cont[1], 0, 5);
                if (!Empresas::where('id', $numProveedor)->exists()) {
                    throw new \DomainException("El proveedor de la fila {$row} no es válido.");
                }
                $numTransportista = substr($cont[2], 0, 5);
                if (!Proveedor::where('id', $numTransportista)->exists()) {
                    throw new \DomainException("El transportista de la fila {$row} no es válido.");
                }
            }

            $pesoCont = $cont[7 + $sumarIndex] ?? null;
            if (is_null($pesoCont) || $pesoCont === '' || !is_numeric($pesoCont) || floatval($pesoCont) <= 0 || floatval($pesoCont) > self::MAX_PESO_CONTENEDOR_TON) {
                throw new \InvalidArgumentException("El peso del contenedor de la fila {$row} debe expresarse en toneladas (mayor a 0 y máximo " . self::MAX_PESO_CONTENEDOR_TON . " toneladas).");
            }

            $row++;
        }

        // 2. Inserción dentro de transacción
        return DB::transaction(function () use ($contenedores, $params, $user, $sumarIndex, $mapsService) {
            $sumarIndexOriginal = $sumarIndex;
            foreach ($contenedores as $contenedor) {
                $pesocontenedor = (float) ($contenedor[7 + $sumarIndexOriginal] ?? 0);
                $numSubCliente = substr($contenedor[0], 0, 5);
                $pesoReglamentario = 22;
                $numContenedor = str_replace(' ', '', $contenedor[3 + $sumarIndexOriginal]);

                $url = $contenedor[18 + $sumarIndexOriginal] ?? null;
                $lat = null;
                $lng = null;
                if ($url) {
                    $coords = $mapsService->resolver($url);
                    $lat = $coords['lat'] ?? null;
                    $lng = $coords['lng'] ?? null;
                }

                $cotizacion = new Cotizaciones();
                $cotizacion->id_cliente = $user->id_cliente ?: ($params['id_cliente'] ?? null);
                $cotizacion->id_subcliente = $numSubCliente;
                if (!empty($params['permiso_proveedor']) && $params['permiso_proveedor'] == 1) {
                    $cotizacion->id_empresa = substr($contenedor[1], 0, 5);
                    $cotizacion->id_proveedor = substr($contenedor[2], 0, 5);
                } else {
                    $cotizacion->id_empresa = $user->id_empresa;
                }

                $cotizacion->origen = $contenedor[4 + $sumarIndexOriginal] ?? null;
                $cotizacion->destino = $contenedor[5 + $sumarIndexOriginal] ?? null;
                $cotizacion->tamano = $contenedor[6 + $sumarIndexOriginal] ?? null;
                $cotizacion->peso_contenedor = $pesocontenedor;
                $cotizacion->bloque = $contenedor[14 + $sumarIndexOriginal] ?? null;
                $cotizacion->bloque_hora_i = $contenedor[15 + $sumarIndexOriginal] ?? null;
                $cotizacion->bloque_hora_f = $contenedor[16 + $sumarIndexOriginal] ?? null;
                $cotizacion->direccion_entrega = $contenedor[17 + $sumarIndexOriginal] ?? null;
                $cotizacion->latitud = $lat;
                $cotizacion->longitud = $lng;
                $cotizacion->otro = 0;
                $cotizacion->fecha_modulacion = $contenedor[12 + $sumarIndexOriginal] ?? null;
                $cotizacion->fecha_entrega = $contenedor[13 + $sumarIndexOriginal] ?? Carbon::now();
                $cotizacion->iva = 0;
                $cotizacion->retencion = 0;
                $cotizacion->peso_reglamentario = $pesoReglamentario;
                $cotizacion->precio_sobre_peso = 0;
                $cotizacion->sobrepeso = ($pesocontenedor > $pesoReglamentario) ? ($pesocontenedor - $pesoReglamentario) : 0;
                $cotizacion->estatus = !empty($params['uuid']) ? 'Documentos Faltantes' : 'Pendiente';
                $cotizacion->precio_viaje = 0;
                $cotizacion->burreo = 0;
                $cotizacion->maniobra = 0;
                $cotizacion->estadia = 0;
                $cotizacion->base_factura = 0;
                $cotizacion->base_taref = 0;
                $cotizacion->total = 0;
                $cotizacion->restante = 0;
                $cotizacion->estatus_pago = '0';
                $cotizacion->origen_captura = $params['origen_captura'] ?? null;
                $cotizacion->user_id = $user->id;
                $cotizacion->save();

                $doc = new DocumCotizacion();
                $doc->id_cotizacion = $cotizacion->id;
                $doc->num_contenedor = $numContenedor;
                $doc->id_empresa = $cotizacion->id_empresa;
                $doc->save();
            }

            return [
                "Titulo" => "Proceso satisfactorio",
                "Mensaje" => "Se han guardado las cotizaciones con éxito",
                "TMensaje" => "success"
            ];
        });
    }

    /**
     * Guarda cotizaciones locales en lote desde matriz de datos.
     */
    public function storeMultipleLocal(array $contenedores, array $params, $user): array
    {
        $row = 1;
        $sumarIndex = (!empty($params['permiso_proveedor']) && $params['permiso_proveedor'] == 1) ? 2 : 0;

        foreach ($contenedores as $cont) {
            $numContenedor = str_replace(' ', '', $cont[3 + $sumarIndex] ?? '');
            if (empty($numContenedor)) {
                throw new \DomainException("El contenedor en la fila {$row} es requerido.");
            }
            if (DocumCotizacion::where('num_contenedor', $numContenedor)->exists()) {
                throw new \DomainException("El contenedor {$numContenedor} ya existe en la empresa");
            }

            $numSubCliente = substr($cont[0] ?? '', 0, 5);
            if (!Subclientes::where('id', $numSubCliente)->exists()) {
                throw new \DomainException("El subcliente de la fila {$row} no es un cliente registrado");
            }

            if (!empty($params['permiso_proveedor']) && $params['permiso_proveedor'] == 1) {
                $numProveedor = substr($cont[1] ?? '', 0, 5);
                if (!Empresas::where('id', $numProveedor)->exists()) {
                    throw new \DomainException("El proveedor de la fila {$row} no es un proveedor registrado");
                }
                $numTransportista = substr($cont[2] ?? '', 0, 5);
                if (!Proveedor::where('id', $numTransportista)->exists()) {
                    throw new \DomainException("El transportista de la fila {$row} no es un transportista registrado");
                }
            }

            $pesoContenedorVal = $cont[5 + $sumarIndex] ?? null;
            if (is_null($pesoContenedorVal) || $pesoContenedorVal === '' || !is_numeric($pesoContenedorVal) || floatval($pesoContenedorVal) <= 0 || floatval($pesoContenedorVal) > self::MAX_PESO_CONTENEDOR_TON) {
                throw new \InvalidArgumentException("El peso del contenedor de la fila {$row} debe expresarse en toneladas (mayor a 0 y máximo " . self::MAX_PESO_CONTENEDOR_TON . " toneladas).");
            }

            $row++;
        }

        return DB::transaction(function () use ($contenedores, $params, $user, $sumarIndex) {
            $origenInicial = $params['origen_captura'] ?? null;
            $pesoReglamentario = 22;

            foreach ($contenedores as $contenedor) {
                $pesocontenedor = (float) ($contenedor[5 + $sumarIndex] ?? 0);
                $numSubCliente = substr($contenedor[0], 0, 5);
                $numContenedor = str_replace(' ', '', $contenedor[3 + $sumarIndex]);

                $cotizaciones = new Cotizaciones();
                $cotizaciones->id_cliente = $user->id_cliente;
                $cotizaciones->id_subcliente = $numSubCliente;
                if (!empty($params['permiso_proveedor']) && $params['permiso_proveedor'] == 1) {
                    $numProveedor = substr($contenedor[1], 0, 5);
                    $numTransportista = substr($contenedor[2], 0, 5);
                    $cotizaciones->id_empresa = $numProveedor;
                    $cotizaciones->id_proveedor = $numTransportista;
                    $cotizaciones->empresa_local = $params['id_proveedor'] ?? null;
                } else {
                    $cotizaciones->id_empresa = $user->id_empresa;
                }
                if (!empty($params['id_transportista']) && $params['id_transportista'] !== $cotizaciones->id_proveedor) {
                    $cotizaciones->id_proveedor = $params['id_transportista'];
                    $cotizaciones->transportista_local = $params['id_transportista'] ?? null;
                }
                $cotizaciones->origen = $contenedor[2 + $sumarIndex] ?? null;
                $cotizaciones->destino = $contenedor[3 + $sumarIndex] ?? null;
                $cotizaciones->tamano = $contenedor[4 + $sumarIndex] ?? null;
                $cotizaciones->peso_contenedor = $pesocontenedor;
                $cotizaciones->bloque = $contenedor[14 + $sumarIndex] ?? null;
                $cotizaciones->bloque_hora_i = $contenedor[15 + $sumarIndex] ?? null;
                $cotizaciones->bloque_hora_f = $contenedor[16 + $sumarIndex] ?? null;
                $cotizaciones->direccion_entrega = $contenedor[17 + $sumarIndex] ?? null;
                $cotizaciones->otro = 0;
                $cotizaciones->fecha_modulacion = $contenedor[12 + $sumarIndex] ?? null;
                $cotizaciones->fecha_entrega = $contenedor[13 + $sumarIndex] ?? null;
                $cotizaciones->bloque_local = $contenedor[14 + $sumarIndex] ?? null;
                $cotizaciones->bloque_hora_i_local = $contenedor[15 + $sumarIndex] ?? null;
                $cotizaciones->bloque_hora_f_local = $contenedor[16 + $sumarIndex] ?? null;
                $cotizaciones->origen_local = $params['origen'] ?? null;
                $cotizaciones->destino_local = $params['destino'] ?? null;
                $cotizaciones->costo_maniobra_local = 0;
                $cotizaciones->estado_contenedor = 'AMARILLO';
                $cotizaciones->fecha_modulacion_local = $contenedor[12 + $sumarIndex] ?? null;
                $cotizaciones->puerto = 'Lazaro';
                $cotizaciones->fecha_ingreso_puerto = null;
                $cotizaciones->fecha_salida_puerto = null;
                $cotizaciones->dias_estadia = 0;
                $cotizaciones->tarifa_estadia = 0;
                $cotizaciones->total_estadia = 0;
                $cotizaciones->dias_pernocta = 0;
                $cotizaciones->tarifa_pernocta = 0;
                $cotizaciones->total_pernocta = 0;
                $cotizaciones->total_general = 0;
                $cotizaciones->motivo_demora = null;
                $cotizaciones->liberado = 0;
                $cotizaciones->fecha_liberacion = null;
                $cotizaciones->responsable = 'Viaje local';
                $cotizaciones->observaciones = 'Contenedor cargado desde carga masiva local';
                $cotizaciones->iva = 0;
                $cotizaciones->retencion = 0;
                $cotizaciones->peso_reglamentario = $pesoReglamentario;
                $cotizaciones->precio_sobre_peso = 0;
                $cotizaciones->sobrepeso = ($pesocontenedor > $pesoReglamentario) ? $pesocontenedor - $pesoReglamentario : 0;
                $cotizaciones->estatus = !empty($params['uuid']) ? 'Documentos Faltantes' : 'Pendiente';
                $cotizaciones->precio_viaje = 0;
                $cotizaciones->burreo = 0;
                $cotizaciones->maniobra = 0;
                $cotizaciones->estadia = 0;
                $cotizaciones->base_factura = 0;
                $cotizaciones->base_taref = 0;
                $cotizaciones->precio_tonelada = 0;
                $cotizaciones->total = 0;
                $cotizaciones->restante = 0;
                $cotizaciones->estatus_pago = '0';
                $cotizaciones->origen_captura = $origenInicial;
                $cotizaciones->user_id = $user->id;
                $cotizaciones->save();

                $docucotizaciones = new DocumCotizacion();
                $docucotizaciones->id_cotizacion = $cotizaciones->id;
                $docucotizaciones->num_contenedor = $numContenedor;
                $docucotizaciones->id_empresa = $cotizaciones->id_empresa;
                $docucotizaciones->save();
            }

            return [
                "Titulo" => "Proceso satisfactorio",
                "Mensaje" => "Cotización creada con exito",
                "TMensaje" => "success"
            ];
        });
    }

    /**
     * Obtiene contenedores con estatus 'NO ASIGNADA' para asignación.
     */
    public function getContenedoresAsignables()
    {
        $contenedoresPendientes = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
            ->join('clients as cl', 'cotizaciones.id_cliente', '=', 'cl.id')
            ->join('subclientes as sc', 'cotizaciones.id_subcliente', '=', 'sc.id')
            ->where('estatus', '=', 'NO ASIGNADA')
            ->where('jerarquia', '!=', 'Secundario')
            ->orderBy('created_at', 'desc')
            ->selectRaw('cotizaciones.*, d.num_contenedor, d.doc_eir, d.doc_ccp, d.boleta_liberacion, d.doda, cl.nombre as cliente, sc.nombre as subcliente')
            ->get();

        return $contenedoresPendientes->map(function ($c) {
            $numContenedor = $c->num_contenedor;
            $tipo = "Sencillo";
            $docCCP = ($c->doc_ccp == null) ? false : true;
            $doda = ($c->doda == null) ? false : true;
            $boletaLiberacion = ($c->boleta_liberacion == null) ? false : true;

            if (!is_null($c->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $c->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion.Asignaciones')
                    ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $docCCP = ($docCCP && $secundaria->DocCotizacion->doc_ccp) ? true : false;
                    $doda = ($doda && $secundaria->DocCotizacion->doda) ? true : false;
                    $boletaLiberacion = ($boletaLiberacion && $secundaria->DocCotizacion->boleta_liberacion) ? true : false;
                    $numContenedor .= '  ' . $secundaria->DocCotizacion->num_contenedor;
                }

                $tipo = "Full";
            }

            return [
                "IdContenedor" => $c->id,
                "Cliente" => $c->cliente,
                "SubCliente" => $c->subcliente,
                "NumContenedor" => $numContenedor,
                "Estatus" => $c->estatus,
                "Origen" => $c->origen,
                "Destino" => $c->destino,
                "Peso" => $c->peso_contenedor,
                "BoletaLiberacion" => $boletaLiberacion,
                "DODA" => $doda,
                "FormatoCartaPorte" => $docCCP,
                "IdCliente" => $c->id_cliente,
                "tipo" => $tipo,
            ];
        });
    }

    /**
     * Cancela un viaje solicitado por un cliente externo según número de contenedor.
     */
    public function cancelarViajeCliente(string $numContenedor, $user): array
    {
        $cotizacion = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
            ->where('d.num_contenedor', $numContenedor)
            ->first();

        if (!$cotizacion) {
            return [
                "Titulo" => "No encontrado",
                "Mensaje" => "No se encontró viaje con el contenedor $numContenedor",
                "TMensaje" => "warning"
            ];
        }

        if ($cotizacion->estatus === 'Cancelada') {
            return [
                "Titulo" => "Previamente Cancelado",
                "Mensaje" => "El contenedor $numContenedor fue cancelado previamente",
                "TMensaje" => "info"
            ];
        }

        $cotizacionModel = Cotizaciones::findOrFail($cotizacion->id);
        $cotizacionModel->estatus = 'Cancelada';
        $cotizacionModel->save();

        try {
            $emailList = array_filter([env('MAIL_NOTIFICATIONS'), $user?->email]);
            if (!empty($emailList)) {
                Mail::to($emailList)->send(new \App\Mail\NotificaCancelarViajeMail($cotizacionModel, $numContenedor));
            }
        } catch (\Throwable $e) {
            Log::channel('daily')->warning("No se pudo enviar correo de cancelación para {$numContenedor}: " . $e->getMessage());
        }

        return [
            "Titulo" => "Cancelado correctamente",
            "Mensaje" => "Se canceló el viaje con el Núm. Contenedor $numContenedor",
            "TMensaje" => "success"
        ];
    }

    /**
     * Actualiza el estatus de una maniobra local y registra la bitácora.
     */
    public function cambiarEstatusManiobra(int $idCotizacion, int $idEstatus, ?string $nota, $user): array
    {
        return DB::transaction(function () use ($idCotizacion, $idEstatus, $nota, $user) {
            $cotizacion = Cotizaciones::findOrFail($idCotizacion);
            $cotizacion->estatus_maniobra_id = $idEstatus;
            $cotizacion->save();

            $bitacora = new BitacoraCotizacionesEstatus();
            $bitacora->cotizaciones_id = $cotizacion->id;
            $bitacora->estatus_id = $idEstatus;
            $bitacora->user_id = $user->id;
            $bitacora->nota = $nota;
            $bitacora->save();

            return [
                "Titulo" => "Estatus actualizado",
                "Mensaje" => "El estatus de la maniobra se actualizó correctamente",
                "TMensaje" => "success"
            ];
        });
    }

    /**
     * Consulta el historial de cambios de estatus de una maniobra.
     */
    public function getHistorialEstatusManiobra(int $idCotizacion)
    {
        return BitacoraCotizacionesEstatus::with('estatus', 'usuario')
            ->where('cotizaciones_id', $idCotizacion)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($h) {
                return [
                    'estatus' => $h->estatus?->nombre ?? 'N/A',
                    'nota' => $h->nota,
                    'usuario' => $h->usuario?->name ?? 'Sistema',
                    'created_at' => $h->created_at ? $h->created_at->format('d/m/Y H:i') : '',
                ];
            });
    }

    /**
     * Obtiene el listado de contenedores en patio para un cliente externo.
     */
    public function getListPatio(int $idCliente)
    {
        $contenedoresPendientes = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
            ->where('cotizaciones.id_cliente', '=', $idCliente)
            ->where('estatus', '=', 'local')
            ->whereIn('tipo_viaje_seleccion', ['local', 'local_to_foraneo'])
            ->where('jerarquia', '!=', 'Secundario')
            ->where('en_patio', '=', '1')
            ->orderBy('created_at', 'desc')
            ->selectRaw('cotizaciones.*, d.num_contenedor, d.doc_eir, d.doc_ccp, d.boleta_liberacion, d.doda, d.foto_patio, d.boleta_patio')
            ->get();

        return $contenedoresPendientes->map(function ($c) {
            $numContenedor = $c->num_contenedor;
            $docCCP = !is_null($c->doc_ccp);
            $doda = !is_null($c->doda);
            $boletaLiberacion = !is_null($c->boleta_liberacion);
            $cartaPorte = (bool) $c->carta_porte;
            $boletaVacio = !is_null($c->img_boleta);
            $docEir = $c->doc_eir;
            $fotoPatio = !is_null($c->foto_patio);
            $boleta_patio = !is_null($c->boleta_patio);
            $tipo = "Sencillo";

            if (!is_null($c->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $c->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion.Asignaciones')
                    ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $docCCP = ($docCCP && $secundaria->DocCotizacion->doc_ccp);
                    $doda = ($doda && $secundaria->DocCotizacion->doda);
                    $docEir = ($docEir && $secundaria->DocCotizacion->doc_eir);
                    $boletaLiberacion = ($boletaLiberacion && $secundaria->DocCotizacion->boleta_liberacion);
                    $cartaPorte = ($cartaPorte && $secundaria->carta_porte);
                    $boletaVacio = ($boletaVacio && $secundaria->img_boleta);
                    $fotoPatio = ($fotoPatio && $secundaria->foto_patio);
                    $numContenedor .= '  ' . $secundaria->DocCotizacion->num_contenedor;
                }

                $tipo = "Full";
            }

            return [
                "NumContenedor" => $numContenedor,
                "Estatus" => ($c->estatus == "Local") ? "Patio" : $c->estatus,
                "Origen" => $c->origen,
                "Destino" => $c->destino,
                "Peso" => $c->peso_contenedor,
                "BoletaLiberacion" => $boletaLiberacion,
                "DODA" => $doda,
                "foto_patio" => $fotoPatio,
                "FormatoCartaPorte" => $docCCP,
                "PreAlta" => $boletaVacio,
                "BoletaPatio" => $boleta_patio,
                "FechaSolicitud" => Carbon::parse($c->created_at)->format('Y-m-d'),
                "tipo" => $tipo,
                "id" => $c->id
            ];
        });
    }

    /**
     * Obtiene contenedores locales pendientes para un cliente externo.
     */
    public function getContenedoresLocalesCliente(int $idCliente, $estatus, bool $ocultarForaneo = false)
    {
        $condicion = ($estatus != 'all') ? '=' : '!=';
        $contenedoresPendientes = Cotizaciones::join('docum_cotizacion as d', 'cotizaciones.id', '=', 'd.id_cotizacion')
            ->join('estatus_maniobras as estat', 'estat.id', '=', 'cotizaciones.estatus_maniobra_id')
            ->join('subclientes', 'subclientes.id', '=', 'cotizaciones.sub_cliente_local')
            ->join('clients', 'clients.id', '=', 'subclientes.id_cliente')
            ->join('empresas', 'empresas.id', '=', 'cotizaciones.empresa_local')
            ->join('proveedores', 'proveedores.id', '=', 'cotizaciones.transportista_local')
            ->leftjoin('navieras', 'navieras.id', '=', 'd.naviera_id')
            ->where('cotizaciones.id_cliente', $idCliente)
            ->where('cotizaciones.estatus_maniobra_id', $condicion, $estatus)
            ->when($ocultarForaneo, function ($query) {
                $query->where('tipo_viaje_seleccion', 'local');
            }, function ($query) {
                $query->whereIn('tipo_viaje_seleccion', ['local', 'local_to_foraneo']);
            })
            ->where('jerarquia', '!=', 'Secundario')
            ->orderBy('cotizaciones.created_at', 'desc')
            ->select([
                'cotizaciones.*',
                'd.num_contenedor',
                'd.doc_eir',
                'd.doc_ccp',
                'd.boleta_liberacion',
                'd.doda',
                'd.foto_patio',
                'd.boleta_patio',
                'd.terminal',
                'd.num_autorizacion',
                'navieras.naviera',
                'd.cita_at',
                'd.eta',
                'd.pedimento_recibido_at',
                'estat.nombre as estatus_maniobra',
                'empresas.nombre as empresa',
                'proveedores.nombre as proveedor',
                'clients.nombre as cliente',
                'subclientes.nombre as subcliente',
            ])
            ->get();

        return $contenedoresPendientes->map(function ($c) {
            $numContenedor = $c->num_contenedor;
            $docCCP = !is_null($c->doc_ccp);
            $doda = !is_null($c->doda);
            $boletaLiberacion = !is_null($c->boleta_liberacion);
            $boletaVacio = !is_null($c->img_boleta);
            $docEir = !is_null($c->doc_eir);
            $fotoPatio = !is_null($c->foto_patio);
            $boleta_patio = !is_null($c->boleta_patio);
            $tipo = "Sencillo";

            if (!is_null($c->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $c->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion')
                    ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $docCCP &= (bool) $secundaria->DocCotizacion->doc_ccp;
                    $doda &= (bool) $secundaria->DocCotizacion->doda;
                    $docEir &= (bool) $secundaria->DocCotizacion->doc_eir;
                    $boletaLiberacion &= (bool) $secundaria->DocCotizacion->boleta_liberacion;
                    $boletaVacio &= (bool) $secundaria->img_boleta;
                    $fotoPatio &= (bool) $secundaria->foto_patio;
                    $numContenedor .= ' ' . $secundaria->DocCotizacion->num_contenedor;
                }

                $tipo = "Full";
            }

            return [
                "NUM_CONTENEDOR_REFER" => $numContenedor,
                "NumContenedor" => self::limpiarNumContenedor($numContenedor),
                "Referencia" => self::obtenerReferencias($numContenedor),
                "EstatusManiobra" => $c->estatus_maniobra,
                "Origen" => $c->origen_local,
                "Destino" => $c->destino_local,
                "Peso" => $c->peso_contenedor,
                "Tamano" => $c->tamano,
                "BoletaLiberacion" => $boletaLiberacion,
                "FechaModulacion" => Carbon::parse($c->fecha_modulacion)->format('Y-m-d'),
                "DODA" => $doda,
                "foto_patio" => $fotoPatio,
                "FormatoCartaPorte" => $docCCP,
                "PreAlta" => $boletaVacio,
                "BoletaPatio" => $boleta_patio,
                "FechaSolicitud" => Carbon::parse($c->created_at)->format('Y-m-d'),
                "naviera" => $c->naviera,
                "cita_at" => $c->cita_at,
                "eta" => $c->eta,
                "pedimento_recibido_at" => $c->pedimento_recibido_at,
                "tipo" => $tipo,
                "Observaciones" => (is_null($c->observaciones) || $c->observaciones === 'null' || trim($c->observaciones) === '') ? '' : $c->observaciones,
                "Terminal" => $c->terminal,
                "Puerto" => $c->puerto,
                "NAutorizacion" => $c->num_autorizacion,
                "cp_pedimento" => $c->cp_pedimento,
                "cp_clase_ped" => $c->cp_clase_ped,
                "dias_estadia" => $c->dias_estadia,
                "dias_pernocta" => $c->dias_pernocta,
                "tarifa_estadia" => $c->tarifa_estadia,
                "tarifa_pernocta" => $c->tarifa_pernocta,
                "total_estadia" => $c->total_estadia,
                "total_pernocta" => $c->total_pernocta,
                "total_general" => $c->total_general,
                "costo_maniobra_local" => $c->costo_maniobra_local,
                "estado_contenedor" => (is_null($c->estado_contenedor) || $c->estado_contenedor === 'null' || trim($c->estado_contenedor) === '') ? 'Ninguno' : $c->estado_contenedor,
                'agente_aduanal' => (is_null($c->agente_aduanal) || $c->agente_aduanal === 'null' || trim($c->agente_aduanal) === '') ? '' : $c->agente_aduanal,
                "Empresa" => $c->empresa,
                "Proveedor" => $c->proveedor,
                "Cliente" => $c->cliente,
                "Subcliente" => $c->subcliente,
                "id" => $c->id,
                "estatus_maniobra_id" => $c->estatus_maniobra_id,
                "convertido_foraneo" => str_contains($c->tipo_viaje_seleccion, 'foraneo'),
            ];
        });
    }

    public static function obtenerReferencias(string $texto): string
    {
        if (trim($texto) === '') {
            return '';
        }

        $elementos = preg_split('/\s+/', trim($texto));
        $referencias = [];

        foreach ($elementos as $elem) {
            $pos = strpos($elem, '-');
            if ($pos !== false) {
                $ref = trim(substr($elem, $pos + 1));
                if ($ref !== '') {
                    $referencias[] = $ref;
                }
            } elseif (preg_match('/\[(.*?)\]/', $elem, $m) && !empty($m[1])) {
                $referencias[] = trim($m[1]);
            }
        }

        return implode(' / ', $referencias);
    }

    public static function limpiarNumContenedor(string $texto): string
    {
        if (trim($texto) === '') {
            return '';
        }

        $elementos = preg_split('/\s+/', trim($texto));
        $contenedores = [];

        foreach ($elementos as $elem) {
            $pos = strpos($elem, '-');
            if ($pos !== false) {
                $cont = trim(substr($elem, 0, $pos));
                if ($cont !== '') {
                    $contenedores[] = $cont;
                }
            } else {
                $cont = trim(preg_replace('/\[.*?\]/', '', $elem));
                if ($cont !== '') {
                    $contenedores[] = $cont;
                }
            }
        }

        return implode(' ', $contenedores);
    }
}
