<?php

namespace App\Services;

use App\Models\Socio;
use App\Models\SocioConfiguracion;
use App\Models\Equipo;
use App\Models\Asignaciones;
use App\Models\Gasto;
use App\Models\SocioCalculoPeriodo;
use App\Models\SocioCalculoDetalle;
use App\Models\SocioCalculoViajeHistorico;
use Illuminate\Support\Facades\DB;

class SociosService
{
    public function getSocios(int $idEmpresa)
    {
        return Socio::withTrashed()->where('id_empresa', $idEmpresa)->orderBy('nombre')->get();
    }

    public function storeSocio(array $data, int $idEmpresa): Socio
    {
        $data['id_empresa'] = $idEmpresa;
        return Socio::create($data);
    }

    public function updateSocio(Socio $socio, array $data): Socio
    {
        $socio->update($data);
        return $socio;
    }

    public function getConfigs(int $idEmpresa)
    {
        return SocioConfiguracion::withTrashed()->with(['socio', 'equipo'])
            ->where('id_empresa', $idEmpresa)
            ->get();
    }

    public function storeConfig(array $data, int $idEmpresa): SocioConfiguracion
    {
        $data['id_empresa'] = $idEmpresa;
        return SocioConfiguracion::create($data);
    }

    public function updateConfig(SocioConfiguracion $config, array $data): SocioConfiguracion
    {
        $config->update($data);
        return $config;
    }

    /**
     * Calculates the grouped utility splits, including global period expenses.
     */
    public function calculatePartnerUtility(string $startDate, string $endDate, int $idEmpresa, ?int $socioId = null, ?int $equipoId = null): array
    {
        $reporteriaService = app(ReporteriaService::class);
        $viajesData = $reporteriaService->getContenedorUtilidad($startDate, $endDate, $idEmpresa);

        // Fetch general indirect expenses of the period (reutilizando exactamente la consulta del Reporte de Resultados)
        $gastosGenerales = $reporteriaService->getGastosGeneralesPeriodo($startDate, $endDate, $idEmpresa);
        $gastosGeneralesPeriodo = (float) $gastosGenerales->sum('monto_aplicado');

        $configsQuery = SocioConfiguracion::whereHas('socio', function ($query) {
                $query->where('activo', 1)->whereNull('deleted_at');
            })
            ->with(['socio', 'equipo'])
            ->where('id_empresa', $idEmpresa)
            ->where('activo', true);

        if ($socioId) {
            $configsQuery->where('socio_id', $socioId);
        }
        if ($equipoId) {
            $configsQuery->where('equipo_id', $equipoId);
        }

        $configs = $configsQuery->get();

        // 1. Group trips and calculate totals per Socio / Config
        $sociosSplit = [];
        foreach ($configs as $c) {
            $key = $c->socio_id . '_' . $c->equipo_id;
            $socioNombre = $c->socio?->nombre ?? 'Socio Desconocido';
            $configUnidad = $c->equipo ? (($c->equipo->id_equipo ? $c->equipo->id_equipo . ' ' : '') . $c->equipo->placas . ' (' . $c->equipo->marca . ')') : 'N/A';

            if (!isset($sociosSplit[$key])) {
                $sociosSplit[$key] = [
                    'socio_id' => $c->socio_id,
                    'socio' => $socioNombre,
                    'unidad_configurada' => $configUnidad,
                    'tipo_pago' => $c->tipo_pago,
                    'valor' => (float) $c->valor,
                    'equipo_id' => $c->equipo_id,
                    'utilidad_bruta_acumulada' => 0,
                    'gastos_operativos_acumulados' => 0,
                    'numero_viajes' => 0,
                    'distribucion_socio' => 0
                ];
            }
        }

        $totalUtilidadBrutaReporte = 0;
        $totalGastosOperativosViajes = 0;
        $viajesDesglose = [];

        foreach ($viajesData as $v) {
            $numContenedor = $v['numContenedor'];
            $cliente = $v['cliente'];
            $precioViaje = (float) ($v['precioViaje'] ?? 0);
            $pagoOperacion = (float) ($v['pagoOperacion'] ?? 0);
            $gastosViaje = (float) ($v['gastosViaje'] ?? 0);
            $gastosExtra = (float) ($v['gastosExtra'] ?? 0);
            $gastosDiferidos = (float) ($v['gastosDiferidos'] ?? 0);
            $dineroSinJustificar = (float) ($v['dineroViajeSinJustificar'] ?? 0);

            // Utilidad bruta del viaje = Flete / Ingreso - Pago de Operación / Sueldo
            $utilidadBrutaViaje = $precioViaje - $pagoOperacion;
            // Gastos operativos directos del viaje/unidad (combustibles, casetas, viáticos, diferidos)
            $gastosOperativosViaje = $gastosViaje + $gastosExtra + $gastosDiferidos + $dineroSinJustificar;
            // Utilidad neta real del viaje (coincide exactamente con $v['utilidad'] de ReporteriaService)
            $utilidadNetaViaje = (float) ($v['utilidad'] ?? ($utilidadBrutaViaje - $gastosOperativosViaje));

            $viajeInicia = $v['viajeInicia']; // Y-m-d

            // Find matching asignacion to get the truck (id_camion)
            $numContenedorClean = explode(' / ', $numContenedor)[0] ?? $numContenedor;
            $asignacion = Asignaciones::whereHas('Contenedor', function ($q) use ($numContenedorClean) {
                $q->where('num_contenedor', $numContenedorClean);
            })->first();

            $camionId = $asignacion ? $asignacion->id_camion : null;

            if ($equipoId && $camionId != $equipoId) {
                continue;
            }

            // Find configs active for this truck on the trip date
            $matchedConfigs = $configs->filter(function ($c) use ($camionId, $viajeInicia, $socioId) {
                if ($c->equipo_id != $camionId) return false;
                if ($socioId && $c->socio_id != $socioId) return false;

                $startValid = is_null($c->fecha_inicio) || ($viajeInicia >= $c->fecha_inicio);
                $endValid = is_null($c->fecha_fin) || ($viajeInicia <= $c->fecha_fin);

                return $startValid && $endValid;
            });

            if (($socioId || $equipoId) && $matchedConfigs->isEmpty()) {
                continue;
            }

            // En el Reporte de Resultados, Utilidad Bruta es la suma de la utilidad de todos los viajes:
            $totalUtilidadBrutaReporte += $utilidadNetaViaje;
            $totalGastosOperativosViajes += $gastosOperativosViaje;

            $camion = $camionId ? Equipo::find($camionId) : null;
            $camionName = $camion ? (($camion->id_equipo ? $camion->id_equipo . ' ' : '') . $camion->placas . ' (' . $camion->marca . ')') : 'Desconocido';

            $viajesDesglose[] = [
                'contenedor' => $numContenedor,
                'cliente' => $cliente,
                'unidad' => $camionName,
                'utilidad_viaje' => $utilidadNetaViaje,
                'fecha_viaje' => $viajeInicia,
                'estatus_viaje' => $v['estatusViaje'] ?? 'S/N'
            ];

            foreach ($matchedConfigs as $c) {
                $key = $c->socio_id . '_' . $c->equipo_id;
                if (!isset($sociosSplit[$key])) {
                    $socioNombre = $c->socio?->nombre ?? 'Socio Desconocido';
                    $configUnidad = $c->equipo ? (($c->equipo->id_equipo ? $c->equipo->id_equipo . ' ' : '') . $c->equipo->placas . ' (' . $c->equipo->marca . ')') : 'N/A';
                    $sociosSplit[$key] = [
                        'socio_id' => $c->socio_id,
                        'socio' => $socioNombre,
                        'unidad_configurada' => $configUnidad,
                        'tipo_pago' => $c->tipo_pago,
                        'valor' => (float) $c->valor,
                        'equipo_id' => $c->equipo_id,
                        'utilidad_bruta_acumulada' => 0,
                        'gastos_operativos_acumulados' => 0,
                        'numero_viajes' => 0,
                        'distribucion_socio' => 0
                    ];
                }

                $sociosSplit[$key]['utilidad_bruta_acumulada'] += $utilidadBrutaViaje;
                $sociosSplit[$key]['gastos_operativos_acumulados'] += $gastosOperativosViaje;
                $sociosSplit[$key]['numero_viajes']++;
            }
        }

        // 2. Direct truck trip expenses and calculate partner distribution based on Distributable Net Utility
        $sociosFinal = [];
        $totalPagosSocios = 0;

        $corteGuardado = SocioCalculoPeriodo::where('id_empresa', $idEmpresa)
            ->where('fecha_desde', $startDate)
            ->where('fecha_hasta', $endDate)
            ->exists();

        // Calculate general indirect expenses per equipment
        $uniqueEquiposCount = collect($sociosSplit)->pluck('equipo_id')->unique()->filter()->count();
        $gastoGeneralPorEquipo = $uniqueEquiposCount > 0 ? ($gastosGeneralesPeriodo / $uniqueEquiposCount) : $gastosGeneralesPeriodo;

        foreach ($sociosSplit as $key => &$split) {
            $sId = $split['socio_id'];
            $camionId = $split['equipo_id'];

            // Gastos Camión = Gastos operativos acumulados de sus viajes (combustible, casetas, viáticos, diferidos)
            $split['gastos_camion'] = $split['gastos_operativos_acumulados'];
            // Utilidad Neta Camión (operativa de viajes)
            $split['utilidad_neta_camion'] = $split['utilidad_bruta_acumulada'] - $split['gastos_camion'];

            // Utilidad a Repartir del Camión (después de deducir gastos indirectos del mes)
            $utilidadRepartirCamion = max(0, $split['utilidad_neta_camion'] - $gastoGeneralPorEquipo);
            $split['utilidad_a_repartir'] = $utilidadRepartirCamion;

            // A cada socio se reparte del total de la utilidad neta a repartir * % de cada socio (o cuota fija por viaje)
            if ($split['tipo_pago'] === 'porcentaje') {
                $split['distribucion_socio'] = max(0, round($utilidadRepartirCamion * ($split['valor'] / 100), 2));
            } else {
                // Fixed fee per trip
                $split['distribucion_socio'] = round($split['numero_viajes'] * $split['valor'], 2);
            }

            $totalPagosSocios += $split['distribucion_socio'];

            // Get historical balance metrics for this partner
            $balance = $this->getSocioBalance($sId, $endDate);

            $totalAsignado = $balance['total_asignado'];
            // If the period was already saved, subtract previously saved distribution for this period to avoid double counting
            if ($corteGuardado) {
                $periodo = SocioCalculoPeriodo::where('id_empresa', $idEmpresa)
                    ->where('fecha_desde', $startDate)
                    ->where('fecha_hasta', $endDate)
                    ->first();
                if ($periodo) {
                    $savedDetail = SocioCalculoDetalle::where('calculo_periodo_id', $periodo->id)
                        ->where('socio_id', $sId)
                        ->first();
                    if ($savedDetail) {
                        $totalAsignado -= $savedDetail->monto_distribuido;
                    }
                }
            }

            $saldoAcumulado = $totalAsignado + $split['distribucion_socio'];
            $saldoPendiente = $saldoAcumulado - $balance['total_pagado'];

            $deudaPendiente = $saldoPendiente < 0 ? abs($saldoPendiente) : 0;
            $observacion = $saldoPendiente < 0 ? 'Saldo en contra (Ajuste de corte/Pagos excedidos)' : '';

            $sociosFinal[] = [
                'socio_id' => $split['socio_id'],
                'socio' => $split['socio'],
                'unidad' => $split['unidad_configurada'],
                'tipo_pago' => $split['tipo_pago'] === 'porcentaje' ? 'Porcentaje' : 'Cuota Fija',
                'factor' => $split['tipo_pago'] === 'porcentaje' ? ($split['valor'] . '%') : ('$' . number_format($split['valor'], 2)),
                'viajes_realizados' => $split['numero_viajes'],
                'utilidad_bruta' => $split['utilidad_bruta_acumulada'],
                'gastos_camion' => $split['gastos_camion'],
                'utilidad_neta' => $split['utilidad_neta_camion'],
                'utilidad_a_repartir' => $utilidadRepartirCamion,
                'monto_distribuido' => $split['distribucion_socio'],
                'saldo_acumulado' => $saldoAcumulado,
                'total_pagado' => $balance['total_pagado'],
                'saldo_pendiente' => $saldoPendiente,
                'deuda_pendiente' => $deudaPendiente,
                'observacion' => $observacion
            ];
        }

        // 3. Pagos Desglose (Chronological order: newest to oldest)
        $pagosQuery = \App\Models\SocioPago::with(['socio', 'banco', 'user', 'calculoPeriodo'])
            ->where('id_empresa', $idEmpresa)
            ->whereBetween('fecha_aplicacion', [$startDate, $endDate]);

        if ($socioId) {
            $pagosQuery->where('socio_id', $socioId);
        }
        if ($equipoId) {
            $pagosQuery->whereHas('socio.configurations', function($q) use ($equipoId) {
                $q->where('equipo_id', $equipoId);
            });
        }

        $pagosDesglose = $pagosQuery->orderBy('fecha_aplicacion', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($pago) {
                $periodoTexto = '';
                if ($pago->calculoPeriodo) {
                    $periodoTexto = ' (Corte #' . $pago->calculo_periodo_id . ': ' .
                        \Carbon\Carbon::parse($pago->calculoPeriodo->fecha_desde)->format('d/m/Y') . ' al ' .
                        \Carbon\Carbon::parse($pago->calculoPeriodo->fecha_hasta)->format('d/m/Y') . ')';
                }
                return [
                    'id' => $pago->id,
                    'fecha_pago' => $pago->fecha_aplicacion ? $pago->fecha_aplicacion->format('Y-m-d') : null,
                    'fecha_formateada' => $pago->fecha_aplicacion ? $pago->fecha_aplicacion->format('d-m-Y') : 'S/N',
                    'socio' => $pago->socio->nombre ?? 'S/N',
                    'concepto' => $pago->calculo_periodo_id ? ('Liquidación de Utilidad' . $periodoTexto) : 'Abono / Anticipo a Cuenta',
                    'banco' => $pago->banco ? ($pago->banco->nombre_banco . ($pago->banco->cuenta_bancaria ? ' (' . $pago->banco->cuenta_bancaria . ')' : '')) : 'N/A',
                    'monto' => (float)$pago->monto,
                    'registrado_por' => $pago->user->name ?? 'Sistema'
                ];
            })
            ->values()
            ->toArray();

        $totalPagadoPeriodo = collect($pagosDesglose)->sum('monto');
        $totalPagadoSociosColumna = collect($sociosFinal)->sum('total_pagado');

        return [
            'fecha_desde' => $startDate,
            'fecha_hasta' => $endDate,
            'total_utilidad_bruta_viajes' => round($totalUtilidadBrutaReporte, 2),
            'total_gastos_periodo' => round($gastosGeneralesPeriodo, 2),
            'utilidad_neta_distribuible' => round($totalUtilidadBrutaReporte - $gastosGeneralesPeriodo, 2),
            'total_distribuido_socios' => round($totalPagosSocios, 2),
            'total_pagado_socios' => round($totalPagadoSociosColumna, 2),
            'utilidad_neta_empresa' => round(($totalUtilidadBrutaReporte - $gastosGeneralesPeriodo) - $totalPagosSocios, 2),
            'total_pagado_periodo' => (float) $totalPagadoPeriodo,
            'socios_desglose' => $sociosFinal,
            'viajes_desglose' => $viajesDesglose,
            'pagos_desglose' => $pagosDesglose
        ];
    }

    /**
     * Saves a calculation snapshot of a period to the database.
     */
    public function saveCalculoPeriodo(array $params, int $idEmpresa, int $userId): array
    {
        $startDate = $params['from'];
        $endDate = $params['to'];
        $equipoId = isset($params['equipo_id']) && $params['equipo_id'] !== '' ? (int)$params['equipo_id'] : null;

        // 1. Overlap Validation
        $overlapQuery = SocioCalculoPeriodo::where('id_empresa', $idEmpresa)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('fecha_desde', '<=', $endDate)
                      ->where('fecha_hasta', '>=', $startDate);
            })
            ->where(function ($query) use ($startDate, $endDate, $equipoId) {
                $query->where('fecha_desde', '!=', $startDate)
                      ->orWhere('fecha_hasta', '!=', $endDate)
                      ->orWhere('equipo_id', '!=', $equipoId);
            });

        if ($equipoId !== null) {
            $overlapQuery->where(function ($query) use ($equipoId) {
                $query->whereNull('equipo_id')
                      ->orWhere('equipo_id', $equipoId);
            });
        }
        $overlap = $overlapQuery->first();

        if ($overlap) {
            $overlapLabel = $overlap->equipo ? 'unidad ' . ($overlap->equipo->id_equipo ?? $overlap->equipo->placas) : 'corte global';
            return [
                'success' => false,
                'Mensaje' => 'El rango de fechas se traslapa con un corte existente para ' . $overlapLabel . ': (' . $overlap->fecha_desde . ' al ' . $overlap->fecha_hasta . ').'
            ];
        }

        // Get calculations data
        $calcData = $this->calculatePartnerUtility($startDate, $endDate, $idEmpresa, null, $equipoId);

        return DB::transaction(function () use ($calcData, $startDate, $endDate, $idEmpresa, $userId, $equipoId) {
            // Find if period already exists to update it and avoid changing parent ID (which would break payment relations)
            $periodo = SocioCalculoPeriodo::where('id_empresa', $idEmpresa)
                ->where('fecha_desde', $startDate)
                ->where('fecha_hasta', $endDate)
                ->where('equipo_id', $equipoId)
                ->first();

            if ($periodo) {
                $periodo->update([
                    'total_utilidad_bruta_viajes' => $calcData['total_utilidad_bruta_viajes'],
                    'total_gastos_periodo' => $calcData['total_gastos_periodo'],
                    'utilidad_neta_distribuible' => $calcData['utilidad_neta_distribuible'],
                    'user_id' => $userId
                ]);
                $periodo->detalles()->delete();
                $periodo->viajesHistorico()->delete();
            } else {
                $periodo = SocioCalculoPeriodo::create([
                    'id_empresa' => $idEmpresa,
                    'equipo_id' => $equipoId,
                    'fecha_desde' => $startDate,
                    'fecha_hasta' => $endDate,
                    'total_utilidad_bruta_viajes' => $calcData['total_utilidad_bruta_viajes'],
                    'total_gastos_periodo' => $calcData['total_gastos_periodo'],
                    'utilidad_neta_distribuible' => $calcData['utilidad_neta_distribuible'],
                    'user_id' => $userId
                ]);
            }

            foreach ($calcData['socios_desglose'] as $soc) {
                SocioCalculoDetalle::create([
                    'calculo_periodo_id' => $periodo->id,
                    'socio_id' => $soc['socio_id'],
                    'tipo_pago' => $soc['tipo_pago'] === 'Porcentaje' ? 'porcentaje' : 'cuota_fija',
                    'valor_pactado' => (float) filter_var($soc['factor'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                    'monto_distribuido' => $soc['monto_distribuido']
                ]);
            }

            foreach ($calcData['viajes_desglose'] as $viaje) {
                SocioCalculoViajeHistorico::create([
                    'calculo_periodo_id' => $periodo->id,
                    'contenedor' => $viaje['contenedor'],
                    'cliente' => $viaje['cliente'],
                    'unidad' => $viaje['unidad'],
                    'utilidad_viaje' => $viaje['utilidad_viaje'],
                    'fecha_viaje' => $viaje['fecha_viaje']
                ]);
            }

            return ['success' => true, 'Mensaje' => 'Corte guardado con éxito.'];
        });
    }

    /**
     * Checks if there are discrepancies between current DB and saved snapshot
     */
    public function getSnapshotComparativa(string $startDate, string $endDate, int $idEmpresa, ?int $equipoId = null): array
    {
        // 1. Check for overlap
        $overlapQuery = SocioCalculoPeriodo::where('id_empresa', $idEmpresa)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('fecha_desde', '<=', $endDate)
                      ->where('fecha_hasta', '>=', $startDate);
            })
            ->where(function ($query) use ($startDate, $endDate, $equipoId) {
                $query->where('fecha_desde', '!=', $startDate)
                      ->orWhere('fecha_hasta', '!=', $endDate)
                      ->orWhere('equipo_id', '!=', $equipoId);
            });

        if ($equipoId !== null) {
            $overlapQuery->where(function ($query) use ($equipoId) {
                $query->whereNull('equipo_id')
                      ->orWhere('equipo_id', $equipoId);
            });
        }
        $overlap = $overlapQuery->first();

        if ($overlap) {
            $overlapLabel = $overlap->equipo ? 'unidad ' . ($overlap->equipo->id_equipo ?? $overlap->equipo->placas) : 'corte global';
            return [
                'has_saved' => false,
                'has_overlap' => true,
                'overlap_message' => 'El rango se traslapa con un corte existente para ' . $overlapLabel . ': (' . $overlap->fecha_desde . ' al ' . $overlap->fecha_hasta . ').'
            ];
        }

        $periodo = SocioCalculoPeriodo::where('id_empresa', $idEmpresa)
            ->where('fecha_desde', $startDate)
            ->where('fecha_hasta', $endDate)
            ->where('equipo_id', $equipoId)
            ->with(['viajesHistorico', 'detalles.socio'])
            ->first();

        if (!$periodo) {
            return ['has_saved' => false];
        }

        $calcActual = $this->calculatePartnerUtility($startDate, $endDate, $idEmpresa, null, $equipoId);

        $diferencias = [];
        // Check trip discrepancies
        foreach ($calcActual['viajes_desglose'] as $act) {
            $hist = $periodo->viajesHistorico->where('contenedor', $act['contenedor'])->first();
            if (!$hist) {
                $diferencias[] = [
                    'tipo' => 'Viaje Nuevo',
                    'referencia' => $act['contenedor'],
                    'guardado' => 0.00,
                    'actual' => $act['utilidad_viaje'],
                    'diferencia' => $act['utilidad_viaje']
                ];
            } elseif (abs($hist->utilidad_viaje - $act['utilidad_viaje']) > 0.01) {
                $diferencias[] = [
                    'tipo' => 'Utilidad Modificada',
                    'referencia' => $act['contenedor'],
                    'guardado' => (float)$hist->utilidad_viaje,
                    'actual' => $act['utilidad_viaje'],
                    'diferencia' => $act['utilidad_viaje'] - $hist->utilidad_viaje
                ];
            }
        }

        foreach ($periodo->viajesHistorico as $hist) {
            $act = collect($calcActual['viajes_desglose'])->where('contenedor', $hist->contenedor)->first();
            if (!$act) {
                $diferencias[] = [
                    'tipo' => 'Viaje Eliminado / Anulado',
                    'referencia' => $hist->contenedor,
                    'guardado' => (float)$hist->utilidad_viaje,
                    'actual' => 0.00,
                    'diferencia' => -$hist->utilidad_viaje
                ];
            }
        }

        // Check general expenses discrepancies
        if (abs($periodo->total_gastos_periodo - $calcActual['total_gastos_periodo']) > 0.01) {
            $diferencias[] = [
                'tipo' => 'Gastos del Periodo Modificados',
                'referencia' => 'Gastos Indirectos del Mes',
                'guardado' => (float)$periodo->total_gastos_periodo,
                'actual' => $calcActual['total_gastos_periodo'],
                'diferencia' => $calcActual['total_gastos_periodo'] - $periodo->total_gastos_periodo
            ];
        }

        return [
            'has_saved' => true,
            'fecha_guardado' => $periodo->created_at->format('Y-m-d H:i'),
            'utilidad_bruta_guardado' => (float)$periodo->total_utilidad_bruta_viajes,
            'utilidad_bruta_actual' => $calcActual['total_utilidad_bruta_viajes'],
            'gastos_guardado' => (float)$periodo->total_gastos_periodo,
            'gastos_actual' => $calcActual['total_gastos_periodo'],
            'diferencias' => $diferencias
        ];
    }

    public function getSocioBalance(int $socioId, ?string $endDate = null): array
    {
        $detailsQuery = \App\Models\SocioCalculoDetalle::where('socio_id', $socioId);
        if ($endDate) {
            $detailsQuery->whereHas('calculoPeriodo', function ($q) use ($endDate) {
                $q->where('fecha_hasta', '<=', $endDate);
            });
        }
        $totalAsignado = $detailsQuery->sum('monto_distribuido');

        $pagosQuery = \App\Models\SocioPago::where('socio_id', $socioId);
        if ($endDate) {
            $pagosQuery->where('fecha_aplicacion', '<=', $endDate);
        }
        $totalPagado = $pagosQuery->sum('monto');

        return [
            'total_asignado' => (float)$totalAsignado,
            'total_pagado' => (float)$totalPagado,
            'saldo_pendiente' => (float)($totalAsignado - $totalPagado)
        ];
    }

    public function registrarPagoSocio(array $data, int $idEmpresa, int $userId): \App\Models\SocioPago
    {
        return DB::transaction(function () use ($data, $idEmpresa, $userId) {
            $socio = Socio::findOrFail($data['socio_id']);

            $pago = \App\Models\SocioPago::create([
                'id_empresa' => $idEmpresa,
                'socio_id' => $data['socio_id'],
                'monto' => $data['monto'],
                'banco_id' => $data['banco_id'],
                'fecha_aplicacion' => $data['fecha_aplicacion'],
                'user_id' => $userId
            ]);

            // Register bank movement (debit/cargo)
            app(\App\Services\BancosService::class)->registrarMovimiento([
                'cuenta_bancaria_id' => $data['banco_id'],
                'tipo' => 'cargo', // cargo = debit
                'monto' => $data['monto'],
                'concepto' => 'Pago a Socio: ' . $socio->nombre,
                'fecha_movimiento' => $data['fecha_aplicacion'],
                'origen' => 'sistema',
                'referencia' => 'Pago de utilidades #' . $pago->id,
                'referenciaable_type' => \App\Models\SocioPago::class,
                'referenciaable_id' => $pago->id
            ]);

            return $pago;
        });
    }
}
