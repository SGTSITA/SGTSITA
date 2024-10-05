<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $configuracion = Configuracion::create([
            'nombre_sistema' => 'SITA',
            'color_principal' => '#47a0cd',
            'logo' => '62c7b5166ea00W-TECHBL6.png',
            'favicon' => '62c7b5166f3b7LogosinF.png',
            'color_iconos_sidebar' => '#e1915b',
            'color_iconos_cards' => '#dbdd5a',
            'color_boton_add' => '#e6e02d',
            'color_boton_save' => '#3fd73c',
            'color_boton_close' => '#e60f0f',
        ]);
    }
}
