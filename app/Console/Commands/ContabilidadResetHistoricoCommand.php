<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Poliza;
use App\Models\PolizaMovimiento;
use Illuminate\Support\Facades\DB;

class ContabilidadResetHistoricoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contabilidad:reset-historico 
                            {--empresa= : ID de la empresa a reiniciar (opcional)}
                            {--all : Eliminar todas las pólizas incluyendo las de apertura}
                            {--confirm : Omitir solicitud de confirmación interactiva}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina pólizas contables generadas automáticamente para permitir pruebas repetitivas de sincronización';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->warn("================================================================================");
        $this->warn("      SGTSITA - REINICIO / LIMPIEZA DE PÓLIZAS CONTABLES HISTÓRICAS            ");
        $this->warn("================================================================================");

        $empresaId = $this->option('empresa');
        $all = (bool) $this->option('all');
        $confirm = (bool) $this->option('confirm');

        $query = Poliza::query();
        if ($empresaId) {
            $query->where('id_empresa', $empresaId);
        }

        if (!$all) {
            $query->where(function ($q) {
                $q->whereIn('origen_tipo', [
                    'cotizacion',
                    'cobro_cxc',
                    'gasto_devengado',
                    'gasto_pago',
                    'prestamo_operador',
                    'socio_pago',
                    'movimiento_bancario',
                ])->orWhere('es_apertura', false);
            })->where('es_apertura', false);
        }

        $totalPolizas = $query->count();

        if ($totalPolizas === 0) {
            $this->info("No se encontraron pólizas para eliminar con los filtros seleccionados.");
            return Command::SUCCESS;
        }

        $msg = $all 
            ? "Se eliminarán TODAS las pólizas ({$totalPolizas}) incluyendo Pólizas de Apertura"
            : "Se eliminarán {$totalPolizas} pólizas operativas automáticas (conservando Pólizas de Apertura)";

        if ($empresaId) {
            $msg .= " para la empresa ID {$empresaId}.";
        } else {
            $msg .= " para TODAS las empresas.";
        }

        $this->line($msg);

        if (!$confirm && !$this->confirm("¿Deseas continuar con la eliminación?", true)) {
            $this->line("Operación cancelada por el usuario.");
            return Command::SUCCESS;
        }

        $polizasIds = $query->pluck('id');

        DB::transaction(function () use ($polizasIds) {
            PolizaMovimiento::whereIn('poliza_id', $polizasIds)->delete();
            Poliza::whereIn('id', $polizasIds)->delete();
        });

        $this->info("✓ Se eliminaron {$totalPolizas} pólizas y sus asientos contables.");
        $this->info("El módulo contable está listo para una nueva ejecución de sincronización.");

        return Command::SUCCESS;
    }
}
