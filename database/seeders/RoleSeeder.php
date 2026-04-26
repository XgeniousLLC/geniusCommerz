<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public const ROLES = [
        'super-admin',
        'store-manager',
        'operations',
        'inventory-manager',
        'marketing',
        'call-agent',
        'call-supervisor',
        'accountant',
        'content-editor',
        'read-only-analyst',
    ];

    public function run(): void
    {
        foreach (self::ROLES as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'admin']);
        }
    }
}
