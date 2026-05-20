<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_investigation_order', function (Blueprint $table) {
            $table->id();

            $table->string('order_number')->nullable()->unique();

            $table->foreignId('ipd_id')->nullable()
                ->constrained('i_p_d_patients')->nullOnDelete();

            $table->foreignId('opd_id')->nullable()
                ->constrained('opd_patients')->nullOnDelete();

            $table->foreignId('er_id')->nullable()
                ->constrained('er_patients')->nullOnDelete();

            $table->foreignId('appointment_id')->nullable()
                ->constrained('appointments')->nullOnDelete();

            $table->foreignId('case_id')->nullable()
                ->constrained('case_references')->nullOnDelete();

            $table->foreignId('patient_id')->nullable()
                ->constrained('patients')->nullOnDelete();

            $table->foreignId('doctor_id')->nullable()
                ->constrained('doctors')->nullOnDelete();

            $table->dateTime('datetime')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->text('remarks')->nullable();
            $table->string('collected_by')->nullable();
            $table->string('source')->nullable()->comment('sample: ipd, opd, er, appointment, walkin');
            $table->string('lab_name')->nullable();
            $table->string('type')->nullable()->comment('sample: pathology, radiology');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_investigation_order');
    }
};
