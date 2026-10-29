<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BalanceGeneralConceptoCuenta;

class BalanceGeneralConceptosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $conceptos = [
            // ==========================================
            // COLUMNA 1: ACTIVO (Izquierda)
            // ==========================================
            [
                'seccion' => 'activo',
                'concepto' => 'BANCOS',
                'cuenta_codigo_prefijo' => '1.1.1.2%',
                'columna' => 1,
                'orden' => 10,
                'es_calculado' => false,
            ],
            [
                'seccion' => 'activo',
                'concepto' => 'INVERSIONES',
                'cuenta_codigo_prefijo' => '1.1.1.1%',
                'columna' => 1,
                'orden' => 20,
                'es_calculado' => false,
            ],
            [
                'seccion' => 'activo',
                'concepto' => 'CXC',
                'cuenta_codigo_prefijo' => '1.1.2.1%',
                'columna' => 1,
                'orden' => 30,
                'es_calculado' => false,
            ],
            [
                'seccion' => 'activo',
                'concepto' => 'PRESTAMOS',
                'cuenta_codigo_prefijo' => '1.1.2.3.001%',
                'columna' => 1,
                'orden' => 40,
                'es_calculado' => false,
            ],
            [
                'seccion' => 'activo',
                'concepto' => 'GASTOS POR COMPROBAR',
                'cuenta_codigo_prefijo' => '1.1.2.3.002%',
                'columna' => 1,
                'orden' => 50,
                'es_calculado' => false,
            ],
            [
                'seccion' => 'activo',
                'concepto' => 'ANTICIPOS A SOCIOS',
                'cuenta_codigo_prefijo' => '1.1.2.3.003%',
                'columna' => 1,
                'orden' => 55,
                'es_calculado' => false,
            ],
            [
                'seccion' => 'activo',
                'concepto' => 'EQUIPO DE TRANSPORTE',
                'cuenta_codigo_prefijo' => '1.2.4.1.001%',
                'columna' => 1,
                'orden' => 60,
                'es_calculado' => false,
            ],
            [
                'seccion' => 'activo',
                'concepto' => 'CHASIS',
                'cuenta_codigo_prefijo' => '1.2.4.1.002%',
                'columna' => 1,
                'orden' => 70,
                'es_calculado' => false,
            ],
            [
                'seccion' => 'activo',
                'concepto' => 'DEUDORES DIVERSOS',
                'cuenta_codigo_prefijo' => '1.1.2.3.004%',
                'columna' => 1,
                'orden' => 80,
                'es_calculado' => false,
            ],
            [
                'seccion' => 'activo',
                'concepto' => 'GASTOS PAGADOS POR ANTIC.',
                'cuenta_codigo_prefijo' => '1.1.5.1%',
                'columna' => 1,
                'orden' => 90,
                'es_calculado' => false,
            ],

            // ==========================================
            // COLUMNA 2: PASIVO Y PATRIMONIO (Derecha)
            // ==========================================
            // Pasivo
            [
                'seccion' => 'pasivo',
                'concepto' => 'PRESTAMOS',
                'cuenta_codigo_prefijo' => '2.1.3%',
                'columna' => 2,
                'orden' => 10,
                'es_calculado' => false,
            ],
            [
                'seccion' => 'pasivo',
                'concepto' => 'CUENTAS POR PAGAR',
                'cuenta_codigo_prefijo' => '2.1.1%',
                'columna' => 2,
                'orden' => 20,
                'es_calculado' => false,
            ],
            [
                'seccion' => 'pasivo',
                'concepto' => 'ACREEDORES',
                'cuenta_codigo_prefijo' => '2.1.9.1.001%',
                'columna' => 2,
                'orden' => 30,
                'es_calculado' => false,
            ],
            // Patrimonio
            [
                'seccion' => 'patrimonio',
                'concepto' => 'Resultado del ej anterior',
                'cuenta_codigo_prefijo' => '3.2.2%',
                'columna' => 2,
                'orden' => 40,
                'es_calculado' => false,
            ],
            [
                'seccion' => 'patrimonio',
                'concepto' => 'Resultado del ejercicio',
                'cuenta_codigo_prefijo' => '3.2.1%',
                'columna' => 2,
                'orden' => 50,
                'es_calculado' => true,
            ],
            [
                'seccion' => 'patrimonio',
                'concepto' => 'Reparto de Utilidades',
                'cuenta_codigo_prefijo' => '2.1.9.1.002%',
                'columna' => 2,
                'orden' => 60,
                'es_calculado' => false,
            ],

            // ==========================================
            // COLUMNA 3: ESTADO DE RESULTADOS (Pie)
            // ==========================================
            [
                'seccion' => 'estado_resultados',
                'concepto' => 'INGRESOS',
                'cuenta_codigo_prefijo' => '4.1.1%',
                'columna' => 3,
                'orden' => 10,
                'es_calculado' => false,
            ],
            [
                'seccion' => 'estado_resultados',
                'concepto' => 'intereses',
                'cuenta_codigo_prefijo' => '4.3.1%',
                'columna' => 3,
                'orden' => 20,
                'es_calculado' => false,
            ],
            [
                'seccion' => 'estado_resultados',
                'concepto' => 'GASTOS',
                'cuenta_codigo_prefijo' => '5.1%',
                'columna' => 3,
                'orden' => 30,
                'es_calculado' => false,
            ],
            [
                'seccion' => 'estado_resultados',
                'concepto' => 'UTILIDAD',
                'cuenta_codigo_prefijo' => null,
                'columna' => 3,
                'orden' => 40,
                'es_calculado' => true,
            ],
        ];

        foreach ($conceptos as $row) {
            BalanceGeneralConceptoCuenta::updateOrCreate(
                [
                    'id_empresa' => null, // Plantilla global por defecto
                    'seccion' => $row['seccion'],
                    'concepto' => $row['concepto'],
                ],
                array_merge($row, ['activo' => true])
            );
        }
    }
}
