<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\RolesHasPermissions;
use DB;

class CreateRolesHasPermissions extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {

       $permissions = [
           '1',
           '2',
           '3',
           '4',
           '5',
           '6',
           '7',
           '8',
        ];

        foreach ($permissions as $permission) {
             RolesHasPermissions::create([
                 'permission_id' => $permission,
                 'role_id' => 1,
             ]);
        }

    }
}
