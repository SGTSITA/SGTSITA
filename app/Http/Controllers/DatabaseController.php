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

        $fechaActual = date('Ymd_His');

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
            $directory = 'historial_backups';
            if (!\Storage::exists($directory)) {
                \Storage::makeDirectory($directory);
            }

            $zipFileName = "database_backup_{$databaseName}_{$fechaActual}.zip";
            $zipFile = storage_path("app/{$directory}/{$zipFileName}");

            $zip = new ZipArchive();
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $zip->addFile($dumpFile, $dumpFileName);
                $zip->close();
                unlink($dumpFile);

                return redirect()->route('backups.historiales')->with('success', 'Respaldo completo de la Base de Datos generado correctamente en el servidor.');
            }

            return redirect()->route('backups.historiales')->with('error', 'No se pudo empaquetar el respaldo de la base de datos.');
        } else {
            \Illuminate\Support\Facades\Log::error("Error en mysqldump. Comando: " . implode(' ', $command) . " | Mensaje de error: " . $output);
            return back()->with('error', 'No se pudo generar la copia de la base de datos. Verifique los logs de Laravel para más detalles.');
        }
    }

    public function listarBackupsHistoriales()
    {
        $directory = 'historial_backups';
        $files = \Storage::exists($directory) ? \Storage::files($directory) : [];
        
        $backups = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $backups[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => \App\Traits\CommonTrait::calculateFileSize(\Storage::size($file)) ?? (\Storage::size($file) . ' B'),
                    'date' => date('d/m/Y H:i:s', \Storage::lastModified($file)),
                ];
            }
        }

        // Sort backups by date descending
        usort($backups, function ($a, $b) {
            return \Storage::lastModified($b['path']) <=> \Storage::lastModified($a['path']);
        });

        return view('configuracion.backups_historiales', compact('backups'));
    }

    public function descargarBackupHistorial($file)
    {
        $path = 'historial_backups/' . $file;
        if (!\Storage::exists($path)) {
            abort(404, 'El archivo de respaldo no existe.');
        }

        return \Storage::download($path);
    }

    public function ejecutarLimpiezaAhora()
    {
        try {
            \Artisan::call('db:limpiar-historiales');
            return back()->with('success', 'Limpieza y archivado ejecutados correctamente. Nuevos respaldos generados.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error ejecutando limpieza: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al ejecutar la limpieza: ' . $e->getMessage());
        }
    }
}
