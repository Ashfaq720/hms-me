<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bed_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('bed_groups', 'room_id')) {
                $table->unsignedBigInteger('room_id')->nullable()->after('id');
                $table->foreign('room_id')->references('id')->on('rooms')->cascadeOnDelete();
            }

            if (Schema::hasColumn('bed_groups', 'floor_id')) {
                try { $table->dropForeign(['floor_id']); } catch (\Throwable $e) {}
                $table->dropColumn('floor_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bed_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('bed_groups', 'floor_id')) {
                $table->unsignedBigInteger('floor_id')->nullable()->after('id');
                $table->foreign('floor_id')->references('id')->on('floors')->cascadeOnDelete();
            }

            if (Schema::hasColumn('bed_groups', 'room_id')) {
                try { $table->dropForeign(['room_id']); } catch (\Throwable $e) {}
                $table->dropColumn('room_id');
            }
        });
    }
};
