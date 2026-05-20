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
        Schema::create('approval_requests', function (Blueprint $t) {
            $t->id();
            $t->string('request_type', 50);   // PATIENT_EDIT, PRIORITY_OVERRIDE, LIMIT_OVERRIDE, PATIENT_TYPE_CHANGE...
            $t->string('reference_type', 50); // PATIENT/VISIT/Ipd_ADMISSION/APPOINTMENT/TOKEN/BILLING
            $t->unsignedBigInteger('reference_id');

            $t->json('old_values')->nullable();
            $t->json('new_values')->nullable();

            $t->string('status', 20)->default('PENDING'); // PENDING/APPROVED/REJECTED
            $t->text('reason')->nullable();

            $t->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $t->dateTime('requested_at');

            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->dateTime('approved_at')->nullable();
            $t->text('approval_notes')->nullable();

            $t->timestamps();

            $t->index(['request_type', 'status']);
            $t->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
