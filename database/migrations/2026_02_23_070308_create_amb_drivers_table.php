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
        Schema::create('amb_drivers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('nid')->unique();
            $t->string('phone')->nullable();
            $t->string('license_number')->nullable();
            $t->string('license_type')->nullable();
            $t->date('license_expiry')->nullable();
            $t->enum('status', ['ACTIVE', 'SUSPENDED'])->default('ACTIVE');
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amb_drivers');
    }
};
