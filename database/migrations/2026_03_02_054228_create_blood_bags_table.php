<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_bags', function (Blueprint $t) {
            $t->id();

            $t->string('bag_code', 30)->unique();
            $t->enum('bag_type', ['SINGLE', 'DOUBLE', 'TRIPLE']);
            $t->unsignedInteger('volume_ml');

            $t->boolean('is_active')->default(true);

            // cannot change after collection
            $t->boolean('is_locked')->default(false);

            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();
        });

        Schema::create('blood_bag_components', function (Blueprint $t) {
            $t->id();

            $t->foreignId('blood_bag_id')
                ->constrained('blood_bags')
                ->cascadeOnDelete();

            $t->foreignId('component_id')
                ->constrained('components')
                ->restrictOnDelete();

            $t->unique(['blood_bag_id', 'component_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_bag_components');
        Schema::dropIfExists('blood_bags');
    }
};
