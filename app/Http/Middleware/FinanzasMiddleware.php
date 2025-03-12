<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinanzasMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $nivelRequerido)
    {
        $user = Auth::user(); // Obtener usuario autenticado

        if (!$user || $user->x_access_token != env('X_ACCESS_TOKEN')) {
            return abort(403);
        }

        return $next($request);
    }
}
