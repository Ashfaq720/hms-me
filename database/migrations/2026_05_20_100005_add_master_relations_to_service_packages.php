<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Wire service packages to existing OT masters that the spec calls out:
 *
 *   service_packages.surgery_type_id      → ot_surgery_types
 *   service_packages.surgery_category_id  → ot_surgery_categories
 *   service_package_items.master_type/id  → polymorphic-style link to
 *                                            any existing master record
 *                                            (Consumable / SurgeryType /
 *                                            Charge / Medicine / etc.)
 *
 * All columns are nullable so existing rows stay valid.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_packages')) {
            Schema::table('service_packages', function (Blueprint $table) {
                if (! Schema::hasColumn('service_packages', 'surgery_type_id')) {
                    $table->foreignId('surgery_type_id')->nullable()->after('bed_type_id')
                          ->constrained('ot_surgery_types')->nullOnDelete();
                }
                if (! Schema::hasColumn('service_packages', 'surgery_category_id')) {
                    $table->foreignId('surgery_category_id')->nullable()->after('surgery_type_id')
                          ->constrained('ot_surgery_categories')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('service_package_items')) {
            Schema::table('service_package_items', function (Blueprint $table) {
                if (! Schema::hasColumn('service_package_items', 'master_type')) {
                    // Short slug — 'surgery_type', 'consumable', 'charge', 'medicine', 'equipment'
                    $table->string('master_type', 30)->nullable()->after('item_category');
                }
                if (! Schema::hasColumn('service_package_items', 'master_id')) {
                    $table->unsignedBigInteger('master_id')->nullable()->after('master_type');
                    $table->index(['master_type', 'master_id'], 'svc_pkg_items_master_idx');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_packages')) {
            Schema::table('service_packages', function (Blueprint $table) {
                if (Schema::hasColumn('service_packages', 'surgery_category_id')) {
                    $table->dropForeign(['surgery_category_id']);
                    $table->dropColumn('surgery_category_id');
                }
                if (Schema::hasColumn('service_packages', 'surgery_type_id')) {
                    $table->dropForeign(['surgery_type_id']);
                    $table->dropColumn('surgery_type_id');
                }
            });
        }

        if (Schema::hasTable('service_package_items')) {
            Schema::table('service_package_items', function (Blueprint $table) {
                if (Schema::hasColumn('service_package_items', 'master_id')) {
                    $table->dropIndex('svc_pkg_items_master_idx');
                    $table->dropColumn('master_id');
                }
                if (Schema::hasColumn('service_package_items', 'master_type')) {
                    $table->dropColumn('master_type');
                }
            });
        }
    }
};
