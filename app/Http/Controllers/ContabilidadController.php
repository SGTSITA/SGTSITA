<?php

namespace App\Http\Controllers;

use App\Models\Empresas;
use App\Models\Equipo;
use App\Services\ContabilidadService;
use App\Services\EquipoService;
use Carbon\Carbon;
use App\Services\PolizaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContabilidadController extends Controller
{
    public function __construct(
        protected ContabilidadService $contabilidadService,
        protected EquipoService $equipoService,
        protected PolizaService $polizaService
    ) {
    }

    /**
     * Muestra el reporte de Balance General y Estado de Resultados (Modelo TP-002 CONAC).
     */
    public function balanceGeneral(Request $request)
    {
        $user = Auth::user();
        $esAdmin = (bool) ($user->es_admin ?? false);

        // 1. Determinar empresas a consultar
        $todasEmpresas = Empresas::select('id', 'nombre')->orderBy('nombre')->get();

        if ($esAdmin) {
            $inputEmpresas = $request->input('empresas');
            if (empty($inputEmpresas) || $inputEmpresas === 'todas' || in_array('todas', (array) $inputEmpresas)) {
                $empresasIds = $todasEmpresas->pluck('id')->toArray();
                $seleccionEmpresas = ['todas'];
            } else {
                $empresasIds = is_array($inputEmpresas) ? array_map('intval', $inputEmpresas) : [intval($inputEmpresas)];
                $seleccionEmpresas = $empresasIds;
            }
        } else {
            $empresasIds = [$user->id_empresa];
            $seleccionEmpresas = [$user->id_empresa];
        }

        // 2. Parámetros de filtro
        if ($request->filled('daterange') && (!$request->filled('fecha_inicio') || !$request->filled('fecha_corte'))) {
            $partes = explode(' AL ', $request->input('daterange'));
            if (count($partes) === 2) {
                $fechaInicio = Carbon::parse(trim($partes[0]))->format('Y-m-d');
                $fechaCorte = Carbon::parse(trim($partes[1]))->format('Y-m-d');
            }
        }

        $fechaCorte = $request->filled('fecha_corte')
            ? Carbon::parse($request->input('fecha_corte'))->format('Y-m-d')
            : ($fechaCorte ?? now()->format('Y-m-d'));
        $anio = Carbon::parse($fechaCorte)->year;
        $fechaInicio = $request->filled('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))->format('Y-m-d')
            : ($fechaInicio ?? "{$anio}-01-01");

        $idEquipo = $request->filled('id_equipo') ? (int) $request->input('id_equipo') : null;

        // 3. Obtener tractocamiones para el selector
        $equiposDisponibles = Equipo::whereIn('id_empresa', $empresasIds)
            ->where('tipo', 'Tractos / Camiones')
            ->where('activo', 1)
            ->orderBy('id_equipo', 'asc')
            ->get();

        $equipoSeleccionado = $idEquipo ? Equipo::find($idEquipo) : null;

        // 4. Tipo de reporte solicitado
        $tipoReporte = $request->input('tipo_reporte', 'balance_general');
        $catalogoReportes = [
            'balance_general'   => [
                'nombre'      => 'Balance General (Estado de Situación Financiera)',
                'codigo_conac'=> 'ESF-01',
                'icono'       => 'fas fa-balance-scale',
                'disponible'  => true,
                'descripcion' => 'Muestra los saldos de Activo, Pasivo y Patrimonio bajo la ecuación contable A = P + C.',
            ],
            'estado_resultados' => [
                'nombre'      => 'Estado de Actividades / Resultados',
                'codigo_conac'=> 'ERA-02',
                'icono'       => 'fas fa-chart-line',
                'disponible'  => true,
                'descripcion' => 'Desglose detallado de Ingresos por Fletes, Rendimientos e Intereses Financieros menos Gastos Operativos.',
            ],
            'balanza_comprobacion' => [
                'nombre'      => 'Balanza de Comprobación',
                'codigo_conac'=> 'BC-03',
                'icono'       => 'fas fa-list-alt',
                'disponible'  => true,
                'descripcion' => 'Listado de todas las cuentas CONAC de mayor y detalle con movimientos Debe, Haber y Saldos.',
            ],
            'auxiliar_contable' => [
                'nombre'      => 'Auxiliar Contable (Libro Mayor por Cuentas)',
                'codigo_conac'=> 'AC-07',
                'icono'       => 'fas fa-book-open',
                'disponible'  => true,
                'descripcion' => 'Catálogo de cuentas con desglose cronológico de pólizas por cuenta afectable, cargos, abonos y saldos.',
            ],
            'libro_diario' => [
                'nombre'      => 'Libro Diario de Pólizas',
                'codigo_conac'=> 'LD-06',
                'icono'       => 'fas fa-book',
                'disponible'  => true,
                'descripcion' => 'Registro cronológico exhaustivo de pólizas de Ingreso, Egreso, Diario y Apertura con partidas desglosadas.',
            ],
            'variacion_hacienda' => [
                'nombre'      => 'Estado de Variación en la Hacienda Pública / Patrimonio',
                'codigo_conac'=> 'EVH-04',
                'icono'       => 'fas fa-chart-pie',
                'disponible'  => false,
                'descripcion' => 'Modificaciones en el patrimonio generado, reservas y resultados acumulados del ejercicio.',
            ],
            'flujos_efectivo' => [
                'nombre'      => 'Estado de Flujos de Efectivo',
                'codigo_conac'=> 'EFE-05',
                'icono'       => 'fas fa-money-bill-wave',
                'disponible'  => false,
                'descripcion' => 'Origen y aplicación de recursos bancarios y de tesorería clasificados por actividades operativas.',
            ],
        ];

        // 5. Calcular datos según reporte solicitado
        $balance = null;
        $balanza = null;
        $auxiliar = null;
        $libroDiario = null;
        $reportData = null;

        if ($tipoReporte === 'balanza_comprobacion') {
            $balanza = $this->contabilidadService->calcularBalanzaComprobacion($empresasIds, $idEquipo, $fechaCorte, $fechaInicio);
            $reportData = $balanza;
        } elseif ($tipoReporte === 'auxiliar_contable') {
            $auxiliar = $this->contabilidadService->calcularAuxiliarContable($empresasIds, $idEquipo, $fechaCorte, $fechaInicio);
            $reportData = $auxiliar;
        } elseif ($tipoReporte === 'libro_diario') {
            $libroDiario = $this->contabilidadService->obtenerLibroDiario($empresasIds, $idEquipo, $fechaCorte, $fechaInicio);
            $reportData = $libroDiario;
        } else {
            $balance = $this->contabilidadService->calcularBalanceGeneral($empresasIds, $idEquipo, $fechaCorte, $fechaInicio);
            $reportData = $balance;
        }

        if ($request->ajax() || $request->wantsJson() || $request->has('json')) {
            return response()->json([
                'success' => true,
                'data'    => $reportData,
            ]);
        }

        return view('contabilidad.balance-general', [
            'balance'            => $balance,
            'balanza'            => $balanza,
            'auxiliar'           => $auxiliar,
            'libroDiario'        => $libroDiario,
            'todasEmpresas'      => $todasEmpresas,
            'seleccionEmpresas'  => $seleccionEmpresas,
            'esAdmin'            => $esAdmin,
            'equiposDisponibles' => $equiposDisponibles,
            'equipoSeleccionado' => $equipoSeleccionado,
            'idEquipo'           => $idEquipo,
            'fechaCorte'         => $fechaCorte,
            'fechaInicio'        => $fechaInicio,
            'tipoReporte'        => $tipoReporte,
            'catalogoReportes'   => $catalogoReportes,
        ]);
    }

    /**
     * Retorna el detalle/auxiliar de movimientos contables que integran un concepto del Balance o Resultados.
     */
    public function detalleConcepto(Request $request)
    {
        $request->validate([
            'concepto_id' => 'required|integer|exists:balance_general_conceptos_cuentas,id',
            'fecha_corte' => 'nullable|date',
            'id_equipo'   => 'nullable|integer',
        ]);

        $user = Auth::user();
        $esAdmin = (bool) ($user->es_admin ?? false);
        $todasEmpresas = Empresas::select('id', 'nombre')->orderBy('nombre')->get();

        if ($esAdmin || !$user) {
            $inputEmpresas = $request->input('empresas');
            if (empty($inputEmpresas) || $inputEmpresas === 'todas' || in_array('todas', (array) $inputEmpresas)) {
                $empresasIds = $todasEmpresas->pluck('id')->toArray();
            } else {
                $empresasIds = is_array($inputEmpresas) ? array_map('intval', $inputEmpresas) : [intval($inputEmpresas)];
            }
        } else {
            $empresasIds = [$user->id_empresa];
        }

        $fechaCorte = $request->filled('fecha_corte')
            ? Carbon::parse($request->input('fecha_corte'))->format('Y-m-d')
            : now()->format('Y-m-d');

        $idEquipo = $request->filled('id_equipo') ? (int) $request->input('id_equipo') : null;

        try {
            $detalle = $this->contabilidadService->obtenerDetalleConcepto(
                (int) $request->input('concepto_id'),
                $empresasIds,
                $idEquipo,
                $fechaCorte
            );

            return response()->json([
                'success' => true,
                'data'    => $detalle,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el detalle del concepto: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exportación formal institucional de reportes contables CONAC a PDF y Excel.
     */
    public function imprimirBalance(Request $request)
    {
        $user = Auth::user();
        $esAdmin = (bool) ($user->es_admin ?? false);

        $todasEmpresas = Empresas::select('id', 'nombre')->orderBy('nombre')->get();

        if ($esAdmin) {
            $inputEmpresas = $request->input('empresas');
            if (empty($inputEmpresas) || $inputEmpresas === 'todas' || in_array('todas', (array) $inputEmpresas)) {
                $empresasIds = $todasEmpresas->pluck('id')->toArray();
            } else {
                $empresasIds = is_array($inputEmpresas) ? array_map('intval', $inputEmpresas) : [intval($inputEmpresas)];
            }
        } else {
            $empresasIds = [$user->id_empresa];
        }

        $fechaCorte = $request->filled('fecha_corte')
            ? Carbon::parse($request->input('fecha_corte'))->format('Y-m-d')
            : now()->format('Y-m-d');
        $anio = Carbon::parse($fechaCorte)->year;
        $fechaInicio = $request->filled('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))->format('Y-m-d')
            : "{$anio}-01-01";

        $idEquipo = $request->filled('id_equipo') ? (int) $request->input('id_equipo') : null;
        $equipoSeleccionado = $idEquipo ? Equipo::find($idEquipo) : null;
        $tipoReporte = $request->input('tipo_reporte', 'balance_general');
        $formato = $request->input('formato') ?: ($request->input('download') === 'excel' ? 'excel' : ($request->input('download') ? 'pdf' : null));

        $nombresEmpresas = Empresas::whereIn('id', $empresasIds)->pluck('nombre')->implode(', ');
        if (count($empresasIds) === $todasEmpresas->count() && $esAdmin) {
            $nombresEmpresas = 'CONSOLIDADO GENERAL';
        }

        // Obtener datos del reporte solicitado
        if ($tipoReporte === 'balanza_comprobacion') {
            $reportData = $this->contabilidadService->calcularBalanzaComprobacion($empresasIds, $idEquipo, $fechaCorte, $fechaInicio);
            $tituloReporte = 'BALANZA DE COMPROBACIÓN CONAC • CÓDIGO BC-03';
            $filePrefix = 'Balanza_Comprobacion';
        } elseif ($tipoReporte === 'auxiliar_contable') {
            $reportData = $this->contabilidadService->calcularAuxiliarContable($empresasIds, $idEquipo, $fechaCorte, $fechaInicio);
            $tituloReporte = 'AUXILIAR CONTABLE (LIBRO MAYOR POR CUENTAS) • CONAC AC-07';
            $filePrefix = 'Auxiliar_Contable';
        } elseif ($tipoReporte === 'libro_diario') {
            $reportData = $this->contabilidadService->obtenerLibroDiario($empresasIds, $idEquipo, $fechaCorte, $fechaInicio);
            $tituloReporte = 'LIBRO DIARIO DE PÓLIZAS • CONAC LD-06';
            $filePrefix = 'Libro_Diario_Polizas';
        } elseif ($tipoReporte === 'estado_resultados') {
            $reportData = $this->contabilidadService->calcularBalanceGeneral($empresasIds, $idEquipo, $fechaCorte, $fechaInicio);
            $tituloReporte = 'ESTADO DE ACTIVIDADES / RESULTADOS • CONAC ERA-02';
            $filePrefix = 'Estado_Resultados';
        } else {
            $tipoReporte = 'balance_general';
            $reportData = $this->contabilidadService->calcularBalanceGeneral($empresasIds, $idEquipo, $fechaCorte, $fechaInicio);
            $tituloReporte = 'BALANCE GENERAL (ESTADO DE SITUACIÓN FINANCIERA) • CONAC ESF-01';
            $filePrefix = 'Balance_General';
        }

        // 1. Exportación a Excel
        if ($formato === 'excel') {
            $export = new \App\Exports\ContabilidadReporteExport(
                $tipoReporte,
                $reportData,
                $nombresEmpresas,
                $equipoSeleccionado?->texto_select,
                $fechaCorte,
                $reportData['fecha_inicio'] ?? ($reportData['fecha_inicio_ejercicio'] ?? null)
            );
            $fileName = $filePrefix . '_' . $fechaCorte . '.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download($export, $fileName);
        }

        // 2. Exportación a PDF (Descarga directa o visualización en navegador)
        $dataView = [
            'tipoReporte'     => $tipoReporte,
            'tituloReporte'   => $tituloReporte,
            'data'            => $reportData,
            'nombresEmpresas' => $nombresEmpresas,
            'equipoNombre'    => $equipoSeleccionado?->texto_select,
            'fechaCorte'      => $fechaCorte,
            'fechaInicio'     => $reportData['fecha_inicio'] ?? ($reportData['fecha_inicio_ejercicio'] ?? null),
        ];

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('contabilidad.pdf.reporte-conac', $dataView);
            $orientation = in_array($tipoReporte, ['balanza_comprobacion', 'auxiliar_contable', 'libro_diario']) ? 'landscape' : 'portrait';
            $pdf->setPaper('letter', $orientation);
            $fileName = $filePrefix . '_' . $fechaCorte . '.pdf';

            if ($request->input('download') == '1') {
                return $pdf->download($fileName);
            }
            return $pdf->stream($fileName);
        }

        // Fallback HTML si DomPDF no estuviese disponible
        return view('contabilidad.pdf.reporte-conac', $dataView);
    }

    /**
     * Muestra la interfaz de configuración contable (Matriz de Conversión, Mapeo de Balance y Catálogo CONAC).
     */
    public function configuracionIndex(Request $request)
    {
        $idEmpresa = $request->input('id_empresa', Auth::user()->id_empresa);
        $todasEmpresas = Empresas::select('id', 'nombre')->orderBy('nombre')->get();

        // 1. Conceptos del Balance General (Mapeo a Cuentas CONAC)
        $conceptosBalance = \App\Models\BalanceGeneralConceptoCuenta::paraEmpresa($idEmpresa)->get();

        // 2. Reglas de la Guía Contabilizadora / Matriz de Conversión
        $reglasGuia = \App\Models\GuiaContabilizadora::with(['cuentaCargo', 'cuentaAbono'])
            ->where(function ($q) use ($idEmpresa) {
                $q->where('id_empresa', $idEmpresa)->orWhereNull('id_empresa');
            })
            ->get();

        // 3. Catálogo de Cuentas CONAC
        $cuentasContables = \App\Models\CatCuentaContable::orderBy('codigo')->get();

        return view('contabilidad.configuracion', [
            'conceptosBalance' => $conceptosBalance,
            'reglasGuia'       => $reglasGuia,
            'cuentasContables' => $cuentasContables,
            'todasEmpresas'    => $todasEmpresas,
            'idEmpresa'        => $idEmpresa,
        ]);
    }

    /**
     * Actualiza el prefijo o mapeo de un concepto del Balance General.
     */
    public function guardarConceptoBalance(Request $request)
    {
        $request->validate([
            'id'                     => 'required|integer|exists:balance_general_conceptos_cuentas,id',
            'cuenta_codigo_prefijo' => 'nullable|string|max:50',
            'cuenta_contable_id'    => 'nullable|integer|exists:cat_cuentas_contables,id',
        ]);

        $concepto = \App\Models\BalanceGeneralConceptoCuenta::findOrFail($request->id);
        $concepto->update([
            'cuenta_codigo_prefijo' => $request->cuenta_codigo_prefijo,
            'cuenta_contable_id'    => $request->cuenta_contable_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Concepto de balance actualizado correctamente.',
        ]);
    }

    /**
     * Actualiza o crea una regla en la Guía Contabilizadora para una empresa.
     */
    public function guardarReglaGuia(Request $request)
    {
        $request->validate([
            'evento'          => 'required|string|max:50',
            'subtipo'         => 'nullable|string|max:50',
            'id_empresa'      => 'nullable|integer',
            'cuenta_cargo_id' => 'nullable|integer|exists:cat_cuentas_contables,id',
            'cuenta_abono_id' => 'nullable|integer|exists:cat_cuentas_contables,id',
            'regla_dinamica'  => 'nullable|string|max:100',
        ]);

        $regla = \App\Models\GuiaContabilizadora::updateOrCreate(
            [
                'evento'     => $request->evento,
                'subtipo'    => $request->subtipo,
                'id_empresa' => $request->id_empresa ?: null,
            ],
            [
                'cuenta_cargo_id' => $request->cuenta_cargo_id,
                'cuenta_abono_id' => $request->cuenta_abono_id,
                'regla_dinamica'  => $request->regla_dinamica,
                'activo'          => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Regla de la Guía Contabilizadora guardada con éxito.',
            'data'    => $regla,
        ]);
    }

    /**
     * Muestra la vista de captura/edición de Póliza de Apertura / Saldos Iniciales.
     */
    public function polizaAperturaIndex(Request $request)
    {
        $user = Auth::user();
        $esAdmin = (bool) ($user->es_admin ?? false);

        $todasEmpresas = Empresas::select('id', 'nombre')->orderBy('nombre')->get();
        $idEmpresa = $request->input('id_empresa', $user->id_empresa ?: ($todasEmpresas->first()?->id ?? 1));
        $anio = (int) $request->input('anio', now()->year);

        // Cuentas afectables del catálogo CONAC
        $cuentasAfectables = \App\Models\CatCuentaContable::where('es_afectable', true)
            ->where('activo', true)
            ->where(function ($q) use ($idEmpresa) {
                $q->whereNull('id_empresa')->orWhere('id_empresa', $idEmpresa);
            })
            ->orderBy('codigo')
            ->get();

        // Póliza de apertura existente si la hay
        $polizaExistente = $this->contabilidadService->obtenerPolizaApertura($idEmpresa, $anio);

        return view('contabilidad.poliza-apertura', [
            'todasEmpresas'     => $todasEmpresas,
            'idEmpresa'         => $idEmpresa,
            'anio'              => $anio,
            'esAdmin'           => $esAdmin,
            'cuentasAfectables' => $cuentasAfectables,
            'polizaExistente'   => $polizaExistente,
        ]);
    }

    /**
     * Devuelve en JSON la póliza de apertura para una empresa y año.
     */
    public function polizaAperturaData(Request $request)
    {
        $idEmpresa = (int) $request->input('id_empresa', Auth::user()->id_empresa);
        $anio = (int) $request->input('anio', now()->year);

        $poliza = $this->contabilidadService->obtenerPolizaApertura($idEmpresa, $anio);

        return response()->json([
            'success' => true,
            'poliza'  => $poliza,
        ]);
    }

    /**
     * Almacena o actualiza la Póliza de Apertura / Saldos Iniciales con cuadre estricto.
     */
    public function guardarPolizaApertura(Request $request)
    {
        $validated = $request->validate([
            'id_empresa'                 => 'required|integer|exists:empresas,id',
            'fecha'                      => 'required|date',
            'concepto'                   => 'nullable|string|max:255',
            'movimientos'                => 'required|array|min:2',
            'movimientos.*.cuenta_contable_id' => 'required|integer|exists:cat_cuentas_contables,id',
            'movimientos.*.debe'         => 'required|numeric|min:0',
            'movimientos.*.haber'        => 'required|numeric|min:0',
            'movimientos.*.referencia'   => 'nullable|string|max:100',
            'movimientos.*.concepto_detalle' => 'nullable|string|max:255',
        ]);

        try {
            $poliza = $this->polizaService->guardarPolizaApertura($validated);

            return response()->json([
                'success' => true,
                'message' => 'Póliza de Apertura guardada y aplicada exitosamente con partida doble.',
                'poliza'  => $poliza,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la póliza de apertura: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Muestra el Manual Contable y Guía Contabilizadora CONAC / LGCG.
     */
    public function manual()
    {
        return view('contabilidad.manual');
    }

    /**
     * Previsualiza los importes y la póliza de cierre de ejercicio.
     */
    public function previsualizarCierre(Request $request)
    {
        $idEmpresa = (int) $request->input('id_empresa', auth()->user()?->id_empresa);
        $fechaInicio = $request->input('fecha_inicio', '2025-08-01');
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));
        $nombreEjercicio = $request->input('nombre_ejercicio');

        try {
            $data = $this->contabilidadService->previsualizarCierreContable($idEmpresa, $fechaInicio, $fechaFin, $nombreEjercicio);
            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Ejecuta y aplica formalmente el cierre contable de ejercicio.
     */
    public function ejecutarCierre(\App\Http\Requests\CierreContableRequest $request)
    {
        try {
            $idEmpresa = (int) $request->input('id_empresa');
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');
            $nombreEjercicio = $request->input('nombre_ejercicio');

            $resultado = $this->contabilidadService->ejecutarCierreContable($idEmpresa, $fechaInicio, $fechaFin, $nombreEjercicio);

            return response()->json([
                'success' => true,
                'message' => 'Póliza de cierre de ejercicio contable generada y aplicada exitosamente.',
                'data'    => $resultado,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar el cierre contable: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Consulta el historial de cierres contables aplicados.
     */
    public function historialCierres(Request $request)
    {
        $idEmpresa = (int) $request->input('id_empresa', auth()->user()?->id_empresa);
        $historial = $this->contabilidadService->obtenerHistorialCierres($idEmpresa);

        return response()->json([
            'success' => true,
            'data'    => $historial,
        ]);
    }

    /**
     * Revierte un cierre contable aplicado.
     */
    public function revertirCierre(Request $request, int $id)
    {
        $idEmpresa = (int) $request->input('id_empresa', auth()->user()?->id_empresa);

        try {
            $this->contabilidadService->revertirCierreContable($id, $idEmpresa);

            return response()->json([
                'success' => true,
                'message' => 'Póliza de cierre contable revertida y cancelada correctamente.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al revertir el cierre: ' . $e->getMessage(),
            ], 422);
        }
    }
}
