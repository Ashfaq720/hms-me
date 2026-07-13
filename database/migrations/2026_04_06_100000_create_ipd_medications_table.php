<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipd_patient_id')->constrained('i_p_d_patients')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->dateTime('datetime');
            $table->string('dosage')->nullable();
            $table->string('medicated_by')->nullable();
            $table->text('remarks')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_medications');
    }
};
