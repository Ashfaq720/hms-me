<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Grants the Super Admin and Admin roles EVERY permission in the system
 * so they see every menu and can manage every module. Runs LAST in the
 * DatabaseSeeder chain so newly-created permissions (NICU, OT, packages,
 * ER, etc.) are also picked up automatically.
 */
class PermissionInRoleSeeder extends Seeder
{
    public function run(): void
    {
        $all = Permission::all();

        foreach (['Super Admin', 'Admin'] as $roleName) {
            $role = Role::whereName($roleName)->first();
            if ($role) {
                $role->syncPermissions($all);
                $this->command->info("→ {$roleName} role granted all {$all->count()} permissions.");
            }
        }

        // Spatie caches permissions; clear so the change is live immediately.
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Role permissions assigned successfully!');
    }
}
