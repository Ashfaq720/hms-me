<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ot_pre_op_checklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surgery_schedule_id');
            $table->boolean('consent_obtained')->default(false);
            $table->boolean('lab_completed')->default(false);
            $table->boolean('radiology_completed')->default(false);
            $table->boolean('fasting_confirmed')->default(false);
            $table->boolean('blood_arranged')->default(false);
            $table->boolean('allergy_reviewed')->default(false);
            $table->boolean('vitals_recorded')->default(false);
            $table->boolean('anesthesia_clearance')->default(false);
            $table->boolean('doctor_clearance')->default(false);
            $table->boolean('nurse_confirmation')->default(false);
            $table->boolean('site_marked')->default(false);
            $table->boolean('id_band_verified')->default(false);
            $table->json('vitals_snapshot')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('emergency_override')->default(false);
            $table->text('emergency_override_reason')->nullable();
            $table->unsignedBigInteger('override_approved_by')->nullable();
            $table->boolean('is_complete')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamps();

            $table->foreign('surgery_schedule_id')->references('id')->on('ot_surgery_schedules')->onDelete('cascade');
            $table->unique('surgery_schedule_id');
        });

        Schema::create('ot_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surgery_schedule_id');
            $table->string('direction', 20);
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->dateTime('initiated_at')->nullable();
            $table->dateTime('arrived_at')->nullable();
            $table->unsignedBigInteger('porter_id')->nullable();
            $table->unsignedBigInteger('nurse_id')->nullable();
            $table->string('status', 30)->default('Initiated');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('surgery_schedule_id')->references('id')->on('ot_surgery_schedules')->onDelete('cascade');
            $table->index(['surgery_schedule_id', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ot_transfers');
        Schema::dropIfExists('ot_pre_op_checklists');
    }
};
