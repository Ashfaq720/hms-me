<?php

namespace Database\Seeders;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\CostCenter;
use App\Models\Encounter\Encounter;
use App\Models\Hr\Employee;
use App\Models\Hr\EmployeeShift;
use App\Models\Hr\LeaveType;
use App\Models\Insurance\InsurancePolicy;
use App\Models\Insurance\Payer;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryWarehouse;
use App\Models\Organization\Branch;
use App\Models\Organization\Organization;
use App\Models\Patient;
use App\Models\ServiceCharge\ServiceCatalog;
use App\Models\ServiceCharge\ServiceChargeRule;
use App\Models\User;
use App\Services\Inventory\StockLedgerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HmsEnterpriseDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $org = $this->seedOrganization();
            $mainBranch = $this->seedBranches($org);
            $this->attachAdminUserToBranch($mainBranch);
            $this->seedChartOfAccounts($org);
            $this->seedCostCenters($org, $mainBranch);
            $this->seedPayers($org);
            $this->seedServiceCatalog($org, $mainBranch);
            $this->seedWarehouses($org, $mainBranch);
            $this->seedHrFoundation($mainBranch);
            $this->seedEmployees($org, $mainBranch);
            $this->seedInventoryItems($org);
            $this->seedLegacyMasterData();
            $this->seedSamplePatients($org, $mainBranch);
        });

        $this->command?->info('HMS enterprise demo data seeded.');
    }

    private function seedOrganization(): Organization
    {
        return Organization::firstOrCreate(
            ['code' => 'HMSORG'],
            [
                'name' => 'Demo Hospital Group',
                'legal_name' => 'Demo Hospital Group Ltd.',
                'contact_email' => 'admin@demo-hms.local',
                'contact_phone' => '+8801700000000',
                'country' => 'BD',
                'timezone' => 'Asia/Dhaka',
                'default_currency' => 'BDT',
                'is_active' => true,
            ]
        );
    }

    private function seedBranches(Organization $org): Branch
    {
        $main = Branch::firstOrCreate(
            ['organization_id' => $org->id, 'code' => 'MAIN'],
            [
                'name' => 'Main Hospital',
                'type' => 'hospital',
                'address_line1' => '123 Hospital Road',
                'city' => 'Dhaka',
                'country' => 'BD',
                'phone' => '+880200000000',
                'email' => 'main@demo-hms.local',
                'mrn_prefix' => 'MRN',
                'invoice_prefix' => 'INV',
                'health_card_prefix' => 'HC',
                'is_active' => true,
            ]
        );

        Branch::firstOrCreate(
            ['organization_id' => $org->id, 'code' => 'CLINIC'],
            [
                'name' => 'Outpatient Clinic',
                'type' => 'clinic',
                'address_line1' => '456 Clinic Avenue',
                'city' => 'Dhaka',
                'country' => 'BD',
                'is_active' => true,
            ]
        );

        return $main;
    }

    private function attachAdminUserToBranch(Branch $branch): void
    {
        // Find or create the demo admin and ensure they have the canonical
        // "Super Admin" role.
        $admin = User::query()->where('email', 'admin@demo-hms.local')->first()
            ?? User::query()->whereHas('roles', fn ($q) => $q->whereIn('name', ['Super Admin', 'super_admin']))->first()
            ?? User::query()->where('email', 'admin@example.com')->first();

        if (! $admin) {
            $admin = User::create([
                'name' => 'HMS Admin',
                'email' => 'admin@demo-hms.local',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
        }
        if (! $admin->hasRole('Super Admin')) {
            $admin->assignRole('Super Admin');
        }

        $admin->update([
            'current_organization_id' => $branch->organization_id,
            'current_branch_id' => $branch->id,
        ]);

        if (! $admin->branches()->where('branch_id', $branch->id)->exists()) {
            $admin->branches()->attach($branch->id, ['is_default' => true]);
        }
    }

    private function seedChartOfAccounts(Organization $org): void
    {
        $accounts = [
            ['1000', 'Assets', 'asset', null],
            ['1100', 'Cash & Bank', 'asset', 'current'],
            ['1200', 'Accounts Receivable', 'asset', 'current'],
            ['1300', 'Inventory', 'asset', 'current'],
            ['2000', 'Liabilities', 'liability', null],
            ['2100', 'Accounts Payable', 'liability', 'current'],
            ['2200', 'Tax Payable', 'liability', 'current'],
            ['3000', 'Equity', 'equity', null],
            ['4000', 'Revenue', 'income', null],
            ['4100', 'Consultation Revenue', 'income', 'operating'],
            ['4200', 'IPD Revenue', 'income', 'operating'],
            ['4300', 'Pharmacy Revenue', 'income', 'operating'],
            ['4400', 'Lab Revenue', 'income', 'operating'],
            ['4500', 'OT Revenue', 'income', 'operating'],
            ['5000', 'Expenses', 'expense', null],
            ['5100', 'Salaries', 'expense', 'operating'],
            ['5200', 'Supplies & Consumables', 'expense', 'operating'],
            ['5300', 'Utilities', 'expense', 'operating'],
        ];

        foreach ($accounts as [$code, $name, $type, $category]) {
            ChartOfAccount::firstOrCreate(
                ['organization_id' => $org->id, 'code' => $code],
                [
                    'name' => $name,
                    'account_type' => $type,
                    'category' => $category,
                    'is_postable' => $category !== null,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedCostCenters(Organization $org, Branch $branch): void
    {
        foreach (['OPD', 'IPD', 'ER', 'ICU', 'OT', 'LAB', 'RAD', 'PHM'] as $code) {
            CostCenter::firstOrCreate(
                ['organization_id' => $org->id, 'code' => $code],
                ['name' => $code . ' Department', 'branch_id' => $branch->id, 'is_active' => true]
            );
        }
    }

    private function seedPayers(Organization $org): void
    {
        $payers = [
            ['SELF', 'Self Pay', 'self', false],
            ['CORP1', 'Demo Corporate Plan', 'corporate', false],
            ['INS1', 'Demo Insurance Co.', 'insurance', true],
            ['GOV1', 'Government Scheme', 'government', true],
        ];

        foreach ($payers as [$code, $name, $type, $preAuth]) {
            Payer::firstOrCreate(
                ['organization_id' => $org->id, 'code' => $code],
                ['name' => $name, 'type' => $type, 'pre_auth_required' => $preAuth, 'is_active' => true]
            );
        }
    }

    private function seedServiceCatalog(Organization $org, Branch $branch): void
    {
        $services = [
            ['CONSULT_OPD', 'General OPD Consultation', 'consultation', 'per_use', 500, 0],
            ['CONSULT_SPECIALIST', 'Specialist Consultation', 'consultation', 'per_use', 1000, 0],
            ['BED_GENERAL', 'General Bed (Daily)', 'bed', 'per_day', 1500, 0],
            ['BED_CABIN', 'Private Cabin (Daily)', 'bed', 'per_day', 4000, 0],
            ['BED_ICU', 'ICU Bed (Daily)', 'icu_bed', 'per_day', 8000, 0],
            ['BED_NICU', 'NICU Bed (Daily)', 'nicu_bed', 'per_day', 9000, 0],
            ['OT_ROOM', 'OT Room (Per Hour)', 'ot_room', 'per_hour', 2500, 0],
            ['NURSING_DAILY', 'Nursing Service (Daily)', 'nursing', 'per_day', 800, 0],
            ['LAB_CBC', 'Complete Blood Count', 'lab_test', 'per_test', 600, 0],
            ['RAD_XRAY_CHEST', 'X-Ray Chest', 'radiology', 'per_test', 800, 0],
            ['PROC_DRESS', 'Wound Dressing', 'procedure', 'per_session', 300, 0],
            ['AMB_LOCAL', 'Ambulance Local Trip', 'ambulance', 'per_use', 1500, 0],
            ['ADMIN_REG', 'Patient Registration Fee', 'administrative', 'per_use', 100, 0],
            // Generic revenue catalog used by auto-posting observers (Phase 1).
            // unit_price is always overridden by the calling observer.
            ['PHARMACY_REVENUE', 'Pharmacy Sale (auto)', 'pharmacy', 'per_use', 0, 0],
            ['OT_PROCEDURE', 'OT Procedure (auto)', 'procedure', 'per_use', 0, 0],
            ['ICU_DAY', 'ICU Daily Care (auto)', 'icu_bed', 'per_day', 8000, 0],
        ];

        foreach ($services as [$code, $name, $type, $unit, $price, $tax]) {
            $catalog = ServiceCatalog::firstOrCreate(
                ['branch_id' => $branch->id, 'code' => $code],
                [
                    'organization_id' => $org->id,
                    'name' => $name,
                    'service_type' => $type,
                    'charge_unit' => $unit,
                    'base_price' => $price,
                    'tax_percent' => $tax,
                    'patient_type' => 'all',
                    'discount_allowed' => true,
                    'insurance_covered' => true,
                    'package_eligible' => true,
                    'is_active' => true,
                ]
            );

            // Corporate gets 10% off, Staff gets 50% off.
            ServiceChargeRule::firstOrCreate(
                ['service_catalog_id' => $catalog->id, 'rule_kind' => 'patient_type', 'rule_value' => 'corporate'],
                ['branch_id' => $branch->id, 'adjustment_type' => 'percent', 'adjustment_value' => -10, 'priority' => 90, 'is_active' => true]
            );

            ServiceChargeRule::firstOrCreate(
                ['service_catalog_id' => $catalog->id, 'rule_kind' => 'patient_type', 'rule_value' => 'staff'],
                ['branch_id' => $branch->id, 'adjustment_type' => 'percent', 'adjustment_value' => -50, 'priority' => 95, 'is_active' => true]
            );
        }
    }

    private function seedWarehouses(Organization $org, Branch $branch): void
    {
        foreach ([
            ['PHM-MAIN', 'Main Pharmacy', 'pharmacy'],
            ['OT-STORE', 'OT Store', 'ot'],
            ['ICU-STORE', 'ICU Store', 'icu'],
            ['LAB-STORE', 'Lab Store', 'lab'],
            ['MAIN-STORE', 'Central Warehouse', 'main'],
        ] as [$code, $name, $type]) {
            InventoryWarehouse::firstOrCreate(
                ['branch_id' => $branch->id, 'code' => $code],
                ['organization_id' => $org->id, 'name' => $name, 'type' => $type, 'is_active' => true]
            );
        }
    }

    private function seedHrFoundation(Branch $branch): void
    {
        foreach ([
            ['MORNING', '08:00:00', '16:00:00', 480, false],
            ['EVENING', '16:00:00', '00:00:00', 480, true],
            ['NIGHT', '00:00:00', '08:00:00', 480, true],
        ] as [$name, $start, $end, $duration, $night]) {
            EmployeeShift::firstOrCreate(
                ['branch_id' => $branch->id, 'name' => $name],
                ['start_time' => $start, 'end_time' => $end, 'duration_minutes' => $duration, 'is_night_shift' => $night, 'is_active' => true]
            );
        }

        foreach ([
            ['CL', 'Casual Leave', 10],
            ['SL', 'Sick Leave', 14],
            ['AL', 'Annual Leave', 20],
            ['ML', 'Maternity Leave', 90],
        ] as [$code, $name, $quota]) {
            LeaveType::firstOrCreate(['code' => $code], ['name' => $name, 'annual_quota' => $quota, 'is_paid' => true, 'is_active' => true]);
        }
    }

    private function seedEmployees(Organization $org, Branch $branch): void
    {
        // Sample staff mapped to the canonical 19-role matrix.
        $employees = [
            ['DR001', 'Dr. Aisha', 'Rahman',     'doctor',          'aisha@demo-hms.local',  'Doctor'],
            ['DR002', 'Dr. Kamal', 'Hossain',    'doctor',          'kamal@demo-hms.local',  'Doctor'],
            ['NR001', 'Sister Mariam', 'Akter',  'nurse',           'mariam@demo-hms.local', 'Nurse'],
            ['PH001', 'Pharmacist Rafiq', 'Islam','pharmacist',     'rafiq@demo-hms.local',  'Pharmacist'],
            ['FD001', 'Front Desk Nadia', 'Sultana','receptionist', 'nadia@demo-hms.local',  'Front Desk Executive'],
            ['LT001', 'Lab Tech Sami', 'Ahmed',  'lab_technician',  'sami@demo-hms.local',   'Pathology Technician'],
            ['CS001', 'Cashier Tariq', 'Khan',   'cashier',         'tariq@demo-hms.local',  'Cashier'],
            ['IPD001', 'IPD Coord Lima', 'Khatun','admin',          'lima@demo-hms.local',   'IPD Coordinator'],
            ['AUD001', 'Auditor Ratul', 'Das',   'admin',           'ratul@demo-hms.local',  'Auditor'],
        ];

        foreach ($employees as [$code, $first, $last, $staffType, $email, $role]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => "$first $last",
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'current_organization_id' => $org->id,
                    'current_branch_id' => $branch->id,
                ]
            );

            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }

            if (! $user->branches()->where('branch_id', $branch->id)->exists()) {
                $user->branches()->attach($branch->id, ['is_default' => true]);
            }

            // Look up by user_id (unique) so re-runs update the code/staff_type
            // instead of failing the user_id unique constraint.
            Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_code' => $code,
                    'organization_id' => $org->id,
                    'branch_id' => $branch->id,
                    'first_name' => $first,
                    'last_name' => $last,
                    'email' => $email,
                    'staff_type' => $staffType,
                    'joining_date' => now()->subYears(2),
                    'employment_type' => 'full_time',
                    'status' => 'active',
                    'base_salary' => match ($staffType) {
                        'doctor' => 150000,
                        'nurse' => 35000,
                        default => 45000,
                    },
                ]
            );
        }
    }

    private function seedInventoryItems(Organization $org): void
    {
        $items = [
            ['MED-PARA500', 'Paracetamol 500mg', 'medicine', 'Paracetamol', 'tab', true],
            ['MED-AMOX250', 'Amoxicillin 250mg Cap', 'medicine', 'Amoxicillin', 'cap', true],
            ['MED-IVNORM', 'Normal Saline IV 500ml', 'medicine', 'Sodium Chloride', 'btl', true],
            ['MED-INSUL', 'Insulin Vial', 'medicine', 'Insulin', 'vial', true],
            ['SUP-GLOVES', 'Surgical Gloves (pair)', 'consumable', null, 'pair', true],
            ['SUP-SYRINGE', 'Syringe 5ml', 'consumable', null, 'pc', true],
            ['SUP-MASK', 'Face Mask N95', 'consumable', null, 'pc', true],
            ['SUP-COTTON', 'Sterile Cotton 100g', 'consumable', null, 'pack', true],
            ['OT-IMP-MESH', 'Surgical Mesh', 'implant', null, 'pc', true],
            ['REA-CBC', 'CBC Reagent Pack', 'reagent', null, 'pack', true],
        ];

        foreach ($items as [$code, $name, $category, $generic, $uom, $consumable]) {
            InventoryItem::firstOrCreate(
                ['organization_id' => $org->id, 'code' => $code],
                [
                    'name' => $name,
                    'category' => $category,
                    'generic_name' => $generic,
                    'uom' => $uom,
                    'is_consumable' => $consumable,
                    'reorder_level' => 50,
                    'reorder_quantity' => 200,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Seed the existing project's legacy master data so all module menus
     * and downstream demos (bed allocation, OPD visit, etc.) work.
     */
    private function seedLegacyMasterData(): void
    {
        // Departments
        foreach (['General Medicine', 'Surgery', 'Pediatrics', 'Cardiology', 'Obstetrics', 'Radiology', 'Pathology'] as $name) {
            \App\Models\Department::firstOrCreate(['name' => $name]);
        }

        // Designations
        foreach (['Consultant', 'Resident', 'Nurse', 'Pharmacist', 'Lab Technician', 'Radiology Technician', 'Receptionist', 'Cashier', 'Accountant'] as $name) {
            \App\Models\Designation::firstOrCreate(['name' => $name]);
        }

        // Specialists
        foreach (['General', 'Cardiology', 'Pediatrics', 'Surgery', 'Gynecology', 'Orthopedics'] as $name) {
            \App\Models\Specialist::firstOrCreate(['name' => $name]);
        }

        // Doctors – wire each demo doctor user to a Doctor row.
        $generalDept = \App\Models\Department::where('name', 'General Medicine')->first();
        $genSpec = \App\Models\Specialist::where('name', 'General')->first();
        $cardSpec = \App\Models\Specialist::where('name', 'Cardiology')->first();

        $doctorMap = [
            ['aisha@demo-hms.local',  'Dr. Aisha Rahman',   $generalDept, $genSpec],
            ['kamal@demo-hms.local',  'Dr. Kamal Hossain',  $generalDept, $cardSpec],
        ];
        foreach ($doctorMap as [$email, $name, $dept, $spec]) {
            $user = \App\Models\User::where('email', $email)->first();
            if (! $user) {
                continue;
            }
            \App\Models\Doctor::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'phone' => '0177' . random_int(1000000, 9999999),
                    'specialist_id' => optional($spec)->id,
                    'department_id' => optional($dept)->id,
                    'user_id' => $user->id,
                    'gender' => str_contains($name, 'Aisha') ? 'Female' : 'Male',
                ]
            );
        }

        // Floor / Ward / Bed hierarchy
        $floor = \App\Models\Floor::firstOrCreate(['name' => 'Floor 1']);

        $generalType = \App\Models\BedType::firstOrCreate(['name' => 'General']);
        $cabinType   = \App\Models\BedType::firstOrCreate(['name' => 'Cabin']);
        $icuType     = \App\Models\BedType::firstOrCreate(['name' => 'ICU']);

        $wardA = \App\Models\BedGroup::firstOrCreate(['name' => 'Ward A'], ['floor_id' => $floor->id]);
        $cabinGrp = \App\Models\BedGroup::firstOrCreate(['name' => 'Cabin Block'], ['floor_id' => $floor->id]);
        $icuGrp   = \App\Models\BedGroup::firstOrCreate(['name' => 'ICU Wing'], ['floor_id' => $floor->id]);

        $beds = [
            ['A-101', 1500, $generalType, $wardA],
            ['A-102', 1500, $generalType, $wardA],
            ['C-201', 4000, $cabinType,   $cabinGrp],
            ['I-301', 8000, $icuType,     $icuGrp],
        ];
        foreach ($beds as [$name, $rent, $type, $grp]) {
            \App\Models\Bed::firstOrCreate(
                ['name' => $name],
                [
                    'rent' => $rent,
                    'bed_type_id' => $type->id,
                    'bed_group_id' => $grp->id,
                    'is_reserved' => 0,
                ]
            );
        }

        // Symptoms so existing menus have rows.
        foreach (['Fever', 'Cough', 'Headache', 'Chest pain', 'Abdominal pain'] as $s) {
            \App\Models\Symptom::firstOrCreate(['name' => $s]);
        }
        // Lab investigation master data is owned by the legacy LabInvestigationType
        // seeder (it cascades type_id → category_id → investigation). We deliberately
        // do not duplicate that here to avoid schema drift.
    }

    private function seedSamplePatients(Organization $org, Branch $branch): void
    {
        $patients = [
            ['Mr. Ali Ahmed',          '01711000001', 'Male',   '1985-03-12'],
            ['Mrs. Fatima Khan',       '01711000002', 'Female', '1992-07-25'],
            ['Mr. Imran Hossain',      '01711000003', 'Male',   '1970-11-02'],
            ['Ms. Sumaiya Rahman',     '01711000004', 'Female', '2000-01-15'],
            ['Baby Nazia',             '01711000005', 'Female', '2023-09-20'],
        ];

        $payer = Payer::where('organization_id', $org->id)->where('code', 'INS1')->first();

        foreach ($patients as [$name, $phone, $gender, $dob]) {
            $patient = Patient::firstOrCreate(
                ['mobileno' => $phone],
                [
                    'patient_name' => $name,
                    'gender' => $gender,
                    'dob' => $dob,
                    'blood_group' => 'O+',
                    'org_id' => $org->id,
                    'branch_id' => $branch->id,
                ]
            );

            // Issue one open OPD encounter per patient so dashboards have data.
            Encounter::firstOrCreate(
                ['patient_id' => $patient->id, 'encounter_type' => 'OPD', 'status' => 'open'],
                [
                    'organization_id' => $org->id,
                    'branch_id' => $branch->id,
                    'source' => 'walk_in',
                    'started_at' => now()->subDays(random_int(0, 10)),
                ]
            );

            // Give one patient an insurance policy so insurance flows have data.
            if ($payer && str_contains($name, 'Imran')) {
                InsurancePolicy::firstOrCreate(
                    ['payer_id' => $payer->id, 'policy_no' => 'POL-' . $patient->id],
                    [
                        'patient_id' => $patient->id,
                        'plan_name' => 'Gold Plan',
                        'valid_from' => now()->subMonths(6)->toDateString(),
                        'valid_to' => now()->addMonths(6)->toDateString(),
                        'coverage_limit' => 500000,
                        'copay_percent' => 10,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
