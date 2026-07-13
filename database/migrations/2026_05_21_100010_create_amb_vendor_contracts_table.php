<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amb_vendor_contracts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vendor_id')->constrained('amb_vendors')->cascadeOnDelete();
            $t->string('contract_ref')->unique()->nullable(); // e.g. CONT-2026-001
            $t->enum('rate_type', ['PER_KM', 'FIXED', 'PACKAGE'])->default('FIXED');
            $t->decimal('rate_amount', 10, 2);
            $t->decimal('per_km_rate', 8, 2)->nullable(); // only for PER_KM type
            $t->integer('sla_response_minutes')->default(20);
            $t->date('contract_start');
            $t->date('contract_end');
            $t->enum('status', ['ACTIVE', 'EXPIRED', 'TERMINATED'])->default('ACTIVE');
            $t->text('terms')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amb_vendor_contracts');
    }
};
