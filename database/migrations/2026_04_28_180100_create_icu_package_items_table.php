<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_package_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id')->index();

            // High-level category — matches the charge sources we already produce
            // (Bed, Equipment, Procedure, Medicine, Consumable, DoctorVisit, Nursing, Lab, Radiology)
            $table->enum('charge_category', [
                'Bed', 'Equipment', 'Procedure', 'Medicine', 'Consumable',
                'DoctorVisit', 'Nursing', 'Lab', 'Radiology', 'Other',
            ]);

            // Optional precise match — equipment_type for Equipment, procedure name, etc.
            // NULL means "all items in this category".
            $table->string('charge_code', 100)->nullable();
            $table->string('item_name', 200)->nullable();

            $table->enum('rule_type', ['Included', 'Excluded', 'Limited'])->default('Included');

            // For Limited rules
            $table->unsignedSmallInteger('included_qty')->nullable();
            $table->enum('limit_period', ['PerDay', 'PerStay'])->nullable();
            $table->boolean('extra_charge_allowed')->default(true);

            $table->timestamps();

            $table->index(['package_id', 'charge_category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_package_items');
    }
};
