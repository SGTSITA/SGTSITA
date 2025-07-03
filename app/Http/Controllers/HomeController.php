<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotizaciones;
use App\Models\Asignaciones;
use App\Models\UserEmpresa;
use Spatie\Permission\Models\Role;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        //Asignar empresa Inicial usuario
         $user =\Auth::user();
        $userActual =\Auth::user()->id;
        $empActual =\Auth::user()->id_empresa;
       
      

         $empresasAsignadas = auth()->user()->empresasAsignadas()->get();

         $user = User::find($userActual);

        $roles = Role::pluck('name','name')->all();

       
       
        //dd($roles);
         $userRole = $user->roles->pluck('name','name')->all();
        //dd($userRole);
        
       if ($user->hasRole('MEP')) {
            return view('mep.index');
        }
        else {
            if(\Auth::user()->id_cliente != 0){
                        return view('cotizaciones.externos.step_one',compact('empresasAsignadas','empActual','userActual'));
                    }else{
                        return view('dashboard',compact('empresasAsignadas','empActual','userActual'));
                    }

        }
        //
         
         

        
        

    }
}
