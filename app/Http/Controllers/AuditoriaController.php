<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Empresas;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditoriaCifrado;
use App\Services\AuditoriaModelMapperService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AuditoriaController extends Controller
{
    public function index()
    {
        // Log::info('Auditoria@index - inicio');

        if (!Auth::check()) {
            Log::warning('Auditoria@index - usuario NO autenticado');
            abort(401, 'No autenticado');
        }

        Log::info('Audit - usuario autenticado', [
            'user_id'  => Auth::id(),
            'es_admin' => Auth::user()->es_admin ?? null,
        ]);

        if (!Auth::user()->es_admin) {
            Log::warning('Auditoria@index - acceso denegado (no admin)', [
                'user_id' => Auth::id(),
            ]);
            abort(403, 'No autorizado, solo administradores');
        }

        $empresas = Empresas::all();
        $modulosAgrupados = AuditoriaModelMapperService::getModulosAgrupados();

        return view('auditoria.index', compact('empresas', 'modulosAgrupados'));
    }

    public function data(Request $request)
    {
        if (!Auth::user()->es_admin) {
            abort(403);
        }
        $query = ActivityLog::with(['user', 'empresa'])
            ->orderByDesc('created_at');

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('created_at', [
                $request->fecha_inicio . ' 00:00:00',
                $request->fecha_fin . ' 23:59:59'
            ]);
        }

        if ($request->filled('referencia')) {
            $query->where('referencia', 'like', '%' . $request->referencia . '%');
        }

        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        if ($request->filled('modulos')) {
            $modelos = AuditoriaModelMapperService::getModelosPorClaves($request->modulos);
            if (!empty($modelos)) {
                $query->whereIn('model', $modelos);
            }
        }

        $logs = $query->get();

        $logs->each(function ($log) {
            $log->modelo_label = AuditoriaModelMapperService::getEtiquetaAmigable($log->model);
        });

        return $logs;
    }

    public function show($id)
    {
        if (!Auth::user()->es_admin) {
            abort(403, 'No autorizado, solo administradores');
        }
        $log = ActivityLog::with(['user', 'empresa'])->findOrFail($id);

        return response()->json([
            'id' => $id,
            'accion' => $log->action,
            'modelo' => $log->model,
            'modelo_label' => AuditoriaModelMapperService::getEtiquetaAmigable($log->model),
            'modelo_id' => $log->model_id,
            'usuario' => $log->user?->name ?? 'Sistema',
            'correo' => $log->user?->email ?? '',
            'fecha' => $log->created_at->format('Y-m-d H:i:s'),
            'empresa' => $log->empresa?->nombre,
            'referencia' => $log->referencia,
            'old' => AuditoriaCifrado::safeDecrypt($log->old_values),
            'new' => AuditoriaCifrado::safeDecrypt($log->new_values),
        ]);
    }

    public function exportPdf(Request $request, $id)
    {
        $log = ActivityLog::with(['user', 'empresa'])->findOrFail($id);

        $old = AuditoriaCifrado::safeDecrypt($log->old_values) ?? [];
        $new = AuditoriaCifrado::safeDecrypt($log->new_values) ?? [];

        $keys = collect(array_keys($old))
            ->merge(array_keys($new))
            ->unique();

        $cambios = [];

        foreach ($keys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $new[$key] ?? null;

            if ($oldVal !== $newVal) {
                $cambios[$key] = [
                    'old' => $oldVal,
                    'new' => $newVal
                ];
            }
        }

        $data = [
            'accion' => $log->action,
            'modelo' => $log->model,
            'modelo_label' => AuditoriaModelMapperService::getEtiquetaAmigable($log->model),
            'modelo_id' => $log->model_id,
            'usuario' => optional($log->user)->name ?? 'Sistema',
            'correo' => $log->user?->email ?? '',
            'empresa' => optional($log->empresa)->nombre ?? 'N/A',
            'fecha' => $log->created_at,
            'referencia' => $log->referencia,
            'cambios' => $cambios,
        ];

        $pdf = Pdf::loadView('auditoria.pdf', compact('data'));

        return $pdf->download("auditoria_{$id}.pdf");
    }
}
