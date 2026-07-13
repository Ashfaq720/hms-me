<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amb_clinical_notes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('trip_id')->constrained('amb_trips')->cascadeOnDelete();
            $t->foreignId('recorded_by')->nullable()->constrained('users'); // paramedic user
            // Vitals
            $t->string('bp')->nullable();           // e.g. 120/80
            $t->integer('pulse')->nullable();        // bpm
            $t->decimal('spo2', 5, 2)->nullable();   // e.g. 96.00
            $t->decimal('temperature', 5, 2)->nullable(); // °F
            $t->integer('respiratory_rate')->nullable();
            // Support
            $t->boolean('oxygen_given')->default(false);
            $t->boolean('ventilator_used')->default(false);
            // Intervention
            $t->enum('emergency_intervention', ['NONE', 'CPR', 'OXYGEN', 'IV_SUPPORT', 'DEFIBRILLATION', 'OTHER'])
              ->default('NONE');
            $t->text('clinical_notes')->nullable();
            $t->timestamp('recorded_at')->useCurrent();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amb_clinical_notes');
    }
};
