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
        Schema::create('amb_paramedics', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('nid')->unique();
            $t->string('phone')->nullable();
            $t->enum('certification', ['BLS', 'ACLS']);
            $t->date('cert_expiry')->nullable();
            $t->enum('status', ['ACTIVE', 'SUSPENDED'])->default('ACTIVE');
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amb_paramedics');
    }
};
