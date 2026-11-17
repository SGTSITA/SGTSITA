<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RenombrarAnticiposDineroViajeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contabilidad:renombrar-anticipos-dinero-viaje
                            {empresa? : ID de la empresa (opcional, procesa todas si se omite)}
                            {--dry-run : Muestra los registros que se actualizarían sin modificar la base de datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renombra masivamente los conceptos de "Anticipo de viaje por comprobar" a "Dinero para viaje" en Gastos y Pólizas';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("================================================================================");
        $this->info("     SGTSITA - RENOMBRAMIENTO MASIVO: ANTICIPOS A 'DINERO PARA VIAJE'           ");
        $this->info("================================================================================");

        $empresaInput = $this->argument('empresa');
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn(">>> MODO SIMULACIÓN (--dry-run) ACTIVO: No se realizarán cambios. <<<");
        }

        // 1. Obtener empresas
        $empresasQuery = DB::table('empresas')->orderBy('id');
        if ($empresaInput) {
            $empresasQuery->where('id', $empresaInput);
        }
        $empresas = $empresasQuery->get();

        if ($empresas->isEmpty()) {
            $this->error("No se encontraron empresas para procesar.");
            return 1;
        }

        $resumenGlobal = [];
        $totalGastos = 0;
        $totalPolizas = 0;
        $totalMovimientos = 0;

        foreach ($empresas as $emp) {
            $idEmpresa = $emp->id;

            // Contar gastos candidatos
            $gastosCount = DB::table('gastos')
                ->where('id_empresa', $idEmpresa)
                ->where('concepto', 'LIKE', 'Anticipo de viaje por comprobar%')
                ->count();

            // Contar pólizas candidatas
            $polizasCount = DB::table('polizas')
                ->where('id_empresa', $idEmpresa)
                ->where('concepto', 'LIKE', '%Anticipo de viaje por comprobar%')
                ->count();

            // Contar movimientos de póliza candidatos
            $movimientosCount = DB::table('poliza_movimientos as pm')
                ->join('polizas as p', 'p.id', '=', 'pm.poliza_id')
                ->where('p.id_empresa', $idEmpresa)
                ->where('pm.concepto_detalle', 'LIKE', '%Anticipo de viaje por comprobar%')
                ->count();

            if ($gastosCount === 0 && $polizasCount === 0 && $movimientosCount === 0) {
                continue;
            }

            if (!$dryRun) {
                DB::transaction(function () use ($idEmpresa) {
                    // Actualizar gastos
                    DB::table('gastos')
                        ->where('id_empresa', $idEmpresa)
                        ->where('concepto', 'LIKE', 'Anticipo de viaje por comprobar%')
                        ->update([
                            'concepto' => DB::raw("REPLACE(concepto, 'Anticipo de viaje por comprobar', 'Dinero para viaje')"),
                        ]);

                    // Actualizar pólizas
                    DB::table('polizas')
                        ->where('id_empresa', $idEmpresa)
                        ->where('concepto', 'LIKE', '%Anticipo de viaje por comprobar%')
                        ->update([
                            'concepto' => DB::raw("REPLACE(concepto, 'Anticipo de viaje por comprobar', 'Dinero para viaje')"),
                        ]);

                    // Actualizar movimientos de póliza
                    DB::statement("
                        UPDATE poliza_movimientos pm
                        JOIN polizas p ON p.id = pm.poliza_id
                        SET pm.concepto_detalle = REPLACE(pm.concepto_detalle, 'Anticipo de viaje por comprobar', 'Dinero para viaje')
                        WHERE p.id_empresa = ? AND pm.concepto_detalle LIKE '%Anticipo de viaje por comprobar%'
                    ", [$idEmpresa]);
                });
            }

            $totalGastos += $gastosCount;
            $totalPolizas += $polizasCount;
            $totalMovimientos += $movimientosCount;

            $resumenGlobal[] = [
                'empresa' => "#{$idEmpresa} - {$emp->nombre}",
                'gastos' => $gastosCount,
                'polizas' => $polizasCount,
                'movimientos' => $movimientosCount,
            ];
        }

        if (empty($resumenGlobal)) {
            $this->info("No hay registros pendientes por renombrar. Todos ya tienen 'Dinero para viaje'.");
            return 0;
        }

        $this->line("");
        $this->table(['Empresa', 'Gastos', 'Pólizas', 'Movimientos'], $resumenGlobal);

        if (!$dryRun) {
            $this->info("\n¡Renombramiento completado exitosamente! ({$totalGastos} gastos, {$totalPolizas} pólizas, {$totalMovimientos} movimientos).");
            $this->info("Se recomienda ejecutar 'php artisan optimize:clear'.");
        } else {
            $this->warn("\nSimulación completada ({$totalGastos} gastos, {$totalPolizas} pólizas, {$totalMovimientos} movimientos identificados).");
            $this->warn("Para aplicar los cambios reales ejecuta el comando sin '--dry-run'.");
        }

        return 0;
    }
}
