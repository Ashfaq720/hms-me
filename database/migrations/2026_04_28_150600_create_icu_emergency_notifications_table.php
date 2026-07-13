<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_emergency_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('role', 50)->nullable();
            $table->string('notification_type', 30); // Dashboard / Sound / SMS / Push / Email
            $table->dateTime('sent_at');
            $table->dateTime('acknowledged_at')->nullable();
            $table->enum('status', ['Pending', 'Sent', 'Acknowledged', 'Failed'])->default('Sent');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_emergency_notifications');
    }
};
