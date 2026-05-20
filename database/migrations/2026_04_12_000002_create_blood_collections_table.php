<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained('blood_donors')->cascadeOnDelete();
            $table->foreignId('blood_group_id')->constrained('blood_groups')->cascadeOnDelete();
            $table->dateTime('donate_date');
            $table->string('bag_no')->unique();
            $table->decimal('volume', 8, 2);
            $table->string('unit')->default('ML');
            $table->string('lot')->nullable();
            $table->foreignId('charge_id')->nullable()->constrained('charges')->nullOnDelete();
            $table->string('charge_name')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_collections');
    }
};
