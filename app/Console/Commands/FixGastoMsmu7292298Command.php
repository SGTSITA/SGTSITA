<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Gasto;
use App\Models\BitacoraViajeOperador;
use App\Models\GastosOperadores;
use App\Models\Cotizaciones;
use App\Models\DocumCotizacion;
use App\Models\Asignaciones;

class FixGastoMsmu7292298Command extends Command
{
    protected $signature = 'fix:gasto-msmu7292298 {--dry-run : Ejecutar sin guardar cambios en BD}';
    protected $description = 'Restaura el gasto de diésel soft-deleted para el contenedor MSMU7292298 y sincroniza con cotización';

    public function handle()
    {
        $this->info("=== Iniciando regularización y restauración de diésel MSMU7292298 ===");
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn("MODO DRY-RUN ACTIVADO: No se aplicarán cambios a la base de datos.");
        }

        // 1. Localizar contenedor, cotización y asignación
        $doc = DocumCotizacion::where('num_contenedor', 'like', '%MSMU7292298%')->first();
        if (!$doc) {
            $this->error("Contenedor MSMU7292298 no encontrado en DocumCotizacion.");
            return 1;
        }

        $idAsignacion = 3720;
        $asignacion = Asignaciones::find($idAsignacion);
        $idCotizacion = $doc->id_cotizacion; // 4602
        $cotizacion = Cotizaciones::find($idCotizacion);

        $this->line("Contenedor ID: {$doc->id} ({$doc->num_contenedor})");
        $this->line("Cotización ID: {$idCotizacion}");
        $this->line("Asignación ID: {$idAsignacion}");

        // 2. Bitácora #8
        $bitacora = BitacoraViajeOperador::where('id_asignacion', $idAsignacion)->first();
        $this->line("Bitácora ID: " . ($bitacora ? $bitacora->id : 'No encontrada') . " (Diesel: \${$bitacora?->costo}, Litros: {$bitacora?->litros}L, Urea: \${$bitacora?->costo_urea}, Litros Urea: {$bitacora?->litros_urea}L)");

        // 3. GastosOperadores
        $goDiesel = GastosOperadores::where('id_asignacion', $idAsignacion)->where('tipo', 'Diesel')->first();
        $this->line("GastosOperadores Diesel ID: " . ($goDiesel ? $goDiesel->id : 'No encontrado') . " (Cantidad: \${$goDiesel?->cantidad})");

        // 4. Gasto #2236
        $gasto = Gasto::withTrashed()->where(function($q) use ($idAsignacion) {
            $q->where('origen_legacy_id', $idAsignacion)
              ->where('origen_legacy', 'like', 'asignacion_planeacion%')
              ->where('concepto', 'like', '%Diesel%');
        })->first();

        if (!$gasto) {
            $gasto = Gasto::withTrashed()->find(2236);
        }

        if (!$gasto) {
            $this->error("Gasto de diésel para asignación #{$idAsignacion} no encontrado.");
            return 1;
        }

        $this->line("Gasto #{$gasto->id} actual: monto_total=\${$gasto->monto_total}, trashed=" . ($gasto->trashed() ? 'SÍ (' . $gasto->deleted_at . ')' : 'NO'));

        $montoDiesel = $bitacora ? (float)$bitacora->costo : 16827.91;
        $litrosDiesel = $bitacora ? (float)$bitacora->litros : 609.93;
        $litrosUrea = $bitacora ? (float)$bitacora->litros_urea : 48.00;

        if (!$dryRun) {
            DB::transaction(function () use ($gasto, $bitacora, $goDiesel, $cotizacion, $asignacion, $doc, $montoDiesel, $litrosDiesel, $litrosUrea) {
                // 1. Restaurar gasto si está soft-deleted
                if ($gasto->trashed()) {
                    $gasto->restore();
                    $this->info("Gasto #{$gasto->id} RESTAURADO exitosamente (deleted_at = NULL).");
                }

                // 2. Asegurar campos del gasto
                $gasto->monto_total = $montoDiesel;
                $gasto->estatus = $gasto->estatus === 'cancelado' ? 'pendiente_pago' : ($gasto->estatus ?? 'pendiente_pago');
                $gasto->concepto = 'GDI02 - Diesel';
                $gasto->tipo_gasto = 'viaje';
                $gasto->origen_modulo = 'liquidacion_operador';
                $gasto->origen_legacy = 'asignacion_planeacionGDI02 - Diesel';
                $gasto->origen_legacy_id = $asignacion->id;
                $gasto->save();

                // 3. Asegurar vínculos
                $vinculos = [
                    ['tipo_vinculo' => 'cotizacion', 'vinculable_type' => get_class($cotizacion), 'vinculable_id' => $cotizacion->id],
                    ['tipo_vinculo' => 'contenedor', 'vinculable_type' => get_class($doc), 'vinculable_id' => $doc->id, 'observaciones' => $doc->num_contenedor],
                    ['tipo_vinculo' => 'asignacion', 'vinculable_type' => get_class($asignacion), 'vinculable_id' => $asignacion->id],
                ];
                if ($asignacion->id_operador) {
                    $operador = $asignacion->Operador;
                    if ($operador) {
                        $vinculos[] = ['tipo_vinculo' => 'operador', 'vinculable_type' => get_class($operador), 'vinculable_id' => $operador->id];
                    }
                }

                $gasto->vinculos()->delete();
                foreach ($vinculos as $v) {
                    $gasto->vinculos()->create($v);
                }

                // 4. Asegurar imputación
                $gasto->imputaciones()->delete();
                if ($asignacion->id_operador) {
                    $gasto->imputaciones()->create([
                        'fecha_imputacion' => $gasto->fecha_operacion ?? now(),
                        'tipo_imputacion' => 'operador',
                        'imputable_type' => 'App\Models\Operador',
                        'imputable_id' => $asignacion->id_operador,
                        'monto_imputado' => $montoDiesel,
                        'origen' => 'directo',
                    ]);
                }

                // 5. Actualizar GastosOperadores
                if ($goDiesel) {
                    $goDiesel->cantidad = $montoDiesel;
                    $goDiesel->id_cotizacion = $cotizacion->id;
                    $goDiesel->save();
                }

                // 6. Actualizar Cotizaciones
                if ($cotizacion) {
                    $cotizacion->litros_diesel = $litrosDiesel;
                    $cotizacion->litros_urea = $litrosUrea;
                    $cotizacion->save();
                    $this->info("Cotización #{$cotizacion->id} actualizada con litros_diesel={$litrosDiesel}, litros_urea={$litrosUrea}.");
                }
            });

            $this->info("REGULARIZACIÓN EXITOSA: Gasto #{$gasto->id} de diésel activo y visible en el sistema.");
        } else {
            $this->info("DRY-RUN completado. Sin cambios efectuados.");
        }

        return 0;
    }
}
