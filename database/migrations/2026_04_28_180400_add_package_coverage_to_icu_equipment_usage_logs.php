<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('icu_equipment_usage_logs', function (Blueprint $table) {
            $table->boolean('covered_by_package')->default(false)->after('total_amount');
            $table->unsignedBigInteger('package_enrollment_id')->nullable()->after('covered_by_package');
        });
    }

    public function down(): void
    {
        Schema::table('icu_equipment_usage_logs', function (Blueprint $table) {
            $table->dropColumn(['covered_by_package', 'package_enrollment_id']);
        });
    }
};
