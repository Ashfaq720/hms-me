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
        Schema::create('amb_requests', function (Blueprint $t) {
            $t->id();
            $t->enum('source', ['ER_DESK', 'OPD', 'Ipd', 'CALL_CENTER', 'REFERRAL'])->nullable();
            $t->enum('request_type', ['EMERGENCY', 'NORMAL', 'TRANSFER', 'SCHEDULED']);
            $t->enum('priority', ['LOW', 'CRITICAL', 'HIGH', 'NORMAL'])->default('NORMAL');
            $t->string('pick_up_location');
            $t->string('contact_no')->nullable();
            $t->date('date')->nullable();
            $t->time('time')->nullable();
            $t->boolean('is_unknown_patient')->default(0);
            $t->decimal('pickup_lat', 10, 7)->nullable();
            $t->decimal('pickup_lng', 10, 7)->nullable();
            $t->string('drop_location')->nullable();
            $t->decimal('drop_lat', 10, 7)->nullable();
            $t->decimal('drop_lng', 10, 7)->nullable();
            $t->enum('patient_condition', ['CRITICAL', 'STABLE'])->default('STABLE');
            $t->foreignId('patient_id')->nullable()->constrained('patients'); // your HMS patients table ✅
            $t->string('temp_patient_id')->nullable();
            $t->enum('status', ['NEW', 'ASSIGNED', 'CANCELLED', 'COMPLETED'])->default('NEW');
            $t->foreignId('created_by')->nullable()->constrained('users');
            $t->foreignId('ambulance_id')->nullable()->constrained('amb_ambulances');
            $t->foreignId('driver_id')->nullable()->constrained('amb_drivers');
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amb_requests');
    }
};
