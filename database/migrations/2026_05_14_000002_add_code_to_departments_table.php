<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('code', 10)->nullable()->after('name');
        });

        // Auto-generate code from existing department names (first 3 consonant-heavy letters)
        DB::table('departments')->get()->each(function ($dept) {
            $clean = strtoupper(preg_replace('/[^A-Za-z]/', '', $dept->name));
            $code  = substr($clean, 0, 3);
            $code  = str_pad($code ?: 'GEN', 3, 'X');
            DB::table('departments')->where('id', $dept->id)->update(['code' => $code]);
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
