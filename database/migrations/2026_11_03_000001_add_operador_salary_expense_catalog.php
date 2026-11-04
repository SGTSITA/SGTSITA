<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $now = now();
            $sueldosCategoriaId = $this->categoriaId('Sueldos de Operadores', $now);
            $anticiposCategoriaId = $this->categoriaId('Anticipos a Operadores por Comprobar', $now);
            $viaticosCategoriaId = $this->categoriaId('Gastos de Viaje de Operadores', $now);

            if (Schema::hasTable('gasto_conceptos')) {
                $this->concepto($sueldosCategoriaId, 'Sueldo de Operador', 'SUELDO_OPERADOR', true, $now);
                $this->concepto($anticiposCategoriaId, 'Anticipo de viaje por comprobar', 'ANTICIPO_VIAJE_OPERADOR', false, $now);
                $this->concepto($viaticosCategoriaId, 'Gasto de viaje comprobado', 'GASTO_VIAJE_COMPROBADO', true, $now);
            }

            if (Schema::hasTable('cat_cuentas_contables')) {
                $padre = DB::table('cat_cuentas_contables')->where('codigo', '5.1.1')->whereNull('id_empresa')->first();
                if ($padre && !DB::table('cat_cuentas_contables')->where('codigo', '5.1.1.4')->whereNull('id_empresa')->exists()) {
                    DB::table('cat_cuentas_contables')->insert([
                        'id_empresa' => null,
                        'codigo' => '5.1.1.4',
                        'nombre' => 'Sueldos y Salarios de Operadores',
                        'nivel' => 4,
                        'cuenta_padre_id' => $padre->id,
                        'rubro' => 'gastos',
                        'naturaleza' => 'deudora',
                        'es_afectable' => true,
                        'activo' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
                $padreViaticos = DB::table('cat_cuentas_contables')->where('codigo', '5.1.3')->whereNull('id_empresa')->first();
                if ($padreViaticos && !DB::table('cat_cuentas_contables')->where('codigo', '5.1.3.7')->whereNull('id_empresa')->exists()) {
                    DB::table('cat_cuentas_contables')->insert([
                        'id_empresa' => null,
                        'codigo' => '5.1.3.7',
                        'nombre' => 'Servicios de Traslado y Viáticos de Operadores',
                        'nivel' => 4,
                        'cuenta_padre_id' => $padreViaticos->id,
                        'rubro' => 'gastos',
                        'naturaleza' => 'deudora',
                        'es_afectable' => true,
                        'activo' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            if (Schema::hasTable('guia_contabilizadora')) {
                foreach ([
                    ['evento' => 'SUELDO_OPERADOR_DEVENGADO', 'regla_dinamica' => 'sueldo_operador_devengado', 'descripcion_asiento' => 'Devengo de sueldo de operador por viaje planeado'],
                    ['evento' => 'GASTO_COMPROBADO_OPERADOR', 'regla_dinamica' => 'gasto_operador_comprobado', 'descripcion_asiento' => 'Aplicación de gasto comprobado de operador contra anticipo'],
                    ['evento' => 'GASTO_EXCEDENTE_OPERADOR', 'regla_dinamica' => 'gasto_operador_excedente', 'descripcion_asiento' => 'Gasto comprobado excedente por pagar a operador'],
                    ['evento' => 'ANTICIPO_OPERADOR', 'regla_dinamica' => 'anticipo_operador', 'descripcion_asiento' => 'Anticipo de viaje por comprobar entregado a operador'],
                    ['evento' => 'LIQUIDACION_OPERADOR', 'regla_dinamica' => 'liquidacion_operador', 'descripcion_asiento' => 'Liquidación de sueldo, anticipos y deducciones de operador'],
                ] as $regla) {
                    DB::table('guia_contabilizadora')->updateOrInsert(
                        ['id_empresa' => null, 'evento' => $regla['evento'], 'subtipo' => null],
                        array_merge($regla, [
                            'tipo_poliza' => 'diario',
                            'cuenta_cargo_id' => null,
                            'cuenta_abono_id' => null,
                            'activo' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ])
                    );
                }
            }

            if (Schema::hasTable('balance_general_conceptos_cuentas')) {
                DB::table('balance_general_conceptos_cuentas')
                    ->where('cuenta_codigo_prefijo', '1.1.1.1%')
                    ->where('concepto', 'INVERSIONES')
                    ->update(['concepto' => 'EFECTIVO Y CAJA', 'updated_at' => $now]);
            }

            if (Schema::hasTable('asignaciones')) {
                DB::table('asignaciones')
                    ->where('id', 2493)
                    ->where(function ($q) {
                        $q->whereNull('tipo_contrato')->orWhere('tipo_contrato', '');
                    })
                    ->update(['tipo_contrato' => 'Propio']);
            }
        });
    }

    private function categoriaId(string $nombre, $now): ?int
    {
        if (!Schema::hasTable('categorias_gastos')) {
            return null;
        }

        $id = DB::table('categorias_gastos')->where('categoria', $nombre)->value('id');
        if ($id) {
            DB::table('categorias_gastos')->where('id', $id)->update(['is_active' => true, 'updated_at' => $now]);
            return (int) $id;
        }

        return (int) DB::table('categorias_gastos')->insertGetId([
            'categoria' => $nombre,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function concepto(?int $categoriaId, string $nombre, string $clave, bool $afectaUtilidad, $now): void
    {
        if (!$categoriaId) {
            return;
        }

        DB::table('gasto_conceptos')->updateOrInsert(
            ['categoria_gasto_id' => $categoriaId, 'clave' => $clave],
            [
                'nombre' => $nombre,
                'tipo_default' => 'operador',
                'afecta_utilidad' => $afectaUtilidad,
                'permite_diferir' => false,
                'es_recuperable_cliente' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        // Es una migración de catálogo contable. Se conserva el catálogo y las reglas
        // para evitar romper gastos, pólizas o mapeos creados después del despliegue.
    }
};
