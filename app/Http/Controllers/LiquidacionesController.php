<?php

namespace App\Http\Controllers;

use App\Exceptions\SaldoInsuficienteException;
use App\Http\Requests\AplicarLiquidacionRequest;
use App\Http\Requests\JustificarGastosRequest;
use App\Models\Asignaciones;
use App\Services\BancosService;
use App\Services\EquipoService;
use App\Services\LiquidacionesService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LiquidacionesController extends Controller
{
    protected LiquidacionesService $liquidacionesService;
    protected BancosService $bancosService;
    protected EquipoService $equipoService;

    public function __construct(
        LiquidacionesService $liquidacionesService,
        BancosService $bancosService,
        EquipoService $equipoService
    ) {
        $this->liquidacionesService = $liquidacionesService;
        $this->bancosService = $bancosService;
        $this->equipoService = $equipoService;
    }

    public function index()
    {
        return view('liquidaciones.index');
    }

    public function getPagosOperadores(Request $r)
    {
        $datosPago = $this->liquidacionesService->getPagosOperadores((int) Auth::user()->id_empresa);

        return response()->json($datosPago);
    }

    public function getpagosOperadoressaldo()
    {
        $datosPago = $this->liquidacionesService->getPagosOperadoresSaldo((int) Auth::user()->id_empresa);

        return response()->json($datosPago);
    }

    public function show($id)
    {
        $datos = $this->liquidacionesService->getDatosShow((int) $id, (int) Auth::user()->id_empresa);

        if (!$datos) {
            return "No existe información";
        }

        return view('liquidaciones.show', $datos);
    }

    public function getViajesOperador(Request $r)
    {
        $data = $this->liquidacionesService->getViajesOperador((int) $r->operador, (int) Auth::user()->id_empresa);

        return response()->json($data);
    }

    public function justificarGastos(JustificarGastosRequest $r)
    {
        try {
            $this->liquidacionesService->justificarGasto($r->validated(), Auth::user());

            return response()->json([
                "Titulo" => "Correcto",
                "Mensaje" => "Datos guardados con exito",
                "TMensaje" => "success"
            ]);
        } catch (\Throwable $t) {
            $uniqid = uniqid();
            Log::channel('daily')->info("Codigo: $uniqid, Error: LiquidacionesController/justificarGastos " . $t->getMessage());

            return response()->json([
                "Titulo" => "Ha ocurrido un problema",
                "Mensaje" => "Codigo error: $uniqid. Mensaje: " . $t->getMessage(),
                "TMensaje" => "error"
            ]);
        }
    }

    public function justificarGastosMultiples(Request $r)
    {
        try {
            $filas = $r->input('filas', []);
            $this->liquidacionesService->justificarGastosMultiples($filas, Auth::user());

            return response()->json([
                "Titulo" => "Correcto",
                "Mensaje" => "Justificaciones guardadas correctamente",
                "TMensaje" => "success"
            ]);
        } catch (\Throwable $t) {
            $uniqid = uniqid();
            Log::channel('daily')->info("Codigo: $uniqid, Error: LiquidacionesController/justificarGastosMultiples " . $t->getMessage());

            return response()->json([
                "Titulo" => "Ha ocurrido un problema",
                "Mensaje" => "Codigo error: $uniqid. Mensaje: " . $t->getMessage(),
                "TMensaje" => "error"
            ]);
        }
    }

    public function agregarDineroViaje(Request $request)
    {
        try {
            $this->liquidacionesService->agregarDineroViaje($request->all(), Auth::user());

            return response()->json([
                "Titulo" => "Correcto",
                "Mensaje" => "Datos guardados con exito",
                "TMensaje" => "success"
            ]);
        } catch (SaldoInsuficienteException $e) {
            return response()->json([
                "TMensaje" => "error",
                "Titulo" => "Saldo bancos",
                "Mensaje" => $e->getMessage(),
                'success' => false,
            ]);
        } catch (\Throwable $t) {
            $uniqid = uniqid();
            Log::channel('daily')->info("Codigo: $uniqid, Error: LiquidacionesController/agregarDineroViaje " . $t->getMessage());

            return response()->json([
                "Titulo" => "Ha ocurrido un problema",
                "Mensaje" => "Codigo error: $uniqid. Mensaje: " . $t->getMessage(),
                "TMensaje" => "error"
            ]);
        }
    }

    public function getViaticosGuardados(Request $request)
    {
        try {
            $idContenedores = $request->get('id_contenedores');
            if (!$idContenedores && $request->filled('id_contenedor')) {
                $idContenedores = [$request->get('id_contenedor')];
            }

            $resultado = $this->liquidacionesService->getViaticosGuardados(
                $idContenedores ? (array) $idContenedores : null,
                $request->get('numContenedor'),
                (int) Auth::user()->id_empresa
            );

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Throwable $t) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener viáticos: ' . $t->getMessage()
            ], 500);
        }
    }

    public function getDinerosViaje(Request $request)
    {
        try {
            $idContenedores = $request->get('id_contenedores');
            if (!$idContenedores && $request->filled('id_contenedor')) {
                $idContenedores = [$request->get('id_contenedor')];
            }

            $resultado = $this->liquidacionesService->getDinerosViaje(
                $idContenedores ? (array) $idContenedores : null,
                $request->get('numContenedor'),
                (int) Auth::user()->id_empresa
            );

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Throwable $t) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener dinero de viaje: ' . $t->getMessage()
            ], 500);
        }
    }

    public function eliminarDineroViaje(Request $request)
    {
        try {
            $idDinero = (int) $request->get('id_dinero_contenedor');
            $res = $this->liquidacionesService->eliminarDineroViaje($idDinero, Auth::user());

            return response()->json([
                'success' => true,
                'Titulo' => $res['Titulo'] ?? 'Cancelación exitosa',
                'Mensaje' => $res['Mensaje'] ?? 'Dinero cancelado correctamente',
                'TMensaje' => 'success'
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'Titulo' => 'Operación no permitida',
                'Mensaje' => $e->getMessage(),
                'TMensaje' => 'warning'
            ], 422);
        } catch (\Throwable $t) {
            Log::channel('daily')->error('Error en eliminarDineroViaje: ' . $t->getMessage());

            return response()->json([
                'success' => false,
                'Titulo' => 'Error al cancelar',
                'Mensaje' => 'Ocurrió un error al procesar la cancelación: ' . $t->getMessage(),
                'TMensaje' => 'error'
            ], 500);
        }
    }

    public function eliminarViatico(Request $request)
    {
        try {
            $idViatico = (int) $request->get('idviatico');
            $montoViatico = $this->liquidacionesService->eliminarViatico($idViatico, Auth::user());

            return response()->json([
                'success' => true,
                'Titulo' => 'Viático eliminado',
                'Mensaje' => 'El viático por $' . number_format($montoViatico, 2) . ' fue eliminado y el saldo del operador recalculado correctamente.',
                'TMensaje' => 'success'
            ]);
        } catch (\Throwable $t) {
            Log::channel('daily')->error('Error en eliminarViatico: ' . $t->getMessage());

            return response()->json([
                'success' => false,
                'Titulo' => 'Error al eliminar viático',
                'Mensaje' => 'Ocurrió un error: ' . $t->getMessage(),
                'TMensaje' => 'error'
            ], 500);
        }
    }

    public function aplicarPago(AplicarLiquidacionRequest $request)
    {
        try {
            $idLiquidacion = $this->liquidacionesService->aplicarLiquidacion($request->validated(), Auth::user());

            return response()->json([
                "Titulo" => "Pago aplicado",
                "Mensaje" => "Se aplicó el pago correctamente al operador",
                "TMensaje" => "success",
                "IdLiquidacion" => $idLiquidacion
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                "Titulo" => "Acción rechazada",
                "Mensaje" => $e->getMessage(),
                "TMensaje" => "warning"
            ]);
        } catch (SaldoInsuficienteException $e) {
            return response()->json([
                "TMensaje" => "error",
                "Titulo" => "Saldo bancos",
                "Mensaje" => $e->getMessage(),
                'success' => false,
            ]);
        } catch (\Throwable $t) {
            Log::channel('daily')->info('LiquidacionesController->aplicarPago: ' . $t->getMessage());

            return response()->json([
                "Titulo" => "Pago NO aplicado",
                "Mensaje" => "Ocurrio un error al aplicar el pago: " . $t->getMessage(),
                "TMensaje" => "error"
            ]);
        }
    }

    public function comprobantePago(Request $r)
    {
        $datos = $this->liquidacionesService->getDatosComprobantePago((int) $r->IdOperacion, Auth::user());

        $pdf = Pdf::loadView('liquidaciones.pdf', $datos);
        return $pdf->stream('utilidades_rpt.pdf');
    }

    public function historialPagos()
    {
        $equipos = $this->equipoService->getTractocamiones();

        return view('liquidaciones.historial', compact('equipos'));
    }

    public function historialPagosData(Request $r)
    {
        $data = $this->liquidacionesService->getHistorialPagosData($r->all(), (int) Auth::user()->id_empresa);

        return response()->json(["TMensaje" => "success", "data" => $data]);
    }

    public function vistaPrevia(Request $request)
    {
        $viajes = Asignaciones::with([
            'Contenedor.Cotizacion',
            'Prestamos',
            'Justificaciones'
        ])
        ->whereIn('id', $request->viajes ?? [])
        ->get();

        $totalViajes = $viajes->sum('monto_viaje');
        $totalPrestamos = $viajes->flatMap->Prestamos->sum('monto');
        $totalJustificaciones = $viajes->flatMap->Justificaciones->sum('monto');

        return view('liquidaciones.vista-previa', compact(
            'viajes',
            'totalViajes',
            'totalPrestamos',
            'totalJustificaciones'
        ));
    }

    public function vistaPrevialiqu(Request $r)
    {
        $data = $this->liquidacionesService->getVistaPreviaLiqu($r->viajes ?? [], (int) $r->operador_id);

        return response()->json($data);
    }

    public function update($id)
    {
        return redirect()->route('index.liquidacion');
    }

    public function update_varios(Request $request)
    {
        return redirect()->route('index.liquidacion');
    }

    public function deleteHistorialPago($id)
    {
        try {
            $this->liquidacionesService->deleteHistorialPago((int) $id);

            return response()->json([
                "TMensaje" => "success",
                "Titulo" => "Liquidaciones",
                "Mensaje" => "Liquidación eliminada correctamente"
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                "TMensaje" => "error",
                "Titulo" => "Error",
                "Mensaje" => $e->getMessage()
            ]);
        }
    }
}
