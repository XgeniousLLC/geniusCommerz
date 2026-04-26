<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Admin::updateOrCreate(
            ['email' => 'admin@klixbd.com'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@klixbd.com',
                'password' => Hash::make('password'),
                'is_active' => true,
                'role' => 'super-admin',
                'email_verified_at' => now(),
            ]
        );

        $superAdmin->syncRoles('super-admin');
    }
}
