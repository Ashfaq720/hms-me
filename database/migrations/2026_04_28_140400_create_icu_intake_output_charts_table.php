<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_intake_output_charts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('icu_admission_id')->index();
            $table->string('icu_case_id', 30)->index();
            $table->unsignedBigInteger('patient_id')->index();

            $table->dateTime('entry_time');
            $table->enum('entry_type', ['Intake', 'Output']);
            $table->string('category', 30); // IVFluid/OralFluid/Blood/MedFluid/TubeFeeding/Urine/Drain/Vomiting/Stool/BloodLoss/Other
            $table->unsignedInteger('quantity_ml');

            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('entered_by')->nullable();

            $table->timestamps();

            $table->index(['icu_admission_id', 'entry_time']);
            $table->index(['icu_admission_id', 'entry_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_intake_output_charts');
    }
};
