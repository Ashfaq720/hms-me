<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NICU module — neonatal lifecycle (admission → monitoring → feeding →
 * growth → procedures → infection control → consent → discharge).
 * Linked to existing ipd_patient + encounters + patients (mother) so the
 * service-charge engine and billing pipeline pick up NICU charges
 * automatically.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Neonatal admission core
        if (! Schema::hasTable('nicu_admissions')) {
            Schema::create('nicu_admissions', function (Blueprint $t) {
                $t->id();
                $t->foreignId('ipd_patient_id')->nullable()->constrained('i_p_d_patients')->nullOnDelete();
                $t->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
                $t->foreignId('encounter_id')->nullable()->constrained('encounters')->nullOnDelete();
                $t->foreignId('mother_patient_id')->nullable()->constrained('patients')->nullOnDelete();
                $t->foreignId('mother_ipd_patient_id')->nullable()->constrained('i_p_d_patients')->nullOnDelete();
                $t->string('baby_id', 32)->unique();
                $t->enum('source', ['DELIVERY_ROOM', 'OT', 'ER', 'IPD_TRANSFER', 'EXTERNAL_REFERRAL'])->default('DELIVERY_ROOM');
                $t->enum('birth_type', ['NORMAL', 'C_SECTION', 'ASSISTED'])->default('NORMAL');
                $t->boolean('is_multiple_birth')->default(false);
                $t->unsignedTinyInteger('birth_order')->nullable();
                $t->decimal('birth_weight_g', 8, 2)->nullable();
                $t->decimal('birth_length_cm', 6, 2)->nullable();
                $t->decimal('head_circumference_cm', 6, 2)->nullable();
                $t->unsignedSmallInteger('gestational_age_weeks')->nullable();
                $t->unsignedTinyInteger('apgar_1min')->nullable();
                $t->unsignedTinyInteger('apgar_5min')->nullable();
                $t->unsignedTinyInteger('apgar_10min')->nullable();
                $t->enum('admission_priority', ['ROUTINE', 'URGENT', 'CRITICAL'])->default('ROUTINE');
                // Risk flags (auto-computed)
                $t->boolean('is_low_birth_weight')->default(false);
                $t->boolean('is_preterm')->default(false);
                $t->boolean('is_critical')->default(false);
                $t->enum('status', ['admitted', 'discharged', 'transferred', 'expired'])->default('admitted');
                $t->dateTime('admission_time');
                $t->dateTime('discharge_time')->nullable();
                $t->text('admission_notes')->nullable();
                $t->foreignId('admitted_by')->nullable()->constrained('users')->nullOnDelete();
                $t->softDeletes();
                $t->timestamps();
                $t->index(['patient_id', 'status']);
                $t->index('mother_patient_id', 'nicu_mother_idx');
            });
        }

        // 2. NICU resource allocation (incubator / warmer / nicu bed / isolation)
        if (! Schema::hasTable('nicu_resource_allocations')) {
            Schema::create('nicu_resource_allocations', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->foreignId('bed_id')->nullable()->constrained('beds')->nullOnDelete();
                $t->enum('resource_type', ['NICU_BED', 'INCUBATOR', 'WARMER', 'ISOLATION'])->default('NICU_BED');
                $t->string('device_serial', 64)->nullable();
                $t->dateTime('from');
                $t->dateTime('to')->nullable();
                $t->enum('status', ['active', 'released', 'transferred'])->default('active');
                $t->text('reason')->nullable();
                $t->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['nicu_admission_id', 'status']);
            });
        }

        // 3. Real-time vitals
        if (! Schema::hasTable('nicu_vitals')) {
            Schema::create('nicu_vitals', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->dateTime('recorded_at');
                $t->unsignedSmallInteger('heart_rate')->nullable();
                $t->unsignedSmallInteger('respiratory_rate')->nullable();
                $t->unsignedTinyInteger('spo2')->nullable();
                $t->decimal('temperature_c', 4, 1)->nullable();
                $t->decimal('blood_glucose_mgdl', 5, 1)->nullable();
                $t->enum('source', ['DEVICE', 'MANUAL'])->default('MANUAL');
                $t->string('device_id', 64)->nullable();
                // Auto-derived alert flags
                $t->boolean('alert_apnea')->default(false);
                $t->boolean('alert_hypothermia')->default(false);
                $t->boolean('alert_spo2_critical')->default(false);
                $t->boolean('alert_hr_abnormal')->default(false);
                $t->enum('alert_level', ['NORMAL', 'WARNING', 'CRITICAL'])->default('NORMAL');
                $t->text('notes')->nullable();
                $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['nicu_admission_id', 'recorded_at']);
                $t->index('alert_level');
            });
        }

        // 4. Feeding plan + feed log
        if (! Schema::hasTable('nicu_feeding_schedules')) {
            Schema::create('nicu_feeding_schedules', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->enum('feed_type', ['BREAST_MILK', 'FORMULA', 'EBM', 'IV_FLUID', 'TPN'])->default('BREAST_MILK');
                $t->unsignedSmallInteger('interval_hours')->default(3);
                $t->decimal('volume_ml', 6, 2)->nullable();
                $t->date('start_date');
                $t->date('end_date')->nullable();
                $t->boolean('is_active')->default(true);
                $t->foreignId('prescribed_by')->nullable()->constrained('doctors')->nullOnDelete();
                $t->text('notes')->nullable();
                $t->timestamps();
                $t->index('nicu_admission_id');
            });
        }

        if (! Schema::hasTable('nicu_feed_logs')) {
            Schema::create('nicu_feed_logs', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->foreignId('schedule_id')->nullable()->constrained('nicu_feeding_schedules')->nullOnDelete();
                $t->dateTime('fed_at');
                $t->enum('feed_type', ['BREAST_MILK', 'FORMULA', 'EBM', 'IV_FLUID', 'TPN'])->default('BREAST_MILK');
                $t->decimal('volume_ml', 6, 2);
                $t->enum('tolerance', ['GOOD', 'POOR', 'REGURGITATED', 'REFUSED'])->default('GOOD');
                $t->text('notes')->nullable();
                $t->foreignId('fed_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['nicu_admission_id', 'fed_at']);
            });
        }

        // 5. Growth tracking
        if (! Schema::hasTable('nicu_growth_records')) {
            Schema::create('nicu_growth_records', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->date('measured_on');
                $t->decimal('weight_g', 8, 2);
                $t->decimal('length_cm', 6, 2)->nullable();
                $t->decimal('head_circumference_cm', 6, 2)->nullable();
                $t->decimal('weight_change_pct', 6, 2)->nullable();
                $t->boolean('alert_weight_loss')->default(false);
                $t->text('notes')->nullable();
                $t->foreignId('measured_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['nicu_admission_id', 'measured_on']);
            });
        }

        // 6. Medication MAR (weight-based dose tracking)
        if (! Schema::hasTable('nicu_medication_orders')) {
            Schema::create('nicu_medication_orders', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->foreignId('medicine_id')->nullable()->constrained('medicines')->nullOnDelete();
                $t->string('drug_name', 191);
                $t->decimal('dose_per_kg_mg', 8, 3);
                $t->decimal('weight_used_kg', 5, 3);
                $t->decimal('total_dose_mg', 8, 3);
                $t->string('route', 32)->default('IV');
                $t->string('frequency', 32)->default('q8h');
                $t->date('start_date');
                $t->date('end_date')->nullable();
                $t->boolean('safety_override')->default(false);
                $t->text('override_reason')->nullable();
                $t->enum('status', ['ordered', 'active', 'completed', 'discontinued'])->default('ordered');
                $t->foreignId('prescribed_by')->nullable()->constrained('doctors')->nullOnDelete();
                $t->timestamps();
                $t->index(['nicu_admission_id', 'status']);
            });
        }

        if (! Schema::hasTable('nicu_medication_administrations')) {
            Schema::create('nicu_medication_administrations', function (Blueprint $t) {
                $t->id();
                $t->foreignId('order_id')->constrained('nicu_medication_orders')->cascadeOnDelete();
                $t->dateTime('administered_at');
                $t->decimal('dose_given_mg', 8, 3);
                $t->enum('status', ['given', 'held', 'refused', 'missed'])->default('given');
                $t->text('notes')->nullable();
                $t->foreignId('administered_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
            });
        }

        // 7. Procedures (phototherapy, intubation, etc.)
        if (! Schema::hasTable('nicu_procedures')) {
            Schema::create('nicu_procedures', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->string('procedure_code', 32);
                $t->string('procedure_name', 191);
                $t->dateTime('start_time');
                $t->dateTime('end_time')->nullable();
                $t->string('device_id', 64)->nullable();
                $t->enum('status', ['ongoing', 'completed', 'aborted'])->default('ongoing');
                $t->text('clinical_indication')->nullable();
                $t->text('outcome')->nullable();
                $t->foreignId('performed_by')->nullable()->constrained('doctors')->nullOnDelete();
                $t->timestamps();
                $t->index(['nicu_admission_id', 'status']);
            });
        }

        // 8. Infection control
        if (! Schema::hasTable('nicu_infection_records')) {
            Schema::create('nicu_infection_records', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->string('infection_type', 64);
                $t->string('organism', 128)->nullable();
                $t->date('detected_on');
                $t->date('resolved_on')->nullable();
                $t->enum('isolation_required', ['NONE', 'CONTACT', 'DROPLET', 'AIRBORNE'])->default('NONE');
                $t->boolean('alert_cluster')->default(false);
                $t->text('antibiotics_used')->nullable();
                $t->enum('status', ['active', 'resolved'])->default('active');
                $t->text('notes')->nullable();
                $t->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['nicu_admission_id', 'status']);
                $t->index(['infection_type', 'detected_on'], 'nicu_inf_cluster_idx');
            });
        }

        // 9. Parent consent + communication
        if (! Schema::hasTable('nicu_consents')) {
            Schema::create('nicu_consents', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->string('consent_type', 64);
                $t->string('guardian_name', 191);
                $t->string('guardian_relation', 32);
                $t->string('guardian_phone', 32)->nullable();
                $t->dateTime('signed_at');
                $t->string('document_path', 255)->nullable();
                $t->enum('status', ['valid', 'revoked', 'expired'])->default('valid');
                $t->text('notes')->nullable();
                $t->foreignId('witnessed_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['nicu_admission_id', 'consent_type']);
            });
        }

        // 10. Discharge summary
        if (! Schema::hasTable('nicu_discharge_summaries')) {
            Schema::create('nicu_discharge_summaries', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->date('discharge_date');
                $t->decimal('discharge_weight_g', 8, 2)->nullable();
                $t->text('final_diagnosis')->nullable();
                $t->text('treatment_summary')->nullable();
                $t->text('discharge_medications')->nullable();
                $t->text('feeding_advice')->nullable();
                $t->text('vaccination_plan')->nullable();
                $t->date('follow_up_date')->nullable();
                $t->text('follow_up_advice')->nullable();
                $t->enum('discharge_disposition', ['HOME', 'TRANSFER', 'EXPIRED', 'DAMA'])->default('HOME');
                $t->foreignId('approved_by')->nullable()->constrained('doctors')->nullOnDelete();
                $t->timestamps();
                $t->unique('nicu_admission_id');
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'nicu_discharge_summaries',
            'nicu_consents',
            'nicu_infection_records',
            'nicu_procedures',
            'nicu_medication_administrations',
            'nicu_medication_orders',
            'nicu_growth_records',
            'nicu_feed_logs',
            'nicu_feeding_schedules',
            'nicu_vitals',
            'nicu_resource_allocations',
            'nicu_admissions',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
