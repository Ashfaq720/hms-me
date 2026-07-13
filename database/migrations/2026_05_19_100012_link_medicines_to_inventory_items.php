<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add medicines.inventory_item_id so pharmacy dispense can write directly
 * to the unified stock_movements ledger.
 *
 * Backwards-compatible: column is nullable; legacy code that ignores it
 * keeps working, while PharmacyDispenseListener uses it when present.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('medicines')) {
            return;
        }
        if (Schema::hasColumn('medicines', 'inventory_item_id')) {
            return;
        }

        Schema::table('medicines', function (Blueprint $table) {
            $table->foreignId('inventory_item_id')
                ->nullable()
                ->after('id')
                ->constrained('inventory_items')
                ->nullOnDelete();
            $table->index('inventory_item_id', 'med_inv_item_idx');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('medicines') && Schema::hasColumn('medicines', 'inventory_item_id')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->dropIndex('med_inv_item_idx');
                $table->dropConstrainedForeignId('inventory_item_id');
            });
        }
    }
};
