<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_admission_overrides', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('icu_admission_id')->index();

            // What resource forced the override
            $table->enum('resource_issue', [
                'NoBed',
                'NoVentilator',
                'NoMonitor',
                'NoIsolationBed',
                'Other',
            ]);

            $table->text('override_reason');
            $table->unsignedBigInteger('approved_by');
            $table->unsignedBigInteger('temporary_bed_id')->nullable();
            $table->dateTime('override_time');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_admission_overrides');
    }
};
