<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Link an IPD-attached package to the specific bed allocation it
 * applies to. Eliminates the "bed feels duplicated" perception —
 * the package row now points at the actual bed allocation it covers,
 * rather than re-describing the bed in parallel.
 *
 * Nullable: lets older rows (and OPD/Diagnostic packages with no bed)
 * stay valid.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ipd_patient_packages')) return;
        if (Schema::hasColumn('ipd_patient_packages', 'bed_allocation_id')) return;

        Schema::table('ipd_patient_packages', function (Blueprint $table) {
            $table->foreignId('bed_allocation_id')->nullable()
                  ->after('service_package_id')
                  ->constrained('ipd_patient_beds')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ipd_patient_packages')) return;
        if (! Schema::hasColumn('ipd_patient_packages', 'bed_allocation_id')) return;

        Schema::table('ipd_patient_packages', function (Blueprint $table) {
            $table->dropForeign(['bed_allocation_id']);
            $table->dropColumn('bed_allocation_id');
        });
    }
};
