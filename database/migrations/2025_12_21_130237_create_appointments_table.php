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
         Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->integer('patient_id')->nullable()->index();
            $table->integer('case_reference_id')->nullable()->index();
            $table->integer('visit_details_id')->nullable();
            $table->dateTime('date')->nullable();
            $table->time('time')->nullable();
            $table->string('priority', 100);
            $table->string('specialist', 100);
            $table->integer('doctor')->nullable()->index();
            $table->string('amount', 200);
            $table->text('message')->nullable();
            $table->string('appointment_status', 11)->nullable();
            $table->string('source', 100);
            $table->string('is_opd', 10);
            $table->string('is_ipd', 10);
            $table->integer('global_shift_id')->nullable();
            $table->integer('shift_id')->nullable();
            $table->integer('is_queue')->nullable();
            $table->string('live_consult', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
