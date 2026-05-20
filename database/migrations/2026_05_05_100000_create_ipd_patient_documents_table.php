<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_patient_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipd_patient_id')->constrained('i_p_d_patients')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('file');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_patient_documents');
    }
};
