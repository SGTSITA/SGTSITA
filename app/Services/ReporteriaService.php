<?php

namespace App\Services;

use App\Models\Cotizaciones;
use App\Models\DocumCotizacion;
use App\Models\Asignaciones;
use App\Models\Gasto;
use App\Models\DineroContenedor;
use App\Models\ViaticosOperador;
use App\Models\Bancos;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReporteriaService
{
    public function getContenedorUtilidad(string $startDate, string $endDate, int $idEmpresa, ?int $idProveedor = null): array
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
        $viajesQuery = DB::table('cotizaciones as c')
            ->leftJoin('clients as cl', 'c.id_cliente', '=', 'cl.id')
            ->leftJoin('docum_cotizacion as dc', 'c.id', '=', 'dc.id_cotizacion')
            ->leftJoin('asignaciones as a', 'dc.id', '=', 'a.id_contenedor')
            ->leftJoin('operadores as op', 'a.id_operador', '=', 'op.id')
            ->leftJoin('proveedores as pr', 'a.id_proveedor', '=', 'pr.id')
            ->leftJoin('equipos as eq', 'a.id_camion', '=', 'eq.id')
            ->whereBetween('a.fecha_inicio', [$fechaI, $fechaF])
            ->where('c.estatus', '!=', 'Cancelada')
            ->where('c.id_empresa', $idEmpresa);

        if ($idProveedor) {
            $viajesQuery->where('a.id_proveedor', $idProveedor);
        }

        $viajes = $viajesQuery->select(
            'c.id as id_cotizacion',
            'dc.id as id_docum_cotizacion',
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
            'c.referencia_full',
            'c.estatus_planeacion'
        )->get();

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
            $gastosExtra = Gasto::whereHas('vinculos', function ($q) use ($d) {
                $q->where('tipo_vinculo', 'cotizacion')
                  ->where('vinculable_type', Cotizaciones::class)
                  ->where('vinculable_id', $d->id_cotizacion);
            })->where('tipo_gasto', 'cotizacion')->get();

            $gastosOperador = Gasto::whereHas('vinculos', function ($q) use ($d) {
                $q->where('tipo_vinculo', 'cotizacion')
                  ->where('vinculable_type', Cotizaciones::class)
                  ->where('vinculable_id', $d->id_cotizacion);
            })->whereIn('tipo_gasto', ['operador', 'viaje'])->get();

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

            $dineroViaje = DineroContenedor::where('id_contenedor', $d->id_docum_cotizacion)->sum('monto');
            if ($dineroViaje <= 0 && $d->dinero_viaje > 0) {
                $dineroViaje = $d->dinero_viaje;
            }
            $dineroViajeJustificado = ViaticosOperador::where('id_cotizacion', $d->id_cotizacion)->sum('monto');
            $sinJustificar = $dineroViaje - $dineroViajeJustificado;
            if ($sinJustificar < 0) {
                $sinJustificar = 0;
            }

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
                "pagoOperacion" => $pagoOperacion - $sinJustificar,
                "gastosExtra" => $sumGastosExtra,
                "dineroViajeSinJustificar" => $sinJustificar,
                "gastosViaje" => $sumGastosOperador,
                "viajeInicia" => $d->fecha_inicio,
                "viajeTermina" => $d->fecha_fin,
                "tiempoViaje" => $d->tiempo_viaje,
                "gastosDiferidos" => $gastosDiferidos,
                "estatusViaje" => ($d->estatus === 'Aprobada' && $d->estatus_planeacion == 1) ? 'Planeada' : $d->estatus,
                "estatusPago" => $d->estatus_pago == 1 ? 'Pagado' : 'Por Cobrar',
                "utilidad" => ($d->total + $sumGastosExtra) - ($pagoOperacion + $sumGastosExtra + $sumGastosOperador + $gastosDiferidos),
                "detalleGastos" => $detalleGastos,
            ];

            $Info[] = $Columns;
        }

        return $Info;
    }
    public function getGastosGeneralesPeriodo(string $startDate, string $endDate, int $idEmpresa)
    {
        return Gasto::with(['categoria', 'pagos'])
            ->join('gasto_imputaciones as gi', 'gastos.id', '=', 'gi.gasto_id')
            ->where('gastos.id_empresa', $idEmpresa)
            ->whereIn('gi.tipo_imputacion', ['periodo', 'empresa'])
            ->whereBetween('gi.fecha_imputacion', [$startDate, $endDate])
            ->select('gastos.*', 'gi.monto_imputado as monto_aplicado', 'gi.fecha_imputacion as fecha_aplicada')
            ->get();
    }

    /**
     * Obtener reportes liquidados CXC (Cuentas por Cobrar)
     */
    public function getLiquidadosCxc(array $filters, int $idEmpresa)
    {
        $query = Cotizaciones::with(['Cliente', 'Subcliente', 'DocCotizacion'])
            ->where('cotizaciones.id_empresa', $idEmpresa)
            ->whereIn('cotizaciones.estatus', ['Aprobada', 'Finalizado'])
            ->where('cotizaciones.restante', '<=', 0);

        if (!empty($filters['id_client'])) {
            $query->where('cotizaciones.id_cliente', $filters['id_client']);
        }

        if (!empty($filters['id_subcliente'])) {
            $query->where('cotizaciones.id_subcliente', $filters['id_subcliente']);
        }

        if (!empty($filters['id_unidad'])) {
            $idUnidad = $filters['id_unidad'];
            $query->whereHas('DocCotizacion.Asignaciones', function ($q) use ($idUnidad) {
                $q->where('id_camion', $idUnidad);
            });
        }

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $fi = Carbon::parse($filters['fecha_inicio'])->startOfDay();
            $ff = Carbon::parse($filters['fecha_fin'])->endOfDay();

            $query->whereHas('DocCotizacion.Asignaciones', function ($q) use ($fi, $ff) {
                $q->whereBetween('fecha_inicio', [$fi, $ff]);
            });
        }

        return $query->get();
    }

    /**
     * Obtener registros de banco asociados a cotizaciones (Entrada)
     */
    public function getRegistrosBancoEntrada($cotizaciones)
    {
        if (empty($cotizaciones) || $cotizaciones->isEmpty()) {
            return collect();
        }

        $cotizacionIds = $cotizaciones->pluck('id')->filter()->toArray();

        $movimientos = collect();
        if (!empty($cotizacionIds)) {
            $cobroPagoIds = DB::table('cobros_pagos_cotizaciones')
                ->whereIn('cotizacion_id', $cotizacionIds)
                ->pluck('cobro_pago_id')
                ->unique()
                ->filter()
                ->toArray();

            if (!empty($cobroPagoIds)) {
                $movimientos = \App\Models\CatBancoCuentasMovimientos::with('cuentaBancaria')
                    ->where(function ($q) {
                        $q->where('referenciaable_type', \App\Models\CobroPago::class)
                          ->orWhere('referenciaable_type', 'App\\Models\\CobroPago');
                    })
                    ->whereIn('referenciaable_id', $cobroPagoIds)
                    ->where('cancelado', false)
                    ->get();
            }
        }

        if ($movimientos->isNotEmpty()) {
            return $movimientos;
        }

        // Fallback a tabla legacy BancoDinero por número de contenedor
        $contenedores = $cotizaciones->pluck('DocCotizacion.num_contenedor')->filter()->toArray();

        if (empty($contenedores)) {
            return collect();
        }

        return \App\Models\BancoDinero::where('tipo', 'Entrada')
            ->whereJsonContains('contenedores', function ($query) use ($contenedores) {
                foreach ($contenedores as $contenedor) {
                    $query->orWhereJsonContains('contenedores->num_contenedor', $contenedor);
                }
            })->get();
    }

    /**
     * Obtener reportes liquidados CXP (Cuentas por Pagar)
     */
    public function getLiquidadosCxp(array $filters, int $idEmpresa)
    {
        $query = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
            ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
            ->where('cotizaciones.id_empresa', $idEmpresa)
            ->whereIn('cotizaciones.estatus', ['Aprobada', 'Finalizado'])
            ->where('cotizaciones.prove_restante', '<=', 0);

        if (!empty($filters['id_proveedor'])) {
            $query->where('asignaciones.id_proveedor', $filters['id_proveedor']);
        }

        if (!empty($filters['id_unidad'])) {
            $query->where('asignaciones.id_camion', $filters['id_unidad']);
        }

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $fi = Carbon::parse($filters['fecha_inicio'])->startOfDay();
            $ff = Carbon::parse($filters['fecha_fin'])->endOfDay();

            $query->whereBetween('asignaciones.fecha_inicio', [$fi, $ff]);
        }

        return $query->select(
            'asignaciones.*',
            'docum_cotizacion.num_contenedor',
            'docum_cotizacion.id_cotizacion',
            'cotizaciones.origen',
            'cotizaciones.destino',
            'cotizaciones.estatus',
            'cotizaciones.prove_restante'
        )->get();
    }


    /**
     * Obtener registros de banco asociados a cotizaciones (Salida)
     */
    public function getRegistrosBancoSalida($cotizaciones)
    {
        if (empty($cotizaciones) || $cotizaciones->isEmpty()) {
            return collect();
        }

        $cotizacionIds = $cotizaciones->pluck('id_cotizacion')
            ->merge($cotizaciones->pluck('id'))
            ->merge($cotizaciones->pluck('Contenedor.id_cotizacion'))
            ->filter()
            ->unique()
            ->toArray();

        $movimientos = collect();
        if (!empty($cotizacionIds)) {
            $cobroPagoIds = DB::table('cobros_pagos_cotizaciones')
                ->whereIn('cotizacion_id', $cotizacionIds)
                ->pluck('cobro_pago_id')
                ->unique()
                ->filter()
                ->toArray();

            if (!empty($cobroPagoIds)) {
                $movimientos = \App\Models\CatBancoCuentasMovimientos::with('cuentaBancaria')
                    ->where(function ($q) {
                        $q->where('referenciaable_type', \App\Models\CobroPago::class)
                          ->orWhere('referenciaable_type', 'App\\Models\\CobroPago');
                    })
                    ->whereIn('referenciaable_id', $cobroPagoIds)
                    ->where('cancelado', false)
                    ->get();
            }
        }

        if ($movimientos->isNotEmpty()) {
            return $movimientos;
        }

        // Fallback a tabla legacy BancoDinero por número de contenedor
        $contenedores = $cotizaciones->pluck('Contenedor.num_contenedor')
            ->merge($cotizaciones->pluck('DocumCotizacion.num_contenedor'))
            ->filter()
            ->unique()
            ->toArray();

        if (empty($contenedores)) {
            return collect();
        }

        return \App\Models\BancoDinero::where('tipo', 'Salida')
            ->whereJsonContains('contenedores', function ($query) use ($contenedores) {
                foreach ($contenedores as $contenedor) {
                    $query->orWhereJsonContains('contenedores->num_contenedor', $contenedor);
                }
            })->get();
    }

    /**
     * Exportar Liquidados CXC a Excel o PDF
     */
    public function exportLiquidadosCxc(array $cotizacionIds, string $fileType)
    {
        $fechaCarbon = Carbon::now();

        $cotizaciones = Cotizaciones::with(['Cliente', 'DocCotizacion.Asignaciones.Proveedor', 'cobros.cobroPago.bancoA', 'cobros.cobroPago.bancoB'])
            ->whereIn('id', $cotizacionIds)
            ->get();

        $registrosBanco = $this->getRegistrosBancoEntrada($cotizaciones);

        $bancos_oficiales = Bancos::where('tipo', '=', 'Oficial')->get();
        $bancos_no_oficiales = Bancos::where('tipo', '=', 'No Oficial')->get();
        $user = User::where('id', '=', Auth::user()->id)->first();
        $cotizacion_first = Cotizaciones::whereIn('id', $cotizacionIds)->first();

        $idCliente = $cotizacion_first?->id_cliente ? 'cliente_' . $cotizacion_first->id_cliente : 'todos';
        $timestamp = date('Ymd_His');
        $fileName = "cxc_{$idCliente}_{$timestamp}.{$fileType}";

        if ($fileType == "xlsx") {
            Excel::store(new \App\Exports\LiquidadosCxcExport($cotizaciones, $fechaCarbon, $bancos_oficiales, $bancos_no_oficiales, $registrosBanco, $user, $cotizacion_first), $fileName, 'public');
            return Response::download(storage_path('app/public/' . $fileName), $fileName)->deleteFileAfterSend(true);
        } else {
            $pdf = PDF::loadView('reporteria.liquidados.cxc.pdf', compact('cotizaciones', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales', 'registrosBanco', 'user', 'cotizacion_first'))
                ->setPaper([0, 0, 595, 1200], 'landscape');

            $pdf->save(storage_path('app/public/' . $fileName));

            $filePath = storage_path('app/public/' . $fileName);
            return Response::download($filePath, $fileName)->deleteFileAfterSend(true);
        }
    }

    /**
     * Exportar Liquidados CXP a Excel o PDF
     */
    public function exportLiquidadosCxp(array $cotizacionIds, string $fileType)
    {
        $fechaCarbon = Carbon::now();

        $cotizaciones = Asignaciones::with(['Proveedor', 'Contenedor.Cotizacion.pagos.cobroPago.bancoA', 'Contenedor.Cotizacion.pagos.cobroPago.bancoB', 'Contenedor.Cotizacion.pagos.cobroPago.bancoProveedorA', 'Contenedor.Cotizacion.pagos.cobroPago.bancoProveedorB'])
            ->whereIn('id', $cotizacionIds)
            ->get();

        $registrosBanco = $this->getRegistrosBancoSalida($cotizaciones);

        $bancos_oficiales = Bancos::where('tipo', '=', 'Oficial')->get();
        $bancos_no_oficiales = Bancos::where('tipo', '=', 'No Oficial')->get();
        $user = User::where('id', '=', Auth::user()->id)->first();
        $cotizacion = Asignaciones::whereIn('id', $cotizacionIds)->first();

        $idProveedor = $cotizacion?->id_proveedor ? 'proveedor_' . $cotizacion->id_proveedor : 'todos';
        $timestamp = date('Ymd_His');
        $fileName = "cxp_{$idProveedor}_{$timestamp}.{$fileType}";

        if ($fileType == "xlsx") {
            Excel::store(new \App\Exports\LiquidadosCxpExport($cotizaciones, $fechaCarbon, $bancos_oficiales, $bancos_no_oficiales, $registrosBanco, $user, $cotizacion), $fileName, 'public');
            return Response::download(storage_path('app/public/' . $fileName), $fileName)->deleteFileAfterSend(true);
        } else {
            $pdf = PDF::loadView('reporteria.liquidados.cxp.pdf', compact('cotizaciones', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales', 'registrosBanco', 'user', 'cotizacion'))
                ->setPaper('a4', 'landscape');

            $pdf->save(storage_path('app/public/' . $fileName));

            $filePath = storage_path('app/public/' . $fileName);
            return Response::download($filePath, $fileName)->deleteFileAfterSend(true);
        }
    }
}

