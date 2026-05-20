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
        Schema::create('ipd_patient_beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('case_references')->cascadeOnDelete();
            $table->foreignId('ipd_patient_id')->constrained('i_p_d_patients')->cascadeOnDelete();
            $table->foreignId('bed_id')->constrained('beds')->cascadeOnDelete();
            $table->dateTime('from');
            $table->dateTime('to')->nullable();
            $table->string('remarks')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipd_patient_beds');
    }
};
