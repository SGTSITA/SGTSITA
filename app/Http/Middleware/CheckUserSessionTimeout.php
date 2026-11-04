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

        $timeoutSeconds = ((int) config('session.lifetime', 30)) * 60;
        $now = time();

        $rutasPasivas = [
            'notificaciones/usuario/listar',
            'notificaciones/usuario/contador',
            'whatsapp/status*',
            'whatsapp/check*',
        ];

        $rutasRastreo = [
            'coordenadas*',
            'mapa-comparacion*',
            'configurar-geocerca*',
            'gps*',
            'reporteria/consumo-unidades/mapa*',
            'session/keep-alive*',
        ];

        $esRutaPasiva = false;
        foreach ($rutasPasivas as $ruta) {
            if ($request->is($ruta)) {
                $esRutaPasiva = true;
                break;
            }
        }

        $esRutaRastreo = $request->header('X-SGT-Map-Tracking') === '1';
        if (!$esRutaRastreo) {
            foreach ($rutasRastreo as $ruta) {
                if ($request->is($ruta)) {
                    $esRutaRastreo = true;
                    break;
                }
            }
        }

        if (!$esRutaRastreo) {
            $referer = (string) $request->headers->get('referer', '');
            if ($referer !== '') {
                $refererPath = ltrim((string) parse_url($referer, PHP_URL_PATH), '/');
                foreach ($rutasRastreo as $ruta) {
                    if (\Illuminate\Support\Str::is($ruta, $refererPath)) {
                        $esRutaRastreo = true;
                        break;
                    }
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Si está en rastreo de mapas o keep-alive, nunca cerrar la sesión:
        | renovamos last_user_activity_at y continuamos.
        |--------------------------------------------------------------------------
        */
        if ($esRutaRastreo) {
            $lastActivity = (int) session('last_user_activity_at', 0);
            if (($now - $lastActivity) >= 10) {
                session(['last_user_activity_at' => $now]);
            }

            return $next($request);
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
        | Validamos si ya expiró el tiempo de inactividad
        |--------------------------------------------------------------------------
        */
        if (($now - (int) $lastActivity) > $timeoutSeconds) {
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
        | Renovamos actividad si NO es ruta pasiva (con ventana de 10s para evitar
        | condiciones de carrera en disco con peticiones AJAX concurrentes).
        |--------------------------------------------------------------------------
        */
        if (!$esRutaPasiva && ($now - (int) $lastActivity) >= 10) {
            session([
                'last_user_activity_at' => $now,
            ]);
        }

        return $next($request);
    }
}
