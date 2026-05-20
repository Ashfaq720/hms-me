<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();
            $table->enum('transaction_type', ['opd', 'ipd', 'otc']);

            // Common patient/staff
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('pharmacist_id')->nullable();

            // Amounts
            $table->integer('drug_count')->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);

            // Payment (nullable for Ipd — goes to running bill)
            $table->enum('payment_method', ['cash', 'card', 'mobile_banking'])->nullable();
            $table->enum('payment_status', ['paid', 'unpaid', 'partial'])->nullable();

            // Status flow: OPD/Ipd start pending; OTC starts completed
            $table->enum('status', ['pending', 'approved', 'completed', 'cancelled'])->default('pending');
            $table->text('note')->nullable();

            // OPD-specific
            $table->unsignedBigInteger('opd_patient_id')->nullable();
            $table->unsignedBigInteger('prescription_id')->nullable();

            // Ipd-specific
            $table->unsignedBigInteger('ipd_patient_id')->nullable();
            $table->string('requisition_no')->nullable();
            $table->string('ward_bed')->nullable();
            $table->string('request_source')->nullable();

            // OTC-specific
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();

            $table->foreign('patient_id')->references('id')->on('patients')->nullOnDelete();
            $table->foreign('pharmacist_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('opd_patient_id')->references('id')->on('opd_patients')->nullOnDelete();
            $table->foreign('prescription_id')->references('id')->on('prescriptions')->nullOnDelete();
            $table->foreign('ipd_patient_id')->references('id')->on('i_p_d_patients')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_transactions');
    }
};
