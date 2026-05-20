<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_donors', function (Blueprint $t) {
            $t->id();

            $t->string('donor_code', 30)->unique();
            $t->string('name', 150);
            $t->date('dob');
            $t->foreignId('blood_group_id')->constrained('blood_groups')->restrictOnDelete();
            $t->enum('gender', ['MALE', 'FEMALE', 'OTHER']);
            $t->string('father_name', 150)->nullable();
            $t->string('contact_no', 20);
            $t->text('address')->nullable();

            $t->boolean('is_active')->default(true);

            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_donors');
    }
};
