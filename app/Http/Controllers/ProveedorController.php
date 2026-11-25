<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProveedorCuentaRequest;
use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use App\Models\Proveedor;
use App\Services\ProveedorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    protected ProveedorService $proveedorService;

    public function __construct(ProveedorService $proveedorService)
    {
        $this->proveedorService = $proveedorService;
    }

    public function index()
    {
        $tipoViaje = [
            'foraneo' => 'Foráneo',
            'local' => 'Local',
            'local_foraneo' => 'Ambos'
        ];
        $user = auth()->user();

        $proveedores = $this->proveedorService->getProveedoresCatalogoPrincipal($user);
        $empresas = $this->proveedorService->getEmpresasDisponibles($user);

        if (request()->ajax()) {
            return response()->json([
                'proveedores' => $proveedores,
                'empresas' => $empresas,
                'tipoViaje' => $tipoViaje
            ]);
        }

        return view('proveedores.index', compact('proveedores', 'empresas', 'tipoViaje'));
    }

    public function store(StoreProveedorRequest $request): JsonResponse
    {
        try {
            $this->proveedorService->storeProveedor($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Proveedor creado con éxito.'
            ]);
        } catch (\Throwable $e) {
            Log::channel('daily')->error('Error al crear proveedor: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear proveedor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function validarRFC(Request $request): JsonResponse
    {
        $rfc = $request->query('rfc');
        $exists = $this->proveedorService->existeRfc($rfc);

        return response()->json(['exists' => $exists]);
    }

    public function edit($id): JsonResponse
    {
        $proveedor = Proveedor::find($id);

        $tipoViaje = [
            'local' => 'Local',
            'foraneo' => 'Foráneo',
            'local_foraneo' => 'Ambos'
        ];

        if (!$proveedor) {
            return response()->json(['success' => false, 'message' => 'Proveedor no encontrado.'], 404);
        }

        return response()->json([
            'success' => true,
            'proveedor' => $proveedor,
            'tipoViaje' => $tipoViaje
        ]);
    }

    public function update(UpdateProveedorRequest $request, $id): JsonResponse
    {
        try {
            $this->proveedorService->updateProveedor((int) $id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Proveedor actualizado con éxito.'
            ]);
        } catch (\Exception $e) {
            Log::channel('daily')->error('Error al actualizar proveedor: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cuenta(StoreProveedorCuentaRequest $request): JsonResponse
    {
        $this->proveedorService->storeCuentaBancaria($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cuenta bancaria creada exitosamente'
        ]);
    }

    public function definirCuentaPrioridad(Request $request, $id): JsonResponse
    {
        $tipo = (int) $request->tipo;
        $checked = (bool) $request->estado;

        $this->proveedorService->definirCuentaPrioridad((int) $id, $tipo, $checked);

        return response()->json([
            'success' => true,
            'message' => "Cuenta marcada como Cuenta {$tipo}."
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $this->proveedorService->deleteCuentaBancaria((int) $id);

        return response()->json([
            'success' => true,
            'message' => 'Cuenta bancaria eliminada correctamente.'
        ]);
    }

    public function restore($id): JsonResponse
    {
        $this->proveedorService->restoreCuentaBancaria((int) $id);

        return response()->json([
            'success' => true,
            'message' => 'Cuenta bancaria restaurada correctamente.'
        ]);
    }

    public function getProveedoresList(): JsonResponse
    {
        $proveedores = $this->proveedorService->getProveedoresList(auth()->user());

        return response()->json(['list' => $proveedores]);
    }

    public function getCuentasBancarias($id): JsonResponse
    {
        $data = $this->proveedorService->getCuentasBancariasConProveedor((int) $id);

        return response()->json([
            'success' => true,
            'proveedor' => $data['proveedor'],
            'cuentas' => $data['cuentas']
        ]);
    }

    public function cambiarEstadoCuenta(Request $request, $id): JsonResponse
    {
        $estado = (bool) $request->input('activo');
        $cuenta = $this->proveedorService->cambiarEstadoCuentaBancaria((int) $id, $estado);

        $mensaje = $estado
            ? "Cuenta bancaria activada correctamente."
            : "Cuenta bancaria desactivada correctamente.";

        return response()->json([
            'success' => true,
            'message' => $mensaje,
            'nuevo_estado' => $estado,
            'deleted_at' => $cuenta->deleted_at
        ]);
    }

    public function validarCLABE(Request $request): JsonResponse
    {
        return response()->json([
            'exists' => false
        ]);
    }
}
