<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GastoConceptosSeeder extends Seeder
{
    public function run()
    {
        $conceptos = [
            // 1 Combustible
            ['categoria_gasto_id' => 1, 'nombre' => 'Diésel', 'clave' => 'DSL', 'tipo_default' => 'operador'],
            ['categoria_gasto_id' => 1, 'nombre' => 'Gasolina', 'clave' => 'GSL', 'tipo_default' => 'general'],
            
            // 2 Seguros
            ['categoria_gasto_id' => 2, 'nombre' => 'Seguro de Unidad', 'clave' => 'SEG_UND', 'tipo_default' => 'unidad'],
            ['categoria_gasto_id' => 2, 'nombre' => 'Seguro de Carga', 'clave' => 'SEG_CRG', 'tipo_default' => 'viaje'],

            // 3 Comisiones Bancarias
            ['categoria_gasto_id' => 3, 'nombre' => 'Comisión por Transferencia', 'clave' => 'COM_TRF', 'tipo_default' => 'general'],
            ['categoria_gasto_id' => 3, 'nombre' => 'Manejo de Cuenta', 'clave' => 'COM_CTA', 'tipo_default' => 'general'],

            // 4 Impuestos
            ['categoria_gasto_id' => 4, 'nombre' => 'IVA', 'clave' => 'IMP_IVA', 'tipo_default' => 'general'],
            ['categoria_gasto_id' => 4, 'nombre' => 'Retenciones', 'clave' => 'IMP_RET', 'tipo_default' => 'general'],

            // 5 Comisiones de venta
            ['categoria_gasto_id' => 5, 'nombre' => 'Comisión por Venta', 'clave' => 'COM_VTA', 'tipo_default' => 'general'],

            // 6 Alimentos
            ['categoria_gasto_id' => 6, 'nombre' => 'Alimentos Operador', 'clave' => 'ALI_OPE', 'tipo_default' => 'operador'],

            // 7 Peajes
            ['categoria_gasto_id' => 7, 'nombre' => 'Casetas (TAG/IAVE)', 'clave' => 'PEA_TAG', 'tipo_default' => 'viaje'],
            ['categoria_gasto_id' => 7, 'nombre' => 'Casetas Efectivo', 'clave' => 'PEA_EFE', 'tipo_default' => 'viaje'],

            // 8 Hospedaje
            ['categoria_gasto_id' => 8, 'nombre' => 'Hotel Operador', 'clave' => 'HOS_HOT', 'tipo_default' => 'operador'],

            // 9 Transporte/Vuelos
            ['categoria_gasto_id' => 9, 'nombre' => 'Pasajes de Autobús', 'clave' => 'TRA_BUS', 'tipo_default' => 'general'],
            ['categoria_gasto_id' => 9, 'nombre' => 'Boletos de Avión', 'clave' => 'TRA_AVN', 'tipo_default' => 'general'],
            ['categoria_gasto_id' => 9, 'nombre' => 'Taxi/Uber', 'clave' => 'TRA_TXI', 'tipo_default' => 'general'],

            // 10 Permisos/Licencias
            ['categoria_gasto_id' => 10, 'nombre' => 'Licencia Federal', 'clave' => 'PER_LIC', 'tipo_default' => 'operador'],
            ['categoria_gasto_id' => 10, 'nombre' => 'Permiso Especial de Carga', 'clave' => 'PER_CRG', 'tipo_default' => 'viaje'],

            // 11 Serv Mecanico
            ['categoria_gasto_id' => 11, 'nombre' => 'Mantenimiento Preventivo', 'clave' => 'MEC_PRE', 'tipo_default' => 'unidad'],
            ['categoria_gasto_id' => 11, 'nombre' => 'Mantenimiento Correctivo', 'clave' => 'MEC_COR', 'tipo_default' => 'unidad'],
            ['categoria_gasto_id' => 11, 'nombre' => 'Llantas / Pinchaduras', 'clave' => 'MEC_LLA', 'tipo_default' => 'unidad'],

            // 12 Otros
            ['categoria_gasto_id' => 12, 'nombre' => 'Gastos Menores', 'clave' => 'OTR_MIN', 'tipo_default' => 'general'],
            ['categoria_gasto_id' => 12, 'nombre' => 'Papelería / Artículos de Oficina', 'clave' => 'OTR_PAP', 'tipo_default' => 'general'],

            // 13 Refacciones
            ['categoria_gasto_id' => 13, 'nombre' => 'Compra de Refacciones', 'clave' => 'REF_CMP', 'tipo_default' => 'unidad'],
            ['categoria_gasto_id' => 13, 'nombre' => 'Aceites y Filtros', 'clave' => 'REF_ACE', 'tipo_default' => 'unidad'],
        ];

        foreach ($conceptos as $concepto) {
            DB::table('gasto_conceptos')->updateOrInsert(
                [
                    'categoria_gasto_id' => $concepto['categoria_gasto_id'],
                    'clave' => $concepto['clave']
                ],
                [
                    'nombre' => $concepto['nombre'],
                    'tipo_default' => $concepto['tipo_default'],
                    'afecta_utilidad' => 1,
                    'permite_diferir' => 0,
                    'es_recuperable_cliente' => 0,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
