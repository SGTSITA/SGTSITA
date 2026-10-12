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
        $fechaCorte = $request->filled('fecha_corte')
            ? Carbon::parse($request->input('fecha_corte'))->format('Y-m-d')
            : now()->format('Y-m-d');

        $idEquipo = $request->filled('id_equipo') ? (int) $request->input('id_equipo') : null;

        // 3. Obtener tractocamiones para el selector
        $equiposDisponibles = Equipo::whereIn('id_empresa', $empresasIds)
            ->where('tipo', 'Tractos / Camiones')
            ->where('activo', 1)
            ->orderBy('id_equipo', 'asc')
            ->get();

        $equipoSeleccionado = $idEquipo ? Equipo::find($idEquipo) : null;

        // 4. Calcular balance y estado de resultados
        $balance = $this->contabilidadService->calcularBalanceGeneral($empresasIds, $idEquipo, $fechaCorte);

        if ($request->ajax() || $request->wantsJson() || $request->has('json')) {
            return response()->json([
                'success' => true,
                'data'    => $balance,
            ]);
        }

        return view('contabilidad.balance-general', [
            'balance'            => $balance,
            'todasEmpresas'      => $todasEmpresas,
            'seleccionEmpresas'  => $seleccionEmpresas,
            'esAdmin'            => $esAdmin,
            'equiposDisponibles' => $equiposDisponibles,
            'equipoSeleccionado' => $equipoSeleccionado,
            'idEquipo'           => $idEquipo,
            'fechaCorte'         => $fechaCorte,
        ]);
    }

    /**
     * Vista de impresión limpia y exportación para Balance General y Estado de Resultados.
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

        $idEquipo = $request->filled('id_equipo') ? (int) $request->input('id_equipo') : null;
        $equipoSeleccionado = $idEquipo ? Equipo::find($idEquipo) : null;

        $balance = $this->contabilidadService->calcularBalanceGeneral($empresasIds, $idEquipo, $fechaCorte);

        $nombresEmpresas = Empresas::whereIn('id', $empresasIds)->pluck('nombre')->implode(', ');
        if (count($empresasIds) === $todasEmpresas->count() && $esAdmin) {
            $nombresEmpresas = 'CONSOLIDADO GENERAL';
        }

        return view('contabilidad.balance-general-print', [
            'balance'            => $balance,
            'nombresEmpresas'    => $nombresEmpresas,
            'equipoSeleccionado' => $equipoSeleccionado,
            'fechaCorte'         => $fechaCorte,
        ]);
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
}
