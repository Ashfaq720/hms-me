<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inventory foundation (SRS §5.20).
 *
 *  inventory_warehouses      – physical store/sub-store
 *  inventory_items           – unified item master (medicines, OT consumables, supplies)
 *  inventory_item_batches    – batch / expiry / serial
 *  stock_movements           – immutable ledger of every IN/OUT/ADJUSTMENT
 *  purchase_orders + lines   – PO workflow
 *  goods_receipt_notes + lines – GRN workflow
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('code', 64);
            $table->string('name');
            $table->string('type', 32)->default('main'); // main, sub_store, pharmacy, ot, icu, lab, blood_bank
            $table->string('location')->nullable();
            $table->foreignId('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['branch_id', 'code']);
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('code', 64);
            $table->string('name');
            $table->string('category', 64)->default('general');
            $table->string('generic_name')->nullable();
            $table->string('brand')->nullable();
            $table->string('sku', 64)->nullable();
            $table->string('barcode', 64)->nullable();
            $table->string('uom', 32)->default('unit'); // box, vial, tab, ml, kg, etc.
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('reorder_level', 12, 4)->default(0);
            $table->decimal('reorder_quantity', 12, 4)->default(0);
            $table->string('storage_condition')->nullable();
            $table->boolean('is_controlled')->default(false);
            $table->boolean('is_consumable')->default(true);
            $table->boolean('is_asset')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['organization_id', 'code']);
            $table->index(['name', 'is_active']);
        });

        Schema::create('inventory_item_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('inventory_warehouses')->nullOnDelete();
            $table->string('batch_no', 64);
            $table->string('serial_no', 64)->nullable();
            $table->date('mfg_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('current_qty', 14, 4)->default(0);
            $table->string('storage_location')->nullable();
            $table->timestamps();
            $table->index(['inventory_item_id', 'warehouse_id']);
            $table->index('expiry_date');
        });

        // Immutable stock ledger – SRS §5.20, §7.1: NEVER UPDATE, only INSERT.
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('inventory_item_batch_id')->nullable()->constrained('inventory_item_batches')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('inventory_warehouses')->nullOnDelete();

            $table->enum('direction', ['in', 'out', 'adjustment_in', 'adjustment_out', 'transfer_in', 'transfer_out']);
            $table->decimal('quantity', 14, 4);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('balance_after', 14, 4);

            $table->enum('reason', [
                'grn', 'pharmacy_dispense', 'ipd_dispense', 'opd_dispense',
                'ot_consumption', 'icu_consumption', 'lab_consumption',
                'return', 'damaged', 'expired', 'transfer', 'stock_count', 'opening', 'other',
            ]);

            // Polymorphic source so we can trace back to the originating record.
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('reference_no', 64)->nullable();

            $table->text('remarks')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamps();

            $table->index(['inventory_item_id', 'performed_at']);
            $table->index(['warehouse_id', 'performed_at']);
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('po_no', 64)->unique();
            $table->foreignId('warehouse_id')->nullable()->constrained('inventory_warehouses')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'partial', 'completed', 'cancelled'])->default('draft');
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items');
            $table->decimal('quantity_ordered', 14, 4);
            $table->decimal('quantity_received', 14, 4)->default(0);
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('line_total', 14, 2);
            $table->timestamps();
        });

        Schema::create('goods_receipt_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('grn_no', 64)->unique();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('inventory_warehouses');
            $table->date('received_date');
            $table->string('supplier_invoice_no')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('goods_receipt_note_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grn_id')->constrained('goods_receipt_notes')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items');
            $table->foreignId('inventory_item_batch_id')->nullable()->constrained('inventory_item_batches')->nullOnDelete();
            $table->string('batch_no', 64)->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 14, 4);
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('line_total', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_note_lines');
        Schema::dropIfExists('goods_receipt_notes');
        Schema::dropIfExists('purchase_order_lines');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_item_batches');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_warehouses');
    }
};
