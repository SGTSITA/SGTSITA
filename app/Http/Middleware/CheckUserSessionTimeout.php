<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserSessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
       if (!Auth::check()) {
        return $next($request);
    }

    $timeoutSeconds = (int) config('session.lifetime') * 60;
    $now = time();

    $rutasPasivas = [
        'notificaciones/usuario/listar',
        'notificaciones/usuario/contador',
        'whatsapp/status*',
        'whatsapp/check*',
    ];

    $esRutaPasiva = false;

    foreach ($rutasPasivas as $ruta) {
        if ($request->is($ruta)) {
            $esRutaPasiva = true;
            break;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Primero leemos la última actividad ANTES de actualizarla
    |--------------------------------------------------------------------------
    */
    $lastActivity = session('last_user_activity_at');

    /*
    |--------------------------------------------------------------------------
    | Fallback por si alguna sesión vieja no trae last_user_activity_at
    |--------------------------------------------------------------------------
    */
    if (!$lastActivity) {
        session([
            'last_user_activity_at' => $now,
        ]);

        return $next($request);
    }

    /*
    |--------------------------------------------------------------------------
    | Primero validamos si ya expiró
    |--------------------------------------------------------------------------
    */
    if (($now - $lastActivity) > $timeoutSeconds) {
        $sistemaActual = session('sistema_actual');

        Auth::logout();

        $request->session()->forget([
            'sistema_actual',
            'last_user_activity_at',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $redirect = $sistemaActual === 'scb'
            ? route('scb.login')
            : url('login');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'La sesión expiró por inactividad.',
                'redirect' => $redirect,
            ], 419);
        }

        return redirect($redirect)
            ->withErrors([
                'email' => 'Tu sesión expiró por inactividad.',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Solo después de validar, renovamos actividad si NO es ruta pasiva
    |--------------------------------------------------------------------------
    */
    if (!$esRutaPasiva) {
        session([
            'last_user_activity_at' => $now,
        ]);
    }

    return $next($request);
}
}
