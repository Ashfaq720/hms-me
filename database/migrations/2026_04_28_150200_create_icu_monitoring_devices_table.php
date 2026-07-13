<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_monitoring_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_code', 50)->unique();
            $table->string('device_name', 150);
            $table->string('device_type', 30); // MultiparaMonitor, Ventilator, ECG, InfusionPump, PulseOx, TempSensor

            $table->unsignedBigInteger('bed_id')->nullable()->index();
            $table->enum('status', ['Online', 'Offline', 'Maintenance'])->default('Offline');
            $table->dateTime('last_signal_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_monitoring_devices');
    }
};
