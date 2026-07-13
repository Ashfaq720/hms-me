<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bed-type-wise price variants for a package. Optional — only used when a
 * package needs different pricing per bed type (e.g. C-Section Package
 * costs ৳55,000 in General Ward but ৳75,000 in a Cabin).
 *
 * If a package has no rows here, its base_price is the price used everywhere.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_package_bed_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_package_id')
                  ->constrained('service_packages')->cascadeOnDelete();
            $table->foreignId('bed_type_id')
                  ->constrained('bed_types')->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->timestamps();

            $table->unique(['service_package_id', 'bed_type_id'], 'svc_pkg_bed_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_package_bed_prices');
    }
};
