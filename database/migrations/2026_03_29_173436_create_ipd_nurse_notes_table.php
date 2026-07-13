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
        Schema::create('ipd_nurse_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ipd_patient_id');
            $table->dateTime('date');
            $table->string('nurse_name');
            $table->longText('note');
            $table->longText('observations')->nullable();
            $table->timestamps();
            $table->foreign('ipd_patient_id')->references('id')->on('i_p_d_patients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipd_nurse_notes');
    }
};
