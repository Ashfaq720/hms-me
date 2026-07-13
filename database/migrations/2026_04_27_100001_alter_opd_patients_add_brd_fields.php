<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend visit_type enum to include referred and emergency
        DB::statement("ALTER TABLE opd_patients MODIFY COLUMN visit_type ENUM('new','follow_up','recheckup','referred','emergency') NOT NULL DEFAULT 'new'");

        Schema::table('opd_patients', function (Blueprint $table) {
            if (!Schema::hasColumn('opd_patients', 'chief_complaint')) {
                $table->text('chief_complaint')->nullable()->after('remarks');
            }
            if (!Schema::hasColumn('opd_patients', 'referral_source')) {
                $table->string('referral_source')->nullable()->after('chief_complaint');
            }
        });
    }

    public function down(): void
    {
        Schema::table('opd_patients', function (Blueprint $table) {
            $table->dropColumn(['chief_complaint', 'referral_source']);
        });

        DB::statement("ALTER TABLE opd_patients MODIFY COLUMN visit_type ENUM('new','follow_up','recheckup') NOT NULL DEFAULT 'new'");
    }
};
