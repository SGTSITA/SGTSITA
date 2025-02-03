<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Correo;

class CorreoController extends Controller
{
    // Muestra la vista principal con el cliente si aplica
    public function index()
    {
        // Obtén los datos desde la base de datos con LEFT JOIN
        $correos = Correo::leftJoin('clients', 'correos.referencia', '=', 'clients.id')
            ->select(
                'correos.*', 
                'clients.nombre as cliente' // Alias para el nombre del cliente
            )
            ->get();

        // Pasar los datos a la vista
        return view('correo.index', compact('correos'));
    }

    public function getData()
    {
        // Obtener los datos de la base de datos con el nombre del cliente si aplica
        $correos = Correo::leftJoin('clients', 'correos.referencia', '=', 'clients.id')
            ->select(
                'correos.*', 
                'clients.nombre as cliente' 
            )
            ->get();

        return response()->json($correos);
    }

    public function update(Request $request)
    {
        $data = json_decode($request->getContent(), true); // Decodifica el JSON

        foreach ($data as $row) {
            // Validar datos de cada fila
            $validatedData = [
                'correo' => $row[1] ?? null,
                'tipo_correo' => $row[2] ?? null,
                'referencia' => $row[3] ?? null,
                'cotizacion_nueva' => isset($row[5]) ? (bool)$row[5] : false,
                'cancelacion_viaje' => isset($row[6]) ? (bool)$row[6] : false,
                'nuevo_documento' => isset($row[7]) ? (bool)$row[7] : false,
                'viaje_modificado' => isset($row[8]) ? (bool)$row[8] : false,
            ];

            if (!empty($row[0])) {
                // Si el ID existe, actualizar
                Correo::where('id', $row[0])->update($validatedData);
            } else {
                // Si no hay ID, crear nuevo registro
                Correo::create($validatedData);
            }
        }

        return response()->json(['message' => 'Correos actualizados correctamente']);
    }
}
