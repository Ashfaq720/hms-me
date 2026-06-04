<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ot_surgery_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no', 50)->unique();
            $table->unsignedBigInteger('patient_id');
            $table->string('encounter_type', 20);
            $table->unsignedBigInteger('encounter_id')->nullable();
            $table->unsignedBigInteger('ipd_admission_id')->nullable();
            $table->unsignedBigInteger('surgery_type_id')->nullable();
            $table->unsignedBigInteger('surgery_category_id')->nullable();
            $table->unsignedBigInteger('requested_by_doctor_id')->nullable();
            $table->unsignedBigInteger('primary_surgeon_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->date('requested_surgery_date')->nullable();
            $table->time('requested_surgery_time')->nullable();
            $table->unsignedInteger('estimated_duration_minutes')->nullable();
            $table->string('priority', 20)->default('Normal');
            $table->boolean('is_emergency')->default(false);
            $table->text('diagnosis')->nullable();
            $table->text('procedure_notes')->nullable();
            $table->text('clinical_indication')->nullable();
            $table->string('asa_grade', 10)->nullable();
            $table->text('special_requirements')->nullable();
            $table->boolean('blood_required')->default(false);
            $table->unsignedSmallInteger('blood_units')->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->string('status', 40)->default('Draft');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('surgery_type_id')->references('id')->on('ot_surgery_types')->onDelete('set null');
            $table->foreign('surgery_category_id')->references('id')->on('ot_surgery_categories')->onDelete('set null');
            $table->foreign('requested_by_doctor_id')->references('id')->on('doctors')->onDelete('set null');
            $table->foreign('primary_surgeon_id')->references('id')->on('doctors')->onDelete('set null');

            $table->index(['status', 'requested_surgery_date']);
            $table->index('is_emergency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ot_surgery_requests');
    }
};
