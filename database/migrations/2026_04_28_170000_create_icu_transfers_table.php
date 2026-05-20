<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_transfers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('icu_admission_id')->index();
            $table->string('icu_case_id', 30)->index();
            $table->unsignedBigInteger('patient_id')->index();

            $table->enum('transfer_type', ['IcuToIcu', 'IcuToCcu', 'IcuToWard', 'IcuToHigherCare', 'IcuToOT']);

            $table->string('from_unit', 30)->nullable(); // ICU/CCU/NICU/PICU/Ipd
            $table->string('to_unit', 30)->nullable();
            $table->unsignedBigInteger('from_bed_id')->nullable();
            $table->unsignedBigInteger('to_bed_id')->nullable();

            // For ICU→Ipd, this is the new Ipd admission row that gets the patient post-transfer
            $table->unsignedBigInteger('to_ipd_id')->nullable();

            $table->text('transfer_reason');
            $table->dateTime('transfer_time');

            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->enum('status', ['Requested', 'Approved', 'Completed', 'Cancelled'])->default('Completed');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_transfers');
    }
};
