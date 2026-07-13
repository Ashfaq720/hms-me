<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_slot_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->unsignedInteger('consultation_minutes')->default(15);
            $table->unsignedBigInteger('charge_category_id')->nullable();
            $table->unsignedBigInteger('charge_id')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['doctor_id', 'shift_id']);
        });

        Schema::create('doctor_slot_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->string('day', 10);
            $table->time('time_from');
            $table->time('time_to');
            $table->timestamps();

            $table->index(['doctor_id', 'shift_id', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_slot_times');
        Schema::dropIfExists('doctor_slot_settings');
    }
};
