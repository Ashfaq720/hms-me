<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amb_trip_status_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('trip_id')->constrained('amb_trips')->cascadeOnDelete();
            $t->string('from_status')->nullable();
            $t->string('to_status');
            $t->string('delay_reason')->nullable();
            $t->foreignId('changed_by')->nullable()->constrained('users');
            $t->timestamp('changed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amb_trip_status_logs');
    }
};
