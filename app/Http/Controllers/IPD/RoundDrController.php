<?php
namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Ipd\IpdRoundDr;
use Illuminate\Http\Request;

class RoundDrController extends Controller
{
    public function create($id)
    {
        $doctors = Doctor::select('id', 'name')->orderBy('name')->get();
        return view('ipd_patients.round-dr.create', compact('id', 'doctors'));
    }

    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'datetime'             => 'required|date',
            'shift'                => 'nullable|string|max:255',
            'doctor_id'            => 'nullable|exists:doctors,id',
            'visit_count'          => 'nullable|integer|min:0',
            'clinical_observation' => 'nullable|string',
            'notes'                => 'nullable|string',
        ]);

        try {
            $validated['ipd_patient_id'] = $id;
            $validated['visit_count']    = $validated['visit_count'] ?? 0;

            IpdRoundDr::create($validated);

            // return redirect()->route('ipd-patients.ipd-patients.show', $id)
            //     ->with('success', 'Round doctor entry saved successfully.');

            return redirect(route('ipd-patients.ipd-patients.show', $id) . '?tab=rounddr')
                ->with('success', 'Round doctor entry saved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to save round doctor entry: ' . $e->getMessage());
        }
    }
}
