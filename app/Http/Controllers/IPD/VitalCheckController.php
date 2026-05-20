<?php
namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\FrontDesk\VitalCheck;
use App\Models\IpdPatient;
use Illuminate\Http\Request;

class VitalCheckController extends Controller
{
    public function create($id)
    {
        $ipdPatient = IpdPatient::with('patient')->findOrFail($id);
        return view('ipd_patients.vital-check.create', compact('id', 'ipdPatient'));
    }

    public function store(Request $request, $id)
    {
        $data = $request->validate([
            'weight'           => 'nullable|numeric|min:0',
            'height'           => 'nullable|numeric|min:0',
            'blood_pressure'   => 'required|string|max:20',
            'temperature'      => 'required|numeric|min:0',
            'heart_rate'       => 'required|integer|min:0',
            'respiratory_rate' => 'required|integer|min:0',
            'spo2'             => 'required|integer|min:0|max:100',
            'remarks'          => 'nullable|string|max:3000',
        ]);

        try {
            $ipdPatient = IpdPatient::findOrFail($id);

            VitalCheck::create([
                'patient_id'       => $ipdPatient->patient_id,
                'patient_type'     => 'Ipd',
                'ipd_patient_id'   => $ipdPatient->id,
                'weight'           => $data['weight'] ?? null,
                'height'           => $data['height'] ?? null,
                'blood_pressure'   => $data['blood_pressure'] ?? null,
                'temperature'      => $data['temperature'] ?? null,
                'heart_rate'       => $data['heart_rate'] ?? null,
                'respiratory_rate' => $data['respiratory_rate'] ?? null,
                'spo2'             => $data['spo2'] ?? null,
                'remarks'          => $data['remarks'] ?? null,
                'checked_by'       => auth()->id(),
                'checked_at'       => now(),
            ]);


            return redirect(route('ipd-patients.ipd-patients.show', $id) . '?tab=vital-check')
                ->with('success', 'Vital check saved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to save vital check: ' . $e->getMessage());
        }
    }
}
