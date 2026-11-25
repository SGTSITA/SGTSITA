<?php

namespace App\Services;

use App\Models\Bancos;
use App\Models\CatBanco;
use App\Models\CatBancoCuentasMovimientos;
use App\Models\Cotizaciones;
use App\Models\BancoDinero;
use App\Models\BancoDineroOpe;
use App\Models\GastosGenerales;
use App\Models\BancoSaldoDiario;
use App\Models\MovimientoBancario;
use App\Models\Empresas;
use App\Models\Equipo;
use App\Models\GastoImputacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class BancosService
{
    public function getCuentasOption($empresaId, $fechaInicio, $fechaFin, $validarSaldo)
    {
        $cuentas = Bancos::with('catBanco')
        ->where('id_empresa', $empresaId)
        ->where('estado', 1)
        ->get()
        ->map(function ($cuenta) use ($fechaInicio, $fechaFin) {

            $resultado = $this->getSaldoActualFecha(
                $cuenta,
                $fechaInicio,
                $fechaFin
            );

            return [
                'id' => $cuenta->id,
                'tipo' => $cuenta->tipo,
                'nombre_beneficiario' => $cuenta->nombre_beneficiario,
                'banco' => optional($cuenta->catBanco)->nombre,
               'display' =>
                        (optional($cuenta->catBanco)->nombre ?? '')
                        . ' •••• '
                        . substr($cuenta->cuenta_bancaria, -4)
                        . ' '
                        . ($cuenta->tipo ?? '')
                        . ' / '
                        . ($cuenta->nombre_beneficiario ?? ''),
                'saldo_anterior' => $resultado['saldoAnterior'],
                'saldo_actual'   => $resultado['saldoActual'],
            ];
        });


        if ($validarSaldo) {
            $cuentas = $cuentas->filter(function ($cuenta) {
                return $cuenta['saldo_actual'] > 0;
            })->values();
        }
        //   dd($cuentas);

        return $cuentas;
    }


    public function obtenerDetalleCuenta($cuentaId, $empresaId, $fechaInicio = null, $fechaFin = null)
    {



        $cuenta = Bancos::with('catBanco')->where('id', $cuentaId)
            ->where('id_empresa', $empresaId)
            ->where('estado', 1)
            ->firstOrFail();

        $cuentas = Bancos::where('id_empresa', $empresaId)
          ->where('estado', 1)
          ->get();


        $resultado = $this->getsaldoActualFecha(
            $cuenta,
            $fechaInicio,
            $fechaFin
        );

        $movimientos =  $resultado["movimientos"];

        $conteoDepositos = $movimientos->where('tipo', 'abono')->count();
        $totalDepositos  = $movimientos->where('tipo', 'abono')->sum('monto');

        $conteoCargos = $movimientos->where('tipo', 'cargo')->count();
        $totalCargos  = $movimientos->where('tipo', 'cargo')->sum('monto');

        return [
            'cuenta' => $cuenta,
           'movimientos' => $resultado['movimientos'],
    'saldoAnterior' => $resultado['saldoAnterior'],
    'saldoActual' => $resultado['saldoActual'],
            'conteo_depositos' => $conteoDepositos,
            'total_depositos' => $totalDepositos,
            'conteo_cargos' => $conteoCargos,
            'total_cargos' => $totalCargos,
             'cuentas' => $cuentas
        ];
    }
    public function getsaldoActualFecha($cuenta, $fecha_ini, $fecha_fin)
    {
        $saldo = (float) $cuenta->inicial_saldo;


        $fecha_ini = $this->parseFecha($fecha_ini);
        $fecha_fin = $this->parseFecha($fecha_fin);

        // dd($fecha_ini, $fecha_fin);
        if ($fecha_ini) {
            $previos = $cuenta->movimientos()
                ->where('fecha_movimiento', '<', $fecha_ini)
                ->orderBy('fecha_movimiento')
                ->get();

            foreach ($previos as $mov) {
                $saldo = $this->aplicarMovimiento($saldo, $mov);
            }
        }
        $saldoAnterior = $saldo;

        $query = $cuenta->movimientos()
            ->orderBy('fecha_movimiento')
            ->orderBy('created_at');


        if ($fecha_ini && $fecha_fin) {
            $query->whereBetween('fecha_movimiento', [$fecha_ini, $fecha_fin]);
        }

        $movimientos = $query->get();

        $movimientos = $movimientos->map(function ($mov) use (&$saldo) {
            $saldo = $this->aplicarMovimiento($saldo, $mov);
            $mov->saldo_resultante = $saldo;
            return $mov;
        });

        return [
            'movimientos' => $movimientos,
            'saldoAnterior' => $saldoAnterior,
            'saldoActual' => $saldo
        ];


    }
    private function parseFecha($fecha)
    {
        if (!$fecha) {
            return null;
        }

        try {


            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fecha)) {
                return \Carbon\Carbon::createFromFormat('d/m/Y', $fecha)->format('Y-m-d');
            }


            return \Carbon\Carbon::parse($fecha)->format('Y-m-d');

        } catch (\Exception $e) {
            return null;
        }
    }
    private function aplicarMovimiento(float $saldo, $mov): float
    {
        if ($mov->tipo === 'abono') {
            return $saldo + (float) $mov->monto;
        }

        return $saldo - (float) $mov->monto;
    }
    public function validarsaldoparacargo($empresaId, $cuentaId, $fechaAplicacion, $dineroRegistrar)
    {
        $cuenta =  Bancos::where('id', $cuentaId)
        ->where('id_empresa', $empresaId)
        ->where('estado', 1)
        ->first();

        $movimientos = $this->getsaldoActualFecha($cuenta, $fechaAplicacion, $fechaAplicacion);

        //

        if ($movimientos["saldoActual"] < $dineroRegistrar) {
            $faltante = $dineroRegistrar - $movimientos["saldoActual"];

            return [
                'message' => 'Saldo insuficiente para completar el cargo por : '.number_format($dineroRegistrar, 2),
                'saldodisponible' => false,
                'faltante' => $faltante,
            ];
        }
        //  dd($movimientos);
        return [
               'message' => 'Movimiento puede registrarse',
               'saldodisponible' => true,
               'faltante' => 0,
               'saldoActual' => $movimientos["saldoActual"],
               'movimientos' => $movimientos["movimientos"]
           ];


    }

    public function registrarMovimiento($data)
    {

        return DB::transaction(function () use ($data) {

            $tipoConcepto = $data['tipo_concepto'] ?? 'operativo';

            $mov = CatBancoCuentasMovimientos::create([
                'cuenta_bancaria_id' => $data['cuenta_bancaria_id'],
                'tipo'               => $data['tipo'], // cargo | abono
                'monto'              => $data['monto'],
                'concepto'           => $data['concepto'] ?? null,
                'fecha_movimiento'   => $data['fecha_movimiento'],
                'origen'             => $data['origen'] ?? 'sistema',
                'referencia'         => $data['referencia'] ?? null,
                'referenciaable_type'=> $data['referenciaable_type'] ?? null,
                'referenciaable_id'  => $data['referenciaable_id'] ?? null,
                'detalles'           => $data['detalles'] ?? null,
                'observaciones'      => $data['observaciones'] ?? null,
                'tipo_concepto'      => $tipoConcepto,
                'user_id'            => auth()->id(),
            ]);

            // Sync legacy saldo column
            $banco = Bancos::find($data['cuenta_bancaria_id']);
            if ($banco) {
                if ($data['tipo'] === 'abono') {
                    $banco->increment('saldo', $data['monto']);
                } else {
                    $banco->decrement('saldo', $data['monto']);
                }

                // Generar póliza contable automática del movimiento bancario
                try {
                    $polizaService = app(\App\Services\PolizaService::class);
                    if ($tipoConcepto === 'interes' && $data['tipo'] === 'abono') {
                        $asientoData = $polizaService->resolverAsientoPorEvento('INTERES_BANCARIO', null, $banco->id_empresa, [
                            'cuenta_bancaria_id' => $banco->id,
                            'banco'              => $banco->nombre_banco,
                            'cuenta'             => $banco,
                            'monto'              => $data['monto'],
                            'fecha'              => $data['fecha_movimiento'],
                            'concepto'           => 'Interés bancario: ' . ($data['concepto'] ?? ('Rendimiento ' . $banco->nombre_banco)),
                            'origen_tipo'        => 'bancos',
                            'origen_id'          => $mov->id,
                        ]);

                        if (!empty($asientoData)) {
                            $poliza = $polizaService->crearPoliza($asientoData);
                            $mov->update(['poliza_id' => $poliza->id]);
                            $mov->poliza_id = $poliza->id;
                        }
                    } else {
                        $poliza = $polizaService->contabilizarMovimientoBancario($mov);
                        if ($poliza) {
                            $mov->update(['poliza_id' => $poliza->id]);
                            $mov->poliza_id = $poliza->id;
                        }
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("No se pudo generar póliza contable para movimiento bancario {$mov->id}: " . $e->getMessage());
                }
            }

            return $mov;

        });
    }


    public function findMovimiento($idBuscartipo, $modeloType, $cuentaId, $referencia = null)
    {

        $query = CatBancoCuentasMovimientos::where('referenciaable_id', $idBuscartipo)
         ->where('cancelado', 0)
         ->where('cuenta_bancaria_id', $cuentaId)
         ->where('referenciaable_type', $modeloType)
         ->where('tipo', 'cargo')
         ->where('concepto', 'not like', 'Devolución%')
         ->where('origen', 'sistema');


        if ($referencia) {
            $query->where('referencia', $referencia);
        }

        return $query->latest('id')->first();
    }
    public function cancelarMovimiento(int $cuentaId, int $movimientoId,string $fecha_cancelacionmovi )
    {
        return DB::transaction(function () use ($cuentaId, $movimientoId,$fecha_cancelacionmovi) {

            $movimiento = CatBancoCuentasMovimientos::where('id', $movimientoId)
                ->where('cuenta_bancaria_id', $cuentaId)
                ->firstOrFail();
            $fechaCancelacion = $this->parseFecha($fecha_cancelacionmovi);

            if ($movimiento->cancelado) {
                return [
                    'status' => false,
                    'message' => 'Este movimiento ya fue cancelado.'
                ];
            }


            $tipoInverso = $movimiento->tipo === 'cargo'
                ? 'abono'
                : 'cargo';


            $cancelacion = CatBancoCuentasMovimientos::create([
                'cuenta_bancaria_id' => $cuentaId,
                'tipo'               => $tipoInverso,
                'monto'              => $movimiento->monto,
                'concepto'           => 'Devolución - ' . $movimiento->concepto,
                'fecha_movimiento'   => $fechaCancelacion ?? now(),
                'origen'             => 'sistema',
                'referencia'         => 'cancelación' . ($movimiento->referencia ? ' - ' . $movimiento->referencia : ''),
                'referenciaable_type'=> $movimiento->referenciaable_type ?? null,
                'referenciaable_id'  => $movimiento->referenciaable_id ?? null,
                'detalles'           => $movimiento->detalles,
                'cancelado'          => false,
                'user_id'            => auth()->id(),
                'observaciones'      => 'movimiento_original_id |' . $movimiento->id . '|'. 'concepto_original|' . $movimiento->concepto,
            ]);


            $movimiento->update([
                'cancelado' => true
            ]);

            // Sync legacy saldo column
            $banco = Bancos::find($cuentaId);
            if ($banco) {
                if ($tipoInverso === 'abono') {
                    $banco->increment('saldo', $movimiento->monto);
                } else {
                    $banco->decrement('saldo', $movimiento->monto);
                }
            }

            return [
                'status' => true,
                'message' => 'Movimiento cancelado correctamente.',
                'data' => $cancelacion
            ];
        });
    }

    public function procesarIngresosDesdeCobro($request, $resultado, $cobroPagoId)
    {
        $contenedoresA = $resultado['contenedoresAbonos1'] ?? [];
        $contenedoresB = $resultado['contenedoresAbonos2'] ?? [];

        $cliente = \App\Models\Client::find($request->theClient);

        // =========================
        // BANCO A
        // =========================
        if ($request->filled('bankOne') && $request->amountPayOne > 0) {


            Bancos::where('id', $request->bankOne)
                ->update([
                    "saldo" => DB::raw("COALESCE(saldo, 0) + " . floatval($request->amountPayOne))
                ]);


            $this->registrarMovimiento([
                'cuenta_bancaria_id' => $request->bankOne,
                'tipo' => 'abono',
                'monto' => floatval($request->amountPayOne),
                'concepto' => 'Cobro viajes: ' . ($cliente?->nombre ?? ''),
                'fecha_movimiento' => $this->parseFecha($request->FechaAplicacionbank1),
                'referencia' => 'CXC A',
                'detalles' => $contenedoresA,
                'referenciaable_id' => $cobroPagoId,
                'referenciaable_type' => \App\Models\CobroPago::class,
            ]);
        }

        // =========================
        // BANCO B
        // =========================
        if ($request->filled('bankTwo') && $request->amountPayTwo > 0) {


            Bancos::where('id', $request->bankTwo)
                ->update([
                    "saldo" => DB::raw("COALESCE(saldo, 0) + " . floatval($request->amountPayTwo))
                ]);


            $this->registrarMovimiento([
                'cuenta_bancaria_id' => $request->bankTwo,
                'tipo' => 'abono',
                'monto' => floatval($request->amountPayTwo),
                'concepto' => 'Cobro viajes: ' . ($cliente?->nombre ?? ''),
                'fecha_movimiento' => $this->parseFecha($request->FechaAplicacionbank2),
                'referencia' => 'CXC B',
                'detalles' => $contenedoresB,
                'referenciaable_id' => $cobroPagoId,
                'referenciaable_type' => \App\Models\CobroPago::class,
            ]);
        }
    }


    public function guardarBancoLegacy($request, $contenedoresAbonos) //checar si podemos eliminar completo este bancos old
    {
        $banco = new BancoDinero();

        $banco->contenedores = json_encode($contenedoresAbonos);
        $banco->id_cliente = $request->theClient;

        $banco->monto1 = $request->amountPayOne;
        $banco->metodo_pago1 = "Transferencia";
        $banco->id_banco1 = $request->bankOne;

        $banco->monto2 = $request->amountPayTwo;
        $banco->metodo_pago2 = "Transferencia";
        $banco->id_banco2 = $request->bankTwo;

        $banco->fecha_pago = now();
        $banco->tipo = 'Entrada';

        $banco->save();
    }

    public static function generarConcepto(string $tipo, string $concepto, ?string $contenedor = null, ?string $operador = null, ?string $unidad = null): string
    {
        $tipoStr = strtoupper(trim($tipo));
        $conceptoStr = trim($concepto);

        $refContenedor = !empty($contenedor) ? " - Cont: " . trim($contenedor) : "";
        $refOperador = !empty($operador) ? " - Ope: " . trim($operador) : "";
        $refUnidad = !empty($unidad) ? " - " . (str_starts_with(trim($unidad), 'Unidad:') ? trim($unidad) : "Unidad: " . trim($unidad)) : "";

        return sprintf("[%s] %s%s%s%s", $tipoStr, $conceptoStr, $refContenedor, $refOperador, $refUnidad);
    }

    /**
     * Obtiene los catálogos de bancos, cuentas y unidades para la vista principal de CatBancos.
     */
    public function getCatBancosIndexData($user): array
    {
        $idEmpresa = $user->id_empresa;

        $catbancos = CatBanco::activos()
            ->where('id_empresa', $idEmpresa)
            ->withCount('cuentas')
            ->orderBy('orden')
            ->get()
            ->map(function ($banco) {
                $banco->tiene_cuentas = $banco->cuentas_count > 0;
                return $banco;
            });

        $cuentas = Bancos::where('id_empresa', $idEmpresa)
            ->where('estado', 1)
            ->get();

        $equipos = Equipo::where('id_empresa', $idEmpresa)
            ->where('tipo', 'Tractos / Camiones')
            ->orderBy('id_equipo')
            ->get();

        $CatBancosDefault = config('CatAuxiliares.catalogBank');

        return compact('catbancos', 'CatBancosDefault', 'cuentas', 'equipos');
    }

    /**
     * Registra un banco en el catálogo.
     */
    public function storeCatBanco(array $data, ?int $idEmpresa = null): CatBanco
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        return DB::transaction(function () use ($data, $idEmpresa) {
            return CatBanco::create([
                'nombre' => $data['nombre'],
                'codigo' => strtoupper($data['codigo']),
                'razon_social' => $data['razon_social'] ?? null,
                'logo' => $data['logo'] ?? null,
                'color' => $data['color'] ?? null,
                'color_secundario' => $data['color_secundario'] ?? null,
                'id_empresa' => $idEmpresa,
                'activo' => 1,
                'catalog_key' => $data['catalog_key'] ?? null,
            ]);
        });
    }

    /**
     * Actualiza un banco en el catálogo.
     */
    public function updateCatBanco(int $id, array $data, ?int $idEmpresa = null): CatBanco
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;
        $banco = CatBanco::where('id', $id)
            ->where('id_empresa', $idEmpresa)
            ->firstOrFail();

        return DB::transaction(function () use ($banco, $data) {
            $banco->update([
                'nombre' => $data['nombre'],
                'codigo' => strtoupper($data['codigo']),
                'razon_social' => $data['razon_social'] ?? null,
                'logo' => $data['logo'] ?? null,
                'color' => $data['color'] ?? null,
                'color_secundario' => $data['color_secundario'] ?? null,
                'catalog_key' => $data['catalog_key'] ?? null,
            ]);
            return $banco;
        });
    }

    /**
     * Desactiva un banco del catálogo.
     */
    public function desactivarCatBanco(int $id): bool
    {
        $banco = CatBanco::findOrFail($id);
        return $banco->update(['activo' => 0]);
    }

    /**
     * Obtiene las cuentas bancarias asociadas a un CatBanco.
     */
    public function getCuentasPorCatBanco(int $idCatBanco, ?int $idEmpresa = null): array
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        $catBanco = CatBanco::where('id', $idCatBanco)
            ->where('id_empresa', $idEmpresa)
            ->firstOrFail();

        $cuentas = Bancos::where('cat_banco_id', $catBanco->id)
            ->where('id_empresa', $idEmpresa)
            ->where('estado', 1)
            ->get();

        return compact('catBanco', 'cuentas');
    }

    /**
     * Registra una cuenta bancaria vinculada a un CatBanco.
     */
    public function storeCuentaBancaria(array $data, ?int $idEmpresa = null): Bancos
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        return DB::transaction(function () use ($data, $idEmpresa) {
            $banco1Val = (int) ($data['banco_1'] ?? (!empty($data['principal']) ? 1 : 0));

            if ($banco1Val === 1) {
                Bancos::where('id_empresa', $idEmpresa)->update(['banco_1' => 0]);
            }

            $catBanco = CatBanco::find($data['banco_id']);

            return Bancos::create([
                'nombre_beneficiario' => $data['beneficiario'],
                'nombre_banco' => $catBanco ? $catBanco->nombre : '',
                'moneda' => $data['moneda'],
                'cuenta_bancaria' => $data['numero_cuenta'],
                'clabe' => $data['clabe'] ?? null,
                'inicial_saldo' => $data['saldo_inicial'] ?? 0,
                'saldo' => 0,
                'tipo' => $data['tipo_cuenta'],
                'id_empresa' => $idEmpresa,
                'estado' => 1,
                'banco_1' => $banco1Val,
                'cat_banco_id' => $data['banco_id'],
            ]);
        });
    }

    /**
     * Actualiza una cuenta bancaria vinculada a un CatBanco.
     */
    public function updateCuentaBancaria(int $id, array $data, ?int $idEmpresa = null): Bancos
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        return DB::transaction(function () use ($id, $data, $idEmpresa) {
            $banco1Val = (int) ($data['banco_1'] ?? (!empty($data['principal']) ? 1 : 0));

            if ($banco1Val === 1) {
                Bancos::where('id_empresa', $idEmpresa)
                    ->where('id', '!=', $id)
                    ->update(['banco_1' => 0]);
            }

            $catBanco = CatBanco::find($data['banco_id']);
            $cuenta = Bancos::findOrFail($id);

            $cuenta->update([
                'nombre_beneficiario' => $data['beneficiario'],
                'nombre_banco' => $catBanco ? $catBanco->nombre : $cuenta->nombre_banco,
                'moneda' => $data['moneda'],
                'cuenta_bancaria' => $data['numero_cuenta'],
                'clabe' => $data['clabe'] ?? null,
                'inicial_saldo' => $data['saldo_inicial'] ?? 0,
                'saldo' => 0,
                'tipo' => $data['tipo_cuenta'],
                'estado' => 1,
                'banco_1' => $banco1Val,
                'cat_banco_id' => $data['banco_id'],
            ]);

            return $cuenta;
        });
    }

    /**
     * Aplica una transferencia entre dos cuentas bancarias de la misma empresa con validación de saldo.
     */
    public function transferenciaEntreCuentas(array $data, ?int $idEmpresa = null): array
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;
        $monto = (float) $data['monto'];
        $cuentaOrigen = (int) $data['cuenta_origen'];
        $cuentaDestino = (int) $data['cuenta_destino'];
        $fechaAplicacion = $data['fecha_aplicacion'];
        $concepto = $data['concepto'];

        $validarSaldo = $this->validarsaldoparacargo($idEmpresa, $cuentaOrigen, $fechaAplicacion, $monto);

        if ($validarSaldo["saldodisponible"] === false) {
            return [
                'success' => false,
                'status' => 422,
                'TMensaje' => "error",
                'Titulo' => "Saldo Insuficiente",
                'Mensaje' => $validarSaldo["message"]
            ];
        }

        return DB::transaction(function () use ($cuentaOrigen, $cuentaDestino, $monto, $fechaAplicacion, $concepto) {
            CatBancoCuentasMovimientos::create([
                'cuenta_bancaria_id' => $cuentaOrigen,
                'tipo' => 'cargo',
                'monto' => $monto,
                'concepto' => $concepto,
                'fecha_movimiento' => $fechaAplicacion,
                'origen' => 'transferencia',
                'referencia' => 'TR',
                'user_id' => auth()->id(),
            ]);

            CatBancoCuentasMovimientos::create([
                'cuenta_bancaria_id' => $cuentaDestino,
                'tipo' => 'abono',
                'monto' => $monto,
                'concepto' => $concepto,
                'fecha_movimiento' => $fechaAplicacion,
                'origen' => 'transferencia',
                'referencia' => 'TR',
                'user_id' => auth()->id(),
            ]);

            return [
                'success' => true,
                'status' => 200,
                'message' => 'Transferencia aplicada correctamente'
            ];
        });
    }

    /**
     * Obtiene los movimientos bancarios imputados o asociados a una unidad de transporte.
     */
    public function getMovimientosPorUnidad(int $idEquipo, ?int $idEmpresa = null): array
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        $cuentas = Bancos::where('id_empresa', $idEmpresa)
            ->where('estado', 1)
            ->with('catBanco')
            ->get();

        $cuentasIds = $cuentas->pluck('id')->toArray();

        if (empty($cuentasIds)) {
            return [
                'movimientos' => [],
                'bancos_resumen' => [],
                'total_movimientos' => 0,
                'total_monto' => 0,
                'total_monto_formateado' => '$0.00',
            ];
        }

        $movimientos = CatBancoCuentasMovimientos::whereIn('cuenta_bancaria_id', $cuentasIds)
            ->where('cancelado', false)
            ->deUnidad($idEquipo)
            ->with(['cuentaBancaria.catBanco'])
            ->orderBy('fecha_movimiento', 'desc')
            ->get();

        $bancosResumen = [];
        $totalMonto = 0;

        $formateados = $movimientos->map(function ($m) use (&$bancosResumen, &$totalMonto) {
            $catBancoId = $m->cuentaBancaria?->cat_banco_id;
            $catBancoNombre = $m->cuentaBancaria?->catBanco?->nombre ?? 'Banco';
            $monto = (float) $m->monto;

            if ($m->tipo === 'cargo') {
                $totalMonto += $monto;
            }

            if ($catBancoId) {
                if (!isset($bancosResumen[$catBancoId])) {
                    $bancosResumen[$catBancoId] = [
                        'cat_banco_id' => $catBancoId,
                        'banco_nombre' => $catBancoNombre,
                        'conteo' => 0,
                        'total' => 0,
                    ];
                }
                $bancosResumen[$catBancoId]['conteo']++;
                $bancosResumen[$catBancoId]['total'] += $monto;
            }

            return [
                'id' => $m->id,
                'fecha_movimiento' => $m->fecha_movimiento ? Carbon::parse($m->fecha_movimiento)->format('d/m/Y') : '-',
                'concepto' => $m->concepto,
                'referencia' => $m->referencia,
                'tipo' => $m->tipo,
                'monto' => $monto,
                'monto_formateado' => '$' . number_format($monto, 2),
                'banco_nombre' => $catBancoNombre,
                'cat_banco_id' => $catBancoId,
                'cuenta_bancaria' => $m->cuentaBancaria?->cuenta_bancaria ?? '-',
                'nombre_beneficiario' => $m->cuentaBancaria?->nombre_beneficiario ?? '-',
                'url_cuenta' => route('bancoscuentas.movimientos', $m->cuenta_bancaria_id),
            ];
        });

        foreach ($bancosResumen as &$res) {
            $res['total_formateado'] = '$' . number_format($res['total'], 2);
        }

        return [
            'movimientos' => $formateados,
            'bancos_resumen' => array_values($bancosResumen),
            'total_movimientos' => $movimientos->count(),
            'total_monto' => $totalMonto,
            'total_monto_formateado' => '$' . number_format($totalMonto, 2),
        ];
    }

    /**
     * Calcula los saldos consolidados para la vista legacy bancos.index.
     */
    public function getBancosIndexSaldos(?int $idEmpresa = null)
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;
        $bancos = Bancos::where('id_empresa', $idEmpresa)->get();

        foreach ($bancos as $banco) {
            $cotizaciones = Cotizaciones::where('id_banco1', $banco->id)->orWhere('id_banco2', $banco->id)->get();
            $proveedores = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
                ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
                ->whereNull('asignaciones.id_camion')
                ->where(function ($q) use ($banco) {
                    $q->where('cotizaciones.id_prove_banco1', $banco->id)
                      ->orWhere('cotizaciones.id_prove_banco2', $banco->id);
                })
                ->select('cotizaciones.*')
                ->get();

            $banco_dinero_entrada = BancoDinero::where('tipo', 'Entrada')
                ->where(function ($q) use ($banco) {
                    $q->where('id_banco1', $banco->id)->orWhere('id_banco2', $banco->id);
                })->get();

            $banco_dinero_salida = BancoDinero::where('tipo', 'Salida')
                ->where(function ($q) use ($banco) {
                    $q->where('id_banco1', $banco->id)->orWhere('id_banco2', $banco->id);
                })->get();

            $banco_dinero_salida_ope = BancoDineroOpe::whereIn('tipo', ['Entrada', 'Salida'])
                ->where(function ($q) use ($banco) {
                    $q->where('id_banco1', $banco->id)->orWhere('id_banco2', $banco->id);
                })->get();

            $gastos_generales = GastoImputacion::join('gastos', 'gastos.id', '=', 'gasto_imputaciones.gasto_id')
                ->join('gasto_pagos', 'gasto_pagos.gasto_id', '=', 'gastos.id')
                ->whereNull('gastos.deleted_at')
                ->where('gastos.estatus', '!=', 'cancelado')
                ->whereIn('gasto_imputaciones.tipo_imputacion', ['periodo', 'empresa'])
                ->where('gasto_pagos.cuenta_bancaria_id', $banco->id)
                ->select(
                    'gasto_imputaciones.*',
                    'gasto_imputaciones.monto_imputado as monto1',
                    'gasto_imputaciones.fecha_imputacion as fecha',
                    'gastos.concepto as motivo'
                )
                ->get();

            $banco_entrada = $banco_dinero_entrada->sum(function ($item) use ($banco) {
                return ($item->id_banco1 == $banco->id ? $item->monto1 : 0) + ($item->id_banco2 == $banco->id ? $item->monto2 : 0);
            });

            $banco_salida = $banco_dinero_salida->sum(function ($item) use ($banco) {
                return ($item->id_banco1 == $banco->id ? $item->monto1 : 0) + ($item->id_banco2 == $banco->id ? $item->monto2 : 0);
            });

            $total = $cotizaciones->sum(function ($item) use ($banco) {
                return ($item->id_banco1 == $banco->id ? $item->monto1 : 0) + ($item->id_banco2 == $banco->id ? $item->monto2 : 0);
            });

            $pagos = $proveedores->sum(function ($item) use ($banco) {
                return ($item->id_prove_banco1 == $banco->id ? $item->prove_monto1 : 0) + ($item->id_prove_banco2 == $banco->id ? $item->prove_monto2 : 0);
            });

            $pagos_salida = $banco_dinero_salida_ope->sum(function ($item) use ($banco) {
                return ($item->id_banco1 == $banco->id ? $item->monto1 : 0) + ($item->id_banco2 == $banco->id ? $item->monto2 : 0);
            });

            $gastos_extras = $gastos_generales->sum('monto1');
            $total_pagos = $pagos + $pagos_salida + $banco_salida + $gastos_extras;
            $saldo = ($banco->saldo_inicial + $total + $banco_entrada) - $total_pagos;

            $banco->saldo = $saldo;
        }

        return $bancos;
    }

    /**
     * Alterna el estado de cuenta global para un banco de la empresa.
     */
    public function toggleCuentaGlobal(int $idBanco, ?int $idEmpresa = null): array
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;
        $bancoSeleccionado = Bancos::findOrFail($idBanco);

        if ($bancoSeleccionado->cuenta_global) {
            $bancoSeleccionado->cuenta_global = false;
            $bancoSeleccionado->save();

            return ['success' => true, 'message' => 'Cuenta global desactivada.'];
        }

        $cuentaGlobalExistente = Bancos::where('cuenta_global', true)
            ->where('id_empresa', $idEmpresa)
            ->first();

        if ($cuentaGlobalExistente) {
            return [
                'success' => false,
                'message' => 'Ya existe una cuenta global activa. Debes desactivarla primero.'
            ];
        }

        $bancoSeleccionado->cuenta_global = true;
        $bancoSeleccionado->save();

        return ['success' => true, 'message' => 'Cuenta global activada correctamente.'];
    }

    /**
     * Alterna la marca de Banco 1 asegurando que pertenezca a la empresa propia.
     */
    public function toggleBanco1(int $idBanco, bool $activar, ?int $idEmpresa = null): array
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;
        $banco = Bancos::findOrFail($idBanco);

        if ((int) $banco->id_empresa !== (int) $idEmpresa) {
            return [
                'success' => false,
                'status' => 403,
                'message' => 'No tienes permisos sobre este banco.'
            ];
        }

        $empresaPropia = (bool) Empresas::where('id', $idEmpresa)->value('empresa_propia');
        if (!$empresaPropia) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'La empresa no está marcada como propia.'
            ];
        }

        if ($activar) {
            Bancos::where('id_empresa', $banco->id_empresa)
                ->where('id', '!=', $banco->id)
                ->update(['banco_1' => false]);

            $banco->banco_1 = true;
            $banco->save();

            return [
                'success' => true,
                'status' => 200,
                'message' => 'Banco activado como Banco 1.'
            ];
        }

        $banco->banco_1 = false;
        $banco->save();

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Banco desactivado como Banco 1.'
        ];
    }

    /**
     * Activa o inactiva (SoftDelete) un banco.
     */
    public function cambiarEstadoBanco(int $idBanco, int $estado): array
    {
        $banco = Bancos::withTrashed()->findOrFail($idBanco);

        if ($estado == 1) {
            $banco->restore();
            $banco->estado = 1;
        } else {
            $banco->estado = 0;
            $banco->delete();
        }

        $banco->save();

        return [
            'success' => true,
            'estado' => $banco->estado,
            'deleted_at' => $banco->deleted_at
        ];
    }

    /**
     * Obtiene los movimientos combinados de un banco para el reporte legacy de bancos.show.
     */
    public function getLegacyLedgerCombined(int $idBanco, $startOfWeek, $endOfWeek, ?int $idEmpresa = null): array
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;
        $banco = Bancos::where('id_empresa', $idEmpresa)->where('id', $idBanco)->firstOrFail();
        $fechaBanco = date('2024-10-21');

        $saldoDiario = BancoSaldoDiario::where([["fecha", "=", $startOfWeek], ["id_banco", $banco->id]]);
        $saldoInicial = $saldoDiario->exists() ? (float) $saldoDiario->first()->saldo_inicial : 0;

        $movimientosBancarios = MovimientoBancario::where('id_banco', $banco->id)
            ->where('is_active', 1)
            ->whereBetween('fecha_movimiento', [$startOfWeek, $endOfWeek])
            ->get();

        $cotizaciones = Cotizaciones::where(function ($query) use ($idBanco) {
            $query->where('id_banco1', '=', $idBanco)
                  ->orWhere('id_banco2', '=', $idBanco);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();

        $proveedores = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
            ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
            ->whereBetween('cotizaciones.fecha_pago_proveedor', [$startOfWeek, $endOfWeek])
            ->whereNull('asignaciones.id_camion')
            ->where(function ($query) use ($idBanco) {
                $query->where('cotizaciones.id_prove_banco1', '=', $idBanco)
                      ->orWhere('cotizaciones.id_prove_banco2', '=', $idBanco);
            })
            ->select('cotizaciones.*')
            ->get();

        $banco_dinero_entrada = BancoDinero::where('tipo', 'Entrada')
            ->where(function ($query) use ($idBanco) {
                $query->where('id_banco1', '=', $idBanco)
                      ->orWhere('id_banco2', '=', $idBanco);
            })
            ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
            ->get();

        $banco_dinero_salida = BancoDinero::where('tipo', 'Salida')
            ->where(function ($query) use ($idBanco) {
                $query->where('id_banco1', '=', $idBanco)
                      ->orWhere('id_banco2', '=', $idBanco);
            })
            ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
            ->get();

        $banco_dinero_salida_ope = BancoDineroOpe::whereIn('tipo', ['Entrada', 'Salida'])
            ->whereNull('contenedores')
            ->where(function ($query) use ($idBanco) {
                $query->where('id_banco1', '=', $idBanco)
                      ->orWhere('id_banco2', '=', $idBanco);
            })
            ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
            ->get();

        $banco_dinero_salida_ope_varios = BancoDineroOpe::whereIn('tipo', ['Entrada', 'Salida'])
            ->whereNull('id_cotizacion')
            ->where(function ($query) use ($idBanco) {
                $query->where('id_banco1', '=', $idBanco)
                      ->orWhere('id_banco2', '=', $idBanco);
            })
            ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
            ->get();

        $gastos_generales = GastoImputacion::join('gastos', 'gastos.id', '=', 'gasto_imputaciones.gasto_id')
            ->join('gasto_pagos', 'gasto_pagos.gasto_id', '=', 'gastos.id')
            ->whereNull('gastos.deleted_at')
            ->where('gastos.estatus', '!=', 'cancelado')
            ->whereIn('gasto_imputaciones.tipo_imputacion', ['periodo', 'empresa'])
            ->where('gasto_pagos.cuenta_bancaria_id', '=', $idBanco)
            ->whereBetween('gasto_imputaciones.fecha_imputacion', [$startOfWeek, $endOfWeek])
            ->select(
                'gasto_imputaciones.*',
                'gasto_imputaciones.monto_imputado as monto1',
                'gasto_imputaciones.fecha_imputacion as fecha',
                'gastos.concepto as motivo'
            )
            ->get();

        $totalSalidas = $banco_dinero_salida->where('fecha_pago', '>=', $fechaBanco)
            ->map(fn ($item) => $item->id_banco1 == $idBanco ? $item->monto1 : ($item->id_banco2 == $idBanco ? $item->monto2 : 0))->sum()
            + $proveedores->where('fecha_pago_proveedor', '>=', $fechaBanco)
            ->map(fn ($item) => $item->id_prove_banco1 == $idBanco ? $item->prove_monto1 : ($item->id_prove_banco2 == $idBanco ? $item->prove_monto2 : 0))->sum()
            + $banco_dinero_salida_ope->where('fecha_pago', '>=', $fechaBanco)
            ->map(fn ($item) => $item->id_banco1 == $idBanco ? $item->monto1 : ($item->id_banco2 == $idBanco ? $item->monto2 : 0))->sum()
            + $banco_dinero_salida_ope_varios->where('fecha_pago', '>=', $fechaBanco)
            ->map(fn ($item) => $item->id_banco1 == $idBanco ? $item->monto1 : ($item->id_banco2 == $idBanco ? $item->monto2 : 0))->sum()
            + $gastos_generales->where('fecha', '>=', $fechaBanco)->sum('monto1')
            + $movimientosBancarios->where('tipo_movimiento', 0)->sum('monto');

        $totalEntradas = $cotizaciones->where('fecha_pago', '>=', $fechaBanco)
            ->map(fn ($item) => $item->id_banco1 == $idBanco ? $item->monto1 : ($item->id_banco2 == $idBanco ? $item->monto2 : 0))->sum()
            + $banco_dinero_entrada->where('fecha_pago', '>=', $fechaBanco)
            ->map(fn ($item) => $item->id_banco1 == $idBanco ? $item->monto1 : ($item->id_banco2 == $idBanco ? $item->monto2 : 0))->sum()
            + $movimientosBancarios->where('tipo_movimiento', 1)->sum('monto');

        $saldoFinal = $saldoInicial + $totalEntradas - $totalSalidas;
        $empresaPropiaUsuario = (bool) Empresas::where('id', $idEmpresa)->value('empresa_propia');

        $combined = collect()
            ->merge($cotizaciones)
            ->merge($banco_dinero_entrada)
            ->merge($proveedores)
            ->merge($banco_dinero_salida_ope)
            ->merge($banco_dinero_salida_ope_varios)
            ->merge($banco_dinero_salida)
            ->merge($gastos_generales)
            ->merge($movimientosBancarios)
            ->sortBy(function ($item) {
                if (isset($item->fecha_pago)) {
                    return Carbon::parse($item->fecha_pago);
                } elseif (isset($item->fecha_pago_proveedor)) {
                    return Carbon::parse($item->fecha_pago_proveedor);
                } elseif (isset($item->fecha)) {
                    return Carbon::parse($item->fecha);
                }
                return null;
            });

        return compact(
            'combined',
            'empresaPropiaUsuario',
            'startOfWeek',
            'endOfWeek',
            'banco',
            'cotizaciones',
            'proveedores',
            'banco_dinero_entrada',
            'banco_dinero_salida',
            'banco_dinero_salida_ope',
            'banco_dinero_salida_ope_varios',
            'gastos_generales',
            'saldoInicial',
            'saldoFinal'
        );
    }

    /**
     * Registra un movimiento bancario manual legacy.
     */
    public function registrarMovimientoLegacy(array $data): void
    {
        DB::transaction(function () use ($data) {
            MovimientoBancario::create([
                'id_banco' => $data['bank'],
                'tipo_movimiento' => $data['tipoTransaccion'],
                'descripcion_movimiento' => $data['txtDescripcion'],
                'monto' => $data['txtMonto'],
                'fecha_movimiento' => date('Y-m-d'),
                'is_active' => true
            ]);

            $operacion = (intval($data['tipoTransaccion']) == 1) ? '+' : '-';

            Bancos::where('id', $data['bank'])->update([
                "saldo" => DB::raw("saldo $operacion " . (float) $data['txtMonto'])
            ]);
        });
    }
}

