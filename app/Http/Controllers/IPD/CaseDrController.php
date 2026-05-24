<?php
namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\IpdPatient;
use App\Models\Ipd\IpdCaseDr;
use Illuminate\Http\Request;

class CaseDrController extends Controller
{
    public function create($id)
    {
        $doctors = Doctor::all();
        return view('ipd_patients.case-dr.create', compact('id', 'doctors'));
    }

    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'datetime'     => 'required|date',
            'doctor_id'    => 'nullable|exists:doctors,id',
            'shift'        => 'nullable|string|max:255',
            'note'         => 'nullable|string',
            'diagnosis'    => 'nullable|string',
            'order_to'     => 'nullable|in:Nurse,Round Dr',
            'observations' => 'nullable|string',
            'order'        => 'nullable|string',
            'priority'     => 'required|in:Normal,Urgent,Critical',
        ]);

        try {
            $validated['ipd_patient_id'] = $id;
            $validated['doctor_id']      = $validated['doctor_id'] ?? IpdPatient::where('id', $id)->value('doctor_id');
            IpdCaseDr::create($validated);

            // return redirect()->route('ipd-patients.ipd-patients.show', $id)
            //     ->with('success', 'Case doctor entry saved successfully.');

            return redirect(route('ipd-patients.ipd-patients.show', $id) . '?tab=casedr')
                ->with('success', 'Case doctor entry saved successfully.');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Failed to save case doctor entry: ' . $e->getMessage());
        }
    }
}
