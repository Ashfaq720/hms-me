<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('i_p_d_patients', function (Blueprint $table) {
            $table->string('admission_type')->nullable()->after('possible_discharge_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('i_p_d_patients', function (Blueprint $table) {
            $table->dropColumn('admission_type');
        });
    }
};
