<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('icd10_code', 20)->nullable()->after('findings');
            $table->string('icd10_description')->nullable()->after('icd10_code');
            $table->text('follow_up_note')->nullable()->after('next_visit');
            $table->text('radiology_orders')->nullable()->after('follow_up_note');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn(['icd10_code', 'icd10_description', 'follow_up_note', 'radiology_orders']);
        });
    }
};
