<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make `packages` the SINGLE central master for every package category
 * (OPD / IPD / OT / ICU / Diagnostic / Health Checkup / Corporate / Maternity).
 *
 * Backwards-compatible: every new column is nullable so legacy code
 * continues to work. The icu_packages table remains for legacy ICU-billing
 * compatibility, but new packages should be created in this unified table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('packages')) {
            return;
        }
        Schema::table('packages', function (Blueprint $table) {
            if (! Schema::hasColumn('packages', 'package_type')) {
                $table->string('package_type', 32)->nullable()->after('name');
            }
            if (! Schema::hasColumn('packages', 'category')) {
                $table->string('category', 64)->nullable()->after('package_type');
            }
            if (! Schema::hasColumn('packages', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('category');
            }
            if (! Schema::hasColumn('packages', 'patient_type')) {
                $table->string('patient_type', 32)->nullable()->default('all')->after('department_id');
            }
            if (! Schema::hasColumn('packages', 'validity_days')) {
                $table->unsignedInteger('validity_days')->nullable()->after('patient_type');
            }
            if (! Schema::hasColumn('packages', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('validity_days');
            }
            if (! Schema::hasColumn('packages', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('packages')) {
            return;
        }
        Schema::table('packages', function (Blueprint $table) {
            foreach (['package_type', 'category', 'department_id', 'patient_type', 'validity_days', 'is_active'] as $col) {
                if (Schema::hasColumn('packages', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('packages', 'branch_id')) {
                $table->dropConstrainedForeignId('branch_id');
            }
        });
    }
};
