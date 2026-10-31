<?php

namespace Database\Seeders;

use App\Models\CatCuentaContable;
use App\Models\EmpresaCuentaConfig;
use App\Services\CuentaContableResolverService;
use Illuminate\Database\Seeder;

class EmpresaCuentasConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mapeoRoles = [
            CuentaContableResolverService::ROL_CAJA_OPERATIVA       => ['codigo' => '1.1.1.1.001', 'desc' => 'Caja General / Fondos Operativos'],
            CuentaContableResolverService::ROL_BANCOS_DEFAULT       => ['codigo' => '1.1.1.2.001', 'desc' => 'Bancos e Instituciones Financieras'],
            CuentaContableResolverService::ROL_CLIENTES_CXC         => ['codigo' => '1.1.2.1.001', 'desc' => 'Clientes Nacionales de Transporte'],
            CuentaContableResolverService::ROL_PROVEEDORES_CXP      => ['codigo' => '2.1.1.2.001', 'desc' => 'Proveedores Nacionales de Servicios y Bienes'],
            CuentaContableResolverService::ROL_PRESTAMOS_OPERADORES => ['codigo' => '1.1.2.3.001', 'desc' => 'Préstamos y Anticipos a Operadores'],
            CuentaContableResolverService::ROL_ANTICIPOS_SOCIOS     => ['codigo' => '1.1.2.3.003', 'desc' => 'Anticipos a Permisionarios / Socios'],
            CuentaContableResolverService::ROL_DEUDORES_DIVERSOS    => ['codigo' => '1.1.2.3.004', 'desc' => 'Otros Deudores Diversos / Gastos por Comprobar'],
            CuentaContableResolverService::ROL_PASIVO_SOCIOS        => ['codigo' => '2.1.9.1.002', 'desc' => 'Liquidaciones Pendientes a Permisionarios'],
            CuentaContableResolverService::ROL_CAPITAL_PATRIMONIO   => ['codigo' => '3.1.1.1.001', 'desc' => 'Patrimonio Contribuido'],
            CuentaContableResolverService::ROL_RESULTADO_EJERCICIO  => ['codigo' => '3.2.1.1',     'desc' => 'Resultado del Ejercicio Actual'],
            CuentaContableResolverService::ROL_RESULTADO_ANTERIORES => ['codigo' => '3.2.2.1',     'desc' => 'Resultado de Ejercicios Anteriores'],
            CuentaContableResolverService::ROL_INGRESOS_OPERATIVOS  => ['codigo' => '4.1.1.1',     'desc' => 'Ingresos por Fletes y Servicios'],
            CuentaContableResolverService::ROL_INGRESOS_INTERESES   => ['codigo' => '4.3.1.1',     'desc' => 'Intereses Ganados en Inversiones Bancarias'],
            CuentaContableResolverService::ROL_GASTOS_ADMIN         => ['codigo' => '5.1.3.1',     'desc' => 'Gastos Administrativos y Generales'],
            CuentaContableResolverService::ROL_GASTOS_COMBUSTIBLE   => ['codigo' => '5.1.1.1',     'desc' => 'Combustibles y Lubricantes'],
            CuentaContableResolverService::ROL_GASTOS_CASETAS       => ['codigo' => '5.1.1.2',     'desc' => 'Peajes y Casetas de Autopista'],
            CuentaContableResolverService::ROL_GASTOS_MANIOBRAS     => ['codigo' => '5.1.1.3',     'desc' => 'Maniobras y Burreo'],
            CuentaContableResolverService::ROL_GASTOS_MANTENIMIENTO => ['codigo' => '5.1.2.1',     'desc' => 'Mantenimiento y Refacciones de Unidades'],
        ];

        // 1. Sembrar configuración Global (id_empresa = NULL)
        foreach ($mapeoRoles as $rol => $info) {
            $cuenta = CatCuentaContable::where('codigo', $info['codigo'])->first();
            if (!$cuenta) {
                $cuenta = CatCuentaContable::where('codigo', 'like', $info['codigo'] . '%')
                    ->where('es_afectable', true)
                    ->first();
            }

            if ($cuenta) {
                EmpresaCuentaConfig::updateOrCreate(
                    [
                        'id_empresa' => null,
                        'rol_clave'  => $rol,
                    ],
                    [
                        'cuenta_contable_id' => $cuenta->id,
                        'descripcion'        => $info['desc'] . ' (Plantilla Global)',
                        'activo'             => true,
                    ]
                );
            }
        }

        // 2. Sembrar configuración específica para Empresa ID 8 (Oliva)
        foreach ($mapeoRoles as $rol => $info) {
            $cuenta = CatCuentaContable::where('codigo', $info['codigo'])->first();
            if (!$cuenta) {
                $cuenta = CatCuentaContable::where('codigo', 'like', $info['codigo'] . '%')
                    ->where('es_afectable', true)
                    ->first();
            }

            if ($cuenta) {
                EmpresaCuentaConfig::updateOrCreate(
                    [
                        'id_empresa' => 8,
                        'rol_clave'  => $rol,
                    ],
                    [
                        'cuenta_contable_id' => $cuenta->id,
                        'descripcion'        => $info['desc'] . ' (Empresa 8 - Oliva)',
                        'activo'             => true,
                    ]
                );
            }
        }
    }
}
