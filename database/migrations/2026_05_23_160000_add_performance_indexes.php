<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes recommended by the enterprise audit.
 * Each index is created defensively (skip if already exists) so the
 * migration can be re-run on environments that already have some.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->safeIndex('service_charge_postings', ['encounter_id', 'status'], 'idx_postings_encounter_status');
        $this->safeIndex('stock_movements', ['inventory_item_id', 'direction'], 'idx_movements_item_dir');
        $this->safeIndex('bills', ['patient_id', 'bill_date'], 'idx_bills_patient_date');
        $this->safeIndex('bills', ['bill_type', 'status'], 'idx_bills_type_status');
        $this->safeIndex('encounters', ['subject_type', 'subject_id'], 'idx_enc_subject');
        $this->safeIndex('encounters', ['patient_id', 'status'], 'idx_enc_patient_status');
        $this->safeIndex('opd_patients', ['date', 'status'], 'idx_opd_date_status');
        $this->safeIndex('i_p_d_patients', ['admission_date', 'status'], 'idx_ipd_date_status');
        $this->safeIndex('activity_log', ['created_at'], 'idx_act_log_created');
        $this->safeIndex('lab_investigation_order', ['patient_id', 'created_at'], 'idx_lab_patient_date');
    }

    public function down(): void
    {
        foreach ([
            'service_charge_postings' => 'idx_postings_encounter_status',
            'stock_movements'         => 'idx_movements_item_dir',
            'bills'                   => ['idx_bills_patient_date', 'idx_bills_type_status'],
            'encounters'              => ['idx_enc_subject', 'idx_enc_patient_status'],
            'opd_patients'            => 'idx_opd_date_status',
            'i_p_d_patients'          => 'idx_ipd_date_status',
            'activity_log'            => 'idx_act_log_created',
            'lab_investigation_order' => 'idx_lab_patient_date',
        ] as $tbl => $idx) {
            foreach ((array) $idx as $i) {
                try {
                    Schema::table($tbl, fn (Blueprint $t) => $t->dropIndex($i));
                } catch (\Throwable $e) {
                    // already gone
                }
            }
        }
    }

    private function safeIndex(string $table, array $columns, string $name): void
    {
        if (! Schema::hasTable($table)) return;

        $exists = DB::select(
            "SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1",
            [$table, $name]
        );
        if (! empty($exists)) return;

        Schema::table($table, function (Blueprint $t) use ($columns, $name) {
            $t->index($columns, $name);
        });
    }
};
