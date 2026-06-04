<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ER (Emergency Room) module — operational layer.
 * Triage / clinical notes / hourly observations / transfer requests.
 * Linked into the unified encounter layer so charges & bills flow.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 5-level triage assessment (Red / Orange / Yellow / Green / Black)
        if (! Schema::hasTable('er_triages')) {
            Schema::create('er_triages', function (Blueprint $t) {
                $t->id();
                $t->foreignId('er_patient_id')->constrained('er_patients')->cascadeOnDelete();
                $t->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
                $t->enum('triage_level', ['RED', 'ORANGE', 'YELLOW', 'GREEN', 'BLACK']);
                $t->unsignedTinyInteger('pain_score')->nullable();
                $t->string('consciousness_level', 32)->nullable();
                $t->string('chief_complaint', 500)->nullable();
                // Vitals snapshot at triage
                $t->string('blood_pressure', 16)->nullable();
                $t->unsignedSmallInteger('pulse')->nullable();
                $t->unsignedSmallInteger('respiratory_rate')->nullable();
                $t->unsignedTinyInteger('spo2')->nullable();
                $t->decimal('temperature_c', 4, 1)->nullable();
                $t->decimal('blood_glucose_mgdl', 5, 1)->nullable();
                $t->text('notes')->nullable();
                $t->foreignId('triaged_by')->nullable()->constrained('users')->nullOnDelete();
                $t->dateTime('triaged_at')->useCurrent();
                $t->timestamps();
                $t->index(['er_patient_id', 'triage_level']);
            });
        }

        // SOAP-style clinical notes (multiple per ER visit)
        if (! Schema::hasTable('er_clinical_notes')) {
            Schema::create('er_clinical_notes', function (Blueprint $t) {
                $t->id();
                $t->foreignId('er_patient_id')->constrained('er_patients')->cascadeOnDelete();
                $t->enum('note_type', ['SOAP', 'PROGRESS', 'PROCEDURE', 'CONSULT', 'DISCHARGE'])->default('SOAP');
                $t->text('subjective')->nullable();
                $t->text('objective')->nullable();
                $t->text('assessment')->nullable();
                $t->text('plan')->nullable();
                $t->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
                $t->dateTime('recorded_at')->useCurrent();
                $t->boolean('signed')->default(false);
                $t->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['er_patient_id', 'note_type']);
            });
        }

        // Hourly observation chart (BP/Pulse/Temp/RR/SpO2/Pain + IO + O2)
        if (! Schema::hasTable('er_observations')) {
            Schema::create('er_observations', function (Blueprint $t) {
                $t->id();
                $t->foreignId('er_patient_id')->constrained('er_patients')->cascadeOnDelete();
                $t->dateTime('observed_at');
                $t->string('blood_pressure', 16)->nullable();
                $t->unsignedSmallInteger('pulse')->nullable();
                $t->unsignedSmallInteger('respiratory_rate')->nullable();
                $t->unsignedTinyInteger('spo2')->nullable();
                $t->decimal('temperature_c', 4, 1)->nullable();
                $t->unsignedTinyInteger('pain_score')->nullable();
                $t->decimal('fluid_intake_ml', 8, 2)->nullable();
                $t->decimal('fluid_output_ml', 8, 2)->nullable();
                $t->decimal('o2_lpm', 4, 1)->nullable();
                $t->string('consciousness', 32)->nullable();
                $t->text('notes')->nullable();
                $t->foreignId('observed_by')->nullable()->constrained('users')->nullOnDelete();
                $t->boolean('alert_critical')->default(false);
                $t->timestamps();
                $t->index(['er_patient_id', 'observed_at']);
            });
        }

        // Transfer request out of ER → IPD/ICU/CCU/NICU/OT/Ward/Home
        if (! Schema::hasTable('er_transfers')) {
            Schema::create('er_transfers', function (Blueprint $t) {
                $t->id();
                $t->foreignId('er_patient_id')->constrained('er_patients')->cascadeOnDelete();
                $t->enum('target', ['IPD', 'ICU', 'CCU', 'NICU', 'OT', 'WARD', 'HOME', 'REFERRED', 'EXPIRED']);
                $t->foreignId('target_bed_id')->nullable()->constrained('beds')->nullOnDelete();
                $t->foreignId('target_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
                $t->text('handover_summary')->nullable();
                $t->text('clinical_indication')->nullable();
                $t->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED', 'CANCELLED', 'COMPLETED'])->default('PENDING');
                $t->dateTime('requested_at')->useCurrent();
                $t->dateTime('accepted_at')->nullable();
                $t->dateTime('completed_at')->nullable();
                $t->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
                $t->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
                // Cross-reference to whatever was created on the other side
                $t->string('target_reference_type', 64)->nullable();
                $t->unsignedBigInteger('target_reference_id')->nullable();
                $t->timestamps();
                $t->index(['er_patient_id', 'target']);
                $t->index(['status', 'requested_at']);
            });
        }
    }

    public function down(): void
    {
        foreach (['er_transfers', 'er_observations', 'er_clinical_notes', 'er_triages'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
