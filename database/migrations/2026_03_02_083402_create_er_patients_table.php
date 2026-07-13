<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('er_patients', function (Blueprint $t) {
            $t->id();

            $t->foreignId('case_id')->constrained('case_references')->cascadeOnDelete();
            $t->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $t->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $t->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $t->dateTime('arrival_time');
            $t->enum('discount_type', ['CORPORATE', 'INSURANCE','STUFF','SELF'])->nullable();
            $t->string('description')->nullable();
            $t->integer('age')->nullable();
            $t->string('gender')->nullable();
            $t->string('blood_group')->nullable();
            $t->string('third_party_name')->nullable();
            $t->string('third_party_contact')->nullable();
            $t->string('relation')->nullable();
            $t->enum('priority', ['CRITICAL','HIGH','NORMAL'])->default('NORMAL')->index();
            $t->string('remarks')->nullable();
            $t->string('status')->default('ADMITTED')->index();

            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('er_patients');
    }
};
