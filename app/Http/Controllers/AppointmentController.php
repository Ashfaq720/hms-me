<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentPriority;
use App\Models\Doctor;
use App\Models\DoctorFee;
use App\Models\DoctorSlotTime;
use App\Models\Patient;
use App\Models\PatientCharge;
use App\Models\Shift;
use App\Models\Transaction;
use App\Services\FontDesk\CaseReferenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index()
    {
        $today_appointments = Appointment::with(['patient', 'doctorRelation'])
            ->whereDate('date', today())
            ->latest()
            ->get();

        $upcoming_appointments = Appointment::with(['patient', 'doctorRelation'])
            ->whereDate('date', '>', today())
            ->orderBy('date')
            ->get();

        $old_appointments = Appointment::with(['patient', 'doctorRelation'])
            ->whereDate('date', '<', today())
            ->latest('date')
            ->get();

        $patient_view_list = Appointment::with(['patient', 'doctorRelation'])
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('appointments')->groupBy('patient_id');
            })
            ->latest('date')
            ->get();

        return view('appointments.index', compact(
            'today_appointments',
            'upcoming_appointments',
            'old_appointments',
            'patient_view_list'
        ));
    }

    public function create()
    {
        $patients   = Patient::select('id', 'patient_name')->get();
        $doctors    = Doctor::select('id', 'name')->get();
        $shifts     = Shift::where('is_active', 1)->orderBy('id')->get(['id', 'name']);
        $priorities = AppointmentPriority::where('is_active', 1)->orderBy('id')->get(['id', 'name']);
        return view('appointments.create', compact('patients', 'doctors', 'shifts', 'priorities'));
    }

    public function store(Request $request, CaseReferenceService $caseService)
    {

        $validated = $request->validate([
            'patient_id'         => 'required|exists:patients,id',
            'doctor'             => 'required|exists:doctors,id',
            'case_reference_id'  => 'nullable|integer',
            'visit_details_id'   => 'nullable|integer',
            'date'               => 'required|date',
            'time'               => 'nullable|date_format:H:i',
            'priority'           => 'required|string|max:100',
            'specialist'         => 'required|string|max:100',
            'amount'             => 'required|numeric|min:0',
            'message'            => 'nullable|string|max:1000',
            'appointment_status' => 'nullable|in:Pending,Approved,Rejected,Cancelled',
            'visit_status'       => 'nullable|in:booked,checked_in,waiting,in_consultation,completed,closed,cancelled,no_show,referred,converted_to_er',
            'source'             => 'required|in:Walk-in,Phone,Web',
            'is_opd'             => 'required|in:Yes,No',
            'is_ipd'             => 'required|in:Yes,No',
            'is_queue'           => 'nullable|integer',
            'global_shift_id'    => 'nullable|integer',
            'shift_id'           => 'nullable|exists:shifts,id',
            'slot'               => ['nullable', 'regex:/^\d{2}:\d{2}\|\d{2}:\d{2}$/'],
            'live_consult'       => 'required|in:Zoom,Meet,None',
        ]);

        $validated['appointment_status'] ??= 'Pending';
        $validated['visit_status'] ??= 'booked';

        [$validated['slot_time_from'], $validated['slot_time_to']] = $this->splitSlot($validated['slot'] ?? null);
        unset($validated['slot']);

        $doctorFee = DoctorFee::where('doctor_id', $validated['doctor'])->first();

        DB::transaction(function () use ($request, $validated, $doctorFee, $caseService) {

         // 3. Create case reference
            $caseId = $caseService->createCase($validated['patient_id'], 'APPOINTMENT');

            $validated['case_reference_id'] = $caseId;


            $appointment = Appointment::create($validated);

            if (!$doctorFee) {
                return;
            }

            // Check if patient has a previous appointment with this doctor
            $lastAppointment = Appointment::where('patient_id', $validated['patient_id'])
                ->where('doctor', $validated['doctor'])
                ->where('id', '!=', $appointment->id)
                ->latest('date')
                ->first();

            $isFollowUp = false;

            if ($lastAppointment && $doctorFee->follow_up_window) {
                $daysSinceLast = $lastAppointment->date
                    ?->diffInDays($appointment->date);

                if ($daysSinceLast !== null && $daysSinceLast <= $doctorFee->follow_up_window) {
                    $isFollowUp = true;
                }
            }

            $fee = $isFollowUp
                ? ($doctorFee->follow_up_fee ?? 0)
                : ($doctorFee->first_visit_fee ?? 0);

            $chargeItem = $isFollowUp ? 'Follow-up Visit Fee' : 'First Visit Fee';

            if ($fee <= 0) {
                return;
            }

            // Create PatientCharge
            PatientCharge::create([
                'case_id'        =>  $caseId ?? null,
                'charge_module'  => 'appointment',
                'doctor_id'      => $validated['doctor'],
                'appointment_id' => $appointment->id,
                'charge_item'    => $chargeItem,
                'unit_price'     => $fee,
                'quantity'       => 1,
                'amount'         => $fee,
                'vat'            => 0,
                'tax'            => 0,
                'net_amount'     => $fee,
                'date'           => $appointment->date ?? now(),
                'status'         => 'pending',
                'is_paid'        => false,
                'is_bill_generated' => false,
                'created_by'     => auth()->id(),
            ]);

            // Create Transaction
            Transaction::create([
                'patient_id'   => $validated['patient_id'],
                'case_id'      =>  $caseId ?? null,
                'type'         => 'payment',
                'section'      => 'opd',
                'amount'       => $fee,
                'net_amount'   => $fee,
                'payment_via'  => 'cash',
                'payment_date' => $appointment->date ?? now(),
                'notes'        => $chargeItem . ' - Appointment #' . $appointment->id,
                'received_by'  => auth()->id(),
                'status'       => 'pending',
            ]);
        });

        $tab = $this->resolveTab($validated['date']);

        return redirect()->route('appointments.index', ['tab' => $tab])
            ->with('success', 'Appointment created successfully');
    }

    public function edit(Appointment $appointment)
    {
        $patients   = Patient::select('id', 'patient_name')->get();
        $doctors    = Doctor::select('id', 'name')->get();
        $shifts     = Shift::where('is_active', 1)->orderBy('id')->get(['id', 'name']);
        $priorities = AppointmentPriority::where('is_active', 1)->orderBy('id')->get(['id', 'name']);
        return view('appointments.edit', compact('appointment', 'patients', 'doctors', 'shifts', 'priorities'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'patient_id'         => 'required|exists:patients,id',
            'doctor'             => 'required|exists:doctors,id',
            'case_reference_id'  => 'nullable|integer',
            'visit_details_id'   => 'nullable|integer',
            'date'               => 'required|date',
            'time'               => 'nullable|date_format:H:i,H:i:s',
            'priority'           => 'required|string|max:100',
            'specialist'         => 'required|string|max:100',
            'amount'             => 'required|numeric|min:0',
            'message'            => 'nullable|string|max:1000',
            'appointment_status' => 'nullable|in:Pending,Approved,Rejected,Cancelled',
            'visit_status'       => 'nullable|in:booked,checked_in,waiting,in_consultation,completed,closed,cancelled,no_show,referred,converted_to_er',
            'source'             => 'required|in:Walk-in,Phone,Web',
            'is_opd'             => 'required|in:Yes,No',
            'is_ipd'             => 'required|in:Yes,No',
            'is_queue'           => 'nullable|integer',
            'global_shift_id'    => 'nullable|integer',
            'shift_id'           => 'nullable|exists:shifts,id',
            'slot'               => ['nullable', 'regex:/^\d{2}:\d{2}\|\d{2}:\d{2}$/'],
            'live_consult'       => 'required|in:Zoom,Meet,None',
        ]);

        $validated['appointment_status'] ??= 'Pending';
        $validated['visit_status'] ??= 'booked';

        [$validated['slot_time_from'], $validated['slot_time_to']] = $this->splitSlot($validated['slot'] ?? null);
        unset($validated['slot']);

        $appointment->update($validated);

        $tab = $this->resolveTab($appointment->date);

        return redirect()->route('appointments.index', ['tab' => $tab])
            ->with('success', 'Appointment updated successfully');
    }

    public function destroy(Request $request, Appointment $appointment)
    {
        $tab = $request->query('tab', $this->resolveTab($appointment->date));

        $appointment->delete();

        return redirect()->route('appointments.index', ['tab' => $tab])
            ->with('success', 'Appointment deleted successfully');
    }

    private function splitSlot(?string $slot): array
    {
        if (!$slot || !str_contains($slot, '|')) {
            return [null, null];
        }
        [$from, $to] = explode('|', $slot, 2);
        return [$from, $to];
    }

    public function getDoctorShifts(Request $request)
    {
        $data = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        $shiftIds = DoctorSlotTime::where('doctor_id', $data['doctor_id'])
            ->distinct()
            ->pluck('shift_id')
            ->unique()
            ->values();

        $shifts = Shift::whereIn('id', $shiftIds)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->unique('name')
            ->values();

        return response()->json($shifts->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values());
    }

    public function getSlots(Request $request)
    {
        $data = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'shift_id'  => 'required|exists:shifts,id',
            'date'      => 'required|date',
        ]);

        $day   = \Carbon\Carbon::parse($data['date'])->format('l');
        $slots = DoctorSlotTime::where('doctor_id', $data['doctor_id'])
            ->where('shift_id', $data['shift_id'])
            ->where('day', $day)
            ->select('time_from', 'time_to')
            ->distinct()
            ->orderBy('time_from')
            ->get()
            ->map(fn ($s) => [
                'time_from' => substr($s->time_from, 0, 5),
                'time_to'   => substr($s->time_to, 0, 5),
            ])
            ->unique(fn ($s) => $s['time_from'] . '|' . $s['time_to'])
            ->values();

        return response()->json($slots);
    }

    public function doctorWise(Request $request)
    {
        $request->validate([
            'doctor_id' => 'nullable|exists:doctors,id',
            'from_date' => 'nullable|date',
            'to_date'   => 'nullable|date|after_or_equal:from_date',
        ]);

        $doctors = Doctor::select('id', 'name')->get();

        $appointments = collect();

        if ($request->filled('doctor_id')) {
            $query = Appointment::with(['patient', 'doctorRelation'])
                ->where('doctor', $request->doctor_id);

            if ($request->filled('from_date')) {
                $query->whereDate('date', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->whereDate('date', '<=', $request->to_date);
            }

            $appointments = $query->latest('date')->get();
        }

        return view('appointments.doctor-wise', compact('doctors', 'appointments'));
    }

    private function resolveTab($appointmentDate): string
    {
        if (!$appointmentDate) {
            return 'today';
        }

        $date = $appointmentDate instanceof \Carbon\Carbon
            ? $appointmentDate
            : \Carbon\Carbon::parse($appointmentDate);

        if ($date->isToday()) {
            return 'today';
        }

        return $date->isFuture() ? 'upcoming' : 'old';
    }

    public function getDoctorFee(Request $request)
    {
        $request->validate([
            'doctor_id'  => 'required|exists:doctors,id',
            'patient_id' => 'required|exists:patients,id',
        ]);

        $doctorFee = DoctorFee::where('doctor_id', $request->doctor_id)->first();

        if (!$doctorFee) {
            return response()->json([
                'fee'          => 0,
                'fee_type'     => 'none',
                'message'      => 'No fee configured for this doctor.',
            ]);
        }

        // Check last appointment of this patient with this doctor
        $lastAppointment = Appointment::where('patient_id', $request->patient_id)
            ->where('doctor', $request->doctor_id)
            ->latest('date')
            ->first();

        $isFollowUp = false;

        if ($lastAppointment && $doctorFee->follow_up_window) {
            $daysSinceLast = $lastAppointment->date
                ?->diffInDays(now());

            if ($daysSinceLast !== null && $daysSinceLast <= $doctorFee->follow_up_window) {
                $isFollowUp = true;
            }
        }

        $fee = $isFollowUp
            ? ($doctorFee->follow_up_fee ?? 0)
            : ($doctorFee->first_visit_fee ?? 0);

        $feeType = $isFollowUp ? 'Follow-up Visit' : 'First Visit';

        return response()->json([
            'fee'               => $fee,
            'fee_type'          => $feeType,
            'first_visit_fee'   => $doctorFee->first_visit_fee ?? 0,
            'follow_up_fee'     => $doctorFee->follow_up_fee ?? 0,
            'follow_up_window'  => $doctorFee->follow_up_window,
            'last_visit_date'   => $lastAppointment?->date?->format('d M Y'),
            'days_since_last'   => $lastAppointment?->date?->diffInDays(now()),
        ]);
    }
}
