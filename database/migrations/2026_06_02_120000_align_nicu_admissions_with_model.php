<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Align nicu_admissions with App\Models\NicuAdmission expectations.
 *
 * Why: two earlier migrations both created `nicu_admissions` —
 *   2026_05_20_100001_create_nicu_module_tables.php (lean schema, ran first)
 *   2026_05_21_100002_create_nicu_admissions_table.php (rich schema, skipped
 *   by its own hasTable guard)
 * The model, observer, controller, views and EndToEndSampleSeeder all use
 * the rich schema. This migration adds the missing columns to the lean
 * table so everything else can keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('nicu_admissions')) return;

        Schema::table('nicu_admissions', function (Blueprint $t) {
            if (! Schema::hasColumn('nicu_admissions', 'admission_no')) {
                $t->string('admission_no', 30)->nullable()->after('id');
            }
            if (! Schema::hasColumn('nicu_admissions', 'baby_patient_id')) {
                $t->foreignId('baby_patient_id')->nullable()->after('mother_ipd_patient_id')
                    ->constrained('patients')->nullOnDelete();
            }
            if (! Schema::hasColumn('nicu_admissions', 'case_id')) {
                $t->foreignId('case_id')->nullable()->after('baby_patient_id')
                    ->constrained('case_references')->nullOnDelete();
            }
            if (! Schema::hasColumn('nicu_admissions', 'source_type')) {
                $t->string('source_type', 32)->nullable()->after('case_id');
            }
            if (! Schema::hasColumn('nicu_admissions', 'source_id')) {
                $t->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }
            if (! Schema::hasColumn('nicu_admissions', 'bed_id')) {
                $t->foreignId('bed_id')->nullable()->after('source_id')
                    ->constrained('beds')->nullOnDelete();
            }
            if (! Schema::hasColumn('nicu_admissions', 'bed_type_id')) {
                $t->foreignId('bed_type_id')->nullable()->after('bed_id')
                    ->constrained('bed_types')->nullOnDelete();
            }
            if (! Schema::hasColumn('nicu_admissions', 'admitted_at')) {
                $t->dateTime('admitted_at')->nullable()->after('admission_time');
            }
            if (! Schema::hasColumn('nicu_admissions', 'birth_at')) {
                $t->dateTime('birth_at')->nullable()->after('admitted_at');
            }
            if (! Schema::hasColumn('nicu_admissions', 'birth_weight_grams')) {
                $t->decimal('birth_weight_grams', 8, 2)->nullable()->after('birth_at');
            }
            if (! Schema::hasColumn('nicu_admissions', 'delivery_type')) {
                $t->string('delivery_type', 16)->nullable()->after('apgar_10min');
            }
            if (! Schema::hasColumn('nicu_admissions', 'service_package_id')) {
                $t->foreignId('service_package_id')->nullable()->after('delivery_type')
                    ->constrained('service_packages')->nullOnDelete();
            }
            if (! Schema::hasColumn('nicu_admissions', 'clinical_notes')) {
                $t->text('clinical_notes')->nullable()->after('admission_notes');
            }
            if (! Schema::hasColumn('nicu_admissions', 'discharge_summary')) {
                $t->text('discharge_summary')->nullable()->after('clinical_notes');
            }
            if (! Schema::hasColumn('nicu_admissions', 'discharged_at')) {
                $t->dateTime('discharged_at')->nullable()->after('discharge_time');
            }
            if (! Schema::hasColumn('nicu_admissions', 'discharged_by')) {
                $t->foreignId('discharged_by')->nullable()->after('discharged_at')
                    ->constrained('users')->nullOnDelete();
            }
        });

        // Backfill the columns the model needs to be non-null/usable.
        // 1. admission_no for any existing rows (NICU-YYYY-NNNNNN).
        $rows = DB::table('nicu_admissions')->whereNull('admission_no')->orderBy('id')->get(['id']);
        $year = date('Y');
        $startN = 1;
        foreach ($rows as $r) {
            $no = sprintf('NICU-%s-%06d', $year, $startN++);
            DB::table('nicu_admissions')->where('id', $r->id)->update(['admission_no' => $no]);
        }

        // 2. admitted_at — copy from admission_time when present.
        DB::statement(
            'UPDATE nicu_admissions SET admitted_at = admission_time
             WHERE admitted_at IS NULL AND admission_time IS NOT NULL'
        );

        // 3. birth_weight_grams — copy from birth_weight_g when present.
        if (Schema::hasColumn('nicu_admissions', 'birth_weight_g')) {
            DB::statement(
                'UPDATE nicu_admissions SET birth_weight_grams = birth_weight_g
                 WHERE birth_weight_grams IS NULL AND birth_weight_g IS NOT NULL'
            );
        }

        // 4. delivery_type — map from birth_type enum (NORMAL/C_SECTION/ASSISTED).
        if (Schema::hasColumn('nicu_admissions', 'birth_type')) {
            DB::statement("
                UPDATE nicu_admissions SET delivery_type = CASE birth_type
                    WHEN 'NORMAL' THEN 'Vaginal'
                    WHEN 'C_SECTION' THEN 'C-Section'
                    WHEN 'ASSISTED' THEN 'Assisted'
                    ELSE 'Other' END
                WHERE delivery_type IS NULL AND birth_type IS NOT NULL
            ");
        }

        // 5. Now mark admission_no unique (after backfill).
        Schema::table('nicu_admissions', function (Blueprint $t) {
            $t->unique('admission_no', 'nicu_admissions_admission_no_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('nicu_admissions')) return;

        Schema::table('nicu_admissions', function (Blueprint $t) {
            $t->dropUnique('nicu_admissions_admission_no_unique');
            $drop = ['admission_no','baby_patient_id','case_id','source_type','source_id',
                'bed_id','bed_type_id','admitted_at','birth_at','birth_weight_grams',
                'delivery_type','service_package_id','clinical_notes','discharge_summary',
                'discharged_at','discharged_by'];
            foreach ($drop as $col) {
                if (Schema::hasColumn('nicu_admissions', $col)) {
                    // Foreign-keyed columns need the FK dropped first.
                    if (in_array($col, ['baby_patient_id','case_id','bed_id','bed_type_id','service_package_id','discharged_by'])) {
                        try { $t->dropForeign(["nicu_admissions_{$col}_foreign"]); } catch (\Throwable $e) {}
                    }
                    $t->dropColumn($col);
                }
            }
        });
    }
};
