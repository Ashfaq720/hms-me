<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_visitors', function (Blueprint $table) {
            $table->time('visit_time')->nullable()->after('visit_date');
        });
    }

    public function down(): void
    {
        Schema::table('patient_visitors', function (Blueprint $table) {
            $table->dropColumn('visit_time');
        });
    }
};
