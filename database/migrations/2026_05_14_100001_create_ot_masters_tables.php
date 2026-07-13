<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ot_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('type', 50)->nullable();
            $table->unsignedBigInteger('floor_id')->nullable();
            $table->string('block')->nullable();
            $table->boolean('is_emergency')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('status', 30)->default('available');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('ot_equipments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->unsignedBigInteger('ot_room_id')->nullable();
            $table->string('serial_no')->nullable();
            $table->string('status', 30)->default('available');
            $table->date('last_service_date')->nullable();
            $table->date('next_service_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('ot_room_id')->references('id')->on('ot_rooms')->onDelete('set null');
        });

        Schema::create('ot_surgery_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 30)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ot_anesthesia_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 30)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ot_surgery_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 30)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedInteger('standard_duration_minutes')->default(60);
            $table->decimal('standard_charge', 12, 2)->default(0);
            $table->decimal('surgeon_charge', 12, 2)->default(0);
            $table->decimal('anesthesia_charge', 12, 2)->default(0);
            $table->decimal('ot_room_charge', 12, 2)->default(0);
            $table->decimal('recovery_charge', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('ot_surgery_categories')->onDelete('set null');
        });

        Schema::create('ot_consumables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->nullable()->unique();
            $table->string('type', 30)->default('consumable');
            $table->string('unit', 20)->nullable();
            $table->decimal('rate', 12, 2)->default(0);
            $table->boolean('is_implant')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ot_consumables');
        Schema::dropIfExists('ot_surgery_types');
        Schema::dropIfExists('ot_anesthesia_types');
        Schema::dropIfExists('ot_surgery_categories');
        Schema::dropIfExists('ot_equipments');
        Schema::dropIfExists('ot_rooms');
    }
};
