<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsModuleSeeder extends Seeder
{
    public function run()
    {
        $modulos = [
            'role' => 'Permisos para gestionar roles y sus permisos.',
            'client' => 'Administración de clientes.',
            'clientes' => 'Administración de clientes.',
            'subclientes' => 'Gestión de subclientes.',
            'proovedores' => 'Gestión de proveedores y cuentas bancarias.',
            'proveedores' => 'Gestión de proveedores.',
            'equipos' => 'Gestión de equipos y documentos.',
            'operadores' => 'Gestión de operadores y pagos.',
            'cotizaciones' => 'Gestión de cotizaciones y sus estados.',
            'planeacion' => 'Control de la planeación y ejecución.',
            'bancos' => 'Gestión de bancos y transacciones.',
            'cuentas' => 'Control de cuentas por pagar y cobrar.',
            'roles' => 'Administración de roles y usuarios.',
            'empresas' => 'Gestión de empresas.',
            'configuracion' => 'Parámetros generales del sistema.',
            'liquidaciones' => 'Gestión de liquidaciones.',
            'gastos' => 'Control de gastos generales.',
            'catalogo' => 'Administración de catálogos base.',
            'reportes' => 'Generación de reportes del sistema.',
            'coordenadasv' => 'Gestión de coordenadas del sistema.',
            'proveedores-viajes' => 'Gestión de proveedores para viajes.',
        ];

        foreach (Permission::all() as $permiso) {
            $prefijo = explode('-', $permiso->name)[0];

            $permiso->update([
                'modulo' => $prefijo,
                'descripcion' => $modulos[$prefijo] ?? 'Permisos del módulo ' . ucfirst($prefijo),
            ]);
        }
    }
}
