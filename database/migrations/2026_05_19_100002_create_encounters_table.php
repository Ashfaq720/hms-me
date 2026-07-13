<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unified encounter layer (SRS §4.1, §5).
 *
 * One row per clinical/administrative episode regardless of type. OPD, IPD,
 * ER, ICU, procedure-only, lab-only, pharmacy-only and telemedicine cases
 * all live here. The existing opd_patients / i_p_d_patients / er_patients
 * tables remain (legacy detail) and gain an `encounter_id` FK to this table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encounters', function (Blueprint $table) {
            $table->id();
            $table->string('encounter_no', 32)->unique();

            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();

            // Encounter type covers every service-delivery channel.
            $table->enum('encounter_type', [
                'OPD', 'IPD', 'ER', 'ICU', 'CCU', 'NICU',
                'PROCEDURE', 'OT', 'LAB_ONLY', 'RADIOLOGY_ONLY',
                'PHARMACY_ONLY', 'AMBULANCE', 'TELEMEDICINE', 'HEALTH_CHECKUP',
            ]);

            // Where the patient came from before the encounter.
            $table->enum('source', [
                'walk_in', 'appointment', 'referral', 'corporate', 'insurance',
                'lab_only', 'pharmacy_only', 'emergency', 'transfer_in', 'follow_up',
            ])->default('walk_in');

            // For follow-ups and inter-encounter transfers.
            $table->foreignId('parent_encounter_id')->nullable()->constrained('encounters')->nullOnDelete();
            $table->foreignId('appointment_id')->nullable();
            $table->foreignId('doctor_id')->nullable();
            $table->foreignId('department_id')->nullable();

            // Polymorphic pointer to the detail record (OpdPatient / IpdPatient / ErPatient / ...).
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            $table->enum('status', [
                'draft', 'open', 'in_progress', 'on_hold', 'closed', 'cancelled',
            ])->default('open');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->string('chief_complaint')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_medico_legal')->default(false);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'encounter_type', 'status']);
            $table->index(['branch_id', 'started_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encounters');
    }
};
