<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ot_consumables', function (Blueprint $table) {
            $table->decimal('current_stock', 12, 2)->default(0)->after('rate');
            $table->decimal('reorder_level', 12, 2)->default(0)->after('current_stock');
            $table->unsignedBigInteger('linked_medicine_id')->nullable()->after('reorder_level');
            $table->string('store')->nullable()->after('linked_medicine_id');
        });

        Schema::create('ot_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ot_consumable_id');
            $table->unsignedBigInteger('surgery_schedule_id')->nullable();
            $table->unsignedBigInteger('consumable_usage_id')->nullable();
            $table->string('movement_type', 20);
            $table->decimal('quantity', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamps();

            $table->foreign('ot_consumable_id')->references('id')->on('ot_consumables')->onDelete('cascade');
            $table->foreign('surgery_schedule_id')->references('id')->on('ot_surgery_schedules')->onDelete('set null');
            $table->foreign('consumable_usage_id')->references('id')->on('ot_consumable_usages')->onDelete('set null');
            $table->index(['ot_consumable_id', 'created_at']);
        });

        Schema::create('ot_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('role')->nullable();
            $table->string('type', 60);
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('entity_type', 60)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('action_url')->nullable();
            $table->string('severity', 20)->default('info');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['role', 'read_at']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ot_notifications');
        Schema::dropIfExists('ot_stock_movements');
        Schema::table('ot_consumables', function (Blueprint $table) {
            $table->dropColumn(['current_stock', 'reorder_level', 'linked_medicine_id', 'store']);
        });
    }
};
