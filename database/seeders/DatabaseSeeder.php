<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * NOTE: do NOT add `use WithoutModelEvents` here — many HMS models
 * (OtSurgeryRequest, NicuAdmission, ServicePackage, OtSurgerySchedule,
 * Patient, …) rely on the `creating` event to auto-generate their
 * code/serial number columns. WithoutModelEvents skips those events
 * and the inserts then fail with NOT NULL constraint errors on
 * fields like request_no, admission_no, etc.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // ── Foundation: permissions, roles, users, settings ───────
            PermissionSeeder::class,
            RoleSeeder::class,
            CompanySettingsSeeder::class,
            SettingsSeeder::class,
            UserSeeder::class,

            // ── Enterprise modules (SRS rollout 2026-05-19) ───────────
            // Must run BEFORE PermissionInRoleSeeder so the full permission
            // catalogue is in place when we sync everything to Super Admin
            // and Admin at the end.
            HmsEnterprisePermissionSeeder::class,
            // HmsEnterpriseDemoSeeder is intentionally disabled — it has
            // multiple schema-drift bugs (bed_groups.floor_id dropped,
            // doctors.doctor_code required, encounters.encounter_no required,
            // request_no required on ot_surgery_requests, etc). The newer
            // hospital-master seeders below provide the same demo data
            // more accurately.
            // HmsEnterpriseDemoSeeder::class,
            HmsModuleSeedSeeder::class,

            // ── Hospital master data ──────────────────────────────────
            BedMasterSeeder::class,        // floors / rooms / bed groups / bed types / beds
            ChargesMasterSeeder::class,    // charge types / categories / tax / units / charges
            LabInvestigationSeeder::class, // lab types / categories / 73 tests
            PackageMasterSeeder::class,    // 26 service packages + items + bed-price variants
            DoctorSeeder::class,           // departments / specialists / 11 demo doctors w/ users

            // ── Appointment config (must run AFTER doctors + charges) ──
            AppointmentPrioritySeeder::class, // Normal / Urgent / Emergency / Follow-up / Walk-in / VIP
            DoctorSlotSeeder::class,          // shifts + doctor_shift + slot_settings + slot_times

            // ── End-to-end demo patient journeys (needs doctors) ──────
            EndToEndSampleSeeder::class,

            // ── Modules that the original chain forgot ────────────────
            // Pharmacy masters, organization+branch+employees, demo lab
            // orders and demo bills/items. All firstOrCreate-style, safe
            // to re-run.
            MissingModulesSeeder::class,

            // ── Phase 1: unify medicines under inventory_items ────────
            // Creates inventory_warehouses, inventory_items per medicine,
            // mirrors medicine_batches → inventory_item_batches, and
            // writes opening stock_movements. Pharmacy + Inventory now
            // share a single live-quantity source.
            UnifyMedicineInventorySeeder::class,

            // ── Backfill medicines.medical_group_id + company_id from
            //    keyword mapping so Drug Master + Inventory pages show
            //    real "group / brand" data. Idempotent.
            BackfillMedicineRelationsSeeder::class,

            // ── Demo non-medicine inventory (consumables + assets) so the
            //    Inventory > Items > Consumables / Equipment filters show
            //    real rows. Idempotent.
            InventoryDemoItemsSeeder::class,

            // ── Backfill icu_equipment.inventory_item_id + seed NICU
            //    equipment so the ICU/CCU/NICU pages all show data and
            //    every row is linked to the unified inventory master.
            IcuEquipmentUnifySeeder::class,

            // ── FINAL STEP: ensure Super Admin + Admin see every menu ──
            // Runs last so it picks up every permission created by any
            // earlier seeder, including HmsEnterprisePermissionSeeder.
            PermissionInRoleSeeder::class,
        ]);
    }
}
