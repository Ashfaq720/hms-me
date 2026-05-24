<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\IpdPatient;
use App\Models\OpdPatient;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorPortalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Doctor portal landing — only patients/visits assigned to the logged-in doctor.
     * Falls back to a "you're not registered as a doctor" notice for non-doctor users.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $doctor = $user->doctor ?? Doctor::where('email', $user->email)->first();

        if (! $doctor) {
            return view('doctor_portal.not_a_doctor');
        }

        $tab = $request->get('tab', 'today');

        $today_opd = OpdPatient::with(['patient', 'department'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('date', today())
            ->latest('id')
            ->paginate(20, ['*'], 'today_page')->withQueryString();

        $upcoming_opd = OpdPatient::with(['patient', 'department'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('date', '>', today())
            ->orderBy('date')
            ->paginate(20, ['*'], 'upcoming_page')->withQueryString();

        $ipd_patients = IpdPatient::with(['patient', 'department', 'bedAllocations.bed'])
            ->where('doctor_id', $doctor->id)
            ->whereNull('discharge_date')
            ->latest('admission_date')
            ->paginate(20, ['*'], 'ipd_page')->withQueryString();

        $today_appointments = Appointment::with('patient')
            ->where('doctor', $doctor->id)
            ->whereDate('date', today())
            ->orderBy('slot_time_from')
            ->paginate(20, ['*'], 'appt_page')->withQueryString();

        $recent_prescriptions = Prescription::with(['patient'])
            ->where('doctor_id', $doctor->id)
            ->latest('id')
            ->limit(10)
            ->get();

        $stats = [
            'today_opd_count'         => OpdPatient::where('doctor_id', $doctor->id)->whereDate('date', today())->count(),
            'upcoming_opd_count'      => OpdPatient::where('doctor_id', $doctor->id)->whereDate('date', '>', today())->count(),
            'ipd_active_count'        => IpdPatient::where('doctor_id', $doctor->id)->whereNull('discharge_date')->count(),
            'ipd_discharged_count'    => IpdPatient::where('doctor_id', $doctor->id)->whereNotNull('discharge_date')->count(),
            'today_appointments_count'=> Appointment::where('doctor', $doctor->id)->whereDate('date', today())->count(),
            'rx_total'                => Prescription::where('doctor_id', $doctor->id)->count(),
            'rx_this_month'           => Prescription::where('doctor_id', $doctor->id)->whereMonth('created_at', now()->month)->count(),
        ];

        return view('doctor_portal.index', compact(
            'doctor', 'tab', 'stats',
            'today_opd', 'upcoming_opd', 'ipd_patients',
            'today_appointments', 'recent_prescriptions'
        ));
    }
}
