<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Cotizaciones;
use App\Models\CobroPago;
use App\Models\CobroPagoCotizacion;

class CuentasCobrarService
{
    public function basequery($validarRestante = false)
    {

        $costosSub = DB::table('viajes_costos')
            ->join('viajes', 'viajes.id', '=', 'viajes_costos.viaje_id')
            ->where('viajes.estado', 'activo')
            ->whereIn('viajes_costos.concepto', ['base_factura','base_taref','iva','retencion'])
            ->groupBy('viajes_costos.viaje_id')
            ->select(
                'viajes_costos.viaje_id',
                DB::raw("
                SUM(
                    CASE
                        WHEN tipo_operacion = 'descuento'
                        THEN -monto
                        ELSE monto
                    END
                ) as total_costos
            ")
            );


        $cobrosSub = DB::table('cobros_pagos_cotizaciones')
            ->join('cobros_pagos', 'cobros_pagos.id', '=', 'cobros_pagos_cotizaciones.cobro_pago_id')
            ->where('cobros_pagos.tipo', 'cxc')
            ->groupBy('cotizacion_id')
            ->select(
                'cotizacion_id',
                DB::raw('SUM(monto) as total_cobrado')
            );


        $gastosSub = DB::table('gastos_extras')
            ->groupBy('id_cotizacion')
            ->where('estatus', '!=', 'eliminado')
            ->select(
                'id_cotizacion',
                DB::raw('SUM(monto) as gastos_T')
            );

        $base = DB::table('cotizaciones')
           ->join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
           ->join('clients', 'cotizaciones.id_cliente', '=', 'clients.id')
           ->leftJoin('subclientes', 'subclientes.id', '=', 'cotizaciones.id_subcliente')
           ->join('viajes_cotizacion', 'cotizaciones.id', '=', 'viajes_cotizacion.cotizacion_id')
           ->join('viajes', function ($join) {
               $join->on('viajes.id', '=', 'viajes_cotizacion.viaje_id')
                    ->where('viajes.estado', 'activo');
           })

           ->leftJoinSub($costosSub, 'costos', function ($join) {
               $join->on('viajes.id', '=', 'costos.viaje_id');
           })

           ->leftJoinSub($gastosSub, 'gastos_ext', function ($join) {
               $join->on('cotizaciones.id', '=', 'gastos_ext.id_cotizacion');
           })

           ->leftJoinSub($cobrosSub, 'cobros', function ($join) {
               $join->on('cotizaciones.id', '=', 'cobros.cotizacion_id');
           })
           ->leftJoin('asignaciones as a', 'a.id_contenedor', '=', 'docum_cotizacion.id')
           ->whereIn('cotizaciones.estatus', ['Aprobada', 'Finalizado'])
           ->where('cotizaciones.id_empresa', '=', auth()->user()->id_empresa)
           ->where('cotizaciones.estatus_pago', '=', '0')
            ->where('jerarquia', 'Principal')
         //   ->where('cotizaciones.restante', '>', 0)

           ->select([
               'cotizaciones.id',
               'cotizaciones.id_cliente',
               'cotizaciones.origen',
'cotizaciones.destino',
'cotizaciones.jerarquia',
               'clients.nombre as cliente_nombre',
               'clients.telefono as cliente_telefono',
               'subclientes.nombre as nombre_subcliente',
'subclientes.telefono as telefono_subcliente',
'cotizaciones.id_subcliente',
'cotizaciones.tipo_viaje',
'cotizaciones.estatus',
'cotizaciones.referencia_full',
               'docum_cotizacion.num_contenedor',
               'viajes.id as viaje_id',
               'a.fehca_inicio_guard as fecha_inicio_guard',

               DB::raw('COALESCE(costos.total_costos,0) + COALESCE(gastos_ext.gastos_T,0) as total_costos'),

               DB::raw('COALESCE(cobros.total_cobrado,0) as total_cobrado'),

               DB::raw('
                (COALESCE(costos.total_costos,0) + COALESCE(gastos_ext.gastos_T,0))
                - COALESCE(cobros.total_cobrado,0)
                as total_restante
            ')
           ]);

        $query = DB::query()->fromSub($base, 't')   ->select('t.*');


        $query->when($validarRestante, function ($q) {
            $q->where('t.total_restante', '>', 0);
        });


        return $query;
    }

    public function getCuentasPorCobrar($filtros = [])
    {
        $query = $this->basequery(true);


        $query->leftJoin('estado_cuenta_cotizaciones as ecc', 'ecc.cotizacion_id', '=', 't.id')
              ->leftJoin('estado_cuenta as ec', 'ec.id', '=', 'ecc.estado_cuenta_id')
              ->addSelect([
                                'ec.numero as numero_edo_cuenta',
                  'ec.id as id_numero_edo_cuenta'
              ]);


        if (!empty($filtros['id_cliente'])) {
            $query->where('t.id_cliente', $filtros['id_cliente']);
        }

        if (!empty($filtros['id_subcliente'])) {
            $query->where('t.id_subcliente', $filtros['id_subcliente']);
        }

        if (!empty($filtros['id_proveedor'])) {
            $query->whereExists(function ($q) use ($filtros) {
                $q->select(DB::raw(1))
                  ->from('docum_cotizacion as dc')
                  ->join('asignaciones as a', 'a.id_contenedor', '=', 'dc.id')
                  ->whereColumn('dc.id_cotizacion', 't.id')
                  ->where('a.id_proveedor', $filtros['id_proveedor']);
            });
        }

        if (!empty($filtros['numero_edo_cuenta'])) {
            $query->where('ec.id', $filtros['numero_edo_cuenta']);
        }

        $cotizaciones = $this->resolverFulles($query->get());

        return $cotizaciones;
    }

    public function obtenerporCliente($id_cliente = null)
    {
        $query = $this->basequery(true);

        if ($id_cliente !== null) {
            $query->where('id_cliente', $id_cliente);
        }

        return $query
            ->groupBy(
                'id_cliente',
                'cliente_nombre',
                'cliente_telefono',
            )
            ->select(
                'id_cliente',
                'cliente_nombre',
                'cliente_telefono',
                DB::raw('COUNT(*) as total_cotizaciones'),
                DB::raw('SUM(COALESCE(t.total_costos, 0)) as total_costos'),
                DB::raw('SUM(COALESCE(t.total_cobrado, 0)) as total_cobrado'),
                DB::raw('SUM(COALESCE(t.total_restante, 0)) as total_restante')
            )
            ->get();
    }



    public function obtenerporClienteId($id_cliente, $resolverfulles = false)
    {
        $base = $this->baseQuery(true)
    ->where('t.id_cliente', $id_cliente)
    ->get();


        $cotizacion = $base;
        if ($resolverfulles) {
            $cotizacion = $this->resolverFulles($base);
            // dd($cotizacion);
        }


        return $cotizacion;
    }

    public function resolverFulles($rows)
    {

        // dd($rows);
        $agrupados = $rows->groupBy(function ($item) {
            return $item->referencia_full ?? 'single_' . $item->id;
        });

        $referenciasFull = $rows
            ->pluck('referencia_full')
            ->filter()
            ->unique();

        $secundarios = Cotizaciones::with('DocCotizacion')
    ->whereIn('referencia_full', $referenciasFull)
    ->where('jerarquia', 'Secundario')
    ->get()
    ->groupBy('referencia_full');


        return $agrupados->map(function ($items, $key) use ($secundarios) {

            $principal = $items->first();

            $tipoViajeRaw = $principal->tipo_viaje ?? null;

            $tipoViaje = (!$tipoViajeRaw || $tipoViajeRaw == 'Seleccionar Opcion')
                ? "Subcontratado"
                : $tipoViajeRaw;



            $idSubcliente = $principal->id_subcliente ?? null;

            $subCliente = $idSubcliente
                ? trim(($principal->nombre_subcliente ?? '') . " / " . ($principal->telefono_subcliente ?? ''))
                : "";
            $contenedores = collect([$principal->num_contenedor]);
            $tipo = 'Sencillo';
            if ($principal->referencia_full && isset($secundarios[$principal->referencia_full])) {
                $secundariosDeFull = $secundarios->get($principal->referencia_full, collect());

                foreach ($secundariosDeFull as $secundario) {
                    $contenedores->push($secundario->DocCotizacion->num_contenedor);
                }
                $tipo = 'Full';
            }

            return (object) [
                'id' => $principal->id,
                'id_cliente' => $principal->id_cliente,


                'num_contenedor' => $contenedores->implode(' / '),

                'cliente_nombre' => $principal->cliente_nombre,
                'cliente_telefono' => $principal->cliente_telefono,

                'nombre_subcliente' =>   $subCliente,
                'telefono_subcliente' => $principal->telefono_subcliente,
                'id_subcliente' => $principal->id_subcliente,


                'tipo' => $tipo,

                'tipo_viaje' => $tipoViaje,
                'estatus' => $principal->estatus,


                'total_costos' => $items->sum('total_costos'),
                'total_cobrado' => $items->sum('total_cobrado'),
                'total_restante' => $items->sum('total_restante'),

                'referencia_full' => $principal->referencia_full,
                'origen' => $principal->origen ?? null,
'destino' => $principal->destino ?? null,
'jerarquia' => $principal->jerarquia ?? null,

// estado cuenta
'numero_edo_cuenta' => $principal->numero_edo_cuenta ?? null,
'id_numero_edo_cuenta' => $principal->id_numero_edo_cuenta ?? null,

// fecha asignacion (si ya la traes en basequery)
'fecha_inicio_guard' => $principal->fecha_inicio_guard ?? null,
            ];
        })->values();
    }


    //cobros


    private function validarMontos($rows)
    {
        foreach ($rows as $c) {
            if ($c[8] > $c[4]) {
                throw new \Exception("Error en contenedor {$c[0]}: el pago es mayor al saldo");
            }
        }
    }

    private function crearCobro($request)
    {
        return CobroPago::create([
            'tipo' => 'cxc',
            'cliente_id' => $request->theClient,
            'bancoA_id' => $request->bankOne,
            'monto_A' => $request->amountPayOne ?? 0,
            'fechaAplicacion1' => $request->FechaAplicacionbank1
                ? \Carbon\Carbon::createFromFormat('d/m/Y', $request->FechaAplicacionbank1)->format('Y-m-d')
                : null,
            'bancoB_id' => $request->bankTwo,
            'monto_B' => $request->amountPayTwo ?? 0,
            'fechaAplicacion2' => $request->FechaAplicacionbank2
                ? \Carbon\Carbon::createFromFormat('d/m/Y', $request->FechaAplicacionbank2)->format('Y-m-d')
                : null,
            'user_id' => auth()->id(),
            'observaciones' => null,
        ]);
    }
    private function procesarSencillo(
        $cotizacion,
        $c,
        $cobroPago,
        &$contenedoresAbonos,
        &$contenedoresAbonos1,
        &$contenedoresAbonos2
    ) {
        $abono = $c[8];
        $pagoA = $c[6];
        $pagoB = $c[7];

        $this->aplicarAbonoCotizacion($cotizacion, $abono);

        $num = $cotizacion->DocCotizacion->num_contenedor;

        $contenedoresAbonos[] = [
            'num_contenedor' => $num,
            'abono' => $abono
        ];

        $this->registrarPagos(
            $cotizacion->id,
            $cobroPago->id,
            $pagoA,
            $pagoB,
            $num,
            $contenedoresAbonos1,
            $contenedoresAbonos2
        );
    }

    private function procesarFull(
        $cotizacion,
        $c,
        $cobroPago,
        &$contenedoresAbonos,
        &$contenedoresAbonos1,
        &$contenedoresAbonos2
    ) {

        $grupo = Cotizaciones::with('DocCotizacion')
            ->where('referencia_full', $cotizacion->referencia_full)
            ->get();

        $principal = $grupo->firstWhere('jerarquia', 'Principal');
        $secundaria = $grupo->firstWhere('jerarquia', 'Secundario');

        $abono = $c[8];
        $pagoA = $c[6];
        $pagoB = $c[7];


        $this->aplicarAbonoCotizacion($principal, $abono);


        $num = collect([
            $principal?->DocCotizacion?->num_contenedor,
            $secundaria?->DocCotizacion?->num_contenedor
        ])->filter()->implode(' / ');

        $contenedoresAbonos[] = [
            'num_contenedor' => $num,
            'abono' => $abono
        ];


        $this->registrarPagos(
            $principal->id,
            $cobroPago->id,
            $pagoA,
            $pagoB,
            $num,
            $contenedoresAbonos1,
            $contenedoresAbonos2
        );


        if ($secundaria) {
            if ($pagoA > 0) {
                CobroPagoCotizacion::create([
                    'cobro_pago_id' => $cobroPago->id,
                    'cotizacion_id' => $secundaria->id,
                    'origen' => 'A',
                    'monto' => 0,
                ]);
            }

            if ($pagoB > 0) {
                CobroPagoCotizacion::create([
                    'cobro_pago_id' => $cobroPago->id,
                    'cotizacion_id' => $secundaria->id,
                    'origen' => 'B',
                    'monto' => 0,
                ]);
            }
        }
    }
    public function procesarCobro($request)
    {
        $cotizaciones = json_decode($request->datahotTable);

        $this->validarMontos($cotizaciones);

        $cobroPago = $this->crearCobro($request);

        $contenedoresAbonos = [];
        $contenedoresAbonos1 = [];
        $contenedoresAbonos2 = [];

        foreach ($cotizaciones as $c) {

            if ($c[8] <= 0) {
                continue;
            }

            $cotizacion = Cotizaciones::with('DocCotizacion')->find($c[9]);

            if ($cotizacion->referencia_full) {
                $this->procesarFull(
                    $cotizacion,
                    $c,
                    $cobroPago,
                    $contenedoresAbonos,
                    $contenedoresAbonos1,
                    $contenedoresAbonos2
                );
            } else {
                $this->procesarSencillo(
                    $cotizacion,
                    $c,
                    $cobroPago,
                    $contenedoresAbonos,
                    $contenedoresAbonos1,
                    $contenedoresAbonos2
                );
            }
        }

        return compact(
            'cobroPago',
            'contenedoresAbonos',
            'contenedoresAbonos1',
            'contenedoresAbonos2'
        );
    }
    private function registrarPagos(
        $cotizacionId,
        $cobroPagoId,
        $pagoA,
        $pagoB,
        $numContenedor,
        &$contenedoresAbonos1,
        &$contenedoresAbonos2
    ) {

        if ($pagoA > 0) {

            $contenedoresAbonos1[] = [
                'num_contenedor' => $numContenedor,
                'abono' => $pagoA
            ];

            CobroPagoCotizacion::create([
                'cobro_pago_id' => $cobroPagoId,
                'cotizacion_id' => $cotizacionId,
                'origen' => 'A',
                'monto' => $pagoA,
            ]);
        }

        if ($pagoB > 0) {

            $contenedoresAbonos2[] = [
                'num_contenedor' => $numContenedor,
                'abono' => $pagoB
            ];

            CobroPagoCotizacion::create([
                'cobro_pago_id' => $cobroPagoId,
                'cotizacion_id' => $cotizacionId,
                'origen' => 'B',
                'monto' => $pagoB,
            ]);
        }
    }

    private function aplicarAbonoCotizacion($cotizacion, $abono) //ver si podemos quitar completamente esto
    {
        $cotizacion->restante = max(0, $cotizacion->restante - $abono);
        $cotizacion->estatus_pago = $cotizacion->restante == 0;
        $cotizacion->fecha_pago = now();
        $cotizacion->save();
    }

}
