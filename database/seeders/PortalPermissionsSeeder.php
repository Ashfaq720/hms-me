<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PortalPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Register the new portal permissions
        // The permissions table on this project requires module_id (extends Spatie schema).
        $moduleId = \DB::table('modules')->where('name', 'Patient Portal')->value('id');
        if (! $moduleId) {
            $moduleId = \DB::table('modules')->insertGetId([
                'name' => 'Patient Portal', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $portalPerms = [
            'doctor.portal.view',
            'patient.portal.access',
            'patient.portal.view-bills',
            'patient.portal.view-prescriptions',
        ];
        foreach ($portalPerms as $name) {
            if (! Permission::where('name', $name)->exists()) {
                Permission::create([
                    'name' => $name,
                    'guard_name' => 'web',
                    'module_id' => $moduleId,
                ]);
            }
        }

        // 2. Identify Super Admin / Admin / Doctor roles
        $allPerms = Permission::all();

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin      = Role::firstOrCreate(['name' => 'Admin',       'guard_name' => 'web']);
        $doctor     = Role::firstOrCreate(['name' => 'Doctor',      'guard_name' => 'web']);

        // 3. Super Admin + Admin → ALL permissions (262 + 4 new)
        $superAdmin->syncPermissions($allPerms);
        $admin->syncPermissions($allPerms);

        // 4. Doctor role → portal + clinical permissions
        $doctorPerms = Permission::where(function ($q) {
            $q->whereIn('name', [
                'doctor.portal.view',
                'opd-patients.view', 'opd-patients.create', 'opd-patients.update',
                'ipd-patients.view',
                'prescriptions.create', 'prescriptions.view', 'prescriptions.update',
                'lab.order.create', 'lab.order.view',
                'icu.admission.view', 'nicu.admission.view',
                'nurse_notes.view',
                'billing.bill.view',
            ])->orWhere('name', 'like', 'opd-patients.%')
              ->orWhere('name', 'like', 'ipd-patients.%')
              ->orWhere('name', 'like', 'prescriptions.%')
              ->orWhere('name', 'like', 'icu.%')
              ->orWhere('name', 'like', 'nicu.%');
        })->get();
        $doctor->syncPermissions($doctorPerms);

        // 5. Assign Super Admin role to the first user (system bootstrap)
        $firstUser = \App\Models\User::first();
        if ($firstUser && ! $firstUser->hasRole('Super Admin')) {
            $firstUser->assignRole('Super Admin');
        }

        $this->command->info("Portal permissions assigned:");
        $this->command->info("  Super Admin: " . $superAdmin->permissions()->count() . " permissions");
        $this->command->info("  Admin:       " . $admin->permissions()->count() . " permissions");
        $this->command->info("  Doctor:      " . $doctor->permissions()->count() . " permissions");
    }
}
