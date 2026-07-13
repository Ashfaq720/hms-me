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
        Schema::create('doctor_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->unique()->cascadeOnDelete();
            $table->integer('first_visit_fee')->nullable();
            $table->integer('follow_up_fee')->nullable();
            $table->integer('follow_up_window')->nullable();
            $table->integer('ipd_visit_fee')->nullable();
            $table->integer('opd_visit_fee')->nullable();
            $table->integer('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_fees');
    }
};
