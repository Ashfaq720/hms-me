<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Make package_services.service_id nullable so new packages can be saved
 * with only service_catalog_id (legacy services table is being retired).
 * Must match the existing column type (int unsigned, not bigint).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('package_services', 'service_id')) return;
        DB::statement('ALTER TABLE package_services MODIFY COLUMN service_id INT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (! Schema::hasColumn('package_services', 'service_id')) return;
        DB::statement('ALTER TABLE package_services MODIFY COLUMN service_id INT UNSIGNED NOT NULL');
    }
};
