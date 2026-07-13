<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('storage_locations', function (Blueprint $t) {
            $t->id();

            $t->string('location_code', 30)->unique();
            $t->string('location_name', 150);

            $t->enum('location_type', ['BLOOD_BANK', 'REFRIGERATOR', 'DEEP_FREEZER']);

            $t->unsignedInteger('capacity_units')->default(0);

            $t->boolean('temperature_monitoring_required')->default(false);
            $t->string('device_id', 100)->nullable();

            $t->enum('status', ['ACTIVE', 'MAINTENANCE'])->default('ACTIVE');

            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();

            $t->index(['location_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_locations');
    }
};
