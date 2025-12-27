<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Operador;
use App\Models\Prestamo;
use App\Models\PagoPrestamo;
use App\Models\Bancos;
use App\Models\BancoDinero;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PrestamosController extends Controller
{
    public function index()
    {
        $operadores = Operador::where('id_empresa', auth()->user()->id_empresa)->get();
        $bancos = Bancos::where('id_empresa', auth()->user()->id_empresa)->get();

        $prestamos = Prestamo::with(['operador', 'banco', 'pagoprestamos'])
           ->when(!auth()->user()->is_admin, function ($q) {
               $q->whereHas('operador', function ($q2) {
                   $q2->where('id_empresa', auth()->user()->id_empresa);
               });
           })
           ->orderBy('created_at', 'desc')
           ->get();

        // $historial = $prestamos->pagos()->orderBy('created_at')->get();

        return view('operadores.prestamos.registrar_prestamos', compact('operadores', 'bancos', 'prestamos'));
    }

    public function store(Request $request)
    {
        $rules = [
            'id_operador' => 'required|exists:operadores,id',
            'id_banco' => 'required|exists:bancos,id',
            'cantidad' => 'required|numeric|min:0.01',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'TMensaje' => "warning",
                "Mensaje" => 'Faltan datos',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['id_operador', 'id_banco', 'cantidad']);
        $data['pagos'] = 0;
        $data['saldo_actual'] = $data['cantidad'];

        $bancoAfectado = Bancos::where('id', '=', $request->get('id_banco'));
        $saldoActualBanco = $bancoAfectado->first()->saldo || 0;
        $montoPrestamo = $request->get('cantidad');

        if ($saldoActualBanco < $montoPrestamo) {
            return response()->json(["Titulo" => "Prestamo no aplicado","Mensaje" => "No se puede aplicar el prestamo desde la cuenta seleccionada ya que el saldo es insuficiente", "TMensaje" => "warning"]);
        }

        $prestamo = Prestamo::create($data);

        Bancos::where('id', '=', $request->get('id_banco'))->update(["saldo" => DB::raw("saldo - ". $montoPrestamo)]);

        $bancoDinero[] = [
            "monto1" => $montoPrestamo,
            "metodo_pago1" => 'Transferencia',
            "descripcion" => "Prestamo Operador",
            "id_banco1" => $request->get('id_banco'),
            "contenedores" => '[]',
            "tipo" => 'Salida',
            "fecha_pago" =>  date('Y-m-d'),
        ];

        BancoDinero::insert($bancoDinero);

        return response()->json([
            'success' => true,
            'message' => 'Préstamo guardado correctamente',
            'data' => $prestamo
        ], 201);
    }



    public function getListaPrestamos()
    {
        $prestamos = Prestamo::with(['operador', 'banco', 'pagoprestamos'])
            ->when(!auth()->user()->is_admin, function ($q) {
                $q->whereHas('operador', function ($q2) {
                    $q2->where('id_empresa', auth()->user()->id_empresa);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();



        return response()->json([
            'prestamos' => $prestamos

        ]);
    }
    public function getPrestamosPagos($prestamo)
    {
        $prestamos = Prestamo::with(['operador', 'pagoprestamos'])
        ->where('id', $prestamo)
        ->first();

        //   dd($prestamos);

        $historial = $prestamos->pagoprestamos()->orderBy('created_at')->get();

        return response()->json([
            'prestamos' => $prestamos,
            'historial' => $historial,
            'id_prestamo' => $prestamo,
            'total' => (float) $prestamos->cantidad,
        ]);
    }


    public function abonar(Request $request, $id)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'id_banco_abono' => 'required|exists:bancos,id',
            'referencia' => 'nullable|string|max:150',
        ]);

        DB::beginTransaction();
        try {
            $prestamo = Prestamo::findOrFail($id);

            // Registrar el abono en pagos_prestamos (NUEVO ESTÁNDAR)
            PagoPrestamo::create([
                'id_liquidacion' => 0, // porque es pago directo del operador
                'id_prestamo'    => $prestamo->id,
                'saldo_anterior' => $prestamo->saldo_actual,
                'monto_pago'     => $request->monto,

                // Nuevos campos
                'tipo_origen'    => 'directo',
                'id_banco'       => $request->id_banco_abono,
                'referencia'     => $request->referencia ?? '',
                'fecha_pago'     => now(),
            ]);

            // Actualizar saldo del préstamo
            $prestamo->pagos += $request->monto;
            $prestamo->saldo_actual = max($prestamo->cantidad - $prestamo->pagos, 0);
            $prestamo->save();

            // Descontar del banco
            Bancos::where('id', $request->id_banco_abono)
                ->update(["saldo" => DB::raw("saldo - " . $request->monto)]);

            // Registrar movimiento en tabla banco_dinero
            $bancoDinero[] = [
                "monto1"       => $request->monto,
                "metodo_pago1" => 'Transferencia',
                "descripcion"  => "Abono a préstamo de operador " . ($request->referencia ?? ''),
                "id_banco1"    => $request->id_banco_abono,
                "contenedores" => '[]',
                "tipo"         => 'Salida',
                "fecha_pago"   => date('Y-m-d'),
            ];

            BancoDinero::insert($bancoDinero);

            DB::commit();

            // Refrescar datos para el grid
            $prestamosActualizados = Prestamo::with(['operador', 'banco'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Abono registrado correctamente',
                'prestamosActualizados' => $prestamosActualizados
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al registrar abono: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el abono'
            ], 500);
        }
    }
}
