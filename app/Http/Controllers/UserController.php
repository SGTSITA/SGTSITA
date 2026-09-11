<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use App\Models\Empresas;
use App\Models\Client;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $user = auth()->user();


        $query = User::with('Empresa')
            ->orderBy('id', 'DESC');

        if (!$user->es_admin) {

            $query->where('id_empresa', $user->id_empresa);
        }


        if ($request->has('json')) {
            $users = $query->get();

            return response()->json($users->map(function ($user) {
                return [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'empresa' => ['nombre' => optional($user->Empresa)->nombre],
                    'roles'   => $user->getRoleNames()
                ];
            }));
        }


        $data = $query->paginate(10);

        return view('users.index', compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }


    public function index_externos()
    {
        $clientes = Client::where('id_empresa', '=', auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        return view('manager.usuarios.crear-usuario-externo', ["clientes" => $clientes]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();

        $user = auth()->user();


        $listaEmpresas = $user->es_admin
            ? Empresas::orderBy('nombre')->get()
            : Empresas::where('id', $user->id_empresa)
                ->orderBy('nombre')
                ->get();


        $clientes = $user->es_admin
            ? Client::orderBy('created_at', 'desc')->get()
            : Client::where('id_empresa', $user->id_empresa)
                ->orderBy('created_at', 'desc')
                ->get();

        return view('users.create', compact('roles', 'listaEmpresas', 'clientes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required|array',
            'id_empresa' => 'required|exists:empresas,id',
            'id_cliente' => 'nullable|integer|min:0',
        ]);


        $input = $request->only(['name', 'email', 'password', 'id_empresa', 'id_cliente']);
        $input['password'] = Hash::make($input['password']);

        $input['es_admin'] = $request->has('es_admin');
        $roles = $request->input('roles');

        // Si no es cliente, se borra el id_cliente
        if (!in_array('CLIENTE', $roles)) {
            $input['id_cliente'] = 0;
        }

        $user = User::create($input);
        $user->assignRole($roles);

        if ($request->has('uuid')) {
            return response()->json(["TMensaje" => "success", "Mensaje" => "Usuario creado correctamente"]);
        }

        Session::flash('success', 'Se ha guardado sus datos con éxito');
        return redirect()->route('users.index')
                        ->with('success', 'Usuario creado con éxito');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();

        $userAuth = auth()->user();

        /* EMPRESAS */
        $listaEmpresas = $userAuth->es_admin
            ? Empresas::orderBy('id', 'DESC')->get()
            : Empresas::where('id', $userAuth->id_empresa)->get();
        /* CLIENTES */
        $clientes = $userAuth->es_admin
            ? Client::orderBy('created_at', 'desc')->get()
            : Client::where('id_empresa', $userAuth->id_empresa)
                ->orderBy('created_at', 'desc')
                ->get();


        return view('users.edit', compact('user', 'roles', 'userRole', 'listaEmpresas', 'clientes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
            'roles' => 'required'
        ]);




        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, array('password'));
        }

        $user = User::find($id);
        $input['id_cliente'] = $user->id_cliente;
        $user->update($input);
        DB::table('model_has_roles')->where('model_id', $id)->delete();

        $user->assignRole($request->input('roles'));


        return redirect()->route('users.index')
                        ->with('success', 'Usuario actualizado con exito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::find($id)->delete();

        Session::flash('delete', 'Se ha eliminado sus datos con exito');
        return redirect()->route('users.index')
                        ->with('success', 'User deleted successfully');
    }


    public function cambiarEmpresa(Request $request)
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
        ]);


        $user = User::findOrFail(auth()->id());
        $user->id_empresa = $request->empresa_id;
        $user->save();

        return redirect()->back()->with('success', 'Empresa cambiada correctamente.');
    }

    public function resetPassword($id)
    {
        try {
            $user = User::findOrFail($id);

            // Generar una contraseña temporal
            $tempPassword = Str::random(8);

            // Guardar en BD
            $user->password = Hash::make($tempPassword);
            $user->save();

            // Responder JSON
            return response()->json([
                'success' => true,
                'temp_password' => $tempPassword,
                'email' => $user->email,
                'name' => $user->name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar la contraseña: ' . $e->getMessage()
            ]);
        }
    }
}
