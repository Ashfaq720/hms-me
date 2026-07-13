<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ot_surgery_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_no', 50)->unique();
            $table->unsignedBigInteger('surgery_request_id');
            $table->unsignedBigInteger('ot_room_id');
            $table->dateTime('scheduled_start');
            $table->dateTime('scheduled_end');
            $table->dateTime('actual_start')->nullable();
            $table->dateTime('actual_end')->nullable();
            $table->string('status', 40)->default('Scheduled');
            $table->boolean('emergency_fast_track')->default(false);
            $table->text('cancellation_reason')->nullable();
            $table->text('reschedule_reason')->nullable();
            $table->unsignedBigInteger('rescheduled_from_schedule_id')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('surgery_request_id')->references('id')->on('ot_surgery_requests')->onDelete('cascade');
            $table->foreign('ot_room_id')->references('id')->on('ot_rooms')->onDelete('restrict');

            $table->index(['ot_room_id', 'scheduled_start', 'scheduled_end'], 'ot_sched_room_time_idx');
            $table->index(['status', 'scheduled_start']);
        });

        Schema::create('ot_surgery_teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surgery_schedule_id');
            $table->string('role', 40);
            $table->unsignedBigInteger('staff_id');
            $table->string('staff_type', 20)->default('user');
            $table->boolean('is_primary')->default(false);
            $table->dateTime('assigned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('surgery_schedule_id')->references('id')->on('ot_surgery_schedules')->onDelete('cascade');
            $table->index(['staff_id', 'staff_type']);
            $table->index(['surgery_schedule_id', 'role']);
        });

        Schema::create('ot_schedule_equipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surgery_schedule_id');
            $table->unsignedBigInteger('ot_equipment_id');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('surgery_schedule_id')->references('id')->on('ot_surgery_schedules')->onDelete('cascade');
            $table->foreign('ot_equipment_id')->references('id')->on('ot_equipments')->onDelete('cascade');
            $table->unique(['surgery_schedule_id', 'ot_equipment_id'], 'ot_sched_equip_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ot_schedule_equipments');
        Schema::dropIfExists('ot_surgery_teams');
        Schema::dropIfExists('ot_surgery_schedules');
    }
};
