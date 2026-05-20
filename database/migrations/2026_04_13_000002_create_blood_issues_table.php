<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_collection_id')->nullable()->constrained('component_collections')->nullOnDelete();
            $table->foreignId('blood_collection_id')->nullable()->constrained('blood_collections')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('case_id')->nullable()->constrained('case_references')->nullOnDelete();
            $table->dateTime('issue_datetime');
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('reference_name')->nullable();
            $table->string('technician_name')->nullable();
            $table->foreignId('charge_id')->nullable()->constrained('charges')->nullOnDelete();
            $table->enum('type', ['blood', 'component']);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_issues');
    }
};
