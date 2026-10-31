<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerarRespaldoSistemaCompletoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:sistema-completo
                            {--eliminar : Elimina respaldos completos del servidor}
                            {--archivo= : Nombre de archivo específico a eliminar}
                            {--listar : Lista los respaldos completos existentes}
                            {--sin-bd : Omite el volcado de la base de datos MySQL}
                            {--sin-gps : Excluye la tabla de coordenadas GPS para aligerar la base de datos}
                            {--force : Omite preguntas de confirmación al eliminar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un respaldo integral (código, documentos físicos y BD) excluyendo .env y vendor, con opción para eliminarlo.';

    /**
     * Directorio relativo en storage/app para almacenar los respaldos completos.
     */
    protected $backupDir = 'backups_completos';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $backupPath = storage_path("app/{$this->backupDir}");

        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        // 1. Si se solicita solo listar
        if ($this->option('listar')) {
            return $this->listarRespaldos($backupPath);
        }

        // 2. Si se solicita eliminar
        if ($this->option('eliminar')) {
            return $this->eliminarRespaldos($backupPath);
        }

        // 3. Flujo normal: Generar nuevo respaldo
        return $this->generarRespaldo($backupPath);
    }

    /**
     * Genera el respaldo comprimido (.tar.gz)
     */
    protected function generarRespaldo(string $backupPath): int
    {
        $fechaActual = date('Ymd_His');
        $tarFileName = "respaldo_sgtsita_completo_{$fechaActual}.tar.gz";
        $tarFilePath = "{$backupPath}/{$tarFileName}";
        $sqlTempName = "dump_sgtsita_temp_{$fechaActual}.sql";
        $sqlTempPath = base_path($sqlTempName);

        $this->info("==================================================================");
        $this->info("   INICIANDO RESPALDO INTEGRAL DE SGTSITA (DOCUMENTOS + CÓDIGO)");
        $this->info("==================================================================");
        $this->line("Directorio destino: storage/app/{$this->backupDir}/{$tarFileName}");

        $conBD = !$this->option('sin-bd');

        // Paso 1: Dump de Base de Datos
        if ($conBD) {
            $this->newLine();
            $this->comment("Paso 1/3: Generando volcado de base de datos MySQL...");

            $dbStatus = $this->generarDumpBD($sqlTempPath);
            if (!$dbStatus) {
                $this->error("Error crítico: Falló la generación del volcado SQL.");
                if (File::exists($sqlTempPath)) {
                    @unlink($sqlTempPath);
                }
                return 1;
            }

            $sqlSize = $this->formatearBytes(filesize($sqlTempPath));
            $this->info("✓ Volcado SQL generado correctamente ({$sqlSize}).");
        } else {
            $this->newLine();
            $this->comment("Paso 1/3: Omitiendo volcado de base de datos (flag --sin-bd activo).");
        }

        // Paso 2: Empaquetar con GNU tar y comprimir con gzip
        $this->newLine();
        $this->comment("Paso 2/3: Empaquetando código, documentos físicos y BD...");
        $this->line("   - Se incluyen: public/cotizaciones, public/uploads, public/equipos, app, routes, etc.");
        $this->line("   - Se excluyen estrictamente: .env, vendor/, node_modules/, .git/ y cachés.");

        $tarStatus = $this->ejecutarTarGz($tarFilePath);

        // Limpiar el dump SQL temporal
        if (File::exists($sqlTempPath)) {
            @unlink($sqlTempPath);
        }

        if (!$tarStatus || !File::exists($tarFilePath) || filesize($tarFilePath) === 0) {
            $this->error("Error crítico: No se pudo generar el archivo comprimido.");
            return 1;
        }

        // Paso 3: Resumen final
        $finalSize = $this->formatearBytes(filesize($tarFilePath));
        $this->newLine();
        $this->info("==================================================================");
        $this->info("✓ ¡RESPALDO INTEGRAL GENERADO CON ÉXITO!");
        $this->info("==================================================================");
        $this->table(
            ['Propiedad', 'Detalle'],
            [
                ['Archivo', $tarFileName],
                ['Tamaño comprimido', $finalSize],
                ['Ubicación en host', "storage/app/{$this->backupDir}/{$tarFileName}"],
                ['Fecha de creación', date('Y-m-d H:i:s')],
            ]
        );

        $this->newLine();
        $this->comment("Instrucciones de resguardo y limpieza:");
        $this->line("1. Copia o mueve este archivo a tu memoria USB o disco duro externo.");
        $this->line("2. Cuando ya lo tengas a salvo y quieras borrarlo del servidor para no ocupar espacio, ejecuta:");
        $this->info("   docker compose exec app php artisan backup:sistema-completo --eliminar");
        $this->line("   o para un archivo específico:");
        $this->info("   docker compose exec app php artisan backup:sistema-completo --eliminar --archivo={$tarFileName}");
        $this->newLine();

        return 0;
    }

    /**
     * Ejecuta mysqldump para exportar la base de datos replicando la configuración de DatabaseController
     */
    protected function generarDumpBD(string $sqlFilePath): bool
    {
        $database = env('DB_DATABASE') ?: config('database.connections.mysql.database');
        $username = env('DB_USERNAME') ?: config('database.connections.mysql.username');
        $password = env('DB_PASSWORD') ?: config('database.connections.mysql.password');
        $host = env('DB_HOST') ?: config('database.connections.mysql.host');
        $port = env('DB_PORT') ?: config('database.connections.mysql.port');

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($isWindows) {
            $laragonPath = 'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe';
            $mysqldumpPath = file_exists($laragonPath) ? $laragonPath : 'mysqldump';
        } else {
            $mysqldumpPath = file_exists('/usr/bin/mysqldump') ? '/usr/bin/mysqldump' : 'mysqldump';
        }

        $command = [
            '"' . $mysqldumpPath . '"',
            '--user=' . escapeshellarg($username),
            '--password=' . escapeshellarg($password),
            '--host=' . escapeshellarg($host),
        ];

        if (!empty($port)) {
            $command[] = '--port=' . escapeshellarg($port);
        }

        $command[] = '--single-transaction';
        $command[] = '--skip-ssl';
        $command[] = '--databases';
        $command[] = escapeshellarg($database);
        $command[] = '--result-file=' . escapeshellarg($sqlFilePath);
        $command[] = '--add-drop-database';
        $command[] = '--add-drop-table';
        $command[] = '--default-character-set=utf8';
        $command[] = '--skip-comments';

        // Opción para aligerar excluyendo coordenadas GPS si se solicita
        if ($this->option('sin-gps')) {
            $this->line("   (Excluyendo tabla coordenadas_historial para optimizar tamaño)");
            $command[] = '--ignore-table=' . escapeshellarg("{$database}.coordenadas_historial");
        }

        $commandStr = implode(' ', $command) . ' 2>&1';
        $output = shell_exec($commandStr);

        $ok = File::exists($sqlFilePath) && filesize($sqlFilePath) > 0;

        if (!$ok) {
            $this->newLine();
            $this->error("Error ejecutando mysqldump:");
            $this->line("Comando intentado: " . preg_replace('/--password=[^\s]+/', '--password=****', $commandStr));
            $this->line("Salida del sistema: " . ($output ?: '(sin salida)'));
        }

        return $ok;
    }

    /**
     * Empaqueta el proyecto usando tar con exclusiones de seguridad y rendimiento
     */
    protected function ejecutarTarGz(string $tarFilePath): bool
    {
        $baseDir = base_path();

        // Lista de exclusiones estrictas
        $excludes = [
            '--exclude=.env',
            '--exclude=.env.*',
            '--exclude=vendor',
            '--exclude=node_modules',
            '--exclude=.git',
            '--exclude=storage/framework/cache/*',
            '--exclude=storage/framework/sessions/*',
            '--exclude=storage/framework/views/*',
            '--exclude=storage/app/backups_completos',
            '--exclude=storage/app/historial_backups',
            '--exclude=whatsapp-bot/node_modules',
            '--exclude=whatsapp-bot/.env',
            '--exclude=whatsapp-bot/.env.*',
        ];

        $excludeStr = implode(' ', $excludes);

        // Ejecutar tar desde base_path() para empaquetar archivos limpios
        $command = "cd " . escapeshellarg($baseDir) . " && tar {$excludeStr} -czf " . escapeshellarg($tarFilePath) . " . 2>&1";
        $output = shell_exec($command);

        return File::exists($tarFilePath) && filesize($tarFilePath) > 0;
    }

    /**
     * Lista los respaldos completos existentes
     */
    protected function listarRespaldos(string $backupPath): int
    {
        $archivos = File::glob("{$backupPath}/respaldo_sgtsita_completo_*.tar.gz");

        if (empty($archivos)) {
            $this->info("No hay respaldos completos guardados en storage/app/{$this->backupDir}/");
            return 0;
        }

        $tabla = [];
        $totalBytes = 0;

        foreach ($archivos as $archivo) {
            $size = filesize($archivo);
            $totalBytes += $size;
            $tabla[] = [
                basename($archivo),
                $this->formatearBytes($size),
                date('Y-m-d H:i:s', filemtime($archivo)),
            ];
        }

        $this->info("Respaldos completos encontrados en storage/app/{$this->backupDir}/:");
        $this->table(['Nombre de Archivo', 'Tamaño', 'Fecha'], $tabla);
        $this->info("Total de espacio ocupado: " . $this->formatearBytes($totalBytes));

        return 0;
    }

    /**
     * Elimina respaldos completos del servidor
     */
    protected function eliminarRespaldos(string $backupPath): int
    {
        $archivoEspecifico = $this->option('archivo');

        if ($archivoEspecifico) {
            $archivoEspecifico = basename($archivoEspecifico);
            $targetPath = "{$backupPath}/{$archivoEspecifico}";

            if (!File::exists($targetPath)) {
                $this->error("El archivo especificado no existe: {$archivoEspecifico}");
                return 1;
            }

            $size = filesize($targetPath);

            if (!$this->option('force') && !$this->confirm("¿Estás seguro de eliminar '{$archivoEspecifico}' (" . $this->formatearBytes($size) . ")?", false)) {
                $this->info("Operación cancelada.");
                return 0;
            }

            File::delete($targetPath);
            $this->info("✓ Archivo '{$archivoEspecifico}' eliminado correctamente.");
            return 0;
        }

        // Si no se especificó archivo, listar y preguntar
        $archivos = File::glob("{$backupPath}/respaldo_sgtsita_completo_*.tar.gz");

        if (empty($archivos)) {
            $this->info("No hay respaldos completos para eliminar en storage/app/{$this->backupDir}/");
            return 0;
        }

        $opciones = ['Todos los respaldos'];
        $nombres = [];
        $totalBytes = 0;

        foreach ($archivos as $arc) {
            $nom = basename($arc);
            $sz = filesize($arc);
            $totalBytes += $sz;
            $nombres[$nom] = $arc;
            $opciones[] = "{$nom} (" . $this->formatearBytes($sz) . ")";
        }

        if ($this->option('force')) {
            foreach ($archivos as $arc) {
                File::delete($arc);
            }
            $this->info("✓ Se eliminaron todos los respaldos completos (" . $this->formatearBytes($totalBytes) . " liberados).");
            return 0;
        }

        $seleccion = $this->choice(
            "¿Qué respaldo deseas eliminar del servidor?",
            array_merge($opciones, ['Cancelar']),
            count($opciones) // Por defecto 'Cancelar'
        );

        if ($seleccion === 'Cancelar') {
            $this->info("Operación cancelada.");
            return 0;
        }

        if ($seleccion === 'Todos los respaldos') {
            if ($this->confirm("¿Confirmas eliminar TODOS los respaldos (" . $this->formatearBytes($totalBytes) . ")?", false)) {
                foreach ($archivos as $arc) {
                    File::delete($arc);
                }
                $this->info("✓ Se eliminaron todos los respaldos completos. Se liberaron " . $this->formatearBytes($totalBytes) . " en disco.");
            } else {
                $this->info("Operación cancelada.");
            }
            return 0;
        }

        // Uno específico seleccionado del menú
        $nombreSeleccionado = explode(' (', $seleccion)[0];
        if (isset($nombres[$nombreSeleccionado])) {
            File::delete($nombres[$nombreSeleccionado]);
            $this->info("✓ Respaldo '{$nombreSeleccionado}' eliminado correctamente.");
        }

        return 0;
    }

    /**
     * Formatea un número de bytes a una cadena legible (KB, MB, GB)
     */
    protected function formatearBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
