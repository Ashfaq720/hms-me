<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Idempotency guard — the rooms table is also created by the earlier
        // migration 2026_05_20_100000_enhance_bed_master_with_rooms_and_pricing
        // when running on a fresh DB. Skip if already present.
        if (Schema::hasTable('rooms')) return;

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('floor_id')->constrained('floors')->cascadeOnDelete();
            $table->integer('is_active')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
