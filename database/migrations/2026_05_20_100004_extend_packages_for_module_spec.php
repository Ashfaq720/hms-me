<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 of Package Module spec — 16-feature implementation.
 *
 *  1. Extend `packages` with code, admission_type, bed_type_id, status.
 *  2. Extend `package_services` with is_included + note (Included/Excluded service config).
 *  3. Create `package_price_rules` for multi-axis pricing
 *     (bed × duration × department × patient category).
 *  4. Extend `package_enrollments.status` enum to the 7 states from spec §14.
 *
 * Everything is additive + nullable; existing 28 packages + 117 enrolments
 * keep working with sensible defaults.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── packages: 4 new columns ──
        Schema::table('packages', function (Blueprint $t) {
            if (! Schema::hasColumn('packages', 'code')) {
                $t->string('code', 32)->nullable()->after('name')->unique('packages_code_unique');
            }
            if (! Schema::hasColumn('packages', 'admission_type')) {
                $t->enum('admission_type', ['PLANNED', 'EMERGENCY', 'DAY_CARE', 'WALK_IN', 'ANY'])
                    ->nullable()->default('ANY')->after('package_type');
            }
            if (! Schema::hasColumn('packages', 'bed_type_id')) {
                $t->foreignId('bed_type_id')->nullable()->after('department_id')
                    ->constrained('bed_types')->nullOnDelete();
            }
            if (! Schema::hasColumn('packages', 'status')) {
                $t->enum('status', ['active', 'inactive', 'archived'])
                    ->default('active')->after('is_active');
            }
        });

        // ── package_services: included/excluded flag ──
        Schema::table('package_services', function (Blueprint $t) {
            if (! Schema::hasColumn('package_services', 'is_included')) {
                $t->boolean('is_included')->default(true)->after('service_catalog_id');
            }
            if (! Schema::hasColumn('package_services', 'note')) {
                $t->string('note', 255)->nullable()->after('amount');
            }
        });

        // ── package_price_rules: multi-axis pricing ──
        if (! Schema::hasTable('package_price_rules')) {
            Schema::create('package_price_rules', function (Blueprint $t) {
                $t->id();
                // packages.id is legacy `int unsigned` (NOT bigint). foreignId()
                // would create a bigint column → MySQL 3780 (incompatible FK types).
                // Use unsignedInteger + explicit foreign() to match exactly.
                $t->unsignedInteger('package_id');
                $t->foreign('package_id')->references('id')->on('packages')->cascadeOnDelete();
                $t->foreignId('bed_type_id')->nullable()->constrained('bed_types')->nullOnDelete();
                $t->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                $t->unsignedSmallInteger('duration_days')->nullable();
                $t->enum('patient_category', ['GENERAL', 'CORPORATE', 'INSURANCE', 'STAFF', 'ANY'])
                    ->default('ANY');
                $t->decimal('price', 12, 2);
                $t->date('valid_from')->nullable();
                $t->date('valid_to')->nullable();
                $t->boolean('is_active')->default(true);
                $t->text('notes')->nullable();
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
                $t->index(['package_id', 'is_active'], 'pkg_rule_active_idx');
                $t->index(['bed_type_id', 'duration_days'], 'pkg_rule_bed_dur_idx');
            });
        }

        // ── package_enrollments: extend status to 7 states ──
        // Old values: active, cancelled, completed (current).
        // New values: draft, confirmed, partially_used, completed, cancelled, refunded, closed.
        // 'active' is preserved as an additional value so existing 117 rows keep working.
        if (Schema::hasColumn('package_enrollments', 'status')) {
            DB::statement("ALTER TABLE package_enrollments
                MODIFY COLUMN status ENUM('draft','confirmed','active','partially_used','completed','cancelled','refunded','closed')
                DEFAULT 'active'");
        }
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $t) {
            foreach (['status', 'code'] as $col) {
                if (Schema::hasColumn('packages', $col)) $t->dropColumn($col);
            }
            if (Schema::hasColumn('packages', 'bed_type_id')) {
                $t->dropConstrainedForeignId('bed_type_id');
            }
            if (Schema::hasColumn('packages', 'admission_type')) {
                $t->dropColumn('admission_type');
            }
        });
        Schema::table('package_services', function (Blueprint $t) {
            foreach (['is_included', 'note'] as $col) {
                if (Schema::hasColumn('package_services', $col)) $t->dropColumn($col);
            }
        });
        Schema::dropIfExists('package_price_rules');
    }
};
