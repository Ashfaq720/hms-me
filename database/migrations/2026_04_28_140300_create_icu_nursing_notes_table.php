<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_nursing_notes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('icu_admission_id')->index();
            $table->string('icu_case_id', 30)->index();
            $table->unsignedBigInteger('patient_id')->index();

            $table->enum('shift', ['Morning', 'Evening', 'Night'])->nullable();
            $table->dateTime('observation_time');

            // Free-form clinical fields per BRD §7.1
            $table->string('consciousness_level', 50)->nullable(); // e.g. Alert, Drowsy, Unresponsive, GCS X
            $table->unsignedTinyInteger('pain_score')->nullable(); // 0-10
            $table->string('respiratory_support', 100)->nullable();
            $table->string('oxygen_flow', 50)->nullable();
            $table->string('position', 50)->nullable();
            $table->string('skin_condition', 100)->nullable();
            $table->string('general_condition', 100)->nullable();

            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('entered_by')->nullable();

            $table->timestamps();

            $table->index(['icu_admission_id', 'observation_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_nursing_notes');
    }
};
