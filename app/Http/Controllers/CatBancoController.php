<?php

namespace App\Http\Controllers;

use App\Models\CatBanco;
use App\Models\Bancos;
use App\Models\CatBancoCuentasMovimientos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\BancosService;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CuentaBancosExport;

class CatBancoController extends Controller
{
    protected $bankService;

    public function __construct(BancosService $bankService)
    {
        $this->bankService = $bankService;
    }

    // public function __construct()
    // {
    //     $this->middleware('auth');

    //     $this->middleware('permission:bancos-ver')->only('index');
    //     $this->middleware('permission:bancos-create')->only('store');
    //     $this->middleware('permission:bancos-edit')->only('update');
    //     $this->middleware('permission:bancos-delete')->only('destroy');
    // }


    public function index()
    {
        $catbancos = CatBanco::activos()
    ->where('id_empresa', Auth::user()->id_empresa)
    ->withCount('cuentas')
    ->orderBy('orden')
    ->get()
    ->map(function ($banco) {
        $banco->tiene_cuentas = $banco->cuentas_count > 0;
        return $banco;
    });


        $cuentas = Bancos::where('id_empresa', Auth::user()->id_empresa)
                   ->where('estado', 1)
                   ->get();

        $CatBancosDefault = config('CatAuxiliares.catalogBank');

        return view('bancos.indexv2', compact('catbancos', 'CatBancosDefault', 'cuentas'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'nombre'        => 'required|string|max:100',
            'codigo'        => 'required|string|max:10|unique:cat_bancos,codigo',
            'razon_social'  => 'nullable|string|max:150',
            'logo'          => 'nullable|string|max:255',
            'color'         => 'nullable|string|max:20',
            'color_secundario'         => 'nullable|string|max:20',
            'catalog_key'         => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($request) {
            CatBanco::create([
                'nombre'       => $request->nombre,
                'codigo'       => strtoupper($request->codigo),
                'razon_social' => $request->razon_social,
                'logo'         => $request->logo,
                'color'        => $request->color,
                'color_secundario'        => $request->color_secundario,
                'id_empresa'   => Auth::user()->id_empresa,
                'activo'       => 1,
                'catalog_key'       => $request->catalog_key,
            ]);
        });

        return response()->json([
    'success' => true,
    'message' => 'Banco creado correctamente'
]);
    }


    public function update(Request $request, $id)
    {
        $banco = CatBanco::where('id', $id)
    ->where('id_empresa', auth()->user()->id_empresa)
    ->firstOrFail();

        $request->validate([
            'nombre'        => 'required|string|max:100',
            'codigo'        => 'required|string|max:10|unique:cat_bancos,codigo,' . $banco->id,
            'razon_social'  => 'nullable|string|max:150',
            'logo'          => 'nullable|string|max:255',
            'color'         => 'nullable|string|max:20',
 'color_secundario'         => 'nullable|string|max:20',
            'catalog_key'         => 'nullable|string|max:50',

        ]);

        DB::transaction(function () use ($request, $banco) {
            $banco->update([
                'nombre'       => $request->nombre,
                'codigo'       => strtoupper($request->codigo),
                'razon_social' => $request->razon_social,
                'logo'         => $request->logo,
                'color'        => $request->color,
                 'color_secundario'        => $request->color_secundario,
                  'catalog_key'       => $request->catalog_key,
            ]);
        });

        return response()->json([
    'success' => true,
    'message' => 'Banco actualizado correctamente'
]);
    }


    public function destroy($id)
    {
        $banco = CatBanco::findOrFail($id);

        $banco->update([
            'activo' => 0
        ]);

        return redirect()->back()->with('success', 'Banco desactivado');
    }

    public function create_cuentas($id)
    {
        $empresaId = auth()->user()->id_empresa;

        $catBanco = CatBanco::where('id', $id)
            ->where('id_empresa', $empresaId)
            ->firstOrFail();

        $cuentas = Bancos::where('cat_banco_id', $catBanco->id)
            ->where('id_empresa', $empresaId)
            ->where('estado', 1)
            ->get();

        //vamos a mapear cuentas y buscar sus movimientos

        return view('bancos.index-cuentas', compact('catBanco', 'cuentas'));
    }

    public function store_cuentas(Request $request)
    {
        $request->validate([
            'banco_id'       => 'required|integer',
            'tipo_cuenta'    => 'required|string',
            'moneda'         => 'required|string',
            'numero_cuenta'  => 'required|string|max:50',
            'clabe'          => 'nullable|string|max:18',
            'beneficiario'   => 'required|string|max:255',
            'saldo_inicial'  => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {


            if ($request->has('principal')) {
                Bancos::where('id_empresa', auth()->user()->id_empresa)
                    ->update(['banco_1' => 0]);
            }

            $cat_bancos = CatBanco::find($request->banco_id)->first();

            $cuenta = Bancos::create([
                'nombre_beneficiario' => $request->beneficiario,
                'nombre_banco'        =>  $cat_bancos->nombre,
                'moneda' => $request->moneda,
                'cuenta_bancaria'     => $request->numero_cuenta,
                'clabe'               => $request->clabe,
                'inicial_saldo'       => $request->saldo_inicial ?? 0,
                'saldo'               => 0, //este saldo debe ser dinamico
                'tipo'                => $request->tipo_cuenta,
                'id_empresa'          => auth()->user()->id_empresa,
                'estado'              => 1,
                'banco_1'             => $request->has('principal') ? 1 : 0,
                'cat_banco_id'        => $request->banco_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cuenta bancaria registrada correctamente',
                'data'    => $cuenta
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la cuenta',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function update_cuentas(Request $request, $id)
    {


        $request->validate([
               'banco_id'       => 'required|integer',
               'tipo_cuenta'    => 'required|string',
               'moneda'         => 'required|string',
               'numero_cuenta'  => 'required|string|max:50',
               'clabe'          => 'nullable|string|max:18',
               'beneficiario'   => 'required|string|max:255',
               'saldo_inicial'  => 'required|numeric|min:0',
           ]);

        DB::beginTransaction();

        try {


            if ($request->has('principal')) {
                Bancos::where('id_empresa', auth()->user()->id_empresa)
                    ->update(['banco_1' => 0]);
            }

            $cat_bancos = CatBanco::find($request->banco_id)->first();
            $cuenta = Bancos::find($id);

            $cuenta = $cuenta->update([
                'nombre_beneficiario' => $request->beneficiario,
                'nombre_banco'        =>  $cat_bancos->nombre,
                'moneda' => $request->moneda,
                'cuenta_bancaria'     => $request->numero_cuenta,
                'clabe'               => $request->clabe,
                'inicial_saldo'       => $request->saldo_inicial ?? 0,
                'saldo'               => 0, //este saldo debe ser dinamico
                'tipo'                => $request->tipo_cuenta,

                'estado'              => 1,
                'banco_1'             => $request->has('principal') ? 1 : 0,
                'cat_banco_id'        => $request->banco_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cuenta bancaria actualizada correctamente',
                'data'    => $cuenta
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la cuenta',
                'error'   => $e->getMessage()
            ], 500);
        }


    }


    public function mostrar_movimientos($id)
    {
        $empresaId = auth()->user()->id_empresa;

        $data =   $this->bankService->obtenerDetalleCuenta($id, $empresaId);


        //  dd($data);

        // $movimientos =  $cuenta->movimientos;
        // //  dd($movimientos);
        // $conteoDepositos = $movimientos->where('tipo', 'abono')->count();
        // $totalDepositos  = $movimientos->where('tipo', 'abono')->sum('monto');

        // $conteoCargos = $movimientos->where('tipo', 'cargo')->count();
        // $totalCargos  = $movimientos->where('tipo', 'cargo')->sum('monto');

        // $saldoIni = (float) ($cuenta->saldo_inicial ?? 0);

        // $saldoActual = $saldoIni +  $totalDepositos - $totalCargos;

        // $saldo = $saldoIni;
        // foreach ($movimientos as $mov) {

        //     if ($mov->tipo === 'abono') {
        //         $saldo += (float) $mov->monto;
        //     } else {
        //         $saldo -= (float) $mov->monto;
        //     }

        //     $mov->saldo_resultante = $saldo;
        // }

        // $movimientos = $cuenta->movimientosConSaldo();

        // $saldoActual = $cuenta->saldo_actual;
        // $conteoDepositos = $cuenta->conteo_depositos;
        // $totalDepositos = $cuenta->total_depositos;
        // $conteoCargos = $cuenta->conteo_cargos;
        // $totalCargos = $cuenta->total_cargos;

        // dd($saldoIni, $cargos, $abonos, $saldoActual);
        return view('bancos.catbancos-cuentas-movimientos', $data);
    }


    public function getmovimientosperiodo(Request $request, $idcuenta)
    {
        $empresaId = auth()->user()->id_empresa;

        $fecha_ini = $request->fecha_inicio;
        $fecha_fin = $request->fecha_fin;

        $data =   $this->bankService->obtenerDetalleCuenta($idcuenta, $empresaId, $fecha_ini, $fecha_fin);

        return response()->json($data);
    }


    public function store_movimientos_cuentas(Request $request, bancos $cuenta)
    {

        $request->validate([
        'tipo'       => ['required', 'in:abono,cargo'],
        'concepto'   => ['required', 'string'],
        'monto'      => ['required', 'numeric', 'gt:0'],
        'fecha_movimiento'      => ['required', 'date'],
        'referencia' => ['nullable', 'string'],
        'origen' => 'required|in:manual,banco,ajuste,importacion',

    ]);

        $cuenta->movimientos()->create([
            'tipo'         => $request->tipo,
            'concepto'     => $request->concepto,
            'monto'        => $request->monto,
            'fecha_movimiento'        => $request->fecha_movimiento,
            'referencia'   => $request->referencia,
              'origen' => $request->origen,
              'user_id' => auth()->id()

        ]);

        return response()->json([
    'success' => true,
    'message' => 'El movimiento se registró correctamente'
]);
    }


    public function transferencia(Request $request)
    {
        $request->validate([
            'cuenta_origen'      => 'required|different:cuenta_destino',
            'cuenta_destino'     => 'required',
            'concepto'           => 'required|string|max:255',
            'monto'              => 'required|numeric|min:0.01',
            'fecha_aplicacion'   => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            //$referencia =
            $empresaId = auth()->user()->id_empresa;
            $validarSaldo = $this->bankService->validarsaldoparacargo($empresaId, $request->cuenta_origen, $request->fecha_aplicacion, $request->monto);
            if ($validarSaldo["saldodisponible"] == false) {

                return response()->json([
                                  "TMensaje" => "error",
                               "Titulo" => "Saldo Insuficiente",
                                  "Mensaje" => $validarSaldo["message"],
                                  'success' => false

                                 ]);

            }



            CatBancoCuentasMovimientos::create([
                'cuenta_bancaria_id'   => $request->cuenta_origen,
                'tipo'        => 'cargo',
                'monto'       => $request->monto,
                'concepto'    => $request->concepto,
                'fecha_movimiento'       => $request->fecha_aplicacion,
                'origen'       => 'transferencia',
                'referencia'  => 'TR',
                'user_id' => auth()->id(),

            ]);


            CatBancoCuentasMovimientos::create([
                'cuenta_bancaria_id'   => $request->cuenta_destino,
                'tipo'        => 'abono',
                'monto'       => $request->monto,
                'concepto'    => $request->concepto,
             'fecha_movimiento'       => $request->fecha_aplicacion,
                'origen'       => 'transferencia',
                'referencia'  => 'TR',
                 'user_id' => auth()->id(),

            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transferencia aplicada correctamente'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al aplicar la transferencia',
                'error'   => $e->getMessage()
            ], 500);
        }
    }




    public function exportar(Request $request)
    {

        $request->validate([
        'cuenta_id' => 'required|integer',
        'formato' => 'required|in:pdf,excel',
        'fecha_inicio' => 'nullable|date',
        'fecha_fin' => 'nullable|date',
    ]);

        $empresaId = auth()->user()->id_empresa;

        $data = $this->bankService->obtenerDetalleCuenta($request->cuenta_id, $empresaId, $request->fecha_inicio, $request->fecha_fin);

        if ($request->formato === 'excel') {
            $movimientos = $data['movimientos'];

            return Excel::download(
                new CuentaBancosExport($movimientos, $data['cuenta'], $data['saldoAnterior'], $data['total_depositos'], $data['total_cargos'], $data['saldoActual']),
                'movimientos.xlsx'
            );
        }

        $data['fecha_inicio'] = Carbon::parse($request->fecha_inicio)->toDateString();
        $data['fecha_fin']    = Carbon::parse($request->fecha_fin)->toDateString();

        if ($request->formato === 'pdf') {
            // dd('algo');
            $pdf = PDF::loadView('bancos.pdf_catbancos_cuentas_movimientos', $data);
            return $pdf->download('movimientos.pdf');
        }

        abort(400);


    }

}
