<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('pharmacy_transactions')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->string('dosage')->nullable();   // OPD / OTC
            $table->string('duration')->nullable(); // Ipd
            $table->integer('qty_required');
            $table->integer('available_qty')->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->string('store')->nullable();
            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('medicine_batches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_transaction_items');
    }
};
