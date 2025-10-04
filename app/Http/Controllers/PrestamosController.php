<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Operador;
use App\Models\Prestamo;
use App\Models\Bancos;
use App\Models\BancoDinero;
use DB;

use Illuminate\Support\Facades\Validator;

class PrestamosController extends Controller
{
    public function index(){
        $operadores = Operador::where('id_empresa',auth()->user()->id_empresa)->get();
        $bancos = Bancos::where('id_empresa',auth()->user()->id_empresa)->get();
        return view('operadores.prestamos.registrar_prestamos',compact('operadores','bancos'));
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

        $bancoAfectado = Bancos::where('id' ,'=',$request->get('id_banco'));
        $saldoActualBanco = $bancoAfectado->first()->saldo || 0;
        $montoPrestamo = $request->get('cantidad');

        if($saldoActualBanco < $montoPrestamo){
            return response()->json(["Titulo" => "Prestamo no aplicado","Mensaje" => "No se puede aplicar el prestamo desde la cuenta seleccionada ya que el saldo es insuficiente", "TMensaje" => "warning"]);
        }

        $prestamo = Prestamo::create($data);

        Bancos::where('id' ,'=',$request->get('id_banco'))->update(["saldo" => DB::raw("saldo - ". $montoPrestamo)]);

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
}
