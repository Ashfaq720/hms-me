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
        Schema::create('medicine_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->integer('qty')->default(1);
            $table->foreignId('prescribed_by')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('ipd_id')->nullable()->constrained('i_p_d_patients')->nullOnDelete();
            $table->foreignId('er_id')->nullable()->constrained('er_patients')->nullOnDelete();
            $table->foreignId('case_id')->nullable()->constrained('case_references')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->string('order_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_orders');
    }
};
