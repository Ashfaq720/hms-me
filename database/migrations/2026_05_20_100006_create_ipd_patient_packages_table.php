<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * IPD patient packages — junction between an IPD admission and the
 * service packages applied to it. One admission can carry several
 * packages (e.g. IPD nursing + OT + neonatal for a maternity case).
 *
 * The status drives billing and modification rules. Approval fields
 * are populated only when the parent service_package.requires_approval
 * is true.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ipd_patient_packages')) return;

        Schema::create('ipd_patient_packages', function (Blueprint $table) {
            $table->id();
            // IPD table name is `i_p_d_patients` — Laravel's snake_case
            // conversion of the model class `IpdPatient` (consecutive
            // capitals become separate snake-case tokens).
            $table->foreignId('ipd_admission_id')
                  ->constrained('i_p_d_patients')->cascadeOnDelete();
            $table->foreignId('service_package_id')
                  ->constrained('service_packages')->restrictOnDelete();

            // Snapshot the price actually agreed at apply-time. Lets us
            // change the package master later without rewriting history.
            $table->decimal('agreed_price', 12, 2);
            $table->decimal('price_override', 12, 2)->nullable();
            $table->foreignId('price_override_approved_by')->nullable()
                  ->constrained('users')->nullOnDelete();

            $table->enum('status', [
                'Draft', 'Pending Approval', 'Confirmed',
                'Partially Used', 'Completed',
                'Cancelled', 'Refunded', 'Closed',
            ])->default('Draft')->index();

            // Approval tracking (only relevant when the parent package's
            // requires_approval = true).
            $table->foreignId('approved_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // Cancellation / refund audit
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();

            $table->text('remarks')->nullable();

            $table->foreignId('applied_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->timestamp('applied_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['ipd_admission_id', 'status'], 'ipd_pkg_admission_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_patient_packages');
    }
};
