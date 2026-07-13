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
        Schema::create('amb_vendors', function (Blueprint $t) {
            $t->id();
            $t->string('vendor_code')->unique();
            $t->string('vendor_name');
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->integer('sla_response_minutes')->default(15);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amb_vendors');
    }
};
