<?php

namespace App\Http\Controllers\OPD;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\LabInvestigation;
use App\Models\OpdPatient;
use App\Models\Pharmacy\Medicine;
use App\Models\Prescription;
use App\Models\PresciptionLabInvestigation;
use App\Models\PresciptionMedicine;
use App\Models\PresciptionSymptom;
use App\Models\Symptom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpdPrescriptionController extends Controller
{
    public function create($opdPatientId)
    {
        $opdPatient = OpdPatient::with(['patient', 'doctor.department', 'doctor.designation'])->findOrFail($opdPatientId);
        $doctors = Doctor::select('id', 'name')->get();
        $symptoms = Symptom::where('status', true)->select('id', 'name')->get();
        $medicines = Medicine::where('status', true)->select('id', 'medicine_name')->get();
        $labInvestigations = LabInvestigation::where('status', true)->select('id', 'name', 'short_name')->get();

        return view('opd_patients.prescriptions.create', compact('opdPatient', 'doctors', 'symptoms', 'labInvestigations', 'medicines'));
    }

    public function store(Request $request, $opdPatientId)
    {
        $opdPatient = OpdPatient::findOrFail($opdPatientId);

        $validated = $request->validate([
            'doctor_id'                                 => 'nullable|exists:doctors,id',
            'date'                                      => 'required|date',
            'findings'                                  => 'nullable|string',
            'icd10_code'                                => 'nullable|string|max:20',
            'icd10_description'                         => 'nullable|string|max:255',
            'advice'                                    => 'nullable|string',
            'next_visit'                                => 'nullable|date',
            'follow_up_note'                            => 'nullable|string',
            'radiology_orders'                          => 'nullable|string',
            'symptoms'                                  => 'nullable|array',
            'symptoms.*.symptom_id'                     => 'required|exists:symptoms,id',
            'medicines'                                 => 'nullable|array',
            'medicines.*.medicine_id'                   => 'nullable|integer',
            'medicines.*.dosage'                        => 'nullable|string|max:255',
            'medicines.*.frequency'                     => 'nullable|string|max:255',
            'medicines.*.duration'                      => 'nullable|string|max:255',
            'medicines.*.note'                          => 'nullable|string',
            'lab_investigations'                        => 'nullable|array',
            'lab_investigations.*.lab_investigation_id' => 'required|exists:lab_investigations,id',
            'lab_investigations.*.note'                 => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $prescription = Prescription::create([
                'opd_patient_id'   => $opdPatient->id,
                'patient_id'       => $opdPatient->patient_id,
                'doctor_id'        => $validated['doctor_id'] ?? $opdPatient->doctor_id,
                'date'             => $validated['date'],
                'findings'         => $validated['findings'] ?? null,
                'icd10_code'       => $validated['icd10_code'] ?? null,
                'icd10_description' => $validated['icd10_description'] ?? null,
                'advice'           => $validated['advice'] ?? null,
                'next_visit'       => $validated['next_visit'] ?? null,
                'follow_up_note'   => $validated['follow_up_note'] ?? null,
                'radiology_orders' => $validated['radiology_orders'] ?? null,
                'generated_by'     => auth()->id(),
                'type'             => 'Manual',
            ]);

            if (!empty($validated['symptoms'])) {
                foreach ($validated['symptoms'] as $symptom) {
                    PresciptionSymptom::create([
                        'prescription_id' => $prescription->id,
                        'symptom_id'      => $symptom['symptom_id'],
                        'note'            => $symptom['note'] ?? null,
                    ]);
                }
            }

            if (!empty($validated['medicines'])) {
                foreach ($validated['medicines'] as $medicine) {
                    PresciptionMedicine::create([
                        'prescription_id' => $prescription->id,
                        'medicine_id'     => $medicine['medicine_id'] ?? null,
                        'dosage'          => $medicine['dosage'] ?? null,
                        'frequency'       => $medicine['frequency'] ?? null,
                        'duration'        => $medicine['duration'] ?? null,
                        'note'            => $medicine['note'] ?? null,
                    ]);
                }
            }

            if (!empty($validated['lab_investigations'])) {
                foreach ($validated['lab_investigations'] as $lab) {
                    PresciptionLabInvestigation::create([
                        'prescription_id'      => $prescription->id,
                        'lab_investigation_id' => $lab['lab_investigation_id'],
                        'note'                 => $lab['note'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('opd-patients.show', $opdPatient->id)
                ->with('success', 'Prescription created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to save prescription: ' . $e->getMessage());
        }
    }

    public function show($opdPatientId, $prescriptionId)
    {
        $opdPatient = OpdPatient::with('patient')->findOrFail($opdPatientId);
        $prescription = Prescription::with(['doctor', 'symptoms.symptom', 'medicines', 'labInvestigations.labInvestigation'])
            ->where('opd_patient_id', $opdPatientId)
            ->findOrFail($prescriptionId);

        return view('opd_patients.prescriptions.show', compact('opdPatient', 'prescription'));
    }

    public function pdf($opdPatientId, $prescriptionId)
    {
        $opdPatient = OpdPatient::with(['patient', 'doctor.department', 'doctor.designation'])->findOrFail($opdPatientId);
        $prescription = Prescription::with(['doctor.department', 'doctor.designation', 'symptoms.symptom', 'medicines', 'labInvestigations.labInvestigation'])
            ->where('opd_patient_id', $opdPatientId)
            ->findOrFail($prescriptionId);

        $html = view('opd_patients.prescriptions.pdf', compact('opdPatient', 'prescription'))->render();

        $mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
        $mpdf->WriteHTML($html);

        return $mpdf->Output('prescription-' . $prescription->prescription_no . '.pdf', 'I');
    }

    public function destroy($opdPatientId, $prescriptionId)
    {
        $prescription = Prescription::where('opd_patient_id', $opdPatientId)->findOrFail($prescriptionId);
        $prescription->delete();

        return redirect()
            ->route('opd-patients.show', $opdPatientId)
            ->with('success', 'Prescription deleted successfully.');
    }
}
