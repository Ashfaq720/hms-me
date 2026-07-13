<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bridge legacy package_services.service_id (→ services table) to the
 * service_catalogs engine. Adds a nullable service_catalog_id alongside
 * the existing service_id so BillingService::syncItemsFromPostings()
 * can match bill_items.service_catalog_id against active package
 * enrolments and flag is_package_included automatically.
 *
 * The legacy service_id column is kept for backwards compatibility with
 * the existing package builder UI.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('package_services')) return;
        if (Schema::hasColumn('package_services', 'service_catalog_id')) return;

        Schema::table('package_services', function (Blueprint $t) {
            $t->foreignId('service_catalog_id')
                ->nullable()
                ->after('service_id')
                ->constrained('service_catalogs')
                ->nullOnDelete();
            $t->index('service_catalog_id', 'pkg_svc_catalog_idx');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('package_services') && Schema::hasColumn('package_services', 'service_catalog_id')) {
            Schema::table('package_services', function (Blueprint $t) {
                $t->dropIndex('pkg_svc_catalog_idx');
                $t->dropConstrainedForeignId('service_catalog_id');
            });
        }
    }
};
