<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('amb_vendors', function (Blueprint $t) {
            $t->string('contact_person')->nullable()->after('vendor_name');
            $t->enum('ambulance_type', ['BASIC', 'EMERGENCY', 'ALS', 'ICU', 'NEONATAL', 'MIXED'])
              ->default('BASIC')->after('email');
            $t->enum('rate_contract_type', ['PER_KM', 'FIXED', 'PACKAGE'])->default('FIXED')->after('ambulance_type');
            $t->decimal('base_rate', 10, 2)->nullable()->after('rate_contract_type');
            $t->decimal('performance_score', 4, 2)->nullable()->comment('0.00 to 5.00')->after('sla_response_minutes');
            $t->text('notes')->nullable()->after('performance_score');
        });
    }

    public function down(): void
    {
        Schema::table('amb_vendors', function (Blueprint $t) {
            $t->dropColumn(['contact_person', 'ambulance_type', 'rate_contract_type', 'base_rate', 'performance_score', 'notes']);
        });
    }
};
