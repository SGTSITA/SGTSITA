<?php

namespace App\Services\scb;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScbAuthService
{
    public function check(): bool
    {
        return Auth::check()
            && session('sistema_actual') === 'scb'
            && Auth::user()?->can('SCB-Acceso');
    }

    public function login(string $email, string $password, bool $remember = false): array
    {
        $credentials = [
            'email' => $email,
            'password' => $password,
        ];

        if (!Auth::attempt($credentials, $remember)) {
            return [
                'success' => false,
                'message' => 'Las credenciales no son correctas.',
            ];
        }

        $user = Auth::user();

        if (!$user->can('SCB-Acceso')) {
            Auth::logout();

            return [
                'success' => false,
                'code' => 'without_access',
                'message' => 'Tu usuario no tiene acceso a este sistema.',
            ];
        }

       session([
    'sistema_actual' => 'scb',
    'last_user_activity_at' => time(),
]);

        return [
            'success' => true,
            'message' => 'Acceso correcto.',
        ];
    }

    public function logout(Request $request): void
    {
        Auth::logout();

        $request->session()->forget([
    'sistema_actual',
    'last_user_activity_at',
]);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }


}
