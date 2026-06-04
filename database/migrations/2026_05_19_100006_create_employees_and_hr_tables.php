<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * HR + Employee foundation (SRS §5.23, §9).
 *
 * Every hospital staff member (doctor, nurse, pharmacist, receptionist,
 * accountant, technician, admin, housekeeping...) is a unified Employee
 * record linked optionally to a User account for system access. Roles +
 * permissions live in Spatie tables, but department/branch/shift context
 * lives here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();

            $table->string('employee_code', 32)->unique();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('national_id')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('photo_path')->nullable();

            $table->foreignId('department_id')->nullable();
            $table->foreignId('designation_id')->nullable();

            // Functional staff_type used by RBAC + workflow routing.
            $table->enum('staff_type', [
                'doctor', 'nurse', 'pharmacist', 'lab_technician', 'radiologist',
                'radiology_technician', 'receptionist', 'cashier', 'accountant',
                'inventory_manager', 'hr_manager', 'admin', 'security', 'housekeeping',
                'paramedic', 'ambulance_driver', 'biomedical', 'it_support', 'other',
            ]);

            $table->date('joining_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'visiting', 'contract', 'intern'])->default('full_time');
            $table->enum('status', ['active', 'on_leave', 'suspended', 'resigned', 'terminated'])->default('active');

            $table->decimal('base_salary', 14, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->json('extra')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'staff_type', 'status']);
        });

        Schema::create('employee_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('degree');
            $table->string('institution')->nullable();
            $table->year('year')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->boolean('is_night_shift')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_roster', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('employee_shifts')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['scheduled', 'completed', 'absent', 'swapped'])->default('scheduled');
            $table->string('ward_or_department')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'shift_id', 'date']);
            $table->index(['date', 'shift_id']);
        });

        Schema::create('employee_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'leave', 'holiday'])->default('present');
            $table->string('source', 32)->default('manual'); // manual, biometric, mobile, sso
            $table->string('remarks')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'date']);
        });

        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16);
            $table->string('name');
            $table->unsignedInteger('annual_quota')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique('code');
        });

        Schema::create('employee_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days', 6, 2);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'status']);
        });

        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('period_label', 32); // 2026-05
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('status', ['draft', 'computed', 'approved', 'paid', 'cancelled'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->unique(['branch_id', 'period_label']);
        });

        Schema::create('payroll_run_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees');
            $table->decimal('base_salary', 14, 2)->default(0);
            $table->decimal('allowances_total', 14, 2)->default(0);
            $table->decimal('deductions_total', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('commission_total', 14, 2)->default(0);
            $table->decimal('net_pay', 14, 2)->default(0);
            $table->json('breakdown')->nullable();
            $table->enum('status', ['draft', 'computed', 'paid', 'cancelled'])->default('draft');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('doctor_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('encounter_id')->nullable()->constrained('encounters')->nullOnDelete();
            $table->string('source', 32); // consultation, procedure, ot, package, manual
            $table->decimal('gross_amount', 14, 2)->default(0);
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->decimal('commission_amount', 14, 2)->default(0);
            $table->enum('status', ['accrued', 'paid', 'cancelled'])->default('accrued');
            $table->date('accrued_on');
            $table->foreignId('payroll_run_id')->nullable()->constrained('payroll_runs')->nullOnDelete();
            $table->string('reference_no', 64)->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_wallet_transactions');
        Schema::dropIfExists('payroll_run_lines');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('employee_leaves');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('employee_attendance');
        Schema::dropIfExists('employee_roster');
        Schema::dropIfExists('employee_shifts');
        Schema::dropIfExists('employee_qualifications');
        Schema::dropIfExists('employees');
    }
};
