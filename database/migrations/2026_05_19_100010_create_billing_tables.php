<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unified Billing Engine (SRS §5.17, §10.9 + prompt library Phase 2).
 *
 *  bills            – one bill per encounter (provisional → final)
 *  bill_items       – snapshot rows pulled from service_charge_postings
 *                     or manually added charges. Immutable once final.
 *  bill_payments    – cash/card/MFS/cheque receipts
 *  bill_refunds     – approved refunds against a bill
 *  bill_discounts   – discount + waiver lines with approver audit
 *
 * NOTE: The legacy `transactions` + `patient_charges` tables remain in
 * place for backwards compatibility. The new tables are the canonical
 * consolidated bill that the Insight Dashboard reads from.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('encounter_id')->nullable()->constrained('encounters')->nullOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('payer_id')->nullable()->constrained('payers')->nullOnDelete();
            $table->foreignId('insurance_policy_id')->nullable()->constrained('insurance_policies')->nullOnDelete();

            $table->string('bill_no', 64)->unique();
            $table->enum('bill_type', ['opd', 'ipd', 'er', 'icu', 'ot', 'procedure', 'lab', 'radiology', 'pharmacy', 'ambulance', 'package', 'other'])->default('opd');
            $table->enum('status', ['draft', 'provisional', 'final', 'paid', 'partially_paid', 'cancelled', 'refunded'])->default('draft');

            $table->date('bill_date');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_total', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->decimal('paid_total', 14, 2)->default(0);
            $table->decimal('refund_total', 14, 2)->default(0);
            $table->decimal('balance_due', 14, 2)->default(0);

            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'bill_date']);
            $table->index(['patient_id', 'status']);
            $table->index(['encounter_id']);
        });

        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->foreignId('service_catalog_id')->nullable()->constrained('service_catalogs')->nullOnDelete();
            $table->foreignId('service_charge_posting_id')->nullable()->constrained('service_charge_postings')->nullOnDelete();

            $table->string('description');
            $table->string('item_type', 32)->default('service'); // service, package, pharmacy, lab, radiology, ot, procedure, bed, manual
            $table->decimal('quantity', 12, 4)->default(1);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2);

            $table->boolean('is_package_included')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->string('receipt_no', 64)->unique();
            $table->enum('method', ['cash', 'card', 'mfs', 'cheque', 'bank_transfer', 'insurance', 'corporate', 'advance', 'other'])->default('cash');
            $table->string('reference_no', 64)->nullable();
            $table->decimal('amount', 14, 2);
            $table->date('payment_date');
            $table->enum('status', ['received', 'voided'])->default('received');
            $table->text('notes')->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['bill_id', 'status']);
        });

        Schema::create('bill_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->foreignId('bill_payment_id')->nullable()->constrained('bill_payments')->nullOnDelete();
            $table->string('refund_no', 64)->unique();
            $table->decimal('amount', 14, 2);
            $table->string('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('bill_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->enum('kind', ['discount', 'waiver'])->default('discount');
            $table->enum('mode', ['percent', 'flat'])->default('percent');
            $table->decimal('value', 14, 4);
            $table->decimal('amount_applied', 14, 2)->default(0);
            $table->string('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'applied'])->default('pending');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_discounts');
        Schema::dropIfExists('bill_refunds');
        Schema::dropIfExists('bill_payments');
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('bills');
    }
};
