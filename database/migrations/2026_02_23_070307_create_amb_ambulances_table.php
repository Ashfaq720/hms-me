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
        Schema::create('amb_ambulances', function (Blueprint $t) {
            $t->id();
            $t->string('reg_no')->unique();
            $t->enum('type', ['BLS', 'EMERGENCY', 'ALS', 'ICU', 'NEONATAL']);
            $t->enum('ownership', ['HOSPITAL', 'OUTSOURCED'])->default('HOSPITAL');
            $t->foreignId('vendor_id')->nullable()->constrained('amb_vendors');
            $t->unsignedTinyInteger('stretcher_capacity')->default(1);
            $t->unsignedTinyInteger('attendants_capacity')->default(1);
            $t->string('oxygen_capacity')->nullable();
            $t->enum('status', ['AVAILABLE', 'ON_TRIP', 'MAINTENANCE'])->default('AVAILABLE');
            $t->date('fitness_expiry')->nullable();
            $t->date('insurance_expiry')->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amb_ambulances');
    }
};
