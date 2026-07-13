<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_mortality_audits', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('icu_admission_id')->unique();
            $table->string('icu_case_id', 30)->index();
            $table->unsignedBigInteger('patient_id')->index();

            $table->dateTime('death_time');
            $table->text('cause_of_death');
            $table->unsignedBigInteger('code_blue_event_id')->nullable();
            $table->text('resuscitation_details')->nullable();
            $table->unsignedBigInteger('death_declared_by');
            $table->string('body_handover_to', 200)->nullable();

            $table->enum('audit_status', ['Pending', 'InReview', 'Completed'])->default('Pending');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->enum('preventability', ['Preventable', 'NonPreventable', 'Indeterminate'])->nullable();
            $table->text('contributing_factors')->nullable();
            $table->text('clinical_remarks')->nullable();
            $table->text('committee_remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_mortality_audits');
    }
};
