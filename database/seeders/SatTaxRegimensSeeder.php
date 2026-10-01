<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatTaxRegimensSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $taxRegimens = [
            ['sat_code' => '601', 'regimen_fiscal' => 'General de Ley Personas Morales', 'is_active' => true],
            ['sat_code' => '603', 'regimen_fiscal' => 'Personas Morales con Fines no Lucrativos', 'is_active' => true],
            ['sat_code' => '605', 'regimen_fiscal' => 'Sueldos y Salarios e Ingresos Asimilados a Salarios', 'is_active' => true],
            ['sat_code' => '606', 'regimen_fiscal' => 'Arrendamiento', 'is_active' => true],
            ['sat_code' => '607', 'regimen_fiscal' => 'Régimen de Enajenación o Adquisición de Bienes', 'is_active' => true],
            ['sat_code' => '608', 'regimen_fiscal' => 'Demás ingresos', 'is_active' => true],
            ['sat_code' => '609', 'regimen_fiscal' => 'Consolidación', 'is_active' => false],
            ['sat_code' => '610', 'regimen_fiscal' => 'Residentes en el Extranjero sin Establecimiento Permanente en México', 'is_active' => true],
            ['sat_code' => '611', 'regimen_fiscal' => 'Ingresos por Dividendos (socios y accionistas)', 'is_active' => true],
            ['sat_code' => '612', 'regimen_fiscal' => 'Personas Físicas con Actividades Empresariales y Profesionales', 'is_active' => true],
            ['sat_code' => '614', 'regimen_fiscal' => 'Ingresos por intereses', 'is_active' => true],
            ['sat_code' => '615', 'regimen_fiscal' => 'Régimen de los ingresos por obtención de premios', 'is_active' => true],
            ['sat_code' => '616', 'regimen_fiscal' => 'Sin obligaciones fiscales', 'is_active' => true],
            ['sat_code' => '620', 'regimen_fiscal' => 'Sociedades Cooperativas de Producción que optan por diferir sus ingresos', 'is_active' => true],
            ['sat_code' => '621', 'regimen_fiscal' => 'Incorporación Fiscal', 'is_active' => false],
            ['sat_code' => '622', 'regimen_fiscal' => 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras', 'is_active' => true],
            ['sat_code' => '623', 'regimen_fiscal' => 'Opcional para Grupos de Sociedades', 'is_active' => true],
            ['sat_code' => '624', 'regimen_fiscal' => 'Coordinados', 'is_active' => true],
            ['sat_code' => '625', 'regimen_fiscal' => 'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas', 'is_active' => true],
            ['sat_code' => '626', 'regimen_fiscal' => 'Régimen Simplificado de Confianza', 'is_active' => true],
            ['sat_code' => '628', 'regimen_fiscal' => 'HIDROCARBUROS', 'is_active' => true],
            ['sat_code' => '629', 'regimen_fiscal' => 'DE LOS REGÍMENES FISCALES PREFERENTES Y DE LAS EMPRESAS MULTINACIONALES', 'is_active' => true],
            ['sat_code' => '630', 'regimen_fiscal' => 'ENAJENACIÓN DE ACCIONES EN BOLSA DE VALORES', 'is_active' => true],
        ];

        DB::table('sat_regimen_fiscal')->insert($taxRegimens);
    }
}
