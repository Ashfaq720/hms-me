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
        Schema::create('blood_groups', function (Blueprint $t) {
            $t->id();

            $t->string('code', 30)->unique();
            $t->enum('abo_group', ['A', 'B', 'AB', 'O']);
            $t->enum('rh_factor', ['POS', 'NEG']);
            $t->string('display_name', 60);

            $t->boolean('is_active')->default(true);

            // Non-negotiable: cannot edit once used
            $t->boolean('is_locked')->default(false);

            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();

            $t->unique(['abo_group', 'rh_factor']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blood_groups');
    }
};
