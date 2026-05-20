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
        Schema::create('amb_trips', function (Blueprint $t) {
            $t->id();
            $t->foreignId('request_id')->constrained('amb_requests');
            $t->foreignId('ambulance_id')->constrained('amb_ambulances');
            $t->foreignId('driver_id')->constrained('amb_drivers');
            $t->foreignId('paramedic_id')->nullable()->constrained('amb_paramedics');
            $t->foreignId('vendor_id')->nullable()->constrained('amb_vendors');
            $t->enum('status', ['ASSIGNED', 'EN_ROUTE_PICKUP', 'PATIENT_ONBOARD', 'EN_ROUTE_HOSPITAL', 'COMPLETED'])->default('ASSIGNED');
            $t->integer('eta_minutes')->nullable();
            $t->decimal('distance_km', 8, 2)->nullable();
            $t->integer('duration_minutes')->nullable();
            $t->string('delay_reason')->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amb_trips');
    }
};
