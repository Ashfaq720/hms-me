<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_emergency_events', function (Blueprint $table) {
            $table->id();

            $table->string('event_no', 30)->unique();   // CODE-YYYYMMDD-NNNN
            $table->unsignedBigInteger('icu_admission_id')->index();
            $table->string('icu_case_id', 30)->index();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('bed_id')->nullable();

            $table->enum('event_type', [
                'CardiacArrest',
                'RespiratoryArrest',
                'SevereDesaturation',
                'SuddenCollapse',
                'Seizure',
                'Shock',
                'Other',
            ]);

            $table->unsignedBigInteger('activated_by');
            $table->dateTime('activated_at');
            $table->dateTime('team_notified_at')->nullable();
            $table->dateTime('first_response_at')->nullable();
            $table->dateTime('doctor_arrival_at')->nullable();
            $table->dateTime('closed_at')->nullable();

            $table->enum('status', [
                'Activated',
                'TeamNotified',
                'ResponseStarted',
                'InProgress',
                'Stabilized',
                'Closed',
            ])->default('Activated');

            $table->enum('outcome', [
                'Stabilized',
                'TransferredToOT',
                'TransferredToHigherCare',
                'Expired',
                'Referred',
            ])->nullable();

            $table->text('final_remarks')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();

            $table->timestamps();

            $table->index(['icu_admission_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_emergency_events');
    }
};
