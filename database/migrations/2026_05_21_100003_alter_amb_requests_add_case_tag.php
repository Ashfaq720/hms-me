<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('amb_requests', function (Blueprint $t) {
            $t->enum('case_tag', ['TRAUMA', 'STROKE', 'CARDIAC', 'RESPIRATORY', 'OTHER'])
              ->nullable()
              ->after('patient_condition');
            $t->string('requested_by_name')->nullable()->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('amb_requests', function (Blueprint $t) {
            $t->dropColumn(['case_tag', 'requested_by_name']);
        });
    }
};
