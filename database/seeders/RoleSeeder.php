<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $roles = [
            ['name' => 'Super Admin', 'description' => 'User with full access to all system features and settings.'],
            ['name' => 'Admin', 'description' => 'User with administrative privileges to manage system settings and users.'],
            ['name' => 'Accountant', 'description' => 'User responsible for managing financial records and transactions.'],
            ['name' => 'Hr Manager', 'description' => 'User responsible for managing human resources and employee relations.'],
            ['name' => 'Employee', 'description' => 'Regular user with access to their own information and tasks.'],
            ['name' => 'Manager', 'description' => 'User with managerial responsibilities and oversight.'],
            ['name' => 'User', 'description' => 'Standard user with limited access to system features.'],
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName['name'],
                'description' => $roleName['description'],
                'guard_name' => 'web',
            ]);
        }

        $this->command->info('Roles seeded successfully!');
    }
}