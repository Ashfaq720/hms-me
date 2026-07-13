<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the composite (source_type, source_id) index that should have
 * accompanied the source_* columns we added in
 * 2026_06_02_120000_align_nicu_admissions_with_model.php — without it,
 * polymorphic morphTo lookups (NicuAdmission → OtSurgerySchedule etc.)
 * full-scan the table.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('nicu_admissions')) return;
        if (! Schema::hasColumn('nicu_admissions', 'source_id')) return;

        // Skip if some index already covers (source_type, source_id)
        $exists = DB::selectOne("
            SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS cols
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'nicu_admissions'
              AND COLUMN_NAME = 'source_id'
            GROUP BY INDEX_NAME
            HAVING cols LIKE 'source_type,source_id%'
            LIMIT 1
        ", [DB::getDatabaseName()]);

        if ($exists) return;

        Schema::table('nicu_admissions', function (Blueprint $t) {
            $t->index(['source_type', 'source_id'], 'nicu_admissions_source_morph_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('nicu_admissions')) return;
        try {
            Schema::table('nicu_admissions', function (Blueprint $t) {
                $t->dropIndex('nicu_admissions_source_morph_idx');
            });
        } catch (\Throwable $e) {}
    }
};
