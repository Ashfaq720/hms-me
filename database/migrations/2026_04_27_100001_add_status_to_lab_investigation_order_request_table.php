<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_investigation_order_request', function (Blueprint $table) {
            $table->string('status')->nullable()->default('Pending')->after('lab_inv_category_id');
            $table->string('file')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('lab_investigation_order_request', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('file');
        });
    }
};
