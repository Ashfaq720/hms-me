<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Minimal NICU admission record — Phase A.
 *
 * Captures the moment a newborn enters NICU (from OT delivery, IPD
 * transfer, ER, or external referral). The clinical depth (vitals,
 * feeding, growth) lives in future Phase-B tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Idempotency guard — also created by the earlier NICU module
        // migration 2026_05_20_100001_create_nicu_module_tables.
        if (Schema::hasTable('nicu_admissions')) return;

        Schema::create('nicu_admissions', function (Blueprint $table) {
            $table->id();
            $table->string('admission_no', 30)->unique();   // NICU-2026-000001

            // Mother + baby linkage
            $table->foreignId('mother_patient_id')->nullable()
                  ->constrained('patients')->nullOnDelete();
            $table->foreignId('baby_patient_id')
                  ->constrained('patients')->cascadeOnDelete();

            // Case chain
            $table->foreignId('case_id')->nullable()
                  ->constrained('case_references')->nullOnDelete();

            // Source of admission — slug + id polymorphic (same pattern
            // as ot_surgery_requests.encounter_type/encounter_id).
            $table->enum('source_type', ['OT', 'IPD', 'ER', 'External'])->index();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->index(['source_type', 'source_id'], 'nicu_source_idx');

            // Bed allocation — points at the actual bed in beds table
            // (incubators / warmers / nicu beds all live there with the
            // appropriate bed_type).
            $table->foreignId('bed_id')->nullable()
                  ->constrained('beds')->nullOnDelete();
            $table->foreignId('bed_type_id')->nullable()
                  ->constrained('bed_types')->nullOnDelete();

            // Birth details
            $table->dateTime('admitted_at');
            $table->dateTime('birth_at')->nullable();
            $table->decimal('birth_weight_grams', 8, 2)->nullable();
            $table->decimal('birth_length_cm', 6, 2)->nullable();
            $table->decimal('head_circumference_cm', 6, 2)->nullable();
            $table->unsignedSmallInteger('gestational_age_weeks')->nullable();
            $table->unsignedSmallInteger('apgar_1min')->nullable();
            $table->unsignedSmallInteger('apgar_5min')->nullable();
            $table->enum('delivery_type', ['Vaginal', 'C-Section', 'Assisted', 'Other'])->nullable();

            // Auto-set risk flags
            $table->boolean('is_preterm')->default(false);
            $table->boolean('is_low_birth_weight')->default(false);
            $table->boolean('is_critical')->default(false);
            $table->boolean('is_multiple_birth')->default(false);

            // Status
            $table->enum('status', [
                'Admitted', 'In Progress', 'Discharged',
                'Transferred', 'Deceased',
            ])->default('Admitted')->index();

            // Service package attached (if NICU package was applied)
            $table->foreignId('service_package_id')->nullable()
                  ->constrained('service_packages')->nullOnDelete();

            $table->text('clinical_notes')->nullable();
            $table->text('discharge_summary')->nullable();
            $table->dateTime('discharged_at')->nullable();
            $table->foreignId('discharged_by')->nullable()
                  ->constrained('users')->nullOnDelete();

            $table->foreignId('admitted_by')->nullable()
                  ->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['mother_patient_id', 'status'], 'nicu_mom_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nicu_admissions');
    }
};
