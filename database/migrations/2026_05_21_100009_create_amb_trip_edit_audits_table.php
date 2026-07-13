<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amb_trip_edit_audits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('trip_id')->constrained('amb_trips')->cascadeOnDelete();
            $t->string('edited_field');   // e.g. pickup_location, priority, case_tag
            $t->text('old_value')->nullable();
            $t->text('new_value')->nullable();
            $t->string('edit_reason')->nullable();
            $t->foreignId('edited_by')->nullable()->constrained('users');
            $t->timestamp('edited_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amb_trip_edit_audits');
    }
};
