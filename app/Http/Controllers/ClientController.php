<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Subclientes;
use App\Models\User;
use Illuminate\Http\Request;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Session;
use DB;
use Log;
use Hash;
use Mail;
use Auth;

/**
 * Class ClientController
 * @package App\Http\Controllers
 */
class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {

        $clients = Client::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        $subclientes = Subclientes::where('id_empresa' ,'=',auth()->user()->id_empresa)->orderBy('created_at', 'desc')->get();

        return view('client.index', compact('clients', 'subclientes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $client = new Client();
        return view('client.create', compact('client'));
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        try{
            DB::beginTransaction();
            $this->validate($request, [
                'nombre' => 'required',
                'correo' => 'required',
                'telefono' => 'required'
            ]);

            $fechaActual = date('Y-m-d');

            $client = new Client;
            $client->id_empresa = auth()->user()->id_empresa;

            $client->nombre = $request->get('nombre');
            $client->correo = $request->get('correo');
            $client->telefono = $request->get('telefono');
            $client->direccion = $request->get('direccion');
            $client->regimen_fiscal = $request->get('regimen_fiscal');
            $client->rfc = $request->get('rfc');
            $client->nombre_empresa = $request->get('nombre_empresa');
            $client->fecha = $fechaActual;
            $client->save();

            //Crear usuario para el cliente
            $welcomePassword = strtoupper(uniqid());
            $clientUser['name'] = $request->get('nombre');
            $clientUser['email'] = $request->get('correo');
            $clientUser['password'] = Hash::make($welcomePassword);
            $clientUser['id_empresa'] = auth()->user()->id_empresa;
            $clientUser['id_cliente'] = $client->id;

            $user = User::create($clientUser);
            $user->assignRole([3]); // Se asgna ROL de cliente
            DB::commit();

            $emails = [$request->get('correo'),env('MAIL_NOTIFICATIONS')];
            Mail::to($emails)->send(new \App\Mail\WelcomeMail($client, $welcomePassword));
            


            return response()->json(["TMensaje" =>"success", "Titulo" => "Cliente creado correctamente", "Mensaje" => "El cliente se ha creado con exito. Contraseña de acceso generada y enviada correctamente: $welcomePassword"]);
          //  Session::flash('success', 'Se ha guardado sus datos con exito, Usuario de acceso generado automaticamente con contraseña: '.$welcomePassword);
            //return redirect()->back()->with('success', 'Cliente creado correctamente. Proporcione contraseña: '.$welcomePassword);
        }catch(\Throwable $t){
            DB::rollback();
            Log::channel('daily')->info('Error al crear cliente: '.$t->getMessage());
            return response()->json(["TMensaje" =>"error", "Titulo" => "Ocurrio un error", "Mensaje" => $t->getMessage()]);

            //Session::flash('warning', 'Error:'.$t->getMessage());

            //return redirect()->back()->with('error', 'No se pudo crear el cliente. '.$t->getMessage());
        }
        

    }

    /**
     * Obtiene la lista de clientes del usuario logueado (según la empresa a la que corresponde)
     */
    public function get_list(){
        $clientes = Client::join('client_empresa as ce','clients.id','=','ce.id_client')
                            ->where('ce.id_empresa',Auth::User()->id_empresa)
                            ->where('is_active',1)
                            ->orderBy('nombre')->get();

        $list = $clientes->map(function($c){
            return [
                "IdCliente" => $c->id,
                "Nombre" => $c->nombre,
                "Correo" => $c->correo,
                "Telefono" => $c->telefono,
                "RFC" => $c->rfc,
                "RegimenFiscal" => $c->regimen_fiscal,
                "Empresa" => $c->nombre_empresa,
                "Direccion" => $c->direccion,
            ];
        });

        return response()->json(["list" => $list, "TMensaje" => "success"]);
    }

    /**
     * Vista disponible unicamente en Modulo Externo de Clientes
     */
    public function index_subcliente(){
        return view('client.cliente_externo');
    }

    public function new_subcliente(Request $request){
        return view('client.new_subclient',["idClient" => $request->idClient]);
    }

    public function subcliente_list(){
        return view('client.subcliente_list');
    }

    public function subcliente_list_internal(Request $request){
        $client = Client::where('id',$request->id_client)->first();
        return view('client.subcliente_list_internal',["id_client" => $request->id_client, "client" => $client]);
    }

    public function subcliente_get_list(Request $request){
        $subClientes = Subclientes::where('id_cliente',$request->_cliente)->get();
        $list = $subClientes->map(function ($s){
            return [
                "IdSubCliente" => $s->id,
                "SubCliente" => $s->nombre,
                "RFC" => $s->rfc,
                "NombreComercial" => $s->nombre_empresa,
                "CorreoElectronico" => $s->correo,
                "Telefono" => $s->telefono
            ];
        });

        return response()->json(["TMensaje" => "success", "data" => $list]);
    }

    public function store_subclientes(Request $request){
        $this->validate($request, [
            'nombre' => 'required',
            'correo' => 'required',
            'telefono' => 'required'
        ]);

        $fechaActual = date('Y-m-d');

        $client = new Subclientes;
        $client->id_cliente = $request->has('id_client') ? $request->id_client : $request->id_cliente;
        $client->nombre = $request->get('nombre');
        $client->correo = $request->get('correo');
        $client->telefono = $request->get('telefono');
        $client->direccion = $request->get('direccion');
        $client->regimen_fiscal = $request->get('regimen_fiscal');
        $client->rfc = $request->get('rfc');
        $client->nombre_empresa = $request->get('nombre_empresa');
        $client->fecha = $fechaActual;
        $client->save();

        if($request->has('uuid')){
            return response()->json(["TMensaje" => "success", "Mensaje" => "Se ha creado el cliente correctamente","Titulo" => "Proceso satisfactorio"]);
        }

        return response()->json(["TMensaje" => "success", "Mensaje" => "Se ha creado el cliente correctamente","Titulo" => "Proceso satisfactorio"]);



    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $client = Client::find($id);

        return view('client.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show_edit(Request $request)
    {
        $subCliente = Subclientes::find($request->id_subcliente);

        return (Auth::User()->id_cliente != 0) 
         ? view('client.cliente_externo', ["subCliente" =>$subCliente])
         : view('client.new_subclient', ["subCliente" =>$subCliente, "idClient" => $request->idClient]);

      //  return view('client.cliente_externo', ["subCliente" =>$subCliente]);
    }

    public function edit(Request $request)
    {
        $cliente = Client::find($request->id_client);

        return view('client.create', compact('cliente'));
    }

    public function edit_subclientes($id)
    {
        $subcliente = Subclientes::find($id);

        return (Auth::User()->id_cliente != 0) 
         ? view('client.subclientes', compact('subcliente'))
         : view('client.new_subclient', compact('subcliente'));
    }


    public function update_subclientes(Request $request, Subclientes $id = null)
    {
        
        if($request->has('id_subcliente')){
            $id = Subclientes::find($request->id_subcliente);
        }

        $id->update($request->all());

        return response()->json(["TMensaje" => "success","Mensaje" => "SubCliente modificado con exito","Titulo" => "Proceso exitoso!"]);

        Session::flash('edit', 'Se ha editado sus datos con exito');
        return redirect()->route('clients.index')
            ->with('success', 'Client updated successfully');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Client $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $client = Client::where('id',$request->idClient);
        $client->update($request->only('nombre','rfc','regimen_fiscal','nombre_empresa','correo','telefono','direccion'));

        return response()->json(["Titulo" => "Datos actualizados", "Mensaje" => "Los datos del cliente han sido actualizados correctamente","TMensaje" => "success"]);
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $client = Client::find($id)->delete();

        Session::flash('delete', 'Se ha eliminado sus datos con exito');
        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully');
    }
}
