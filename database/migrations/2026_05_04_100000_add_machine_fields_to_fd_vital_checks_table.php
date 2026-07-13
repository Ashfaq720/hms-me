<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fd_vital_checks', function (Blueprint $t) {
            $t->boolean('machine_fetched')->default(false)->after('spo2');
            $t->string('machine_device_id', 100)->nullable()->after('machine_fetched');
        });
    }

    public function down(): void
    {
        Schema::table('fd_vital_checks', function (Blueprint $t) {
            $t->dropColumn(['machine_fetched', 'machine_device_id']);
        });
    }
};
