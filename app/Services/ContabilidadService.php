<?php

namespace App\Services;

use App\Models\CatCuentaContable;
use App\Models\BalanceGeneralConceptoCuenta;
use App\Models\PolizaMovimiento;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ContabilidadService
{
    protected PolizaService $polizaService;

    public function __construct(PolizaService $polizaService)
    {
        $this->polizaService = $polizaService;
    }

    /**
     * Obtiene el árbol contable CONAC con cálculo de saldos y propagación recursiva ascendente.
     *
     * @param array|int $empresas ID o array de IDs de empresas (para consolidación)
     * @param int|null $idEquipo Filtro opcional por unidad / camión
     * @param string $fechaCorte Fecha de corte de saldos
     * @param string|null $fechaInicio Fecha inicial opcional (para acotar periodo)
     * @return Collection Cuentas con debe, haber, saldo_neto acumulado
     */
    public function obtenerArbolSaldos(array|int $empresas, ?int $idEquipo = null, string $fechaCorte = '', ?string $fechaInicio = null): Collection
    {
        $empresasIds = is_array($empresas) ? $empresas : [$empresas];
        $fechaCorte = !empty($fechaCorte) ? Carbon::parse($fechaCorte)->format('Y-m-d') : now()->format('Y-m-d');
        $fechaInicio = !empty($fechaInicio) ? Carbon::parse($fechaInicio)->format('Y-m-d') : null;

        // 1. Obtener catálogo aplicable (globales + específicas de las empresas seleccionadas)
        $cuentas = CatCuentaContable::where('activo', true)
            ->where(function ($q) use ($empresasIds) {
                $q->whereNull('id_empresa')
                  ->orWhereIn('id_empresa', $empresasIds);
            })
            ->orderBy('nivel', 'asc')
            ->orderBy('codigo', 'asc')
            ->get();

        // 2. Consultar movimientos agrupados por cuenta_contable_id
        $movQuery = DB::table('poliza_movimientos as pm')
            ->join('polizas as p', 'p.id', '=', 'pm.poliza_id')
            ->whereIn('p.id_empresa', $empresasIds)
            ->where('p.estatus', 'aplicada')
            ->where('p.fecha', '<=', $fechaCorte);

        if ($fechaInicio) {
            $movQuery->where(function ($q) use ($fechaInicio) {
                $q->where('p.fecha', '>=', $fechaInicio)
                  ->orWhere('p.es_apertura', 1);
            });
        }

        if ($idEquipo) {
            $movQuery->where(function ($q) use ($idEquipo) {
                $q->where('pm.id_equipo', $idEquipo)
                  ->orWhere('p.id_equipo', $idEquipo);
            });
        }

        $saldosDirectos = $movQuery->select([
            'pm.cuenta_contable_id',
            DB::raw('SUM(pm.debe) as total_debe'),
            DB::raw('SUM(pm.haber) as total_haber'),
        ])
        ->groupBy('pm.cuenta_contable_id')
        ->get()
        ->keyBy('cuenta_contable_id');

        // 3. Inicializar saldos en cada cuenta
        $cuentasDict = [];
        foreach ($cuentas as $c) {
            $mov = $saldosDirectos->get($c->id);
            $debe = $mov ? (float) $mov->total_debe : 0.00;
            $haber = $mov ? (float) $mov->total_haber : 0.00;

            // Para cuentas analíticas afectables, el saldo base es su movimiento directo
            $saldoBase = 0.00;
            if ($c->es_afectable) {
                $saldoBase = ($c->naturaleza === 'deudora') ? ($debe - $haber) : ($haber - $debe);
            }

            $cuentasDict[$c->id] = (object) [
                'id'               => $c->id,
                'codigo'           => $c->codigo,
                'nombre'           => $c->nombre,
                'nivel'            => (int) $c->nivel,
                'cuenta_padre_id'  => $c->cuenta_padre_id,
                'rubro'            => $c->rubro,
                'naturaleza'       => $c->naturaleza,
                'es_afectable'     => (bool) $c->es_afectable,
                'id_empresa'       => $c->id_empresa,
                'debe'             => $debe,
                'haber'            => $haber,
                'saldo_neto'       => $saldoBase,
                'hijos_ids'        => [],
            ];
        }

        // Registrar relaciones padre-hijo
        foreach ($cuentasDict as $item) {
            if ($item->cuenta_padre_id && isset($cuentasDict[$item->cuenta_padre_id])) {
                $cuentasDict[$item->cuenta_padre_id]->hijos_ids[] = $item->id;
            }
        }

        // 4. Algoritmo de Agregación Recursiva Ascendente
        // Procesamos de nivel 5 a nivel 1 en orden descendente
        for ($nivel = 5; $nivel >= 1; $nivel--) {
            foreach ($cuentasDict as $item) {
                if ($item->nivel === $nivel) {
                    // Si no es afectable (es cuenta de mayor acumulativa), su saldo es la suma de sus hijos
                    if (!$item->es_afectable && !empty($item->hijos_ids)) {
                        $sumaHijos = 0.00;
                        $debeHijos = 0.00;
                        $haberHijos = 0.00;
                        foreach ($item->hijos_ids as $hijoId) {
                            if (isset($cuentasDict[$hijoId])) {
                                $hijo = $cuentasDict[$hijoId];
                                $sumaHijos += $hijo->saldo_neto;
                                $debeHijos += $hijo->debe;
                                $haberHijos += $hijo->haber;
                            }
                        }
                        $item->saldo_neto = round($sumaHijos, 2);
                        $item->debe = round($debeHijos, 2);
                        $item->haber = round($haberHijos, 2);
                    }
                }
            }
        }

        return collect(array_values($cuentasDict));
    }

    /**
     * Calcula el Balance General y el Estado de Resultados siguiendo el formato del PDF (TP 002).
     *
     * @param array|int $empresas
     * @param int|null $idEquipo
     * @param string $fechaCorte
     * @return array
     */
    public function calcularBalanceGeneral(array|int $empresas, ?int $idEquipo = null, string $fechaCorte = ''): array
    {
        $empresasIds = is_array($empresas) ? $empresas : [$empresas];
        $empresaPrincipal = $empresasIds[0] ?? auth()->user()?->id_empresa;
        $fechaCorte = !empty($fechaCorte) ? Carbon::parse($fechaCorte)->format('Y-m-d') : now()->format('Y-m-d');

        // Fecha de inicio del ejercicio para el Estado de Resultados
        $anio = Carbon::parse($fechaCorte)->year;
        $fechaInicioEjercicio = "{$anio}-01-01";

        // Obtener árbol con saldos acumulados al corte
        $arbolAcumulado = $this->obtenerArbolSaldos($empresasIds, $idEquipo, $fechaCorte);
        // Obtener árbol del ejercicio actual para Estado de Resultados
        $arbolEjercicio = $this->obtenerArbolSaldos($empresasIds, $idEquipo, $fechaCorte, $fechaInicioEjercicio);

        // Obtener configuración de conceptos de balance
        $conceptos = BalanceGeneralConceptoCuenta::paraEmpresa($empresaPrincipal)->get();

        $activoItems = [];
        $pasivoItems = [];
        $patrimonioItems = [];
        $resultadosItems = [];

        $totalActivo = 0.00;
        $totalPasivo = 0.00;
        $totalPatrimonio = 0.00;

        // Variables del Estado de Resultados
        $ingresosFletes = 0.00;
        $interesesGanados = 0.00;
        $gastosTotales = 0.00;

        // 1. Calcular renglones de Estado de Resultados primero para determinar la Utilidad del Ejercicio
        foreach ($conceptos->where('seccion', 'estado_resultados') as $c) {
            $monto = 0.00;
            if ($c->cuenta_codigo_prefijo) {
                $prefijoLimpio = rtrim($c->cuenta_codigo_prefijo, '%');
                $monto = $this->sumarSaldoPorPrefijo($arbolEjercicio, $prefijoLimpio);
            }

            if (strtoupper($c->concepto) === 'INGRESOS') {
                $ingresosFletes = $monto;
            } elseif (strtoupper($c->concepto) === 'INTERESES') {
                $interesesGanados = $monto;
            } elseif (strtoupper($c->concepto) === 'GASTOS') {
                $gastosTotales = $monto;
            }
        }

        $utilidadEjercicio = round(($ingresosFletes + $interesesGanados) - $gastosTotales, 2);

        // Construir items de resultados
        foreach ($conceptos->where('seccion', 'estado_resultados') as $c) {
            $monto = 0.00;
            if ($c->es_calculado && strtoupper($c->concepto) === 'UTILIDAD') {
                $monto = $utilidadEjercicio;
            } elseif ($c->cuenta_codigo_prefijo) {
                $prefijoLimpio = rtrim($c->cuenta_codigo_prefijo, '%');
                $monto = $this->sumarSaldoPorPrefijo($arbolEjercicio, $prefijoLimpio);
            }

            $resultadosItems[] = [
                'id'           => $c->id,
                'concepto'     => $c->concepto,
                'prefijo'      => $c->cuenta_codigo_prefijo,
                'monto'        => $monto,
                'es_calculado' => $c->es_calculado,
                'orden'        => $c->orden,
            ];
        }

        // 2. Calcular ACTIVO
        foreach ($conceptos->where('seccion', 'activo') as $c) {
            $monto = 0.00;
            if ($c->cuenta_codigo_prefijo) {
                $prefijoLimpio = rtrim($c->cuenta_codigo_prefijo, '%');
                $monto = $this->sumarSaldoPorPrefijo($arbolAcumulado, $prefijoLimpio);
            }

            $totalActivo += $monto;
            $activoItems[] = [
                'id'           => $c->id,
                'concepto'     => $c->concepto,
                'prefijo'      => $c->cuenta_codigo_prefijo,
                'monto'        => $monto,
                'es_calculado' => $c->es_calculado,
                'orden'        => $c->orden,
            ];
        }

        // 3. Calcular PASIVO
        foreach ($conceptos->where('seccion', 'pasivo') as $c) {
            $monto = 0.00;
            if ($c->cuenta_codigo_prefijo) {
                $prefijoLimpio = rtrim($c->cuenta_codigo_prefijo, '%');
                $monto = $this->sumarSaldoPorPrefijo($arbolAcumulado, $prefijoLimpio);
            }

            $totalPasivo += $monto;
            $pasivoItems[] = [
                'id'           => $c->id,
                'concepto'     => $c->concepto,
                'prefijo'      => $c->cuenta_codigo_prefijo,
                'monto'        => $monto,
                'es_calculado' => $c->es_calculado,
                'orden'        => $c->orden,
            ];
        }

        // 4. Calcular PATRIMONIO
        foreach ($conceptos->where('seccion', 'patrimonio') as $c) {
            $monto = 0.00;
            if ($c->es_calculado && str_contains(strtolower($c->concepto), 'resultado del ejercicio')) {
                // Saldo inicial acumulado de apertura más la utilidad neta de operación del periodo
                $saldoApertura = $c->cuenta_codigo_prefijo ? $this->sumarSaldoPorPrefijo($arbolAcumulado, rtrim($c->cuenta_codigo_prefijo, '%')) : 0.00;
                $monto = $saldoApertura + $utilidadEjercicio;
            } elseif ($c->cuenta_codigo_prefijo) {
                $prefijoLimpio = rtrim($c->cuenta_codigo_prefijo, '%');
                $monto = $this->sumarSaldoPorPrefijo($arbolAcumulado, $prefijoLimpio);

                // Si es el concepto de Resultado de Ejercicios Anteriores (3.2.2) y no hay póliza explícita de cierre acumulada
                if (str_starts_with($prefijoLimpio, '3.2.2')) {
                    $fechaFinPrevio = Carbon::parse($fechaInicioEjercicio)->subDay()->format('Y-m-d');
                    $arbolPrevio = $this->obtenerArbolSaldos($empresasIds, $idEquipo, $fechaFinPrevio);
                    $ingresosPrevios = $this->sumarSaldoPorPrefijo($arbolPrevio, '4.1.1') + $this->sumarSaldoPorPrefijo($arbolPrevio, '4.3.1');
                    $gastosPrevios = $this->sumarSaldoPorPrefijo($arbolPrevio, '5.1');
                    $monto = round($monto + ($ingresosPrevios - $gastosPrevios), 2);
                }
            }

            $totalPatrimonio += $monto;
            $patrimonioItems[] = [
                'id'           => $c->id,
                'concepto'     => $c->concepto,
                'prefijo'      => $c->cuenta_codigo_prefijo,
                'monto'        => $monto,
                'es_calculado' => $c->es_calculado,
                'orden'        => $c->orden,
            ];
        }

        $totalPasivoYPatrimonio = round($totalPasivo + $totalPatrimonio, 2);
        $totalActivo = round($totalActivo, 2);
        $diferencia = round($totalActivo - $totalPasivoYPatrimonio, 2);
        $cuadrado = abs($diferencia) < 0.01;

        return [
            'fecha_corte'            => $fechaCorte,
            'fecha_inicio_ejercicio' => $fechaInicioEjercicio,
            'empresas_ids'           => $empresasIds,
            'id_equipo'              => $idEquipo,
            'activo'                 => [
                'items' => $activoItems,
                'total' => $totalActivo,
            ],
            'pasivo'                 => [
                'items' => $pasivoItems,
                'total' => $totalPasivo,
            ],
            'patrimonio'             => [
                'items' => $patrimonioItems,
                'total' => $totalPatrimonio,
            ],
            'total_pasivo_patrimonio' => $totalPasivoYPatrimonio,
            'estado_resultados'      => [
                'items'              => $resultadosItems,
                'ingresos_fletes'    => $ingresosFletes,
                'intereses_ganados'  => $interesesGanados,
                'gastos_totales'     => $gastosTotales,
                'utilidad_neta'      => $utilidadEjercicio,
            ],
            'cuadrado'               => $cuadrado,
            'diferencia'             => $diferencia,
        ];
    }

    /**
     * Calcula la Balanza de Comprobación CONAC (BC-03).
     *
     * @param array|int $empresas
     * @param int|null $idEquipo
     * @param string $fechaCorte
     * @param string|null $fechaInicio
     * @return array
     */
    public function calcularBalanzaComprobacion(array|int $empresas, ?int $idEquipo = null, string $fechaCorte = '', ?string $fechaInicio = null): array
    {
        $empresasIds = is_array($empresas) ? $empresas : [$empresas];
        $fechaCorte = !empty($fechaCorte) ? Carbon::parse($fechaCorte)->format('Y-m-d') : now()->format('Y-m-d');
        $anio = Carbon::parse($fechaCorte)->year;
        $fechaInicio = !empty($fechaInicio) ? Carbon::parse($fechaInicio)->format('Y-m-d') : "{$anio}-01-01";

        // 1. Catálogo aplicable
        $cuentas = CatCuentaContable::where('activo', true)
            ->where(function ($q) use ($empresasIds) {
                $q->whereNull('id_empresa')->orWhereIn('id_empresa', $empresasIds);
            })
            ->orderBy('codigo', 'asc')
            ->get();

        // 2. Movimientos Iniciales (< fechaInicio o es_apertura = 1)
        $movsInicialesQuery = DB::table('poliza_movimientos as pm')
            ->join('polizas as p', 'p.id', '=', 'pm.poliza_id')
            ->whereIn('p.id_empresa', $empresasIds)
            ->where('p.estatus', 'aplicada')
            ->where(function ($q) use ($fechaInicio) {
                $q->where('p.fecha', '<', $fechaInicio)->orWhere('p.es_apertura', 1);
            });

        if ($idEquipo) {
            $movsInicialesQuery->where(function ($q) use ($idEquipo) {
                $q->where('pm.id_equipo', $idEquipo)->orWhere('p.id_equipo', $idEquipo);
            });
        }

        $movsIniciales = $movsInicialesQuery->select(
            'pm.cuenta_contable_id',
            DB::raw('SUM(pm.debe) as debe'),
            DB::raw('SUM(pm.haber) as haber')
        )
        ->groupBy('pm.cuenta_contable_id')
        ->get()
        ->keyBy('cuenta_contable_id');

        // 3. Movimientos del Periodo (>= fechaInicio y <= fechaCorte y es_apertura = 0)
        $movsPeriodoQuery = DB::table('poliza_movimientos as pm')
            ->join('polizas as p', 'p.id', '=', 'pm.poliza_id')
            ->whereIn('p.id_empresa', $empresasIds)
            ->where('p.estatus', 'aplicada')
            ->where('p.fecha', '>=', $fechaInicio)
            ->where('p.fecha', '<=', $fechaCorte)
            ->where('p.es_apertura', 0);

        if ($idEquipo) {
            $movsPeriodoQuery->where(function ($q) use ($idEquipo) {
                $q->where('pm.id_equipo', $idEquipo)->orWhere('p.id_equipo', $idEquipo);
            });
        }

        $movsPeriodo = $movsPeriodoQuery->select(
            'pm.cuenta_contable_id',
            DB::raw('SUM(pm.debe) as debe'),
            DB::raw('SUM(pm.haber) as haber')
        )
        ->groupBy('pm.cuenta_contable_id')
        ->get()
        ->keyBy('cuenta_contable_id');

        $filas = [];
        $totIniDeudor = 0.00;
        $totIniAcreedor = 0.00;
        $totDebe = 0.00;
        $totHaber = 0.00;
        $totFinDeudor = 0.00;
        $totFinAcreedor = 0.00;

        foreach ($cuentas as $c) {
            if (!$c->es_afectable) {
                continue;
            }

            $ini = $movsIniciales->get($c->id);
            $iniDebe = $ini ? (float) $ini->debe : 0.00;
            $iniHaber = $ini ? (float) $ini->haber : 0.00;
            $saldoIni = ($c->naturaleza === 'deudora') ? ($iniDebe - $iniHaber) : ($iniHaber - $iniDebe);

            $per = $movsPeriodo->get($c->id);
            $debe = $per ? (float) $per->debe : 0.00;
            $haber = $per ? (float) $per->haber : 0.00;

            $saldoFin = ($c->naturaleza === 'deudora') ? ($saldoIni + $debe - $haber) : ($saldoIni + $haber - $debe);

            if (abs($saldoIni) < 0.001 && abs($debe) < 0.001 && abs($haber) < 0.001 && abs($saldoFin) < 0.001) {
                continue;
            }

            $saldoIniDeudor = ($c->naturaleza === 'deudora' && $saldoIni > 0) ? $saldoIni : (($c->naturaleza === 'acreedora' && $saldoIni < 0) ? abs($saldoIni) : 0.00);
            $saldoIniAcreedor = ($c->naturaleza === 'acreedora' && $saldoIni > 0) ? $saldoIni : (($c->naturaleza === 'deudora' && $saldoIni < 0) ? abs($saldoIni) : 0.00);

            $saldoFinDeudor = ($c->naturaleza === 'deudora' && $saldoFin > 0) ? $saldoFin : (($c->naturaleza === 'acreedora' && $saldoFin < 0) ? abs($saldoFin) : 0.00);
            $saldoFinAcreedor = ($c->naturaleza === 'acreedora' && $saldoFin > 0) ? $saldoFin : (($c->naturaleza === 'deudora' && $saldoFin < 0) ? abs($saldoFin) : 0.00);

            $totIniDeudor += $saldoIniDeudor;
            $totIniAcreedor += $saldoIniAcreedor;
            $totDebe += $debe;
            $totHaber += $haber;
            $totFinDeudor += $saldoFinDeudor;
            $totFinAcreedor += $saldoFinAcreedor;

            $filas[] = [
                'id'                 => $c->id,
                'codigo'             => $c->codigo,
                'nombre'             => $c->nombre,
                'naturaleza'         => $c->naturaleza,
                'nivel'              => $c->nivel,
                'rubro'              => $c->rubro,
                'saldo_ini'          => round($saldoIni, 2),
                'saldo_ini_deudor'   => round($saldoIniDeudor, 2),
                'saldo_ini_acreedor' => round($saldoIniAcreedor, 2),
                'debe'               => round($debe, 2),
                'haber'              => round($haber, 2),
                'saldo_fin'          => round($saldoFin, 2),
                'saldo_fin_deudor'   => round($saldoFinDeudor, 2),
                'saldo_fin_acreedor' => round($saldoFinAcreedor, 2),
            ];
        }

        $cuadrado = abs($totDebe - $totHaber) < 0.01;

        return [
            'fecha_corte'            => $fechaCorte,
            'fecha_inicio'           => $fechaInicio,
            'empresas_ids'           => $empresasIds,
            'id_equipo'              => $idEquipo,
            'cuentas'                => $filas,
            'totales'                => [
                'saldo_ini_deudor'   => round($totIniDeudor, 2),
                'saldo_ini_acreedor' => round($totIniAcreedor, 2),
                'debe'               => round($totDebe, 2),
                'haber'              => round($totHaber, 2),
                'saldo_fin_deudor'   => round($totFinDeudor, 2),
                'saldo_fin_acreedor' => round($totFinAcreedor, 2),
            ],
            'cuadrado'               => $cuadrado,
            'diferencia_movimientos' => round(abs($totDebe - $totHaber), 2),
        ];
    }

    /**
     * Suma los saldos netos de las cuentas que comienzan con el prefijo dado.
     * Toma el saldo de la cuenta de mayor más alta que coincida para no duplicar sumas.
     */
    public function sumarSaldoPorPrefijo(Collection $arbol, string $prefijo): float
    {
        // 1. Si existe una cuenta exacta con ese código, usamos su saldo neto consolidado
        $cuentaExacta = $arbol->firstWhere('codigo', $prefijo);
        if ($cuentaExacta) {
            return (float) $cuentaExacta->saldo_neto;
        }

        // 2. Si es un prefijo (ej. 1.1.2.3.001), sumamos las hojas afectables que inicien con ese prefijo
        return (float) $arbol
            ->filter(fn ($c) => str_starts_with($c->codigo, $prefijo) && $c->es_afectable)
            ->sum('saldo_neto');
    }

    /**
     * Obtiene la póliza de apertura registrada para una empresa en un ejercicio/año.
     */
    public function obtenerPolizaApertura(int $idEmpresa, ?int $anio = null): ?\App\Models\Poliza
    {
        $anio = $anio ?: now()->year;
        return \App\Models\Poliza::with(['movimientos.cuentaContable'])
            ->where('id_empresa', $idEmpresa)
            ->where('es_apertura', true)
            ->whereYear('fecha', $anio)
            ->first();
    }

    /**
     * Obtiene el desglose detallado de movimientos contables que integran un concepto del Balance o Estado de Resultados.
     *
     * @param int $conceptoId ID del concepto en balance_general_concepto_cuentas
     * @param array|int $empresas
     * @param int|null $idEquipo
     * @param string $fechaCorte
     * @return array
     */
    public function obtenerDetalleConcepto(int $conceptoId, array|int $empresas, ?int $idEquipo = null, string $fechaCorte = ''): array
    {
        $concepto = BalanceGeneralConceptoCuenta::findOrFail($conceptoId);
        $empresasIds = is_array($empresas) ? $empresas : [$empresas];
        $fechaCorte = !empty($fechaCorte) ? Carbon::parse($fechaCorte)->format('Y-m-d') : now()->format('Y-m-d');
        $anio = Carbon::parse($fechaCorte)->year;
        $fechaInicioEjercicio = "{$anio}-01-01";

        $prefijo = $concepto->cuenta_codigo_prefijo ? rtrim($concepto->cuenta_codigo_prefijo, '%') : '';

        $movQuery = DB::table('poliza_movimientos as pm')
            ->join('polizas as p', 'p.id', '=', 'pm.poliza_id')
            ->join('cat_cuentas_contables as c', 'c.id', '=', 'pm.cuenta_contable_id')
            ->whereIn('p.id_empresa', $empresasIds)
            ->where('p.estatus', 'aplicada')
            ->where('p.fecha', '<=', $fechaCorte);

        if ($idEquipo) {
            $movQuery->where(function ($q) use ($idEquipo) {
                $q->where('pm.id_equipo', $idEquipo)
                  ->orWhere('p.id_equipo', $idEquipo);
            });
        }

        // Filtro según sección
        if ($concepto->seccion === 'estado_resultados') {
            $movQuery->where(function ($q) use ($fechaInicioEjercicio) {
                $q->where('p.fecha', '>=', $fechaInicioEjercicio)
                  ->orWhere('p.es_apertura', 1);
            });
            if ($prefijo) {
                $movQuery->where('c.codigo', 'like', "{$prefijo}%");
            }
        } elseif ($concepto->seccion === 'patrimonio' && str_starts_with($prefijo, '3.2.2')) {
            // Resultado de Ejercicios Anteriores:
            // Si hay movimientos en 3.2.2 los muestra, de lo contrario muestra los resultados acumulados de ejercicios anteriores
            $existe322 = (clone $movQuery)->where('c.codigo', 'like', "{$prefijo}%")->exists();
            if ($existe322) {
                $movQuery->where('c.codigo', 'like', "{$prefijo}%");
            } else {
                $movQuery->where('p.fecha', '<', $fechaInicioEjercicio)
                         ->where(function($q) {
                             $q->where('c.codigo', 'like', '4%')
                               ->orWhere('c.codigo', 'like', '5%');
                         });
            }
        } elseif ($concepto->seccion === 'patrimonio' && $concepto->es_calculado && str_contains(strtolower($concepto->concepto), 'resultado del ejercicio')) {
            // Resultado del ejercicio actual: ingresos 4% y gastos 5% del ejercicio
            $movQuery->where('p.fecha', '>=', $fechaInicioEjercicio)
                     ->where(function($q) {
                         $q->where('c.codigo', 'like', '4%')
                           ->orWhere('c.codigo', 'like', '5%');
                     });
        } else {
            // Activo o Pasivo estándar
            if ($prefijo) {
                $movQuery->where('c.codigo', 'like', "{$prefijo}%");
            }
        }

        $movimientosRaw = $movQuery->select([
            'pm.id as movimiento_id',
            'pm.debe',
            'pm.haber',
            'pm.referencia',
            'pm.concepto_detalle',
            'p.id as poliza_id',
            'p.numero as poliza_numero',
            'p.tipo as poliza_tipo',
            'p.fecha as poliza_fecha',
            'p.concepto as poliza_concepto',
            'p.origen_tipo as poliza_origen_tipo',
            'p.origen_id as poliza_origen_id',
            'c.codigo as cuenta_codigo',
            'c.nombre as cuenta_nombre',
            'c.naturaleza as cuenta_naturaleza',
        ])
        ->orderBy('p.fecha', 'asc')
        ->orderBy('p.id', 'asc')
        ->orderBy('pm.id', 'asc')
        ->get();

        $mapaContenedores = $this->resolverContenedoresPorPolizas($movimientosRaw);

        $totalDebe = 0.00;
        $totalHaber = 0.00;
        $saldoAcumulado = 0.00;
        $listaMovimientos = [];

        foreach ($movimientosRaw as $m) {
            $debe = (float) $m->debe;
            $haber = (float) $m->haber;
            $totalDebe += $debe;
            $totalHaber += $haber;

            // Para saldo acumulado: si la cuenta o sección es deudora (activo, gastos) suma debe y resta haber.
            // Si es acreedora (pasivo, ingresos, patrimonio) suma haber y resta debe.
            if ($concepto->seccion === 'activo' || str_starts_with($m->cuenta_codigo, '5.')) {
                $saldoAcumulado += ($debe - $haber);
            } else {
                $saldoAcumulado += ($haber - $debe);
            }

            $contenedor = null;
            if (!empty($m->referencia) && preg_match('/[A-Z]{4}\d{7}/', $m->referencia)) {
                $contenedor = $m->referencia;
            } else {
                $contenedor = $mapaContenedores[$m->poliza_id] ?? null;
            }

            $referenciaFinal = $m->referencia;
            if ($contenedor) {
                if (empty($referenciaFinal)) {
                    $referenciaFinal = $contenedor;
                } elseif (!str_contains($referenciaFinal, $contenedor)) {
                    $referenciaFinal = "{$referenciaFinal} [{$contenedor}]";
                }
            }

            $listaMovimientos[] = [
                'id'              => $m->movimiento_id,
                'fecha'           => $m->poliza_fecha,
                'fecha_formato'   => Carbon::parse($m->poliza_fecha)->format('d/m/Y'),
                'poliza_id'       => $m->poliza_id,
                'poliza_numero'   => $m->poliza_numero,
                'poliza_tipo'     => $m->poliza_tipo,
                'cuenta_codigo'   => $m->cuenta_codigo,
                'cuenta_nombre'   => $m->cuenta_nombre,
                'concepto'        => $m->concepto_detalle ?: $m->poliza_concepto,
                'referencia'      => $referenciaFinal,
                'contenedor'      => $contenedor,
                'debe'            => $debe,
                'haber'           => $haber,
                'saldo_acumulado' => round($saldoAcumulado, 2),
            ];
        }

        return [
            'concepto_id'  => $concepto->id,
            'concepto'     => $concepto->concepto,
            'seccion'      => $concepto->seccion,
            'prefijo'      => $prefijo,
            'total_debe'   => round($totalDebe, 2),
            'total_haber'  => round($totalHaber, 2),
            'saldo_neto'   => round($saldoAcumulado, 2),
            'conteo'       => count($listaMovimientos),
            'movimientos'  => $listaMovimientos,
        ];
    }

    /**
     * Resuelve los números de contenedor vinculados a un conjunto de movimientos contables (DocumCotizacion).
     *
     * @param \Illuminate\Support\Collection $movimientosRaw
     * @return array<int, string> Mapa [poliza_id => num_contenedor]
     */
    protected function resolverContenedoresPorPolizas($movimientosRaw): array
    {
        $mapa = [];
        if ($movimientosRaw->isEmpty()) {
            return $mapa;
        }

        $cotizacionPolizas = [];
        $cpcPolizas = [];
        $gastoPolizas = [];
        $bancoMovPolizas = [];
        $pagoPrestamoPolizas = [];

        foreach ($movimientosRaw as $m) {
            $pId = $m->poliza_id;
            $tipo = $m->poliza_origen_tipo ?? null;
            $origenId = $m->poliza_origen_id ?? null;

            if (!$tipo || !$origenId) {
                continue;
            }

            if (in_array($tipo, ['cotizacion', 'cotizacion_cxp', 'cotizacion_cobro'])) {
                $cotizacionPolizas[$pId] = (int) $origenId;
            } elseif (in_array($tipo, ['cobro_cxc', 'cxp_proveedor_pago'])) {
                $cpcPolizas[$pId] = (int) $origenId;
            } elseif (in_array($tipo, ['gasto_devengado', 'gasto_pago'])) {
                $gastoPolizas[$pId] = (int) $origenId;
            } elseif ($tipo === 'banco_movimiento') {
                $bancoMovPolizas[$pId] = (int) $origenId;
            } elseif ($tipo === 'pago_prestamo_operador') {
                $pagoPrestamoPolizas[$pId] = (int) $origenId;
            }
        }

        // 1. Cotizaciones directas
        if (!empty($cotizacionPolizas)) {
            $cotIds = array_values(array_unique($cotizacionPolizas));
            $cots = \App\Models\DocumCotizacion::whereIn('id_cotizacion', $cotIds)
                ->whereNotNull('num_contenedor')
                ->where('num_contenedor', '!=', '')
                ->pluck('num_contenedor', 'id_cotizacion');

            foreach ($cotizacionPolizas as $pId => $cId) {
                if (isset($cots[$cId])) {
                    $mapa[$pId] = $cots[$cId];
                }
            }
        }

        // 2. Cobros / Pagos de Cotizaciones (CobroPagoCotizacion)
        if (!empty($cpcPolizas)) {
            $cpcIds = array_values(array_unique($cpcPolizas));
            $cpcs = \App\Models\CobroPagoCotizacion::whereIn('id', $cpcIds)->pluck('cotizacion_id', 'id');
            $cotIdsFromCpc = $cpcs->values()->unique()->filter()->toArray();

            if (!empty($cotIdsFromCpc)) {
                $cotsCpc = \App\Models\DocumCotizacion::whereIn('id_cotizacion', $cotIdsFromCpc)
                    ->whereNotNull('num_contenedor')
                    ->where('num_contenedor', '!=', '')
                    ->pluck('num_contenedor', 'id_cotizacion');

                foreach ($cpcPolizas as $pId => $cpcId) {
                    $cId = $cpcs[$cpcId] ?? null;
                    if ($cId && isset($cotsCpc[$cId])) {
                        $mapa[$pId] = $cotsCpc[$cId];
                    }
                }
            }
        }

        // 3. Gastos (GastoDevengado / GastoPago)
        if (!empty($gastoPolizas)) {
            $gastoIds = array_values(array_unique($gastoPolizas));
            $vinculos = \App\Models\GastoVinculo::whereIn('gasto_id', $gastoIds)->get();

            $docIds = $vinculos->where('vinculable_type', 'App\Models\DocumCotizacion')->pluck('vinculable_id')->filter()->toArray();
            $cotIdsGasto = $vinculos->where('vinculable_type', 'App\Models\Cotizaciones')->pluck('vinculable_id')->filter()->toArray();
            $asigIdsGasto = $vinculos->where('vinculable_type', 'App\Models\Asignaciones')->pluck('vinculable_id')->filter()->toArray();

            $mapDocGasto = !empty($docIds) ? \App\Models\DocumCotizacion::whereIn('id', $docIds)->pluck('num_contenedor', 'id') : collect();
            $mapCotGasto = !empty($cotIdsGasto) ? \App\Models\DocumCotizacion::whereIn('id_cotizacion', $cotIdsGasto)->pluck('num_contenedor', 'id_cotizacion') : collect();
            $mapAsigCont = !empty($asigIdsGasto) ? \App\Models\Asignaciones::whereIn('id', $asigIdsGasto)->with('Contenedor')->get()->keyBy('id') : collect();

            $gastoToCont = [];
            foreach ($vinculos as $v) {
                if ($v->vinculable_type === 'App\Models\DocumCotizacion' && isset($mapDocGasto[$v->vinculable_id])) {
                    $gastoToCont[$v->gasto_id] = $mapDocGasto[$v->vinculable_id];
                } elseif ($v->vinculable_type === 'App\Models\Cotizaciones' && isset($mapCotGasto[$v->vinculable_id])) {
                    $gastoToCont[$v->gasto_id] = $mapCotGasto[$v->vinculable_id];
                } elseif ($v->vinculable_type === 'App\Models\Asignaciones' && isset($mapAsigCont[$v->vinculable_id])) {
                    $contNum = $mapAsigCont[$v->vinculable_id]?->Contenedor?->num_contenedor;
                    if ($contNum) {
                        $gastoToCont[$v->gasto_id] = $contNum;
                    }
                }
            }

            foreach ($gastoPolizas as $pId => $gId) {
                if (isset($gastoToCont[$gId])) {
                    $mapa[$pId] = $gastoToCont[$gId];
                }
            }
        }

        // 4. Movimientos Bancarios (CatBancoCuentasMovimientos)
        if (!empty($bancoMovPolizas)) {
            $movIds = array_values(array_unique($bancoMovPolizas));
            $movs = \App\Models\CatBancoCuentasMovimientos::whereIn('id', $movIds)->get()->keyBy('id');

            // Asignaciones
            $asigIds = $movs->where('referenciaable_type', 'App\Models\Asignaciones')->pluck('referenciaable_id')->filter()->toArray();
            $asigs = !empty($asigIds) ? \App\Models\Asignaciones::whereIn('id', $asigIds)->get()->keyBy('id') : collect();
            $asigDocIds = $asigs->pluck('id_contenedor')->filter()->toArray();
            $mapAsigDocs = !empty($asigDocIds) ? \App\Models\DocumCotizacion::whereIn('id', $asigDocIds)->pluck('num_contenedor', 'id') : collect();

            // CobroPago
            $cpIds = $movs->where('referenciaable_type', 'App\Models\CobroPago')->pluck('referenciaable_id')->filter()->toArray();
            $cpCots = !empty($cpIds) ? \App\Models\CobroPagoCotizacion::whereIn('cobro_pago_id', $cpIds)->pluck('cotizacion_id', 'cobro_pago_id') : collect();
            $mapCpDocs = !empty($cpCots) ? \App\Models\DocumCotizacion::whereIn('id_cotizacion', $cpCots->values())->pluck('num_contenedor', 'id_cotizacion') : collect();

            // Cotizaciones directas
            $bancoCotIds = $movs->where('referenciaable_type', 'App\Models\Cotizaciones')->pluck('referenciaable_id')->filter()->toArray();
            $mapBancoCotDocs = !empty($bancoCotIds) ? \App\Models\DocumCotizacion::whereIn('id_cotizacion', $bancoCotIds)->pluck('num_contenedor', 'id_cotizacion') : collect();

            // GastosOperadores
            $goIds = $movs->where('referenciaable_type', 'App\Models\GastosOperadores')->pluck('referenciaable_id')->filter()->toArray();
            $gos = !empty($goIds) ? \App\Models\GastosOperadores::whereIn('id', $goIds)->get()->keyBy('id') : collect();
            $goCotIds = $gos->pluck('id_cotizacion')->filter()->toArray();
            $mapGoDocs = !empty($goCotIds) ? \App\Models\DocumCotizacion::whereIn('id_cotizacion', $goCotIds)->pluck('num_contenedor', 'id_cotizacion') : collect();

            // Liquidaciones
            $liqIds = $movs->where('referenciaable_type', 'App\Models\Liquidaciones')->pluck('referenciaable_id')->filter()->toArray();
            $liqDocs = !empty($liqIds) ? \App\Models\LiquidacionContenedor::whereIn('id_liquidacion', $liqIds)->pluck('id_contenedor', 'id_liquidacion') : collect();
            $mapLiqDocs = !empty($liqDocs) ? \App\Models\DocumCotizacion::whereIn('id', $liqDocs->values())->pluck('num_contenedor', 'id') : collect();

            // Gastos en banco
            $gastoBancoIds = $movs->where('referenciaable_type', 'App\Models\Gasto')->pluck('referenciaable_id')->filter()->toArray();
            $gastoBancoVinculos = !empty($gastoBancoIds) ? \App\Models\GastoVinculo::whereIn('gasto_id', $gastoBancoIds)->get() : collect();
            $mapGastoBanco = [];
            foreach ($gastoBancoVinculos as $gbv) {
                if ($gbv->vinculable_type === 'App\Models\DocumCotizacion') {
                    $num = \App\Models\DocumCotizacion::where('id', $gbv->vinculable_id)->value('num_contenedor');
                    if ($num) $mapGastoBanco[$gbv->gasto_id] = $num;
                } elseif ($gbv->vinculable_type === 'App\Models\Cotizaciones') {
                    $num = \App\Models\DocumCotizacion::where('id_cotizacion', $gbv->vinculable_id)->value('num_contenedor');
                    if ($num) $mapGastoBanco[$gbv->gasto_id] = $num;
                }
            }

            foreach ($bancoMovPolizas as $pId => $mId) {
                $mov = $movs[$mId] ?? null;
                if (!$mov) continue;

                $cont = null;
                if ($mov->referenciaable_type === 'App\Models\Cotizaciones') {
                    $cont = $mapBancoCotDocs[$mov->referenciaable_id] ?? null;
                } elseif ($mov->referenciaable_type === 'App\Models\Asignaciones') {
                    $asig = $asigs[$mov->referenciaable_id] ?? null;
                    if ($asig && $asig->id_contenedor) {
                        $cont = $mapAsigDocs[$asig->id_contenedor] ?? null;
                    }
                } elseif ($mov->referenciaable_type === 'App\Models\CobroPago') {
                    $cotId = $cpCots[$mov->referenciaable_id] ?? null;
                    if ($cotId) {
                        $cont = $mapCpDocs[$cotId] ?? null;
                    }
                } elseif ($mov->referenciaable_type === 'App\Models\GastosOperadores') {
                    $go = $gos[$mov->referenciaable_id] ?? null;
                    if ($go && $go->id_cotizacion) {
                        $cont = $mapGoDocs[$go->id_cotizacion] ?? null;
                    }
                } elseif ($mov->referenciaable_type === 'App\Models\Liquidaciones') {
                    $docId = $liqDocs[$mov->referenciaable_id] ?? null;
                    if ($docId) {
                        $cont = $mapLiqDocs[$docId] ?? null;
                    }
                } elseif ($mov->referenciaable_type === 'App\Models\Gasto') {
                    $cont = $mapGastoBanco[$mov->referenciaable_id] ?? null;
                }

                if (!$cont) {
                    $str = ($mov->concepto ?? '') . ' ' . ($mov->referencia ?? '');
                    if (preg_match('/[A-Z]{4}\d{7}/', $str, $matches)) {
                        $cont = $matches[0];
                    }
                }

                if ($cont) {
                    $mapa[$pId] = $cont;
                }
            }
        }

        // 5. Pagos de Préstamos vía liquidación
        if (!empty($pagoPrestamoPolizas)) {
            $pagoIds = array_values(array_unique($pagoPrestamoPolizas));
            $pagos = \App\Models\PagoPrestamo::whereIn('id', $pagoIds)->get()->keyBy('id');
            $liqIds = $pagos->pluck('id_liquidacion')->filter()->toArray();
            $liqDocs = !empty($liqIds) ? \App\Models\LiquidacionContenedor::whereIn('id_liquidacion', $liqIds)->pluck('id_contenedor', 'id_liquidacion') : collect();
            $mapLiqDocs = !empty($liqDocs) ? \App\Models\DocumCotizacion::whereIn('id', $liqDocs->values())->pluck('num_contenedor', 'id') : collect();

            foreach ($pagoPrestamoPolizas as $pId => $pagoId) {
                $pago = $pagos[$pagoId] ?? null;
                if ($pago && $pago->id_liquidacion) {
                    $docId = $liqDocs[$pago->id_liquidacion] ?? null;
                    if ($docId && isset($mapLiqDocs[$docId])) {
                        $mapa[$pId] = $mapLiqDocs[$docId];
                    }
                }
            }
        }

        // 6. Respaldo general: Regex en el concepto de la póliza o detalle si no se resolvió por relación
        foreach ($movimientosRaw as $m) {
            $pId = $m->poliza_id;
            if (empty($mapa[$pId])) {
                $texto = ($m->referencia ?? '') . ' ' . ($m->concepto_detalle ?? '') . ' ' . ($m->poliza_concepto ?? '');
                if (preg_match('/[A-Z]{4}\d{7}/', $texto, $matches)) {
                    $mapa[$pId] = $matches[0];
                }
            }
        }

        return $mapa;
    }
}

