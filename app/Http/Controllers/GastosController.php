<?php

namespace App\Http\Controllers;

use App\Models\Bancos;
use App\Models\Gasto;
use App\Models\GastoPago;
use App\Models\GastoConcepto;
use App\Models\Empresas;
use App\Services\GastosService;
use App\Services\BancosService;
use App\Exceptions\SaldoInsuficienteException;
use App\Http\Requests\StoreGastoRequest;
use App\Http\Requests\TransferenciaBancariaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GastosController extends Controller
{
    public function __construct(
        private GastosService $gastosService,
        private BancosService $bancosService
    ) {
    }

    public function index()
    {
        $idEmpresa = auth()->user()->id_empresa;
        $empresa = Empresas::withoutGlobalScopes()->find($idEmpresa);

        $catalogos = $this->gastosService->getCatalogosIndex($idEmpresa);
        $catalogos['empresaActual'] = $empresa;
        $catalogos['requiereUnidadGasto'] = (bool) ($empresa?->requiere_unidad_gasto ?? false);

        return view('gastos.index', $catalogos);
    }

    public function data(Request $request)
    {
        return response()->json([
            'TMensaje' => 'success',
            'gastos' => $this->gastosService->listar([
                'id_empresa' => auth()->user()->id_empresa,
                'from' => $request->from,
                'to' => $request->to,
                'tipo_gasto' => $request->tipo_gasto,
                'search' => $request->search,
                'cotizacion_id' => $request->cotizacion_id,
                'categoria_id' => $request->categoria_id,
                'subcategoria_id' => $request->subcategoria_id,
                'id_equipo' => $request->id_equipo,
            ]),
        ]);
    }

    public function store(StoreGastoRequest $request)
    {
        try {
            $gasto = $this->gastosService->prepararYRegistrarGasto(
                $request->all(),
                auth()->user()
            );

            return response()->json([
                'TMensaje' => 'success',
                'Titulo' => 'Gasto registrado',
                'Mensaje' => 'El gasto se registró correctamente.',
                'gasto' => $gasto,
            ]);
        } catch (SaldoInsuficienteException $e) {
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Saldo insuficiente',
                'Mensaje' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Error al registrar',
                'Mensaje' => 'Ocurrió un error al guardar el gasto: ' . $e->getMessage(),
            ]);
        }
    }

    public function pay(Request $request, Gasto $gasto)
    {
        abort_unless($gasto->id_empresa === auth()->user()->id_empresa, 403);

        $data = $request->validate([
            'cuenta_bancaria_id' => ['required', 'exists:bancos,id'],
            'fecha_pago' => ['required', 'date'],
            'monto' => ['nullable', 'numeric', 'min:0.01'],
            'referencia' => ['nullable', 'string', 'max:100'],
            'comprobante' => ['nullable', 'string', 'max:255'],
        ]);

        $gasto->loadMissing(['equipo', 'vinculos']);
        $equipoNombre = null;
        if ($gasto->equipo) {
            $equipoNombre = $gasto->equipo->id_equipo ?: $gasto->equipo->placas;
        } elseif ($gasto->id_equipo) {
            $eq = \App\Models\Equipo::find($gasto->id_equipo);
            $equipoNombre = $eq ? ($eq->id_equipo ?: $eq->placas) : null;
        } else {
            $vUnidad = $gasto->vinculos->firstWhere('tipo_vinculo', 'unidad');
            if ($vUnidad) {
                $eq = \App\Models\Equipo::find($vUnidad->vinculable_id);
                $equipoNombre = $eq ? ($eq->id_equipo ?: $eq->placas) : null;
            }
        }

        $data['concepto_banco'] = BancosService::generarConcepto('gasto', $gasto->concepto, null, null, $equipoNombre);
        $data['referencia_banco'] = $data['referencia'] ?? 'PAGO GASTO';

        $pago = $this->gastosService->pagar($gasto, $data);

        return response()->json([
            'TMensaje' => 'success',
            'Titulo' => 'Pago aplicado',
            'Mensaje' => 'El pago se registro correctamente en gastos y bancos.',
            'pago' => $pago,
        ]);
    }

    public function historialPagos(Gasto $gasto)
    {
        return response()->json([
            'TMensaje' => 'success',
            'pagos' => $this->gastosService->obtenerHistorialPagos($gasto),
        ]);
    }

    public function payMultiple(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
            'cuenta_bancaria_id' => ['required', 'exists:bancos,id'],
            'fecha_pago' => ['required', 'date'],
            'referencia' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $this->gastosService->pagarMultipleGastos(
                $data['ids'],
                (int) $data['cuenta_bancaria_id'],
                $data['fecha_pago'],
                $data['referencia'] ?? null,
                auth()->user()
            );

            return response()->json([
                'TMensaje' => 'success',
                'Titulo' => 'Pagos aplicados',
                'Mensaje' => 'Los pagos se aplicaron correctamente en gastos y bancos.',
            ]);
        } catch (SaldoInsuficienteException $e) {
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Saldo insuficiente',
                'Mensaje' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Error al pagar',
                'Mensaje' => 'Ocurrió un error al procesar los pagos: ' . $e->getMessage(),
            ]);
        }
    }

    public function cancelarPago(Request $request, GastoPago $pago)
    {
        $this->gastosService->cancelarPago(
            $pago,
            $request->fecha_cancelacion ?? now()->format('Y-m-d')
        );

        return response()->json([
            'TMensaje' => 'success',
            'Titulo' => 'Pago cancelado',
            'Mensaje' => 'El pago fue cancelado correctamente.',
        ]);
    }

    public function update(StoreGastoRequest $request, Gasto $gasto)
    {
        abort_unless($gasto->id_empresa === auth()->user()->id_empresa, 403);

        try {
            $gastoUpdated = $this->gastosService->actualizarGasto(
                $gasto,
                $request->all(),
                auth()->user()
            );

            return response()->json([
                'TMensaje' => 'success',
                'Titulo' => 'Gasto actualizado',
                'Mensaje' => 'El gasto y sus imputaciones se actualizaron correctamente.',
                'gasto' => $gastoUpdated,
            ]);
        } catch (SaldoInsuficienteException $e) {
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Saldo insuficiente en banco',
                'Mensaje' => $e->getMessage(),
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Gasto no editable',
                'Mensaje' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Error al actualizar',
                'Mensaje' => 'Ocurrió un error al actualizar el gasto: ' . $e->getMessage(),
            ]);
        }
    }

    public function getConceptosByCategoria($categoriaId)
    {
        $conceptos = GastoConcepto::where('categoria_gasto_id', $categoriaId)
            ->where('is_active', 1)
            ->orderBy('nombre')
            ->get();

        return response()->json($conceptos);
    }

    public function destroy(Request $request, Gasto $gasto)
    {
        abort_unless($gasto->id_empresa === auth()->user()->id_empresa, 403);

        try {
            $this->gastosService->eliminarGasto($gasto, $request->fecha_cancelacion);

            return response()->json([
                'TMensaje' => 'success',
                'Titulo' => 'Gasto eliminado',
                'Mensaje' => 'El gasto y sus pagos asociados fueron eliminados correctamente.',
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Acción rechazada',
                'Mensaje' => $e->getMessage(),
            ]);
        } catch (\Throwable $t) {
            Log::channel('daily')->error('Error al eliminar gasto desde modulo unificado', [
                'gasto_id' => $gasto->id,
                'error' => $t->getMessage(),
            ]);

            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Error al eliminar',
                'Mensaje' => 'Ocurrió un error al intentar eliminar el gasto: ' . $t->getMessage(),
            ]);
        }
    }

    public function getCuentasBancarias(Request $request)
    {
        $fecha = $request->input('fecha', now()->format('Y-m-d'));
        $validarSaldo = $request->boolean('validar_saldo', false);
        $empresaId = auth()->user()->id_empresa;

        $cuentas = $this->bancosService->getCuentasOption(
            $empresaId,
            $fecha,
            $fecha,
            $validarSaldo
        );

        return response()->json([
            'TMensaje' => 'success',
            'cuentas' => $cuentas,
        ]);
    }

    public function storeMovimientoBancario(Request $request)
    {
        $validated = $request->validate([
            'cuenta_bancaria_id' => 'required|exists:bancos,id',
            'tipo'               => 'required|in:abono,cargo',
            'concepto'           => 'required|string|max:255',
            'monto'              => 'required|numeric|not_in:0|min:0.01',
            'fecha_movimiento'   => 'required|date',
            'referencia'         => 'nullable|string|max:100',
            'origen'             => 'required|string|max:50',
        ]);

        try {
            $movimiento = $this->bancosService->registrarMovimiento([
                'cuenta_bancaria_id' => $validated['cuenta_bancaria_id'],
                'tipo'               => $validated['tipo'],
                'concepto'           => $validated['concepto'],
                'monto'              => $validated['monto'],
                'fecha_movimiento'   => $validated['fecha_movimiento'],
                'referencia'         => $validated['referencia'] ?? null,
                'origen'             => $validated['origen'] ?? 'manual',
            ]);

            return response()->json([
                'TMensaje' => 'success',
                'Titulo' => 'Movimiento registrado',
                'Mensaje' => 'El movimiento bancario fue registrado correctamente.',
                'movimiento' => $movimiento,
                'cuenta_id' => $validated['cuenta_bancaria_id'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'TMensaje' => 'error',
                'Titulo' => 'Error al registrar',
                'Mensaje' => 'Ocurrió un error al registrar el movimiento bancario: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function storeTransferenciaBancaria(TransferenciaBancariaRequest $request)
    {
        $res = $this->bancosService->transferenciaEntreCuentas(
            $request->validated(),
            auth()->user()->id_empresa
        );

        if (!($res['success'] ?? true)) {
            return response()->json($res, $res['status'] ?? 422);
        }

        return response()->json([
            'TMensaje' => 'success',
            'Titulo' => 'Transferencia realizada',
            'Mensaje' => 'La transferencia entre cuentas se aplicó correctamente.',
            'cuenta_origen_id' => $request->cuenta_origen,
            'cuenta_destino_id' => $request->cuenta_destino,
        ]);
    }
}
