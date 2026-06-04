<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuAlert;
use App\Models\Icu\IcuAntibioticUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class IcuAntibioticController extends Controller
{
    /**
     * BRD §8 antibiotic stewardship rules: any active antibiotic running >7 days
     * gets a one-time review alert.
     */
    public const LONG_USE_DAYS = 7;

    public function index($admissionId)
    {
        $admission = IcuAdmission::findOrFail($admissionId);

        $logs = IcuAntibioticUsageLog::where('icu_admission_id', $admission->id)
            ->orderByDesc('id')->get();

        // Check stewardship for any active antibiotic (one-time alert per row)
        $this->raiseLongUseAlerts($admission, $logs);

        return view('icu.antibiotics.index', compact('admission', 'logs'));
    }

    public function store(Request $request, $admissionId)
    {
        $request->validate([
            'antibiotic_name'   => ['required', 'string', 'max:150'],
            'dose'              => ['nullable', 'string', 'max:50'],
            'route'             => ['nullable', 'string', 'max:30'],
            'frequency'         => ['nullable', 'string', 'max:50'],
            'start_date'        => ['required', 'date'],
            'stop_date'         => ['nullable', 'date', 'after_or_equal:start_date'],
            'indication'        => ['nullable', 'string', 'max:255'],
            'culture_report_id' => ['nullable', 'integer'],
            'is_restricted'     => ['nullable', 'boolean'],
            'doctor_order_id'   => ['nullable', 'integer', 'exists:icu_doctor_orders,id'],
            'remarks'           => ['nullable', 'string', 'max:1000'],
        ]);

        $admission = IcuAdmission::findOrFail($admissionId);

        // Duplicate check (BRD §8): same antibiotic still Active for this admission
        $duplicate = IcuAntibioticUsageLog::where('icu_admission_id', $admission->id)
            ->where('antibiotic_name', $request->antibiotic_name)
            ->where('status', 'Active')
            ->exists();

        $log = IcuAntibioticUsageLog::create([
            'icu_admission_id'  => $admission->id,
            'icu_case_id'       => $admission->icu_case_id,
            'patient_id'        => $admission->patient_id,
            'doctor_order_id'   => $request->doctor_order_id,
            'antibiotic_name'   => $request->antibiotic_name,
            'dose'              => $request->dose,
            'route'             => $request->route,
            'frequency'         => $request->frequency,
            'start_date'        => $request->start_date,
            'stop_date'         => $request->stop_date,
            'indication'        => $request->indication,
            'culture_report_id' => $request->culture_report_id,
            'is_restricted'     => (bool) $request->is_restricted,
            'prescribed_by'     => auth()->id(),
            'status'            => $request->stop_date ? 'Stopped' : 'Active',
            'remarks'           => $request->remarks,
        ]);

        if ($duplicate) {
            IcuAlert::create([
                'icu_admission_id' => $admission->id,
                'icu_case_id'      => $admission->icu_case_id,
                'patient_id'       => $admission->patient_id,
                'alert_type'       => 'Other',
                'severity'         => 'Warning',
                'message'          => "Duplicate active antibiotic: {$log->antibiotic_name}.",
                'source_module'    => 'icu_antibiotic_usage_logs',
                'source_id'        => $log->id,
                'status'           => 'Active',
            ]);
        }

        return back()->with('success', 'Antibiotic recorded.');
    }

    public function stop(Request $request, $admissionId, $logId)
    {
        $request->validate([
            'stop_date' => ['nullable', 'date'],
            'remarks'   => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $admissionId, $logId) {
            $log = IcuAntibioticUsageLog::lockForUpdate()->findOrFail($logId);
            if ($log->icu_admission_id != $admissionId) {
                throw new \RuntimeException('Antibiotic log does not belong to this admission.');
            }

            $log->update([
                'stop_date' => $request->stop_date ?: now()->toDateString(),
                'status'    => 'Stopped',
                'remarks'   => trim(($log->remarks ?? '') . ($request->remarks ? "\n[Stopped] " . $request->remarks : '')),
            ]);
        });

        return back()->with('success', 'Antibiotic stopped.');
    }

    /**
     * Raise long-use alerts (one-time per log) for any Active antibiotic > LONG_USE_DAYS days.
     */
    protected function raiseLongUseAlerts(IcuAdmission $admission, $logs): void
    {
        foreach ($logs as $log) {
            if ($log->status !== 'Active' || $log->long_use_alerted_at) {
                continue;
            }
            if ($log->durationDays() > self::LONG_USE_DAYS) {
                IcuAlert::create([
                    'icu_admission_id' => $admission->id,
                    'icu_case_id'      => $admission->icu_case_id,
                    'patient_id'       => $admission->patient_id,
                    'alert_type'       => 'Other',
                    'severity'         => 'Warning',
                    'message'          => sprintf(
                        '%s active for %d days — stewardship review.',
                        $log->antibiotic_name,
                        $log->durationDays()
                    ),
                    'source_module'    => 'icu_antibiotic_usage_logs',
                    'source_id'        => $log->id,
                    'status'           => 'Active',
                ]);
                $log->update(['long_use_alerted_at' => now()]);
            }
        }
    }
}
