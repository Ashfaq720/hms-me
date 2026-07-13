<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_infection_control_overrides', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('icu_admission_id')->index();
            $table->string('icu_case_id', 30)->index();

            $table->string('required_isolation_type', 30);
            $table->unsignedBigInteger('assigned_bed_id');
            $table->text('override_reason');
            $table->unsignedBigInteger('approved_by');
            $table->dateTime('override_time');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_infection_control_overrides');
    }
};
