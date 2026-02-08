<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditoriaCifrado;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

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

        //  Log::info('Auditoria@index - usuario es admin, consultando logs');



        // Log::info('Auditoria@index - logs obtenidos', [
        //     'total' => $logs->count(),
        // ]);

        return view('auditoria.index', );

    }
    public function data(Request $request)
    {
        if (!Auth::user()->es_admin) {
            abort(403);
        }
        $query = ActivityLog::with('user')
        ->orderByDesc('created_at');

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('created_at', [
                $request->fecha_inicio . ' 00:00:00',
                $request->fecha_fin . ' 23:59:59'
            ]);
        }

        return $query->paginate(100);

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
