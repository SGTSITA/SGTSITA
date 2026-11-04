<?php

namespace App\Services;

use App\Models\Asignaciones;
use App\Models\DineroContenedor;
use App\Models\CatBancoCuentasMovimientos;
use App\Models\Gasto;
use App\Models\Liquidaciones;
use App\Models\Poliza;
use App\Models\ViaticosOperador;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OperadorGastosContabilidadService
{
    public function __construct(
        private GastosService $gastosService,
        private PolizaService $polizaService
    ) {
    }

    public function sincronizarPlaneacion(Asignaciones $asignacion, bool $incluirAnticipos = true): void
    {
        DB::transaction(function () use ($asignacion, $incluirAnticipos) {
            $asignacion->loadMissing('Contenedor.cotizacion', 'Operador', 'Camion');
            $esPropio = $asignacion->tipo_contrato === 'Propio' || (empty($asignacion->tipo_contrato) && empty($asignacion->id_proveedor) && !empty($asignacion->id_operador));
            if (!$esPropio || !$asignacion->id_operador) {
                return;
            }

            $sueldo = round((float) $asignacion->sueldo_viaje, 2);
            if ($sueldo > 0) {
                $gastoSueldo = $this->gastosService->registrar($this->payloadVinculado($asignacion, [
                    'categoria_gasto_id' => $this->categoriaId('Sueldos de Operadores'),
                    'gasto_concepto_id' => $this->conceptoId('SUELDO_OPERADOR'),
                    'concepto' => 'Sueldo operador - ' . ($asignacion->Operador?->nombre ?? 'Operador'),
                    'descripcion' => 'Sueldo bruto devengado al planear el viaje. La liquidación separa pago bancario y compensaciones.',
                    'monto_total' => $sueldo,
                    'fecha_gasto' => $asignacion->fecha_inicio ?: now(),
                    'tipo_gasto' => 'operador',
                    'estatus' => 'pendiente_pago',
                    'origen_legacy' => 'asignacion_sueldo_operador',
                    'origen_legacy_id' => $asignacion->id,
                ]));

                $this->polizaService->contabilizarSueldoOperadorDevengado($gastoSueldo);
            }

            if (!$incluirAnticipos) {
                return;
            }

            $dineros = DineroContenedor::where('id_contenedor', $asignacion->id_contenedor)
                ->where('monto', '>', 0)
                ->where(function ($q) {
                    $q->where('es_principal', true)->orWhere('motivo', 'Dinero para viaje');
                })
                ->orderBy('id')
                ->get();
            if ($dineros->isEmpty() && (float) $asignacion->dinero_viaje > 0) {
                $dineros = collect([null]);
            }

            foreach ($dineros as $dinero) {
                $this->sincronizarAnticipo($asignacion, $dinero);
            }
        });
    }

    public function sincronizarAnticipo(Asignaciones $asignacion, ?DineroContenedor $dinero = null): ?Gasto
    {
        $asignacion->loadMissing('Operador', 'Contenedor.cotizacion');
        $anticipo = round((float) ($dinero?->monto ?? $asignacion->dinero_viaje), 2);
        if ($anticipo <= 0) {
            return null;
        }

        return DB::transaction(function () use ($asignacion, $dinero, $anticipo) {
            $gasto = $this->gastosService->registrar($this->payloadVinculado($asignacion, [
                'categoria_gasto_id' => $this->categoriaId('Anticipos a Operadores por Comprobar'),
                'gasto_concepto_id' => $this->conceptoId('ANTICIPO_VIAJE_OPERADOR'),
                'concepto' => 'Anticipo de viaje por comprobar - ' . ($asignacion->Operador?->nombre ?? 'Operador'),
                'descripcion' => 'Activo por comprobar. No se reconoce como gasto hasta aceptar los comprobantes.',
                'monto_total' => $anticipo,
                'fecha_gasto' => $dinero?->fecha_entrega_monto ?: $asignacion->fecha_inicio ?: now(),
                'tipo_gasto' => 'operador',
                'estatus' => 'pagado',
                'origen_legacy' => $dinero ? 'dinero_contenedor_anticipo' : 'asignacion_anticipo_operador',
                'origen_legacy_id' => $dinero?->id ?: $asignacion->id,
            ]));

            $referenciaPago = 'ANTICIPO-' . ($dinero?->id ?: $asignacion->id);
            $pago = $gasto->pagos()->firstOrNew(['referencia' => $referenciaPago]);
            $pago->fill([
                'cuenta_bancaria_id' => $dinero?->id_banco ?: $asignacion->id_banco1_dinero_viaje,
                'movimiento_bancario_id' => $dinero?->id_movimiento_bancario,
                'fecha_pago' => $dinero?->fecha_entrega_monto ?: $asignacion->fecha_inicio ?: now(),
                'monto' => $anticipo,
                'metodo_pago' => 'Anticipo de viaje',
                'estatus' => 'aplicado',
                'user_id' => auth()->id(),
            ]);
            $pago->save();
            $gasto->updateQuietly(['estatus' => 'pagado']);
            return $gasto;
        });
    }

    public function sincronizarComprobacion(ViaticosOperador $viatico, ?Gasto $gasto = null): ?Gasto
    {
        return DB::transaction(function () use ($viatico, $gasto) {
            $viatico->loadMissing('Cotizacion.DocCotizacion');
            $contenedor = $viatico->Cotizacion?->DocCotizacion;
            if (!$contenedor) {
                return null;
            }

            $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();
            if (!$asignacion) {
                return null;
            }
            $asignacion->loadMissing('Operador');

            $gasto = $gasto ?: Gasto::withTrashed()
                ->where('origen_legacy', 'viaticos_operadores')
                ->where('origen_legacy_id', $viatico->id)
                ->first();
            $monto = round((float) ($gasto?->monto_total ?? $viatico->monto), 2);
            if ($monto <= 0) {
                return $gasto;
            }

            $gasto = $this->gastosService->registrar($this->payloadVinculado($asignacion, [
                'id' => $gasto?->id,
                'categoria_gasto_id' => $this->categoriaId('Gastos de Viaje de Operadores'),
                'gasto_concepto_id' => $this->conceptoId('GASTO_VIAJE_COMPROBADO'),
                'concepto' => $viatico->descripcion_gasto ?: 'Gasto de viaje comprobado',
                'descripcion' => 'Comprobación aceptada de anticipo de viaje.',
                'monto_total' => $monto,
                'fecha_gasto' => $viatico->fecha_comprobante ?: $viatico->created_at ?: now(),
                'tipo_gasto' => 'operador',
                'estatus' => 'pagado',
                'origen_legacy' => 'viaticos_operadores',
                'origen_legacy_id' => $viatico->id,
            ]));

            $this->polizaService->contabilizarGastoComprobadoOperador($gasto);
            $referenciaPago = 'COMPROBACION-' . $viatico->id;
            $pagoComprobacion = $gasto->pagos()->firstOrNew(['referencia' => $referenciaPago]);
            $pagoComprobacion->fill([
                'cuenta_bancaria_id' => null,
                'movimiento_bancario_id' => null,
                'fecha_pago' => $viatico->fecha_comprobante ?: $viatico->created_at ?: now(),
                'monto' => $monto,
                'metodo_pago' => 'Aplicación de anticipo comprobado',
                'estatus' => 'aplicado',
                'user_id' => auth()->id(),
            ]);
            $pagoComprobacion->save();
            $gasto->updateQuietly(['estatus' => 'pagado']);
            return $gasto;
        });
    }

    public function sincronizarExcedenteComprobacion(ViaticosOperador $viatico, ?Gasto $gasto = null): ?Gasto
    {
        return DB::transaction(function () use ($viatico, $gasto) {
            $viatico->loadMissing('Cotizacion.DocCotizacion');
            $contenedor = $viatico->Cotizacion?->DocCotizacion;
            $asignacion = $contenedor ? Asignaciones::where('id_contenedor', $contenedor->id)->first() : null;
            if (!$asignacion) {
                return null;
            }

            $gasto = $gasto ?: Gasto::withTrashed()
                ->where('origen_legacy', 'viaticos_operadores_excedente')
                ->where('origen_legacy_id', $viatico->id)
                ->first();
            if (!$gasto || (float) $gasto->monto_total <= 0) {
                return $gasto;
            }

            $gasto = $this->gastosService->registrar($this->payloadVinculado($asignacion, [
                'id' => $gasto->id,
                'categoria_gasto_id' => $this->categoriaId('Gastos de Viaje de Operadores'),
                'gasto_concepto_id' => $this->conceptoId('GASTO_VIAJE_COMPROBADO'),
                'concepto' => $gasto->concepto ?: 'Gasto excedente comprobado de viaje',
                'descripcion' => 'Comprobación aceptada que excede el anticipo; pendiente de reembolso al operador.',
                'monto_total' => $gasto->monto_total,
                'fecha_gasto' => $gasto->fecha_gasto ?: $viatico->fecha_comprobante ?: now(),
                'tipo_gasto' => 'operador',
                'estatus' => $gasto->estatus ?: 'pendiente_pago',
                'origen_legacy' => 'viaticos_operadores_excedente',
                'origen_legacy_id' => $viatico->id,
            ]));
            $this->polizaService->contabilizarGastoExcedenteOperador($gasto);
            return $gasto;
        });
    }

    public function sincronizarLiquidacion(Liquidaciones $liquidacion, ?int $movimientoBancarioId = null): void
    {
        DB::transaction(function () use ($liquidacion, $movimientoBancarioId) {
            $liquidacion->loadMissing('viajes.Contenedores');
            foreach ($liquidacion->viajes as $detalle) {
                $asignacion = Asignaciones::where('id_contenedor', $detalle->id_contenedor)->first();
                if (!$asignacion) {
                    continue;
                }
                if (!$gastoSueldo = Gasto::where('origen_legacy', 'asignacion_sueldo_operador')->where('origen_legacy_id', $asignacion->id)->first()) {
                    $this->sincronizarPlaneacion($asignacion);
                    $gastoSueldo = Gasto::where('origen_legacy', 'asignacion_sueldo_operador')->where('origen_legacy_id', $asignacion->id)->first();
                }
                if (!$gastoSueldo) {
                    continue;
                }

                $saldoNoComprobado = max(0, round((float) $detalle->dinero_viaje - (float) $detalle->dinero_justificado, 2));
                $asignacion->refresh();
                $montoLiquidado = round((float) $detalle->total_pagado + ($asignacion->estatus_pagado === 'Pagado' ? $saldoNoComprobado : 0), 2);
                if ($montoLiquidado <= 0) {
                    continue;
                }

                $referencia = 'LIQ-' . $liquidacion->id . '-ASIG-' . $asignacion->id;
                if ($gastoSueldo->pagos()->where('referencia', $referencia)->exists()) {
                    continue;
                }

                $gastoSueldo->pagos()->create([
                    'cuenta_bancaria_id' => null,
                    'movimiento_bancario_id' => $movimientoBancarioId,
                    'fecha_pago' => $liquidacion->fecha,
                    'monto' => $montoLiquidado,
                    'metodo_pago' => 'Liquidación operador (banco y compensación de anticipo)',
                    'referencia' => $referencia . ' / banco=' . number_format((float) $detalle->total_pagado, 2, '.', '') . ' / no comprobado=' . number_format($asignacion->estatus_pagado === 'Pagado' ? $saldoNoComprobado : 0, 2, '.', ''),
                    'estatus' => 'aplicado',
                    'user_id' => auth()->id(),
                ]);

                $pagado = (float) $gastoSueldo->pagos()->where('estatus', 'aplicado')->sum('monto');
                $gastoSueldo->update(['estatus' => $pagado + 0.001 >= (float) $gastoSueldo->monto_total ? 'pagado' : 'pagado_parcial']);
            }
        });
    }

    public function sincronizarHistorico(int $idEmpresa, ?string $desde, ?string $hasta, bool $dryRun): array
    {
        $asignaciones = Asignaciones::with(['Contenedor.cotizacion', 'Operador', 'Camion'])
            ->where('id_empresa', $idEmpresa)
            ->where(function ($q) {
                $q->where('tipo_contrato', 'Propio')
                  ->orWhere(function ($sq) {
                      $sq->where(function ($ssq) {
                          $ssq->whereNull('tipo_contrato')->orWhere('tipo_contrato', '');
                      })->whereNotNull('id_operador')->whereNull('id_proveedor');
                  });
            })
            ->where('sueldo_viaje', '>', 0)
            ->when($desde, fn ($q) => $q->whereDate('fecha_inicio', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('fecha_inicio', '<=', $hasta))
            ->orderBy('id')
            ->get();

        $summary = ['asignaciones' => 0, 'sueldos' => 0, 'monto_sueldos' => 0.0, 'anticipos' => 0, 'monto_anticipos' => 0.0, 'comprobaciones' => 0, 'monto_comprobaciones' => 0.0, 'excedentes' => 0, 'monto_excedentes' => 0.0, 'polizas' => 0, 'errores' => 0];
        foreach ($asignaciones as $asignacion) {
            $summary['asignaciones']++;
            $existeSueldo = Gasto::withTrashed()->where('origen_legacy', 'asignacion_sueldo_operador')->where('origen_legacy_id', $asignacion->id)->exists();
            $gastoSueldo = Gasto::withTrashed()->where('origen_legacy', 'asignacion_sueldo_operador')->where('origen_legacy_id', $asignacion->id)->first();
            $polizaSueldoExiste = $gastoSueldo && Poliza::where('origen_tipo', 'sueldo_operador_devengado')->where('origen_id', $gastoSueldo->id)->exists();
            if (!$existeSueldo || !$polizaSueldoExiste) {
                $summary['sueldos']++;
                $summary['monto_sueldos'] += (float) $asignacion->sueldo_viaje;
            }
            $dinero = DineroContenedor::where('id_contenedor', $asignacion->id_contenedor)->where('monto', '>', 0)
                ->when($desde, fn ($q) => $q->whereDate('fecha_entrega_monto', '>=', $desde))
                ->when($hasta, fn ($q) => $q->whereDate('fecha_entrega_monto', '<=', $hasta))
                ->get();
            $hayRegistrosDinero = DineroContenedor::where('id_contenedor', $asignacion->id_contenedor)->where('monto', '>', 0)->exists();
            $tieneAnticipoAsignacion = !$hayRegistrosDinero && (float) $asignacion->dinero_viaje > 0;
            $summary['anticipos'] += $dinero->count() + ($tieneAnticipoAsignacion ? 1 : 0);
            $summary['monto_anticipos'] += (float) $dinero->sum('monto') + ($tieneAnticipoAsignacion ? (float) $asignacion->dinero_viaje : 0);
            $summary['polizas'] += !$polizaSueldoExiste ? 1 : 0;

            if (!$dryRun) {
                try {
                    $this->sincronizarPlaneacion($asignacion, false);
                    if (!$hayRegistrosDinero && (float) $asignacion->dinero_viaje > 0) {
                        $this->sincronizarAnticipo($asignacion);
                    }
                    foreach ($dinero as $registroDinero) {
                        $this->sincronizarAnticipo($asignacion, $registroDinero);
                        if (!$registroDinero->id_movimiento_bancario) {
                            continue;
                        }
                        $movimiento = CatBancoCuentasMovimientos::find($registroDinero->id_movimiento_bancario);
                        if ($movimiento) {
                            $polizaAntes = $movimiento->poliza_id;
                            $this->polizaService->contabilizarMovimientoBancario($movimiento);
                            $summary['polizas'] += empty($polizaAntes) ? 1 : 0;
                        }
                    }
                } catch (\Throwable $e) {
                    $summary['errores']++;
                    Log::warning('Backfill de contabilidad de operador falló para asignación ' . $asignacion->id . ': ' . $e->getMessage());
                }
            }
        }

        $viaticos = ViaticosOperador::whereHas('Cotizacion', fn ($q) => $q->where('id_empresa', $idEmpresa))
            ->when($desde, fn ($q) => $q->whereDate('fecha_comprobante', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('fecha_comprobante', '<=', $hasta))
            ->orderBy('id')
            ->get();
        foreach ($viaticos as $viatico) {
            $existe = Gasto::withTrashed()->where('origen_legacy', 'viaticos_operadores')->where('origen_legacy_id', $viatico->id)->exists();
            $gastoComprobacion = Gasto::withTrashed()->where('origen_legacy', 'viaticos_operadores')->where('origen_legacy_id', $viatico->id)->first();
            $polizaComprobacionExiste = $gastoComprobacion && Poliza::where('origen_tipo', 'gasto_comprobacion_operador')->where('origen_id', $gastoComprobacion->id)->exists();
            if (!$existe || !$polizaComprobacionExiste) {
                $summary['comprobaciones']++;
                $summary['monto_comprobaciones'] += (float) ($gastoComprobacion?->monto_total ?? $viatico->monto);
            }
            $summary['polizas'] += !$polizaComprobacionExiste ? 1 : 0;
            $gastoExcedente = Gasto::withTrashed()->where('origen_legacy', 'viaticos_operadores_excedente')->where('origen_legacy_id', $viatico->id)->first();
            if ($gastoExcedente && (float) $gastoExcedente->monto_total > 0) {
                $polizaExcedenteExiste = Poliza::where('origen_tipo', 'gasto_comprobacion_excedente_operador')->where('origen_id', $gastoExcedente->id)->exists();
                if (!$polizaExcedenteExiste) {
                    $summary['excedentes']++;
                    $summary['monto_excedentes'] += (float) $gastoExcedente->monto_total;
                }
                $summary['polizas'] += $polizaExcedenteExiste ? 0 : 1;
            }
            if (!$dryRun) {
                try {
                    $this->sincronizarComprobacion($viatico);
                    if ($gastoExcedente) {
                        $this->sincronizarExcedenteComprobacion($viatico, $gastoExcedente);
                    }
                } catch (\Throwable $e) {
                    $summary['errores']++;
                    Log::warning('Backfill de comprobación de operador falló para viático ' . $viatico->id . ': ' . $e->getMessage());
                }
            }
        }

        $movimientosLiquidacion = CatBancoCuentasMovimientos::with('cuentaBancaria')
            ->where('referenciaable_type', Liquidaciones::class)
            ->where('tipo', 'cargo')
            ->whereHas('cuentaBancaria', fn ($q) => $q->where('id_empresa', $idEmpresa))
            ->when($desde, fn ($q) => $q->whereDate('fecha_movimiento', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('fecha_movimiento', '<=', $hasta))
            ->get();
        foreach ($movimientosLiquidacion as $movimiento) {
            $polizaExiste = Poliza::where('origen_tipo', 'banco_movimiento')->where('origen_id', $movimiento->id)
                ->whereHas('movimientos', fn ($q) => $q->where('referencia', 'PAGO_SUELDO_OPERADOR'))
                ->exists();
            $summary['polizas'] += $polizaExiste ? 0 : 1;
            if (!$dryRun) {
                try {
                    $polizaAntes = $movimiento->poliza_id;
                    $this->polizaService->contabilizarMovimientoBancario($movimiento);
                    $liquidacion = Liquidaciones::find($movimiento->referenciaable_id);
                    if ($liquidacion) {
                        $this->sincronizarLiquidacion($liquidacion, $movimiento->id);
                    }
                } catch (\Throwable $e) {
                    $summary['errores']++;
                    Log::warning('Backfill de liquidación de operador falló para movimiento ' . $movimiento->id . ': ' . $e->getMessage());
                }
            }
        }

        return $summary;
    }

    private function payloadVinculado(Asignaciones $asignacion, array $payload): array
    {
        $asignacion->loadMissing('Contenedor.cotizacion', 'Operador');
        $contenedor = $asignacion->Contenedor;
        $cotizacion = $contenedor?->cotizacion;
        $fecha = $payload['fecha_gasto'] ?? $asignacion->fecha_inicio ?? now();
        $monto = round((float) ($payload['monto_total'] ?? 0), 2);

        return array_merge([
            'id_empresa' => $asignacion->id_empresa,
            'tipo_gasto' => 'operador',
            'metodo_imputacion' => 'directo',
            'origen_modulo' => 'liquidacion_operador',
            'estatus' => 'pendiente_pago',
            'user_id' => auth()->id(),
            'vinculos' => array_values(array_filter([
                $cotizacion ? ['tipo_vinculo' => 'cotizacion', 'vinculable_type' => get_class($cotizacion), 'vinculable_id' => $cotizacion->id] : null,
                $contenedor ? ['tipo_vinculo' => 'contenedor', 'vinculable_type' => get_class($contenedor), 'vinculable_id' => $contenedor->id, 'observaciones' => $contenedor->num_contenedor] : null,
                ['tipo_vinculo' => 'asignacion', 'vinculable_type' => get_class($asignacion), 'vinculable_id' => $asignacion->id],
                $asignacion->id_operador ? ['tipo_vinculo' => 'operador', 'vinculable_type' => \App\Models\Operador::class, 'vinculable_id' => $asignacion->id_operador] : null,
            ])),
            'imputaciones' => [[
                'fecha_imputacion' => $fecha,
                'tipo_imputacion' => 'viaje',
                'imputable_type' => Asignaciones::class,
                'imputable_id' => $asignacion->id,
                'monto_imputado' => $monto,
                'origen' => 'directo',
            ]],
        ], $payload);
    }

    private function categoriaId(string $categoria): ?int
    {
        return DB::table('categorias_gastos')->where('categoria', $categoria)->value('id');
    }

    private function conceptoId(string $clave): ?int
    {
        return DB::table('gasto_conceptos')->where('clave', $clave)->value('id');
    }
}
