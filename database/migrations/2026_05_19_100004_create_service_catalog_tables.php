<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Service Charge engine (SRS §5.18).
 *
 *  service_catalogs            – catalog of every billable service
 *  service_charge_rules        – branch/patient-type/package-specific pricing
 *  service_charge_postings     – audit log of each auto-post (immutable)
 *  service_charge_audit_logs   – price-change / override audit
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_catalogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            $table->string('code', 64);
            $table->string('name');
            $table->string('department_code', 64)->nullable();

            // service_type drives which workflow auto-posts the charge
            $table->enum('service_type', [
                'consultation', 'bed', 'icu_bed', 'nicu_bed', 'ot_room',
                'nursing', 'procedure', 'lab_test', 'radiology',
                'pharmacy', 'equipment', 'ambulance', 'package',
                'administrative', 'other',
            ]);

            // unit drives how many charge lines to post
            $table->enum('charge_unit', [
                'per_use', 'per_hour', 'per_day', 'per_session', 'per_unit',
                'per_km', 'per_test', 'per_dose', 'per_package',
            ])->default('per_use');

            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);

            $table->enum('patient_type', ['all', 'self', 'corporate', 'insurance', 'staff'])->default('all');

            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();

            $table->boolean('discount_allowed')->default(true);
            $table->boolean('insurance_covered')->default(true);
            $table->boolean('package_eligible')->default(true);
            $table->boolean('is_active')->default(true);

            $table->text('description')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['branch_id', 'code']);
            $table->index(['service_type', 'is_active']);
        });

        Schema::create('service_charge_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_catalog_id')->constrained('service_catalogs')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // rule_kind defines how the rule is matched. Examples:
            //   bed_type, ward, room, encounter_type, package, payer, patient_type, time_band
            $table->string('rule_kind', 64);
            $table->string('rule_value');

            $table->enum('adjustment_type', ['percent', 'flat', 'override'])->default('percent');
            $table->decimal('adjustment_value', 12, 4)->default(0);

            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();

            $table->unsignedInteger('priority')->default(100);
            $table->boolean('requires_approval')->default(false);
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['service_catalog_id', 'rule_kind', 'is_active'], 'svc_rule_lookup_idx');
        });

        Schema::create('service_charge_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('encounter_id')->nullable()->constrained('encounters')->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignId('service_catalog_id')->constrained('service_catalogs');

            // Trigger event metadata so we can prove WHY this charge was posted.
            $table->string('trigger_event', 64);
            $table->string('trigger_source_type')->nullable();
            $table->unsignedBigInteger('trigger_source_id')->nullable();

            $table->decimal('quantity', 12, 4)->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);

            $table->json('rules_applied')->nullable();
            $table->json('metadata')->nullable();
            $table->string('reason')->nullable();

            $table->enum('status', ['posted', 'reversed', 'voided'])->default('posted');
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();

            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['encounter_id', 'status']);
            $table->index(['patient_id', 'created_at']);
            $table->index(['trigger_source_type', 'trigger_source_id'], 'svc_post_trigger_idx');
        });

        Schema::create('service_charge_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_catalog_id')->nullable()->constrained('service_catalogs')->nullOnDelete();
            $table->foreignId('service_charge_rule_id')->nullable()->constrained('service_charge_rules')->nullOnDelete();
            $table->foreignId('service_charge_posting_id')->nullable()->constrained('service_charge_postings')->nullOnDelete();

            $table->string('action', 64);
            $table->json('before_value')->nullable();
            $table->json('after_value')->nullable();
            $table->string('reason')->nullable();

            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_charge_audit_logs');
        Schema::dropIfExists('service_charge_postings');
        Schema::dropIfExists('service_charge_rules');
        Schema::dropIfExists('service_catalogs');
    }
};
