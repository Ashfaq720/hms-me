<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NICU Phase B — clinical observation tables. All hang off a single
 * nicu_admission_id (the baby's admission) so a NICU stay aggregates
 * monitoring, feeding, growth, MAR, procedures, infections, and consent
 * paperwork in one bundle.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Vitals (Monitoring)
        if (! Schema::hasTable('nicu_vitals')) {
            Schema::create('nicu_vitals', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->dateTime('recorded_at');
                $t->unsignedSmallInteger('heart_rate')->nullable();
                $t->unsignedSmallInteger('respiratory_rate')->nullable();
                $t->decimal('temperature_c', 4, 1)->nullable();
                $t->unsignedSmallInteger('spo2')->nullable();
                $t->unsignedSmallInteger('systolic')->nullable();
                $t->unsignedSmallInteger('diastolic')->nullable();
                $t->decimal('blood_glucose', 5, 1)->nullable();
                $t->text('notes')->nullable();
                $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['nicu_admission_id', 'recorded_at']);
            });
        }

        // Feeding & Nutrition
        if (! Schema::hasTable('nicu_feedings')) {
            Schema::create('nicu_feedings', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->dateTime('fed_at');
                $t->string('feed_type'); // Breast, Formula, EBM, TPN, NG, OG
                $t->string('route')->nullable(); // Oral, NG, OG, IV
                $t->decimal('volume_ml', 6, 1)->nullable();
                $t->boolean('tolerated')->default(true);
                $t->boolean('vomited')->default(false);
                $t->text('notes')->nullable();
                $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['nicu_admission_id', 'fed_at']);
            });
        }

        // Growth & Charting
        if (! Schema::hasTable('nicu_growth_records')) {
            Schema::create('nicu_growth_records', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->date('measured_on');
                $t->decimal('weight_grams', 7, 1)->nullable();
                $t->decimal('length_cm', 5, 1)->nullable();
                $t->decimal('head_circumference_cm', 5, 1)->nullable();
                $t->text('notes')->nullable();
                $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['nicu_admission_id', 'measured_on']);
            });
        }

        // Medication Administration Record (MAR)
        if (! Schema::hasTable('nicu_medications')) {
            Schema::create('nicu_medications', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->dateTime('administered_at');
                $t->string('drug_name');
                $t->string('dose');
                $t->string('route')->nullable(); // IV, IM, PO, etc.
                $t->string('frequency')->nullable();
                $t->text('indication')->nullable();
                $t->text('notes')->nullable();
                $t->foreignId('prescribed_by')->nullable()->constrained('doctors')->nullOnDelete();
                $t->foreignId('administered_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['nicu_admission_id', 'administered_at']);
            });
        }

        // Procedures
        if (! Schema::hasTable('nicu_procedures')) {
            Schema::create('nicu_procedures', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->dateTime('performed_at');
                $t->string('procedure_name');
                $t->string('outcome')->nullable(); // Successful, Failed, Partial
                $t->text('notes')->nullable();
                $t->foreignId('performed_by')->nullable()->constrained('doctors')->nullOnDelete();
                $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['nicu_admission_id', 'performed_at']);
            });
        }

        // Infection Control
        if (! Schema::hasTable('nicu_infections')) {
            Schema::create('nicu_infections', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->string('infection_type'); // Sepsis, NEC, Pneumonia, UTI, Other
                $t->string('organism')->nullable();
                $t->string('source')->nullable(); // CLABSI, VAP, Surgical, etc.
                $t->string('isolation_status')->nullable(); // None, Contact, Droplet, Airborne
                $t->date('identified_on');
                $t->date('resolved_on')->nullable();
                $t->text('treatment_summary')->nullable();
                $t->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['nicu_admission_id', 'identified_on']);
            });
        }

        // Parent Consent
        if (! Schema::hasTable('nicu_consents')) {
            Schema::create('nicu_consents', function (Blueprint $t) {
                $t->id();
                $t->foreignId('nicu_admission_id')->constrained('nicu_admissions')->cascadeOnDelete();
                $t->string('consent_type'); // Admission, Surgery, Blood Transfusion, Investigation, Discharge AMA, Photo, Research, Other
                $t->dateTime('signed_at');
                $t->string('signed_by_name');
                $t->string('relation_to_baby')->nullable(); // Father, Mother, Guardian
                $t->string('witness_name')->nullable();
                $t->string('document_path')->nullable();
                $t->text('notes')->nullable();
                $t->foreignId('captured_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['nicu_admission_id', 'signed_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('nicu_consents');
        Schema::dropIfExists('nicu_infections');
        Schema::dropIfExists('nicu_procedures');
        Schema::dropIfExists('nicu_medications');
        Schema::dropIfExists('nicu_growth_records');
        Schema::dropIfExists('nicu_feedings');
        Schema::dropIfExists('nicu_vitals');
    }
};
