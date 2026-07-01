<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (TokenMismatchException $e, $request) {
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
                'message' => 'Tu sesión expiró. Inicia sesión nuevamente.',
                'redirect' => $redirect,
            ], 419);
        }

        return redirect($redirect)
            ->withErrors([
                'email' => 'Tu sesión expiró. Inicia sesión nuevamente.',
            ]);
    });
    }
}
