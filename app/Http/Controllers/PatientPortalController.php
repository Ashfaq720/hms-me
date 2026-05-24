<?php

namespace App\Http\Controllers;

use App\Models\Billing\Bill;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PatientPortalController extends Controller
{
    /**
     * Patient self-service portal — patients can see their own bills,
     * prescriptions, lab results, visits.
     *
     * Auth via the 'patient' guard (separate from staff 'web' guard).
     */

    public function showLogin()
    {
        if (Auth::guard('patient')->check()) {
            return redirect()->route('portal.dashboard');
        }
        return view('portal.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'identifier' => 'required|string',  // MRN OR mobile OR email
            'password'   => 'required|string|min:4',
        ]);

        $patient = Patient::where('mrn', $data['identifier'])
            ->orWhere('mobileno', $data['identifier'])
            ->orWhere('email', $data['identifier'])
            ->first();

        if (! $patient || ! $patient->portal_password || ! Hash::check($data['password'], $patient->portal_password)) {
            throw ValidationException::withMessages([
                'identifier' => 'Wrong MRN/mobile/email or password.',
            ]);
        }

        Auth::guard('patient')->login($patient, $request->boolean('remember'));
        $patient->forceFill(['portal_last_login_at' => now()])->save();

        return redirect()->intended(route('portal.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('patient')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('portal.login');
    }

    public function dashboard()
    {
        $patient = Auth::guard('patient')->user();

        $bills = Bill::with('payments')
            ->where('patient_id', $patient->id)
            ->latest('id')->limit(20)->get();

        $unpaidBills = Bill::where('patient_id', $patient->id)
            ->where('balance_due', '>', 0)
            ->count();

        $prescriptions = Prescription::with('medicines.medicine', 'doctor')
            ->where('patient_id', $patient->id)
            ->latest('id')->limit(10)->get();

        // OPD/IPD visit timeline
        $visits = collect();
        foreach (\DB::table('opd_patients')->where('patient_id', $patient->id)->get(['id', 'date', 'token_no', 'doctor_id', 'status']) as $o) {
            $visits->push((object) [
                'type'      => 'OPD',
                'date'      => $o->date,
                'reference' => $o->token_no,
                'status'    => $o->status,
                'doctor_id' => $o->doctor_id,
            ]);
        }
        foreach (\DB::table('i_p_d_patients')->where('patient_id', $patient->id)->get(['id', 'admission_date', 'discharge_date', 'ipd_no', 'doctor_id', 'status']) as $i) {
            $visits->push((object) [
                'type'      => 'IPD',
                'date'      => $i->admission_date,
                'reference' => $i->ipd_no,
                'status'    => $i->discharge_date ? 'Discharged' : $i->status,
                'doctor_id' => $i->doctor_id,
            ]);
        }
        $visits = $visits->sortByDesc('date')->take(15)->values();

        $stats = [
            'total_visits'   => $visits->count(),
            'unpaid_bills'   => $unpaidBills,
            'total_due'      => (float) Bill::where('patient_id', $patient->id)->sum('balance_due'),
            'prescriptions'  => Prescription::where('patient_id', $patient->id)->count(),
            'lab_orders'     => \DB::table('lab_investigation_order')->where('patient_id', $patient->id)->count(),
        ];

        return view('portal.dashboard', compact('patient', 'bills', 'prescriptions', 'visits', 'stats'));
    }

    public function bills()
    {
        $patient = Auth::guard('patient')->user();
        $bills = Bill::with(['payments', 'items'])
            ->where('patient_id', $patient->id)
            ->latest('id')->paginate(20);
        return view('portal.bills', compact('patient', 'bills'));
    }

    public function prescriptions()
    {
        $patient = Auth::guard('patient')->user();
        $prescriptions = Prescription::with(['medicines.medicine', 'doctor', 'labInvestigations.labInvestigation', 'symptoms.symptom'])
            ->where('patient_id', $patient->id)
            ->latest('id')->paginate(15);
        return view('portal.prescriptions', compact('patient', 'prescriptions'));
    }

    public function profile()
    {
        $patient = Auth::guard('patient')->user();
        return view('portal.profile', compact('patient'));
    }

    public function changePassword(Request $request)
    {
        $patient = Auth::guard('patient')->user();
        $data = $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);
        if (! Hash::check($data['current_password'], $patient->portal_password)) {
            throw ValidationException::withMessages(['current_password' => 'Current password is wrong.']);
        }
        $patient->forceFill(['portal_password' => Hash::make($data['new_password'])])->save();
        return back()->with('success', 'Password updated.');
    }
}
