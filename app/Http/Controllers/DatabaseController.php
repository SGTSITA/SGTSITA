<?php

// En app/Http/Controllers/DatabaseController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseController extends Controller
{
    public function descargarBaseDeDatos()
    {
        // Datos de la base de datos
        $databaseName = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST');

        // Obtener la fecha y hora actual
        $fechaActual = date('Ymd_His'); // Formato: AñoMesDía_HoraMinutoSegundo
        $databaseName = env('DB_DATABASE'); // Obtener el nombre de la base de datos desde el archivo .env

        // Construir el nombre del archivo con la fecha, hora y nombre de la base de datos
        $dumpFileName = "{$databaseName}_backup_{$fechaActual}.sql";
        $dumpFile = storage_path("app/{$dumpFileName}");

        // $mysqldumpPath = 'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe';
        $mysqldumpPath = '/bin/mysqldump';


        $command = [
            $mysqldumpPath,
            '--user=' . $username,
            '--password=' . $password,
            '--host=' . $host,
            '--databases', // Incluye la instrucción CREATE DATABASE en el archivo de volcado
            $databaseName,
            '--result-file=' . $dumpFile,
            '--add-drop-database', // Añadir un DROP DATABASE IF EXISTS antes de la creación
            '--add-drop-table', // Añadir un DROP TABLE IF EXISTS antes de la creación
            '--default-character-set=utf8', // Establecer el conjunto de caracteres a UTF-8
            '--skip-comments', // Omitir los comentarios predeterminados de mysqldump
        ];


        $respuesta = shell_exec(implode(' ', $command));

        if ($respuesta === null) { // Si el comando se ejecuta correctamente
            // Añadir el comando USE para seleccionar la base de datos al inicio del archivo
            $contenido = file_get_contents($dumpFile);

            // Insertar el comando USE después del CREATE DATABASE
            $nuevoContenido = preg_replace(
                "/(CREATE DATABASE IF NOT EXISTS `{$databaseName}`.*?;)/s",
                "$1\nUSE `{$databaseName}`;\n",
                $contenido
            );

            // Sobrescribir el archivo con el nuevo contenido
            file_put_contents($dumpFile, $nuevoContenido);

            return response()->download($dumpFile)->deleteFileAfterSend(true);
        } else {
            return back()->with('error', 'No se pudo generar la copia de la base de datos.');
        }
    }
}
