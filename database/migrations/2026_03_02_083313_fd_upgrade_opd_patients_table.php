<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('opd_patients', function (Blueprint $t) {
            if (!Schema::hasColumn('opd_patients', 'case_id')) {
                $t->foreignId('case_id')->nullable()->constrained('case_references')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('opd_patients', 'patient_id')) {
                $t->foreignId('patient_id')->nullable()->constrained('patients')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('opd_patients', 'doctor_id')) {
                $t->foreignId('doctor_id')->nullable()->constrained('doctors')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('opd_patients', 'department_id')) {
                $t->foreignId('department_id')->nullable()->constrained('departments')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('opd_patients', 'visit_date')) {
                $t->dateTime('visit_date')->nullable()->index();
            }
            if (!Schema::hasColumn('opd_patients', 'serial_no')) {
                $t->string('serial_no', 30)->nullable()->index(); // OPD-SERIAL-...
            }
            if (!Schema::hasColumn('opd_patients', 'remarks')) {
                $t->string('remarks')->nullable();
            }
            if (!Schema::hasColumn('opd_patients', 'status')) {
                $t->string('status')->default('Registered')->index();
            }
        });
    }

    public function down(): void {}
};
