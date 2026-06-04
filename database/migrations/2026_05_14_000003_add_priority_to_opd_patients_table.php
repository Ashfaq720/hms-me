<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE opd_patients
            ADD COLUMN priority ENUM('Normal','Senior Citizen','VIP','Emergency')
            NOT NULL DEFAULT 'Normal'
            AFTER status");
    }

    public function down(): void
    {
        Schema::table('opd_patients', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
