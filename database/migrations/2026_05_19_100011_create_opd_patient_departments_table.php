
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lightweight OPD-department appointment log referenced by
 * OpdPatientDepartmentController. The original project shipped the
 * controller and Blade views but never created the migration / model.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('opd_patient_departments')) {
            return;
        }

        Schema::create('opd_patient_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable();
            $table->foreignId('department_id')->nullable();
            $table->date('appointment_date');
            $table->string('reason')->nullable();
            $table->string('status', 32)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['patient_id', 'appointment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opd_patient_departments');
    }
};
