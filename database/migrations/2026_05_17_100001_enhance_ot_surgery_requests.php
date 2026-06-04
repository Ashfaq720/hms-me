<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ot_surgery_requests', function (Blueprint $table) {
            // FR-03: diagnosis detail
            $table->text('secondary_diagnosis')->nullable()->after('diagnosis');
            $table->string('icd_code', 20)->nullable()->after('secondary_diagnosis');

            // FR-04: emergency metadata
            $table->text('emergency_reason')->nullable()->after('is_emergency');
            $table->boolean('is_life_threatening')->default(false)->after('emergency_reason');
            $table->boolean('is_immediate_ot')->default(false)->after('is_life_threatening');

            // FR-06: preferred date flexibility
            $table->string('date_flexibility', 20)->default('Flexible')->after('estimated_duration_minutes');
            $table->text('flexibility_reason')->nullable()->after('date_flexibility');

            // FR-07: explicit required OT type
            $table->string('required_ot_type', 50)->nullable()->after('flexibility_reason');

            // FR-09: blood arrangement detail
            $table->json('blood_components')->nullable()->after('blood_group');
            $table->boolean('crossmatch_required')->default(false)->after('blood_components');
            $table->text('blood_bank_instruction')->nullable()->after('crossmatch_required');

            // FR-13: hierarchical approval
            $table->boolean('junior_approval_required')->default(false)->after('reviewed_at');
            $table->unsignedBigInteger('junior_approved_by')->nullable()->after('junior_approval_required');
            $table->dateTime('junior_approved_at')->nullable()->after('junior_approved_by');
            $table->boolean('consultant_approval_required')->default(false)->after('junior_approved_at');
            $table->unsignedBigInteger('consultant_approved_by')->nullable()->after('consultant_approval_required');
            $table->dateTime('consultant_approved_at')->nullable()->after('consultant_approved_by');

            // FR-15: pending information / fast-track support
            $table->text('pending_info_reason')->nullable()->after('rejection_reason');

            $table->index(['is_life_threatening', 'is_immediate_ot']);
        });

        // FR-08: equipment requirements per request (qty + mandatory + setup instr)
        Schema::create('ot_request_equipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surgery_request_id');
            $table->unsignedBigInteger('ot_equipment_id')->nullable();
            $table->string('equipment_name');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->boolean('is_mandatory')->default(true);
            $table->text('setup_instruction')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('surgery_request_id')->references('id')->on('ot_surgery_requests')->onDelete('cascade');
            $table->foreign('ot_equipment_id')->references('id')->on('ot_equipments')->onDelete('set null');
            $table->index('surgery_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ot_request_equipments');

        Schema::table('ot_surgery_requests', function (Blueprint $table) {
            $table->dropIndex(['is_life_threatening', 'is_immediate_ot']);
            $table->dropColumn([
                'secondary_diagnosis', 'icd_code',
                'emergency_reason', 'is_life_threatening', 'is_immediate_ot',
                'date_flexibility', 'flexibility_reason',
                'required_ot_type',
                'blood_components', 'crossmatch_required', 'blood_bank_instruction',
                'junior_approval_required', 'junior_approved_by', 'junior_approved_at',
                'consultant_approval_required', 'consultant_approved_by', 'consultant_approved_at',
                'pending_info_reason',
            ]);
        });
    }
};
