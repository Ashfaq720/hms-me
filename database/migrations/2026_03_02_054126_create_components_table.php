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
        Schema::create('components', function (Blueprint $t) {
            $t->id();

            $t->string('component_code', 30)->unique();
            $t->string('component_name', 120)->unique();

            // Simplified: parent_type removed; keep derived_from only
            $t->enum('derived_from', ['WHOLE_BLOOD', 'COMPONENT'])->default('WHOLE_BLOOD');

            // Shelf life enforced by system
            $t->unsignedInteger('shelf_life_value');
            $t->enum('shelf_life_unit', ['HOURS', 'DAYS']);

            $t->enum('storage_requirement', ['BLOOD_BANK', 'REFRIGERATOR', 'DEEP_FREEZER']);

            // Optional but recommended from requirements
            $t->unsignedInteger('min_volume_ml')->nullable();
            $t->unsignedInteger('max_volume_ml')->nullable();

            $t->boolean('is_active')->default(true);

            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
