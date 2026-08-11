<?php

// En app/Http/Controllers/DatabaseController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use ZipArchive;

class DatabaseController extends Controller
{
    public function descargarBaseDeDatos()
    {

        $databaseName = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST');


        $fechaActual = date('Ymd_His'); // Formato: AñoMesDía_HoraMinutoSegundo


        $dumpFileName = "{$databaseName}_backup_{$fechaActual}.sql";
        $dumpFile = storage_path("app/{$dumpFileName}");


        $mysqldumpPath = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe'
            : '/usr/bin/mysqldump';

        $command = [
            '"' . $mysqldumpPath . '"',
            '--user=' . escapeshellarg($username),
            '--password=' . escapeshellarg($password),
            '--host=' . escapeshellarg($host),
            '--single-transaction',
            '--databases',
            escapeshellarg($databaseName),
            '--result-file=' . escapeshellarg($dumpFile),
            '--add-drop-database',
            '--add-drop-table',
            '--default-character-set=utf8',
            '--skip-comments',
        ];


        $output = shell_exec(implode(' ', $command) . ' 2>&1');


        if (file_exists($dumpFile) && filesize($dumpFile) > 0) {


            $zipFileName = "{$databaseName}_backup_{$fechaActual}.zip";
            $zipFile = storage_path("app/{$zipFileName}");

            $zip = new ZipArchive();
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {

                $zip->addFile($dumpFile, $dumpFileName);
                $zip->close();


                unlink($dumpFile);


                return response()->download($zipFile)->deleteFileAfterSend(true);
            }


            return response()->download($dumpFile)->deleteFileAfterSend(true);
        } else {
            \Illuminate\Support\Facades\Log::error("Error en mysqldump. Comando: " . implode(' ', $command) . " | Mensaje de error: " . $output);
            return back()->with('error', 'No se pudo generar la copia de la base de datos. Verifique los logs de Laravel para más detalles.');
        }
    }
}
