<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\scb\ScbAuthService;
use App\Http\Controllers\Controller;


class ScbAuthController extends Controller
{
     public function __construct(
        protected ScbAuthService $authService
    ) {
    }

    public function showLogin()
    {
        if ($this->authService->check()) {
            return redirect()->route('scb.dashboard');
        }

        return view('scb.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $login = $this->authService->login(
            email: $request->email,
            password: $request->password,
            remember: $request->boolean('remember')
        );

          if (!$login['success']) {
        if (($login['code'] ?? null) === 'without_access') {
            $request->session()->regenerateToken();
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => $login['message'],
            ]);
    }

        $request->session()->regenerate();

        return redirect()->intended(route('scb.dashboard'));
    }

   public function logout(Request $request)
{
    $this->authService->logout($request);

    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente.',
            'redirect' => route('scb.login'),
        ]);
    }

    return redirect()->route('scb.login');
}
}
