<?php

namespace App\Services;

use App\Models\Cotizaciones;
use App\Models\DocumCotizacion;
use App\Models\Asignaciones;
use App\Models\Gasto;
use App\Models\DineroContenedor;
use App\Models\ViaticosOperador;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReporteriaService
{
    public function getContenedorUtilidad(string $startDate, string $endDate, int $idEmpresa): array
    {
        $fechaI = Carbon::parse($startDate)->startOfDay();
        $fechaF = Carbon::parse($endDate)->endOfDay();

        // 1. Obtener gastos de unidades (equipos) prorrateados desde las imputaciones del módulo unificado
        $gastosUnidad = DB::table('gasto_imputaciones')
            ->join('gastos', 'gasto_imputaciones.gasto_id', '=', 'gastos.id')
            ->where('gasto_imputaciones.imputable_type', \App\Models\Equipo::class)
            ->where('gastos.id_empresa', $idEmpresa)
            ->whereNull('gastos.deleted_at')
            ->whereBetween('gasto_imputaciones.fecha_imputacion', [$fechaI->format('Y-m-d'), $fechaF->format('Y-m-d')])
            ->select(
                'gasto_imputaciones.imputable_id as id_camion',
                'gastos.concepto as motivo',
                DB::raw('SUM(gasto_imputaciones.monto_imputado) as total_gastos_periodo')
            )
            ->groupBy('gasto_imputaciones.imputable_id', 'gastos.concepto')
            ->get();

        // 2. Obtener viajes/asignaciones del periodo
        $viajes = DB::table('cotizaciones as c')
            ->leftJoin('clients as cl', 'c.id_cliente', '=', 'cl.id')
            ->leftJoin('docum_cotizacion as dc', 'c.id', '=', 'dc.id_cotizacion')
            ->leftJoin('asignaciones as a', 'dc.id', '=', 'a.id_contenedor')
            ->leftJoin('operadores as op', 'a.id_operador', '=', 'op.id')
            ->leftJoin('proveedores as pr', 'a.id_proveedor', '=', 'pr.id')
            ->leftJoin('equipos as eq', 'a.id_camion', '=', 'eq.id')
            ->whereBetween('a.fecha_inicio', [$fechaI, $fechaF])
            ->where('c.estatus', '!=', 'Cancelada')
            ->where('c.id_empresa', $idEmpresa)
            ->select(
                'c.id as id_cotizacion',
                'a.id_camion',
                'dc.num_contenedor',
                'cl.nombre as cliente',
                'op.nombre as Operador',
                'a.sueldo_viaje',
                'a.dinero_viaje',
                'pr.nombre as Proveedor',
                'a.total_proveedor',
                'c.total',
                'c.estatus',
                'c.estatus_pago',
                'c.fecha_pago',
                'a.fecha_inicio',
                'a.fecha_fin',
                DB::raw('DATEDIFF(a.fecha_fin, a.fecha_inicio) as tiempo_viaje'),
                'c.referencia_full'
            )
            ->get();

        // Agrupar asignaciones por camión para calcular el prorrateo de gastos por viaje en memoria
        $viajesPorCamion = $viajes->groupBy('id_camion');

        $Info = [];

        foreach ($viajes as $d) {
            $detalleGastos = [];

            // Prorratear los gastos de la unidad (equipo) por viaje
            $camionGastos = $gastosUnidad->where('id_camion', $d->id_camion);
            $totalViajesCamion = isset($viajesPorCamion[$d->id_camion]) ? count($viajesPorCamion[$d->id_camion]) : 1;

            foreach ($camionGastos as $gc) {
                $gastoPorViaje = $gc->total_gastos_periodo / $totalViajesCamion;
                $detalleGastos[] = [
                    "fecha_gasto" => $fechaI->format('Y-m-d'),
                    "monto_gasto" => round($gastoPorViaje, 2),
                    "tipo_gasto" => "DIFERIDO",
                    "motivo_gasto" => $gc->motivo
                ];
            }

            // 3. Obtener gastos unificados (Extras y de Viaje/Operador)
            $gastosUnificados = Gasto::whereHas('vinculos', function ($q) use ($d) {
                $q->where('tipo_vinculo', 'cotizacion')
                  ->where('vinculable_type', Cotizaciones::class)
                  ->where('vinculable_id', $d->id_cotizacion);
            })->get();

            $gastosExtra = $gastosUnificados->where('tipo_gasto', 'cotizacion');
            $gastosOperador = $gastosUnificados->whereIn('tipo_gasto', ['operador', 'viaje']);

            foreach ($gastosExtra as $ge) {
                $detalleGastos[] = [
                    "fecha_gasto" => $ge->fecha_gasto?->format('Y-m-d') ?? $ge->created_at->format('Y-m-d'),
                    "monto_gasto" => (float) $ge->monto_total,
                    "tipo_gasto" => "Gasto Extra",
                    "motivo_gasto" => $ge->concepto
                ];
            }

            foreach ($gastosOperador as $go) {
                $detalleGastos[] = [
                    "fecha_gasto" => $go->fecha_gasto?->format('Y-m-d') ?? $go->created_at->format('Y-m-d'),
                    "monto_gasto" => (float) $go->monto_total,
                    "tipo_gasto" => "Gastos Viaje",
                    "motivo_gasto" => $go->concepto
                ];
            }

            $dineroViaje = DineroContenedor::where('id_contenedor', $d->id_cotizacion)->sum('monto');
            $dineroViajeJustificado = ViaticosOperador::where('id_cotizacion', $d->id_cotizacion)->sum('monto');
            $sinJustificar = $dineroViaje - $dineroViajeJustificado;

            $contenedor = $d->num_contenedor;

            if (!is_null($d->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $d->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion.Asignaciones')
                    ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $contenedor .= ' / ' . $secundaria->DocCotizacion->num_contenedor;
                }
            }

            $pagoOperacion = (is_null($d->Proveedor)) ? $d->sueldo_viaje : $d->total_proveedor;
            $gastosDiferidos = round($camionGastos->sum('total_gastos_periodo') / $totalViajesCamion, 2);

            $sumGastosExtra = $gastosExtra->sum('monto_total');
            $sumGastosOperador = $gastosOperador->sum('monto_total');

            $Columns = [
                "numContenedor" => $contenedor,
                "cliente" => $d->cliente,
                "precioViaje" => $d->total + $sumGastosExtra,
                "transportadoPor" => (is_null($d->Proveedor)) ? 'Operador' : 'Proveedor',
                "operadorOrProveedor" => (is_null($d->Proveedor)) ? $d->Operador : $d->Proveedor,
                "pagoOperacion" => $pagoOperacion - abs($sinJustificar),
                "gastosExtra" => $sumGastosExtra,
                "dineroViajeSinJustificar" => abs($sinJustificar),
                "gastosViaje" => $sumGastosOperador,
                "viajeInicia" => $d->fecha_inicio,
                "viajeTermina" => $d->fecha_fin,
                "tiempoViaje" => $d->tiempo_viaje,
                "gastosDiferidos" => $gastosDiferidos,
                "estatusViaje" => $d->estatus,
                "estatusPago" => $d->estatus_pago == 1 ? 'Pagado' : 'Por Cobrar',
                "utilidad" => ($d->total + $sumGastosExtra) - ($pagoOperacion + $sumGastosExtra + $sumGastosOperador + $gastosDiferidos),
                "detalleGastos" => $detalleGastos,
            ];

            $Info[] = $Columns;
        }

        return $Info;
    }
}
