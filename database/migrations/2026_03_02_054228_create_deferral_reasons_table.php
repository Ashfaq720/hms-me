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
        Schema::create('deferral_reasons', function (Blueprint $t) {
            $t->id();

            $t->string('deferral_code', 30)->unique();
            $t->string('deferral_reason', 200);

            $t->enum('deferral_type', ['TEMP', 'PERM']);
            $t->unsignedInteger('default_duration_days')->nullable(); // TEMP only

            $t->string('regulatory_reference', 200)->nullable();

            $t->boolean('is_active')->default(true);

            // once used: cannot delete/edit
            $t->boolean('is_locked')->default(false);

            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deferral_reasons');
    }
};
