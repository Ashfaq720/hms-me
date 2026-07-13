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
        Schema::table('ipd_nurse_notes', function (Blueprint $table) {
            $table->string('title')->nullable()->after('ipd_patient_id');
            $table->string('doctor_category')->nullable()->after('title');
            $table->string('shift')->nullable()->after('doctor_category');
            $table->foreignId('doctor_id')->nullable()->after('shift')->constrained('doctors')->cascadeOnDelete();
            $table->enum('priority', ['Normal', 'Urgent', 'Critical'])->default('Normal')->after('doctor_id');
            $table->string('file')->nullable()->after('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipd_nurse_notes', function (Blueprint $table) {
            $table->dropColumn(['title', 'doctor_category', 'shift', 'doctor_id', 'priority', 'file']);
        });
    }
};
