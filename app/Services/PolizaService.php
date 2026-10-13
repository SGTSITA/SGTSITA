<?php

namespace App\Services;

use App\Models\Poliza;
use App\Models\PolizaMovimiento;
use App\Models\CatCuentaContable;
use App\Models\GuiaContabilizadora;
use App\Models\Bancos;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PolizaService
{
    /**
     * Crea una póliza contable con validación estricta de partida doble y cuentas afectables.
     *
     * @param array $data
     * @return Poliza
     * @throws InvalidArgumentException
     */
    public function crearPoliza(array $data): Poliza
    {
        return DB::transaction(function () use ($data) {
            $idEmpresa = $data['id_empresa'] ?? auth()->user()?->id_empresa;
            if (!$idEmpresa) {
                throw new InvalidArgumentException('El ID de la empresa es requerido para emitir una póliza.');
            }

            $tipo = $data['tipo'] ?? 'diario';
            $esApertura = !empty($data['es_apertura']) || $tipo === 'apertura';
            if ($tipo === 'apertura') {
                $tipo = 'diario';
            }

            if (!in_array($tipo, ['ingreso', 'egreso', 'diario'])) {
                throw new InvalidArgumentException("Tipo de póliza inválido: {$tipo}");
            }

            $fecha = !empty($data['fecha']) ? Carbon::parse($data['fecha'])->format('Y-m-d') : now()->format('Y-m-d');
            $concepto = trim($data['concepto'] ?? '');
            if (empty($concepto)) {
                throw new InvalidArgumentException('El concepto de la póliza es requerido.');
            }

            $movimientos = $data['movimientos'] ?? [];
            if (!is_array($movimientos) || count($movimientos) < 2) {
                throw new InvalidArgumentException('Una póliza requiere al menos 2 asientos (cargos y abonos por partida doble).');
            }

            // 1. Validar cuentas afectables y calcular totales
            $totalDebe = 0.00;
            $totalHaber = 0.00;
            $cuentasIds = array_unique(array_filter(array_column($movimientos, 'cuenta_contable_id')));

            $cuentas = CatCuentaContable::whereIn('id', $cuentasIds)
                ->get()
                ->keyBy('id');

            foreach ($movimientos as $index => $mov) {
                $cuentaId = $mov['cuenta_contable_id'] ?? null;
                if (!$cuentaId || !isset($cuentas[$cuentaId])) {
                    throw new InvalidArgumentException("La cuenta contable en el asiento #" . ($index + 1) . " no existe.");
                }

                $cuenta = $cuentas[$cuentaId];
                if (!$cuenta->es_afectable) {
                    throw new InvalidArgumentException("La cuenta {$cuenta->codigo} - {$cuenta->nombre} es de título/mayor acumulativa y NO permite movimientos directos (debe ser afectable).");
                }

                $debe = round((float) ($mov['debe'] ?? 0.00), 2);
                $haber = round((float) ($mov['haber'] ?? 0.00), 2);

                if ($debe < 0 || $haber < 0) {
                    throw new InvalidArgumentException("Los montos de debe y haber deben ser positivos o cero en el asiento #" . ($index + 1));
                }

                if ($debe == 0 && $haber == 0) {
                    throw new InvalidArgumentException("El asiento #" . ($index + 1) . " debe tener un monto en el Debe o en el Haber.");
                }

                $totalDebe += $debe;
                $totalHaber += $haber;
            }

            // 2. Validar Partida Doble estricta
            $totalDebe = round($totalDebe, 2);
            $totalHaber = round($totalHaber, 2);
            if (round(abs($totalDebe - $totalHaber), 2) !== 0.00) {
                throw new InvalidArgumentException("Póliza descuadrada: El total del Debe (\${$totalDebe}) no coincide con el total del Haber (\${$totalHaber}).");
            }

            // 3. Generar número consecutivo mensual
            $numeroFolio = $data['numero'] ?? $this->generarNumeroFolio($idEmpresa, $tipo, $fecha, $esApertura);

            // 4. Crear cabecera de póliza
            $poliza = Poliza::create([
                'id_empresa'   => $idEmpresa,
                'id_equipo'    => $data['id_equipo'] ?? null,
                'tipo'         => $tipo,
                'es_apertura'  => $esApertura,
                'numero'       => $numeroFolio,
                'fecha'        => $fecha,
                'concepto'     => $concepto,
                'origen_tipo'  => $data['origen_tipo'] ?? null,
                'origen_id'    => $data['origen_id'] ?? null,
                'estatus'      => $data['estatus'] ?? 'aplicada',
                'total_debe'   => $totalDebe,
                'total_haber'  => $totalHaber,
                'creado_por'   => $data['creado_por'] ?? auth()->id(),
            ]);

            // 5. Crear asientos
            foreach ($movimientos as $index => $mov) {
                PolizaMovimiento::create([
                    'poliza_id'          => $poliza->id,
                    'cuenta_contable_id' => $mov['cuenta_contable_id'],
                    'id_equipo'          => $mov['id_equipo'] ?? ($data['id_equipo'] ?? null),
                    'linea'              => $index + 1,
                    'referencia'         => $mov['referencia'] ?? null,
                    'concepto_detalle'   => $mov['concepto_detalle'] ?? null,
                    'debe'               => round((float) ($mov['debe'] ?? 0.00), 2),
                    'haber'              => round((float) ($mov['haber'] ?? 0.00), 2),
                ]);
            }

            return $poliza->load('movimientos.cuentaContable');
        });
    }

    /**
     * Resuelve los asientos de una póliza consultando la Guía Contabilizadora (Matriz de Conversión).
     *
     * @param string $evento
     * @param string|null $subtipo
     * @param int $idEmpresa
     * @param array $contexto
     * @return array
     */
    public function resolverAsientoPorEvento(string $evento, ?string $subtipo, int $idEmpresa, array $contexto = []): array
    {
        $reglaQuery = GuiaContabilizadora::paraEvento($evento, $idEmpresa);
        if ($subtipo) {
            $reglaQuery->where(function ($q) use ($subtipo) {
                $q->where('subtipo', $subtipo)->orWhereNull('subtipo');
            });
        }
        $regla = $reglaQuery->first();

        if (!$regla) {
            throw new InvalidArgumentException("No se encontró una regla en la Guía Contabilizadora para el evento '{$evento}'.");
        }

        $monto = round((float) ($contexto['monto'] ?? 0.00), 2);
        if ($monto <= 0) {
            throw new InvalidArgumentException("El monto de la transacción debe ser mayor a 0 para generar la póliza.");
        }

        // Resolver cuenta de cargo
        $cuentaCargoId = $regla->cuenta_cargo_id;
        // Resolver cuenta de abono
        $cuentaAbonoId = $regla->cuenta_abono_id;

        // Reglas dinámicas (ej. cuenta asignada a un banco específico)
        if ($regla->regla_dinamica === 'liquidacion_con_anticipo_y_banco') {
            $montoTotal = round((float) ($contexto['monto_total_utilidad'] ?? $monto), 2);
            $montoAnticipo = round((float) ($contexto['monto_anticipo_amortizado'] ?? 0.00), 2);
            $montoBanco = round((float) ($contexto['monto_pago_banco'] ?? max(0, $montoTotal - $montoAnticipo)), 2);

            $cuentaPasivoSocios = $regla->cuenta_cargo_id ?: CatCuentaContable::where('codigo', '2.1.9.1.002')->first()?->id;
            $cuentaAnticipo = $regla->cuenta_abono_id ?: CatCuentaContable::where('codigo', '1.1.2.3.003')->first()?->id;
            $cuentaBanco = $this->resolverCuentaBanco($contexto['cuenta_bancaria_id'] ?? null, $idEmpresa);

            if (!$cuentaPasivoSocios || !$cuentaAnticipo || !$cuentaBanco) {
                throw new InvalidArgumentException("No se pudieron resolver las cuentas contables para liquidación y amortización de anticipos.");
            }

            $concepto = $contexto['concepto'] ?? ($regla->descripcion_asiento ?: "Liquidación de socio con anticipos");
            $movimientos = [
                [
                    'cuenta_contable_id' => $cuentaPasivoSocios,
                    'referencia'         => $contexto['referencia'] ?? null,
                    'concepto_detalle'   => $concepto,
                    'debe'               => $montoTotal,
                    'haber'              => 0.00,
                    'id_equipo'          => $contexto['id_equipo'] ?? null,
                ]
            ];

            if ($montoAnticipo > 0) {
                $movimientos[] = [
                    'cuenta_contable_id' => $cuentaAnticipo,
                    'referencia'         => 'AMORTIZACION',
                    'concepto_detalle'   => 'Compensación de anticipo a socio',
                    'debe'               => 0.00,
                    'haber'              => $montoAnticipo,
                    'id_equipo'          => $contexto['id_equipo'] ?? null,
                ];
            }

            if ($montoBanco > 0) {
                $movimientos[] = [
                    'cuenta_contable_id' => $cuentaBanco,
                    'referencia'         => $contexto['referencia'] ?? null,
                    'concepto_detalle'   => 'Pago bancario remanente liquidación socio',
                    'debe'               => 0.00,
                    'haber'              => $montoBanco,
                    'id_equipo'          => $contexto['id_equipo'] ?? null,
                ];
            }

            return [
                'id_empresa'   => $idEmpresa,
                'id_equipo'    => $contexto['id_equipo'] ?? null,
                'tipo'         => 'egreso',
                'fecha'        => $contexto['fecha'] ?? now()->format('Y-m-d'),
                'concepto'     => $concepto,
                'origen_tipo'  => $contexto['origen_tipo'] ?? null,
                'origen_id'    => $contexto['origen_id'] ?? null,
                'movimientos'  => $movimientos,
            ];
        } elseif ($regla->regla_dinamica === 'cuenta_banco') {
            $cuentaBanco = $this->resolverCuentaBanco($contexto['cuenta_bancaria_id'] ?? null, $idEmpresa);
            if ($regla->tipo_poliza === 'ingreso') {
                $cuentaCargoId = $cuentaCargoId ?: $cuentaBanco;
            } elseif ($regla->tipo_poliza === 'egreso') {
                $cuentaAbonoId = $cuentaAbonoId ?: $cuentaBanco;
            }
        } elseif ($regla->regla_dinamica === 'cuenta_categoria_gasto_y_banco') {
            $cuentaCargoId = $this->resolverCuentaGasto($contexto['categoria_gasto_id'] ?? null, $idEmpresa, $contexto['concepto'] ?? null);
            $cuentaAbonoId = $this->resolverCuentaBanco($contexto['cuenta_bancaria_id'] ?? null, $idEmpresa);
        }

        if (!$cuentaCargoId || !$cuentaAbonoId) {
            throw new InvalidArgumentException("No fue posible resolver las cuentas de cargo y abono para el evento '{$evento}'.");
        }

        $concepto = $contexto['concepto'] ?? ($regla->descripcion_asiento ?: "Póliza automática: {$evento}");

        return [
            'id_empresa'   => $idEmpresa,
            'id_equipo'    => $contexto['id_equipo'] ?? null,
            'tipo'         => $regla->tipo_poliza,
            'fecha'        => $contexto['fecha'] ?? now()->format('Y-m-d'),
            'concepto'     => $concepto,
            'origen_tipo'  => $contexto['origen_tipo'] ?? null,
            'origen_id'    => $contexto['origen_id'] ?? null,
            'movimientos'  => [
                [
                    'cuenta_contable_id' => $cuentaCargoId,
                    'referencia'         => $contexto['referencia'] ?? null,
                    'concepto_detalle'   => $concepto,
                    'debe'               => $monto,
                    'haber'              => 0.00,
                    'id_equipo'          => $contexto['id_equipo'] ?? null,
                ],
                [
                    'cuenta_contable_id' => $cuentaAbonoId,
                    'referencia'         => $contexto['referencia'] ?? null,
                    'concepto_detalle'   => $concepto,
                    'debe'               => 0.00,
                    'haber'              => $monto,
                    'id_equipo'          => $contexto['id_equipo'] ?? null,
                ],
            ],
        ];
    }

    /**
     * Resuelve la subcuenta analítica de un banco por su ID o asigna una default de bancos.
     */
    public function resolverCuentaBanco(?int $bancoId, int $idEmpresa): int
    {
        if ($bancoId) {
            $banco = Bancos::find($bancoId);
            if ($banco) {
                $nombreBanco = strtoupper($banco->nombre_banco ?? $banco->nombre ?? $banco->banco ?? '');
                if (str_contains($nombreBanco, 'AZTECA')) {
                    $c = CatCuentaContable::where('codigo', '1.1.1.2.002')->first();
                    if ($c) return $c->id;
                } elseif (str_contains($nombreBanco, 'BANAMEX') || str_contains($nombreBanco, 'CITI')) {
                    $c = CatCuentaContable::where('codigo', '1.1.1.2.003')->first();
                    if ($c) return $c->id;
                } elseif (str_contains($nombreBanco, 'SANTANDER')) {
                    $c = CatCuentaContable::where('codigo', '1.1.1.2.004')->first();
                    if ($c) return $c->id;
                }
            }
        }

        // Default: BBVA Bancomer analítica o primera cuenta afectable de bancos
        $default = CatCuentaContable::where('codigo', '1.1.1.2.001')->first()
            ?: CatCuentaContable::where('codigo', 'like', '1.1.1.2%')->where('es_afectable', true)->first();

        if (!$default) {
            throw new InvalidArgumentException("No se encontró una subcuenta analítica afectable para Bancos (1.1.1.2).");
        }

        return $default->id;
    }

    /**
     * Resuelve la subcuenta analítica de gasto operativo.
     */
    public function resolverCuentaGasto(?int $categoriaGastoId, int $idEmpresa, ?string $concepto = null): int
    {
        $conceptoStr = strtoupper($concepto ?? '');

        if ($categoriaGastoId) {
            $cat = \App\Models\CategoriasGastos::find($categoriaGastoId);
            if ($cat) {
                $conceptoStr .= ' ' . strtoupper($cat->categoria);
            }
        }

        if (str_contains($conceptoStr, 'DIESEL') || str_contains($conceptoStr, 'COMBUSTIBLE') || str_contains($conceptoStr, 'GASOLINA')) {
            $c = CatCuentaContable::where('codigo', '5.1.1.1')->first();
            if ($c) return $c->id;
        } elseif (str_contains($conceptoStr, 'CASETA') || str_contains($conceptoStr, 'PEAJE') || str_contains($conceptoStr, 'TAG')) {
            $c = CatCuentaContable::where('codigo', '5.1.1.2')->first();
            if ($c) return $c->id;
        } elseif (str_contains($conceptoStr, 'MANIOBRA') || str_contains($conceptoStr, 'BURREO') || str_contains($conceptoStr, 'CARGA') || str_contains($conceptoStr, 'DESCARGA')) {
            $c = CatCuentaContable::where('codigo', '5.1.1.3')->first();
            if ($c) return $c->id;
        } elseif (str_contains($conceptoStr, 'TALLER') || str_contains($conceptoStr, 'MANTENIMIENTO') || str_contains($conceptoStr, 'LLANTA') || str_contains($conceptoStr, 'REFACCION')) {
            $c = CatCuentaContable::where('codigo', '5.1.2.1')->first();
            if ($c) return $c->id;
        }

        // Default: Gastos Administrativos y Generales (5.1.3.1)
        $gasto = CatCuentaContable::where('codigo', '5.1.3.1')->first()
            ?: CatCuentaContable::where('codigo', 'like', '5.1%')->where('es_afectable', true)->first();

        if (!$gasto) {
            throw new InvalidArgumentException("No se encontró una cuenta analítica afectable de Gastos de Funcionamiento (5.1).");
        }

        return $gasto->id;
    }

    /**
     * Genera el siguiente folio consecutivo mensual para una póliza.
     */
    public function generarNumeroFolio(int $idEmpresa, string $tipo, string $fecha, bool $esApertura = false): string
    {
        $dt = Carbon::parse($fecha);
        $periodo = $dt->format('Ym');
        $prefijo = $esApertura ? 'APE' : match ($tipo) {
            'ingreso' => 'ING',
            'egreso'  => 'EGR',
            default   => 'DIA',
        };

        $query = Poliza::where('id_empresa', $idEmpresa)
            ->whereYear('fecha', $dt->year);

        if ($esApertura) {
            $query->where('es_apertura', true);
        } else {
            $query->where('tipo', $tipo)
                  ->where('es_apertura', false)
                  ->whereMonth('fecha', $dt->month);
        }

        $ultimoNumero = $query->orderBy('id', 'desc')->value('numero');

        $consecutivo = 1;
        if ($ultimoNumero && preg_match('/-(\d+)$/', $ultimoNumero, $matches)) {
            $consecutivo = ((int) $matches[1]) + 1;
        }

        return sprintf('%s-%s-%04d', $prefijo, $periodo, $consecutivo);
    }

    /**
     * Guarda o actualiza la Póliza Inicial / Saldos Iniciales de una empresa a una fecha de corte.
     *
     * @param array $data ['id_empresa', 'fecha', 'concepto', 'movimientos' => [...]]
     * @return Poliza
     * @throws InvalidArgumentException
     */
    public function guardarPolizaApertura(array $data): Poliza
    {
        return DB::transaction(function () use ($data) {
            $idEmpresa = $data['id_empresa'] ?? auth()->user()?->id_empresa;
            if (!$idEmpresa) {
                throw new InvalidArgumentException('El ID de la empresa es requerido para la póliza de apertura.');
            }

            $fecha = !empty($data['fecha']) ? Carbon::parse($data['fecha'])->format('Y-m-d') : now()->format('Y-m-d');
            $anio = Carbon::parse($fecha)->year;
            $concepto = !empty($data['concepto']) ? trim($data['concepto']) : "Póliza de Apertura / Saldos Iniciales Ejercicio {$anio}";

            $polizaExistente = Poliza::where('id_empresa', $idEmpresa)
                ->where('es_apertura', true)
                ->whereYear('fecha', $anio)
                ->first();

            $data['id_empresa'] = $idEmpresa;
            $data['tipo'] = 'diario';
            $data['es_apertura'] = true;
            $data['fecha'] = $fecha;
            $data['concepto'] = $concepto;
            $data['origen_tipo'] = 'poliza_apertura';
            $data['origen_id'] = null;

            if ($polizaExistente) {
                $movimientos = $data['movimientos'] ?? [];
                if (!is_array($movimientos) || count($movimientos) < 2) {
                    throw new InvalidArgumentException('Una póliza requiere al menos 2 asientos (partida doble).');
                }

                $totalDebe = 0.00;
                $totalHaber = 0.00;
                $cuentasIds = array_unique(array_filter(array_column($movimientos, 'cuenta_contable_id')));
                $cuentas = CatCuentaContable::whereIn('id', $cuentasIds)->get()->keyBy('id');

                foreach ($movimientos as $index => $mov) {
                    $cuentaId = $mov['cuenta_contable_id'] ?? null;
                    if (!$cuentaId || !isset($cuentas[$cuentaId])) {
                        throw new InvalidArgumentException("La cuenta contable en el asiento #" . ($index + 1) . " no existe.");
                    }
                    $cuenta = $cuentas[$cuentaId];
                    if (!$cuenta->es_afectable) {
                        throw new InvalidArgumentException("La cuenta {$cuenta->codigo} - {$cuenta->nombre} debe ser afectable.");
                    }
                    $debe = round((float) ($mov['debe'] ?? 0.00), 2);
                    $haber = round((float) ($mov['haber'] ?? 0.00), 2);
                    $totalDebe += $debe;
                    $totalHaber += $haber;
                }

                $totalDebe = round($totalDebe, 2);
                $totalHaber = round($totalHaber, 2);
                if (round(abs($totalDebe - $totalHaber), 2) !== 0.00) {
                    throw new InvalidArgumentException("Póliza descuadrada: El total del Debe (\${$totalDebe}) no coincide con el total del Haber (\${$totalHaber}).");
                }

                $polizaExistente->movimientos()->delete();
                $polizaExistente->update([
                    'fecha' => $fecha,
                    'concepto' => $concepto,
                    'estatus' => 'aplicada',
                    'total_debe' => $totalDebe,
                    'total_haber' => $totalHaber,
                ]);

                foreach ($movimientos as $index => $mov) {
                    PolizaMovimiento::create([
                        'poliza_id' => $polizaExistente->id,
                        'cuenta_contable_id' => $mov['cuenta_contable_id'],
                        'id_equipo' => $mov['id_equipo'] ?? null,
                        'linea' => $index + 1,
                        'referencia' => $mov['referencia'] ?? 'SALDO_INICIAL',
                        'concepto_detalle' => $mov['concepto_detalle'] ?? $concepto,
                        'debe' => round((float) ($mov['debe'] ?? 0.00), 2),
                        'haber' => round((float) ($mov['haber'] ?? 0.00), 2),
                    ]);
                }

                return $polizaExistente->load('movimientos.cuentaContable');
            }

            return $this->crearPoliza($data);
        });
    }

    /**
     * Cancela una póliza contable.
     */
    public function cancelarPoliza(int $polizaId, ?string $motivo = null): Poliza
    {
        return DB::transaction(function () use ($polizaId, $motivo) {
            $poliza = Poliza::findOrFail($polizaId);
            $poliza->update([
                'estatus'  => 'cancelada',
                'concepto' => trim($poliza->concepto . ($motivo ? " (CANCELADA: {$motivo})" : " (CANCELADA)")),
            ]);
            return $poliza;
        });
    }

    /**
     * Contabiliza un gasto devengado (Cargo a Cuenta de Gasto, Abono a Proveedores CXP).
     */
    public function contabilizarGastoDevengado(\App\Models\Gasto $gasto): ?Poliza
    {
        return DB::transaction(function () use ($gasto) {
            $monto = round((float) ($gasto->monto_total ?? 0), 2);
            if ($monto <= 0 || $gasto->estatus === 'cancelado') {
                return null;
            }

            // Eliminar previa si existía para evitar duplicidad
            $polizaPrevia = Poliza::where('origen_tipo', 'gasto_devengado')
                ->where('origen_id', $gasto->id)
                ->first();
            if ($polizaPrevia) {
                $polizaPrevia->delete();
            }

            $idEmpresa = $gasto->id_empresa ?? 1;
            $cuentaGastoId = $this->resolverCuentaGasto($gasto->categoria_gasto_id, $idEmpresa, $gasto->concepto);

            $cuentaProveedores = CatCuentaContable::where('codigo', '2.1.1.2.001')->first()
                ?: CatCuentaContable::where('codigo', 'like', '2.1.1.2%')->where('es_afectable', true)->first();

            if (!$cuentaProveedores) {
                return null;
            }

            $fecha = !empty($gasto->fecha_gasto)
                ? Carbon::parse($gasto->fecha_gasto)->format('Y-m-d')
                : now()->format('Y-m-d');

            return $this->crearPoliza([
                'id_empresa'   => $idEmpresa,
                'id_equipo'    => $gasto->id_equipo,
                'tipo'         => 'diario',
                'es_apertura'  => false,
                'fecha'        => $fecha,
                'concepto'     => 'Provisión Gasto #' . $gasto->id . ' - ' . ($gasto->concepto ?? 'Gasto Operativo'),
                'origen_tipo'  => 'gasto_devengado',
                'origen_id'    => $gasto->id,
                'movimientos'  => [
                    [
                        'cuenta_contable_id' => $cuentaGastoId,
                        'id_equipo'          => $gasto->id_equipo,
                        'debe'               => $monto,
                        'haber'              => 0.00,
                        'referencia'         => 'GASTO_' . $gasto->id,
                        'concepto_detalle'   => $gasto->concepto ?? 'Gasto Devengado',
                    ],
                    [
                        'cuenta_contable_id' => $cuentaProveedores->id,
                        'id_equipo'          => $gasto->id_equipo,
                        'debe'               => 0.00,
                        'haber'              => $monto,
                        'referencia'         => 'CXP_PROV',
                        'concepto_detalle'   => 'Pasivo Proveedores / Gasto #' . $gasto->id,
                    ],
                ],
            ]);
        });
    }

    /**
     * Contabiliza el pago de un gasto (Cargo a Proveedores CXP, Abono a Bancos).
     */
    public function contabilizarGastoPago(\App\Models\GastoPago $pago): ?Poliza
    {
        return DB::transaction(function () use ($pago) {
            $monto = round((float) ($pago->monto ?? 0), 2);
            if ($monto <= 0) {
                return null;
            }

            $pago->loadMissing('gasto');
            $idEmpresa = $pago->gasto?->id_empresa ?? 1;

            // Eliminar previa si existía
            $polizaPrevia = Poliza::where('origen_tipo', 'gasto_pago')
                ->where('origen_id', $pago->id)
                ->first();
            if ($polizaPrevia) {
                $polizaPrevia->delete();
            }

            $cuentaProveedores = CatCuentaContable::where('codigo', '2.1.1.2.001')->first()
                ?: CatCuentaContable::where('codigo', 'like', '2.1.1.2%')->where('es_afectable', true)->first();

            $cuentaBancoId = $this->resolverCuentaBanco($pago->cuenta_bancaria_id, $idEmpresa);

            if (!$cuentaProveedores) {
                return null;
            }

            $fecha = !empty($pago->fecha_pago)
                ? Carbon::parse($pago->fecha_pago)->format('Y-m-d')
                : now()->format('Y-m-d');

            return $this->crearPoliza([
                'id_empresa'   => $idEmpresa,
                'id_equipo'    => $pago->gasto?->id_equipo,
                'tipo'         => 'egreso',
                'es_apertura'  => false,
                'fecha'        => $fecha,
                'concepto'     => 'Pago de Gasto #' . ($pago->gasto_id ?? 'N/A') . ' - Ref: ' . ($pago->referencia ?? 'Pago Bancario'),
                'origen_tipo'  => 'gasto_pago',
                'origen_id'    => $pago->id,
                'movimientos'  => [
                    [
                        'cuenta_contable_id' => $cuentaProveedores->id,
                        'id_equipo'          => $pago->gasto?->id_equipo,
                        'debe'               => $monto,
                        'haber'              => 0.00,
                        'referencia'         => 'CANC_CXP',
                        'concepto_detalle'   => 'Cancelación Pasivo Proveedor Gasto #' . $pago->gasto_id,
                    ],
                    [
                        'cuenta_contable_id' => $cuentaBancoId,
                        'id_equipo'          => $pago->gasto?->id_equipo,
                        'debe'               => 0.00,
                        'haber'              => $monto,
                        'referencia'         => 'PAGO_BANCO',
                        'concepto_detalle'   => 'Salida de Bancos por Pago Gasto #' . $pago->gasto_id,
                    ],
                ],
            ]);
        });
    }

    /**
     * Contabiliza un cobro a cliente de CxC (Cargo a Bancos, Abono a Clientes CXC).
     */
    public function contabilizarCobroCxc(\App\Models\CobroPagoCotizacion $cpc): ?Poliza
    {
        return DB::transaction(function () use ($cpc) {
            $monto = round((float) ($cpc->monto ?? 0), 2);
            if ($monto <= 0) {
                return null;
            }

            $cpc->loadMissing(['cotizacion.cliente', 'cobroPago']);
            $cotizacion = $cpc->cotizacion;
            $cobroPago = $cpc->cobroPago;

            $idEmpresa = $cotizacion?->id_empresa ?? 1;

            // Eliminar previa si existía
            $polizaPrevia = Poliza::where('origen_tipo', 'cobro_cxc')
                ->where('origen_id', $cpc->id)
                ->first();
            if ($polizaPrevia) {
                $polizaPrevia->delete();
            }

            $cuentaClientes = CatCuentaContable::where('codigo', '1.1.2.1.001')->first()
                ?: CatCuentaContable::where('codigo', 'like', '1.1.2.1%')->where('es_afectable', true)->first();

            $bancoId = $cobroPago?->id_banco ?? null;
            $cuentaBancoId = $this->resolverCuentaBanco($bancoId, $idEmpresa);

            if (!$cuentaClientes) {
                return null;
            }

            $fecha = !empty($cobroPago?->fecha_pago)
                ? Carbon::parse($cobroPago->fecha_pago)->format('Y-m-d')
                : now()->format('Y-m-d');

            return $this->crearPoliza([
                'id_empresa'   => $idEmpresa,
                'id_equipo'    => null,
                'tipo'         => 'ingreso',
                'es_apertura'  => false,
                'fecha'        => $fecha,
                'concepto'     => 'Cobro CXC Cotización #' . ($cotizacion?->id ?? 'N/A') . ' Cliente: ' . ($cotizacion?->cliente?->nombre ?? 'N/A'),
                'origen_tipo'  => 'cobro_cxc',
                'origen_id'    => $cpc->id,
                'movimientos'  => [
                    [
                        'cuenta_contable_id' => $cuentaBancoId,
                        'debe'               => $monto,
                        'haber'              => 0.00,
                        'referencia'         => 'DEP_BANCO',
                        'concepto_detalle'   => 'Ingreso en Bancos Cobro CXC Viaje #' . ($cotizacion?->id ?? ''),
                    ],
                    [
                        'cuenta_contable_id' => $cuentaClientes->id,
                        'debe'               => 0.00,
                        'haber'              => $monto,
                        'referencia'         => 'AMORT_CXC',
                        'concepto_detalle'   => 'Abono a CXC Cliente Viaje #' . ($cotizacion?->id ?? ''),
                    ],
                ],
            ]);
        });
    }

    /**
     * Contabiliza un préstamo u adelanto otorgado a un operador (Cargo a Préstamos Activo, Abono a Bancos).
     */
    public function contabilizarPrestamoOperador(\App\Models\Prestamo $prestamo): ?Poliza
    {
        return DB::transaction(function () use ($prestamo) {
            $monto = round((float) ($prestamo->cantidad ?? 0), 2);
            if ($monto <= 0) {
                return null;
            }

            $prestamo->loadMissing('operador');
            $idEmpresa = auth()->user()?->id_empresa ?? 1;

            // Eliminar previa si existía
            $polizaPrevia = Poliza::where('origen_tipo', 'prestamo_operador')
                ->where('origen_id', $prestamo->id)
                ->first();
            if ($polizaPrevia) {
                $polizaPrevia->delete();
            }

            $cuentaPrestamos = CatCuentaContable::where('codigo', '1.1.2.3.001')->first()
                ?: CatCuentaContable::where('codigo', 'like', '1.1.2.3%')->where('es_afectable', true)->first();

            $cuentaBancoId = $this->resolverCuentaBanco($prestamo->id_banco, $idEmpresa);

            if (!$cuentaPrestamos) {
                return null;
            }

            $fecha = !empty($prestamo->fecha_prestamo)
                ? Carbon::parse($prestamo->fecha_prestamo)->format('Y-m-d')
                : now()->format('Y-m-d');

            $tipoDesc = ($prestamo->tipo === \App\Models\Prestamo::TIPO_ADELANTO) ? 'Adelanto' : 'Préstamo';

            return $this->crearPoliza([
                'id_empresa'   => $idEmpresa,
                'id_equipo'    => null,
                'tipo'         => 'egreso',
                'es_apertura'  => false,
                'fecha'        => $fecha,
                'concepto'     => "{$tipoDesc} Otorgado a Operador: " . ($prestamo->operador?->nombre ?? 'N/A') . " (#{$prestamo->id})",
                'origen_tipo'  => 'prestamo_operador',
                'origen_id'    => $prestamo->id,
                'movimientos'  => [
                    [
                        'cuenta_contable_id' => $cuentaPrestamos->id,
                        'debe'               => $monto,
                        'haber'              => 0.00,
                        'referencia'         => 'DEUDOR_OPE',
                        'concepto_detalle'   => "{$tipoDesc} Operador " . ($prestamo->operador?->nombre ?? ''),
                    ],
                    [
                        'cuenta_contable_id' => $cuentaBancoId,
                        'debe'               => 0.00,
                        'haber'              => $monto,
                        'referencia'         => 'SALIDA_BANCO',
                        'concepto_detalle'   => "Salida de Bancos por {$tipoDesc} #" . $prestamo->id,
                    ],
                ],
            ]);
        });
    }

    /**
     * Contabiliza la provisión de CXP a proveedores de fletes subcontratados (exclusivo GC).
     */
    public function contabilizarProvisionCxpProveedor(\App\Models\Cotizaciones $cotizacion): ?Poliza
    {
        return DB::transaction(function () use ($cotizacion) {
            $cotizacion->loadMissing(['DocCotizacion.Asignaciones.Proveedor']);
            $asignacion = $cotizacion->DocCotizacion?->Asignaciones;

            if (!$asignacion || $asignacion->tipo_contrato !== 'Subcontratado') {
                return null;
            }

            $monto = round((float) ($asignacion->total_proveedor ?? 0), 2);
            if ($monto <= 0) {
                return null;
            }

            $idEmpresa = $cotizacion->id_empresa ?? 7;

            // Eliminar previa si existía
            $polizaPrevia = Poliza::where('origen_tipo', 'cotizacion_cxp')
                ->where('origen_id', $cotizacion->id)
                ->first();
            if ($polizaPrevia) {
                $polizaPrevia->delete();
            }

            $cuentaGasto = CatCuentaContable::where('codigo', '5.1.1.3')->first()
                ?: CatCuentaContable::where('codigo', '5.1.3.1')->first();

            $cuentaProveedores = CatCuentaContable::where('codigo', '2.1.1.2.001')->first()
                ?: CatCuentaContable::where('codigo', 'like', '2.1.1.2%')->where('es_afectable', true)->first();

            if (!$cuentaGasto || !$cuentaProveedores) {
                return null;
            }

            $fecha = !empty($cotizacion->fecha_entrega)
                ? Carbon::parse($cotizacion->fecha_entrega)->format('Y-m-d')
                : now()->format('Y-m-d');

            return $this->crearPoliza([
                'id_empresa'   => $idEmpresa,
                'id_equipo'    => null,
                'tipo'         => 'diario',
                'es_apertura'  => false,
                'fecha'        => $fecha,
                'concepto'     => 'Provisión CXP Flete Subcontratado Viaje #' . $cotizacion->id . ' Prov: ' . ($asignacion->Proveedor?->nombre ?? 'N/A'),
                'origen_tipo'  => 'cotizacion_cxp',
                'origen_id'    => $cotizacion->id,
                'movimientos'  => [
                    [
                        'cuenta_contable_id' => $cuentaGasto->id,
                        'debe'               => $monto,
                        'haber'              => 0.00,
                        'referencia'         => 'COSTO_SUBCONTRATO',
                        'concepto_detalle'   => 'Costo Flete Subcontratado Viaje #' . $cotizacion->id,
                    ],
                    [
                        'cuenta_contable_id' => $cuentaProveedores->id,
                        'debe'               => 0.00,
                        'haber'              => $monto,
                        'referencia'         => 'CXP_PROV_FLETE',
                        'concepto_detalle'   => 'Pasivo Proveedor Flete Viaje #' . $cotizacion->id,
                    ],
                ],
            ]);
        });
    }

    /**
     * Contabiliza el pago bancario a un proveedor de flete subcontratado (exclusivo GC).
     */
    public function contabilizarPagoCxpProveedor(\App\Models\CobroPagoCotizacion $cpc): ?Poliza
    {
        return DB::transaction(function () use ($cpc) {
            $monto = round((float) ($cpc->monto ?? 0), 2);
            if ($monto <= 0) {
                return null;
            }

            $cpc->loadMissing(['cotizacion', 'cobroPago']);
            $cotizacion = $cpc->cotizacion;
            $cobroPago = $cpc->cobroPago;

            $idEmpresa = $cotizacion?->id_empresa ?? 7;

            // Eliminar previa si existía
            $polizaPrevia = Poliza::where('origen_tipo', 'cxp_proveedor_pago')
                ->where('origen_id', $cpc->id)
                ->first();
            if ($polizaPrevia) {
                $polizaPrevia->delete();
            }

            $cuentaProveedores = CatCuentaContable::where('codigo', '2.1.1.2.001')->first()
                ?: CatCuentaContable::where('codigo', 'like', '2.1.1.2%')->where('es_afectable', true)->first();

            $bancoId = $cobroPago?->id_banco ?? null;
            $cuentaBancoId = $this->resolverCuentaBanco($bancoId, $idEmpresa);

            if (!$cuentaProveedores) {
                return null;
            }

            $fecha = !empty($cobroPago?->fecha_pago)
                ? Carbon::parse($cobroPago->fecha_pago)->format('Y-m-d')
                : now()->format('Y-m-d');

            return $this->crearPoliza([
                'id_empresa'   => $idEmpresa,
                'id_equipo'    => null,
                'tipo'         => 'egreso',
                'es_apertura'  => false,
                'fecha'        => $fecha,
                'concepto'     => 'Pago CXP Proveedor Flete Viaje #' . ($cotizacion?->id ?? 'N/A'),
                'origen_tipo'  => 'cxp_proveedor_pago',
                'origen_id'    => $cpc->id,
                'movimientos'  => [
                    [
                        'cuenta_contable_id' => $cuentaProveedores->id,
                        'debe'               => $monto,
                        'haber'              => 0.00,
                        'referencia'         => 'CANC_CXP_FLETE',
                        'concepto_detalle'   => 'Cancelación CXP Proveedor Flete Viaje #' . ($cotizacion?->id ?? ''),
                    ],
                    [
                        'cuenta_contable_id' => $cuentaBancoId,
                        'debe'               => 0.00,
                        'haber'              => $monto,
                        'referencia'         => 'SALIDA_BANCO',
                        'concepto_detalle'   => 'Pago Bancario a Proveedor Flete Viaje #' . ($cotizacion?->id ?? ''),
                    ],
                ],
            ]);
        });
    }
}
