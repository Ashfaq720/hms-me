<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opd_patients', function (Blueprint $table) {
            if (!Schema::hasColumn('opd_patients', 'shift_id')) {
                $table->unsignedBigInteger('shift_id')->nullable()->after('doctor_id');
            }
            if (!Schema::hasColumn('opd_patients', 'slot_time_from')) {
                $table->time('slot_time_from')->nullable()->after('shift_id');
            }
            if (!Schema::hasColumn('opd_patients', 'slot_time_to')) {
                $table->time('slot_time_to')->nullable()->after('slot_time_from');
            }
        });
    }

    public function down(): void
    {
        Schema::table('opd_patients', function (Blueprint $table) {
            $table->dropColumn(['shift_id', 'slot_time_from', 'slot_time_to']);
        });
    }
};
