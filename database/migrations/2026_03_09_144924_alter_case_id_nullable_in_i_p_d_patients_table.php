<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('i_p_d_patients', function (Blueprint $table) {
            $table->unsignedBigInteger('case_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('i_p_d_patients', function (Blueprint $table) {
            $table->unsignedBigInteger('case_id')->nullable(false)->change();
        });
    }
};
