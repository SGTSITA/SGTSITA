<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Subclientes;
use Illuminate\Http\Request;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Session;

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
        $this->validate($request, [
            'nombre' => 'required',
            'correo' => 'required',
            'telefono' => 'required'
        ]);

        $fechaActual = date('Y-m-d');

        $client = new Client;
        $client->id_empresa =auth()->user()->id_empresa;

        $client->nombre = $request->get('nombre');
        $client->correo = $request->get('correo');
        $client->telefono = $request->get('telefono');
        $client->direccion = $request->get('direccion');
        $client->regimen_fiscal = $request->get('regimen_fiscal');
        $client->rfc = $request->get('rfc');
        $client->nombre_empresa = $request->get('nombre_empresa');
        $client->fecha = $fechaActual;
        $client->save();

        Session::flash('success', 'Se ha guardado sus datos con exito');
        return redirect()->back()
            ->with('success', 'Cliente created successfully.');

    }

    /**
     * Vista disponible unicamente en Modulo Externo de Clientes
     */
    public function index_subcliente(){
        return view('client.cliente_externo');
    }

    public function subcliente_list(){
        return view('client.subcliente_list');
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

        Session::flash('success', 'Se ha guardado sus datos con exito');
        return redirect()->back()
            ->with('success', 'Cliente created successfully.');

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
        return view('client.cliente_externo', ["subCliente" =>$subCliente]);
    }

    public function edit($id)
    {
        $client = Client::find($id);

        return view('client.edit', compact('client'));
    }

    public function edit_subclientes($id)
    {
        $subcliente = Subclientes::find($id);

        return view('client.subclientes', compact('subcliente'));
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
    public function update(Request $request, Client $id)
    {

        $id->update($request->all());

        Session::flash('edit', 'Se ha editado sus datos con exito');
        return redirect()->route('clients.index')
            ->with('success', 'Client updated successfully');
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
