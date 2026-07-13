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
        Schema::create('passes', function (Blueprint $t) {
            $t->id();
            $t->string('pass_no')->unique();
            $t->string('pass_type', 20); // VISITOR/ATTENDANT

            $t->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $t->string('reference_type', 20)->nullable(); // VISIT/Ipd_ADMISSION/GENERAL
            $t->unsignedBigInteger('reference_id')->nullable();

            $t->string('full_name');
            $t->string('mobile')->nullable();
            $t->string('relationship', 50)->nullable();
            $t->string('id_type', 30)->nullable(); // NID/PASSPORT/DRIVING_LICENSE
            $t->string('id_number')->nullable();
            $t->string('purpose', 50)->nullable();
            $t->string('photo_path')->nullable();

            $t->dateTime('valid_from');
            $t->dateTime('valid_to');

            $t->boolean('special_permission')->default(false);
            $t->string('status', 20)->default('ISSUED'); // ISSUED/CHECKED_IN/CHECKED_OUT/EXPIRED/CANCELLED
            $t->text('cancel_reason')->nullable();
            $t->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $t->dateTime('cancelled_at')->nullable();

            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['pass_type', 'status']);
            $t->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passes');
    }
};
