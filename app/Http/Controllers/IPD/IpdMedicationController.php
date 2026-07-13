<?php
namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\IpdPatient;
use App\Models\Ipd\IpdMedication;
use App\Models\Pharmacy\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IpdMedicationController extends Controller
{
    public function create($id)
    {
        $ipdPatient = IpdPatient::with(['patient', 'doctor'])->findOrFail($id);
        $medicines  = Medicine::where('status', true)->select('id', 'medicine_name', 'medicine_unit_id', 'medicine_category_id', 'medical_group_id')->with(['unit', 'category', 'medicalGroup'])->get();
        return view('ipd_patients.medications.create', compact('ipdPatient', 'medicines'));
    }

    public function store(Request $request, $id)
    {
        $ipdPatient = IpdPatient::findOrFail($id);

        $validated = $request->validate([
            'medications'                => 'required|array|min:1',
            'medications.*.medicine_id'  => 'required|exists:medicines,id',
            'medications.*.datetime'     => 'required|date',
            'medications.*.dosage'       => 'nullable|string|max:255',
            'medications.*.medicated_by' => 'nullable|string|max:255',
            'medications.*.remarks'      => 'nullable|string|max:1000',
            'medications.*.notes'        => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['medications'] as $medication) {
                IpdMedication::create([
                    'ipd_patient_id' => $ipdPatient->id,
                    'medicine_id'    => $medication['medicine_id'],
                    'datetime'       => $medication['datetime'],
                    'dosage'         => $medication['dosage'] ?? null,
                    'medicated_by'   => $medication['medicated_by'] ?? null,
                    'remarks'        => $medication['remarks'] ?? null,
                    'notes'          => $medication['notes'] ?? null,
                ]);
            }

            DB::commit();

            // return redirect()
            //     ->route('ipd-patients.ipd-patients.show', $ipdPatient->id)
            //     ->with('success', 'Medications added successfully.');

            return redirect(route('ipd-patients.ipd-patients.show', $ipdPatient->id) . '?tab=medication')
                ->with('success', 'Medications added successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to save medications: ' . $e->getMessage());
        }
    }

    public function destroy($ipdPatientId, $medicationId)
    {
        $medication = IpdMedication::where('ipd_patient_id', $ipdPatientId)->findOrFail($medicationId);
        $medication->delete();

        // return redirect()
        //     ->route('ipd-patients.ipd-patients.show', $ipdPatientId)
        //     ->with('success', 'Medication deleted successfully.');

        return redirect(route('ipd-patients.ipd-patients.show', $ipdPatientId) . '?tab=medication')
            ->with('success', 'Medication deleted successfully.');
    }
}
