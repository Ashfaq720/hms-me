<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_infection_exposure_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('icu_admission_id')->index();
            $table->string('icu_case_id', 30)->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('infection_record_id')->nullable()->index();

            $table->enum('exposure_type', ['SamePatient', 'SameBed', 'SameUnit', 'SameStaff', 'SameEquipment', 'Other']);

            $table->unsignedBigInteger('related_patient_id')->nullable();
            $table->unsignedBigInteger('related_bed_id')->nullable();
            $table->unsignedBigInteger('related_equipment_id')->nullable();
            $table->unsignedBigInteger('related_staff_id')->nullable();

            $table->dateTime('exposure_time');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_infection_exposure_logs');
    }
};
