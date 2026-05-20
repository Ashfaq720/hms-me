<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_equipment_usage_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('icu_admission_id')->index();
            $table->string('icu_case_id', 30)->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('bed_id')->nullable();
            $table->unsignedBigInteger('equipment_id')->index();
            $table->string('equipment_type', 30);

            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();

            // Billing snapshot (frozen at usage start; recalculated on close)
            $table->enum('billing_unit', ['Hour', 'Day', 'Session', 'Fixed']);
            $table->decimal('charge_rate', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            // 'InUse' while open; 'Closed' when end_time set; 'Cancelled' for retroactive removal
            $table->enum('status', ['InUse', 'Closed', 'Cancelled'])->default('InUse');

            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->unsignedBigInteger('removed_by')->nullable();
            $table->string('remove_reason', 255)->nullable();

            // FK back to PatientCharge once posted (avoids double-billing on refresh)
            $table->unsignedBigInteger('patient_charge_id')->nullable()->index();

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['icu_admission_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_equipment_usage_logs');
    }
};
