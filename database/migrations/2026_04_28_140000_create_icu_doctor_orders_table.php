<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_doctor_orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('icu_admission_id')->index();
            $table->string('icu_case_id', 30)->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('doctor_id');

            $table->enum('order_type', [
                'Medication',
                'Lab',
                'Radiology',
                'Procedure',
                'NursingCare',
                'DietFluid',
                'Monitoring',
            ]);
            $table->string('order_title', 255);
            $table->text('order_details')->nullable();

            $table->enum('priority', ['Routine', 'Urgent', 'STAT'])->default('Routine');

            $table->dateTime('start_time')->nullable();
            $table->string('frequency', 50)->nullable();   // e.g. "8 hourly", "BD", "OD"
            $table->string('duration', 50)->nullable();    // e.g. "5 days"

            $table->enum('status', [
                'Ordered',
                'Acknowledged',
                'InProgress',
                'Completed',
                'Cancelled',
                'OnHold',
                'Rejected',
                'Modified',
            ])->default('Ordered');

            $table->boolean('requires_doctor_ack')->default(false);
            $table->dateTime('doctor_acknowledged_at')->nullable();
            $table->unsignedBigInteger('doctor_acknowledged_by')->nullable();

            // Cross-module link when this order spawns a downstream record
            // (medicine_orders, lab_investigation_orders, operation_history, etc.)
            $table->string('linked_module', 30)->nullable();
            $table->unsignedBigInteger('linked_id')->nullable();

            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['icu_admission_id', 'status']);
            $table->index(['priority', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_doctor_orders');
    }
};
