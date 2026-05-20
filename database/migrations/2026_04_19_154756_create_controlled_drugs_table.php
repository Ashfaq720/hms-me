<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlled_drugs', function (Blueprint $table) {
            $table->id();
            $table->string('entry_no')->unique();
            $table->dateTime('entry_date');
            $table->string('doctor_name');
            $table->string('dea_number')->nullable();
            $table->foreignId('medicine_id')->nullable()->constrained('medicines')->nullOnDelete();
            $table->string('generic_name');
            $table->string('lot_number');
            $table->string('schedule');
            $table->date('expiration_date')->nullable();
            $table->string('ndc_code')->nullable();
            $table->enum('action_type', ['received', 'removed'])->default('received');
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('unit')->default('mg');
            $table->enum('inventory_status', ['available', 'low_stock', 'out_of_stock'])->default('available');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlled_drugs');
    }
};
