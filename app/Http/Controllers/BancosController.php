<?php

namespace App\Http\Controllers;

use App\Models\Bancos;
use App\Models\BancoSaldoDiario;
use App\Models\CatBanco;
use App\Services\BancosService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BancosController extends Controller
{
    protected BancosService $bancosService;

    public function __construct(BancosService $bancosService)
    {
        $this->bancosService = $bancosService;
    }

    public function index(): View
    {
        $bancos = $this->bancosService->getBancosIndexSaldos();

        return view('bancos.index', compact('bancos'));
    }

    public function list()
    {
        $fecha = Carbon::now()->format('Y-m-d');

        return $this->bancosService->getCuentasOption(auth()->user()->id_empresa, $fecha, $fecha, true);
    }

    public function cambiarEstado(Request $request, $id): JsonResponse
    {
        $result = $this->bancosService->cambiarEstadoBanco((int) $id, (int) $request->estado);

        return response()->json($result);
    }

    public function store(Request $request): RedirectResponse
    {
        $banco = new Bancos();
        $banco->nombre_beneficiario = $request->get('nombre_beneficiario');
        $banco->nombre_banco = $request->get('nombre_banco');
        $banco->cuenta_bancaria = $request->get('cuenta_bancaria');
        $banco->clabe = $request->get('clabe');
        $banco->saldo_inicial = $request->get('saldo_inicial');
        $banco->saldo = 0;
        $banco->id_empresa = auth()->user()->id_empresa;
        $banco->save();

        return redirect()->route('index.bancos')
            ->with('success', 'Banco creado exitosamente.');
    }

    public function edit($id): View
    {
        $startOfWeek = Carbon::now()->format('Y-m-d');
        $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d');
        $fecha = date('Y-m-d');

        $data = $this->bancosService->getLegacyLedgerCombined((int) $id, $startOfWeek, $endOfWeek);
        $data['fecha'] = $fecha;

        return view('bancos.show', $data);
    }

    public function advance_bancos(Request $request, $id): View
    {
        $startOfWeek = $request->get('fecha_de');
        $endOfWeek = $request->get('fecha_hasta');
        $fecha = date('Y-m-d');

        $data = $this->bancosService->getLegacyLedgerCombined((int) $id, $startOfWeek, $endOfWeek);
        $data['fecha'] = $fecha;

        return view('bancos.show', $data);
    }

    public function update(Request $request, Bancos $id): RedirectResponse
    {
        $id->update($request->all());

        return redirect()->back()->with('success', 'Banco editado exitosamente');
    }

    public function pdf(Request $request, $id)
    {
        $startOfWeek = $request->get('fecha_de') ?? Carbon::now()->startOfWeek()->format('Y-m-d');
        $endOfWeek = $request->get('fecha_hasta') ?? Carbon::now()->endOfWeek()->format('Y-m-d');
        $fecha = date('Y-m-d');

        $data = $this->bancosService->getLegacyLedgerCombined((int) $id, $startOfWeek, $endOfWeek);
        $banco = $data['banco'];
        $combined = $data['combined'];

        $penultimaTotal = 0;
        $ultimaTotal = 0;

        foreach ($combined as $item) {
            if (isset($item->fecha_pago)) {
                $amount = isset($item->id_banco1) && $item->id_banco1 == $banco->id
                    ? $item->monto1
                    : $item->monto2;

                if (!isset($item->id_operador)) {
                    $penultimaTotal += $amount;
                } else {
                    $ultimaTotal += $amount;
                }
            } elseif (isset($item->fecha_pago_proveedor)) {
                $amount = isset($item->id_prove_banco1) && $item->id_prove_banco1 == $banco->id
                    ? $item->prove_monto1
                    : $item->prove_monto2;
                $ultimaTotal += $amount;
            } elseif (isset($item->fecha)) {
                $amount = $item->monto1;
                $ultimaTotal += $amount;
            }
        }

        $diferencia = $penultimaTotal - $ultimaTotal;

        $pdf = Pdf::loadView('bancos.pdf_banco', compact('combined', 'startOfWeek', 'fecha', 'banco', 'ultimaTotal', 'penultimaTotal', 'diferencia'));

        return $pdf->download('Reporte Banco.pdf');
    }

    public static function saldos_diarios(): void
    {
        $saldoDiarios = BancoSaldoDiario::where('fecha', Carbon::now()->format('Y-m-d'));
        if (!$saldoDiarios->exists()) {
            $bancos = Bancos::selectRaw('id as id_banco, now() as fecha, saldo as saldo_inicial, saldo as saldo_final')->get()->toArray();
            BancoSaldoDiario::insert($bancos);
        }
    }

    public function registrar_movimiento(Request $r): JsonResponse
    {
        try {
            $this->bancosService->registrarMovimientoLegacy($r->all());

            return response()->json([
                "Titulo" => "Movimiento Exitoso",
                "Mensaje" => "Movimiento aplicado correctamente",
                "TMensaje" => "success"
            ]);
        } catch (\Throwable $t) {
            return response()->json([
                "Titulo" => "Error",
                "Mensaje" => "Error: " . $t->getMessage(),
                "TMensaje" => "error"
            ]);
        }
    }

    public function cambiarCuentaGlobal(Request $request, $id): JsonResponse
    {
        $result = $this->bancosService->toggleCuentaGlobal((int) $id);

        return response()->json($result);
    }

    public function cambiarBanco1(Request $request, $id): JsonResponse
    {
        $activar = (bool) $request->input('value', false);
        $result = $this->bancosService->toggleBanco1((int) $id, $activar);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message']
        ], $result['status'] ?? 200);
    }

    public function index2(): View
    {
        $catbancos = CatBanco::all()->sortBy('orden');

        return view('bancos.indexv2', compact('catbancos'));
    }
}
