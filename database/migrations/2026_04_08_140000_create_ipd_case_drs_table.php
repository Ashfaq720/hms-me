<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ipd_case_drs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ipd_patient_id');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->dateTime('datetime');
            $table->string('shift')->nullable();
            $table->longText('note')->nullable();
            $table->longText('diagnosis')->nullable();
            $table->string('order_to')->nullable();
            $table->longText('observations')->nullable();
            $table->longText('order')->nullable();
            $table->string('priority')->default('Normal');
            $table->timestamps();

            $table->foreign('ipd_patient_id')->references('id')->on('i_p_d_patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipd_case_drs');
    }
};
