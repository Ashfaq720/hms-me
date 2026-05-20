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
        Schema::create('lab_investigations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name', 20)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('lab_investigation_categories')->cascadeOnDelete();
            $table->string('department')->nullable();
            $table->string('sample_type')->nullable();
            $table->integer('report_time_hours')->nullable();
            $table->text('normal_range')->nullable();
            $table->string('unit', 50)->nullable();
            $table->string('method')->nullable();
            $table->text('preparation')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_investigations');
    }
};
