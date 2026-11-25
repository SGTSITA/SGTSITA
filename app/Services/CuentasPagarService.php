<?php

namespace App\Services;

use App\Models\Cotizaciones;
use App\Models\CobroPago;
use App\Models\CobroPagoCotizacion;
use App\Models\Proveedor;
use App\Models\Bancos;
use App\Models\BancoDinero;
use App\Models\Client;
use App\Models\CuentasBancarias;
use App\Exceptions\SaldoInsuficienteException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CuentasPagarService
{
    public function __construct(
        protected BancosService $bancosService
    ) {
    }

    /**
     * Obtiene los proveedores con saldo pendiente agrupado y número de cotizaciones.
     */
    public function obtenerProveedoresConSaldo(int $idEmpresa)
    {
        $cotizacionIds = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
            ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
            ->whereIn('asignaciones.tipo_contrato', ['Subcontratado'])
            ->where('cotizaciones.id_empresa', '=', $idEmpresa)
            ->where(function ($query) {
                $query->where('cotizaciones.estatus', '=', 'Aprobada')
                    ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
            })
            ->where('cotizaciones.prove_restante', '>', 0)
            ->whereRaw('
                (
                    asignaciones.total_proveedor -
                    (
                        SELECT COALESCE(SUM(cpc.monto),0)
                        FROM cobros_pagos_cotizaciones cpc
                        JOIN cobros_pagos cp ON cp.id = cpc.cobro_pago_id
                        WHERE cpc.cotizacion_id = cotizaciones.id
                        AND cp.tipo = "cxp"
                    )
                ) > 0
            ')
            ->select('cotizaciones.id')
            ->pluck('cotizaciones.id');

        return Cotizaciones::whereIn('id', $cotizacionIds)
            ->with(['DocCotizacion.Asignaciones.Proveedor'])
            ->get()
            ->groupBy('DocCotizacion.Asignaciones.id_proveedor')
            ->map(function ($group) {
                $totalRestante = $group->sum('prove_restante');

                return [
                    'id_proveedor' => $group->first()->DocCotizacion?->Asignaciones?->id_proveedor,
                    'total_cotizaciones' => $group->count(),
                    'proveedor' => $group->first()->DocCotizacion?->Asignaciones?->Proveedor,
                    'total_restante_formateado' => number_format($totalRestante, 0, '.', ','),
                ];
            });
    }

    /**
     * Obtiene los viajes por liquidar de un proveedor para la tabla HandsOnTable.
     */
    public function obtenerViajesPorLiquidar(int $idEmpresa, int $idProveedor): array
    {
        $querybase = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
            ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
            ->leftJoin('estado_cuenta_cotizaciones as ecc', 'ecc.cotizacion_id', '=', 'cotizaciones.id')
            ->leftJoin('estado_cuenta as ec', 'ec.id', '=', 'ecc.estado_cuenta_id')
            ->whereIn('asignaciones.tipo_contrato', ['Subcontratado'])
            ->where('jerarquia', 'Principal')
            ->where(function ($query) {
                $query->where('cotizaciones.estatus', '=', 'Aprobada')
                    ->orWhere('cotizaciones.estatus', '=', 'Finalizado');
            })
            ->where('cotizaciones.id_empresa', '=', $idEmpresa)
            ->where('asignaciones.id_proveedor', '=', $idProveedor)
            ->where('cotizaciones.prove_restante', '>', 0)
            ->select(
                'asignaciones.*',
                'docum_cotizacion.num_contenedor',
                'docum_cotizacion.id_cotizacion',
                'cotizaciones.estatus',
                'cotizaciones.prove_restante',
                'cotizaciones.id_cuenta_prov',
                'cotizaciones.dinero_cuenta_prov',
                'cotizaciones.id_cuenta_prov2',
                'cotizaciones.dinero_cuenta_prov2',
                'cotizaciones.referencia_full',
                'ec.numero as numero_edo_cuenta',
                DB::raw('
                    (
                        SELECT COALESCE(SUM(cpc.monto),0)
                        FROM cobros_pagos_cotizaciones cpc
                        JOIN cobros_pagos cp ON cp.id = cpc.cobro_pago_id
                        WHERE cpc.cotizacion_id = cotizaciones.id
                        AND cp.tipo = "cxp"
                    ) as total_pagado_cxp
                ')
            );

        $cotizacionesPorPagar = DB::query()
            ->fromSub($querybase, 't')
            ->whereRaw('(t.total_proveedor - t.total_pagado_cxp) > 0')
            ->get();

        $handsOnTableData = $cotizacionesPorPagar->map(function ($item) {
            $numContenedor = $item->num_contenedor;
            if (!is_null($item->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $item->referencia_full)
                    ->where('jerarquia', 'Secundario')
                    ->with('DocCotizacion.Asignaciones')
                    ->first();

                if ($secundaria && $secundaria->DocCotizacion) {
                    $numContenedor .= ' / ' . $secundaria->DocCotizacion->num_contenedor;
                }
            }

            $edoCuenta = $item->numero_edo_cuenta ?? 'NA';
            $t_pag = (float) $item->total_pagado_cxp;
            $restante = (float) $item->total_proveedor - (float) $item->total_pagado_cxp;

            return [
                $numContenedor, // index 0
                $edoCuenta,     // index 1
                ($item->estatus == 'Aprobada') ? "En Curso" : $item->estatus, // index 2
                $restante,      // index 3
                $restante,      // index 4
                0,              // index 5
                0,              // index 6
                0,              // index 7
                $item->id_cotizacion, // index 8
                $t_pag          // index 9
            ];
        });

        return compact('handsOnTableData', 'cotizacionesPorPagar');
    }

    /**
     * Obtiene los catálogos para la vista show (pagos_v2).
     */
    public function getDatosShow(int $idProveedor, int $idEmpresa): array
    {
        $fecha = Carbon::now()->format('Y-m-d');
        $bancos = $this->bancosService->getCuentasOption($idEmpresa, $fecha, $fecha, true);
        $proveedor = Proveedor::findOrFail($idProveedor);
        $banco_proveedor = CuentasBancarias::where('id_proveedores', $proveedor->id)->get();

        return compact('bancos', 'proveedor', 'banco_proveedor');
    }

    /**
     * Procesa la aplicación de pagos a proveedores (CXP) con validación de saldo y cargos bancarios.
     */
    public function procesarPagoProveedor(array $requestData, $user): array
    {
        $cotizaciones = is_string($requestData['datahotTableCXP'] ?? null)
            ? json_decode($requestData['datahotTableCXP'], true)
            : ($requestData['datahotTableCXP'] ?? []);

        foreach ($cotizaciones as $c) {
            if (($c[7] ?? 0) > ($c[3] ?? 0)) {
                return [
                    "success" => false,
                    "Titulo" => "Error en el contenedor " . ($c[0] ?? ''),
                    "Mensaje" => "No se puede aplicar el pago para el contenedor porque existe un error en el monto del pago o es mayor al Saldo Original",
                    "TMensaje" => "warning"
                ];
            }
        }

        $idEmpresa = $user->id_empresa;
        $monto1 = (float) ($requestData['amountPayOne'] ?? 0);
        $monto2 = (float) ($requestData['amountPayTwo'] ?? 0);

        if (!empty($requestData['bankOne']) && $monto1 > 0) {
            $fechaAplicacion1 = $requestData['FechaAplicacionPago1'] ?? date('Y-m-d');
            $val1 = $this->bancosService->validarsaldoparacargo($idEmpresa, $requestData['bankOne'], $fechaAplicacion1, $monto1);
            if (!$val1["saldodisponible"]) {
                throw new SaldoInsuficienteException($val1["message"]);
            }
        }

        if (!empty($requestData['bankTwo']) && $monto2 > 0) {
            $fechaAplicacion2 = $requestData['FechaAplicacionPago2'] ?? date('Y-m-d');
            $val2 = $this->bancosService->validarsaldoparacargo($idEmpresa, $requestData['bankTwo'], $fechaAplicacion2, $monto2);
            if (!$val2["saldodisponible"]) {
                throw new SaldoInsuficienteException($val2["message"]);
            }
        }

        return DB::transaction(function () use ($requestData, $cotizaciones, $user, $monto1, $monto2) {
            $fechaApp1 = !empty($requestData['FechaAplicacionbank1'])
                ? Carbon::createFromFormat('d/m/Y', $requestData['FechaAplicacionbank1'])->format('Y-m-d')
                : null;

            $fechaApp2 = !empty($requestData['FechaAplicacionbank2'])
                ? Carbon::createFromFormat('d/m/Y', $requestData['FechaAplicacionbank2'])->format('Y-m-d')
                : null;

            $cobroPago = CobroPago::create([
                'tipo' => 'cxp',
                'proveedor_id' => $requestData['theProvider'] ?? null,
                'bancoA_id' => $requestData['bankOne'] ?? null,
                'monto_A' => $monto1,
                'fechaAplicacion1' => $fechaApp1,
                'bancoB_id' => $requestData['bankTwo'] ?? null,
                'monto_B' => $monto2,
                'fechaAplicacion2' => $fechaApp2,
                'user_id' => $user->id,
                'banco_proveedor_idA' => $requestData['bankProvOne'] ?? null,
                'banco_proveedor_idB' => $requestData['bankProvTwo'] ?? null,
                'observaciones' => null,
            ]);

            $contenedoresAbonos = [];
            $contenedoresAbonos1 = [];
            $contenedoresAbonos2 = [];

            foreach ($cotizaciones as $c) {
                if (($c[7] ?? 0) > 0) {
                    $id = $c[8];
                    $cotizacion = Cotizaciones::findOrFail($id);

                    $abono = (float) $c[7];
                    $nuevoRestante = (float) $cotizacion->prove_restante - $abono;
                    if ($nuevoRestante < 0) {
                        $nuevoRestante = 0;
                    }

                    $cotizacion->prove_restante = $nuevoRestante;
                    $cotizacion->fecha_pago_proveedor = date('Y-m-d');
                    $cotizacion->save();

                    $cleanContenedor = preg_replace('/\s*\(Edo:.*?\)/i', '', $c[0]);
                    $contenedoresAbonos[] = [
                        'num_contenedor' => $cleanContenedor,
                        'abono' => $abono
                    ];

                    if (($c[5] ?? 0) > 0) {
                        $contenedoresAbonos1[] = [
                            'num_contenedor' => $cleanContenedor,
                            'abono' => (float) $c[5]
                        ];

                        CobroPagoCotizacion::create([
                            'cobro_pago_id' => $cobroPago->id,
                            'cotizacion_id' => $id,
                            'origen' => 'A',
                            'monto' => (float) $c[5],
                        ]);
                    }

                    if (($c[6] ?? 0) > 0) {
                        $contenedoresAbonos2[] = [
                            'num_contenedor' => $cleanContenedor,
                            'abono' => (float) $c[6]
                        ];

                        CobroPagoCotizacion::create([
                            'cobro_pago_id' => $cobroPago->id,
                            'cotizacion_id' => $id,
                            'origen' => 'B',
                            'monto' => (float) $c[6],
                        ]);
                    }
                }
            }

            // BancoDinero legacy
            $banco = new BancoDinero();
            $banco->contenedores = json_encode($contenedoresAbonos);
            $banco->id_proveedor = $requestData['theProvider'] ?? null;
            $banco->monto1 = $monto1;
            $banco->metodo_pago1 = 'TRANSFERENCIA';
            $banco->id_banco1 = $requestData['bankOne'] ?? null;
            $banco->monto2 = $monto2;
            $banco->metodo_pago2 = 'TRANSFERENCIA';
            $banco->id_banco2 = $requestData['bankTwo'] ?? null;
            $banco->fecha_pago = date('Y-m-d');
            $banco->tipo = 'Salida';
            $banco->save();

            // Descontar saldo cuentas bancarias
            if (!empty($requestData['bankOne']) && $monto1 > 0) {
                Bancos::where('id', $requestData['bankOne'])->update(["saldo" => DB::raw("saldo - {$monto1}")]);

                $proveedor = Proveedor::find($requestData['theProvider']);
                $this->bancosService->registrarMovimiento([
                    'cuenta_bancaria_id' => $requestData['bankOne'],
                    'tipo' => 'cargo',
                    'monto' => $monto1,
                    'concepto' => 'Pago proveedor: ' . ($proveedor?->nombre ?? ''),
                    'fecha_movimiento' => $fechaApp1 ?: date('Y-m-d'),
                    'origen' => null,
                    'referencia' => 'CXP',
                    'detalles' => json_encode($contenedoresAbonos1),
                    'referenciaable_id' => $cobroPago->id,
                    'referenciaable_type' => CobroPago::class,
                    'observaciones' => 'id banco a proveedor: ' . ($requestData['bankProvOne'] ?? ''),
                ]);
            }

            if (!empty($requestData['bankTwo']) && $monto2 > 0) {
                Bancos::where('id', $requestData['bankTwo'])->update(["saldo" => DB::raw("saldo - {$monto2}")]);

                $proveedor = Proveedor::find($requestData['theProvider']);
                $this->bancosService->registrarMovimiento([
                    'cuenta_bancaria_id' => $requestData['bankTwo'],
                    'tipo' => 'cargo',
                    'monto' => $monto2,
                    'concepto' => 'Pago proveedor: ' . ($proveedor?->nombre ?? ''),
                    'fecha_movimiento' => $fechaApp2 ?: date('Y-m-d'),
                    'origen' => null,
                    'referencia' => 'CXP',
                    'detalles' => json_encode($contenedoresAbonos2),
                    'referenciaable_id' => $cobroPago->id,
                    'referenciaable_type' => CobroPago::class,
                    'observaciones' => 'id banco a proveedor: ' . ($requestData['bankProvTwo'] ?? ''),
                ]);
            }

            return [
                "success" => true,
                "Titulo" => "Pago exitoso",
                "Mensaje" => "Hemos aplicado el pago a los elementos indicados",
                "TMensaje" => "success"
            ];
        });
    }

    /**
     * Aplica el registro de pago directo legacy para CXP.
     */
    public function aplicarPagoDirectoLegacy(int $id, array $data, $request): void
    {
        DB::transaction(function () use ($id, $data, $request) {
            $cotizacion = Cotizaciones::findOrFail($id);
            $cotizacion->prove_monto1 = $data['monto1'] ?? null;
            $cotizacion->prove_metodo_pago1 = $data['metodo_pago1'] ?? null;
            $cotizacion->id_prove_banco1 = $data['id_banco1'] ?? null;

            if ($request->hasFile("comprobante1")) {
                $file = $request->file('comprobante1');
                $path = public_path() . '/pagos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $cotizacion->prove_comprobante_pago1 = $fileName;
            }

            $cotizacion->prove_monto2 = $data['monto2'] ?? null;
            $cotizacion->prove_metodo_pago2 = $data['metodo_pago2'] ?? null;
            $cotizacion->id_prove_banco2 = $data['id_banco2'] ?? null;

            if ($request->hasFile("comprobante2")) {
                $file = $request->file('comprobante2');
                $path = public_path() . '/pagos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $cotizacion->prove_comprobante_pago2 = $fileName;
            }

            $suma = (float) ($data['monto1'] ?? 0) + (float) ($data['monto2'] ?? 0);
            $resta = (float) $cotizacion->prove_restante - $suma;
            $cotizacion->prove_restante = $resta;

            $cotizacion->id_cuenta_prov = $data['id_cuenta_prov'] ?? null;
            $cotizacion->dinero_cuenta_prov = $data['dinero_cuenta_prov'] ?? null;
            $cotizacion->id_cuenta_prov2 = $data['id_cuenta_prov2'] ?? null;
            $cotizacion->dinero_cuenta_prov2 = $data['dinero_cuenta_prov2'] ?? null;
            $cotizacion->fecha_pago_proveedor = date('Y-m-d');
            $cotizacion->save();
        });
    }

    /**
     * Aplica el registro de pagos varios legacy distribuidos entre varias cotizaciones para CXP.
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
                $nuevoRestante = (float) $cotizacion->prove_restante - $abono;

                if ($nuevoRestante < 0) {
                    $nuevoRestante = 0;
                }

                $cotizacion->prove_restante = $nuevoRestante;
                $cotizacion->fecha_pago_proveedor = date('Y-m-d');
                $cotizacion->save();

                $contenedoresAbonos[] = [
                    'num_contenedor' => $cotizacion->DocCotizacion?->num_contenedor,
                    'abono' => $abono
                ];
            }

            $banco = new BancoDinero();
            $banco->contenedores = json_encode($contenedoresAbonos);
            $banco->id_proveedor = $data['id_cliente'] ?? null;
            $banco->monto1 = $data['monto1_varios'] ?? null;
            $banco->metodo_pago1 = $data['metodo_pago1_varios'] ?? null;
            $banco->id_banco1 = $data['id_banco1_varios'] ?? null;

            if ($request->hasFile("comprobante1_varios")) {
                $file = $request->file('comprobante1');
                $path = public_path() . '/pagos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $banco->comprobante_pago1 = $fileName;
            }

            $banco->monto2 = $data['monto2_varios'] ?? null;
            $banco->metodo_pago2 = $data['metodo_pago2_varios'] ?? null;
            $banco->id_banco2 = $data['id_banco2_varios'] ?? null;

            if ($request->hasFile("comprobante2_varios")) {
                $file = $request->file('comprobante2');
                $path = public_path() . '/pagos';
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move($path, $fileName);
                $banco->comprobante_pago2 = $fileName;
            }

            $banco->fecha_pago = date('Y-m-d');
            $banco->tipo = 'Salida';
            $banco->save();
        });
    }

    /**
     * Obtiene el listado de pagos históricos CXP con filtros.
     */
    public function obtenerPagosHistoricos(array $filters, int $idEmpresa)
    {
        $query = CobroPago::with([
            'proveedor',
            'bancoA',
            'bancoB',
            'detalles.cotizacion.DocCotizacion',
            'detalles.cotizacion.estadoCuenta'
        ])
        ->where('tipo', 'cxp')
        ->whereHas('detalles.cotizacion', function ($q) use ($idEmpresa) {
            $q->where('cotizaciones.id_empresa', $idEmpresa);
        });

        if (!empty($filters['proveedor_id'])) {
            $query->where('proveedor_id', $filters['proveedor_id']);
        }

        if (!empty($filters['cliente_id'])) {
            $cliId = $filters['cliente_id'];
            $query->whereHas('detalles.cotizacion', function ($q) use ($cliId) {
                $q->where('id_cliente', $cliId);
            });
        }

        if (!empty($filters['num_contenedor'])) {
            $container = $filters['num_contenedor'];
            $query->whereHas('detalles.cotizacion.DocCotizacion', function ($q) use ($container) {
                $q->where('num_contenedor', 'like', "%{$container}%");
            });
        }

        if (!empty($filters['fecha_inicio']) && !empty($filters['fecha_fin'])) {
            $fi = Carbon::parse($filters['fecha_inicio'])->startOfDay();
            $ff = Carbon::parse($filters['fecha_fin'])->endOfDay();
            $query->where(function ($q) use ($fi, $ff) {
                $q->whereBetween('fechaAplicacion1', [$fi, $ff])
                  ->orWhereBetween('fechaAplicacion2', [$fi, $ff]);
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(30);
    }
}
