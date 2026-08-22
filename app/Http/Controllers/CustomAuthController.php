<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Cotizaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CustomAuthController extends Controller
{
    public function index(Request $request)
    {

        if (
            Auth::check()
            && session('sistema_actual') === 'sgt'
            && Auth::user()?->can('SGT-Acceso')
        ) {
            return redirect('dashboard');
        }

        if (Auth::check()) {
            $this->cerrarSesionCompleta($request);
        }

        return view('auth.login');
    }

    public function customLogin(Request $request)
    {
        if ($request->filled('password')) {
            $request->validate([
                'email' => 'required',
                'password' => 'required',
            ]);

            $credentials = $request->only('email', 'password');


            if (!Auth::attempt($credentials)) {
                return response([
                    'mensaje' => 'Las credenciales de acceso son incorrectas. Verifique su información',
                ], 401);
            }


            $request->session()->regenerate();

            $user = Auth::user();

            if (!$user) {
                $this->cerrarSesionCompleta($request);

                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se pudo iniciar sesión correctamente.',
                ], 401);
            }


            if (!$user->can('SGT-Acceso')) {
                $this->cerrarSesionCompleta($request);

                return response()->json([
                    'success' => false,
                    'code' => 'without_sgt_access',
                    'mensaje' => 'Tu usuario no tiene acceso al sistema SGT. Verifica que estés entrando al sistema correcto.',
                ], 403);
            }


           session([
    'sistema_actual' => 'sgt',
    'last_user_activity_at' => time(),
]);


            return redirect('dashboard')
                ->withSuccess('Signed in');
        }


        $client = Client::where('email', $request->email)->firstOrFail();

        // Aquí dejas tu lógica anterior de login sin password.
    }

    public function registration()
    {
        return view('auth.registration');
    }

    public function customRegistration(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $data = $request->all();
        $this->create($data);

        return redirect('dashboard')->withSuccess('You have signed-in');
    }

    public function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function dashboard(Request $request)
    {

        if (
            Auth::check()
            && session('sistema_actual') === 'sgt'
            && Auth::user()?->can('SGT-Acceso')
        ) {
            return view('dashboard');
        }

        $this->cerrarSesionCompleta($request);

        return redirect('login')
            ->withErrors([
                'email' => 'Debes iniciar sesión en el sistema SGT.',
            ]);
    }

    public function signOut(Request $request)
    {

        if (Auth::check()) {
            Cotizaciones::where('editing_by', Auth::id())
                ->update([
                    'editing_by' => null,
                    'editing_at' => null,
                ]);
        }

        $this->cerrarSesionCompleta($request);

        return redirect('login');
    }

    private function cerrarSesionCompleta(Request $request): void
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
