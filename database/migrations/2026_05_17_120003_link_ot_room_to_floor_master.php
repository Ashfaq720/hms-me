<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // floor_id was already a nullable unsigned column; now add the FK constraint.
        if (Schema::hasTable('floors') && Schema::hasColumn('ot_rooms', 'floor_id')) {
            Schema::table('ot_rooms', function (Blueprint $table) {
                try {
                    $table->foreign('floor_id')->references('id')->on('floors')->onDelete('set null');
                } catch (\Throwable $e) {
                    // FK may already exist on retry — silently ignore
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ot_rooms', 'floor_id')) {
            Schema::table('ot_rooms', function (Blueprint $table) {
                try { $table->dropForeign(['floor_id']); } catch (\Throwable $e) {}
            });
        }
    }
};
