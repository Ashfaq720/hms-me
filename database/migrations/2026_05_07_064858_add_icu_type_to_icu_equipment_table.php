<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('icu_equipment', function (Blueprint $table) {
            // ICU / CCU / NICU / PICU — explicit unit ownership
            $table->string('icu_type', 10)->nullable()->after('equipment_type')->index();
        });
    }

    public function down(): void
    {
        Schema::table('icu_equipment', function (Blueprint $table) {
            $table->dropIndex(['icu_type']);
            $table->dropColumn('icu_type');
        });
    }
};
