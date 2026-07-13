<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('ot_pacu_records', 'recovery_clearance')) {
            return;
        }
        Schema::table('ot_pacu_records', function (Blueprint $table) {
            $table->boolean('recovery_clearance')->default(false)->after('aldrete_score');
            $table->text('recovery_clearance_notes')->nullable()->after('recovery_clearance');
            $table->unsignedBigInteger('cleared_by')->nullable()->after('recovery_clearance_notes');
            $table->dateTime('cleared_at')->nullable()->after('cleared_by');
            $table->string('consciousness_level', 30)->nullable()->after('cleared_at');
        });
    }

    public function down(): void
    {
        Schema::table('ot_pacu_records', function (Blueprint $table) {
            $table->dropColumn([
                'recovery_clearance', 'recovery_clearance_notes', 'cleared_by',
                'cleared_at', 'consciousness_level',
            ]);
        });
    }
};
