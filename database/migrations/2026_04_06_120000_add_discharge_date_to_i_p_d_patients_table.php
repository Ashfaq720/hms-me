<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('i_p_d_patients', function (Blueprint $table) {
            $table->datetime('discharge_date')->nullable()->after('possible_discharge_date');
        });
    }

    public function down(): void
    {
        Schema::table('i_p_d_patients', function (Blueprint $table) {
            $table->dropColumn('discharge_date');
        });
    }
};
