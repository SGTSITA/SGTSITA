<?php

namespace App\Services;

use App\Models\Asignaciones;
use App\Models\Cotizaciones;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ConsumoUnidadesService
{
    public function generar(array $data): array
    {
        $unidadId = $data['unidad_id'];
        $fechaInicio = $data['fecha_inicio'];
        $fechaFin = $data['fecha_fin'];

        $filtroPrincipal = function ($q) {
            $q->where(function ($q2) {
                $q2->where('jerarquia', '!=', 'Secundario')
                    ->orWhereNull('jerarquia');
            });
        };

        $asignacionesBase = Asignaciones::query()
            ->with([
                'Contenedor.Cotizacion',
                'Operador',
                'Camion',
                'bitacoraViaje',
            ])
            ->where('id_camion', $unidadId)
            ->whereDate('fecha_inicio', '>=', $fechaInicio)
            ->whereDate('fecha_inicio', '<=', $fechaFin)
            ->whereHas('Contenedor.Cotizacion', $filtroPrincipal)
            ->orderBy('fecha_inicio')
            ->orderBy('id')
            ->get()
            ->values();

        $ultimoViaje = $asignacionesBase->last();

        $asignacionExtra = null;

        if ($ultimoViaje) {
            $asignacionExtra = Asignaciones::query()
                ->with([
                    'Contenedor.Cotizacion',
                    'Operador',
                    'Camion',
                    'bitacoraViaje',
                ])
                ->where('id_camion', $unidadId)
                ->whereHas('Contenedor.Cotizacion', $filtroPrincipal)
                ->where(function ($q) use ($ultimoViaje) {
                    $q->where('fecha_inicio', '>', $ultimoViaje->fecha_inicio)
                        ->orWhere(function ($q2) use ($ultimoViaje) {
                            $q2->where('fecha_inicio', $ultimoViaje->fecha_inicio)
                                ->where('id', '>', $ultimoViaje->id);
                        });
                })
                ->orderBy('fecha_inicio')
                ->orderBy('id')
                ->first();
        }

        $asignacionesParaCalculo = $asignacionExtra
            ? $asignacionesBase->concat([$asignacionExtra])->values()
            : $asignacionesBase->values();

        $tipoConsumo = $data['tipo_consumo'] ?? 'diesel';
        $refresh = isset($data['refresh']) && filter_var($data['refresh'], FILTER_VALIDATE_BOOLEAN);

        $rows = $asignacionesBase->map(function ($asignacion, $index) use ($asignacionesParaCalculo, $tipoConsumo, $refresh) {
            $contenedor = $asignacion->Contenedor;
            $cotizacion = $contenedor?->Cotizacion;

            $bitacora = $asignacion->bitacoraViaje;
            $siguienteAsignacion = $asignacionesParaCalculo->get($index + 1);
            $bitacoraSiguiente = $siguienteAsignacion?->bitacoraViaje;
            $cotizacionSiguiente = $siguienteAsignacion?->Contenedor?->Cotizacion;

            // Consultar coordenadas_historial de rastreo de este contenedor y camión durante el viaje
            $fechaInicio = $asignacion->fecha_inicio;
            $fechaFin = $asignacion->fecha_fin ?? $siguienteAsignacion?->fecha_inicio ?? now();
            $coordenadasRuta = [];

            $kmGoogle = null;
            if (!$refresh && !empty($asignacion->ruta_coordenadas)) {
                $decoded = json_decode($asignacion->ruta_coordenadas, false);
                if (is_array($decoded)) {
                    $coordenadasRuta = $decoded;
                } elseif (is_object($decoded)) {
                    $coordenadasRuta = $decoded->coordenadas ?? [];
                    $kmGoogle = $decoded->km_google ?? null;
                }
            }

            if (empty($coordenadasRuta)) {
                $fechaInicioQuery = ($bitacora && $bitacora->viaje_iniciado)
                    ? Carbon::parse($bitacora->viaje_iniciado)
                    : (($bitacora && $bitacora->created_at)
                        ? Carbon::parse($bitacora->created_at)
                        : Carbon::parse($fechaInicio)->startOfDay());

                $fechaFinQuery = Carbon::parse($fechaFin)->endOfDay();
                if ($bitacora && $bitacora->viaje_finalizado) {
                    $viajeFin = Carbon::parse($bitacora->viaje_finalizado);
                    if ($viajeFin->gt($fechaFinQuery)) {
                        $fechaFinQuery = $viajeFin;
                    }
                }

                $coordenadasRuta = \DB::table('coordenadas_historial')
                    ->where(function ($query) use ($contenedor, $asignacion) {
                        $query->where(function ($q) use ($contenedor) {
                            if ($contenedor) {
                                $q->where('ubicacionable_id', $contenedor->id)
                                  ->whereIn('ubicacionable_type', ['rastreo service', 'App\\Models\\DocumCotizacion', 'DocumCotizacion', 'Contenedor']);
                            } else {
                                $q->whereRaw('1 = 0');
                            }
                        })
                        ->orWhere(function ($q) use ($asignacion) {
                            if ($asignacion && $asignacion->id_camion) {
                                $q->where('ubicacionable_id', $asignacion->id_camion)
                                  ->whereIn('ubicacionable_type', ['App\\Models\\Equipo', 'App\\Models\\Equipos', 'Equipo', 'Equipos', 'OperadorMovil']);
                            } else {
                                $q->whereRaw('1 = 0');
                            }
                        });
                    })
                    ->where('registrado_en', '>=', $fechaInicioQuery)
                    ->where('registrado_en', '<=', $fechaFinQuery)
                    ->orderBy('registrado_en', 'asc')
                    ->get(['latitud', 'longitud', 'registrado_en'])
                    ->toArray();

                // Calcular distancia real con coordenadas completas antes del downsampling
                $kmHistorialCalculado = 0.0;
                if (count($coordenadasRuta) > 1) {
                    for ($i = 0; $i < count($coordenadasRuta) - 1; $i++) {
                        $kmHistorialCalculado += $this->calcularDistanciaHaversine(
                            (float)$coordenadasRuta[$i]->latitud,
                            (float)$coordenadasRuta[$i]->longitud,
                            (float)$coordenadasRuta[$i+1]->latitud,
                            (float)$coordenadasRuta[$i+1]->longitud
                        );
                    }
                }

                $coordenadasRuta = $this->downsampleCoordinates($coordenadasRuta, 100);

                $viajeFinalizado = ($asignacion->fecha_fin !== null) || ($siguienteAsignacion !== null);
                if ($viajeFinalizado && !empty($coordenadasRuta)) {
                    $asignacion->ruta_coordenadas = json_encode($coordenadasRuta);
                    $asignacion->save();
                }
            } else {
                // Calcular distancia real con coordenadas completas antes del downsampling
                $kmHistorialCalculado = 0.0;
                if (count($coordenadasRuta) > 1) {
                    for ($i = 0; $i < count($coordenadasRuta) - 1; $i++) {
                        $kmHistorialCalculado += $this->calcularDistanciaHaversine(
                            (float)$coordenadasRuta[$i]->latitud,
                            (float)$coordenadasRuta[$i]->longitud,
                            (float)$coordenadasRuta[$i+1]->latitud,
                            (float)$coordenadasRuta[$i+1]->longitud
                        );
                    }
                }
                $coordenadasRuta = $this->downsampleCoordinates($coordenadasRuta, 100);
            }

            $esEstimado = false;
            $origenKm = 'Sin KM';
            $litrosCapturadosViaje = 0.0;
            $litrosCalculoConsumo = 0.0;
            $observacion = null;
            $km = 0.0;

            if ($tipoConsumo === 'diesel') {
                // 1. Prioridad Principal: Captura Manual
                if ($cotizacion && $cotizacion->km_recorridos > 0) {
                    $km = (float) $cotizacion->km_recorridos;
                    $esEstimado = false;
                    if ($kmGoogle !== null && abs($km - floatval($kmGoogle)) < 0.1) {
                        $origenKm = 'Guardado desde Mapa';
                    } else {
                        $origenKm = 'Captura Manual';
                    }
                } else {
                    // 2. Respaldo 1: Ruta por Historial de Coordenadas
                    $kmHistorial = $kmHistorialCalculado;

                    if ($kmHistorial > 0) {
                        $km = $kmHistorial;
                        $esEstimado = true;
                        $origenKm = 'Ruta Historial Coordenadas';
                    }
                    // 3. Respaldo 2: Coordenadas Diésel
                    elseif ($bitacora && $bitacora->latitud && $bitacora->longitud && $bitacoraSiguiente && $bitacoraSiguiente->latitud && $bitacoraSiguiente->longitud) {
                        $kmEstimado = $this->obtenerDistanciaPorCarretera(
                            $bitacora->latitud,
                            $bitacora->longitud,
                            $bitacoraSiguiente->latitud,
                            $bitacoraSiguiente->longitud
                        );
                        if ($kmEstimado > 0) {
                            $km = $kmEstimado;
                            $esEstimado = true;
                            $origenKm = 'Coordenadas Diésel';
                        }
                    }
                    // 4. Respaldo 3: Diferencia de Odómetros
                    elseif ($bitacora && $bitacora->odometro > 0 && $bitacoraSiguiente && $bitacoraSiguiente->odometro > 0) {
                        $kmEstimado = floatval($bitacoraSiguiente->odometro) - floatval($bitacora->odometro);
                        if ($kmEstimado > 0) {
                            $km = $kmEstimado;
                            $esEstimado = false;
                            $origenKm = 'Diferencia Odómetros';
                        }
                    }
                    // 5. Respaldo 4: Estimación por Coordenadas del Viaje
                    elseif ($bitacora) {
                        $kmEstimado = $this->calcularDistanciaHaversine(
                            $bitacora->latitud,
                            $bitacora->longitud,
                            $bitacora->latitud_fin,
                            $bitacora->longitud_fin
                        );
                        if ($kmEstimado > 0) {
                            $km = $kmEstimado;
                            $esEstimado = true;
                            $origenKm = 'Estimación Coordenadas';
                        }
                    }
                }

                $litrosCapturadosViaje = (float) ($cotizacion?->litros_diesel ?? 0);
                $litrosCalculoConsumo = (float) ($cotizacionSiguiente?->litros_diesel ?? 0);

                if (!$siguienteAsignacion) {
                    $observacion = 'Pendiente de siguiente carga para calcular consumo';
                } elseif ($km <= 0 && $litrosCalculoConsumo <= 0) {
                    $observacion = 'Sin KM y sin litros para cálculo';
                } elseif ($km <= 0) {
                    $observacion = 'Sin KM capturados';
                } elseif ($litrosCalculoConsumo <= 0) {
                    $observacion = 'El siguiente viaje no tiene litros capturados';
                }

                if ($esEstimado) {
                    $observacion = ($observacion ? $observacion . ' | ' : '') . 'KM estimados por coordenadas';
                }
            } else {
                // TIPO CONSUMO === 'UREA'
                $litrosCapturadosViaje = (float) ($cotizacion?->litros_urea ?? 0);

                if ($litrosCapturadosViaje > 0) {
                    $siguienteAsignacionUrea = null;
                    $cotizacionSiguienteUrea = null;
                    $bitacoraSiguienteUrea = null;
                    $limitIndex = $asignacionesParaCalculo->count();

                    for ($i = $index + 1; $i < $asignacionesParaCalculo->count(); $i++) {
                        $asigTemp = $asignacionesParaCalculo->get($i);
                        $cotiTemp = $asigTemp?->Contenedor?->Cotizacion;
                        if ($cotiTemp && $cotiTemp->litros_urea > 0) {
                            $siguienteAsignacionUrea = $asigTemp;
                            $cotizacionSiguienteUrea = $cotiTemp;
                            $bitacoraSiguienteUrea = $asigTemp->bitacoraViaje;
                            $limitIndex = $i;
                            break;
                        }
                    }

                    $km = 0.0;
                    for ($i = $index; $i < $limitIndex; $i++) {
                        $asigTemp = $asignacionesParaCalculo->get($i);
                        $cotiTemp = $asigTemp?->Contenedor?->Cotizacion;
                        $bitacoraTemp = $asigTemp?->bitacoraViaje;
                        $asigSiguienteTemp = $asignacionesParaCalculo->get($i + 1);
                        $bitacoraSiguienteTemp = $asigSiguienteTemp?->bitacoraViaje;

                        $tripKm = 0.0;

                        // Intentar obtener ruta histórica guardada o consultar y persistir
                        $coordsSegmento = [];
                        $kmGoogleSeg = null;
                        if (!$refresh && !empty($asigTemp->ruta_coordenadas)) {
                            $decodedSeg = json_decode($asigTemp->ruta_coordenadas, false);
                            if (is_array($decodedSeg)) {
                                $coordsSegmento = $decodedSeg;
                            } elseif (is_object($decodedSeg)) {
                                $coordsSegmento = $decodedSeg->coordenadas ?? [];
                                $kmGoogleSeg = $decodedSeg->km_google ?? null;
                            }
                        }

                        if (empty($coordsSegmento)) {
                            $cTemp = $asigTemp?->Contenedor;
                            $fInicioTemp = $asigTemp->fecha_inicio;
                            $fFinTemp = $asigTemp->fecha_fin ?? $asigSiguienteTemp?->fecha_inicio ?? now();

                            $fInicioTempQuery = ($bitacoraTemp && $bitacoraTemp->viaje_iniciado)
                                ? Carbon::parse($bitacoraTemp->viaje_iniciado)
                                : (($bitacoraTemp && $bitacoraTemp->created_at)
                                    ? Carbon::parse($bitacoraTemp->created_at)
                                    : ($fInicioTemp ? Carbon::parse($fInicioTemp)->startOfDay() : null));

                            $fFinTempQuery = $fFinTemp ? Carbon::parse($fFinTemp)->endOfDay() : null;
                            if ($bitacoraTemp && $bitacoraTemp->viaje_finalizado && $fFinTempQuery) {
                                $viajeFinTemp = Carbon::parse($bitacoraTemp->viaje_finalizado);
                                if ($viajeFinTemp->gt($fFinTempQuery)) {
                                    $fFinTempQuery = $viajeFinTemp;
                                }
                            }

                            $queryBuilder = \DB::table('coordenadas_historial')
                                ->where(function ($query) use ($cTemp, $asigTemp) {
                                    $query->where(function ($q) use ($cTemp) {
                                        if ($cTemp) {
                                            $q->where('ubicacionable_id', $cTemp->id)
                                              ->whereIn('ubicacionable_type', ['rastreo service', 'App\\Models\\DocumCotizacion', 'DocumCotizacion', 'Contenedor']);
                                        } else {
                                            $q->whereRaw('1 = 0');
                                        }
                                    })
                                    ->orWhere(function ($q) use ($asigTemp) {
                                        if ($asigTemp && $asigTemp->id_camion) {
                                            $q->where('ubicacionable_id', $asigTemp->id_camion)
                                              ->whereIn('ubicacionable_type', ['App\\Models\\Equipo', 'App\\Models\\Equipos', 'Equipo', 'Equipos', 'OperadorMovil']);
                                        } else {
                                            $q->whereRaw('1 = 0');
                                        }
                                    });
                                });

                            if ($fInicioTempQuery) {
                                $queryBuilder->where('registrado_en', '>=', $fInicioTempQuery);
                            }
                            if ($fFinTempQuery) {
                                $queryBuilder->where('registrado_en', '<=', $fFinTempQuery);
                            }

                            $coordsSegmento = $queryBuilder
                                ->orderBy('registrado_en', 'asc')
                                ->get(['latitud', 'longitud', 'registrado_en'])
                                ->toArray();

                            // Calcular distancia real con coordenadas completas antes del downsampling
                            $kmHistSegCalculado = 0.0;
                            if (count($coordsSegmento) > 1) {
                                for ($j = 0; $j < count($coordsSegmento) - 1; $j++) {
                                    $kmHistSegCalculado += $this->calcularDistanciaHaversine(
                                        (float)$coordsSegmento[$j]->latitud,
                                        (float)$coordsSegmento[$j]->longitud,
                                        (float)$coordsSegmento[$j+1]->latitud,
                                        (float)$coordsSegmento[$j+1]->longitud
                                    );
                                }
                            }

                            $coordsSegmento = $this->downsampleCoordinates($coordsSegmento, 100);

                            $segFinished = ($asigTemp->fecha_fin !== null) || ($asigSiguienteTemp !== null);
                            if ($segFinished && !empty($coordsSegmento)) {
                                $asigTemp->ruta_coordenadas = json_encode($coordsSegmento);
                                $asigTemp->save();
                            }
                        } else {
                            // Calcular distancia real con coordenadas completas antes del downsampling
                            $kmHistSegCalculado = 0.0;
                            if (count($coordsSegmento) > 1) {
                                for ($j = 0; $j < count($coordsSegmento) - 1; $j++) {
                                    $kmHistSegCalculado += $this->calcularDistanciaHaversine(
                                        (float)$coordsSegmento[$j]->latitud,
                                        (float)$coordsSegmento[$j]->longitud,
                                        (float)$coordsSegmento[$j+1]->latitud,
                                        (float)$coordsSegmento[$j+1]->longitud
                                    );
                                }
                            }
                            $coordsSegmento = $this->downsampleCoordinates($coordsSegmento, 100);
                        }

                        if ($cotiTemp && $cotiTemp->km_recorridos > 0) {
                            $tripKm = (float) $cotiTemp->km_recorridos;
                        } else {
                            $kmHistSeg = $kmHistSegCalculado;

                            if ($kmHistSeg > 0) {
                                $tripKm = $kmHistSeg;
                            }
                            // 2. Respaldo: Coordenadas Diésel
                            elseif ($bitacoraTemp && $bitacoraTemp->latitud && $bitacoraTemp->longitud && $bitacoraSiguienteTemp && $bitacoraSiguienteTemp->latitud && $bitacoraSiguienteTemp->longitud) {
                                $tripKm = $this->obtenerDistanciaPorCarretera(
                                    $bitacoraTemp->latitud,
                                    $bitacoraTemp->longitud,
                                    $bitacoraSiguienteTemp->latitud,
                                    $bitacoraSiguienteTemp->longitud
                                );
                            }
                            // 3. Respaldo: Diferencia de Odómetros
                            elseif ($bitacoraTemp && $bitacoraTemp->odometro > 0 && $bitacoraSiguienteTemp && $bitacoraSiguienteTemp->odometro > 0) {
                                $tripKm = floatval($bitacoraSiguienteTemp->odometro) - floatval($bitacoraTemp->odometro);
                            }
                            // 4. Respaldo: Estimación por Coordenadas
                            elseif ($bitacoraTemp) {
                                $tripKm = $this->calcularDistanciaHaversine(
                                    $bitacoraTemp->latitud,
                                    $bitacoraTemp->longitud,
                                    $bitacoraTemp->latitud_fin,
                                    $bitacoraTemp->longitud_fin
                                );
                            }
                        }

                        $km += $tripKm;
                    }

                    $litrosCalculoConsumo = (float) ($cotizacionSiguienteUrea?->litros_urea ?? 0);
                    $origenKm = 'Acumulado Urea';

                    if (!$siguienteAsignacionUrea) {
                        $observacion = 'Pendiente de siguiente carga de Urea para calcular consumo';
                    } elseif ($km <= 0 && $litrosCalculoConsumo <= 0) {
                        $observacion = 'Sin KM y sin Urea para cálculo';
                    } elseif ($km <= 0) {
                        $observacion = 'Sin KM acumulados';
                    } elseif ($litrosCalculoConsumo <= 0) {
                        $observacion = 'El siguiente viaje de Urea no tiene litros capturados';
                    }
                } else {
                    // No Urea load on this trip
                    $km = 0.0;
                    $origenKm = 'Sin KM';

                    if ($cotizacion && $cotizacion->km_recorridos > 0) {
                        $km = (float) $cotizacion->km_recorridos;
                        if ($kmGoogleSeg !== null && abs($km - floatval($kmGoogleSeg)) < 0.1) {
                            $origenKm = 'Guardado desde Mapa';
                        } else {
                            $origenKm = 'Captura Manual';
                        }
                    } else {
                        // 1. Prioridad: Ruta Historial Coordenadas
                        $kmHistorial = 0.0;
                        if (count($coordenadasRuta) > 1) {
                            for ($i = 0; $i < count($coordenadasRuta) - 1; $i++) {
                                $kmHistorial += $this->calcularDistanciaHaversine(
                                    (float)$coordenadasRuta[$i]->latitud,
                                    (float)$coordenadasRuta[$i]->longitud,
                                    (float)$coordenadasRuta[$i+1]->latitud,
                                    (float)$coordenadasRuta[$i+1]->longitud
                                );
                            }
                        }

                        if ($kmHistorial > 0) {
                            $km = $kmHistorial;
                            $origenKm = 'Ruta Historial Coordenadas';
                        }
                        // 2. Respaldo: Coordenadas Diésel
                        elseif ($bitacora && $bitacora->latitud && $bitacora->longitud && $bitacoraSiguiente && $bitacoraSiguiente->latitud && $bitacoraSiguiente->longitud) {
                            $km = $this->obtenerDistanciaPorCarretera(
                                $bitacora->latitud,
                                $bitacora->longitud,
                                $bitacoraSiguiente->latitud,
                                $bitacoraSiguiente->longitud
                            );
                            if ($km > 0) {
                                $origenKm = 'Coordenadas Diésel';
                            }
                        }
                        // 3. Respaldo: Diferencia de Odómetros
                        elseif ($bitacora && $bitacora->odometro > 0 && $bitacoraSiguiente && $bitacoraSiguiente->odometro > 0) {
                            $km = floatval($bitacoraSiguiente->odometro) - floatval($bitacora->odometro);
                            if ($km > 0) {
                                $origenKm = 'Diferencia Odómetros';
                            }
                        }
                        // 4. Respaldo: Estimación por Coordenadas
                        elseif ($bitacora) {
                            $km = $this->calcularDistanciaHaversine(
                                $bitacora->latitud,
                                $bitacora->longitud,
                                $bitacora->latitud_fin,
                                $bitacora->longitud_fin
                            );
                            if ($km > 0) {
                                $origenKm = 'Estimación Coordenadas';
                            }
                        }
                    }
                    $litrosCalculoConsumo = 0.0;
                    $observacion = 'Sin recarga de Urea en este viaje';
                }
            }

            $rendimiento = $litrosCalculoConsumo > 0
                ? round($km / $litrosCalculoConsumo, 3)
                : null;

            $consumoLitrosPor100Km = $km > 0 && $litrosCalculoConsumo > 0
                ? round(($litrosCalculoConsumo / $km) * 100, 3)
                : null;

            return [
                'asignacion_id' => $asignacion->id,
                'cotizacion_id' => $cotizacion?->id,
                'contenedor_id' => $contenedor?->id,
                'peso_contenedor' => $cotizacion?->peso_contenedor ?? 0,

                'fecha_inicio' => $asignacion->fecha_inicio
                    ? Carbon::parse($asignacion->fecha_inicio)->format('d/m/Y')
                    : 'S/N',

                'fecha_fin' => $asignacion->fecha_fin
                    ? Carbon::parse($asignacion->fecha_fin)->format('d/m/Y')
                    : 'S/N',

                'contenedor' => $this->obtenerTextoContenedor($asignacion),
                'operador' => $asignacion->Operador?->nombre ?? 'S/N',

                'origen' => $cotizacion?->origen ?? 'S/N',
                'destino' => $cotizacion?->destino ?? 'S/N',
                'destino_lat' => $cotizacion?->latitud,
                'destino_lng' => $cotizacion?->longitud,

                'km_recorridos' => round($km, 2),
                'origen_km' => $origenKm,

                'litros_capturados_viaje' => round($litrosCapturadosViaje, 3),
                'litros_calculo_consumo' => round($litrosCalculoConsumo, 3),

                /*
                 * Alias por compatibilidad con vistas anteriores.
                 */
                'litros_diesel' => round($litrosCapturadosViaje, 3),

                'rendimiento_km_litro' => $rendimiento,
                'consumo_litros_100_km' => $consumoLitrosPor100Km,

                'litros_tomados_de_asignacion_id' => $siguienteAsignacion?->id,
                'litros_tomados_de_cotizacion_id' => $cotizacionSiguiente?->id,

                'litros_tomados_de_contenedor' => $siguienteAsignacion
                    ? $this->obtenerTextoContenedor($siguienteAsignacion)
                    : null,

                'observacion' => $observacion,
                'coordenadas_ruta' => $coordenadasRuta,
                'diesel_lat' => $bitacora?->latitud,
                'diesel_lng' => $bitacora?->longitud,
                'diesel_siguiente_lat' => $bitacoraSiguiente?->latitud,
                'diesel_siguiente_lng' => $bitacoraSiguiente?->longitud,
                'viaje_finalizado_lat' => $bitacora?->latitud_fin,
                'viaje_finalizado_lng' => $bitacora?->longitud_fin,
                'viaje_finalizado_fecha' => $bitacora?->viaje_finalizado ? Carbon::parse($bitacora->viaje_finalizado)->format('d/m/Y H:i') : null,
            ];
        });

        $resumen = $this->generarResumen($rows, $tipoConsumo);

        return [
            'success' => true,
            'resumen' => $resumen,
            'rows' => $rows->values()->toArray(),
        ];
    }

    private function generarResumen(Collection $rows, string $tipoConsumo): array
    {
        if ($tipoConsumo === 'urea') {
            $totalKm = round($rows->filter(function($row) {
                return $row['litros_capturados_viaje'] > 0;
            })->sum('km_recorridos'), 2);

            // Fallback if no urea was captured, show sum of all kms
            if ($totalKm <= 0) {
                $totalKm = round($rows->sum('km_recorridos'), 2);
            }
        } else {
            $totalKm = round($rows->sum('km_recorridos'), 2);
        }

        $totalLitrosCapturados = round($rows->sum('litros_capturados_viaje'), 3);
        $totalLitrosCalculo = round($rows->sum('litros_calculo_consumo'), 3);

        $rendimientoPromedio = $totalLitrosCalculo > 0
            ? round($totalKm / $totalLitrosCalculo, 3)
            : null;

        $consumoPromedioLitros100Km = $totalKm > 0 && $totalLitrosCalculo > 0
            ? round(($totalLitrosCalculo / $totalKm) * 100, 3)
            : null;

        $viajesConDatos = $rows->filter(function ($row) {
            return $row['km_recorridos'] > 0 && $row['litros_calculo_consumo'] > 0;
        })->count();

        $viajesSinDatos = $rows->filter(function ($row) {
            return $row['km_recorridos'] <= 0 || $row['litros_calculo_consumo'] <= 0;
        })->count();

        return [
            'total_viajes' => $rows->count(),
            'viajes_con_datos' => $viajesConDatos,
            'viajes_sin_datos' => $viajesSinDatos,
            'total_km' => $totalKm,

            /*
             * Este queda como alias general.
             */
            'total_litros' => $totalLitrosCalculo,

            'total_litros_capturados' => $totalLitrosCapturados,
            'total_litros_calculo' => $totalLitrosCalculo,

            'rendimiento_promedio' => $rendimientoPromedio,
            'consumo_promedio_litros_100_km' => $consumoPromedioLitros100Km,
        ];
    }

    private function obtenerTextoContenedor($asignacion): string
    {
        $contenedor = $asignacion?->Contenedor;
        $cotizacion = $contenedor?->Cotizacion;

        $numContenedor = $contenedor?->num_contenedor ?? 'S/N';

        if ($cotizacion?->referencia_full) {
            $cotizacionSecundaria = Cotizaciones::query()
                ->with('DocCotizacion')
                ->where('referencia_full', $cotizacion->referencia_full)
                ->where('jerarquia', 'Secundario')
                ->first();

            if ($cotizacionSecundaria) {
                $docSecundario = $cotizacionSecundaria->DocCotizacion;

                if ($docSecundario instanceof Collection) {
                    $numSecundario = $docSecundario->first()?->num_contenedor;
                } else {
                    $numSecundario = $docSecundario?->num_contenedor;
                }

                 $numContenedor .= ' / ' . ($numSecundario ?? 'S/N');
            }
        }

        return $numContenedor;
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

        // Factor de curvatura por carreteras (aproximado 1.18x en topografía mexicana)
        return round($distance * 1.18, 2);
    }

    private function downsampleCoordinates(array $coordinates, int $maxPoints = 100): array
    {
        return $coordinates;
    }

    public function procesarAsignacionUnica(int $asignacionId, string $tipoConsumo = 'diesel', bool $refresh = false): array
    {
        $asignacion = Asignaciones::with([
            'Contenedor.Cotizacion',
            'Operador',
            'Camion',
            'bitacoraViaje',
        ])->findOrFail($asignacionId);

        $unidadId = $asignacion->id_camion;

        $filtroPrincipal = function ($q) {
            $q->where(function ($q2) {
                $q2->where('jerarquia', '!=', 'Secundario')
                    ->orWhereNull('jerarquia');
            });
        };

        $siguienteAsignacion = Asignaciones::query()
            ->with([
                'Contenedor.Cotizacion',
                'Operador',
                'Camion',
                'bitacoraViaje',
            ])
            ->where('id_camion', $unidadId)
            ->whereHas('Contenedor.Cotizacion', $filtroPrincipal)
            ->where(function ($q) use ($asignacion) {
                $q->where('fecha_inicio', '>', $asignacion->fecha_inicio)
                    ->orWhere(function ($q2) use ($asignacion) {
                        $q2->where('fecha_inicio', $asignacion->fecha_inicio)
                            ->where('id', '>', $asignacion->id);
                    });
            })
            ->orderBy('fecha_inicio')
            ->orderBy('id')
            ->first();

        $contenedor = $asignacion->Contenedor;
        $cotizacion = $contenedor?->Cotizacion;

        $bitacora = $asignacion->bitacoraViaje;
        $bitacoraSiguiente = $siguienteAsignacion?->bitacoraViaje;
        $cotizacionSiguiente = $siguienteAsignacion?->Contenedor?->Cotizacion;

        $fechaInicio = $asignacion->fecha_inicio;
        $fechaFin = $asignacion->fecha_fin ?? $siguienteAsignacion?->fecha_inicio ?? now();
        $coordenadasRuta = [];
        $esHistoricoGuardado = false;

        $kmGoogle = null;
        if (!$refresh && !empty($asignacion->ruta_coordenadas)) {
            $decoded = json_decode($asignacion->ruta_coordenadas, false);
            if (is_array($decoded)) {
                $coordenadasRuta = $decoded;
                $esHistoricoGuardado = true;
            } elseif (is_object($decoded)) {
                $coordenadasRuta = $decoded->coordenadas ?? [];
                $kmGoogle = $decoded->km_google ?? null;
                $esHistoricoGuardado = true;
            }
        }

        if (empty($coordenadasRuta)) {
            $fechaInicioQuery = ($bitacora && $bitacora->viaje_iniciado)
                ? Carbon::parse($bitacora->viaje_iniciado)
                : (($bitacora && $bitacora->created_at)
                    ? Carbon::parse($bitacora->created_at)
                    : Carbon::parse($fechaInicio)->startOfDay());

            $fechaFinQuery = Carbon::parse($fechaFin)->endOfDay();
            if ($bitacora && $bitacora->viaje_finalizado) {
                $viajeFin = Carbon::parse($bitacora->viaje_finalizado);
                if ($viajeFin->gt($fechaFinQuery)) {
                    $fechaFinQuery = $viajeFin;
                }
            }

            $coordenadasRuta = \DB::table('coordenadas_historial')
                ->where(function ($query) use ($contenedor, $asignacion) {
                    $query->where(function ($q) use ($contenedor) {
                        if ($contenedor) {
                            $q->where('ubicacionable_id', $contenedor->id)
                              ->whereIn('ubicacionable_type', ['rastreo service', 'App\\Models\\DocumCotizacion', 'DocumCotizacion', 'Contenedor']);
                        } else {
                            $q->whereRaw('1 = 0');
                        }
                    })
                    ->orWhere(function ($q) use ($asignacion) {
                        if ($asignacion && $asignacion->id_camion) {
                            $q->where('ubicacionable_id', $asignacion->id_camion)
                              ->whereIn('ubicacionable_type', ['App\\Models\\Equipo', 'App\\Models\\Equipos', 'Equipo', 'Equipos', 'OperadorMovil']);
                        } else {
                            $q->whereRaw('1 = 0');
                        }
                    });
                })
                ->where('registrado_en', '>=', $fechaInicioQuery)
                ->where('registrado_en', '<=', $fechaFinQuery)
                ->orderBy('registrado_en', 'asc')
                ->get(['latitud', 'longitud', 'registrado_en'])
                ->toArray();

            $kmHistorialCalculado = 0.0;
            if (count($coordenadasRuta) > 1) {
                for ($i = 0; $i < count($coordenadasRuta) - 1; $i++) {
                    $kmHistorialCalculado += $this->calcularDistanciaHaversine(
                        (float)$coordenadasRuta[$i]->latitud,
                        (float)$coordenadasRuta[$i]->longitud,
                        (float)$coordenadasRuta[$i+1]->latitud,
                        (float)$coordenadasRuta[$i+1]->longitud
                    );
                }
            }

            $coordenadasRuta = $this->downsampleCoordinates($coordenadasRuta, 100);

            $viajeFinalizado = ($asignacion->fecha_fin !== null) || ($siguienteAsignacion !== null);
            if ($viajeFinalizado && !empty($coordenadasRuta)) {
                $asignacion->ruta_coordenadas = json_encode($coordenadasRuta);
                $asignacion->save();
            }
        } else {
            $kmHistorialCalculado = 0.0;
            if (count($coordenadasRuta) > 1) {
                for ($i = 0; $i < count($coordenadasRuta) - 1; $i++) {
                    $kmHistorialCalculado += $this->calcularDistanciaHaversine(
                        (float)$coordenadasRuta[$i]->latitud,
                        (float)$coordenadasRuta[$i]->longitud,
                        (float)$coordenadasRuta[$i+1]->latitud,
                        (float)$coordenadasRuta[$i+1]->longitud
                    );
                }
            }
            $coordenadasRuta = $this->downsampleCoordinates($coordenadasRuta, 100);
        }

        $esEstimado = false;
        $origenKm = 'Sin KM';
        $litrosCapturadosViaje = 0.0;
        $litrosCalculoConsumo = 0.0;
        $observacion = null;
        $km = 0.0;

        if ($tipoConsumo === 'diesel') {
            if ($cotizacion && $cotizacion->km_recorridos > 0) {
                $km = (float) $cotizacion->km_recorridos;
                $esEstimado = false;
                if ($kmGoogle !== null && abs($km - floatval($kmGoogle)) < 0.1) {
                    $origenKm = 'Guardado desde Mapa';
                } else {
                    $origenKm = 'Captura Manual';
                }
            } else {
                $kmHistorial = $kmHistorialCalculado;
                if ($kmHistorial > 0) {
                    $km = $kmHistorial;
                    $esEstimado = true;
                    $origenKm = 'Ruta Historial Coordenadas';
                }
                elseif ($bitacora && $bitacora->latitud && $bitacora->longitud && $bitacoraSiguiente && $bitacoraSiguiente->latitud && $bitacoraSiguiente->longitud) {
                    $kmEstimado = $this->obtenerDistanciaPorCarretera(
                        $bitacora->latitud,
                        $bitacora->longitud,
                        $bitacoraSiguiente->latitud,
                        $bitacoraSiguiente->longitud
                    );
                    if ($kmEstimado > 0) {
                        $km = $kmEstimado;
                        $esEstimado = true;
                        $origenKm = 'Coordenadas Diésel';
                    }
                }
                elseif ($bitacora && $bitacora->odometro > 0 && $bitacoraSiguiente && $bitacoraSiguiente->odometro > 0) {
                    $kmEstimado = floatval($bitacoraSiguiente->odometro) - floatval($bitacora->odometro);
                    if ($kmEstimado > 0) {
                        $km = $kmEstimado;
                        $esEstimado = false;
                        $origenKm = 'Diferencia Odómetros';
                    }
                }
                elseif ($bitacora) {
                    $kmEstimado = $this->calcularDistanciaHaversine(
                        $bitacora->latitud,
                        $bitacora->longitud,
                        $bitacora->latitud_fin,
                        $bitacora->longitud_fin
                    );
                    if ($kmEstimado > 0) {
                        $km = $kmEstimado;
                        $esEstimado = true;
                        $origenKm = 'Estimación Coordenadas';
                    }
                }
            }
        }

        return [
            'asignacion_id' => $asignacion->id,
            'cotizacion_id' => $cotizacion?->id,
            'contenedor_id' => $contenedor?->id,
            'peso_contenedor' => $cotizacion?->peso_contenedor ?? 0,
            'fecha_inicio' => $asignacion->fecha_inicio ? Carbon::parse($asignacion->fecha_inicio)->format('d/m/Y') : 'S/N',
            'fecha_fin' => $asignacion->fecha_fin ? Carbon::parse($asignacion->fecha_fin)->format('d/m/Y') : 'S/N',
            'contenedor' => $this->obtenerTextoContenedor($asignacion),
            'operador' => $asignacion->Operador?->nombre ?? 'S/N',
            'origen' => $cotizacion?->origen ?? 'S/N',
            'destino' => $cotizacion?->destino ?? 'S/N',
            'destino_lat' => $cotizacion?->latitud,
            'destino_lng' => $cotizacion?->longitud,
            'km_recorridos' => round($km, 2),
            'origen_km' => $origenKm,
            'coordenadas_ruta' => $coordenadasRuta,
            'es_historico_guardado' => $esHistoricoGuardado,
            'diesel_lat' => $bitacora?->latitud,
            'diesel_lng' => $bitacora?->longitud,
            'viaje_finalizado_lat' => $bitacora?->latitud_fin ?? $bitacoraSiguiente?->latitud,
            'viaje_finalizado_lng' => $bitacora?->longitud_fin ?? $bitacoraSiguiente?->longitud,
            'viaje_finalizado_fecha' => $bitacora?->viaje_finalizado,
        ];
    }
}
