<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Cotizaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\UserEmpresa;
use Illuminate\Support\Facades\Auth;

class CustomAuthController extends Controller
{
    public function index()
    {
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
            if (Auth::attempt($credentials)) {

                return redirect()->intended('dashboard')
                            ->withSuccess('Signed in');


            }
    $user = Auth::user();
           if (!$user->can('SGT-Acceso')) {
        Auth::logout();

        $request->session()->regenerateToken();

        return response()->json([
            'success' => false,
            'code' => 'without_sgt_access',
            'mensaje' => 'Tu usuario no tiene acceso al sistema SGT. Verifica que estés entrando al sistema correcto.',
        ], 403);
    }


            // dd('las credenciales son incorrectas');
            //  return redirect("login")->withSuccess('Login details are not valid');
            return response([
                                  "mensaje" => "Las credenciales de acceso son incorrectas. Verifique su información"
                              ], 401);

        } else {
            // dd('else request password');

            $client = Client::where('email', $request->email)->firstOrFail();



        }

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
        $check = $this->create($data);

        return redirect("dashboard")->withSuccess('You have signed-in');
    }

    public function create(array $data)
    {
        return User::create([
          'name' => $data['name'],
          'email' => $data['email'],
          'password' => Hash::make($data['password'])
        ]);
    }

    public function dashboard()
    {
        if (Auth::check()) {
            return view('dashboard');
        }

        return redirect("login")->withSuccess('You are not allowed to access');
    }

    public function signOut()
    {

        $user = auth()->user();



         Cotizaciones::where('editing_by', auth()->id())
        ->update([
            'editing_by' => null,
            'editing_at' => null,
        ]);



        Session::flush();
        Auth::logout();

        return Redirect('login');
    }
}
