<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fd_vital_checks', function (Blueprint $t) {
            $t->id();

            $t->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $t->enum('patient_type', ['OPD', 'Ipd', 'ER'])->index();

            $t->unsignedBigInteger('opd_patient_id')->nullable()->index();
            $t->unsignedBigInteger('ipd_patient_id')->nullable()->index();
            $t->unsignedBigInteger('er_patient_id')->nullable()->index();

            $t->string('patient_token', 30)->nullable()->index();
            $t->string('patient_name', 255)->nullable(); // UI filled (optional)
            $t->string('gender', 20)->nullable();
            $t->unsignedInteger('age')->nullable();

            $t->decimal('weight', 8, 2)->nullable();
            $t->decimal('height', 8, 2)->nullable();
            $t->string('blood_pressure', 20)->nullable();
            $t->decimal('temperature', 5, 2)->nullable();
            $t->unsignedInteger('heart_rate')->nullable();
            $t->unsignedInteger('respiratory_rate')->nullable();
            $t->unsignedInteger('spo2')->nullable();

            $t->text('remarks')->nullable();

            $t->unsignedBigInteger('checked_by')->nullable()->index();
            $t->timestamp('checked_at')->useCurrent()->index();

            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fd_vital_checks');
    }
};
