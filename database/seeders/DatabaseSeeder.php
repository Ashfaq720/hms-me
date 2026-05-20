<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Seed roles and permissions first
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            PermissionInRoleSeeder::class,
            CompanySettingsSeeder::class,
            SettingsSeeder::class,
            UserSeeder::class,
        ]);
    }
}
