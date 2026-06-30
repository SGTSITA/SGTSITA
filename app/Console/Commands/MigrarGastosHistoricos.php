<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\CategoriasGastos;
use App\Models\GastoConcepto;
use App\Models\Gasto;
use App\Models\GastoPago;
use App\Models\GastoImputacion;
use App\Models\GastoVinculo;

class MigrarGastosHistoricos extends Command
{
    protected $signature = 'gastos:migrar-historico';

    protected $description = 'Migra gastos históricos desde gastos_generales, gastos_extras y gastos_operadores al módulo unificado de gastos';

    public function handle()
    {
        $this->info('🚀 Iniciando migración de gastos históricos...');

        // 1. Crear/Verificar categoría y conceptos de migración
        $this->info('1. Configurando categoría y conceptos de migración...');
        
        $categoriaMigracion = CategoriasGastos::where('categoria', 'Migración Histórica')->first();
        if (!$categoriaMigracion) {
            $categoriaMigracion = new CategoriasGastos();
            $categoriaMigracion->categoria = 'Migración Histórica';
            $categoriaMigracion->is_active = true;
            $categoriaMigracion->save();
        }

        $conceptosMigracion = [
            'MIG_GEN' => [
                'nombre' => 'Gasto General Migrado',
                'tipo_default' => 'general',
            ],
            'MIG_EXT' => [
                'nombre' => 'Gasto Extra Migrado',
                'tipo_default' => 'cotizacion',
            ],
            'MIG_OPE' => [
                'nombre' => 'Gasto Operador Migrado',
                'tipo_default' => 'operador',
            ],
        ];

        $conceptoIds = [];
        foreach ($conceptosMigracion as $clave => $info) {
            $concepto = GastoConcepto::where('categoria_gasto_id', $categoriaMigracion->id)
                ->where('clave', $clave)
                ->first();
            if (!$concepto) {
                $concepto = new GastoConcepto();
                $concepto->categoria_gasto_id = $categoriaMigracion->id;
                $concepto->clave = $clave;
                $concepto->nombre = $info['nombre'];
                $concepto->tipo_default = $info['tipo_default'];
                $concepto->afecta_utilidad = true;
                $concepto->permite_diferir = false;
                $concepto->es_recuperable_cliente = false;
                $concepto->is_active = true;
                $concepto->save();
            }
            $conceptoIds[$clave] = $concepto->id;
        }

        // Obtener ID de la primera empresa como fallback
        $fallbackEmpresaId = DB::table('empresas')->orderBy('id')->first()?->id ?? 1;

        // 2. Migrar gastos_generales
        if (Schema::hasTable('gastos_generales')) {
            $this->info('2. Migrando gastos_generales...');
            $gastosGenerales = DB::table('gastos_generales')->get();
            $countGen = 0;

            foreach ($gastosGenerales as $gGeneral) {
                // Evitar duplicados
                $existe = Gasto::where('origen_legacy', 'gastos_generales')
                    ->where('origen_legacy_id', $gGeneral->id)
                    ->exists();

                if ($existe) {
                    continue;
                }

                $montoTotal = floatval($gGeneral->monto1 ?? 0) + floatval($gGeneral->monto2 ?? 0);
                if ($montoTotal <= 0) {
                    continue;
                }

                // Determinar categoría y concepto
                $catId = $categoriaMigracion->id;
                $conceptoId = $conceptoIds['MIG_GEN'];

                if (!empty($gGeneral->id_categoria)) {
                    $catExiste = DB::table('categorias_gastos')->where('id', $gGeneral->id_categoria)->exists();
                    if ($catExiste) {
                        $catId = $gGeneral->id_categoria;
                        // Intentar buscar un concepto asignado a esta categoría
                        $con = DB::table('gasto_conceptos')->where('categoria_gasto_id', $catId)->first();
                        if ($con) {
                            $conceptoId = $con->id;
                        } else {
                            // Crear un concepto genérico para esa categoría si no existe ninguno
                            $conNuevo = GastoConcepto::where('categoria_gasto_id', $catId)
                                ->where('clave', 'MIG_GEN_CAT_' . $catId)
                                ->first();
                            if (!$conNuevo) {
                                $conNuevo = new GastoConcepto();
                                $conNuevo->categoria_gasto_id = $catId;
                                $conNuevo->clave = 'MIG_GEN_CAT_' . $catId;
                                $conNuevo->nombre = 'Concepto Migrado Cat ' . $catId;
                                $conNuevo->tipo_default = 'general';
                                $conNuevo->afecta_utilidad = true;
                                $conNuevo->permite_diferir = false;
                                $conNuevo->es_recuperable_cliente = false;
                                $conNuevo->is_active = true;
                                $conNuevo->save();
                            }
                            $conceptoId = $conNuevo->id;
                        }
                    }
                }

                // Determinar estatus
                $estatus = ($gGeneral->pago_realizado == 1) ? 'pagado' : 'pendiente_pago';

                DB::transaction(function () use ($gGeneral, $montoTotal, $catId, $conceptoId, $estatus, $fallbackEmpresaId) {
                    $empresaId = $gGeneral->id_empresa ?? $fallbackEmpresaId;
                    
                    // Crear Gasto
                    $gasto = Gasto::create([
                        'id_empresa' => $empresaId,
                        'categoria_gasto_id' => $catId,
                        'gasto_concepto_id' => $conceptoId,
                        'concepto' => $gGeneral->motivo ?? 'Gasto General Migrado #' . $gGeneral->id,
                        'descripcion' => 'Migrado de gastos_generales',
                        'monto_total' => $montoTotal,
                        'moneda' => 'MXN',
                        'fecha_gasto' => $gGeneral->fecha ?? now()->toDateString(),
                        'fecha_operacion' => $gGeneral->fecha ?? now()->toDateString(),
                        'tipo_gasto' => 'general',
                        'metodo_imputacion' => 'directo',
                        'estatus' => $estatus,
                        'origen_modulo' => 'migracion',
                        'origen_legacy' => 'gastos_generales',
                        'origen_legacy_id' => $gGeneral->id,
                        'user_id' => null,
                    ]);

                    // Crear Partida
                    DB::table('gasto_partidas')->insert([
                        'gasto_id' => $gasto->id,
                        'categoria_gasto_id' => $catId,
                        'gasto_concepto_id' => $conceptoId,
                        'concepto' => $gGeneral->motivo ?? 'Gasto General Migrado #' . $gGeneral->id,
                        'monto' => $montoTotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Crear pagos si corresponde
                    if ($gGeneral->pago_realizado == 1) {
                        if (floatval($gGeneral->monto1 ?? 0) > 0) {
                            GastoPago::create([
                                'gasto_id' => $gasto->id,
                                'cuenta_bancaria_id' => $gGeneral->id_banco1,
                                'fecha_pago' => $gGeneral->fecha ?? now()->toDateString(),
                                'monto' => floatval($gGeneral->monto1),
                                'metodo_pago' => $gGeneral->metodo_pago1,
                                'estatus' => 'aplicado',
                            ]);
                        }

                        if (floatval($gGeneral->monto2 ?? 0) > 0) {
                            GastoPago::create([
                                'gasto_id' => $gasto->id,
                                'cuenta_bancaria_id' => $gGeneral->id_banco2,
                                'fecha_pago' => $gGeneral->fecha ?? now()->toDateString(),
                                'monto' => floatval($gGeneral->monto2),
                                'metodo_pago' => $gGeneral->metodo_pago2,
                                'estatus' => 'aplicado',
                            ]);
                        }
                    }
                });

                $countGen++;
            }
            $this->info("✔ Gastos generales migrados: {$countGen}");
        }

        // 3. Migrar gastos_extras
        if (Schema::hasTable('gastos_extras')) {
            $this->info('3. Migrando gastos_extras...');
            $gastosExtras = DB::table('gastos_extras')->get();
            $countExt = 0;

            foreach ($gastosExtras as $gExtra) {
                // Evitar duplicados
                $existe = Gasto::where('origen_legacy', 'gastos_extras')
                    ->where('origen_legacy_id', $gExtra->id)
                    ->exists();

                if ($existe) {
                    continue;
                }

                $monto = floatval($gExtra->monto ?? 0);
                if ($monto <= 0) {
                    continue;
                }

                // Intentar buscar empresa asociada a la cotización
                $empresaId = $fallbackEmpresaId;
                if (!empty($gExtra->id_cotizacion)) {
                    $cot = DB::table('cotizaciones')->where('id', $gExtra->id_cotizacion)->first();
                    if ($cot) {
                        $empresaId = $cot->id_empresa ?? $fallbackEmpresaId;
                    }
                }

                $estatus = ($gExtra->estatus === 'pagado') ? 'pagado' : 'pendiente_pago';

                DB::transaction(function () use ($gExtra, $monto, $categoriaMigracion, $conceptoIds, $estatus, $empresaId) {
                    $catId = $categoriaMigracion->id;
                    $conceptoId = $conceptoIds['MIG_EXT'];
                    $fecha = $gExtra->fecha_aplicacion ?? now()->toDateString();

                    $gasto = Gasto::create([
                        'id_empresa' => $empresaId,
                        'categoria_gasto_id' => $catId,
                        'gasto_concepto_id' => $conceptoId,
                        'concepto' => $gExtra->descripcion ?? 'Gasto Extra Migrado #' . $gExtra->id,
                        'descripcion' => 'Migrado de gastos_extras',
                        'monto_total' => $monto,
                        'moneda' => 'MXN',
                        'fecha_gasto' => $fecha,
                        'fecha_operacion' => $fecha,
                        'tipo_gasto' => 'cotizacion',
                        'metodo_imputacion' => 'directo',
                        'estatus' => $estatus,
                        'origen_modulo' => 'migracion',
                        'origen_legacy' => 'gastos_extras',
                        'origen_legacy_id' => $gExtra->id,
                        'user_id' => null,
                    ]);

                    // Crear Partida
                    DB::table('gasto_partidas')->insert([
                        'gasto_id' => $gasto->id,
                        'categoria_gasto_id' => $catId,
                        'gasto_concepto_id' => $conceptoId,
                        'concepto' => $gExtra->descripcion ?? 'Gasto Extra Migrado #' . $gExtra->id,
                        'monto' => $monto,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Crear Imputación a la cotización
                    if (!empty($gExtra->id_cotizacion)) {
                        GastoImputacion::create([
                            'gasto_id' => $gasto->id,
                            'fecha_imputacion' => $fecha,
                            'tipo_imputacion' => 'cotizacion',
                            'imputable_type' => 'App\Models\Cotizaciones',
                            'imputable_id' => $gExtra->id_cotizacion,
                            'monto_imputado' => $monto,
                            'origen' => 'directo',
                        ]);

                        // Vincular a nivel de vinculos
                        GastoVinculo::create([
                            'gasto_id' => $gasto->id,
                            'tipo_vinculo' => 'cotizacion',
                            'vinculable_type' => 'App\Models\Cotizaciones',
                            'vinculable_id' => $gExtra->id_cotizacion,
                        ]);
                    }

                    // Crear pago si está pagado
                    if ($estatus === 'pagado') {
                        GastoPago::create([
                            'gasto_id' => $gasto->id,
                            'cuenta_bancaria_id' => $gExtra->cuenta_bancaria_id,
                            'fecha_pago' => $fecha,
                            'monto' => $monto,
                            'estatus' => 'aplicado',
                        ]);
                    }
                });

                $countExt++;
            }
            $this->info("✔ Gastos extras migrados: {$countExt}");
        }

        // 4. Migrar gastos_operadores
        if (Schema::hasTable('gastos_operadores')) {
            $this->info('4. Migrando gastos_operadores...');
            
            $gastosOperadores = DB::table('gastos_operadores')->where('estatus', '!=', 'eliminado')->get();
            $countOpe = 0;

            foreach ($gastosOperadores as $gOperador) {
                // Evitar duplicados
                $existe = Gasto::where('origen_legacy', 'gastos_operadores')
                    ->where('origen_legacy_id', $gOperador->id)
                    ->exists();

                if ($existe) {
                    continue;
                }

                $monto = floatval($gOperador->cantidad ?? 0);
                if ($monto <= 0) {
                    continue;
                }

                // Intentar buscar empresa asociada a la cotización
                $empresaId = $fallbackEmpresaId;
                if (!empty($gOperador->id_cotizacion)) {
                    $cot = DB::table('cotizaciones')->where('id', $gOperador->id_cotizacion)->first();
                    if ($cot) {
                        $empresaId = $cot->id_empresa ?? $fallbackEmpresaId;
                    }
                }

                $estatus = ($gOperador->estatus === 'pagado') ? 'pagado' : 'pendiente_pago';

                DB::transaction(function () use ($gOperador, $monto, $categoriaMigracion, $conceptoIds, $estatus, $empresaId) {
                    $catId = $categoriaMigracion->id;
                    $conceptoId = $conceptoIds['MIG_OPE'];
                    
                    $fecha = $gOperador->fecha_pago ?? $gOperador->created_at ?? now()->toDateString();
                    if (is_string($fecha) && strlen($fecha) > 10) {
                        $fecha = substr($fecha, 0, 10);
                    }

                    $tipoGasto = $gOperador->tipo ?? 'Gasto Operador';

                    $gasto = Gasto::create([
                        'id_empresa' => $empresaId,
                        'categoria_gasto_id' => $catId,
                        'gasto_concepto_id' => $conceptoId,
                        'concepto' => "{$tipoGasto} - Operador Migrado #" . $gOperador->id,
                        'descripcion' => 'Migrado de gastos_operadores',
                        'monto_total' => $monto,
                        'moneda' => 'MXN',
                        'fecha_gasto' => $fecha,
                        'fecha_operacion' => $fecha,
                        'tipo_gasto' => 'operador',
                        'metodo_imputacion' => 'directo',
                        'estatus' => $estatus,
                        'origen_modulo' => 'migracion',
                        'origen_legacy' => 'gastos_operadores',
                        'origen_legacy_id' => $gOperador->id,
                        'user_id' => null,
                    ]);

                    // Crear Partida
                    DB::table('gasto_partidas')->insert([
                        'gasto_id' => $gasto->id,
                        'categoria_gasto_id' => $catId,
                        'gasto_concepto_id' => $conceptoId,
                        'concepto' => "{$tipoGasto} - Operador Migrado #" . $gOperador->id,
                        'monto' => $monto,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Crear Imputación al Operador
                    if (!empty($gOperador->id_operador)) {
                        GastoImputacion::create([
                            'gasto_id' => $gasto->id,
                            'fecha_imputacion' => $fecha,
                            'tipo_imputacion' => 'operador',
                            'imputable_type' => 'App\Models\Operador',
                            'imputable_id' => $gOperador->id,
                            'monto_imputado' => $monto,
                            'origen' => 'directo',
                        ]);

                        // Vincular a nivel de vinculos (Operador)
                        GastoVinculo::create([
                            'gasto_id' => $gasto->id,
                            'tipo_vinculo' => 'operador',
                            'vinculable_type' => 'App\Models\Operador',
                            'vinculable_id' => $gOperador->id,
                        ]);
                    }

                    // Vincular a Cotización/Asignación si existen
                    if (!empty($gOperador->id_cotizacion)) {
                        GastoVinculo::create([
                            'gasto_id' => $gasto->id,
                            'tipo_vinculo' => 'cotizacion',
                            'vinculable_type' => 'App\Models\Cotizaciones',
                            'vinculable_id' => $gOperador->id_cotizacion,
                        ]);
                    }

                    if (!empty($gOperador->id_asignacion)) {
                        GastoVinculo::create([
                            'gasto_id' => $gasto->id,
                            'tipo_vinculo' => 'asignacion',
                            'vinculable_type' => 'App\Models\Asignaciones',
                            'vinculable_id' => $gOperador->id_asignacion,
                        ]);
                    }

                    // Crear pago si está pagado
                    if ($estatus === 'pagado') {
                        GastoPago::create([
                            'gasto_id' => $gasto->id,
                            'cuenta_bancaria_id' => $gOperador->id_banco,
                            'fecha_pago' => $fecha,
                            'monto' => $monto,
                            'comprobante' => $gOperador->comprobante,
                            'estatus' => 'aplicado',
                        ]);
                    }
                });

                $countOpe++;
            }
            $this->info("✔ Gastos de operadores migrados: {$countOpe}");
        }

        $this->info('✅ Proceso de migración finalizado exitosamente.');
    }
}
