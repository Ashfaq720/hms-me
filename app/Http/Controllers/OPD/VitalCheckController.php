<?php

namespace App\Http\Controllers\OPD;

use App\Http\Controllers\Controller;
use App\Models\FrontDesk\VitalCheck;
use App\Models\OpdPatient;
use Illuminate\Http\Request;

class VitalCheckController extends Controller
{
    public function create($id)
    {
        $opdPatient = OpdPatient::with('patient')->findOrFail($id);

        return view('opd_patients.vital-check.create', compact('id', 'opdPatient'));
    }

    public function store(Request $request, $id)
    {
        $data = $request->validate([
            'weight'           => 'nullable|numeric|min:0',
            'height'           => 'nullable|numeric|min:0',
            'blood_pressure'   => 'nullable|string|max:20',
            'temperature'      => 'nullable|numeric|min:0',
            'heart_rate'       => 'nullable|integer|min:0',
            'respiratory_rate' => 'nullable|integer|min:0',
            'spo2'             => 'nullable|integer|min:0|max:100',
            'remarks'          => 'nullable|string|max:3000',
        ]);

        try {
            $opdPatient = OpdPatient::findOrFail($id);

            VitalCheck::create([
                'patient_id'       => $opdPatient->patient_id,
                'patient_type'     => 'OPD',
                'opd_patient_id'   => $opdPatient->id,
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

            return back()->with('success', 'Vital check saved successfully.');

            // return redirect()->route('ipd-patients.ipd-patients.show', $id)
            //     ->with('success', 'Vital check saved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to save vital check: ' . $e->getMessage());
        }
    }
}
