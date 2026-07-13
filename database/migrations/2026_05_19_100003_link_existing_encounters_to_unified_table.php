<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add encounter_id pointer + organization/branch scope to existing
 * encounter detail tables so they can be unified under the new encounters table.
 */
return new class extends Migration
{
    public function up(): void
    {
        $tables = ['opd_patients', 'i_p_d_patients', 'er_patients'];

        foreach ($tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'encounter_id')) {
                    $table->foreignId('encounter_id')->nullable()->after('id')
                        ->constrained('encounters')->nullOnDelete();
                }
                if (! Schema::hasColumn($tableName, 'organization_id')) {
                    $table->foreignId('organization_id')->nullable()->after('encounter_id')
                        ->constrained('organizations')->nullOnDelete();
                }
                if (! Schema::hasColumn($tableName, 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->after('organization_id')
                        ->constrained('branches')->nullOnDelete();
                }
            });
        }

        // Patient gets canonical organization/branch scope (replacing free-text fields).
        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                if (! Schema::hasColumn($table->getTable(), 'org_id')) {
                    $table->foreignId('org_id')->nullable()->after('id')
                        ->constrained('organizations')->nullOnDelete();
                }
                if (! Schema::hasColumn($table->getTable(), 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->after('org_id')
                        ->constrained('branches')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['opd_patients', 'i_p_d_patients', 'er_patients'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'encounter_id')) {
                    $table->dropConstrainedForeignId('encounter_id');
                }
                if (Schema::hasColumn($tableName, 'organization_id')) {
                    $table->dropConstrainedForeignId('organization_id');
                }
                if (Schema::hasColumn($tableName, 'branch_id')) {
                    $table->dropConstrainedForeignId('branch_id');
                }
            });
        }

        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                if (Schema::hasColumn('patients', 'branch_id')) {
                    $table->dropConstrainedForeignId('branch_id');
                }
                if (Schema::hasColumn('patients', 'org_id')) {
                    $table->dropConstrainedForeignId('org_id');
                }
            });
        }
    }
};
