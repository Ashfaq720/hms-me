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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_name');
            $table->date('dob')->nullable();
            $table->string('image')->nullable();
            $table->string('mobileno')->unique();
            $table->string('email')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->enum('marital_status', ['Single', 'Married', 'Divorced', 'Widowed'])->nullable();
            $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            $table->longText('address')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('patient_type')->nullable();
            $table->string('identification_number')->nullable();
            $table->string('known_allergies')->nullable();
            $table->string('note')->nullable();
            $table->string('is_ipd')->default(0);
            $table->string('insurance')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('organization_id')->nullable();
            $table->string('organization_api_link')->nullable();
            $table->string('supporting_doc')->nullable();;
            $table->enum('discount_type', ['CORPORATE', 'INSURANCE','STUFF','SELF'])->nullable();
            $table->date('insurance_validity')->nullable();
            $table->integer('is_dead')->default(0);
            $table->integer('is_active')->default(1);
            $table->unsignedBigInteger('lang_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
