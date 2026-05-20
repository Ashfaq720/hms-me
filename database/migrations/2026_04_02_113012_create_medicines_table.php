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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('medicine_name');
            $table->foreignId('medicine_category_id')->constrained('medicine_categories')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('medical_group_id')->nullable()->constrained('medical_groups')->nullOnDelete();
            $table->foreignId('medicine_unit_id')->constrained('medicine_units')->cascadeOnDelete();

            $table->string('medicine_composition')->nullable();
            $table->string('min_level')->nullable();
            $table->string('reorder_level')->nullable();
            $table->decimal('tax', 8, 2)->nullable()->default(0);
            $table->string('box_packing')->nullable();
            $table->string('vat_ac')->nullable();
            $table->string('rack_number')->nullable();
            $table->text('note')->nullable();
            $table->string('photo')->nullable();

            $table->integer('available_qty')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
