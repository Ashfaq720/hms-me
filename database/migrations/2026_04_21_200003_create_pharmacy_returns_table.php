<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no')->unique();
            $table->foreignId('transaction_id')->constrained('pharmacy_transactions')->restrictOnDelete();
            $table->enum('transaction_type', ['opd', 'ipd', 'otc']);
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('returned_by')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'completed'])->default('pending');
            $table->text('note')->nullable();

            $table->foreign('patient_id')->references('id')->on('patients')->nullOnDelete();
            $table->foreign('returned_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_returns');
    }
};
