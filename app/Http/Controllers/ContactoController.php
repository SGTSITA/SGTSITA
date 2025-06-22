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
        'foto' => 'nullable|image|max:2048' // valida imagen
    ]);

    $data = $request->except('foto');

    if ($request->hasFile('foto')) {
        $file = $request->file('foto');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/contactos'), $filename);
        $data['foto'] = 'uploads/contactos/' . $filename;
    }

    Contacto::create($data);

    return redirect()->route('contactos.index')->with('success', 'Contacto creado');
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



    
}
