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
                'concepto'     => $c->concepto,
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
                'concepto'     => $c->concepto,
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
                'concepto'     => $c->concepto,
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
            }

            $totalPatrimonio += $monto;
            $patrimonioItems[] = [
                'concepto'     => $c->concepto,
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
     * Suma los saldos netos de las cuentas que comienzan con el prefijo dado.
     * Toma el saldo de la cuenta de mayor más alta que coincida para no duplicar sumas.
     */
    protected function sumarSaldoPorPrefijo(Collection $arbol, string $prefijo): float
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
}
