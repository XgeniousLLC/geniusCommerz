<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public const PERMISSIONS = [
        'admins' => ['view admins', 'create admins', 'edit admins', 'delete admins'],
        'users' => ['view users', 'create users', 'edit users', 'delete users'],
        'pages' => ['view pages', 'create pages', 'edit pages', 'delete pages', 'publish pages'],
        'roles' => ['view roles', 'create roles', 'edit roles', 'delete roles'],
        'settings' => ['view settings', 'edit settings'],
    ];

    public function run(): void
    {
        $all = collect(self::PERMISSIONS)->flatten()->all();

        foreach ($all as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
        }

        // super-admin gets every permission
        $superAdmin = Role::where('name', 'super-admin')->where('guard_name', 'admin')->first();
        $superAdmin?->syncPermissions($all);
    }
}
