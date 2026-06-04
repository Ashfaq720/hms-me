<?php

namespace App\Http\Controllers\OPD;

use App\Http\Controllers\Controller;
use App\Models\Opd\OpdMedication;
use App\Models\OpdPatient;
use App\Models\Pharmacy\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpdMedicationController extends Controller
{
    public function create($id)
    {
        $opdPatient = OpdPatient::with(['patient', 'doctor'])->findOrFail($id);
        $medicines = Medicine::where('status', true)
            ->select('id', 'medicine_name', 'medicine_unit_id', 'medicine_category_id', 'medical_group_id')
            ->with(['unit', 'category', 'medicalGroup'])
            ->get();

        return view('opd_patients.medications.create', compact('opdPatient', 'medicines'));
    }

    public function store(Request $request, $id)
    {
        $opdPatient = OpdPatient::findOrFail($id);

        $validated = $request->validate([
            'medications'                 => 'required|array|min:1',
            'medications.*.medicine_id'   => 'required|exists:medicines,id',
            'medications.*.datetime'      => 'required|date',
            'medications.*.dosage'        => 'nullable|string|max:255',
            'medications.*.medicated_by'  => 'nullable|string|max:255',
            'medications.*.remarks'       => 'nullable|string|max:1000',
            'medications.*.notes'         => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['medications'] as $medication) {
                OpdMedication::create([
                    'opd_patient_id' => $opdPatient->id,
                    'medicine_id'    => $medication['medicine_id'],
                    'datetime'       => $medication['datetime'],
                    'dosage'         => $medication['dosage'] ?? null,
                    'medicated_by'   => $medication['medicated_by'] ?? null,
                    'remarks'        => $medication['remarks'] ?? null,
                    'notes'          => $medication['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect(route('opd-patients.show', $opdPatient->id) . '?tab=medication')
                ->with('success', 'Medications added successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to save medications: ' . $e->getMessage());
        }
    }

    public function destroy($opdPatientId, $medicationId)
    {
        $medication = OpdMedication::where('opd_patient_id', $opdPatientId)->findOrFail($medicationId);
        $medication->delete();

        return redirect(route('opd-patients.show', $opdPatientId) . '?tab=medication')
            ->with('success', 'Medication deleted successfully.');
    }
}
