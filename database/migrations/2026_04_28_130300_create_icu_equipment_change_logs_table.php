<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_equipment_change_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('icu_admission_id')->index();
            $table->unsignedBigInteger('old_equipment_id')->nullable();
            $table->unsignedBigInteger('new_equipment_id')->nullable();
            $table->unsignedBigInteger('old_usage_log_id')->nullable();
            $table->unsignedBigInteger('new_usage_log_id')->nullable();
            $table->string('change_reason', 255);
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->dateTime('changed_at');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_equipment_change_logs');
    }
};
