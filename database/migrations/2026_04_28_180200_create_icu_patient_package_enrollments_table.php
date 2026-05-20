<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_patient_package_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('icu_admission_id')->index();
            $table->string('icu_case_id', 30)->index();
            $table->unsignedBigInteger('patient_id')->index();

            $table->unsignedBigInteger('package_id')->nullable();   // null when mode=Itemized
            $table->enum('billing_mode', ['Itemized', 'Package', 'Mixed'])->default('Itemized');

            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();

            $table->enum('status', ['Active', 'Ended'])->default('Active');

            $table->unsignedBigInteger('applied_by')->nullable();
            $table->string('approval_reference', 100)->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['icu_admission_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_patient_package_enrollments');
    }
};
