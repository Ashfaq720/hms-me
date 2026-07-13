<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('visit_status', [
                'booked',
                'checked_in',
                'in_consultation',
                'completed',
                'cancelled',
                'no_show',
            ])->default('booked')->after('appointment_status');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('visit_status');
        });
    }
};
