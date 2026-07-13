<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('icu_equipment')) return;

        Schema::table('icu_equipment', function (Blueprint $t) {
            if (! Schema::hasColumn('icu_equipment', 'inventory_item_id')) {
                $t->unsignedBigInteger('inventory_item_id')->nullable()->after('id');
                $t->index('inventory_item_id', 'idx_icu_eq_inv');
                $t->foreign('inventory_item_id', 'fk_icu_eq_inv')
                    ->references('id')->on('inventory_items')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('icu_equipment')) return;
        Schema::table('icu_equipment', function (Blueprint $t) {
            if (Schema::hasColumn('icu_equipment', 'inventory_item_id')) {
                try { $t->dropForeign('fk_icu_eq_inv'); } catch (\Throwable $e) {}
                try { $t->dropIndex('idx_icu_eq_inv'); } catch (\Throwable $e) {}
                $t->dropColumn('inventory_item_id');
            }
        });
    }
};
