<?php

// En app/Http/Controllers/DatabaseController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use ZipArchive;

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

        // Construir el nombre del archivo con la fecha, hora y nombre de la base de datos
        $dumpFileName = "{$databaseName}_backup_{$fechaActual}.sql";
        $dumpFile = storage_path("app/{$dumpFileName}");

        // Detectar sistema operativo para definir la ruta de mysqldump
        $mysqldumpPath = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe'
            : '/usr/bin/mysqldump';

        $command = [
            '"' . $mysqldumpPath . '"',
            '--user=' . escapeshellarg($username),
            '--password=' . escapeshellarg($password),
            '--host=' . escapeshellarg($host),
            '--skip-ssl', // Evita errores de certificados autofirmados (ej. Docker local)
            '--single-transaction', // Clave: evita bloqueos de tablas en producción para motor InnoDB
            '--databases', // Incluye la instrucción CREATE DATABASE y USE en el archivo de volcado automáticamente
            escapeshellarg($databaseName),
            '--result-file=' . escapeshellarg($dumpFile),
            '--add-drop-database', // Añadir un DROP DATABASE IF EXISTS antes de la creación
            '--add-drop-table', // Añadir un DROP TABLE IF EXISTS antes de la creación
            '--default-character-set=utf8', // Establecer el conjunto de caracteres a UTF-8
            '--skip-comments', // Omitir los comentarios predeterminados de mysqldump
        ];

        // Ejecutar el comando de volcado redirigiendo errores standard a stdout
        $output = shell_exec(implode(' ', $command) . ' 2>&1');

        // Verificar que el archivo se haya generado y no esté vacío
        if (file_exists($dumpFile) && filesize($dumpFile) > 0) {
            
            // Nombre del archivo ZIP comprimido
            $zipFileName = "{$databaseName}_backup_{$fechaActual}.zip";
            $zipFile = storage_path("app/{$zipFileName}");

            $zip = new ZipArchive();
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                // Comprimir el archivo SQL
                $zip->addFile($dumpFile, $dumpFileName);
                $zip->close();

                // Eliminar el archivo SQL original de inmediato para ahorrar espacio
                unlink($dumpFile);

                // Enviar la descarga del ZIP comprimido y eliminarlo del servidor tras enviarlo
                return response()->download($zipFile)->deleteFileAfterSend(true);
            }

            // Fallback: Si falla la compresión ZIP, descargar el SQL original
            return response()->download($dumpFile)->deleteFileAfterSend(true);
        } else {
            \Illuminate\Support\Facades\Log::error("Error en mysqldump. Comando: " . implode(' ', $command) . " | Mensaje de error: " . $output);
            return back()->with('error', 'No se pudo generar la copia de la base de datos. Verifique los logs de Laravel para más detalles.');
        }
    }
}
