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
        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charge_type_id')->constrained('charge_types')->cascadeOnDelete();
            $table->foreignId('charge_category_id')->constrained('charge_categories')->cascadeOnDelete();
            $table->foreignId('unite_type_id')->constrained('unite_types')->cascadeOnDelete();
            $table->foreignId('tax_category_id')->constrained('tax_categories')->cascadeOnDelete();
            $table->string('charge_name');
            $table->decimal('tax', 8, 2)->nullable()->default(0);
            $table->decimal('standard_charge', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charges');
    }
};
