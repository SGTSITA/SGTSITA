<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GuiaContabilizadora;
use App\Models\CatCuentaContable;

class GuiaContabilizadoraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cuentaIntereses = CatCuentaContable::where('codigo', '4.3.1.1')->first()?->id;
        $cuentaClientes = CatCuentaContable::where('codigo', '1.1.2.1.001')->first()?->id;
        $cuentaIngresosFletes = CatCuentaContable::where('codigo', '4.1.1.1')->first()?->id;
        $cuentaGastoAnticipado = CatCuentaContable::where('codigo', '1.1.5.1.099')->first()?->id;
        $cuentaResultado = CatCuentaContable::where('codigo', '3.2.1.1')->first()?->id;
        $cuentaRepartoSocios = CatCuentaContable::where('codigo', '2.1.9.1.002')->first()?->id;

        $reglas = [
            [
                'evento' => 'INTERES_BANCARIO',
                'subtipo' => null,
                'tipo_poliza' => 'ingreso',
                'cuenta_cargo_id' => null,
                'cuenta_abono_id' => $cuentaIntereses,
                'regla_dinamica' => 'cuenta_banco',
                'descripcion_asiento' => 'Rendimientos e Intereses Bancarios del Periodo',
            ],
            [
                'evento' => 'GASTO_OPERATIVO',
                'subtipo' => 'directo',
                'tipo_poliza' => 'egreso',
                'cuenta_cargo_id' => null,
                'cuenta_abono_id' => null,
                'regla_dinamica' => 'cuenta_categoria_gasto_y_banco',
                'descripcion_asiento' => 'Pago de Gasto Operativo de Transporte',
            ],
            [
                'evento' => 'GASTO_ANTICIPADO',
                'subtipo' => 'diferido',
                'tipo_poliza' => 'egreso',
                'cuenta_cargo_id' => $cuentaGastoAnticipado,
                'cuenta_abono_id' => null,
                'regla_dinamica' => 'cuenta_banco',
                'descripcion_asiento' => 'Registro de Gasto Pagado por Anticipado',
            ],
            [
                'evento' => 'COBRO_CXC',
                'subtipo' => null,
                'tipo_poliza' => 'ingreso',
                'cuenta_cargo_id' => null,
                'cuenta_abono_id' => $cuentaClientes,
                'regla_dinamica' => 'cuenta_banco',
                'descripcion_asiento' => 'Cobro de Facturas y Fletes CXC',
            ],
            [
                'evento' => 'PROVISION_VIAJE',
                'subtipo' => null,
                'tipo_poliza' => 'diario',
                'cuenta_cargo_id' => $cuentaClientes,
                'cuenta_abono_id' => $cuentaIngresosFletes,
                'regla_dinamica' => null,
                'descripcion_asiento' => 'Provisión de Flete por Viaje Concluido',
            ],
            [
                'evento' => 'REPARTO_SOCIOS',
                'subtipo' => null,
                'tipo_poliza' => 'diario',
                'cuenta_cargo_id' => $cuentaResultado,
                'cuenta_abono_id' => $cuentaRepartoSocios,
                'regla_dinamica' => null,
                'descripcion_asiento' => 'Provisión de Utilidad y Reparto a Socios del Periodo',
            ],
            [
                'evento' => 'PAGO_SOCIOS',
                'subtipo' => null,
                'tipo_poliza' => 'egreso',
                'cuenta_cargo_id' => $cuentaRepartoSocios,
                'cuenta_abono_id' => null,
                'regla_dinamica' => 'cuenta_banco',
                'descripcion_asiento' => 'Pago Bancario de Utilidades a Socios',
            ],
            [
                'evento' => 'ANTICIPO_SOCIO',
                'subtipo' => null,
                'tipo_poliza' => 'egreso',
                'cuenta_cargo_id' => CatCuentaContable::where('codigo', '1.1.2.3.003')->first()?->id,
                'cuenta_abono_id' => null,
                'regla_dinamica' => 'cuenta_banco',
                'descripcion_asiento' => 'Anticipo / Adelanto a Socio a Cuenta de Utilidad',
            ],
            [
                'evento' => 'LIQUIDACION_SOCIOS_CON_ANTICIPO',
                'subtipo' => null,
                'tipo_poliza' => 'egreso',
                'cuenta_cargo_id' => $cuentaRepartoSocios,
                'cuenta_abono_id' => CatCuentaContable::where('codigo', '1.1.2.3.003')->first()?->id,
                'regla_dinamica' => 'liquidacion_con_anticipo_y_banco',
                'descripcion_asiento' => 'Liquidación de Utilidades a Socios con Amortización de Anticipos',
            ],
        ];

        foreach ($reglas as $regla) {
            GuiaContabilizadora::updateOrCreate(
                [
                    'id_empresa' => null, // Regla default global
                    'evento' => $regla['evento'],
                    'subtipo' => $regla['subtipo'],
                ],
                array_merge($regla, ['activo' => true])
            );
        }
    }
}
