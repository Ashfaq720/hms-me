<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('icu_mortality_audits', function (Blueprint $table) {
            $table->text('final_diagnosis')->nullable()->after('death_time');
        });
    }

    public function down(): void
    {
        Schema::table('icu_mortality_audits', function (Blueprint $table) {
            $table->dropColumn('final_diagnosis');
        });
    }
};
