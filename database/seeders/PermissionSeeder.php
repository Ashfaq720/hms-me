<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\Module;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions only if they don't exist
       $modules = [
            'Users' => [
                ['name' => 'user_access', 'guard_name' => 'web'],
                ['name' => 'user_create', 'guard_name' => 'web'],
                ['name' => 'user_edit', 'guard_name' => 'web'],
                ['name' => 'user_delete', 'guard_name' => 'web'],
                ['name' => 'user_show', 'guard_name' => 'web'],
                ['name' => 'user_import', 'guard_name' => 'web'],
                ['name' => 'user_export', 'guard_name' => 'web']
            ],
            
            'Users Log & Activity' => [
                ['name' => 'user_log', 'guard_name' => 'web'],
                ['name' => 'user_activity', 'guard_name' => 'web']
            ],
            
            'Modules' => [
                ['name' => 'module_access', 'guard_name' => 'web'],
                ['name' => 'module_create', 'guard_name' => 'web'],
                ['name' => 'module_edit', 'guard_name' => 'web'],
                ['name' => 'module_delete', 'guard_name' => 'web'],
                ['name'=> 'module_show', 'guard_name' => 'web']
            ],
            
            'Permission' => [
                ['name' => 'permission_access', 'guard_name' => 'web'],
                ['name' => 'permission_create', 'guard_name' => 'web'],
                ['name' => 'permission_edit', 'guard_name' => 'web'],
                ['name' => 'permission_delete', 'guard_name' => 'web'],
                ['name'=> 'permission_show', 'guard_name' => 'web'],
                ['name' => 'permission_assign', 'guard_name' => 'web'],
            ],
            
            'Roles' => [
                ['name' => 'role_access', 'guard_name' => 'web'],
                ['name' => 'role_create', 'guard_name' => 'web'],
                ['name' => 'role_edit', 'guard_name' => 'web'],
                ['name' => 'role_delete', 'guard_name' => 'web'],
                ['name' => 'role_show', 'guard_name' => 'web'],
            ],
            
            'Categories' => [
                ['name' => 'category_access', 'guard_name' => 'web'],
                ['name' => 'category_create', 'guard_name' => 'web'],
                ['name' => 'category_edit', 'guard_name' => 'web'],
                ['name' => 'category_delete', 'guard_name' => 'web'],
                ['name' => 'category_show', 'guard_name' => 'web']
            ],
            
            'Brands' => [
                ['name' => 'brand_access', 'guard_name' => 'web'],
                ['name' => 'brand_create', 'guard_name' => 'web'],
                ['name' => 'brand_edit', 'guard_name' => 'web'],
                ['name' => 'brand_delete', 'guard_name' => 'web'],
                ['name' => 'brand_show', 'guard_name' => 'web']
            ],
            
            'Attributes' => [
                ['name' => 'attribute_access', 'guard_name' => 'web'],
                ['name' => 'attribute_create', 'guard_name' => 'web'],
                ['name' => 'attribute_edit', 'guard_name' => 'web'],
                ['name' => 'attribute_delete', 'guard_name' => 'web'],
                ['name' => 'attribute_show', 'guard_name' => 'web']
            ],
            
            'Products' => [
                ['name' => 'product_access', 'guard_name' => 'web'],
                ['name' => 'product_create', 'guard_name' => 'web'],
                ['name' => 'product_edit', 'guard_name' => 'web'],
                ['name' => 'product_delete', 'guard_name' => 'web'],
                ['name' => 'product_show', 'guard_name' => 'web']
            ],

            'Inventory' => [
                ['name' => 'inventory_access', 'guard_name' => 'web'],
                ['name' => 'inventory_create', 'guard_name' => 'web'],
                ['name' => 'inventory_edit', 'guard_name' => 'web'],
                ['name' => 'inventory_delete', 'guard_name' => 'web'],
                ['name' => 'inventory_show', 'guard_name' => 'web']
            ],

            'Suppliers' => [
                ['name' => 'supplier_access', 'guard_name' => 'web'],
                ['name' => 'supplier_create', 'guard_name' => 'web'],
                ['name' => 'supplier_edit', 'guard_name' => 'web'],
                ['name' => 'supplier_delete', 'guard_name' => 'web'],
                ['name' => 'supplier_show', 'guard_name' => 'web']
            ],

            'Orders' => [
                ['name' => 'order_access', 'guard_name' => 'web'],
                ['name' => 'order_create', 'guard_name' => 'web'],
                ['name' => 'order_edit', 'guard_name' => 'web'],
                ['name' => 'order_delete', 'guard_name' => 'web'],
                ['name' => 'order_show', 'guard_name' => 'web'],
            ],

            'Customers' => [
                ['name' => 'customer_access', 'guard_name' => 'web'],
                ['name' => 'customer_create', 'guard_name' => 'web'],
                ['name' => 'customer_edit', 'guard_name' => 'web'],
                ['name' => 'customer_delete', 'guard_name' => 'web'],
                ['name' => 'customer_show', 'guard_name' => 'web'],
            ],

            'Purchases' => [
                ['name' => 'purchase_access', 'guard_name' => 'web'],
                ['name' => 'purchase_create', 'guard_name' => 'web'],
                ['name' => 'purchase_edit', 'guard_name' => 'web'],
                ['name' => 'purchase_delete', 'guard_name' => 'web'],
                ['name' => 'purchase_show', 'guard_name' => 'web'],

                ['name' => 'purchase_invoice', 'guard_name' => 'web'],
                ['name' => 'purchase_return', 'guard_name' => 'web']
            ],

            'Purchase Orders' => [
                ['name' => 'purchase_order_access', 'guard_name' => 'web'],
                ['name' => 'purchase_order_create', 'guard_name' => 'web'],
                ['name' => 'purchase_order_edit', 'guard_name' => 'web'],
                ['name' => 'purchase_order_delete', 'guard_name' => 'web'],
                ['name' => 'purchase_order_show', 'guard_name' => 'web']
            ],

            'Warehouses' => [
                ['name' => 'warehouse_access', 'guard_name' => 'web'],
                ['name' => 'warehouse_create', 'guard_name' => 'web'],
                ['name' => 'warehouse_edit', 'guard_name' => 'web'],
                ['name' => 'warehouse_delete', 'guard_name' => 'web'],
                ['name' => 'warehouse_show', 'guard_name' => 'web'],
            ],
          
            'Settings' => [
                ['name' => 'setting_access', 'guard_name' => 'web'],
                ['name' => 'setting_create', 'guard_name' => 'web'],
                ['name' => 'setting_edit', 'guard_name' => 'web'],
                ['name' => 'setting_delete', 'guard_name' => 'web'],
                ['name' => 'setting_show', 'guard_name' => 'web'],
            ],

            'Profile Management' => [
                ['name' => 'view_profile', 'guard_name' => 'web'],
                ['name' => 'update_profile', 'guard_name' => 'web'],
            ],
        ];

        foreach ($modules as $moduleName => $permissions) {
            $module = Module::firstOrCreate([
                'name' => $moduleName,
                'slug' => Str::slug($moduleName),
                'description' => $moduleName . ' Module'
            ]);

            foreach ($permissions as $permissionData) {
                Permission::firstOrCreate([
                    'name'       => $permissionData['name'],
                    'guard_name' => $permissionData['guard_name'],
                    'module_id'  => $module->id,
                ]);
            }
        }

        //permissionGroup end

        foreach ($modules as $key => $permissions) {
            $module = Module::where('name', $key)->first();
            if (! $module) {
                $module = Module::create(['name' => $key]);
            }
            foreach ($permissions as $permission) {
                $permissionCheck = Permission::where('name', $permission['name'])->exists();
                if (! $permissionCheck) {
                    Permission::create(['name' => $permission['name'], 'guard_name' => $permission['guard_name'], 'module_id' => $module->id]);
                }
            }
        }

        $this->command->info('Permissions seeded successfully!');
    }
}