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
        Schema::create('patient_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->nullable()->constrained('case_references')->nullOnDelete();
            $table->string('charge_module')->nullable()->comment('ipd, opd, appointment, er_register, pathology, radiology, blood_bank, pharmacy');
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->unsignedBigInteger('ipd_id')->nullable();
            $table->unsignedBigInteger('opd_id')->nullable();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('er_register_id')->nullable();
            $table->unsignedBigInteger('pathology_id')->nullable();
            $table->unsignedBigInteger('radiology_id')->nullable();
            $table->unsignedBigInteger('blood_bank_id')->nullable();
            $table->unsignedBigInteger('pharmacy_id')->nullable();
            $table->string('charge_item')->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('vat', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->dateTime('date')->nullable();
            $table->longText('notes')->nullable();
            $table->string('files')->nullable();
            $table->longText('remarks')->nullable();
            $table->string('status')->default('pending')->comment('pending, approved, billed, paid, cancelled, refunded');
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_bill_generated')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_charges');
    }
};
