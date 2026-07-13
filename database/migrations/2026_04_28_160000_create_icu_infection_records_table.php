<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_infection_records', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('icu_admission_id')->index();
            $table->string('icu_case_id', 30)->index();
            $table->unsignedBigInteger('patient_id')->index();

            $table->enum('infection_status', ['Suspected', 'Confirmed', 'RuledOut', 'Resolved'])
                ->default('Suspected');
            $table->string('infection_name', 150)->nullable();
            $table->string('organism', 150)->nullable();

            $table->enum('isolation_type', ['Airborne', 'Contact', 'Droplet', 'Standard', 'None'])
                ->default('None');

            $table->enum('suspected_source', [
                'CommunityAcquired',
                'HospitalAcquired',
                'IcuAcquired',
                'PostSurgical',
                'DeviceAssociated',
                'Unknown',
            ])->default('Unknown');

            $table->dateTime('first_detected_at')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->unsignedBigInteger('lab_report_id')->nullable();

            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('tagged_by')->nullable();
            $table->dateTime('tagged_at');

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['icu_admission_id', 'is_active']);
            $table->index(['isolation_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_infection_records');
    }
};
