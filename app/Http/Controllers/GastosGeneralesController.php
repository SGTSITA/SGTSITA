<?php

namespace App\Http\Controllers;

use App\Models\Bancos;
use App\Services\BancosService;
use App\Services\GastosService;
use App\Exceptions\SaldoInsuficienteException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GastosGeneralesController extends Controller
{
    public function __construct(
        protected BancosService $bancosService,
        protected GastosService $gastosService
    ) {
    }

    public function index()
    {
        $data = $this->gastosService->getGastosGeneralesIndexData(auth()->user()->id_empresa);
        return view('gastos_generales.index', $data);
    }

    public function getGastos(Request $r)
    {
        $data = $this->gastosService->getGastosGeneralesGridData(
            auth()->user()->id_empresa,
            $r->from,
            $r->to
        );

        return response()->json($data);
    }

    public function store(Request $request)
    {
        try {
            $resultado = $this->gastosService->storeGastoGeneral(
                $request->all(),
                auth()->user()
            );

            $bancos = Bancos::where('id_empresa', auth()->user()->id_empresa)->get();

            return response()->json([
                "Titulo" => $resultado["Titulo"],
                "Mensaje" => $resultado["Mensaje"],
                "TMensaje" => $resultado["TMensaje"],
                "Bancos" => $bancos,
            ]);
        } catch (SaldoInsuficienteException $e) {
            return response()->json([
                "TMensaje" => "error",
                "Titulo" => "Saldo bancos 1",
                "Mensaje" => $e->getMessage(),
                'success' => false,
            ]);
        } catch (\Throwable $t) {
            Log::channel('daily')->error('Error en GastosGeneralesController->store: ' . $t->getMessage());
            return response()->json([
                "Titulo" => "Gasto no aplicado",
                "Mensaje" => "Ha ocurrido un error, no se puede aplicar el gasto",
                "TMensaje" => "danger",
                "Error" => $t->getMessage(),
            ]);
        }
    }

    public function aplicarGastos($gastoId, $formaAplicacion, $viajes, $unidades, $bancoId)
    {
        return $this->gastosService->aplicarGastosGenerales($gastoId, $formaAplicacion, $viajes, $unidades, $bancoId);
    }

    public function eliminarGasto(Request $r)
    {
        try {
            $this->gastosService->eliminarGastoGeneral(
                (int) $r->IdGasto,
                $r->fechacancelacion,
                auth()->user()->id_empresa
            );

            return response()->json([
                "Titulo" => "Exito!",
                "Mensaje" => "Se ha eliminado el gasto exitosamente",
                "TMensaje" => "success",
            ]);
        } catch (\Throwable $t) {
            Log::channel('daily')->info("Error: GastosGeneralesController->eliminarGasto->" . $t->getMessage());
            return response()->json([
                "Titulo" => "Gasto no aplicado",
                "Mensaje" => "Ha ocurrido un error, no se puede aplicar el gasto " . $t->getMessage(),
                "TMensaje" => "error",
            ]);
        }
    }
}
