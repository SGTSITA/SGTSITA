<?php

namespace App\Services;

use App\Models\DocumCotizacion;
use App\Models\Asignaciones;
use App\Models\BitacoraViajeOperador;
use App\Models\Cotizaciones;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
            $boletaVacio = $doc ? !empty($doc->img_boleta) : false;
            $docEir = $doc ? $doc->doc_eir : null;
            $fotoPatio = $doc ? !empty($doc->foto_patio) : false;
            $boletaPatio = $doc ? !empty($doc->boleta_patio) : false;
            $cartaPortepdf = !empty($cotizacion->carta_porte);
            $carta_porte_xml = !empty($cotizacion->carta_porte_xml);

            $tipo = "Sencillo";

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
                    $boletaVacio = ($boletaVacio && !empty($secundaria->img_boleta));
                    $fotoPatio = ($fotoPatio && !empty($secundaria->foto_patio));
                    $contenedor .= ' ' . $secundaria->DocCotizacion->num_contenedor;
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
                'peso_contenedor' => $cotizacion->peso_contenedor,
                'Peso' => $cotizacion->peso_contenedor,
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
}
