<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ViajesCostos;
use App\Models\Viajes;

class CorregirSobrepesoViajesCostosCommand extends Command
{
    /**
     * El nombre y firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'viajes:corregir-sobrepeso-costos 
                            {--dry-run : Ejecuta una simulación sin aplicar cambios en la base de datos}
                            {--viaje= : ID de viaje específico a corregir}';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Recalcula y corrige el monto de sobrepeso en viajes_costos utilizando la diferencia exacta (peso_contenedor - peso_reglamentario)';

    /**
     * Ejecuta el comando.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $viajeId = $this->option('viaje');

        $this->info("⚖️ Iniciando revisión y corrección de sobrepeso en viajes_costos...");
        if ($isDryRun) {
            $this->warn("MODO SIMULACIÓN (--dry-run activo): No se realizarán cambios en la base de datos.");
        }

        $query = ViajesCostos::where('concepto', 'sobrepeso');
        if ($viajeId) {
            $query->where('viaje_id', $viajeId);
        }

        $costos = $query->get();
        $this->info("Costos de sobrepeso evaluados: " . $costos->count());

        $corregidos = 0;
        $sinCambio = 0;
        $errores = 0;

        foreach ($costos as $costo) {
            $viaje = Viajes::with('cotizaciones')->find($costo->viaje_id);
            if (!$viaje || $viaje->cotizaciones->isEmpty()) {
                $errores++;
                continue;
            }

            $precio = floatval($costo->meta['precio_sobre_peso'] ?? 0);
            $pesoPrevio = floatval($costo->meta['peso'] ?? 0);

            // Calcular diferencia exacta a partir del peso del contenedor real
            $pesoExacto = 0;
            if ($viaje->tipo === 'full') {
                foreach ($viaje->cotizaciones as $c) {
                    $pCont = floatval($c->peso_contenedor ?? 0);
                    $pReg = floatval($c->peso_reglamentario ?: 22);
                    if ($pCont > $pReg) {
                        $pesoExacto += ($pCont - $pReg);
                    }
                    if ($precio <= 0) {
                        $precio = floatval($c->precio_sobre_peso ?? 0);
                    }
                }
            } else {
                if ($viaje->cotizaciones->count() === 1) {
                    $c = $viaje->cotizaciones->first();
                    $pCont = floatval($c->peso_contenedor ?? 0);
                    $pReg = floatval($c->peso_reglamentario ?: 22);
                    $pesoExacto = max($pCont - $pReg, 0);
                    if ($precio <= 0) {
                        $precio = floatval($c->precio_sobre_peso ?? 0);
                    }
                } else {
                    $c = $viaje->cotizaciones->first(function ($item) use ($pesoPrevio) {
                        $pCont = floatval($item->peso_contenedor ?? 0);
                        $pReg = floatval($item->peso_reglamentario ?: 22);
                        return round(max($pCont - $pReg, 0), 2) == round($pesoPrevio, 2);
                    });
                    if ($c) {
                        $pCont = floatval($c->peso_contenedor ?? 0);
                        $pReg = floatval($c->peso_reglamentario ?: 22);
                        $pesoExacto = max($pCont - $pReg, 0);
                        if ($precio <= 0) {
                            $precio = floatval($c->precio_sobre_peso ?? 0);
                        }
                    }
                }
            }

            if ($pesoExacto <= 0 || $precio <= 0 || $pesoPrevio <= 0) {
                $sinCambio++;
                continue;
            }

            // Solo corregir si el peso previo coincide por redondeo (evita alterar casos anómalos históricos)
            $esDiferenciaRedondeo = round($pesoExacto, 2) == round($pesoPrevio, 2);
            $hayDiferenciaDecimal = abs($pesoExacto - $pesoPrevio) > 0.0001;

            if ($esDiferenciaRedondeo && $hayDiferenciaDecimal) {
                $nuevoMonto = round($pesoExacto * $precio, 2);
                $montoActual = floatval($costo->monto);

                $this->line("Viaje #{$viaje->id} (Costo #{$costo->id}):");
                $this->line("  - Monto anterior: \${$montoActual} (Peso: {$pesoPrevio} ton)");
                $this->line("  - Monto nuevo:    \${$nuevoMonto} (Peso exacto: {$pesoExacto} ton @ \${$precio}/ton)");
                $this->line("  - Diferencia:     \$" . round($nuevoMonto - $montoActual, 2));

                if (!$isDryRun) {
                    DB::transaction(function () use ($costo, $nuevoMonto, $pesoExacto, $precio) {
                        $meta = is_array($costo->meta) ? $costo->meta : [];
                        $meta['peso'] = $pesoExacto;
                        $meta['precio_sobre_peso'] = $precio;
                        $meta['precio_tonelada'] = $nuevoMonto;

                        $costo->update([
                            'monto' => $nuevoMonto,
                            'meta' => $meta,
                        ]);
                    });
                }
                $corregidos++;
            } else {
                $sinCambio++;
            }
        }

        $this->newLine();
        $this->info("=========================================");
        $this->info("Resumen de Ejecución:");
        $this->info(" - Costos corregidos: {$corregidos}");
        $this->info(" - Costos sin cambio: {$sinCambio}");
        $this->info(" - Errores/omitidos:  {$errores}");
        $this->info("=========================================");

        return 0;
    }
}
