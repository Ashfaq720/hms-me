<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('pharmacy_returns')->cascadeOnDelete();
            $table->foreignId('transaction_item_id')->constrained('pharmacy_transaction_items')->restrictOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->integer('qty_returned');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->string('store')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_return_items');
    }
};
