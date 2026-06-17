<?php

namespace App\Services;

use App\Models\Asignaciones;
use App\Models\Cotizaciones;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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

        $rows = $asignacionesBase->map(function ($asignacion, $index) use ($asignacionesParaCalculo) {
            $contenedor = $asignacion->Contenedor;
            $cotizacion = $contenedor?->Cotizacion;

            $siguienteAsignacion = $asignacionesParaCalculo->get($index + 1);
            $cotizacionSiguiente = $siguienteAsignacion?->Contenedor?->Cotizacion;

            $km = (float) ($cotizacion?->km_recorridos ?? 0);
            $litrosCapturadosViaje = (float) ($cotizacion?->litros_diesel ?? 0);
            $litrosCalculoConsumo = (float) ($cotizacionSiguiente?->litros_diesel ?? 0);

            $rendimiento = $litrosCalculoConsumo > 0
                ? round($km / $litrosCalculoConsumo, 3)
                : null;

            $consumoLitrosPor100Km = $km > 0 && $litrosCalculoConsumo > 0
                ? round(($litrosCalculoConsumo / $km) * 100, 3)
                : null;

            $observacion = null;

            if (!$siguienteAsignacion) {
                $observacion = 'Pendiente de siguiente carga para calcular consumo';
            } elseif ($km <= 0 && $litrosCalculoConsumo <= 0) {
                $observacion = 'Sin KM y sin litros para cálculo';
            } elseif ($km <= 0) {
                $observacion = 'Sin KM capturados';
            } elseif ($litrosCalculoConsumo <= 0) {
                $observacion = 'El siguiente viaje no tiene litros capturados';
            }

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

                'km_recorridos' => round($km, 2),

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
            ];
        });

        $resumen = $this->generarResumen($rows);

        return [
            'success' => true,
            'resumen' => $resumen,
            'rows' => $rows->values()->toArray(),
        ];
    }

    private function generarResumen(Collection $rows): array
    {
        $totalKm = round($rows->sum('km_recorridos'), 2);

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
}
