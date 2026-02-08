<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditoriaCifrado;
use Illuminate\Support\Facades\Log;

class AuditoriaController extends Controller
{
    public function index()
    {
        Log::info('Auditoria@index - inicio');

        if (!Auth::check()) {
            Log::warning('Auditoria@index - usuario NO autenticado');
            abort(401, 'No autenticado');
        }

        Log::info('Auditoria@index - usuario autenticado', [
            'user_id'  => Auth::id(),
            'es_admin' => Auth::user()->es_admin ?? null,
        ]);

        if (!Auth::user()->es_admin) {
            Log::warning('Auditoria@index - acceso denegado (no admin)', [
                'user_id' => Auth::id(),
            ]);
            abort(403, 'No autorizado, solo administradores');
        }

        Log::info('Auditoria@index - usuario es admin, consultando logs');

        $logs = ActivityLog::with('user')
            ->latest()
            ->get();

        Log::info('Auditoria@index - logs obtenidos', [
            'total' => $logs->count(),
        ]);

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
