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
        Schema::create('amb_assignment_audits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('trip_id')->constrained('amb_trips');
            $t->enum('event_type', ['ASSIGNED', 'REASSIGNED', 'CANCELLED', 'EMERGENCY_OVERRIDE']);
            $t->foreignId('prev_ambulance_id')->nullable()->constrained('amb_ambulances');
            $t->foreignId('new_ambulance_id')->nullable()->constrained('amb_ambulances');
            $t->foreignId('prev_driver_id')->nullable()->constrained('amb_drivers');
            $t->foreignId('new_driver_id')->nullable()->constrained('amb_drivers');
            $t->foreignId('prev_paramedic_id')->nullable()->constrained('amb_paramedics');
            $t->foreignId('new_paramedic_id')->nullable()->constrained('amb_paramedics');
            $t->boolean('override_flag')->default(false);
            $t->unsignedBigInteger('emergency_request_id')->nullable();
            $t->string('sla_status_at_change')->nullable();
            $t->text('reason')->nullable();
            $t->foreignId('changed_by')->nullable()->constrained('users');
            $t->timestamp('changed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amb_assignment_audits');
    }
};
