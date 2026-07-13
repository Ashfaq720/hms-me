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
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('rent')->default(0);
            $table->foreignId('bed_type_id')->constrained('bed_types')->cascadeOnDelete();
            $table->foreignId('bed_group_id')->constrained('bed_groups')->cascadeOnDelete();
            $table->integer('is_active')->default(1);
            $table->integer('is_reserved')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
