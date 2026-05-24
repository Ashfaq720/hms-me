<?php

namespace App\Http\Controllers\ER;

use App\Http\Controllers\Controller;
use App\Models\FrontDesk\ErPatient;
use Illuminate\Http\Request;

/**
 * ER patient tracking board + show page + lifecycle actions.
 *
 * Uses existing er_patients schema — status/priority/remarks are the
 * only mutable fields. Transfer to IPD / ICU / CCU / NICU / OT is just
 * a status change + a deep-link to the relevant module's create flow;
 * those modules already accept ER as a source via case_id chaining.
 */
class ErPatientController extends Controller
{
    public function index(Request $request)
    {
        $q = ErPatient::with([
            'patient:id,patient_name,mrn,mobileno,gender,dob',
            'doctor:id,name',
            'department:id,name',
        ]);

        // Filters
        if ($s = trim((string) $request->get('search'))) {
            $q->whereHas('patient', fn ($p) => $p->where('patient_name', 'like', "%{$s}%")
                                                  ->orWhere('mrn', 'like', "%{$s}%")
                                                  ->orWhere('mobileno', 'like', "%{$s}%"));
        }
        if (in_array($request->get('status'), ErPatient::STATUSES, true)) {
            $q->where('status', $request->get('status'));
        }
        if (in_array($request->get('priority'), ErPatient::PRIORITIES, true)) {
            $q->where('priority', $request->get('priority'));
        }
        if ($request->get('view') === 'active') {
            $q->active();
        } elseif ($request->get('view') === 'today') {
            $q->today();
        }

        $patients = $q->latest('arrival_time')->paginate(25)->appends($request->query());

        return view('er.patients.index', compact('patients'));
    }

    public function show(ErPatient $erPatient)
    {
        $erPatient->load([
            'patient', 'doctor', 'department', 'caseReference',
        ]);

        // History from same case (visits across modules)
        $caseHistory = $erPatient->caseReference
            ? \App\Models\Patient::find($erPatient->patient_id)?->histories()->latest()->limit(10)->get()
            : collect();

        return view('er.patients.show', compact('erPatient', 'caseHistory'));
    }

    /**
     * Update status / priority / clinical remarks in one form.
     * Status transitions only — clinical detail lives on Patient/Case.
     */
    public function update(Request $request, ErPatient $erPatient)
    {
        $data = $request->validate([
            'status'   => ['nullable', \Illuminate\Validation\Rule::in(ErPatient::STATUSES)],
            'priority' => ['nullable', \Illuminate\Validation\Rule::in(ErPatient::PRIORITIES)],
            'doctor_id'=> ['nullable', 'exists:doctors,id'],
            'remarks'  => ['nullable', 'string', 'max:2000'],
        ]);

        $erPatient->update(array_filter($data, fn ($v) => $v !== null && $v !== ''));

        return back()->with('success', 'ER patient updated.');
    }

    /**
     * Transfer-out action — marks the ER record as Admitted/Referred and
     * sends the user to the matching module's create flow. The receiving
     * module is responsible for its own admission; the case_id (and
     * patient_id) carry the link, so we don't duplicate data here.
     */
    public function transferOut(Request $request, ErPatient $erPatient)
    {
        $data = $request->validate([
            'destination' => ['required', 'in:IPD,ICU,CCU,NICU,OT,REFER'],
            'remarks'     => ['nullable', 'string', 'max:1000'],
        ]);

        $erPatient->update([
            'status'  => $data['destination'] === 'REFER'
                ? ErPatient::STATUS_REFERRED
                : ErPatient::STATUS_ADMITTED,
            'remarks' => trim(($erPatient->remarks ?? '') . "\n[" . now()->format('Y-m-d H:i') . "] Transferred → " . $data['destination']
                            . ($data['remarks'] ? ' — ' . $data['remarks'] : '')),
        ]);

        // Redirect to the destination module's create/admission page,
        // pre-filling patient_id so the user doesn't re-enter it.
        $patientId = $erPatient->patient_id;

        return match ($data['destination']) {
            'IPD'   => redirect()->route('ipd-patients.create', ['patient_id' => $patientId])
                                ->with('success', "ER #{$erPatient->id} marked Admitted → IPD. Continue admission below."),
            'ICU'   => redirect()->route('icu.admissions.index', ['icu_type' => 'ICU'])
                                ->with('info', "ER #{$erPatient->id} marked Admitted → ICU. Create the ICU admission for {$erPatient->patient?->patient_name}."),
            'CCU'   => redirect()->route('icu.admissions.index', ['icu_type' => 'CCU'])
                                ->with('info', "ER #{$erPatient->id} marked Admitted → CCU. Create the CCU admission for {$erPatient->patient?->patient_name}."),
            'NICU'  => redirect()->route('nicu.admissions.index')
                                ->with('info', "ER #{$erPatient->id} marked Admitted → NICU. Open the relevant admission to create NICU record."),
            'OT'    => redirect()->route('ot.surgery-requests.index')
                                ->with('info', "ER #{$erPatient->id} → OT. Create a surgery request for {$erPatient->patient?->patient_name}."),
            'REFER' => back()->with('success', "ER #{$erPatient->id} marked Referred out."),
        };
    }

    /** Discharge / mark expired / cancel (one-button actions). */
    public function close(Request $request, ErPatient $erPatient)
    {
        $data = $request->validate([
            'outcome' => ['required', \Illuminate\Validation\Rule::in([
                ErPatient::STATUS_DISCHARGED,
                ErPatient::STATUS_EXPIRED,
                ErPatient::STATUS_CANCELLED,
            ])],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $erPatient->update([
            'status'  => $data['outcome'],
            'remarks' => trim(($erPatient->remarks ?? '') . "\n[" . now()->format('Y-m-d H:i') . '] '
                            . $data['outcome']
                            . ($data['remarks'] ? ' — ' . $data['remarks'] : '')),
        ]);

        return back()->with('success', "ER patient marked {$data['outcome']}.");
    }
}
