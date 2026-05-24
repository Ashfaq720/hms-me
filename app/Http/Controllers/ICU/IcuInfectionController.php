<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuAlert;
use App\Models\Icu\IcuInfectionRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class IcuInfectionController extends Controller
{
    public function index($admissionId)
    {
        $admission = IcuAdmission::with('patient', 'bed.bedType')->findOrFail($admissionId);

        $records = IcuInfectionRecord::where('icu_admission_id', $admission->id)
            ->orderByDesc('id')->get();

        return view('icu.infection.index', compact('admission', 'records'));
    }

    public function store(Request $request, $admissionId)
    {
        $request->validate([
            'infection_status'  => ['required', Rule::in(['Suspected', 'Confirmed', 'RuledOut', 'Resolved'])],
            'infection_name'    => ['nullable', 'string', 'max:150'],
            'organism'          => ['nullable', 'string', 'max:150'],
            'isolation_type'    => ['required', Rule::in(['Airborne', 'Contact', 'Droplet', 'Standard', 'None'])],
            'suspected_source'  => ['required', Rule::in([
                'CommunityAcquired', 'HospitalAcquired', 'IcuAcquired',
                'PostSurgical', 'DeviceAssociated', 'Unknown',
            ])],
            'first_detected_at' => ['nullable', 'date'],
            'lab_report_id'     => ['nullable', 'integer'],
            'remarks'           => ['nullable', 'string', 'max:1000'],
        ]);

        // BRD §13: Confirmed status should reference a lab report
        if ($request->infection_status === 'Confirmed' && empty($request->lab_report_id)) {
            return back()->with('error', 'Confirmed infections require a Lab Report reference.')->withInput();
        }

        DB::beginTransaction();
        try {
            $admission = IcuAdmission::findOrFail($admissionId);

            $rec = IcuInfectionRecord::create([
                'icu_admission_id'  => $admission->id,
                'icu_case_id'       => $admission->icu_case_id,
                'patient_id'        => $admission->patient_id,
                'infection_status'  => $request->infection_status,
                'infection_name'    => $request->infection_name,
                'organism'          => $request->organism,
                'isolation_type'    => $request->isolation_type,
                'suspected_source'  => $request->suspected_source,
                'first_detected_at' => $request->first_detected_at,
                'confirmed_at'      => $request->infection_status === 'Confirmed' ? now() : null,
                'confirmed_by'      => $request->infection_status === 'Confirmed' ? auth()->id() : null,
                'lab_report_id'     => $request->lab_report_id,
                'remarks'           => $request->remarks,
                'tagged_by'         => auth()->id(),
                'tagged_at'         => now(),
                'is_active'         => true,
            ]);

            // Mirror the latest active isolation type onto the admission so bed checks see it
            if ($rec->isolation_type !== 'None') {
                $admission->update(['isolation_type' => $rec->isolation_type]);
            }

            // If patient is currently in a non-isolation bed and isolation is now required,
            // raise an Active alert so the dashboard surfaces the mismatch.
            $needsIsolation = $rec->isolation_type !== 'None'
                && $rec->infection_status !== 'RuledOut'
                && $rec->infection_status !== 'Resolved';

            $bedType = $admission->bed?->bedType;
            $bedAllowed = $bedType?->is_isolation_bed
                && (! $bedType->allowed_isolation_type || $bedType->allowed_isolation_type === $rec->isolation_type);

            if ($needsIsolation && ! $bedAllowed && $admission->bed_id) {
                IcuAlert::create([
                    'icu_admission_id' => $admission->id,
                    'icu_case_id'      => $admission->icu_case_id,
                    'patient_id'       => $admission->patient_id,
                    'bed_id'           => $admission->bed_id,
                    'alert_type'       => 'IsolationRequired',
                    'severity'         => 'Critical',
                    'message'          => sprintf(
                        'Patient requires %s isolation but bed %s is not an isolation bed.',
                        $rec->isolation_type,
                        $admission->bed?->name ?? '#' . $admission->bed_id
                    ),
                    'source_module'    => 'icu_infection_records',
                    'source_id'        => $rec->id,
                    'status'           => 'Active',
                ]);
            }

            DB::commit();
            return back()->with('success', 'Infection record saved.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Save failed: ' . $e->getMessage());
        }
    }

    public function resolve(Request $request, $admissionId, $recordId)
    {
        $request->validate([
            'resolution_remarks' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $admissionId, $recordId) {
            $rec = IcuInfectionRecord::lockForUpdate()->findOrFail($recordId);
            if ($rec->icu_admission_id != $admissionId) {
                throw new \RuntimeException('Record does not belong to this admission.');
            }

            $rec->update([
                'infection_status' => 'Resolved',
                'is_active'        => false,
                'remarks'          => trim(($rec->remarks ?? '') . "\n[Resolved] " . $request->resolution_remarks),
            ]);

            // If no other active infection requires isolation, clear admission flag
            $admission = $rec->admission;
            $stillIsolating = IcuInfectionRecord::where('icu_admission_id', $admissionId)
                ->where('is_active', true)
                ->where('isolation_type', '!=', 'None')
                ->exists();

            if (! $stillIsolating && $admission) {
                $admission->update(['isolation_type' => 'None']);
            }

            // Close any related "IsolationRequired" alerts
            IcuAlert::where('source_module', 'icu_infection_records')
                ->where('source_id', $rec->id)
                ->whereIn('status', ['Active', 'Acknowledged'])
                ->update([
                    'status'       => 'Closed',
                    'closed_by'    => auth()->id(),
                    'closed_at'    => now(),
                    'action_taken' => 'Infection resolved: ' . $request->resolution_remarks,
                ]);
        });

        return back()->with('success', 'Infection marked resolved.');
    }
}
