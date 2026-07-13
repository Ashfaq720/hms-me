<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds permissions for the enterprise modules added on 2026-05-19:
 *   Organizations & Branches, Encounters, Service Charge, Inventory & Stock,
 *   Insurance & Claims, Accounting, HR/Payroll, Patient Portal, Telemedicine,
 *   Package Enrollment, Reporting/Dashboard.
 *
 * Also wires a baseline RBAC matrix from SRS §9 onto Spatie roles
 * (super_admin, hospital_admin, doctor, nurse, receptionist, pharmacist,
 *  lab_tech, radiologist, accountant, inventory_manager, hr_manager,
 *  insurance_officer, patient_portal).
 */
class HmsEnterprisePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = $this->moduleMap();

        foreach ($modules as $moduleName => $permissions) {
            $module = Module::firstOrCreate([
                'name' => $moduleName,
                'slug' => Str::slug($moduleName),
                'description' => $moduleName . ' Module',
            ]);

            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web',
                ], ['module_id' => $module->id]);
            }
        }

        $this->assignRoleMatrix();

        $this->command?->info('HMS enterprise permissions + role matrix seeded.');
    }

    private function moduleMap(): array
    {
        return [
            'Organizations & Branches' => [
                'organization.view', 'organization.manage',
                'branch.view', 'branch.manage', 'branch.switch',
            ],
            'Encounters' => [
                'encounter.view', 'encounter.create', 'encounter.update',
                'encounter.close', 'encounter.reopen', 'encounter.delete',
            ],
            'Service Charge' => [
                'service_charge.view', 'service_charge.manage',
                'service_charge.post', 'service_charge.reverse', 'service_charge.audit',
            ],
            'Inventory & Stock' => [
                'inventory.item.view', 'inventory.item.manage',
                'inventory.warehouse.view', 'inventory.warehouse.manage',
                'inventory.po.view', 'inventory.po.manage', 'inventory.po.approve',
                'inventory.grn.view', 'inventory.grn.post',
                'inventory.stock.view', 'inventory.stock.adjust', 'inventory.stock.transfer',
                'inventory.expiry.view',
            ],
            'Insurance & Claims' => [
                'insurance.payer.view', 'insurance.payer.manage',
                'insurance.policy.view', 'insurance.policy.manage',
                'insurance.preauth.view', 'insurance.preauth.submit', 'insurance.preauth.approve',
                'insurance.claim.view', 'insurance.claim.submit',
                'insurance.claim.adjudicate', 'insurance.claim.settle',
            ],
            'Accounting & Finance' => [
                'accounting.coa.view', 'accounting.coa.manage',
                'accounting.journal.view', 'accounting.journal.post', 'accounting.journal.reverse',
                'accounting.bank.view', 'accounting.bank.reconcile',
                'accounting.report.view',
            ],
            'HR & Payroll' => [
                'hr.employee.view', 'hr.employee.manage',
                'hr.attendance.view', 'hr.attendance.manage',
                'hr.roster.view', 'hr.roster.manage',
                'hr.leave.view', 'hr.leave.approve',
                'hr.payroll.view', 'hr.payroll.run', 'hr.payroll.approve',
                'hr.wallet.view',
            ],
            'Patient Portal' => [
                'portal.user.view', 'portal.user.manage',
                'portal.family.link', 'portal.feedback.view',
            ],
            'Telemedicine' => [
                'telemedicine.session.view', 'telemedicine.session.schedule',
                'telemedicine.session.start', 'telemedicine.session.end',
            ],
            'Package Enrollment' => [
                'package.enrollment.view', 'package.enrollment.manage',
                'package.consumption.view', 'package.consumption.adjust',
            ],
            'Reporting & Dashboard' => [
                'dashboard.executive.view', 'dashboard.operational.view',
                'report.revenue.view', 'report.clinical.view',
                'report.inventory.view', 'report.hr.view',
                'report.export',
            ],
            'Billing Engine' => [
                'billing.bill.view', 'billing.bill.manage', 'billing.bill.finalize',
                'billing.bill.cancel', 'billing.bill.print',
                'billing.payment.collect', 'billing.payment.void',
                'billing.discount.approve', 'billing.waiver.approve',
                'billing.refund.request', 'billing.refund.approve',
            ],
            'Emergency Room' => [
                'er.dashboard.view', 'er.board.view',
                'er.patient.view', 'er.patient.register', 'er.patient.update',
                'er.triage.record', 'er.note.record', 'er.observation.record',
                'er.transfer.request', 'er.transfer.approve',
                'er.codeblue.activate', 'er.report.view',
            ],
            'NICU' => [
                'nicu.admission.view', 'nicu.admission.create',
                'nicu.admission.update', 'nicu.admission.discharge',
                'nicu.resource.view', 'nicu.resource.assign', 'nicu.resource.release',
                'nicu.vital.view', 'nicu.vital.record',
                'nicu.feeding.view', 'nicu.feeding.manage', 'nicu.feed.log',
                'nicu.growth.view', 'nicu.growth.record',
                'nicu.medication.view', 'nicu.medication.prescribe', 'nicu.medication.administer',
                'nicu.procedure.view', 'nicu.procedure.manage',
                'nicu.infection.view', 'nicu.infection.report',
                'nicu.consent.view', 'nicu.consent.capture',
                'nicu.dashboard.view', 'nicu.alert.acknowledge',
            ],
        ];
    }

    /**
     * Map roles → permissions. Roles are created if missing.
     */
    private function assignRoleMatrix(): void
    {
        // Canonical 19-role matrix per hospital workflow doc.
        // Wildcards (`module.*`) expand against the live `permissions` table
        // at seed time, so any new permission added later is auto-picked-up
        // by `Super Admin` and `Hospital Admin`.
        $matrix = [
            'Super Admin' => '*',
            'Hospital Admin' => '*',

            'Front Desk Executive' => [
                'encounter.view', 'encounter.create', 'encounter.update',
                'service_charge.view',
                'package.enrollment.view', 'package.enrollment.manage',
                'insurance.policy.view', 'insurance.preauth.submit',
                'portal.user.view',
                'billing.bill.view', 'billing.payment.collect',
                'dashboard.operational.view',
            ],
            'Appointment Agent' => [
                'encounter.view',
                'service_charge.view',
                'billing.bill.view',
                'dashboard.operational.view',
            ],
            'Doctor' => [
                'encounter.view', 'encounter.create', 'encounter.update', 'encounter.close',
                'telemedicine.session.view', 'telemedicine.session.start', 'telemedicine.session.end',
                'service_charge.view',
                'package.enrollment.view', 'package.consumption.view',
                'dashboard.operational.view', 'report.clinical.view',
            ],
            'Nurse' => [
                'encounter.view', 'encounter.update',
                'inventory.stock.view',
                'package.consumption.view',
                'dashboard.operational.view',
            ],
            'ER Doctor' => [
                'encounter.view', 'encounter.create', 'encounter.update', 'encounter.close',
                'service_charge.view',
                'dashboard.operational.view',
            ],
            'ER Triage Nurse' => [
                'encounter.view', 'encounter.update',
                'dashboard.operational.view',
            ],
            'IPD Coordinator' => [
                'encounter.view', 'encounter.create', 'encounter.update', 'encounter.close',
                'service_charge.view',
                'package.enrollment.view', 'package.enrollment.manage',
                'inventory.stock.view',
                'billing.bill.view',
                'dashboard.operational.view',
            ],
            'ICU Staff' => [
                'encounter.view', 'encounter.update',
                'inventory.stock.view',
                'service_charge.view',
                'dashboard.operational.view',
            ],
            'OT Coordinator' => [
                'encounter.view', 'encounter.update',
                'inventory.stock.view', 'inventory.stock.adjust',
                'service_charge.view', 'service_charge.post',
                'dashboard.operational.view',
            ],
            'Surgeon' => [
                'encounter.view', 'encounter.update', 'encounter.close',
                'service_charge.view',
                'dashboard.operational.view',
            ],
            'Anesthetist' => [
                'encounter.view', 'encounter.update',
                'service_charge.view',
                'dashboard.operational.view',
            ],
            'Pathology Technician' => [
                'encounter.view',
                'inventory.stock.view',
                'service_charge.view', 'service_charge.post',
                'report.clinical.view',
            ],
            'Radiologist' => [
                'encounter.view',
                'service_charge.view', 'service_charge.post',
                'report.clinical.view',
            ],
            'Pharmacist' => [
                'inventory.item.view', 'inventory.warehouse.view',
                'inventory.stock.view', 'inventory.stock.adjust',
                'inventory.expiry.view',
                'inventory.grn.view', 'inventory.grn.post',
                'service_charge.view', 'service_charge.post',
                'report.inventory.view',
            ],
            'Cashier' => [
                'service_charge.view', 'service_charge.audit',
                'accounting.*',
                'insurance.claim.view', 'insurance.claim.submit',
                'insurance.claim.adjudicate', 'insurance.claim.settle',
                'report.revenue.view', 'report.export',
                'billing.*',
                'dashboard.executive.view', 'dashboard.operational.view',
            ],
            'Blood Bank Officer' => [
                'inventory.item.view', 'inventory.stock.view', 'inventory.stock.adjust',
                'service_charge.view',
                'report.inventory.view',
            ],
            'Ambulance Dispatcher' => [
                'encounter.view',
                'service_charge.view',
                'dashboard.operational.view',
            ],
            'Patient' => [
                'portal.user.view',
            ],
            'Auditor' => [
                'encounter.view', 'service_charge.view', 'service_charge.audit',
                'billing.bill.view',
                'inventory.item.view', 'inventory.stock.view', 'inventory.expiry.view',
                'insurance.claim.view',
                'accounting.coa.view', 'accounting.journal.view',
                'dashboard.executive.view', 'dashboard.operational.view',
                'report.revenue.view', 'report.clinical.view',
                'report.inventory.view', 'report.export',
            ],
        ];

        $allPermissions = Permission::pluck('name')->all();

        foreach ($matrix as $roleName => $patterns) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($patterns === '*') {
                $role->syncPermissions($allPermissions);
                continue;
            }

            $resolved = [];
            foreach ($patterns as $pattern) {
                if (str_ends_with($pattern, '.*')) {
                    $prefix = substr($pattern, 0, -2);
                    foreach ($allPermissions as $name) {
                        if (str_starts_with($name, $prefix . '.')) {
                            $resolved[] = $name;
                        }
                    }
                } else {
                    $resolved[] = $pattern;
                }
            }

            $resolved = array_values(array_unique(array_intersect($resolved, $allPermissions)));
            $role->syncPermissions($resolved);
        }
    }
}
