<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            AdminSeeder::class,
            SiteSettingsSeeder::class,
            GeneralSettingsSeeder::class,
            IntegrationSeeder::class,
        ]);

        // Create regular test user (idempotent — safe to run multiple times)
        if (! User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name'  => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
    }
}
