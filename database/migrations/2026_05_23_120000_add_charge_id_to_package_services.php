<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_services', function (Blueprint $t) {
            if (! Schema::hasColumn('package_services', 'charge_id')) {
                $t->unsignedBigInteger('charge_id')->nullable()->after('service_catalog_id');
                $t->index('charge_id');
            }
        });

        // Backfill: map service_catalog_id -> charges.id via the CHG-NNNN code pattern
        $rows = DB::table('package_services as ps')
            ->join('service_catalogs as sc', 'sc.id', '=', 'ps.service_catalog_id')
            ->whereNotNull('ps.service_catalog_id')
            ->where('sc.code', 'like', 'CHG-%')
            ->select('ps.id', 'sc.code')
            ->get();

        foreach ($rows as $row) {
            $chargeId = (int) ltrim(substr($row->code, 4), '0');
            if ($chargeId > 0 && DB::table('charges')->where('id', $chargeId)->exists()) {
                DB::table('package_services')->where('id', $row->id)->update(['charge_id' => $chargeId]);
            }
        }

        // Add FK after backfill
        try {
            Schema::table('package_services', function (Blueprint $t) {
                $t->foreign('charge_id')->references('id')->on('charges')->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // FK may already exist
        }
    }

    public function down(): void
    {
        Schema::table('package_services', function (Blueprint $t) {
            try { $t->dropForeign(['charge_id']); } catch (\Throwable $e) {}
            if (Schema::hasColumn('package_services', 'charge_id')) {
                $t->dropColumn('charge_id');
            }
        });
    }
};
