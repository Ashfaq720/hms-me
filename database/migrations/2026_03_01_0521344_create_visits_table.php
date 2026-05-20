<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $t) {
            $t->id();
            $t->string('visit_no')->unique();      // OPD-YYYYMMDD-0001 / ER-...
            $t->string('visit_type', 10);          // OPD/ER

            $t->foreignId('patient_id')->constrained('patients')->restrictOnDelete();

            // Exactly one link required: source_type=APPOINTMENT/TOKEN; source_id = id
            $t->string('source_type', 20)->nullable(); // APPOINTMENT/TOKEN
            $t->unsignedBigInteger('source_id')->nullable();

            $t->foreignId('doctor_id')->constrained('doctors')->restrictOnDelete();
            $t->foreignId('department_id')->constrained('departments')->restrictOnDelete();

            // OPD fields
            $t->text('chief_complaint')->nullable();
            $t->string('history_flag', 20)->nullable(); // NEW/FOLLOW_UP
            $t->json('quick_flags')->nullable();        // Fever/Pain/etc (configurable)

            // Status and timestamps
            $t->string('status', 30)->default('WAITING'); // WAITING/IN_CONSULTATION/COMPLETED/CANCELLED/REFERRED
            $t->dateTime('waiting_started_at')->nullable();
            $t->dateTime('consultation_started_at')->nullable();
            $t->dateTime('consultation_ended_at')->nullable();
            $t->dateTime('cancelled_at')->nullable();

            // ER fields
            $t->string('arrival_mode', 20)->nullable();  // WALK_IN/AMBULANCE
            $t->string('triage_priority', 20)->nullable(); // CRITICAL/HIGH/NORMAL
            $t->string('initial_priority', 20)->nullable();
            $t->text('triage_notes')->nullable();
            $t->unsignedBigInteger('ambulance_trip_id')->nullable(); // optional integration id

            $t->timestamps();

            $t->index(['visit_type', 'created_at']);
            $t->index(['doctor_id', 'status']);
            $t->index(['patient_id', 'status']);
            $t->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
