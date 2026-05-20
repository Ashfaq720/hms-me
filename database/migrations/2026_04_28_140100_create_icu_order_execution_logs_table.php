<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_order_execution_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->string('status', 30); // matches icu_doctor_orders.status
            $table->unsignedBigInteger('executed_by')->nullable();
            $table->dateTime('execution_start_time')->nullable();
            $table->dateTime('execution_end_time')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_order_execution_logs');
    }
};
