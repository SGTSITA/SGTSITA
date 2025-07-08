<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContactoController extends Controller
{
    public function index()
    {
        return view('contactos.index');
    }

    public function create()
    {
        return view('contactos.create');
    }

public function store(Request $request)
{
    $request->validate([
        'nombre' => 'required|string',
        'telefono' => 'required|string',
        'email' => 'nullable|email',
        'empresa' => 'nullable|string',
        'foto' => 'nullable|image|max:2048'
    ]);

    $existeNombre = Contacto::where('nombre', $request->nombre)
        ->whereNull('deleted_at')->exists();

    $existeTelefono = Contacto::where('telefono', $request->telefono)
        ->whereNull('deleted_at')->exists();

    if ($existeNombre || $existeTelefono) {
        $mensaje = [];
        if ($existeNombre) $mensaje[] = 'Ya existe un contacto con ese nombre.';
        if ($existeTelefono) $mensaje[] = 'Ya existe un contacto con ese número.';

        // 👉 respuesta para AJAX
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => implode('<br>', $mensaje)
            ], 422);
        }

        return redirect()->back()
            ->withErrors(['validacion' => implode(' ', $mensaje)])
            ->withInput();
    }

    $data = $request->except('foto');

    if ($request->hasFile('foto')) {
        $file = $request->file('foto');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/contactos'), $filename);
        $data['foto'] = 'uploads/contactos/' . $filename;
    }

    Contacto::create($data);

    if ($request->ajax() || $request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Contacto creado correctamente'
        ]);
    }

    return redirect()->route('contactos.index')->with('success', 'Contacto creado correctamente');
}



    // Listado de contactos incluyendo los eliminados (para AG Grid)
   public function list()
{
    $contactos = Contacto::withTrashed()->get();

    // Convertimos la ruta de la imagen en URL completa
    $contactos->transform(function ($contacto) {
        if ($contacto->foto) {
            $contacto->foto = asset($contacto->foto); // convierte 'uploads/contactos/archivo.jpg' en 'http://.../uploads/contactos/archivo.jpg'
        }
        return $contacto;
    });

    return response()->json($contactos);
}

    // Soft delete (inactivar)
    public function inactivar($id)
    {
        $contacto = Contacto::findOrFail($id);
        $contacto->delete();

        return response()->json(['message' => 'Contacto inactivado'], Response::HTTP_OK);
    }

    // Restaurar contacto (reactivar)
    public function activar($id)
    {
        $contacto = Contacto::withTrashed()->findOrFail($id);

        if ($contacto->trashed()) {
            $contacto->restore();
        }

        return response()->json(['message' => 'Contacto reactivado'], Response::HTTP_OK);
    }
    public function edit($id)
{
    $contacto = Contacto::findOrFail($id);
    return view('contactos.edit', compact('contacto'));
}



public function update(Request $request, $id)
{
    $request->validate([
        'nombre' => 'required|string',
        'telefono' => 'required|string',
        'email' => 'nullable|email',
        'empresa' => 'nullable|string',
        'foto' => 'nullable|image|max:2048'
    ]);

    $mensajeError = null;

    // Validar duplicados por nombre (excluyendo al actual)
    $existeNombre = Contacto::where('nombre', $request->nombre)
        ->where('id', '!=', $id)
        ->whereNull('deleted_at')
        ->exists();

    // Validar duplicados por teléfono (excluyendo al actual)
    $existeTelefono = Contacto::where('telefono', $request->telefono)
        ->where('id', '!=', $id)
        ->whereNull('deleted_at')
        ->exists();

    if ($existeNombre) {
        $mensajeError = 'Ya existe otro contacto con ese nombre.';
    }

    if ($existeTelefono) {
        $mensajeError = $mensajeError
            ? $mensajeError . ' Y también con ese número.'
            : 'Ya existe otro contacto con ese número.';
    }

    if ($mensajeError) {
        return redirect()->back()
            ->withErrors(['validacion' => $mensajeError])
            ->withInput();
    }

    $contacto = Contacto::findOrFail($id);
    $data = $request->except('foto');

    if ($request->hasFile('foto')) {
        if ($contacto->foto && file_exists(public_path($contacto->foto))) {
            unlink(public_path($contacto->foto));
        }

        $file = $request->file('foto');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/contactos'), $filename);
        $data['foto'] = 'uploads/contactos/' . $filename;
    }

    $contacto->update($data);

    return redirect()->route('contactos.index')->with('success', 'Contacto actualizado correctamente');
}


    
}
