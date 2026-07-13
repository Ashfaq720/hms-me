<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Service Packages — master table.
 *
 * Distinct from the older `packages` and `icu_packages` tables, which stay
 * exactly as they are. This is the rich, hospital-wide package master that
 * Front Desk uses during patient registration to attach bundled services
 * by patient type / department / bed type.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();          // PKG-OT-001
            $table->string('name', 200);
            $table->string('slug', 220)->nullable()->index();

            $table->enum('package_type', [
                'IPD', 'OPD', 'OT', 'ICU', 'CCU',
                'Diagnostic', 'Health Checkup', 'Procedure',
            ])->index();

            $table->foreignId('department_id')->nullable()
                  ->constrained('departments')->nullOnDelete();

            // For IPD-class packages only — Planned / Emergency / Day Care
            $table->enum('admission_type', ['Planned', 'Emergency', 'Day Care'])->nullable();

            // Default bed type for the package — used when bed-wise pricing is not
            // configured. Specific bed prices live in service_package_bed_prices.
            $table->foreignId('bed_type_id')->nullable()
                  ->constrained('bed_types')->nullOnDelete();

            $table->unsignedSmallInteger('duration_days')->nullable();

            // The bundled price patients pay. Bed-wise variants (if any) override.
            $table->decimal('base_price', 12, 2)->default(0);

            $table->longText('included_services_text')->nullable();
            $table->longText('excluded_services_text')->nullable();

            $table->enum('patient_category', ['General', 'Corporate', 'Insurance'])->nullable();

            $table->boolean('requires_approval')->default(false);
            $table->string('approval_role', 60)->nullable();    // 'Billing Manager' / 'Admin' / 'Duty Manager'

            $table->enum('status', ['Active', 'Inactive'])->default('Active')->index();

            $table->text('remarks')->nullable();

            $table->foreignId('created_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()
                  ->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['package_type', 'department_id', 'status'], 'svc_pkg_type_dept_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_packages');
    }
};
