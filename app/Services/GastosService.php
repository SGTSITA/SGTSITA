<?php

namespace App\Services;

use App\Models\Asignaciones;
use App\Models\Cotizaciones;
use App\Models\DocumCotizacion;
use App\Models\Gasto;
use App\Models\GastoPago;
use App\Models\GastosExtras;
use App\Models\GastosGenerales;
use App\Models\GastosOperadores;
use App\Models\CategoriasGastos;
use App\Models\Equipo;
use App\Models\Operador;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GastosService
{
    public function __construct(private BancosService $bancosService)
    {
    }


    public function getCatalogosIndex(int $idEmpresa): array
    {
        $fecha = now()->format('Y-m-d');

        return [
            'categorias' => CategoriasGastos::orderBy('categoria')->get(),

            'bancos' => $this->bancosService->getCuentasOption(
                $idEmpresa,
                $fecha,
                $fecha,
                true
            ),

            'equipos' => Equipo::where('id_empresa', $idEmpresa)
                ->orderBy('acceso')
                ->get(),

            'operadores' => Operador::where('id_empresa', $idEmpresa)
                ->orderBy('nombre')
                ->get(),

            'viajes' => Asignaciones::with('Contenedor')
                ->where('id_empresa', $idEmpresa)
                ->where('fecha_inicio', '>=', now()->subMonths(3))
                ->orderBy('fecha_inicio', 'desc')
                ->get(),
        ];
    }

    public function listar(array $filters)
    {
        $fechaInicio = !empty($filters['from'])
            ? Carbon::parse($filters['from'])->startOfDay()
            : now()->startOfMonth();

        $fechaFin = !empty($filters['to'])
            ? Carbon::parse($filters['to'])->endOfDay()
            : now()->endOfMonth();

        $query = Gasto::with([
            'categoria',
            'pagos.cuentaBancaria',
            'vinculos.vinculable'
        ])
        ->where('id_empresa', $filters['id_empresa']);

        if (empty($filters['cotizacion_id'])) {
            $query->whereBetween('fecha_gasto', [
                $fechaInicio->format('Y-m-d'),
                $fechaFin->format('Y-m-d')
            ]);
        }

        if (!empty($filters['cotizacion_id'])) {
            $query->whereHas('vinculos', function ($q) use ($filters) {
                $q->where(function ($sub) use ($filters) {
                    $sub->where('tipo_vinculo', 'cotizacion')
                        ->where('vinculable_type', Cotizaciones::class)
                        ->where('vinculable_id', $filters['cotizacion_id']);
                })->orWhere(function ($sub) use ($filters) {
                    $sub->where('tipo_vinculo', 'asignacion')
                        ->where('vinculable_type', Asignaciones::class)
                        ->whereIn('vinculable_id', function ($subq) use ($filters) {
                            $subq->select('id')->from('asignaciones')
                                ->whereIn('id_contenedor', function ($subq2) use ($filters) {
                                    $subq2->select('id')->from('docum_cotizacion')
                                        ->where('id_cotizacion', $filters['cotizacion_id']);
                                });
                        });
                })->orWhere(function ($sub) use ($filters) {
                    $sub->where('tipo_vinculo', 'contenedor')
                        ->where('vinculable_type', DocumCotizacion::class)
                        ->whereIn('vinculable_id', function ($subq) use ($filters) {
                            $subq->select('id')->from('docum_cotizacion')
                                ->where('id_cotizacion', $filters['cotizacion_id']);
                        });
                });
            });
        }

        if (!empty($filters['tipo_gasto']) && $filters['tipo_gasto'] !== 'todos') {
            if ($filters['tipo_gasto'] === 'cotizacion') {
                $query->where(function ($q) {
                    $q->where('tipo_gasto', 'cotizacion')
                      ->orWhere(function ($sub) {
                          $sub->where('tipo_gasto', 'viaje')
                              ->whereHas('imputaciones', function ($sub2) {
                                  $sub2->where('tipo_imputacion', 'cotizacion');
                              });
                      });
                });
            } else {
                $query->where('tipo_gasto', $filters['tipo_gasto']);
            }
        }

        if (!empty($filters['search'])) {

            $search = $filters['search'];

            $query->where(function ($q) use ($search) {

                $q->where('concepto', 'like', "%{$search}%")
                ->orWhere('folio', 'like', "%{$search}%")
                ->orWhere('descripcion', 'like', "%{$search}%");

            });
        }

        return $query
            ->orderBy('fecha_gasto')
            ->orderBy('id')
            ->get()
            ->map(fn (Gasto $gasto) => $this->transformarListado($gasto));
    }
    private function transformarListado(Gasto $gasto): array
    {
        $pagosActivos = $gasto->pagos
            ->where('estatus', '!=', 'cancelado');

        return [

            'id' => $gasto->id,
            'folio' => $gasto->folio,
            'concepto' => $gasto->concepto,
            'categoria' => $gasto->categoria?->categoria,
            'categoria_gasto_id' => $gasto->categoria_gasto_id,
            'gasto_concepto_id' => $gasto->gasto_concepto_id,

            'tipo_gasto' => $gasto->tipo_gasto,
            'metodo_imputacion' => $gasto->metodo_imputacion,

            'monto_total' => (float) $gasto->monto_total,
            'monto_pagado' => $gasto->monto_pagado,
            'saldo_pendiente' => $gasto->saldo_pendiente,

            'fecha_gasto' => optional($gasto->fecha_gasto)->format('Y-m-d'),
            'fecha_operacion' => optional($gasto->fecha_operacion)->format('Y-m-d'),

            'estatus' => $gasto->estatus,

            'origen_modulo' => $gasto->origen_modulo,
            'origen_legacy' => $gasto->origen_legacy,
            'origen_legacy_id' => $gasto->origen_legacy_id,

            'vinculos' => $this->transformarVinculos($gasto),

            'pagos' => $gasto->pagos
                ->map(function ($pago) {

                    return [
                        'id' => $pago->id,
                        'fecha_pago' => optional($pago->fecha_pago)->format('Y-m-d'),
                        'monto' => (float) $pago->monto,
                        'referencia' => $pago->referencia,
                        'estatus' => $pago->estatus,
                        'movimiento_bancario_id' => $pago->movimiento_bancario_id,
                        'cuenta_bancaria' => $pago->cuentaBancaria ? [
                            'id' => $pago->cuentaBancaria->id,
                            'nombre' => $pago->cuentaBancaria->nombre_banco,
                            'nombre_beneficiario' => $pago->cuentaBancaria->nombre_beneficiario,
                        ] : null,
                    ];

                })
                ->values()
                ->toArray(),

            'total_pagos' => $pagosActivos->count(),

            'tiene_un_pago' => $pagosActivos->count() === 1,

            'ultimo_pago_id' => $pagosActivos
                ->sortByDesc('id')
                ->first()?->id,

          'ultima_fecha_pago' => optional(
    $pagosActivos
        ->sortByDesc('fecha_pago')
        ->first()?->fecha_pago
)->format('Y-m-d'),
            'cotizacion_id' => $gasto->vinculos->where('tipo_vinculo', 'cotizacion')->first()?->vinculable_id,
        ];
    }

    private function transformarVinculos(Gasto $gasto): array
{
    return $gasto->vinculos
        ->filter(function ($vinculo) use ($gasto) {

            if (in_array($vinculo->tipo_vinculo, ['contenedor', 'cotizacion'])) {

                $hasAsignacion = $gasto->vinculos
                    ->contains('tipo_vinculo', 'asignacion');

                if ($hasAsignacion) {
                    return false;
                }
            }

            return true;
        })
        ->map(function ($vinculo) {

            $nombre = $vinculo->observaciones ?: '';

            if ($vinculo->vinculable) {

                if ($vinculo->vinculable instanceof Equipo) {

                    $nombre = 'Unidad: '
                        . ($vinculo->vinculable->id_equipo .' Placas:' . $vinculo->vinculable->placas
                        ?: $vinculo->vinculable->placas);

                } elseif ($vinculo->vinculable instanceof DocumCotizacion) {

                    $nombre = 'Contenedor: '
                        . $vinculo->vinculable->num_contenedor;

                } elseif ($vinculo->vinculable instanceof Operador) {

                    $nombre = 'Operador: '
                        . $vinculo->vinculable->nombre;

                } elseif ($vinculo->vinculable instanceof Asignaciones) {

                    $vinculo->vinculable->loadMissing('Contenedor');

                    $nombre = 'Viaje (Contenedor): '
                        . ($vinculo->vinculable->Contenedor?->num_contenedor
                        ?: 'ID ' . $vinculo->vinculable->id);

                } elseif ($vinculo->vinculable instanceof Cotizaciones) {

                    $nombre = 'Cotización: '
                        . ($vinculo->vinculable->referencia_full
                        ?: 'ID ' . $vinculo->vinculable->id);
                }
            }

            return [
                'tipo' => $vinculo->tipo_vinculo,
                'detalle' => $nombre,
                'observaciones' => $vinculo->observaciones,
            ];
        })
        ->values()
        ->toArray();
}



    public function registrar(array $data): Gasto
    {
        return DB::transaction(function () use ($data) {
            $gasto = $this->resolverGastoExistente($data);

            $payload = [
                'id_empresa' => $data['id_empresa'],
                'categoria_gasto_id' => $data['categoria_gasto_id'] ?? null,
                'gasto_concepto_id' => $data['gasto_concepto_id'] ?? null,
                'folio' => $data['folio'] ?? null,
                'concepto' => $data['concepto'],
                'descripcion' => $data['descripcion'] ?? null,
                'monto_total' => $this->normalizarMonto($data['monto_total'] ?? 0),
                'moneda' => $data['moneda'] ?? 'MXN',
                'fecha_gasto' => $this->normalizarFecha($data['fecha_gasto'] ?? now()),
                'fecha_operacion' => isset($data['fecha_operacion'])
                    ? $this->normalizarFecha($data['fecha_operacion'])
                    : null,
                'tipo_gasto' => $data['tipo_gasto'] ?? 'general',
                'metodo_imputacion' => $data['metodo_imputacion'] ?? 'directo',
                'estatus' => $data['estatus'] ?? 'pendiente_pago',
                'origen_modulo' => $data['origen_modulo'] ?? 'manual',
                'origen_legacy' => $data['origen_legacy'] ?? null,
                'origen_legacy_id' => $data['origen_legacy_id'] ?? null,
                'user_id' => $data['user_id'] ?? auth()->id(),
            ];

            if ($gasto) {
                $gasto->update($payload);
            } else {
                $gasto = Gasto::create($payload);
            }

            $this->reemplazarRelacion($gasto, 'partidas', $data['partidas'] ?? []);
            $this->reemplazarRelacion($gasto, 'vinculos', $data['vinculos'] ?? []);
            $this->reemplazarRelacion($gasto, 'imputaciones', $data['imputaciones'] ?? []);
            $this->reemplazarRelacion($gasto, 'programaciones', $data['programaciones'] ?? []);
            $this->sincronizarEstatusPago($gasto);

            return $gasto->fresh(['partidas', 'vinculos', 'imputaciones', 'programaciones', 'pagos']);
        });
    }

    public function registrarDesdeGastoExtra(GastosExtras $legacy): Gasto
    {
        $legacy->loadMissing('Cotizacion.DocCotizacion');

        $cotizacion = $legacy->Cotizacion;
        $contenedor = $cotizacion?->DocCotizacion;

        $gasto = $this->registrar([
            'id_empresa' => $cotizacion?->id_empresa ?? auth()->user()?->id_empresa,
            'concepto' => $legacy->descripcion ?: 'Gasto extra',
            'descripcion' => $legacy->descripcion,
            'monto_total' => $legacy->monto,
            'fecha_gasto' => $legacy->created_at ?? now(),
            'fecha_operacion' => $legacy->fecha_aplicacion,
            'tipo_gasto' => 'cotizacion',
            'metodo_imputacion' => 'directo',
            'estatus' => $this->mapearEstatusLegacy($legacy->estatus ?? 'pendiente'),
            'origen_modulo' => 'gasto_extra',
            'origen_legacy' => 'gastos_extras',
            'origen_legacy_id' => $legacy->id,
            'vinculos' => array_filter([
                $cotizacion ? [
                    'tipo_vinculo' => 'cotizacion',
                    'vinculable_type' => get_class($cotizacion),
                    'vinculable_id' => $cotizacion->id,
                    'observaciones' => 'Vinculo generado desde gastos_extras.',
                ] : null,
                $contenedor ? [
                    'tipo_vinculo' => 'contenedor',
                    'vinculable_type' => get_class($contenedor),
                    'vinculable_id' => $contenedor->id,
                    'observaciones' => $contenedor->num_contenedor,
                ] : null,
            ]),
            'imputaciones' => [
                [
                    'fecha_imputacion' => $legacy->fecha_aplicacion ?? $legacy->created_at ?? now(),
                    'tipo_imputacion' => 'cotizacion',
                    'imputable_type' => $cotizacion ? get_class($cotizacion) : null,
                    'imputable_id' => $cotizacion?->id,
                    'monto_imputado' => $legacy->monto,
                    'origen' => 'directo',
                ],
            ],
        ]);

        if (($legacy->estatus ?? null) === 'pagado' && $legacy->cuenta_bancaria_id) {
            $this->registrarPagoLegacySiFalta($gasto, $legacy->cuenta_bancaria_id, $legacy->fecha_aplicacion, $legacy->monto);
        }

        return $gasto;
    }

    public function registrarDesdeGastoOperador(GastosOperadores $legacy): Gasto
    {
        $legacy->loadMissing('Cotizacion.DocCotizacion', 'Asignaciones', 'Operador');

        $cotizacion = $legacy->Cotizacion;
        $contenedor = $cotizacion?->DocCotizacion;
        $asignacion = $legacy->Asignaciones;
        $operador = $legacy->Operador;

        $gasto = $this->registrar([
            'id_empresa' => $asignacion?->id_empresa ?? $cotizacion?->id_empresa ?? auth()->user()?->id_empresa,
            'concepto' => $legacy->tipo ?: 'Gasto operador',
            'descripcion' => $legacy->tipo,
            'monto_total' => $legacy->cantidad,
            'fecha_gasto' => $legacy->created_at ?? now(),
            'fecha_operacion' => $legacy->fecha_pago,
            'tipo_gasto' => 'operador',
            'metodo_imputacion' => 'directo',
            'estatus' => $this->mapearEstatusLegacy($legacy->estatus ?? 'pendiente'),
            'origen_modulo' => 'liquidacion_operador',
            'origen_legacy' => 'gastos_operadores',
            'origen_legacy_id' => $legacy->id,
            'vinculos' => array_filter([
                $cotizacion ? [
                    'tipo_vinculo' => 'cotizacion',
                    'vinculable_type' => get_class($cotizacion),
                    'vinculable_id' => $cotizacion->id,
                ] : null,
                $contenedor ? [
                    'tipo_vinculo' => 'contenedor',
                    'vinculable_type' => get_class($contenedor),
                    'vinculable_id' => $contenedor->id,
                    'observaciones' => $contenedor->num_contenedor,
                ] : null,
                $asignacion ? [
                    'tipo_vinculo' => 'asignacion',
                    'vinculable_type' => get_class($asignacion),
                    'vinculable_id' => $asignacion->id,
                ] : null,
                $operador ? [
                    'tipo_vinculo' => 'operador',
                    'vinculable_type' => get_class($operador),
                    'vinculable_id' => $operador->id,
                ] : null,
            ]),
            'imputaciones' => [
                [
                    'fecha_imputacion' => $legacy->fecha_pago ?? $legacy->created_at ?? now(),
                    'tipo_imputacion' => 'operador',
                    'imputable_type' => $operador ? get_class($operador) : null,
                    'imputable_id' => $operador?->id,
                    'monto_imputado' => $legacy->cantidad,
                    'origen' => 'directo',
                ],
            ],
        ]);

        if (($legacy->estatus ?? null) === 'Pagado' && $legacy->id_banco) {
            $this->registrarPagoLegacySiFalta($gasto, $legacy->id_banco, $legacy->fecha_pago, $legacy->cantidad);
        }

        return $gasto;
    }

    public function registrarDesdeGastoGeneral(GastosGenerales $legacy): Gasto
    {
        $gasto = $this->registrar([
            'id_empresa' => $legacy->id_empresa,
            'categoria_gasto_id' => $legacy->id_categoria,
            'concepto' => $legacy->motivo ?: 'Gasto general',
            'descripcion' => $legacy->motivo,
            'monto_total' => $legacy->monto1,
            'fecha_gasto' => $legacy->fecha,
            'fecha_operacion' => $legacy->fecha_operacion,
            'tipo_gasto' => $this->resolverTipoGeneral($legacy),
            'metodo_imputacion' => $legacy->diferir_gasto ? 'diferido' : 'directo',
            'estatus' => $legacy->pago_realizado ? 'pagado' : 'pendiente_pago',
            'origen_modulo' => 'gasto_general',
            'origen_legacy' => 'gastos_generales',
            'origen_legacy_id' => $legacy->id,
            'vinculos' => $this->vinculosDesdeAplicacionGeneral($legacy),
            'imputaciones' => [
                [
                    'periodo_id' => null,
                    'fecha_imputacion' => $legacy->fecha,
                    'tipo_imputacion' => $legacy->diferir_gasto ? 'periodo' : 'empresa',
                    'imputable_type' => null,
                    'imputable_id' => null,
                    'monto_imputado' => $legacy->monto1,
                    'origen' => $legacy->diferir_gasto ? 'diferido' : 'directo',
                ],
            ],
            'programaciones' => $this->programacionesDesdeGeneral($legacy),
        ]);

        $bancoPago = $legacy->id_banco1 ?: $legacy->id_banco2;

        if ((int) $legacy->pago_realizado === 1 && $bancoPago) {
            $this->registrarPagoLegacySiFalta($gasto, $bancoPago, $legacy->fecha_operacion ?? $legacy->fecha, $legacy->monto1);
        }

        return $gasto;
    }

    public function pagar(Gasto $gasto, array $data): GastoPago
    {
        return DB::transaction(function () use ($gasto, $data) {
            $monto = $this->normalizarMonto($data['monto'] ?? $gasto->saldo_pendiente);
            $fechaPago = $this->normalizarFecha($data['fecha_pago'] ?? now());
            $cuentaId = $data['cuenta_bancaria_id'];

            $validacion = $this->bancosService->validarsaldoparacargo(
                $gasto->id_empresa,
                $cuentaId,
                $fechaPago,
                $monto
            );

            if (!$validacion['saldodisponible']) {
                throw new \Exception($validacion['message']);
            }

            // Dynamically construct detailed json for bank movements details column if not provided
            $detallesMovimiento = null;
            if (isset($data['detalles_banco'])) {
                $detallesMovimiento = $data['detalles_banco'];
            } else {
                $gasto->loadMissing('vinculos');
                $vinculosInfo = [];
                foreach ($gasto->vinculos as $v) {
                    $vinculosInfo[] = [
                        'tipo' => $v->tipo_vinculo,
                        'referencia' => $v->observaciones ?: $v->vinculable_id
                    ];
                }
                $detallesMovimiento = [
                    [
                        'gasto_id' => $gasto->id,
                        'concepto' => $gasto->concepto,
                        'monto' => $monto,
                        'tipo_gasto' => $gasto->tipo_gasto,
                        'vinculos' => $vinculosInfo
                    ]
                ];
            }

            $movimiento = $this->bancosService->registrarMovimiento([
                'cuenta_bancaria_id' => $cuentaId,
                'tipo' => 'cargo',
                'monto' => $monto,
                'concepto' => $data['concepto_banco'] ?? 'Pago gasto: ' . $gasto->concepto,
                'fecha_movimiento' => $fechaPago,
                'referencia' => $data['referencia_banco'] ?? 'GASTO',
                'detalles' => $detallesMovimiento,
                'referenciaable_id' => $gasto->id,
                'referenciaable_type' => Gasto::class,
                'observaciones' => $data['observaciones_banco'] ?? null,
            ]);

            $pago = GastoPago::create([
                'gasto_id' => $gasto->id,
                'gasto_programacion_id' => $data['gasto_programacion_id'] ?? null,
                'cuenta_bancaria_id' => $cuentaId,
                'movimiento_bancario_id' => $movimiento?->id,
                'fecha_pago' => $fechaPago,
                'monto' => $monto,
                'metodo_pago' => $data['metodo_pago'] ?? 'Transferencia',
                'referencia' => $data['referencia'] ?? null,
                'comprobante' => $data['comprobante'] ?? null,
                'estatus' => 'aplicado',
                'user_id' => $data['user_id'] ?? auth()->id(),
            ]);

            $this->actualizarProgramacionPagada($pago);
            $this->sincronizarEstatusPago($gasto);

            return $pago;
        });
    }

    public function cancelarPago(GastoPago $pago, string $fechaCancelacion): void
    {
        DB::transaction(function () use ($pago, $fechaCancelacion) {
            if ($pago->estatus === 'cancelado') {
                return;
            }

            if ($pago->movimiento_bancario_id && $pago->cuenta_bancaria_id) {
                $this->bancosService->cancelarMovimiento(
                    $pago->cuenta_bancaria_id,
                    $pago->movimiento_bancario_id,
                    $fechaCancelacion
                );
            } elseif ($pago->cuenta_bancaria_id) {
                // Si es un pago migrado (sin movimiento_bancario_id), registramos la devolución (abono)
                // de manera directa en la cuenta bancaria usando los datos del pago.
                $this->bancosService->registrarMovimiento([
                    'cuenta_bancaria_id' => $pago->cuenta_bancaria_id,
                    'tipo'               => 'abono', // Devolución (contrario al cargo original)
                    'monto'              => $pago->monto,
                    'concepto'           => 'Devolución - ' . ($pago->gasto?->concepto ?? 'Pago cancelado'),
                    'fecha_movimiento'   => $fechaCancelacion,
                    'origen'             => 'sistema',
                    'referencia'         => 'cancelación' . ($pago->referencia ? ' - ' . $pago->referencia : ''),
                    'referenciaable_type' => Gasto::class,
                    'referenciaable_id'   => $pago->gasto_id,
                    'observaciones'      => 'Cancelación de pago histórico migrado. Pago ID: ' . $pago->id,
                ]);
            }

            $pago->update(['estatus' => 'cancelado']);
            $this->sincronizarEstatusPago($pago->gasto);
        });
    }

    public function cancelarDesdeLegacy(string $origenLegacy, int $origenLegacyId): void
    {
        DB::transaction(function () use ($origenLegacy, $origenLegacyId) {
            $gasto = Gasto::withTrashed()
                ->where('origen_legacy', $origenLegacy)
                ->where('origen_legacy_id', $origenLegacyId)
                ->first();

            if (!$gasto) {
                return;
            }

            $gasto->pagos()
                ->where('estatus', 'aplicado')
                ->update(['estatus' => 'cancelado']);

            $gasto->updateQuietly(['estatus' => 'cancelado']);
            $gasto->delete();
        });
    }

    private function resolverGastoExistente(array $data): ?Gasto
    {
        if (!empty($data['origen_legacy']) && !empty($data['origen_legacy_id'])) {
            $query = Gasto::withTrashed()
                ->where('origen_legacy', $data['origen_legacy'])
                ->where('origen_legacy_id', $data['origen_legacy_id']);

            if (str_starts_with($data['origen_legacy'], 'asignacion_planeacion') && !empty($data['concepto'])) {
                $query->where('concepto', $data['concepto']);
            }

            return $query->first();
        }

        return !empty($data['id']) ? Gasto::find($data['id']) : null;
    }

    private function reemplazarRelacion(Gasto $gasto, string $relacion, array $items): void
    {
        if (!array_key_exists($relacion, [
            'partidas' => true,
            'vinculos' => true,
            'imputaciones' => true,
            'programaciones' => true,
        ])) {
            return;
        }

        $gasto->{$relacion}()->delete();

        foreach ($items as $item) {
            $gasto->{$relacion}()->create($item);
        }
    }

    private function registrarPagoLegacySiFalta(Gasto $gasto, ?int $cuentaId, $fecha, $monto): void
    {
        if (!$cuentaId || $gasto->pagos()->where('estatus', 'aplicado')->exists()) {
            return;
        }

        GastoPago::create([
            'gasto_id' => $gasto->id,
            'cuenta_bancaria_id' => $cuentaId,
            'fecha_pago' => $this->normalizarFecha($fecha ?? now()),
            'monto' => $this->normalizarMonto($monto),
            'metodo_pago' => 'Transferencia',
            'referencia' => 'LEGACY',
            'estatus' => 'aplicado',
            'user_id' => auth()->id(),
        ]);

        $this->sincronizarEstatusPago($gasto);
    }

    private function sincronizarEstatusPago(Gasto $gasto): void
    {
        if ($gasto->estatus === 'cancelado') {
            return;
        }

        $pagado = (float) $gasto->pagos()->where('estatus', 'aplicado')->sum('monto');
        $total = (float) $gasto->monto_total;

        $estatus = match (true) {
            $pagado <= 0 => 'pendiente_pago',
            $pagado < $total => 'pagado_parcial',
            default => 'pagado',
        };

        $gasto->updateQuietly(['estatus' => $estatus]);
    }

    private function actualizarProgramacionPagada(GastoPago $pago): void
    {
        if (!$pago->gasto_programacion_id) {
            return;
        }

        $programacion = $pago->programacion;
        $montoPagado = $programacion->pagos?->sum('monto') ?? GastoPago::where('gasto_programacion_id', $programacion->id)
            ->where('estatus', 'aplicado')
            ->sum('monto');

        $programacion->update([
            'monto_pagado' => $montoPagado,
            'estatus' => $montoPagado >= (float) $programacion->monto_programado
                ? 'pagado'
                : 'parcial',
        ]);
    }

    private function mapearEstatusLegacy(?string $estatus): string
    {
        return match (strtolower((string) $estatus)) {
            'pagado' => 'pagado',
            'eliminado', 'cancelado' => 'cancelado',
            default => 'pendiente_pago',
        };
    }

    private function resolverTipoGeneral(GastosGenerales $legacy): string
    {
        $aplicacion = $this->decodificarAplicacion($legacy->aplicacion_gasto);

        return match ($aplicacion['aplicacion'] ?? null) {
            'equipos' => 'unidad',
            'viajes' => 'viaje',
            'periodo' => 'periodo',
            default => 'general',
        };
    }

    private function vinculosDesdeAplicacionGeneral(GastosGenerales $legacy): array
    {
        $aplicacion = $this->decodificarAplicacion($legacy->aplicacion_gasto);

        if (($aplicacion['aplicacion'] ?? null) === 'equipos') {
            return collect($aplicacion['elementos'] ?? [])
                ->map(fn ($item) => [
                    'tipo_vinculo' => 'unidad',
                    'vinculable_type' => \App\Models\Equipo::class,
                    'vinculable_id' => Arr::get($item, 'equipo'),
                    'observaciones' => 'Vinculo generado desde aplicacion_gasto.',
                ])
                ->filter(fn ($item) => $item['vinculable_id'])
                ->values()
                ->all();
        }

        if (($aplicacion['aplicacion'] ?? null) === 'viajes') {
            return collect($aplicacion['elementos'] ?? [])
                ->map(function ($item) {
                    $contenedor = DocumCotizacion::where('num_contenedor', Arr::get($item, 'num_contenedor'))->first();

                    return $contenedor ? [
                        'tipo_vinculo' => 'contenedor',
                        'vinculable_type' => get_class($contenedor),
                        'vinculable_id' => $contenedor->id,
                        'observaciones' => $contenedor->num_contenedor,
                    ] : null;
                })
                ->filter()
                ->values()
                ->all();
        }

        return [];
    }

    private function programacionesDesdeGeneral(GastosGenerales $legacy): array
    {
        if (!$legacy->diferir_gasto || !$legacy->fecha_diferido_inicial || !$legacy->fecha_diferido_final) {
            return [];
        }

        return [
            [
                'numero_periodo' => 1,
                'fecha_programada' => $legacy->fecha_diferido_inicial,
                'fecha_vencimiento' => $legacy->fecha_diferido_final,
                'monto_programado' => $legacy->monto1,
                'monto_pagado' => $legacy->pago_realizado ? $legacy->monto1 : 0,
                'estatus' => $legacy->pago_realizado ? 'pagado' : 'pendiente',
            ],
        ];
    }

    private function decodificarAplicacion($aplicacion): array
    {
        if (is_array($aplicacion)) {
            return $aplicacion;
        }

        if (!$aplicacion) {
            return [];
        }

        $decoded = json_decode($aplicacion, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizarMonto($monto): float
    {
        return round((float) str_replace(',', '', (string) $monto), 2);
    }

    private function normalizarFecha($fecha): string
    {
        return Carbon::parse($fecha)->format('Y-m-d');
    }


    public function obtenerHistorialPagos(Gasto $gasto): array
{
     return $gasto->pagos()
        ->with('cuentaBancaria')
        ->orderByDesc('fecha_pago')
        ->orderByDesc('id')
        ->get()
        ->map(function ($pago) {

            return [
                'id' => $pago->id,
                'fecha_pago' => optional($pago->fecha_pago)->format('Y-m-d'),
                'monto' => (float) $pago->monto,
                'referencia' => $pago->referencia,
                'metodo_pago' => $pago->metodo_pago,
                'estatus' => $pago->estatus,
                'cuenta' => $pago->cuentaBancaria?->cuenta,
                'movimiento_bancario_id' => $pago->movimiento_bancario_id,
            ];
        })
        ->toArray();
}
}
