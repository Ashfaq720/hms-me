<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ot_surgery_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('blood_group_id')->nullable()->after('blood_group');
            $table->foreign('blood_group_id')->references('id')->on('blood_groups')->onDelete('set null');
            $table->index('blood_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('ot_surgery_requests', function (Blueprint $table) {
            $table->dropForeign(['blood_group_id']);
            $table->dropIndex(['blood_group_id']);
            $table->dropColumn('blood_group_id');
        });
    }
};
