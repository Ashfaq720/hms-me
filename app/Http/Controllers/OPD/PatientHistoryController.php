<?php

namespace App\Http\Controllers\OPD;

use App\Http\Controllers\Controller;
use App\Models\OpdPatient;
use App\Models\PatientHistory;
use Illuminate\Http\Request;

class PatientHistoryController extends Controller
{
    public function store(Request $request, OpdPatient $opdPatient)
    {
        $validated = $request->validate([
            'history_type' => 'required|in:medical,surgical,family,allergy',
            'description'  => 'required|string|max:2000',
        ]);

        PatientHistory::create([
            'patient_id'   => $opdPatient->patient_id,
            'history_type' => $validated['history_type'],
            'description'  => $validated['description'],
            'recorded_by'  => auth()->id(),
        ]);

        return redirect(route('opd-patients.show', $opdPatient->id) . '?tab=history')
            ->with('success', 'Patient history recorded.');
    }

    public function update(Request $request, OpdPatient $opdPatient, PatientHistory $history)
    {
        $validated = $request->validate([
            'history_type' => 'required|in:medical,surgical,family,allergy',
            'description'  => 'required|string|max:2000',
        ]);

        $history->update($validated);

        return redirect(route('opd-patients.show', $opdPatient->id) . '?tab=history')
            ->with('success', 'Patient history updated.');
    }

    public function destroy(OpdPatient $opdPatient, PatientHistory $history)
    {
        $history->delete();

        return redirect(route('opd-patients.show', $opdPatient->id) . '?tab=history')
            ->with('success', 'History entry removed.');
    }
}
