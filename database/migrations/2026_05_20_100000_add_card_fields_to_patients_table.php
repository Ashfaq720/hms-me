<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('card_status', 20)->nullable()->default('active')->after('health_card_no');
            $table->string('card_type', 30)->nullable()->after('card_status');
            $table->date('card_issued_at')->nullable()->after('card_type');
            $table->date('card_expires_at')->nullable()->after('card_issued_at');
            $table->text('card_notes')->nullable()->after('card_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'card_status',
                'card_type',
                'card_issued_at',
                'card_expires_at',
                'card_notes',
            ]);
        });
    }
};
