<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_billing_mode_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('icu_admission_id')->index();
            $table->string('old_billing_mode', 20)->nullable();
            $table->string('new_billing_mode', 20)->nullable();
            $table->unsignedBigInteger('old_package_id')->nullable();
            $table->unsignedBigInteger('new_package_id')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->dateTime('changed_at');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_billing_mode_audit_logs');
    }
};
