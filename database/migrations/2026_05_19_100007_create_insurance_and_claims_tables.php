<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Insurance & Claims foundation (SRS §5.21). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('code', 32);
            $table->string('name');
            $table->enum('type', ['insurance', 'corporate', 'government', 'tpa', 'self'])->default('insurance');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('default_discount_percent', 5, 2)->default(0);
            $table->boolean('pre_auth_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['organization_id', 'code']);
        });

        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payer_id')->constrained('payers')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('policy_no');
            $table->string('plan_name')->nullable();
            $table->date('valid_from');
            $table->date('valid_to');
            $table->decimal('coverage_limit', 14, 2)->default(0);
            $table->decimal('deductible', 14, 2)->default(0);
            $table->decimal('copay_percent', 5, 2)->default(0);
            $table->string('subscriber_name')->nullable();
            $table->string('relationship', 32)->nullable();
            $table->enum('status', ['active', 'expired', 'suspended', 'cancelled'])->default('active');
            $table->timestamps();
            $table->unique(['payer_id', 'policy_no']);
            $table->index(['patient_id', 'status']);
        });

        Schema::create('pre_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_policy_id')->constrained('insurance_policies')->cascadeOnDelete();
            $table->foreignId('encounter_id')->nullable()->constrained('encounters')->nullOnDelete();
            $table->string('pre_auth_no', 64)->unique();
            $table->decimal('requested_amount', 14, 2);
            $table->decimal('approved_amount', 14, 2)->default(0);
            $table->text('diagnosis')->nullable();
            $table->text('justification')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'partially_approved', 'rejected', 'expired'])->default('draft');
            $table->string('payer_reference_no')->nullable();
            $table->date('valid_until')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('decision_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('payer_id')->constrained('payers');
            $table->foreignId('insurance_policy_id')->constrained('insurance_policies');
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('encounter_id')->nullable()->constrained('encounters')->nullOnDelete();
            $table->foreignId('pre_authorization_id')->nullable()->constrained('pre_authorizations')->nullOnDelete();

            $table->string('claim_no', 64)->unique();
            $table->string('bill_reference')->nullable();

            $table->decimal('gross_amount', 14, 2);
            $table->decimal('patient_copay', 14, 2)->default(0);
            $table->decimal('claim_amount', 14, 2);
            $table->decimal('approved_amount', 14, 2)->default(0);
            $table->decimal('settled_amount', 14, 2)->default(0);

            $table->enum('status', [
                'draft', 'submitted', 'under_review', 'approved', 'rejected',
                'short_paid', 'settled', 'appeal', 'closed',
            ])->default('draft');

            $table->date('claim_date');
            $table->date('submission_date')->nullable();
            $table->date('settlement_date')->nullable();

            $table->json('attachments')->nullable();
            $table->text('denial_reason')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('claim_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->cascadeOnDelete();
            $table->foreignId('service_catalog_id')->nullable()->constrained('service_catalogs')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 4)->default(1);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('line_total', 14, 2);
            $table->decimal('approved_amount', 14, 2)->default(0);
            $table->string('denial_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_items');
        Schema::dropIfExists('claims');
        Schema::dropIfExists('pre_authorizations');
        Schema::dropIfExists('insurance_policies');
        Schema::dropIfExists('payers');
    }
};
