<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ot_post_op_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surgery_schedule_id');
            $table->text('procedure_summary')->nullable();
            $table->text('immediate_findings')->nullable();
            $table->text('post_op_diagnosis')->nullable();
            $table->text('orders')->nullable();
            $table->text('medications')->nullable();
            $table->text('care_instructions')->nullable();
            $table->text('follow_up_plan')->nullable();
            $table->string('disposition', 30)->nullable();
            $table->unsignedBigInteger('signed_by')->nullable();
            $table->dateTime('signed_at')->nullable();
            $table->timestamps();

            $table->foreign('surgery_schedule_id')->references('id')->on('ot_surgery_schedules')->onDelete('cascade');
            $table->unique('surgery_schedule_id');
        });

        Schema::create('ot_pacu_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surgery_schedule_id');
            $table->dateTime('admitted_at')->nullable();
            $table->dateTime('discharged_at')->nullable();
            $table->string('bed_no')->nullable();
            $table->json('vitals_log')->nullable();
            $table->text('pain_score_log')->nullable();
            $table->text('medications_given')->nullable();
            $table->text('observations')->nullable();
            $table->unsignedTinyInteger('aldrete_score')->nullable();
            $table->string('discharge_destination', 30)->nullable();
            $table->string('status', 30)->default('In Recovery');
            $table->unsignedBigInteger('discharged_by')->nullable();
            $table->timestamps();

            $table->foreign('surgery_schedule_id')->references('id')->on('ot_surgery_schedules')->onDelete('cascade');
        });

        Schema::create('ot_cleaning_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ot_room_id');
            $table->unsignedBigInteger('surgery_schedule_id')->nullable();
            $table->string('cleaning_type', 30)->default('routine');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->text('checklist')->nullable();
            $table->boolean('is_complete')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('ot_room_id')->references('id')->on('ot_rooms')->onDelete('cascade');
            $table->foreign('surgery_schedule_id')->references('id')->on('ot_surgery_schedules')->onDelete('set null');
        });

        Schema::create('ot_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surgery_request_id')->nullable();
            $table->unsignedBigInteger('surgery_schedule_id')->nullable();
            $table->string('document_type', 50);
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->boolean('is_signed')->default(false);
            $table->dateTime('signed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('surgery_request_id')->references('id')->on('ot_surgery_requests')->onDelete('cascade');
            $table->foreign('surgery_schedule_id')->references('id')->on('ot_surgery_schedules')->onDelete('cascade');
        });

        Schema::create('ot_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 60);
            $table->unsignedBigInteger('entity_id');
            $table->string('action', 60);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->text('reason')->nullable();
            $table->json('payload')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ot_audit_logs');
        Schema::dropIfExists('ot_documents');
        Schema::dropIfExists('ot_cleaning_logs');
        Schema::dropIfExists('ot_pacu_records');
        Schema::dropIfExists('ot_post_op_notes');
    }
};
