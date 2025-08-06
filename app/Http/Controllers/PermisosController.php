<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;
use DB;

class PermisosController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'modulo' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $slug = Str::slug($request->name, '-');

        $permission = new Permission;
        $permission->name = $slug;
        $permission->modulo = $request->modulo;
        $permission->description = $request->description;
        $permission->save();

        return redirect()->route('roles.create')
            ->with('success', 'El permiso fue creado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'modulo' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $permission = Permission::findOrFail($id);
        $permission->name = $request->name;
        $permission->modulo = $request->modulo;
        $permission->description = $request->description;
        $permission->save();

        return redirect()->route('roles.create')
            ->with('edit', 'El permiso fue actualizado correctamente.');
    }

    public function destroy($id)
    {
        Permission::destroy($id);
        return redirect()->route('roles.create')
            ->with('delete', 'El permiso fue eliminado correctamente.');
    }


    public function updateAjax(Request $request, $id)
{
   $request->validate([
    'modulo' => 'nullable|string|max:255',
    'description' => 'nullable|string|max:255',
    'sistema' => 'nullable|string|max:10',
]);

$permission = Permission::findOrFail($id);
$permission->modulo = $request->modulo;
$permission->description = $request->description;
$permission->sistema = $request->sistema;
$permission->save();

    return response()->json(['success' => true, 'message' => 'Permiso actualizado con éxito.']);
}

}
