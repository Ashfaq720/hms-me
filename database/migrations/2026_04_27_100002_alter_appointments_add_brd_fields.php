<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend visit_status enum
        DB::statement("ALTER TABLE appointments MODIFY COLUMN visit_status ENUM('booked','checked_in','waiting','in_consultation','completed','closed','cancelled','no_show','referred','converted_to_er') NOT NULL DEFAULT 'booked'");

        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable()->after('visit_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('cancellation_reason');
        });

        DB::statement("ALTER TABLE appointments MODIFY COLUMN visit_status ENUM('booked','checked_in','in_consultation','completed','cancelled','no_show') NOT NULL DEFAULT 'booked'");
    }
};
