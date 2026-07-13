<?php
namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\IpdPatient;
use App\Models\LabInvestigation;
use App\Models\Pharmacy\Medicine;
use App\Models\PresciptionLabInvestigation;
use App\Models\PresciptionMedicine;
use App\Models\PresciptionSymptom;
use App\Models\Prescription;
use App\Models\Symptom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IpdPrescriptionController extends Controller
{
    public function create($ipdPatientId)
    {
        $ipdPatient        = IpdPatient::with(['patient', 'doctor.department', 'doctor.designation'])->findOrFail($ipdPatientId);
        $doctors           = Doctor::select('id', 'name')->get();
        $symptoms          = Symptom::where('status', true)->select('id', 'name')->get();
        $medicines         = Medicine::where('status', true)->select('id', 'medicine_name')->get();
        $labInvestigations = LabInvestigation::where('status', true)->select('id', 'name', 'short_name')->get();

        return view('ipd_patients.prescriptions.create', compact('ipdPatient', 'doctors', 'symptoms', 'labInvestigations', 'medicines'));
    }

    public function store(Request $request, $ipdPatientId)
    {
        // dd($request->all());
        $ipdPatient = IpdPatient::findOrFail($ipdPatientId);

        $validated = $request->validate([
            'doctor_id'                                 => 'nullable|exists:doctors,id',
            'date'                                      => 'required|date',
            'findings'                                  => 'nullable|string',
            'advice'                                    => 'nullable|string',
            'next_visit'                                => 'nullable|date',
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
                'ipd_patient_id' => $ipdPatient->id,
                'patient_id'     => $ipdPatient->patient_id,
                'doctor_id'      => $validated['doctor_id'] ?? $ipdPatient->doctor_id,
                'date'           => $validated['date'],
                'findings'       => $validated['findings'] ?? null,
                'advice'         => $validated['advice'] ?? null,
                'next_visit'     => $validated['next_visit'] ?? null,
                'generated_by'   => auth()->id(),
                'type'           => 'Manual',
            ]);

            // Save symptoms
            if (! empty($validated['symptoms'])) {
                foreach ($validated['symptoms'] as $symptom) {
                    PresciptionSymptom::create([
                        'prescription_id' => $prescription->id,
                        'symptom_id'      => $symptom['symptom_id'],
                        'note'            => $symptom['note'] ?? null,
                    ]);
                }
            }

            // Save medicines
            if (! empty($validated['medicines'])) {
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

            // Save lab investigations
            if (! empty($validated['lab_investigations'])) {
                foreach ($validated['lab_investigations'] as $lab) {
                    PresciptionLabInvestigation::create([
                        'prescription_id'      => $prescription->id,
                        'lab_investigation_id' => $lab['lab_investigation_id'],
                        'note'                 => $lab['note'] ?? null,
                    ]);
                }
            }

            DB::commit();

            // return redirect()
            //     ->route('ipd-patients.ipd-patients.show', $ipdPatient->id)
            //     ->with('success', 'Prescription created successfully.');

            return redirect(route('ipd-patients.ipd-patients.show', $ipdPatient->id) . '?tab=prescription')
                ->with('success', 'Prescription created successfully.');
        } catch (\Throwable $e) {
            dd($e->getMessage());
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to save prescription: ' . $e->getMessage());
        }
    }

    public function show($ipdPatientId, $prescriptionId)
    {
        $ipdPatient   = IpdPatient::with('patient')->findOrFail($ipdPatientId);
        $prescription = Prescription::with(['doctor', 'symptoms.symptom', 'medicines', 'labInvestigations.labInvestigation'])
            ->where('ipd_patient_id', $ipdPatientId)
            ->findOrFail($prescriptionId);

        return view('ipd_patients.prescriptions.show', compact('ipdPatient', 'prescription'));
    }

    public function pdf($ipdPatientId, $prescriptionId)
    {
        $ipdPatient   = IpdPatient::with(['patient', 'doctor.department', 'doctor.designation'])->findOrFail($ipdPatientId);
        $prescription = Prescription::with(['doctor.department', 'doctor.designation', 'symptoms.symptom', 'medicines', 'labInvestigations.labInvestigation'])
            ->where('ipd_patient_id', $ipdPatientId)
            ->findOrFail($prescriptionId);

        $html = view('ipd_patients.prescriptions.pdf', compact('ipdPatient', 'prescription'))->render();

        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4',
        ]);

        $mpdf->WriteHTML($html);

        return $mpdf->Output('prescription-' . $prescription->prescription_no . '.pdf', 'I');
    }

    public function destroy($ipdPatientId, $prescriptionId)
    {
        $prescription = Prescription::where('ipd_patient_id', $ipdPatientId)->findOrFail($prescriptionId);
        $prescription->delete();

        // return redirect()
        //     ->route('ipd-patients.ipd-patients.show', $ipdPatientId)
        //     ->with('success', 'Prescription deleted successfully.');

        return redirect(route('ipd-patients.ipd-patients.show', $ipdPatientId) . '?tab=prescription')
            ->with('success', 'Prescription deleted successfully.');
    }
}
