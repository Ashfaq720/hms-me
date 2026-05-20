<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_antibiotic_usage_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('icu_admission_id')->index();
            $table->string('icu_case_id', 30)->index();
            $table->unsignedBigInteger('patient_id')->index();

            $table->unsignedBigInteger('doctor_order_id')->nullable()->index(); // optional link to icu_doctor_orders
            $table->unsignedBigInteger('medicine_id')->nullable();

            $table->string('antibiotic_name', 150);
            $table->string('dose', 50)->nullable();
            $table->string('route', 30)->nullable();    // IV, PO, IM, etc.
            $table->string('frequency', 50)->nullable();

            $table->date('start_date');
            $table->date('stop_date')->nullable();

            $table->string('indication', 255)->nullable();
            $table->unsignedBigInteger('culture_report_id')->nullable();
            $table->unsignedBigInteger('prescribed_by')->nullable();

            $table->boolean('is_restricted')->default(false);
            $table->dateTime('long_use_alerted_at')->nullable();

            $table->enum('status', ['Active', 'Stopped', 'Switched'])->default('Active');
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['icu_admission_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_antibiotic_usage_logs');
    }
};
