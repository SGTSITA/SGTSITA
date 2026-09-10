<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\coordenadashistorial;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LimpiarHistoriales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:limpiar-historiales {--dry-run : Muestra los registros que se borrarían sin borrarlos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Respalda en archivos ZIP y depura coordenadas e historiales antiguos.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info("Iniciando depuración y archivado de historiales...");

        // Definir fechas límite: 90 días para coordenadas, 90 días para logs de actividad
        $fechaCoordenadas = Carbon::now()->subDays(90);
        $fechaActivity = Carbon::now()->subDays(90);

        $this->info("Fecha límite para Coordenadas (90 días): " . $fechaCoordenadas->toDateString());
        $this->info("Fecha límite para Activity Logs (90 días): " . $fechaActivity->toDateString());

        // 1. Procesar Coordenadas
        $this->info("--------------------------------------------------");
        $this->info("Procesando coordenadas_historial...");
        $queryCoordenadas = coordenadashistorial::where('registrado_en', '<', $fechaCoordenadas);
        $countCoordenadas = $queryCoordenadas->count();
        $this->info("Coordenadas a depurar: {$countCoordenadas}");

        if ($countCoordenadas > 0) {
            $fileName = 'coordenadas_historial_hasta_' . $fechaCoordenadas->format('Ymd') . '_' . Carbon::now()->format('Ymd_His') . '.csv';
            $filePath = 'historial_backups/' . $fileName;

            if ($dryRun) {
                $this->comment("[DRY RUN] Se exportarían y eliminarían {$countCoordenadas} coordenadas.");
            } else {
                $tempFile = tempnam(sys_get_temp_dir(), 'coords');
                $fp = fopen($tempFile, 'w');
                
                // Cabeceras del CSV
                fputcsv($fp, [
                    'id', 'latitud', 'longitud', 'registrado_en', 'user_id', 
                    'ubicacionable_id', 'ubicacionable_type', 'tipo', 'id_convoy',
                    'status_api', 'id_compania_gps', 'tiempo_respuesta_ms', 'valorSolicitado',
                    'response_json', 'error_message'
                ]);

                // Exportar por bloques para evitar agotar la memoria
                $queryCoordenadas->chunk(2000, function ($rows) use ($fp) {
                    foreach ($rows as $row) {
                        fputcsv($fp, [
                            $row->id, $row->latitud, $row->longitud, $row->registrado_en, $row->user_id,
                            $row->ubicacionable_id, $row->ubicacionable_type, $row->tipo, $row->id_convoy,
                            $row->status_api, $row->id_compania_gps, $row->tiempo_respuesta_ms, $row->valorSolicitado,
                            $row->response_json, $row->error_message
                        ]);
                    }
                });
                fclose($fp);

                // Subir a Storage local
                Storage::put($filePath, fopen($tempFile, 'r'));
                unlink($tempFile);
                $this->info("Exportación temporal a CSV creada: {$filePath}");

                // Comprimir a ZIP
                $zipPath = $this->compressToZip($filePath, $fileName);
                if ($zipPath) {
                    $this->info("Comprimido correctamente a: {$zipPath}");
                    Storage::delete($filePath); // Borrar el CSV original

                    // Eliminar registros de la base de datos
                    $this->info("Eliminando registros de la base de datos...");
                    $queryCoordenadas->delete();
                    $this->info("¡Coordenadas depuradas con éxito!");
                } else {
                    $this->error("Error al comprimir. No se eliminaron los registros de la base de datos.");
                }
            }
        } else {
            $this->info("No hay coordenadas antiguas para depurar.");
        }

        // 2. Procesar Activity Logs
        $this->info("--------------------------------------------------");
        $this->info("Procesando activity_log...");
        $queryActivity = ActivityLog::where('created_at', '<', $fechaActivity);
        $countActivity = $queryActivity->count();
        $this->info("Logs de actividad a depurar: {$countActivity}");

        if ($countActivity > 0) {
            $fileNameAct = 'activity_log_hasta_' . $fechaActivity->format('Ymd') . '_' . Carbon::now()->format('Ymd_His') . '.csv';
            $filePathAct = 'historial_backups/' . $fileNameAct;

            if ($dryRun) {
                $this->comment("[DRY RUN] Se exportarían y eliminarían {$countActivity} logs de actividad.");
            } else {
                $tempFileAct = tempnam(sys_get_temp_dir(), 'actlog');
                $fpAct = fopen($tempFileAct, 'w');

                // Cabeceras del CSV
                fputcsv($fpAct, [
                    'id', 'model', 'model_id', 'action', 'old_values', 'new_values',
                    'user_id', 'ip', 'user_agent', 'request_payload', 'campos_modificados',
                    'referencia', 'empresa_id', 'created_at', 'updated_at'
                ]);

                $queryActivity->chunk(2000, function ($rows) use ($fpAct) {
                    foreach ($rows as $row) {
                        fputcsv($fpAct, [
                            $row->id, $row->model, $row->model_id, $row->action, 
                            is_array($row->old_values) ? json_encode($row->old_values) : $row->old_values, 
                            is_array($row->new_values) ? json_encode($row->new_values) : $row->new_values,
                            $row->user_id, $row->ip, $row->user_agent, $row->request_payload, 
                            $row->campos_modificados, $row->referencia, $row->empresa_id,
                            $row->created_at, $row->updated_at
                        ]);
                    }
                });
                fclose($fpAct);

                Storage::put($filePathAct, fopen($tempFileAct, 'r'));
                unlink($tempFileAct);
                $this->info("Exportación temporal a CSV creada: {$filePathAct}");

                $zipPathAct = $this->compressToZip($filePathAct, $fileNameAct);
                if ($zipPathAct) {
                    $this->info("Comprimido correctamente a: {$zipPathAct}");
                    Storage::delete($filePathAct); // Borrar el CSV original

                    $this->info("Eliminando registros de la base de datos...");
                    $queryActivity->delete();
                    $this->info("¡Logs de actividad depurados con éxito!");
                } else {
                    $this->error("Error al comprimir. No se eliminaron los registros de la base de datos.");
                }
            }
        } else {
            $this->info("No hay logs de actividad antiguos para depurar.");
        }

        $this->info("--------------------------------------------------");
        $this->backupDatabase();
        $this->cleanOldBackups();
        $this->info("Proceso terminado.");
        return 0;
    }

    /**
     * Genera un respaldo completo de la base de datos y lo guarda en el servidor.
     */
    private function backupDatabase()
    {
        $this->info("Generando respaldo automático de la base de datos...");
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
            $zipFileName = "database_backup_{$databaseName}_{$fechaActual}.zip";
            $zipFile = storage_path("app/{$directory}/{$zipFileName}");

            // Ensure directory exists
            if (!file_exists(storage_path("app/{$directory}"))) {
                mkdir(storage_path("app/{$directory}"), 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $zip->addFile($dumpFile, $dumpFileName);
                $zip->close();
                $this->info("Respaldo de base de datos generado correctamente en: {$zipFileName}");
            }
            unlink($dumpFile);
        } else {
            $this->error("No se pudo generar el respaldo automático de la base de datos.");
        }
    }

    /**
     * Limpia los archivos de respaldo ZIP antiguos (más de 30 días) en el servidor.
     */
    private function cleanOldBackups()
    {
        $this->info("Limpiando respaldos ZIP antiguos del servidor (más de 30 días)...");
        $directory = 'historial_backups';
        
        if (Storage::exists($directory)) {
            $files = Storage::files($directory);
            $limite = Carbon::now()->subDays(30)->getTimestamp();
            $deletedCount = 0;

            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                    $lastModified = Storage::lastModified($file);
                    if ($lastModified < $limite) {
                        Storage::delete($file);
                        $deletedCount++;
                    }
                }
            }
            $this->info("Se eliminaron {$deletedCount} archivos de respaldo antiguos del servidor.");
        }
    }

    /**
     * Comprime un archivo a formato ZIP usando ZipArchive.
     */
    private function compressToZip($storageFilePath, $fileNameInZip)
    {
        $zipFileName = str_replace('.csv', '.zip', $fileNameInZip);
        $zipStoragePath = 'historial_backups/' . $zipFileName;

        $zip = new \ZipArchive();
        $tempZipFile = tempnam(sys_get_temp_dir(), 'zip');

        if ($zip->open($tempZipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $csvContent = Storage::get($storageFilePath);
            $zip->addFromString($fileNameInZip, $csvContent);
            $zip->close();

            Storage::put($zipStoragePath, fopen($tempZipFile, 'r'));
            unlink($tempZipFile);
            return $zipStoragePath;
        }

        return null;
    }
}
