<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CatCuentaContable;
use Illuminate\Support\Facades\DB;

class CatCuentasContablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cuentas = [
            // ==========================================
            // NIVEL 1: GÉNERO
            // ==========================================
            ['codigo' => '1', 'nombre' => 'ACTIVO', 'nivel' => 1, 'padre' => null, 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => false],
            ['codigo' => '2', 'nombre' => 'PASIVO', 'nivel' => 1, 'padre' => null, 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => false],
            ['codigo' => '3', 'nombre' => 'HACIENDA PÚBLICA / PATRIMONIO', 'nivel' => 1, 'padre' => null, 'rubro' => 'capital', 'naturaleza' => 'acreedora', 'es_afectable' => false],
            ['codigo' => '4', 'nombre' => 'INGRESOS Y OTROS BENEFICIOS', 'nivel' => 1, 'padre' => null, 'rubro' => 'ingresos', 'naturaleza' => 'acreedora', 'es_afectable' => false],
            ['codigo' => '5', 'nombre' => 'GASTOS Y OTRAS PÉRDIDAS', 'nivel' => 1, 'padre' => null, 'rubro' => 'gastos', 'naturaleza' => 'deudora', 'es_afectable' => false],

            // ==========================================
            // NIVEL 2: GRUPO
            // ==========================================
            ['codigo' => '1.1', 'nombre' => 'ACTIVO CIRCULANTE', 'nivel' => 2, 'padre' => '1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => false],
            ['codigo' => '1.2', 'nombre' => 'ACTIVO NO CIRCULANTE', 'nivel' => 2, 'padre' => '1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => false],

            ['codigo' => '2.1', 'nombre' => 'PASIVO CIRCULANTE', 'nivel' => 2, 'padre' => '2', 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => false],
            ['codigo' => '2.2', 'nombre' => 'PASIVO NO CIRCULANTE', 'nivel' => 2, 'padre' => '2', 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => false],

            ['codigo' => '3.1', 'nombre' => 'PATRIMONIO CONTRIBUIDO', 'nivel' => 2, 'padre' => '3', 'rubro' => 'capital', 'naturaleza' => 'acreedora', 'es_afectable' => false],
            ['codigo' => '3.2', 'nombre' => 'PATRIMONIO GENERADO', 'nivel' => 2, 'padre' => '3', 'rubro' => 'capital', 'naturaleza' => 'acreedora', 'es_afectable' => false],

            ['codigo' => '4.1', 'nombre' => 'INGRESOS DE GESTIÓN', 'nivel' => 2, 'padre' => '4', 'rubro' => 'ingresos', 'naturaleza' => 'acreedora', 'es_afectable' => false],
            ['codigo' => '4.3', 'nombre' => 'OTROS INGRESOS Y BENEFICIOS', 'nivel' => 2, 'padre' => '4', 'rubro' => 'ingresos', 'naturaleza' => 'acreedora', 'es_afectable' => false],

            ['codigo' => '5.1', 'nombre' => 'GASTOS DE FUNCIONAMIENTO', 'nivel' => 2, 'padre' => '5', 'rubro' => 'gastos', 'naturaleza' => 'deudora', 'es_afectable' => false],
            ['codigo' => '5.5', 'nombre' => 'OTROS GASTOS Y PÉRDIDAS EXTRAORDINARIAS', 'nivel' => 2, 'padre' => '5', 'rubro' => 'gastos', 'naturaleza' => 'deudora', 'es_afectable' => false],

            // ==========================================
            // NIVEL 3: RUBRO
            // ==========================================
            // Activo Circulante
            ['codigo' => '1.1.1', 'nombre' => 'Efectivo y Equivalentes', 'nivel' => 3, 'padre' => '1.1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => false],
            ['codigo' => '1.1.2', 'nombre' => 'Derechos a Recibir Efectivo o Equivalentes (CXC)', 'nivel' => 3, 'padre' => '1.1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => false],
            ['codigo' => '1.1.3', 'nombre' => 'Derechos a Recibir Bienes o Servicios', 'nivel' => 3, 'padre' => '1.1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => false],
            ['codigo' => '1.1.5', 'nombre' => 'Otros Activos Circulantes (Anticipos y Diferidos)', 'nivel' => 3, 'padre' => '1.1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => false],

            // Activo No Circulante
            ['codigo' => '1.2.4', 'nombre' => 'Bienes Muebles (Equipo de Transporte y Chasis)', 'nivel' => 3, 'padre' => '1.2', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => false],

            // Pasivo Circulante
            ['codigo' => '2.1.1', 'nombre' => 'Cuentas por Pagar a Corto Plazo (Proveedores/CXP)', 'nivel' => 3, 'padre' => '2.1', 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => false],
            ['codigo' => '2.1.3', 'nombre' => 'Préstamos y Porción a Corto Plazo', 'nivel' => 3, 'padre' => '2.1', 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => false],
            ['codigo' => '2.1.9', 'nombre' => 'Otros Pasivos a Corto Plazo (Acreedores y Reparto)', 'nivel' => 3, 'padre' => '2.1', 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => false],

            // Pasivo No Circulante
            ['codigo' => '2.2.3', 'nombre' => 'Préstamos por Pagar a Largo Plazo', 'nivel' => 3, 'padre' => '2.2', 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => false],

            // Patrimonio
            ['codigo' => '3.2.1', 'nombre' => 'Resultado del Ejercicio', 'nivel' => 3, 'padre' => '3.2', 'rubro' => 'capital', 'naturaleza' => 'acreedora', 'es_afectable' => false],
            ['codigo' => '3.2.2', 'nombre' => 'Resultado de Ejercicios Anteriores', 'nivel' => 3, 'padre' => '3.2', 'rubro' => 'capital', 'naturaleza' => 'acreedora', 'es_afectable' => false],

            // Ingresos
            ['codigo' => '4.1.1', 'nombre' => 'Ingresos por Servicios de Transporte', 'nivel' => 3, 'padre' => '4.1', 'rubro' => 'ingresos', 'naturaleza' => 'acreedora', 'es_afectable' => false],
            ['codigo' => '4.3.1', 'nombre' => 'Intereses Ganados e Ingresos Financieros', 'nivel' => 3, 'padre' => '4.3', 'rubro' => 'ingresos', 'naturaleza' => 'acreedora', 'es_afectable' => false],

            // Gastos
            ['codigo' => '5.1.1', 'nombre' => 'Gastos Operativos de Transporte', 'nivel' => 3, 'padre' => '5.1', 'rubro' => 'gastos', 'naturaleza' => 'deudora', 'es_afectable' => false],
            ['codigo' => '5.1.2', 'nombre' => 'Mantenimiento y Refacciones', 'nivel' => 3, 'padre' => '5.1', 'rubro' => 'gastos', 'naturaleza' => 'deudora', 'es_afectable' => false],
            ['codigo' => '5.1.3', 'nombre' => 'Gastos Generales y Administrativos', 'nivel' => 3, 'padre' => '5.1', 'rubro' => 'gastos', 'naturaleza' => 'deudora', 'es_afectable' => false],

            // ==========================================
            // NIVEL 4 Y 5: CUENTAS Y SUBCUENTAS ANALÍTICAS
            // ==========================================
            // Bancos
            ['codigo' => '1.1.1.2', 'nombre' => 'Bancos / Tesorería', 'nivel' => 4, 'padre' => '1.1.1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => false],
            ['codigo' => '1.1.1.2.001', 'nombre' => 'BBVA Bancomer', 'nivel' => 5, 'padre' => '1.1.1.2', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '1.1.1.2.002', 'nombre' => 'Banco Azteca', 'nivel' => 5, 'padre' => '1.1.1.2', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '1.1.1.2.003', 'nombre' => 'Banamex', 'nivel' => 5, 'padre' => '1.1.1.2', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '1.1.1.2.004', 'nombre' => 'Santander', 'nivel' => 5, 'padre' => '1.1.1.2', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '1.1.1.2.005', 'nombre' => 'Banorte', 'nivel' => 5, 'padre' => '1.1.1.2', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],

            // CXC
            ['codigo' => '1.1.2.1', 'nombre' => 'Clientes CXC', 'nivel' => 4, 'padre' => '1.1.2', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => false],
            ['codigo' => '1.1.2.1.001', 'nombre' => 'Clientes Nacionales por Cobrar', 'nivel' => 5, 'padre' => '1.1.2.1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],

            // Préstamos y Gastos por Comprobar / Anticipos
            ['codigo' => '1.1.2.3', 'nombre' => 'Deudores Diversos por Cobrar', 'nivel' => 4, 'padre' => '1.1.2', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => false],
            ['codigo' => '1.1.2.3.001', 'nombre' => 'Préstamos Otorgados a Unidades / Operadores', 'nivel' => 5, 'padre' => '1.1.2.3', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '1.1.2.3.002', 'nombre' => 'Gastos por Comprobar', 'nivel' => 5, 'padre' => '1.1.2.3', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '1.1.2.3.003', 'nombre' => 'Anticipos a Socios por Liquidar', 'nivel' => 5, 'padre' => '1.1.2.3', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],

            // Gastos Pagados por Anticipado
            ['codigo' => '1.1.5.1', 'nombre' => 'Gastos Pagados por Anticipado', 'nivel' => 4, 'padre' => '1.1.5', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => false],
            ['codigo' => '1.1.5.1.001', 'nombre' => 'Software y Licencias', 'nivel' => 5, 'padre' => '1.1.5.1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '1.1.5.1.002', 'nombre' => 'Alquiler y Patios', 'nivel' => 5, 'padre' => '1.1.5.1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '1.1.5.1.003', 'nombre' => 'Placas, Permisos y Gestoría', 'nivel' => 5, 'padre' => '1.1.5.1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '1.1.5.1.004', 'nombre' => 'Cuotas de Administración', 'nivel' => 5, 'padre' => '1.1.5.1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '1.1.5.1.005', 'nombre' => 'Servicios GPS y Monitoreo', 'nivel' => 5, 'padre' => '1.1.5.1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '1.1.5.1.099', 'nombre' => 'Otros Gastos Pagados por Anticipado', 'nivel' => 5, 'padre' => '1.1.5.1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],

            // Equipo de Transporte y Chasis
            ['codigo' => '1.2.4.1', 'nombre' => 'Vehículos y Equipo de Transporte', 'nivel' => 4, 'padre' => '1.2.4', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => false],
            ['codigo' => '1.2.4.1.001', 'nombre' => 'Tractocamiones / Camiones', 'nivel' => 5, 'padre' => '1.2.4.1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '1.2.4.1.002', 'nombre' => 'Remolques y Chasis', 'nivel' => 5, 'padre' => '1.2.4.1', 'rubro' => 'activo', 'naturaleza' => 'deudora', 'es_afectable' => true],

            // CXP y Proveedores
            ['codigo' => '2.1.1.2', 'nombre' => 'Proveedores CXP', 'nivel' => 4, 'padre' => '2.1.1', 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => false],
            ['codigo' => '2.1.1.2.001', 'nombre' => 'Proveedores de Fletes Subcontratados', 'nivel' => 5, 'padre' => '2.1.1.2', 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => true],
            ['codigo' => '2.1.1.2.002', 'nombre' => 'Talleres, Refacciones y Mantenimiento (SAGAZA, etc.)', 'nivel' => 5, 'padre' => '2.1.1.2', 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => true],
            ['codigo' => '2.1.1.2.003', 'nombre' => 'Chasis por Pagar', 'nivel' => 5, 'padre' => '2.1.1.2', 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => true],

            // Préstamos por Pagar
            ['codigo' => '2.1.3.1', 'nombre' => 'Préstamos por Pagar a Corto Plazo', 'nivel' => 4, 'padre' => '2.1.3', 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => false],
            ['codigo' => '2.1.3.1.001', 'nombre' => 'Préstamos Recibidos', 'nivel' => 5, 'padre' => '2.1.3.1', 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => true],

            // Acreedores Diversos y Reparto
            ['codigo' => '2.1.9.1', 'nombre' => 'Acreedores Diversos', 'nivel' => 4, 'padre' => '2.1.9', 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => false],
            ['codigo' => '2.1.9.1.001', 'nombre' => 'Acreedores Diversos Corto Plazo', 'nivel' => 5, 'padre' => '2.1.9.1', 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => true],
            ['codigo' => '2.1.9.1.002', 'nombre' => 'Reparto de Utilidades por Pagar a Socios', 'nivel' => 5, 'padre' => '2.1.9.1', 'rubro' => 'pasivo', 'naturaleza' => 'acreedora', 'es_afectable' => true],

            // Patrimonio Generado
            ['codigo' => '3.2.1.1', 'nombre' => 'Resultado del Ejercicio', 'nivel' => 4, 'padre' => '3.2.1', 'rubro' => 'capital', 'naturaleza' => 'acreedora', 'es_afectable' => true],
            ['codigo' => '3.2.2.1', 'nombre' => 'Resultado de Ejercicios Anteriores', 'nivel' => 4, 'padre' => '3.2.2', 'rubro' => 'capital', 'naturaleza' => 'acreedora', 'es_afectable' => true],

            // Ingresos
            ['codigo' => '4.1.1.1', 'nombre' => 'Ingresos por Fletes y Servicios', 'nivel' => 4, 'padre' => '4.1.1', 'rubro' => 'ingresos', 'naturaleza' => 'acreedora', 'es_afectable' => true],
            ['codigo' => '4.3.1.1', 'nombre' => 'Intereses Ganados de Cuentas Bancarias', 'nivel' => 4, 'padre' => '4.3.1', 'rubro' => 'ingresos', 'naturaleza' => 'acreedora', 'es_afectable' => true],

            // Gastos
            ['codigo' => '5.1.1.1', 'nombre' => 'Combustible (Diesel)', 'nivel' => 4, 'padre' => '5.1.1', 'rubro' => 'gastos', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '5.1.1.2', 'nombre' => 'Casetas y Peajes', 'nivel' => 4, 'padre' => '5.1.1', 'rubro' => 'gastos', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '5.1.1.3', 'nombre' => 'Maniobras y Burreo', 'nivel' => 4, 'padre' => '5.1.1', 'rubro' => 'gastos', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '5.1.2.1', 'nombre' => 'Mantenimiento, Llantas y Reparaciones', 'nivel' => 4, 'padre' => '5.1.2', 'rubro' => 'gastos', 'naturaleza' => 'deudora', 'es_afectable' => true],
            ['codigo' => '5.1.3.1', 'nombre' => 'Gastos Administrativos y Generales', 'nivel' => 4, 'padre' => '5.1.3', 'rubro' => 'gastos', 'naturaleza' => 'deudora', 'es_afectable' => true],
        ];

        // Mapeo en memoria para asignar cuenta_padre_id dinámicamente
        $idsPorCodigo = [];

        foreach ($cuentas as $c) {
            $padreId = null;
            if (!empty($c['padre']) && isset($idsPorCodigo[$c['padre']])) {
                $padreId = $idsPorCodigo[$c['padre']];
            }

            $model = CatCuentaContable::updateOrCreate(
                [
                    'codigo' => $c['codigo'],
                    'id_empresa' => null, // Globales compartidas
                ],
                [
                    'nombre' => $c['nombre'],
                    'nivel' => $c['nivel'],
                    'cuenta_padre_id' => $padreId,
                    'rubro' => $c['rubro'],
                    'naturaleza' => $c['naturaleza'],
                    'es_afectable' => $c['es_afectable'],
                    'activo' => true,
                ]
            );

            $idsPorCodigo[$c['codigo']] = $model->id;
        }
    }
}
