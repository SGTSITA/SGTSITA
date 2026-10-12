<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\DocumCotizacion;
use App\Models\DineroContenedor;
use App\Models\Asignaciones;
use App\Models\CatBancoCuentasMovimientos;

class AuditDineroPrincipalCommand extends Command
{
    protected $signature = 'sgt:dinero-principal:audit 
                            {--fix : Aplica es_principal = 1 a los registros inequívocos confirmados} 
                            {--contenedor= : Filtrar por un número de contenedor específico}
                            {--todos : Auditar todos los registros históricos, incluso liquidados}';

    protected $description = 'Audita registros de dinero_contenedor para identificar el dinero principal ($1,000 habitual) y detectar ambigüedades antes de producción';

    public function handle()
    {
        $this->info("================================================================================");
        $this->info("   AUDITORÍA DE DINERO PARA VIAJE (IDENTIFICACIÓN DE DINERO PRINCIPAL)          ");
        $this->info("================================================================================");

        $aplicarFix = $this->option('fix');
        $filtroContenedor = $this->option('contenedor');
        $auditarTodos = $this->option('todos');

        if ($aplicarFix) {
            $this->warn("MODO FIX ACTIVADO: Se actualizarán los registros inequívocos a es_principal = 1.");
        } else {
            $this->comment("MODO AUDITORÍA (Solo lectura): Ejecuta con --fix para aplicar cambios a casos seguros.");
        }

        // Consultar contenedores que tienen registros en dinero_contenedor
        $query = DocumCotizacion::query()
            ->with(['Asignaciones', 'cotizacion'])
            ->whereHas('Asignaciones', function ($q) use ($auditarTodos) {
                if (!$auditarTodos) {
                    // Por defecto, auditar los que están pendientes de pago o en proceso
                    $q->where(function ($sq) {
                        $sq->where('estatus_pagado', 'Pendiente Pago')
                           ->orWhereNull('estatus_pagado')
                           ->orWhere('estatus_pagado', '');
                    });
                }
            });

        if ($filtroContenedor) {
            $query->where('num_contenedor', 'like', "%{$filtroContenedor}%");
        }

        $contenedores = $query->get();

        if ($contenedores->isEmpty()) {
            $this->warn("No se encontraron contenedores para auditar con los filtros especificados.");
            return 0;
        }

        $this->line("Contenedores a analizar: " . $contenedores->count());
        $this->newLine();

        $tablaFilas = [];
        $totalAmbiguos = 0;
        $totalSinRegistro = 0;
        $totalInequivocos = 0;
        $actualizados = 0;

        foreach ($contenedores as $doc) {
            $asignacion = $doc->Asignaciones;
            $dineros = DineroContenedor::where('id_contenedor', $doc->id)->orderBy('id', 'asc')->get();
            $dineroAsignacion = floatval($asignacion?->dinero_viaje ?? 0);

            if ($dineros->isEmpty()) {
                if ($dineroAsignacion > 0) {
                    $totalSinRegistro++;
                    $tablaFilas[] = [
                        'id_doc' => $doc->id,
                        'contenedor' => $doc->num_contenedor,
                        'regs' => 0,
                        'montos' => "Asignación: $" . number_format($dineroAsignacion, 2),
                        'candidato' => 'Ninguno',
                        'clasificacion' => '<fg=red>FALTA REGISTRO</>',
                        'accion' => 'Requiere revisión manual'
                    ];
                }
                continue;
            }

            // Analizar los dineros del contenedor
            $montosStr = $dineros->map(fn($d) => "#{$d->id}: $" . number_format($d->monto, 2) . " (" . $d->motivo . ")" . ($d->es_principal ? " [P]" : ""))->implode(" | ");

            $candidatoPrincipal = null;
            $motivoClasificacion = '';

            // Caso 1: Solo hay 1 registro de dinero
            if ($dineros->count() === 1) {
                $candidatoPrincipal = $dineros->first();
                $motivoClasificacion = '<fg=green>INÉQUIVOCO (1 solo registro)</>';
            } else {
                // Caso 2: Hay múltiples registros
                // Buscar si hay uno que coincida con dinero_viaje de asignación o $1,000 habitual y motivo 'Dinero para viaje'
                $candidatosExactos = $dineros->filter(function ($d) use ($dineroAsignacion) {
                    $esMotivoOrigen = strtolower(trim($d->motivo)) === 'dinero para viaje';
                    $esMontoAsignacion = abs(floatval($d->monto) - $dineroAsignacion) < 0.01 && $dineroAsignacion > 0;
                    $esMontoEstandar = abs(floatval($d->monto) - 1000.0) < 0.01;
                    return $esMotivoOrigen && ($esMontoAsignacion || $esMontoEstandar);
                });

                if ($candidatosExactos->count() === 1) {
                    $candidatoPrincipal = $candidatosExactos->first();
                    $motivoClasificacion = '<fg=cyan>INÉQUIVOCO (Multi-registro con 1 estándar)</>';
                } else {
                    // Si ya uno tenía es_principal = 1
                    $yaMarcado = $dineros->firstWhere('es_principal', 1);
                    if ($yaMarcado) {
                        $candidatoPrincipal = $yaMarcado;
                        $motivoClasificacion = '<fg=green>YA MARCADO COMO PRINCIPAL</>';
                    } else {
                        $totalAmbiguos++;
                        $motivoClasificacion = '<fg=yellow>AMBIGUO (Múltiples posibles o montos no estándar)</>';
                    }
                }
            }

            $accionStr = 'Ninguna';
            if ($candidatoPrincipal) {
                $totalInequivocos++;
                if ($candidatoPrincipal->es_principal) {
                    $accionStr = '<fg=gray>Ya configurado</>';
                } else {
                    if ($aplicarFix) {
                        DB::transaction(function () use ($candidatoPrincipal, $asignacion) {
                            $candidatoPrincipal->es_principal = 1;
                            
                            // Si no tiene movimiento bancario enlazado, intentar asociarlo
                            if (!$candidatoPrincipal->id_movimiento_bancario && $asignacion) {
                                $mov = CatBancoCuentasMovimientos::where('referenciaable_id', $asignacion->id)
                                    ->where(function ($q) {
                                        $q->where('referenciaable_type', Asignaciones::class)
                                          ->orWhere('referenciaable_type', 'like', '%Asignaciones%');
                                    })
                                    ->where('cancelado', 0)
                                    ->where('tipo', 'cargo')
                                    ->where('concepto', 'not like', 'Devolución%')
                                    ->where('concepto', 'not like', 'Dinero para viaje: %')
                                    ->where('monto', $candidatoPrincipal->monto)
                                    ->latest('id')
                                    ->first();

                                if ($mov) {
                                    $candidatoPrincipal->id_movimiento_bancario = $mov->id;
                                }
                            }

                            $candidatoPrincipal->save();
                        });
                        $actualizados++;
                        $accionStr = '<fg=green>Actualizado a es_principal=1</>';
                    } else {
                        $accionStr = '<fg=cyan>Candidato a fix</>';
                    }
                }
            } else {
                $accionStr = '<fg=yellow>Revisión manual requerida</>';
            }

            $tablaFilas[] = [
                'id_doc' => $doc->id,
                'contenedor' => $doc->num_contenedor,
                'regs' => $dineros->count(),
                'montos' => \Illuminate\Support\Str::limit($montosStr, 60),
                'candidato' => $candidatoPrincipal ? "#{$candidatoPrincipal->id} ($" . number_format($candidatoPrincipal->monto, 2) . ")" : 'Ninguno',
                'clasificacion' => $motivoClasificacion,
                'accion' => $accionStr
            ];
        }

        $this->table(
            ['ID Doc', 'Contenedor', 'Regs', 'Detalle Registros Dinero', 'Candidato Principal', 'Clasificación', 'Acción'],
            $tablaFilas
        );

        $this->newLine();
        $this->info("=== RESUMEN DE AUDITORÍA ===");
        $this->line("Total Contenedores evaluados: " . count($tablaFilas));
        $this->line("Casos Inéquivocos: <fg=green>{$totalInequivocos}</>");
        $this->line("Casos Ambiguos con advertencia: <fg=yellow>{$totalAmbiguos}</>");
        $this->line("Casos sin registros de dinero: <fg=red>{$totalSinRegistro}</>");

        if ($aplicarFix) {
            $this->info("Registros actualizados a es_principal = 1: {$actualizados}");
        } else {
            $this->comment("Para aplicar la marcación en casos inéquivocos, ejecuta:");
            $this->comment("php artisan sgt:dinero-principal:audit --fix");
        }

        return 0;
    }
}
