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
        Schema::table('patient_charges', function (Blueprint $table) {
            $table->unsignedBigInteger('charge_id')->nullable()->after('charge_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_charges', function (Blueprint $table) {
            $table->dropColumn('charge_id');
        });
    }
};
