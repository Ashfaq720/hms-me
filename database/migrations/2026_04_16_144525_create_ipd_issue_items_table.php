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
        Schema::create('ipd_issue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipd_issue_id')->constrained('ipd_issues')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->string('duration')->nullable();
            $table->integer('qty_required')->default(0);
            $table->integer('available_qty')->default(0);
            $table->string('store')->default('Main Pharmacy');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipd_issue_items');
    }
};
