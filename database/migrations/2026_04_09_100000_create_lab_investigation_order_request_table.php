<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_investigation_order_request', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lab_inv_order_id')->nullable()
                ->constrained('lab_investigation_order')->nullOnDelete();

            $table->foreignId('lab_inv_id')->nullable()
                ->constrained('lab_investigations')->nullOnDelete();

            $table->foreignId('lab_inv_type_id')->nullable()
                ->constrained('lab_investigation_types')->nullOnDelete();

            $table->foreignId('lab_inv_category_id')->nullable()
                ->constrained('lab_investigation_categories')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_investigation_order_request');
    }
};
