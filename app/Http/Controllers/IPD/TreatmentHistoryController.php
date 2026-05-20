<?php
namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\IpdPatient;
use App\Models\Ipd\IpdTreatmentHistory;
use App\Models\LabInvestigationType;
use Illuminate\Http\Request;

class TreatmentHistoryController extends Controller
{
    public function create($ipdPatientId)
    {
        $ipdPatient            = IpdPatient::with(['patient', 'doctor', 'department'])->findOrFail($ipdPatientId);
        $doctors               = Doctor::select('id', 'name')->orderBy('name')->get();
        $labInvestigationTypes = LabInvestigationType::where('status', 1)->orderBy('name')->get();
        return view('ipd_patients.treatment-histories.create', compact('ipdPatient', 'doctors', 'labInvestigationTypes'));
    }

    public function store(Request $request, $ipdPatientId)
    {
        $ipdPatient = IpdPatient::findOrFail($ipdPatientId);
        $data       = $this->validateData($request);

        $data['ipd_id']     = $ipdPatient->id;
        $data['case_id']    = $ipdPatient->case_id;
        $data['patient_id'] = $ipdPatient->patient_id;

        IpdTreatmentHistory::create($data);

        // return redirect()
        //     ->route('ipd-patients.ipd-patients.show', $ipdPatient->id)
        //     ->with('success', 'Treatment history created successfully.');

        return redirect(route('ipd-patients.ipd-patients.show', $ipdPatient->id) . '?tab=treatment-history')
            ->with('success', 'Treatment history created successfully.');
    }

    public function edit($ipdPatientId, $historyId)
    {
        $ipdPatient            = IpdPatient::with(['patient', 'doctor', 'department'])->findOrFail($ipdPatientId);
        $history               = IpdTreatmentHistory::where('ipd_id', $ipdPatientId)->findOrFail($historyId);
        $doctors               = Doctor::select('id', 'name')->orderBy('name')->get();
        $labInvestigationTypes = LabInvestigationType::where('status', 1)->orderBy('name')->get();
        return view('ipd_patients.treatment-histories.edit', compact('ipdPatient', 'history', 'doctors', 'labInvestigationTypes'));
    }

    public function update(Request $request, $ipdPatientId, $historyId)
    {
        $history = IpdTreatmentHistory::where('ipd_id', $ipdPatientId)->findOrFail($historyId);
        $data    = $this->validateData($request);

        $history->update($data);

        // return redirect()
        //     ->route('ipd-patients.ipd-patients.show', $ipdPatientId)
        //     ->with('success', 'Treatment history updated successfully.');

        return redirect(route('ipd-patients.ipd-patients.show', $ipdPatientId) . '?tab=treatment-history')
            ->with('success', 'Treatment history updated successfully.');
    }

    public function destroy($ipdPatientId, $historyId)
    {
        $history = IpdTreatmentHistory::where('ipd_id', $ipdPatientId)->findOrFail($historyId);
        $history->delete();

        // return redirect()
        //     ->route('ipd-patients.ipd-patients.show', $ipdPatientId)
        //     ->with('success', 'Treatment history deleted successfully.');

        return redirect(route('ipd-patients.ipd-patients.show', $ipdPatientId) . '?tab=treatment-history')
            ->with('success', 'Treatment history deleted successfully.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'doctor_id'          => ['nullable', 'exists:doctors,id'],
            'date'               => ['required', 'date'],
            'prescribe_medicine' => ['nullable', 'string', 'max:255'],
            'diagnosis'          => ['nullable', 'string', 'max:255'],
            'tx_note'            => ['nullable', 'string'],
        ]);
    }
}
