<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Correo;
use App\Models\User;

class ConfigMecController extends Controller
{
  
        public function index()
        {
            $user = Auth::user();
    
            if (!$user) {
                return redirect()->route('login');
            }
    
            $idCliente = $user->id_cliente; // Definimos correctamente el id_cliente
    
            // Obtener los correos asociados al usuario autenticado
            $correos = Correo::where('referencia', $idCliente)->get();
    
            return view('correo.correo', compact('correos', 'user', 'idCliente'));
        }
    
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $idCliente = $user->id_cliente; // Ahora sí, obtenemos correctamente el id_cliente

        if (!$idCliente) {
            return response()->json(['error' => 'El usuario no tiene un cliente asociado'], 400);
        }

        $data = $request->json()->all();

        foreach ($data as $row) {
            if (empty($row[1])) continue; // Si no hay correo, omitimos la fila

            $validatedData = [
                'correo' => $row[1],
                'tipo_correo' => 'MEC', // Siempre MEC
                'referencia' => $idCliente, // Se guarda el ID del cliente autenticado
                'cotizacion_nueva' => $row[3] ?? false,
                'cancelacion_viaje' => $row[4] ?? false,
                'nuevo_documento' => $row[5] ?? false,
                'viaje_modificado' => $row[6] ?? false,
            ];

            if (!empty($row[0])) {
                Correo::where('id', $row[0])->update($validatedData);
            } else {
                Correo::create($validatedData);
            }
        }

        return response()->json(['message' => 'Configuración guardada exitosamente']);
    }
}