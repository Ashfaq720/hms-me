<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FR-09: buffer time for cleaning between cases
        Schema::table('ot_surgery_schedules', function (Blueprint $table) {
            $table->unsignedSmallInteger('buffer_minutes')->default(30)->after('scheduled_end');
            $table->dateTime('cleaning_buffer_until')->nullable()->after('buffer_minutes');
        });

        // FR-07: technician specialization
        Schema::table('ot_surgery_teams', function (Blueprint $table) {
            $table->string('specialization', 50)->nullable()->after('role');
            $table->dateTime('released_at')->nullable()->after('notes');
            $table->string('released_reason')->nullable()->after('released_at');
        });

        // FR-13: equipment release on cancel
        Schema::table('ot_schedule_equipments', function (Blueprint $table) {
            $table->dateTime('released_at')->nullable()->after('notes');
            $table->string('released_reason')->nullable()->after('released_at');
        });

        // FR-04 / FR-05: doctor unavailability (roster / leave / on-call / OPD overlap)
        Schema::create('ot_doctor_unavailability', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('reason', 30);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->index(['doctor_id', 'start_at', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ot_doctor_unavailability');

        Schema::table('ot_schedule_equipments', function (Blueprint $table) {
            $table->dropColumn(['released_at', 'released_reason']);
        });

        Schema::table('ot_surgery_teams', function (Blueprint $table) {
            $table->dropColumn(['specialization', 'released_at', 'released_reason']);
        });

        Schema::table('ot_surgery_schedules', function (Blueprint $table) {
            $table->dropColumn(['buffer_minutes', 'cleaning_buffer_until']);
        });
    }
};
