<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuVitalLog;
use App\Services\Icu\VitalClassifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IcuVitalsController extends Controller
{
    public function __construct(private VitalClassifier $classifier) {}

    public function index($admissionId)
    {
        $admission = IcuAdmission::with('patient', 'bed')->findOrFail($admissionId);

        $logs = IcuVitalLog::where('icu_admission_id', $admission->id)
            ->orderByDesc('recorded_at')
            ->limit(200)
            ->get();

        $latest = $logs->first();

        return view('icu.vitals.index', compact('admission', 'logs', 'latest'));
    }

    public function store(Request $request, $admissionId)
    {
        $request->validate([
            'recorded_at'      => ['required', 'date'],
            'heart_rate'       => ['nullable', 'integer'],
            'systolic_bp'      => ['nullable', 'integer'],
            'diastolic_bp'     => ['nullable', 'integer'],
            'spo2'             => ['nullable', 'numeric'],
            'respiratory_rate' => ['nullable', 'integer'],
            'temperature'      => ['nullable', 'numeric'],
            'remarks'          => ['nullable', 'string', 'max:500'],
        ]);


        // At least one vital must be present
        if (! collect(['heart_rate', 'systolic_bp', 'diastolic_bp', 'spo2', 'respiratory_rate', 'temperature'])
            ->some(fn($k) => $request->filled($k))) {
            return back()->with('error', 'At least one vital reading must be entered.');
        }

        DB::beginTransaction();
        try {
            $admission = IcuAdmission::findOrFail($admissionId);

            $log = IcuVitalLog::create([
                'icu_admission_id' => $admission->id,
                'icu_case_id'      => $admission->icu_case_id,
                'patient_id'       => $admission->patient_id,
                'bed_id'           => $admission->bed_id,
                'heart_rate'       => $request->heart_rate,
                'systolic_bp'      => $request->systolic_bp,
                'diastolic_bp'     => $request->diastolic_bp,
                'spo2'             => $request->spo2,
                'respiratory_rate' => $request->respiratory_rate,
                'temperature'      => $request->temperature,
                'source_type'      => 'manual',
                'recorded_at'      => $request->recorded_at,
                'entered_by'       => auth()->id(),
                'remarks'          => $request->remarks,
            ]);

            // Classify and persist severity + alerts
            $result = $this->classifier->classifyLog($admission, $log);
            $log->update(['severity' => $result['worst']]);
            $this->classifier->generateAlerts($admission, $log, $result['per_vital']);

            DB::commit();

            return back()->with('success', "Vitals saved (severity: {$result['worst']}).");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ICU vitals store failed', ['message' => $e->getMessage()]);
            return back()->with('error', 'Save failed: ' . $e->getMessage());
        }
    }

    /**
     * Trend data endpoint (JSON) for charting.
     * Returns [{ recorded_at, hr, sbp, dbp, spo2, rr, temp, severity }]
     */
    public function trend(Request $request, $admissionId)
    {
        $hours = (int) $request->input('hours', 24);
        $hours = max(1, min(168, $hours));

        $logs = IcuVitalLog::where('icu_admission_id', $admissionId)
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->orderBy('recorded_at')
            ->get();

        return response()->json([
            'series' => $logs->map(fn($l) => [
                'recorded_at' => $l->recorded_at->toIso8601String(),
                'hr'          => $l->heart_rate,
                'sbp'         => $l->systolic_bp,
                'dbp'         => $l->diastolic_bp,
                'spo2'        => $l->spo2,
                'rr'          => $l->respiratory_rate,
                'temp'        => $l->temperature,
                'severity'    => $l->severity,
            ]),
        ]);
    }
}
