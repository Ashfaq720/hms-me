<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_bed_equipment_mapping', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bed_id')->index();
            $table->unsignedBigInteger('equipment_id')->index();
            $table->boolean('is_default')->default(true);
            $table->enum('status', ['Active', 'Detached'])->default('Active');
            $table->timestamps();

            $table->unique(['bed_id', 'equipment_id'], 'icu_bed_equipment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_bed_equipment_mapping');
    }
};
