<?php
namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\CaseReference;
use App\Models\FrontDesk\ErPatient;
use App\Models\Icu\IcuAdmission;
use App\Models\IpdPatient;
use App\Models\IpdPatientBed;
use App\Models\IpdPatientDocument;
use App\Models\OpdPatient;
use App\Models\Patient;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthCardController extends Controller
{
    public function index()
    {
        return view('backend.health-card.index');
    }

    public function show(Patient $patient)
    {
        return view('patients.health-card', compact('patient'));
    }

    public function checkin(Request $request)
    {
        $request->validate([
            'patient_id'  => 'required|exists:patients,id',
            'destination' => 'required|in:opd,er,ipd,pharmacy,lab',
            'date'        => 'required|date',
        ]);

        $patientId   = $request->input('patient_id');
        $date        = $request->input('date');
        $destination = $request->input('destination');
        $case        = CaseReference::create();

        switch ($destination) {

            case 'opd':
                $request->validate([
                    'department_id'   => 'required|exists:departments,id',
                    'doctor_id'       => 'required|exists:doctors,id',
                    'visit_type'      => 'required|in:new,follow_up,recheckup,referred,emergency',
                    'chief_complaint' => 'nullable|string|max:1000',
                ]);
                $record = OpdPatient::create([
                    'patient_id'      => $patientId,
                    'case_id'         => $case->id,
                    'doctor_id'       => $request->input('doctor_id'),
                    'department_id'   => $request->input('department_id'),
                    'date'            => $date,
                    'visit_type'      => $request->input('visit_type'),
                    'chief_complaint' => $request->input('chief_complaint'),
                ]);
                return redirect()
                    ->route('opd-patients.show', $record->id)
                    ->with('success', 'Patient checked in to OPD.');

            case 'er':
                $request->validate([
                    'priority'    => 'required|in:critical,high,normal',
                    'description' => 'nullable|string|max:1000',
                ]);
                ErPatient::create([
                    'patient_id'   => $patientId,
                    'case_id'      => $case->id,
                    'arrival_time' => $date,
                    'priority'     => strtoupper($request->input('priority')),
                    'description'  => $request->input('description'),
                    'status'       => 'ADMITTED',
                ]);
                return redirect()
                    ->route('front_desk.index')
                    ->with('success', 'Patient checked in to Emergency — ' . strtoupper($request->input('priority')) . ' priority.');

            case 'ipd':
                $request->validate([
                    'department_id'           => 'required|exists:departments,id',
                    'doctor_id'               => 'required|exists:doctors,id',
                    'admission_date'          => 'nullable|date',
                    'possible_discharge_date' => 'nullable|date',
                    'admission_type'          => 'nullable|string|max:100',
                    'ipd_status'              => 'nullable|string|max:50',
                    'patient_history'         => 'nullable|string',
                    'remarks'                 => 'nullable|string',
                    'allocation_choice'       => 'nullable|in:bed,icu',
                    'bed_id'                  => 'required|exists:beds,id',
                    'icu_bed_id'              => 'nullable|exists:beds,id',
                    'from'                    => 'nullable|date',
                    'bed_remarks'             => 'nullable|string',
                    'documents'               => 'nullable|array',
                    'documents.*.file'        => 'nullable|file|max:10240',
                    'amount'                  => 'nullable|numeric|min:0',
                    'files'                   => 'nullable|array',
                    'files.*'                 => 'nullable|file|max:10240',
                ]);

                return DB::transaction(function () use ($request, $patientId, $date, $case) {
                    // A) IPD record
                    $admissionDate = $request->input('admission_date') ?: $date;
                    $ipd           = IpdPatient::create([
                        'patient_id'              => $patientId,
                        'case_id'                 => $case->id,
                        'doctor_id'               => $request->input('doctor_id'),
                        'department_id'           => $request->input('department_id'),
                        'admission_date'          => $admissionDate,
                        'possible_discharge_date' => $request->input('possible_discharge_date'),
                        'admission_type'          => $request->input('admission_type'),
                        'patient_history'         => $request->input('patient_history'),
                        'remarks'                 => $request->input('remarks'),
                        'status'                  => $request->input('ipd_status', 'Admitted'),
                    ]);

                    // B) Bed / ICU allocation
                    $allocationChoice = $request->input('allocation_choice', 'bed');
                    $allocatedBedId   = $allocationChoice === 'icu'
                        ? $request->input('icu_bed_id')
                        : $request->input('bed_id');

                    if ($allocatedBedId) {
                        $allocatedBed = Bed::with('bedType')->findOrFail($allocatedBedId);
                        $bedIsIcu     = (bool) optional($allocatedBed->bedType)->is_icu;

                        IpdPatientBed::create([
                            'case_id'         => $case->id,
                            'ipd_patient_id'  => $ipd->id,
                            'bed_id'          => $allocatedBedId,
                            'allocation_type' => $bedIsIcu ? 'icu' : 'bed',
                            'from'            => $request->input('from', $admissionDate),
                            'to'              => $request->input('to'),
                            'status'          => 'Admitted',
                            'remarks'         => $request->input('bed_remarks'),
                        ]);

                        Bed::where('id', $allocatedBedId)->update(['is_reserved' => true]);

                        if ($bedIsIcu) {
                            $icuType = optional($allocatedBed->bedType)->icu_type ?: 'ICU';
                            $from    = $request->input('from', $admissionDate);
                            IcuAdmission::create([
                                'icu_case_id'         => IcuAdmission::generateCaseId($icuType, new \DateTimeImmutable($from)),
                                'case_id'             => $case->id,
                                'patient_id'          => $patientId,
                                'source_type'         => 'Ipd',
                                'source_id'           => $ipd->id,
                                'icu_type'            => $icuType,
                                'admission_type'      => $request->input('admission_type', 'Emergency'),
                                'admission_diagnosis' => $request->input('patient_history', '(from health-card check-in)'),
                                'referring_doctor_id' => $request->input('doctor_id'),
                                'isolation_type'      => 'None',
                                'ventilator_required' => (bool) optional($allocatedBed->bedType)->has_ventilator_support,
                                'monitor_required'    => (bool) optional($allocatedBed->bedType)->has_monitor_support,
                                'bed_id'              => $allocatedBedId,
                                'admission_time'      => $from,
                                'status'              => 'Admitted',
                                'remarks'             => $request->input('bed_remarks'),
                                'created_by'          => auth()->id(),
                            ]);
                        }
                    }

                    // C) Patient documents
                    foreach ($request->input('documents', []) as $idx => $doc) {
                        $uploaded = $request->file("documents.$idx.file");
                        if (! $uploaded || ! $uploaded->isValid()) {
                            continue;
                        }
                        $stored = $uploaded->store('ipd_patient_documents', 'public');

                        IpdPatientDocument::create([
                            'ipd_patient_id' => $ipd->id,
                            'title'          => $doc['title'] ?? null,
                            'file'           => $stored,
                            'remarks'        => $doc['remarks'] ?? null,
                        ]);
                    }

                    // D) Advance payment
                    if (((float) $request->input('amount', 0)) > 0) {
                        $storedFiles = [];
                        if ($request->hasFile('files')) {
                            foreach ($request->file('files') as $file) {
                                if ($file && $file->isValid()) {
                                    $storedFiles[] = $file->store('transactions', 'public');
                                }
                            }
                        }

                        $amount    = (float) $request->input('amount', 0);
                        $vatPct    = (float) $request->input('vat', 0);
                        $taxPct    = (float) $request->input('tax', 0);
                        $netAmount = round($amount + ($amount * $vatPct / 100) + ($amount * $taxPct / 100), 2);

                        Transaction::create([
                            'patient_id'         => $patientId,
                            'case_id'            => $case->id,
                            'ipd_patient_id'     => $ipd->id,
                            'type'               => 'Advance',
                            'section'            => 'Ipd',
                            'amount'             => $amount,
                            'vat'                => $vatPct,
                            'tax'                => $taxPct,
                            'net_amount'         => $netAmount,
                            'payment_via'        => $request->input('payment_via'),
                            'payment_date'       => $request->input('payment_date'),
                            'cheque_name'        => $request->input('cheque_name'),
                            'cheque_no'          => $request->input('cheque_no'),
                            'cheque_date'        => $request->input('cheque_date'),
                            'card_no'            => $request->input('card_no'),
                            'card_type'          => $request->input('card_type'),
                            'mfs_type'           => $request->input('mfs_type'),
                            'mfs_no'             => $request->input('mfs_no'),
                            'mfs_transaction_id' => $request->input('mfs_transaction_id'),
                            'notes'              => $request->input('notes'),
                            'received_by'        => $request->input('received_by'),
                            'files'              => ! empty($storedFiles) ? json_encode($storedFiles) : null,
                            'status'             => $request->input('payment_status', 'successed'),
                        ]);
                    }

                    return redirect()
                        ->route('ipd-patients.show', $ipd->id)
                        ->with('success', 'Patient admitted to IPD.');
                });

            case 'pharmacy':
                return redirect()
                    ->route('admin.pharmacy.opd-dispense')
                    ->with('success', 'Patient directed to Pharmacy counter.');

            case 'lab':
                return redirect()
                    ->route('front_desk.index')
                    ->with('success', 'Patient directed to Laboratory.');
        }

        return redirect()->route('front_desk.index');
    }

    public function checkFollowUp(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id'  => 'required|exists:doctors,id',
        ]);

        $fee = \App\Models\DoctorFee::where('doctor_id', $request->doctor_id)->first();
        $followUpWindow = $fee?->follow_up_window;

        $lastVisit = OpdPatient::where('patient_id', $request->patient_id)
            ->where('doctor_id', $request->doctor_id)
            ->whereNotNull('date')
            ->orderByDesc('date')
            ->first();

        if (!$lastVisit || !$followUpWindow) {
            return response()->json([
                'is_follow_up'    => false,
                'last_visit_date' => $lastVisit
                    ? \Carbon\Carbon::parse($lastVisit->date)->format('d M Y')
                    : null,
                'days_since_last' => $lastVisit
                    ? (int) \Carbon\Carbon::parse($lastVisit->date)->diffInDays(now())
                    : null,
                'follow_up_window' => $followUpWindow,
            ]);
        }

        $daysSince = (int) \Carbon\Carbon::parse($lastVisit->date)->diffInDays(now());

        return response()->json([
            'is_follow_up'     => $daysSince <= $followUpWindow,
            'last_visit_date'  => \Carbon\Carbon::parse($lastVisit->date)->format('d M Y'),
            'days_since_last'  => $daysSince,
            'follow_up_window' => $followUpWindow,
        ]);
    }

    public function findByCard(Request $request)
    {
        $no = trim($request->input('card_no', ''));

        if (! $no) {
            return response()->json(['error' => 'Card number required'], 422);
        }

        $patient = Patient::where('health_card_no', $no)->first();

        if (! $patient) {
            return response()->json(['error' => 'No patient found for this health card number'], 404);
        }

        return response()->json([
            'id'                => $patient->id,
            'patient_name'      => $patient->patient_name,
            'mobileno'          => $patient->mobileno,
            'mrn'               => $patient->mrn,
            'health_card_no'    => $patient->health_card_no,
            'gender'            => $patient->gender,
            'dob'               => $patient->dob?->format('Y-m-d'),
            'blood_group'       => $patient->blood_group,
            'known_allergies'   => $patient->known_allergies,
            'organization_name' => $patient->organization_name,
            'organization_id'   => $patient->organization_id,
            'discount_type'     => $patient->discount_type,
            'age'               => calculateAgeFromDob($patient->dob),
            'image_url'         => $patient->image ? asset('storage/' . $patient->image) : null,
        ]);
    }
}
