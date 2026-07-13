<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add canonical inventory_item_id link to ot_consumables
        Schema::table('ot_consumables', function (Blueprint $t) {
            if (! Schema::hasColumn('ot_consumables', 'inventory_item_id')) {
                $t->unsignedBigInteger('inventory_item_id')->nullable()->after('id');
                $t->foreign('inventory_item_id')->references('id')->on('inventory_items')->nullOnDelete();
                $t->index('inventory_item_id');
            }
        });

        // 2. Add canonical inventory_item_id link to ot_equipments
        Schema::table('ot_equipments', function (Blueprint $t) {
            if (! Schema::hasColumn('ot_equipments', 'inventory_item_id')) {
                $t->unsignedBigInteger('inventory_item_id')->nullable()->after('id');
                $t->foreign('inventory_item_id')->references('id')->on('inventory_items')->nullOnDelete();
                $t->index('inventory_item_id');
            }
        });

        // 3. Tag inventory_items with subtypes so we can filter for "OT items" / "equipment"
        //    (category is varchar(64) — accepts new values without schema change)
    }

    public function down(): void
    {
        Schema::table('ot_consumables', function (Blueprint $t) {
            if (Schema::hasColumn('ot_consumables', 'inventory_item_id')) {
                try { $t->dropForeign(['inventory_item_id']); } catch (\Throwable $e) {}
                $t->dropColumn('inventory_item_id');
            }
        });
        Schema::table('ot_equipments', function (Blueprint $t) {
            if (Schema::hasColumn('ot_equipments', 'inventory_item_id')) {
                try { $t->dropForeign(['inventory_item_id']); } catch (\Throwable $e) {}
                $t->dropColumn('inventory_item_id');
            }
        });
    }
};
