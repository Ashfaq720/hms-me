<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bed_types', function (Blueprint $table) {
            // Distinguishes ICU subtype: ICU / CCU / NICU / PICU
            // Nullable so non-ICU bed types are unaffected.
            $table->string('icu_type', 10)->nullable()->after('is_icu');
            $table->boolean('has_ventilator_support')->default(false)->after('icu_type');
            $table->boolean('has_monitor_support')->default(false)->after('has_ventilator_support');
            $table->boolean('is_isolation_bed')->default(false)->after('has_monitor_support');
            $table->string('allowed_isolation_type', 30)->nullable()->after('is_isolation_bed');
        });
    }

    public function down(): void
    {
        Schema::table('bed_types', function (Blueprint $table) {
            $table->dropColumn([
                'icu_type',
                'has_ventilator_support',
                'has_monitor_support',
                'is_isolation_bed',
                'allowed_isolation_type',
            ]);
        });
    }
};
