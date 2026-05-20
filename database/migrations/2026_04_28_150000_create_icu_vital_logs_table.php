<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_vital_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('icu_admission_id')->index();
            $table->string('icu_case_id', 30)->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('bed_id')->nullable();

            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->unsignedSmallInteger('systolic_bp')->nullable();
            $table->unsignedSmallInteger('diastolic_bp')->nullable();
            $table->decimal('spo2', 5, 2)->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();

            $table->enum('source_type', ['device', 'manual'])->default('manual');
            $table->unsignedBigInteger('device_id')->nullable();

            // Worst severity assigned by classifier across the populated vitals
            $table->enum('severity', ['Normal', 'Warning', 'Critical'])->default('Normal');

            $table->dateTime('recorded_at');
            $table->unsignedBigInteger('entered_by')->nullable();

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['icu_admission_id', 'recorded_at']);
            $table->index(['icu_admission_id', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_vital_logs');
    }
};
