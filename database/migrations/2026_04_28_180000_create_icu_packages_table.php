<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_code', 50)->unique();
            $table->string('package_name', 150);

            // Which ICU sub-type the package applies to (NULL = applies to all)
            $table->enum('icu_type', ['ICU', 'CCU', 'NICU', 'PICU'])->nullable();

            $table->decimal('rate', 12, 2);
            $table->enum('billing_unit', ['Day', 'Hour', 'Fixed'])->default('Day');

            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_packages');
    }
};
