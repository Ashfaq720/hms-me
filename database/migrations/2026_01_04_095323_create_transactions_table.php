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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('case_id')->constrained('case_references')->cascadeOnDelete();
            $table->foreignId('ipd_patient_id')->nullable()->constrained('i_p_d_patients')->nullOnDelete();
            $table->unsignedBigInteger('opd_patient_id')->nullable();
            $table->unsignedBigInteger('pathology_bill_id')->nullable();
            $table->unsignedBigInteger('pharmacy_bill_id')->nullable();
            $table->unsignedBigInteger('radiology_bill_id')->nullable();
            $table->unsignedBigInteger('blood_bank_bill_id')->nullable();
            $table->string('invoice_no', 50)->nullable()->unique();
            $table->string('type', 30)->nullable()->comment('payment, refund, adjustment, advance');
            $table->string('section', 30)->nullable()->comment('ipd, opd, pharmacy, pathology, radiology, blood_bank');
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('vat', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->string('payment_via', 30)->nullable()->comment('card, cash, cheque, mfs, other');
            $table->datetime('payment_date')->nullable();
            $table->string('cheque_name', 30)->nullable();
            $table->string('cheque_no', 30)->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('card_no', 30)->nullable();
            $table->string('card_type', 30)->nullable()->comment('visa, master, american_express, other');
            $table->string('mfs_type', 30)->nullable()->comment('bkash, nagad, rocket, other');
            $table->string('mfs_no', 30)->nullable();
            $table->string('mfs_transaction_id', 100)->nullable();
            $table->longText('notes')->nullable();
            $table->string('received_by', 100)->nullable();
            $table->json('files')->nullable();
            $table->string('status', 30)->nullable()->comment('pending, successed, failed, canceled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
