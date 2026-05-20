<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PermissionInRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assign permissions to Admin role (note: roles use lowercase names in RoleSeeder)
        $admin = Role::whereName('Admin')->first();
        if ($admin) {
            $admin->givePermissionTo([
                // Core modules
                'user_access',
                'role_access',
                'permission_access',
                'setting_access',
                
                // Inventory / ecommerce / etc.
                'product_access',
                'category_access',
                'brand_access',
                'supplier_access',
                'supplier_show',
                'purchase_access',
                'purchase_show',
                'purchase_order_access',
                'purchase_order_show',
                'view_profile',
                'update_profile',
            ]);
        }

        $this->command->info('Role permissions assigned successfully!');
    }
}