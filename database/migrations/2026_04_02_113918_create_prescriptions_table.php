<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_no')->nullable();
            $table->foreignId('opd_patient_id')->nullable()->constrained('opd_patients')->nullOnDelete();
            $table->foreignId('ipd_patient_id')->nullable()->constrained('i_p_d_patients')->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->datetime('date')->nullable();
            $table->longText('findings')->nullable();
            $table->longText('advice')->nullable();
            $table->date('next_visit')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->string('type')->default('Manual');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
