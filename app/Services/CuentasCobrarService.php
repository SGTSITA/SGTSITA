<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Cotizaciones;
use App\Models\CobroPago;
use App\Models\CobroPagoCotizacion;
use App\Models\Estado_Cuenta;
use App\Models\Estado_Cuenta_Cotizaciones;
use App\Models\Bancos;
use App\Models\BancoDinero;
use App\Models\Client;
use App\Models\Proveedor;

class CuentasCobrarService
{
    public function __construct(
        protected BancosService $bancosService
    ) {
    }

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


        /*  $cobrosSub = DB::table('cobros_pagos_cotizaciones')
             ->join('cobros_pagos', 'cobros_pagos.id', '=', 'cobros_pagos_cotizaciones.cobro_pago_id')
             ->where('cobros_pagos.tipo', 'cxc')
             ->groupBy('cotizacion_id')
             ->select(
                 'cotizacion_id',
                 DB::raw('SUM(monto) as total_cobrado')
             ); */

        $cobrosSub = DB::table('cobros_pagos_cotizaciones as cpc')
    ->join('cobros_pagos as cp', 'cp.id', '=', 'cpc.cobro_pago_id')
    ->join('cotizaciones as c', 'c.id', '=', 'cpc.cotizacion_id')
    ->where('cp.tipo', 'cxc')
    ->groupBy(DB::raw('COALESCE(c.referencia_full, c.id)'))
    ->select(
        DB::raw('COALESCE(c.referencia_full, c.id) as grupo'),
        DB::raw('SUM(cpc.monto) as total_cobrado')
    );


        /*
        $gastosSub = DB::table('gastos_extras')
            ->groupBy('id_cotizacion')
            ->where('estatus', '!=', 'eliminado')
            ->select(
                'id_cotizacion',
                DB::raw('SUM(monto) as gastos_T')
            );
        */
        $gastosSub = DB::table('gasto_imputaciones')
            ->join('gastos', 'gastos.id', '=', 'gasto_imputaciones.gasto_id')
            ->whereNull('gastos.deleted_at')
            ->where('gastos.estatus', '!=', 'cancelado')
            ->where('gasto_imputaciones.tipo_imputacion', '=', 'cotizacion')
            ->groupBy('gasto_imputaciones.imputable_id')
            ->select(
                'gasto_imputaciones.imputable_id as id_cotizacion',
                DB::raw('SUM(gasto_imputaciones.monto_imputado) as gastos_T')
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
              $join->on(DB::raw('COALESCE(cotizaciones.referencia_full, cotizaciones.id)'), '=', 'cobros.grupo');
          })
           ->leftJoin('asignaciones as a', 'a.id_contenedor', '=', 'docum_cotizacion.id')
           ->leftJoin('equipos as eq', 'eq.id', '=', 'a.id_camion')
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
                          'a.id_camion',
                          'eq.id_equipo as camion_codigo',
                          'eq.marca as camion_marca',
                          'eq.placas as camion_placas',

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

        if (!empty($filtros['id_equipo'])) {
            $query->whereExists(function ($q) use ($filtros) {
                $q->select(DB::raw(1))
                  ->from('docum_cotizacion as dc')
                  ->join('asignaciones as a', 'a.id_contenedor', '=', 'dc.id')
                  ->whereColumn('dc.id_cotizacion', 't.id')
                  ->where('a.id_camion', $filtros['id_equipo']);
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
        $query = $this->baseQuery(true)
            ->where('t.id_cliente', $id_cliente)
            ->leftJoin('estado_cuenta_cotizaciones as ecc', 'ecc.cotizacion_id', '=', 't.id')
            ->leftJoin('estado_cuenta as ec', 'ec.id', '=', 'ecc.estado_cuenta_id')
            ->select('t.*')
            ->addSelect([
                'ec.numero as numero_edo_cuenta',
                'ec.id as id_numero_edo_cuenta'
            ]);

        $base = $query->get();


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
'id_camion' => $principal->id_camion ?? null,
'camion_codigo' => $principal->camion_codigo ?? null,
'camion_marca' => $principal->camion_marca ?? null,
'camion_placas' => $principal->camion_placas ?? null,
'texto_unidad' => $principal->camion_codigo ? ($principal->camion_codigo . ' - ' . ($principal->camion_marca ?: 'Tracto')) : ($principal->camion_placas ?: 'Sin unidad'),
            ];
        })->values();
    }


    //cobros


    private function validarMontos($rows)
    {
        foreach ($rows as $c) {
            if ($c[9] > $c[5]) {
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
        $abono = $c[9];
        $pagoA = $c[7];
        $pagoB = $c[8];

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

        $abono = $c[9];
        $pagoA = $c[7];
        $pagoB = $c[8];


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

            if ($c[9] <= 0) {
                continue;
            }

            $cotizacion = Cotizaciones::with('DocCotizacion')->find($c[10]);

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

    public function obtenerPagosHistoricos(array $filters)
    {
        $idEmpresa = auth()->user()->id_empresa;

        // Query base para CobroPago (tipo = cxc)
        $query = \App\Models\CobroPago::with([
            'cliente',
            'bancoA',
            'bancoB',
            'detalles.cotizacion.DocCotizacion',
            'detalles.cotizacion.estadoCuenta'
        ])
        ->where('tipo', 'cxc')
        ->whereHas('detalles.cotizacion', function ($q) use ($idEmpresa) {
            $q->where('cotizaciones.id_empresa', $idEmpresa);
        });

        // Filtrar por Cliente
        if (!empty($filters['cliente_id'])) {
            $query->where('cliente_id', $filters['cliente_id']);
        }

        // Filtrar por Proveedor (a través de cotizaciones -> contenedores -> asignaciones)
        if (!empty($filters['proveedor_id'])) {
            $provId = $filters['proveedor_id'];
            $query->whereHas('detalles.cotizacion.DocCotizacion.Asignaciones', function ($q) use ($provId) {
                $q->where('id_proveedor', $provId);
            });
        }

        // Filtrar por número de contenedor
        if (!empty($filters['num_contenedor'])) {
            $container = $filters['num_contenedor'];
            $query->whereHas('detalles.cotizacion.DocCotizacion', function ($q) use ($container) {
                $q->where('num_contenedor', 'like', "%{$container}%");
            });
        }

        // Filtrar por Rango de Fechas (fechaAplicacion1 o fechaAplicacion2)
        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $fi = \Carbon\Carbon::parse($filters['fecha_inicio'])->startOfDay();
            $ff = \Carbon\Carbon::parse($filters['fecha_fin'])->endOfDay();
            $query->where(function ($q) use ($fi, $ff) {
                $q->whereBetween('fechaAplicacion1', [$fi, $ff])
                  ->orWhereBetween('fechaAplicacion2', [$fi, $ff]);
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(30);
    }

    public function obtenerPagosHistoricosSinPaginacion(array $filters, $tipo = 'cxc')
    {
        $idEmpresa = auth()->user()->id_empresa;

        $query = \App\Models\CobroPago::with([
            'cliente',
            'proveedor',
            'bancoA',
            'bancoB',
            'detalles.cotizacion.DocCotizacion',
            'detalles.cotizacion.estadoCuenta'
        ])
        ->where('tipo', $tipo);
           $query->whereHas('detalles.cotizacion', function ($q) use ($idEmpresa) {
            $q->where('cotizaciones.id_empresa', $idEmpresa);
        });

        // Filtrar por Cliente
        if (!empty($filters['cliente_id'])) {
            if ($tipo === 'cxc') {
                $query->where('cliente_id', $filters['cliente_id']);
            } else {
                $cliId = $filters['cliente_id'];
                $query->whereHas('detalles.cotizacion', function ($q) use ($cliId) {
                    $q->where('id_cliente', $cliId);
                });
            }
        }

        // Filtrar por Proveedor
        if (!empty($filters['proveedor_id'])) {
            if ($tipo === 'cxp') {
                $query->where('proveedor_id', $filters['proveedor_id']);
            } else {
                $provId = $filters['proveedor_id'];
                $query->whereHas('detalles.cotizacion.DocCotizacion.Asignaciones', function ($q) use ($provId) {
                    $q->where('id_proveedor', $provId);
                });
            }
        }

        // Filtrar por número de contenedor
        if (!empty($filters['num_contenedor'])) {
            $container = $filters['num_contenedor'];
            $query->whereHas('detalles.cotizacion.DocCotizacion', function ($q) use ($container) {
                $q->where('num_contenedor', 'like', "%{$container}%");
            });
        }

        // Filtrar por Rango de Fechas
        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $fi = \Carbon\Carbon::parse($filters['fecha_inicio'])->startOfDay();
            $ff = \Carbon\Carbon::parse($filters['fecha_fin'])->endOfDay();
            $query->where(function ($q) use ($fi, $ff) {
                $q->whereBetween('fechaAplicacion1', [$fi, $ff])
                  ->orWhereBetween('fechaAplicacion2', [$fi, $ff]);
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function obtenerPagoDetalle($id)
    {
        return \App\Models\CobroPago::with([
            'cliente',
            'proveedor',
            'bancoA',
            'bancoB',
            'detalles.cotizacion.DocCotizacion',
            'detalles.cotizacion.estadoCuenta'
        ])->findOrFail($id);
    }

    /**
     * Guarda, anexa o edita una asignación de Estado de Cuenta a cotizaciones.
     */
    public function guardarEstadoCuenta(array $data, int $idEmpresa, int $userId): array
    {
        $numeroEdoCuenta = trim($data['numero'] ?? '');
        $idsSeleccionados = (array) ($data['cotizacionesId'] ?? []);
        $modo = $data['modo'] ?? 'crear';

        $estadoCuentaExistente = Estado_Cuenta::where('numero', $numeroEdoCuenta)
            ->where('id_empresa', $idEmpresa)
            ->first();

        if ($modo === 'crear' && $estadoCuentaExistente) {
            return [
                'ok' => false,
                'already_exists' => true,
                'message' => 'El número de estado de cuenta "' . $numeroEdoCuenta . '" ya existe en el sistema.'
            ];
        }

        if ($modo === 'anexar' && !$estadoCuentaExistente) {
            return [
                'ok' => false,
                'message' => 'El número de estado de cuenta "' . $numeroEdoCuenta . '" no existe en el sistema para anexar.'
            ];
        }

        if ($modo === 'editar' && !empty($data['edo_cuenta_actual_id'])) {
            $estadoCuentaActualId = $data['edo_cuenta_actual_id'];
            if ($estadoCuentaExistente && $estadoCuentaExistente->id != $estadoCuentaActualId) {
                return [
                    'ok' => false,
                    'already_exists' => true,
                    'message' => 'El número de estado de cuenta "' . $numeroEdoCuenta . '" ya pertenece a otro estado de cuenta registrado.'
                ];
            }
        }

        return DB::transaction(function () use ($estadoCuentaExistente, $numeroEdoCuenta, $idEmpresa, $userId, $modo, $data, $idsSeleccionados) {
            if ($estadoCuentaExistente) {
                $estadoCuentaTarget = $estadoCuentaExistente;
            } else {
                $estadoCuentaTarget = Estado_Cuenta::create([
                    'numero' => $numeroEdoCuenta,
                    'id_empresa' => $idEmpresa,
                    'created_by' => $userId,
                ]);
            }

            if ($modo === 'editar') {
                $estadoCuentaActualId = $data['edo_cuenta_actual_id'] ?? null;
                $soloEsta = !empty($data['solo_esta']);

                if ($soloEsta) {
                    Estado_Cuenta_Cotizaciones::whereIn('cotizacion_id', $idsSeleccionados)
                        ->update([
                            'estado_cuenta_id' => $estadoCuentaTarget->id,
                            'assigned_by' => $userId,
                            'updated_at' => now()
                        ]);
                } else {
                    Estado_Cuenta_Cotizaciones::where('estado_cuenta_id', $estadoCuentaActualId)
                        ->update([
                            'estado_cuenta_id' => $estadoCuentaTarget->id,
                            'assigned_by' => $userId,
                            'updated_at' => now()
                        ]);
                }
            } else {
                foreach ($idsSeleccionados as $idcot) {
                    Estado_Cuenta_Cotizaciones::updateOrCreate(
                        ['cotizacion_id' => $idcot],
                        [
                            'estado_cuenta_id' => $estadoCuentaTarget->id,
                            'assigned_by' => $userId,
                            'id_empresa' => $idEmpresa
                        ]
                    );
                }
            }

            return [
                'ok' => true,
                'message' => $modo === 'anexar'
                    ? 'Cotizaciones anexadas al estado de cuenta correctamente'
                    : 'Estado de cuenta guardado correctamente'
            ];
        });
    }

    /**
     * Elimina un cobro o pago revertiendo saldos en cotizaciones y cancelando movimientos bancarios.
     */
    public function eliminarCobroPago(int $id, ?string $fechaCancelacion = null): void
    {
        DB::transaction(function () use ($id, $fechaCancelacion) {
            $cobroPago = CobroPago::with('detalles.cotizacion')->findOrFail($id);

            foreach ($cobroPago->detalles as $detalle) {
                $cotizacion = $detalle->cotizacion;
                if ($cotizacion) {
                    if ($cobroPago->tipo === 'cxc') {
                        $cotizacion->restante = $cotizacion->restante + $detalle->monto;
                        $cotizacion->estatus_pago = ($cotizacion->restante == 0);
                        if ($cotizacion->restante > 0) {
                            $cotizacion->fecha_pago = null;
                        }
                    } elseif ($cobroPago->tipo === 'cxp') {
                        $cotizacion->prove_restante = $cotizacion->prove_restante + $detalle->monto;
                        if ($cotizacion->prove_restante > 0) {
                            $cotizacion->fecha_pago_proveedor = null;
                        }
                    }
                    $cotizacion->save();
                }
                $detalle->delete();
            }

            $movimientos = CatBancoCuentasMovimientos::where('referenciaable_id', $cobroPago->id)
                ->where('referenciaable_type', CobroPago::class)
                ->where('cancelado', false)
                ->get();

            $fechaCanc = $fechaCancelacion ?? now()->format('Y-m-d');

            foreach ($movimientos as $mov) {
                $this->bancosService->cancelarMovimiento($mov->cuenta_bancaria_id, $mov->id, $fechaCanc);

                if ($mov->tipo === 'abono') {
                    Bancos::where('id', $mov->cuenta_bancaria_id)
                        ->update(['saldo' => DB::raw("COALESCE(saldo, 0) - " . floatval($mov->monto))]);
                } elseif ($mov->tipo === 'cargo') {
                    Bancos::where('id', $mov->cuenta_bancaria_id)
                        ->update(['saldo' => DB::raw("COALESCE(saldo, 0) + " . floatval($mov->monto))]);
                }
            }

            $cobroPago->delete();
        });
    }

    /**
     * Obtiene los catálogos para el filtro de pagos históricos.
     */
    public function getCatalogosHistorico(int $idEmpresa): array
    {
        $clientes = Client::join('client_empresa as ce', 'clients.id', '=', 'ce.id_client')
            ->where('ce.id_empresa', $idEmpresa)
            ->where('is_active', 1)
            ->orderBy('nombre')
            ->select('clients.*')
            ->get();

        $proveedores = Proveedor::where('id_empresa', $idEmpresa)
            ->orderBy('nombre')
            ->get();

        return compact('clientes', 'proveedores');
    }

    /**
     * Aplica el registro de pago directo legacy para una cotización individual.
     */
    public function aplicarPagoDirectoLegacy(int $id, array $data, $request): void
    {
        DB::transaction(function () use ($id, $data, $request) {
            $cotizacion = Cotizaciones::findOrFail($id);
            $cotizacion->monto1 = $data['monto1'] ?? null;
            $cotizacion->metodo_pago1 = $data['metodo_pago1'] ?? null;
            $cotizacion->id_banco1 = $data['id_banco1'] ?? null;

            if ($request->hasFile("comprobante1")) {
                $file = $request->file('comprobante1');
                $path = public_path() . '/pagos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $cotizacion->comprobante_pago1 = $fileName;
            }

            $cotizacion->monto2 = $data['monto2'] ?? null;
            $cotizacion->metodo_pago2 = $data['metodo_pago2'] ?? null;
            $cotizacion->id_banco2 = $data['id_banco2'] ?? null;

            if ($request->hasFile("comprobante2")) {
                $file = $request->file('comprobante2');
                $path = public_path() . '/pagos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $cotizacion->comprobante_pago2 = $fileName;
            }

            $suma = (float) ($data['monto1'] ?? 0) + (float) ($data['monto2'] ?? 0);
            $resta = (float) $cotizacion->restante - $suma;
            $cotizacion->restante = $resta;
            $cotizacion->estatus_pago = 1;
            $cotizacion->fecha_pago = date('Y-m-d');
            $cotizacion->save();
        });
    }

    /**
     * Aplica el registro de pagos varios legacy distribuidos entre varias cotizaciones.
     */
    public function aplicarPagoVariosLegacy(array $data, $request): void
    {
        DB::transaction(function () use ($data, $request) {
            $cotizacionesData = (array) ($data['id_cotizacion'] ?? []);
            $abonos = (array) ($data['abono'] ?? []);
            $contenedoresAbonos = [];

            foreach ($cotizacionesData as $id) {
                $cotizacion = Cotizaciones::with('DocCotizacion')->findOrFail($id);
                $abono = isset($abonos[$id]) ? floatval($abonos[$id]) : 0;
                $nuevoRestante = (float) $cotizacion->restante - $abono;

                if ($nuevoRestante < 0) {
                    $nuevoRestante = 0;
                }

                $cotizacion->restante = $nuevoRestante;
                $cotizacion->estatus_pago = ($nuevoRestante == 0) ? 1 : 0;
                $cotizacion->fecha_pago = date('Y-m-d');
                $cotizacion->save();

                $contenedoresAbonos[] = [
                    'num_contenedor' => $cotizacion->DocCotizacion?->num_contenedor,
                    'abono' => $abono
                ];
            }

            $banco = new BancoDinero();
            $banco->contenedores = json_encode($contenedoresAbonos);
            $banco->id_cliente = $data['id_cliente'] ?? null;
            $banco->monto1 = $data['monto1_varios'] ?? null;
            $banco->metodo_pago1 = $data['metodo_pago1_varios'] ?? null;
            $banco->id_banco1 = $data['id_banco1_varios'] ?? null;

            if ($request->hasFile("comprobante1_varios")) {
                $file = $request->file('comprobante1_varios');
                $path = public_path() . '/pagos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $banco->comprobante_pago1 = $fileName;
            }

            $banco->monto2 = $data['monto2_varios'] ?? null;
            $banco->metodo_pago2 = $data['metodo_pago2_varios'] ?? null;
            $banco->id_banco2 = $data['id_banco2_varios'] ?? null;

            if ($request->hasFile("comprobante2_varios")) {
                $file = $request->file('comprobante2_varios');
                $path = public_path() . '/pagos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $banco->comprobante_pago2 = $fileName;
            }

            $banco->fecha_pago = date('Y-m-d');
            $banco->tipo = 'Entrada';
            $banco->save();
        });
    }
}
