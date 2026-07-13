<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_histories', function (Blueprint $table) {
            $table->id();
            $table->string('case_id')->nullable();
            $table->unsignedBigInteger('opd_id')->nullable();
            $table->unsignedBigInteger('ipd_id')->nullable();
            $table->unsignedBigInteger('er_id')->nullable();
            $table->string('customer_type')->nullable();

            $table->foreignId('operation_type_id')->nullable()->constrained('operation_types')->nullOnDelete();
            $table->foreignId('operation_id')->nullable()->constrained('operations')->nullOnDelete();
            $table->foreignId('operation_procedure_id')->nullable()->constrained('operation_procedures')->nullOnDelete();
            $table->foreignId('operation_theatre_id')->nullable()->constrained('operation_theatres')->nullOnDelete();

            $table->date('date')->nullable();
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();

            $table->boolean('pre_op')->default(false);
            $table->boolean('vitals')->default(false);
            $table->boolean('consent')->default(false);
            $table->boolean('equipment')->default(false);

            $table->longText('diagnosis')->nullable();

            $table->foreignId('assign_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('assistant_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('main_surgeon_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('anesthesiologist_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('ot_technician')->nullable();

            $table->longText('remarks')->nullable();
            $table->string('status')->default('Scheduled');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_histories');
    }
};
