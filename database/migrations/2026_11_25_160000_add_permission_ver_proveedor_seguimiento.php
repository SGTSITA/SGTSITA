<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $perm = Permission::firstOrCreate([
            'name' => 'ver-proveedor-seguimiento',
            'guard_name' => 'web',
        ]);

        $roles = Role::whereIn('name', ['Super Admin', 'Administrador', 'Admin'])->get();
        foreach ($roles as $role) {
            if (!$role->hasPermissionTo($perm)) {
                $role->givePermissionTo($perm);
            }
        }
    }

    public function down(): void
    {
        Permission::where('name', 'ver-proveedor-seguimiento')->delete();
    }
};
