<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL does not support ALTER COLUMN on enum directly — modify via raw SQL
        DB::statement("ALTER TABLE amb_trips MODIFY COLUMN status ENUM(
            'ASSIGNED',
            'EN_ROUTE_PICKUP',
            'ARRIVED_AT_PICKUP',
            'PATIENT_ONBOARD',
            'EN_ROUTE_HOSPITAL',
            'ARRIVED_AT_HOSPITAL',
            'COMPLETED'
        ) NOT NULL DEFAULT 'ASSIGNED'");

        Schema::table('amb_trips', function (Blueprint $t) {
            $t->timestamp('pickup_arrival_time')->nullable()->after('started_at');
            $t->timestamp('patient_onboard_time')->nullable()->after('pickup_arrival_time');
            $t->timestamp('hospital_arrival_time')->nullable()->after('patient_onboard_time');
        });
    }

    public function down(): void
    {
        Schema::table('amb_trips', function (Blueprint $t) {
            $t->dropColumn(['pickup_arrival_time', 'patient_onboard_time', 'hospital_arrival_time']);
        });

        DB::statement("ALTER TABLE amb_trips MODIFY COLUMN status ENUM(
            'ASSIGNED',
            'EN_ROUTE_PICKUP',
            'PATIENT_ONBOARD',
            'EN_ROUTE_HOSPITAL',
            'COMPLETED'
        ) NOT NULL DEFAULT 'ASSIGNED'");
    }
};
