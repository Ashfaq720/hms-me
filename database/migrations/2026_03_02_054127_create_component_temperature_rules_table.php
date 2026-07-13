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
        Schema::create('component_temperature_rules', function (Blueprint $t) {
            $t->id();

            $t->foreignId('component_id')
                ->constrained('components')
                ->restrictOnDelete();

            $t->decimal('min_temp', 5, 2);
            $t->decimal('max_temp', 5, 2);

            // unit removed (always °C)
            $t->boolean('monitoring_required')->default(true);

            $t->boolean('is_active')->default(true);

            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();

            // $t->unique('component_id'); // one rule per component (foundation)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('component_temperature_rules');
    }
};
