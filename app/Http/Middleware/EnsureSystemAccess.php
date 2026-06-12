<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSystemAccess
{
    public function handle(Request $request, Closure $next)
    {
        /*
         * Modo compatible:
         * Si no hay usuario logueado, no tocamos nada.
         * Así no rompemos rutas públicas que ya existen.
         */
        if (!$request->user()) {
            return $next($request);
        }

        /*
         * SCB tiene su propio acceso.
         */
        if ($this->esRutaScb($request)) {
            return $next($request);
        }

        /*
         * Rutas públicas o compartidas que no deben exigir SGT-Acceso.
         */
        if ($this->esRutaPublica($request)) {
            return $next($request);
        }

        /*
         * Todo lo demás lo tratamos como SGT.
         */
        if ($request->user()->can('SGT-Acceso')) {
            return $next($request);
        }

        /*
         * Si el usuario solo tiene acceso a SCB, lo regresamos a SCB.
         */
        if ($request->user()->can('SCB-Acceso')) {
            if ($request->expectsJson() || !$request->isMethod('GET')) {
                abort(403, 'Tu usuario no tiene acceso al sistema SGT.');
            }

            return redirect()
                ->route('scb.dashboard')
                ->with('error', 'Tu usuario no tiene acceso al sistema SGT.');
        }

        abort(403, 'Tu usuario no tiene acceso a este sistema.');
    }

    private function esRutaScb(Request $request): bool
    {
        return $request->is('scb') ||
            $request->is('scb/*') ||
            $request->routeIs('scb.*');
    }

    private function esRutaPublica(Request $request): bool
    {
        /*
         * Nombres de rutas públicas o de auth.
         */
        if ($request->routeIs([
            'login',
            'login.custom',
            'register-user',
            'register.custom',
            'signout',
            'aviso-privacidad',
            'google.redirect',
        ])) {
            return true;
        }

        /*
         * Paths públicos.
         * Ajusta esta lista si tienes más rutas públicas reales.
         */
        return $request->is(
            '/',
            'login',
            'custom-login',
            'registration',
            'custom-registration',
            'signout',
            'aviso-privacidad',
            'auth/google',
            'auth/google/callback',

            /*
             * Rutas externas / compartidas que parecen públicas en tu web.php.
             */
            'coordenadas/questions/*',
            'coordenadas/edit/*',
            'coordenadas/guardarresp',
            'coordenadas/compartir/save',
            'coordenadas/mapa_rastreo',
            'coordenadas/mapa_rastreo_varios',
            'coordenadas/conboys/encontrar',
            'coordenadas/conboys/getconvoy/*',
            'coordenadas/conboys/agregar'
        );
    }
}
