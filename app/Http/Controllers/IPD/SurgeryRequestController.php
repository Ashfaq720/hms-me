<?php
namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\IpdPatient;
use App\Models\Ot\OtSurgeryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SurgeryRequestController extends Controller
{
    public function create($id)
    {
        $ipdPatient = IpdPatient::with('patient')->findOrFail($id);
        return view('ipd_patients.surgery-request.create', compact('ipdPatient'));
    }

    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'requested_surgery_date' => 'nullable|date',
            'requested_surgery_time' => 'nullable|date_format:H:i',
            'priority'               => 'nullable',
            'clinical_indication'    => 'nullable|string',
        ]);

        try {
            $ipdPatient = IpdPatient::findOrFail($id);

            $priority    = $validated['priority'] ?? 'Normal';
            $isEmergency = in_array($priority, ['Emergency', 'STAT'], true);

            OtSurgeryRequest::create([
                'case_id'                => $ipdPatient->case_id,
                'patient_id'             => $ipdPatient->patient_id,
                'encounter_type'         => 'IPD',
                'ipd_admission_id'       => $ipdPatient->id,
                'requested_by_doctor_id' => $ipdPatient->doctor_id,
                'department_id'          => $ipdPatient->department_id,
                'requested_surgery_date' => $validated['requested_surgery_date'] ?? null,
                'requested_surgery_time' => $validated['requested_surgery_time'] ?? null,
                'priority'               => $priority,
                'blood_group'            => $ipdPatient->patient?->blood_group ?? null,
                'is_emergency'           => $isEmergency,
                'clinical_indication'    => $validated['clinical_indication'] ?? null,
                'status'                 => OtSurgeryRequest::STATUS_DRAFT,
                'created_by'             => auth()->id(),
            ]);

            return redirect(route('ipd-patients.ipd-patients.show', $id) . '?tab=surgery-request')
                ->with('success', 'Surgery request created. OT Management will complete the remaining details.');
        } catch (\Throwable $e) {
            Log::error('Surgery request store failed', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()
                ->with('error', 'Failed to create surgery request: ' . $e->getMessage());
        }
    }
}
