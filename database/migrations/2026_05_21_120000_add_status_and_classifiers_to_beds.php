<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive upgrade for the bed master.
 *
 * Adds:
 *   - code (BED-ICU-001), bed_gender, status (richer than is_reserved),
 *     billing_type, has_oxygen_support, notes
 *
 * The legacy is_reserved boolean stays in place untouched — 40+ callers
 * across IPD / ICU / NICU / OT / PACU / HealthCard / FrontDesk depend on
 * it. The Bed model's saving event keeps is_reserved in sync with the new
 * status enum so old code reads the right value without modification.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beds', function (Blueprint $t) {
            if (! Schema::hasColumn('beds', 'code')) {
                $t->string('code', 60)->nullable()->after('id')->unique();
            }
            if (! Schema::hasColumn('beds', 'bed_gender')) {
                $t->string('bed_gender', 20)->default('Any')->after('bed_group_id');
            }
            if (! Schema::hasColumn('beds', 'status')) {
                $t->string('status', 40)->default('Available')->after('is_reserved');
                $t->index('status');
            }
            if (! Schema::hasColumn('beds', 'billing_type')) {
                $t->string('billing_type', 30)->nullable()->after('status');
            }
            if (! Schema::hasColumn('beds', 'has_oxygen_support')) {
                $t->boolean('has_oxygen_support')->default(false)->after('billing_type');
            }
            if (! Schema::hasColumn('beds', 'notes')) {
                $t->text('notes')->nullable()->after('has_oxygen_support');
            }
        });

        // Back-fill status from existing is_reserved values so the two
        // stay consistent for rows that pre-date this migration.
        \DB::statement("UPDATE beds SET status = CASE WHEN is_reserved = 1 THEN 'Occupied' ELSE 'Available' END WHERE status IS NULL OR status = ''");
    }

    public function down(): void
    {
        Schema::table('beds', function (Blueprint $t) {
            foreach (['code', 'bed_gender', 'status', 'billing_type', 'has_oxygen_support', 'notes'] as $col) {
                if (Schema::hasColumn('beds', $col)) {
                    if ($col === 'code') {
                        $t->dropUnique(['code']);
                    } elseif ($col === 'status') {
                        $t->dropIndex(['status']);
                    }
                    $t->dropColumn($col);
                }
            }
        });
    }
};
