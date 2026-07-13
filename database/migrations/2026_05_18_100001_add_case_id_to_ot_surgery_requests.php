<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ot_surgery_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('case_id')->nullable()->after('id');
            $table->index('case_id');
        });
    }

    public function down(): void
    {
        Schema::table('ot_surgery_requests', function (Blueprint $table) {
            $table->dropIndex(['case_id']);
            $table->dropColumn('case_id');
        });
    }
};
