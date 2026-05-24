<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSlotTime;
use App\Models\Shift;
use Illuminate\Http\Request;

class PatientQueueController extends Controller
{
    public function index(Request $request)
    {
        $doctors = Doctor::orderBy('name')->get(['id', 'name', 'doctor_code']);
        $shifts  = Shift::where('is_active', 1)->orderBy('id')->get(['id', 'name']);

        $appointments = collect();
        $slots        = collect();
        $filters      = [
            'doctor_id' => $request->get('doctor_id'),
            'shift_id'  => $request->get('shift_id'),
            'date'      => $request->get('date'),
            'slot'      => $request->get('slot'),
        ];

        if ($filters['doctor_id'] && $filters['shift_id'] && $filters['date']) {
            $day   = \Carbon\Carbon::parse($filters['date'])->format('l');
            $slots = DoctorSlotTime::where('doctor_id', $filters['doctor_id'])
                ->where('shift_id', $filters['shift_id'])
                ->where('day', $day)
                ->orderBy('time_from')
                ->get();

            $query = Appointment::with(['patient', 'doctorRelation'])
                ->where('doctor', $filters['doctor_id'])
                ->where('shift_id', $filters['shift_id'])
                ->whereDate('date', $filters['date']);

            if ($filters['slot']) {
                [$from, $to] = explode('|', $filters['slot']);
                $query->whereBetween('time', [$from, $to]);
            }

            $appointments = $query->orderBy('time')->get();
        }

        return view('patient-queue.index', compact('doctors', 'shifts', 'slots', 'appointments', 'filters'));
    }

    public function slots(Request $request)
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
            ->orderBy('time_from')
            ->get()
            ->map(function ($s) {
                return [
                    'time_from' => substr($s->time_from, 0, 5),
                    'time_to'   => substr($s->time_to, 0, 5),
                ];
            });

        return response()->json($slots);
    }
}
