<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_equipment', function (Blueprint $table) {
            $table->id();
            $table->string('equipment_code', 50)->unique();
            $table->string('equipment_name', 150);
            $table->enum('equipment_type', [
                'Ventilator',
                'Monitor',
                'InfusionPump',
                'SyringePump',
                'OxygenSupport',
                'DialysisMachine',
                'ECG',
                'PulseOximeter',
                'TemperatureSensor',
                'Other',
            ]);
            $table->string('serial_no', 100)->nullable();
            $table->enum('status', [
                'Available',
                'InUse',
                'Maintenance',
                'Cleaning',
                'Damaged',
                'Reserved',
            ])->default('Available');
            $table->string('location', 100)->nullable();
            $table->unsignedBigInteger('default_bed_id')->nullable()->index();

            // Billing master
            $table->enum('charge_type', ['Hour', 'Day', 'Session', 'Fixed'])->default('Day');
            $table->decimal('charge_rate', 12, 2)->default(0);

            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['equipment_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_equipment');
    }
};
