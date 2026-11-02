<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\StoreSubclienteRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\Empresas;
use App\Models\Subclientes;
use App\Services\ClienteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class ClientController extends Controller
{
    protected ClienteService $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    public function index(): View
    {
        $clients = $this->clienteService->getClientsByEmpresa();
        $subclientes = $this->clienteService->getSubclientesByEmpresa();

        return view('client.index', compact('clients', 'subclientes'));
    }

    public function create(): View
    {
        $client = new Client();
        $empresas = Empresas::all();

        return view('client.create', compact('client', 'empresas'));
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        try {
            $result = $this->clienteService->storeClient($request->validated());
            $welcomePassword = $result['password'];

            return response()->json([
                "TMensaje" => "success",
                "Titulo" => "Cliente creado correctamente",
                "Mensaje" => "El cliente se ha creado con exito. Contraseña de acceso generada y enviada correctamente: $welcomePassword"
            ]);
        } catch (\Throwable $t) {
            Log::channel('daily')->error('Error al crear cliente: ' . $t->getMessage());

            return response()->json([
                "TMensaje" => "error",
                "Titulo" => "Ocurrio un error",
                "Mensaje" => $t->getMessage()
            ]);
        }
    }

    public function get_list(): JsonResponse
    {
        $list = $this->clienteService->getClientesListForEmpresa();

        return response()->json(["list" => $list, "TMensaje" => "success"]);
    }

    public function index_subcliente(): View
    {
        return view('client.cliente_externo');
    }

    public function new_subcliente(Request $request): View
    {
        return view('client.new_subclient', ["idClient" => $request->idClient]);
    }

    public function subcliente_list(): View
    {
        return view('client.subcliente_list');
    }

    public function subcliente_list_internal(Request $request): View
    {
        $client = Client::where('id', $request->id_client)->first();

        return view('client.subcliente_list_internal', ["id_client" => $request->id_client, "client" => $client]);
    }

    public function subcliente_get_list(Request $request): JsonResponse
    {
        $list = $this->clienteService->getSubclientesByCliente((int)$request->_cliente);

        return response()->json(["TMensaje" => "success", "data" => $list]);
    }

    public function store_subclientes(StoreSubclienteRequest $request): JsonResponse
    {
        $this->clienteService->storeSubcliente($request->validated());

        return response()->json([
            "TMensaje" => "success",
            "Mensaje" => "Se ha creado el cliente correctamente",
            "Titulo" => "Proceso satisfactorio"
        ]);
    }

    public function show($id): View
    {
        $client = Client::findOrFail($id);

        return view('client.show', compact('client'));
    }

    public function show_edit(Request $request): View
    {
        $subCliente = Subclientes::find($request->id_subcliente);

        return (Auth::user()->id_cliente != 0)
            ? view('client.cliente_externo', ["subCliente" => $subCliente])
            : view('client.new_subclient', ["subCliente" => $subCliente, "idClient" => $request->idClient]);
    }

    public function edit(Request $request)
    {
        $cliente = Client::findOrFail($request->id_client);

        if ($request->has('toggle_empresa')) {
            $idEmpresa = (int) $request->toggle_empresa;
            $checked = (bool) $request->checked;

            $this->clienteService->toggleEmpresaVinculacion($cliente->id, $idEmpresa, $checked);

            return response()->json(['success' => true]);
        }

        $empresas = Empresas::all();
        $vinculadas = $this->clienteService->getEmpresasVinculadas($cliente->id);

        return view('client.create', compact('cliente', 'empresas', 'vinculadas'));
    }

    public function edit_subclientes($id): View
    {
        $subcliente = Subclientes::find($id);

        return (Auth::user()->id_cliente != 0)
            ? view('client.subclientes', compact('subcliente'))
            : view('client.new_subclient', compact('subcliente'));
    }

    public function update_subclientes(Request $request, ?Subclientes $id = null): JsonResponse
    {
        $subclienteId = $request->get('id_subcliente', $id?->id);

        if ($subclienteId) {
            $this->clienteService->updateSubcliente((int) $subclienteId, $request->except(['_token', 'id_subcliente']));
        }

        return response()->json([
            "TMensaje" => "success",
            "Mensaje" => "SubCliente modificado con exito",
            "Titulo" => "Proceso exitoso!"
        ]);
    }

    public function update(UpdateClientRequest $request): JsonResponse
    {
        $this->clienteService->updateClient((int) $request->idClient, $request->validated());

        return response()->json([
            "Titulo" => "Datos actualizados",
            "Mensaje" => "Los datos del cliente han sido actualizados correctamente",
            "TMensaje" => "success"
        ]);
    }

    public function destroy($id): RedirectResponse
    {
        $this->clienteService->deleteClient((int) $id);

        Session::flash('delete', 'Se ha eliminado sus datos con exito');

        return redirect()->route('clientes.index')
            ->with('success', 'Client deleted successfully');
    }
}
