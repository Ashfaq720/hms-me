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
        Schema::create('opd_dispense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_dispense_id')->constrained('opd_dispenses')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->string('dosage')->nullable();
            $table->integer('qty_required')->default(1);
            $table->integer('available_qty')->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('store')->default('Main Pharmacy');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opd_dispense_items');
    }
};
