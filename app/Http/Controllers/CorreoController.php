<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Correo;

class CorreoController extends Controller
{
    // Muestra la vista principal
    public function index()
    {
        // Obtén los datos desde la base de datos
        $correos = Correo::all();
    
        // Pasa los datos a la vista
        return view('correo.index', compact('correos'));
    }

    public function getData()
{
    // Obtener los datos de la base de datos
    $correos = Correo::all();
    return response()->json($correos);
}

public function update(Request $request)
{
    $data = $request->all();

    foreach ($data as $row) {
        // Valida los datos de cada fila
        $validatedData = [
            'id' => $row[0] ?? null,
            'correo' => $row[1] ?? null,
            'tipo_correo' => $row[2] ?? null,
            'referencia' => $row[3] ?? null,
            'notificacion_nueva' => isset($row[4]) ? (bool)$row[4] : false,
            'cancelacion_viaje' => isset($row[5]) ? (bool)$row[5] : false,
            'nuevo_documento' => isset($row[6]) ? (bool)$row[6] : false,
            'viaje_modificado' => isset($row[7]) ? (bool)$row[7] : false,
        ];

        // Si existe ID, actualiza; si no, crea un nuevo registro
        Correo::updateOrCreate(
            ['id' => $validatedData['id']], // Busca por ID
            $validatedData // Actualiza o crea con los valores proporcionados
        );
    }

    return response()->json(['message' => 'Cambios guardados exitosamente']);
}
}