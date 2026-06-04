<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add license_validity + GENERAL ambulance type
        Schema::table('amb_ambulances', function (Blueprint $table) {
            $table->date('license_validity')->nullable()->after('insurance_expiry');
        });
        DB::statement("ALTER TABLE amb_ambulances MODIFY COLUMN type ENUM('BLS','GENERAL','EMERGENCY','ALS','ICU','NEONATAL') NOT NULL");

        // 2. Fix source enum (Ipd → IPD) + add destination_hospital
        DB::statement("UPDATE amb_requests SET source = 'IPD' WHERE source = 'Ipd'");
        DB::statement("ALTER TABLE amb_requests MODIFY COLUMN source ENUM('ER_DESK','OPD','IPD','CALL_CENTER','REFERRAL') NULL");
        Schema::table('amb_requests', function (Blueprint $table) {
            $table->string('destination_hospital')->nullable()->after('drop_location');
        });
    }

    public function down(): void
    {
        Schema::table('amb_requests', function (Blueprint $table) {
            $table->dropColumn('destination_hospital');
        });
        DB::statement("UPDATE amb_requests SET source = 'Ipd' WHERE source = 'IPD'");
        DB::statement("ALTER TABLE amb_requests MODIFY COLUMN source ENUM('ER_DESK','OPD','Ipd','CALL_CENTER','REFERRAL') NULL");

        DB::statement("ALTER TABLE amb_ambulances MODIFY COLUMN type ENUM('BLS','EMERGENCY','ALS','ICU','NEONATAL') NOT NULL");
        Schema::table('amb_ambulances', function (Blueprint $table) {
            $table->dropColumn('license_validity');
        });
    }
};
