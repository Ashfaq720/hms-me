<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('component_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_group_id')->constrained('blood_groups')->cascadeOnDelete();
            $table->foreignId('blood_collection_id')->constrained('blood_collections')->cascadeOnDelete();
            $table->foreignId('donor_id')->constrained('blood_donors')->cascadeOnDelete();
            $table->foreignId('component_id')->constrained('components')->cascadeOnDelete();
            $table->string('component_bag_no')->unique();
            $table->decimal('volume', 8, 2)->default(0);
            $table->string('unit')->default('ML');
            $table->string('lot')->nullable();
            $table->string('institution')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('datetime');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_collections');
    }
};
