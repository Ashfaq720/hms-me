<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Line items of a service package — what's INCLUDED (the breakdown that
 * front desk + billing + patient see). The package's base_price is the
 * bundled price; items don't sum to it. They exist for documentation,
 * utilization tracking, and audit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_package_id')
                  ->constrained('service_packages')->cascadeOnDelete();

            $table->enum('item_category', [
                'Bed', 'Doctor Visit', 'Nursing', 'OT',
                'Investigation', 'Medicine', 'Consumable',
                'Equipment', 'Procedure', 'Other',
            ]);

            $table->string('item_name', 200);
            $table->decimal('included_qty', 10, 2)->default(1);
            $table->string('unit', 30)->nullable();          // 'days', 'visits', 'pcs', etc.

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('service_package_id', 'svc_pkg_items_pkg_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_package_items');
    }
};
