<?php

namespace App\Http\Controllers;

use App\Models\CuentasBancarias;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Session;

class ProveedorController extends Controller
{
    // Listar proveedores en vista o como JSON (para AJAX)
    public function index()
    {
        $proveedores = Proveedor::where('id_empresa', auth()->user()->id_empresa)
            ->orderBy('created_at', 'desc')
            ->get();

        if (request()->ajax()) {
            return response()->json($proveedores);
        }

        return view('proveedores.index', compact('proveedores'));
    }

    // Crear un nuevo proveedor
    public function store(Request $request)
{
    $request->validate([
        'nombre' => 'required|string|max:255',
        'correo' => 'required|email|max:255',
        'telefono' => 'required|string|max:20',
        'direccion' => 'nullable|string|max:255',
        'regimen_fiscal' => 'nullable|string|max:255',
        'rfc' => 'nullable|string|max:13|unique:proveedores,rfc,NULL,id,id_empresa,' . auth()->user()->id_empresa,
        'tipo' => 'required|string|max:255',
    ]);

    Proveedor::create([
        'nombre' => $request->nombre,
        'correo' => $request->correo,
        'telefono' => $request->telefono,
        'regimen_fiscal' => $request->regimen_fiscal,
        'direccion' => $request->direccion,
        'rfc' => $request->rfc,
        'tipo' => $request->tipo,
        'fecha' => now()->format('Y-m-d'),
        'id_empresa' => auth()->user()->id_empresa,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Proveedor creado con éxito.'
    ]);
}
public function validarRFC(Request $request)
{
    $rfc = $request->query('rfc');

    // 🔹 Buscar solo en proveedores activos (sin SoftDeletes)
    $exists = Proveedor::where('rfc', $rfc)->exists();

    return response()->json(['exists' => $exists]);
}



    // Obtener datos de un proveedor para editar
    public function edit($id)
    {
        $proveedor = Proveedor::find($id);
    
        if (!$proveedor) {
            return response()->json(['success' => false, 'message' => 'Proveedor no encontrado.'], 404);
        }
    
        return response()->json([
            'success' => true,
            'proveedor' => $proveedor
        ]);
    }
    

    // Actualizar proveedor (corrección del error)
    public function update(Request $request, $id)
{
    $proveedor = Proveedor::find($id);

    if (!$proveedor) {
        return response()->json(['success' => false, 'message' => 'Proveedor no encontrado.'], 404);
    }

    $request->validate([
        'nombre' => 'required|string|max:255',
        'correo' => 'required|email|max:255',
        'telefono' => 'required|string|max:20',
        'direccion' => 'nullable|string|max:255',
        'regimen_fiscal' => 'nullable|string|max:255',
        'rfc' => 'nullable|string|max:13',
        'tipo' => 'required|string|max:255',
    ]);

    $proveedor->update($request->all());

    return response()->json([
        'success' => true,
        'message' => 'Proveedor actualizado con éxito.'
    ]);
}

    
    

    // Agregar una cuenta bancaria a un proveedor
    public function cuenta(Request $request)
{
    $request->validate([
        'nombre_beneficiario' => 'required|string|max:255',
        'id_proveedores' => 'required|exists:proveedores,id',
        'cuenta_bancaria' => 'required|string|max:20',
        'nombre_banco' => 'required|string|max:255',
        'cuenta_clabe' => 'required|string|max:18',
    ]);

    CuentasBancarias::create([
        'nombre_beneficiario' => $request->nombre_beneficiario,
        'id_proveedores' => $request->id_proveedores,
        'cuenta_bancaria' => $request->cuenta_bancaria,
        'nombre_banco' => $request->nombre_banco,
        'cuenta_clabe' => $request->cuenta_clabe,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Cuenta bancaria creada exitosamente'
    ]);
}
public function definirCuentaPrioridad(Request $request, $id)
{
    $cuenta = CuentasBancarias::findOrFail($id);
    $tipo = $request->tipo; // 1 o 2
    $checked = $request->estado;

    // 🔹 Limpiar cuenta_1 o cuenta_2 a todas las cuentas del mismo proveedor
    CuentasBancarias::where('id_proveedores', $cuenta->id_proveedores)->update([
        "cuenta_{$tipo}" => false
    ]);

    // 🔹 Asignar cuenta actual
    $cuenta->update(["cuenta_{$tipo}" => $checked]);

    return response()->json([
        'success' => true,
        'message' => "Cuenta marcada como Cuenta {$tipo}."
    ]);
}



    // Eliminar una cuenta bancaria
    public function destroy($id)
    {
        $cuenta = CuentasBancarias::findOrFail($id);
        $cuenta->delete(); // SoftDelete en lugar de eliminar
    
        return response()->json([
            'success' => true,
            'message' => 'Cuenta bancaria eliminada correctamente.'
        ]);
    }
    public function restore($id)
{
    $cuenta = CuentasBancarias::onlyTrashed()->findOrFail($id);
    $cuenta->restore();

    return response()->json([
        'success' => true,
        'message' => 'Cuenta bancaria restaurada correctamente.'
    ]);
}


    // Obtener la lista de proveedores para AG Grid (uso en frontend)
    public function getProveedoresList()
    {
        $proveedores = Proveedor::where('id_empresa', auth()->user()->id_empresa)
            ->orderBy('created_at', 'desc')
            ->get();
    
        return response()->json(['list' => $proveedores]);
    }
    public function getCuentasBancarias($id)
{
    // 🔹 Obtener el proveedor
    $proveedor = Proveedor::find($id);

    // 🔹 Obtener las cuentas bancarias (incluyendo las eliminadas)
    $cuentas = CuentasBancarias::withTrashed()
        ->where('id_proveedores', $id)
        ->get();

    return response()->json([
        'success' => true,
        'proveedor' => $proveedor, // 🔹 Ahora enviamos el proveedor
        'cuentas' => $cuentas
    ]);
}

    
    public function cambiarEstadoCuenta(Request $request, $id)
    {
        $cuenta = CuentasBancarias::withTrashed()->findOrFail($id);
        $estado = $request->input('activo');
    
        if ($estado) {
            // 🔹 Activar la cuenta bancaria (restaurar SoftDelete)
            $cuenta->restore();
            $mensaje = "Cuenta bancaria activada correctamente.";
        } else {
            // 🔹 Desactivar la cuenta bancaria con SoftDelete
            $cuenta->delete();
            $mensaje = "Cuenta bancaria desactivada correctamente.";
        }
    
        return response()->json([
            'success' => true,
            'message' => $mensaje,
            'nuevo_estado' => $estado,
            'deleted_at' => $cuenta->deleted_at
        ]);
    }
    public function validarCLABE(Request $request)
{
    $clabeExiste = CuentasBancarias::where('cuenta_clabe', $request->cuenta_clabe)->exists();

    return response()->json([
        'exists' => false
    ]);
}


    
}
