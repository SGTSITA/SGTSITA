<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditoriaCifrado;

class AuditoriaController extends Controller
{
    public function index()
    {
        if (!Auth::user()->es_admin) {
            abort(403, 'No autorizado, solo administradores');
        }
        $logs = ActivityLog::with('user')
            ->latest();

        return view('auditoria.index', compact('logs'));
    }

    public function show($id)
    {
        if (!Auth::user()->es_admin) {
            abort(403, 'No autorizado, solo administradores');
        }
        $log = ActivityLog::with('user')->findOrFail($id);

        return response()->json([
            'accion' => $log->action,
            'modelo' => $log->model,
            'modelo_id' => $log->model_id,
            'usuario' => $log->user?->name ?? 'Sistema',
            'fecha' => $log->created_at->format('Y-m-d H:i:s'),
            'old' => AuditoriaCifrado::safeDecrypt($log->old_values),
            'new' => AuditoriaCifrado::safeDecrypt($log->new_values),
        ]);
    }
}
