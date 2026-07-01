<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class () extends Migration {
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['name' => 'gastos-new', 'guard_name' => 'web'],
            [
                'modulo' => 'Gastos',
                'description' => 'Acceso al nuevo modulo unificado de gastos',
                'sistema' => 'SGT',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::table('permissions')
            ->where('name', 'gastos-new')
            ->where('guard_name', 'web')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
