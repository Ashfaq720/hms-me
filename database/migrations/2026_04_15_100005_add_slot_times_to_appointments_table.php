<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->time('slot_time_from')->nullable()->after('shift_id');
            $table->time('slot_time_to')->nullable()->after('slot_time_from');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['slot_time_from', 'slot_time_to']);
        });
    }
};
