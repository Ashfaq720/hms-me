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
        Schema::create('i_p_d_patients', function (Blueprint $table) {
            $table->id();
            $table->string('ipd_no')->nullable();
            $table->foreignId('case_id')->nullable()->constrained('case_references')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->datetime('admission_date')->nullable();
            $table->datetime('possible_discharge_date')->nullable();
            $table->string('patient_history')->nullable();
            $table->string('remarks')->nullable();
            $table->string('status')->default('Admitted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('i_p_d_patients');
    }
};
