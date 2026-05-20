<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_vital_thresholds', function (Blueprint $table) {
            $table->id();

            // Patient-specific override; if NULL the row acts as a global default
            $table->unsignedBigInteger('icu_admission_id')->nullable()->index();
            $table->unsignedBigInteger('patient_id')->nullable()->index();

            $table->enum('vital_type', [
                'HeartRate', 'SystolicBP', 'DiastolicBP', 'SpO2', 'RespiratoryRate', 'Temperature',
            ]);

            $table->decimal('normal_min', 8, 2)->nullable();
            $table->decimal('normal_max', 8, 2)->nullable();
            $table->decimal('warning_min', 8, 2)->nullable();
            $table->decimal('warning_max', 8, 2)->nullable();
            $table->decimal('critical_min', 8, 2)->nullable();
            $table->decimal('critical_max', 8, 2)->nullable();

            $table->unsignedBigInteger('configured_by')->nullable();
            $table->timestamps();

            $table->unique(['icu_admission_id', 'vital_type'], 'icu_vital_thr_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_vital_thresholds');
    }
};
