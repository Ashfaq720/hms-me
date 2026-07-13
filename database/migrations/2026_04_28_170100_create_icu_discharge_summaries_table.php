<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_discharge_summaries', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('icu_admission_id')->unique();
            $table->string('icu_case_id', 30)->index();
            $table->unsignedBigInteger('patient_id')->index();

            $table->text('admission_diagnosis')->nullable();
            $table->text('final_diagnosis')->nullable();

            $table->longText('icu_course_summary')->nullable();
            $table->longText('procedures_summary')->nullable();
            $table->longText('ventilator_summary')->nullable();
            $table->longText('investigation_summary')->nullable();
            $table->longText('medication_summary')->nullable();

            $table->string('condition_at_discharge', 100)->nullable();
            $table->longText('followup_advice')->nullable();

            $table->unsignedBigInteger('prepared_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_discharge_summaries');
    }
};
