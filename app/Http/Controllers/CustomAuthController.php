<?php
namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Hash;
use Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class CustomAuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function customLogin(Request $request)
    {
        $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();

              

                return response()->json([
                    'mensaje' => 'Inicio de sesión exitoso',
                    'redirect' => route('dashboard'),
                  
                ]);
            }

            return response()->json([
                'mensaje' => 'Las credenciales de acceso son incorrectas. Verifique su información.'
            ], 401);

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
        if(Auth::check()){
            return view('dashboard');
        }

        return redirect("login")->withSuccess('You are not allowed to access');
    }

    public function signOut() {
        
       $user = auth()->user();

    
    $empresaInicial = UserEmpresa::where('id_user', $user->id)
        ->where('empresaInicial', 1)
        ->first();

    if ($empresaInicial) {
        
        $user->id_empresa = $empresaInicial->id_empresa;
        $user->save();
    }
    
        Session::flush();
        Auth::logout();

        return Redirect('login');
    }
}
