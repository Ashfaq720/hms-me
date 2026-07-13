<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ipd_patient_beds', function (Blueprint $table) {
            // 'bed' for regular bed allocation, 'icu' for ICU allocation
            $table->string('allocation_type', 20)->default('bed')->after('bed_id');
        });
    }

    public function down(): void
    {
        Schema::table('ipd_patient_beds', function (Blueprint $table) {
            $table->dropColumn('allocation_type');
        });
    }
};
