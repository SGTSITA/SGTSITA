<?php

namespace App\Http\Controllers;

use App\Models\CuentasBancarias;
use App\Models\Proveedor;
use App\Models\Empresas;
use App\Models\Configuracion;
use App\Models\user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class ProveedorController extends Controller
{
    // Listar proveedores en vista o como JSON (para AJAX)
    public function index()
    {

        $user = auth()->user();

        if ($user->es_admin) {
        
            $proveedores = Proveedor::orderBy('created_at', 'desc')->get();
            $empresas = Empresas::orderBy('nombre')->get();
        } else {
            
            $proveedores = Proveedor::where('id_empresa', $user->id_empresa)
                ->orderBy('created_at', 'desc')
                ->get();
            $empresas = Empresas::where('id', $user->id_empresa)->orderBy('nombre')->get();
        }

        if (request()->ajax()) {
                      return response()->json([
        'proveedores' => $proveedores,
        'empresas' => $empresas
    ]);
        }

        return view('proveedores.index', compact('proveedores', 'empresas'));
    }

    // Crear un nuevo proveedor
    public function store(Request $request)
{
    // Validaciones base del proveedor
    $request->validate([
        'nombre' => 'required|string|max:255',
        'correo' => 'required|email|max:255',
        'telefono' => 'required|string|max:20',
        'direccion' => 'nullable|string|max:255',
        'regimen_fiscal' => 'nullable|string|max:255',
        'rfc' => 'nullable|string|max:13',
        'tipo' => 'required|string|max:255',
        'tipo_empresa' => 'required|string|in:lista,mep',
    ]);

    DB::beginTransaction();
    try {
        // ===============================
        //  CASO 1: Asignar empresa existente
        // ===============================
        if ($request->tipo_empresa === 'lista') {
            $idEmpresa = $request->id_empresa;
            if (!$idEmpresa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar una empresa existente.'
                ], 422);
            }

            $proveedor = Proveedor::create([
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'regimen_fiscal' => $request->regimen_fiscal,
                'rfc' => $request->rfc,
                'tipo' => $request->tipo,
                'fecha' => now()->format('Y-m-d'),
                'id_empresa' => $idEmpresa,
            ]);
        }

        // ===============================
        //  CASO 2: Crear nueva empresa (MEP)
        // ===============================
        if ($request->tipo_empresa === 'mep') {
            $request->validate([
                'password' => 'required|min:6',
            ]);

             //asignar configuracion por default

             $config= Configuracion::create([
                'nombre_sistema' => strtoupper($request->nombre),
                'color_principal' => '#000000',
                'color_iconos_sidebar' => '#000000',
                'color_iconos_cards' => '#000000', 
                'color_boton_add' => '#000000',
                'icon_boton_add' => '#000000',
                'color_boton_save' => '#000000',
                'icon_boton_save' => '#000000',
                'color_boton_close' => '#000000',
                'icon_boton_close' => '#000000',
            ]);


            $empresa = Empresas::create([
                'nombre' => strtoupper($request->nombre),
                'rfc' => $request->rfc,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'id_tipo_empresa' =>2, // proveedor
                'id_configuracion' => $config->id,
            ]);

          

            Proveedor::$forceEmpresaFromAuth = false;
        
            $proveedor = Proveedor::create([
                'nombre' => $request->nombre,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'regimen_fiscal' => $request->regimen_fiscal,
                'rfc' => $request->rfc,
                'tipo' => $request->tipo,
                'fecha' => now()->format('Y-m-d'),
                'id_empresa' => $empresa->id,
            ]);

           Proveedor::$forceEmpresaFromAuth = true;

            User::create([
                'name' => $request->nombre,
                'email' => $request->correo,
                'password' => bcrypt($request->password),
                'id_empresa' => $empresa->id,
                'es_admin' => false, 
                'id_cliente' => 0,
            ]);

            //asignar rol de proveedor
            $user = User::where('email', $request->correo)->first();
            $user->assignRole('Proveedor Directo');
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Proveedor creado con éxito.'
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al crear proveedor: ' . $e->getMessage(),
        ], 500);
    }
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
        $user = auth()->user();

        if ($user->es_admin) {
   
        $proveedores = Proveedor::with('empresa')
            ->orderBy('created_at', 'desc')
            ->get();
         } else {
        $proveedores = Proveedor::with('empresa')
            ->where('id_empresa', $user->id_empresa)
            ->orderBy('created_at', 'desc')
            ->get();
        }
    
        return response()->json(['list' => $proveedores]);
    }
    public function getCuentasBancarias($id)
    {
   
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
