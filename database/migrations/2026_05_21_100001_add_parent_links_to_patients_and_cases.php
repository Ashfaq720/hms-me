<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mother-baby + case-chaining links.
 *
 * patients.parent_patient_id  → mother (or guardian) patient row.
 *                                Lets a newborn's record point at the
 *                                mother for clinical context.
 * patients.birth_case_id      → the case file under which the baby was
 *                                born (mother's case_id at the time of
 *                                delivery). Distinct from the baby's
 *                                own case_id which is created at NICU
 *                                admission.
 * case_references.parent_case_id → newborn case → mother case, so the
 *                                consolidated bill can group both.
 *
 * Everything nullable + nullOnDelete — existing patients and cases stay
 * untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                if (! Schema::hasColumn('patients', 'parent_patient_id')) {
                    $table->foreignId('parent_patient_id')->nullable()
                          ->after('id')
                          ->constrained('patients')->nullOnDelete();
                }
                if (! Schema::hasColumn('patients', 'birth_case_id')) {
                    $table->foreignId('birth_case_id')->nullable()
                          ->after('parent_patient_id')
                          ->constrained('case_references')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('case_references') &&
            ! Schema::hasColumn('case_references', 'parent_case_id')) {
            Schema::table('case_references', function (Blueprint $table) {
                $table->foreignId('parent_case_id')->nullable()
                      ->after('id')
                      ->constrained('case_references')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                if (Schema::hasColumn('patients', 'birth_case_id')) {
                    $table->dropForeign(['birth_case_id']);
                    $table->dropColumn('birth_case_id');
                }
                if (Schema::hasColumn('patients', 'parent_patient_id')) {
                    $table->dropForeign(['parent_patient_id']);
                    $table->dropColumn('parent_patient_id');
                }
            });
        }
        if (Schema::hasTable('case_references') &&
            Schema::hasColumn('case_references', 'parent_case_id')) {
            Schema::table('case_references', function (Blueprint $table) {
                $table->dropForeign(['parent_case_id']);
                $table->dropColumn('parent_case_id');
            });
        }
    }
};
