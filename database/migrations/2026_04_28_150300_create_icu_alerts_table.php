<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_alerts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('icu_admission_id')->index();
            $table->string('icu_case_id', 30)->index();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('bed_id')->nullable();

            $table->enum('alert_type', [
                'VitalAbnormal',
                'EquipmentUnavailable',
                'DeviceOffline',
                'PendingStatOrder',
                'CodeBlue',
                'IsolationRequired',
                'Other',
            ]);
            $table->string('vital_type', 30)->nullable();    // when alert_type=VitalAbnormal
            $table->string('observed_value', 50)->nullable();

            $table->enum('severity', ['Info', 'Warning', 'Critical'])->default('Warning');
            $table->string('message', 500);

            // Source linkage to keep alerts deduplicated against the originating record
            $table->string('source_module', 30)->nullable(); // e.g. icu_vital_logs
            $table->unsignedBigInteger('source_id')->nullable();

            $table->enum('status', ['Active', 'Acknowledged', 'Closed'])->default('Active');
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->dateTime('acknowledged_at')->nullable();
            $table->text('action_taken')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->dateTime('closed_at')->nullable();

            $table->timestamps();

            $table->index(['icu_admission_id', 'status']);
            $table->index(['severity', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_alerts');
    }
};
