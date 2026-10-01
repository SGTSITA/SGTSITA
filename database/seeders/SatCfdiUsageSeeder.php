<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatCfdiUsageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cfdiUsages = [
            ['sat_code' => 'G01', 'uso_cfdi' => 'Adquisición de mercancías', 'is_active' => true],
            ['sat_code' => 'G02', 'uso_cfdi' => 'Devoluciones, descuentos o bonificaciones', 'is_active' => true],
            ['sat_code' => 'G03', 'uso_cfdi' => 'Gastos en general', 'is_active' => true],
            ['sat_code' => 'I01', 'uso_cfdi' => 'Construcciones', 'is_active' => true],
            ['sat_code' => 'I02', 'uso_cfdi' => 'Mobiliario y equipo de oficina por inversiones', 'is_active' => true],
            ['sat_code' => 'I03', 'uso_cfdi' => 'Equipo de transporte', 'is_active' => true],
            ['sat_code' => 'I04', 'uso_cfdi' => 'Equipo de cómputo y accesorios', 'is_active' => true],
            ['sat_code' => 'I05', 'uso_cfdi' => 'Dados, troqueles, moldes, matrices y herramental', 'is_active' => true],
            ['sat_code' => 'I06', 'uso_cfdi' => 'Comunicaciones telefónicas', 'is_active' => true],
            ['sat_code' => 'I07', 'uso_cfdi' => 'Comunicaciones satelitales', 'is_active' => true],
            ['sat_code' => 'I08', 'uso_cfdi' => 'Otra maquinaria y equipo', 'is_active' => true],
            ['sat_code' => 'D01', 'uso_cfdi' => 'Honorarios médicos, dentales y gastos hospitalarios', 'is_active' => true],
            ['sat_code' => 'D02', 'uso_cfdi' => 'Gastos médicos por incapacidad o discapacidad', 'is_active' => true],
            ['sat_code' => 'D03', 'uso_cfdi' => 'Gastos funerales', 'is_active' => true],
            ['sat_code' => 'D04', 'uso_cfdi' => 'Donativos', 'is_active' => true],
            ['sat_code' => 'D05', 'uso_cfdi' => 'Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación)', 'is_active' => true],
            ['sat_code' => 'D06', 'uso_cfdi' => 'Aportaciones voluntarias al SAR', 'is_active' => true],
            ['sat_code' => 'D07', 'uso_cfdi' => 'Primas por seguros de gastos médicos', 'is_active' => true],
            ['sat_code' => 'D08', 'uso_cfdi' => 'Gastos de transportación escolar obligatoria', 'is_active' => true],
            ['sat_code' => 'D09', 'uso_cfdi' => 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones', 'is_active' => true],
            ['sat_code' => 'D10', 'uso_cfdi' => 'Pagos por servicios educativos (colegiaturas)', 'is_active' => true],
            ['sat_code' => 'P01', 'uso_cfdi' => 'Por definir', 'is_active' => true],
        ];

        DB::table('sat_usos_cfdi')->insert($cfdiUsages);
    }
}
