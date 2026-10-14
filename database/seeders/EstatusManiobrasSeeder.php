<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstatusManiobrasSeeder extends Seeder
{
    public function run(): void
    {
        $estatus = [
            ['nombre' => 'Local solicitado', 'descripcion' => 'Contenedor en maniobra dentro del patio'],
            ['nombre' => 'En patio', 'descripcion' => 'Contenedor dentro del recinto'],
            ['nombre' => 'En revisión', 'descripcion' => 'En inspección física o aduanal'],
            ['nombre' => 'En proceso de documentación', 'descripcion' => 'Validando o procesando documentos'],
            ['nombre' => 'Documentos liberados', 'descripcion' => 'Documentación lista para liberar la unidad'],
            ['nombre' => 'Para liberación', 'descripcion' => 'Pendiente de sello o autorización final'],
            ['nombre' => 'Liberado', 'descripcion' => 'Autorizado y listo para salir'],
            ['nombre' => 'Listo para salir', 'descripcion' => 'Se está preparando para salida'],
            ['nombre' => 'Fuera de patio', 'descripcion' => 'Contenedor entregado / fuera del recinto'],

        
            ['nombre' => 'En espera', 'descripcion' => 'Pendiente por algún requisito'],
            ['nombre' => 'Incidencia', 'descripcion' => 'Contenedor con problema o documentación incorrecta'],
            ['nombre' => 'Cancelado', 'descripcion' => 'Maniobra cancelada'],
        ];

        DB::table('estatus_maniobras')->insert($estatus);
    }
}
