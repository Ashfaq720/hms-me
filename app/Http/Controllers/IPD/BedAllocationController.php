<?php
namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\IpdPatient;
use App\Models\IpdPatientBed;
use Illuminate\Http\Request;

class BedAllocationController extends Controller
{
    public function show($ipdPatientId, $allocationId)
    {
        $ipdPatient = IpdPatient::with('patient')->findOrFail($ipdPatientId);
        $allocation = IpdPatientBed::with('bed.bedType', 'bed.bedGroup')
            ->where('ipd_patient_id', $ipdPatientId)
            ->findOrFail($allocationId);

        return view('ipd_patients.bed-allocations.show', compact('ipdPatient', 'allocation'));
    }

    public function edit($ipdPatientId, $allocationId)
    {
        $ipdPatient = IpdPatient::with('patient')->findOrFail($ipdPatientId);
        $allocation = IpdPatientBed::where('ipd_patient_id', $ipdPatientId)->findOrFail($allocationId);

        $beds = Bed::select('id', 'name', 'rent')
            ->where(function ($query) use ($allocation) {
                $query->where('is_reserved', false)
                    ->orWhere('id', $allocation->bed_id);
            })
            ->get();

        return view('ipd_patients.bed-allocations.edit', compact('ipdPatient', 'allocation', 'beds'));
    }

    public function update(Request $request, $ipdPatientId, $allocationId)
    {
        $ipdPatient = IpdPatient::findOrFail($ipdPatientId);
        $allocation = IpdPatientBed::where('ipd_patient_id', $ipdPatientId)->findOrFail($allocationId);

        $validated = $request->validate([
            'bed_id'  => 'required|exists:beds,id',
            'from'    => 'required|date',
            'to'      => 'nullable|date|after_or_equal:from',
            'remarks' => 'nullable|string',
        ]);

        $oldBedId = $allocation->bed_id;

        if ((int) $oldBedId !== (int) $validated['bed_id']) {
            Bed::where('id', $oldBedId)->update(['is_reserved' => false]);
            Bed::where('id', $validated['bed_id'])->update(['is_reserved' => true]);
        }

        $allocation->update([
            'bed_id'  => $validated['bed_id'],
            'from'    => $validated['from'],
            'to'      => $validated['to'],
            'remarks' => $validated['remarks'],
        ]);

        // return redirect()
        //     ->route('ipd-patients.ipd-patients.show', $ipdPatient->id)
        //     ->with('success', 'Bed allocation updated successfully.');

        return redirect(route('ipd-patients.ipd-patients.show', $ipdPatient->id) . '?tab=bed-history')
            ->with('success', 'Bed allocation updated successfully.');
    }
}
