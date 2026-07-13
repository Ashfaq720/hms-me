<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_round_drs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ipd_patient_id');
            $table->dateTime('datetime');
            $table->string('shift')->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->integer('visit_count')->default(0);
            $table->longText('clinical_observation')->nullable();
            $table->longText('notes')->nullable();
            $table->timestamps();

            $table->foreign('ipd_patient_id')->references('id')->on('i_p_d_patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_round_drs');
    }
};
