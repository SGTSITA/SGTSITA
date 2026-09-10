<?php

namespace App\Http\Controllers;

use App\Services\SociosService;
use App\Models\Socio;
use App\Models\SocioConfiguracion;
use App\Models\Equipo;
use Illuminate\Http\Request;

class SociosController extends Controller
{
    private SociosService $sociosService;

    public function __construct(SociosService $sociosService)
    {
        $this->sociosService = $sociosService;
    }

    public function index()
    {
        $idEmpresa = auth()->user()->id_empresa;
        $socios = $this->sociosService->getSocios($idEmpresa);
        $equipos = Equipo::where('id_empresa', $idEmpresa)
            ->where('tipo', 'Tractos / Camiones')
            ->orderBy('id_equipo')
            ->get();
        $bancos = \App\Models\Bancos::where('id_empresa', $idEmpresa)
            ->where('estado', 1)
            ->orderBy('nombre_banco')
            ->get();

        return view('socios.index', compact('socios', 'equipos', 'bancos'));
    }

    public function getSociosData()
    {
        return response()->json([
            'socios' => $this->sociosService->getSocios(auth()->user()->id_empresa)
        ]);
    }

    public function storeSocio(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
        ]);

        $socio = $this->sociosService->storeSocio($validated, auth()->user()->id_empresa);

        return response()->json([
            'success' => true,
            'Mensaje' => 'Socio registrado con éxito.',
            'socio' => $socio
        ]);
    }

    public function updateSocio(Request $request, Socio $socio)
    {
        abort_unless($socio->id_empresa === auth()->user()->id_empresa, 403);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'activo' => 'required|boolean',
        ]);

        if ($socio->trashed() && $validated['activo']) {
            $socio->restore();
        }

        $this->sociosService->updateSocio($socio, $validated);

        return response()->json([
            'success' => true,
            'Mensaje' => 'Socio actualizado con éxito.'
        ]);
    }

    public function destroySocio(Socio $socio)
    {
        abort_unless($socio->id_empresa === auth()->user()->id_empresa, 403);
        $socio->update(['activo' => 0]);
        $socio->delete();
        return response()->json([
            'success' => true,
            'Mensaje' => 'Socio eliminado con éxito.'
        ]);
    }

    public function getConfigsData()
    {
        return response()->json([
            'configs' => $this->sociosService->getConfigs(auth()->user()->id_empresa)
        ]);
    }

    public function storeConfig(Request $request)
    {
        $validated = $request->validate([
            'socio_id' => 'required|exists:socios,id',
            'equipo_id' => 'required|exists:equipos,id',
            'tipo_pago' => 'required|in:porcentaje,cuota_fija',
            'valor' => 'required|numeric|min:0',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $config = $this->sociosService->storeConfig($validated, auth()->user()->id_empresa);

        return response()->json([
            'success' => true,
            'Mensaje' => 'Configuración de socio aplicada.',
            'config' => $config
        ]);
    }

    public function updateConfig(Request $request, SocioConfiguracion $config)
    {
        abort_unless($config->id_empresa === auth()->user()->id_empresa, 403);

        $validated = $request->validate([
            'socio_id' => 'required|exists:socios,id',
            'equipo_id' => 'required|exists:equipos,id',
            'tipo_pago' => 'required|in:porcentaje,cuota_fija',
            'valor' => 'required|numeric|min:0',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'activo' => 'required|boolean',
        ]);

        if ($config->trashed() && $validated['activo']) {
            $config->restore();
        }

        $this->sociosService->updateConfig($config, $validated);

        return response()->json([
            'success' => true,
            'Mensaje' => 'Configuración actualizada.'
        ]);
    }

    public function destroyConfig(SocioConfiguracion $config)
    {
        abort_unless($config->id_empresa === auth()->user()->id_empresa, 403);
        $config->update(['activo' => 0]);
        $config->delete();
        return response()->json([
            'success' => true,
            'Mensaje' => 'Configuración actualizada.'
        ]);
    }

    public function getUtilityReport(Request $request)
    {
        $startDate = $request->input('from', now()->startOfMonth()->toDateString());
        $endDate = $request->input('to', now()->endOfMonth()->toDateString());
        $socioId = $request->input('socio_id') ? (int)$request->input('socio_id') : null;
        $equipoId = $request->input('equipo_id') ? (int)$request->input('equipo_id') : null;

        $data = $this->sociosService->calculatePartnerUtility(
            $startDate,
            $endDate,
            auth()->user()->id_empresa,
            $socioId,
            $equipoId
        );

        return response()->json($data);
    }

    public function saveCortePeriodo(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'equipo_id' => 'nullable|exists:equipos,id'
        ]);

        $res = $this->sociosService->saveCalculoPeriodo(
            $request->only('from', 'to', 'equipo_id'),
            auth()->user()->id_empresa,
            auth()->user()->id
        );

        return response()->json($res);
    }

    public function checkComparativa(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'equipo_id' => 'nullable|integer'
        ]);

        $res = $this->sociosService->getSnapshotComparativa(
            $request->input('from'),
            $request->input('to'),
            auth()->user()->id_empresa,
            $request->input('equipo_id')
        );

        return response()->json($res);
    }

    public function exportReport(Request $request)
    {
        $startDate = $request->input('from');
        $endDate = $request->input('to');
        $fileType = $request->input('fileType');
        $socioId = $request->input('socio_id') ? (int)$request->input('socio_id') : null;
        $equipoId = $request->input('equipo_id') ? (int)$request->input('equipo_id') : null;
        $tipoReporte = $request->input('tipo_reporte', 'completo');
        $idEmpresa = auth()->user()->id_empresa;
        $empresa = auth()->user()->Empresa->nombre ?? 'SITA';

        $data = $this->sociosService->calculatePartnerUtility(
            $startDate,
            $endDate,
            $idEmpresa,
            $socioId,
            $equipoId
        );

        if ($fileType === 'xlsx') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\SociosUtilidadExport($data, $empresa, $tipoReporte),
                'reporte_utilidad_socios_' . $startDate . '_' . $endDate . '.xlsx'
            );
        }

        if ($fileType === 'pdf') {
            $fechaGeneracion = now()->format('d-m-Y H:i');
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('socios.pdf_reporte', compact('data', 'empresa', 'fechaGeneracion', 'tipoReporte'));
            return $pdf->setPaper('a4', 'portrait')->download('reporte_utilidad_socios_' . $startDate . '_' . $endDate . '.pdf');
        }

        return abort(400, 'Tipo de archivo no válido');
    }

    public function registrarPago(Request $request)
    {
        $validated = $request->validate([
            'socio_id' => 'required|exists:socios,id',
            'monto' => 'required|numeric',
            'banco_id' => 'required|exists:bancos,id',
            'fecha_aplicacion' => 'required|date',
            'concepto' => 'nullable|string|max:255',
            'periodos' => 'nullable|array',
            'periodos.*.id' => 'required|exists:socios_calculos_periodos,id',
            'periodos.*.monto' => 'required|numeric'
        ]);

        $idEmpresa = auth()->user()->id_empresa;
        $userId = auth()->user()->id;

        $pagos = [];

        return \Illuminate\Support\Facades\DB::transaction(function() use ($validated, $idEmpresa, $userId, &$pagos) {
            $socio = \App\Models\Socio::withTrashed()->findOrFail($validated['socio_id']);

            if (!empty($validated['periodos'])) {
                foreach ($validated['periodos'] as $pData) {
                    $periodo = \App\Models\SocioCalculoPeriodo::find($pData['id']);
                    $periodoLabel = $periodo ? (' (Periodo #' . $periodo->id . ' del ' . \Carbon\Carbon::parse($periodo->fecha_desde)->format('d-m-Y') . ' al ' . \Carbon\Carbon::parse($periodo->fecha_hasta)->format('d-m-Y') . ')') : ' (Periodo #' . $pData['id'] . ')';

                    $pago = \App\Models\SocioPago::create([
                        'id_empresa' => $idEmpresa,
                        'socio_id' => $validated['socio_id'],
                        'monto' => $pData['monto'],
                        'banco_id' => $validated['banco_id'],
                        'fecha_aplicacion' => $validated['fecha_aplicacion'],
                        'calculo_periodo_id' => $pData['id'],
                        'user_id' => $userId
                    ]);

                    app(\App\Services\BancosService::class)->registrarMovimiento([
                        'cuenta_bancaria_id' => $validated['banco_id'],
                        'tipo' => 'cargo',
                        'monto' => $pData['monto'],
                        'concepto' => 'Pago a Socio: ' . $socio->nombre . $periodoLabel,
                        'fecha_movimiento' => $validated['fecha_aplicacion'],
                        'origen' => 'sistema',
                        'referencia' => 'Pago de utilidades #' . $pago->id,
                        'referenciaable_type' => \App\Models\SocioPago::class,
                        'referenciaable_id' => $pago->id
                    ]);

                    $pagos[] = $pago;
                }
            } else {
                $pago = \App\Models\SocioPago::create([
                    'id_empresa' => $idEmpresa,
                    'socio_id' => $validated['socio_id'],
                    'monto' => $validated['monto'],
                    'banco_id' => $validated['banco_id'],
                    'fecha_aplicacion' => $validated['fecha_aplicacion'],
                    'calculo_periodo_id' => null,
                    'user_id' => $userId
                ]);

                $conceptoBanco = !empty($validated['concepto']) ? $validated['concepto'] : ('Pago a Socio: ' . $socio->nombre);

                app(\App\Services\BancosService::class)->registrarMovimiento([
                    'cuenta_bancaria_id' => $validated['banco_id'],
                    'tipo' => 'cargo',
                    'monto' => $validated['monto'],
                    'concepto' => $conceptoBanco,
                    'fecha_movimiento' => $validated['fecha_aplicacion'],
                    'origen' => 'sistema',
                    'referencia' => 'Pago de utilidades #' . $pago->id,
                    'referenciaable_type' => \App\Models\SocioPago::class,
                    'referenciaable_id' => $pago->id
                ]);

                $pagos[] = $pago;
            }

            return response()->json([
                'success' => true,
                'Mensaje' => 'Pago(s) registrado(s) con éxito.',
                'pagos' => $pagos
            ]);
        });
    }

    public function getSocioCortes(Request $request)
    {
        $socioId = $request->input('socio_id');
        $idEmpresa = auth()->user()->id_empresa;

        // Get cuts oldest first to apply general abonos starting from the oldest cut
        $cortes = \App\Models\SocioCalculoPeriodo::where('id_empresa', $idEmpresa)
            ->whereHas('detalles', function($q) use ($socioId) {
                $q->where('socio_id', $socioId);
            })
            ->with(['detalles' => function($q) use ($socioId) {
                $q->where('socio_id', $socioId);
            }])
            ->orderBy('id', 'asc')
            ->get();

        // Get total general payments not linked to any specific cut
        $totalGeneralAbonos = \App\Models\SocioPago::where('socio_id', $socioId)
            ->whereNull('calculo_periodo_id')
            ->sum('monto');

        $result = [];
        $abonoRestante = (float)$totalGeneralAbonos;

        foreach ($cortes as $c) {
            $montoDistribuido = $c->detalles->first()->monto_distribuido ?? 0;

            // Payments specifically linked to this cut
            $totalPagadoCorteEspecifico = \App\Models\SocioPago::where('socio_id', $socioId)
                ->where('calculo_periodo_id', $c->id)
                ->sum('monto');

            $saldoPendiente = (float)$montoDistribuido - (float)$totalPagadoCorteEspecifico;

            // Apply remaining general abonos to this cut
            if ($abonoRestante > 0 && $saldoPendiente > 0) {
                $descontar = min($abonoRestante, $saldoPendiente);
                $saldoPendiente -= $descontar;
                $abonoRestante -= $descontar;
                $totalPagadoCorteEspecifico += $descontar;
            }

            $result[] = [
                'id' => $c->id,
                'periodo' => $c->fecha_desde . ' a ' . $c->fecha_hasta,
                'monto_distribuido' => (float)$montoDistribuido,
                'total_pagado' => (float)$totalPagadoCorteEspecifico,
                'saldo_pendiente' => max(0, $saldoPendiente)
            ];
        }

        return response()->json([
            'success' => true,
            'cortes' => array_reverse($result) // Return newest first for UI
        ]);
    }

    public function getPagosHistorial(Request $request)
    {
        $socioId = $request->input('socio_id');
        $equipoId = $request->input('equipo_id');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $idEmpresa = auth()->user()->id_empresa;

        // 1. Fetch Pagos (Abonos)
        $pagosQuery = \App\Models\SocioPago::with(['socio', 'banco', 'user'])
            ->where('id_empresa', $idEmpresa);

        if ($socioId) {
            $pagosQuery->where('socio_id', $socioId);
        }
        if ($fechaDesde && $fechaHasta) {
            $pagosQuery->whereBetween('fecha_aplicacion', [$fechaDesde, $fechaHasta]);
        }
        if ($equipoId) {
            $pagosQuery->whereHas('socio.configurations', function($q) use ($equipoId) {
                $q->where('equipo_id', $equipoId);
            });
        }
        $pagos = $pagosQuery->get();

        // 2. Fetch Cortes (Cargos)
        $cortesQuery = \App\Models\SocioCalculoPeriodo::where('id_empresa', $idEmpresa)
            ->with(['user', 'detalles.socio']);

        if ($socioId) {
            $cortesQuery->whereHas('detalles', function($q) use ($socioId) {
                $q->where('socio_id', $socioId);
            });
        }
        if ($fechaDesde && $fechaHasta) {
            $cortesQuery->where(function($q) use ($fechaDesde, $fechaHasta) {
                $q->whereBetween('created_at', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59'])
                  ->orWhereBetween('fecha_hasta', [$fechaDesde, $fechaHasta]);
            });
        }
        if ($equipoId) {
            $cortesQuery->whereHas('detalles.socio.configurations', function($q) use ($equipoId) {
                $q->where('equipo_id', $equipoId);
            });
        }
        $cortes = $cortesQuery->get();

        // 3. Combine into a unified chronological array
        $movimientos = [];

        foreach ($pagos as $pago) {
            $movimientos[] = [
                'id' => 'pago_' . $pago->id,
                'fecha' => $pago->fecha_aplicacion ? $pago->fecha_aplicacion->format('Y-m-d') : $pago->created_at->format('Y-m-d'),
                'socio_nombre' => $pago->socio->nombre ?? 'S/N',
                'concepto' => 'Pago de utilidades' . ($pago->calculo_periodo_id ? ' (Periodo #' . $pago->calculo_periodo_id . ')' : ' (Abono General)'),
                'banco' => $pago->banco ? $pago->banco->nombre_banco . ' (' . $pago->banco->cuenta_bancaria . ')' : 'N/A',
                'cargo' => 0.0,
                'abono' => (float)$pago->monto,
                'registrado_por' => $pago->user->name ?? 'S/N',
            ];
        }

        foreach ($cortes as $corte) {
            $detalles = $corte->detalles;
            if ($socioId) {
                $detalles = $detalles->where('socio_id', $socioId);
            }

            foreach ($detalles as $det) {
                $movimientos[] = [
                    'id' => 'corte_' . $corte->id . '_' . $det->id,
                    'fecha' => $corte->fecha_hasta,
                    'socio_nombre' => $det->socio->nombre ?? 'S/N',
                    'concepto' => 'Corte de periodo: ' . \Carbon\Carbon::parse($corte->fecha_desde)->format('d/m/Y') . ' a ' . \Carbon\Carbon::parse($corte->fecha_hasta)->format('d/m/Y'),
                    'banco' => 'N/A',
                    'cargo' => (float)$det->monto_distribuido,
                    'abono' => 0.0,
                    'registrado_por' => $corte->user->name ?? 'S/N',
                ];
            }
        }

        // Sort by date ascending to calculate correct running balance
        usort($movimientos, function($a, $b) {
            $t1 = strtotime($a['fecha']);
            $t2 = strtotime($b['fecha']);
            if ($t1 === $t2) {
                return strcmp($a['id'], $b['id']);
            }
            return $t1 < $t2 ? -1 : 1;
        });

        // Calculate running balance (Saldo)
        $saldo = 0.0;
        foreach ($movimientos as &$mov) {
            $saldo += ($mov['cargo'] - $mov['abono']);
            $mov['saldo'] = $saldo;
        }

        // Sort descending for display (latest first)
        usort($movimientos, function($a, $b) {
            $t1 = strtotime($a['fecha']);
            $t2 = strtotime($b['fecha']);
            if ($t1 === $t2) {
                return strcmp($b['id'], $a['id']);
            }
            return $t1 > $t2 ? -1 : 1;
        });

        return response()->json([
            'success' => true,
            'pagos' => $movimientos
        ]);
    }

    public function getCortesHistorial()
    {
        $idEmpresa = auth()->user()->id_empresa;
        $cortes = \App\Models\SocioCalculoPeriodo::where('id_empresa', $idEmpresa)
            ->with(['user', 'detalles.socio', 'equipo'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'cortes' => $cortes
        ]);
    }

    public function reporteriaIndex()
    {
        $idEmpresa = auth()->user()->id_empresa;
        $socios = \App\Models\Socio::where('id_empresa', $idEmpresa)
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get();
        $equipos = \App\Models\Equipo::where('id_empresa', $idEmpresa)
            ->where('tipo', 'Tractos / Camiones')
            ->orderBy('id_equipo')
            ->get();

        return view('reporteria.socios.index', compact('socios', 'equipos'));
    }

    public function destroyCortePeriodo(\App\Models\SocioCalculoPeriodo $corte)
    {
        abort_unless($corte->id_empresa === auth()->user()->id_empresa, 403);

        // 1. Direct payments linked to this cut
        $hasPayments = \App\Models\SocioPago::where('calculo_periodo_id', $corte->id)->exists();

        if ($hasPayments) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar este corte porque ya existen pagos registrados para este periodo.'
            ], 422);
        }

        // 2. Check if general abonos (which don't have calculo_periodo_id) have been applied to this cut.
        $socioIds = $corte->detalles->pluck('socio_id')->unique();

        foreach ($socioIds as $socioId) {
            // Get all cuts for this partner, ordered by id ascending
            $partnerCuts = \App\Models\SocioCalculoPeriodo::where('id_empresa', $corte->id_empresa)
                ->whereHas('detalles', function($q) use ($socioId) {
                    $q->where('socio_id', $socioId);
                })
                ->with(['detalles' => function($q) use ($socioId) {
                    $q->where('socio_id', $socioId);
                }])
                ->orderBy('id', 'asc')
                ->get();

            // Get total general payments for this partner
            $totalGeneralAbonos = \App\Models\SocioPago::where('socio_id', $socioId)
                ->whereNull('calculo_periodo_id')
                ->sum('monto');

            $abonoRestante = (float)$totalGeneralAbonos;

            foreach ($partnerCuts as $pc) {
                $montoDistribuido = $pc->detalles->first()->monto_distribuido ?? 0;
                $totalPagadoCorteEspecifico = \App\Models\SocioPago::where('socio_id', $socioId)
                    ->where('calculo_periodo_id', $pc->id)
                    ->sum('monto');

                $saldoPendiente = (float)$montoDistribuido - (float)$totalPagadoCorteEspecifico;

                if ($abonoRestante > 0 && $saldoPendiente > 0) {
                    $descontar = min($abonoRestante, $saldoPendiente);
                    
                    if ($pc->id == $corte->id && $descontar > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No se puede eliminar este corte porque ya tiene saldo cubierto por abonos generales de un socio.'
                        ], 422);
                    }

                    $abonoRestante -= $descontar;
                }
            }
        }

        return \Illuminate\Support\Facades\DB::transaction(function() use ($corte) {
            $corte->detalles()->delete();
            $corte->viajesHistorico()->delete();
            $corte->delete();

            return response()->json([
                'success' => true,
                'Mensaje' => 'Corte de periodo eliminado por completo.'
            ]);
        });
    }

    public function destroyPago(\App\Models\SocioPago $pago)
    {
        abort_unless($pago->id_empresa === auth()->user()->id_empresa, 403);

        return \Illuminate\Support\Facades\DB::transaction(function() use ($pago) {
            // Find and delete associated bank movement adjusting balance
            $mov = \App\Models\CatBancoCuentasMovimientos::where('referenciaable_type', \App\Models\SocioPago::class)
                ->where('referenciaable_id', $pago->id)
                ->first();

            if ($mov) {
                $banco = \App\Models\Bancos::find($mov->cuenta_bancaria_id);
                if ($banco) {
                    if ($mov->tipo === 'cargo') {
                        $banco->increment('saldo', $mov->monto);
                    } else {
                        $banco->decrement('saldo', $mov->monto);
                    }
                }
                $mov->delete();
            }

            $pago->delete();

            return response()->json([
                'success' => true,
                'Mensaje' => 'Pago eliminado con éxito.'
            ]);
        });
    }
}
