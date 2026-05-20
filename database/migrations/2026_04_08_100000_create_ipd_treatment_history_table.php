<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_treatment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipd_id')->constrained('i_p_d_patients')->cascadeOnDelete();
            $table->foreignId('case_id')->nullable()->constrained('case_references')->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->dateTime('date');
            $table->string('prescribe_medicine')->nullable();
            $table->string('diagnosis')->nullable();
            $table->longText('tx_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_treatment_history');
    }
};
