<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_admissions', function (Blueprint $table) {
            $table->id();

            // ICU-YYYYMMDD-NNNN / CCU-... / NICU-... / PICU-...
            $table->string('icu_case_id', 30)->unique();

            // Cross-module case reference (same as ipd/opd/er use)
            $table->unsignedBigInteger('case_id')->nullable()->index();

            // Patient
            $table->unsignedBigInteger('patient_id')->index();

            // Source: where the patient came from
            $table->enum('source_type', ['ER', 'OPD', 'Ipd', 'DIRECT'])->default('DIRECT');
            $table->unsignedBigInteger('source_id')->nullable();

            // ICU classification
            $table->enum('icu_type', ['ICU', 'CCU', 'NICU', 'PICU']);

                                                              // Clinical
            $table->string('admission_type', 30)->nullable(); // Emergency / Planned / Transfer
            $table->text('admission_diagnosis')->nullable();
            $table->unsignedBigInteger('referring_doctor_id')->nullable();

            // Isolation / resource needs at admission time
            $table->enum('isolation_type', ['Airborne', 'Contact', 'Droplet', 'Standard', 'None'])
                ->default('None');
            $table->boolean('ventilator_required')->default(false);
            $table->boolean('monitor_required')->default(true);

            // Bed assignment (reuses existing beds table)
            $table->unsignedBigInteger('bed_id')->nullable()->index();

            // Lifecycle
            $table->dateTime('admission_time');
            $table->dateTime('transfer_time')->nullable();
            $table->dateTime('discharge_time')->nullable();
            $table->enum('status', [
                'Requested',
                'ResourceChecking',
                'Approved',
                'Admitted',
                'Transferred',
                'Discharged',
                'Cancelled',
                'Expired',
            ])->default('Requested');

            // Outcome (populated on close)
            $table->enum('outcome', [
                'Recovered',
                'Transferred',
                'Referred',
                'Expired',
                'LAMA',
            ])->nullable();
            $table->text('outcome_remarks')->nullable();

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['icu_type', 'status']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_admissions');
    }
};
