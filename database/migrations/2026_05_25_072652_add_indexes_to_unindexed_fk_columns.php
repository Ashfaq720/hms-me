<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds indexes to FK-like columns (*_id) that were missing them.
 * Generated from a live SHOW COLUMNS scan — 111 columns across 60 tables.
 * Each index is created only if absent (idempotent).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->targets() as $table => $columns) {
            if (! Schema::hasTable($table)) continue;
            foreach ($columns as $col) {
                if (! Schema::hasColumn($table, $col)) continue;
                if ($this->indexExists($table, $col)) continue;
                Schema::table($table, fn (Blueprint $t) => $t->index($col, "{$table}_{$col}_idx"));
            }
        }
    }

    public function down(): void
    {
        foreach ($this->targets() as $table => $columns) {
            if (! Schema::hasTable($table)) continue;
            foreach ($columns as $col) {
                $idx = "{$table}_{$col}_idx";
                if ($this->indexExistsByName($table, $idx)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($idx));
                }
            }
        }
    }

    protected function indexExists(string $table, string $column): bool
    {
        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Column_name = ?", [$column]);
        return ! empty($rows);
    }

    protected function indexExistsByName(string $table, string $name): bool
    {
        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$name]);
        return ! empty($rows);
    }

    /** Tables and columns to index. */
    protected function targets(): array
    {
        return [
            "activity_log" => ["subject_id", "causer_id"],
            "amb_assignment_audits" => ["emergency_request_id"],
            "amb_requests" => ["temp_patient_id"],
            "appointments" => ["visit_details_id", "global_shift_id", "shift_id"],
            "approval_requests" => ["reference_id"],
            "bed_types" => ["default_package_id"],
            "beds" => ["default_package_id"],
            "doctor_slot_settings" => ["charge_category_id", "charge_id"],
            "employees" => ["national_id", "department_id", "designation_id"],
            "encounters" => ["appointment_id", "doctor_id", "department_id", "subject_id"],
            "er_transfers" => ["target_reference_id"],
            "fd_vital_checks" => ["machine_device_id"],
            "gl_journals" => ["reference_id"],
            "icu_admission_overrides" => ["temporary_bed_id"],
            "icu_admissions" => ["source_id", "referring_doctor_id"],
            "icu_alerts" => ["patient_id", "bed_id", "source_id"],
            "icu_antibiotic_usage_logs" => ["medicine_id", "culture_report_id"],
            "icu_billing_mode_audit_logs" => ["old_package_id", "new_package_id"],
            "icu_doctor_orders" => ["doctor_id", "linked_id"],
            "icu_emergency_events" => ["patient_id", "bed_id"],
            "icu_emergency_notifications" => ["user_id"],
            "icu_equipment_change_logs" => ["old_equipment_id", "new_equipment_id", "old_usage_log_id", "new_usage_log_id"],
            "icu_equipment_usage_logs" => ["bed_id", "package_enrollment_id"],
            "icu_infection_control_overrides" => ["assigned_bed_id"],
            "icu_infection_exposure_logs" => ["related_patient_id", "related_bed_id", "related_equipment_id", "related_staff_id"],
            "icu_infection_records" => ["lab_report_id"],
            "icu_mortality_audits" => ["code_blue_event_id"],
            "icu_patient_package_enrollments" => ["package_id"],
            "icu_procedure_order" => ["doctor_id"],
            "icu_transfers" => ["from_bed_id", "to_bed_id", "to_ipd_id"],
            "icu_vital_logs" => ["bed_id", "device_id"],
            "ipd_nurse_note_replies" => ["user_id"],
            "nicu_procedures" => ["device_id"],
            "nicu_vitals" => ["device_id"],
            "notifications_logs" => ["reference_id"],
            "opd_patient_departments" => ["doctor_id", "department_id"],
            "opd_patients" => ["shift_id"],
            "operation_histories" => ["case_id", "opd_id", "ipd_id", "er_id"],
            "ot_anesthesia_records" => ["anesthetist_id"],
            "ot_audit_logs" => ["entity_id", "user_id"],
            "ot_consumable_usages" => ["patient_charge_id"],
            "ot_consumables" => ["linked_medicine_id"],
            "ot_notifications" => ["entity_id"],
            "ot_surgery_requests" => ["encounter_id", "ipd_admission_id", "department_id"],
            "ot_surgery_schedules" => ["rescheduled_from_schedule_id"],
            "ot_transfers" => ["porter_id", "nurse_id"],
            "package_consumption_entries" => ["source_id"],
            "packages" => ["department_id"],
            "passes" => ["reference_id"],
            "patient_charges" => ["ipd_id", "opd_id", "appointment_id", "er_register_id", "pathology_id", "radiology_id", "blood_bank_id", "pharmacy_id", "charge_id"],
            "patients" => ["organization_id", "lang_id"],
            "personal_access_tokens" => ["tokenable_id"],
            "purchase_orders" => ["supplier_id"],
            "service_charge_postings" => ["trigger_source_id"],
            "service_package_items" => ["master_id"],
            "stock_movements" => ["source_id"],
            "storage_locations" => ["device_id"],
            "telemedicine_sessions" => ["appointment_id", "doctor_id", "meeting_id"],
            "transactions" => ["opd_patient_id", "pathology_bill_id", "pharmacy_bill_id", "radiology_bill_id", "blood_bank_bill_id", "mfs_transaction_id"],
            "visits" => ["source_id", "ambulance_trip_id"],
        ];
    }
};
