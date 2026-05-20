<?php

namespace App\Services\FontDesk;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CaseReferenceService
{
    public function createCase(int $patientId, string $type): int
    {
        $table = 'case_references';
        $caseNo = strtoupper($type) . '-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));

        $payload = [];
        if (Schema::hasColumn($table, 'case_no')) $payload['case_no'] = $caseNo;
        if (Schema::hasColumn($table, 'case_number')) $payload['case_number'] = $caseNo;
        if (Schema::hasColumn($table, 'patient_id')) $payload['patient_id'] = $patientId;
        if (Schema::hasColumn($table, 'case_type')) $payload['case_type'] = strtoupper($type);
        if (Schema::hasColumn($table, 'type')) $payload['type'] = strtoupper($type);
        if (Schema::hasColumn($table, 'created_by')) $payload['created_by'] = auth()->id();

        if (Schema::hasColumn($table, 'created_at')) $payload['created_at'] = now();
        if (Schema::hasColumn($table, 'updated_at')) $payload['updated_at'] = now();

        return (int) DB::table($table)->insertGetId($payload);
    }
}
