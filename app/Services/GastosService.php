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
use App\Models\CatBancoCuentasMovimientos;
use App\Models\Bancos;
use App\Models\BancoDinero;
use App\Exceptions\SaldoInsuficienteException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GastosService
{
    public function __construct(
        private BancosService $bancosService,
        private EquipoService $equipoService
    ) {
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

            'equipos' => $this->equipoService->getTractocamiones($idEmpresa),

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
            'equipo',
            'imputaciones',
            'programaciones',
            'pagos.cuentaBancaria',
            'vinculos.vinculable'
        ])
        ->where('id_empresa', $filters['id_empresa']);

        if (!empty($filters['categoria_id'])) {
            $query->where('categoria_gasto_id', $filters['categoria_id']);
        }
        if (!empty($filters['subcategoria_id'])) {
            $query->where('gasto_concepto_id', $filters['subcategoria_id']);
        }

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

            if (empty($filters['tipo_gasto']) || !in_array($filters['tipo_gasto'], ['sueldo', 'sueldos'])) {
                $query->where(function ($sq) {
                    $sq->whereNull('origen_legacy')->orWhereNotIn('origen_legacy', [
                        'asignacion_sueldo_operador',
                        'asignacion_anticipo_operador',
                        'dinero_contenedor_anticipo',
                    ]);
                })->whereDoesntHave('categoria', function ($cq) {
                    $cq->where('categoria', 'Sueldos de Operadores');
                });
            }
        }

        if (!empty($filters['tipo_gasto']) && $filters['tipo_gasto'] !== 'todos') {
            if ($filters['tipo_gasto'] === 'sueldo' || $filters['tipo_gasto'] === 'sueldos') {
                $query->where(function ($q) {
                    $q->where('origen_legacy', 'asignacion_sueldo_operador')
                      ->orWhereHas('categoria', fn ($cq) => $cq->where('categoria', 'Sueldos de Operadores'));
                });
            } elseif ($filters['tipo_gasto'] === 'cotizacion') {
                $query->where(function ($q) {
                    $q->where('tipo_gasto', 'cotizacion')
                      ->orWhere(function ($sub) {
                          $sub->where('tipo_gasto', 'viaje')
                              ->whereHas('imputaciones', function ($sub2) {
                                  $sub2->where('tipo_imputacion', 'cotizacion');
                              });
                      });
                });
            } elseif ($filters['tipo_gasto'] === 'operador') {
                $query->whereIn('tipo_gasto', ['operador', 'viaje'])
                      ->where('origen_legacy', '!=', 'asignacion_sueldo_operador')
                      ->whereDoesntHave('categoria', fn ($cq) => $cq->where('categoria', 'Sueldos de Operadores'))
                      ->where(function ($sq) {
                          $sq->whereNull('origen_legacy')->orWhereNotIn('origen_legacy', [
                              'asignacion_anticipo_operador',
                              'dinero_contenedor_anticipo',
                          ]);
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

        if (!empty($filters['id_equipo'])) {
            $idEquipo = (int) $filters['id_equipo'];
            $query->where(function ($q) use ($idEquipo) {
                $q->where('id_equipo', $idEquipo)
                  ->orWhereHas('vinculos', function ($sub) use ($idEquipo) {
                      $sub->where('tipo_vinculo', 'unidad')
                          ->where('vinculable_type', Equipo::class)
                          ->where('vinculable_id', $idEquipo);
                  })
                  ->orWhereHas('imputaciones', function ($sub) use ($idEquipo) {
                      $sub->where('imputable_type', Equipo::class)
                          ->where('imputable_id', $idEquipo);
                  });
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

        $unidadesIds = $gasto->vinculos
            ->where('tipo_vinculo', 'unidad')
            ->pluck('vinculable_id')
            ->values()
            ->toArray();

        if (empty($unidadesIds) && $gasto->id_equipo) {
            $unidadesIds = [$gasto->id_equipo];
        }

        $viajesIds = $gasto->vinculos
            ->whereIn('tipo_vinculo', ['asignacion', 'contenedor'])
            ->pluck('vinculable_id')
            ->values()
            ->toArray();

        $primeraProg = $gasto->programaciones ? $gasto->programaciones->sortBy('fecha_programada')->first() : null;
        $ultimaProg = $gasto->programaciones ? $gasto->programaciones->sortByDesc('fecha_vencimiento')->first() : null;
        $impacto = $gasto->imputaciones ? ($gasto->imputaciones->first()?->tipo_imputacion ?? 'periodo') : 'periodo';

        return [

            'id' => $gasto->id,
            'id_equipo' => $gasto->id_equipo,
            'equipo' => $gasto->equipo ? [
                'id' => $gasto->equipo->id,
                'id_equipo' => $gasto->equipo->id_equipo,
                'placas' => $gasto->equipo->placas,
                'marca' => $gasto->equipo->marca,
            ] : null,
            'unidades_ids' => $unidadesIds,
            'viajes_ids' => $viajesIds,
            'impacto' => $impacto,
            'txtDiferirFechaInicia' => optional($primeraProg?->fecha_programada)->format('Y-m-d'),
            'txtDiferirFechaTermina' => optional($ultimaProg?->fecha_vencimiento)->format('Y-m-d'),
            'numPeriodos' => $gasto->programaciones ? $gasto->programaciones->count() : 0,

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
                            'cuenta_bancaria' => $pago->cuentaBancaria->cuenta_bancaria,
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
                'vinculable_id' => $vinculo->vinculable_id,
                'vinculable_type' => $vinculo->vinculable_type,
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

            $idEquipo = $data['id_equipo'] ?? null;
            if (!$idEquipo && !empty($data['vinculos'])) {
                foreach ($data['vinculos'] as $v) {
                    if (($v['tipo_vinculo'] ?? null) === 'unidad') {
                        $idEquipo = $v['vinculable_id'] ?? null;
                        break;
                    }
                    if (($v['tipo_vinculo'] ?? null) === 'asignacion') {
                        $asig = Asignaciones::find($v['vinculable_id'] ?? null);
                        if ($asig?->id_camion) {
                            $idEquipo = $asig->id_camion;
                            break;
                        }
                    }
                }
            }

            $payload = [
                'id_empresa' => $data['id_empresa'],
                'id_equipo' => $idEquipo,
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
                if ($gasto->trashed()) {
                    $gasto->restore();
                }
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

    public function registrarDesdeGastoOperador(GastosOperadores $legacy, ?Gasto $gastoExistente = null): Gasto
    {
        $legacy->loadMissing('Cotizacion.DocCotizacion', 'Asignaciones', 'Operador');

        $cotizacion = $legacy->Cotizacion;
        $contenedor = $cotizacion?->DocCotizacion;
        $asignacion = $legacy->Asignaciones;
        $operador = $legacy->Operador;

        $categoriaId = null;
        $conceptoId = null;
        $concepto = $legacy->tipo ?: 'Gasto operador';
        $tipoClean = strtolower(trim($legacy->tipo));

        if ($tipoClean === 'diesel') {
            $categoriaId = 1;
            $conceptoId = 24;
            $concepto = 'GDI02 - Diesel';
        } elseif (in_array($tipoClean, ['urea', 'gu001 - urea', 'gurea'])) {
            $categoriaId = 1;
            $con = DB::table('gasto_conceptos')->where('clave', 'GUREA')->orWhere('clave', 'OTR_UR')->orWhere('nombre', 'like', '%Urea%')->first();
            $conceptoId = $con ? $con->id : 42;
            $concepto = 'GU001 - Urea';
        }

        $idEmpresa = $asignacion?->id_empresa ?: ($cotizacion?->id_empresa ?: (auth()->user()?->id_empresa ?: 2));

        $origenLegacy = 'gastos_operadores';
        $origenLegacyId = $legacy->id;

        if ($asignacion && $tipoClean === 'diesel') {
            $origenLegacy = 'asignacion_planeacionGDI02 - Diesel';
            $origenLegacyId = $asignacion->id;
        } elseif ($asignacion && in_array($tipoClean, ['urea', 'gu001 - urea', 'gurea'])) {
            $origenLegacy = 'asignacion_planeacionGU001 - Urea';
            $origenLegacyId = $asignacion->id;
        }

        $gasto = $this->registrar([
            'id' => $gastoExistente?->id,
            'id_empresa' => $idEmpresa,
            'categoria_gasto_id' => $categoriaId,
            'gasto_concepto_id' => $conceptoId,
            'concepto' => $concepto,
            'descripcion' => $legacy->tipo,
            'monto_total' => $legacy->cantidad,
            'fecha_gasto' => $legacy->created_at ?? now(),
            'fecha_operacion' => $legacy->fecha_pago,
            'tipo_gasto' => 'viaje',
            'metodo_imputacion' => 'directo',
            'estatus' => $this->mapearEstatusLegacy($legacy->estatus ?? 'pendiente'),
            'origen_modulo' => 'liquidacion_operador',
            'origen_legacy' => $origenLegacy,
            'origen_legacy_id' => $origenLegacyId,
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
            $this->validarPagoManualPermitido($gasto);
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
            $gasto->loadMissing(['vinculos', 'equipo']);
            $equipoId = $gasto->id_equipo;
            $equipoNombre = null;
            if ($gasto->equipo) {
                $equipoNombre = $gasto->equipo->id_equipo ?: $gasto->equipo->placas;
            } elseif ($gasto->id_equipo) {
                $eq = Equipo::find($gasto->id_equipo);
                $equipoNombre = $eq ? ($eq->id_equipo ?: $eq->placas) : null;
            } else {
                $vUnidad = $gasto->vinculos->firstWhere('tipo_vinculo', 'unidad');
                if ($vUnidad) {
                    $equipoId = $vUnidad->vinculable_id;
                    $eq = Equipo::find($equipoId);
                    $equipoNombre = $eq ? ($eq->id_equipo ?: $eq->placas) : null;
                }
            }

            $detallesMovimiento = null;
            if (isset($data['detalles_banco'])) {
                $detallesMovimiento = $data['detalles_banco'];
            } else {
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
                        'id_equipo' => $equipoId,
                        'unidad' => $equipoNombre,
                        'vinculos' => $vinculosInfo
                    ]
                ];
            }

            $conceptoFinal = $data['concepto_banco'] ?? BancosService::generarConcepto('gasto', $gasto->concepto, null, null, $equipoNombre);

            $movimiento = $this->bancosService->registrarMovimiento([
                'cuenta_bancaria_id' => $cuentaId,
                'tipo' => 'cargo',
                'monto' => $monto,
                'concepto' => $conceptoFinal,
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

            // Generar Póliza Contable Automática bajo la Guía Contabilizadora
            try {
                $polizaService = app(\App\Services\PolizaService::class);
                $eventoGasto = ($gasto->metodo_imputacion === 'diferido' || ($gasto->programaciones && $gasto->programaciones->count() > 1))
                    ? 'GASTO_ANTICIPADO'
                    : 'GASTO_OPERATIVO';
                $subtipoGasto = ($eventoGasto === 'GASTO_ANTICIPADO') ? 'diferido' : 'directo';

                $asientoData = $polizaService->resolverAsientoPorEvento($eventoGasto, $subtipoGasto, $gasto->id_empresa, [
                    'id_equipo'          => $equipoId,
                    'monto'              => $monto,
                    'fecha'              => $fechaPago,
                    'concepto'           => 'Pago gasto: ' . $gasto->concepto . ($equipoNombre ? " (Unidad {$equipoNombre})" : ''),
                    'categoria_gasto_id' => $gasto->categoria_gasto_id,
                    'cuenta_bancaria_id' => $cuentaId,
                    'origen_tipo'        => 'gasto',
                    'origen_id'          => $gasto->id,
                ]);

                if (!empty($asientoData)) {
                    $poliza = $polizaService->crearPoliza($asientoData);
                    if ($movimiento && empty($movimiento->poliza_id)) {
                        $movimiento->update(['poliza_id' => $poliza->id]);
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("No se pudo generar póliza contable para pago de gasto {$gasto->id}: " . $e->getMessage());
            }

            $this->actualizarProgramacionPagada($pago);
            $this->sincronizarEstatusPago($gasto);

            return $pago;
        });
    }

    public function pagarConMovimientoExistente(Gasto $gasto, array $data): GastoPago
    {
        $this->validarPagoManualPermitido($gasto);
        $monto = $this->normalizarMonto($data['monto'] ?? $gasto->saldo_pendiente);
        $fechaPago = $this->normalizarFecha($data['fecha_pago'] ?? now());

        $pago = GastoPago::create([
            'gasto_id' => $gasto->id,
            'gasto_programacion_id' => $data['gasto_programacion_id'] ?? null,
            'cuenta_bancaria_id' => $data['cuenta_bancaria_id'],
            'movimiento_bancario_id' => $data['movimiento_bancario_id'],
            'fecha_pago' => $fechaPago,
            'monto' => $monto,
            'metodo_pago' => $data['metodo_pago'] ?? 'Transferencia',
            'referencia' => $data['referencia'] ?? null,
            'comprobante' => $data['comprobante'] ?? null,
            'estatus' => 'aplicado',
            'user_id' => $data['user_id'] ?? auth()->id(),
        ]);

        // Generar Póliza Contable Automática si el movimiento no tiene póliza
        try {
            $polizaService = app(\App\Services\PolizaService::class);
            $gasto->loadMissing('equipo');
            $eqId = $gasto->id_equipo ?: $gasto->equipo?->id;
            $asientoData = $polizaService->resolverAsientoPorEvento('GASTO_OPERATIVO', 'directo', $gasto->id_empresa, [
                'id_equipo'          => $eqId,
                'monto'              => $monto,
                'fecha'              => $fechaPago,
                'concepto'           => 'Pago gasto: ' . $gasto->concepto,
                'categoria_gasto_id' => $gasto->categoria_gasto_id,
                'cuenta_bancaria_id' => $data['cuenta_bancaria_id'],
                'origen_tipo'        => 'gasto',
                'origen_id'          => $gasto->id,
            ]);

            if (!empty($asientoData)) {
                $poliza = $polizaService->crearPoliza($asientoData);
                if (!empty($data['movimiento_bancario_id'])) {
                    CatBancoCuentasMovimientos::where('id', $data['movimiento_bancario_id'])
                        ->whereNull('poliza_id')
                        ->update(['poliza_id' => $poliza->id]);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("No se pudo generar póliza para gasto {$gasto->id}: " . $e->getMessage());
        }

        $this->actualizarProgramacionPagada($pago);
        $this->sincronizarEstatusPago($gasto);

        return $pago;
    }

    public function cancelarPago(GastoPago $pago, string $fechaCancelacion): void
    {
        DB::transaction(function () use ($pago, $fechaCancelacion) {
            if ($pago->estatus === 'cancelado') {
                return;
            }

            if ($pago->movimiento_bancario_id && $pago->cuenta_bancaria_id) {
                // Verificar si hay otros pagos vinculados al mismo movimiento
                $pagosVinculados = GastoPago::where('movimiento_bancario_id', $pago->movimiento_bancario_id)
                    ->where('estatus', '!=', 'cancelado')
                    ->where('id', '!=', $pago->id)
                    ->exists();

                if ($pagosVinculados) {
                    // Solo hacer un abono por el monto del gasto cancelado
                    $this->bancosService->registrarMovimiento([
                        'cuenta_bancaria_id' => $pago->cuenta_bancaria_id,
                        'tipo'               => 'abono',
                        'monto'              => $pago->monto,
                        'concepto'           => 'Devolución Parcial - ' . ($pago->gasto?->concepto ?? 'Pago cancelado'),
                        'fecha_movimiento'   => $fechaCancelacion,
                        'origen'             => 'sistema',
                        'referencia'         => 'cancelación' . ($pago->referencia ? ' - ' . $pago->referencia : ''),
                        'referenciaable_type' => Gasto::class,
                        'referenciaable_id'   => $pago->gasto_id,
                        'observaciones'      => 'Cancelación parcial de pago múltiple. Pago ID: ' . $pago->id,
                    ]);
                } else {
                    $this->bancosService->cancelarMovimiento(
                        $pago->cuenta_bancaria_id,
                        $pago->movimiento_bancario_id,
                        $fechaCancelacion
                    );
                }
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

            $gasto = $pago->gasto;
            if ($gasto) {
                $this->sincronizarEstatusPago($gasto);
            }
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

    public function sincronizarGastoConBancos(Gasto $gasto, float $nuevoMonto, ?string $fechaGasto = null, ?string $nuevoConcepto = null): Gasto
    {
        return DB::transaction(function () use ($gasto, $nuevoMonto, $fechaGasto, $nuevoConcepto) {
            $nuevoMonto = $this->normalizarMonto($nuevoMonto);
            $fechaPago = $fechaGasto ? $this->normalizarFecha($fechaGasto) : ($gasto->fecha_gasto ? Carbon::parse($gasto->fecha_gasto)->format('Y-m-d') : now()->format('Y-m-d'));

            // 1. Actualizar el gasto y sus imputaciones
            $gasto->monto_total = $nuevoMonto;
            if ($nuevoConcepto) {
                $gasto->concepto = $nuevoConcepto;
            }
            if ($fechaGasto) {
                $gasto->fecha_gasto = $fechaPago;
            }
            $gasto->save();

            foreach ($gasto->imputaciones as $imp) {
                $imp->monto_imputado = $nuevoMonto;
                if ($fechaGasto) {
                    $imp->fecha_imputacion = $fechaPago;
                }
                $imp->save();
            }

            // 2. Verificar si tiene pagos aplicados
            $pagosActivos = $gasto->pagos()->where('estatus', 'aplicado')->get();

            if ($pagosActivos->count() === 1) {
                $pago = $pagosActivos->first();
                $montoAnteriorPago = (float) $pago->monto;
                $diferencia = $nuevoMonto - $montoAnteriorPago;

                // Si el monto aumenta, validar saldo en banco
                if ($diferencia > 0) {
                    $validacion = $this->bancosService->validarsaldoparacargo(
                        $gasto->id_empresa,
                        $pago->cuenta_bancaria_id,
                        $fechaPago,
                        $diferencia
                    );

                    if (!$validacion['saldodisponible']) {
                        throw new \Exception($validacion['message']);
                    }
                }

                // Actualizar GastoPago
                $pago->monto = $nuevoMonto;
                $pago->fecha_pago = $fechaPago;
                $pago->save();

                // Buscar y actualizar el movimiento bancario
                $movimiento = null;
                if ($pago->movimiento_bancario_id) {
                    $movimiento = CatBancoCuentasMovimientos::find($pago->movimiento_bancario_id);
                }

                if (!$movimiento) {
                    $movimiento = CatBancoCuentasMovimientos::where('referenciaable_type', Gasto::class)
                        ->where('referenciaable_id', $gasto->id)
                        ->where('cancelado', false)
                        ->first();
                }

                if ($movimiento) {
                    // Actualizar detalles JSON si existen
                    $detalles = $movimiento->detalles;
                    if (is_array($detalles)) {
                        foreach ($detalles as &$item) {
                            if (isset($item['gasto_id']) && (int)$item['gasto_id'] === (int)$gasto->id) {
                                $item['monto'] = $nuevoMonto;
                                if ($nuevoConcepto) {
                                    $item['concepto'] = $nuevoConcepto;
                                }
                            } elseif (count($detalles) === 1) {
                                $item['monto'] = $nuevoMonto;
                                if ($nuevoConcepto) {
                                    $item['concepto'] = $nuevoConcepto;
                                }
                            }
                        }
                        unset($item);
                    }

                    $movUpdate = [
                        'monto' => $nuevoMonto,
                        'fecha_movimiento' => $fechaPago,
                        'detalles' => $detalles,
                    ];

                    if ($nuevoConcepto && strpos($movimiento->concepto ?? '', '[PAGO MULTIPLE]') === false) {
                        $movUpdate['concepto'] = 'Pago gasto (Editado): ' . $nuevoConcepto;
                    }

                    $movimiento->update($movUpdate);

                    // Sincronizar saldo de la cuenta bancaria
                    $banco = Bancos::find($movimiento->cuenta_bancaria_id);
                    if ($banco) {
                        $abonos = CatBancoCuentasMovimientos::where('cuenta_bancaria_id', $banco->id)->where('tipo', 'abono')->where('cancelado', false)->sum('monto');
                        $cargos = CatBancoCuentasMovimientos::where('cuenta_bancaria_id', $banco->id)->where('tipo', 'cargo')->where('cancelado', false)->sum('monto');
                        $banco->saldo = (float)($banco->inicial_saldo ?? 0) + $abonos - $cargos;
                        $banco->save();
                    }
                }
            }

            $this->sincronizarEstatusPago($gasto);

            return $gasto->fresh(['partidas', 'vinculos', 'imputaciones', 'programaciones', 'pagos']);
        });
    }

    private function resolverGastoExistente(array $data): ?Gasto
    {
        if (!empty($data['id'])) {
            return Gasto::find($data['id']);
        }

        if (!empty($data['origen_legacy']) && !empty($data['origen_legacy_id'])) {
            $query = Gasto::withTrashed()
                ->where('origen_legacy', $data['origen_legacy'])
                ->where('origen_legacy_id', $data['origen_legacy_id']);

            if (str_starts_with($data['origen_legacy'], 'asignacion_planeacion') && !empty($data['concepto'])) {
                $query->where('concepto', $data['concepto']);
            }

            return $query->first();
        }

        return null;
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

    private function validarPagoManualPermitido(Gasto $gasto): void
    {
        if ($gasto->origen_legacy === 'asignacion_sueldo_operador') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'gasto' => 'El sueldo del operador se paga desde la liquidación del viaje; no se puede registrar otro pago desde el módulo Gastos.',
            ]);
        }
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

    /**
     * Resuelve de forma normalizada los vínculos, imputaciones y programaciones de un gasto.
     */
    private function resolverVinculosEImputaciones(array $input, float $montoTotal): array
    {
        $vinculos = [];
        $imputaciones = [];
        $programaciones = [];

        $impacto = $input['impacto'] ?? 'periodo';
        $tipoImputacion = match ($impacto) {
            'cotizacion' => 'cotizacion',
            'viaje'      => 'viaje',
            'periodo'    => 'periodo',
            default      => 'periodo',
        };

        // 0. Cotizacion directa
        if (!empty($input['cotizacion_id'])) {
            $cotizacion = Cotizaciones::find($input['cotizacion_id']);
            if ($cotizacion) {
                $vinculos[] = [
                    'tipo_vinculo' => 'cotizacion',
                    'vinculable_type' => Cotizaciones::class,
                    'vinculable_id' => $cotizacion->id,
                    'observaciones' => 'Vinculo a cotización unificado.',
                ];

                $contenedor = DocumCotizacion::where('id_cotizacion', $cotizacion->id)->first();
                if ($contenedor) {
                    $vinculos[] = [
                        'tipo_vinculo' => 'contenedor',
                        'vinculable_type' => DocumCotizacion::class,
                        'vinculable_id' => $contenedor->id,
                        'observaciones' => $contenedor->num_contenedor,
                    ];

                    $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();
                    if ($asignacion) {
                        $vinculos[] = [
                            'tipo_vinculo' => 'asignacion',
                            'vinculable_type' => Asignaciones::class,
                            'vinculable_id' => $asignacion->id,
                            'observaciones' => 'Vinculo a viaje/asignación unificado.',
                        ];

                        if (($input['tipo_gasto'] ?? '') === 'operador' || ($input['tipo_gasto'] ?? '') === 'viaje') {
                            if ($asignacion->id_operador) {
                                $vinculos[] = [
                                    'tipo_vinculo' => 'operador',
                                    'vinculable_type' => Operador::class,
                                    'vinculable_id' => $asignacion->id_operador,
                                    'observaciones' => 'Vinculo a operador de la asignación.',
                                ];
                            }
                        }
                    }
                }

                $imputaciones[] = [
                    'fecha_imputacion' => $input['fecha_gasto'],
                    'tipo_imputacion' => $tipoImputacion,
                    'imputable_type' => Cotizaciones::class,
                    'imputable_id' => $cotizacion->id,
                    'monto_imputado' => $montoTotal,
                    'origen' => 'directo',
                ];
            }
        }
        // 1. Unidades múltiples
        elseif (($input['tipo_gasto'] ?? '') === 'unidad' && !empty($input['unidades'])) {
            $unidadesIds = (array) $input['unidades'];
            $count = count($unidadesIds);
            $montoProporcional = $count > 0 ? ($montoTotal / $count) : $montoTotal;

            foreach ($unidadesIds as $id) {
                $equipo = Equipo::find($id);
                if ($equipo) {
                    $vinculos[] = [
                        'tipo_vinculo' => 'unidad',
                        'vinculable_type' => Equipo::class,
                        'vinculable_id' => $equipo->id,
                        'observaciones' => 'Vinculo manual a unidad: ' . ($equipo->id_equipo ?: $equipo->placas),
                    ];
                    $imputaciones[] = [
                        'fecha_imputacion' => $input['fecha_gasto'],
                        'tipo_imputacion' => $tipoImputacion,
                        'imputable_type' => Equipo::class,
                        'imputable_id' => $equipo->id,
                        'monto_imputado' => $montoProporcional,
                        'origen' => 'directo',
                    ];
                }
            }
        }
        // Compatibilidad con unidad única
        elseif ((($input['tipo_gasto'] ?? '') === 'unidad' || !empty($input['id_equipo']) || !empty($input['equipo_id'])) && (!empty($input['id_equipo']) || !empty($input['equipo_id']))) {
            $equipoId = $input['id_equipo'] ?? $input['equipo_id'];
            $equipo = Equipo::find($equipoId);
            if ($equipo) {
                $vinculos[] = [
                    'tipo_vinculo' => 'unidad',
                    'vinculable_type' => Equipo::class,
                    'vinculable_id' => $equipo->id,
                    'observaciones' => 'Vinculo manual a unidad: ' . ($equipo->id_equipo ?: $equipo->placas),
                ];
                $imputaciones[] = [
                    'fecha_imputacion' => $input['fecha_gasto'],
                    'tipo_imputacion' => $tipoImputacion,
                    'imputable_type' => Equipo::class,
                    'imputable_id' => $equipo->id,
                    'monto_imputado' => $montoTotal,
                    'origen' => 'directo',
                ];
            }
        }
        // 2. Viajes múltiples
        elseif (in_array($input['tipo_gasto'] ?? '', ['viaje', 'contenedor', 'cotizacion']) && !empty($input['viajes'])) {
            $viajesIds = (array) $input['viajes'];
            $count = count($viajesIds);
            $montoProporcional = $count > 0 ? ($montoTotal / $count) : $montoTotal;

            foreach ($viajesIds as $id) {
                $asignacion = Asignaciones::with('Contenedor.Cotizacion')->find($id);
                if ($asignacion) {
                    $vinculos[] = [
                        'tipo_vinculo' => 'asignacion',
                        'vinculable_type' => Asignaciones::class,
                        'vinculable_id' => $asignacion->id,
                        'observaciones' => 'Vinculo manual a viaje.',
                    ];
                    $imputaciones[] = [
                        'fecha_imputacion' => $input['fecha_gasto'],
                        'tipo_imputacion' => $tipoImputacion,
                        'imputable_type' => Asignaciones::class,
                        'imputable_id' => $asignacion->id,
                        'monto_imputado' => $montoProporcional,
                        'origen' => 'directo',
                    ];

                    if ($asignacion->Contenedor) {
                        $vinculos[] = [
                            'tipo_vinculo' => 'contenedor',
                            'vinculable_type' => DocumCotizacion::class,
                            'vinculable_id' => $asignacion->Contenedor->id,
                            'observaciones' => $asignacion->Contenedor->num_contenedor,
                        ];
                        if ($asignacion->Contenedor->Cotizacion) {
                            $vinculos[] = [
                                'tipo_vinculo' => 'cotizacion',
                                'vinculable_type' => Cotizaciones::class,
                                'vinculable_id' => $asignacion->Contenedor->Cotizacion->id,
                                'observaciones' => 'Vinculo a cotizacion via asignacion.',
                            ];
                        }
                    }
                }
            }
        }
        // Compatibilidad viaje único
        elseif (in_array($input['tipo_gasto'] ?? '', ['viaje', 'contenedor', 'cotizacion']) && !empty($input['asignacion_id'])) {
            $asignacion = Asignaciones::with('Contenedor.Cotizacion')->find($input['asignacion_id']);
            if ($asignacion) {
                $vinculos[] = [
                    'tipo_vinculo' => 'asignacion',
                    'vinculable_type' => Asignaciones::class,
                    'vinculable_id' => $asignacion->id,
                    'observaciones' => 'Vinculo manual a viaje.',
                ];
                $imputaciones[] = [
                    'fecha_imputacion' => $input['fecha_gasto'],
                    'tipo_imputacion' => $tipoImputacion,
                    'imputable_type' => Asignaciones::class,
                    'imputable_id' => $asignacion->id,
                    'monto_imputado' => $montoTotal,
                    'origen' => 'directo',
                ];

                if ($asignacion->Contenedor) {
                    $vinculos[] = [
                        'tipo_vinculo' => 'contenedor',
                        'vinculable_type' => DocumCotizacion::class,
                        'vinculable_id' => $asignacion->Contenedor->id,
                        'observaciones' => $asignacion->Contenedor->num_contenedor,
                    ];
                    if ($asignacion->Contenedor->Cotizacion) {
                        $vinculos[] = [
                            'tipo_vinculo' => 'cotizacion',
                            'vinculable_type' => Cotizaciones::class,
                            'vinculable_id' => $asignacion->Contenedor->Cotizacion->id,
                            'observaciones' => 'Vinculo a cotizacion via asignacion.',
                        ];
                    }
                }
            }
        }
        // Operador
        elseif (($input['tipo_gasto'] ?? '') === 'operador' && !empty($input['operador_id'])) {
            $operador = Operador::find($input['operador_id']);
            if ($operador) {
                $vinculos[] = [
                    'tipo_vinculo' => 'operador',
                    'vinculable_type' => Operador::class,
                    'vinculable_id' => $operador->id,
                    'observaciones' => 'Vinculo manual a operador: ' . $operador->nombre,
                ];
                $imputaciones[] = [
                    'fecha_imputacion' => $input['fecha_gasto'],
                    'tipo_imputacion' => $tipoImputacion,
                    'imputable_type' => Operador::class,
                    'imputable_id' => $operador->id,
                    'monto_imputado' => $montoTotal,
                    'origen' => 'directo',
                ];
            }
        }

        // 4. Periodo diferido
        if (($input['tipo_gasto'] ?? '') === 'periodo' && ($input['metodo_imputacion'] ?? '') === 'diferido' && !empty($input['numPeriodos'])) {
            $numPeriodos = (int) $input['numPeriodos'];
            $montoPeriodo = $montoTotal / $numPeriodos;
            $fechaDesde = Carbon::parse($input['txtDiferirFechaInicia']);
            $fechaHasta = Carbon::parse($input['txtDiferirFechaTermina']);
            $fechaIniciaPeriodo = $fechaDesde->toDateString();

            for ($periodo = 1; $periodo <= $numPeriodos; $periodo++) {
                $finalMes = Carbon::parse($fechaIniciaPeriodo)->endOfMonth();
                $fechaFinPeriodo = ($finalMes > $fechaHasta) ? $fechaHasta->toDateString() : $finalMes->toDateString();
                $fechaIni = $fechaIniciaPeriodo;

                $programaciones[] = [
                    'numero_periodo' => $periodo,
                    'fecha_programada' => $fechaIni,
                    'fecha_vencimiento' => $fechaFinPeriodo,
                    'monto_programado' => $montoPeriodo,
                    'monto_pagado' => 0.00,
                    'estatus' => 'pendiente',
                ];

                $fechaIniciaPeriodo = $finalMes->addDay()->toDateString();
            }

            $imputaciones[] = [
                'fecha_imputacion' => $input['fecha_gasto'],
                'tipo_imputacion' => $tipoImputacion,
                'imputable_type' => null,
                'imputable_id' => null,
                'monto_imputado' => $montoTotal,
                'origen' => 'diferido',
            ];
        }

        if (empty($imputaciones)) {
            $imputaciones[] = [
                'fecha_imputacion' => $input['fecha_gasto'],
                'tipo_imputacion' => ($input['tipo_gasto'] ?? '') === 'periodo' ? 'periodo' : 'empresa',
                'imputable_type' => null,
                'imputable_id' => null,
                'monto_imputado' => $montoTotal,
                'origen' => 'directo',
            ];
        }

        $selectedEquipoId = $input['id_equipo'] ?? ($input['equipo_id'] ?? (is_array($input['unidades'] ?? null) && count($input['unidades']) === 1 ? $input['unidades'][0] : null));

        return compact('vinculos', 'imputaciones', 'programaciones', 'selectedEquipoId');
    }

    /**
     * Prepara los vínculos, imputaciones y programaciones de un gasto y lo registra atómicamente.
     */
    public function prepararYRegistrarGasto(array $input, $user): Gasto
    {
        return DB::transaction(function () use ($input, $user) {
            $montoTotal = (float) ($input['monto_total'] ?? 0);

            // Validar saldo si es pago de contado y tiene banco seleccionado
            if (($input['tipoPago'] ?? null) == 0 && !empty($input['id_banco1'])) {
                $validacion = $this->bancosService->validarsaldoparacargo(
                    $user->id_empresa,
                    $input['id_banco1'],
                    $input['fecha_gasto'],
                    $montoTotal
                );

                if (!$validacion['saldodisponible']) {
                    throw new SaldoInsuficienteException($validacion['message']);
                }
            }

            $resolved = $this->resolverVinculosEImputaciones($input, $montoTotal);

            $storeData = array_merge($input, [
                'id_empresa' => $user->id_empresa,
                'id_equipo' => $resolved['selectedEquipoId'],
                'origen_modulo' => $input['origen_modulo'] ?? 'manual',
                'estatus' => 'pendiente_pago',
                'vinculos' => $resolved['vinculos'],
                'imputaciones' => $resolved['imputaciones'],
                'programaciones' => $resolved['programaciones'],
                'user_id' => $user->id,
            ]);

            $gasto = $this->registrar($storeData);

            // Pago de contado automático
            if (($input['tipoPago'] ?? null) == 0 && !empty($input['id_banco1'])) {
                if (($input['tipo_gasto'] ?? '') === 'unidad' && !empty($input['unidades'])) {
                    $unidadesIds = (array) $input['unidades'];
                    $count = count($unidadesIds);
                    $montoProporcional = $count > 0 ? ($montoTotal / $count) : $montoTotal;

                    foreach ($unidadesIds as $id) {
                        $equipo = Equipo::find($id);
                        if ($equipo) {
                            $ref = 'UNIDAD: ' . ($equipo->id_equipo ?: $equipo->placas);
                            $this->pagar($gasto, [
                                'cuenta_bancaria_id' => $input['id_banco1'],
                                'fecha_pago' => $input['fecha_gasto'],
                                'monto' => $montoProporcional,
                                'metodo_pago' => 'Transferencia',
                                'referencia' => $ref,
                                'referencia_banco' => $ref,
                                'concepto_banco' => BancosService::generarConcepto('gasto', $gasto->concepto, null, null, ($equipo->id_equipo ?: $equipo->placas)),
                            ]);
                        }
                    }
                } elseif (in_array($input['tipo_gasto'] ?? '', ['viaje', 'contenedor', 'cotizacion']) && !empty($input['viajes'])) {
                    $viajesIds = (array) $input['viajes'];
                    $count = count($viajesIds);
                    $montoProporcional = $count > 0 ? ($montoTotal / $count) : $montoTotal;

                    foreach ($viajesIds as $id) {
                        $asignacion = Asignaciones::with(['Contenedor', 'Camion'])->find($id);
                        if ($asignacion) {
                            $numContenedor = $asignacion->Contenedor?->num_contenedor ?: 'S/N';
                            $equipoNombre = $asignacion->Camion ? ($asignacion->Camion->id_equipo ?: $asignacion->Camion->placas) : null;
                            $ref = 'VIAJE: ' . $numContenedor;
                            $this->pagar($gasto, [
                                'cuenta_bancaria_id' => $input['id_banco1'],
                                'fecha_pago' => $input['fecha_gasto'],
                                'monto' => $montoProporcional,
                                'metodo_pago' => 'Transferencia',
                                'referencia' => $ref,
                                'referencia_banco' => $ref,
                                'concepto_banco' => BancosService::generarConcepto('gasto', $gasto->concepto, $numContenedor, null, $equipoNombre),
                            ]);
                        }
                    }
                } else {
                    $equipoNombre = null;
                    if ($gasto->id_equipo) {
                        $eq = $gasto->equipo ?: Equipo::find($gasto->id_equipo);
                        $equipoNombre = $eq ? ($eq->id_equipo ?: $eq->placas) : null;
                    }

                    $this->pagar($gasto, [
                        'cuenta_bancaria_id' => $input['id_banco1'],
                        'fecha_pago' => $input['fecha_gasto'],
                        'monto' => $gasto->monto_total,
                        'metodo_pago' => 'Transferencia',
                        'referencia' => 'Pago automático al registrar',
                        'referencia_banco' => 'GASTO',
                        'concepto_banco' => BancosService::generarConcepto('gasto', $gasto->concepto, null, null, $equipoNombre),
                    ]);
                }
            }

            return $gasto;
        });
    }

    /**
     * Actualiza un gasto unificado, sus imputaciones y su movimiento bancario si aplica.
     */
    public function actualizarGasto(Gasto $gasto, array $input, $user): Gasto
    {
        if ($gasto->estatus === 'cancelado') {
            throw new \DomainException('No es posible editar un gasto que ya ha sido cancelado.');
        }

        return DB::transaction(function () use ($gasto, $input, $user) {
            $montoTotal = (float) ($input['monto_total'] ?? 0);
            $montoDiferencia = $montoTotal - (float) $gasto->monto_total;

            $resolved = $this->resolverVinculosEImputaciones($input, $montoTotal);

            $pagoExistente = $gasto->pagos()->where('estatus', 'aplicado')->first();

            if ($pagoExistente) {
                $esPagoUnico = ($gasto->pagos()->where('estatus', 'aplicado')->count() === 1);
                $nuevoMontoPago = $esPagoUnico ? $montoTotal : ((float) $pagoExistente->monto + $montoDiferencia);
                $diferenciaReal = $nuevoMontoPago - (float) $pagoExistente->monto;

                if ($diferenciaReal > 0) {
                    $validacion = $this->bancosService->validarsaldoparacargo(
                        $user->id_empresa,
                        $pagoExistente->cuenta_bancaria_id,
                        $input['fecha_gasto'],
                        $diferenciaReal
                    );

                    if (!$validacion['saldodisponible']) {
                        throw new SaldoInsuficienteException($validacion['message']);
                    }
                }

                $pagoExistente->update([
                    'monto' => $nuevoMontoPago,
                    'fecha_pago' => $input['fecha_gasto']
                ]);

                $movimiento = null;
                if ($pagoExistente->movimiento_bancario_id) {
                    $movimiento = CatBancoCuentasMovimientos::find($pagoExistente->movimiento_bancario_id);
                } elseif ($gasto->origen_legacy && $gasto->origen_legacy_id) {
                    $legacyTypes = [
                        'gastos_generales' => GastosGenerales::class,
                        'gastos_extras' => GastosExtras::class,
                        'gastos_operadores' => GastosOperadores::class,
                    ];
                    if (isset($legacyTypes[$gasto->origen_legacy])) {
                        $movimiento = CatBancoCuentasMovimientos::where('referenciaable_id', $gasto->origen_legacy_id)
                            ->where('referenciaable_type', $legacyTypes[$gasto->origen_legacy])
                            ->first();
                    }
                }

                if ($movimiento) {
                    $nuevoMontoMovimiento = $esPagoUnico ? $montoTotal : ((float) $movimiento->monto + $montoDiferencia);

                    $detalles = $movimiento->detalles;
                    if (is_array($detalles)) {
                        foreach ($detalles as &$item) {
                            if (isset($item['gasto_id']) && (int) $item['gasto_id'] === (int) $gasto->id) {
                                $item['monto'] = $nuevoMontoMovimiento;
                                $item['concepto'] = $input['concepto'] ?? $movimiento->concepto;
                            } elseif (count($detalles) === 1) {
                                $item['monto'] = $nuevoMontoMovimiento;
                                $item['concepto'] = $input['concepto'] ?? $movimiento->concepto;
                            }
                        }
                        unset($item);
                    }

                    $updateData = [
                        'monto' => $nuevoMontoMovimiento,
                        'fecha_movimiento' => $input['fecha_gasto'],
                        'detalles' => $detalles,
                    ];

                    if (strpos($movimiento->concepto ?? '', '[PAGO MULTIPLE]') === false) {
                        $updateData['concepto'] = 'Pago gasto (Editado): ' . ($input['concepto'] ?? '');
                    }

                    $movimiento->update($updateData);

                    $banco = Bancos::find($movimiento->cuenta_bancaria_id);
                    if ($banco) {
                        $abonos = CatBancoCuentasMovimientos::where('cuenta_bancaria_id', $banco->id)->where('tipo', 'abono')->where('cancelado', false)->sum('monto');
                        $cargos = CatBancoCuentasMovimientos::where('cuenta_bancaria_id', $banco->id)->where('tipo', 'cargo')->where('cancelado', false)->sum('monto');
                        $banco->saldo = (float) ($banco->inicial_saldo ?? 0) + $abonos - $cargos;
                        $banco->save();
                    }
                }
            }

            // Sync legacy tables
            if ($gasto->origen_legacy && $gasto->origen_legacy_id) {
                $legacyId = $gasto->origen_legacy_id;
                if ($gasto->origen_legacy === 'gastos_generales') {
                    DB::table('gastos_generales')
                        ->where('id', $legacyId)
                        ->update([
                            'motivo' => $input['concepto'] ?? '',
                            'monto1' => $montoTotal,
                            'fecha' => $input['fecha_gasto'],
                            'fecha_operacion' => $input['fecha_gasto'],
                        ]);
                } elseif ($gasto->origen_legacy === 'gastos_extras') {
                    DB::table('gastos_extras')
                        ->where('id', $legacyId)
                        ->update([
                            'descripcion' => $input['concepto'] ?? '',
                            'monto' => $montoTotal,
                            'fecha_aplicacion' => $input['fecha_gasto'],
                        ]);
                } elseif ($gasto->origen_legacy === 'gastos_operadores') {
                    DB::table('gastos_operadores')
                        ->where('id', $legacyId)
                        ->update([
                            'cantidad' => $montoTotal,
                            'fecha_pago' => $input['fecha_gasto'],
                        ]);
                }
            }

            $storeData = array_merge($input, [
                'id' => $gasto->id,
                'id_empresa' => $user->id_empresa,
                'id_equipo' => $resolved['selectedEquipoId'],
                'vinculos' => $resolved['vinculos'],
                'imputaciones' => $resolved['imputaciones'],
                'programaciones' => $resolved['programaciones'],
            ]);

            return $this->registrar($storeData);
        });
    }

    /**
     * Elimina lógicamente un gasto unificado y revierte sus pagos asociados.
     */
    public function eliminarGasto(Gasto $gasto, ?string $fechaCancelacion = null): void
    {
        if (in_array($gasto->origen_legacy, ['viaticos_operadores', 'viaticos_operadores_excedente', 'gastos_operadores'])) {
            throw new \DomainException('Este gasto proviene de justificaciones de viáticos o excedentes del viaje del operador y no puede ser eliminado desde este módulo, ya que su flujo de pago se gestiona a través de la liquidación del viaje.');
        }

        DB::transaction(function () use ($gasto, $fechaCancelacion) {
            $fecha = $fechaCancelacion ?? now()->format('Y-m-d');

            foreach ($gasto->pagos()->where('estatus', '!=', 'cancelado')->get() as $pago) {
                $this->cancelarPago($pago, $fecha);
            }

            if ($gasto->origen_legacy && $gasto->origen_legacy_id) {
                $legacyId = $gasto->origen_legacy_id;

                if ($gasto->origen_legacy === 'gastos_operadores') {
                    DB::table('gastos_operadores')
                        ->where('id', $legacyId)
                        ->update(['estatus' => 'eliminado']);
                } elseif (str_starts_with($gasto->origen_legacy, 'asignacion_planeacion')) {
                    if (str_contains($gasto->concepto, 'Diesel')) {
                        DB::table('gastos_operadores')
                            ->where('id_asignacion', $legacyId)
                            ->where('tipo', 'Diesel')
                            ->update(['estatus' => 'eliminado']);
                    }
                } elseif ($gasto->origen_legacy === 'gastos_extras') {
                    DB::table('gastos_extras')
                        ->where('id', $legacyId)
                        ->update(['estatus' => 'eliminado']);

                    $gExtra = DB::table('gastos_extras')->where('id', $legacyId)->first();
                    if ($gExtra && !empty($gExtra->id_cotizacion)) {
                        $monto = floatval($gExtra->monto ?? 0);
                        if ($monto > 0) {
                            DB::table('cotizaciones')
                                ->where('id', $gExtra->id_cotizacion)
                                ->update([
                                    'restante' => DB::raw("restante - {$monto}")
                                ]);
                        }
                    }
                } elseif ($gasto->origen_legacy === 'gastos_generales') {
                    DB::table('gastos_generales')
                        ->where('id', $legacyId)
                        ->delete();

                    DB::table('gastos_operadores')
                        ->where('id_gasto_origen', $legacyId)
                        ->update(['estatus' => 'eliminado']);
                }
            }

            $gasto->update(['estatus' => 'cancelado']);
            $gasto->delete();
        });
    }

    /**
     * Aplica el pago conjunto a una lista de gastos con un único cargo bancario agrupado.
     */
    public function pagarMultipleGastos(array $ids, int $cuentaBancariaId, string $fechaPago, ?string $referencia, $user): void
    {
        DB::transaction(function () use ($ids, $cuentaBancariaId, $fechaPago, $referencia, $user) {
            $totalMonto = 0;
            $gastos = Gasto::whereIn('id', $ids)->get();
            foreach ($gastos as $gasto) {
                if ($gasto->estatus !== 'pagado') {
                    $totalMonto += (float) $gasto->saldo_pendiente;
                }
            }

            $validacion = $this->bancosService->validarsaldoparacargo(
                $user->id_empresa,
                $cuentaBancariaId,
                $fechaPago,
                $totalMonto
            );

            if (!$validacion['saldodisponible']) {
                throw new SaldoInsuficienteException($validacion['message']);
            }

            $conceptos = [];
            $detalles = [];
            $unidadesMap = [];

            foreach ($gastos as $gasto) {
                if ($gasto->estatus !== 'pagado') {
                    $conceptos[] = $gasto->concepto;

                    $gasto->loadMissing(['vinculos', 'equipo']);
                    $vinculosInfo = [];
                    foreach ($gasto->vinculos as $v) {
                        $vinculosInfo[] = [
                            'tipo' => $v->tipo_vinculo,
                            'referencia' => $v->observaciones ?: $v->vinculable_id
                        ];
                    }

                    $equipoId = $gasto->id_equipo;
                    $equipoNombre = null;
                    if ($gasto->equipo) {
                        $equipoNombre = $gasto->equipo->id_equipo ?: $gasto->equipo->placas;
                    } elseif ($gasto->id_equipo) {
                        $eq = Equipo::find($gasto->id_equipo);
                        $equipoNombre = $eq ? ($eq->id_equipo ?: $eq->placas) : null;
                    } else {
                        $vUnidad = $gasto->vinculos->firstWhere('tipo_vinculo', 'unidad');
                        if ($vUnidad) {
                            $equipoId = $vUnidad->vinculable_id;
                            $eq = Equipo::find($equipoId);
                            $equipoNombre = $eq ? ($eq->id_equipo ?: $eq->placas) : null;
                        }
                    }
                    if ($equipoId) {
                        $unidadesMap[$equipoId] = $equipoNombre;
                    }

                    $detalles[] = [
                        'gasto_id' => $gasto->id,
                        'concepto' => $gasto->concepto,
                        'monto' => $gasto->saldo_pendiente,
                        'tipo_gasto' => $gasto->tipo_gasto,
                        'id_equipo' => $equipoId,
                        'unidad' => $equipoNombre,
                        'vinculos' => $vinculosInfo
                    ];
                }
            }

            $conceptoMovimiento = '[PAGO MULTIPLE] Pago de gastos';
            if (count($unidadesMap) === 1) {
                $conceptoMovimiento .= ' - Unidad: ' . reset($unidadesMap);
            }

            $movimiento = $this->bancosService->registrarMovimiento([
                'cuenta_bancaria_id' => $cuentaBancariaId,
                'tipo' => 'cargo',
                'monto' => $totalMonto,
                'concepto' => $conceptoMovimiento,
                'fecha_movimiento' => $fechaPago,
                'referencia' => $referencia ?? 'PAGO MULTIPLE',
                'detalles' => $detalles,
                'referenciaable_id' => null,
                'referenciaable_type' => Gasto::class,
            ]);

            foreach ($gastos as $gasto) {
                if ($gasto->estatus === 'pagado') {
                    continue;
                }

                $this->pagarConMovimientoExistente($gasto, [
                    'cuenta_bancaria_id' => $cuentaBancariaId,
                    'fecha_pago' => $fechaPago,
                    'monto' => $gasto->saldo_pendiente,
                    'referencia' => $referencia ?? 'Pago múltiple',
                    'movimiento_bancario_id' => $movimiento->id,
                ]);
            }
        });
    }

    /**
     * Aplica la distribución de un gasto general hacia viajes, equipos o período.
     */
    public function aplicarGastosGenerales(int $gastoId, string $formaAplicacion, ?array $viajes, ?array $unidades, ?int $bancoId): array
    {
        return DB::transaction(function () use ($gastoId, $formaAplicacion, $viajes, $unidades, $bancoId) {
            $gastosGenerales = GastosGenerales::findOrFail($gastoId);

            switch ($formaAplicacion) {
                case "Viaje":
                    $listaViajes = $viajes ?? [];
                    $numViajes = count($listaViajes);
                    $montoGasto = (float) $gastosGenerales->monto1;
                    $montoViaje = $numViajes > 0 ? ($montoGasto / $numViajes) : $montoGasto;

                    $contenedores = [];

                    foreach ($listaViajes as $lv) {
                        $contenedor = DocumCotizacion::where('id_cotizacion', $lv)->first();
                        $contenedores[] = [
                            'num_contenedor' => $contenedor?->num_contenedor,
                            'monto' => $montoViaje
                        ];

                        $asignacion = Asignaciones::where('id_contenedor', $lv)->first();

                        $datosGasto = [
                            "id_cotizacion" => $contenedor?->id_cotizacion,
                            "id_banco" => $bancoId ?: null,
                            "id_asignacion" => $asignacion?->id,
                            "id_operador" => $asignacion?->id_operador,
                            "cantidad" => $montoViaje,
                            "tipo" => $gastosGenerales->motivo,
                            "estatus" => $bancoId ? 'Pagado' : 'Pago Pendiente',
                            "fecha_pago" => $bancoId ? date('Y-m-d') : null,
                            "pago_inmediato" => $gastosGenerales->diferir_gasto,
                            "id_gasto_origen" => $gastoId,
                            "created_at" => Carbon::now()
                        ];

                        GastosOperadores::create($datosGasto);
                    }

                    $aplicacion = ["aplicacion" => "viajes", "elementos" => $contenedores];
                    break;

                case "Equipo":
                    $listaEquipos = $unidades ?? [];
                    $equipos = [];
                    foreach ($listaEquipos as $le) {
                        $equipos[] = ['equipo' => $le];
                    }
                    $aplicacion = ["aplicacion" => "equipos", "elementos" => $equipos];
                    break;

                default:
                    $aplicacion = ["aplicacion" => "periodo", "elementos" => []];
                    break;
            }

            $gastosGenerales->aplicacion_gasto = json_encode($aplicacion);
            $gastosGenerales->save();

            return [
                "Titulo" => "Exito!",
                "Mensaje" => "Se ha diferido el gasto exitosamente",
                "TMensaje" => "success",
                "Aplicacion" => $aplicacion
            ];
        });
    }

    /**
     * Registra un gasto general y opcionalmente sus parcialidades diferidas y bancos.
     */
    public function storeGastoGeneral(array $requestData, $user): array
    {
        return DB::transaction(function () use ($requestData, $user) {
            $montoGasto = (float) ($requestData['monto1'] ?? 0);
            $idEmpresa = $user->id_empresa;
            $bancoId = $requestData['id_banco1'] ?? null;
            $fechaAplicacion = $requestData['fecha_aplicacion'] ?? ($requestData['fechaInicioSeleccionado'] ?? date('Y-m-d'));

            if ($bancoId && $montoGasto > 0 && ($requestData['tipoPago'] ?? 0) == 0) {
                $validarSaldos = $this->bancosService->validarsaldoparacargo($idEmpresa, $bancoId, $fechaAplicacion, $montoGasto);
                if (!$validarSaldos["saldodisponible"]) {
                    throw new SaldoInsuficienteException($validarSaldos["message"]);
                }
            }

            $pagorealizado = ((int) ($requestData['tipoPago'] ?? 0) === 0) ? 1 : 0;
            $gastoGeneral = new GastosGenerales();
            $gastoGeneral->motivo = $requestData['motivo'] ?? '';
            $gastoGeneral->monto1 = $montoGasto;
            $gastoGeneral->id_categoria = $requestData['categoria_movimiento'] ?? null;
            $gastoGeneral->metodo_pago1 = 'Transferencia';
            $gastoGeneral->id_banco1 = $bancoId;
            $gastoGeneral->fecha = $fechaAplicacion;
            $gastoGeneral->fecha_operacion = $requestData['fecha_movimiento'] ?? $fechaAplicacion;
            $gastoGeneral->id_empresa = $idEmpresa;
            $gastoGeneral->is_active = (($requestData['tipoPago'] ?? 0) == 1) ? 0 : 1;
            $gastoGeneral->diferir_gasto = $requestData['tipoPago'] ?? 0;
            $gastoGeneral->pago_realizado = $pagorealizado;
            $gastoGeneral->save();

            if (($requestData['tipoPago'] ?? 0) == 1) {
                $gastoGeneral->diferir_contador_periodos = $requestData['numPeriodos'] ?? 1;
                $gastoGeneral->fecha_diferido_inicial = $requestData['txtDiferirFechaInicia'] ?? null;
                $gastoGeneral->fecha_diferido_final = $requestData['txtDiferirFechaTermina'] ?? null;
                $gastoGeneral->save();

                $numPeriodos = (int) ($requestData['numPeriodos'] ?? 1);
                $fechaDesde = Carbon::parse($requestData['txtDiferirFechaInicia']);
                $fechaHasta = Carbon::parse($requestData['txtDiferirFechaTermina']);
                $fechaIniciaPeriodo = $fechaDesde->toDateString();
                $montoPeriodo = $montoGasto / $numPeriodos;

                $Daily = [];
                for ($periodo = 1; $periodo <= $numPeriodos; $periodo++) {
                    $finalMes = Carbon::parse($fechaIniciaPeriodo)->endOfMonth();
                    $fechaFinPeriodo = ($finalMes > $fechaHasta) ? $fechaHasta->toDateString() : $finalMes->toDateString();

                    $Daily[] = [
                        "motivo" => ($requestData['motivo'] ?? '') . ": Parcialidad $periodo de $numPeriodos",
                        "monto1" => $montoPeriodo,
                        "id_categoria" => $requestData['categoria_movimiento'] ?? null,
                        "metodo_pago1" => 'Transferencia',
                        "id_banco1" => $bancoId,
                        "fecha" => $fechaIniciaPeriodo,
                        "fecha_operacion" => $requestData['fecha_movimiento'] ?? $fechaAplicacion,
                        "id_empresa" => $idEmpresa,
                        "is_active" => 1,
                        "diferir_gasto" => 1,
                        "fecha_diferido_inicial" => $fechaIniciaPeriodo,
                        "fecha_diferido_final" => $fechaFinPeriodo,
                        "gasto_origen_id" => $gastoGeneral->id,
                        "pago_realizado" => 0,
                        "created_at" => date('Y-m-d H:i:s')
                    ];

                    $fechaIniciaPeriodo = $finalMes->addDay()->toDateString();
                }

                GastosGenerales::insert($Daily);
            } else {
                if ($bancoId && $montoGasto > 0) {
                    $movimientoData = [
                        'cuenta_bancaria_id' => $bancoId,
                        'tipo' => 'cargo',
                        'monto' => $montoGasto,
                        'concepto' => $requestData['motivo'] ?? '',
                        'fecha_movimiento' => $fechaAplicacion,
                        'origen' => null,
                        'referencia' => 'Gastos Generales',
                        'detalles' => "[]",
                        'referenciaable_id' => $gastoGeneral->id,
                        'referenciaable_type' => GastosGenerales::class,
                        'observaciones' => 'GG',
                    ];
                    $this->bancosService->registrarMovimiento($movimientoData);
                }
            }

            $idBancoAplicar = (($requestData['tipoPago'] ?? 0) == 1) ? null : $bancoId;
            $aplicarGasto = $this->aplicarGastosGenerales(
                $gastoGeneral->id,
                $requestData['formasAplicar'] ?? 'Periodo',
                $requestData['viajes'] ?? null,
                $requestData['unidades'] ?? null,
                $idBancoAplicar
            );

            if (($aplicarGasto["TMensaje"] ?? '') === "success") {
                GastosGenerales::where('gasto_origen_id', $gastoGeneral->id)
                    ->update(["aplicacion_gasto" => $aplicarGasto["Aplicacion"]]);
            }

            // Sincronizaciones unificadas
            $this->registrarDesdeGastoGeneral($gastoGeneral->fresh());
            GastosGenerales::where('gasto_origen_id', $gastoGeneral->id)->get()->each(function ($gd) {
                $this->registrarDesdeGastoGeneral($gd);
            });
            GastosOperadores::where('id_gasto_origen', $gastoGeneral->id)->get()->each(function ($go) {
                $this->registrarDesdeGastoOperador($go);
            });

            return [
                "Titulo" => "Gasto registrado",
                "Mensaje" => (($aplicarGasto["TMensaje"] ?? '') != "success") ? "Se ha registrado el gasto, pero no pudo ser aplicado a su selección" : "Se registró el gasto en la cuenta seleccionada y aplicado correctamente",
                "TMensaje" => (($aplicarGasto["TMensaje"] ?? '') != "success") ? "info" : "success",
                "gasto_id" => $gastoGeneral->id,
            ];
        });
    }

    /**
     * Elimina un gasto general y sus impactos bancarios/operadores.
     */
    public function eliminarGastoGeneral(int $gastoId, ?string $fechaCancelacion, int $idEmpresa): void
    {
        DB::transaction(function () use ($gastoId, $fechaCancelacion, $idEmpresa) {
            $gastosGenerales = GastosGenerales::where('id', $gastoId)->firstOrFail();
            $gastosOperadoresOrigen = GastosOperadores::where('id_gasto_origen', $gastoId)->pluck('id');

            GastosOperadores::where('id_gasto_origen', $gastoId)->delete();

            if ($gastosGenerales->pago_realizado === 1 && $gastosGenerales->id_banco1) {
                $movimientoBanco = $this->bancosService->findMovimiento($gastoId, GastosGenerales::class, $gastosGenerales->id_banco1);
                if ($movimientoBanco) {
                    $this->bancosService->cancelarMovimiento($gastosGenerales->id_banco1, $movimientoBanco->id, $fechaCancelacion ?? date('Y-m-d'));
                }
            }

            $gastosGenerales->delete();

            $this->cancelarDesdeLegacy('gastos_generales', $gastosGenerales->id);
            $gastosOperadoresOrigen->each(fn ($id) => $this->cancelarDesdeLegacy('gastos_operadores', $id));
        });
    }

    /**
     * Obtiene los datos del grid HandsOnTable para Gastos Generales.
     */
    public function getGastosGeneralesGridData(int $idEmpresa, string $from, string $to): array
    {
        $fechaInicial = Carbon::parse($from)->startOfMonth()->format('Y-m-d');
        $fechaFinal = Carbon::parse($to)->format('Y-m-d');

        $gastos = GastosGenerales::where('id_empresa', $idEmpresa)
            ->where('is_active', 1)
            ->whereBetween('fecha', [$fechaInicial, $fechaFinal])
            ->orderBy('created_at', 'desc')
            ->get();

        $gastosInformacion = $gastos->map(function ($g) {
            return [
                "IdGasto" => $g->id,
                "Descripcion" => $g->motivo,
                "Monto" => $g->monto1,
                "Categoria" => $g->categoria?->categoria,
                "CuentaOrigen" => $g->banco1?->nombre_banco,
                "FechaGasto" => $g->fecha,
                "FechaContabilizado" => $g->fecha_operacion ?: $g->fecha,
                "Estatus" => true,
                "Diferido" => ($g->diferir_gasto == 1) ? 'Diferido' : '',
                "GastoAplicado" => (!is_null($g->aplicacion_gasto)),
            ];
        });

        $gastosUnidadQuery = "SELECT
            a.id_camion,
            gg.motivo,
            COUNT(DISTINCT a.id) AS total_asignaciones,
            COALESCE(SUM(DISTINCT gg.monto1 / JSON_LENGTH(JSON_EXTRACT(gg.aplicacion_gasto, '$.elementos'))), 0) AS total_gastos_periodo,
            COALESCE(SUM(DISTINCT gg.monto1 / JSON_LENGTH(JSON_EXTRACT(gg.aplicacion_gasto, '$.elementos'))), 0) / COUNT(DISTINCT a.id) AS gasto_por_viaje
            FROM asignaciones a
            LEFT JOIN gastos_generales gg
                ON gg.aplicacion_gasto IS NOT NULL
                AND JSON_VALID(gg.aplicacion_gasto) = 1
                AND JSON_UNQUOTE(JSON_EXTRACT(gg.aplicacion_gasto, '$.aplicacion')) = 'equipos'
                AND JSON_CONTAINS(
                    JSON_EXTRACT(gg.aplicacion_gasto, '$.elementos'),
                    JSON_OBJECT('equipo', CAST(a.id_camion AS CHAR)),
                    '$'
                )
                AND gg.fecha BETWEEN '$fechaInicial' AND '$fechaFinal'
            WHERE a.fecha_inicio BETWEEN '$fechaInicial' AND '$fechaFinal'
            AND a.id_camion IS NOT NULL AND gg.is_active = 1
            GROUP BY a.id_camion, gg.motivo;";

        $gastosUnidad = collect(DB::select($gastosUnidadQuery));

        $cotizaciones = Cotizaciones::where('id_empresa', $idEmpresa)
            ->whereIn('estatus', ['Finalizado', 'Aprobada'])
            ->where('estatus_planeacion', '=', 1)
            ->where('jerarquia', "!=", 'Secundario')
            ->whereHas('DocCotizacion.Asignaciones', function ($query) use ($fechaInicial, $fechaFinal) {
                $query->whereBetween('fecha_inicio', [$fechaInicial, $fechaFinal]);
            })
            ->with(['cliente', 'DocCotizacion.Asignaciones' => function ($query) use ($fechaInicial, $fechaFinal) {
                $query->whereBetween('fecha_inicio', [$fechaInicial, $fechaFinal]);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        $handsOnTableData = $cotizaciones->map(function ($cotizacion) use ($gastosUnidad) {
            $contenedor = $cotizacion->DocCotizacion ? $cotizacion->DocCotizacion->num_contenedor : 'N/A';
            $gastosDiferidos = (count($gastosUnidad) > 0) ? $gastosUnidad->where('id_camion', $cotizacion->DocCotizacion?->Asignaciones?->id_camion)->sum('gasto_por_viaje') : 0;

            if (!is_null($cotizacion->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $cotizacion->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion.Asignaciones')
                    ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $contenedor .= ' ' . $secundaria->DocCotizacion->num_contenedor;
                }
            }

            $Gastos = GastosOperadores::where('id_cotizacion', $cotizacion->id)->get();

            return [
                $contenedor,
                $Gastos->filter(fn ($g) => str_contains($g->tipo, 'GCM01'))->sum('cantidad'),
                $Gastos->filter(fn ($g) => str_contains($g->tipo, 'GDI02'))->sum('cantidad'),
                $Gastos->filter(fn ($g) => str_contains($g->tipo, 'GCP03'))->sum('cantidad'),
                $Gastos->filter(fn ($g) => !str_contains($g->tipo, 'GCM01') && !str_contains($g->tipo, 'GDI02') && !str_contains($g->tipo, 'GCP03'))->sum('cantidad'),
                $gastosDiferidos,
                ($Gastos->filter(fn ($g) => str_contains($g->tipo, 'GCM01'))->first()?->estatus == 'Pagado') ? 1 : 0,
                ($Gastos->filter(fn ($g) => str_contains($g->tipo, 'GDI02'))->first()?->estatus == 'Pagado') ? 1 : 0,
                ($Gastos->filter(fn ($g) => str_contains($g->tipo, 'GCP03'))->first()?->estatus == 'Pagado') ? 1 : 0,
                '',
                $cotizacion->id,
            ];
        });

        return compact('handsOnTableData', 'gastosInformacion');
    }

    /**
     * Aplica el pago conjunto de gastos en GastosContenedoresController@PagarGastosMultiple.
     */
    public function pagarGastosMultipleContenedores(array $gastosPagar, int $bankId, string $fechaAplicacion, float $totalPago, int $idEmpresa): void
    {
        DB::transaction(function () use ($gastosPagar, $bankId, $fechaAplicacion, $totalPago, $idEmpresa) {
            if ($bankId && $totalPago > 0) {
                $validarSaldos = $this->bancosService->validarsaldoparacargo($idEmpresa, $bankId, $fechaAplicacion, $totalPago);
                if (!$validarSaldos["saldodisponible"]) {
                    throw new SaldoInsuficienteException($validarSaldos["message"]);
                }
            }

            foreach ($gastosPagar as $item) {
                if (($item['Origen'] ?? null) === 'Operador') {
                    $gasto = Gasto::find($item['IdGasto']);
                    if ($gasto) {
                        $contenedorVinculo = $gasto->vinculos()->where('tipo_vinculo', 'contenedor')->first();
                        $numContenedor = $contenedorVinculo ? $contenedorVinculo->observaciones : null;

                        $operadorVinculo = $gasto->vinculos()->where('tipo_vinculo', 'operador')->first();
                        $operadorNombre = $operadorVinculo ? str_replace('Operador: ', '', $operadorVinculo->observaciones) : null;

                        $this->pagar($gasto, [
                            'cuenta_bancaria_id' => $bankId,
                            'monto' => $item['Monto'],
                            'fecha_pago' => $fechaAplicacion,
                            'concepto_banco' => BancosService::generarConcepto('gasto', $gasto->concepto, $numContenedor, $operadorNombre),
                            'referencia_banco' => 'GASTO_CONTENEDOR'
                        ]);
                    }
                } elseif (($item['Origen'] ?? null) === 'General') {
                    GastosGenerales::where('id', $item['IdGasto'])->update([
                        "pago_realizado" => 1,
                        "id_banco2" => $bankId,
                        "fecha_operacion" => $fechaAplicacion,
                        "is_active" => 0
                    ]);

                    $this->bancosService->registrarMovimiento([
                        'cuenta_bancaria_id' => $bankId,
                        'tipo' => 'cargo',
                        'monto' => floatval($item['Monto']),
                        'concepto' => 'Pago Gasto General: ' . ($item['NumContenedor'] ?? 'S/N'),
                        'fecha_movimiento' => $fechaAplicacion,
                        'referencia' => 'GASTO_GENERAL',
                        'referenciaable_id' => $item['IdGasto'],
                        'referenciaable_type' => GastosGenerales::class,
                    ]);

                    $gastoGeneral = GastosGenerales::find($item['IdGasto']);
                    if ($gastoGeneral) {
                        $this->registrarDesdeGastoGeneral($gastoGeneral);
                    }
                }
            }
        });
    }

    /**
     * Obtiene los datos HandsOnTable para GastosContenedoresController@gastosViajesList.
     */
    public function getGastosViajesHandsOnTable(int $idEmpresa, string $fechaInicio, string $fechaFin): array
    {
        $gastosUnidadQuery = "SELECT
            a.id_camion,
            g.concepto as motivo,
            COUNT(DISTINCT a.id) AS total_asignaciones,
            COALESCE(SUM(gi.monto_imputado), 0) AS total_gastos_periodo,
            COALESCE(SUM(gi.monto_imputado), 0) / COUNT(DISTINCT a.id) AS gasto_por_viaje
            FROM asignaciones a
            LEFT JOIN gasto_imputaciones gi
                ON gi.tipo_imputacion = 'unidad'
                AND gi.imputable_id = a.id_camion
                AND gi.fecha_imputacion BETWEEN '$fechaInicio' AND '$fechaFin'
            LEFT JOIN gastos g
                ON g.id = gi.gasto_id
                AND g.deleted_at IS NULL
                AND g.estatus != 'cancelado'
            WHERE a.fecha_inicio BETWEEN '$fechaInicio' AND '$fechaFin'
            AND a.id_camion IS NOT NULL
            GROUP BY a.id_camion, g.concepto;";

        $gastosUnidad = collect(DB::select($gastosUnidadQuery));

        $cotizaciones = Cotizaciones::where('id_empresa', $idEmpresa)
            ->whereIn('estatus', ['Finalizado', 'Aprobada'])
            ->where('estatus_planeacion', '=', 1)
            ->where('jerarquia', "!=", 'Secundario')
            ->whereHas('DocCotizacion.Asignaciones', function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin]);
            })
            ->with(['cliente', 'DocCotizacion.Asignaciones' => function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin]);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        $handsOnTableData = $cotizaciones->map(function ($cotizacion) use ($gastosUnidad) {
            $doc = $cotizacion->DocCotizacion;
            $asignacion = $doc?->Asignaciones;
            $contenedor = $doc?->num_contenedor ?? 'N/A';

            if (!is_null($cotizacion->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $cotizacion->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion')
                    ->first();

                if ($secundaria?->DocCotizacion) {
                    $contenedor .= ' ' . $secundaria->DocCotizacion->num_contenedor;
                }
            }

            $gastos = \App\Models\GastoImputacion::join('gastos', 'gastos.id', '=', 'gasto_imputaciones.gasto_id')
                ->join('gasto_vinculos', 'gasto_vinculos.gasto_id', '=', 'gastos.id')
                ->whereNull('gastos.deleted_at')
                ->where('gastos.estatus', '!=', 'cancelado')
                ->where('gasto_vinculos.tipo_vinculo', '=', 'cotizacion')
                ->where('gasto_vinculos.vinculable_type', '=', Cotizaciones::class)
                ->where('gasto_vinculos.vinculable_id', '=', $cotizacion->id)
                ->where('gasto_imputaciones.tipo_imputacion', '=', 'operador')
                ->select(
                    'gasto_imputaciones.*',
                    'gasto_imputaciones.monto_imputado as cantidad',
                    'gastos.concepto as tipo',
                    DB::raw("CASE WHEN gastos.estatus = 'pagado' THEN 'Pagado' ELSE gastos.estatus END as estatus")
                )
                ->get();

            $comision = $gastos->first(fn ($g) => str_contains($g->tipo, 'GCM01'));
            $diesel   = $gastos->first(fn ($g) => str_contains($g->tipo, 'GDI02'));
            $casetas  = $gastos->first(fn ($g) => str_contains($g->tipo, 'GCP03'));

            $gastosDiferidos = $gastosUnidad
                ->where('id_camion', $asignacion?->id_camion)
                ->sum('gasto_por_viaje');

            return [
                "contenedor"       => $contenedor,
                "comision"         => $gastos->where('tipo', 'like', '%GCM01%')->sum('cantidad'),
                "diesel"           => $gastos->where('tipo', 'like', '%GDI02%')->sum('cantidad'),
                "casetas"          => $gastos->where('tipo', 'like', '%GCP03%')->sum('cantidad'),
                "varios"           => $gastos->reject(
                    fn ($g) =>
                    str_contains($g->tipo, 'GCM01') ||
                    str_contains($g->tipo, 'GDI02') ||
                    str_contains($g->tipo, 'GCP03')
                )->sum('cantidad'),
                "diferidos"        => $gastosDiferidos,

                "pago_comision"    => $comision?->estatus === 'Pagado' ? 1 : 0,
                "pago_diesel"      => $diesel?->estatus === 'Pagado' ? 1 : 0,
                "pago_casetas"     => $casetas?->estatus === 'Pagado' ? 1 : 0,

                "banco"            => null,
                "fecha_aplicacion" => null,
                "id_cotizacion"    => $cotizacion->id,
            ];
        });

        return $handsOnTableData->toArray();
    }

    /**
     * Confirma y guarda el batch de gastos de viaje desde HandsOnTable en GastosContenedoresController.
     */
    public function confirmarGastosContenedores(array $gastosContenedores, int $idEmpresa): void
    {
        DB::transaction(function () use ($gastosContenedores, $idEmpresa) {
            $nuevosGastos = [];
            $bancoDinero = [];

            foreach ($gastosContenedores as $gasto) {
                $numContenedor = $gasto['contenedor'] ?? null;
                $bank = isset($gasto['banco']) ? trim(substr($gasto['banco'], 0, 5)) : null;
                $fechaPago = !empty($gasto['fecha_aplicacion']) ? Carbon::parse($gasto['fecha_aplicacion']) : null;

                $contenedor = DocumCotizacion::where('num_contenedor', $numContenedor)
                    ->where('id_empresa', $idEmpresa)
                    ->first();

                if (!$contenedor) {
                    continue;
                }

                $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();

                $tipos = [
                    'comision' => [
                        'codigo' => 'GCM01 - Comisión',
                        'monto'  => $gasto['comision'] ?? 0,
                        'pago'   => $gasto['pago_comision'] ?? false
                    ],
                    'diesel' => [
                        'codigo' => 'GDI02 - Diesel',
                        'monto'  => $gasto['diesel'] ?? 0,
                        'pago'   => $gasto['pago_diesel'] ?? false
                    ],
                    'casetas' => [
                        'codigo' => 'GCP03 - Casetas / Peaje',
                        'monto'  => $gasto['casetas'] ?? 0,
                        'pago'   => $gasto['pago_casetas'] ?? false
                    ],
                ];

                foreach ($tipos as $tipo) {
                    if ($tipo['monto'] <= 0) {
                        continue;
                    }

                    if ($tipo['pago'] && empty($bank)) {
                        throw new \InvalidArgumentException('Ha seleccionado pago inmediato pero no indicó banco.');
                    }

                    $existeGasto = \App\Models\GastoImputacion::join('gastos', 'gastos.id', '=', 'gasto_imputaciones.gasto_id')
                        ->join('gasto_vinculos', 'gasto_vinculos.gasto_id', '=', 'gastos.id')
                        ->whereNull('gastos.deleted_at')
                        ->where('gastos.estatus', '!=', 'cancelado')
                        ->where('gasto_vinculos.tipo_vinculo', '=', 'cotizacion')
                        ->where('gasto_vinculos.vinculable_type', '=', Cotizaciones::class)
                        ->where('gasto_vinculos.vinculable_id', '=', $contenedor->id_cotizacion)
                        ->where('gasto_imputaciones.tipo_imputacion', '=', 'operador')
                        ->where('gastos.concepto', '=', $tipo['codigo'])
                        ->select('gasto_imputaciones.*', 'gasto_imputaciones.monto_imputado as cantidad', 'gastos.concepto as tipo', 'gastos.estatus as estatus')
                        ->first();

                    if (!$existeGasto) {
                        $nuevosGastos[] = [
                            "banco" => intval($bank),
                            "gasto" => $tipo['monto'],
                            "fechaAplicacion" => $fechaPago
                        ];

                        $datosGasto = [
                            "id_cotizacion" => $contenedor->id_cotizacion,
                            "id_banco"      => $tipo['pago'] ? intval($bank) : null,
                            "id_asignacion" => $asignacion?->id,
                            "id_operador"   => $asignacion?->id_operador,
                            "cantidad"      => $tipo['monto'],
                            "tipo"          => $tipo['codigo'],
                            "estatus"       => $tipo['pago'] ? 'Pagado' : 'Pago Pendiente',
                            "fecha_pago"    => $tipo['pago'] ? $fechaPago : null,
                            "pago_inmediato" => $tipo['pago'],
                            "created_at"    => now(),
                        ];

                        if ($tipo['pago']) {
                            if ($bank && $tipo['monto'] > 0) {
                                $validarSaldos = $this->bancosService->validarsaldoparacargo($idEmpresa, intval($bank), $fechaPago, $tipo['monto']);
                                if (!$validarSaldos["saldodisponible"]) {
                                    throw new SaldoInsuficienteException($validarSaldos["message"]);
                                }
                            }

                            $contenedoresAbonos = [
                                [
                                    'num_contenedor' => $numContenedor,
                                    'tipo' => $tipo['codigo'],
                                    'abono' => $tipo['monto']
                                ]
                            ];

                            $bancoDinero[] = [
                                "monto1"        => $tipo['monto'],
                                "metodo_pago1"  => 'Transferencia',
                                "descripcion"   => $tipo['codigo'] . " " . $numContenedor,
                                "id_banco1"     => $bank,
                                "contenedores"  => json_encode($contenedoresAbonos),
                                "tipo"          => 'Salida',
                                "fecha_pago"    => $fechaPago?->format('Y-m-d'),
                            ];

                            $gastoOp = GastosOperadores::create($datosGasto);
                            $this->registrarDesdeGastoOperador($gastoOp);

                            $dataBancoNuevo = [
                                'cuenta_bancaria_id' => intval($bank),
                                'tipo' => 'cargo',
                                'monto' => floatval($tipo['monto']),
                                'concepto' => $tipo['codigo'] . " " . $numContenedor,
                                'fecha_movimiento' => $fechaPago?->format('Y-m-d'),
                                'origen' => null,
                                'referencia' => 'GOP',
                                'detalles' => json_encode($contenedoresAbonos),
                                'referenciaable_id' => $gastoOp->id,
                                'referenciaable_type' => GastosOperadores::class,
                            ];

                            $this->bancosService->registrarMovimiento($dataBancoNuevo);
                        }
                    } else {
                        if ($existeGasto->cantidad != $tipo['monto']) {
                            $existeGasto->update(["cantidad" => $tipo['monto']]);
                        }
                        $this->registrarDesdeGastoOperador($existeGasto->fresh());
                    }
                }
            }

            if (!empty($bancoDinero)) {
                BancoDinero::insert($bancoDinero);
            }

            $gastosBancos = collect($nuevosGastos)->groupBy('banco')->map(fn ($items) => $items->sum('gasto'));
            foreach ($gastosBancos as $idBanco => $totalGasto) {
                if ($idBanco) {
                    Bancos::where('id', $idBanco)->update([
                        "saldo" => DB::raw("saldo - {$totalGasto}")
                    ]);
                }
            }
        });
    }


    /**
     * Obtiene los datos para la vista de Gastos Generales.
     */
    public function getGastosGeneralesIndexData(int $idEmpresa): array
    {
        $fecha = Carbon::now()->format('Y-m-d');
        $bancos = $this->bancosService->getCuentasOption($idEmpresa, $fecha, $fecha, true);
        $categorias = CategoriasGastos::orderBy('categoria')->get();
        $now = Carbon::now()->format('d.m.Y');
        $initDay = Carbon::now()->subDays(15)->format('d.m.Y');
        $equipos = Equipo::where('id_empresa', $idEmpresa)->get();

        $firstDay = Carbon::now()->startOfMonth();

        $planeaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
            ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
            ->where('asignaciones.fecha_inicio', '>=', $firstDay)
            ->where('asignaciones.id_empresa', $idEmpresa)
            ->where('cotizaciones.estatus', 'Aprobada')
            ->where('estatus_planeacion', 1)
            ->select('asignaciones.*', 'docum_cotizacion.num_contenedor', 'cotizaciones.id_cliente', 'cotizaciones.referencia_full', 'cotizaciones.tipo_viaje')
            ->get();

        $viajes = $planeaciones->map(function ($p) {
            $itemNumContenedor = $p->num_contenedor;

            if ($p->tipo_viaje === "Full") {
                $cotizacionFull = Cotizaciones::where('referencia_full', $p->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->first();

                if ($cotizacionFull) {
                    $contenedorSecundario = DocumCotizacion::where("id_cotizacion", $cotizacionFull->id)->first();
                    if ($contenedorSecundario) {
                        $itemNumContenedor .= " / " . $contenedorSecundario->num_contenedor;
                    }
                }
            }

            return (object) [
                'fecha_inicio' => $p->fecha_inicio,
                'fecha_fin' => $p->fecha_fin,
                'id_contenedor' => $p->id_contenedor,
                'id_cliente' => $p->id_cliente,
                'num_contenedor' => $itemNumContenedor,
            ];
        });

        return compact('bancos', 'categorias', 'now', 'initDay', 'equipos', 'viajes');
    }

    /**
     * Obtiene los gastos por pagar consolidados para GastosContenedoresController@getGxp.
     */
    public function getGastosPorPagarConsolidados(int $idEmpresa)
    {
        $gastosOperadores = Gasto::with(['vinculos.vinculable', 'categoria'])
            ->where('id_empresa', $idEmpresa)
            ->whereIn('tipo_gasto', ['operador', 'viaje'])
            ->where('estatus', '!=', 'Pagado')
            ->get()
            ->map(function ($g) {
                $vinculoAsignacion = $g->vinculos->firstWhere('tipo_vinculo', 'asignacion');
                $asignacion = $vinculoAsignacion ? $vinculoAsignacion->vinculable : null;
                $contenedor = $asignacion ? optional($asignacion->Contenedor)->num_contenedor : '';

                return [
                    'IdGasto' => $g->id,
                    'Descripcion' => $g->concepto,
                    'NumContenedor' => $contenedor,
                    'Monto' => $g->monto_total ?? 0,
                    'FechaGasto' => Carbon::parse($g->fecha_gasto)->format('Y-m-d'),
                    'FechaPago' => null,
                    'fecha_inicio' => optional($asignacion)->fecha_inicio,
                    'fecha_fin' => optional($asignacion)->fecha_fin,
                    'Origen' => 'Operador'
                ];
            });

        $gastosGenerales = DB::table('gasto_imputaciones')
            ->join('gastos', 'gastos.id', '=', 'gasto_imputaciones.gasto_id')
            ->leftJoin('categorias_gastos', 'categorias_gastos.id', '=', 'gastos.categoria_gasto_id')
            ->leftJoin('gasto_programaciones', 'gasto_programaciones.gasto_imputacion_id', '=', 'gasto_imputaciones.id')
            ->whereNull('gastos.deleted_at')
            ->where('gastos.id_empresa', $idEmpresa)
            ->where('gastos.estatus', '!=', 'pagado')
            ->select(
                'gastos.id as IdGasto',
                'gastos.concepto as NumContenedor',
                'categorias_gastos.categoria as Descripcion',
                'gasto_imputaciones.monto_imputado as Monto',
                'gastos.fecha_gasto as FechaGasto',
                'gasto_imputaciones.fecha_imputacion as FechaPago',
                'gasto_programaciones.fecha_programada as fecha_inicio',
                'gasto_programaciones.fecha_vencimiento as fecha_fin',
                DB::raw("'General' as Origen")
            )
            ->get();

        return collect($gastosOperadores)->merge($gastosGenerales);
    }
}
