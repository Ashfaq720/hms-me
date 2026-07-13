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
        Schema::create('notifications_logs', function (Blueprint $t) {
            $t->id();
            $t->string('channel', 20); // IN_APP/SMS/WHATSAPP/EMAIL
            $t->string('template_key')->nullable();
            $t->string('to_address')->nullable(); // phone/email/user ref
            $t->text('message');

            $t->string('reference_type', 20); // APPOINTMENT/TOKEN/VISIT/Ipd_ADMISSION/PASS
            $t->unsignedBigInteger('reference_id');

            $t->string('status', 20)->default('QUEUED'); // QUEUED/SENT/FAILED/DELIVERED(optional)
            $t->text('provider_response')->nullable();
            $t->dateTime('sent_at')->nullable();

            $t->timestamps();

            $t->index(['reference_type', 'reference_id']);
            $t->index(['status', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications_logs');
    }
};
