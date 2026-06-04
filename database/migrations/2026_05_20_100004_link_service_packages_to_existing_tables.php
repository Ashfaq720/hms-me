<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Wire the new service_packages master into the modules that need to
 * reference it:
 *   - ot_surgery_schedules : when an OT booking uses a package, link it
 *   - patient_charges      : when a charge is part of a package, stamp it
 *
 * All columns are nullable — existing data is untouched and existing code
 * paths continue to work unchanged. The FKs use nullOnDelete so removing
 * a package never cascades into destroying surgery or billing rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ot_surgery_schedules') &&
            ! Schema::hasColumn('ot_surgery_schedules', 'service_package_id')) {
            Schema::table('ot_surgery_schedules', function (Blueprint $table) {
                $table->foreignId('service_package_id')->nullable()->after('ot_room_id')
                      ->constrained('service_packages')->nullOnDelete();
            });
        }

        if (Schema::hasTable('patient_charges') &&
            ! Schema::hasColumn('patient_charges', 'service_package_id')) {
            Schema::table('patient_charges', function (Blueprint $table) {
                $table->foreignId('service_package_id')->nullable()->after('case_id')
                      ->constrained('service_packages')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ot_surgery_schedules') &&
            Schema::hasColumn('ot_surgery_schedules', 'service_package_id')) {
            Schema::table('ot_surgery_schedules', function (Blueprint $table) {
                $table->dropForeign(['service_package_id']);
                $table->dropColumn('service_package_id');
            });
        }

        if (Schema::hasTable('patient_charges') &&
            Schema::hasColumn('patient_charges', 'service_package_id')) {
            Schema::table('patient_charges', function (Blueprint $table) {
                $table->dropForeign(['service_package_id']);
                $table->dropColumn('service_package_id');
            });
        }
    }
};
